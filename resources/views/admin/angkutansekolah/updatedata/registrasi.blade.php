<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
<meta name="csrf-token" content="{{ csrf_token() }}"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Registrasi Siswa — Admin</title>
  <link rel="canonical" href="/admin/registrasi-siswa"/>
  <link rel="icon" href="/favicon.ico"/>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/admin/admin-shell.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
  <script src="/assets/admin/angkutansekolah/sb-secure.js"></script>
  <style>
    /* ── Form grid ── */
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; padding: 24px; }
    .field { display: flex; flex-direction: column; gap: 5px; }
    .field label { font-size: 11.5px; font-weight: 600; letter-spacing: .5px; text-transform: uppercase; color: var(--text-3); }
    .field input, .field select {
      padding: 9px 14px; border: 1.5px solid var(--border); border-radius: 9px;
      font-family: 'DM Sans', sans-serif; font-size: 14px; color: var(--text-1);
      background: var(--surface-2); outline: none;
      transition: border-color .15s, box-shadow .15s, background .15s;
      appearance: none; -webkit-appearance: none;
    }
    .field input:focus, .field select:focus { border-color: var(--accent); box-shadow: 0 0 0 3px var(--accent-glow); background: #fff; }
    .field input[readonly] { background: var(--surface-2); color: var(--text-3); cursor: default; }
    .field .hint { font-size: 11px; color: var(--text-4); }
    .span-2 { grid-column: span 2; }
    .select-wrap { position: relative; }
    .select-wrap::after { content: '▾'; position: absolute; right: 13px; top: 50%; transform: translateY(-50%); color: var(--text-4); font-size: 13px; pointer-events: none; }
    .select-wrap select { width: 100%; padding-right: 32px; cursor: pointer; }
    .select-loading { display: flex; align-items: center; gap: 8px; padding: 9px 14px; border: 1.5px solid var(--border); border-radius: 9px; background: var(--surface-2); color: var(--text-4); font-size: 13.5px; }
    .form-footer { padding: 16px 24px; border-top: 1px solid var(--border); display: flex; gap: 10px; justify-content: flex-end; }

    /* ── Table ── */
    .table-wrap { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; font-size: 13.5px; }
    th { padding: 11px 16px; text-align: left; font-size: 11px; font-weight: 600; letter-spacing: .5px; text-transform: uppercase; color: var(--text-4); border-bottom: 1px solid var(--border); white-space: nowrap; }
    td { padding: 11px 16px; border-bottom: 1px solid var(--border); vertical-align: middle; }
    tr:last-child td { border-bottom: none; }
    tr:hover td { background: var(--surface-2); }
    .badge { display: inline-flex; padding: 2px 10px; border-radius: 999px; font-size: 11px; font-weight: 600; }
    .badge-blue { background: #dbeafe; color: #1d4ed8; }
    .badge-pink { background: #fce7f3; color: #9d174d; }
    .empty-state { text-align: center; padding: 60px 24px; color: var(--text-4); font-size: 13.5px; }

    /* ── Filter bar ── */
    .filter-bar { padding: 14px 20px; border-bottom: 1px solid var(--border); display: flex; flex-direction: column; gap: 10px; }
    .filter-row { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
    .filter-search-wrap { position: relative; flex: 1; min-width: 180px; }
    .filter-icon { position: absolute; left: 11px; top: 50%; transform: translateY(-50%); font-size: 13px; pointer-events: none; }
    .filter-search-wrap input { width: 100%; padding: 8px 14px 8px 32px; border: 1.5px solid var(--border); border-radius: 9px; font-size: 13.5px; font-family: 'DM Sans', sans-serif; outline: none; background: var(--surface-2); transition: border-color .15s; }
    .filter-search-wrap input:focus { border-color: var(--accent); background: #fff; }
    .filter-selects { display: flex; gap: 8px; flex-wrap: wrap; }
    .filter-select-wrap { position: relative; }
    .filter-select-wrap::after { content: '▾'; position: absolute; right: 10px; top: 50%; transform: translateY(-50%); color: var(--text-4); font-size: 11px; pointer-events: none; }
    .filter-select-wrap select { padding: 7px 26px 7px 11px; border: 1.5px solid var(--border); border-radius: 9px; font-family: 'DM Sans', sans-serif; font-size: 13px; color: var(--text-1); background: var(--surface-2); outline: none; cursor: pointer; appearance: none; -webkit-appearance: none; transition: border-color .15s; }
    .filter-select-wrap select:focus { border-color: var(--accent); box-shadow: 0 0 0 3px var(--accent-glow); }
    .filter-select-wrap select.active-filter { border-color: var(--accent); background: #eff6ff; color: #1d4ed8; font-weight: 600; }
    .filter-summary { font-size: 12px; color: var(--text-4); }

    /* ── Pagination ── */
    .pagination-bar {
      display: flex; align-items: center; justify-content: space-between;
      padding: 12px 20px; border-top: 1px solid var(--border);
      flex-wrap: wrap; gap: 10px;
    }
    .pagination-info { font-size: 12.5px; color: var(--text-4); }
    .pagination-info strong { color: var(--text-2); }
    .pagination-controls { display: flex; align-items: center; gap: 4px; flex-wrap: wrap; }
    .pg-btn {
      min-width: 34px; height: 34px; padding: 0 8px;
      border-radius: 8px; border: 1.5px solid var(--border);
      background: var(--surface); font-size: 13px; font-weight: 500;
      cursor: pointer; color: var(--text-3); transition: all .15s;
      font-family: 'DM Sans', sans-serif; display: flex; align-items: center; justify-content: center;
    }
    .pg-btn:hover:not(:disabled) { border-color: var(--accent); color: var(--accent); }
    .pg-btn.active { background: var(--accent); border-color: var(--accent); color: white; font-weight: 700; }
    .pg-btn:disabled { opacity: 0.35; cursor: not-allowed; }
    .pg-btn.pg-ellipsis { border: none; background: none; cursor: default; color: var(--text-4); }
    .pg-btn.pg-ellipsis:hover { border: none; color: var(--text-4); }
    .per-page-wrap { display: flex; align-items: center; gap: 8px; font-size: 12.5px; color: var(--text-4); }
    .per-page-wrap select { padding: 5px 24px 5px 10px; border: 1.5px solid var(--border); border-radius: 8px; font-family: 'DM Sans',sans-serif; font-size: 12.5px; color: var(--text-2); background: var(--surface-2); outline: none; cursor: pointer; appearance: none; -webkit-appearance: none; transition: border-color .15s; }
    .per-page-wrap select:focus { border-color: var(--accent); }
    .per-page-wrap .pw-arrow { position: relative; }
    .per-page-wrap .pw-arrow::after { content: '▾'; position: absolute; right: 8px; top: 50%; transform: translateY(-50%); color: var(--text-4); font-size: 10px; pointer-events: none; }

    /* ── Loading overlay ── */
    .table-loading {
      position: relative; min-height: 120px;
    }
    .table-loading::after {
      content: ''; position: absolute; inset: 0;
      background: rgba(255,255,255,.6);
      display: flex; align-items: center; justify-content: center;
      border-radius: 0 0 14px 14px;
      z-index: 5;
    }
    .tbl-spinner-row td { padding: 40px 0; text-align: center; }
    .tbl-spinner {
      display: inline-block;
      width: 24px; height: 24px;
      border: 3px solid var(--border-2);
      border-top-color: var(--accent);
      border-radius: 50%;
      animation: spin .7s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }

    /* ── Toast ── */
    #toast { position: fixed; bottom: 24px; right: 24px; background: var(--navy); color: #fff; padding: 12px 20px; border-radius: 10px; font-size: 13.5px; font-weight: 500; box-shadow: 0 8px 24px rgba(0,0,0,.25); z-index: 9999; transform: translateY(80px); opacity: 0; transition: all .3s cubic-bezier(.22,.61,.36,1); }
    #toast.show { transform: translateY(0); opacity: 1; }
    #toast.success { background: #15803d; }
    #toast.error   { background: #b91c1c; }

    .spinner-sm { width: 14px; height: 14px; border: 2px solid var(--border); border-top-color: var(--accent); border-radius: 50%; animation: spin .7s linear infinite; flex-shrink: 0; }

    @media (max-width: 640px) {
      .form-grid { grid-template-columns: 1fr; }
      .span-2 { grid-column: span 1; }
      .pagination-bar { flex-direction: column; align-items: flex-start; }
    }
  </style>
</head>
<body>
<div class="adm-shell">
  @include('admin.partials.sidebar', ['currentPage' => 'update_siswa', 'currentModule' => 'angkutansekolah'])
  <main class="adm-main">
    <div class="adm-content">

      <div class="adm-page-header">
        <div>
          <h1 class="adm-page-title">Registrasi Siswa</h1>
          <p class="adm-page-subtitle">Daftarkan siswa baru dan kelola data pengguna kartu RFID</p>
        </div>
      </div>

      <!-- Form Card -->
      <div class="card" style="margin-bottom:24px;">
        <div style="padding:20px 24px 0;font-size:15px;font-weight:600;color:var(--text-1);">Tambah / Edit Siswa</div>
        <div class="form-grid">
          <div class="field">
            <label>NIK <span style="color:#ef4444">*</span></label>
            <input type="text" id="f-nik" inputmode="numeric" pattern="\d*" maxlength="16" placeholder="16 digit NIK" autocomplete="off"/>
            <div style="display:flex;justify-content:space-between;align-items:center;">
              <span class="hint">Sesuai KTP/KIA — 16 digit angka</span>
              <span class="hint" id="nik-counter" style="font-variant-numeric:tabular-nums;">0 / 16</span>
            </div>
          </div>
          <div class="field">
            <label>Nama Lengkap <span style="color:#ef4444">*</span></label>
            <input type="text" id="f-nama" placeholder="Nama siswa"/>
          </div>
          <div class="field">
            <label>No. HP</label>
            <input type="text" id="f-hp" inputmode="numeric" pattern="\d*" maxlength="15" placeholder="08xxxxxxxxxx" autocomplete="off"/>
          </div>
          <div class="field">
            <label>Jenis Kelamin <span style="color:#ef4444">*</span></label>
            <div class="select-wrap">
              <select id="f-jk">
                <option value="">Pilih</option>
                <option value="Laki-laki">Laki-laki</option>
                <option value="Perempuan">Perempuan</option>
              </select>
            </div>
          </div>
          <div class="field">
            <label>Domisili (Kecamatan) <span style="color:#ef4444">*</span></label>
            <div id="wrap-domisili"><div class="select-loading"><div class="spinner-sm"></div> Memuat...</div></div>
          </div>
          <div class="field">
            <label>Nama Sekolah <span style="color:#ef4444">*</span></label>
            <div id="wrap-sekolah"><div class="select-loading"><div class="spinner-sm"></div> Memuat...</div></div>
          </div>
          <div class="field span-2">
            <label>Catatan (opsional)</label>
            <input type="text" id="f-catatan" placeholder="Misal: difabel, perlu kursi khusus, dll"/>
          </div>
        </div>
        <div class="form-footer">
          <button class="btn btn-secondary" onclick="resetForm()">Reset</button>
          <button class="btn btn-primary" id="btn-simpan" onclick="simpanSiswa()">Simpan Siswa</button>
        </div>
      </div>

      <!-- Tabel Card -->
      <div class="card">
        <div class="filter-bar">
          <div class="filter-row">
            <div class="filter-search-wrap">
              <span class="filter-icon">🔍</span>
              <input type="text" id="search-input" placeholder="Cari nama atau NIK..."/>
            </div>
            <div class="filter-selects">
              <div class="filter-select-wrap">
                <select id="filter-sekolah"><option value="">Semua Sekolah</option></select>
              </div>
              <div class="filter-select-wrap">
                <select id="filter-domisili"><option value="">Semua Kecamatan</option></select>
              </div>
              <div class="filter-select-wrap">
                <select id="filter-jk">
                  <option value="">Semua JK</option>
                  <option value="Laki-laki">Laki-laki</option>
                  <option value="Perempuan">Perempuan</option>
                </select>
              </div>
            </div>
            <div style="display:flex;gap:8px;flex-shrink:0;">
              <button class="btn btn-secondary btn-sm" onclick="resetFilter()">Reset</button>
              <button class="btn btn-success btn-sm" onclick="exportExcel()">↓ Export Excel</button>
            </div>
          </div>
          <div class="filter-summary" id="filter-summary"></div>
        </div>

        <div class="table-wrap" id="table-wrap">
          <table>
            <thead>
              <tr>
                <th>NIK</th><th>Nama</th><th>No. HP</th><th>Jenis Kelamin</th>
                <th>Sekolah</th><th>Domisili</th><th>Aksi</th>
              </tr>
            </thead>
            <tbody id="tabel-siswa">
              <tr class="tbl-spinner-row"><td colspan="7"><div class="tbl-spinner"></div></td></tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="pagination-bar" id="pagination-bar">
          <div class="pagination-info" id="pagination-info">Memuat...</div>
          <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
            <div class="per-page-wrap">
              Tampilkan
              <div class="pw-arrow">
                <select id="per-page-select" onchange="onPerPageChange()">
                  <option value="20">20</option>
                  <option value="50">50</option>
                  <option value="100">100</option>
                </select>
              </div>
              per halaman
            </div>
            <div class="pagination-controls" id="pagination-controls"></div>
          </div>
        </div>
      </div>

    </div>
  </main>
</div>
<div id="toast"></div>

<script>
// ── Supabase config ─────────────────────────────────────────────────────────
const SB_URL  = {!! json_encode(config('services.supabase.url')) !!};
const SB_ANON = {!! json_encode(config('services.supabase.anon_key')) !!};
const sbHdr   = () => ({ 'Content-Type': 'application/json', 'apikey': SB_ANON, 'Authorization': `Bearer ${SB_ANON}` });

// ── Role: superadmin = akses hapus data; dishubta = tidak ───────────────────
const IS_SUPERADMIN = {!! json_encode(session('admin_role') === 'superadmin') !!};

// ── State ───────────────────────────────────────────────────────────────────
let domisiliData = [];
let sekolahData  = [];
let editingNik   = null;
let rowDataMap   = {};  // map id → row data, untuk menghindari JSON inline di onclick

// Pagination & filter state
let currentPage  = 1;
let perPage      = 20;
let totalCount   = 0;
let isLoading    = false;

// Debounce timer untuk search
let searchDebounce = null;

// ── Helpers ─────────────────────────────────────────────────────────────────

/** Fetch satu halaman data siswa dari Supabase dengan filter & pagination */
async function fetchPage(page, limit, q, fSek, fDom, fJk) {
  const from = (page - 1) * limit;
  const to   = from + limit - 1;

  // Bangun query params filter
  const params = new URLSearchParams({ select: '*', order: 'nama.asc' });

  if (q) {
    // Supabase: OR filter — cari di nama atau nik
    params.set('or', `(nama.ilike.*${q}*,nik.ilike.*${q}*)`);
  }
  if (fSek) params.set('sekolah', `eq.${fSek}`);
  if (fDom) params.set('domisili', `eq.${fDom}`);
  if (fJk)  params.set('jenis_kelamin', `eq.${fJk}`);

  const res = await fetch(`${SB_URL}/rest/v1/user_RFID?${params}`, {
    headers: {
      ...sbHdr(),
      'Range'      : `${from}-${to}`,
      'Range-Unit' : 'items',
      'Prefer'     : 'count=exact',   // minta total count di header Content-Range
    }
  });

  if (!res.ok) {
    const err = await res.json().catch(() => ({}));
    throw new Error(err.message || `HTTP ${res.status}`);
  }

  // Ambil total dari header Content-Range: items 0-19/352
  const rangeHeader = res.headers.get('Content-Range') || '';
  const match       = rangeHeader.match(/\/(\d+)$/);
  const total       = match ? parseInt(match[1], 10) : 0;
  const rows        = await res.json();

  return { rows, total };
}

/** Fetch semua halaman — hanya untuk export CSV */
async function fetchAllForExport(q, fSek, fDom, fJk) {
  const all = [];
  let from  = 0;
  const batchSize = 1000;

  while (true) {
    const params = new URLSearchParams({ select: '*', order: 'nama.asc' });
    if (q)    params.set('or', `(nama.ilike.*${q}*,nik.ilike.*${q}*)`);
    if (fSek) params.set('sekolah', `eq.${fSek}`);
    if (fDom) params.set('domisili', `eq.${fDom}`);
    if (fJk)  params.set('jenis_kelamin', `eq.${fJk}`);

    const res = await fetch(`${SB_URL}/rest/v1/user_RFID?${params}`, {
      headers: {
        ...sbHdr(),
        'Range'     : `${from}-${from + batchSize - 1}`,
        'Range-Unit': 'items',
        'Prefer'    : 'count=none',
      }
    });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const rows = await res.json();
    all.push(...rows);
    if (rows.length < batchSize) break;
    from += batchSize;
  }
  return all;
}

// ── Inisialisasi awal ───────────────────────────────────────────────────────
async function init() {
  try {
    // Fetch lookup data (kecil, sekali saja)
    [domisiliData, sekolahData] = await Promise.all([
      sbGetShared('domisili', 'select=id,kecamatan&limit=10000'),
      sbGetShared('sekolah',  'select=id,sekolah&limit=10000'),
    ]);
    domisiliData.sort((a, b) => (a.kecamatan || '').localeCompare(b.kecamatan || '', 'id'));
    sekolahData.sort((a, b)  => (a.sekolah   || '').localeCompare(b.sekolah   || '', 'id'));

    buildSelect('wrap-domisili', 'f-domisili', domisiliData, 'kecamatan', 'Pilih Kecamatan...');
    buildSelect('wrap-sekolah',  'f-sekolah',  sekolahData,  'sekolah',   'Pilih Sekolah...');
    populateFilterDropdowns();

    // Pasang listener filter dengan debounce untuk search
    document.getElementById('search-input').addEventListener('input', () => {
      clearTimeout(searchDebounce);
      searchDebounce = setTimeout(() => goToPage(1), 350);
    });
    document.getElementById('filter-sekolah').addEventListener('change',  () => goToPage(1));
    document.getElementById('filter-domisili').addEventListener('change', () => goToPage(1));
    document.getElementById('filter-jk').addEventListener('change',       () => goToPage(1));

    // Load halaman pertama
    await loadPage(1);
  } catch (err) {
    toast('Gagal memuat: ' + err.message, 'error');
  }
}

// ── Load & render satu halaman ──────────────────────────────────────────────
async function loadPage(page) {
  if (isLoading) return;
  isLoading    = true;
  currentPage  = page;

  const tbody = document.getElementById('tabel-siswa');
  tbody.innerHTML = `<tr class="tbl-spinner-row"><td colspan="7"><div class="tbl-spinner"></div></td></tr>`;
  document.getElementById('pagination-controls').innerHTML = '';
  document.getElementById('pagination-info').textContent = 'Memuat...';

  const q    = document.getElementById('search-input').value.trim();
  const fSek = document.getElementById('filter-sekolah').value;
  const fDom = document.getElementById('filter-domisili').value;
  const fJk  = document.getElementById('filter-jk').value;

  // Update active-filter style
  ['filter-sekolah', 'filter-domisili', 'filter-jk'].forEach(id => {
    document.getElementById(id).classList.toggle('active-filter', !!document.getElementById(id).value);
  });

  try {
    const { rows, total } = await fetchPage(page, perPage, q, fSek, fDom, fJk);
    totalCount = total;
    renderTableRows(rows);
    renderPagination(page, Math.ceil(total / perPage), total, rows.length);
    updateFilterSummary(q, fSek, fDom, fJk, total);
  } catch (err) {
    tbody.innerHTML = `<tr><td colspan="7" class="empty-state">Gagal memuat: ${err.message}</td></tr>`;
    toast('Gagal memuat data: ' + err.message, 'error');
  } finally {
    isLoading = false;
  }
}

function goToPage(page) {
  if (page < 1) return;
  const totalPages = Math.ceil(totalCount / perPage);
  if (page > totalPages && totalPages > 0) return;
  loadPage(page);
}

function onPerPageChange() {
  perPage = parseInt(document.getElementById('per-page-select').value, 10);
  loadPage(1);
}

// ── Render rows ─────────────────────────────────────────────────────────────
function renderTableRows(rows) {
  const tbody = document.getElementById('tabel-siswa');
  if (!rows.length) {
    tbody.innerHTML = `<tr><td colspan="7" class="empty-state">Tidak ada data yang cocok</td></tr>`;
    return;
  }
  // Simpan data baris ke map agar bisa diakses tanpa inline JSON di onclick
  rowDataMap = {};
  tbody.innerHTML = rows.map(s => {
    rowDataMap[s.nik] = s;
    const btnHapus = IS_SUPERADMIN
      ? '<button class="btn btn-danger btn-sm" onclick="hapusSiswa(\''+s.nik+'\',\''+esc(s.nama)+'\')">Hapus</button>'
      : '';
    return `
    <tr>
      <td><code style="font-family:'DM Mono',monospace;font-size:12px;">${s.nik || '-'}</code></td>
      <td style="font-weight:500;color:var(--text-1);">${esc(s.nama)}</td>
      <td>${esc(s.no_hp) || '-'}</td>
      <td><span class="badge ${s.jenis_kelamin === 'Laki-laki' ? 'badge-blue' : 'badge-pink'}">${esc(s.jenis_kelamin) || '-'}</span></td>
      <td>${esc(s.sekolah) || '-'}</td>
      <td>${esc(s.domisili) || '-'}</td>
      <td style="display:flex;gap:6px;">
        <button class="btn btn-secondary btn-sm" onclick="editSiswaById('${s.nik}')">Edit</button>
        ${btnHapus}
      </td>
    </tr>`;
  }).join('');
}

// ── Render pagination ───────────────────────────────────────────────────────
function renderPagination(page, totalPages, totalRows, pageRows) {
  const from = totalRows === 0 ? 0 : (page - 1) * perPage + 1;
  const to   = Math.min(page * perPage, totalRows);

  document.getElementById('pagination-info').innerHTML =
    totalRows === 0
      ? 'Tidak ada data'
      : `Menampilkan <strong>${from}–${to}</strong> dari <strong>${totalRows}</strong> siswa`;

  const ctrl = document.getElementById('pagination-controls');
  if (totalPages <= 1) { ctrl.innerHTML = ''; return; }

  // Tombol Prev
  let html = `<button class="pg-btn" ${page <= 1 ? 'disabled' : ''} onclick="goToPage(${page - 1})">&#8592;</button>`;

  // Angka halaman dengan ellipsis
  const pages = buildPageNumbers(page, totalPages);
  pages.forEach(p => {
    if (p === '…') {
      html += `<button class="pg-btn pg-ellipsis" disabled>…</button>`;
    } else {
      html += `<button class="pg-btn ${p === page ? 'active' : ''}" onclick="goToPage(${p})">${p}</button>`;
    }
  });

  // Tombol Next
  html += `<button class="pg-btn" ${page >= totalPages ? 'disabled' : ''} onclick="goToPage(${page + 1})">&#8594;</button>`;

  ctrl.innerHTML = html;
}

/** Hasilkan array nomor halaman + ellipsis (misal: [1, '…', 4, 5, 6, '…', 12]) */
function buildPageNumbers(current, total) {
  if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1);
  const pages = new Set([1, total, current]);
  if (current > 1) pages.add(current - 1);
  if (current < total) pages.add(current + 1);
  const sorted = [...pages].sort((a, b) => a - b);
  const result = [];
  let prev = 0;
  for (const p of sorted) {
    if (p - prev > 1) result.push('…');
    result.push(p);
    prev = p;
  }
  return result;
}

// ── Filter summary ──────────────────────────────────────────────────────────
function updateFilterSummary(q, fSek, fDom, fJk, total) {
  const el = document.getElementById('filter-summary');
  const hasFilter = q || fSek || fDom || fJk;
  if (!hasFilter) { el.textContent = ''; return; }
  const parts = [];
  if (q)    parts.push(`teks "<strong>${esc(q)}</strong>"`);
  if (fSek) parts.push(`sekolah "<strong>${esc(fSek)}</strong>"`);
  if (fDom) parts.push(`kecamatan "<strong>${esc(fDom)}</strong>"`);
  if (fJk)  parts.push(`JK "<strong>${esc(fJk)}</strong>"`);
  el.innerHTML = `Ditemukan <strong>${total}</strong> siswa — filter: ${parts.join(', ')}`;
}

// ── Dropdown builders ────────────────────────────────────────────────────────
function buildSelect(wrapId, selectId, data, field, placeholder) {
  const wrap = document.getElementById(wrapId);
  if (!wrap) return;
  const sel = document.createElement('select');
  sel.id = selectId;
  sel.style.cssText = `width:100%;padding:9px 32px 9px 14px;border:1.5px solid var(--border);border-radius:9px;font-family:'DM Sans',sans-serif;font-size:14px;color:var(--text-1);background:var(--surface-2);outline:none;transition:border-color .15s;appearance:none;-webkit-appearance:none;cursor:pointer;`;
  sel.addEventListener('focus', () => { sel.style.borderColor = 'var(--accent)'; sel.style.boxShadow = '0 0 0 3px var(--accent-glow)'; sel.style.background = '#fff'; });
  sel.addEventListener('blur',  () => { sel.style.borderColor = 'var(--border)';  sel.style.boxShadow = 'none'; sel.style.background = 'var(--surface-2)'; });
  const def = document.createElement('option');
  def.value = ''; def.textContent = placeholder;
  sel.appendChild(def);
  data.forEach(row => {
    const opt = document.createElement('option');
    opt.value = opt.textContent = row[field] || '';
    sel.appendChild(opt);
  });
  wrap.innerHTML = '';
  const div = document.createElement('div');
  div.className = 'select-wrap';
  div.appendChild(sel);
  wrap.appendChild(div);
}

function populateFilterDropdowns() {
  const selSek = document.getElementById('filter-sekolah');
  sekolahData.forEach(row => {
    const o = document.createElement('option');
    o.value = o.textContent = row.sekolah || '';
    selSek.appendChild(o);
  });
  const selDom = document.getElementById('filter-domisili');
  domisiliData.forEach(row => {
    const o = document.createElement('option');
    o.value = o.textContent = row.kecamatan || '';
    selDom.appendChild(o);
  });
}

// ── Reset filter ────────────────────────────────────────────────────────────
function resetFilter() {
  document.getElementById('search-input').value = '';
  ['filter-sekolah', 'filter-domisili', 'filter-jk'].forEach(id => {
    document.getElementById(id).value = '';
    document.getElementById(id).classList.remove('active-filter');
  });
  goToPage(1);
}

// ── Simpan / update siswa ───────────────────────────────────────────────────
async function simpanSiswa() {
  const nik     = document.getElementById('f-nik').value.trim();
  const nama    = document.getElementById('f-nama').value.trim();
  const hp      = document.getElementById('f-hp').value.trim();
  const jk      = document.getElementById('f-jk').value;
  const dom     = document.getElementById('f-domisili')?.value || '';
  const sek     = document.getElementById('f-sekolah')?.value  || '';
  const catatan = document.getElementById('f-catatan').value.trim();

  if (!nik || !nama || !jk || !dom || !sek) { toast('Lengkapi semua field wajib', 'error'); return; }
  if (!/^\d{16}$/.test(nik)) { toast('NIK harus 16 digit angka', 'error'); return; }

  const btn = document.getElementById('btn-simpan');
  btn.disabled = true;
  btn.textContent = 'Menyimpan...';

  try {
    const body = { nik, nama, no_hp: hp, jenis_kelamin: jk, domisili: dom, sekolah: sek, catatan };
    if (editingNik) {
      await sbPatch('user_RFID', `nik=eq.${encodeURIComponent(editingNik)}`, body);
      toast('Data siswa diperbarui', 'success');
    } else {
      await sbPost('user_RFID', body);
      toast('Siswa berhasil didaftarkan', 'success');
    }
    resetForm();
    await loadPage(editingNik ? currentPage : 1);   // kembali ke hal 1 kalau tambah baru
  } catch (err) {
    toast(err.message, 'error');
  } finally {
    btn.disabled    = false;
    btn.textContent = editingNik ? 'Update Siswa' : 'Simpan Siswa';
  }
}

// ── Edit siswa (dari baris tabel) ───────────────────────────────────────────
function editSiswaById(nik) {
  const s = rowDataMap[nik];
  if (!s) { toast('Data tidak ditemukan, coba refresh halaman', 'error'); return; }
  editSiswa(s);
}

function editSiswa(s) {
  editingNik = s.nik;
  const nikEl = document.getElementById('f-nik');
  nikEl.value    = s.nik || '';
  nikEl.readOnly = false;   // NIK boleh diedit saat update
  updateNikCounter(nikEl.value.length);
  document.getElementById('f-nama').value    = s.nama    || '';
  document.getElementById('f-hp').value      = s.no_hp   || '';
  document.getElementById('f-jk').value      = s.jenis_kelamin || '';
  document.getElementById('f-catatan').value = s.catatan || '';
  const selDom = document.getElementById('f-domisili');
  const selSek = document.getElementById('f-sekolah');
  if (selDom) selDom.value = s.domisili || '';
  if (selSek) selSek.value = s.sekolah  || '';
  document.getElementById('btn-simpan').textContent = 'Update Siswa';
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

// ── Hapus siswa ─────────────────────────────────────────────────────────────
async function hapusSiswa(id, nama) {
  if (!IS_SUPERADMIN) { toast('Akses ditolak', 'error'); return; }
  if (!(await confirmDangerModal(`Hapus siswa "${nama}"?`))) return;
  try {
    // Lewat admin/api/angkutansekolah/db.php (pakai service key server-side), bukan fetch
    // langsung ke Supabase dengan anon key — anon key tidak diizinkan
    // DELETE oleh RLS sehingga sebelumnya selalu gagal ("Failed to fetch").
    await sbDelete('user_RFID', `nik=eq.${encodeURIComponent(id)}`);
    toast('Siswa dihapus', 'success');
    const newTotal      = Math.max(0, totalCount - 1);
    const newTotalPages = Math.max(1, Math.ceil(newTotal / perPage));
    const goPage        = currentPage > newTotalPages ? newTotalPages : currentPage;
    await loadPage(goPage);
  } catch (err) {
    toast('Gagal menghapus: ' + err.message, 'error');
  }
}

// ── Reset form ──────────────────────────────────────────────────────────────
function resetForm() {
  editingNik = null;
  ['f-nik', 'f-nama', 'f-hp', 'f-catatan'].forEach(id => {
    const el = document.getElementById(id);
    if (el) { el.value = ''; if (id === 'f-nik') { el.readOnly = false; updateNikCounter(0); } }
  });
  document.getElementById('f-jk').value = '';
  const selDom = document.getElementById('f-domisili');
  const selSek = document.getElementById('f-sekolah');
  if (selDom) selDom.value = '';
  if (selSek) selSek.value = '';
  document.getElementById('btn-simpan').textContent = 'Simpan Siswa';
}

// ── Export Excel (ambil semua dengan filter aktif, output .xlsx elegan) ───────
async function exportExcel() {
  const btn = document.querySelector('[onclick="exportExcel()"]');
  const origHTML = btn.innerHTML;
  btn.disabled  = true;
  btn.innerHTML = '<span style="display:inline-block;width:12px;height:12px;border:2px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:50%;animation:spin .7s linear infinite;vertical-align:middle;margin-right:5px;"></span>Mengambil data...';

  try {
    const q    = document.getElementById('search-input').value.trim();
    const fSek = document.getElementById('filter-sekolah').value;
    const fDom = document.getElementById('filter-domisili').value;
    const fJk  = document.getElementById('filter-jk').value;

    const all = await fetchAllForExport(q, fSek, fDom, fJk);

    const dateStr   = new Date().toLocaleDateString('id-ID', { day:'2-digit', month:'long', year:'numeric' });
    const COLS      = ['A','B','C','D','E','F','G'];
    const HEADERS   = ['NIK', 'Nama Lengkap', 'No. HP', 'Jenis Kelamin', 'Sekolah', 'Domisili', 'Catatan'];
    const COL_WIDTHS= [20, 28, 16, 14, 36, 20, 28]; // chars

    // Build worksheet data: title rows + header + data rows
    const TITLE_ROW   = 1; // row index (1-based)
    const SUBTITLE_ROW= 2;
    const HEADER_ROW  = 4;
    const DATA_START  = 5;

    const wb = XLSX.utils.book_new();
    const wsData = [];

    // Row 1: Judul
    wsData.push(['DATA SISWA ANGKUTAN SEKOLAH', '', '', '', '', '', '']);
    // Row 2: Tanggal cetak
    wsData.push([`Dicetak: ${dateStr}  |  Total: ${all.length} siswa${q||fSek||fDom||fJk?' (filter aktif)':''}`, '', '', '', '', '', '']);
    // Row 3: kosong
    wsData.push(['', '', '', '', '', '', '']);
    // Row 4: Header kolom
    wsData.push(HEADERS);
    // Row 5+: data
    all.forEach((s, i) => {
      wsData.push([
        s.nik          || '',
        s.nama         || '',
        s.no_hp        || '',
        s.jenis_kelamin|| '',
        s.sekolah      || '',
        s.domisili     || '',
        s.catatan      || '',
      ]);
    });

    const ws = XLSX.utils.aoa_to_sheet(wsData);

    // ── Column widths ────────────────────────────────────────────
    ws['!cols'] = COL_WIDTHS.map(w => ({ wch: w }));

    // ── Merge title cells A1:G1 and subtitle A2:G2 ───────────────
    ws['!merges'] = [
      { s:{r:0,c:0}, e:{r:0,c:6} },
      { s:{r:1,c:0}, e:{r:1,c:6} },
    ];

    // ── Helper to set/merge cell style ───────────────────────────
    function setStyle(cellAddr, styleObj) {
      if (!ws[cellAddr]) ws[cellAddr] = { t:'z', v:'' };
      ws[cellAddr].s = Object.assign(ws[cellAddr].s || {}, styleObj);
    }

    // Navy colour used in the admin shell
    const NAVY   = '0B1120';
    const ACCENT = '3B82F6';
    const WHITE  = 'FFFFFF';
    const LIGHT  = 'EFF6FF'; // very light blue
    const BORDER_COLOR = 'CBD5E1';
    const LAKI_BG  = 'DBEAFE';
    const LAKI_FG  = '1D4ED8';
    const PEREMPUAN_BG = 'FCE7F3';
    const PEREMPUAN_FG = '9D174D';

    const thinBorder = {
      top:    { style:'thin', color:{ rgb: BORDER_COLOR } },
      bottom: { style:'thin', color:{ rgb: BORDER_COLOR } },
      left:   { style:'thin', color:{ rgb: BORDER_COLOR } },
      right:  { style:'thin', color:{ rgb: BORDER_COLOR } },
    };

    // ── Title row (row 1) ─────────────────────────────────────────
    setStyle('A1', {
      font:      { bold:true, sz:15, color:{ rgb: WHITE }, name:'Arial' },
      fill:      { patternType:'solid', fgColor:{ rgb: NAVY } },
      alignment: { horizontal:'center', vertical:'center' },
    });
    ws['!rows'] = ws['!rows'] || [];
    ws['!rows'][0] = { hpt: 28 }; // row height in points

    // ── Subtitle row (row 2) ──────────────────────────────────────
    setStyle('A2', {
      font:      { sz:10, italic:true, color:{ rgb: WHITE }, name:'Arial' },
      fill:      { patternType:'solid', fgColor:{ rgb: ACCENT } },
      alignment: { horizontal:'center', vertical:'center' },
    });
    ws['!rows'][1] = { hpt: 18 };
    ws['!rows'][2] = { hpt: 6 }; // gap row

    // Style the merged cells (B1:G1, B2:G2) same fill so they're seamless
    for (let c = 1; c <= 6; c++) {
      const addr1 = XLSX.utils.encode_cell({r:0, c});
      const addr2 = XLSX.utils.encode_cell({r:1, c});
      if (!ws[addr1]) ws[addr1] = {t:'z',v:''};
      if (!ws[addr2]) ws[addr2] = {t:'z',v:''};
      ws[addr1].s = { fill:{ patternType:'solid', fgColor:{ rgb: NAVY   } } };
      ws[addr2].s = { fill:{ patternType:'solid', fgColor:{ rgb: ACCENT } } };
    }

    // ── Header row (row 4) ────────────────────────────────────────
    ws['!rows'][3] = { hpt: 20 };
    COLS.forEach((col, ci) => {
      const addr = col + HEADER_ROW;
      setStyle(addr, {
        font:      { bold:true, sz:10, color:{ rgb: WHITE }, name:'Arial' },
        fill:      { patternType:'solid', fgColor:{ rgb: '1E3A5F' } },
        alignment: { horizontal:'center', vertical:'center', wrapText:true },
        border:    thinBorder,
      });
    });

    // ── Data rows ─────────────────────────────────────────────────
    all.forEach((s, i) => {
      const rowIdx  = DATA_START + i;       // 1-based Excel row
      const rowArr  = i;                    // 0-based array index
      const isEven  = i % 2 === 0;
      const rowFill = isEven
        ? { patternType:'solid', fgColor:{ rgb: 'F8FAFC' } }
        : { patternType:'solid', fgColor:{ rgb: WHITE   } };

      ws['!rows'][rowIdx - 1] = { hpt: 16 };

      COLS.forEach((col, ci) => {
        const addr = col + rowIdx;
        if (!ws[addr]) ws[addr] = { t:'z', v:'' };

        let cellStyle = {
          font:   { sz:10, name:'Arial' },
          fill:   rowFill,
          border: thinBorder,
          alignment: { vertical:'center' },
        };

        // NIK → monospace center
        if (ci === 0) {
          cellStyle.font  = { sz:10, name:'Courier New' };
          cellStyle.alignment = { horizontal:'center', vertical:'center' };
        }
        // Nama → bold slightly
        if (ci === 1) {
          cellStyle.font = { sz:10, bold:true, name:'Arial', color:{ rgb:'0F172A' } };
        }
        // HP → center
        if (ci === 2) {
          cellStyle.alignment = { horizontal:'center', vertical:'center' };
        }
        // Jenis Kelamin → colored badge feel
        if (ci === 3) {
          const isLaki = (s.jenis_kelamin||'') === 'Laki-laki';
          cellStyle.font      = { sz:10, bold:true, name:'Arial', color:{ rgb: isLaki ? LAKI_FG : PEREMPUAN_FG } };
          cellStyle.fill      = { patternType:'solid', fgColor:{ rgb: isLaki ? LAKI_BG  : PEREMPUAN_BG } };
          cellStyle.alignment = { horizontal:'center', vertical:'center' };
        }
        // Sekolah → wrap text
        if (ci === 4) {
          cellStyle.alignment = { vertical:'center', wrapText:true };
        }
        // Domisili
        if (ci === 5) {
          cellStyle.fill = { patternType:'solid', fgColor:{ rgb: isEven ? 'F0FDF4' : 'FAFFFE' } };
          cellStyle.font = { sz:10, name:'Arial', color:{ rgb:'166534' } };
        }
        // Catatan → italic gray
        if (ci === 6) {
          cellStyle.font      = { sz:9, italic:true, name:'Arial', color:{ rgb:'94A3B8' } };
          cellStyle.alignment = { vertical:'center', wrapText:true };
        }

        ws[addr].s = cellStyle;
      });
    });

    // ── Freeze header row ─────────────────────────────────────────
    ws['!freeze'] = { xSplit:0, ySplit: DATA_START - 1, topLeftCell:'A' + DATA_START };

    // ── Auto filter on header row ─────────────────────────────────
    ws['!autofilter'] = { ref: `A${HEADER_ROW}:G${HEADER_ROW + all.length}` };

    XLSX.utils.book_append_sheet(wb, ws, 'Data Siswa');

    // ── Write & download ──────────────────────────────────────────
    const filename = `data_siswa_${new Date().toISOString().slice(0,10)}.xlsx`;
    XLSX.writeFile(wb, filename, { bookType:'xlsx', type:'binary', cellStyles:true });

    toast(`✓ ${all.length} siswa diekspor ke Excel`, 'success');
  } catch (err) {
    toast('Gagal export: ' + err.message, 'error');
  } finally {
    btn.disabled  = false;
    btn.innerHTML = origHTML;
  }
}

// ── Utils ────────────────────────────────────────────────────────────────────
function esc(str) {
  if (!str) return '';
  return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function toast(msg, type = 'info') {
  const el = document.getElementById('toast');
  el.textContent = msg;
  el.className = type;
  void el.offsetWidth;
  el.classList.add('show');
  setTimeout(() => el.classList.remove('show'), 3000);
}

// ── Boot ─────────────────────────────────────────────────────────────────────

// Filter input hanya angka untuk NIK dan HP
function onlyDigits(el, maxLen) {
  el.addEventListener('input', () => {
    // Hapus semua karakter bukan angka
    const clean = el.value.replace(/\D/g, '').slice(0, maxLen);
    if (el.value !== clean) el.value = clean;
    if (el.id === 'f-nik') updateNikCounter(clean.length);
  });
  el.addEventListener('keydown', (e) => {
    // Izinkan: angka, backspace, delete, tab, arrow, home, end, ctrl+a/c/v/x
    const allowed = ['Backspace','Delete','Tab','ArrowLeft','ArrowRight','Home','End'];
    if (allowed.includes(e.key)) return;
    if ((e.ctrlKey || e.metaKey) && ['a','c','v','x'].includes(e.key.toLowerCase())) return;
    if (!/^\d$/.test(e.key)) e.preventDefault();
  });
  el.addEventListener('paste', (e) => {
    e.preventDefault();
    const pasted = (e.clipboardData || window.clipboardData).getData('text');
    const clean  = pasted.replace(/\D/g, '').slice(0, maxLen);
    // insert at cursor
    const start = el.selectionStart, end = el.selectionEnd;
    const cur   = el.value;
    const next  = (cur.slice(0, start) + clean + cur.slice(end)).slice(0, maxLen);
    el.value = next;
    if (el.id === 'f-nik') updateNikCounter(next.length);
  });
}

function updateNikCounter(len) {
  const el = document.getElementById('nik-counter');
  if (!el) return;
  el.textContent = `${len} / 16`;
  el.style.color = len === 16 ? 'var(--emerald, #10b981)' : len > 0 ? 'var(--text-3)' : 'var(--text-4)';
}

onlyDigits(document.getElementById('f-nik'), 16);
onlyDigits(document.getElementById('f-hp'),  15);

init();
</script>

<script>(function(){const s=document.getElementById('admSidebar'),o=document.getElementById('admOverlay'),ob=document.getElementById('sidebarOpen'),cb=document.getElementById('sidebarClose');function op(){s.classList.add('open');o.classList.add('show');document.body.style.overflow='hidden';}function cl(){s.classList.remove('open');o.classList.remove('show');document.body.style.overflow='';}if(ob)ob.addEventListener('click',op);if(cb)cb.addEventListener('click',cl);if(o)o.addEventListener('click',cl);})();</script>
</body>
</html>