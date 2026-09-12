<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"/>
<meta name="csrf-token" content="{{ csrf_token() }}"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover"/>
<title>Trayek Wisata Gratis - Pantai Selatan Tulungagung | Dishub Kabupaten Tulungagung</title>
<meta name="description" content="Jelajahi Pantai Selatan Tulungagung gratis setiap Sabtu & Minggu lewat Jalur Lintas Selatan (JLS). Layanan Trayek Wisata Gratis dari Dinas Perhubungan Kabupaten Tulungagung."/>

<meta property="og:type" content="website"/>
<meta property="og:site_name" content="Dishub Kabupaten Tulungagung"/>
<meta property="og:title" content="Trayek Wisata Gratis - Jelajahi Pantai Selatan Tulungagung"/>
<meta property="og:description" content="Gratis setiap Sabtu & Minggu lewat Jalur Lintas Selatan (JLS). Pesan kursi langsung dari HP - kuota terbatas."/>
<meta property="og:image" content="{{ url('/assets/trayek-wisata/hero-poster.jpg') }}"/>
<meta property="og:image:width" content="1200"/>
<meta property="og:image:height" content="630"/>
<meta property="og:url" content="{{ url('/trayek-wisata') }}"/>
<meta property="og:locale" content="id_ID"/>
<meta name="twitter:card" content="summary_large_image"/>
<meta name="twitter:title" content="Trayek Wisata Gratis - Jelajahi Pantai Selatan Tulungagung"/>
<meta name="twitter:description" content="Gratis setiap Sabtu & Minggu lewat Jalur Lintas Selatan (JLS). Pesan kursi langsung dari HP."/>
<meta name="twitter:image" content="{{ url('/assets/trayek-wisata/hero-poster.jpg') }}"/>
<meta name="theme-color" content="#0A1F44"/>
<link rel="manifest" href="/assets/trayek-wisata/manifest.webmanifest"/>
<link rel="icon" href="/favicon.ico"/>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,300..700;1,9..144,400..600&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.css"/>
<link rel="stylesheet" href="{{ asset_url('/assets/trayek-wisata/style.css') }}"/>
</head>
<body>
<script src="{{ asset_url('/assets/trayek-wisata/nav-progress.js') }}"></script>
<a href="#top" class="skip-link">Lompat ke konten utama</a>

@include('partials.trayek-wisata.nav')

<!-- ═══ HERO SINEMATIK ═══ -->
<header class="hero" id="top">
  <div class="hero-media">
    <video autoplay muted loop playsinline poster="/assets/trayek-wisata/hero-poster.jpg"
           onerror="this.style.display='none';">
      <source src="/assets/trayek-wisata/hero.mp4" type="video/mp4"/>
    </video>
    <div class="hero-fallback" id="heroFallback"></div>
  </div>
  <div class="hero-scrim"></div>

  <div class="hero-inner wrap">
    <div class="hero-badge"><span class="dot"></span> Gratis · Setiap Sabtu &amp; Minggu · Dishub Kab. Tulungagung</div>
    <div class="hero-stat" id="heroStat" style="display:none">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      <span><strong id="heroStatNum">0</strong> orang sudah ikut trayek wisata ini</span>
    </div>
    <h1 class="hero-title">Jelajahi <em>Pantai Selatan</em><br/>Tulungagung.</h1>
    <p class="hero-sub">Angkutan sekolah dialihkan jadi trayek wisata gratis lewat Jalur Lintas Selatan (JLS), gratis setiap akhir pekan.</p>
    <div class="hero-cta">
      <a class="btn btn-solid" href="/trayek-wisata/rute">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M12 21s7-4.35 7-11a7 7 0 1 0-14 0c0 6.65 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>
        Lihat Trayek
      </a>
      <a class="btn btn-ghost" href="/trayek-wisata/jadwal">Pesan Kursi</a>
    </div>
  </div>

  <div class="hero-scroll"><span>Gulir</span><span class="hero-scroll-line"></span></div>
  <svg class="hero-wave" viewBox="0 0 1440 90" preserveAspectRatio="none"><path fill="#FBFBF7" d="M0,40 C240,90 480,0 720,30 C960,60 1200,10 1440,45 L1440,90 L0,90 Z"/></svg>
</header>

<!-- ═══ CUPLIKAN ALUR PERJALANAN ═══ -->
<section class="story" id="story">
  <div class="wrap">
    <div class="story-head reveal">
      <div class="eyebrow">Alur Perjalanan</div>
      <h2 class="sect-title" style="margin-top:14px">Satu trayek,<br/>banyak cerita.</h2>
      <p class="sect-lede">Dari terminal keberangkatan, menyusuri Jalur Lintas Selatan, hingga singgah di tiap pantai - begini urutan perjalanan yang akan Anda tempuh.</p>
    </div>
    <div class="story-track">
      <div class="story-spine"></div>
      <div class="story-spine-fill" id="storySpineFill"></div>
      <div id="storyItems">
        <div class="skel-story-item"><div class="skel skel-dot"></div><div class="skel-body"><div class="skel skel-line" style="width:120px"></div><div class="skel skel-line" style="width:220px;height:22px;margin-top:10px"></div><div class="skel skel-line" style="width:340px"></div></div></div>
      </div>
    </div>
  </div>
</section>

<!-- ═══ DESTINASI UNGGULAN ═══ -->
<section class="gallery" id="wisata">
  <div class="wrap">
    <div class="reveal">
      <div class="eyebrow">Destinasi</div>
      <h2 class="sect-title" style="margin-top:14px">Pantai-pantai yang<br/>akan Anda singgahi.</h2>
      <p class="sect-lede">Setiap titik dipilih karena punya karakter sendiri - dari ombak besar Selatan hingga teluk tenang tersembunyi di balik tebing.</p>
    </div>
    <div class="gallery-grid" id="galleryGrid">
      <div class="skel skel-card" style="aspect-ratio:4/5"></div>
      <div class="skel skel-card" style="aspect-ratio:4/5"></div>
      <div class="skel skel-card" style="aspect-ratio:4/5"></div>
    </div>
  </div>
</section>

<!-- ═══ Modal Galeri Wisata ═══ -->
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

<!-- ═══ JADWAL MINGGU INI ═══ -->
<section class="jadwal" id="jadwal">
  <div class="wrap" style="position:relative">
    <div class="reveal">
      <div class="eyebrow">Jadwal</div>
      <h2 class="sect-title" style="margin-top:14px">Berangkat<br/>akhir pekan ini.</h2>
      <p class="sect-lede">Kursi terbatas - status hijau berarti masih longgar. Lihat jadwal lengkap 4 minggu ke depan di halaman Jadwal.</p>
    </div>
    <div class="jadwal-scroll" id="jadwalScroll">
      <div class="skel" style="flex:0 0 280px;height:230px;background:rgba(255,255,255,.08)"></div>
      <div class="skel" style="flex:0 0 280px;height:230px;background:rgba(255,255,255,.08)"></div>
    </div>
  </div>
</section>

<!-- ═══ KUOTA MINGGU INI ═══ -->
<section class="kuota">
  <div class="wrap">
    <div class="reveal">
      <div class="eyebrow">Kuota Realtime</div>
      <h2 class="sect-title" style="margin-top:14px">Kuota minggu ini.</h2>
    </div>
    <div class="kuota-grid">
      <div class="kuota-ring-wrap reveal">
        <div class="kuota-ring">
          <svg width="260" height="260" viewBox="0 0 260 260">
            <circle class="kuota-ring-track" cx="130" cy="130" r="112"/>
            <circle class="kuota-ring-fill" id="kuotaRingFill" cx="130" cy="130" r="112" stroke-dasharray="704" stroke-dashoffset="704"/>
          </svg>
          <div class="kuota-ring-label">
            <div class="kuota-ring-num" id="kuotaPercent">0%</div>
            <div class="kuota-ring-sub">Kursi Terisi</div>
          </div>
        </div>
      </div>
      <div class="reveal">
        <div class="kuota-bus-card">
          <img src="/assets/icons/bus_sekolah.svg" alt="Ilustrasi Bus Sekolah" class="kuota-bus-img"/>
        </div>
        <div class="kuota-list" id="kuotaList">
          <div class="kuota-list-item"><span>Total Kuota</span><b id="kTotal">-</b></div>
          <div class="kuota-list-item"><span>Kursi Terisi</span><b id="kTerisi">-</b></div>
          <div class="kuota-list-item"><span>Sisa Kursi</span><b id="kSisa">-</b></div>
          <div class="kuota-list-item"><span>Jumlah Trayek Minggu Ini</span><b id="kTrayek">-</b></div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ═══ CARA MENGIKUTI ═══ -->
<section class="boarding">
  <div class="wrap">
    <div class="reveal">
      <div class="eyebrow">Cara Mengikuti</div>
      <h2 class="sect-title" style="margin-top:14px">Empat langkah,<br/>siap berangkat.</h2>
    </div>
    <div class="boarding-strip reveal">
      <div class="boarding-step">
        <div class="boarding-num">01</div>
        <div class="boarding-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><path d="M10 17l5-5-5-5"/><path d="M15 12H3"/></svg></div>
        <div class="boarding-title">Login</div>
        <div class="boarding-desc">Masuk dengan akun Google - cepat, tanpa perlu buat akun baru.</div>
      </div>
      <div class="boarding-step">
        <div class="boarding-num">02</div>
        <div class="boarding-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="9"/></svg></div>
        <div class="boarding-title">Verifikasi</div>
        <div class="boarding-desc">Lengkapi data diri &amp; NIK, scan KTP langsung lewat kamera.</div>
      </div>
      <div class="boarding-step">
        <div class="boarding-num">03</div>
        <div class="boarding-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="17" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="16" y1="2" x2="16" y2="6"/></svg></div>
        <div class="boarding-title">Pesan Kursi</div>
        <div class="boarding-desc">Pilih trayek &amp; tanggal - 1 NIK berhak 1 tiket setiap 4 minggu.</div>
      </div>
      <div class="boarding-step">
        <div class="boarding-num">04</div>
        <div class="boarding-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M8 6v6M2 12h19.6M2 12c0-3.3 0-5 .8-6.1S5 4 8.5 4h7c3.5 0 4.9.8 5.7 1.9S22 8.7 22 12v3.5c0 1.2 0 1.8-.4 2.2s-1 .4-2.2.4H4.6c-1.2 0-1.8 0-2.2-.4S2 16.7 2 15.5z"/></svg></div>
        <div class="boarding-title">Datang &amp; Berangkat</div>
        <div class="boarding-desc">Hadir di titik keberangkatan sesuai jam - bus akan menunggu Anda di sana.</div>
      </div>
    </div>
  </div>
</section>

<!-- ═══ FAQ (cuplikan) ═══ -->
<section class="faq" id="faq">
  <div class="wrap">
    <div class="reveal">
      <div class="eyebrow">Tanya Jawab</div>
      <h2 class="sect-title" style="margin-top:14px">Yang sering ditanyakan.</h2>
    </div>
    <div class="faq-list" id="faqList">
      <div class="faq-item">
        <button class="faq-q" onclick="toggleFaq(this)" aria-expanded="false"><span>Apakah benar-benar gratis?</span><span class="faq-q-icon">+</span></button>
        <div class="faq-a"><div class="faq-a-inner">Ya. Trayek Wisata ini adalah alih fungsi layanan Angkutan Sekolah Gratis milik Dinas Perhubungan Kabupaten Tulungagung yang dioperasikan setiap Sabtu &amp; Minggu - tidak ada biaya tiket sama sekali.</div></div>
      </div>
      <div class="faq-item">
        <button class="faq-q" onclick="toggleFaq(this)" aria-expanded="false"><span>Apakah saya bisa memesan lebih dari 1 kali dalam sebulan?</span><span class="faq-q-icon">+</span></button>
        <div class="faq-a"><div class="faq-a-inner">Setiap NIK - baik pemilik akun maupun anggota keluarga yang didaftarkan - hanya bisa memperoleh 1 tiket setiap 4 minggu, dihitung dari tanggal pemesanan terakhir.</div></div>
      </div>
    </div>
    <div class="reveal" style="margin-top:24px">
      <a href="/trayek-wisata/faq" class="tw-more-link">Lihat semua pertanyaan <span>→</span></a>
    </div>
  </div>
</section>

@include('partials.trayek-wisata.footer')

<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.js"></script>
<script src="https://unpkg.com/lenis@1.1.13/dist/lenis.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script src="{{ asset_url('/assets/trayek-wisata/ticket-modal.js') }}"></script>
<script src="{{ asset_url('/assets/trayek-wisata/tw-core.js') }}"></script>
<script>
  twLoadAuth();
  twBootstrap({
    story:   { limit: 4, moreLink: '/trayek-wisata/rute' },
    gallery: { limit: 4, moreLink: '/trayek-wisata/destinasi' },
    jadwal:  { limit: 4, moreLink: '/trayek-wisata/jadwal' },
    kuota: true,
    heroStat: true,
  });
</script>
</body>
</html>
