<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"/>
<meta name="csrf-token" content="{{ csrf_token() }}"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover"/>
<title>Trayek Wisata Gratis | Dishub Kabupaten Tulungagung</title>
<meta name="description" content="Pilih destinasi dan jadwal Trayek Wisata Gratis Dishub Kabupaten Tulungagung setiap akhir pekan."/>
<meta property="og:type" content="website"/>
<meta property="og:site_name" content="Dishub Kabupaten Tulungagung"/>
<meta property="og:title" content="Trayek Wisata Gratis Tulungagung"/>
<meta property="og:description" content="Pilih destinasi, lihat jadwal, dan dapatkan kursi gratis untuk menjelajahi Pantai Selatan Tulungagung."/>
<meta property="og:image" content="{{ url('/assets/trayek-wisata/hero-poster.jpg') }}"/>
<meta property="og:url" content="{{ url('/trayek-wisata') }}"/>
<meta property="og:locale" content="id_ID"/>
<meta name="twitter:card" content="summary_large_image"/>
<meta name="twitter:title" content="Trayek Wisata Gratis Tulungagung"/>
<meta name="twitter:description" content="Pilih destinasi dan jadwal wisata gratis setiap akhir pekan."/>
<meta name="twitter:image" content="{{ url('/assets/trayek-wisata/hero-poster.jpg') }}"/>
<meta name="theme-color" content="#0A1F44"/>
<link rel="manifest" href="/assets/trayek-wisata/manifest.webmanifest"/>
<link rel="icon" href="/favicon.ico"/>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.css"/>
<link rel="stylesheet" href="{{ asset_url('/assets/trayek-wisata/style.css') }}"/>
</head>
<body>
<script src="{{ asset_url('/assets/trayek-wisata/nav-progress.js') }}"></script>
<a href="#top" class="skip-link">Lompat ke konten utama</a>
@include('partials.trayek-wisata.nav')

<header class="hero homepage-hero" id="top">
  <div class="hero-media">
    <video autoplay muted loop playsinline poster="/assets/trayek-wisata/hero-poster.jpg" onerror="this.style.display='none';">
      <source src="/assets/trayek-wisata/hero.mp4" type="video/mp4"/>
    </video>
    <div class="hero-fallback" id="heroFallback"></div>
  </div>
  <div class="hero-scrim"></div>
  <div class="hero-inner wrap">
    <div class="hero-badge"><span class="dot"></span> Gratis setiap Sabtu &amp; Minggu</div>
    <div class="hero-stat" id="heroStat" style="display:none">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      <span><strong id="heroStatNum">0</strong> orang sudah ikut</span>
    </div>
    <h1 class="hero-title">Akhir pekan,<br/><em>ke Pantai Selatan.</em></h1>
    <p class="hero-sub">Naik trayek wisata gratis Dishub Kabupaten Tulungagung. Pilih destinasi, cek jadwal, lalu berangkat dengan nyaman.</p>
    <div class="hero-cta">
      <a class="btn btn-solid" href="/trayek-wisata/jadwal">Pilih Jadwal</a>
      <a class="btn btn-ghost" href="/trayek-wisata/destinasi">Lihat Destinasi</a>
    </div>
  </div>
  <div class="hero-scroll"><span>Gulir</span><span class="hero-scroll-line"></span></div>
  <svg class="hero-wave" viewBox="0 0 1440 90" preserveAspectRatio="none" aria-hidden="true"><path fill="#FBFBF7" d="M0,40 C240,90 480,0 720,30 C960,60 1200,10 1440,45 L1440,90 L0,90 Z"/></svg>
</header>

<main id="main">
  <section class="home-start" aria-labelledby="home-start-title">
    <div class="wrap">
      <div class="home-section-heading">
        <span class="eyebrow"><svg class="eyebrow-svg" width="28" height="12" viewBox="0 0 28 12" fill="none" aria-hidden="true"><path d="M1 6h19" stroke="currentColor" stroke-linecap="round"/><circle cx="24" cy="6" r="2" fill="currentColor"/></svg>Mulai di sini</span>
        <h2 id="home-start-title">Mau melihat tempatnya,<br/><em>atau langsung berangkat?</em></h2>
      </div>
      <div class="home-choice-grid">
        <a class="home-choice home-choice-destination" href="/trayek-wisata/destinasi">
          <span class="home-choice-kicker">01 · Jelajahi</span>
          <span class="home-choice-title">Kenali destinasi</span>
          <span class="home-choice-copy">Lihat pantai dan titik wisata yang bisa Anda kunjungi.</span>
          <span class="home-choice-action">Lihat destinasi <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6"/></svg></span>
        </a>
        <a class="home-choice home-choice-schedule" href="/trayek-wisata/jadwal">
          <span class="home-choice-kicker">02 · Pilihan utama</span>
          <span class="home-choice-title">Pilih jadwal &amp; dapatkan kursi</span>
          <span class="home-choice-copy">Lihat rute, titik keberangkatan, kuota, lalu booking gratis.</span>
          <span class="home-choice-action">Buka jadwal <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6"/></svg></span>
        </a>
      </div>
      <p class="home-note"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/></svg> Data diri dan peserta hanya diminta saat Anda memilih jadwal.</p>
    </div>
  </section>

  <section class="jadwal home-schedule" id="jadwal" aria-labelledby="home-schedule-title">
    <div class="wrap" style="position:relative">
      <div class="home-schedule-head">
        <div>
          <span class="eyebrow eyebrow-light"><svg class="eyebrow-svg" width="28" height="12" viewBox="0 0 28 12" fill="none" aria-hidden="true"><path d="M1 6h19" stroke="currentColor" stroke-linecap="round"/><circle cx="24" cy="6" r="2" fill="currentColor"/></svg>Keberangkatan terdekat</span>
          <h2 id="home-schedule-title" class="schedule-heading">Cari waktu yang<br/><em>paling pas.</em></h2>
        </div>
        <a class="tw-more-link home-inline-link" href="/trayek-wisata/jadwal">Lihat semua jadwal <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6"/></svg></a>
      </div>
      <div class="jadwal-scroll" id="jadwalScroll">
        <div class="skel" style="flex:0 0 280px;height:230px;background:rgba(255,255,255,.08)"></div>
        <div class="skel" style="flex:0 0 280px;height:230px;background:rgba(255,255,255,.08)"></div>
      </div>
    </div>
  </section>

  <section class="home-destinations" aria-labelledby="home-destination-title">
    <div class="wrap">
      <div class="home-destination-head">
        <div>
          <span class="eyebrow"><svg class="eyebrow-svg" width="28" height="12" viewBox="0 0 28 12" fill="none" aria-hidden="true"><path d="M1 6h19" stroke="currentColor" stroke-linecap="round"/><circle cx="24" cy="6" r="2" fill="currentColor"/></svg>Tujuan perjalanan</span>
          <h2 id="home-destination-title">Pantai Selatan<br/><em>menunggu Anda.</em></h2>
        </div>
        <a class="tw-more-link home-inline-link" href="/trayek-wisata/destinasi">Lihat semua destinasi <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6"/></svg></a>
      </div>
      <div class="gallery-grid home-gallery-grid" id="galleryGrid">
        <div class="skel skel-card" style="aspect-ratio:4/5"></div>
        <div class="skel skel-card" style="aspect-ratio:4/5"></div>
        <div class="skel skel-card" style="aspect-ratio:4/5"></div>
      </div>
    </div>
  </section>
</main>

<div class="wisata-modal-overlay" id="wisataModalOverlay" onclick="if(event.target===this) closeWisataModal()" role="dialog" aria-modal="true" aria-label="Galeri destinasi">
  <div class="wisata-modal">
    <button class="wisata-modal-close" onclick="closeWisataModal()" aria-label="Tutup galeri">✕</button>
    <div class="wisata-modal-media" id="wisataModalMedia">
      <button class="wisata-modal-nav prev" onclick="wisataModalNav(-1)" aria-label="Foto sebelumnya">‹</button>
      <button class="wisata-modal-nav next" onclick="wisataModalNav(1)" aria-label="Foto berikutnya">›</button>
      <div class="wisata-modal-dots" id="wisataModalDots"></div>
    </div>
    <div class="wisata-modal-body">
      <div class="wisata-modal-tag" id="wisataModalTag">Pantai Selatan</div>
      <div class="wisata-modal-name" id="wisataModalName"></div>
      <p class="wisata-modal-desc" id="wisataModalDesc"></p>
      <div class="wisata-modal-visit" id="wisataModalVisit"></div>
    </div>
  </div>
</div>

@include('partials.trayek-wisata.footer')
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.js"></script>
<script src="https://unpkg.com/lenis@1.1.13/dist/lenis.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script src="{{ asset_url('/assets/trayek-wisata/ticket-modal.js') }}"></script>
<script src="{{ asset_url('/assets/trayek-wisata/tw-core.js') }}"></script>
<script>
  twLoadAuth();
  twBootstrap({
    gallery: { limit: 3, moreLink: '/trayek-wisata/destinasi' },
    jadwal: { limit: 3, moreLink: '/trayek-wisata/jadwal' },
    heroStat: true,
  });
</script>
</body>
</html>
