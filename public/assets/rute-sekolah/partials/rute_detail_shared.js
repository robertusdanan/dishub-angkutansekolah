
import { proxyGet } from './supabase_proxy_client.js';

// ── Derivasi waktu (Pagi/Siang/Sore/Malam) dari id_map ─────────────────
export function waktuFromIdMap(idMap) {
  const low = String(idMap || '').toLowerCase();
  if (low.includes('pagi'))  return 'Pagi';
  if (low.includes('siang')) return 'Siang';
  if (low.includes('sore'))  return 'Sore';
  if (low.includes('malam')) return 'Malam';
  return 'Lainnya';
}


export function ruteLabelFromIdMap(idMap) {
  const m = String(idMap || '').match(/rute(\d+)/i);
  const num = m ? parseInt(m[1], 10) : 1;
  return { num, label: `Rute ${num}` };
}

/**
 * Ambil & kelompokkan data detail satu trayek (Bus atau MPU).
 *
 * @param {{trayekTable:string, driverTable:string, id:string}} opts
 * @returns {Promise<
 *   {status:'trayek_not_found'} |
 *   {status:'route_not_found'} |
 *   {status:'ok', trayekName:string, pagi:Array, siang:Array, lainnya:Array}
 * >}
 */
export async function fetchRuteDetailGrouped({ trayekTable, driverTable, id }) {
  // 1) Ambil nama trayek dulu (dipakai untuk cocokkan driver_*.trayek)
  const trRows = await proxyGet(trayekTable, `&col[]=id&val[]=${encodeURIComponent(id)}&select=id,nama`);
  if (!Array.isArray(trRows) || trRows.length === 0) {
    return { status: 'trayek_not_found' };
  }
  const trayekName = trRows[0].nama || 'Trayek tidak diketahui';

  // 2) Ambil titik rute (map) & daftar sopir (driver_*) sekaligus
  const [mapRows, driverRows] = await Promise.all([
    proxyGet('map', `&col[]=id_trayek&val[]=${encodeURIComponent(id)}&select=id_map,urutan,nama_tempat&order=id_map.asc,urutan.asc`),
    proxyGet(driverTable, `&col[]=trayek&val[]=${encodeURIComponent(trayekName)}&select=sopir:driver,nopol:plat,rute&order=plat.asc`),
  ]);

  if (!Array.isArray(mapRows) || mapRows.length === 0) {
    return { status: 'route_not_found' };
  }

  const drivers = Array.isArray(driverRows) ? driverRows : [];
  // Filter KETAT: hanya driver yang id_map-nya benar-benar dicentang di
  // admin (driver_*.rute) yang tampil untuk rute tsb. Tidak ada fallback
  // "tampilkan semua sopir trayek" walau belum ada satupun yang dicentang.
  function driversForIdMap(idMap) {
    return drivers.filter(d => Array.isArray(d.rute) && d.rute.includes(idMap));
  }

  // 3) Kelompokkan titik map per id_map → jadi satu "rute" utuh
  const byIdMap = {};
  mapRows.forEach(r => {
    if (!byIdMap[r.id_map]) byIdMap[r.id_map] = [];
    byIdMap[r.id_map].push(r);
  });

  const routes = Object.keys(byIdMap).map(idMap => {
    const pts   = byIdMap[idMap].sort((a, b) => (a.urutan || 0) - (b.urutan || 0));
    const rute  = pts.map(p => p.nama_tempat).filter(Boolean).join(' - ');
    const waktu = waktuFromIdMap(idMap);
    const { num, label } = ruteLabelFromIdMap(idMap);
    return { idMap, rute, waktu, num, label, drivers: driversForIdMap(idMap) };
  });

  const pagi    = routes.filter(r => r.waktu === 'Pagi').sort((a, b) => a.num - b.num);
  const siang   = routes.filter(r => r.waktu === 'Siang').sort((a, b) => a.num - b.num);
  const lainnya = routes.filter(r => r.waktu !== 'Pagi' && r.waktu !== 'Siang').sort((a, b) => a.num - b.num);

  return { status: 'ok', trayekName, pagi, siang, lainnya };
}
