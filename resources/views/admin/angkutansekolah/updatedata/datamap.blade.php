<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
<meta name="csrf-token" content="{{ csrf_token() }}"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Edit Data Map - Admin</title>
  <link rel="icon" href="/favicon.ico"/>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/admin/admin-shell.css">
  <style>
    .adm-shell{display:flex!important}.adm-main{flex:1;min-width:0}
    .page-info{display:flex;align-items:flex-start;gap:12px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:12px;padding:14px 16px;margin-bottom:22px;font-size:13px;color:#1e40af;line-height:1.7}
    .page-info svg{flex-shrink:0;margin-top:2px}
    .tbadge{display:inline-block;background:var(--accent);color:#fff;padding:1px 8px;border-radius:4px;font-size:11px;font-family:'DM Mono',monospace;margin:0 2px}

    /* Toolbar */
    .toolbar{display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin-bottom:20px}
    .search-wrap{position:relative;flex:1;min-width:180px;max-width:300px}
    .search-wrap svg{position:absolute;left:11px;top:50%;transform:translateY(-50%);color:var(--text-4);pointer-events:none}
    .search-input{width:100%;padding:8px 11px 8px 36px;border:1.5px solid var(--border);border-radius:9px;font-family:'DM Sans',sans-serif;font-size:13px;color:var(--text-1);background:var(--surface-2);outline:none;transition:border-color .15s,box-shadow .15s}
    .search-input:focus{border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-glow);background:#fff}

    /* Route list */
    .route-list{display:flex;flex-direction:column;gap:12px}

    .route-block{background:var(--surface);border:1.5px solid var(--border);border-radius:14px;overflow:hidden;transition:border-color .2s,box-shadow .2s}
    .route-block:hover{border-color:var(--border-2)}
    .route-block.expanded{border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-glow)}

    /* Route header */
    .route-head{display:flex;align-items:center;gap:12px;padding:14px 18px;cursor:pointer;user-select:none;transition:background .15s}
    .route-head:hover{background:var(--surface-2)}
    .route-expand-icon{transition:transform .2s;color:var(--text-3);flex-shrink:0}
    .route-block.expanded .route-expand-icon{transform:rotate(90deg);color:var(--accent)}

    .id-map-tag{display:inline-flex;align-items:center;gap:6px;background:var(--navy);color:#fff;font-family:'DM Mono',monospace;font-size:12.5px;font-weight:600;padding:4px 12px;border-radius:8px;flex-shrink:0}
    .route-meta{flex:1;min-width:0}
    .route-title{font-size:13.5px;font-weight:600;color:var(--text-1);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .route-sub{font-size:12px;color:var(--text-4);margin-top:2px}
    .count-pill{display:inline-flex;align-items:center;padding:3px 10px;border-radius:99px;font-size:11.5px;font-weight:600;background:#dbeafe;color:#1d4ed8;border:1px solid #bfdbfe;flex-shrink:0}

    /* code_map badge on route head */
    .code-map-tag{display:inline-flex;align-items:center;padding:3px 10px;border-radius:6px;font-size:11.5px;font-weight:600;background:#fef3c7;color:#92400e;border:1px solid #fde68a;flex-shrink:0;font-family:'DM Mono',monospace}

    /* Trayek badge on route head */
    .trayek-tag{display:inline-flex;align-items:center;padding:3px 10px;border-radius:6px;font-size:11.5px;font-weight:600;background:#dcfce7;color:#15803d;border:1px solid #bbf7d0;flex-shrink:0}

    @media (max-width: 640px) {
      .route-head { flex-wrap: wrap; gap: 8px; padding: 10px 12px; }
      .route-meta { width: 100%; order: 3; }
      .points-section { overflow-x: auto; -webkit-overflow-scrolling: touch; }
      .points-table { min-width: 680px; }
    }
    /* Expanded body */
    .route-body{border-top:1.5px solid var(--border);padding:0;display:none}
    .route-block.expanded .route-body{display:block}

    /* Trayek read-only row */
    .trayek-row{display:flex;align-items:center;gap:10px;padding:12px 18px;border-bottom:1px dashed var(--border);background:var(--surface-2)}
    .trayek-row label{font-size:11px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--text-3);flex-shrink:0}
    .trayek-readonly{flex:1;display:flex;align-items:center;gap:8px;background:#f1f5f9;border:1.5px solid var(--border);border-radius:8px;padding:7px 12px;font-size:13px;font-family:'DM Sans',sans-serif;color:var(--text-2);cursor:default;min-height:36px}
    

    /* code_map read-only row */
    .codemap-row{display:flex;align-items:center;gap:10px;padding:10px 18px;border-bottom:1px dashed var(--border);background:#fffbeb}
    .codemap-row label{font-size:11px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#92400e;flex-shrink:0}
    .codemap-val{font-family:'DM Mono',monospace;font-size:13px;font-weight:600;color:#78350f;background:#fef3c7;padding:5px 14px;border-radius:8px;border:1.5px solid #fde68a;display:flex;align-items:center;gap:8px}
    .codemap-auto-badge{font-size:10px;background:#fbbf24;color:#fff;padding:2px 7px;border-radius:99px;font-weight:700;letter-spacing:.05em}

    /* Points table */
    .points-section{padding:16px 18px}
    .points-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:12px}
    .points-label{font-size:11px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--text-3)}

    .points-table{width:100%;border-collapse:collapse;font-size:13px}
    .points-table thead th{padding:8px 10px;font-size:10.5px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:var(--text-3);border-bottom:1.5px solid var(--border);text-align:left;background:var(--surface-2)}
    .points-table thead th:first-child{width:44px;text-align:center}
    .points-table thead th:last-child{width:52px;text-align:center}
    .points-table tbody tr{border-bottom:1px solid var(--border);transition:background .1s}
    .points-table tbody tr:last-child{border-bottom:none}
    .points-table tbody tr:hover{background:#f8fafc}

    .urutan-cell{text-align:center;font-family:'DM Mono',monospace;font-size:12px;color:var(--text-4);width:44px}
    .drag-handle{cursor:grab;color:var(--text-4);padding:0 6px;display:inline-flex;align-items:center}
    .drag-handle:active{cursor:grabbing}
    .pt-row.dragging{opacity:.4;background:#eff6ff!important}
    .pt-row.drag-over{border-top:2px solid var(--accent)!important}

    .pt-input{width:100%;background:transparent;border:1.5px solid transparent;border-radius:7px;padding:6px 8px;font-size:13px;font-family:'DM Sans',sans-serif;color:var(--text-1);outline:none;transition:border-color .15s,background .15s}
    .pt-input:hover{border-color:var(--border);background:var(--surface-2)}
    .pt-input:focus{border-color:var(--accent);background:#fff;box-shadow:0 0 0 2px var(--accent-glow)}
    .pt-input.mono{font-family:'DM Mono',monospace;font-size:12px}
    .pt-input.coord{width:110px}

    .btn-del-pt{display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border:none;border-radius:7px;background:#fff0f0;color:#ef4444;cursor:pointer;transition:background .15s;flex-shrink:0}
    .btn-del-pt:hover{background:#fecaca}

    /* Add point button */
    .btn-add-pt{display:flex;align-items:center;gap:7px;width:100%;padding:9px 12px;margin-top:10px;border:1.5px dashed var(--border);border-radius:9px;background:transparent;font-family:'DM Sans',sans-serif;font-size:13px;color:var(--text-3);cursor:pointer;transition:all .15s;justify-content:center}
    .btn-add-pt:hover{border-color:var(--accent);color:var(--accent);background:#eff6ff}
    .btn-add-pt svg{flex-shrink:0}

    /* Footer actions */
    .route-foot{display:flex;align-items:center;gap:10px;padding:12px 18px;border-top:1.5px solid var(--border);background:var(--surface-2)}
    .btn-save-map{display:flex;align-items:center;gap:7px;padding:9px 20px;background:var(--accent);color:white;border:none;border-radius:9px;font-family:'DM Sans',sans-serif;font-size:13px;font-weight:600;cursor:pointer;transition:all .15s}
    .btn-save-map:hover:not(:disabled){background:#2563eb;box-shadow:0 4px 14px rgba(59,130,246,.3)}
    .btn-save-map:disabled{background:var(--text-4);cursor:not-allowed}
    .btn-save-map.saved{background:var(--emerald)}
    .save-status{font-size:12px;color:var(--text-4)}

    /* New route modal */
    .modal-overlay{position:fixed;inset:0;background:rgba(15,23,42,.45);backdrop-filter:blur(3px);z-index:1000;display:flex;align-items:center;justify-content:center;padding:20px;opacity:0;pointer-events:none;transition:opacity .2s}
    .modal-overlay.open{opacity:1;pointer-events:all}
    .modal-box{background:var(--surface);border:1.5px solid var(--border);border-radius:18px;width:100%;max-width:480px;box-shadow:0 20px 60px rgba(15,23,42,.2);transform:translateY(14px) scale(.97);transition:transform .2s;overflow:hidden}
    .modal-overlay.open .modal-box{transform:translateY(0) scale(1)}
    .modal-hd{display:flex;align-items:center;justify-content:space-between;padding:18px 22px;border-bottom:1.5px solid var(--border);background:var(--surface-2)}
    .modal-hd h2{font-size:14.5px;font-weight:700;color:var(--text-1)}
    .modal-close{width:30px;height:30px;border:none;background:var(--border);border-radius:7px;cursor:pointer;display:flex;align-items:center;justify-content:center;color:var(--text-3);transition:all .15s}
    .modal-close:hover{background:#fef2f2;color:#ef4444}
    .modal-bd{padding:22px}
    .modal-field{margin-bottom:16px}
    .modal-field label{display:block;font-size:11px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--text-3);margin-bottom:7px}
    .modal-field input,.modal-field select{width:100%;padding:9px 12px;border:1.5px solid var(--border);border-radius:9px;font-family:'DM Sans',sans-serif;font-size:13px;color:var(--text-1);background:var(--surface-2);outline:none;transition:border-color .15s,box-shadow .15s;box-sizing:border-box}
    .modal-field select{appearance:none;-webkit-appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='13' height='13' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 10px center;padding-right:30px}
    .modal-field input:focus,.modal-field select:focus{border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-glow);background:#fff}
    .modal-ft{display:flex;justify-content:flex-end;gap:10px;padding:16px 22px;border-top:1.5px solid var(--border);background:var(--surface-2)}

    /* Nomor rute field - hanya muncul saat MPU */
    .modal-field.rute-num-field{display:none}
    .modal-field.rute-num-field.visible{display:block}
    .rute-num-hint{font-size:11px;color:var(--text-4);margin-top:4px;line-height:1.5}

    /* code_map preview field in modal */
    .codemap-preview-box{background:#fffbeb;border:1.5px solid #fde68a;border-radius:9px;padding:9px 14px;font-family:'DM Mono',monospace;font-size:13px;font-weight:600;color:#78350f;display:flex;align-items:center;justify-content:space-between;min-height:39px}
    .codemap-preview-box .empty{color:#d97706;font-weight:400;font-family:'DM Sans',sans-serif;font-size:12px;font-style:italic}
    .codemap-preview-badge{font-size:10px;background:#fbbf24;color:#fff;padding:2px 7px;border-radius:99px;font-weight:700;letter-spacing:.05em;flex-shrink:0}

    /* Skeleton */
    .skel{background:linear-gradient(90deg,var(--border) 25%,var(--surface-2) 50%,var(--border) 75%);background-size:200%;animation:shimmer 1.4s infinite;border-radius:6px}
    @keyframes shimmer{0%{background-position:200% 0}100%{background-position:-200% 0}}
    .bspin{width:13px;height:13px;border:2px solid rgba(255,255,255,.3);border-top-color:#fff;border-radius:50%;animation:spin .6s linear infinite}
    @keyframes spin{to{transform:rotate(360deg)}}

    /* Toast */
    .toast{position:fixed;bottom:24px;right:24px;z-index:9999;display:flex;align-items:center;gap:8px;padding:11px 16px;border-radius:10px;font-size:13px;font-weight:500;box-shadow:0 8px 24px rgba(15,23,42,.15);border:1.5px solid transparent;opacity:0;transform:translateY(10px);transition:opacity .2s,transform .2s;pointer-events:none;max-width:320px}
    .toast.show{opacity:1;transform:translateY(0)}
    .toast.success{background:#f0fdf4;border-color:#bbf7d0;color:#15803d}
    .toast.error{background:#fef2f2;border-color:#fecaca;color:#b91c1c}
    .empty-state{text-align:center;padding:48px 20px;color:var(--text-4)}
    .empty-state svg{display:block;margin:0 auto 12px;opacity:.3}
  </style>
</head>
<body>
<div class="adm-shell">
  @include('admin.partials.sidebar', ['currentPage' => 'update_map', 'currentModule' => 'angkutansekolah'])
  <main class="adm-main">
    <div class="adm-content">
      <div class="adm-page-header">
        <div>
          <h1 class="adm-page-title">Edit Data Map</h1>
          <p class="adm-page-subtitle">Kelola titik-titik rute di tabel <strong>map</strong> untuk tampilan peta</p>
        </div>
        <div style="display:flex;gap:8px">
          <button class="btn btn-secondary btn-sm" id="btnRefresh">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
            Refresh
          </button>
          <button class="btn btn-primary btn-sm" id="btnNewRoute">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Rute Baru
          </button>
        </div>
      </div>

      <div class="page-info">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <div>
          Setiap <strong>id_map</strong> = 1 rute lengkap. Klik baris untuk expand, lalu edit titik lokasi.<br>
          Format id_map - Bus: <span class="tbadge">kalidawir1_pagi</span> &nbsp; MPU: <span class="tbadge">mpu_campurdarat_rute1_siang</span><br>
          <strong>code_map</strong> dibuat otomatis dari id_map - Bus: <span class="tbadge">rute_karangrejo</span> &nbsp; MPU: <span class="tbadge">rute_mpu_campurdarat</span>
        </div>
      </div>

      <!-- Toolbar -->
      <div class="toolbar">
        <div class="search-wrap">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input type="text" id="searchInput" class="search-input" placeholder="Cari id_map, nama trayek, tempat..."/>
        </div>
        <span id="routeCount" style="font-size:12.5px;color:var(--text-4);white-space:nowrap"></span>
      </div>

      <div class="route-list" id="routeList">
        <!-- skeleton -->
        @for ($i = 0; $i < 4; $i++)
        <div class="route-block"><div class="route-head" style="gap:12px;padding:14px 18px"><div class="skel" style="width:130px;height:28px;border-radius:8px"></div><div class="skel" style="flex:1;height:16px;border-radius:4px"></div><div class="skel" style="width:60px;height:22px;border-radius:99px"></div></div></div>
        @endfor
      </div>

    </div>
  </main>
</div>

<!-- New Route Modal -->
<div class="modal-overlay" id="newRouteModal">
  <div class="modal-box">
    <div class="modal-hd">
      <h2>Buat Rute Baru</h2>
      <button class="modal-close" id="modalClose">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="modal-bd">

      <!-- Trayek -->
      <div class="modal-field">
        <label>Trayek <span style="color:#ef4444">*</span></label>
        <select id="newTrayekSel" onchange="onModalTrayekChange()">
          <option value="">- Pilih Trayek -</option>
        </select>
      </div>

      <!-- Waktu -->
      <div class="modal-field">
        <label>Waktu <span style="color:#ef4444">*</span></label>
        <select id="newWaktuSel" onchange="updateIdMapPreview()"
          style="width:100%;padding:9px 12px;border:1.5px solid var(--border);border-radius:9px;font-family:'DM Sans',sans-serif;font-size:13px;color:var(--text-1);background:var(--surface-2);outline:none;appearance:none;-webkit-appearance:none;background-image:url('data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'13\' height=\'13\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'%2394a3b8\' stroke-width=\'2\'%3E%3Cpolyline points=\'6 9 12 15 18 9\'/%3E%3C/svg%3E');background-repeat:no-repeat;background-position:right 10px center;padding-right:30px;box-sizing:border-box">
          <option value="">- Pilih Waktu -</option>
          <option value="pagi">Pagi</option>
          <option value="siang">Siang</option>
          <option value="sore">Sore</option>
          <option value="malam">Malam</option>
        </select>
      </div>

      <!-- Nomor Rute - hanya muncul untuk MPU -->
      <div class="modal-field rute-num-field" id="fieldRuteNum">
        <label>
          Nomor Rute
          <span style="font-size:10px;font-weight:500;color:var(--text-4);text-transform:none;letter-spacing:0">(khusus MPU)</span>
        </label>
        <input type="number" id="newRuteNum" min="1" max="99" value="1"
          placeholder="1" oninput="updateIdMapPreview()"
          style="font-family:'DM Mono',monospace"/>
        <div class="rute-num-hint">
          Digunakan untuk membedakan variasi rute dalam trayek yang sama.<br>
          Contoh: <strong>rute1</strong> = Campurdarat → Tulungagung, <strong>rute2</strong> = Campurdarat → Boyolangu, dst.
        </div>
      </div>

      <!-- id_map preview -->
      <div class="modal-field">
        <label>
          id_map
          <span style="font-size:10px;font-weight:500;color:var(--text-4);text-transform:none;letter-spacing:0">(otomatis)</span>
        </label>
        <div style="position:relative">
          <input type="text" id="newIdMap" readonly
            style="background:#f1f5f9;color:var(--text-3);cursor:default;font-family:'DM Mono',monospace;font-size:13px;border:1.5px solid var(--border);border-radius:9px;padding:9px 12px;width:100%;box-sizing:border-box"
            placeholder="Pilih trayek &amp; waktu di atas..."/>
          <span style="position:absolute;right:10px;top:50%;transform:translateY(-50%);font-size:10px;background:#e0f2fe;color:#0369a1;padding:2px 7px;border-radius:99px;font-weight:600;pointer-events:none">AUTO</span>
        </div>
        <div style="font-size:11px;color:var(--text-4);margin-top:4px">
          Dibuat otomatis. Tidak bisa diedit manual.
        </div>
      </div>

      <!-- code_map preview -->
      <div class="modal-field">
        <label>
          code_map
          <span style="font-size:10px;font-weight:500;color:var(--text-4);text-transform:none;letter-spacing:0">(otomatis dari id_map)</span>
        </label>
        <div class="codemap-preview-box" id="newCodeMapPreview">
          <span class="empty">Pilih trayek &amp; waktu di atas...</span>
          <span class="codemap-preview-badge">AUTO</span>
        </div>
        <div style="font-size:11px;color:var(--text-4);margin-top:4px">
          Referensi grup rute. Dibuat otomatis, tidak bisa diedit.
        </div>
      </div>

    </div>
    <div class="modal-ft">
      <button class="btn btn-secondary btn-sm" id="modalCancel">Batal</button>
      <button class="btn btn-primary btn-sm" id="modalSave">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
        Buat Rute
      </button>
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
// service_role key tidak lagi dikirim ke browser (lihat admin/api/angkutansekolah/db.php).
const sbRealtime = window.supabase ? window.supabase.createClient(SB_URL_PUBLIC, SB_ANON_PUBLIC) : null;

// ── State ──────────────────────────────────────────────────────────
let allMapRows  = [];
let routeGroups = {};   // id_map → { points, id_trayek, nama_trayek, code_map }
let routeIds    = [];
let trayeksBus  = [];   // [{id, nama, jenis:'bus'}]
let trayeksMpu  = [];   // [{id, nama, jenis:'mpu'}]
let allTrayeks  = [];   // combined with label

// Drag state
let dragSrc = null;

// ── code_map generator ─────────────────────────────────────────────
// Aturan derivasi dari id_map:
//   MPU  → id_map: mpu_{slug}_rute{N}_{waktu}  → code_map: rute_mpu_{slug}
//   Bus  → id_map: {slug}_{waktu}               → code_map: rute_{slug}
//          id_map: {slug}_rute{N}_{waktu}        → code_map: rute_{slug}
//
// Contoh:
//   mpu_campurdarat_rute1_pagi   → rute_mpu_campurdarat
//   mpu_pagerwojo_rute2_siang    → rute_mpu_pagerwojo
//   karangrejo_pagi              → rute_karangrejo
//   kalidawir1_rute2_sore        → rute_kalidawir1
function generateCodeMap(idMap) {
  if (!idMap) return '';
  const WAKTU = ['pagi','siang','sore','malam'];

  if (idMap.startsWith('mpu_')) {
    // Format: mpu_{slug}_rute{N}_{waktu}
    // Ambil bagian setelah 'mpu_', buang '_rute{N}_{waktu}' di akhir
    let rest = idMap.slice(4); // hilangkan 'mpu_'
    // Buang _rute{N}_{waktu} atau _{waktu} di akhir
    rest = rest.replace(/_rute\d+_(?:pagi|siang|sore|malam)$/, '');
    rest = rest.replace(/_(?:pagi|siang|sore|malam)$/, '');
    return rest ? `rute_mpu_${rest}` : '';
  } else {
    // Format: {slug}_{waktu} atau {slug}_rute{N}_{waktu}
    let slug = idMap;
    slug = slug.replace(/_rute\d+_(?:pagi|siang|sore|malam)$/, '');
    slug = slug.replace(/_(?:pagi|siang|sore|malam)$/, '');
    return slug ? `rute_${slug}` : '';
  }
}

// ── id_map generator ──────────────────────────────────────────────
// Bus  : {slug}_{waktu}                    → kalidawir1_pagi
//        jika nomor rute > 1: {slug}_rute{N}_{waktu}
// MPU  : mpu_{slug}_rute{N}_{waktu}        → mpu_campurdarat_rute1_siang
function generateIdMap(trayekNama, jenis, waktu, ruteNum) {
  if (!trayekNama || !waktu) return '';

  const beforeDash = trayekNama.trim().split(/\s+[-–]\s+/)[0];
  const slug = beforeDash.toLowerCase().replace(/[^a-z0-9]+/g, '');
  if (!slug) return '';

  const num = parseInt(ruteNum) || 1;

  if (jenis === 'mpu') {
    return `mpu_${slug}_rute${num}_${waktu}`;
  } else {
    if (num > 1) return `${slug}_rute${num}_${waktu}`;
    return `${slug}_${waktu}`;
  }
}

// ── Fetch ──────────────────────────────────────────────────────────
// sbGet/sbPost/sbPatch/sbDelete sekarang datang dari sb-secure.js
// (lewat admin/api/angkutansekolah/db.php, service key dipegang server).

async function loadAll() {
  try {
    const [mapRows, trBus, trMpu] = await Promise.all([
      sbGetShared('map','select=*&order=id_map.asc,urutan.asc'),
      sbGetShared('trayek_bus','select=id,nama&order=nama.asc'),
      sbGetShared('trayek_mpu','select=id,nama&order=nama.asc'),
    ]);

    allMapRows = mapRows;
    trayeksBus = trBus.map(t => ({...t, jenis:'bus'}));
    trayeksMpu = trMpu.map(t => ({...t, jenis:'mpu'}));
    allTrayeks = [
      ...trayeksBus.map(t => ({...t, label:`[Bus] ${t.nama}`})),
      ...trayeksMpu.map(t => ({...t, label:`[MPU] ${t.nama}`})),
    ];

    buildGroups(mapRows);
    renderList();
    populateNewRouteModal();
  } catch(e) {
    document.getElementById('routeList').innerHTML =
      `<div class="empty-state"><svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>Gagal memuat: ${esc(e.message)}</div>`;
  }
}

function buildGroups(rows) {
  routeGroups = {}; routeIds = [];
  rows.forEach(r => {
    if (!routeGroups[r.id_map]) {
      // Gunakan code_map dari DB jika ada, fallback ke generate
      const codeMap = r.code_map || generateCodeMap(r.id_map);
      routeGroups[r.id_map] = {
        points      : [],
        id_trayek   : r.id_trayek   || '',
        nama_trayek : r.nama_trayek || '',
        code_map    : codeMap,
      };
      routeIds.push(r.id_map);
    }
    routeGroups[r.id_map].points.push({
      nama_tempat : r.nama_tempat || '',
      lat         : r.lat        || '',
      lng         : r.lng        || '',
    });
  });
}

// ── Render list ────────────────────────────────────────────────────
function renderList() {
  const search = document.getElementById('searchInput').value.toLowerCase();
  const ids = routeIds.filter(id => {
    if (!search) return true;
    const g = routeGroups[id];
    return id.toLowerCase().includes(search) ||
      (g.nama_trayek||'').toLowerCase().includes(search) ||
      (g.code_map||'').toLowerCase().includes(search) ||
      g.points.some(p => (p.nama_tempat||'').toLowerCase().includes(search));
  });

  document.getElementById('routeCount').textContent = `${ids.length} rute`;
  const list = document.getElementById('routeList');

  if (!ids.length) {
    list.innerHTML = `<div class="empty-state"><svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>Tidak ada rute.</div>`;
    return;
  }

  list.innerHTML = ids.map(id => buildRouteBlock(id)).join('');

  list.querySelectorAll('.route-head').forEach(hd => {
    hd.addEventListener('click', () => toggleRoute(hd.dataset.id));
  });
}

function buildRouteBlock(id) {
  const g       = routeGroups[id];
  const trId    = g.id_trayek;
  const tr      = allTrayeks.find(t => String(t.id) === String(trId));
  const trLabel = tr ? tr.label : (g.nama_trayek || '-');
  const codeMap = g.code_map || generateCodeMap(id);

  const trOpts = ''; // tidak dipakai - trayek read-only

  const ptRows = g.points.map((p,i) => buildPointRow(id, i, p)).join('');

  return `
  <div class="route-block" id="rb_${esc(id)}">
    <div class="route-head" data-id="${esc(id)}">
      <svg class="route-expand-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
      <div class="id-map-tag">
        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"/><line x1="8" y1="2" x2="8" y2="18"/><line x1="16" y1="6" x2="16" y2="22"/></svg>
        ${esc(id)}
      </div>
      <div class="route-meta">
        <div class="route-title">${esc(trLabel)}</div>
        <div class="route-sub">${g.points.map(p=>p.nama_tempat).filter(Boolean).slice(0,4).join(' → ')}${g.points.length>4?` +${g.points.length-4} lagi`:''}</div>
      </div>
      ${codeMap ? `<span class="code-map-tag" title="code_map">${esc(codeMap)}</span>` : ''}
      <span class="count-pill">${g.points.length} titik</span>
    </div>
    <div class="route-body" id="body_${esc(id)}">
      <!-- Trayek row - read-only -->
      <div class="trayek-row">
        <label>Trayek</label>
      </div>
      <!-- code_map read-only row -->
      <div class="codemap-row">
        <label>code_map</label>
        <div class="codemap-val" id="codeMapVal_${esc(id)}">
          ${esc(codeMap) || '<span style="color:#d97706;font-weight:400;font-size:12px;font-style:italic">-</span>'}
          <span class="codemap-auto-badge">AUTO</span>
        </div>
      </div>
      <!-- Points -->
      <div class="points-section">
        <div class="points-head">
          <span class="points-label">Titik Rute</span>
        </div>
        <table class="points-table">
          <thead>
            <tr>
              <th>No.</th>
              <th style="width:24px"></th>
              <th>Nama Tempat</th>
              <th style="width:110px">Lat</th>
              <th style="width:110px">Lng</th>
              <th></th>
            </tr>
          </thead>
          <tbody id="pts_${esc(id)}">${ptRows}</tbody>
        </table>
        <button class="btn-add-pt" onclick="addPoint('${esc(id)}')">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Tambah Titik
        </button>
      </div>
      <!-- Footer -->
      <div class="route-foot">
        <button class="btn-save-map" id="btnSaveMap_${esc(id)}" onclick="saveRoute('${esc(id)}')">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
          Simpan Rute
        </button>
        <span class="save-status" id="saveStatus_${esc(id)}"></span>
        <div style="flex:1"></div>
        <button class="btn btn-danger btn-sm" onclick="deleteRoute('${esc(id)}')">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
          Hapus Rute
        </button>
      </div>
    </div>
  </div>`;
}

function buildPointRow(id, i, p) {
  return `
  <tr class="pt-row" draggable="true" data-id="${esc(id)}" data-idx="${i}"
    ondragstart="onDragStart(event)" ondragover="onDragOver(event)" ondrop="onDrop(event,this)" ondragleave="onDragLeave(event)">
    <td class="urutan-cell">${i+1}</td>
    <td style="width:24px;text-align:center">
      <span class="drag-handle" title="Geser untuk ubah urutan">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
      </span>
    </td>
    <td><input class="pt-input" type="text" placeholder="Nama tempat / halte" value="${esc(p.nama_tempat)}" data-field="nama_tempat"/></td>
    <td><input class="pt-input mono coord" type="text" placeholder="-8.0000" value="${esc(p.lat)}" data-field="lat"/></td>
    <td><input class="pt-input mono coord" type="text" placeholder="111.9000" value="${esc(p.lng)}" data-field="lng"/></td>
    <td style="text-align:center">
      <button class="btn-del-pt" onclick="delPoint(this)" title="Hapus titik">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </td>
  </tr>`;
}

// ── Toggle expand ──────────────────────────────────────────────────
function toggleRoute(id) {
  document.getElementById(`rb_${id}`).classList.toggle('expanded');
}

// ── Add point ──────────────────────────────────────────────────────
function addPoint(id) {
  const tbody = document.getElementById(`pts_${id}`);
  const idx   = tbody.querySelectorAll('.pt-row').length;
  const tmp   = document.createElement('tbody');
  tmp.innerHTML = buildPointRow(id, idx, {nama_tempat:'',lat:'',lng:''});
  const newRow = tmp.querySelector('tr');
  newRow.addEventListener('dragstart', onDragStart);
  newRow.addEventListener('dragover',  e => onDragOver(e));
  newRow.addEventListener('drop',      e => onDrop(e, newRow));
  newRow.addEventListener('dragleave', onDragLeave);
  tbody.appendChild(newRow);
  newRow.querySelector('input').focus();
  renumberRows(tbody);
}

// ── Delete point ───────────────────────────────────────────────────
function delPoint(btn) {
  const row   = btn.closest('.pt-row');
  const tbody = row.closest('tbody');
  row.remove();
  renumberRows(tbody);
}

function renumberRows(tbody) {
  tbody.querySelectorAll('.pt-row').forEach((r,i) => {
    r.dataset.idx = i;
    r.querySelector('.urutan-cell').textContent = i+1;
  });
}

// ── Drag & drop reorder ────────────────────────────────────────────
function onDragStart(e) {
  dragSrc = e.currentTarget;
  dragSrc.classList.add('dragging');
  e.dataTransfer.effectAllowed = 'move';
}
function onDragOver(e) {
  e.preventDefault();
  const row = e.currentTarget;
  if (row !== dragSrc) row.classList.add('drag-over');
}
function onDragLeave(e) { e.currentTarget.classList.remove('drag-over'); }
function onDrop(e, target) {
  e.preventDefault();
  target.classList.remove('drag-over');
  if (!dragSrc || dragSrc === target) { dragSrc = null; return; }
  const tbody  = target.closest('tbody');
  const rows   = [...tbody.querySelectorAll('.pt-row')];
  const srcIdx = rows.indexOf(dragSrc);
  const tgtIdx = rows.indexOf(target);
  if (srcIdx < tgtIdx) tbody.insertBefore(dragSrc, target.nextSibling);
  else tbody.insertBefore(dragSrc, target);
  dragSrc.classList.remove('dragging');
  dragSrc = null;
  renumberRows(tbody);
}

// ── Read points from DOM ───────────────────────────────────────────
function readPoints(id) {
  const tbody = document.getElementById(`pts_${id}`);
  return [...tbody.querySelectorAll('.pt-row')].map((row, i) => ({
    urutan      : i + 1,
    nama_tempat : row.querySelector('[data-field="nama_tempat"]').value.trim(),
    lat         : parseFloat(row.querySelector('[data-field="lat"]').value) || 0,
    lng         : parseFloat(row.querySelector('[data-field="lng"]').value) || 0,
  }));
}

// ── Save route ─────────────────────────────────────────────────────
async function saveRoute(id) {
  const btn     = document.getElementById(`btnSaveMap_${id}`);
  const status  = document.getElementById(`saveStatus_${id}`);
  // Trayek tidak bisa diubah - ambil dari state lokal
  const trId    = routeGroups[id]?.id_trayek   || null;
  const trNama  = routeGroups[id]?.nama_trayek || '';
  const points  = readPoints(id);
  const codeMap = generateCodeMap(id);   // ← selalu di-generate dari id_map

  if (points.length === 0)            { showToast('⚠️ Minimal harus ada 1 titik', 'error'); return; }
  if (points.find(p=>!p.nama_tempat)) { showToast('⚠️ Nama tempat tidak boleh kosong', 'error'); return; }

  btn.disabled = true;
  btn.innerHTML = '<span class="bspin"></span> Menyimpan...';
  status.textContent = '';

  try {
    // 1. Delete existing rows
    await sbDelete('map', `id_map=eq.${encodeURIComponent(id)}`);

    // 2. Insert new rows - sertakan code_map di setiap baris
    const rows = points.map(p => ({
      id_map      : id,
      code_map    : codeMap  || null,   // ← kolom code_map dikirim ke Supabase
      urutan      : p.urutan,
      id_trayek   : trId    || null,
      nama_trayek : trNama  || null,
      nama_tempat : p.nama_tempat,
      lat         : p.lat,
      lng         : p.lng,
    }));

    await sbPost('map', rows, 'return=minimal');

    // Update local state
    if (!routeGroups[id]) routeGroups[id] = {};
    routeGroups[id].points   = points;
    routeGroups[id].code_map = codeMap || '';
    // id_trayek & nama_trayek tidak diupdate - tidak bisa diubah

    // Update header preview
    const headSub    = document.querySelector(`#rb_${id} .route-sub`);
    const countPill  = document.querySelector(`#rb_${id} .count-pill`);
    const codeMapTag = document.querySelector(`#rb_${id} .route-head .code-map-tag`);
    if (headSub)    headSub.textContent    = points.map(p=>p.nama_tempat).slice(0,4).join(' → ') + (points.length>4?` +${points.length-4} lagi`:'');
    if (countPill)  countPill.textContent  = `${points.length} titik`;
    if (codeMapTag) codeMapTag.textContent = codeMap;

    showToast(`✅ Rute ${id} tersimpan (${points.length} titik)`);
    status.textContent = `Disimpan ${new Date().toLocaleTimeString('id-ID')}`;
    btn.classList.add('saved');
    btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> Tersimpan!';
    setTimeout(()=>{ btn.classList.remove('saved'); btn.disabled=false; btn.innerHTML='<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> Simpan Rute'; },2200);
  } catch(e) {
    showToast(`❌ ${e.message}`, 'error');
    btn.disabled = false;
    btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> Simpan Rute';
  }
}

// ── Delete route ───────────────────────────────────────────────────
async function deleteRoute(id) {
  if (!(await confirmDangerModal(`Hapus semua data rute dengan id_map = "${id}"?`))) return;
  try {
    await sbDelete('map', `id_map=eq.${encodeURIComponent(id)}`);
    delete routeGroups[id];
    routeIds = routeIds.filter(x=>x!==id);
    renderList();
    showToast(`🗑️ Rute ${id} dihapus`);
  } catch(e) {
    showToast(`❌ Gagal hapus: ${e.message}`, 'error');
  }
}

// ── Modal: saat trayek berubah ─────────────────────────────────────
function onModalTrayekChange() {
  const trSel  = document.getElementById('newTrayekSel');
  const trId   = trSel.value;
  const tr     = allTrayeks.find(t => String(t.id) === String(trId));
  const isMpu  = tr?.jenis === 'mpu';

  // Tampilkan/sembunyikan field nomor rute
  const fieldRuteNum = document.getElementById('fieldRuteNum');
  if (isMpu) {
    fieldRuteNum.classList.add('visible');
  } else {
    fieldRuteNum.classList.remove('visible');
    document.getElementById('newRuteNum').value = '1';
  }

  updateIdMapPreview();
}

function updateIdMapPreview() {
  const trSel   = document.getElementById('newTrayekSel');
  const waktu   = document.getElementById('newWaktuSel').value;
  const trOpt   = trSel.options[trSel.selectedIndex];
  const trId    = trSel.value;
  const nama    = trOpt?.dataset?.nama || '';
  const tr      = allTrayeks.find(t => String(t.id) === String(trId));
  const jenis   = tr?.jenis || 'bus';
  const ruteNum = parseInt(document.getElementById('newRuteNum').value) || 1;

  const idMap   = generateIdMap(nama, jenis, waktu, ruteNum);
  const codeMap = generateCodeMap(idMap);

  document.getElementById('newIdMap').value = idMap;

  // Update code_map preview
  const previewEl = document.getElementById('newCodeMapPreview');
  if (codeMap) {
    previewEl.innerHTML = `${esc(codeMap)}<span class="codemap-preview-badge">AUTO</span>`;
  } else {
    previewEl.innerHTML = `<span class="empty">Pilih trayek &amp; waktu di atas...</span><span class="codemap-preview-badge">AUTO</span>`;
  }
}

// ── Populate new route modal ───────────────────────────────────────
function populateNewRouteModal() {
  const sel = document.getElementById('newTrayekSel');
  sel.innerHTML = `<option value="">- Pilih Trayek -</option>` +
    allTrayeks.map(t => `<option value="${esc(t.id)}" data-nama="${esc(t.nama)}" data-jenis="${esc(t.jenis)}">${esc(t.label)}</option>`).join('');
}

// ── Modal open/close ───────────────────────────────────────────────
document.getElementById('btnNewRoute').addEventListener('click', () => {
  document.getElementById('newIdMap').value     = '';
  document.getElementById('newTrayekSel').value = '';
  document.getElementById('newWaktuSel').value  = '';
  document.getElementById('newRuteNum').value   = '1';
  document.getElementById('fieldRuteNum').classList.remove('visible');
  document.getElementById('newCodeMapPreview').innerHTML =
    `<span class="empty">Pilih trayek &amp; waktu di atas...</span><span class="codemap-preview-badge">AUTO</span>`;
  document.getElementById('newRouteModal').classList.add('open');
  document.getElementById('newTrayekSel').focus();
});

['modalClose','modalCancel'].forEach(id =>
  document.getElementById(id).addEventListener('click', closeModal)
);
document.getElementById('newRouteModal').addEventListener('click', e => {
  if (e.target === e.currentTarget) closeModal();
});
document.addEventListener('keydown', e => { if (e.key==='Escape') closeModal(); });
function closeModal() { document.getElementById('newRouteModal').classList.remove('open'); }

// ── Modal save ─────────────────────────────────────────────────────
document.getElementById('modalSave').addEventListener('click', async () => {
  const newId   = document.getElementById('newIdMap').value.trim();
  const trSel   = document.getElementById('newTrayekSel');
  const trId    = trSel.value;
  const trNama  = trSel.options[trSel.selectedIndex]?.dataset?.nama || '';
  const waktu   = document.getElementById('newWaktuSel').value;
  const codeMap = generateCodeMap(newId);   // ← generate code_map dari id_map baru

  if (!trId)  { showToast('⚠️ Pilih trayek terlebih dahulu', 'error'); return; }
  if (!waktu) { showToast('⚠️ Pilih waktu terlebih dahulu', 'error'); return; }
  if (!newId) { showToast('⚠️ id_map gagal digenerate', 'error'); return; }
  if (routeGroups[newId]) { showToast(`⚠️ id_map "${newId}" sudah ada`, 'error'); return; }

  const btn = document.getElementById('modalSave');
  btn.disabled = true;

  try {
    const row = {
      id_map      : newId,
      code_map    : codeMap  || null,   // ← kolom code_map dikirim ke Supabase
      urutan      : 1,
      id_trayek   : trId    || null,
      nama_trayek : trNama  || null,
      nama_tempat : '',
      lat         : 0,
      lng         : 0,
    };
    await sbPost('map', row, 'return=minimal');

    routeGroups[newId] = {
      points      : [{nama_tempat:'',lat:0,lng:0}],
      id_trayek   : trId    || '',
      nama_trayek : trNama  || '',
      code_map    : codeMap || '',
    };
    routeIds.push(newId);
    closeModal();
    renderList();
    setTimeout(() => {
      const block = document.getElementById(`rb_${newId}`);
      if (block) { block.classList.add('expanded'); block.scrollIntoView({behavior:'smooth',block:'center'}); }
    }, 100);
    showToast(`✅ Rute ${newId} dibuat (code_map: ${codeMap}) - isi titiknya sekarang`);
  } catch(e) {
    showToast(`❌ ${e.message}`, 'error');
  } finally {
    btn.disabled = false;
  }
});

// ── Helpers ────────────────────────────────────────────────────────
function esc(s){return String(s??'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;')}
let _tt; function showToast(msg,type='success'){const el=document.getElementById('toast');el.textContent=msg;el.className=`toast ${type} show`;clearTimeout(_tt);_tt=setTimeout(()=>el.classList.remove('show'),3200)}

document.getElementById('searchInput').addEventListener('input', renderList);
document.getElementById('btnRefresh').addEventListener('click', loadAll);

let _rtReloadTimer = null;
function scheduleReload() {
  clearTimeout(_rtReloadTimer);
  _rtReloadTimer = setTimeout(() => { loadAll(); }, 400);
}
if (sbRealtime) {
  sbRealtime
    .channel('admin-data-map')
    .on('postgres_changes', { event: '*', schema: 'public', table: 'map' }, scheduleReload)
    .on('postgres_changes', { event: '*', schema: 'public', table: 'trayek_bus' }, scheduleReload)
    .on('postgres_changes', { event: '*', schema: 'public', table: 'trayek_mpu' }, scheduleReload)
    .subscribe();
}

loadAll();
</script>
</body>
</html>