<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="icon" href="/favicon.ico"/>
  <title>Rute Angkutan Sekolah - Dishub Tulungagung</title>
  <meta name="description" content="Daftar trayek BUS dan MPU Angkutan Sekolah Gratis Dishub Tulungagung, lengkap dengan peta gabungan seluruh rute." />

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
    :root {
      --navy:   #0a1f44;
      --blue:   #1a56db;
      --sky:    #38bdf8;
      --accent: #f59e0b;
      --green:  #059669;
    }

    *, *::before, *::after { box-sizing: border-box; }

    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      background: #f8faff;
      color: #1e293b;
      margin: 0;
    }

    /* ── PAGE HERO STRIP (ringkas, bukan video - tetap serasi warna homepage) ── */
    .page-hero {
      background: linear-gradient(135deg, var(--navy) 0%, #0d2a5c 100%);
      padding: 112px 2rem 100px;
      position: relative;
      overflow: hidden;
    }
    .page-hero::after {
      content: '';
      position: absolute;
      inset: 0;
      background: radial-gradient(circle at 85% 20%, rgba(56,189,248,0.18) 0%, transparent 55%);
      pointer-events: none;
    }
    .page-hero-inner {
      max-width: 1100px;
      margin: 0 auto;
      position: relative;
      z-index: 1;
    }
    .breadcrumb {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 12.5px;
      color: rgba(255,255,255,0.5);
      margin-bottom: 14px;
    }
    .breadcrumb a { color: rgba(255,255,255,0.5); text-decoration: none; }
    .breadcrumb a:hover { color: #fff; }
    .page-hero h2 {
      font-size: clamp(1.6rem, 3.5vw, 2.3rem);
      font-weight: 800;
      color: #fff;
      letter-spacing: -0.8px;
      margin: 0 0 10px;
    }
    .page-hero p {
      font-size: 14.5px;
      color: rgba(255,255,255,0.65);
      max-width: 560px;
      margin: 0;
      line-height: 1.6;
    }

    /* ── PETA GABUNGAN - banner elegan terpisah, di atas switcher BUS/MPU ── */
    .section-wrap {
      max-width: 1100px;
      margin: -56px auto 0;
      padding: 0 2rem 80px;
      position: relative;
      z-index: 2;
    }

    .map-banner {
      display: flex;
      align-items: center;
      gap: 20px;
      background: linear-gradient(135deg, #0d2a5c 0%, #0a1f44 100%);
      border-radius: 20px;
      padding: 22px 26px;
      text-decoration: none;
      color: #fff;
      margin-bottom: 22px;
      box-shadow: 0 12px 40px rgba(10,31,68,0.22);
      position: relative;
      overflow: hidden;
      transition: transform 0.25s ease, box-shadow 0.25s ease;
    }
    .map-banner::after {
      content: '';
      position: absolute;
      inset: 0;
      background: radial-gradient(circle at 92% 15%, rgba(56,189,248,0.28) 0%, transparent 55%);
      pointer-events: none;
    }
    .map-banner:hover {
      transform: translateY(-3px);
      box-shadow: 0 18px 50px rgba(10,31,68,0.32);
    }
    .map-banner-icon {
      position: relative; z-index: 1;
      width: 52px; height: 52px;
      border-radius: 14px;
      background: rgba(0,201,167,0.15);
      border: 1px solid rgba(0,201,167,0.35);
      display: flex; align-items: center; justify-content: center;
      font-size: 21px;
      color: #00C9A7;
      flex-shrink: 0;
    }
    .map-banner-text { position: relative; z-index: 1; flex: 1; min-width: 0; }
    .map-banner-text h4 {
      font-size: 16px;
      font-weight: 800;
      margin: 0 0 4px;
      letter-spacing: -0.2px;
    }
    .map-banner-text p {
      font-size: 12.5px;
      color: rgba(255,255,255,0.65);
      margin: 0;
      line-height: 1.5;
    }
    .map-banner-cta {
      position: relative; z-index: 1;
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 13px;
      font-weight: 700;
      padding: 11px 20px;
      border-radius: 100px;
      background: rgba(255,255,255,0.1);
      border: 1px solid rgba(255,255,255,0.22);
      white-space: nowrap;
      flex-shrink: 0;
      transition: all 0.2s ease;
    }
    .map-banner:hover .map-banner-cta {
      background: rgba(255,255,255,0.2);
      gap: 11px;
    }
    .map-banner-cta .live-dot {
      width: 6px; height: 6px;
      border-radius: 50%;
      background: #22c55e;
      box-shadow: 0 0 0 3px rgba(34,197,94,0.25);
      flex-shrink: 0;
    }

    /* ── SWITCHER: BUS | MPU ── */
    .switcher-card {
      background: #fff;
      border-radius: 20px;
      border: 1px solid #e8edf5;
      box-shadow: 0 12px 40px rgba(10,31,68,0.12);
      padding: 10px;
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 10px;
      align-items: stretch;
      margin-bottom: 40px;
    }
    .switch-pill {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      padding: 16px 20px;
      border-radius: 14px;
      border: none;
      cursor: pointer;
      font-family: inherit;
      font-size: 14.5px;
      font-weight: 700;
      color: #64748b;
      background: transparent;
      transition: all 0.25s ease;
      text-decoration: none;
    }
    .switch-pill i { font-size: 17px; }
    .switch-pill:hover { background: #f1f5f9; color: #1e293b; }
    .switch-pill.active-bus {
      background: var(--navy);
      color: #fff;
      box-shadow: 0 4px 16px rgba(10,31,68,0.25);
    }
    .switch-pill.active-mpu {
      background: var(--green);
      color: #fff;
      box-shadow: 0 4px 16px rgba(5,150,105,0.3);
    }
    .switch-pill .pill-count {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-width: 20px;
      height: 20px;
      padding: 0 6px;
      border-radius: 100px;
      font-size: 11px;
      font-weight: 700;
      background: rgba(255,255,255,0.2);
      color: inherit;
    }
    .switch-pill:not(.active-bus):not(.active-mpu) .pill-count {
      background: #e2e8f0;
      color: #64748b;
    }

    @media (max-width: 640px) {
      .switcher-card { grid-template-columns: 1fr; }
      .map-banner { flex-wrap: wrap; }
      .map-banner-cta { width: 100%; justify-content: center; }
    }


    /* ── SECTION HEADER ── */
    .section-header {
      display: flex;
      align-items: flex-end;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 20px;
      margin-bottom: 28px;
      padding-bottom: 22px;
      border-bottom: 1px solid #e8edf5;
    }
    .section-tag {
      display: inline-block;
      font-size: 11px;
      font-weight: 700;
      letter-spacing: 2px;
      text-transform: uppercase;
      color: var(--blue);
      margin-bottom: 6px;
    }
    .section-title {
      font-size: clamp(1.3rem, 3vw, 1.7rem);
      font-weight: 800;
      color: var(--navy);
      letter-spacing: -0.5px;
      margin: 0;
      transition: color 0.3s ease;
    }
    .section-title.mpu-mode { color: var(--green); }

    /* ── CARDS ── */
    .trayek-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(290px, 1fr));
      gap: 20px;
    }
    .trayek-grid.tab-hidden { display: none !important; }
    #contentBus .route-card, #contentMPU .route-card {
      display: flex;
      flex-direction: column;
      gap: 18px;
      background: #fff;
      border-radius: 16px;
      padding: 22px;
      border: 1px solid #e8edf5;
      transition: all 0.3s cubic-bezier(0.34,1.56,0.64,1);
      box-shadow: 0 1px 4px rgba(0,0,0,0.04);
    }
    #contentBus .route-card:hover, #contentMPU .route-card:hover {
      transform: translateY(-6px);
      box-shadow: 0 20px 50px rgba(10, 31, 68, 0.12);
      border-color: #c7d7f0;
    }
    .route-card-top {
      display: flex;
      align-items: center;
      gap: 14px;
    }
    .route-card-icon {
      flex-shrink: 0;
      width: 44px;
      height: 44px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .route-card-icon img { width: 22px; height: 22px; object-fit: contain; }
    .route-card-icon-bus { background: linear-gradient(135deg, rgba(26,86,219,0.12), rgba(56,189,248,0.12)); }
    .route-card-icon-mpu { background: linear-gradient(135deg, rgba(5,150,105,0.12), rgba(45,212,191,0.12)); }
    .route-card-title {
      font-size: 15px;
      font-weight: 700;
      color: var(--navy);
      margin: 0;
      line-height: 1.35;
    }
    .route-card-btn {
      margin-top: auto;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      width: 100%;
      padding: 11px 16px;
      border-radius: 12px;
      border: none;
      font-family: inherit;
      font-size: 13px;
      font-weight: 700;
      cursor: pointer;
      transition: all 0.2s ease;
    }
    .route-card-btn i { font-size: 11px; transition: transform 0.2s ease; }
    .route-card-btn:hover i { transform: translateX(3px); }
    .route-card-btn-bus { background: #eef4ff; color: var(--blue); }
    .route-card-btn-bus:hover { background: var(--blue); color: #fff; }
    .route-card-btn-mpu { background: #ecfdf5; color: var(--green); }
    .route-card-btn-mpu:hover { background: var(--green); color: #fff; }

    /* ── LOADER ── */
    #loader {
      position: fixed;
      inset: 0;
      background: rgba(255,255,255,0.85);
      backdrop-filter: blur(6px);
      z-index: 200;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    #loader.hidden { display: none; }
    .loader-ring {
      width: 48px;
      height: 48px;
      border: 3px solid #e2e8f0;
      border-top-color: var(--blue);
      border-radius: 50%;
      animation: spin 0.8s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }

    @media (max-width: 768px) {
      .page-hero { padding: 96px 1.25rem 90px; }
      .section-wrap { padding: 0 1.25rem 60px; }
      .section-header { flex-direction: column; align-items: flex-start; gap: 16px; }
    }
  </style>
</head>
<body>

  <!-- Header - komponen terpusat, sama dengan seluruh halaman lain -->
  @include('partials.site-header', ['solid' => true, 'loginNext' => '/admin/data-absensi'])

  <!-- Page hero strip -->
  <section class="page-hero">
    <div class="page-hero-inner">
      <div class="breadcrumb">
        <a href="/">Beranda</a>
        <i class="fa-solid fa-chevron-right" style="font-size:9px;"></i>
        <span>Rute Angkutan Sekolah</span>
      </div>
      <h2>Rute Angkutan Sekolah</h2>
      <p>Pilih BUS atau MPU untuk melihat daftar trayek, atau buka peta gabungan untuk melihat seluruh rute &amp; posisi driver sekaligus.</p>
    </div>
  </section>

  <div class="section-wrap">

    <!-- Peta Gabungan - banner elegan tersendiri, terpisah dari switcher BUS/MPU -->
    <a href="/rute-sekolah/peta" class="map-banner">
      <div class="map-banner-icon"><i class="fa-solid fa-map-location-dot"></i></div>
      <div class="map-banner-text">
        <h4>Peta Gabungan Rute</h4>
        <p>Lihat seluruh trayek BUS &amp; MPU sekaligus, lengkap dengan posisi driver secara real-time.</p>
      </div>
      <div class="map-banner-cta">
        <span class="live-dot"></span>
        Buka Peta <i class="fa-solid fa-arrow-right"></i>
      </div>
    </a>

    <!-- Switcher: BUS | MPU -->
    <div class="switcher-card" role="tablist">
      <button id="tabBus" class="switch-pill active-bus" role="tab" aria-selected="true">
        <i class="fa-solid fa-bus"></i> BUS
        <span class="pill-count" id="countBus">–</span>
      </button>
      <button id="tabMPU" class="switch-pill" role="tab" aria-selected="false">
        <i class="fa-solid fa-van-shuttle"></i> MPU
        <span class="pill-count" id="countMPU">–</span>
      </button>
    </div>

    <div class="section-header">
      <div class="section-heading">
        <p class="section-tag">Daftar Trayek</p>
        <h3 class="section-title" id="section-title-text">BUS</h3>
      </div>
    </div>

    <section id="contentBus" class="trayek-grid"></section>
    <section id="contentMPU" class="trayek-grid tab-hidden"></section>
  </div>

  <!-- Loader -->
  <div id="loader">
    <div class="loader-ring"></div>
  </div>

  <!-- Footer - komponen terpusat, sama dengan seluruh halaman lain -->
  @include('partials.site-footer')

  <script type="module">
    import renderBus from '{{ asset_url('/assets/rute-sekolah/partials/busangkutan.js') }}';
    import renderMPU from '{{ asset_url('/assets/rute-sekolah/partials/mpuangkutan.js') }}';

    const tabBus      = document.getElementById('tabBus');
    const tabMPU      = document.getElementById('tabMPU');
    const contentBus  = document.getElementById('contentBus');
    const contentMPU  = document.getElementById('contentMPU');
    const loader      = document.getElementById('loader');
    const sectionText = document.getElementById('section-title-text');
    const countBus    = document.getElementById('countBus');
    const countMPU    = document.getElementById('countMPU');

    function applyCardClasses(container) {
      container.querySelectorAll(':scope > div').forEach(el => el.classList.add('route-card'));
    }

    function showLoader() { loader.classList.remove('hidden'); }
    function hideLoader() { loader.classList.add('hidden'); }

    async function loadLists() {
      showLoader();
      await Promise.all([
        renderBus(contentBus).then(() => {
          applyCardClasses(contentBus);
          const n = contentBus.querySelectorAll('.route-card').length;
          countBus.textContent = n > 0 ? n : '–';
        }),
        renderMPU(contentMPU).then(() => {
          applyCardClasses(contentMPU);
          const n = contentMPU.querySelectorAll('.route-card').length;
          countMPU.textContent = n > 0 ? n : '–';
          contentMPU.dataset.loaded = true;
        }),
      ]);
      hideLoader();
    }
    loadLists();

    // ── Realtime: refresh otomatis saat admin ubah data trayek ──
    window.SB_URL  = {!! json_encode(config('services.supabase.url')) !!};
    window.SB_ANON = {!! json_encode(config('services.supabase.anon_key')) !!};

    const sbScript = document.createElement('script');
    sbScript.src = 'https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2';
    sbScript.onload = () => {
      if (window.SB_URL && window.SB_ANON && window.supabase) {
        const sbRealtime = window.supabase.createClient(window.SB_URL, window.SB_ANON);
        let _rtTimer = null;
        const scheduleReload = () => {
          clearTimeout(_rtTimer);
          _rtTimer = setTimeout(loadLists, 500);
        };
        sbRealtime
          .channel('rute-list-page')
          .on('postgres_changes', { event: '*', schema: 'public', table: 'trayek_bus' }, scheduleReload)
          .on('postgres_changes', { event: '*', schema: 'public', table: 'trayek_mpu' }, scheduleReload)
          .subscribe();
        window.addEventListener('pagehide', () => sbRealtime.removeAllChannels());
      }
    };
    document.head.appendChild(sbScript);

    tabBus.addEventListener('click', () => {
      tabBus.className = 'switch-pill active-bus';
      tabBus.setAttribute('aria-selected', 'true');
      tabMPU.className = 'switch-pill';
      tabMPU.setAttribute('aria-selected', 'false');
      contentBus.classList.remove('tab-hidden');
      contentMPU.classList.add('tab-hidden');
      sectionText.textContent = 'BUS';
      sectionText.classList.remove('mpu-mode');
    });

    tabMPU.addEventListener('click', () => {
      tabMPU.className = 'switch-pill active-mpu';
      tabMPU.setAttribute('aria-selected', 'true');
      tabBus.className = 'switch-pill';
      tabBus.setAttribute('aria-selected', 'false');
      contentBus.classList.add('tab-hidden');
      contentMPU.classList.remove('tab-hidden');
      sectionText.textContent = 'MPU';
      sectionText.classList.add('mpu-mode');
    });
  </script>
</body>
</html>
