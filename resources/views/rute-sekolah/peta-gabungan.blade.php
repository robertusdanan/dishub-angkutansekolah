<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Peta Gabungan Rute — Dishub Tulungagung</title>
  <link rel="icon" href="/favicon.ico"/>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      background: #060b17;
      overflow: hidden;
    }

    #map {
      position: fixed;
      top: 0; bottom: 0;
      left: 0;
      right: 0;
      z-index: 1;
    }

    /* Tile peta dibikin sedikit gelap, selaras dengan peta interaktif ASDP */
    .leaflet-tile-pane { filter: brightness(0.78) saturate(0.85); }

    /* Pengaman: garis rute, marker driver/pengunjung, label titik, dan batas
       wilayah HARUS tetap di atas filter gelap tile (tidak ikut meredup).
       Leaflet sudah memisahkan pane-nya secara arsitektur, baris ini cuma
       jaga-jaga kalau ada CSS lain yang tanpa sengaja menimpa. */
    .leaflet-overlay-pane,
    .leaflet-marker-pane,
    .leaflet-shadow-pane,
    .leaflet-tooltip-pane,
    .leaflet-popup-pane {
      filter: none !important;
    }

    /* Glow tipis di garis rute (SVG path) supaya tetap kontras & terang
       walau di atas tile yang sudah digelapkan */
    .leaflet-overlay-pane path {
      filter: drop-shadow(0 0 3px rgba(0, 0, 0, 0.55));
    }

    /* ── Sidebar kiri ── */
    #sidebar {
      position: fixed;
      top: 0; bottom: 0; left: 0;
      width: 300px;
      z-index: 900;
      background: rgba(15, 22, 46, 0.85);
      backdrop-filter: blur(24px) saturate(160%);
      -webkit-backdrop-filter: blur(24px) saturate(160%);
      border-right: 1px solid rgba(255,255,255,0.08);
      display: flex;
      flex-direction: column;
      transition: transform 0.3s ease;
    }
    #sidebar.collapsed { transform: translateX(-100%); }

    .sidebar-head {
      padding: 18px 18px 14px;
      border-bottom: 1px solid rgba(255,255,255,0.08);
    }
    .sidebar-head-top {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 10px;
      margin-bottom: 12px;
    }
    .sidebar-back {
      display: inline-flex;
      align-items: center;
      gap: 7px;
      font-size: 12.5px;
      font-weight: 600;
      color: #8BA3BC;
      text-decoration: none;
      transition: color 0.2s;
    }
    .sidebar-back:hover { color: #F0F4F8; }
    .sidebar-close-btn {
      display: none;
      align-items: center;
      justify-content: center;
      width: 32px;
      height: 32px;
      border-radius: 8px;
      background: rgba(255,255,255,0.06);
      border: 1px solid rgba(255,255,255,0.1);
      color: #8BA3BC;
      cursor: pointer;
      font-size: 14px;
      transition: background 0.15s, color 0.15s;
    }
    .sidebar-close-btn:hover { color: #F0F4F8; background: rgba(255,255,255,0.12); }
    .sidebar-title {
      font-size: 15px;
      font-weight: 700;
      color: #F0F4F8;
      letter-spacing: -0.01em;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .sidebar-title i { color: #06b6d4; font-size: 14px; }
    .sidebar-sub {
      font-size: 11.5px;
      color: rgba(140,163,188,0.75);
      margin-top: 4px;
    }

    .sidebar-body {
      flex: 1;
      overflow-y: auto;
      padding: 12px 14px 20px;
    }
    .sidebar-body::-webkit-scrollbar { width: 6px; }
    .sidebar-body::-webkit-scrollbar-thumb { background: rgba(140,163,188,0.25); border-radius: 10px; }

    .group-label {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 10px 6px 6px;
      font-size: 10.5px;
      font-weight: 700;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      color: rgba(140,163,188,0.75);
    }
    .group-toggle-all {
      font-size: 10.5px;
      font-weight: 600;
      color: #06b6d4;
      cursor: pointer;
      background: none;
      border: none;
      font-family: inherit;
      letter-spacing: 0.03em;
    }
    .group-toggle-all:hover { text-decoration: underline; }

    .route-item {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 9px 8px;
      border-radius: 10px;
      cursor: pointer;
      transition: background 0.15s ease;
    }
    .route-item:hover { background: rgba(255,255,255,0.05); }

    .route-item input[type=checkbox] {
      appearance: none;
      -webkit-appearance: none;
      width: 16px;
      height: 16px;
      border-radius: 5px;
      border: 1.5px solid rgba(140,163,188,0.4);
      cursor: pointer;
      flex-shrink: 0;
      position: relative;
      background: transparent;
    }
    .route-item input[type=checkbox]:checked { border-color: var(--rc, #06b6d4); background: var(--rc, #06b6d4); }
    .route-item input[type=checkbox]:checked::after {
      content: '';
      position: absolute;
      left: 4.5px; top: 1.5px;
      width: 4px; height: 8px;
      border: solid #060b17;
      border-width: 0 2px 2px 0;
      transform: rotate(45deg);
    }
    /* Checkbox parent trayek saat sebagian rute (mis. cuma Pagi) tercentang */
    .route-item input[type=checkbox]:indeterminate {
      border-color: var(--rc, #06b6d4);
      background: transparent;
    }
    .route-item input[type=checkbox]:indeterminate::after {
      content: '';
      position: absolute;
      left: 3px; top: 6.5px;
      width: 8px; height: 2px;
      background: var(--rc, #06b6d4);
      border: none;
      transform: none;
    }

    /* ── Submenu kecil per trayek: pilih rute Pagi / Siang ── */
    .route-group { margin-bottom: 2px; }
    .route-parent { position: relative; }
    .route-chevron {
      margin-left: auto;
      flex-shrink: 0;
      width: 22px; height: 22px;
      display: flex; align-items: center; justify-content: center;
      background: none;
      border: none;
      border-radius: 6px;
      color: rgba(140,163,188,0.6);
      font-size: 10.5px;
      cursor: pointer;
      transition: transform 0.2s ease, color 0.15s ease, background 0.15s ease;
    }
    .route-chevron:hover { color: #F0F4F8; background: rgba(255,255,255,0.06); }
    .route-chevron.open { transform: rotate(180deg); }

    .route-submenu {
      max-height: 0;
      opacity: 0;
      overflow: hidden;
      padding-left: 30px;
      transition: max-height 0.32s cubic-bezier(0.4,0,0.2,1), opacity 0.22s ease;
    }
    .route-submenu.open { max-height: 260px; opacity: 1; }

    .route-parent { cursor: pointer; }
    .route-parent.active-route { background: rgba(6,182,212,0.08); }
    .route-parent.active-route .route-name { color: #F0F4F8; }

    .route-active-badge {
      display: none;
      align-items: center;
      flex-shrink: 0;
      font-size: 9.5px;
      font-weight: 700;
      letter-spacing: 0.03em;
      color: var(--rc, #06b6d4);
      background: color-mix(in srgb, var(--rc, #06b6d4) 16%, transparent);
      border: 1px solid color-mix(in srgb, var(--rc, #06b6d4) 40%, transparent);
      padding: 2px 7px;
      border-radius: 20px;
      white-space: nowrap;
    }

    .route-subitem {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 6px 8px;
      border-radius: 8px;
      cursor: pointer;
      transition: background 0.15s ease;
    }
    .route-subitem:hover { background: rgba(255,255,255,0.04); }
    .route-subitem input[type=checkbox] {
      appearance: none;
      -webkit-appearance: none;
      width: 13px; height: 13px;
      border-radius: 4px;
      border: 1.5px solid rgba(140,163,188,0.4);
      cursor: pointer;
      flex-shrink: 0;
      position: relative;
      background: transparent;
    }
    .route-subitem input[type=checkbox]:checked { border-color: var(--rc, #06b6d4); background: var(--rc, #06b6d4); }
    .route-subitem input[type=checkbox]:checked::after {
      content: '';
      position: absolute;
      left: 3px; top: 0.5px;
      width: 3.5px; height: 7px;
      border: solid #060b17;
      border-width: 0 1.5px 1.5px 0;
      transform: rotate(45deg);
    }
    .route-subname {
      font-size: 11.5px;
      font-weight: 500;
      color: rgba(226,232,240,0.85);
    }

    .route-swatch {
      width: 10px; height: 10px;
      border-radius: 3px;
      flex-shrink: 0;
    }

    .route-name {
      font-size: 12.5px;
      font-weight: 500;
      color: #E2E8F0;
      line-height: 1.3;
      flex: 1;
      min-width: 0;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    .sidebar-empty {
      padding: 20px 8px;
      font-size: 12px;
      color: rgba(140,163,188,0.6);
      text-align: center;
    }

    /* ── Toggle Rute Pagi / Siang (di bawah header sidebar) ── */
    .waktu-toggle {
      display: flex;
      gap: 4px;
      margin-top: 14px;
      padding: 3px;
      background: rgba(255,255,255,0.04);
      border: 1px solid rgba(255,255,255,0.08);
      border-radius: 10px;
    }
    .waktu-btn {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
      border: none;
      background: transparent;
      color: rgba(140,163,188,0.75);
      font-family: inherit;
      font-size: 11.5px;
      font-weight: 600;
      letter-spacing: 0.02em;
      padding: 8px 0;
      border-radius: 7px;
      cursor: pointer;
      transition: background 0.2s ease, color 0.2s ease;
    }
    .waktu-btn i { font-size: 10.5px; }
    .waktu-btn:hover { color: #F0F4F8; }
    .waktu-btn.active {
      background: #06b6d4;
      color: #060b17;
      box-shadow: 0 2px 10px rgba(6,182,212,0.35);
    }

    .sidebar-foot {
      padding: 12px 18px 16px;
      border-top: 1px solid rgba(255,255,255,0.08);
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 11.5px;
      color: rgba(140,163,188,0.75);
    }
    .sidebar-foot .live-dot {
      width: 7px; height: 7px; border-radius: 50%;
      background: #06b6d4; flex-shrink: 0;
      position: relative;
    }
    .sidebar-foot .live-dot::before {
      content: '';
      position: absolute; inset: -4px;
      border-radius: 50%;
      background: rgba(6,182,212,0.3);
      animation: pulse-ring 2s ease-out infinite;
    }
    @keyframes pulse-ring { 0% { transform: scale(0.6); opacity: 1; } 100% { transform: scale(1.8); opacity: 0; } }

    /* Toggle sidebar button (mobile / collapsed state) */
    #sidebar-toggle {
      position: fixed;
      top: 14px;
      left: 14px;
      z-index: 950;
      width: 40px;
      height: 40px;
      border-radius: 10px;
      background: rgba(10,14,28,0.94);
      backdrop-filter: blur(16px);
      border: 1px solid rgba(255,255,255,0.1);
      color: #F0F4F8;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      font-size: 15px;
      transition: left 0.3s ease;
    }
    #sidebar-toggle.shifted { left: 314px; }

    /* ── Top title bar (di kanan tombol sidebar) ── */
    #header-bar {
      position: fixed;
      top: 14px;
      left: 66px;
      z-index: 900;
      display: inline-flex;
      align-items: center;
      gap: 10px;
      padding: 10px 18px 10px 12px;
      background: rgba(10, 14, 28, 0.72);
      backdrop-filter: blur(20px) saturate(180%);
      -webkit-backdrop-filter: blur(20px) saturate(180%);
      border: 1px solid rgba(255,255,255,0.08);
      border-radius: 100px;
      box-shadow: 0 0 0 1px rgba(255,255,255,0.03), 0 8px 32px rgba(0,0,0,0.5);
      max-width: calc(100vw - 340px);
      transition: left 0.3s ease;
    }
    #header-bar.shifted { left: 328px; }
    .title-icon {
      width: 34px; height: 34px;
      background: linear-gradient(145deg, #0e7490 0%, #082f49 100%);
      border: 2px solid #67e8f9;
      border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      flex-shrink: 0;
      box-shadow: 0 3px 10px rgba(0,0,0,0.6), 0 0 0 3px rgba(6,182,212,0.15);
    }
    .title-icon i { color: #ecfeff; font-size: 14px; }
    .title-text { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
    .title-label {
      font-size: 10.5px; font-weight: 500;
      color: rgba(140,163,188,0.8);
      letter-spacing: 0.06em; text-transform: uppercase;
    }
    .title-name {
      font-size: 14.5px; font-weight: 600; color: #fff;
      white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }

    /* ── Bottom stat card ── */
    #bottom-card {
      position: fixed;
      bottom: 24px;
      left: 50%;
      transform: translateX(-50%);
      z-index: 900;
      background: rgba(10, 14, 28, 0.72);
      backdrop-filter: blur(20px) saturate(180%);
      -webkit-backdrop-filter: blur(20px) saturate(180%);
      border: 1px solid rgba(255,255,255,0.08);
      border-radius: 16px;
      padding: 14px 20px;
      display: flex;
      align-items: center;
      gap: 16px;
      width: max-content;
      max-width: calc(100vw - 40px);
      box-shadow: 0 0 0 1px rgba(255,255,255,0.03), 0 8px 32px rgba(0,0,0,0.5);
    }
    .card-stat { display: flex; flex-direction: column; align-items: center; gap: 3px; }
    .card-stat-label {
      font-size: 10px; font-weight: 500;
      color: rgba(140,163,188,0.7);
      letter-spacing: 0.07em; text-transform: uppercase;
      white-space: nowrap;
    }
    .card-stat-value { font-size: 16px; font-weight: 700; font-family: 'DM Mono', monospace; color: #F0F4F8; }
    .card-stat-value.accent { color: #06b6d4; }
    .card-divider { width: 1px; height: 32px; background: rgba(140,163,188,0.15); }

    /* ── Loading overlay ── */
    #loading-overlay {
      position: fixed; inset: 0; z-index: 2000;
      background: #060b17;
      display: flex; flex-direction: column;
      align-items: center; justify-content: center;
      gap: 14px;
      transition: opacity 0.5s ease;
    }
    #loading-overlay.hidden { opacity: 0; pointer-events: none; }
    .loading-spinner {
      width: 40px; height: 40px;
      border: 3px solid rgba(6,182,212,0.15);
      border-top-color: #06b6d4;
      border-radius: 50%;
      animation: spin 0.9s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }
    .loading-title { font-size: 15px; font-weight: 600; color: #F0F4F8; letter-spacing: 0.01em; }
    .loading-text { font-size: 13px; font-weight: 500; color: rgba(140,163,188,0.8); letter-spacing: 0.02em; min-height: 18px; text-align: center; transition: opacity 0.25s ease; }
    .loading-bar-track {
      width: 220px; height: 5px; border-radius: 10px;
      background: rgba(140,163,188,0.15); overflow: hidden;
    }
    .loading-bar-fill {
      height: 100%; width: 4%; border-radius: 10px;
      background: linear-gradient(90deg, #06b6d4, #38BDF8);
      transition: width 0.4s ease;
    }
    /* ── State error (dipicu lewat class .error di #loading-overlay) ── */
    #loading-overlay.error .loading-spinner,
    #loading-overlay.error .loading-bar-track { display: none; }
    .loading-error-icon {
      display: none;
      width: 44px; height: 44px; border-radius: 50%;
      align-items: center; justify-content: center;
      background: rgba(239,68,68,0.12);
      border: 1px solid rgba(239,68,68,0.35);
      color: #EF4444; font-size: 18px;
    }
    #loading-overlay.error .loading-error-icon { display: flex; }
    #loading-overlay.error .loading-title { color: #EF4444; }
    .loading-retry-btn {
      display: none;
      margin-top: 4px; padding: 8px 18px;
      background: rgba(6,182,212,0.12);
      border: 1px solid rgba(6,182,212,0.35);
      color: #06b6d4; font-size: 13px; font-weight: 600;
      border-radius: 8px; cursor: pointer;
      font-family: 'Plus Jakarta Sans', sans-serif;
      transition: background 0.2s ease;
    }
    .loading-retry-btn:hover { background: rgba(6,182,212,0.22); }
    #loading-overlay.error .loading-retry-btn { display: inline-block; }

    /* ── Leaflet overrides ── */
    .leaflet-control-zoom { border: none !important; box-shadow: none !important; margin-bottom: 80px !important; }
    .leaflet-control-zoom a {
      background: rgba(10,14,28,0.88) !important;
      backdrop-filter: blur(12px) !important;
      color: #8BA3BC !important;
      border: 1px solid rgba(140,163,188,0.15) !important;
      border-radius: 8px !important;
      margin-bottom: 4px !important;
      width: 32px !important; height: 32px !important;
      line-height: 32px !important; font-size: 16px !important;
    }
    .leaflet-control-zoom a:hover { color: #F0F4F8 !important; border-color: rgba(6,182,212,0.35) !important; }
    .leaflet-bar { border: none !important; }

    .modern-tooltip {
      background: rgba(10, 14, 28, 0.92);
      color: #F0F4F8;
      font-family: 'Plus Jakarta Sans', sans-serif;
      font-weight: 500;
      font-size: 12px;
      padding: 6px 12px;
      border-radius: 8px;
      border: 1px solid rgba(6, 182, 212, 0.25);
      white-space: nowrap;
    }

    /* Backdrop overlay saat sidebar terbuka di mobile */
    #sidebar-backdrop {
      position: fixed;
      inset: 0;
      z-index: 890;
      background: rgba(6, 11, 23, 0.65);
      backdrop-filter: blur(4px);
      -webkit-backdrop-filter: blur(4px);
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.3s ease;
    }
    #sidebar-backdrop.active {
      opacity: 1;
      pointer-events: auto;
    }

    @media (max-width: 860px) {
      #sidebar { width: 84vw; max-width: 320px; transform: translateX(-100%); z-index: 920; }
      #sidebar.open { transform: translateX(0); }
      #sidebar-toggle.shifted { left: 14px; }
      #sidebar-toggle.hide-on-open { opacity: 0; pointer-events: none; }
      .sidebar-close-btn { display: flex; }
      #header-bar { left: 66px; max-width: calc(100vw - 90px); }
      #header-bar.shifted { left: 66px; }
      #bottom-card { bottom: 16px; padding: 12px 16px; width: calc(100vw - 32px); }
    }
  </style>
</head>
<body>

  <!-- Loading overlay -->
  <div id="loading-overlay">
    <div class="loading-error-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
    <div class="loading-spinner"></div>
    <div class="loading-title">Memuat Peta Gabungan</div>
    <div class="loading-text" id="loading-step">Menyiapkan peta...</div>
    <div class="loading-bar-track"><div class="loading-bar-fill" id="loading-bar-fill"></div></div>
    <button type="button" class="loading-retry-btn" onclick="location.reload()">Muat Ulang</button>
  </div>

  <!-- Sidebar backdrop (klik luar untuk tutup di mobile) -->
  <div id="sidebar-backdrop"></div>

  <!-- Sidebar toggle button -->
  <button id="sidebar-toggle" aria-label="Buka/tutup daftar rute">
    <i class="fa-solid fa-bars"></i>
  </button>

  <!-- Header bar -->
  <div id="header-bar">
    <div class="title-icon"><i class="fa-solid fa-map-location-dot"></i></div>
    <div class="title-text">
      <span class="title-label">Peta Gabungan</span>
      <span class="title-name" id="title-name-text">Memuat data rute...</span>
    </div>
  </div>

  <!-- Sidebar -->
  <aside id="sidebar">
    <div class="sidebar-head">
      <div class="sidebar-head-top">
        <a href="/rute-sekolah" class="sidebar-back">
          <i class="fa-solid fa-arrow-left fa-xs"></i> Kembali ke Rute Bus Sekolah
        </a>
        <button type="button" class="sidebar-close-btn" id="sidebar-close" aria-label="Tutup daftar rute">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
      <div class="sidebar-title"><i class="fa-solid fa-sliders"></i> Tampilkan Rute</div>
      <div class="sidebar-sub">Pilih trayek yang ingin ditampilkan di peta.</div>
      <div class="waktu-toggle" id="waktu-toggle">
        <button type="button" class="waktu-btn active" data-waktu="pagi">
          <i class="fa-solid fa-sun"></i> Rute Pagi
        </button>
        <button type="button" class="waktu-btn" data-waktu="siang">
          <i class="fa-solid fa-cloud-sun"></i> Rute Siang
        </button>
      </div>
    </div>
    <div class="sidebar-body" id="sidebar-body">
      <div class="sidebar-empty">Memuat daftar trayek...</div>
    </div>
    <div class="sidebar-foot">
      <span class="live-dot"></span>
      Posisi driver selalu tampil semua &amp; realtime
    </div>
  </aside>

  <!-- Map -->
  <div id="map"></div>

  <!-- Bottom stat card -->
  <div id="bottom-card">
    <div class="card-stat">
      <span class="card-stat-label">Trayek Aktif</span>
      <span class="card-stat-value accent" id="stat-trayek">–</span>
    </div>
    <div class="card-divider"></div>
    <div class="card-stat">
      <span class="card-stat-label">Total Trayek</span>
      <span class="card-stat-value" id="stat-total-trayek">–</span>
    </div>
    <div class="card-divider"></div>
    <div class="card-stat">
      <span class="card-stat-label">Driver Online</span>
      <span class="card-stat-value" id="stat-driver">–</span>
    </div>
  </div>

  <script>
    window.SB_URL  = {!! json_encode(config('services.supabase.url')) !!};
    window.SB_ANON = {!! json_encode(config('services.supabase.anon_key')) !!};
  </script>
  <script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>
  <script src="{{ asset_url('/assets/rute-sekolah/partials/driver_status.js') }}" defer></script>
  <script src="{{ asset_url('/assets/rute-sekolah/partials/peta_gabungan.js') }}" defer></script>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      var overlay  = document.getElementById('loading-overlay');
      var stepText = document.getElementById('loading-step');
      var barFill  = document.getElementById('loading-bar-fill');

      var hideOverlay = function () {
        if (barFill) barFill.style.width = '100%';
        overlay.classList.add('hidden');
        setTimeout(function () { overlay.style.display = 'none'; }, 500);
      };

      // Update teks langkah & progress bar sesuai kemajuan nyata pemuatan data
      // (dikirim oleh peta_gabungan.js lewat CustomEvent, bukan tebakan waktu).
      document.addEventListener('map-progress', function (e) {
        var d = (e && e.detail) || {};
        if (stepText && d.text) stepText.textContent = d.text;
        if (barFill && typeof d.percent === 'number') barFill.style.width = d.percent + '%';
      });

      document.addEventListener('map-ready', hideOverlay);

      // Kalau gagal memuat, overlay TETAP tampil tapi berubah ke tampilan
      // error yang jelas (bukan menghilang diam-diam), supaya pengguna
      // tidak bingung "ini masih loading atau memang eror".
      document.addEventListener('map-error', function (e) {
        var d = (e && e.detail) || {};
        overlay.classList.add('error');
        if (stepText) stepText.textContent = d.message || 'Gagal memuat peta gabungan.';
      });

      // Jaring pengaman TERAKHIR: kalau proses benar-benar MACET (tidak ada
      // progress masuk sama sekali selama beberapa waktu — bukan sekadar
      // lama), baru tampilkan error. Timer ini di-RESET setiap kali ada
      // event map-progress baru, jadi proses yang masih berjalan (walau
      // lambat) tidak akan salah dianggap macet.
      var STALL_LIMIT_MS = 25000;
      var watchdog;
      function resetWatchdog() {
        clearTimeout(watchdog);
        watchdog = setTimeout(function () {
          overlay.classList.add('error');
          if (stepText) stepText.textContent = 'Memuat macet, tidak ada progress. Periksa koneksi Anda lalu coba lagi.';
        }, STALL_LIMIT_MS);
      }
      resetWatchdog();
      document.addEventListener('map-progress', resetWatchdog);
      document.addEventListener('map-ready', function () { clearTimeout(watchdog); });
      document.addEventListener('map-error', function () { clearTimeout(watchdog); });

      // ── Sidebar toggle ──
      var sidebar   = document.getElementById('sidebar');
      var toggleBtn = document.getElementById('sidebar-toggle');
      var headerBar = document.getElementById('header-bar');
      var backdrop  = document.getElementById('sidebar-backdrop');
      var closeBtn  = document.getElementById('sidebar-close');
      var isMobile  = () => window.innerWidth <= 860;

      function applyState(open) {
        if (isMobile()) {
          sidebar.classList.toggle('open', open);
          sidebar.classList.remove('collapsed');
          toggleBtn.classList.toggle('hide-on-open', open);
          toggleBtn.classList.remove('shifted');
          headerBar.classList.remove('shifted');
          if (backdrop) backdrop.classList.toggle('active', open);
        } else {
          sidebar.classList.toggle('collapsed', !open);
          sidebar.classList.remove('open');
          toggleBtn.classList.remove('hide-on-open');
          toggleBtn.classList.toggle('shifted', open);
          headerBar.classList.toggle('shifted', open);
          if (backdrop) backdrop.classList.remove('active');
        }
      }

      var sidebarOpen = false; // sembunyikan sidebar di awal, baik desktop maupun mobile
      applyState(sidebarOpen);

      toggleBtn.addEventListener('click', function () {
        sidebarOpen = !sidebarOpen;
        applyState(sidebarOpen);
      });

      if (closeBtn) {
        closeBtn.addEventListener('click', function () {
          sidebarOpen = false;
          applyState(sidebarOpen);
        });
      }

      if (backdrop) {
        backdrop.addEventListener('click', function () {
          sidebarOpen = false;
          applyState(sidebarOpen);
        });
      }

      window.addEventListener('resize', function () { applyState(sidebarOpen); });
    });
  </script>

</body>
</html>
