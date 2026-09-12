
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>ABSENSI ANGKUTAN SEKOLAH</title>
  <meta name="description" content="Absensi siswa yang menggunakan angkutan sekolah gratis Dinas Perhubungan Kabupaten Tulungagung." />

  <link rel="dns-prefetch" href="//www.highperformanceformat.com">
  <link rel="preconnect" href="//www.highperformanceformat.com" crossorigin>
  <link rel="dns-prefetch" href="//pl28795675.effectivegatecpm.com">
  <link rel="preconnect" href="//pl28795675.effectivegatecpm.com" crossorigin>
  <link rel="icon" href="/favicon.ico" />
  <link rel="stylesheet" href="{{ asset_url('/assets/absen-qrcode/style.css') }}" />
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
  <script src="https://accounts.google.com/gsi/client" async defer></script>

  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --navy:   #1a1854;
      --navy2:  #2e2a8a;
      --purple: #3b1f6e;
      --gold:   #d4af37;
      --gold2:  #f0d060;
      --gold3:  #b8941f;
    }

    /* Override style.css untuk halaman ini */
    body {
      font-family: 'Montserrat', sans-serif !important;
      background-image: url('/assets/sekolah.jpeg') !important;
      background-size: cover !important;
      background-position: center !important;
      background-repeat: no-repeat !important;
      background-attachment: fixed !important;
      background-color: rgba(20, 16, 60, 0.82) !important;
      background-blend-mode: multiply !important;
      min-height: 100vh !important;
      min-height: 100dvh !important;
      margin: 0 !important;
      padding: 20px 16px !important;
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
    }

    /* Wrapper: susun .container dan #ad-container secara vertikal, center */
    .page-wrapper {
      display: flex;
      flex-direction: column;
      align-items: center;
      width: 100%;
      max-width: 480px;
      margin: auto;
    }

    /* Dekorasi lingkaran latar */
    body::before {
      content: '';
      position: fixed;
      width: 600px; height: 600px;
      border-radius: 50%;
      border: 70px solid rgba(212,175,55,.05);
      top: -200px; right: -200px;
      pointer-events: none;
      z-index: 0;
    }
    body::after {
      content: '';
      position: fixed;
      width: 400px; height: 400px;
      border-radius: 50%;
      border: 50px solid rgba(212,175,55,.05);
      bottom: -120px; left: -120px;
      pointer-events: none;
      z-index: 0;
    }

    /* ── CONTAINER ── */
    .container {
      max-width: 480px !important;
      width: 100% !important;
      margin: 0 !important;
      background: rgba(255,255,255,0.06) !important;
      border: 1px solid rgba(212,175,55,0.2) !important;
      backdrop-filter: blur(4px) !important;
      -webkit-backdrop-filter: blur(4px) !important;
      border-radius: 24px !important;
      padding: 40px 32px 36px !important;
      box-shadow: 0 20px 60px rgba(0,0,0,0.4) !important;
      position: relative;
      z-index: 1;
      animation: fadeUp .7s cubic-bezier(.22,.61,.36,1) both;
    }

    /* Garis emas atas */
    .container::before {
      content: '';
      position: absolute;
      top: 0; left: 50%;
      transform: translateX(-50%);
      width: 80px; height: 3px;
      background: linear-gradient(90deg, transparent, var(--gold), transparent);
      border-radius: 2px;
    }

    /* ── HEADER ── */
    .page-header {
      text-align: center;
      margin-bottom: 28px;
    }

    .logo-ring {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 88px; height: 88px;
      border-radius: 50%;
      border: 2px solid rgba(212,175,55,.4);
      background: rgba(255,255,255,.05);
      margin-bottom: 16px;
      box-shadow: 0 0 30px rgba(212,175,55,.15);
    }
    .logo-ring img {
      width: 80px; height: 80px;
      border-radius: 50%;
      object-fit: cover;
    }

    .page-title {
      font-family: 'Cormorant Garamond', serif !important;
      font-size: 26px !important;
      font-weight: 700 !important;
      color: #fff !important;
      letter-spacing: .5px;
      line-height: 1.3;
      text-shadow: none !important;
      margin-bottom: 0 !important;
    }

    .page-subtitle {
      font-size: 11px;
      font-weight: 500;
      letter-spacing: 2px;
      color: var(--gold);
      text-transform: uppercase;
      margin-top: 8px;
    }

    .divider {
      width: 50px; height: 2px;
      background: linear-gradient(90deg, transparent, var(--gold), transparent);
      margin: 12px auto 0;
    }

    /* ── GOOGLE SIGN IN ── */
    .g_id_signin {
      display: flex !important;
      justify-content: center !important;
      margin: 20px 0 8px !important;
    }

    /* ── PROFILE BOX ── */
    .profile-box {
      background: rgba(255,255,255,.08) !important;
      border: 1px solid rgba(212,175,55,.2) !important;
      border-radius: 12px !important;
      padding: 12px !important;
    }
    .profile-box strong { color: #fff !important; }

    /* Email lebih kontras */
    .profile-box div span,
    .profile-box div div {
      color: rgba(255,255,255,0.65) !important;
    }

    /* Sembunyikan tombol Google saat sudah login */
    body.is-logged-in .g_id_signin,
    body.is-logged-in #g_id_onload { display: none !important; }

    .profile-box button {
      background: rgba(220,53,69,.7) !important;
      border-radius: 8px !important;
      font-family: 'Montserrat', sans-serif !important;
      font-size: 12px !important;
      padding: 6px 12px !important;
      width: auto !important;
      margin-top: 0 !important;
    }
    .profile-box button:hover {
      background: rgba(220,53,69,1) !important;
      transform: none !important;
    }

    /* ── FORM ELEMENTS ── */
    form {
      display: flex !important;
      flex-direction: column !important;
      gap: 4px !important;
    }

    form label {
      font-family: 'Montserrat', sans-serif !important;
      font-size: 11px !important;
      font-weight: 600 !important;
      letter-spacing: 1px !important;
      text-transform: uppercase !important;
      color: var(--gold) !important;
      margin-bottom: 5px !important;
    }

    form input[type="text"],
    form select {
      background: rgba(255,255,255,.1) !important;
      border: 1px solid rgba(212,175,55,.25) !important;
      border-radius: 10px !important;
      color: #fff !important;
      font-family: 'Montserrat', sans-serif !important;
      font-size: 14px !important;
      padding: 11px 14px !important;
      margin-bottom: 14px !important;
      transition: border-color .3s, background .3s !important;
    }
    form input[type="text"]:focus,
    form select:focus {
      border-color: rgba(212,175,55,.6) !important;
      background: rgba(255,255,255,.15) !important;
      outline: none !important;
      box-shadow: 0 0 0 3px rgba(212,175,55,.1) !important;
    }
    form input[type="text"]::placeholder {
      color: rgba(255,255,255,.35) !important;
    }
    form select option {
      background: var(--navy);
      color: #fff;
    }

    /* Submit button */
    #submitButton {
      background: linear-gradient(135deg, var(--gold), var(--gold3)) !important;
      color: var(--navy) !important;
      font-family: 'Montserrat', sans-serif !important;
      font-weight: 700 !important;
      font-size: 14px !important;
      letter-spacing: .5px !important;
      border-radius: 50px !important;
      border: none !important;
      padding: 13px !important;
      box-shadow: 0 4px 20px rgba(212,175,55,.35) !important;
      transition: all .3s !important;
      margin-top: 8px !important;
      cursor: pointer !important;
    }
    #submitButton:hover {
      background: linear-gradient(135deg, var(--gold2), var(--gold)) !important;
      box-shadow: 0 6px 28px rgba(212,175,55,.55) !important;
      transform: translateY(-2px) !important;
    }
    #submitButton:disabled {
      opacity: .6 !important;
      transform: none !important;
      cursor: not-allowed !important;
    }

    /* Response box */
    #response {
      font-family: 'Montserrat', sans-serif;
      font-size: 13px;
      border-radius: 10px;
      margin-top: 4px;
    }

    /* Info angkutan */
    #info-angkutan {
      font-size: 13px !important;
      color: rgba(255,255,255,.65) !important;
      text-align: center !important;
      margin-top: 0 !important;
    }

    /* Loading / spinner text */
    #form-container p { color: rgba(255,255,255,.65) !important; }

    /* Autocomplete dropdown */
    #domisiliDropdown,
    #sekolahDropdown {
      background: #1e1a60 !important;
      border: 1px solid rgba(212,175,55,.3) !important;
      border-radius: 8px !important;
      box-shadow: 0 8px 24px rgba(0,0,0,.4) !important;
    }
    .autocomplete-item { color: #fff !important; font-size: 13px !important; }
    .autocomplete-item:hover { background: rgba(212,175,55,.15) !important; }

    /* ── AD CONTAINER (di luar .container, tepat di bawah form) ── */
    #ad-container {
      width: 100%;
      max-width: 480px;
      margin-top: 12px;
      padding: 14px 0 0;
      display: flex;
      flex-direction: column;
      align-items: center;
      min-height: 0;
      z-index: 1;
    }

    #ad-container:not(:empty)::before {
      content: '';
      font-family: 'Montserrat', sans-serif;
      font-size: 9px;
      font-weight: 600;
      letter-spacing: 2px;
      text-transform: uppercase;
      color: rgba(255,255,255,0.2);
      margin-bottom: 8px;
    }
    /* ── DEV CREDIT ── */
    .dev-credit {
      position: fixed;
      bottom: 10px; right: 14px;
      font-family: 'Montserrat', sans-serif;
      font-size: 11px;
      color: rgba(255,255,255,.25);
      background: rgba(255,255,255,.06);
      backdrop-filter: blur(6px);
      border: 1px solid rgba(255,255,255,.08);
      border-radius: 20px;
      padding: 4px 12px;
      opacity: 0;
      animation: fadeUp 1s .5s ease forwards;
      transition: all .4s ease;
      z-index: 100;
      user-select: none;
    }
    .dev-credit:hover {
      color: rgba(255,255,255,.7);
      background: rgba(255,255,255,.12);
      border-color: rgba(212,175,55,.3);
    }
    .dev-credit a {
      color: var(--gold);
      text-decoration: none;
      font-weight: 600;
      transition: color .3s;
    }
    .dev-credit a:hover { color: var(--gold2); }

    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(16px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 480px) {
      .container { padding: 32px 20px 28px !important; }
      .page-title { font-size: 22px !important; }
    }
    /* ── RUTE BUTTON ── */
.rute-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  margin-top: 14px;
  padding: 7px 16px;
  background: rgba(212,175,55,0.08);
  border: 1px solid rgba(212,175,55,0.35);
  border-radius: 50px;
  color: var(--gold);
  font-family: 'Montserrat', sans-serif;
  font-size: 11px;
  font-weight: 600;
  letter-spacing: 0.5px;
  text-decoration: none;
  text-transform: uppercase;
  transition: all 0.3s ease;
  backdrop-filter: blur(4px);
}
.rute-btn:hover {
  background: rgba(212,175,55,0.18);
  border-color: var(--gold);
  color: var(--gold2);
  box-shadow: 0 0 18px rgba(212,175,55,0.25);
  transform: translateY(-1px);
}
.rute-btn svg {
  flex-shrink: 0;
  opacity: 0.85;
}
  </style>

</head>

<body>

  <!-- Wrapper agar container form + ad-container sejajar vertikal dan center -->
  <div class="page-wrapper">

    <div class="container">

<!-- Header -->
<div class="page-header">
  <div class="logo-ring">
    <img src="/assets/dishub.png" alt="Logo Dishub Tulungagung">
  </div>
  <h1 class="page-title">Absensi<br>Angkutan Sekolah Gratis</h1>
  <p class="page-subtitle">Dinas Perhubungan Kabupaten Tulungagung</p>
  <div class="divider"></div>

  <a href="/rute-sekolah"
     target="_blank"
     rel="noopener noreferrer"
     class="rute-btn">
<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
     fill="none" stroke="currentColor" stroke-width="2"
     stroke-linecap="round" stroke-linejoin="round">
  <path d="M8 6v6"/>
  <path d="M16 6v6"/>
  <path d="M2 12h19.6"/>
  <path d="M18 18h3s.5-1.7.8-2.8c.1-.4.2-.8.2-1.2 0-.4-.1-.8-.2-1.2l-1.4-5c-.2-.8-1-1.8-2-1.8H4.4c-1 0-1.8 1-2 1.8l-1.4 5c-.1.4-.2.8-.2 1.2 0 .4.1.8.2 1.2C1.5 16.3 2 18 2 18h3"/>
  <circle cx="7" cy="18" r="2"/>
  <path d="M9 18h6"/>
  <circle cx="17" cy="18" r="2"/>
</svg>
    Lihat Rute Bus Sekolah
    <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24"
         fill="none" stroke="currentColor" stroke-width="2.5"
         stroke-linecap="round" stroke-linejoin="round">
      <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
      <polyline points="15 3 21 3 21 9"/>
      <line x1="10" y1="14" x2="21" y2="3"/>
    </svg>
  </a>
</div>   

    <!-- Google Sign-In onload -->
    <div id="g_id_onload"
         data-client_id="{{ config('services.google.client_id') }}"
         data-callback="handleCredentialResponse"
         data-auto_prompt="false">
    </div>

    <!-- Tombol Google Sign-In -->
    <div class="g_id_signin"
         data-type="standard"
         data-size="large"
         data-theme="outline"
         data-text="sign_in_with"
         data-shape="rectangular"
         data-logo_alignment="left">
    </div>

    <!-- Kontainer form -->
    <div id="login-container"></div>
    <div id="form-container"></div>
    <div id="response"></div>

  </div>
  <!-- /container -->
 <div id="ad-container"></div>

  </div>

</script>
<div class="dev-credit" id="devCredit"></div>

@include('partials.dev-credit-script', ['containerId' => 'devCredit'])

  <script>
    // Sembunyikan tombol Google secepat mungkin jika sudah login
    if (localStorage.getItem('nama') && localStorage.getItem('email')) {
      document.body.classList.add('is-logged-in');
    }
  </script>
  <script>
    window.SB_URL  = {!! json_encode(config('services.supabase.url')) !!};
    window.SB_ANON = {!! json_encode(config('services.supabase.anon_key')) !!};
  </script>
  <script src="{{ asset_url('/assets/absen-qrcode/script.js') }}"></script>
</body>
</html>