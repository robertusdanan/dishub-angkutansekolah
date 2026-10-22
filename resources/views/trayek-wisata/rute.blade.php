<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"/>
<meta name="csrf-token" content="{{ csrf_token() }}"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover"/>
<title>Peta Rute Interaktif - Trayek Wisata Gratis | Dishub Kabupaten Tulungagung</title>
<meta name="description" content="Jelajahi rute lengkap Trayek Wisata Gratis di peta interaktif - lihat tiap titik, foto, video, dan estimasi waktu tiba sebelum berangkat."/>
<meta name="theme-color" content="#0A1F44"/>
<link rel="icon" href="/favicon.ico"/>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.css"/>
<link rel="stylesheet" href="{{ asset_url('/assets/trayek-wisata/style.css') }}"/>
</head>
<body>
<script src="{{ asset_url('/assets/trayek-wisata/nav-progress.js') }}"></script>
<a href="#main" class="skip-link">Lompat ke konten utama</a>

@include('partials.trayek-wisata.nav', ['twNavSolid' => false])

@include('partials.trayek-wisata.page-header', [
    'twPageEyebrow' => 'Peta Rute',
    'twPageTitle' => 'Jelajahi rutenya<br/>sebelum berangkat.',
    'twPageDesc' => 'Pilih paket trayek, lalu ketuk tiap titik di peta untuk melihat foto, estimasi tiba, jam keberangkatan, titik pemberhentian, dan jadwal pulang.',
])

<main id="main">
<!-- ═══ PETA RUTE ═══ -->
<section class="explorer" id="explorer">
  <div class="explorer-head wrap reveal" style="padding-top:60px">
    <div class="explorer-chips" id="explorerChips"><!-- diisi JS --></div>
  </div>

  <div class="explorer-stage">
    <div id="twMap"></div>
    <div class="explorer-panel" id="explorerPanel">
      <div class="explorer-panel-media" id="panelMedia"></div>
      <button class="explorer-panel-close" onclick="closePanel()" aria-label="Tutup detail lokasi">✕</button>
      <div class="explorer-panel-body">
        <div class="explorer-panel-name" id="panelName"></div>
        <div class="explorer-panel-desc" id="panelDesc"></div>
        <div class="explorer-panel-stats">
          <div class="explorer-stat"><b id="panelEta">-</b>Perkiraan Tiba</div>
          <div class="explorer-stat"><b id="panelDur">-</b>Dari Titik Sebelumnya</div>
          <div class="explorer-stat"><b id="panelVisit">-</b>Kunjungan</div>
        </div>
        <div class="explorer-panel-actions">
          <a class="btn btn-solid btn-sm" id="panelGmaps" target="_blank" rel="noopener">Buka di Google Maps</a>
        </div>
      </div>
    </div>
    <div class="explorer-hint"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M7 3.5v10.2l3.1-2.5 2.1 5.3 2-.8-2.1-5.2 4-.1L7 3.5Z"/><path d="M5 20h14"/></svg> Ketuk titik di peta untuk melihat detail</div>
  </div>
</section>

<!-- ═══ ALUR PERJALANAN LENGKAP ═══ -->
<section class="story" id="story">
  <div class="wrap">
    <div class="story-head reveal">
      <div class="eyebrow"><svg class="eyebrow-svg" width="28" height="12" viewBox="0 0 28 12" fill="none" aria-hidden="true"><path d="M1 6h19" stroke="currentColor" stroke-linecap="round"/><circle cx="24" cy="6" r="2" fill="currentColor"/></svg>Alur Perjalanan</div>
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

<section style="background:var(--paper);padding:0 0 100px;text-align:center">
  <a href="/trayek-wisata/jadwal" class="btn btn-solid">Sudah siap? Pesan Kursi →</a>
</section>
</main>

@include('partials.trayek-wisata.footer')

<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.js"></script>
<script src="https://unpkg.com/lenis@1.1.13/dist/lenis.min.js"></script>
<script src="{{ asset_url('/assets/trayek-wisata/tw-core.js') }}"></script>
<script>
  twLoadAuth();
  twBootstrap({ explorer: true, story: true });
</script>
</body>
</html>
