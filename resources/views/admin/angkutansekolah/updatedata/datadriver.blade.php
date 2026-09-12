<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
<meta name="csrf-token" content="{{ csrf_token() }}"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Data Driver - Admin</title>
  <link rel="icon" href="/favicon.ico"/>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/admin/admin-shell.css">
  <style>
    .adm-shell { display: flex !important; }
    .adm-main  { flex: 1; min-width: 0; }

    /* ── Tab bar ── */
    .tab-bar { display:flex; gap:4px; background:var(--surface-2); border:1.5px solid var(--border); border-radius:12px; padding:4px; margin-bottom:22px; width:fit-content; }
    .tab-btn { display:flex; align-items:center; gap:7px; padding:8px 18px; border:none; border-radius:8px; background:transparent; font-family:'DM Sans',sans-serif; font-size:13.5px; font-weight:500; color:var(--text-3); cursor:pointer; transition:all .15s; }
    .tab-btn:hover { background:var(--surface); color:var(--text-1); }
    .tab-btn.active { background:var(--navy); color:white; font-weight:600; box-shadow:0 2px 8px rgba(15,23,42,.18); }
    .tab-count { background:rgba(255,255,255,.15); padding:1px 7px; border-radius:99px; font-size:11px; font-weight:700; }
    .tab-btn:not(.active) .tab-count { background:var(--border); color:var(--text-3); }

    /* ── Toolbar ── */
    .toolbar { display:flex; gap:10px; align-items:center; margin-bottom:16px; flex-wrap:wrap; }
    .search-wrap { position:relative; flex:1; min-width:160px; max-width:260px; }
    .search-wrap svg { position:absolute; left:11px; top:50%; transform:translateY(-50%); color:var(--text-4); pointer-events:none; }
    .search-input { width:100%; padding:8px 11px 8px 34px; border:1.5px solid var(--border); border-radius:9px; font-family:'DM Sans',sans-serif; font-size:13px; color:var(--text-1); background:var(--surface-2); outline:none; transition:border-color .15s,box-shadow .15s; }
    .search-input:focus { border-color:var(--accent); box-shadow:0 0 0 3px var(--accent-glow); background:#fff; }

    /* ── Table card ── */
    .driver-card { background:var(--surface); border:1.5px solid var(--border); border-radius:14px; overflow-x:auto; -webkit-overflow-scrolling:touch; }
    .driver-table { width:100%; min-width:640px; border-collapse:collapse; }
    .driver-table thead tr { background:var(--surface-2); border-bottom:1.5px solid var(--border); }
    .driver-table th { padding:10px 16px; text-align:left; font-size:10.5px; font-weight:700; letter-spacing:.07em; text-transform:uppercase; color:var(--text-3); white-space:nowrap; }
    .driver-table tbody tr { border-bottom:1px solid var(--border); transition:background .12s; }
    .driver-table tbody tr:last-child { border-bottom:none; }
    .driver-table tbody tr:hover { background:var(--surface-2); }
    .driver-table td { padding:10px 16px; font-size:13px; color:var(--text-1); vertical-align:middle; }

    /* Badges */
    .plat-badge    { display:inline-block; background:var(--navy); color:#fff; padding:3px 10px; border-radius:6px; font-family:'DM Mono',monospace; font-size:12px; white-space:nowrap; }
    .trayek-badge  { display:inline-block; background:var(--surface-2); border:1.5px solid var(--border); color:var(--text-2); padding:2px 9px; border-radius:6px; font-size:12px; white-space:nowrap; }
    .trayek-badge.empty { color:var(--text-4); font-style:italic; border-style:dashed; }

    /* Inline edit inputs */
    .edit-input { width:100%; padding:6px 10px; border:1.5px solid var(--border); border-radius:7px; font-family:'DM Sans',sans-serif; font-size:13px; color:var(--text-1); background:var(--surface); outline:none; transition:border-color .15s,box-shadow .15s; box-sizing:border-box; }
    .edit-input:focus { border-color:var(--accent); box-shadow:0 0 0 3px var(--accent-glow); }
    .edit-input.mono { font-family:'DM Mono',monospace; font-size:12.5px; }
    select.edit-input { cursor:pointer; }

    /* Split plat */
    .plat-split { display:flex; align-items:center; gap:4px; min-width:200px; }
    .plat-split .plat-prefix { flex:0 0 46px; text-align:center; padding-left:6px; padding-right:6px; }
    .plat-split .plat-num    { flex:0 0 64px; text-align:center; }
    .plat-split .plat-suf    { flex:0 0 50px; text-align:center; padding-left:6px; padding-right:6px; }
    .split-sep { color:var(--text-4); font-size:11px; flex-shrink:0; user-select:none; }

    /* Split driver name */
    .drv-split { display:flex; align-items:center; gap:4px; }
    .drv-split .drv-1 { flex:1; min-width:80px; }
    .drv-split .drv-2 { flex:1; min-width:80px; }
    .drv-sep { color:var(--text-4); font-weight:700; font-size:13px; flex-shrink:0; user-select:none; }

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
    .new-row-grid    { display:grid; grid-template-columns:auto 1fr 1fr auto; gap:12px; align-items:end; }
    .field-group     { display:flex; flex-direction:column; gap:4px; }
    .field-label     { font-size:10.5px; font-weight:700; color:var(--text-3); letter-spacing:.05em; text-transform:uppercase; }

    .btn-add-driver { display:flex; align-items:center; gap:6px; padding:8px 16px; background:var(--accent); color:#fff; border:none; border-radius:8px; font-family:'DM Sans',sans-serif; font-size:13px; font-weight:600; cursor:pointer; transition:all .15s; white-space:nowrap; height:38px; align-self:flex-end; }
    .btn-add-driver:hover    { background:#2563eb; box-shadow:0 4px 14px rgba(59,130,246,.3); }
    .btn-add-driver:disabled { background:var(--text-4); cursor:not-allowed; }

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
    .info-panel { display:flex; gap:10px; background:#eff6ff; border:1px solid #bfdbfe; border-radius:10px; padding:12px 14px; margin-bottom:20px; font-size:12.5px; color:#1e40af; line-height:1.7; }
    .info-panel svg { flex-shrink:0; margin-top:2px; }
    .tbadge { display:inline-block; background:var(--accent); color:#fff; padding:1px 7px; border-radius:4px; font-size:11px; font-family:'DM Mono',monospace; margin:0 2px; }

    @media(max-width:900px) {
      .new-row-grid { grid-template-columns:1fr 1fr; }
    }
    @media(max-width:540px) {
      .new-row-grid { grid-template-columns:1fr; }
    }

    /* ── Rute chips & tombol Atur Rute ── */
    .rute-chip-wrap { display:flex; flex-wrap:wrap; gap:4px; align-items:center; margin-bottom:6px; max-width:220px; }
    .rute-chip { display:inline-flex; align-items:center; background:#eff6ff; border:1px solid #bfdbfe; color:#1d4ed8; font-size:11px; font-weight:600; padding:2px 8px; border-radius:5px; white-space:nowrap; }
    .rute-chip.empty { background:var(--surface-2); border-color:var(--border); color:var(--text-4); font-style:italic; font-weight:500; }
    .btn-atur-rute { display:inline-flex; align-items:center; gap:5px; padding:4px 9px; border-radius:7px; font-family:'DM Sans',sans-serif; font-size:11.5px; font-weight:600; cursor:pointer; border:1.5px solid #bfdbfe; background:#eff6ff; color:#1d4ed8; transition:all .15s; }
    .btn-atur-rute:hover { background:#1d4ed8; color:#fff; border-color:#1d4ed8; }

    /* ── Lokasi GPS ── */
    .coord-cell { font-family:'DM Mono',monospace; font-size:11.5px; color:var(--text-2); }
    .coord-empty { color:var(--text-4); font-style:italic; font-size:12px; }
    .coord-time { font-size:10.5px; color:var(--text-4); display:block; margin-top:1px; }

    /* ── Toolbar: tombol reset semua koordinat ── */
    .btn-reset-coord { display:inline-flex; align-items:center; gap:6px; padding:8px 14px; background:#f0fdf4; color:#0d9488; border:1.5px solid #99f6e4; border-radius:9px; font-family:'DM Sans',sans-serif; font-size:12.5px; font-weight:600; cursor:pointer; transition:all .15s; white-space:nowrap; }
    .btn-reset-coord:hover { background:#0d9488; color:#fff; border-color:#0d9488; }

    /* ── Modal Atur Rute ── */
    .rm-overlay { display:none; position:fixed; inset:0; background:rgba(15,23,42,.45); z-index:2000; align-items:center; justify-content:center; padding:20px; }
    .rm-overlay.open { display:flex; }
    .rm-box { background:#fff; border-radius:16px; width:100%; max-width:460px; max-height:82vh; display:flex; flex-direction:column; box-shadow:0 20px 60px rgba(15,23,42,.3); }
    .rm-head { padding:18px 20px 14px; border-bottom:1.5px solid var(--border); display:flex; align-items:flex-start; justify-content:space-between; gap:10px; }
    .rm-title { font-size:15px; font-weight:700; color:var(--text-1); }
    .rm-sub { font-size:12px; color:var(--text-3); margin-top:2px; }
    .rm-close { background:none; border:none; cursor:pointer; color:var(--text-3); padding:4px; border-radius:6px; }
    .rm-close:hover { background:var(--surface-2); color:var(--text-1); }
    .rm-body { padding:16px 20px; overflow-y:auto; flex:1; }
    .rm-group-title { font-size:11px; font-weight:700; letter-spacing:.06em; text-transform:uppercase; color:var(--text-4); margin:14px 0 8px; }
    .rm-group-title:first-child { margin-top:0; }
    .rm-item { display:flex; align-items:flex-start; gap:9px; padding:8px 9px; border-radius:9px; cursor:pointer; transition:background .12s; }
    .rm-item:hover { background:var(--surface-2); }
    .rm-item input[type=checkbox] { margin-top:2px; width:16px; height:16px; accent-color:var(--accent); cursor:pointer; flex-shrink:0; }
    .rm-item-label { font-size:13px; font-weight:600; color:var(--text-1); }
    .rm-item-sub { font-size:11.5px; color:var(--text-3); margin-top:1px; }
    .rm-empty { text-align:center; padding:30px 10px; color:var(--text-4); font-size:12.5px; }
    .rm-ft { padding:14px 20px; border-top:1.5px solid var(--border); display:flex; gap:10px; }
    .rm-btn-cancel { flex:0 0 auto; padding:9px 16px; background:var(--surface-2); border:1.5px solid var(--border); border-radius:9px; font-family:'DM Sans',sans-serif; font-size:13px; font-weight:600; color:var(--text-3); cursor:pointer; }
    .rm-btn-save { flex:1; padding:9px; background:var(--accent); color:#fff; border:none; border-radius:9px; font-family:'DM Sans',sans-serif; font-size:13px; font-weight:600; cursor:pointer; }
    .rm-btn-save:hover { background:#2563eb; }
    .rm-btn-save:disabled { background:var(--text-4); cursor:not-allowed; }
  </style>
</head>
<body>
<div class="adm-shell">
  @include('admin.partials.sidebar', ['currentPage' => 'update_driver', 'currentModule' => 'angkutansekolah'])

  <main class="adm-main">
    <div class="adm-content">

      <div class="adm-page-header">
        <div>
          <h1 class="adm-page-title">Data Driver</h1>
          <p class="adm-page-subtitle">Kelola data plat &amp; nama driver Bus dan MPU</p>
        </div>
        <div style="display:flex;gap:8px">
          <button class="btn-reset-coord" id="btnResetCoord">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="9"/><path d="M15 9l-6 6M9 9l6 6"/></svg>
            Reset Semua Koordinat
          </button>
          <button class="btn btn-secondary btn-sm" id="btnRefresh">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
            Refresh
          </button>
        </div>
      </div>

      <div class="info-panel">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <div>
          Isi data Plat. Nama driver bisa 1 atau 2 orang (dipisah <span class="tbadge">-</span>). tambahkan data <span class="tbadge">Trayek</span> dari menu <strong>Data Trayek</strong>.<br>
          Kolom <span class="tbadge">Rute</span> menentukan driver ini tampil di Rute 1/2/3 yang mana di halaman website (pilih trayek dulu, lalu klik "Atur Rute"). Kolom <span class="tbadge">Lokasi GPS</span> terisi otomatis saat driver membuka link absen RFID-nya.
        </div>
      </div>

      <!-- Tab bus/mpu -->
      <div class="tab-bar">
        <button class="tab-btn active" id="tabBus" onclick="switchTab('bus')">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
          Driver Bus
          <span class="tab-count" id="cntBus">-</span>
        </button>
        <button class="tab-btn" id="tabMpu" onclick="switchTab('mpu')">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 17H3a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11a2 2 0 0 1 2 2v3"/><rect x="9" y="11" width="14" height="10" rx="2"/><circle cx="12" cy="21" r="1"/><circle cx="20" cy="21" r="1"/></svg>
          Driver MPU
          <span class="tab-count" id="cntMpu">-</span>
        </button>
      </div>

      <!-- Search -->
      <div class="toolbar">
        <div class="search-wrap">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input type="text" id="searchInput" class="search-input" placeholder="Cari plat, nama, atau trayek..."/>
        </div>
      </div>

      <!-- Table card -->
      <div class="driver-card">
        <table class="driver-table">
          <thead>
            <tr>
              <th style="width:36px">#</th>
              <th>Plat Kendaraan</th>
              <th>Nama Driver</th>
              <th>Trayek</th>
              <th>Rute</th>
              <th>Lokasi GPS</th>
              <th style="width:160px">Aksi</th>
            </tr>
          </thead>
          <tbody id="driverTableBody">
            <tr><td colspan="7" class="tbl-empty">
              <div class="skel" style="width:60%;margin:0 auto 8px"></div>
              <div class="skel" style="width:45%;margin:0 auto"></div>
            </td></tr>
          </tbody>
        </table>

        <!-- Add new driver -->
        <div class="new-row-section">
          <div class="new-row-label">+ Tambah Driver Baru</div>
          <div class="new-row-grid">

            <!-- Plat -->
            <div class="field-group">
              <span class="field-label">Plat Kendaraan</span>
              <div class="plat-split">
                <input type="text" id="nPrefix" class="edit-input mono plat-prefix" value="AG" maxlength="4"
                  autocomplete="off" oninput="this.value=this.value.toUpperCase()"/>
                <span class="split-sep">·</span>
                <input type="text" id="nNum" class="edit-input mono plat-num" placeholder="1058" maxlength="5"
                  autocomplete="off" oninput="this.value=this.value.replace(/\D/g,'')"/>
                <span class="split-sep">·</span>
                <input type="text" id="nSuf" class="edit-input mono plat-suf" placeholder="UR" maxlength="3"
                  autocomplete="off" oninput="this.value=this.value.replace(/[^a-zA-Z]/g,'').toUpperCase()"/>
              </div>
            </div>

            <!-- Nama -->
            <div class="field-group">
              <span class="field-label">Nama Driver</span>
              <div class="drv-split">
                <input type="text" id="nDrv1" class="edit-input drv-1" placeholder="Driver 1"
                  autocomplete="off" oninput="this.value=this.value.toUpperCase()"/>
                <span class="drv-sep">–</span>
                <input type="text" id="nDrv2" class="edit-input drv-2" placeholder="Driver 2 (opsional)"
                  autocomplete="off" oninput="this.value=this.value.toUpperCase()"/>
              </div>
            </div>

            <!-- Trayek -->
            <div class="field-group">
              <span class="field-label">Trayek</span>
              <select id="nTrayek" class="edit-input">
                <option value="">- Pilih Trayek -</option>
              </select>
            </div>

            <button class="btn-add-driver" id="btnAddDriver" onclick="addDriver()">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
              Tambah
            </button>
          </div>
        </div>
      </div>

    </div>
  </main>
</div>

<!-- ══ MODAL ATUR RUTE ══ -->
<div class="rm-overlay" id="rmOverlay">
  <div class="rm-box">
    <div class="rm-head">
      <div>
        <div class="rm-title" id="rmTitle">Atur Rute</div>
        <div class="rm-sub" id="rmSub">-</div>
      </div>
      <button class="rm-close" id="rmClose" type="button">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="rm-body" id="rmBody">
      <!-- diisi JS -->
    </div>
    <div class="rm-ft">
      <button class="rm-btn-cancel" id="rmCancel" type="button">Batal</button>
      <button class="rm-btn-save" id="rmSave" type="button">Simpan Rute</button>
    </div>
  </div>
</div>

<div class="toast" id="toast"></div>

<script>
const SB_URL_PUBLIC  = {!! json_encode(config('services.supabase.url')) !!};
const SB_ANON_PUBLIC = {!! json_encode(config('services.supabase.anon_key')) !!};
</script>
<script src="/assets/admin/angkutansekolah/sb-secure.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>
<script>
// service_role key tidak lagi dikirim ke browser - sbGet/sbPost/sbPatch/
// sbDelete sekarang datang dari admin/assets/angkutansekolah/sb-secure.js (lewat admin/api/angkutansekolah/db.php).
const sbRealtime = window.supabase ? window.supabase.createClient(SB_URL_PUBLIC, SB_ANON_PUBLIC) : null;

// ── State ──────────────────────────────────────────────────────────
let currentTab = 'bus';
let data       = { bus: [], mpu: [] };
let trayekData = { bus: [], mpu: [] };
let allMapRows = []; // semua baris tabel map - dipakai untuk deteksi Rute 1/2/3 per trayek
let rmDriverId = null; // id driver yang sedang diatur rutenya di modal

// ── Tables affected by driver changes ─────────────────────────────
const tableConfigs = [
  { name:'absensi_RFID', type:'combined', field:'plat_driver', format:(p,d)=>`${p} - ${d}` },
  { name:'absensi_foto', type:'combined', field:'plat_driver', format:(p,d)=>`${p} - ${d}` },
];

// ── Auto ID (URL-safe 16 chars) ────────────────────────────────────
function genId() {
  const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
  return Array.from({length:16}, ()=>chars[Math.floor(Math.random()*chars.length)]).join('');
}

// ── Plat helpers ───────────────────────────────────────────────────
function buildPlat(prefix, num, suf) {
  const p = prefix.trim().toUpperCase();
  const n = num.trim();
  const s = suf.trim().toUpperCase();
  if (!p || !n) return '';
  return s ? `${p} ${n} ${s}` : `${p} ${n}`;
}

function parsePlat(plat) {
  const parts = (plat||'').trim().split(/\s+/);
  return { prefix: parts[0]||'AG', num: parts[1]||'', suf: parts[2]||'' };
}

// ── Driver name helpers ────────────────────────────────────────────
function buildDriver(d1, d2) {
  const a = d1.trim().toUpperCase();
  const b = d2.trim().toUpperCase();
  if (!a) return '';
  return b ? `${a} - ${b}` : a;
}

function parseDriver(driver) {
  const idx = (driver||'').indexOf(' - ');
  if (idx === -1) return { d1:(driver||'').trim(), d2:'' };
  return { d1:driver.slice(0,idx).trim(), d2:driver.slice(idx+3).trim() };
}

// ── Load ───────────────────────────────────────────────────────────
async function loadAll() {
  renderSkeleton();
  try {
    const [bus, mpu, tBus, tMpu, mapRows] = await Promise.all([
      sbGetShared('driver_bus', 'select=id,plat,driver,trayek,lat,lng,update_at,rute&order=plat.asc'),
      sbGetShared('driver_mpu', 'select=id,plat,driver,trayek,lat,lng,update_at,rute&order=plat.asc'),
      sbGetShared('trayek_bus', 'select=id,nama&order=nama.asc'),
      sbGetShared('trayek_mpu', 'select=id,nama&order=nama.asc'),
      sbGetShared('map', 'select=id_map,id_trayek,urutan,nama_tempat&order=id_map.asc,urutan.asc'),
    ]);
    data.bus = bus; data.mpu = mpu;
    trayekData.bus = tBus; trayekData.mpu = tMpu;
    allMapRows = Array.isArray(mapRows) ? mapRows : [];
    document.getElementById('cntBus').textContent = bus.length;
    document.getElementById('cntMpu').textContent = mpu.length;
    populateTrayekSelect('nTrayek', currentTab, '');
    renderTable();
  } catch(e) {
    document.getElementById('driverTableBody').innerHTML =
      `<tr><td colspan="5" class="tbl-empty" style="color:#ef4444">Gagal memuat: ${esc(e.message)}</td></tr>`;
  }
}

// ── Populate trayek <select> ───────────────────────────────────────
// Nilai yang tersimpan di kolom `trayek` adalah nama trayek (string)
function populateTrayekSelect(selectId, tab, selected) {
  const sel  = document.getElementById(selectId);
  if (!sel) return;
  const list = trayekData[tab] || [];
  sel.innerHTML = '<option value="">- Pilih Trayek -</option>' +
    list.map(t => `<option value="${esc(t.nama)}"${t.nama === selected ? ' selected' : ''}>${esc(t.nama)}</option>`).join('');
}

function buildTrayekOpts(tab, selected) {
  const list = trayekData[tab] || [];
  return '<option value="">- Pilih Trayek -</option>' +
    list.map(t => `<option value="${esc(t.nama)}"${t.nama === selected ? ' selected' : ''}>${esc(t.nama)}</option>`).join('');
}

// ── Rute helpers ───────────────────────────────────────────────────
// Mengikuti logika yang sama persis dengan halaman publik
// (pages/ruteangkutansekolah/partials/rute_bus.js & rute_mpu.js) supaya
// label "Rute 1/2/3" yang dilihat admin di sini SAMA dengan yang tampil
// di website.
const WAKTU_ORDER = ['Pagi', 'Siang', 'Sore', 'Malam', 'Lainnya'];

function waktuFromIdMap(idMap) {
  const low = String(idMap || '').toLowerCase();
  if (low.includes('pagi'))  return 'Pagi';
  if (low.includes('siang')) return 'Siang';
  if (low.includes('sore'))  return 'Sore';
  if (low.includes('malam')) return 'Malam';
  return 'Lainnya';
}

function ruteLabelFromIdMap(idMap) {
  const m = String(idMap || '').match(/rute(\d+)/i);
  const num = m ? parseInt(m[1], 10) : 1;
  return { num, label: `Rute ${num}` };
}

// Ambil daftar rute (per id_map unik) yang TERDETEKSI dari tabel `map`
// untuk satu nama trayek tertentu - inilah yang jadi pilihan checklist.
function getRuteOptionsForTrayek(tab, trayekNama) {
  if (!trayekNama) return [];
  const tr = (trayekData[tab] || []).find(t => t.nama === trayekNama);
  if (!tr) return [];

  const byIdMap = {};
  allMapRows.forEach(r => {
    if (r.id_trayek !== tr.id) return;
    if (!byIdMap[r.id_map]) byIdMap[r.id_map] = [];
    byIdMap[r.id_map].push(r);
  });

  const list = Object.keys(byIdMap).map(idMap => {
    const pts    = byIdMap[idMap].sort((a, b) => (a.urutan || 0) - (b.urutan || 0));
    const waktu  = waktuFromIdMap(idMap);
    const { num, label } = ruteLabelFromIdMap(idMap);
    const tempat = pts.map(p => p.nama_tempat).filter(Boolean).join(' - ');
    return { idMap, waktu, num, label, tempat };
  });

  list.sort((a, b) => WAKTU_ORDER.indexOf(a.waktu) - WAKTU_ORDER.indexOf(b.waktu) || a.num - b.num);
  return list;
}

// Format waktu relatif sederhana untuk kolom Lokasi GPS
function relTime(iso) {
  if (!iso) return '';
  const diffMs = Date.now() - new Date(iso).getTime();
  if (diffMs < 0 || isNaN(diffMs)) return '';
  const s = Math.floor(diffMs / 1000);
  if (s < 60)   return `${s} detik lalu`;
  const m = Math.floor(s / 60);
  if (m < 60)   return `${m} menit lalu`;
  const h = Math.floor(m / 60);
  if (h < 24)   return `${h} jam lalu`;
  const d = Math.floor(h / 24);
  return `${d} hari lalu`;
}

// ── Render ─────────────────────────────────────────────────────────
function renderSkeleton() {
  document.getElementById('driverTableBody').innerHTML =
    [...Array(4)].map(()=>`<tr><td colspan="5" style="padding:14px 16px">
      <div class="skel" style="width:30%;margin-bottom:6px"></div>
      <div class="skel" style="width:55%"></div>
    </td></tr>`).join('');
}

function renderTable() {
  const search = document.getElementById('searchInput').value.toLowerCase();
  const list   = (data[currentTab]||[]).filter(d=>
    !search ||
    (d.plat||'').toLowerCase().includes(search) ||
    (d.driver||'').toLowerCase().includes(search) ||
    (d.trayek||'').toLowerCase().includes(search)
  );

  if (!list.length) {
    document.getElementById('driverTableBody').innerHTML =
      `<tr><td colspan="7" class="tbl-empty">Tidak ada data${search?' yang cocok':''}.</td></tr>`;
    return;
  }

  document.getElementById('driverTableBody').innerHTML = list.map((d, i) => {
    const pl  = parsePlat(d.plat);
    const drv = parseDriver(d.driver);
    const tOp = buildTrayekOpts(currentTab, d.trayek || '');

    return `
    <tr id="row_${esc(d.id)}" data-id="${esc(d.id)}">
      <td style="color:var(--text-4);font-size:12px">${i+1}</td>

      <!-- Plat -->
      <td>
        <span class="vv-plat"><span class="plat-badge">${esc(d.plat)}</span></span>
        <div class="plat-split vv-edit" style="display:none">
          <input type="text" class="edit-input mono plat-prefix ei-pref" value="${esc(pl.prefix)}" maxlength="4"
            autocomplete="off" oninput="this.value=this.value.toUpperCase()"/>
          <span class="split-sep">·</span>
          <input type="text" class="edit-input mono plat-num ei-num" value="${esc(pl.num)}" maxlength="5"
            autocomplete="off" oninput="this.value=this.value.replace(/\\D/g,'')"/>
          <span class="split-sep">·</span>
          <input type="text" class="edit-input mono plat-suf ei-suf" value="${esc(pl.suf)}" maxlength="3"
            autocomplete="off" oninput="this.value=this.value.replace(/[^a-zA-Z]/g,'').toUpperCase()"/>
        </div>
      </td>

      <!-- Driver name -->
      <td>
        <span class="vv-driver">${esc(d.driver)}</span>
        <div class="drv-split vv-edit" style="display:none">
          <input type="text" class="edit-input drv-1 ei-d1" value="${esc(drv.d1)}" placeholder="Driver 1"
            autocomplete="off" oninput="this.value=this.value.toUpperCase()"/>
          <span class="drv-sep">–</span>
          <input type="text" class="edit-input drv-2 ei-d2" value="${esc(drv.d2)}" placeholder="Driver 2 (opsional)"
            autocomplete="off" oninput="this.value=this.value.toUpperCase()"/>
        </div>
      </td>

      <!-- Trayek -->
      <td>
        <span class="vv-trayek"><span class="trayek-badge${d.trayek?'':' empty'}">${d.trayek ? esc(d.trayek) : '-'}</span></span>
        <select class="edit-input ei-trayek vv-edit" style="display:none">${tOp}</select>
      </td>

      <!-- Rute (checklist Rute 1/2/3, disimpan di kolom rute) -->
      <td>
        <div class="rute-chip-wrap">
          ${Array.isArray(d.rute) && d.rute.length
            ? d.rute.map(idMap => `<span class="rute-chip">${esc(ruteLabelFromIdMap(idMap).label)}</span>`).join('')
            : '<span class="rute-chip empty">Belum diatur</span>'}
        </div>
        <button type="button" class="btn-atur-rute" onclick="openRuteModal('${esc(d.id)}')">
          <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
          Atur Rute
        </button>
      </td>

      <!-- Lokasi GPS (read-only, otomatis dari link absen RFID driver) -->
      <td>
        ${(d.lat != null && d.lng != null)
          ? `<span class="coord-cell">${Number(d.lat).toFixed(5)}, ${Number(d.lng).toFixed(5)}</span><span class="coord-time">${esc(relTime(d.update_at))}</span>`
          : '<span class="coord-empty">Belum ada data</span>'}
      </td>

      <!-- Actions -->
      <td class="td-actions">
        <button class="btn-edit vv-btn" onclick="startEdit('${esc(d.id)}')">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
          Edit
        </button>
        <button class="btn-del vv-btn" onclick="deleteDriver('${esc(d.id)}')" style="margin-left:4px">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
        </button>
        <span class="eg-btns" style="display:none; gap:4px">
          <button class="btn-save-row" onclick="saveEdit('${esc(d.id)}')">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            Simpan
          </button>
          <button class="btn-cancel-row" onclick="cancelEdit('${esc(d.id)}')">Batal</button>
        </span>
      </td>
    </tr>`;
  }).join('');
}

// ── Edit helpers ───────────────────────────────────────────────────
function startEdit(id) {
  const row = document.getElementById(`row_${id}`);
  if (!row) return;
  row.querySelectorAll('.vv-plat, .vv-driver, .vv-trayek').forEach(el=>el.style.display='none');
  row.querySelectorAll('.vv-edit').forEach(el=>el.style.display='flex');
  row.querySelector('.ei-trayek').style.display = 'block';
  row.querySelectorAll('.vv-btn').forEach(el=>el.style.display='none');
  row.querySelector('.eg-btns').style.display = 'inline-flex';
  row.querySelector('.ei-pref').focus();
}

function cancelEdit(id) {
  const row = document.getElementById(`row_${id}`);
  if (!row) return;
  const rec = (data[currentTab]||[]).find(d=>String(d.id)===String(id));
  if (rec) {
    const pl  = parsePlat(rec.plat);
    const drv = parseDriver(rec.driver);
    row.querySelector('.ei-pref').value   = pl.prefix;
    row.querySelector('.ei-num').value    = pl.num;
    row.querySelector('.ei-suf').value    = pl.suf;
    row.querySelector('.ei-d1').value     = drv.d1;
    row.querySelector('.ei-d2').value     = drv.d2;
    row.querySelector('.ei-trayek').value = rec.trayek || '';
  }
  _viewMode(row);
}

function _viewMode(row) {
  row.querySelectorAll('.vv-plat, .vv-driver, .vv-trayek').forEach(el=>el.style.display='inline');
  row.querySelectorAll('.vv-edit').forEach(el=>el.style.display='none');
  row.querySelectorAll('.vv-btn').forEach(el=>el.style.display='inline-flex');
  row.querySelector('.eg-btns').style.display='none';
}

async function saveEdit(id) {
  const row       = document.getElementById(`row_${id}`);
  const newPlat   = buildPlat(row.querySelector('.ei-pref').value, row.querySelector('.ei-num').value, row.querySelector('.ei-suf').value);
  const newDriver = buildDriver(row.querySelector('.ei-d1').value, row.querySelector('.ei-d2').value);
  const newTrayek = row.querySelector('.ei-trayek').value;

  if (!newPlat)   { showToast('Plat tidak boleh kosong', 'error'); return; }
  if (!newDriver) { showToast('Nama driver tidak boleh kosong', 'error'); return; }

  const rec     = (data[currentTab]||[]).find(d=>String(d.id)===String(id));
  const oldPlat = rec.plat;
  const oldName = rec.driver;

  const btnSave = row.querySelector('.btn-save-row');
  btnSave.disabled = true;
  btnSave.innerHTML = '<span class="bspin"></span>';

  try {
    const table = currentTab==='bus' ? 'driver_bus' : 'driver_mpu';
    await sbPatch(table, `id=eq.${encodeURIComponent(id)}`, { plat:newPlat, driver:newDriver, trayek:newTrayek||null });

    if (newPlat!==oldPlat || newDriver!==oldName) {
      await propagateUpdate(oldPlat, oldName, newPlat, newDriver);
    }

    // Catatan: kolom `nama` di deeplink_absenangkutan TIDAK perlu dipatch lagi.
    // Halaman pages/absenrfid/pages/listlink.php & pages/absenqrcode/pages/listlink.php sekarang mengambil nama driver secara
    // live via join id ke driver_bus/driver_mpu, jadi otomatis realtime.

    rec.plat=newPlat; rec.driver=newDriver; rec.trayek=newTrayek||null;
    showToast(`✅ ${newPlat} berhasil diperbarui`);
    renderTable();
  } catch(e) {
    showToast(`❌ ${e.message}`, 'error');
    btnSave.disabled=false;
    btnSave.innerHTML='<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> Simpan';
  }
}

// ── Add new ────────────────────────────────────────────────────────
async function addDriver() {
  const newPlat   = buildPlat(document.getElementById('nPrefix').value, document.getElementById('nNum').value, document.getElementById('nSuf').value);
  const newDriver = buildDriver(document.getElementById('nDrv1').value, document.getElementById('nDrv2').value);
  const newTrayek = document.getElementById('nTrayek').value;

  if (!newPlat)   { showToast('Plat tidak boleh kosong', 'error'); return; }
  if (!newDriver) { showToast('Nama driver tidak boleh kosong', 'error'); return; }

  const btn = document.getElementById('btnAddDriver');
  btn.disabled = true;
  btn.innerHTML = '<span class="bspin"></span> Menyimpan...';

  try {
    const newId  = genId();
    const table  = currentTab==='bus' ? 'driver_bus' : 'driver_mpu';
    const result = await sbPost(table, { id:newId, plat:newPlat, driver:newDriver, trayek:newTrayek||null });
    const newRec = Array.isArray(result) ? result[0] : result;

    // Catatan: deeplink_absenangkutan sudah tidak dipakai lagi. Halaman
    // pages/absenrfid/pages/listlink.php & pages/absenqrcode/pages/listlink.php sekarang ambil data langsung dari
    // driver_bus/driver_mpu (id + driver), jadi tidak perlu insert apa pun
    // ke deeplink_absenangkutan di sini.

    data[currentTab].push(newRec);
    data[currentTab].sort((a,b)=>(a.plat||'').localeCompare(b.plat||''));
    document.getElementById(`cnt${currentTab==='bus'?'Bus':'Mpu'}`).textContent = data[currentTab].length;

    document.getElementById('nPrefix').value = 'AG';
    document.getElementById('nNum').value    = '';
    document.getElementById('nSuf').value    = '';
    document.getElementById('nDrv1').value   = '';
    document.getElementById('nDrv2').value   = '';
    document.getElementById('nTrayek').value = '';

    showToast(`✅ Driver ${newPlat} ditambahkan`);
    renderTable();
  } catch(e) {
    showToast(`❌ ${e.message}`, 'error');
  } finally {
    btn.disabled=false;
    btn.innerHTML='<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Tambah';
  }
}

// ── Delete ─────────────────────────────────────────────────────────
async function deleteDriver(id) {
  const rec = (data[currentTab]||[]).find(d=>String(d.id)===String(id));
  if (!rec) return;
  if (!(await confirmDangerModal(`Hapus driver "${rec.plat} - ${rec.driver}"?\n\nData di tabel lain TIDAK otomatis dihapus.`))) return;

  try {
    const table = currentTab==='bus' ? 'driver_bus' : 'driver_mpu';
    await sbDelete(table, `id=eq.${encodeURIComponent(id)}`);

    // Catatan: deeplink_absenangkutan sudah tidak dipakai lagi, jadi tidak
    // perlu ikut dihapus dari sini.

    data[currentTab] = data[currentTab].filter(d=>String(d.id)!==String(id));
    document.getElementById(`cnt${currentTab==='bus'?'Bus':'Mpu'}`).textContent = data[currentTab].length;
    showToast(`🗑️ Driver ${rec.plat} dihapus`);
    renderTable();
  } catch(e) {
    showToast(`❌ ${e.message}`, 'error');
  }
}

// ── Propagate ──────────────────────────────────────────────────────
async function propagateUpdate(oldPlat, oldName, newPlat, newName) {
  for (const cfg of tableConfigs) {
    try {
      let filter, body;
      if (cfg.type==='combined') {
        const oldVal = cfg.format(oldPlat, oldName);
        const newVal = cfg.format(newPlat, newName);
        filter = `${cfg.field}=eq.${encodeURIComponent(oldVal)}`;
        body   = { [cfg.field]: newVal };
      } else if (cfg.type==='dual') {
        filter = `${cfg.fields.plat}=eq.${encodeURIComponent(oldPlat)}&${cfg.fields.driver}=eq.${encodeURIComponent(oldName)}`;
        body   = { [cfg.fields.plat]:newPlat, [cfg.fields.driver]:newName };
      }
      if (filter && body) await sbPatch(cfg.name, filter, body);
    } catch(e) { console.warn(`Propagate ke ${cfg.name} gagal:`, e.message); }
  }
}

// ── Modal Atur Rute ──────────────────────────────────────────────────
function openRuteModal(id) {
  const rec = (data[currentTab] || []).find(d => String(d.id) === String(id));
  if (!rec) return;

  if (!rec.trayek) {
    showToast('⚠️ Pilih Trayek driver ini dulu sebelum atur Rute', 'error');
    return;
  }

  const options = getRuteOptionsForTrayek(currentTab, rec.trayek);
  rmDriverId = id;

  document.getElementById('rmTitle').textContent = `Atur Rute - ${rec.plat}`;
  document.getElementById('rmSub').textContent    = `${rec.driver} · Trayek: ${rec.trayek}`;

  const body = document.getElementById('rmBody');
  if (!options.length) {
    body.innerHTML = `<div class="rm-empty">Belum ada rute terdeteksi untuk trayek <strong>${esc(rec.trayek)}</strong>.<br>Tambahkan titik rute lewat menu <strong>Data Map</strong> dulu.</div>`;
  } else {
    const selected = new Set(Array.isArray(rec.rute) ? rec.rute : []);
    let html = '';
    let lastWaktu = null;
    options.forEach(o => {
      if (o.waktu !== lastWaktu) {
        html += `<div class="rm-group-title">Rute ${o.waktu}</div>`;
        lastWaktu = o.waktu;
      }
      html += `
        <label class="rm-item">
          <input type="checkbox" value="${esc(o.idMap)}" ${selected.has(o.idMap) ? 'checked' : ''}>
          <div>
            <div class="rm-item-label">${esc(o.label)}</div>
            <div class="rm-item-sub">${esc(o.tempat || o.idMap)}</div>
          </div>
        </label>`;
    });
    body.innerHTML = html;
  }

  document.getElementById('rmOverlay').classList.add('open');
}

function closeRuteModal() {
  document.getElementById('rmOverlay').classList.remove('open');
  rmDriverId = null;
}

async function saveRuteModal() {
  if (!rmDriverId) return;
  const table   = currentTab === 'bus' ? 'driver_bus' : 'driver_mpu';
  const checked = [...document.querySelectorAll('#rmBody input[type=checkbox]:checked')].map(el => el.value);

  const btn = document.getElementById('rmSave');
  btn.disabled = true;
  btn.textContent = 'Menyimpan...';
  try {
    await sbPatch(table, `id=eq.${encodeURIComponent(rmDriverId)}`, { rute: checked });
    const rec = (data[currentTab] || []).find(d => String(d.id) === String(rmDriverId));
    if (rec) rec.rute = checked;
    showToast('✅ Rute berhasil disimpan');
    closeRuteModal();
    renderTable();
  } catch (e) {
    showToast(`❌ ${e.message}`, 'error');
  } finally {
    btn.disabled = false;
    btn.textContent = 'Simpan Rute';
  }
}

document.getElementById('rmClose').addEventListener('click', closeRuteModal);
document.getElementById('rmCancel').addEventListener('click', closeRuteModal);
document.getElementById('rmSave').addEventListener('click', saveRuteModal);
document.getElementById('rmOverlay').addEventListener('click', e => { if (e.target.id === 'rmOverlay') closeRuteModal(); });

// ── Reset Semua Koordinat (per tab aktif: Bus atau MPU) ─────────────
async function resetAllCoords() {
  const table   = currentTab === 'bus' ? 'driver_bus' : 'driver_mpu';
  const label   = currentTab === 'bus' ? 'Bus' : 'MPU';
  const confirmed = await confirmDangerModal(
    `Semua koordinat lokasi terakhir driver ${label} akan dikosongkan. Koordinat akan otomatis terisi lagi begitu driver membuka link absen RFID-nya.`,
    'Reset semua koordinat?'
  );
  if (!confirmed) return;

  const btn = document.getElementById('btnResetCoord');
  btn.disabled = true;
  try {
    await sbPatch(table, 'or=(lat.not.is.null,lng.not.is.null)', { lat: null, lng: null, update_at: null });
    (data[currentTab] || []).forEach(d => { d.lat = null; d.lng = null; d.update_at = null; });
    showToast(`✅ Koordinat driver ${label} direset`);
    renderTable();
  } catch (e) {
    showToast(`❌ ${e.message}`, 'error');
  } finally {
    btn.disabled = false;
  }
}
document.getElementById('btnResetCoord').addEventListener('click', resetAllCoords);

// ── Helpers ────────────────────────────────────────────────────────
function esc(s){ return String(s??'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;'); }

function switchTab(tab) {
  currentTab = tab;
  document.getElementById('tabBus').classList.toggle('active', tab==='bus');
  document.getElementById('tabMpu').classList.toggle('active', tab==='mpu');
  document.getElementById('searchInput').value = '';
  populateTrayekSelect('nTrayek', tab, '');
  renderTable();
}

let _tt;
function showToast(msg, type='success') {
  const el = document.getElementById('toast');
  el.textContent = msg;
  el.className = `toast ${type} show`;
  clearTimeout(_tt);
  _tt = setTimeout(()=>el.classList.remove('show'), 3200);
}

// ── Events ─────────────────────────────────────────────────────────
document.getElementById('searchInput').addEventListener('input', renderTable);
document.getElementById('btnRefresh').addEventListener('click', loadAll);
document.getElementById('nNum').addEventListener('keydown',  e=>{ if(e.key==='Enter') document.getElementById('nSuf').focus(); });
document.getElementById('nSuf').addEventListener('keydown',  e=>{ if(e.key==='Enter') document.getElementById('nDrv1').focus(); });
document.getElementById('nDrv1').addEventListener('keydown', e=>{ if(e.key==='Enter') document.getElementById('nDrv2').focus(); });
document.getElementById('nDrv2').addEventListener('keydown', e=>{ if(e.key==='Enter') addDriver(); });

let _rtReloadTimer = null;
function scheduleReload() {
  clearTimeout(_rtReloadTimer);
  _rtReloadTimer = setTimeout(() => { loadAll(); }, 400);
}
if (sbRealtime) {
  sbRealtime
    .channel('admin-data-driver')
    .on('postgres_changes', { event: '*', schema: 'public', table: 'driver_bus' }, scheduleReload)
    .on('postgres_changes', { event: '*', schema: 'public', table: 'driver_mpu' }, scheduleReload)
    .on('postgres_changes', { event: '*', schema: 'public', table: 'trayek_bus' }, scheduleReload)
    .on('postgres_changes', { event: '*', schema: 'public', table: 'trayek_mpu' }, scheduleReload)
    .on('postgres_changes', { event: '*', schema: 'public', table: 'map' }, scheduleReload)
    .subscribe();
}

loadAll();
</script>
</body>
</html>