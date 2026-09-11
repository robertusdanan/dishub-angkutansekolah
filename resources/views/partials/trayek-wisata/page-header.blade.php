{{--
    Port dari pages/trayekwisata/partials/page-header.php.
    Props: $twPageEyebrow, $twPageTitle (HTML, sudah aman - literal dari kode
    kita sendiri, bukan input user), $twPageDesc (opsional)
--}}
<header class="page-header">
  <div class="wrap">
    <a href="/trayek-wisata" class="page-header-back">← Kembali ke Beranda</a>
    <div class="eyebrow eyebrow-light">{{ $twPageEyebrow }}</div>
    <h1 class="page-header-title">{!! $twPageTitle !!}</h1>
    @if (!empty($twPageDesc))
    <p class="page-header-desc">{{ $twPageDesc }}</p>
    @endif
  </div>
</header>
