'use strict';

// ─── CONFIG ─────────────────────────────────────────────────
const DRIVER_ID     = window.DRIVER_ID;
const SB_URL        = window.SB_URL;
const SB_ANON       = window.SB_ANON;
const GPS_PROXY_URL = window.GPS_PROXY_URL || '/absenrfid/php/gps-write';
const STANDBY_MS    = 60_000;
const RESULT_MS     = 8_000;
const SYNC_MS       = 30_000;
const DB_NAME       = 'absen_bus_db';
const DB_VER        = 1;


let gps_table       = null; 
let gps_plat        = null;  
let gps_codeMap     = null; 
let gps_tracking    = false;
let gps_watchId     = null;
let gps_wakeLock    = null;
let gps_lastLat     = null;
let gps_lastLng     = null;
let gps_lastSentAt  = 0;    
let gps_lastSavedAt = 0;      
let gps_channel     = null;   
let gps_driverName  = null;   
let gps_heartbeatTimer = null; 
let gps_lastMoveAt     = Date.now(); 

const GPS_MIN_INTERVAL_MS  = 6000;

const GPS_MOVE_THRESHOLD_M = 15;

const GPS_SAVE_INTERVAL_MS = 60_000;

const GPS_HEARTBEAT_MS      = 45_000;

const GPS_IDLE_AFTER_MS     = 5 * 60_000;

const GPS_HEARTBEAT_IDLE_MS = 120_000;

const sbRealtime = (window.SB_URL && window.SB_ANON && window.supabase)
  ? window.supabase.createClient(window.SB_URL, window.SB_ANON)
  : null;

// ─── SUPABASE HELPERS ────────────────────────────────────────
const sbHdr = () => ({
  'Content-Type' : 'application/json',
  'apikey'       : SB_ANON,
  'Authorization': `Bearer ${SB_ANON}`,
});

async function sbFetchAll(table, filters = {}, select = '*') {
  const allRows = [];
  let from = 0;
  const pageSize = 1000;
  while (true) {
    const params = new URLSearchParams({ select, ...filters });
    const res = await fetch(`${SB_URL}/rest/v1/${table}?${params}`, {
      headers: { ...sbHdr(), 'Range': `${from}-${from + pageSize - 1}`, 'Range-Unit': 'items', 'Prefer': 'count=none' },
    });
    if (!res.ok) throw new Error(`Supabase ${table} error: ${res.status}`);
    const rows = await res.json();
    allRows.push(...rows);
    if (rows.length < pageSize) break;
    from += pageSize;
  }
  return allRows;
}

async function sbSelect(table, filters = {}, limit = 1, select = '*') {
  const params = new URLSearchParams({ select, limit, ...filters });
  const res = await fetch(`${SB_URL}/rest/v1/${table}?${params}`, { headers: sbHdr() });
  if (!res.ok) throw new Error(`Supabase ${table} ${res.status}`);
  return res.json();
}

// ─── STATE ──────────────────────────────────────────────────
let db           = null;
let standbyTimer = null;
let rfidBuffer   = '';
let rfidTimer    = null;
let isOnline     = navigator.onLine;

// ─── INIT ───────────────────────────────────────────────────
(async function init() {
  db = await openDB();
  await prefetchCache();
  startClock();
  focusRfidInput();
  setupRfidCapture();
  setupManualNikInput();
  setupNetworkWatch();
  setInterval(syncOfflineQueue, SYNC_MS);
  syncOfflineQueue();
  updateOfflineBadge();

  // Auto-start GPS tracking jika dibuka via link driver yang valid
  if (DRIVER_ID) {
    initGpsDriver();
  }
})();

// ─── IndexedDB ──────────────────────────────────────────────
function openDB() {
  return new Promise((resolve, reject) => {
    const req = indexedDB.open(DB_NAME, DB_VER);
    req.onupgradeneeded = e => {
      const d = e.target.result;
      if (!d.objectStoreNames.contains('users'))
        d.createObjectStore('users', { keyPath: 'nik' });
      if (!d.objectStoreNames.contains('absensi_queue'))
        d.createObjectStore('absensi_queue', { keyPath: 'qid', autoIncrement: true });
      if (!d.objectStoreNames.contains('absensi_today'))
        d.createObjectStore('absensi_today', { keyPath: 'unikKey' });
      if (!d.objectStoreNames.contains('meta'))
        d.createObjectStore('meta', { keyPath: 'key' });
    };
    req.onsuccess = e => resolve(e.target.result);
    req.onerror   = () => reject(req.error);
  });
}

function dbGet(store, key) {
  return new Promise((res, rej) => {
    const tx = db.transaction(store, 'readonly');
    const r  = tx.objectStore(store).get(key);
    r.onsuccess = () => res(r.result);
    r.onerror   = () => rej(r.error);
  });
}

function dbPut(store, value) {
  return new Promise((res, rej) => {
    const tx = db.transaction(store, 'readwrite');
    const r  = tx.objectStore(store).put(value);
    r.onsuccess = () => res(r.result);
    r.onerror   = () => rej(r.error);
  });
}

function dbDelete(store, key) {
  return new Promise((res, rej) => {
    const tx = db.transaction(store, 'readwrite');
    const r  = tx.objectStore(store).delete(key);
    r.onsuccess = () => res();
    r.onerror   = () => rej(r.error);
  });
}

function dbGetAll(store) {
  return new Promise((res, rej) => {
    const tx = db.transaction(store, 'readonly');
    const r  = tx.objectStore(store).getAll();
    r.onsuccess = () => res(r.result);
    r.onerror   = () => rej(r.error);
  });
}

// ─── PREFETCH CACHE ─────────────────────────────────────────
async function prefetchCache() {
  if (!navigator.onLine) return;
  try {
    // Kiosk hanya pernah membaca kolom nik/nama/jenis_kelamin/domisili/sekolah
    // dari data siswa (lihat processNik/showResult) - kolom lain (mis. foto,
    // rfid, created_at, dll) tidak pernah dipakai, jadi tidak perlu ikut
    // diunduh tiap kali kiosk boot/reconnect. Ini bisa jadi ribuan baris
    // siswa se-kabupaten, jadi pembatasan kolom ini lumayan terasa di egress.
    const items = await sbFetchAll('user_RFID', {}, 'nik,nama,jenis_kelamin,domisili,sekolah');
    const tx = db.transaction('users', 'readwrite');
    const store = tx.objectStore('users');
    store.clear();
    items.forEach(u => { if (u.nik) store.put(u); });
    await dbPut('meta', { key: 'lastCacheAt', value: Date.now() });
    await cleanTodayCache();
  } catch (err) {
    console.warn('[cache] prefetch gagal:', err);
  }
}

async function cleanTodayCache() {
  const all = await dbGetAll('absensi_today');
  const today = todayStr();
  const tx = db.transaction('absensi_today', 'readwrite');
  const store = tx.objectStore('absensi_today');
  all.forEach(r => { if (!r.unikKey.includes(today)) store.delete(r.unikKey); });
}

// ─── CLOCK ──────────────────────────────────────────────────
function startClock() {
  function tick() {
    const now = new Date();
    document.getElementById('clock').textContent =
      now.toLocaleTimeString('id-ID', { hour12: false });
    document.getElementById('date-label').textContent =
      now.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
  }
  tick();
  setInterval(tick, 1000);
}

function focusRfidInput() {
  const inp    = document.getElementById('rfid-input');
  const manual = document.getElementById('manual-nik-input');
  inp.focus();
  document.addEventListener('click', e => {
    // Jangan rebut fokus jika pengguna sedang klik ke kolom manual NIK
    if (manual && (e.target === manual || manual.contains(e.target))) return;
    inp.focus();
  });
  document.addEventListener('visibilitychange', () => {
    if (!document.hidden && document.activeElement !== manual) inp.focus();
  });
}

// ─── MANUAL NIK INPUT ───────────────────────────────────────
function setupManualNikInput() {
  const inp      = document.getElementById('manual-nik-input');
  const counter  = document.getElementById('nik-char-count');
  const feedback = document.getElementById('nik-feedback');
  let   nikTimer = null;

  if (!inp) return;

  // Saat field mendapat fokus, hentikan rfid capture sementara
  inp.addEventListener('focus', () => {
    clearTimeout(rfidTimer);
  });

  // Saat field kehilangan fokus, kembalikan fokus ke rfid input (setelah delay kecil)
  inp.addEventListener('blur', () => {
    setTimeout(() => {
      const active = document.activeElement;
      if (active !== inp) focusRfidInput();
    }, 150);
  });

  inp.addEventListener('input', () => {
    // Hanya izinkan angka
    const raw     = inp.value.replace(/\D/g, '').slice(0, 16);
    inp.value     = raw;
    const len     = raw.length;

    // Update counter
    counter.textContent = `${len} / 16`;
    counter.classList.toggle('full', len === 16);

    // Reset state visual
    inp.classList.remove('nik-valid', 'nik-error');
    clearTimeout(nikTimer);

    if (len === 0) {
      setFeedback('', '');
      return;
    }

    if (len < 16) {
      setFeedback(`${16 - len} digit lagi`, 'info');
      return;
    }

    // Tepat 16 digit → proses otomatis
    inp.classList.add('nik-valid');
    setFeedback('Mencari data...', 'searching');

    nikTimer = setTimeout(async () => {
      try {
        await processManualNik(raw, inp, feedback);
      } catch (err) {
        inp.classList.remove('nik-valid');
        inp.classList.add('nik-error');
        setFeedback('Terjadi kesalahan. Coba lagi.', 'error');
      }
    }, 300);
  });

  // Cegah karakter non-angka
  inp.addEventListener('keydown', e => {
    if (e.key === 'Enter') { e.preventDefault(); return; }
    // Izinkan: backspace, delete, tab, arrow, ctrl+a/c/v
    const allowed = ['Backspace','Delete','Tab','ArrowLeft','ArrowRight','Home','End'];
    if (allowed.includes(e.key)) return;
    if (e.ctrlKey || e.metaKey) return;
    if (!/^\d$/.test(e.key)) e.preventDefault();
  });

  function setFeedback(msg, type) {
    feedback.textContent = msg;
    feedback.className   = 'nik-feedback';
    if (msg) {
      feedback.classList.add('show');
      if (type) feedback.classList.add(type);
    }
  }
}

async function processManualNik(nik, inp, feedback) {
  function setFeedback(msg, type) {
    feedback.textContent = msg;
    feedback.className   = 'nik-feedback';
    if (msg) {
      feedback.classList.add('show');
      if (type) feedback.classList.add(type);
    }
  }

  // Cek cache lokal dulu
  let user = await dbGet('users', nik);

  // Fallback Supabase
  if (!user && navigator.onLine) {
    try {
      const rows = await sbSelect('user_RFID', { nik: `eq.${nik}` }, 1, 'nik,nama,jenis_kelamin,domisili,sekolah');
      if (rows.length > 0) {
        user = rows[0];
        if (user.nik) await dbPut('users', user);
      }
    } catch (_) {}
  }

  if (!user) {
    // NIK tidak ditemukan
    inp.classList.remove('nik-valid');
    inp.classList.add('nik-error');
    setFeedback('✕  NIK tidak terdaftar / data siswa belum ada', 'error');

    // Kosongkan field setelah 2.5 detik agar bisa coba lagi
    setTimeout(() => {
      inp.value = '';
      inp.classList.remove('nik-error');
      document.getElementById('nik-char-count').textContent = '0 / 16';
      document.getElementById('nik-char-count').classList.remove('full');
      setFeedback('', '');
    }, 2500);
    return;
  }

  // Ditemukan - bersihkan field lalu jalankan proses absen normal
  inp.value = '';
  inp.classList.remove('nik-valid', 'nik-error');
  document.getElementById('nik-char-count').textContent = '0 / 16';
  document.getElementById('nik-char-count').classList.remove('full');
  setFeedback('', '');

  // Kembalikan fokus ke rfid input agar tap kartu tetap berfungsi
  document.getElementById('rfid-input').focus();

  // Proses absen menggunakan alur yang sama dengan tap kartu
  processNik(nik);
}

// ─── RFID CAPTURE ───────────────────────────────────────────
function setupRfidCapture() {
  const inp = document.getElementById('rfid-input');
  inp.addEventListener('keydown', e => {
    if (e.key === 'Enter') {
      e.preventDefault();
      const nik = rfidBuffer.trim();
      rfidBuffer = '';
      clearTimeout(rfidTimer);
      if (nik.length >= 8) processNik(nik);
      inp.value = '';
    }
  });
  inp.addEventListener('input', () => {
    rfidBuffer = inp.value;
    clearTimeout(rfidTimer);
    rfidTimer = setTimeout(() => {
      const nik = rfidBuffer.trim();
      rfidBuffer = '';
      inp.value = '';
      if (nik.length >= 8) processNik(nik);
    }, 200);
  });
}

// ─── PROCESS NIK ────────────────────────────────────────────
async function processNik(nik) {
  clearTimeout(standbyTimer);
  try {
    // 1. Cache lokal
    let user = await dbGet('users', nik);

    // 2. Fallback ke Supabase (FIXED: dulu pakai PocketBase)
    if (!user && navigator.onLine) {
      const rows = await sbSelect('user_RFID', { nik: `eq.${nik}` }, 1, 'nik,nama,jenis_kelamin,domisili,sekolah');
      if (rows.length > 0) {
        user = rows[0];
        if (user.nik) await dbPut('users', user);
      }
    }

    if (!user) { showResult({ type: 'unknown', nik }); return; }

    const driverInfo = await getDriverInfo();

    const jam     = new Date().getHours();
    const inPagi  = jam >= 4 && jam < 11;
    const inSiang = jam >= 11 && jam < 20;
    if (!inPagi && !inSiang) {
      showResult({ type: 'error', user, message: 'Di luar jam absen' });
      return;
    }
    const shift = inPagi ? 'pagi' : 'siang';

    const today    = todayStr();
    const unikKey  = `${nik}_${today}_${shift}`;
    const existing = await dbGet('absensi_today', unikKey);
    if (existing) { showResult({ type: 'duplicate', user, shift }); return; }

    const record = {
      nik,
      nama          : user.nama,
      jenis_kelamin : user.jenis_kelamin || '',
      domisili      : user.domisili || '',
      sekolah       : user.sekolah || '',
      transportasi  : driverInfo.jenis,
      trayek        : driverInfo.trayek || '',
      plat_driver   : driverInfo.platDriver || '',
      waktu         : new Date().toISOString(),
      shift,
      unikKey,
    };

    if (navigator.onLine) {
      await saveAbsensiOnline(record);
    } else {
      await enqueueAbsensi(record);
    }

    await dbPut('absensi_today', { unikKey, waktu: record.waktu });
    showResult({ type: 'success', user, shift, record });

  } catch (err) {
    console.error('[processNik]', err);
    showResult({ type: 'error', message: err.message });
  }
}

// ─── DRIVER INFO (FIXED: Supabase) ──────────────────────────
async function getDriverInfo() {
  if (!DRIVER_ID) return { jenis: '-', trayek: '-', platDriver: '-' };

  // Cek cache IndexedDB hanya jika db sudah siap
  if (db) {
    try {
      const cached = await dbGet('meta', 'driverInfo');
      if (cached) return cached.value;
    } catch (_) {}
  }

  if (navigator.onLine) {
    for (const col of ['driver_bus', 'driver_mpu']) {
      try {
        const rows = await sbSelect(col, { id: `eq.${DRIVER_ID}` }, 1, 'trayek,plat,driver');
        if (rows.length > 0) {
          const d    = rows[0];
          const info = {
            jenis      : col === 'driver_bus' ? 'BUS' : 'MPU',
            trayek     : d.trayek || '',
            platDriver : `${d.plat} - ${d.driver}`,
          };
          if (db) {
            try { await dbPut('meta', { key: 'driverInfo', value: info }); } catch (_) {}
          }
          return info;
        }
      } catch (_) {}
    }
  }
  return { jenis: '-', trayek: '-', platDriver: '-' };
}

// ─── SAVE / QUEUE ────────────────────────────────────────────
async function saveAbsensiOnline(record) {
  const res = await fetch('/absenrfid/php/cek-rfid', {
    method     : 'POST',
    headers    : { 'Content-Type': 'application/json' },
    credentials: 'same-origin',
    body       : JSON.stringify({ payload: record }),
  });

  // Pastikan response adalah JSON - jika bukan, lempar error dengan body mentah
  const text = await res.text();
  let json;
  try {
    json = JSON.parse(text);
  } catch (_) {
    throw new Error('Server error (bukan JSON): ' + text.slice(0, 120));
  }

  if (!res.ok || json.status !== 'ok') {
    if (json.status === 'duplicate') return;
    throw new Error(json.message || 'Gagal simpan absensi');
  }
}

async function enqueueAbsensi(record) {
  await dbPut('absensi_queue', { ...record, qid: undefined });
  console.info('[offline] antrian:', record.unikKey);
}

async function syncOfflineQueue() {
  if (!navigator.onLine) return;
  const queue = await dbGetAll('absensi_queue');
  for (const item of queue) {
    try {
      await saveAbsensiOnline(item);
      await dbDelete('absensi_queue', item.qid);
    } catch (err) {
      console.warn('[sync] gagal:', item.qid, err.message);
    }
  }
}

// ─── NETWORK WATCH ──────────────────────────────────────────
function setupNetworkWatch() {
  function update() {
    isOnline = navigator.onLine;
    updateOfflineBadge();
    if (isOnline) { prefetchCache(); syncOfflineQueue(); }
  }
  window.addEventListener('online',  update);
  window.addEventListener('offline', update);
}

function updateOfflineBadge() {
  const badge = document.getElementById('offline-badge');
  if (!badge) return;
  badge.classList.toggle('hidden', navigator.onLine);
}

// ─── SHOW RESULT ─────────────────────────────────────────────
function showResult({ type, user, shift, record, message, nik }) {
  const el = id => document.getElementById(id);
  const nama = user?.nama || 'Kartu Tidak Dikenal';

  el('result-name').textContent = nama;

  el('result-meta').textContent = [user?.sekolah, user?.domisili].filter(Boolean).join(' · ') || (nik ? `NIK: ${nik}` : '');
  el('result-shift').textContent = shift === 'pagi' ? 'SHIFT PAGI' : shift === 'siang' ? 'SHIFT SIANG' : '';
  el('result-time').textContent  = new Date().toLocaleTimeString('id-ID', { hour12: false });

  const statusBox = el('result-status');
  statusBox.className = 'result-status';
  const icon = el('status-icon'), text = el('status-text');
  if (type === 'success')   { statusBox.classList.add('success');   icon.textContent = '✓'; text.textContent = 'Absen Berhasil'; }
  else if (type==='duplicate'){statusBox.classList.add('duplicate');icon.textContent = '⚠'; text.textContent = 'Sudah Absen Sesi Ini'; }
  else if (type === 'error') { statusBox.classList.add('error');    icon.textContent = '✕'; text.textContent = message || 'Gagal Absen'; }
  else                       { statusBox.classList.add('unknown');  icon.textContent = '?'; text.textContent = 'Kartu Tidak Terdaftar'; }

  switchScreen('screen-result');

  const bar = el('progress-bar');
  bar.style.transition = 'none';
  bar.style.transform  = 'scaleX(1)';
  requestAnimationFrame(() => requestAnimationFrame(() => {
    bar.style.transition = `transform ${RESULT_MS}ms linear`;
    bar.style.transform  = 'scaleX(0)';
  }));

  clearTimeout(standbyTimer);
  standbyTimer = setTimeout(() => {
    switchScreen('screen-standby');
    // Reset kolom manual NIK
    const manualInp = document.getElementById('manual-nik-input');
    if (manualInp) { manualInp.value = ''; manualInp.classList.remove('nik-valid','nik-error'); }
    const counter = document.getElementById('nik-char-count');
    if (counter) { counter.textContent = '0 / 16'; counter.classList.remove('full'); }
    const fb = document.getElementById('nik-feedback');
    if (fb) { fb.textContent = ''; fb.className = 'nik-feedback'; }
    document.getElementById('rfid-input').focus();
  }, RESULT_MS);
}

function switchScreen(targetId) {
  document.querySelectorAll('.screen').forEach(s => s.classList.remove('active'));
  document.getElementById(targetId).classList.add('active');
}

function todayStr() {
  const d = new Date();
  return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
}

// ── VEHICLE INDICATOR ───────────────────────────────────────
function updateVehicleIndicator(plat, driver) {
  const el = document.getElementById('vehicle-indicator');
  const elPlat = document.getElementById('vehicle-plat');
  const elDriver = document.getElementById('vehicle-driver');
  if (!el || !elPlat || !elDriver) return;

  elPlat.textContent   = plat   || '-';
  elDriver.textContent = driver || '-';
  el.classList.remove('hidden');
}

function normalizePlat(plat) {
  return (plat || '').replace(/[^a-zA-Z0-9]/g, '').toLowerCase();
}

function gpsDistanceMeters(lat1, lng1, lat2, lng2) {
  const R = 6371e3;
  const toRad = d => d * (Math.PI / 180);
  const dLat = toRad(lat2 - lat1);
  const dLng = toRad(lng2 - lng1);
  const a = Math.sin(dLat / 2) ** 2 +
            Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) * Math.sin(dLng / 2) ** 2;
  return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
}

function gpsUpdateBadge(text, type) {
  let badge = document.getElementById('gps-driver-badge');
  if (!badge) {
    badge = document.createElement('div');
    badge.id = 'gps-driver-badge';
    badge.style.cssText = [
      'position:fixed', 'bottom:12px', 'right:14px', 'z-index:999',
      'font-family:Montserrat,sans-serif', 'font-size:11px', 'font-weight:600',
      'padding:4px 10px', 'border-radius:20px', 'letter-spacing:.4px',
      'transition:all .4s ease', 'pointer-events:none',
      'box-shadow:0 2px 8px rgba(0,0,0,.25)',
    ].join(';');
    document.body.appendChild(badge);
  }
  badge.textContent = text;
  if (type === 'active') {
    badge.style.background = '#16a34a';
    badge.style.color = '#fff';
  } else if (type === 'error') {
    badge.style.background = '#dc2626';
    badge.style.color = '#fff';
  } else {
    badge.style.background = 'rgba(0,0,0,.45)';
    badge.style.color = 'rgba(255,255,255,.7)';
  }
}

function gpsBroadcastLocation(lat, lng) {
  if (!gps_channel) return;
  gps_channel.send({
    type    : 'broadcast',
    event   : 'pos',
    payload : { id: DRIVER_ID, table: gps_table, plat: gps_plat, lat, lng, update_at: new Date().toISOString() },
  });
}

async function gpsSaveLocation(lat, lng) {
  if (!gps_table || !DRIVER_ID) return;
  if (!navigator.onLine) return; // pasti gagal - jangan coba kirim, tunggu online lagi
  try {
    const res = await fetch(
      `${GPS_PROXY_URL}?action=save&table=${encodeURIComponent(gps_table)}&id=${encodeURIComponent(DRIVER_ID)}`,
      {
        method : 'POST',
        headers: { 'Content-Type': 'application/json' },
        body   : JSON.stringify({ lat, lng }),
      }
    );
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    gpsUpdateBadge(`📡 GPS Aktif`, 'active');
  } catch (err) {
    console.warn('[GPS] gagal simpan lokasi:', err.message);
    gpsUpdateBadge('📡 GPS - gagal kirim', 'error');
  }
}

function gpsScheduleHeartbeat() {
  const idleFor = Date.now() - gps_lastMoveAt;
  const delay = idleFor >= GPS_IDLE_AFTER_MS ? GPS_HEARTBEAT_IDLE_MS : GPS_HEARTBEAT_MS;

  gps_heartbeatTimer = setTimeout(() => {
    if (gps_lastLat !== null && gps_lastLng !== null) {
      gpsSaveLocation(gps_lastLat, gps_lastLng);
      gps_lastSavedAt = Date.now();
    }
    gpsScheduleHeartbeat();
  }, delay);
}

async function gpsStartTracking() {
  if (gps_tracking || !gps_table) return;
  if (!navigator.geolocation) {
    gpsUpdateBadge('📡 GPS tidak didukung', 'error');
    return;
  }
  gps_tracking = true;
  gpsUpdateBadge('📡 GPS Aktif', 'active');

  if (sbRealtime && gps_codeMap) {
    gps_channel = sbRealtime.channel(`driver-pos-${gps_codeMap}`, {
      config: { presence: { key: DRIVER_ID } },
    });
    gps_channel.subscribe(async (status) => {
      if (status === 'SUBSCRIBED') {
        try {
          await gps_channel.track({
            id       : DRIVER_ID,
            table    : gps_table,
            plat     : gps_plat,
            driver   : gps_driverName,
            online_at: new Date().toISOString(),
          });
        } catch (err) {
          console.warn('[GPS] gagal track presence:', err.message);
        }
      }
    });
  }


  try {
    if ('wakeLock' in navigator) {
      gps_wakeLock = await navigator.wakeLock.request('screen');
    }
  } catch (_) {}

  gpsScheduleHeartbeat();

  window.addEventListener('pagehide', () => {
    clearTimeout(gps_heartbeatTimer);
    if (gps_lastLat !== null && gps_lastLng !== null && gps_table && DRIVER_ID) {
      navigator.sendBeacon?.(
        `${GPS_PROXY_URL}?action=save&table=${encodeURIComponent(gps_table)}&id=${encodeURIComponent(DRIVER_ID)}`,
        new Blob([JSON.stringify({ lat: gps_lastLat, lng: gps_lastLng })], { type: 'application/json' })
      );
    }
    if (gps_channel) { try { gps_channel.untrack(); } catch (_) {} }
    if (sbRealtime) sbRealtime.removeAllChannels();
  });

  gps_watchId = navigator.geolocation.watchPosition(
    pos => {
      const { latitude: lat, longitude: lng } = pos.coords;
      const now = Date.now();

      if (gps_lastLat !== null && gps_lastLng !== null) {
        const dist = gpsDistanceMeters(gps_lastLat, gps_lastLng, lat, lng);
        if (dist < GPS_MOVE_THRESHOLD_M) {

          gps_lastLat = lat;
          gps_lastLng = lng;
          return;
        }
        if (now - gps_lastSentAt < GPS_MIN_INTERVAL_MS) {
          gps_lastLat = lat;
          gps_lastLng = lng;
          return;
        }
      }
      gps_lastLat    = lat;
      gps_lastLng    = lng;
      gps_lastSentAt = now;
      gps_lastMoveAt = now; 
      if (gps_channel) {
        gpsBroadcastLocation(lat, lng);
      } else {

        if (now - gps_lastSavedAt >= GPS_SAVE_INTERVAL_MS) {
          gps_lastSavedAt = now;
          gpsSaveLocation(lat, lng);
        }
      }
    },
    err => {
      console.warn('[GPS] error geolocation:', err.message);
      gpsUpdateBadge('📡 GPS - izin ditolak', 'error');
    },
    { enableHighAccuracy: true, maximumAge: 0, timeout: 15000 }
  );
}


async function initGpsDriver() {
  gpsUpdateBadge('📡 GPS - memuat...', '');

  try {

    let platNorm   = '';
    let rawPlat    = '';
    let namaDriver = '';
    let table      = null;

    for (const col of ['driver_bus', 'driver_mpu']) {
      try {
        const rows = await sbSelect(col, { id: `eq.${DRIVER_ID}` }, 1, 'trayek,plat,driver');
        if (rows.length > 0 && rows[0].plat) {
          rawPlat    = rows[0].plat;
          namaDriver = rows[0].driver || '';
          platNorm   = normalizePlat(rawPlat);
          table      = col;
          console.log(`[GPS] plat dari ${col}:`, rawPlat, '→', platNorm);
          break;
        }
      } catch (e) {
        console.warn(`[GPS] gagal query ${col}:`, e.message);
      }
    }

    if (!table) {
      gpsUpdateBadge('📡 GPS - plat tidak ditemukan', 'error');
      console.warn('[GPS] plat kosong - DRIVER_ID:', DRIVER_ID);
      return;
    }


    updateVehicleIndicator(rawPlat, namaDriver);

    gps_table      = table;
    gps_plat       = rawPlat;
    gps_driverName = namaDriver;

    const initRes = await fetch(
      `${GPS_PROXY_URL}?action=init&table=${encodeURIComponent(gps_table)}&id=${encodeURIComponent(DRIVER_ID)}`
    );

    if (!initRes.ok) {
      const errData = await initRes.json().catch(() => ({}));
      console.warn('[GPS] init gagal:', errData.error || initRes.status);
      gpsUpdateBadge('📡 GPS - gagal memuat kanal', 'error');
      return;
    }

    const initData = await initRes.json();
    gps_codeMap = initData.code_map || null;
    console.log('[GPS] init ok | table:', gps_table, '| code_map:', gps_codeMap);

    if (!gps_codeMap) {

      console.warn('[GPS] code_map kosong - lengkapi Trayek & Data Map driver ini agar muncul di peta publik.');
    }

    await gpsStartTracking();

  } catch (err) {
    console.error('[GPS] initGpsDriver error:', err);
    gpsUpdateBadge('📡 GPS - error', 'error');
  }
}
