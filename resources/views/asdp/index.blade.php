<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ASDP Tulungagung</title>

  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
  <link rel="icon" href="/favicon.ico" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">

  <style>
    :root {
      --primary:        #3b82f6;
      --accent:         #06b6d4;
      --bg-glass:       rgba(10, 14, 28, 0.72);
      --bg-glass-light: rgba(15, 22, 46, 0.85);
      --border:         rgba(255,255,255,0.08);
      --border-hover:   rgba(6,182,212,0.4);
      --text:           #e2e8f0;
      --text-muted:     #64748b;
      --text-dim:       #94a3b8;
      --tambang-color:  #06b6d4;
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      background: #060b17;
      color: var(--text);
      height: 100vh;
      overflow: hidden;
    }

    #map { position: fixed; inset: 0; width: 100%; height: 100%; z-index: 0; }
    .leaflet-control-container { display: none !important; }
    .leaflet-tile-pane { filter: brightness(0.78) saturate(0.85); }

    .leaflet-overlay-pane,
    .leaflet-marker-pane,
    .leaflet-shadow-pane,
    .leaflet-tooltip-pane,
    .leaflet-popup-pane {
      filter: none !important;
    }

    .leaflet-overlay-pane path {
      filter: drop-shadow(0 0 3px rgba(0, 0, 0, 0.55));
    }

    /* ─── HEADER ─── */
    .header {
      position: fixed; top: 16px; left: 50%; transform: translateX(-50%);
      z-index: 1200; display: flex; align-items: center; gap: 12px;
      background: var(--bg-glass);
      backdrop-filter: blur(20px) saturate(180%);
      -webkit-backdrop-filter: blur(20px) saturate(180%);
      border: 1px solid var(--border); border-radius: 100px;
      padding: 10px 20px 10px 14px;
      box-shadow: 0 0 0 1px rgba(255,255,255,0.03), 0 8px 32px rgba(0,0,0,0.5);
      white-space: nowrap;
    }
    .header img { height: 32px; border-radius: 50%; }
    .header-divider { width: 1px; height: 22px; background: var(--border); }
    .header .title { font-weight: 700; font-size: 14px; letter-spacing: .3px; color: #f1f5f9; }
    .header .subtitle { font-size: 11px; color: var(--text-muted); font-weight: 400; }

    /* ─── BACK BUTTON ─── */
    .back-btn {
      position: fixed; top: 16px; left: 16px; z-index: 1250;
      display: flex; align-items: center; gap: 8px;
      background: var(--bg-glass); backdrop-filter: blur(20px) saturate(180%);
      -webkit-backdrop-filter: blur(20px) saturate(180%);
      border: 1px solid var(--border); border-radius: 100px;
      width: 40px; height: 40px; justify-content: center;
      color: var(--text); font-size: 13px; font-weight: 600;
      font-family: 'Plus Jakarta Sans', sans-serif; text-decoration: none; cursor: pointer;
      box-shadow: 0 4px 20px rgba(0,0,0,0.4); transition: all .25s cubic-bezier(.4,0,.2,1);
      overflow: hidden;
    }
    .back-btn svg { flex-shrink: 0; }
    .back-btn span {
      max-width: 0; opacity: 0; white-space: nowrap; overflow: hidden;
      transition: max-width .25s ease, opacity .2s ease, margin .25s ease;
    }
    .back-btn:hover {
      width: auto; padding: 0 18px 0 14px;
      border-color: var(--border-hover); background: rgba(15,22,46,0.92);
      box-shadow: 0 6px 24px rgba(0,0,0,0.5), 0 0 0 1px var(--border-hover);
      color: var(--accent);
    }
    .back-btn:hover span { max-width: 100px; opacity: 1; margin-left: 2px; }

    /* ─── MENU BTN ─── */
    .menu-btn {
      position: fixed; top: 80px; left: 16px; z-index: 1200;
      display: flex; align-items: center; gap: 8px;
      background: var(--bg-glass); backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      border: 1px solid var(--border); border-radius: 12px;
      padding: 10px 16px; color: var(--text); font-size: 13px; font-weight: 600;
      font-family: 'Plus Jakarta Sans', sans-serif; cursor: pointer;
      box-shadow: 0 4px 20px rgba(0,0,0,0.4); transition: all .2s ease;
    }
    .menu-btn:hover {
      border-color: var(--border-hover); background: rgba(15,22,46,0.92);
      transform: translateY(-1px);
      box-shadow: 0 6px 28px rgba(0,0,0,0.5), 0 0 0 1px var(--border-hover);
    }

    /* ─── EBOOK BTN ─── */
    .ebook-btn {
      position: fixed; bottom: 20px; right: 16px; z-index: 1200;
      display: flex; align-items: center; gap: 8px;
      background: var(--bg-glass); backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      border: 1px solid rgba(6,182,212,0.3); border-radius: 12px;
      padding: 10px 16px; color: var(--tambang-color); font-size: 13px; font-weight: 600;
      font-family: 'Plus Jakarta Sans', sans-serif; cursor: pointer;
      box-shadow: 0 4px 20px rgba(0,0,0,0.4), 0 0 20px rgba(6,182,212,0.1);
      transition: all .2s ease;
    }
    .ebook-btn:hover {
      background: rgba(6,182,212,0.1); border-color: rgba(6,182,212,0.5);
      transform: translateY(-2px);
      box-shadow: 0 8px 28px rgba(0,0,0,0.5), 0 0 30px rgba(6,182,212,0.2);
    }

    /* ─── EBOOK POPUP ─── */
    .ebook-popup {
      position: fixed; bottom: 75px; right: 16px;
      width: 560px; height: 360px;
      background: var(--bg-glass-light);
      backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px);
      border: 1px solid var(--border); border-radius: 16px;
      box-shadow: 0 20px 60px rgba(0,0,0,0.7);
      overflow: hidden; z-index: 1201; display: none;
      animation: fadeUp .25s ease;
    }
    @keyframes fadeUp { from{ opacity:0; transform:translateY(12px); } to{ opacity:1; transform:translateY(0); } }
    .ebook-popup iframe { width: 100%; height: 100%; border: none; }
    .ebook-close {
      position: absolute; top: 10px; right: 10px;
      width: 28px; height: 28px; border-radius: 6px;
      background: rgba(244,63,94,0.15); border: 1px solid rgba(244,63,94,0.3);
      color: #f43f5e; cursor: pointer; font-size: 13px; font-weight: 700;
      display: grid; place-items: center; z-index: 1202; transition: all .2s;
    }
    .ebook-close:hover { background: rgba(244,63,94,0.3); }

    /* ─── BACKDROP ─── */
    .menu-backdrop {
      position: fixed; inset: 0; z-index: 1050;
      background: rgba(0,0,0,0.4); backdrop-filter: blur(2px);
      opacity: 0; visibility: hidden; transition: all .3s;
    }
    .menu-backdrop.active { opacity: 1; visibility: visible; }

    /* ─── SIDE PANEL ─── */
    .menu-panel {
      position: fixed; top: 0; left: -380px;
      width: 340px; height: 100%;
      background: var(--bg-glass-light);
      backdrop-filter: blur(24px) saturate(160%);
      -webkit-backdrop-filter: blur(24px) saturate(160%);
      border-right: 1px solid var(--border);
      box-shadow: 8px 0 40px rgba(0,0,0,0.5);
      transition: left .3s cubic-bezier(.4,0,.2,1);
      z-index: 1100; display: flex; flex-direction: column; overflow: hidden;
    }
    .menu-panel.active { left: 0; }

    .panel-header { padding: 28px 20px 20px; border-bottom: 1px solid var(--border); flex-shrink: 0; }
    .panel-header-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }
    .panel-title { font-size: 18px; font-weight: 800; color: #f1f5f9; letter-spacing: -.2px; }
    .panel-close {
      width: 32px; height: 32px; border-radius: 8px;
      border: 1px solid var(--border); background: rgba(255,255,255,0.04);
      color: var(--text-dim); cursor: pointer; display: grid; place-items: center; transition: all .2s;
    }
    .panel-close:hover { background: rgba(244,63,94,0.12); border-color: rgba(244,63,94,0.3); color: #f43f5e; }

    .search-wrap { position: relative; }
    .search-wrap svg { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted); pointer-events: none; }
    .search-wrap input {
      width: 100%; padding: 10px 12px 10px 38px;
      background: rgba(255,255,255,0.04); border: 1px solid var(--border);
      border-radius: 10px; color: var(--text); font-family: 'Plus Jakarta Sans', sans-serif;
      font-size: 13px; outline: none; transition: all .2s;
    }
    .search-wrap input::placeholder { color: var(--text-muted); }
    .search-wrap input:focus { border-color: var(--border-hover); background: rgba(6,182,212,0.05); box-shadow: 0 0 0 3px rgba(6,182,212,0.1); }

    .stats-row { padding: 14px 20px; display: flex; gap: 8px; border-bottom: 1px solid var(--border); flex-shrink: 0; }
    .stat-card { flex: 1; background: rgba(255,255,255,0.03); border: 1px solid var(--border); border-radius: 10px; padding: 10px 12px; text-align: center; }
    .stat-card .num { font-size: 22px; font-weight: 800; color: #f1f5f9; font-family: 'DM Mono', monospace; }
    .stat-card .lbl { font-size: 10px; color: var(--text-muted); margin-top: 2px; font-weight: 500; text-transform: uppercase; letter-spacing: .5px; }

    .panel-list { flex: 1; overflow-y: auto; padding: 12px 10px; }
    .panel-list::-webkit-scrollbar { width: 4px; }
    .panel-list::-webkit-scrollbar-track { background: transparent; }
    .panel-list::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 4px; }

    .list-item { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 10px; cursor: pointer; transition: background .15s, transform .15s; margin-bottom: 3px; }
    .list-item:hover { background: rgba(255,255,255,0.06); transform: translateX(3px); }
    .list-item-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; background: var(--tambang-color); }
    .list-item-name { flex: 1; font-size: 13px; font-weight: 500; color: var(--text); line-height: 1.4; }

    .list-empty { padding: 24px 12px; text-align: center; color: var(--text-muted); font-size: 13px; }

    .panel-footer { padding: 14px 20px; border-top: 1px solid var(--border); flex-shrink: 0; }
    .beranda-button {
      display: flex; align-items: center; justify-content: center; gap: 8px;
      width: 100%; padding: 11px;
      background: linear-gradient(135deg, var(--primary) 0%, #2563eb 100%);
      color: white; text-decoration: none; border-radius: 10px;
      font-size: 13px; font-weight: 700; font-family: 'Plus Jakarta Sans', sans-serif;
      letter-spacing: .2px; box-shadow: 0 4px 20px rgba(59,130,246,0.3); transition: all .2s;
    }
    .beranda-button:hover { transform: translateY(-2px); box-shadow: 0 8px 28px rgba(59,130,246,0.45); }

    /* ─── POPUP ─── */
    .leaflet-popup-content-wrapper {
      background: var(--bg-glass-light) !important;
      backdrop-filter: blur(20px) !important; -webkit-backdrop-filter: blur(20px) !important;
      border: 1px solid var(--border) !important; border-radius: 16px !important;
      box-shadow: 0 20px 60px rgba(0,0,0,0.7), 0 0 0 1px rgba(255,255,255,0.05) !important;
      padding: 0 !important;
    }
    .leaflet-popup-tip-container { display: none; }
    .leaflet-popup-content { margin: 0 !important; width: auto !important; }
    .leaflet-popup-close-button { color: var(--text-muted) !important; top: 10px !important; right: 10px !important; font-size: 18px !important; z-index: 10; }
    .leaflet-popup-close-button:hover { color: #f43f5e !important; }
    .leaflet-popup-content-wrapper a.popup-dir-btn,
    .leaflet-popup-content a.popup-dir-btn { color: #ffffff !important; }

    .popup-card { width: 240px; overflow: hidden; border-radius: 16px; }
    .popup-card-img { width: 100%; height: 130px; object-fit: cover; display: block; }
    .popup-card-img-placeholder { width: 100%; height: 80px; background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); display: flex; align-items: center; justify-content: center; }
    .popup-card-body { padding: 14px 16px; }
    .popup-card h3 { font-size: 14px; font-weight: 700; color: #f1f5f9; margin-bottom: 5px; line-height: 1.3; }
    .popup-card p { font-size: 12px; color: var(--text-dim); line-height: 1.5; margin-bottom: 12px; }
    .popup-dir-btn {
      display: flex; align-items: center; justify-content: center; gap: 6px;
      width: 100%; padding: 9px;
      background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
      color: #ffffff !important; text-decoration: none; border-radius: 8px;
      font-size: 13px; font-weight: 800; font-family: 'Plus Jakarta Sans', sans-serif;
      letter-spacing: .3px; transition: all .2s;
      box-shadow: 0 4px 16px rgba(37,99,235,0.5);
      text-shadow: 0 1px 3px rgba(0,0,0,0.3);
    }
    .popup-dir-btn:hover { box-shadow: 0 6px 20px rgba(59,130,246,0.45); transform: translateY(-1px); }

    /* ─── DEV CREDIT ─── */
    .dev-credit { position: fixed; bottom: 10px; left: 14px; font-size: 11px; color: rgba(203,213,225,0.75); z-index: 1000; font-family: 'DM Mono', monospace; text-shadow: 0 1px 4px rgba(0,0,0,0.6); }
    .dev-credit a { color: #93c5fd; text-decoration: none; }
    .dev-credit a:hover { color: var(--primary); }

    /* ─── LOADING ─── */
    #loadingOverlay { position: fixed; inset: 0; background: #060b17; display: flex; flex-direction: column; align-items: center; justify-content: center; z-index: 9999; transition: opacity .5s ease, visibility .5s ease; }
    #loadingOverlay.hidden { opacity: 0; visibility: hidden; pointer-events: none; }

    .loading-spinner { width: 40px; height: 40px; border: 3px solid rgba(6,182,212,0.15); border-top-color: var(--accent); border-radius: 50%; animation: spin .9s linear infinite; margin-bottom: 20px; }
    @keyframes spin { to { transform: rotate(360deg); } }
    .loading-title { font-size: 18px; font-weight: 800; color: #f1f5f9; margin-bottom: 4px; letter-spacing: -.2px; }
    .loading-subtitle { font-size: 13px; color: var(--text-muted); margin-bottom: 28px; }

    .progress-wrap { width: 260px; text-align: center; }
    .progress-track { width: 100%; height: 3px; background: rgba(255,255,255,0.06); border-radius: 999px; overflow: hidden; margin-bottom: 12px; }
    .progress-bar { height: 100%; width: 0%; background: linear-gradient(90deg, var(--accent), #0284c7); border-radius: 999px; transition: width .18s linear; box-shadow: 0 0 10px rgba(6,182,212,0.6); }
    .progress-text { font-family: 'DM Mono', monospace; font-size: 13px; font-weight: 500; color: var(--text-muted); }

    /* ─── MARKER ICONS ─── */
    .marker-icon-wrap { overflow: visible !important; background: transparent !important; }
    .mi-inner {
      display: flex; flex-direction: column; align-items: center; gap: 5px;
      will-change: transform; transition: transform 0.25s ease;
      transform-origin: 50% 17px;
    }
    .mi-dot {
      width: 34px; height: 34px; border-radius: 50%;
      display: flex; align-items: center; justify-content: center; flex-shrink: 0;
      background: linear-gradient(145deg, #0e7490 0%, #082f49 100%);
      border: 2.5px solid #67e8f9;
      box-shadow: 0 3px 10px rgba(0,0,0,0.6), 0 0 0 3px rgba(6,182,212,0.15);
    }
    .mi-img { width: 20px; height: 20px; object-fit: contain; }
    .mi-label {
      background: rgba(6,13,23,0.94); border: 1px solid rgba(103,232,249,0.5);
      color: #ecfeff; border-radius: 100px; padding: 3px 9px;
      white-space: nowrap; max-width: 150px; overflow: hidden; text-overflow: ellipsis;
      font-family: 'Plus Jakarta Sans', sans-serif; font-size: 10.5px;
      font-weight: 700; letter-spacing: .2px;
      box-shadow: 0 3px 10px rgba(0,0,0,0.55);
    }

    @media (max-width: 600px) {
      .menu-panel { width: 88%; }
      .header .subtitle { display: none; }
      .ebook-popup { width: 90%; right: 5%; }
    }
  </style>
</head>
<body>

  <!-- LOADING -->
  <div id="loadingOverlay">
    <div class="loading-spinner"></div>
    <div class="loading-title">Peta Interaktif</div>
    <div class="loading-subtitle">Angkutan Sungai, Danau & Penyeberangan</div>
    <div class="progress-wrap" role="status" aria-live="polite">
      <div class="progress-track"><div id="progressBar" class="progress-bar"></div></div>
      <div id="progressText" class="progress-text">Memuat data…</div>
    </div>
  </div>

  <!-- BACK BUTTON -->
  <a class="back-btn" href="/" aria-label="Kembali ke beranda">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
      <line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>
    </svg>
    <span>Beranda</span>
  </a>

  <!-- HEADER -->
  <header class="header">
    <img src="/assets/dishub.png" alt="Dishub">
    <div class="header-divider"></div>
    <div>
      <div class="title">Dishub Tulungagung</div>
      <div class="subtitle">Angkutan Sungai, Danau & Penyeberangan</div>
    </div>
  </header>

  <!-- MENU BTN -->
  <button class="menu-btn" id="toggleMenu" aria-label="Buka daftar tambangan">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
      <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
    </svg>
    Daftar Tambangan
  </button>

  <!-- BACKDROP -->
  <div class="menu-backdrop" id="menuBackdrop"></div>

  <!-- SIDE PANEL -->
  <aside class="menu-panel" id="menuPanel" aria-hidden="true">
    <div class="panel-header">
      <div class="panel-header-top">
        <div class="panel-title">Daftar Tambangan</div>
        <button class="panel-close" id="closeMenu" aria-label="Tutup panel">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
          </svg>
        </button>
      </div>
      <div class="search-wrap">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
          <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        <input type="text" id="searchInput" placeholder="Cari nama tambangan…" autocomplete="off">
      </div>
    </div>

    <div class="stats-row">
      <div class="stat-card">
        <div class="num" id="statTotal">—</div>
        <div class="lbl">Total Lokasi</div>
      </div>
    </div>

    <div class="panel-list" id="locationsList"></div>

    <div class="panel-footer">
      <a href="/" target="_blank" class="beranda-button">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
          <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
          <polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/>
        </svg>
        Beranda
      </a>
    </div>
  </aside>

  <div id="map"></div>

  <!-- EBOOK BUTTON -->
  <button class="ebook-btn" id="openEbook">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
      <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
    </svg>
    E-Book Life Jacket
  </button>

  <!-- EBOOK POPUP -->
  <div class="ebook-popup" id="ebookPopup">
    <button class="ebook-close" id="closeEbook">✕</button>
    <iframe src="https://online.anyflip.com/xmchw/arjo/index.html" loading="lazy"></iframe>
  </div>

<div class="dev-credit" id="devCredit"></div>

@include('partials.dev-credit-script', ['containerId' => 'devCredit'])

  <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

  <script type="module">
    const PROXY = '/api/supabase-proxy';

    const loadingOverlay = document.getElementById("loadingOverlay");
    const progressBar    = document.getElementById("progressBar");
    const progressText   = document.getElementById("progressText");
    const toggleMenu     = document.getElementById("toggleMenu");
    const closeMenu      = document.getElementById("closeMenu");
    const menuPanel      = document.getElementById("menuPanel");
    const menuBackdrop   = document.getElementById("menuBackdrop");
    const searchInput    = document.getElementById("searchInput");
    const locationsList  = document.getElementById("locationsList");
    const statTotal      = document.getElementById("statTotal");
    const openEbook      = document.getElementById("openEbook");
    const ebookPopup     = document.getElementById("ebookPopup");
    const closeEbook     = document.getElementById("closeEbook");

    let progress = 0, smoothTimer = null, finishingTimer = null;

    function updateUIProgress(v) {
      const pct = Math.min(100, Math.max(0, Math.floor(v)));
      progressBar.style.width = pct + "%";
      progressText.textContent = pct < 100 ? `Memuat data… ${pct}%` : "Selesai!";
    }
    function startSmooth() {
      clearInterval(smoothTimer);
      smoothTimer = setInterval(() => {
        if (progress < 88) { progress = Math.min(88, progress + Math.random() * .8 + .2); updateUIProgress(progress); }
      }, 50);
    }
    function finalizeAndHide() {
      clearInterval(smoothTimer); clearInterval(finishingTimer);
      finishingTimer = setInterval(() => {
        if (progress < 100) {
          progress = Math.min(100, progress + Math.max(1, (100 - progress) * .12));
          updateUIProgress(progress);
        } else {
          clearInterval(finishingTimer);
          setTimeout(() => { loadingOverlay.classList.add("hidden"); loadingOverlay.setAttribute("aria-hidden","true"); }, 350);
        }
      }, 18);
    }
    startSmooth();

    const map = L.map('map', { zoomControl: false }).setView([-8.068, 111.985], 11);
    L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
      maxZoom: 19, subdomains: ['mt0','mt1','mt2','mt3']
    }).addTo(map);

    fetch('/assets/asdp/tulungagung.geojson').then(r => r.json()).then(data => {
      L.geoJSON(data, { style: { color: '#93c5fd', weight: 3, opacity: 1, fillColor: 'rgba(59,130,246,0.07)', fillOpacity: 1 } }).addTo(map);
    }).catch(() => console.warn("GeoJSON tidak tersedia"));

    function makeIcon(name) {
      return L.divIcon({
        className: 'marker-icon-wrap',
        html: `<div class="mi-inner">
          <div class="mi-dot">
            <img src='/assets/asdp/images/penyeberangan.png' class="mi-img">
          </div>
          <div class="mi-label">${name}</div>
        </div>`,
        iconSize: [150, 62], iconAnchor: [75, 34], popupAnchor: [0, -42]
      });
    }

    function getScale() {
      const z = map.getZoom();
      return Math.max(0.7, Math.min(2.0, 0.7 + (z - 9) * 0.1));
    }
    function refreshIconSizes() {
      const s = getScale();
      document.querySelectorAll('.mi-inner').forEach(el => {
        el.style.transform = `scale(${s})`;
      });
    }
    map.on('zoomend', refreshIconSizes);

    let tambangan = [];
    const markers = [];

    function resolveImagePath(img) {
      if (!img || typeof img !== 'string') return '';
      if (img.startsWith('http://') || img.startsWith('https://')) return img;
      // Foto lokasi ASDP yang diupload lewat admin sekarang disimpan di
      // /uploads/asdp/ (dikumpulkan 1 folder dengan media lokal modul
      // lain), makanya path absolut — beda dari icon marker statis
      // 'images/penyeberangan.png' di atas yang tetap relatif.
      return '/uploads/asdp/' + img;
    }

    function createPopupHtml(loc) {
      const hasImg = !!loc.image;
      return `
        <div class="popup-card">
          ${hasImg
            ? `<img class="popup-card-img" src="${loc.image}" alt="${loc.name}" loading="lazy">`
            : `<div class="popup-card-img-placeholder"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="rgba(100,116,139,0.5)" stroke-width="1.5"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg></div>`
          }
          <div class="popup-card-body">
            <h3>${loc.name}</h3>
            ${loc.description ? `<p>${loc.description}</p>` : ''}
            <a class="popup-dir-btn" href="https://www.google.com/maps/dir/?api=1&destination=${loc.lat},${loc.lng}" target="_blank" style="color:#ffffff!important;">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="3 11 22 2 13 21 11 13 3 11"/></svg>
              Navigasi
            </a>
          </div>
        </div>`;
    }

    function openPanel() { menuPanel.classList.add('active'); menuPanel.setAttribute('aria-hidden','false'); menuBackdrop.classList.add('active'); toggleMenu.style.display = 'none'; }
    function closePanel() { menuPanel.classList.remove('active'); menuPanel.setAttribute('aria-hidden','true'); menuBackdrop.classList.remove('active'); toggleMenu.style.display = ''; }

    toggleMenu.addEventListener('click', openPanel);
    closeMenu.addEventListener('click', closePanel);
    menuBackdrop.addEventListener('click', closePanel);
    openEbook.addEventListener('click', () => { ebookPopup.style.display = ebookPopup.style.display === 'block' ? 'none' : 'block'; });
    closeEbook.addEventListener('click', () => { ebookPopup.style.display = 'none'; });

    function renderList(data) {
      locationsList.innerHTML = '';
      if (!data.length) { locationsList.innerHTML = '<div class="list-empty">Tidak ada lokasi ditemukan.</div>'; return; }
      data.forEach(obj => {
        const el = document.createElement('div');
        el.className = 'list-item';
        el.innerHTML = `<div class="list-item-dot"></div><div class="list-item-name">${obj.name}</div>`;
        el.addEventListener('click', () => {
          const m = markers.find(x => x.name === obj.name.toLowerCase());
          if (m) { map.setView([m.lat, m.lng], 17, { animate: true }); m.marker.openPopup(); }
          closePanel();
        });
        locationsList.appendChild(el);
      });
    }

    searchInput.addEventListener('input', () => {
      const kw = searchInput.value.toLowerCase();
      renderList(tambangan.filter(t => t.name.toLowerCase().includes(kw)));
    });

    async function loadData() {
      try {
        const res = await fetch(`${PROXY}?table=list_tambangan&order=name.asc`);
        if (!res.ok) {
          const err = await res.json().catch(() => ({}));
          throw new Error(err.error || 'HTTP ' + res.status);
        }
        const records = await res.json();
        if (!Array.isArray(records)) {
          throw new Error(records && records.error ? records.error : 'Format data tidak sesuai');
        }
        tambangan = records.map(r => ({ name: r.name, lat: parseFloat(r.lat), lng: parseFloat(r.lng), image: resolveImagePath(r.image), description: r.description }));

        tambangan.forEach(loc => {
          const marker = L.marker([loc.lat, loc.lng], { icon: makeIcon(loc.name) });
          marker.bindPopup(createPopupHtml(loc), { maxWidth: 260, minWidth: 240 });
          marker.addTo(map);
          markers.push({ name: loc.name.toLowerCase(), marker, lat: loc.lat, lng: loc.lng });
        });

        statTotal.textContent = tambangan.length;
        renderList(tambangan);
        finalizeAndHide();
      } catch (err) {
        console.error("Gagal memuat data:", err);
        progressText.textContent = "Gagal memuat (cek console)";
        setTimeout(finalizeAndHide, 1200);
      }
    }

    loadData();
    window.addEventListener('beforeunload', () => { clearInterval(smoothTimer); clearInterval(finishingTimer); });
  </script>
</body>
</html>