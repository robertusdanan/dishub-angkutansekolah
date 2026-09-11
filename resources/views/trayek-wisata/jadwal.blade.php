<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"/>
<meta name="csrf-token" content="{{ csrf_token() }}"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover"/>
<title>Jadwal & Pesan Kursi — Trayek Wisata Gratis | Dishub Kabupaten Tulungagung</title>
<meta name="description" content="Lihat jadwal Trayek Wisata Gratis 4 minggu ke depan dan pesan kursi langsung — gratis, setiap Sabtu & Minggu, Dishub Kabupaten Tulungagung."/>
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
    'twPageEyebrow' => 'Jadwal',
    'twPageTitle' => '4 minggu ke depan,<br/>siap dipesan.',
    'twPageDesc' => 'Trayek ditampilkan otomatis untuk 4 minggu ke depan mengikuti kalender Dishub. Kursi terbatas — status hijau berarti masih longgar.',
])

<main id="main">
<section class="jadwal" id="jadwal" style="padding-top:60px">
  <div class="wrap" style="position:relative">
    <div class="jadwal-tabs">
      <div class="jadwal-tab active" data-hari="" onclick="switchJadwalTab(this,'')">Semua</div>
      <div class="jadwal-tab" data-hari="SABTU" onclick="switchJadwalTab(this,'SABTU')">Sabtu</div>
      <div class="jadwal-tab" data-hari="MINGGU" onclick="switchJadwalTab(this,'MINGGU')">Minggu</div>
    </div>
    <div class="jadwal-scroll full-grid" id="jadwalScroll">
      <div class="skel" style="flex:0 0 280px;height:230px;background:rgba(255,255,255,.08)"></div>
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
          <div class="kuota-list-item"><span>Total Kuota</span><b id="kTotal">—</b></div>
          <div class="kuota-list-item"><span>Kursi Terisi</span><b id="kTerisi">—</b></div>
          <div class="kuota-list-item"><span>Sisa Kursi</span><b id="kSisa">—</b></div>
          <div class="kuota-list-item"><span>Jumlah Trayek Minggu Ini</span><b id="kTrayek">—</b></div>
        </div>
      </div>
    </div>
  </div>
</section>
</main>

@include('partials.trayek-wisata.footer')

<script src="https://unpkg.com/lenis@1.1.13/dist/lenis.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script src="{{ asset_url('/assets/trayek-wisata/ticket-modal.js') }}"></script>
<script src="{{ asset_url('/assets/trayek-wisata/tw-core.js') }}"></script>
<script>
  twLoadAuth();
  twBootstrap({ jadwal: true, kuota: true });
</script>
</body>
</html>
