<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
<meta name="csrf-token" content="{{ csrf_token() }}"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Data Domisili — Admin</title>
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

    /* ── Counter badge ── */
    .count-badge { display:inline-flex; align-items:center; gap:6px; padding:6px 14px; background:var(--surface-2); border:1.5px solid var(--border); border-radius:10px; font-size:13px; color:var(--text-2); font-weight:500; }
    .count-badge strong { color:var(--accent); font-weight:700; }

    /* ── Table card ── */
    .data-card { background:var(--surface); border:1.5px solid var(--border); border-radius:14px; overflow:hidden; }
    .data-table { width:100%; border-collapse:collapse; }
    .data-table thead tr { background:var(--surface-2); border-bottom:1.5px solid var(--border); }
    .data-table th { padding:10px 16px; text-align:left; font-size:10.5px; font-weight:700; letter-spacing:.07em; text-transform:uppercase; color:var(--text-3); white-space:nowrap; }
    .data-table tbody tr { border-bottom:1px solid var(--border); transition:background .12s; }
    .data-table tbody tr:last-child { border-bottom:none; }
    .data-table tbody tr:hover { background:var(--surface-2); }
    .data-table td { padding:10px 16px; font-size:13px; color:var(--text-1); vertical-align:middle; }

    /* ID badge */
    .id-badge { display:inline-block; font-family:'DM Mono',monospace; font-size:11.5px; background:var(--surface-2); border:1px solid var(--border); color:var(--text-3); padding:2px 8px; border-radius:5px; }

    /* Kecamatan badge */
    .kec-badge { display:inline-block; background:#f0fdf4; border:1px solid #bbf7d0; color:#15803d; padding:2px 10px; border-radius:6px; font-size:12.5px; font-weight:500; }

    /* Inline edit */
    .edit-input { width:100%; padding:6px 10px; border:1.5px solid var(--border); border-radius:7px; font-family:'DM Sans',sans-serif; font-size:13px; color:var(--text-1); background:var(--surface); outline:none; transition:border-color .15s,box-shadow .15s; box-sizing:border-box; }
    .edit-input:focus { border-color:var(--accent); box-shadow:0 0 0 3px var(--accent-glow); }

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
    .new-row-section { border-top:1.5px dashed var(--border); padding:16px 16px 14px; background:var(--surface-2); }
    .new-row-label   { font-size:11px; font-weight:700; letter-spacing:.06em; text-transform:uppercase; color:var(--text-3); margin-bottom:12px; }
    .new-row-grid    { display:grid; grid-template-columns:1fr auto; gap:12px; align-items:end; }
    .field-group     { display:flex; flex-direction:column; gap:4px; }
    .field-label     { font-size:10.5px; font-weight:700; color:var(--text-3); letter-spacing:.05em; text-transform:uppercase; }

    .btn-add { display:flex; align-items:center; gap:6px; padding:8px 16px; background:var(--accent); color:#fff; border:none; border-radius:8px; font-family:'DM Sans',sans-serif; font-size:13px; font-weight:600; cursor:pointer; transition:all .15s; white-space:nowrap; height:38px; align-self:flex-end; }
    .btn-add:hover    { background:#2563eb; box-shadow:0 4px 14px rgba(59,130,246,.3); }
    .btn-add:disabled { background:var(--text-4); cursor:not-allowed; }

    /* Empty / skeleton */
    .tbl-empty { text-align:center; padding:32px; color:var(--text-4); font-size:13px; }
    .skel { background:linear-gradient(90deg,var(--border) 25%,var(--surface-2) 50%,var(--border) 75%); background-size:200%; animation:shimmer 1.4s infinite; border-radius:5px; height:14px; }
    @keyframes shimmer { 0%{background-position:200% 0}100%{background-position:-200% 0} }

    /* Toast */
    .toast { position:fixed; bottom:24px; right:24px; z-index:9999; display:flex; align-items:center; gap:8px; padding:11px 16px; border-radius:10px; font-size:13px; font-weight:500; box-shadow:0 8px 24px rgba(15,23,42,.15); border:1.5px solid transparent; opacity:0; transform:translateY(10px); transition:opacity .2s,transform .2s; pointer-events:none; max-width:340px; }
    .toast.show    { opacity:1; transform:translateY(0); }
    .toast.success { background:#f0fdf4; border-color:#bbf7d0; color:#15803d; }
    .toast.error   { background:#fef2f2; border-color:#fecaca; color:#b91c1c; }

    /* Spinner */
    .bspin { width:12px; height:12px; border:2px solid rgba(255,255,255,.3); border-top-color:#fff; border-radius:50%; animation:spin .6s linear infinite; display:inline-block; }
    @keyframes spin { to{transform:rotate(360deg)} }

    /* Info panel */
    .info-panel { display:flex; gap:10px; background:#f0fdf4; border:1px solid #bbf7d0; border-radius:10px; padding:12px 14px; margin-bottom:20px; font-size:12.5px; color:#14532d; line-height:1.7; }
    .info-panel svg { flex-shrink:0; margin-top:2px; }

    /* Pagination */
    .pagination-bar { display:flex; align-items:center; justify-content:space-between; padding:12px 16px; border-top:1px solid var(--border); flex-wrap:wrap; gap:8px; }
    .pg-info { font-size:12.5px; color:var(--text-4); }
    .pg-info strong { color:var(--text-2); }
    .pg-controls { display:flex; gap:4px; align-items:center; }
    .pg-btn { min-width:32px; height:32px; padding:0 8px; border-radius:7px; border:1.5px solid var(--border); background:var(--surface); font-size:12.5px; font-weight:500; cursor:pointer; color:var(--text-3); transition:all .15s; font-family:'DM Sans',sans-serif; display:flex; align-items:center; justify-content:center; }
    .pg-btn:hover:not(:disabled) { border-color:var(--accent); color:var(--accent); }
    .pg-btn.active { background:var(--accent); border-color:var(--accent); color:white; font-weight:700; }
    .pg-btn:disabled { opacity:.35; cursor:not-allowed; }

    /* Preview hint for title-case */
    .preview-hint { font-size:11.5px; color:var(--text-4); margin-top:4px; min-height:16px; }
    .preview-hint span { color:#15803d; font-weight:600; }

    @media(max-width:600px) {
      .new-row-grid { grid-template-columns:1fr; }
    }
  </style>
</head>
<body>
<div class="adm-shell">
  @include('admin.partials.sidebar', ['currentPage' => 'update_domisili', 'currentModule' => 'angkutansekolah'])

  <main class="adm-main">
    <div class="adm-content">

      <div class="adm-page-header">
        <div>
          <h1 class="adm-page-title">Data Domisili</h1>
          <p class="adm-page-subtitle">Kelola daftar kecamatan — huruf depan otomatis besar (Title Case)</p>
        </div>
        <button class="btn btn-secondary btn-sm" id="btnRefresh" onclick="loadData()">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
          Refresh
        </button>
      </div>

      <div class="info-panel">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <div>
          ID kecamatan <strong>digenerate otomatis</strong>. Nama kecamatan dikonversi ke <strong>Huruf Depan Besar</strong> (Title Case) secara otomatis saat menyimpan.
        </div>
      </div>

      <!-- Toolbar -->
      <div class="toolbar">
        <div class="search-wrap">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input type="text" id="searchInput" class="search-input" placeholder="Cari nama kecamatan..." oninput="renderTable()"/>
        </div>
        <div class="count-badge">
          Total: <strong id="totalCount">—</strong> kecamatan
        </div>
      </div>

      <!-- Table card -->
      <div class="data-card">
        <table class="data-table">
          <thead>
            <tr>
              <th style="width:36px">#</th>
              <th style="width:110px">ID</th>
              <th>Nama Kecamatan</th>
              <th style="width:150px">Aksi</th>
            </tr>
          </thead>
          <tbody id="tableBody">
            <tr><td colspan="4" class="tbl-empty">
              <div class="skel" style="width:60%;margin:0 auto 8px"></div>
              <div class="skel" style="width:40%;margin:0 auto"></div>
            </td></tr>
          </tbody>
        </table>

        <!-- Pagination -->
        <div class="pagination-bar" id="paginationBar" style="display:none">
          <div class="pg-info" id="pgInfo"></div>
          <div class="pg-controls" id="pgControls"></div>
        </div>

        <!-- Add new row -->
        <div class="new-row-section">
          <div class="new-row-label">+ Tambah Kecamatan Baru</div>
          <div class="new-row-grid">
            <div class="field-group">
              <span class="field-label">Nama Kecamatan</span>
              <input type="text" id="nKecamatan" class="edit-input" placeholder="Contoh: Kedungwaru"
                autocomplete="off"
                oninput="updatePreview(this)"
                onkeydown="if(event.key==='Enter')addRow()"/>
              <div class="preview-hint" id="previewHint"></div>
            </div>
            <button class="btn-add" id="btnAdd" onclick="addRow()">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
              Tambah
            </button>
          </div>
        </div>
      </div>

    </div>
  </main>
</div>

<div class="toast" id="toast"></div>

<script>
const SB_URL_PUBLIC  = {!! json_encode(config('services.supabase.url')) !!};
const SB_ANON_PUBLIC = {!! json_encode(config('services.supabase.anon_key')) !!};
</script>
<script src="/assets/admin/angkutansekolah/sb-secure.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>
<script>
// service_role key tidak lagi dikirim ke browser (lihat admin/api/angkutansekolah/db.php).
const sbRealtime = window.supabase ? window.supabase.createClient(SB_URL_PUBLIC, SB_ANON_PUBLIC) : null;

// ── State ──────────────────────────────────────────────────────────
let allData    = [];
let currentPage = 1;
const PER_PAGE  = 20;

// ── ID generator (5-digit numeric) ────────────────────────────────
function genId() {
  return String(Math.floor(10000 + Math.random() * 89999));
}

// ── Title Case ─────────────────────────────────────────────────────
function toTitleCase(str) {
  return str.trim().replace(/\w\S*/g, w => w.charAt(0).toUpperCase() + w.slice(1).toLowerCase());
}

// ── Escape HTML ────────────────────────────────────────────────────
function esc(s) {
  return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// ── Toast ──────────────────────────────────────────────────────────
let _toastTimer;
function showToast(msg, type='success') {
  const t = document.getElementById('toast');
  t.textContent = msg; t.className = `toast ${type} show`;
  clearTimeout(_toastTimer);
  _toastTimer = setTimeout(() => t.classList.remove('show'), 3200);
}

// ── Preview hint (shows title-case result as user types) ───────────
function updatePreview(input) {
  const raw = input.value;
  const hint = document.getElementById('previewHint');
  if (!raw.trim()) { hint.innerHTML = ''; return; }
  const tc = toTitleCase(raw);
  hint.innerHTML = tc !== raw.trim() ? `Akan disimpan sebagai: <span>${esc(tc)}</span>` : '';
}

// ── Load ───────────────────────────────────────────────────────────
async function loadData() {
  document.getElementById('tableBody').innerHTML =
    `<tr><td colspan="4" class="tbl-empty"><div class="skel" style="width:55%;margin:0 auto 8px"></div><div class="skel" style="width:38%;margin:0 auto"></div></td></tr>`;
  document.getElementById('paginationBar').style.display = 'none';
  try {
    allData = await sbGetShared('domisili', 'select=id,kecamatan&order=kecamatan.asc');
    document.getElementById('totalCount').textContent = allData.length;
    currentPage = 1;
    renderTable();
  } catch(e) {
    document.getElementById('tableBody').innerHTML =
      `<tr><td colspan="4" class="tbl-empty" style="color:#ef4444">Gagal memuat: ${esc(e.message)}</td></tr>`;
  }
}

// ── Filter + Paginate ──────────────────────────────────────────────
function getFiltered() {
  const q = document.getElementById('searchInput').value.toLowerCase().trim();
  if (!q) return allData;
  return allData.filter(r => (r.kecamatan||'').toLowerCase().includes(q) || String(r.id).includes(q));
}

// ── Render Table ───────────────────────────────────────────────────
function renderTable() {
  const filtered = getFiltered();
  const total    = filtered.length;
  const pages    = Math.max(1, Math.ceil(total / PER_PAGE));
  if (currentPage > pages) currentPage = pages;

  const start = (currentPage - 1) * PER_PAGE;
  const slice = filtered.slice(start, start + PER_PAGE);

  const tbody = document.getElementById('tableBody');

  if (!slice.length) {
    tbody.innerHTML = `<tr><td colspan="4" class="tbl-empty">Tidak ada data${document.getElementById('searchInput').value ? ' yang cocok' : ''}.</td></tr>`;
    document.getElementById('paginationBar').style.display = 'none';
    return;
  }

  tbody.innerHTML = slice.map((row, i) => `
    <tr id="row_${esc(row.id)}" data-id="${esc(row.id)}">
      <td style="color:var(--text-4);font-size:12px">${start + i + 1}</td>
      <td><span class="id-badge">${esc(row.id)}</span></td>

      <!-- View mode -->
      <td>
        <span class="vv-text kec-badge">${esc(row.kecamatan)}</span>
        <input type="text" class="edit-input vv-edit" style="display:none"
          value="${esc(row.kecamatan)}" maxlength="80"
          autocomplete="off"/>
      </td>

      <!-- Actions -->
      <td class="td-actions">
        <div class="vv-view-btns" style="display:flex;gap:5px">
          <button class="btn-edit" onclick="startEdit('${esc(row.id)}')">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            Edit
          </button>
          <button class="btn-del" onclick="deleteRow('${esc(row.id)}', '${esc(row.kecamatan)}')">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
            Hapus
          </button>
        </div>
        <div class="vv-edit-btns" style="display:none;gap:5px">
          <button class="btn-save-row" onclick="saveRow('${esc(row.id)}')">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            Simpan
          </button>
          <button class="btn-cancel-row" onclick="cancelEdit('${esc(row.id)}')">Batal</button>
        </div>
      </td>
    </tr>
  `).join('');

  // Pagination
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

// ── Inline Edit ────────────────────────────────────────────────────
function startEdit(id) {
  const row = document.getElementById(`row_${id}`);
  row.querySelectorAll('.vv-text').forEach(el => el.style.display = 'none');
  row.querySelectorAll('.vv-edit').forEach(el => { el.style.display = ''; el.focus(); el.select(); });
  row.querySelector('.vv-view-btns').style.display = 'none';
  row.querySelector('.vv-edit-btns').style.display = 'flex';
}

function cancelEdit(id) {
  const row  = document.getElementById(`row_${id}`);
  const orig = allData.find(r => String(r.id) === String(id));
  if (orig) row.querySelector('.vv-edit').value = orig.kecamatan;
  row.querySelectorAll('.vv-text').forEach(el => el.style.display = '');
  row.querySelectorAll('.vv-edit').forEach(el => el.style.display = 'none');
  row.querySelector('.vv-view-btns').style.display = 'flex';
  row.querySelector('.vv-edit-btns').style.display = 'none';
}

async function saveRow(id) {
  const row   = document.getElementById(`row_${id}`);
  const input = row.querySelector('.vv-edit');
  const val   = toTitleCase(input.value);
  if (!val) { showToast('Nama kecamatan tidak boleh kosong.', 'error'); input.focus(); return; }

  const btn = row.querySelector('.btn-save-row');
  btn.innerHTML = '<span class="bspin"></span>';
  btn.disabled = true;

  try {
    await sbPatch('domisili', `id=eq.${encodeURIComponent(id)}`, { kecamatan: val });
    const idx = allData.findIndex(r => String(r.id) === String(id));
    if (idx !== -1) allData[idx].kecamatan = val;
    row.querySelector('.vv-text').textContent = val;
    cancelEdit(id);
    showToast('✓ Data kecamatan berhasil diperbarui.');
  } catch(e) {
    showToast(`Gagal menyimpan: ${e.message}`, 'error');
    btn.innerHTML = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> Simpan';
    btn.disabled = false;
  }
}

// ── Delete ─────────────────────────────────────────────────────────
async function deleteRow(id, nama) {
  if (!(await confirmDangerModal(`Hapus kecamatan "${nama}"?\n\nPastikan tidak ada siswa yang masih terdaftar di kecamatan ini.`))) return;
  try {
    await sbDelete('domisili', `id=eq.${encodeURIComponent(id)}`);
    allData = allData.filter(r => String(r.id) !== String(id));
    document.getElementById('totalCount').textContent = allData.length;
    renderTable();
    showToast('✓ Kecamatan berhasil dihapus.');
  } catch(e) {
    showToast(`Gagal menghapus: ${e.message}`, 'error');
  }
}

// ── Add new row ────────────────────────────────────────────────────
async function addRow() {
  const input = document.getElementById('nKecamatan');
  const val   = toTitleCase(input.value);
  if (!val) { showToast('Nama kecamatan tidak boleh kosong.', 'error'); input.focus(); return; }

  // Check duplicate
  if (allData.some(r => r.kecamatan.toLowerCase() === val.toLowerCase())) {
    showToast(`Kecamatan "${val}" sudah ada.`, 'error'); input.focus(); return;
  }

  const btn = document.getElementById('btnAdd');
  btn.innerHTML = '<span class="bspin"></span> Menyimpan...';
  btn.disabled = true;

  try {
    const newId = genId();
    await sbPost('domisili', { id: newId, kecamatan: val });
    allData.push({ id: newId, kecamatan: val });
    allData.sort((a,b) => a.kecamatan.localeCompare(b.kecamatan));
    document.getElementById('totalCount').textContent = allData.length;
    input.value = '';
    document.getElementById('previewHint').innerHTML = '';

    // Jump to the page containing the new record
    const idx = allData.findIndex(r => r.id === newId);
    currentPage = Math.floor(idx / PER_PAGE) + 1;
    renderTable();
    showToast(`✓ Kecamatan "${val}" berhasil ditambahkan.`);
  } catch(e) {
    showToast(`Gagal menambahkan: ${e.message}`, 'error');
  } finally {
    btn.innerHTML = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Tambah';
    btn.disabled = false;
  }
}

// ── Init ───────────────────────────────────────────────────────────
let _rtReloadTimer = null;
function scheduleReload() {
  clearTimeout(_rtReloadTimer);
  _rtReloadTimer = setTimeout(() => { loadData(); }, 400);
}
if (sbRealtime) {
  sbRealtime
    .channel('admin-data-domisili')
    .on('postgres_changes', { event: '*', schema: 'public', table: 'domisili' }, scheduleReload)
    .subscribe();
}

loadData();
</script>
</body>
</html>
