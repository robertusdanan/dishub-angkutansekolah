

const PROXY            = '/api/supabase-proxy';


const CLR = {
  bg        : 'rgba(15,22,46,0.85)',
  bgSolid   : '#060b17',
  text      : '#F0F4F8',
  muted     : '#8BA3BC',
  mutedDim  : 'rgba(140,163,188,0.15)',
  online    : '#22c55e',
  offline   : '#EF4444',
};

const PALETTE = [
  '#06b6d4', '#38BDF8', '#F59E0B', '#F472B6', '#A78BFA',
  '#FB923C', '#4ADE80', '#3b82f6', '#FACC15', '#F87171',
  '#2DD4BF', '#C084FC', '#FB7185', '#34D399', '#818CF8',
];

const sbRealtime = (window.SB_URL && window.SB_ANON && window.supabase)
  ? window.supabase.createClient(window.SB_URL, window.SB_ANON, {
      realtime: { params: { eventsPerSecond: 10 } },
    })
  : null;

const mkIcon = (html, w = 30, h = 50, ax = 15, ay = 42) =>
  L.divIcon({ html, className: '', iconSize: [w, h], iconAnchor: [ax, ay] });

async function sbFetch(table, extra = '') {
  const url = `${PROXY}?table=${encodeURIComponent(table)}${extra}`;
  const res = await fetch(url);
  if (!res.ok) {
    const body = await res.json().catch(() => ({}));
    throw new Error(body.error || `[${table}] HTTP ${res.status}`);
  }
  return res.json();
}

// Routing jalan sungguhan via proxy → OSRM (sama persis dengan rute_map.js,
// supaya bentuk garis di peta gabungan konsisten dengan halaman detail trayek).
async function fetchRoute(waypoints, profile = 'driving') {
  const coordsStr = waypoints.map(p => `${p.lng},${p.lat}`).join(';');
  const url = new URL(PROXY, location.origin);
  url.searchParams.set('action', 'route');
  url.searchParams.set('coords', coordsStr);
  url.searchParams.set('profile', profile);
  const res = await fetch(url);
  if (!res.ok) {
    const body = await res.json().catch(() => ({}));
    throw new Error(body.error || `[route] HTTP ${res.status}`);
  }
  const data = await res.json();
  if (!data.routes?.length) throw new Error('Rute tidak ditemukan dari server.');
  return data.routes[0].geometry;
}

function hideLoading() {
  document.dispatchEvent(new Event('map-ready'));
}
function setLoadingStep(text, percent) {
  document.dispatchEvent(new CustomEvent('map-progress', { detail: { text, percent } }));
}
function showLoadingError(message) {
  document.dispatchEvent(new CustomEvent('map-error', { detail: { message } }));
}
function setTitle(text) {
  const el = document.getElementById('title-name-text');
  if (el) el.textContent = text;
}
function setStat(id, value) {
  const el = document.getElementById(id);
  if (el) el.textContent = value;
}

function makeDriverMarker(driver, status) {
  const color = status.color;
  const label = driver.plat ?? '-';
  const busOpacity = status.dim ? 0.55 : 1;
  return mkIcon(
    `<div style="display:flex;flex-direction:column;align-items:center;cursor:pointer;font-family:'Plus Jakarta Sans',sans-serif;opacity:${busOpacity};">
      <div style="background:rgba(6,13,23,0.94);color:${color};padding:2px 9px;
        border-radius:100px;font-size:10px;font-weight:700;margin-bottom:4px;
        border:1px solid ${color}66;letter-spacing:0.04em;white-space:nowrap;
        box-shadow:0 3px 10px rgba(0,0,0,0.55);">${status.label}</div>
      <div style="width:36px;height:36px;border-radius:50%;display:flex;align-items:center;
        justify-content:center;flex-shrink:0;
        background:linear-gradient(145deg, ${color}33 0%, ${CLR.bgSolid} 130%);
        border:2.5px solid ${color};
        box-shadow:0 3px 10px rgba(0,0,0,0.6), 0 0 0 3px ${color}26;">
        <i class="fa-solid fa-bus" style="font-size:15px;color:${color};"></i>
      </div>
      <div style="margin-top:4px;background:rgba(6,13,23,0.94);color:${color};
        padding:2px 8px;font-size:10px;border-radius:100px;border:1px solid ${color}66;
        font-weight:700;white-space:nowrap;box-shadow:0 3px 10px rgba(0,0,0,0.5);">${label}</div>
    </div>`,
    30, 66, 15, 46
  );
}

// ═══════════════════════════════════════════════════════════════════════
//  CARD INFO DRIVER - mengambang di atas titik driver, ikut mengalir halus
//  saat titik bergerak (posisi dihitung dari koordinat marker Leaflet).
// ═══════════════════════════════════════════════════════════════════════
function formatLastOnline(ts) {
  if (!ts) return 'Belum pernah online';
  const diffMs = Date.now() - new Date(ts).getTime();
  if (diffMs < 45_000) return 'Baru saja';
  const min = Math.floor(diffMs / 60_000);
  if (min < 60) return `${min} menit lalu`;
  const hr = Math.floor(min / 60);
  if (hr < 24) return `${hr} jam lalu`;
  const day = Math.floor(hr / 24);
  return `${day} hari lalu`;
}

let openDriverMarker = null; // marker Leaflet yang cardnya sedang tampil

function ensureDriverCard() {
  if (document.getElementById('driver-card')) return;

  const style = document.createElement('style');
  style.textContent = `
    #driver-card{position:fixed;z-index:1150;left:-9999px;top:-9999px;
      opacity:0;pointer-events:none;transform:translateY(6px) scale(.97);
      transition:opacity .18s ease, transform .18s ease, left .28s cubic-bezier(.22,.61,.36,1), top .28s cubic-bezier(.22,.61,.36,1);
      font-family:'Plus Jakarta Sans',sans-serif;}
    #driver-card.visible{opacity:1;pointer-events:auto;transform:translateY(0) scale(1);}
    #driver-card .dc-inner{position:relative;width:min(240px,80vw);
      background:rgba(15,22,46,0.88);backdrop-filter:blur(24px);-webkit-backdrop-filter:blur(24px);
      border:1px solid rgba(255,255,255,0.08);border-radius:16px;padding:14px 16px 12px;
      box-shadow:0 20px 60px rgba(0,0,0,0.65), 0 0 0 1px rgba(255,255,255,0.05);}
    #driver-card .dc-close{position:absolute;top:8px;right:8px;width:22px;height:22px;
      display:flex;align-items:center;justify-content:center;background:rgba(140,163,188,0.1);
      border:none;border-radius:50%;color:rgba(140,163,188,0.7);font-size:11px;cursor:pointer;
      line-height:1;padding:0;transition:background .12s,color .12s;}
    #driver-card .dc-close:hover{background:rgba(140,163,188,0.22);color:#F0F4F8;}
    #driver-card .dc-plat{font-size:17px;font-weight:800;letter-spacing:.03em;color:#F0F4F8;margin-bottom:8px;padding-right:20px;}
    #driver-card .dc-row{display:flex;align-items:center;justify-content:space-between;
      padding:5px 0;border-top:1px solid rgba(255,255,255,0.06);}
    #driver-card .dc-label{font-size:10px;color:rgba(140,163,188,0.75);font-weight:500;
      letter-spacing:.05em;text-transform:uppercase;}
    #driver-card .dc-value{font-size:12px;color:#F0F4F8;font-weight:600;text-align:right;max-width:60%;}
    #driver-card .dc-status{display:inline-flex;align-items:center;gap:5px;}
    #driver-card .dc-dot{width:7px;height:7px;border-radius:50%;flex-shrink:0;}
    #driver-card .dc-arrow{width:14px;height:8px;margin:0 auto;display:block;overflow:visible;}
  `;
  document.head.appendChild(style);

  const card = document.createElement('div');
  card.id = 'driver-card';
  card.innerHTML = `
    <div class="dc-inner">
      <button class="dc-close" type="button" aria-label="Tutup">✕</button>
      <div class="dc-plat" id="dc-plat">-</div>
      <div class="dc-row"><span class="dc-label">Driver</span><span class="dc-value" id="dc-driver">-</span></div>
      <div class="dc-row"><span class="dc-label">Trayek</span><span class="dc-value" id="dc-trayek">-</span></div>
      <div class="dc-row"><span class="dc-label">Terakhir Online</span>
        <span class="dc-value dc-status"><span class="dc-dot" id="dc-dot"></span><span id="dc-lastonline">-</span></span>
      </div>
    </div>
    <svg class="dc-arrow" viewBox="0 0 14 8" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path d="M0 0 L7 8 L14 0" fill="rgba(15,22,46,0.88)" stroke="rgba(255,255,255,0.08)" stroke-width="1" stroke-linejoin="round"/>
    </svg>`;
  document.body.appendChild(card);

  card.querySelector('.dc-close').addEventListener('click', closeDriverCard);
}

function fillDriverCard(driver, status) {
  document.getElementById('dc-plat').textContent   = driver.plat || '-';
  document.getElementById('dc-driver').textContent = driver.driver || '-';
  document.getElementById('dc-trayek').textContent = driver.trayek || '-';
  document.getElementById('dc-lastonline').textContent =
    status.key === 'online' ? 'Online sekarang' : `${status.label} · ${formatLastOnline(driver.update_at)}`;
  document.getElementById('dc-dot').style.background = status.color;
}

function positionDriverCard(animate = true) {
  if (!openDriverMarker || !window._leafletMap) return;
  const card = document.getElementById('driver-card');
  if (!card) return;
  const map     = window._leafletMap;
  const latlng  = openDriverMarker.getLatLng();
  const pt      = map.latLngToContainerPoint(latlng);
  const mapRect = map.getContainer().getBoundingClientRect();
  const cw = card.offsetWidth;
  const ch = card.offsetHeight;
  const left = mapRect.left + pt.x - cw / 2;
  const top  = mapRect.top  + pt.y - ch - 40;

  if (!animate) card.style.transition = 'opacity .18s ease, transform .18s ease';
  card.style.left = `${left}px`;
  card.style.top  = `${top}px`;
  if (!animate) {
    void card.offsetWidth;
    card.style.transition = '';
  }
}

function openDriverCard(marker) {
  ensureDriverCard();
  fillDriverCard(marker.driverData, marker.status);
  openDriverMarker = marker;
  document.getElementById('driver-card').classList.add('visible');
  positionDriverCard(false);
}

function closeDriverCard() {
  const card = document.getElementById('driver-card');
  if (card) card.classList.remove('visible');
  openDriverMarker = null;
}

// ── Derivasi waktu (Pagi/Siang/Lainnya) dari id_map, sama seperti rute_bus.js ──
function waktuKeyFromIdMap(idMap) {
  const low = String(idMap || '').toLowerCase();
  if (low.includes('pagi'))  return 'pagi';
  if (low.includes('siang')) return 'siang';
  return 'lainnya';
}

function makeStopDot(color) {
  return mkIcon(
    `<div style="width:8px;height:8px;border-radius:50%;background:${color};
      box-shadow:0 0 0 2px ${CLR.bgSolid};"></div>`,
    8, 8, 4, 4
  );
}

document.addEventListener('DOMContentLoaded', async () => {
  const sidebarBody = document.getElementById('sidebar-body');

  try {
    // ── 1. Init peta ─────────────────────────────────────────────────────
    const map = L.map('map', {
      zoomControl: false,
      attributionControl: false,
    }).setView([-8.0648, 111.9028], 11); // pusat kira-kira Tulungagung, disesuaikan setelah data masuk
    window._leafletMap = map; // dipakai untuk memposisikan driver-card

    // Card info driver ikut mengalir saat peta digeser/di-zoom (instan), dan
    // meluncur halus saat titik driver sendiri berpindah (lihat upsertDriverMarker).
    map.on('move zoom', () => positionDriverCard(false));
    map.on('click', () => closeDriverCard());

    L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
      maxZoom: 19,
      subdomains: ['mt0'],
    }).addTo(map);

    // Garis batas Kota/Kabupaten Tulungagung - gaya sama dengan peta ASDP
    fetch('/assets/rute-sekolah/geo/tulungagung.geojson')
      .then(r => r.json())
      .then(data => {
        L.geoJSON(data, {
          style: { color: '#93c5fd', weight: 3, opacity: 1, fillColor: 'rgba(59,130,246,0.07)', fillOpacity: 1 },
        }).addTo(map);
      })
      .catch(() => console.warn('[peta_gabungan] Batas wilayah (geojson) tidak tersedia.'));

    L.control.zoom({ position: 'bottomright' }).addTo(map);

    // ── 2. Ambil semua data trayek & titik map sekaligus ──────────────────
    setLoadingStep('Mengambil data trayek & titik rute...', 20);
    const [trayekBusRows, trayekMpuRows, mapRows] = await Promise.all([
      sbFetch('trayek_bus', '&select=id,nama'),
      sbFetch('trayek_mpu', '&select=id,nama'),
      sbFetch('map', '&select=id_trayek,id_map,urutan,nama_tempat,lat,lng,code_map'),
    ]);

    const trayekBus = Array.isArray(trayekBusRows) ? trayekBusRows : [];
    const trayekMpu = Array.isArray(trayekMpuRows) ? trayekMpuRows : [];
    const allMap    = Array.isArray(mapRows) ? mapRows : [];

    const busIds = new Set(trayekBus.map(t => t.id));
    const mpuIds = new Set(trayekMpu.map(t => t.id));
    const namaById = {};
    trayekBus.forEach(t => { namaById[t.id] = t.nama; });
    trayekMpu.forEach(t => { namaById[t.id] = t.nama; });

    // Kelompokkan titik map per id_trayek → per id_map
    const byTrayek = {};
    allMap.forEach(r => {
      if (!r.id_trayek) return;
      if (!byTrayek[r.id_trayek]) byTrayek[r.id_trayek] = {};
      if (!byTrayek[r.id_trayek][r.id_map]) byTrayek[r.id_trayek][r.id_map] = [];
      byTrayek[r.id_trayek][r.id_map].push(r);
    });

    const trayekIds = Object.keys(byTrayek).filter(id => namaById[id]);

    if (trayekIds.length === 0) {
      setTitle('Belum ada data rute.');
      sidebarBody.innerHTML = '<div class="sidebar-empty">Belum ada data trayek.</div>';
      hideLoading();
      return;
    }

    // ── 3. Bangun layer per trayek + warna ─────────────────────────────────
    const routesByTrayek = {}; // idTrayek -> { pagi:[{num,label,layer}], siang:[...], lainnya:[...] }
    const colorByTrayek = {};
    const allBounds = [];
    let totalTitik = 0;
    let trayekSelesai = 0;

    setLoadingStep(`Menggambar rute (0/${trayekIds.length} trayek)...`, 45);

    // Derivasi nomor rute ("Rute 1", "Rute 2", ...) dari id_map - sama seperti rute_bus.js
    function ruteNumFromIdMap(idMap) {
      const m = String(idMap || '').match(/rute[_-]?(\d+)/i);
      return m ? parseInt(m[1], 10) : 1;
    }

    // Tiap id_map (= 1 rute spesifik) dapat layer SENDIRI (tidak digabung),
    // supaya kalau 1 trayek punya beberapa rute (Rute 1/Rute 2/Rute 3) untuk
    // waktu yang sama, masing-masing bisa ditampilkan satu-per-satu (eksklusif)
    // lewat submenu di sidebar.
    await Promise.all(trayekIds.map(async (idTrayek, idx) => {
      const color = PALETTE[idx % PALETTE.length];
      colorByTrayek[idTrayek] = color;

      const routesByWaktu = { pagi: [], siang: [], lainnya: [] };
      const idMapGroups = byTrayek[idTrayek];

      // Semua id_map (rute) dalam satu trayek digambar paralel, tiap-tiap
      // rute mengikuti jalan sungguhan lewat OSRM - persis seperti di
      // halaman detail rute (rute_map.js).
      await Promise.all(Object.keys(idMapGroups).map(async idMap => {
        const waktuKey = waktuKeyFromIdMap(idMap);
        const routeNum = ruteNumFromIdMap(idMap);
        const layer = L.layerGroup();

        const pts = idMapGroups[idMap]
          .slice()
          .sort((a, b) => (a.urutan || 0) - (b.urutan || 0));

        const latlngs = pts
          .map(p => [parseFloat(p.lat), parseFloat(p.lng)])
          .filter(([lat, lng]) => isFinite(lat) && isFinite(lng));

        if (latlngs.length < 1) return;

        totalTitik += latlngs.length;
        latlngs.forEach(ll => allBounds.push(ll));

        if (latlngs.length >= 2) {
          const waypoints = latlngs.map(([lat, lng]) => ({ lat, lng }));
          try {
            const geojson = await fetchRoute(waypoints, 'driving');

            // Glow layer di bawah (sama seperti rute_map.js)
            L.geoJSON(geojson, {
              style: { color, weight: 8, opacity: 0.15 },
            }).addTo(layer);

            // Garis rute solid mengikuti jalan
            L.geoJSON(geojson, {
              style: { color, weight: 3.5, opacity: 0.9 },
            }).addTo(layer);
          } catch (routeErr) {
            console.warn(`[peta_gabungan] Routing gagal untuk ${idMap}, fallback ke garis lurus:`, routeErr);
            L.polyline(latlngs, {
              color,
              weight: 3,
              opacity: 0.6,
              dashArray: '6, 6',
              lineCap: 'round',
            }).addTo(layer);
          }
        }

        pts.forEach((p, i) => {
          const lat = parseFloat(p.lat);
          const lng = parseFloat(p.lng);
          if (!isFinite(lat) || !isFinite(lng)) return;
          L.marker([lat, lng], { icon: makeStopDot(color) })
            .bindTooltip(p.nama_tempat || `Titik ${i + 1}`, { className: 'modern-tooltip', direction: 'top', offset: [0, -4] })
            .addTo(layer);
        });

        routesByWaktu[waktuKey].push({ num: routeNum, idMap, layer });
      }));

      // Urutkan tiap grup waktu berdasarkan nomor rute (Rute 1, Rute 2, ...)
      Object.values(routesByWaktu).forEach(arr => arr.sort((a, b) => a.num - b.num));

      routesByTrayek[idTrayek] = routesByWaktu;
      // default: semua trayek NONAKTIF (tidak ditambahkan ke peta dulu)

      trayekSelesai++;
      const pct = 45 + Math.round((trayekSelesai / trayekIds.length) * 40); // 45–85%
      setLoadingStep(`Menggambar rute (${trayekSelesai}/${trayekIds.length} trayek)...`, pct);
    }));

    // Fit ke semua titik
    if (allBounds.length > 0) {
      map.fitBounds(L.latLngBounds(allBounds), { padding: [60, 60] });
    }

    setTitle(`${trayekBus.length} Trayek BUS • ${trayekMpu.length} Trayek MPU`);
    setStat('stat-trayek', trayekIds.length);
    setStat('stat-total-trayek', trayekIds.length);

    setLoadingStep('Menyiapkan tampilan akhir...', 95);
    map.once('tilesloaded', hideLoading);
    setTimeout(hideLoading, 3500);

    // ── 4. Sidebar: daftar trayek, dikelompokkan BUS / MPU ─────────────────
    //    - Trayek dgn 1 rute saja (utk waktu terpilih) → checkbox biasa.
    //    - Trayek dgn >1 rute (Rute 1/2/3, dst) → tidak bisa langsung
    //      dicentang; klik barisnya untuk membuka submenu (animasi elegan),
    //      lalu pilih SATU rute saja (eksklusif, tidak bisa gabung).
    const busTrayekIds = trayekIds.filter(id => busIds.has(id));
    const mpuTrayekIds = trayekIds.filter(id => mpuIds.has(id));
    const otherTrayekIds = trayekIds.filter(id => !busIds.has(id) && !mpuIds.has(id));

    let currentWaktu = 'pagi'; // default pilihan awal: Rute Pagi
    const addedLayer = {}; // id_trayek -> layerGroup rute yang sedang tampil di peta

    // Ambil daftar rute (Rute 1/2/3...) milik 1 trayek utk waktu yang sedang aktif
    function routesForCurrentWaktu(id) {
      const rw = routesByTrayek[id];
      if (!rw) return [];
      let list = rw[currentWaktu];
      if (!list || list.length === 0) list = rw.lainnya;
      return list || [];
    }

    function updateActiveCount() {
      setStat('stat-trayek', Object.keys(addedLayer).length);
    }

    // Sinkronkan tampilan baris induk (badge "Rute N" + highlight) saat aktif/nonaktif
    function syncParentRowState(id) {
      const row = sidebarBody.querySelector(`[data-trayek-parent="${id}"]`);
      if (!row) return;
      const badge = document.getElementById(`badge-${id}`);
      const active = !!addedLayer[id];
      row.classList.toggle('active-route', active);
      if (badge) {
        const checkedBox = sidebarBody.querySelector(`input[type=checkbox][data-trayek="${id}"][data-route]:checked`);
        badge.textContent = checkedBox ? `Rute ${checkedBox.dataset.route}` : '';
        badge.style.display = checkedBox ? 'inline-flex' : 'none';
      }
    }

    // Aktifkan 1 rute spesifik utk 1 trayek (otomatis lepas rute lain trayek yg sama)
    function activateRoute(id, layer) {
      if (addedLayer[id]) map.removeLayer(addedLayer[id]);
      layer.addTo(map);
      addedLayer[id] = layer;
      updateActiveCount();
      syncParentRowState(id);
    }

    function deactivateTrayek(id) {
      if (addedLayer[id]) {
        map.removeLayer(addedLayer[id]);
        delete addedLayer[id];
      }
      updateActiveCount();
      syncParentRowState(id);
    }

    function renderGroup(title, ids, groupKey) {
      if (ids.length === 0) return '';
      let html = `
        <div class="group-label">
          <span>${title}</span>
          <button class="group-toggle-all" data-group="${groupKey}" data-action="show">Tampilkan</button>
        </div>`;
      ids.forEach(id => {
        const color = colorByTrayek[id] || '#8BA3BC';
        const routes = routesForCurrentWaktu(id);

        if (routes.length <= 1) {
          // Trayek dengan 1 rute saja → checkbox biasa, langsung tampil/sembunyi
          html += `
            <label class="route-item" style="--rc:${color}">
              <input type="checkbox" data-trayek="${id}">
              <span class="route-swatch" style="background:${color}"></span>
              <span class="route-name">${namaById[id] || id}</span>
            </label>`;
        } else {
          // Trayek dengan beberapa rute → tidak bisa langsung dicentang,
          // klik barisnya utk membuka submenu Rute 1 / Rute 2 / dst (eksklusif)
          html += `
            <div class="route-group">
              <div class="route-item route-parent" style="--rc:${color}" data-trayek-parent="${id}">
                <span class="route-swatch" style="background:${color}"></span>
                <span class="route-name">${namaById[id] || id}</span>
                <span class="route-active-badge" id="badge-${id}" style="display:none"></span>
                <button type="button" class="route-chevron" data-chevron="${id}" aria-label="Tampilkan pilihan rute">
                  <i class="fa-solid fa-chevron-down"></i>
                </button>
              </div>
              <div class="route-submenu" id="submenu-${id}">
                ${routes.map(r => `
                  <label class="route-subitem" style="--rc:${color}">
                    <input type="checkbox" data-trayek="${id}" data-route="${r.num}">
                    <span class="route-subname">Rute ${r.num}</span>
                  </label>`).join('')}
              </div>
            </div>`;
        }
      });
      return html;
    }

    function wireSidebarEvents() {
      // Trayek 1-rute → checkbox biasa langsung aktif/nonaktif
      sidebarBody.querySelectorAll('input[type=checkbox][data-trayek]:not([data-route])').forEach(cb => {
        cb.addEventListener('change', () => {
          const id = cb.dataset.trayek;
          const routes = routesForCurrentWaktu(id);
          if (cb.checked && routes[0]) {
            activateRoute(id, routes[0].layer);
          } else {
            deactivateTrayek(id);
          }
        });
      });

      // Klik baris trayek multi-rute → buka/tutup submenu dgn animasi elegan
      sidebarBody.querySelectorAll('[data-trayek-parent]').forEach(row => {
        row.addEventListener('click', () => {
          const id = row.dataset.trayekParent;
          const submenu = document.getElementById(`submenu-${id}`);
          const chevron = row.querySelector('.route-chevron');
          const isOpen = submenu.classList.toggle('open');
          chevron.classList.toggle('open', isOpen);
        });
      });

      // Checkbox Rute 1/2/3 → eksklusif per trayek (pilih 1 rute matikan yang lain)
      sidebarBody.querySelectorAll('input[type=checkbox][data-route]').forEach(cb => {
        cb.addEventListener('change', () => {
          const id = cb.dataset.trayek;
          if (cb.checked) {
            sidebarBody
              .querySelectorAll(`input[type=checkbox][data-trayek="${id}"][data-route]`)
              .forEach(other => { if (other !== cb) other.checked = false; });
            const routes = routesForCurrentWaktu(id);
            const chosen = routes.find(r => r.num === parseInt(cb.dataset.route, 10));
            if (chosen) activateRoute(id, chosen.layer);
          } else {
            deactivateTrayek(id);
          }
        });
      });

      // Tombol "Sembunyikan semua" / "Tampilkan semua" per kategori
      sidebarBody.querySelectorAll('.group-toggle-all').forEach(btn => {
        btn.addEventListener('click', () => {
          const groupKey = btn.dataset.group;
          const idsInGroup = groupKey === 'bus' ? busTrayekIds : groupKey === 'mpu' ? mpuTrayekIds : otherTrayekIds;
          const nowShowing = btn.dataset.action === 'show';

          idsInGroup.forEach(id => {
            const routes = routesForCurrentWaktu(id);
            if (nowShowing) {
              if (routes[0]) activateRoute(id, routes[0].layer);
              const simpleCb = sidebarBody.querySelector(`input[type=checkbox][data-trayek="${id}"]:not([data-route])`);
              if (simpleCb) simpleCb.checked = true;
              const firstSub = sidebarBody.querySelector(`input[type=checkbox][data-trayek="${id}"][data-route="${routes[0]?.num}"]`);
              if (firstSub) firstSub.checked = true;
            } else {
              deactivateTrayek(id);
              sidebarBody.querySelectorAll(`input[type=checkbox][data-trayek="${id}"]`).forEach(cb => { cb.checked = false; });
            }
          });

          btn.dataset.action = nowShowing ? 'hide' : 'show';
          btn.textContent = nowShowing ? 'Sembunyikan' : 'Tampilkan';
        });
      });
    }

    function renderSidebar() {
      sidebarBody.innerHTML =
        renderGroup('Trayek BUS', busTrayekIds, 'bus') +
        renderGroup('Trayek MPU', mpuTrayekIds, 'mpu') +
        renderGroup('Lainnya', otherTrayekIds, 'lain');
      wireSidebarEvents();
      updateActiveCount();
    }

    renderSidebar(); // semua trayek nonaktif di awal

    // Tombol elegan "Rute Pagi" / "Rute Siang" di header sidebar - daftar rute
    // per trayek bisa berbeda antar waktu, jadi tampilan direset & sidebar
    // digambar ulang setiap kali waktu berpindah.
    const waktuButtons = document.querySelectorAll('.waktu-btn');
    waktuButtons.forEach(btn => {
      btn.addEventListener('click', () => {
        const waktu = btn.dataset.waktu;
        if (waktu === currentWaktu) return;

        waktuButtons.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        currentWaktu = waktu;

        Object.keys(addedLayer).forEach(id => {
          map.removeLayer(addedLayer[id]);
          delete addedLayer[id];
        });
        renderSidebar();
      });
    });

    // ── 5. Marker driver - SELALU tampil SEMUA, tidak ikut filter sidebar ──
    const driverMarkers = new Map();
    const presentDriverIds = new Set(); // id driver yang presence-nya aktif (gabungan semua channel code_map)
    let onlineCount = 0;

    function upsertDriverMarker(driver, table) {
      const lat = parseFloat(driver.lat);
      const lng = parseFloat(driver.lng);
      if (!isFinite(lat) || !isFinite(lng)) return;
      const key      = `${table}-${driver.id}`;
      const existing = driverMarkers.get(key);

      // Sama seperti di rute_map.js: broadcast dari driver cuma bawa
      // {id, table, plat, lat, lng, update_at} - merge, jangan replace total,
      // supaya driver/trayek yang sudah didapat dari fetch awal tidak hilang.
      const merged = existing ? { ...existing.driverData, ...driver } : driver;
      const status = DRIVER_STATUS.compute(presentDriverIds.has(String(merged.id)), merged.update_at);
      const icon   = makeDriverMarker(merged, status);

      if (existing) {
        existing.setLatLng([lat, lng]);
        existing.setIcon(icon);
        existing.driverData = merged;
        existing.status     = status;
        if (openDriverMarker === existing) {
          fillDriverCard(merged, status);
          positionDriverCard(true); // ikut mengalir halus ke posisi baru
        }
      } else {
        const marker = L.marker([lat, lng], { icon, zIndexOffset: 1000 }).addTo(map);
        marker.driverData = merged;
        marker.status      = status;
        marker.on('click', (e) => { L.DomEvent.stopPropagation(e); openDriverCard(marker); });
        driverMarkers.set(key, marker);
      }
      recomputeOnline();
    }

    function removeDriverMarker(id, table) {
      const key = `${table}-${id}`;
      const existing = driverMarkers.get(key);
      if (existing) {
        map.removeLayer(existing);
        driverMarkers.delete(key);
      }
      recomputeOnline();
    }

    function recomputeOnline() {
      onlineCount = 0;
      driverMarkers.forEach(m => { if (m.status && m.status.key === 'online') onlineCount++; });
      setStat('stat-driver', onlineCount);
    }

    // Presence baru masuk/keluar TIDAK mengubah lat/lng - cukup hitung ulang
    // status & ikon tiap marker yang sudah ada, tanpa fetch ulang ke DB.
    function refreshAllMarkerStatuses() {
      driverMarkers.forEach((marker) => {
        const status = DRIVER_STATUS.compute(presentDriverIds.has(String(marker.driverData.id)), marker.driverData.update_at);
        marker.status = status;
        marker.setIcon(makeDriverMarker(marker.driverData, status));
        if (openDriverMarker === marker) fillDriverCard(marker.driverData, status);
      });
      recomputeOnline();
    }

    // Jam berganti tanpa event apapun bisa mengubah status "Belum Beroperasi"
    // ↔ "Offline" pada jam batas (04:00/20:00) - cek ulang tiap menit.
    setInterval(refreshAllMarkerStatuses, 60_000);

    const loadAllDrivers = async () => {
      try {
        const [busDrivers, mpuDrivers] = await Promise.all([
          sbFetch('driver_bus', '&select=id,plat,driver,trayek,lat,lng,update_at,code_map'),
          sbFetch('driver_mpu', '&select=id,plat,driver,trayek,lat,lng,update_at,code_map'),
        ]);
        (Array.isArray(busDrivers) ? busDrivers : []).forEach(d => upsertDriverMarker(d, 'driver_bus'));
        (Array.isArray(mpuDrivers) ? mpuDrivers : []).forEach(d => upsertDriverMarker(d, 'driver_mpu'));
      } catch (e) {
        console.error('[peta_gabungan] Gagal memuat posisi driver:', e);
      }
    };
    await loadAllDrivers();

    // ── Realtime ─────────────────────────────────────────────────────────
    // PERBAIKAN BUG: versi lama subscribe ke channel bernama 'driver-pos-all',
    // padahal driver (bus.js) broadcast ke channel 'driver-pos-<code_map>'
    // masing-masing trayek. Nama channel Supabase harus PERSIS SAMA supaya
    // broadcast diterima - jadi selama ini peta gabungan TIDAK PERNAH
    // menerima broadcast posisi sama sekali (hanya "meloncat" tiap ~60 detik
    // lewat postgres_changes). Sekarang subscribe SATU channel per code_map
    // yang benar-benar ada di data rute (allMap), sama seperti driver.
    if (sbRealtime) {
      const uniqueCodeMaps = new Set(allMap.map(r => r.code_map).filter(Boolean));

      uniqueCodeMaps.forEach(cm => {
        const ch = sbRealtime.channel(`driver-pos-${cm}`);
        ch.on('broadcast', { event: 'pos' }, ({ payload }) => {
          upsertDriverMarker(payload, payload.table || 'driver_bus');
        })
        // Presence per-channel digabung ke satu Set bersama (presentDriverIds)
        // supaya status di peta gabungan konsisten dgn peta per-trayek.
        .on('presence', { event: 'sync' }, () => {
          const state = ch.presenceState();
          const idsInThisChannel = new Set();
          Object.values(state).forEach(entries => {
            entries.forEach(e => { if (e.id) idsInThisChannel.add(String(e.id)); });
          });
          // Hapus dulu id lama milik channel ini (kalau ada yg leave), lalu
          // gabungkan id yang sedang present sekarang.
          driverMarkers.forEach((marker, key) => {
            if (marker.driverData.code_map === cm) presentDriverIds.delete(String(marker.driverData.id));
          });
          idsInThisChannel.forEach(id => presentDriverIds.add(id));
          refreshAllMarkerStatuses();
        })
        .subscribe();
      });

      // Safety net: kalau ada driver yang code_map-nya belum tercakup di atas
      // (mis. trayek baru diatur, atau race condition saat load), tetap
      // dengarkan seluruh tabel via postgres_changes supaya perubahan DB
      // (tulis tiap heartbeat ~45 detik) tetap sampai walau broadcast/presence
      // channel spesifiknya terlewat.
      sbRealtime
        .channel('driver-pos-db-sync')
        .on(
          'postgres_changes',
          { event: '*', schema: 'public', table: 'driver_bus' },
          (payload) => {
            if (payload.eventType === 'DELETE') removeDriverMarker(payload.old?.id, 'driver_bus');
            else upsertDriverMarker(payload.new, 'driver_bus');
          }
        )
        .on(
          'postgres_changes',
          { event: '*', schema: 'public', table: 'driver_mpu' },
          (payload) => {
            if (payload.eventType === 'DELETE') removeDriverMarker(payload.old?.id, 'driver_mpu');
            else upsertDriverMarker(payload.new, 'driver_mpu');
          }
        )
        .subscribe();

      window.addEventListener('pagehide', () => sbRealtime.removeAllChannels());

      // Jaring pengaman: re-sync REST berkala (lihat penjelasan lengkap di
      // rute_map.js) - Realtime bisa sesekali terlewat, jadi tetap re-fetch
      // penuh secara berkala supaya data akhirnya konsisten.
      const safetySync = setInterval(loadAllDrivers, 45_000);
      window.addEventListener('pagehide', () => clearInterval(safetySync));
    } else {
      console.warn('[peta_gabungan] Realtime tidak tersedia, fallback ke polling 15s.');
      const pollInterval = setInterval(loadAllDrivers, 15_000);
      window.addEventListener('pagehide', () => clearInterval(pollInterval));
    }

  } catch (err) {
    console.error('[peta_gabungan]', err);
    setTitle('Gagal memuat peta gabungan.');
    sidebarBody.innerHTML = '<div class="sidebar-empty">Gagal memuat data.</div>';
    showLoadingError(err.message || 'Terjadi kesalahan tak terduga saat memuat peta.');
  }
});
