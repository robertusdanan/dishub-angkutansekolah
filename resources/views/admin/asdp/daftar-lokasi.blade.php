<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"/>
<meta name="csrf-token" content="{{ csrf_token() }}"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Daftar Lokasi ASDP — Admin</title>
<link rel="icon" href="/favicon.ico"/>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/admin/admin-shell.css">
<style>
  .adm-shell { display: flex !important; }
  .adm-main  { flex: 1; min-width: 0; }

  /* ── Toolbar ── */
  .toolbar { display:flex; gap:10px; align-items:center; margin-bottom:16px; flex-wrap:wrap; }
  .search-wrap { position:relative; flex:1; min-width:160px; max-width:320px; }
  .search-wrap svg { position:absolute; left:11px; top:50%; transform:translateY(-50%); color:var(--text-4); pointer-events:none; }
  .search-input { width:100%; padding:8px 11px 8px 34px; border:1.5px solid var(--border); border-radius:9px; font-family:'DM Sans',sans-serif; font-size:13px; color:var(--text-1); background:var(--surface-2); outline:none; transition:border-color .15s,box-shadow .15s; }
  .search-input:focus { border-color:var(--accent); box-shadow:0 0 0 3px var(--accent-glow); background:#fff; }

  .count-badge { display:inline-flex; align-items:center; gap:6px; padding:6px 14px; background:var(--surface-2); border:1.5px solid var(--border); border-radius:10px; font-size:13px; color:var(--text-2); font-weight:500; }
  .count-badge strong { color:var(--accent); font-weight:700; }

  /* ── Table card ── */
  .data-card { background:var(--surface); border:1.5px solid var(--border); border-radius:14px; overflow:hidden; }
  .data-table { width:100%; border-collapse:collapse; }
  .data-table thead tr { background:var(--surface-2); border-bottom:1.5px solid var(--border); }
  .data-table th { padding:10px 14px; text-align:left; font-size:10.5px; font-weight:700; letter-spacing:.07em; text-transform:uppercase; color:var(--text-3); white-space:nowrap; }
  .data-table tbody tr { border-bottom:1px solid var(--border); transition:background .12s; }
  .data-table tbody tr:last-child { border-bottom:none; }
  .data-table tbody tr:hover { background:var(--surface-2); }
  .data-table td { padding:10px 14px; font-size:13px; color:var(--text-1); vertical-align:top; }

  .id-badge { display:inline-block; font-family:'DM Mono',monospace; font-size:11px; background:var(--surface-2); border:1px solid var(--border); color:var(--text-3); padding:2px 7px; border-radius:5px; white-space:nowrap; }

  /* Thumbnail gambar */
  .thumb-wrap { position:relative; width:64px; height:64px; flex-shrink:0; }
  .foto-thumb { width:64px; height:64px; border-radius:9px; object-fit:cover; cursor:pointer; border:1.5px solid var(--border); display:block; background:var(--surface-2); transition:transform .15s,box-shadow .15s; }
  .foto-thumb:hover { transform:scale(1.05); box-shadow:0 4px 14px rgba(0,0,0,.14); }
  .thumb-placeholder { width:64px; height:64px; border-radius:9px; border:1.5px dashed var(--border); display:flex; align-items:center; justify-content:center; color:var(--text-4); background:var(--surface-2); }
  .thumb-edit-btns { display:flex; flex-direction:column; gap:4px; margin-top:6px; }
  .btn-thumb-action { display:inline-flex; align-items:center; justify-content:center; gap:4px; padding:4px 8px; border-radius:6px; font-family:'DM Sans',sans-serif; font-size:11px; font-weight:600; cursor:pointer; border:1.5px solid var(--border); background:var(--surface); color:var(--text-3); transition:all .15s; white-space:nowrap; }
  .btn-thumb-action:hover { border-color:var(--accent); color:var(--accent); }
  .btn-thumb-action.danger:hover { border-color:#ef4444; color:#ef4444; background:#fef2f2; }

  /* Inline edit */
  .edit-input, .edit-textarea { width:100%; padding:7px 10px; border:1.5px solid var(--border); border-radius:7px; font-family:'DM Sans',sans-serif; font-size:13px; color:var(--text-1); background:var(--surface); outline:none; transition:border-color .15s,box-shadow .15s; box-sizing:border-box; }
  .edit-input:focus, .edit-textarea:focus { border-color:var(--accent); box-shadow:0 0 0 3px var(--accent-glow); }
  .edit-textarea { resize:vertical; min-height:60px; font-family:inherit; line-height:1.5; }

  .desc-view { color:var(--text-2); font-size:12.5px; line-height:1.5; max-width:340px; display:block; white-space:pre-line; }
  .desc-view.empty { color:var(--text-4); font-style:italic; }

  /* Action buttons */
  .td-actions { white-space:nowrap; }
  .btn-edit, .btn-save-row, .btn-cancel-row, .btn-del {
    display:inline-flex; align-items:center; justify-content:center; gap:5px;
    padding:5px 10px; border-radius:7px; font-family:'DM Sans',sans-serif;
    font-size:12px; font-weight:600; cursor:pointer; border:1.5px solid; transition:all .15s;
  }
  .btn-edit       { background:#eff6ff; color:#1d4ed8; border-color:#bfdbfe; }
  .btn-edit:hover { background:#1d4ed8; color:#fff; border-color:#1d4ed8; }
  .btn-save-row   { background:#f0fdf4; color:#15803d; border-color:#bbf7d0; }
  .btn-save-row:hover { background:#15803d; color:#fff; border-color:#15803d; }
  .btn-cancel-row { background:var(--surface-2); color:var(--text-3); border-color:var(--border); }
  .btn-cancel-row:hover { background:var(--border); color:var(--text-1); }
  .btn-del        { background:#fef2f2; color:#ef4444; border-color:#fecaca; }
  .btn-del:hover  { background:#ef4444; color:#fff; border-color:#ef4444; }

  /* New row form */
  .new-row-section { border-top:1.5px dashed var(--border); padding:18px 16px 16px; background:var(--surface-2); }
  .new-row-label   { font-size:11px; font-weight:700; letter-spacing:.06em; text-transform:uppercase; color:var(--text-3); margin-bottom:12px; }
  .new-row-grid    { display:grid; grid-template-columns:100px 1fr 1fr; gap:14px; align-items:start; }
  .field-group     { display:flex; flex-direction:column; gap:4px; }
  .field-label     { font-size:10.5px; font-weight:700; color:var(--text-3); letter-spacing:.05em; text-transform:uppercase; }

  .new-thumb-box { width:100px; height:100px; border-radius:10px; border:1.5px dashed var(--border); display:flex; align-items:center; justify-content:center; overflow:hidden; cursor:pointer; background:var(--surface); color:var(--text-4); position:relative; }
  .new-thumb-box img { width:100%; height:100%; object-fit:cover; }
  .new-thumb-box:hover { border-color:var(--accent); color:var(--accent); }
  .new-thumb-hint { font-size:10.5px; color:var(--text-4); margin-top:6px; text-align:center; line-height:1.4; }

  .btn-add { display:flex; align-items:center; gap:6px; padding:9px 18px; background:var(--accent); color:#fff; border:none; border-radius:8px; font-family:'DM Sans',sans-serif; font-size:13px; font-weight:600; cursor:pointer; transition:all .15s; white-space:nowrap; margin-top:14px; }
  .btn-add:hover    { background:#2563eb; box-shadow:0 4px 14px rgba(59,130,246,.3); }
  .btn-add:disabled { background:var(--text-4); cursor:not-allowed; }

  .tbl-empty { text-align:center; padding:32px; color:var(--text-4); font-size:13px; }
  .skel { background:linear-gradient(90deg,var(--border) 25%,var(--surface-2) 50%,var(--border) 75%); background-size:200%; animation:shimmer 1.4s infinite; border-radius:5px; height:14px; }
  @keyframes shimmer { 0%{background-position:200% 0}100%{background-position:-200% 0} }

  .toast { position:fixed; bottom:24px; right:24px; z-index:9999; display:flex; align-items:center; gap:8px; padding:11px 16px; border-radius:10px; font-size:13px; font-weight:500; box-shadow:0 8px 24px rgba(15,23,42,.15); border:1.5px solid transparent; opacity:0; transform:translateY(10px); transition:opacity .2s,transform .2s; pointer-events:none; max-width:340px; }
  .toast.show    { opacity:1; transform:translateY(0); }
  .toast.success { background:#f0fdf4; border-color:#bbf7d0; color:#15803d; }
  .toast.error   { background:#fef2f2; border-color:#fecaca; color:#b91c1c; }

  .bspin { width:12px; height:12px; border:2px solid rgba(255,255,255,.3); border-top-color:#fff; border-radius:50%; animation:spin .6s linear infinite; display:inline-block; }
  @keyframes spin { to{transform:rotate(360deg)} }

  .info-panel { display:flex; gap:10px; background:#eff6ff; border:1px solid #bfdbfe; border-radius:10px; padding:12px 14px; margin-bottom:20px; font-size:12.5px; color:#1e40af; line-height:1.7; }
  .info-panel svg { flex-shrink:0; margin-top:2px; }

  .pagination-bar { display:flex; align-items:center; justify-content:space-between; padding:12px 16px; border-top:1px solid var(--border); flex-wrap:wrap; gap:8px; }
  .pg-info { font-size:12.5px; color:var(--text-4); }
  .pg-info strong { color:var(--text-2); }
  .pg-controls { display:flex; gap:4px; align-items:center; }
  .pg-btn { min-width:32px; height:32px; padding:0 8px; border-radius:7px; border:1.5px solid var(--border); background:var(--surface); font-size:12.5px; font-weight:500; cursor:pointer; color:var(--text-3); transition:all .15s; font-family:'DM Sans',sans-serif; display:flex; align-items:center; justify-content:center; }
  .pg-btn:hover:not(:disabled) { border-color:var(--accent); color:var(--accent); }
  .pg-btn.active { background:var(--accent); border-color:var(--accent); color:white; font-weight:700; }
  .pg-btn:disabled { opacity:.35; cursor:not-allowed; }

  /* Modal preview gambar */
  .img-modal-overlay { position:fixed; inset:0; background:rgba(10,14,28,.82); z-index:9998; display:none; align-items:center; justify-content:center; padding:24px; }
  .img-modal-overlay.open { display:flex; }
  .img-modal-overlay img { max-width:min(90vw,720px); max-height:85vh; border-radius:12px; box-shadow:0 20px 60px rgba(0,0,0,.5); }

  @media(max-width:760px) {
    .new-row-grid { grid-template-columns:1fr; }
    .new-thumb-box { width:100%; height:140px; }
  }
</style>
</head>
<body>
<div class="adm-shell">
  @include('admin.partials.sidebar', ['currentPage' => 'asdp_daftar', 'currentModule' => 'asdp'])

  <main class="adm-main">
    <div class="adm-content">

      <div class="adm-page-header">
        <div>
          <h1 class="adm-page-title">Daftar Lokasi ASDP</h1>
          <p class="adm-page-subtitle">Kelola nama, deskripsi, dan foto lokasi penyeberangan (tambangan) yang tampil di peta ASDP publik</p>
        </div>
        <button class="btn btn-secondary btn-sm" id="btnRefresh" onclick="loadData()">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
          Refresh
        </button>
      </div>

      <div class="info-panel">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <div>
          ID lokasi <strong>digenerate otomatis</strong>. Foto yang diupload <strong>otomatis dikompres &amp; diperkecil</strong> maksimal resolusi HD (1280×720) — upload foto sebesar apapun (termasuk 4K) tetap aman. Data di tabel ini dibaca lewat <strong>cache bersama</strong> dengan halaman peta ASDP publik (segar otomatis begitu ada perubahan). Untuk mengatur koordinat peta, buka menu <strong>Lokasi ASDP</strong>.
        </div>
      </div>

      <div class="toolbar">
        <div class="search-wrap">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input type="text" id="searchInput" class="search-input" placeholder="Cari nama atau deskripsi lokasi..." oninput="renderTable()"/>
        </div>
        <div class="count-badge">Total: <strong id="totalCount">—</strong> lokasi</div>
      </div>

      <div class="data-card">
        <table class="data-table">
          <thead>
            <tr>
              <th style="width:36px">#</th>
              <th style="width:100px">ID</th>
              <th style="width:100px">Foto</th>
              <th style="width:200px">Nama Lokasi</th>
              <th>Deskripsi</th>
              <th style="width:150px">Aksi</th>
            </tr>
          </thead>
          <tbody id="tableBody">
            <tr><td colspan="6" class="tbl-empty">
              <div class="skel" style="width:60%;margin:0 auto 8px"></div>
              <div class="skel" style="width:40%;margin:0 auto"></div>
            </td></tr>
          </tbody>
        </table>

        <div class="pagination-bar" id="paginationBar" style="display:none">
          <div class="pg-info" id="pgInfo"></div>
          <div class="pg-controls" id="pgControls"></div>
        </div>

        <!-- Tambah lokasi baru -->
        <div class="new-row-section">
          <div class="new-row-label">+ Tambah Lokasi ASDP Baru</div>
          <div class="new-row-grid">

            <div class="field-group">
              <span class="field-label">Foto</span>
              <div class="new-thumb-box" id="newThumbBox" onclick="document.getElementById('newImageInput').click()">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
              </div>
              <input type="file" id="newImageInput" accept="image/*" style="display:none" onchange="onNewImageSelected(event)"/>
              <div class="new-thumb-hint">Klik untuk pilih foto (opsional)</div>
            </div>

            <div class="field-group">
              <span class="field-label">Nama Lokasi</span>
              <input type="text" id="nName" class="edit-input" placeholder="Contoh: Tambangan Ngujang" autocomplete="off"/>
              <span class="field-label" style="margin-top:8px">Deskripsi</span>
              <textarea id="nDesc" class="edit-textarea" placeholder="Deskripsi singkat lokasi (opsional)" rows="3"></textarea>
            </div>

            <div class="field-group">
              <span class="field-label">&nbsp;</span>
              <div style="font-size:12px;color:var(--text-4);line-height:1.6">
                Koordinat (lat/lng) diatur belakangan lewat menu <strong>Lokasi ASDP</strong> setelah lokasi ini tersimpan.
              </div>
              <button class="btn-add" id="btnAdd" onclick="addRow()">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Tambah Lokasi
              </button>
            </div>

          </div>
        </div>
      </div>

    </div>
  </main>
</div>

<div class="toast" id="toast"></div>

<div class="img-modal-overlay" id="imgModalOverlay" onclick="closeImgModal()">
  <img id="imgModalImg" src="" alt="Preview foto lokasi ASDP"/>
</div>

<script>
const SB_URL_PUBLIC  = {!! json_encode(config('services.supabase.url')) !!};
const SB_ANON_PUBLIC = {!! json_encode(config('services.supabase.anon_key')) !!};
</script>
<script src="/assets/admin/angkutansekolah/sb-secure.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>
<script>
const sbRealtime = window.supabase ? window.supabase.createClient(SB_URL_PUBLIC, SB_ANON_PUBLIC) : null;
const UPLOAD_API = '/admin/api/asdp/upload-image';

// ── State ──────────────────────────────────────────────────────────
let allData     = [];
let currentPage = 1;
const PER_PAGE  = 15;

// File yang baru dipilih tapi BELUM diupload — diupload betulan hanya
// saat tombol Simpan/Tambah diklik, supaya klik "Batal" tidak pernah
// menimpa foto lama di server (lihat catatan di saveEdit/addRow).
const pendingFiles  = {};   // { [rowId]: File }
const removeFlags   = {};   // { [rowId]: true }  → tandai "hapus foto" saat simpan

// ── ID generator (16 karakter, sama seperti halaman admin lain) ────
function genId() {
  const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
  return Array.from({length:16}, () => chars[Math.floor(Math.random()*chars.length)]).join('');
}

function esc(s) {
  return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

function nowIso() { return new Date().toISOString(); }

function imgUrl(filename) {
  if (!filename) return '';
  if (/^https?:\/\//i.test(filename)) return filename;
  return '/uploads/asdp/' + filename;
}

let _toastTimer;
function showToast(msg, type='success') {
  const t = document.getElementById('toast');
  t.textContent = msg; t.className = `toast ${type} show`;
  clearTimeout(_toastTimer);
  _toastTimer = setTimeout(() => t.classList.remove('show'), 3200);
}

function openImgModal(src) {
  if (!src) return;
  document.getElementById('imgModalImg').src = src;
  document.getElementById('imgModalOverlay').classList.add('open');
}
function closeImgModal() { document.getElementById('imgModalOverlay').classList.remove('open'); }

// ── Load ───────────────────────────────────────────────────────────
async function loadData() {
  document.getElementById('tableBody').innerHTML =
    `<tr><td colspan="6" class="tbl-empty"><div class="skel" style="width:55%;margin:0 auto 8px"></div><div class="skel" style="width:38%;margin:0 auto"></div></td></tr>`;
  document.getElementById('paginationBar').style.display = 'none';
  try {
    // Sengaja pakai query yang SAMA PERSIS dengan halaman publik peta ASDP
    // (pages/asdp/index.html: table=list_tambangan&order=name.asc, tanpa
    // parameter select → default '*') supaya cache-nya SATU FILE YANG SAMA
    // dipakai bersama oleh admin & publik. Cache ini permanen (TTL aman 24
    // jam) dan otomatis dihapus tiap ada sbPost/sbPatch/sbDelete ke tabel
    // ini (lihat _invalidateServerCache di sb-secure.js) — jadi meskipun
    // dibaca dari cache, datanya tetap segar begitu ada perubahan nyata,
    // tanpa perlu tiap admin/tab menembak Supabase langsung satu-satu.
    allData = await sbGetShared('list_tambangan', 'order=name.asc');
    document.getElementById('totalCount').textContent = allData.length;
    currentPage = 1;
    renderTable();
  } catch(e) {
    document.getElementById('tableBody').innerHTML =
      `<tr><td colspan="6" class="tbl-empty" style="color:#ef4444">Gagal memuat: ${esc(e.message)}</td></tr>`;
  }
}

function getFiltered() {
  const q = document.getElementById('searchInput').value.toLowerCase().trim();
  if (!q) return allData;
  return allData.filter(r =>
    (r.name||'').toLowerCase().includes(q) ||
    (r.description||'').toLowerCase().includes(q)
  );
}

// ── Render ─────────────────────────────────────────────────────────
function renderTable() {
  const filtered = getFiltered();
  const total    = filtered.length;
  const pages    = Math.max(1, Math.ceil(total / PER_PAGE));
  if (currentPage > pages) currentPage = pages;

  const start = (currentPage - 1) * PER_PAGE;
  const slice = filtered.slice(start, start + PER_PAGE);
  const tbody = document.getElementById('tableBody');

  if (!slice.length) {
    tbody.innerHTML = `<tr><td colspan="6" class="tbl-empty">Tidak ada data${document.getElementById('searchInput').value ? ' yang cocok' : ''}.</td></tr>`;
    document.getElementById('paginationBar').style.display = 'none';
    return;
  }

  tbody.innerHTML = slice.map((row, i) => {
    const hasImg = !!row.image;
    const src    = imgUrl(row.image);
    return `
    <tr id="row_${esc(row.id)}" data-id="${esc(row.id)}">
      <td style="color:var(--text-4);font-size:12px">${start + i + 1}</td>
      <td><span class="id-badge">${esc(row.id)}</span></td>

      <!-- Foto -->
      <td>
        <div class="thumb-wrap vv-thumb-view">
          ${hasImg
            ? `<img src="${esc(src)}" class="foto-thumb" onclick="openImgModal('${esc(src)}')" loading="lazy">`
            : `<div class="thumb-placeholder"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg></div>`
          }
        </div>
        <div class="vv-edit" style="display:none">
          <div class="thumb-wrap">
            <img src="${hasImg ? esc(src) : ''}" class="foto-thumb ei-thumb-preview" style="${hasImg ? '' : 'display:none'}" onclick="document.getElementById('fileInput_${esc(row.id)}').click()">
            <div class="thumb-placeholder ei-thumb-placeholder" style="${hasImg ? 'display:none' : ''}" onclick="document.getElementById('fileInput_${esc(row.id)}').click()">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
            </div>
          </div>
          <input type="file" id="fileInput_${esc(row.id)}" accept="image/*" style="display:none" onchange="onEditImageSelected(event, '${esc(row.id)}')"/>
          <div class="thumb-edit-btns">
            <button type="button" class="btn-thumb-action" onclick="document.getElementById('fileInput_${esc(row.id)}').click()">Ganti Foto</button>
            <button type="button" class="btn-thumb-action danger ei-remove-btn" style="${hasImg ? '' : 'display:none'}" onclick="markRemoveImage('${esc(row.id)}')">Hapus Foto</button>
          </div>
        </div>
      </td>

      <!-- Nama -->
      <td>
        <span class="vv-text vv-name">${esc(row.name)}</span>
        <input type="text" class="edit-input vv-edit ei-name" style="display:none" value="${esc(row.name)}" maxlength="150" autocomplete="off"/>
      </td>

      <!-- Deskripsi -->
      <td>
        <span class="vv-text desc-view ${row.description ? '' : 'empty'}">${row.description ? esc(row.description) : '— tidak ada deskripsi —'}</span>
        <textarea class="edit-textarea vv-edit ei-desc" style="display:none" rows="3" maxlength="1000">${esc(row.description || '')}</textarea>
      </td>

      <!-- Aksi -->
      <td class="td-actions">
        <div class="vv-view-btns" style="display:flex;gap:5px">
          <button class="btn-edit" onclick="startEdit('${esc(row.id)}')">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            Edit
          </button>
          <button class="btn-del" onclick="deleteRow('${esc(row.id)}', '${esc(row.name)}')">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
            Hapus
          </button>
        </div>
        <div class="vv-edit-btns" style="display:none;gap:5px;flex-direction:column">
          <button class="btn-save-row" onclick="saveEdit('${esc(row.id)}')">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            Simpan
          </button>
          <button class="btn-cancel-row" onclick="cancelEdit('${esc(row.id)}')">Batal</button>
        </div>
      </td>
    </tr>`;
  }).join('');

  const bar = document.getElementById('paginationBar');
  bar.style.display = total > PER_PAGE ? 'flex' : 'none';
  document.getElementById('pgInfo').innerHTML =
    `Menampilkan <strong>${start+1}–${Math.min(start+PER_PAGE, total)}</strong> dari <strong>${total}</strong>`;

  const ctrl = document.getElementById('pgControls');
  let btns = `<button class="pg-btn" onclick="goPage(${currentPage-1})" ${currentPage===1?'disabled':''}>‹</button>`;
  for (let p = 1; p <= pages; p++) {
    if (pages > 7 && Math.abs(p - currentPage) > 2 && p !== 1 && p !== pages) {
      if (p === currentPage - 3 || p === currentPage + 3) btns += `<button class="pg-btn" style="border:none;cursor:default">…</button>`;
      continue;
    }
    btns += `<button class="pg-btn${p===currentPage?' active':''}" onclick="goPage(${p})">${p}</button>`;
  }
  btns += `<button class="pg-btn" onclick="goPage(${currentPage+1})" ${currentPage===pages?'disabled':''}>›</button>`;
  ctrl.innerHTML = btns;
}

function goPage(p) {
  const pages = Math.ceil(getFiltered().length / PER_PAGE);
  if (p < 1 || p > pages) return;
  currentPage = p;
  renderTable();
}

// ── Edit mode ──────────────────────────────────────────────────────
function startEdit(id) {
  delete pendingFiles[id];
  delete removeFlags[id];
  const row = document.getElementById(`row_${id}`);
  row.querySelectorAll('.vv-text, .vv-thumb-view').forEach(el => el.style.display = 'none');
  row.querySelectorAll('.vv-edit').forEach(el => { el.style.display = ''; });
  row.querySelector('.vv-view-btns').style.display = 'none';
  row.querySelector('.vv-edit-btns').style.display = 'flex';
  row.querySelector('.ei-name').focus();
}

function cancelEdit(id) {
  delete pendingFiles[id];
  delete removeFlags[id];
  const row  = document.getElementById(`row_${id}`);
  const orig = allData.find(r => String(r.id) === String(id));
  if (orig) {
    row.querySelector('.ei-name').value = orig.name;
    row.querySelector('.ei-desc').value = orig.description || '';
    const preview = row.querySelector('.ei-thumb-preview');
    const placeholder = row.querySelector('.ei-thumb-placeholder');
    if (orig.image) {
      preview.src = imgUrl(orig.image);
      preview.style.display = '';
      placeholder.style.display = 'none';
      row.querySelector('.ei-remove-btn').style.display = '';
    } else {
      preview.style.display = 'none';
      placeholder.style.display = '';
      row.querySelector('.ei-remove-btn').style.display = 'none';
    }
  }
  row.querySelectorAll('.vv-text, .vv-thumb-view').forEach(el => el.style.display = '');
  row.querySelectorAll('.vv-edit').forEach(el => el.style.display = 'none');
  row.querySelector('.vv-view-btns').style.display = 'flex';
  row.querySelector('.vv-edit-btns').style.display = 'none';
}

// Preview lokal (belum diupload) begitu file dipilih di baris edit
function onEditImageSelected(evt, id) {
  const file = evt.target.files && evt.target.files[0];
  if (!file) return;
  pendingFiles[id] = file;
  delete removeFlags[id];

  const row = document.getElementById(`row_${id}`);
  const preview     = row.querySelector('.ei-thumb-preview');
  const placeholder = row.querySelector('.ei-thumb-placeholder');
  const reader = new FileReader();
  reader.onload = e => {
    preview.src = e.target.result;
    preview.style.display = '';
    placeholder.style.display = 'none';
    row.querySelector('.ei-remove-btn').style.display = '';
  };
  reader.readAsDataURL(file);
}

function markRemoveImage(id) {
  removeFlags[id] = true;
  delete pendingFiles[id];
  const row = document.getElementById(`row_${id}`);
  row.querySelector('.ei-thumb-preview').style.display = 'none';
  row.querySelector('.ei-thumb-placeholder').style.display = '';
  row.querySelector('.ei-remove-btn').style.display = 'none';
  document.getElementById(`fileInput_${id}`).value = '';
}

// Upload gambar ke server (kompres + resize otomatis di sisi server).
// `name` dikirim karena nama file yang disimpan di server sekarang
// mengikuti Nama Lokasi (bukan id lagi) — lihat upload_image.php.
async function uploadImageFor(id, file, oldImage, name) {
  const fd = new FormData();
  fd.append('action', 'upload');
  fd.append('id', id);
  fd.append('name', name);
  fd.append('image', file);
  fd.append('old_image', oldImage || '');
  const r = await fetch(UPLOAD_API, { method:'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }, body: fd });
  const j = await r.json().catch(() => ({}));
  if (!r.ok) throw new Error(j.error || `Upload gagal (HTTP ${r.status})`);
  return j.filename;
}

// Sinkronkan nama file foto yang SUDAH ADA dengan Nama Lokasi baru,
// dipakai saat admin ganti nama tapi TIDAK memilih foto baru — supaya
// nama file tetap konsisten mengikuti nama lokasi tanpa perlu upload ulang.
async function renameImageFor(oldFilename, name) {
  const fd = new FormData();
  fd.append('action', 'rename');
  fd.append('old_filename', oldFilename);
  fd.append('name', name);
  const r = await fetch(UPLOAD_API, { method:'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }, body: fd });
  const j = await r.json().catch(() => ({}));
  if (!r.ok) throw new Error(j.error || `Rename gagal (HTTP ${r.status})`);
  return j.filename;
}

async function deleteImageFile(filename) {
  if (!filename) return;
  try {
    const fd = new FormData();
    fd.append('action', 'delete');
    fd.append('filename', filename);
    await fetch(UPLOAD_API, { method:'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }, body: fd });
  } catch(e) { console.warn('Gagal hapus file gambar lama:', e); }
}

async function saveEdit(id) {
  const row  = document.getElementById(`row_${id}`);
  const rec  = allData.find(r => String(r.id) === String(id));
  const name = row.querySelector('.ei-name').value.trim();
  const desc = row.querySelector('.ei-desc').value.trim();

  if (!name) { showToast('Nama lokasi tidak boleh kosong', 'error'); row.querySelector('.ei-name').focus(); return; }

  const btnSave = row.querySelector('.btn-save-row');
  btnSave.disabled = true;
  btnSave.innerHTML = '<span class="bspin"></span>';

  try {
    let newImage = rec.image || '';

    if (pendingFiles[id]) {
      // Foto baru dipilih → upload, nama file otomatis mengikuti Nama
      // Lokasi terbaru (server yang tentukan, termasuk anti-tabrakan nama).
      newImage = await uploadImageFor(id, pendingFiles[id], rec.image || '', name);
    } else if (removeFlags[id]) {
      await deleteImageFile(rec.image || '');
      newImage = '';
    } else if (rec.image && name !== rec.name) {
      // Tidak ada foto baru, tapi Nama Lokasi berubah dan baris ini sudah
      // punya foto → rename filenya di server supaya tetap sinkron dengan
      // nama terbaru (tanpa perlu upload ulang gambarnya).
      newImage = await renameImageFor(rec.image, name);
    }

    await sbPatch('list_tambangan', `id=eq.${encodeURIComponent(id)}`, {
      name, description: desc, image: newImage, updated: nowIso(),
    });

    rec.name = name; rec.description = desc; rec.image = newImage;
    delete pendingFiles[id]; delete removeFlags[id];

    showToast(`✓ "${name}" berhasil diperbarui`);
    renderTable();
  } catch(e) {
    showToast(`Gagal menyimpan: ${e.message}`, 'error');
    btnSave.disabled = false;
    btnSave.innerHTML = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> Simpan';
  }
}

// ── Hapus baris ────────────────────────────────────────────────────
async function deleteRow(id, name) {
  const rec = allData.find(r => String(r.id) === String(id));
  if (!(await confirmDangerModal(`Hapus lokasi "${name}"?\n\nFoto yang tersimpan di server juga akan ikut terhapus.`))) return;

  try {
    await sbDelete('list_tambangan', `id=eq.${encodeURIComponent(id)}`);
    if (rec && rec.image) await deleteImageFile(rec.image);
    allData = allData.filter(r => String(r.id) !== String(id));
    document.getElementById('totalCount').textContent = allData.length;
    renderTable();
    showToast(`🗑️ Lokasi "${name}" dihapus`);
  } catch(e) {
    showToast(`Gagal menghapus: ${e.message}`, 'error');
  }
}

// ── Tambah baru ────────────────────────────────────────────────────
function onNewImageSelected(evt) {
  const file = evt.target.files && evt.target.files[0];
  if (!file) return;
  pendingFiles['NEW'] = file;
  const box = document.getElementById('newThumbBox');
  const reader = new FileReader();
  reader.onload = e => { box.innerHTML = `<img src="${e.target.result}">`; };
  reader.readAsDataURL(file);
}

async function addRow() {
  const name = document.getElementById('nName').value.trim();
  const desc = document.getElementById('nDesc').value.trim();

  if (!name) { showToast('Nama lokasi tidak boleh kosong', 'error'); document.getElementById('nName').focus(); return; }

  const btn = document.getElementById('btnAdd');
  btn.disabled = true;
  btn.innerHTML = '<span class="bspin"></span> Menyimpan...';

  try {
    const newId = genId();
    let image = '';
    if (pendingFiles['NEW']) {
      image = await uploadImageFor(newId, pendingFiles['NEW'], '', name);
    }

    const ts = nowIso();
    await sbPost('list_tambangan', {
      id: newId, name, description: desc, image,
      lat: '', lng: '', created: ts, updated: ts,
    });

    allData.push({ id:newId, name, description:desc, image, lat:'', lng:'', created:ts, updated:ts });
    allData.sort((a,b) => (a.name||'').localeCompare(b.name||''));
    document.getElementById('totalCount').textContent = allData.length;

    document.getElementById('nName').value = '';
    document.getElementById('nDesc').value = '';
    document.getElementById('newImageInput').value = '';
    delete pendingFiles['NEW'];
    document.getElementById('newThumbBox').innerHTML =
      `<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>`;

    const idx = allData.findIndex(r => r.id === newId);
    currentPage = Math.floor(idx / PER_PAGE) + 1;
    renderTable();
    showToast(`✓ Lokasi "${name}" berhasil ditambahkan`);
  } catch(e) {
    showToast(`Gagal menambahkan: ${e.message}`, 'error');
  } finally {
    btn.disabled = false;
    btn.innerHTML = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Tambah Lokasi';
  }
}

// ── Realtime + init ────────────────────────────────────────────────
let _rtReloadTimer = null;
function scheduleReload() {
  clearTimeout(_rtReloadTimer);
  _rtReloadTimer = setTimeout(() => { loadData(); }, 400);
}
if (sbRealtime) {
  sbRealtime
    .channel('admin-asdp-daftar')
    .on('postgres_changes', { event: '*', schema: 'public', table: 'list_tambangan' }, scheduleReload)
    .subscribe();
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeImgModal(); });

loadData();
</script>
</body>
</html>
