<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"/>
<meta name="csrf-token" content="{{ csrf_token() }}"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Data Absensi — Admin Angkutan Sekolah</title>
<link rel="canonical" href="{{ url('/admin/data-absensi') }}"/>
<link rel="icon" href="/favicon.ico"/>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/admin/admin-shell.css">
<script src="https://cdn.jsdelivr.net/npm/exceljs@4.4.0/dist/exceljs.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>

<style>
  /* ── PAGE-SPECIFIC STYLES ── */
  .filter-card  { padding: 20px 24px; margin-bottom: 20px; }
  .filter-grid  { display: flex; flex-wrap: wrap; gap: 10px; align-items: flex-end; }

  .autocomplete-container { position: relative; }
  .filter-section { display: none; }
  .filter-section.show { display: block; }

  .stats-row { display: flex; align-items: center; gap: 10px; margin-bottom: 18px; flex-wrap: wrap; }

  /* Avatar */
  .avatar { height: 30px; width: 30px; border-radius: 50%; object-fit: cover; border: 2px solid var(--border); }
  .avatar-placeholder {
    width: 30px; height: 30px; border-radius: 50%;
    background: var(--border); display: flex; align-items: center;
    justify-content: center; font-size: 13px; color: var(--text-4); flex-shrink: 0;
  }

  /* Action row: tombol selalu 1 baris, tidak wrap */
  .filter-actions {
    display: flex;
    flex-wrap: nowrap;
    gap: 8px;
    align-items: center;
    padding-top: 12px;
    border-top: 1px solid var(--border);
    margin-top: 4px;
  }
  .filter-actions .btn { flex-shrink: 0; white-space: nowrap; }

  @media (max-width: 640px) {
    .form-control { min-width: 0 !important; width: 100%; }
    .filter-grid  { flex-direction: column; }
    .stats-row    { gap: 8px; }
    .filter-actions .btn { flex: 1; justify-content: center; font-size: 13px; padding: 9px 10px; }
  }

  /* ── Tabel: hanya ~10 baris terlihat, sisanya discroll ── */
  #absensiTableWrapper {
    max-height: 480px;     /* fallback, disesuaikan JS ke tinggi header + 10 baris */
    overflow-y: auto;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
  }
  /* Header tabel tetap terlihat saat discroll ke bawah */
  #absensiTableWrapper thead th {
    position: sticky;
    top: 0;
    z-index: 3;
    background: var(--navy);
  }

  /* Baris kiri-tabel-kanan: tombol geser kiri/kanan diletakkan DI LUAR
     kotak yang di-scroll, jadi tidak pernah bertumpuk dengan scrollbar
     bawaan browser di tepi tabel. */
  .table-nav-row { display: flex; align-items: stretch; gap: 8px; }
  .table-nav-row #absensiTableWrapper { flex: 1 1 auto; min-width: 0; }

  /* Baris tombol scroll-ke-bawah: diletakkan DI BAWAH tabel (di luar
     area yang di-scroll), jadi tidak bertumpuk dengan scrollbar horizontal. */
  .table-down-row { display: flex; justify-content: center; margin-top: 10px; }

  /* Tombol navigasi scroll — selalu terlihat, posisinya statis di luar
     kotak scroll (bukan mengambang di atasnya) */
  .scroll-nav-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    flex-shrink: 0;
    align-self: center;
    border-radius: 50%;
    border: 1.5px solid var(--border);
    background: var(--surface);
    color: var(--text-3);
    box-shadow: 0 2px 10px rgba(15,23,42,0.14);
    cursor: pointer;
    transition: all var(--tr);
  }
  .scroll-nav-btn:hover  { background: var(--accent); border-color: var(--accent); color: #fff; }
  .scroll-nav-btn:active { transform: scale(0.92); }
  .scroll-down-btn.at-bottom svg { transform: rotate(180deg); }

  .scroll-hint {
    text-align: center;
    font-size: 11.5px;
    color: var(--text-4);
    font-style: italic;
    margin: 8px 0 0;
  }

  @media (max-width: 640px) {
    #absensiTableWrapper { max-height: 440px; }
    .scroll-nav-btn { width: 30px; height: 30px; }
    .table-nav-row { gap: 5px; }
  }
</style>
</head>
<body>

<div class="adm-shell">

  @include('admin.partials.sidebar', ['currentPage' => 'data_absensi', 'currentModule' => 'angkutansekolah'])

  <main class="adm-main">
    <div class="adm-content">

      <!-- Page Header -->
      <div class="adm-page-header">
        <div>
          <h1 class="adm-page-title">Data Absensi</h1>
          <p class="adm-page-subtitle">Angkutan Sekolah — Dinas Perhubungan Kab. Tulungagung</p>
        </div>
      </div>

      <!-- Stats Row -->
      <div class="stats-row">
        <div class="stat-chip">
          <span class="label">Total Data</span>
          <span class="value" id="totalCount">—</span>
        </div>
      </div>

      <!-- Filter Card -->
      @if ($isGuest)
      <!-- ── MODE TAMU: filter terbatas, data hari ini saja ── -->
      <div class="card filter-card">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:14px;padding:9px 13px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:9px;">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          <span style="font-size:12.5px;color:#1d4ed8;font-weight:500;">Mode Tamu — menampilkan data absensi hari ini</span>
          <a href="/admin/logout" style="margin-left:auto;font-size:12px;color:#64748b;text-decoration:none;display:flex;align-items:center;gap:4px;">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            Login
          </a>
        </div>
        <div class="filter-grid">
          <form autocomplete="off" style="margin:0;">
            <div class="autocomplete-container">
              <input type="text" id="filterNama" placeholder="Cari Nama/NIK Siswa"
                list="namaDropdown" class="form-control" style="min-width:200px;"/>
              <datalist id="namaDropdown"></datalist>
            </div>
          </form>

          <select id="filterTransportasi" class="form-control" style="min-width:150px;">
            <option value="">Semua Transportasi</option>
            <option value="BUS">BUS</option>
            <option value="MPU">MPU</option>
          </select>

          <select id="filterAbsen" class="form-control" style="min-width:150px;">
            <option value="">Pagi dan Siang</option>
            <option value="pagi">Absen Pagi</option>
            <option value="siang">Absen Siang</option>
          </select>
        </div><!-- /.filter-grid -->
        <div class="filter-actions">
          <button id="btnFilter" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
            Terapkan Filter
          </button>
        </div><!-- /.filter-actions -->
      </div>
      @else
      <!-- ── MODE ADMIN: semua filter tersedia ── -->
      <div class="card filter-card">
        <div class="filter-grid">
          <select id="filterTanggal" class="form-control" style="min-width:160px;">
            <option value="">Semua Tanggal</option>
            <option value="today">Hari Ini</option>
            <option value="week">Mingguan</option>
            <option value="month">Bulanan</option>
          </select>

          <input type="text" id="datePicker" placeholder="Pilih tanggal / bulan"
            class="form-control hidden" style="min-width:200px;" readonly/>

          <form autocomplete="off" style="margin:0;">
            <div class="autocomplete-container">
              <input type="text" id="filterNama" placeholder="Cari Nama/NIK Siswa"
                list="namaDropdown" class="form-control" style="min-width:200px;"/>
              <datalist id="namaDropdown"></datalist>
            </div>
          </form>

          <select id="filterTransportasi" class="form-control" style="min-width:150px;">
            <option value="">Semua Transportasi</option>
            <option value="BUS">BUS</option>
            <option value="MPU">MPU</option>
          </select>

          <div class="autocomplete-container filter-section" id="trayekContainer" style="min-width:160px;">
            <select id="filterTrayek" class="form-control" style="min-width:160px;">
              <option value="">Semua Trayek</option>
            </select>
          </div>

          <div class="autocomplete-container filter-section" id="driverContainer" style="min-width:160px;">
            <select id="filterDriver" class="form-control" style="min-width:160px;">
              <option value="">Pilih Plat / Driver</option>
            </select>
          </div>

          <select id="filterAbsen" class="form-control" style="min-width:150px;">
            <option value="">Pagi dan Siang</option>
            <option value="pagi">Absen Pagi</option>
            <option value="siang">Absen Siang</option>
          </select>

        </div><!-- /.filter-grid -->
        <div class="filter-actions">
          <button id="btnFilter" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
            Terapkan Filter
          </button>
          <button id="btnDownload" class="btn btn-success">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Excel
          </button>
        </div><!-- /.filter-actions -->
      </div>
      @endif

      <!-- Table Card -->
      <div class="table-scroll-outer">
        <div class="table-nav-row">
          <button type="button" class="scroll-nav-btn scroll-left-btn" id="scrollLeftBtn" title="Geser ke kiri" aria-label="Geser tabel ke kiri">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
          </button>

          <div class="card table-wrapper" id="absensiTableWrapper">
            <table>
              <thead>
                <tr>
                  <th>Nama</th>
                  @if (!$isGuest)
                  <th>NIK/Email</th>
                  @endif
                  <th>Gender</th>
                  <th>Domisili</th>
                  <th>Sekolah</th>
                  <th>Transportasi</th>
                  <th>Trayek</th>
                  <th>Driver</th>
                  <th>Waktu</th>
                  <th>Sesi</th>
                  <th>Sumber</th>
                </tr>
              </thead>
              <tbody id="reportBody"></tbody>
            </table>
          </div>

          <button type="button" class="scroll-nav-btn scroll-right-btn" id="scrollRightBtn" title="Geser ke kanan" aria-label="Geser tabel ke kanan">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
          </button>
        </div>

        <div class="table-down-row">
          <button type="button" class="scroll-nav-btn scroll-down-btn" id="scrollDownBtn" title="Scroll ke bawah" aria-label="Scroll tabel ke bawah">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
          </button>
        </div>
      </div>
      <p class="scroll-hint">Menampilkan 10 baris per tampilan — geser tabel untuk melihat data selengkapnya</p>

      <div id="pagination"></div>


    </div><!-- /.adm-content -->
  </main><!-- /.adm-main -->
</div><!-- /.adm-shell -->


<script>
// ══ SUPABASE HELPER ══
const SB_URL   = {!! json_encode(config('services.supabase.url')) !!};
const SB_ANON  = {!! json_encode(config('services.supabase.anon_key')) !!};
const IS_GUEST = {{ $isGuest ? 'true' : 'false' }};
const sbHdr = () => ({ 'apikey': SB_ANON, 'Authorization': `Bearer ${SB_ANON}` });
// Kolom NIK/Email disembunyikan di Mode Tamu, jadi total kolom tabel
// jadi satu lebih sedikit — dipakai untuk colspan placeholder loading/kosong/error.
const COLSPAN = IS_GUEST ? 10 : 11;
</script>
<script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>
<script>
const sbRealtime = window.supabase ? window.supabase.createClient(SB_URL, SB_ANON) : null;


async function sbGetAll(table, filters = {}, select = '*') {
  const all = []; let from = 0; const PAGE = 1000;
  while (true) {
    const params = new URLSearchParams({ select, ...filters });
    const res = await fetch(`${SB_URL}/rest/v1/${table}?${params}`, {
      headers: { ...sbHdr(), 'Range': `${from}-${from+PAGE-1}`, 'Range-Unit': 'items', 'Prefer': 'count=none' }
    });
    if (!res.ok) throw new Error(`sbGetAll(${table}) HTTP ${res.status}`);
    const rows = await res.json(); all.push(...rows);
    if (rows.length < PAGE) break;
    from += PAGE;
  }
  return all;
}

let driverByTrayek = {}, fpInstance = null;
const PAGE_SIZE = 50;
let currentPage = 1, totalCount = 0, activeFilters = {};

function showToast(type = 'info', message = '') {
  const old = document.getElementById('toast-box');
  if (old) old.remove();
  const div = document.createElement('div');
  div.id = 'toast-box';
  div.className = `toast-box toast-${type}`;
  div.textContent = message;
  document.body.appendChild(div);
  setTimeout(() => { div.style.opacity = '0'; div.style.transform = 'translateX(20px)'; div.style.transition = 'all 0.3s'; setTimeout(() => div.remove(), 300); }, 3000);
}

function setFilterEnabled(enabled) {
  // Catatan: 'filterNama' SENGAJA tidak ikut dikunci di sini. Kolom cari
  // nama/NIK sekarang mencari otomatis tiap huruf diketik (live search),
  // jadi kalau ikut di-disable saat loading, user akan kehilangan fokus/
  // tidak bisa lanjut mengetik setiap kali hasil sedang dimuat.
  const ids = IS_GUEST
    ? ['filterTransportasi','filterAbsen','btnFilter']
    : ['filterTransportasi','filterTrayek','filterDriver','filterTanggal','datePicker','filterAbsen','btnFilter','btnDownload'];
  ids.forEach(id => {
    const el = document.getElementById(id); if (el) el.disabled = !enabled;
  });
  // Filter sesi (Pagi/Siang) butuh rentang tanggal yang jelas — kalau admin
  // sedang pilih "Semua Tanggal", filter sesi tetap dikunci nonaktif meski
  // filter lain baru selesai loading & diaktifkan kembali.
  if (!IS_GUEST && enabled && filterTanggal && filterTanggal.value === '') {
    const filterAbsenEl = document.getElementById('filterAbsen');
    if (filterAbsenEl) filterAbsenEl.disabled = true;
  }
}

async function loadDropdownData() {
  // Mode Tamu tidak punya filter Trayek/Driver di UI-nya sama sekali,
  // jadi tidak perlu tarik seluruh isi driver_bus/driver_mpu — hemat egress.
  if (IS_GUEST) return;
  try {
    const [allBus, allMpu] = await Promise.all([
      sbGetAll('driver_bus'), sbGetAll('driver_mpu'),
    ]);
    populateTrayekDropdown('', allBus, allMpu);
    window._allBus = allBus; window._allMpu = allMpu;
  } catch (e) { console.error('loadDropdownData gagal:', e); }
}

// Autocomplete nama siswa — TIDAK mengunduh semua nama siswa sekaligus
// (bisa puluhan ribu baris), cukup cari ke server tiap kali mengetik
// (di-debounce), hasil dibatasi 8 nama saja untuk saran datalist.
let _namaAcTimer = null;
document.querySelectorAll('#filterNama').forEach(input => {
  input.addEventListener('input', (e) => {
    clearTimeout(_namaAcTimer);
    const q = e.target.value.trim();
    if (q.length < 2) return; // tunggu minimal 2 huruf
    _namaAcTimer = setTimeout(async () => {
      try {
        const rows = await fetch(
          `${SB_URL}/rest/v1/user_RFID?select=nama&nama=ilike.*${encodeURIComponent(q)}*&limit=8`,
          { headers: sbHdr() }
        ).then(r => r.json());
        const names = [...new Set((rows || []).map(r => r.nama).filter(Boolean))];
        document.querySelectorAll('#namaDropdown').forEach(dl => {
          dl.innerHTML = names.map(n => `<option value="${n}">`).join('');
        });
      } catch (_) { /* autocomplete opsional, tidak fatal kalau gagal */ }
    }, 300);
  });
});

// Live search kolom "Cari Nama/NIK Siswa" — begitu Tamu/Admin mengetik,
// data laporan otomatis diperbarui sendiri per huruf, tanpa perlu klik
// "Terapkan Filter". Di-debounce sedikit (350ms) supaya tidak menembak
// request ke server di setiap ketukan tombol saat mengetik cepat.
let _liveSearchTimer = null;
document.querySelectorAll('#filterNama').forEach(input => {
  input.addEventListener('input', () => {
    clearTimeout(_liveSearchTimer);
    _liveSearchTimer = setTimeout(() => { loadReport(); }, 350);
  });
});

function populateTrayekDropdown(transportasi, allBus, allMpu) {
  const trayekSelect = document.getElementById('filterTrayek');
  if (!trayekSelect) return;  // tidak ada di mode tamu
  allBus = allBus || window._allBus || [];
  allMpu = allMpu || window._allMpu || [];
  trayekSelect.innerHTML = `<option value="">Semua Trayek</option>`;
  driverByTrayek = {};
  let data = transportasi === 'bus' ? allBus : transportasi === 'mpu' ? allMpu : [...allBus, ...allMpu];
  const trayekSet = new Set();
  data.forEach(i => {
    if (i.trayek) {
      trayekSet.add(i.trayek);
      if (!driverByTrayek[i.trayek]) driverByTrayek[i.trayek] = [];
      const drv = i.driver || i.nama_driver || i.plat;
      if (drv && !driverByTrayek[i.trayek].includes(drv)) driverByTrayek[i.trayek].push(drv);
    }
  });
  Array.from(trayekSet).sort().forEach(tr => {
    const opt = document.createElement('option'); opt.value = tr; opt.textContent = tr;
    trayekSelect.appendChild(opt);
  });
}

if (!IS_GUEST) {
  document.getElementById('filterTransportasi').addEventListener('change', e => {
    const val = e.target.value.trim().toLowerCase();
    const tc = document.getElementById('trayekContainer');
    const dc = document.getElementById('driverContainer');
    document.getElementById('filterDriver').value = '';
    dc.classList.remove('show');
    if (val) { tc.classList.add('show'); populateTrayekDropdown(val); }
    else     { tc.classList.remove('show'); document.getElementById('filterTrayek').value = ''; }
  });
}

function setupTrayekDriverLogic() {
  if (IS_GUEST) return;
  const dc = document.getElementById('driverContainer');
  const ds = document.getElementById('filterDriver');
  const ts = document.getElementById('filterTrayek');
  ts.addEventListener('change', () => {
    const t = ts.value;
    ds.innerHTML = `<option value="">Pilih Plat/Driver</option>`;
    if (!t) { dc.classList.remove('show'); return; }
    const drvList = driverByTrayek[t] || [];
    if (drvList.length) {
      dc.classList.add('show');
      drvList.forEach(d => { const o = document.createElement('option'); o.value = d; o.textContent = d; ds.appendChild(o); });
      if (drvList.length === 1) ds.value = drvList[0];
    } else dc.classList.remove('show');
  });
}

const filterTanggal = document.getElementById('filterTanggal');
const datePicker    = document.getElementById('datePicker');

if (filterTanggal) filterTanggal.addEventListener('change', () => {
  const now = new Date();
  if (fpInstance) { fpInstance.destroy(); fpInstance = null; }
  datePicker.value = ''; datePicker.placeholder = 'Pilih tanggal / bulan';
  datePicker.classList.add('hidden'); datePicker.removeAttribute('readonly');

  switch (filterTanggal.value) {
    case 'today':
      datePicker.classList.remove('hidden');
      datePicker.value = now.toISOString().split('T')[0];
      datePicker.setAttribute('readonly', true);
      break;
    case 'week':
      datePicker.classList.remove('hidden');
      datePicker.placeholder = 'Pilih rentang tanggal (max 7 hari)';
      datePicker.setAttribute('readonly', true);
      const startDefault = new Date(); startDefault.setDate(now.getDate() - 6);
      fpInstance = flatpickr(datePicker, {
        mode: 'range', dateFormat: 'Y-m-d',
        defaultDate: [startDefault, now], allowInput: false, clickOpens: true,
        onChange: dates => {
          if (dates.length === 2 && ((dates[1] - dates[0]) / 86400000 + 1) > 7) {
            fpInstance.clear(); alert('Maksimal 7 hari!');
          }
        },
      });
      break;
    case 'month':
      datePicker.classList.remove('hidden');
      datePicker.placeholder = 'Pilih bulan & tahun';
      datePicker.setAttribute('readonly', true);
      fpInstance = flatpickr(datePicker, {
        plugins: [new monthSelectPlugin({ shorthand: true, dateFormat: 'Y-m', altFormat: 'F Y' })],
        dateFormat: 'Y-m', defaultDate: now, allowInput: false, clickOpens: true,
      });
      break;
    default:
      datePicker.classList.add('hidden');
      datePicker.setAttribute('readonly', true);
  }

  // Filter sesi (Pagi/Siang) hanya valid kalau ada rentang tanggal yang jelas.
  const filterAbsenEl = document.getElementById('filterAbsen');
  if (filterAbsenEl) {
    if (filterTanggal.value === '') {
      filterAbsenEl.value = '';
      filterAbsenEl.disabled = true;
      filterAbsenEl.title = 'Pilih rentang tanggal dulu untuk memakai filter sesi';
    } else {
      filterAbsenEl.disabled = false;
      filterAbsenEl.title = '';
    }
  }
}); // end filterTanggal.addEventListener

// Format tanggal YYYY-MM-DD dari objek Date lokal (dipakai untuk kirim
// rentang minggu/bulan ke admin/api/angkutansekolah/absensi_report.php).
function ymd(d) {
  return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
}

// Susun parameter query untuk admin/api/angkutansekolah/absensi_report.php dari kondisi
// filter UI saat ini. Tidak ada lagi query PostgREST langsung dari
// browser — server yang menentukan sumber data (cache lokal untuk hari
// sebelumnya, live untuk hari ini) berdasarkan tanggal yang diminta di sini.
function buildFilters() {
  const p = {};
  const search = document.getElementById('filterNama').value.trim();
  const transportasi = document.getElementById('filterTransportasi').value;
  const absenFilter  = document.getElementById('filterAbsen').value;
  if (transportasi) p.transportasi = transportasi.toUpperCase();
  if (search)        p.search = search;

  if (IS_GUEST) {
    p.tanggal = 'today';
    if (absenFilter) p.absen = absenFilter;
    return p;
  }

  const trayek = document.getElementById('filterTrayek').value;
  const driver = document.getElementById('filterDriver').value;
  const tVal   = filterTanggal ? filterTanggal.value : '';

  if (trayek) p.trayek = trayek;
  if (driver) p.driver = driver;

  p.tanggal = tVal; // '' (semua tanggal) | 'today' | 'week' | 'month'
  if (tVal === 'week' && fpInstance?.selectedDates.length === 2) {
    const [d1, d2] = fpInstance.selectedDates;
    p.range_start = ymd(d1);
    p.range_end   = ymd(d2);
  } else if (tVal === 'month' && fpInstance?.selectedDates.length === 1) {
    const d = fpInstance.selectedDates[0];
    p.range_month = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`;
  }

  // Filter sesi (Pagi/Siang) butuh rentang tanggal yang jelas (UI sudah
  // mengunci filter ini nonaktif kalau tVal === '', lihat setupTrayekDriverLogic
  // & listener filterTanggal di atas).
  if (absenFilter && tVal) p.absen = absenFilter;

  return p;
}

// ── Semua pengambilan data laporan sekarang lewat admin/api/angkutansekolah/absensi_report.php
// (bukan langsung ke Supabase dari browser lagi). Endpoint itu yang
// menggabungkan absensi_RFID + absensi_QRCode, menerapkan filter, dan
// menentukan per-tanggal apakah datanya diambil live (hari ini) atau dari
// cache lokal server (hari-hari sebelumnya).
async function callAbsensiApi(params) {
  const qs = new URLSearchParams(params);
  const res = await fetch(`/admin/api/angkutansekolah/absensi-report?${qs.toString()}`);
  if (!res.ok) {
    let msg = `HTTP ${res.status}`;
    try { const j = await res.json(); if (j && j.error) msg = j.error; } catch (_) {}
    throw new Error(msg);
  }
  return res.json();
}

async function fetchPage(filters, page) {
  const { rows, total } = await callAbsensiApi({ ...filters, action: 'page', page, pageSize: PAGE_SIZE });
  return { rows, total };
}

async function fetchAllForExport(filters) {
  const { rows } = await callAbsensiApi({ ...filters, action: 'export' });
  return rows;
}

function sourceBadge(source) {
  if (source === 'absensi_QRCode') {
    return `<span class="source-badge source-qr" title="Data migrasi — absen via QR Code (tabel absensi_QRCode)">
      <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><line x1="14" y1="14" x2="14" y2="21"/><line x1="21" y1="14" x2="21" y2="21"/><line x1="17.5" y1="14" x2="17.5" y2="17.5"/><line x1="14" y1="17.5" x2="21" y2="17.5"/></svg>
      QR Code
    </span>`;
  }
  return `<span class="source-badge source-rfid" title="Absen via kartu RFID (tabel absensi_RFID)">
    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="2" y="6" width="20" height="12" rx="2"/><path d="M6 6V4a2 2 0 012-2h8a2 2 0 012 2v2"/><line x1="6" y1="12" x2="6" y2="12.01"/></svg>
    RFID
  </span>`;
}

function renderRows(rows) {
  const tbody = document.getElementById('reportBody');
  const wrap = document.getElementById('absensiTableWrapper');
  if (wrap) wrap.scrollTop = 0;
  if (!rows.length) {
    tbody.innerHTML = `<tr><td colspan="${COLSPAN}" style="padding:36px;text-align:center;color:var(--text-4);font-style:italic;">Tidak ada data ditemukan</td></tr>`;
    fitTableWrapperHeight();
    return;
  }
  tbody.innerHTML = rows.map(item => {
    const dt = new Date(item.waktu);
    const wibMs = dt.getTime() + 7 * 3600000;
    const dtWib = new Date(wibMs);
    const waktuStr = dtWib.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', timeZone: 'UTC' }) + ' — ' + dtWib.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', timeZone: 'UTC' });
    const h = dtWib.getUTCHours();
    let shift = 'Lainnya', cls = 'shift-other';
    if (h >= 4 && h < 11)  { shift = 'Pagi';  cls = 'shift-pagi'; }
    else if (h >= 11 && h <= 19) { shift = 'Siang'; cls = 'shift-siang'; }
    return `<tr>
      <td><div style="display:flex;align-items:center;gap:9px;">
        <div class="avatar-placeholder">👤</div>
        <span style="font-weight:500;color:var(--text-1);">${item.nama || '—'}</span>
      </div></td>
      ${IS_GUEST ? '' : `<td style="font-family:'DM Mono',monospace;font-size:11.5px;">${item.nik || item.email || '—'}</td>`}
      <td>${item.jenis_kelamin || '—'}</td>
      <td>${item.domisili || '—'}</td>
      <td>${item.sekolah || '—'}</td>
      <td>${item.transportasi || '—'}</td>
      <td>${item.trayek || '—'}</td>
      <td>${item.plat_driver || '—'}</td>
      <td style="font-family:'DM Mono',monospace;font-size:11.5px;white-space:nowrap;">${waktuStr}</td>
      <td><span class="shift-badge ${cls}">${shift}</span></td>
      <td>${sourceBadge(item._source)}</td>
    </tr>`;
  }).join('');
  fitTableWrapperHeight();
}

// ── Tabel Data Absensi: batasi tinggi agar hanya ~10 baris terlihat,
//    sisanya bisa discroll ke bawah. Tinggi dihitung dari header +
//    tinggi 10 baris nyata (bukan angka tebakan), jadi tetap presisi
//    walau ukuran font/padding berubah di layar berbeda.
function fitTableWrapperHeight() {
  const wrap = document.getElementById('absensiTableWrapper');
  if (!wrap) return;
  const rows = wrap.querySelectorAll('tbody#reportBody tr');
  // Lewati saat baris tunggal placeholder (loading / kosong / error)
  if (rows.length <= 1 && rows[0] && rows[0].querySelector('td[colspan]')) return;
  requestAnimationFrame(() => {
    const thead = wrap.querySelector('thead');
    const rowsToShow = Math.min(rows.length, 10);
    let height = thead ? thead.getBoundingClientRect().height : 0;
    for (let i = 0; i < rowsToShow; i++) height += rows[i].getBoundingClientRect().height;
    if (height > 0) wrap.style.maxHeight = Math.ceil(height) + 'px';
    updateScrollDownIcon();
  });
}

function updateScrollDownIcon() {
  const wrap = document.getElementById('absensiTableWrapper');
  const btn  = document.getElementById('scrollDownBtn');
  if (!wrap || !btn) return;
  const canScroll = wrap.scrollHeight > wrap.clientHeight + 2;
  const atBottom  = wrap.scrollTop + wrap.clientHeight >= wrap.scrollHeight - 2;
  btn.style.display = canScroll ? 'flex' : 'none';
  btn.classList.toggle('at-bottom', atBottom);
  btn.title = atBottom ? 'Kembali ke atas' : 'Scroll ke bawah';
}

(function setupTableScrollNav() {
  const wrap  = document.getElementById('absensiTableWrapper');
  const btnDown  = document.getElementById('scrollDownBtn');
  const btnLeft  = document.getElementById('scrollLeftBtn');
  const btnRight = document.getElementById('scrollRightBtn');
  if (!wrap) return;

  btnDown?.addEventListener('click', () => {
    const atBottom = wrap.scrollTop + wrap.clientHeight >= wrap.scrollHeight - 2;
    wrap.scrollTo({ top: atBottom ? 0 : wrap.scrollTop + wrap.clientHeight * 0.85, behavior: 'smooth' });
  });
  btnLeft?.addEventListener('click',  () => wrap.scrollBy({ left: -180, behavior: 'smooth' }));
  btnRight?.addEventListener('click', () => wrap.scrollBy({ left: 180, behavior: 'smooth' }));

  wrap.addEventListener('scroll', updateScrollDownIcon, { passive: true });
  window.addEventListener('resize', () => fitTableWrapperHeight());
})();

function renderPagination(page) {
  const totalPages = Math.ceil(totalCount / PAGE_SIZE);
  const el = document.getElementById('pagination');
  el.innerHTML = '';
  if (totalPages <= 1) return;
  const mk = (label, pg, disabled = false, active = false) => {
    const btn = document.createElement('button');
    btn.innerHTML = label; btn.disabled = disabled;
    if (active) btn.classList.add('active-page');
    if (disabled) btn.style.opacity = '0.35';
    btn.onclick = () => goToPage(pg);
    el.appendChild(btn);
  };
  mk('←', page - 1, page === 1);
  for (let i = 1; i <= totalPages; i++) {
    if (i === 1 || i === totalPages || (i >= page - 2 && i <= page + 2)) mk(i, i, false, i === page);
    else if (i === page - 3 || i === page + 3) { const s = document.createElement('span'); s.textContent = '…'; s.style.cssText = 'padding:0 6px;color:var(--text-4);'; el.appendChild(s); }
  }
  mk('→', page + 1, page === totalPages);
}

async function goToPage(page) {
  currentPage = page;
  const tbody = document.getElementById('reportBody');
  tbody.innerHTML = `<tr><td colspan="${COLSPAN}" style="padding:36px;text-align:center;color:var(--text-4);">
    <div style="display:inline-flex;align-items:center;gap:12px;">
      <div class="spinner"></div> Memuat halaman ${page}…
    </div></td></tr>`;
  setFilterEnabled(false);
  try {
    const { rows, total } = await fetchPage(activeFilters, page);
    totalCount = total; renderRows(rows); renderPagination(page);
    document.getElementById('totalCount').textContent = total.toLocaleString('id-ID');
    window.scrollTo({ top: document.getElementById('reportBody').getBoundingClientRect().top + window.scrollY - 80, behavior: 'smooth' });
  } catch (e) {
    tbody.innerHTML = `<tr><td colspan="${COLSPAN}" style="padding:24px;text-align:center;color:#ef4444;">Gagal: ${e.message}</td></tr>`;
  } finally { setFilterEnabled(true); }
}

// Penanda urutan permintaan — dipakai supaya kalau live search menembak
// beberapa loadReport() beruntun (mengetik cepat), hanya respons dari
// permintaan TERAKHIR yang boleh menimpa tabel. Respons lama yang datang
// belakangan (out-of-order karena jaringan) otomatis diabaikan.
let _loadReportSeq = 0;

async function loadReport() {
  const seq = ++_loadReportSeq;
  activeFilters = buildFilters(); currentPage = 1;
  const tbody = document.getElementById('reportBody');
  tbody.innerHTML = `<tr><td colspan="${COLSPAN}" style="padding:48px;text-align:center;color:var(--text-4);">
    <div style="display:flex;flex-direction:column;align-items:center;gap:14px;">
      <div class="spinner"></div>
      <span style="font-style:italic;">Memuat data...</span>
    </div></td></tr>`;
  document.getElementById('totalCount').textContent = '—';
  document.getElementById('pagination').innerHTML = '';
  setFilterEnabled(false);
  try {
    const { rows, total } = await fetchPage(activeFilters, 1);
    if (seq !== _loadReportSeq) return; // ada request lebih baru menyusul, abaikan hasil ini
    totalCount = total; renderRows(rows); renderPagination(1);
    document.getElementById('totalCount').textContent = total.toLocaleString('id-ID');
    showToast('success', `${total.toLocaleString('id-ID')} data ditemukan`);
  } catch (e) {
    if (seq !== _loadReportSeq) return;
    tbody.innerHTML = `<tr><td colspan="${COLSPAN}" style="padding:28px;text-align:center;color:#ef4444;">Gagal memuat: ${e.message}</td></tr>`;
    showToast('error', 'Gagal memuat data');
  } finally { if (seq === _loadReportSeq) setFilterEnabled(true); }
}

// ── Label periode yang sedang aktif, dipakai di baris info Kop laporan ──
function periodeLabel(filters) {
  if (!filters.tanggal) return 'Semua Tanggal';
  if (filters.tanggal === 'today') return 'Hari Ini (' + new Date().toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' }) + ')';
  if (filters.tanggal === 'week' && filters.range_start && filters.range_end) {
    const f = d => new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
    return `${f(filters.range_start)} — ${f(filters.range_end)}`;
  }
  if (filters.tanggal === 'month' && filters.range_month) {
    const [y, m] = filters.range_month.split('-').map(Number);
    return new Date(y, m - 1, 1).toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
  }
  return 'Semua Tanggal';
}

async function downloadExcel() {
  showToast('info', 'Menyiapkan export…');
  try {
    let rows = await fetchAllForExport(activeFilters);
    if (!rows.length) { showToast('info', 'Tidak ada data'); return; }

    // Urutan hasil export dibalik dari urutan tabel (terbaru→lama) menjadi
    // kronologis (lama→baru), lebih wajar untuk sebuah laporan cetak.
    rows = rows.slice().reverse();

    // ── Susun baris data siap-cetak + hitung ringkasan gender ──
    let countL = 0, countP = 0;
    const dataRows = rows.map((item, idx) => {
      const wibMs = new Date(item.waktu).getTime() + 7 * 3600000; // → WIB
      const dtWib = new Date(wibMs);
      const tgl = `${String(dtWib.getUTCDate()).padStart(2,'0')}-${String(dtWib.getUTCMonth()+1).padStart(2,'0')}-${dtWib.getUTCFullYear()}`;
      const jam = `${String(dtWib.getUTCHours()).padStart(2,'0')}:${String(dtWib.getUTCMinutes()).padStart(2,'0')}`;
      const h = dtWib.getUTCHours();
      const sesi = h >= 4 && h < 11 ? 'Pagi' : h >= 11 && h <= 19 ? 'Siang' : 'Lainnya';
      let plat = '', drv = '';
      if (item.plat_driver) { const p = item.plat_driver.split('-').map(x => x.trim()).filter(Boolean); plat = p[0] || ''; drv = p.slice(1).join(' - '); }
      const jk = item.jenis_kelamin || '';
      if (jk === 'Laki-laki') countL++; else if (jk === 'Perempuan') countP++;
      const row = [idx + 1, tgl, jam, sesi, item.nama || '—'];
      if (!IS_GUEST) row.push(item.nik || item.email || '—');
      row.push(jk || '—', item.domisili || '—', item.sekolah || '—', item.transportasi || '—', item.trayek || '—', plat || '—', drv || '—', item._source === 'absensi_QRCode' ? 'QR Code' : 'RFID');
      return row;
    });
    const totalAbsen = rows.length;

    // ── Ambil logo Dishub untuk Kop (dipasang sebagai gambar di sel kiri atas) ──
    let logoBuffer = null;
    try {
      const logoRes = await fetch('/assets/dishub.png');
      if (logoRes.ok) logoBuffer = await logoRes.arrayBuffer();
    } catch (_) { /* kalau logo gagal diambil, laporan tetap dibuat tanpa gambar */ }

    // ── Definisi kolom tabel ──
    const headers = ['No', 'Tanggal', 'Jam', 'Sesi', 'Nama'];
    const widths  = [5, 12, 8, 9, 22];
    if (!IS_GUEST) { headers.push('NIK / Email'); widths.push(17); }
    headers.push('Jenis Kelamin', 'Domisili', 'Sekolah', 'Transportasi', 'Trayek', 'Plat Nomor', 'Driver', 'Sumber');
    widths.push(13, 16, 24, 13, 14, 12, 16, 10);
    const N = headers.length;

    const NAVY   = 'FF0B1120';
    const ACCENT = 'FF3B82F6';
    const LIGHT  = 'FFEFF6FF';
    const BAND   = 'FFF8FAFC';
    const BORDER = 'FFCBD5E1';
    const thinBorder = { style: 'thin', color: { argb: BORDER } };

    const wb = new ExcelJS.Workbook();
    wb.creator = 'Dinas Perhubungan Kabupaten Tulungagung';
    wb.created = new Date();
    const ws = wb.addWorksheet('Laporan Absensi', {
      pageSetup: { orientation: 'landscape', fitToPage: true, fitToWidth: 1, fitToHeight: 0, margins: { left: 0.4, right: 0.4, top: 0.5, bottom: 0.5, header: 0.2, footer: 0.2 } },
      views: [{ showGridLines: false }],
    });
    ws.columns = widths.map(w => ({ width: w }));

    let r = 1;

    // ══ KOP LAPORAN — logo di kiri, identitas instansi di kanan (dipadatkan) ══
    ws.mergeCells(1, 1, 3, 2);
    if (logoBuffer) {
      const imgId = wb.addImage({ buffer: logoBuffer, extension: 'png' });
      ws.addImage(imgId, { tl: { col: 0.35, row: 0.12 }, ext: { width: 52, height: 52 } });
    }
    const kopLine = (row, text, opts) => {
      ws.mergeCells(row, 3, row, N);
      const c = ws.getCell(row, 3);
      c.value = text;
      c.font = Object.assign({ name: 'Calibri', color: { argb: NAVY } }, opts);
      c.alignment = { vertical: 'middle', horizontal: 'left' };
    };
    kopLine(1, 'PEMERINTAH KABUPATEN TULUNGAGUNG', { size: 9.5, color: { argb: 'FF64748B' } });
    kopLine(2, 'DINAS PERHUBUNGAN', { size: 15, bold: true });
    kopLine(3, 'LAPORAN DATA ABSENSI ANGKUTAN SEKOLAH', { size: 11, bold: true, color: { argb: ACCENT } });
    for (let i = 1; i <= 3; i++) ws.getRow(i).height = 17;
    r = 4;

    // Garis pemisah Kop
    ws.mergeCells(r, 1, r, N);
    ws.getRow(r).height = 4;
    for (let c = 1; c <= N; c++) ws.getCell(r, c).border = { bottom: { style: 'medium', color: { argb: ACCENT } } };
    r++;
    ws.getRow(r).height = 6;
    r++;

    // Info periode & waktu cetak — satu sel saja (tidak di-merge lebar), teks
    // meluber alami ke sel kosong di sebelahnya, jadi baris tetap ringkas.
    ws.getCell(r, 1).value = 'Periode : ' + periodeLabel(activeFilters);
    ws.getCell(r, 1).font = { size: 9.5, color: { argb: 'FF334155' } };
    const cPrint = ws.getCell(r, N);
    cPrint.value = 'Dicetak : ' + new Date().toLocaleString('id-ID', { day: '2-digit', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' }) + ' WIB';
    cPrint.font = { size: 9.5, color: { argb: 'FF334155' } };
    cPrint.alignment = { horizontal: 'right' };
    ws.getRow(r).height = 14;
    r += 2;

    // ══ RINGKASAN — 3 kartu total ══
    const per = Math.floor(N / 3);
    const spans = [[1, per], [per + 1, per * 2], [per * 2 + 1, N]];
    const cards = [
      { label: 'TOTAL SISWA PEREMPUAN', value: countP, fill: 'FFFCE7F3', text: 'FFBE185D' },
      { label: 'TOTAL SISWA LAKI-LAKI', value: countL, fill: 'FFDBEAFE', text: 'FF1D4ED8' },
      { label: 'TOTAL SISWA YANG ABSEN', value: totalAbsen, fill: 'FFE0F2E9', text: 'FF15803D' },
    ];
    const labelRow = r, valueRow = r + 1;
    cards.forEach((card, i) => {
      const [c1, c2] = spans[i];
      ws.mergeCells(labelRow, c1, labelRow, c2);
      ws.mergeCells(valueRow, c1, valueRow, c2);
      const lc = ws.getCell(labelRow, c1);
      lc.value = card.label;
      lc.font = { size: 9, bold: true, color: { argb: card.text } };
      lc.alignment = { horizontal: 'center', vertical: 'middle' };
      const vc = ws.getCell(valueRow, c1);
      vc.value = card.value;
      vc.font = { size: 17, bold: true, color: { argb: card.text } };
      vc.alignment = { horizontal: 'center', vertical: 'middle' };
      for (let row = labelRow; row <= valueRow; row++) {
        for (let c = c1; c <= c2; c++) {
          const cell = ws.getCell(row, c);
          cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: card.fill } };
          cell.border = {
            top: row === labelRow ? { style: 'thin', color: { argb: card.text } } : undefined,
            bottom: row === valueRow ? { style: 'thin', color: { argb: card.text } } : undefined,
            left: c === c1 ? { style: 'thin', color: { argb: card.text } } : undefined,
            right: c === c2 ? { style: 'thin', color: { argb: card.text } } : undefined,
          };
        }
      }
    });
    ws.getRow(labelRow).height = 14;
    ws.getRow(valueRow).height = 20;
    r = valueRow + 1;

    // ══ TABEL DATA ══
    const headerRow = r;
    headers.forEach((h, i) => {
      const cell = ws.getCell(headerRow, i + 1);
      cell.value = h;
      cell.font = { bold: true, size: 10, color: { argb: 'FFFFFFFF' } };
      cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: NAVY } };
      cell.alignment = { horizontal: 'center', vertical: 'middle' };
      cell.border = { top: thinBorder, bottom: thinBorder, left: thinBorder, right: thinBorder };
    });
    ws.getRow(headerRow).height = 20;
    r++;

    dataRows.forEach((rowData, idx) => {
      const rowIdx = r + idx;
      const band = idx % 2 === 1;
      rowData.forEach((val, i) => {
        const cell = ws.getCell(rowIdx, i + 1);
        cell.value = val;
        cell.font = { size: 10, color: { argb: 'FF1E293B' } };
        cell.alignment = { horizontal: i === 0 ? 'center' : 'left', vertical: 'middle' };
        cell.border = { top: thinBorder, bottom: thinBorder, left: thinBorder, right: thinBorder };
        if (band) cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: BAND } };
      });
    });
    r += dataRows.length;

    ws.autoFilter = { from: { row: headerRow, column: 1 }, to: { row: headerRow, column: N } };
    ws.views = [{ state: 'frozen', ySplit: headerRow, showGridLines: false }];

    r += 1;
    ws.mergeCells(r, 1, r, N);
    const footCell = ws.getCell(r, 1);
    footCell.value = `Dicetak otomatis oleh Sistem Layanan Angkutan Sekolah — Dinas Perhubungan Kabupaten Tulungagung. Total ${totalAbsen.toLocaleString('id-ID')} data absensi.`;
    footCell.font = { size: 8.5, italic: true, color: { argb: 'FF94A3B8' } };
    footCell.alignment = { horizontal: 'center' };

    const buffer = await wb.xlsx.writeBuffer();
    const blob = new Blob([buffer], { type: 'application/octet-stream' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `Laporan_Absensi_${new Date().toISOString().slice(0, 10)}.xlsx`;
    document.body.appendChild(a); a.click(); a.remove();
    URL.revokeObjectURL(url);

    showToast('success', 'Laporan berhasil diunduh');
  } catch (e) { showToast('error', 'Gagal export: ' + e.message); }
}

document.getElementById('btnFilter')?.addEventListener('click', loadReport);
document.getElementById('btnDownload')?.addEventListener('click', downloadExcel);

window.onload = async () => {
  if (!IS_GUEST) {
    document.getElementById('filterDriver').value = '';
    document.getElementById('filterTrayek').value = '';
    document.getElementById('trayekContainer').classList.remove('show');
    document.getElementById('driverContainer').classList.remove('show');
    filterTanggal.value = 'today';
    filterTanggal.dispatchEvent(new Event('change'));
    setupTrayekDriverLogic();
  }
  await loadDropdownData();
  await loadReport();

  // ── Realtime ──────────────────────────────────────────────────
  // Absensi (RFID/foto) masuk kapan saja sepanjang hari. Auto-refresh
  // laporan hanya jika admin sedang melihat rentang "Hari ini" &
  // sedang di halaman pertama — supaya tidak mengganggu saat admin
  // sedang menelusuri laporan bulan/minggu lalu atau halaman lain.
  let _rtReloadTimer = null;
  const scheduleReload = () => {
    if (filterTanggal.value !== 'today' || currentPage !== 1) return;
    clearTimeout(_rtReloadTimer);
    _rtReloadTimer = setTimeout(loadReport, 800);
  };
  if (sbRealtime) {
    sbRealtime
      .channel('admin-dashboard-absensi')
      .on('postgres_changes', { event: '*', schema: 'public', table: 'absensi_RFID' }, scheduleReload)
      .on('postgres_changes', { event: '*', schema: 'public', table: 'absensi_QRCode' }, scheduleReload)
      .on('postgres_changes', { event: '*', schema: 'public', table: 'absensi_foto' }, scheduleReload)
      .subscribe();
    window.addEventListener('pagehide', () => sbRealtime.removeAllChannels());
  }
};
</script>

<!-- Sidebar toggle script (shared, must be after sidebar.php) -->
<script>
(function () {
  const sidebar  = document.getElementById('admSidebar');
  const overlay  = document.getElementById('admOverlay');
  const openBtn  = document.getElementById('sidebarOpen');
  const closeBtn = document.getElementById('sidebarClose');
  function open()  { sidebar.classList.add('open');  overlay.classList.add('show'); document.body.style.overflow = 'hidden'; }
  function close() { sidebar.classList.remove('open'); overlay.classList.remove('show'); document.body.style.overflow = ''; }
  if (openBtn)  openBtn.addEventListener('click', open);
  if (closeBtn) closeBtn.addEventListener('click', close);
  if (overlay)  overlay.addEventListener('click', close);
})();
</script>

</body>
</html>