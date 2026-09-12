<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="icon" href="/favicon.ico"/>
  <title>Digitalisasi Layanan Transportasi Publik</title>
  <meta name="title" content="Angkutan Sekolah Gratis (Bus Gratis) Tulungagung" />
  <meta name="description" content="Layanan Angkutan Sekolah Gratis (Bus Gratis) dari Dinas Perhubungan Kabupaten Tulungagung untuk mempermudah akses pelajar menuju sekolah dengan aman dan tanpa biaya." />
  <meta property="og:type" content="website" />
  <meta property="og:url" content="{{ url('/') }}" />
  <meta property="og:title" content="Angkutan Sekolah Gratis (Bus Gratis) Tulungagung" />
  <meta property="og:description" content="Layanan Angkutan Sekolah Gratis dari Dinas Perhubungan Kabupaten Tulungagung." />
  <meta property="og:image" content="{{ url('/assets/angkutan_sekolah.webp') }}" />
  <meta property="twitter:card" content="summary_large_image" />
  <meta property="twitter:url" content="{{ url('/') }}" />
  <meta property="twitter:title" content="Angkutan Sekolah Gratis (Bus Gratis) Tulungagung" />
  <meta property="twitter:description" content="Layanan Angkutan Sekolah Gratis dari Dinas Perhubungan Kabupaten Tulungagung." />
  <meta property="twitter:image" content="{{ url('/assets/angkutan_sekolah.webp') }}" />

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
    .hero {
      position: relative;
      width: 100%;
      height: 100vh;
      height: 100dvh;
      min-height: 520px;
      overflow: hidden;
    }
    .hero-video {
      position: absolute;
      inset: 0;
      width: 100%;
      height: 100%;
      object-fit: cover;
      object-position: center;
    }
    .hero-overlay {
      position: absolute;
      inset: 0;
      background: linear-gradient(
        160deg,
        rgba(10, 31, 68, 0.75) 0%,
        rgba(10, 31, 68, 0.55) 50%,
        rgba(10, 31, 68, 0.35) 100%
      );
    }
    .hero-content {
      position: relative;
      z-index: 2;
      height: 100%;
      display: flex;
      flex-direction: column;
      justify-content: center;
      padding: 68px 2.5rem 3rem;
      max-width: 820px;
    }
    .hero-title {
      font-size: clamp(2rem, 5.5vw, 3.6rem);
      font-weight: 800;
      color: #fff;
      line-height: 1.12;
      letter-spacing: -1.5px;
      margin: 0 0 18px;
    }
    .hero-title em {
      font-style: normal;
      color: var(--sky);
    }
    .hero-sub {
      font-size: clamp(1rem, 2vw, 1.15rem);
      color: rgba(255,255,255,0.72);
      line-height: 1.65;
      max-width: 480px;
      margin: 0 0 36px;
      font-weight: 400;
    }
    .hero-scroll-hint {
      position: absolute;
      bottom: 28px;
      left: 50%;
      transform: translateX(-50%);
      z-index: 2;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 6px;
      color: rgba(255,255,255,0.45);
      font-size: 11px;
      letter-spacing: 1.5px;
      text-transform: uppercase;
    }
    .scroll-mouse {
      width: 22px;
      height: 36px;
      border: 2px solid rgba(255,255,255,0.3);
      border-radius: 12px;
      display: flex;
      justify-content: center;
      padding-top: 5px;
    }
    .scroll-mouse::before {
      content:'';
      width: 3px;
      height: 8px;
      background: rgba(255,255,255,0.5);
      border-radius: 2px;
      animation: scroll-down 1.8s ease-in-out infinite;
    }
    @keyframes scroll-down { 0%{opacity:1;transform:translateY(0);} 100%{opacity:0;transform:translateY(10px);} }
    .section-wrap {
      max-width: 1100px;
      margin: 0 auto;
      padding: 60px 2rem 80px;
    }
    .section-header {
      display: flex;
      align-items: flex-end;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 20px;
      margin-bottom: 36px;
      padding-bottom: 28px;
      border-bottom: 1px solid #e8edf5;
    }
    .section-heading {
      flex: 1;
      min-width: 200px;
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
      font-size: clamp(1.4rem, 3vw, 1.9rem);
      font-weight: 800;
      color: var(--navy);
      letter-spacing: -0.5px;
      margin: 0;
    }
    .menu-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 20px;
    }
    .menu-card {
      display: flex;
      flex-direction: column;
      background: #fff;
      border: 1px solid #e8edf5;
      border-radius: 18px;
      padding: 24px;
      text-decoration: none;
      color: inherit;
      transition: all 0.3s cubic-bezier(0.34,1.56,0.64,1);
      box-shadow: 0 1px 4px rgba(0,0,0,0.04);
    }
    .menu-card:hover {
      transform: translateY(-6px);
      box-shadow: 0 20px 50px rgba(10, 31, 68, 0.12);
      border-color: #c7d7f0;
    }
    .menu-card-row {
      display: flex;
      align-items: center;
      gap: 18px;
    }
    .menu-card-icon {
      flex-shrink: 0;
      width: 54px;
      height: 54px;
      border-radius: 14px;
      background: linear-gradient(135deg, var(--blue), var(--sky));
      display: flex;
      align-items: center;
      justify-content: center;
      color: #fff;
      font-size: 22px;
      box-shadow: 0 6px 16px rgba(26,86,219,0.3);
    }
    .menu-card-body { flex: 1; min-width: 0; }
    .menu-card-body h4 {
      font-size: 16px;
      font-weight: 800;
      color: var(--navy);
      margin: 0 0 4px;
    }
    .menu-card-body p {
      font-size: 13px;
      color: #64748b;
      margin: 0;
      line-height: 1.5;
    }
    .menu-card-badge-off {
      display: inline-block;
      font-size: 10px;
      font-weight: 700;
      letter-spacing: .02em;
      color: #b91c1c;
      background: #fee2e2;
      border-radius: 6px;
      padding: 2px 7px;
      vertical-align: middle;
      margin-left: 4px;
    }
    .menu-card-arrow {
      flex-shrink: 0;
      color: #cbd5e1;
      font-size: 14px;
      transition: transform 0.25s ease, color 0.25s ease;
    }
    .menu-card:hover .menu-card-arrow {
      color: var(--blue);
      transform: translateX(4px);
    }
    .menu-card.is-soon {
      border-style: dashed;
      border-color: #e2e8f0;
      background: #fbfcfe;
    }
    .menu-card.is-soon:hover {
      border-color: #fbbf24;
      box-shadow: 0 20px 50px rgba(180, 83, 9, 0.10);
    }
    .menu-card.is-soon .menu-card-icon {
      background: linear-gradient(135deg, #cbd5e1, #94a3b8);
      box-shadow: none;
    }
    .menu-card.is-soon .menu-card-body p { color: #94a3b8; }
    .menu-card-soon-row {
      margin-top: 14px;
      padding-top: 14px;
      padding-left: 72px;
      border-top: 1px dashed #eef1f6;
    }
    .menu-card-soon {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-size: 11px;
      font-weight: 700;
      letter-spacing: .02em;
      color: #b45309;
      background: #fff7ed;
      border: 1px solid #fed7aa;
      padding: 7px 13px;
      border-radius: 999px;
      white-space: nowrap;
      transition: border-color 0.25s ease, background 0.25s ease;
    }
    .menu-card-soon i { font-size: 10px; }
    .menu-card.is-soon:hover .menu-card-soon {
      background: #fef3e2;
      border-color: #f59e0b;
    }
    @media (max-width: 768px) {
      .hero-content { padding: 68px 1.25rem 2.5rem; }
      .section-wrap { padding: 40px 1.25rem 60px; }
      .section-header { flex-direction: column; align-items: flex-start; gap: 16px; }
      .tab-pill { padding: 9px 18px; font-size: 13px; }
    }
  </style>
</head>
<body>

  @include('partials.site-header', ['loginNext' => '/'])

  <section class="hero">
    <video
      class="hero-video"
      src="{{ asset('assets/bussekolah.mp4') }}"
      autoplay muted loop playsinline
    ></video>
    <div class="hero-overlay"></div>
    <div class="hero-content">
      <h2 class="hero-title">
        Digitalisasi Layanan <br><em>Transportasi Publik Terintegrasi</em>
      </h2>
      <p class="hero-sub">
        Transformasi digital dalam penyelenggaraan layanan transportasi yang terintegrasi, inovatif, dan berorientasi pada peningkatan kualitas pelayanan publik.
      </p>
    </div>
    <div class="hero-scroll-hint">
      <div class="scroll-mouse"></div>
      Scroll
    </div>
  </section>

  <div id="main-content">
    <div class="section-wrap">

      <div class="section-header">
        <div class="section-heading">
          <p class="section-tag">Menu Layanan</p>
          <h3 class="section-title">Pilih Layanan</h3>
        </div>
      </div>

      <div class="menu-grid">
        @foreach ($menuItems as $mi)
        <a href="{{ $mi['href'] }}" class="menu-card{{ !$mi['aktif'] ? ' is-soon' : '' }}">
          <div class="menu-card-row">
            <div class="menu-card-icon">
              <i class="{{ $mi['icon'] }}"></i>
            </div>
            <div class="menu-card-body">
              <h4>{{ $mi['title'] }}@if ($isSuperAdminView && !$mi['aktif']) <span class="menu-card-badge-off">Nonaktif</span>@endif</h4>
              <p>{{ $mi['desc'] }}</p>
            </div>
            @if ($mi['aktif'])
            <i class="fa-solid fa-chevron-right menu-card-arrow"></i>
            @endif
          </div>
          @if (!$mi['aktif'])
          <div class="menu-card-soon-row">
            <span class="menu-card-soon"><i class="fa-regular fa-clock"></i> Segera Hadir</span>
          </div>
          @endif
        </a>
        @endforeach
      </div>
    </div>
  </div>

  @include('partials.site-footer')

</body>
</html>
