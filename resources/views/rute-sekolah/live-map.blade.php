<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Rute Angkutan</title>
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
      height: 100vh;
      height: 100dvh;
      width: 100vw;
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

    /* ── Top Header Bar ── */
    #top-nav-bar {
      position: absolute;
      top: 14px;
      left: 14px;
      z-index: 1000;
      display: inline-flex;
      align-items: center;
      gap: 10px;
      max-width: calc(100vw - 28px);
    }

    #map-back-btn {
      width: 42px;
      height: 42px;
      border-radius: 50%;
      background: rgba(10, 14, 28, 0.88);
      backdrop-filter: blur(20px) saturate(180%);
      -webkit-backdrop-filter: blur(20px) saturate(180%);
      border: 1px solid rgba(255, 255, 255, 0.12);
      color: #F0F4F8;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      font-size: 14px;
      flex-shrink: 0;
      box-shadow: 0 0 0 1px rgba(255,255,255,0.03), 0 8px 32px rgba(0, 0, 0, 0.5);
      transition: all 0.2s ease;
      text-decoration: none;
    }

    #map-back-btn:hover {
      background: rgba(14, 116, 144, 0.95);
      border-color: #06b6d4;
      color: #fff;
      transform: scale(1.05);
    }

    #header-bar {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      padding: 8px 18px 8px 12px;
      background: rgba(10, 14, 28, 0.72);
      backdrop-filter: blur(20px) saturate(180%);
      -webkit-backdrop-filter: blur(20px) saturate(180%);
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 100px;
      box-shadow: 0 0 0 1px rgba(255,255,255,0.03), 0 8px 32px rgba(0, 0, 0, 0.5);
      pointer-events: none;
      min-width: 0;
      overflow: hidden;
    }

    #map-title {
      display: flex;
      align-items: center;
      gap: 10px;
      color: #F0F4F8;
      font-size: 15px;
      font-weight: 600;
      letter-spacing: -0.01em;
    }

    .title-icon {
      width: 34px;
      height: 34px;
      background: linear-gradient(145deg, #0e7490 0%, #082f49 100%);
      border: 2px solid #67e8f9;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      box-shadow: 0 3px 10px rgba(0,0,0,0.6), 0 0 0 3px rgba(6,182,212,0.15);
    }

    .title-icon i {
      color: #ecfeff;
      font-size: 14px;
    }

    .title-text {
      display: flex;
      flex-direction: column;
      gap: 2px;
    }

    .title-label {
      font-size: 11px;
      font-weight: 500;
      color: rgba(140, 163, 188, 0.8);
      letter-spacing: 0.06em;
      text-transform: uppercase;
    }

    .title-name {
      font-size: 15px;
      font-weight: 600;
      color: #FFFFFF;
      letter-spacing: -0.01em;
      text-shadow: 0 1px 4px rgba(0,0,0,0.4);
    }

    /* Live pulse indicator */
    .live-dot {
      width: 7px;
      height: 7px;
      background: #06b6d4;
      border-radius: 50%;
      position: relative;
      flex-shrink: 0;
    }

    .live-dot::before {
      content: '';
      position: absolute;
      inset: -4px;
      border-radius: 50%;
      background: rgba(6, 182, 212, 0.3);
      animation: pulse-ring 2s ease-out infinite;
    }

    @keyframes pulse-ring {
      0%   { transform: scale(0.6); opacity: 1; }
      100% { transform: scale(1.8); opacity: 0; }
    }

    /* ── Bottom Info Card ── */
    #bottom-card {
      position: absolute;
      bottom: 24px;
      left: 50%;
      transform: translateX(-50%);
      z-index: 1000;
      background: rgba(10, 14, 28, 0.88);
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      border: 1px solid rgba(6, 182, 212, 0.18);
      border-radius: 16px;
      padding: 14px 20px;
      display: flex;
      align-items: center;
      gap: 16px;
      min-width: 280px;
      max-width: 460px;
      width: max-content;
    }

    .card-stat {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 3px;
      flex: 1;
    }

    .card-stat-label {
      font-size: 10px;
      font-weight: 500;
      color: rgba(140, 163, 188, 0.7);
      letter-spacing: 0.07em;
      text-transform: uppercase;
      white-space: nowrap;
    }

    .card-stat-value {
      font-size: 16px;
      font-weight: 700;
      font-family: 'DM Mono', monospace;
      color: #F0F4F8;
      letter-spacing: -0.02em;
    }

    .card-stat-value.accent {
      color: #06b6d4;
    }

    .card-divider {
      width: 1px;
      height: 32px;
      background: rgba(140, 163, 188, 0.15);
      flex-shrink: 0;
    }

    /* ── Tooltip ── */
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
      letter-spacing: 0.01em;
    }

    /* ── Loading State ── */
    #loading-overlay {
      position: absolute;
      inset: 0;
      z-index: 2000;
      background: #060b17;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 16px;
      transition: opacity 0.5s ease;
    }

    #loading-overlay.hidden {
      opacity: 0;
      pointer-events: none;
    }

    .loading-spinner {
      width: 36px;
      height: 36px;
      border: 2px solid rgba(6, 182, 212, 0.15);
      border-top-color: #06b6d4;
      border-radius: 50%;
      animation: spin 0.9s linear infinite;
    }

    @keyframes spin {
      to { transform: rotate(360deg); }
    }

    .loading-text {
      font-size: 13px;
      font-weight: 500;
      color: rgba(140, 163, 188, 0.7);
      letter-spacing: 0.03em;
    }

    /* ── Leaflet Overrides ── */
    .leaflet-control-zoom {
      border: none !important;
      box-shadow: none !important;
      margin-bottom: 80px !important;
    }

    .leaflet-control-zoom a {
      background: rgba(10, 14, 28, 0.88) !important;
      backdrop-filter: blur(12px) !important;
      color: #8BA3BC !important;
      border: 1px solid rgba(140, 163, 188, 0.15) !important;
      border-radius: 8px !important;
      margin-bottom: 4px !important;
      width: 32px !important;
      height: 32px !important;
      line-height: 32px !important;
      font-size: 16px !important;
      transition: all 0.15s ease !important;
    }

    .leaflet-control-zoom a:hover {
      color: #F0F4F8 !important;
      border-color: rgba(6, 182, 212, 0.35) !important;
    }

    .leaflet-bar {
      border: none !important;
    }

    @media (max-width: 480px) {
      #bottom-card {
        bottom: 16px;
        padding: 10px 14px;
        min-width: unset;
        width: calc(100vw - 32px);
      }

      #top-nav-bar {
        top: 10px;
        left: 10px;
        gap: 8px;
        max-width: calc(100vw - 20px);
      }

      #map-back-btn {
        width: 38px;
        height: 38px;
        font-size: 13px;
      }

      #header-bar {
        padding: 6px 14px 6px 10px;
      }

      .title-name {
        font-size: 13px;
      }
    }

    /* ── Stop Modal ── */
    #stop-modal {
      position: absolute;
      z-index: 1100;
      pointer-events: none;
      opacity: 0;
      transform: translateY(6px) scale(0.97);
      transition: opacity 0.18s ease, transform 0.18s ease;
    }

    #stop-modal.visible {
      pointer-events: auto;
      opacity: 1;
      transform: translateY(0) scale(1);
    }

    .stop-modal-inner {
      background: rgba(15, 22, 46, 0.85);
      backdrop-filter: blur(24px);
      -webkit-backdrop-filter: blur(24px);
      border: 1px solid rgba(255,255,255,0.08);
      border-radius: 16px;
      padding: 14px 16px 12px;
      min-width: 180px;
      max-width: 240px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.7), 0 0 0 1px rgba(255,255,255,0.05);
      position: relative;
    }

    .stop-modal-label {
      font-size: 10px;
      font-weight: 500;
      color: rgba(140, 163, 188, 0.65);
      letter-spacing: 0.07em;
      text-transform: uppercase;
      margin-bottom: 5px;
    }

    .stop-modal-name {
      font-size: 14px;
      font-weight: 600;
      color: #F0F4F8;
      line-height: 1.35;
      letter-spacing: -0.01em;
    }

    .stop-modal-index {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      margin-top: 8px;
      font-size: 11px;
      font-weight: 500;
      color: #06b6d4;
    }

    .stop-modal-close {
      position: absolute;
      top: 8px;
      right: 10px;
      width: 20px;
      height: 20px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: rgba(140, 163, 188, 0.1);
      border: none;
      border-radius: 50%;
      color: rgba(140, 163, 188, 0.6);
      font-size: 11px;
      cursor: pointer;
      line-height: 1;
      padding: 0;
      transition: background 0.12s, color 0.12s;
    }

    .stop-modal-close:hover {
      background: rgba(140, 163, 188, 0.2);
      color: #F0F4F8;
    }

    /* Panah bawah modal */
    .stop-modal-arrow {
      width: 12px;
      height: 7px;
      margin: 0 auto;
      overflow: visible;
    }

  </style>
</head>
<body>

  <!-- Loading Overlay -->
  <div id="loading-overlay">
    <div class="loading-spinner"></div>
    <span class="loading-text">Memuat peta rute...</span>
  </div>

  <!-- Top Navigation & Header Bar -->
  <div id="top-nav-bar">
    <button type="button" id="map-back-btn" onclick="history.back()" aria-label="Kembali ke halaman sebelumnya">
      <i class="fa-solid fa-arrow-left"></i>
    </button>
    <div id="header-bar">
      <div id="map-title">
        <div class="title-icon">
          <i class="fas fa-route"></i>
        </div>
        <div class="title-text">
          <span class="title-label">Peta Rute</span>
          <span class="title-name" id="title-name-text">Memuat...</span>
        </div>
        <div class="live-dot"></div>
      </div>
    </div>
  </div>

  <!-- Map -->
  <div id="map"></div>

  <!-- Stop Modal -->
  <div id="stop-modal">
    <div class="stop-modal-inner">
      <button class="stop-modal-close" id="stop-modal-close" aria-label="Tutup">✕</button>
      <div class="stop-modal-label">Titik Jalur</div>
      <div class="stop-modal-name" id="stop-modal-name">—</div>
      <div class="stop-modal-index" id="stop-modal-index"></div>
    </div>
    <svg class="stop-modal-arrow" viewBox="0 0 12 7" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path d="M0 0 L6 7 L12 0" fill="rgba(15,22,46,0.85)" stroke="rgba(255,255,255,0.08)" stroke-width="1" stroke-linejoin="round"/>
    </svg>
  </div>

  <!-- Bottom Info Card -->
  <div id="bottom-card">
    <div class="card-stat">
      <span class="card-stat-label">Jalur</span>
      <span class="card-stat-value accent" id="stat-halte">—</span>
    </div>
    <div class="card-divider"></div>
    <div class="card-stat">
      <span class="card-stat-label">Jarak</span>
      <span class="card-stat-value" id="stat-jarak">—</span>
    </div>
    <div class="card-divider"></div>
    <div class="card-stat">
      <span class="card-stat-label">Durasi</span>
      <span class="card-stat-value" id="stat-durasi">—</span>
    </div>
  </div>

  <script>
    // Anon key aman dikirim ke browser (dibatasi Row Level Security di Supabase,
    // hanya boleh SELECT/read). Dipakai untuk koneksi Realtime asli (WebSocket),
    // menggantikan polling 30 detik yang lama.
    window.SB_URL  = {!! json_encode(config('services.supabase.url')) !!};
    window.SB_ANON = {!! json_encode(config('services.supabase.anon_key')) !!};
  </script>
  <script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>
  <script src="{{ asset_url('/assets/rute-sekolah/partials/driver_status.js') }}" defer></script>
  <script src="{{ asset_url('/assets/rute-sekolah/partials/rute_map.js') }}" defer></script>

  <script>
    // Hide loading overlay once map tiles begin rendering
    document.addEventListener('DOMContentLoaded', function () {
      var overlay = document.getElementById('loading-overlay');
      // Wait for first map interaction or timeout
      var hideOverlay = function () {
        overlay.classList.add('hidden');
        setTimeout(function () { overlay.style.display = 'none'; }, 500);
      };

      // Auto-hide after 3s as fallback
      setTimeout(hideOverlay, 3000);

      // Hook into Leaflet map load if available
      document.addEventListener('map-ready', hideOverlay);
    });
  </script>

</body>
</html>