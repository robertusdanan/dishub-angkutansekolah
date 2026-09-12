<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - List Link Absen Angkutan</title>
  <link rel="icon" href="/favicon.ico"/>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
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

    body {
      font-family: 'Montserrat', sans-serif;
      background: var(--navy);
      background-image:
        radial-gradient(ellipse at top left,  var(--navy2) 0%, transparent 55%),
        radial-gradient(ellipse at bottom right, var(--purple) 0%, transparent 55%);
      min-height: 100vh;
      min-height: 100dvh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 24px 16px;
      position: relative;
      overflow: hidden;
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
    }
    body::after {
      content: '';
      position: fixed;
      width: 400px; height: 400px;
      border-radius: 50%;
      border: 50px solid rgba(212,175,55,.05);
      bottom: -120px; left: -120px;
      pointer-events: none;
    }

    /* Partikel bintang dekoratif */
    .star {
      position: fixed;
      width: 2px; height: 2px;
      border-radius: 50%;
      background: rgba(212,175,55,.4);
      pointer-events: none;
    }

    /* ── WRAPPER ── */
    .login-outer {
      width: 100%;
      max-width: 420px;
      animation: fadeUp .7s cubic-bezier(.22,.61,.36,1) both;
    }

    /* ── LOGO / BRAND ── */
    .brand {
      text-align: center;
      margin-bottom: 32px;
    }

    .logo-ring {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 80px; height: 80px;
      border-radius: 50%;
      border: 2px solid rgba(212,175,55,.35);
      background: rgba(255,255,255,.05);
      margin-bottom: 16px;
      box-shadow: 0 0 30px rgba(212,175,55,.12), 0 0 60px rgba(212,175,55,.05);
      transition: box-shadow .3s;
    }
    .logo-ring:hover {
      box-shadow: 0 0 40px rgba(212,175,55,.25), 0 0 80px rgba(212,175,55,.08);
    }
    .logo-ring img {
      width: 72px; height: 72px;
      border-radius: 50%;
      object-fit: cover;
    }

    .brand h1 {
      font-family: 'Cormorant Garamond', serif;
      font-size: 26px;
      font-weight: 700;
      color: #fff;
      letter-spacing: .3px;
      line-height: 1.25;
    }

    .brand p {
      margin-top: 8px;
      font-size: 12px;
      color: rgba(255,255,255,.4);
      letter-spacing: .3px;
    }

    .gold-line {
      width: 50px; height: 2px;
      background: linear-gradient(90deg, transparent, var(--gold), transparent);
      margin: 12px auto 0;
    }

    /* ── CARD ── */
    .login-card {
      background: rgba(255,255,255,.05);
      border: 1px solid rgba(212,175,55,.18);
      border-radius: 24px;
      padding: 36px 32px 28px;
      box-shadow: 0 24px 64px rgba(0,0,0,.35), 0 0 0 1px rgba(212,175,55,.06);
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);
      position: relative;
      overflow: hidden;
    }

    /* Garis emas tipis di atas card */
    .login-card::before {
      content: '';
      position: absolute;
      top: 0; left: 0; right: 0;
      height: 2px;
      background: linear-gradient(90deg, transparent, var(--gold), transparent);
    }

    .card-title {
      font-family: 'Cormorant Garamond', serif;
      font-size: 22px;
      font-weight: 700;
      color: #fff;
      margin-bottom: 4px;
    }
    .card-sub {
      font-size: 12px;
      color: rgba(255,255,255,.35);
      margin-bottom: 28px;
      letter-spacing: .3px;
    }

    /* ── ERROR BOX ── */
    .error-box {
      display: flex;
      align-items: center;
      gap: 9px;
      background: rgba(239,68,68,.12);
      border: 1px solid rgba(239,68,68,.3);
      border-radius: 10px;
      color: #f87171;
      font-size: 13px;
      font-weight: 500;
      padding: 11px 14px;
      margin-bottom: 20px;
      animation: shake .4s ease;
    }

    /* ── FIELD ── */
    .field-group {
      display: flex;
      flex-direction: column;
      gap: 6px;
      margin-bottom: 16px;
    }
    .field-group label {
      font-size: 10.5px;
      font-weight: 600;
      letter-spacing: 1.2px;
      text-transform: uppercase;
      color: rgba(212,175,55,.65);
    }

    .input-wrap {
      position: relative;
    }
    /* Hanya icon kiri - bukan tombol toggle */
    .input-wrap > svg {
      position: absolute;
      left: 14px; top: 50%;
      transform: translateY(-50%);
      color: rgba(212,175,55,.45);
      pointer-events: none;
      flex-shrink: 0;
    }
    .input-wrap input {
      width: 100%;
      padding: 12px 44px 12px 42px; /* kanan 44px beri ruang icon mata */
      background: rgba(255,255,255,.06);
      border: 1.5px solid rgba(212,175,55,.2);
      border-radius: 12px;
      color: #fff;
      font-family: 'Montserrat', sans-serif;
      font-size: 14px;
      outline: none;
      transition: border-color .2s, background .2s, box-shadow .2s;
    }
    .input-wrap input::placeholder {
      color: rgba(255,255,255,.22);
    }
    .input-wrap input:focus {
      border-color: rgba(212,175,55,.55);
      background: rgba(255,255,255,.09);
      box-shadow: 0 0 0 3px rgba(212,175,55,.08);
    }

    /* Toggle password visibility */
    .toggle-pw {
      position: absolute;
      right: 10px; top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      cursor: pointer;
      color: rgba(255,255,255,.35);
      padding: 6px;        /* area klik lebih luas */
      line-height: 0;
      transition: color .2s;
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 2;          /* pastikan di atas input */
    }
    .toggle-pw:hover { color: rgba(212,175,55,.75); }
    .toggle-pw:focus { outline: none; }

    /* ── TOMBOL LOGIN ── */
    .btn-login {
      width: 100%;
      padding: 13px;
      background: linear-gradient(135deg, var(--gold), var(--gold3));
      color: var(--navy);
      border: none;
      border-radius: 12px;
      font-family: 'Montserrat', sans-serif;
      font-size: 14px;
      font-weight: 700;
      letter-spacing: .3px;
      cursor: pointer;
      transition: all .25s;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      margin-top: 8px;
      box-shadow: 0 6px 20px rgba(212,175,55,.25);
      position: relative;
      overflow: hidden;
    }
    .btn-login::after {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(135deg, rgba(255,255,255,.15), transparent);
      opacity: 0;
      transition: opacity .2s;
    }
    .btn-login:hover {
      background: linear-gradient(135deg, var(--gold2), var(--gold));
      box-shadow: 0 8px 28px rgba(212,175,55,.4);
      transform: translateY(-1px);
    }
    .btn-login:hover::after { opacity: 1; }
    .btn-login:active { transform: translateY(0); }

    /* Loading state */
    .btn-login.loading { pointer-events: none; opacity: .75; }
    .spinner {
      width: 16px; height: 16px;
      border: 2px solid rgba(26,24,84,.3);
      border-top-color: var(--navy);
      border-radius: 50%;
      animation: spin .65s linear infinite;
      display: none;
    }
    .btn-login.loading .spinner { display: block; }
    .btn-login.loading .btn-text { display: none; }

    /* ── FOOTER CARD ── */
    .card-footer {
      text-align: center;
      margin-top: 22px;
      font-size: 11.5px;
      color: rgba(255,255,255,.2);
    }
    .card-footer a {
      color: rgba(212,175,55,.5);
      text-decoration: none;
      transition: color .2s;
    }
    .card-footer a:hover { color: var(--gold); }

    /* ── CREDIT ── */
    .dev-credit {
      position: fixed;
      bottom: 8px; right: 14px;
      font-size: 11px;
      color: rgba(255,255,255,.2);
      z-index: 999;
    }
    .dev-credit a {
      color: inherit;
      text-decoration: none;
      border-bottom: 1px dotted rgba(212,175,55,.25);
      transition: color .2s, border-color .2s;
    }
    .dev-credit a:hover { color: var(--gold); border-color: var(--gold); }

    /* ── ANIMASI ── */
    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(28px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    @keyframes shake {
      0%, 100% { transform: translateX(0); }
      20%       { transform: translateX(-6px); }
      40%       { transform: translateX(6px); }
      60%       { transform: translateX(-4px); }
      80%       { transform: translateX(4px); }
    }

    @keyframes spin {
      to { transform: rotate(360deg); }
    }

    @media (max-width: 440px) {
      .login-card { padding: 28px 20px 22px; }
    }
  </style>
</head>
<body>

<!-- Bintang dekoratif -->
<div class="star" style="top:15%;left:10%;"></div>
<div class="star" style="top:35%;left:85%;width:3px;height:3px;opacity:.6;"></div>
<div class="star" style="top:65%;left:8%;width:3px;height:3px;"></div>
<div class="star" style="top:80%;left:78%;"></div>
<div class="star" style="top:22%;left:55%;opacity:.5;"></div>
<div class="star" style="top:50%;left:92%;opacity:.4;"></div>

<div class="login-outer">

  <!-- Brand -->
  <div class="brand">
    <div class="logo-ring">
      <img src="/assets/dishub.png" alt="Logo Dishub Tulungagung">
    </div>
    <h1>Absen Angkutan<br>Sekolah Gratis</h1>
    <p>Sistem Absensi Dinas Perhubungan Tulungagung</p>
    <div class="gold-line"></div>
  </div>

  <!-- Card Login -->
  <div class="login-card">
    <div class="card-title">Masuk</div>
    <div class="card-sub">Diperlukan login untuk mengakses daftar link absensi</div>

    @if ($error)
    <div class="error-box">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
        <circle cx="12" cy="12" r="10"/>
        <line x1="12" y1="8" x2="12" y2="12"/>
        <line x1="12" y1="16" x2="12.01" y2="16"/>
      </svg>
      {{ $error }}
    </div>
    @endif
    @error('throttle')
    <div class="error-box">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
        <circle cx="12" cy="12" r="10"/>
        <line x1="12" y1="8" x2="12" y2="12"/>
        <line x1="12" y1="16" x2="12.01" y2="16"/>
      </svg>
      {{ $message }}
    </div>
    @enderror

    <form method="POST" action="/absenrfid/login" autocomplete="off" id="loginForm">
      @csrf
      <!-- Username -->
      <div class="field-group">
        <label for="username">Username</label>
        <div class="input-wrap">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
            <circle cx="12" cy="7" r="4"/>
          </svg>
          <input
            type="text"
            id="username"
            name="username"
            placeholder="Masukkan username"
            autocomplete="username"
            required
            value="{{ $oldUsername }}"
          >
        </div>
      </div>

      <!-- Password -->
      <div class="field-group">
        <label for="password">Password</label>
        <div class="input-wrap">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
          </svg>
          <input
            type="password"
            id="password"
            name="password"
            placeholder="Masukkan password"
            autocomplete="current-password"
            required
          >
          <button type="button" class="toggle-pw" onclick="togglePassword()" title="Tampilkan password">
            <svg id="eyeIcon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
              <circle cx="12" cy="12" r="3"/>
            </svg>
          </button>
        </div>
      </div>

      <button type="submit" class="btn-login" id="loginBtn">
        <div class="spinner"></div>
        <span class="btn-text" style="display:flex;align-items:center;gap:8px;">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
            <polyline points="10 17 15 12 10 7"/>
            <line x1="15" y1="12" x2="3" y2="12"/>
          </svg>
          Masuk
        </span>
      </button>
    </form>

    <div class="card-footer">
      Sistem Absensi &mdash; Dinas Perhubungan Kab. Tulungagung
    </div>
  </div>

</div>

<!-- Credit -->
<div class="dev-credit">
  @include('partials.dev-credit-html')
</div>

<script>
  // Toggle visibility password
  let pwVisible = false;
  function togglePassword() {
    pwVisible = !pwVisible;
    const inp = document.getElementById('password');
    const ico = document.getElementById('eyeIcon');
    inp.type = pwVisible ? 'text' : 'password';
    ico.innerHTML = pwVisible
      ? `<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/>`
      : `<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>`;
  }

  // Loading state saat submit
  document.getElementById('loginForm').addEventListener('submit', function() {
    document.getElementById('loginBtn').classList.add('loading');
  });

  // Auto-focus username
  window.addEventListener('DOMContentLoaded', function() {
    const u = document.getElementById('username');
    if (!u.value) u.focus();
    else document.getElementById('password').focus();
  });
</script>

</body>
</html>