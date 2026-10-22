{{--
    Port dari pages/trayekwisata/partials/page-header.php.
    Props: $twPageEyebrow, $twPageTitle (HTML, sudah aman - literal dari kode
    kita sendiri, bukan input user), $twPageDesc (opsional)
--}}
<header class="page-header">
  <div class="wrap page-header-grid">
    <div class="page-header-copy">
      <a href="/trayek-wisata" class="page-header-back">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>
        Kembali ke Beranda
      </a>
      <div class="eyebrow eyebrow-light"><svg class="eyebrow-svg" width="24" height="8" viewBox="0 0 24 8" fill="none" aria-hidden="true"><path d="M1 4h22" stroke="currentColor" stroke-linecap="round"/></svg>{{ $twPageEyebrow }}</div>
      <h1 class="page-header-title">{!! $twPageTitle !!}</h1>
      @if (!empty($twPageDesc))
      <p class="page-header-desc">{{ $twPageDesc }}</p>
      @endif
    </div>
    <div class="page-header-art" aria-hidden="true">
      <svg viewBox="0 0 520 300" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M-20 246C65 205 93 267 168 224S283 151 353 185s105 9 190-59" stroke="rgba(255,255,255,.30)" stroke-width="1.5" stroke-dasharray="5 8"/>
        <path d="M16 265c80-38 114 13 183-27 72-42 97-82 176-47 54 24 84 7 129-35" stroke="rgba(255,255,255,.14)" stroke-width="32" stroke-linecap="round"/>
        <circle cx="74" cy="225" r="7" fill="#F3B27D"/><circle cx="74" cy="225" r="13" stroke="rgba(243,178,125,.38)"/>
        <circle cx="284" cy="178" r="7" fill="#A9E0D0"/><circle cx="284" cy="178" r="13" stroke="rgba(169,224,208,.38)"/>
        <circle cx="436" cy="126" r="7" fill="#fff"/><circle cx="436" cy="126" r="13" stroke="rgba(255,255,255,.38)"/>
      </svg>
    </div>
  </div>
</header>
