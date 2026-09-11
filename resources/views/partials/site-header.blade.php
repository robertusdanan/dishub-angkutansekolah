{{--
    Port dari core/site_header.php (render_site_header_assets() + render_site_header()).
    Props: $links (array, opsional), $solid (bool, default false), $cta (array|null), $loginNext (string|null)
--}}
@php
    $links = $links ?? [];
    $solid = $solid ?? false;
    $cta = $cta ?? null;
    $loginNext = $loginNext ?? null;
@endphp

@once
<style>
  .gh-header {
    position: fixed;
    top: 0; left: 0; right: 0;
    z-index: 1000;
    padding: 0 2rem;
    height: 68px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    background: rgba(10, 31, 68, 0.55);
    backdrop-filter: blur(18px) saturate(160%);
    -webkit-backdrop-filter: blur(18px) saturate(160%);
    border-bottom: 1px solid rgba(255,255,255,0.08);
    transition: background 0.4s ease, box-shadow 0.4s ease;
    font-family: 'Plus Jakarta Sans', 'DM Sans', sans-serif;
  }
  .gh-header.scrolled,
  .gh-header.is-solid {
    background: rgba(10, 31, 68, 0.97);
    box-shadow: 0 4px 30px rgba(0,0,0,0.25);
  }
  .gh-brand {
    display: flex;
    align-items: center;
    gap: 12px;
    text-decoration: none;
    flex-shrink: 0;
  }
  .gh-brand img {
    height: 36px;
    width: auto;
    filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));
  }
  .gh-brand-text h1 {
    font-size: 15px;
    font-weight: 700;
    color: #fff;
    margin: 0;
    line-height: 1.2;
    letter-spacing: -0.2px;
  }
  .gh-brand-text p {
    font-size: 11px;
    color: rgba(255,255,255,0.55);
    margin: 0;
  }
  .gh-links {
    display: flex;
    align-items: center;
    gap: 26px;
    flex: 1;
    justify-content: center;
  }
  .gh-link {
    font-size: 13.5px;
    font-weight: 600;
    color: rgba(255,255,255,0.78);
    text-decoration: none;
    transition: color 0.2s ease;
    white-space: nowrap;
  }
  .gh-link:hover,
  .gh-link.is-active { color: #fff; }
  .gh-cta {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-shrink: 0;
  }
  .gh-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 18px;
    border-radius: 100px;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    white-space: nowrap;
    transition: all 0.25s ease;
  }
  .gh-btn-solid {
    background: #f59e0b;
    color: #0a1f44;
    border: none;
    box-shadow: 0 3px 12px rgba(245,158,11,0.35);
  }
  .gh-btn-solid:hover {
    background: #f6a800;
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(245,158,11,0.45);
  }
  .nav-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 16px;
    border-radius: 100px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.25s ease;
    border: 1px solid rgba(255,255,255,0.2);
    color: rgba(255,255,255,0.85);
    background: transparent;
  }
  .nav-link:hover {
    background: rgba(255,255,255,0.12);
    color: #fff;
    border-color: rgba(255,255,255,0.35);
  }
  .nav-login {
    background: #f59e0b;
    color: #0a1f44;
    border: none;
    box-shadow: 0 3px 12px rgba(245,158,11,0.35);
  }
  .nav-login:hover {
    background: #f6a800;
    color: #0a1f44;
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(245,158,11,0.45);
  }
  .nav-account {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 5px 14px 5px 5px;
    border-radius: 100px;
    font-size: 12.5px;
    font-weight: 600;
    text-decoration: none;
    color: rgba(255,255,255,0.92);
    background: rgba(255,255,255,0.08);
    border: 1px solid rgba(255,255,255,0.22);
    transition: all 0.25s ease;
    cursor: pointer;
    font-family: inherit;
  }
  .nav-account:hover {
    background: rgba(255,255,255,0.16);
    border-color: rgba(255,255,255,0.4);
    transform: translateY(-1px);
  }
  .nav-account-avatar {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 23px;
    height: 23px;
    border-radius: 50%;
    background: #f59e0b;
    color: #0a1f44;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    flex-shrink: 0;
  }
  .nav-account.is-guest .nav-account-avatar {
    background: rgba(255,255,255,0.2);
    color: #fff;
  }
  .nav-account-name {
    max-width: 90px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }
  @media (max-width: 900px) {
    .gh-links { display: none; }
  }
  @media (max-width: 768px) {
    .gh-header { padding: 0 1rem; }
  }
  @media (max-width: 480px) {
    .gh-brand-text p { display: none; }
  }
</style>
@endonce

<header class="gh-header{{ $solid ? ' is-solid' : '' }}" id="ghHeader">
  <a class="gh-brand" href="/">
    <img src="/assets/dishub.png" alt="Logo Dishub"/>
    <div class="gh-brand-text">
      <h1>Dinas Perhubungan</h1>
      <p>Kabupaten Tulungagung</p>
    </div>
  </a>
  @if ($links)
  <nav class="gh-links">
    @foreach ($links as $l)
      <a class="gh-link{{ !empty($l['active']) ? ' is-active' : '' }}" href="{{ $l['href'] }}">{{ $l['label'] }}</a>
    @endforeach
  </nav>
  @endif
  <div class="gh-cta">
    @if ($cta)
      <a class="gh-btn gh-btn-solid" href="{{ $cta['href'] }}">{{ $cta['label'] }}</a>
    @endif
    @include('partials.nav-account', ['loginNext' => $loginNext])
  </div>
</header>
@unless ($solid)
<script>
  (function () {
    var h = document.getElementById('ghHeader');
    if (!h) return;
    window.addEventListener('scroll', function () {
      h.classList.toggle('scrolled', window.scrollY > 60);
    }, { passive: true });
  })();
</script>
@endunless
