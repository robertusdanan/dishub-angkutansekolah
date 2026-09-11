

const PROXY          = '/api/supabase-proxy';
const MAP_FILTER_COL = 'id_map';
const DRV_FILTER_COL = 'code_map';

const sbRealtime = (window.SB_URL && window.SB_ANON && window.supabase)
  ? window.supabase.createClient(window.SB_URL, window.SB_ANON, {
      realtime: { params: { eventsPerSecond: 10 } },
    })
  : null;

// ── Design tokens (selaras dengan CSS di rute.php & peta ASDP) ─────────────
const CLR = {
  bg        : 'rgba(15,22,46,0.85)',
  bgSolid   : '#060b17',
  teal      : '#06b6d4',
  tealDim   : 'rgba(6,182,212,0.18)',
  tealBorder: 'rgba(6,182,212,0.35)',
  dotGradA  : '#0e7490',
  dotGradB  : '#082f49',
  dotBorder : '#67e8f9',
  text      : '#F0F4F8',
  muted     : '#8BA3BC',
  mutedDim  : 'rgba(140,163,188,0.15)',
  online    : '#22c55e',
  offline   : '#EF4444',
  warn      : '#F59E0B',
  user      : '#3b82f6',
  routeLine : '#3b82f6',
  routeFall : '#6B7280',
};

// Helper: div-icon HTML builder
const mkIcon = (html, w = 30, h = 50, ax = 15, ay = 42) =>
  L.divIcon({ html, className: '', iconSize: [w, h], iconAnchor: [ax, ay] });

// ── Proxy fetch ──────────────────────────────────────────────────────────────
async function sbFetch(table, filters = {}, order = '') {
  const url = new URL(PROXY, location.origin);
  url.searchParams.set('table', table);
  Object.entries(filters).forEach(([c, v]) => {
    url.searchParams.append('col[]', c);
    url.searchParams.append('val[]', v);
  });
  if (order) url.searchParams.set('order', order);
  const res = await fetch(url);
  if (!res.ok) {
    const body = await res.json().catch(() => ({}));
    throw new Error(body.error || `[${table}] HTTP ${res.status}`);
  }
  return res.json();
}

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
  return {
    geojson    : data.routes[0].geometry,
    distanceKm : (data.routes[0].distance / 1000).toFixed(2),
    durationMin: Math.round(data.routes[0].duration / 60),
  };
}

// ── Helpers: hide loading & update UI slots ──────────────────────────────────
function hideLoading() {
  const ov = document.getElementById('loading-overlay');
  if (!ov) return;
  ov.classList.add('hidden');
  setTimeout(() => { ov.style.display = 'none'; }, 500);
}

function setTitle(name) {
  const el = document.getElementById('title-name-text');
  if (el) el.textContent = name;
}

function setStat(id, value) {
  const el = document.getElementById(id);
  if (el) el.textContent = value;
}

// ── Stop modal ───────────────────────────────────────────────────────────────
function showStopModal(name, index, total, lat, lng) {
  const modal    = document.getElementById('stop-modal');
  const nameEl   = document.getElementById('stop-modal-name');
  const indexEl  = document.getElementById('stop-modal-index');

  nameEl.textContent = name;

  // Label posisi: Awal / Akhir / nomor urut
  if (index === 0)           indexEl.innerHTML = `<i class="fa-solid fa-circle-dot" style="font-size:10px;"></i> Awal Jalur`;
  else if (index === total - 1) indexEl.innerHTML = `<i class="fa-solid fa-flag-checkered" style="font-size:10px;"></i> Akhir Jalur`;
  else                       indexEl.innerHTML = `<i class="fa-solid fa-map-pin" style="font-size:10px;"></i> Titik ke-${index + 1} dari ${total}`;

  // Posisikan modal di atas marker via Leaflet latLngToContainerPoint
  modal.classList.remove('visible');
  modal.style.left = '-9999px';
  modal.style.top  = '-9999px';
  modal.classList.add('visible');

  // Tunda satu frame agar elemen terrender dulu, baru hitung ukuran
  requestAnimationFrame(() => {
    // _map diakses dari closure global yang diset saat map init
    if (!window._leafletMap) return;
    const pt     = window._leafletMap.latLngToContainerPoint([lat, lng]);
    const mw     = modal.offsetWidth;
    const mh     = modal.offsetHeight;
    modal.style.left = `${pt.x - mw / 2}px`;
    modal.style.top  = `${pt.y - mh - 10}px`;
  });
}

function hideStopModal() {
  const modal = document.getElementById('stop-modal');
  modal.classList.remove('visible');
}

// ── Marker builders ──────────────────────────────────────────────────────────
function makeStopMarker(name, color, isTerminal) {
  const dot = isTerminal
    ? `<div style="width:16px;height:16px;border-radius:50%;flex-shrink:0;
        background:linear-gradient(145deg, ${color} 0%, ${CLR.bgSolid} 130%);
        border:2.5px solid ${color};
        box-shadow:0 3px 10px rgba(0,0,0,0.6), 0 0 0 3px ${color}26;"></div>`
    : `<div style="width:10px;height:10px;border-radius:50%;background:${color};
        box-shadow:0 0 0 2px ${CLR.bgSolid},0 2px 6px rgba(0,0,0,0.5);flex-shrink:0;"></div>`;

  const badge = isTerminal
    ? `<div style="background:rgba(6,13,23,0.94);color:#ecfeff;padding:3px 9px;
        border-radius:100px;border:1px solid ${color}80;font-size:11px;
        font-weight:700;white-space:nowrap;letter-spacing:0.01em;
        margin-bottom:4px;font-family:'Plus Jakarta Sans',sans-serif;
        box-shadow:0 3px 10px rgba(0,0,0,0.55);">${name}</div>`
    : `<div style="background:rgba(6,13,23,0.94);color:${CLR.text};padding:3px 8px;
        border-radius:100px;border:1px solid rgba(103,232,249,0.35);font-size:10px;
        font-weight:600;white-space:nowrap;margin-bottom:3px;max-width:110px;
        overflow:hidden;text-overflow:ellipsis;font-family:'Plus Jakarta Sans',sans-serif;
        box-shadow:0 3px 10px rgba(0,0,0,0.5);">${name}</div>`;

  return mkIcon(
    `<div style="display:flex;flex-direction:column;align-items:center;">
      ${badge}${dot}
    </div>`,
    isTerminal ? 120 : 110,
    isTerminal ? 44  : 36,
    isTerminal ? 60  : 55,
    isTerminal ? 38  : 30
  );
}

function makeDriverMarker(driver, status) {
  const color = status.color;
  const busOpacity = status.dim ? 0.55 : 1; // driver offline/belum beroperasi → ikon bus diredupkan
  return mkIcon(
    `<div style="display:flex;flex-direction:column;align-items:center;cursor:pointer;font-family:'Plus Jakarta Sans',sans-serif;opacity:${busOpacity};">
      <div style="background:rgba(6,13,23,0.94);color:${color};padding:2px 9px;
        border-radius:100px;font-size:10px;font-weight:700;margin-bottom:4px;
        border:1px solid ${color}66;letter-spacing:0.04em;white-space:nowrap;
        box-shadow:0 3px 10px rgba(0,0,0,0.55);">${status.label}</div>
      <div style="width:38px;height:38px;border-radius:50%;display:flex;align-items:center;
        justify-content:center;flex-shrink:0;
        background:linear-gradient(145deg, ${color}33 0%, ${CLR.bgSolid} 130%);
        border:2.5px solid ${color};
        box-shadow:0 3px 10px rgba(0,0,0,0.6), 0 0 0 3px ${color}26;">
        <i class="fa-solid fa-bus" style="font-size:16px;color:${color};"></i>
      </div>
      <div style="margin-top:4px;background:rgba(6,13,23,0.94);color:${color};
        padding:2px 8px;font-size:10px;border-radius:100px;border:1px solid ${color}66;
        font-weight:700;white-space:nowrap;box-shadow:0 3px 10px rgba(0,0,0,0.5);">${driver.plat}</div>
    </div>`,
    30, 68, 15, 47
  );
}

// ═══════════════════════════════════════════════════════════════════════
//  CARD INFO DRIVER — mengambang di atas titik driver, ikut mengalir halus
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
      <div class="dc-plat" id="dc-plat">—</div>
      <div class="dc-row"><span class="dc-label">Driver</span><span class="dc-value" id="dc-driver">—</span></div>
      <div class="dc-row"><span class="dc-label">Trayek</span><span class="dc-value" id="dc-trayek">—</span></div>
      <div class="dc-row"><span class="dc-label">Terakhir Online</span>
        <span class="dc-value dc-status"><span class="dc-dot" id="dc-dot"></span><span id="dc-lastonline">—</span></span>
      </div>
    </div>
    <svg class="dc-arrow" viewBox="0 0 14 8" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path d="M0 0 L7 8 L14 0" fill="rgba(15,22,46,0.88)" stroke="rgba(255,255,255,0.08)" stroke-width="1" stroke-linejoin="round"/>
    </svg>`;
  document.body.appendChild(card);

  card.querySelector('.dc-close').addEventListener('click', closeDriverCard);
}

function fillDriverCard(driver, status) {
  document.getElementById('dc-plat').textContent   = driver.plat || '—';
  document.getElementById('dc-driver').textContent = driver.driver || '—';
  document.getElementById('dc-trayek').textContent = driver.trayek || '—';
  document.getElementById('dc-lastonline').textContent =
    status.key === 'online' ? 'Online sekarang' : `${status.label} · ${formatLastOnline(driver.update_at)}`;
  document.getElementById('dc-dot').style.background = status.color;
}

// Hitung ulang posisi card supaya persis di atas marker (pakai koordinat
// marker Leaflet asli, bukan posisi terakhir yang di-cache, jadi selalu akurat
// walau map digeser/di-zoom atau titik driver baru saja pindah).
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
  const top  = mapRect.top  + pt.y - ch - 44; // jarak ke atas marker (di atas ikon bus + status badge)

  if (!animate) card.style.transition = 'opacity .18s ease, transform .18s ease';
  card.style.left = `${left}px`;
  card.style.top  = `${top}px`;
  if (!animate) {
    void card.offsetWidth; // reflow
    card.style.transition = '';
  }
}

function openDriverCard(marker) {
  ensureDriverCard();
  fillDriverCard(marker.driverData, marker.status);
  openDriverMarker = marker;
  document.getElementById('driver-card').classList.add('visible');
  positionDriverCard(false); // muncul langsung tepat di atas marker, tanpa meluncur dari posisi lama
}

function closeDriverCard() {
  const card = document.getElementById('driver-card');
  if (card) card.classList.remove('visible');
  openDriverMarker = null;
}

function makeUserMarker() {
  return mkIcon(
    `<div style="display:flex;flex-direction:column;align-items:center;font-family:'Plus Jakarta Sans',sans-serif;">
      <div style="background:rgba(6,13,23,0.94);color:${CLR.user};padding:3px 9px;
        border-radius:100px;border:1px solid ${CLR.user}66;font-size:11px;
        font-weight:700;margin-bottom:4px;white-space:nowrap;
        box-shadow:0 3px 10px rgba(0,0,0,0.55);">Lokasi Anda</div>
      <div style="width:32px;height:32px;border-radius:50%;display:flex;align-items:center;
        justify-content:center;flex-shrink:0;
        background:linear-gradient(145deg, ${CLR.user}33 0%, ${CLR.bgSolid} 130%);
        border:2.5px solid ${CLR.user};
        box-shadow:0 3px 10px rgba(0,0,0,0.6), 0 0 0 3px ${CLR.user}26;">
        <i class="fa-solid fa-location-dot" style="font-size:14px;color:${CLR.user};"></i>
      </div>
    </div>`,
    30, 60, 15, 45
  );
}

// ── Leaflet custom control builder ───────────────────────────────────────────
function makeCtrl(position, html, extraStyle = '') {
  const ctrl = L.control({ position });
  ctrl.onAdd = () => {
    const div = L.DomUtil.create('div');
    div.style.cssText = [
      `background:${CLR.bg}`,
      'backdrop-filter:blur(16px)',
      '-webkit-backdrop-filter:blur(16px)',
      `border:1px solid ${CLR.mutedDim}`,
      'border-radius:10px',
      `color:${CLR.text}`,
      "font-family:'Plus Jakarta Sans',sans-serif",
      'font-size:13px',
      'font-weight:500',
      'padding:8px 14px',
      'line-height:1.4',
      extraStyle,
    ].join(';');
    div.innerHTML = html;
    return div;
  };
  return ctrl;
}

// ── Main ─────────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', async () => {
  const params = new URLSearchParams(window.location.search);
  const id_map = params.get('id_map');
  if (!id_map) { hideLoading(); return; }

  try {
    // ── 1. Titik-titik rute ───────────────────────────────────────────────
    const mapRows = await sbFetch('map', { [MAP_FILTER_COL]: id_map }, 'urutan.asc');
    if (!Array.isArray(mapRows) || mapRows.length === 0) {
      setTitle('Rute tidak ditemukan.');
      setStat('stat-halte', '0');
      hideLoading();
      return;
    }

    const filtered = mapRows.sort((a, b) => parseInt(a.urutan) - parseInt(b.urutan));
    const codeMap  = filtered[0].code_map;
    const trayek   = filtered[0].nama_trayek || id_map;

    // Update title & halte count secepatnya
    setTitle(trayek);
    setStat('stat-halte', filtered.length);

    // ── 2. Init Leaflet ───────────────────────────────────────────────────
    const map = L.map('map', {
      zoomControl: false,
      attributionControl: false,
    }).setView([parseFloat(filtered[0].lat), parseFloat(filtered[0].lng)], 13);
    window._leafletMap = map; // dipakai oleh showStopModal

    // Card info driver ikut mengalir saat peta digeser/di-zoom (instan, 1:1
    // dengan gerakan tangan/scroll), dan meluncur halus saat titik driver
    // sendiri yang berpindah (lihat positionDriverCard(true) di upsertDriverMarker).
    map.on('move zoom', () => positionDriverCard(false));
    map.on('click', () => closeDriverCard());

    // Tile layer Google Maps
    L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
      maxZoom: 19,
      subdomains: ['mt0'],
    }).addTo(map);

    // Garis batas Kota/Kabupaten Tulungagung — gaya sama dengan peta ASDP
    fetch('/assets/rute-sekolah/geo/tulungagung.geojson')
      .then(r => r.json())
      .then(data => {
        L.geoJSON(data, {
          style: { color: '#93c5fd', weight: 3, opacity: 1, fillColor: 'rgba(59,130,246,0.07)', fillOpacity: 1 },
        }).addTo(map);
      })
      .catch(() => console.warn('[rute_map] Batas wilayah (geojson) tidak tersedia.'));

    // Zoom control posisi kanan-tengah, di atas bottom card
    L.control.zoom({ position: 'bottomright' }).addTo(map);

    // ── 3. Marker titik rute ──────────────────────────────────────────────
    const waypoints = filtered.map(p => L.latLng(parseFloat(p.lat), parseFloat(p.lng)));

    filtered.forEach((point, i) => {
      const lat      = parseFloat(point.lat);
      const lng      = parseFloat(point.lng);
      const name     = point.nama_tempat || `Titik ${i + 1}`;
      const isFirst  = i === 0;
      const isLast   = i === filtered.length - 1;
      const isTerminal = isFirst || isLast;
      const color    = isFirst ? CLR.teal : isLast ? CLR.offline : CLR.muted;

      L.marker([lat, lng], { icon: makeStopMarker(name, color, isTerminal) })
        .addTo(map)
        .on('click', () => showStopModal(name, i, filtered.length, lat, lng));
    });

    // Tutup modal saat peta di-drag atau zoom
    map.on('movestart zoomstart', hideStopModal);

    // Tombol tutup modal
    document.getElementById('stop-modal-close').addEventListener('click', hideStopModal);

    map.fitBounds(L.latLngBounds(waypoints), { padding: [52, 52] });

    // Sembunyikan loading saat map mulai tampil
    map.once('tilesloaded', hideLoading);
    setTimeout(hideLoading, 3500); // fallback

    // ── 4. Routing via proxy → OSRM ──────────────────────────────────────
    try {
      const { geojson, distanceKm, durationMin } = await fetchRoute(waypoints, 'driving');

      L.geoJSON(geojson, {
        style: {
          color  : CLR.routeLine,
          weight : 4,
          opacity: 0.85,
        },
      }).addTo(map);

      // Glow layer di bawah
      L.geoJSON(geojson, {
        style: {
          color  : CLR.routeLine,
          weight : 10,
          opacity: 0.12,
        },
      }).addTo(map);

      // Update bottom card
      setStat('stat-jarak', `${distanceKm} km`);
      setStat('stat-durasi', `~${durationMin} mnt`);

    } catch (routeErr) {
      console.warn('[rute_map] Routing gagal, fallback ke garis lurus:', routeErr);

      L.polyline(waypoints, {
        color    : CLR.routeFall,
        weight   : 3,
        opacity  : 0.6,
        dashArray: '8, 6',
      }).addTo(map);

      // Warning badge di topright
      makeCtrl('topright',
        `<i class="fa-solid fa-triangle-exclamation" style="color:${CLR.warn};margin-right:6px;"></i>
         <span style="color:${CLR.warn};">Rute perkiraan</span>`,
        `border-color:${CLR.warn}33;margin-top:8px;margin-right:8px;`
      ).addTo(map);

      setStat('stat-jarak', '—');
      setStat('stat-durasi', '—');
    }

    // ── 5. Driver markers (REALTIME asli, bukan polling) ───────────────────
    let driverMarkers = new Map(); // "table-id" → L.Marker (biar update posisi mulus, tanpa flicker)
    const presentDriverIds = new Set(); // id driver yang presence-nya sedang aktif di kanal ini

    function upsertDriverMarker(driver, table) {
      const lat = parseFloat(driver.lat);
      const lng = parseFloat(driver.lng);
      if (!isFinite(lat) || !isFinite(lng)) {
        console.warn('[driver] Koordinat tidak valid:', driver);
        return;
      }
      const key = `${table}-${driver.id}`;
      const existing = driverMarkers.get(key);

      // PENTING: payload broadcast dari driver (bus.js) sengaja RINGAN — hanya
      // {id, table, plat, lat, lng, update_at}, TIDAK memuat driver/trayek.
      // Kalau langsung di-replace total, data driver/trayek yang sudah lengkap
      // dari fetch awal (REST, select=*) akan hilang tertimpa "—" tiap kali ada
      // broadcast baru. Jadi digabung (merge): field yang tidak ada di payload
      // baru tetap pakai data lama; field yang ada (lat/lng/update_at/plat)
      // selalu pakai yang terbaru.
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
        const marker = L.marker([lat, lng], { icon }).addTo(map);
        marker.driverData = merged;
        marker.status      = status;
        marker.on('click', (e) => { L.DomEvent.stopPropagation(e); openDriverCard(marker); });
        driverMarkers.set(key, marker);
      }
    }

    function removeDriverMarker(id, table) {
      const key = `${table}-${id}`;
      const existing = driverMarkers.get(key);
      if (existing) {
        map.removeLayer(existing);
        driverMarkers.delete(key);
      }
    }

    // Presence baru masuk/keluar TIDAK mengubah lat/lng — cukup hitung ulang
    // status & ikon tiap marker yang sudah ada, tanpa perlu fetch ulang ke DB.
    function refreshAllMarkerStatuses() {
      driverMarkers.forEach((marker) => {
        const status = DRIVER_STATUS.compute(presentDriverIds.has(String(marker.driverData.id)), marker.driverData.update_at);
        marker.status = status;
        marker.setIcon(makeDriverMarker(marker.driverData, status));
        if (openDriverMarker === marker) fillDriverCard(marker.driverData, status);
      });
    }

    // Jam berganti tanpa event apapun bisa mengubah status "Belum Beroperasi"
    // ↔ "Offline" pada jam batas (04:00/20:00) — cek ulang tiap menit supaya
    // tidak perlu menunggu event lain untuk memperbarui tampilan.
    setInterval(refreshAllMarkerStatuses, 60_000);

    // Muat posisi awal sekali via proxy (bukan polling — hanya saat halaman dibuka).
    // code_map bisa cocok di driver_bus ATAU driver_mpu (namespace-nya sudah
    // beda: "rute_..." untuk Bus, "rute_mpu_..." untuk MPU), jadi query dua-duanya.
    const loadInitialDrivers = async () => {
      try {
        const [busDrivers, mpuDrivers] = await Promise.all([
          sbFetch('driver_bus', { [DRV_FILTER_COL]: codeMap }),
          sbFetch('driver_mpu', { [DRV_FILTER_COL]: codeMap }),
        ]);
        const busList = Array.isArray(busDrivers) ? busDrivers : [];
        const mpuList = Array.isArray(mpuDrivers) ? mpuDrivers : [];
        if (busList.length === 0 && mpuList.length === 0) {
          console.warn('[driver] Tidak ada data driver untuk codeMap:', codeMap);
        }
        busList.forEach(d => upsertDriverMarker(d, 'driver_bus'));
        mpuList.forEach(d => upsertDriverMarker(d, 'driver_mpu'));
      } catch (e) {
        console.error('[rute_map] Gagal memuat posisi awal driver:', e);
      }
    };
    await loadInitialDrivers();

    // Dengarkan perubahan posisi secara realtime (native Supabase, WebSocket).
    // Setiap kali driver mengirim GPS baru, event ini langsung dipush ke sini
    // tanpa perlu polling berulang ke server.
    // Dengarkan posisi driver secara realtime (native Supabase WebSocket),
    // dua jalur sekaligus di kanal yang sama dengan yang dipakai driver:
    //  - broadcast 'pos'      → update halus tiap beberapa detik (tanpa DB)
    //  - postgres_changes     → sinkron ke DB tiap ~60 detik / saat driver
    //                            baru login (jaga-jaga kalau broadcast
    //                            terlewat, mis. driver ganti jaringan)
    if (sbRealtime) {
      const posChannel = sbRealtime.channel(`driver-pos-${codeMap}`);
      posChannel
        .on('broadcast', { event: 'pos' }, ({ payload }) => {
          upsertDriverMarker(payload, payload.table || 'driver_bus');
        })
        .on(
          'postgres_changes',
          { event: '*', schema: 'public', table: 'driver_bus', filter: `${DRV_FILTER_COL}=eq.${codeMap}` },
          (payload) => {
            if (payload.eventType === 'DELETE') removeDriverMarker(payload.old?.id, 'driver_bus');
            else upsertDriverMarker(payload.new, 'driver_bus');
          }
        )
        .on(
          'postgres_changes',
          { event: '*', schema: 'public', table: 'driver_mpu', filter: `${DRV_FILTER_COL}=eq.${codeMap}` },
          (payload) => {
            if (payload.eventType === 'DELETE') removeDriverMarker(payload.old?.id, 'driver_mpu');
            else upsertDriverMarker(payload.new, 'driver_mpu');
          }
        )
        // Presence: sinkron status koneksi tablet driver — jauh lebih instan
        // & akurat (hitungan detik) dibanding menebak dari selisih update_at.
        .on('presence', { event: 'sync' }, () => {
          presentDriverIds.clear();
          const state = posChannel.presenceState();
          Object.values(state).forEach(entries => {
            entries.forEach(e => { if (e.id) presentDriverIds.add(String(e.id)); });
          });
          refreshAllMarkerStatuses();
        })
        .subscribe();

      // Pastikan koneksi realtime ditutup rapi saat halaman ditinggalkan.
      window.addEventListener('pagehide', () => {
        sbRealtime.removeAllChannels();
      });

      // ── Jaring pengaman: re-sync REST berkala ──────────────────────────
      // Realtime (broadcast/postgres_changes) BISA sesekali terlewat (mis.
      // reconnect WebSocket, replication lag di sisi Supabase) — kalau itu
      // terjadi pas driver sedang diam (hanya heartbeat DB tiap 45 detik,
      // tidak broadcast), marker bisa macet di data lama sampai halaman
      // di-reload manual. Re-fetch penuh (select=*, termasuk driver/trayek)
      // tiap 45 detik ini menjamin data akhirnya tetap benar walau ada event
      // Realtime yang terlewat — dan karena proxy sudah cache query "live"
      // 5 detik + query lain permanen (lihat supabase_proxy.php), ini tetap
      // ringan meski dibuka banyak pengunjung sekaligus.
      const safetySync = setInterval(loadInitialDrivers, 45_000);
      window.addEventListener('pagehide', () => clearInterval(safetySync));
    } else {
      // Fallback: kalau Supabase JS gagal dimuat (mis. CDN diblokir),
      // tetap jalan dengan polling ringan supaya peta tidak mati total.
      console.warn('[rute_map] Realtime tidak tersedia, fallback ke polling 15s.');
      const pollInterval = setInterval(loadInitialDrivers, 15_000);
      window.addEventListener('pagehide', () => clearInterval(pollInterval));
    }

    // ── 6. Lokasi pengguna ────────────────────────────────────────────────
    let userMarker     = null;
    let lastUserLatLng = null;

    if (navigator.geolocation) {
      const updatePos = ({ coords: { latitude: lat, longitude: lng } }) => {
        lastUserLatLng = [lat, lng];
        if (userMarker) userMarker.setLatLng(lastUserLatLng);
        else userMarker = L.marker(lastUserLatLng, { icon: makeUserMarker() }).addTo(map);
      };
      navigator.geolocation.getCurrentPosition(updatePos, () => {});
      setInterval(() => navigator.geolocation.getCurrentPosition(updatePos, () => {}), 3_000);
    }

    // ── 6b. Tombol "Fokus ke Lokasi Anda" — mengambang tepat di atas panel
    //        Jalur/Jarak/Durasi, kamera terbang halus kembali ke posisi user. ──
    const recenterBtn = L.DomUtil.create('button');
    recenterBtn.innerHTML = `<i class="fa-solid fa-location-crosshairs" style="font-size:14px;"></i>`;
    recenterBtn.setAttribute('aria-label', 'Fokus ke lokasi Anda');
    Object.assign(recenterBtn.style, {
      position      : 'fixed',
      left          : '50%',
      transform     : 'translateX(-50%)',
      zIndex        : '1001',
      width         : '42px',
      height        : '42px',
      borderRadius  : '50%',
      background    : CLR.bg,
      color         : CLR.teal,
      border        : `1px solid ${CLR.tealBorder}`,
      cursor        : 'pointer',
      display       : 'flex',
      alignItems    : 'center',
      justifyContent: 'center',
      backdropFilter: 'blur(16px)',
      boxShadow     : '0 10px 28px rgba(0,0,0,0.45)',
      transition    : 'transform .15s ease, background .15s ease, bottom .2s ease',
    });
    recenterBtn.onmouseover = () => { recenterBtn.style.transform = 'translateX(-50%) scale(1.06)'; };
    recenterBtn.onmouseout  = () => { recenterBtn.style.transform = 'translateX(-50%) scale(1)'; };
    recenterBtn.onclick = () => {
      if (!lastUserLatLng) return;
      map.flyTo(lastUserLatLng, Math.max(map.getZoom(), 16), { duration: 0.8 });
    };
    document.body.appendChild(recenterBtn);

    // Posisikan tombol dinamis persis di atas #bottom-card (mengikuti tinggi
    // panel yang bisa berubah di layar sempit/mobile).
    function positionRecenterBtn() {
      const bottomCard = document.getElementById('bottom-card');
      if (!bottomCard) return;
      const rect = bottomCard.getBoundingClientRect();
      recenterBtn.style.bottom = `${window.innerHeight - rect.top + 14}px`;
    }
    positionRecenterBtn();
    window.addEventListener('resize', positionRecenterBtn);
    setTimeout(positionRecenterBtn, 300); // jaga-jaga kalau layout stat card berubah setelah data masuk


    // ── 7. Tombol kembali (selaras dengan design) ─────────────────────────
    const btn = L.DomUtil.create('button');
    btn.innerHTML = `<i class="fa-solid fa-arrow-left" style="font-size:13px;"></i>
                     <span>Kembali</span>`;
    btn.onclick = () => history.back();
    Object.assign(btn.style, {
      position      : 'fixed',
      bottom        : '24px',
      left          : '20px',
      zIndex        : '1001',
      background    : CLR.bg,
      color         : CLR.text,
      padding       : '9px 16px',
      borderRadius  : '20px',
      fontWeight    : '500',
      fontSize      : '13px',
      border        : `1px solid ${CLR.mutedDim}`,
      cursor        : 'pointer',
      display       : 'flex',
      alignItems    : 'center',
      gap           : '7px',
      backdropFilter: 'blur(16px)',
      letterSpacing : '0.01em',
      transition    : 'border-color 0.15s',
      fontFamily    : "'Plus Jakarta Sans', sans-serif",
    });
    btn.onmouseover = () => { btn.style.borderColor = CLR.tealBorder; btn.style.color = CLR.teal; };
    btn.onmouseout  = () => { btn.style.borderColor = CLR.mutedDim;   btn.style.color = CLR.text; };
    document.body.appendChild(btn);

  } catch (err) {
    console.error('[rute_map]', err);
    setTitle('Gagal memuat peta.');
    hideLoading();
  }
});