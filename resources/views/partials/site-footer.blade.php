{{--
    Port dari core/site_footer.php (render_site_footer_assets() + render_site_footer())
    dan core/footer.php (kredit developer - dipertahankan sesuai instruksi).
    Props: $orgName (string, opsional), $desc (string, opsional)
--}}
@php
    $credit = app(\App\Services\SiteCredit\SiteCreditService::class)->get();
    $footerOrgName = $orgName ?? ($credit['org_name'] ?: 'Dinas Perhubungan Kabupaten Tulungagung');
    $footerDesc = $desc ?? 'Transformasi digital layanan transportasi publik - terintegrasi, inovatif, dan berorientasi pada peningkatan kualitas pelayanan bagi warga Kabupaten Tulungagung.';
@endphp

@once
<style>
  .gh-footer {
    background: #0a1f44;
    padding: 56px 2rem 20px;
    font-family: 'Plus Jakarta Sans', 'DM Sans', sans-serif;
  }
  .gh-footer-inner { max-width: 1100px; margin: 0 auto; }
  .gh-footer-grid {
    display: grid;
    grid-template-columns: 1.4fr 1fr 1fr;
    gap: 40px;
    padding-bottom: 32px;
    border-bottom: 1px solid rgba(255,255,255,0.08);
  }
  .gh-footer-brand {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 15px;
    font-weight: 700;
    color: #fff;
    margin-bottom: 12px;
  }
  .gh-footer-brand img { height: 30px; width: auto; }
  .gh-footer-desc {
    font-size: 13px;
    line-height: 1.7;
    color: rgba(255,255,255,0.5);
    margin: 0;
    max-width: 360px;
  }
  .gh-footer-label {
    font-size: 11.5px;
    font-weight: 700;
    letter-spacing: .04em;
    text-transform: uppercase;
    color: rgba(255,255,255,0.4);
    margin-bottom: 14px;
  }
  .gh-footer-link {
    display: block;
    font-size: 13.5px;
    color: rgba(255,255,255,0.72);
    text-decoration: none;
    margin-bottom: 10px;
    transition: color 0.2s ease;
  }
  .gh-footer-link:hover { color: #38bdf8; }
  .gh-footer-bottom {
    padding-top: 18px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
  }
  .gh-footer-copy {
    font-size: 12px;
    color: rgba(255,255,255,0.32);
  }
  .gh-footer-credit {
    font-size: 11.5px;
    color: rgba(255,255,255,0.28);
    transition: color 0.2s;
  }
  .gh-footer-credit a {
    color: inherit;
    text-decoration: none;
    border-bottom: 1px dotted rgba(255,255,255,0.2);
  }
  .gh-footer-credit a:hover { color: #38bdf8; border-bottom-color: #38bdf8; }
  @media (max-width: 768px) {
    .gh-footer { padding: 44px 1.25rem 20px; }
    .gh-footer-grid { grid-template-columns: 1fr; gap: 28px; }
  }
</style>
@endonce

<footer class="gh-footer">
  <div class="gh-footer-inner">
    <div class="gh-footer-grid">
      <div>
        <div class="gh-footer-brand">
          <img src="/assets/dishub.png" alt="Logo Dishub"/>
          {{ $footerOrgName }}
        </div>
        <p class="gh-footer-desc">{{ $footerDesc }}</p>
      </div>
      <div>
        <div class="gh-footer-label">Navigasi</div>
        <a class="gh-footer-link" href="/">Beranda</a>
        <a class="gh-footer-link" href="/rute-sekolah">Rute Angkutan Sekolah</a>
        <a class="gh-footer-link" href="/asdp/">Peta Interaktif ASDP</a>
        <a class="gh-footer-link" href="/trayek-wisata">Trayek Wisata Gratis</a>
        <a class="gh-footer-link" href="/balikgratis">Balik Gratis</a>
      </div>
      <div>
        <div class="gh-footer-label">Kontak</div>
        <a class="gh-footer-link" href="tel:+62355321234">(0355) 321-234</a>
        <a class="gh-footer-link" href="mailto:dishub@tulungagung.go.id">dishub@tulungagung.go.id</a>
        <span class="gh-footer-link" style="margin-bottom:0;">Jl. Panglima Sudirman, Tulungagung</span>
      </div>
    </div>
    <div class="gh-footer-bottom">
      <span class="gh-footer-copy">© {{ date('Y') }} {{ $footerOrgName }}</span>
      <span class="gh-footer-credit" id="ghFooterCredit"></span>
    </div>
  </div>
</footer>

@include('partials.dev-credit-script', ['containerId' => 'ghFooterCredit'])
