{{--
    Port dari pages/trayekwisata/partials/footer.php.
--}}
@include('partials.site-footer', [
    'desc' => 'Program Dinas Perhubungan Kabupaten Tulungagung — mengantar warga menikmati Pantai Selatan lewat Jalur Lintas Selatan, gratis setiap akhir pekan.',
])

<div class="tw-toast" id="twToastEl"></div>

<button class="floating-cta" id="floatingCta" onclick="location.href='/trayek-wisata/jadwal'" aria-label="Pesan kursi trayek wisata">
  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="17" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="16" y1="2" x2="16" y2="6"/></svg>
  Pesan Kursi
</button>
