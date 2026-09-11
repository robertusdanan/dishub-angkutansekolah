<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
<meta name="csrf-token" content="{{ csrf_token() }}"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Data Trayek — Admin</title>
  <link rel="icon" href="/favicon.ico"/>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/admin/admin-shell.css">
  <style>
    .adm-shell { display:flex !important; }
    .adm-main  { flex:1; min-width:0; }

    /* ── Tab bar ── */
    .tab-bar { display:flex; gap:4px; background:var(--surface-2); border:1.5px solid var(--border); border-radius:12px; padding:4px; margin-bottom:22px; width:fit-content; }
    .tab-btn { display:flex; align-items:center; gap:7px; padding:8px 18px; border:none; border-radius:8px; background:transparent; font-family:'DM Sans',sans-serif; font-size:13.5px; font-weight:500; color:var(--text-3); cursor:pointer; transition:all .15s; }
    .tab-btn:hover { background:var(--surface); color:var(--text-1); }
    .tab-btn.active { background:var(--navy); color:white; font-weight:600; box-shadow:0 2px 8px rgba(15,23,42,.18); }
    .tab-count { background:rgba(255,255,255,.15); padding:1px 7px; border-radius:99px; font-size:11px; font-weight:700; }
    .tab-btn:not(.active) .tab-count { background:var(--border); color:var(--text-3); }

    /* ── Toolbar ── */
    .toolbar { display:flex; gap:10px; align-items:center; margin-bottom:16px; flex-wrap:wrap; }
    .search-wrap { position:relative; flex:1; min-width:160px; max-width:280px; }
    .search-wrap svg { position:absolute; left:11px; top:50%; transform:translateY(-50%); color:var(--text-4); pointer-events:none; }
    .search-input { width:100%; padding:8px 11px 8px 34px; border:1.5px solid var(--border); border-radius:9px; font-family:'DM Sans',sans-serif; font-size:13px; color:var(--text-1); background:var(--surface-2); outline:none; transition:border-color .15s,box-shadow .15s; }
    .search-input:focus { border-color:var(--accent); box-shadow:0 0 0 3px var(--accent-glow); background:#fff; }

    /* ── Table card ── */
    .trayek-card { background:var(--surface); border:1.5px solid var(--border); border-radius:14px; overflow:hidden; }
    .trayek-table { width:100%; border-collapse:collapse; }
    .trayek-table thead tr { background:var(--surface-2); border-bottom:1.5px solid var(--border); }
    .trayek-table th { padding:10px 16px; text-align:left; font-size:10.5px; font-weight:700; letter-spacing:.07em; text-transform:uppercase; color:var(--text-3); white-space:nowrap; }
    .trayek-table tbody tr { border-bottom:1px solid var(--border); transition:background .12s; }
    .trayek-table tbody tr:last-child { border-bottom:none; }
    .trayek-table tbody tr:hover { background:var(--surface-2); }
    .trayek-table td { padding:10px 16px; font-size:13px; color:var(--text-1); vertical-align:middle; }

    /* ID badge */
    .id-badge { display:inline-block; background:var(--surface-2); border:1.5px solid var(--border); color:var(--text-3); padding:2px 9px; border-radius:6px; font-family:'DM Mono',monospace; font-size:11.5px; font-weight:600; white-space:nowrap; }

    /* Jenis badge */
    .jenis-badge { display:inline-flex; align-items:center; gap:5px; padding:3px 10px; border-radius:6px; font-size:11.5px; font-weight:700; letter-spacing:.03em; white-space:nowrap; }
    .jenis-badge.bus { background:#eff6ff; color:#1d4ed8; border:1.5px solid #bfdbfe; }
    .jenis-badge.mpu { background:#f0fdf4; color:#15803d; border:1.5px solid #bbf7d0; }

    /* Inline edit inputs */
    .edit-input { width:100%; padding:6px 10px; border:1.5px solid var(--border); border-radius:7px; font-family:'DM Sans',sans-serif; font-size:13px; color:var(--text-1); background:var(--surface); outline:none; transition:border-color .15s,box-shadow .15s; }
    .edit-input:focus { border-color:var(--accent); box-shadow:0 0 0 3px var(--accent-glow); }
    .edit-input.mono { font-family:'DM Mono',monospace; font-size:12.5px; }
    select.edit-input { cursor:pointer; }

    /* Action buttons */
    .td-actions { white-space:nowrap; }
    .btn-edit, .btn-save-row, .btn-cancel-row, .btn-del {
      display:inline-flex; align-items:center; justify-content:center; gap:5px;
      padding:5px 10px; border-radius:7px; font-family:'DM Sans',sans-serif;
      font-size:12px; font-weight:600; cursor:pointer; border:1.5px solid; transition:all .15s;
    }
    .btn-edit      { background:#eff6ff; color:#1d4ed8; border-color:#bfdbfe; }
    .btn-edit:hover{ background:#1d4ed8; color:#fff; border-color:#1d4ed8; }
    .btn-save-row  { background:#f0fdf4; color:#15803d; border-color:#bbf7d0; }
    .btn-save-row:hover { background:#15803d; color:#fff; border-color:#15803d; }
    .btn-cancel-row{ background:var(--surface-2); color:var(--text-3); border-color:var(--border); }
    .btn-cancel-row:hover { background:var(--border); color:var(--text-1); }
    .btn-del       { background:#fef2f2; color:#ef4444; border-color:#fecaca; }
    .btn-del:hover { background:#ef4444; color:#fff; border-color:#ef4444; }

    /* Row view/edit states — visibility now handled by JS for split-nama */
    .row-view .id-readonly { display:block; }
    .row-view .btn-edit,
    .row-view .btn-del     { display:inline-flex; }

    /* New row form */
    .new-row-section { border-top:1.5px dashed var(--border); padding:14px 16px; background:var(--surface-2); }
    .new-row-label   { font-size:11px; font-weight:700; letter-spacing:.06em; text-transform:uppercase; color:var(--text-3); margin-bottom:10px; }
    .new-row-form    { display:flex; gap:10px; align-items:center; flex-wrap:wrap; }
    .new-row-form .edit-input { flex:1; min-width:120px; }
    .new-row-form select.edit-input { flex:0 0 130px; }
    .btn-add { display:flex; align-items:center; gap:6px; padding:7px 16px; background:var(--accent); color:#fff; border:none; border-radius:8px; font-family:'DM Sans',sans-serif; font-size:13px; font-weight:600; cursor:pointer; transition:all .15s; white-space:nowrap; }
    .btn-add:hover    { background:#2563eb; box-shadow:0 4px 14px rgba(59,130,246,.3); }
    .btn-add:disabled { background:var(--text-4); cursor:not-allowed; }

    /* Info panel */
    .info-panel { display:flex; gap:10px; background:#eff6ff; border:1px solid #bfdbfe; border-radius:10px; padding:12px 14px; margin-bottom:20px; font-size:12.5px; color:#1e40af; line-height:1.7; }
    .info-panel svg { flex-shrink:0; margin-top:2px; }
    .tbadge { display:inline-block; background:var(--accent); color:#fff; padding:1px 7px; border-radius:4px; font-size:11px; font-family:'DM Mono',monospace; margin:0 2px; }

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

    /* Nama col wider */
    .col-nama { min-width:200px; }

    /* Split nama inputs */
    .nama-split { display:flex; align-items:center; gap:6px; }
    .nama-split .edit-input { flex:1; min-width:80px; }
    .nama-sep { color:var(--text-4); font-weight:700; font-size:14px; flex-shrink:0; }
    .nama-view { white-space:nowrap; }

    /* Auto-ID display */
    .id-auto { display:inline-flex; align-items:center; gap:5px; }
    .id-auto-badge { display:inline-block; background:var(--surface-2); border:1.5px solid var(--border); color:var(--text-3); padding:2px 9px; border-radius:6px; font-family:'DM Mono',monospace; font-size:11.5px; font-weight:600; white-space:nowrap; }
    .id-readonly { background:var(--surface-2) !important; color:var(--text-3) !important; cursor:default !important; }
    .id-readonly:focus { border-color:var(--border) !important; box-shadow:none !important; }
  </style>
</head>
<body>
<div class="adm-shell">
  @include('admin.partials.sidebar', ['currentPage' => 'update_trayek', 'currentModule' => 'angkutansekolah'])

  <main class="adm-main">
    <div class="adm-content">

      <div class="adm-page-header">
        <div>
          <h1 class="adm-page-title">Data Trayek</h1>
          <p class="adm-page-subtitle">Kelola nama-nama trayek Bus dan MPU</p>
        </div>
        <button class="btn btn-secondary btn-sm" id="btnRefresh">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
          Refresh
        </button>
      </div>

      <div class="info-panel">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
 <div>
          Tambah atau edit Nama <strong>Trayek</strong> dari Tabel.
        </div>
      </div>

      <!-- Tab bus / mpu -->
      <div class="tab-bar">
        <button class="tab-btn active" id="tabBus" onclick="switchTab('bus')">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
          Trayek Bus
          <span class="tab-count" id="cntBus">—</span>
        </button>
        <button class="tab-btn" id="tabMpu" onclick="switchTab('mpu')">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 17H3a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11a2 2 0 0 1 2 2v3"/><rect x="9" y="11" width="14" height="10" rx="2"/><circle cx="12" cy="21" r="1"/><circle cx="20" cy="21" r="1"/></svg>
          Trayek MPU
          <span class="tab-count" id="cntMpu">—</span>
        </button>
      </div>

      <!-- Search -->
      <div class="toolbar">
        <div class="search-wrap">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input type="text" id="searchInput" class="search-input" placeholder="Cari ID atau nama trayek..."/>
        </div>
      </div>

      <!-- Table card -->
      <div class="trayek-card">
        <table class="trayek-table">
          <thead>
            <tr>
              <th style="width:36px">#</th>
              <th style="width:90px">ID Trayek</th>
              <th class="col-nama">Nama Trayek</th>
              <th style="width:170px">Aksi</th>
            </tr>
          </thead>
          <tbody id="trayekTableBody">
            <tr><td colspan="4" class="tbl-empty">
              <div class="skel" style="width:55%;margin:0 auto 8px"></div>
              <div class="skel" style="width:40%;margin:0 auto"></div>
            </td></tr>
          </tbody>
        </table>

        <!-- Add new trayek form -->
        <div class="new-row-section">
          <div class="new-row-label">+ Tambah Trayek Baru</div>
          <div class="new-row-form">
            <div style="display:flex;flex-direction:column;gap:4px;flex:0 0 150px">
              <label style="font-size:10.5px;font-weight:700;color:var(--text-3);letter-spacing:.05em;text-transform:uppercase">ID Trayek (otomatis)</label>
              <input type="text" id="newId" class="edit-input mono id-readonly" placeholder="auto…" readonly autocomplete="off"/>
            </div>
            <div style="display:flex;flex-direction:column;gap:4px;flex:1;min-width:240px">
              <label style="font-size:10.5px;font-weight:700;color:var(--text-3);letter-spacing:.05em;text-transform:uppercase">Nama Trayek</label>
              <div class="nama-split">
                <input type="text" id="newTempat1" class="edit-input" placeholder="Tempat asal (mis. Kalidawir 1)" autocomplete="off" oninput="autoTitleCase(this); updateNewId()"/>
                <span class="nama-sep">–</span>
                <input type="text" id="newTempat2" class="edit-input" placeholder="Tempat tujuan (mis. Tulungagung)" autocomplete="off" oninput="autoTitleCase(this)"/>
              </div>
            </div>
            <button class="btn-add" id="btnAdd" onclick="addTrayek()" style="align-self:flex-end;margin-bottom:0">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
              Tambah
            </button>
          </div>
        </div>
      </div>

    </div><!-- /adm-content -->
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
let currentTab = 'bus';
let data = { bus: [], mpu: [] };

// ── Tabel target per jenis ─────────────────────────────────────────
function tabel(tab) { return tab === 'bus' ? 'trayek_bus' : 'trayek_mpu'; }

// ── Load ───────────────────────────────────────────────────────────
async function loadAll() {
  renderSkeleton();
  try {
    const [bus, mpu] = await Promise.all([
      sbGetShared('trayek_bus', 'select=id,jenis,nama&order=id.asc'),
      sbGetShared('trayek_mpu', 'select=id,jenis,nama&order=id.asc'),
    ]);
    data.bus = bus;
    data.mpu = mpu;
    document.getElementById('cntBus').textContent = bus.length;
    document.getElementById('cntMpu').textContent = mpu.length;
    renderTable();
  } catch (e) {
    document.getElementById('trayekTableBody').innerHTML =
      `<tr><td colspan="4" class="tbl-empty" style="color:#ef4444">Gagal memuat data: ${esc(e.message)}</td></tr>`;
  }
}

// ── Render ─────────────────────────────────────────────────────────
function renderSkeleton() {
  document.getElementById('trayekTableBody').innerHTML =
    [...Array(4)].map(() => `<tr><td colspan="4" style="padding:14px 16px">
      <div class="skel" style="width:25%;margin-bottom:6px"></div>
      <div class="skel" style="width:50%"></div>
    </td></tr>`).join('');
}

function jenisBadge(jenis) {
  const j = (jenis || '').toLowerCase();
  return `<span class="jenis-badge ${j}">${j.toUpperCase()}</span>`;
}

function renderTable() {
  const search = document.getElementById('searchInput').value.toLowerCase();
  const list   = (data[currentTab] || []).filter(r =>
    !search ||
    String(r.id  || '').toLowerCase().includes(search) ||
    String(r.nama || '').toLowerCase().includes(search)
  );

  if (!list.length) {
    document.getElementById('trayekTableBody').innerHTML =
      `<tr><td colspan="4" class="tbl-empty">Tidak ada data${search ? ' yang cocok' : ''}.</td></tr>`;
    return;
  }

  document.getElementById('trayekTableBody').innerHTML = list.map((r, i) => {
    // Split nama by ' - ' (with spaces) or '-'
    const parts = String(r.nama || '').split(/\s*-\s*/);
    const tempat1 = parts[0] || '';
    const tempat2 = parts.slice(1).join(' - ') || '';
    return `
    <tr class="row-view" id="row_${esc(r.id)}" data-id="${esc(r.id)}">
      <td style="color:var(--text-4);font-size:12px">${i + 1}</td>

      <td>
        <span class="view-val"><span class="id-badge">${esc(r.id)}</span></span>
        <input class="edit-input mono id-readonly" value="${esc(r.id)}" placeholder="auto" readonly autocomplete="off" style="display:none"/>
      </td>

      <td class="col-nama">
        <span class="view-val nama-view">${esc(r.nama)}</span>
        <div class="nama-split" style="display:none">
          <input class="edit-input nama-t1" value="${esc(tempat1)}" placeholder="Tempat asal" autocomplete="off"
            oninput="autoTitleCase(this); autoUpdateRowId(this)"/>
          <span class="nama-sep">–</span>
          <input class="edit-input nama-t2" value="${esc(tempat2)}" placeholder="Tempat tujuan" autocomplete="off"
            oninput="autoTitleCase(this)"/>
        </div>
      </td>

      <td class="td-actions">
        <button class="btn-edit" onclick="startEdit('${esc(r.id)}')">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
          Edit
        </button>
        <span class="save-group" style="display:none">
          <button class="btn-save-row" onclick="saveEdit('${esc(r.id)}')">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            Simpan
          </button>
          <button class="btn-cancel-row" onclick="cancelEdit('${esc(r.id)}')">Batal</button>
        </span>
        <button class="btn-del" onclick="deleteTrayek('${esc(r.id)}')" style="margin-left:4px">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
        </button>
      </td>
    </tr>`;
  }).join('');
}

// ── Edit inline ────────────────────────────────────────────────────
function startEdit(id) {
  const row = document.getElementById(`row_${id}`);
  if (!row) return;
  row.className = 'row-edit';
  // Show the split-nama div, hide view spans
  row.querySelector('.nama-split').style.display  = 'flex';
  row.querySelector('.nama-view').style.display   = 'none';
  row.querySelector('.id-readonly').style.display = 'block';
  row.querySelector('.view-val').style.display    = 'none';
  row.querySelector('.save-group').style.display  = 'inline-flex';
  row.querySelector('.btn-edit').style.display    = 'none';
  row.querySelector('.btn-del').style.display     = 'none';
  // Focus tempat1
  const t1 = row.querySelector('.nama-t1');
  if (t1) t1.focus();
}

function cancelEdit(id) {
  const row = document.getElementById(`row_${id}`);
  if (!row) return;
  const rec = (data[currentTab] || []).find(r => String(r.id) === String(id));
  if (rec) {
    const parts   = String(rec.nama || '').split(/\s*-\s*/);
    const t1Input = row.querySelector('.nama-t1');
    const t2Input = row.querySelector('.nama-t2');
    const idInput = row.querySelector('.id-readonly');
    if (t1Input) t1Input.value = parts[0] || '';
    if (t2Input) t2Input.value = parts.slice(1).join(' - ') || '';
    if (idInput) idInput.value = rec.id;
  }
  // Reset visibility
  row.querySelector('.nama-split').style.display  = 'none';
  row.querySelector('.nama-view').style.display   = 'inline';
  row.querySelector('.id-readonly').style.display = 'none';
  row.querySelector('.view-val').style.display    = 'inline';
  row.querySelector('.save-group').style.display  = 'none';
  row.querySelector('.btn-edit').style.display    = 'inline-flex';
  row.querySelector('.btn-del').style.display     = 'inline-flex';
  row.className = 'row-view';
}

async function saveEdit(oldId) {
  const row    = document.getElementById(`row_${oldId}`);
  const t1     = row.querySelector('.nama-t1').value.trim();
  const t2     = row.querySelector('.nama-t2').value.trim();
  const newNama = t2 ? `${t1} - ${t2}` : t1;
  const newId   = buildId(currentTab, t1);
  const newJenis= currentTab;

  if (!t1) { showToast('Nama tempat asal tidak boleh kosong', 'error'); return; }

  const btn = row.querySelector('.btn-save-row');
  btn.disabled = true;
  btn.innerHTML = '<span class="bspin"></span>';

  try {
    const tb = tabel(currentTab);
    await sbPatch(tb, `id=eq.${encodeURIComponent(oldId)}`, { id: newId, jenis: newJenis, nama: newNama });

    // Update local state
    const rec = (data[currentTab] || []).find(r => String(r.id) === String(oldId));
    if (rec) { rec.id = newId; rec.jenis = newJenis; rec.nama = newNama; }

    showToast(`✅ Trayek "${newId}" berhasil diperbarui`);
    renderTable();
  } catch (e) {
    showToast(`❌ ${e.message}`, 'error');
    btn.disabled = false;
    btn.innerHTML = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> Simpan';
  }
}

// ── Add new ────────────────────────────────────────────────────────
async function addTrayek() {
  const t1Input = document.getElementById('newTempat1');
  const t2Input = document.getElementById('newTempat2');

  const t1 = t1Input.value.trim();
  const t2 = t2Input.value.trim();
  const newNama  = t2 ? `${t1} - ${t2}` : t1;
  const newId    = buildId(currentTab, t1);
  const newJenis = currentTab;

  if (!t1) { showToast('Nama tempat asal wajib diisi', 'error'); return; }

  const btn = document.getElementById('btnAdd');
  btn.disabled = true;
  btn.innerHTML = '<span class="bspin"></span> Menyimpan...';

  try {
    const tb     = tabel(currentTab);
    const result = await sbPost(tb, { id: newId, jenis: newJenis, nama: newNama });
    const newRec = Array.isArray(result) ? result[0] : result;

    data[currentTab].push(newRec);
    data[currentTab].sort((a, b) => String(a.id).localeCompare(String(b.id)));
    document.getElementById(`cnt${currentTab === 'bus' ? 'Bus' : 'Mpu'}`).textContent = data[currentTab].length;

    t1Input.value = '';
    t2Input.value = '';
    document.getElementById('newId').value = '';
    showToast(`✅ Trayek "${newId}" berhasil ditambahkan`);
    renderTable();
  } catch (e) {
    showToast(`❌ ${e.message}`, 'error');
  } finally {
    btn.disabled = false;
    btn.innerHTML = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Tambah';
  }
}

// ── Delete ─────────────────────────────────────────────────────────
async function deleteTrayek(id) {
  const rec = (data[currentTab] || []).find(r => String(r.id) === String(id));
  if (!rec) return;
  if (!(await confirmDangerModal(`Hapus trayek "${rec.id} — ${rec.nama}"?\n\nPastikan trayek ini sudah tidak digunakan di data lain.`))) return;

  try {
    const tb = tabel(currentTab);
    await sbDelete(tb, `id=eq.${encodeURIComponent(id)}`);
    data[currentTab] = data[currentTab].filter(r => String(r.id) !== String(id));
    document.getElementById(`cnt${currentTab === 'bus' ? 'Bus' : 'Mpu'}`).textContent = data[currentTab].length;
    showToast(`🗑️ Trayek "${id}" dihapus`);
    renderTable();
  } catch (e) {
    showToast(`❌ ${e.message}`, 'error');
  }
}

// ── Auto ID & Title Case helpers ───────────────────────────────────

/**
 * Build id_trayek: {tab}_{tempat1_lowercase_nospasi_nonspecial}
 * "Kalidawir 1" → "bus_kalidawir1"
 * "Campurdarat"  → "mpu_campurdarat"
 */
function buildId(tab, tempat1) {
  const slug = tempat1
    .toLowerCase()
    .normalize('NFD').replace(/[\u0300-\u036f]/g, '')  // strip accents
    .replace(/[^a-z0-9]/g, '');                         // remove spaces & symbols
  return `${tab}_${slug}`;
}

/**
 * Title-case an input value on every keystroke.
 * Huruf pertama tiap kata menjadi kapital.
 */
function autoTitleCase(input) {
  const pos   = input.selectionStart;
  const val   = input.value;
  const cased = val.replace(/\b\w/g, c => c.toUpperCase());
  if (cased !== val) {
    input.value = cased;
    // Restore cursor position
    input.setSelectionRange(pos, pos);
  }
}

/** Update the readonly newId field based on newTempat1 */
function updateNewId() {
  const t1  = document.getElementById('newTempat1').value.trim();
  const out = document.getElementById('newId');
  out.value = t1 ? buildId(currentTab, t1) : '';
}

/** Update the readonly id cell inside an edit row based on tempat1 */
function autoUpdateRowId(t1Input) {
  const row     = t1Input.closest('tr');
  const idInput = row.querySelector('.edit-input.mono');
  if (idInput) idInput.value = buildId(currentTab, t1Input.value.trim());
}

// ── Helpers ────────────────────────────────────────────────────────
function esc(s) {
  return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

function switchTab(tab) {
  currentTab = tab;
  document.getElementById('tabBus').classList.toggle('active', tab === 'bus');
  document.getElementById('tabMpu').classList.toggle('active', tab === 'mpu');
  document.getElementById('searchInput').value = '';
  renderTable();
}

let _tt;
function showToast(msg, type = 'success') {
  const el = document.getElementById('toast');
  el.textContent = msg;
  el.className = `toast ${type} show`;
  clearTimeout(_tt);
  _tt = setTimeout(() => el.classList.remove('show'), 3000);
}

// ── Events ─────────────────────────────────────────────────────────
document.getElementById('searchInput').addEventListener('input', renderTable);
document.getElementById('btnRefresh').addEventListener('click', loadAll);
document.getElementById('newTempat1').addEventListener('keydown', e => { if (e.key === 'Enter') document.getElementById('newTempat2').focus(); });
document.getElementById('newTempat2').addEventListener('keydown', e => { if (e.key === 'Enter') addTrayek(); });
// Also update newId whenever tab switches
const origSwitchTab = switchTab;
window.switchTab = function(tab) { origSwitchTab(tab); updateNewId(); };

let _rtReloadTimer = null;
function scheduleReload() {
  clearTimeout(_rtReloadTimer);
  _rtReloadTimer = setTimeout(() => { loadAll(); }, 400);
}
if (sbRealtime) {
  sbRealtime
    .channel('admin-data-trayek')
    .on('postgres_changes', { event: '*', schema: 'public', table: 'trayek_bus' }, scheduleReload)
    .on('postgres_changes', { event: '*', schema: 'public', table: 'trayek_mpu' }, scheduleReload)
    .subscribe();
}

loadAll();
</script>
</body>
</html>