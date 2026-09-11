<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"/>
<meta name="csrf-token" content="{{ csrf_token() }}"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover"/>
<title>Destinasi Pantai — Trayek Wisata Gratis | Dishub Kabupaten Tulungagung</title>
<meta name="description" content="Semua pantai yang disinggahi Trayek Wisata Gratis Dishub Kabupaten Tulungagung — lengkap foto, video, dan cerita tiap destinasi."/>
<meta name="theme-color" content="#0A1F44"/>
<link rel="icon" href="/favicon.ico"/>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,300..700;1,9..144,400..600&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset_url('/assets/trayek-wisata/style.css') }}"/>
</head>
<body>
<script src="{{ asset_url('/assets/trayek-wisata/nav-progress.js') }}"></script>
<a href="#main" class="skip-link">Lompat ke konten utama</a>

@include('partials.trayek-wisata.nav', ['twNavSolid' => false])

@include('partials.trayek-wisata.page-header', [
    'twPageEyebrow' => 'Destinasi',
    'twPageTitle' => 'Pantai-pantai yang<br/>akan Anda singgahi.',
    'twPageDesc' => 'Setiap titik dipilih karena punya karakter sendiri — dari ombak besar Selatan hingga teluk tenang tersembunyi di balik tebing. Ketuk untuk melihat galeri lengkapnya.',
])

<main id="main">
<section class="gallery" style="padding-top:70px">
  <div class="wrap">
    <div class="gallery-grid" id="galleryGrid">
      <div class="skel skel-card" style="aspect-ratio:4/5"></div>
      <div class="skel skel-card" style="aspect-ratio:4/5"></div>
      <div class="skel skel-card" style="aspect-ratio:4/5"></div>
      <div class="skel skel-card" style="aspect-ratio:4/5"></div>
    </div>
  </div>
</section>

<section style="background:var(--paper);padding:0 0 100px;text-align:center">
  <a href="/trayek-wisata/jadwal" class="btn btn-solid">Sudah siap? Pesan Kursi →</a>
</section>
</main>

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

@include('partials.trayek-wisata.footer')

<script src="https://unpkg.com/lenis@1.1.13/dist/lenis.min.js"></script>
<script src="{{ asset_url('/assets/trayek-wisata/tw-core.js') }}"></script>
<script>
  twLoadAuth();
  twBootstrap({ gallery: true });
</script>
</body>
</html>
