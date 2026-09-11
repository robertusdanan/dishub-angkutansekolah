<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Login — Admin Absensi</title>
  <link rel="canonical" href="{{ url('/admin/login') }}"/>
  <link rel="icon" href="/favicon.ico"/>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
      --navy:  #0b1120;
      --accent:#3b82f6;
      --acc2:  #60a5fa;
      --bg:    #f1f5f9;
      --border:#e2e8f0;
      --text1: #0f172a;
      --text3: #64748b;
      --text4: #94a3b8;
      --s50:   #f8fafc;
    }
    body {
      font-family: 'DM Sans', sans-serif;
      background: var(--bg);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background-image: radial-gradient(circle at 20% 20%, rgba(59,130,246,.06) 0%, transparent 50%),
                        radial-gradient(circle at 80% 80%, rgba(99,102,241,.06) 0%, transparent 50%);
    }

    .login-wrap {
      width: 400px;
      max-width: calc(100vw - 32px);
    }

    .login-brand {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      margin-bottom: 22px;
    }
    .login-brand img { height: 38px; width: 38px; border-radius: 10px; padding: 3px; }
    .login-brand-name { font-size: 17px; font-weight: 700; color: var(--text1); }
    .login-brand-accent { color: var(--accent); }

    .login-card {
      background: #fff;
      border-radius: 18px;
      border: 1px solid var(--border);
      box-shadow: 0 8px 32px rgba(15,23,42,.08), 0 2px 8px rgba(15,23,42,.04);
      padding: 36px 36px 28px;
    }

    .login-title { font-size: 20px; font-weight: 700; color: var(--text1); margin-bottom: 4px; }
    .login-sub   { font-size: 13px; color: var(--text4); margin-bottom: 26px; }

    .field-group  { display: flex; flex-direction: column; gap: 4px; margin-bottom: 14px; }
    .field-group label { font-size: 11.5px; font-weight: 600; letter-spacing: .5px; text-transform: uppercase; color: var(--text3); }
    .field-group input {
      padding: 11px 14px;
      border: 1.5px solid var(--border);
      border-radius: 10px;
      font-family: 'DM Sans', sans-serif;
      font-size: 14.5px;
      color: var(--text1);
      background: var(--s50);
      outline: none;
      transition: border-color .15s, box-shadow .15s, background .15s;
    }
    .field-group input:focus { border-color: var(--accent); background: #fff; box-shadow: 0 0 0 3px rgba(59,130,246,.12); }

    .error-box {
      background: #fef2f2; border: 1px solid #fecaca; border-radius: 9px;
      color: #b91c1c; font-size: 13px; font-weight: 500;
      padding: 10px 14px; margin-bottom: 16px;
      display: flex; align-items: center; gap: 8px;
    }
    .notice-box {
      background: #e6f4ea; border: 1px solid #bbe5c8; border-radius: 9px;
      color: #1a7f37; font-size: 13px; font-weight: 500;
      padding: 10px 14px; margin-bottom: 16px;
      display: flex; align-items: center; gap: 8px;
    }

    .btn-login {
      width: 100%; padding: 12px;
      background: var(--accent); color: #fff;
      border: none; border-radius: 10px;
      font-family: 'DM Sans', sans-serif;
      font-size: 15px; font-weight: 600;
      cursor: pointer;
      transition: background .15s, box-shadow .15s, transform .1s;
      margin-top: 8px;
      display: flex; align-items: center; justify-content: center; gap: 8px;
    }
    .btn-login:hover   { background: #2563eb; box-shadow: 0 4px 14px rgba(59,130,246,.3); }
    .btn-login:active  { transform: translateY(1px); }

    /* ── Dua pilihan elegan (layar awal) ─────────────────────────────── */
    .choice-btn {
      width: 100%; padding: 14px 16px;
      background: #fff;
      color: var(--text1);
      border: 1.5px solid var(--border);
      border-radius: 12px;
      font-family: 'DM Sans', sans-serif;
      font-size: 14.5px; font-weight: 600;
      cursor: pointer;
      transition: border-color .2s, box-shadow .2s, transform .1s, background .2s;
      display: flex; align-items: center; gap: 12px;
      text-decoration: none;
      margin-bottom: 12px;
    }
    .choice-btn:last-of-type { margin-bottom: 0; }
    .choice-btn-icon {
      width: 36px; height: 36px; border-radius: 9px;
      display: flex; align-items: center; justify-content: center;
      flex-shrink: 0; background: var(--s50);
    }
    .choice-btn-text { display: flex; flex-direction: column; gap: 1px; text-align: left; }
    .choice-btn-title { font-size: 14.5px; font-weight: 700; color: var(--text1); }
    .choice-btn-sub { font-size: 11.5px; font-weight: 500; color: var(--text4); }
    .choice-btn:hover { border-color: #a5b4fc; box-shadow: 0 4px 14px rgba(99,102,241,.12); transform: translateY(-1px); }
    .choice-btn:active { transform: translateY(0); }

    .back-link {
      display: inline-flex; align-items: center; gap: 6px;
      font-size: 12.5px; font-weight: 600; color: var(--text3);
      text-decoration: none; margin-bottom: 18px; cursor: pointer;
      background: none; border: none; font-family: 'DM Sans', sans-serif;
    }
    .back-link:hover { color: var(--text1); }

    #adminFormView { display: none; }

    .login-footer { text-align: center; margin-top: 18px; font-size: 12px; color: var(--text4); }
  </style>
</head>
<body>

<div class="login-wrap">
<div class="login-brand" onclick="window.location.href='/'" style="cursor:pointer;">
    <img src="/assets/dishub.png" alt="Logo Dishub"/>
    <div class="login-brand-name">
        Dishub<span class="login-brand-accent">Tulungagung</span>
    </div>
</div>

  <div class="login-card">

    @if ($sudahLoginPengguna)
    <div class="notice-box">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6 9 17l-5-5"/></svg>
      Anda sedang masuk sebagai <b>Pengguna</b> ({{ session('admin_user', '') }}).
      <a href="/admin/tiket-saya" style="font-weight:700;text-decoration:underline;">Lanjut ke Tiket Saya</a>
      atau <a href="/akun/keluar?next=/admin/login" style="font-weight:700;text-decoration:underline;">keluar</a>
      untuk masuk sebagai Admin/Pengelola di bawah.
    </div>
    @endif

    <!-- ═══ Layar 1: pilih cara masuk ═══ -->
    <div id="choiceView">
      <div class="login-title">Selamat datang</div>
      <div class="login-sub">Pilih cara Anda masuk</div>

      <!-- Login akun pengguna (publik) — Google, dipakai lintas layanan
           Trayek Wisata, Balik Gratis, Data Absensi (mode hari ini), dan
           Tiket Saya. TERPISAH dari akun staf: lihat pages/akun/callback.php
           untuk detail pemisahan sesinya. -->
      <a href="{{ $sudahLoginPengguna ? '/admin/tiket-saya' : '/akun/masuk?next='.rawurlencode($next) }}" class="choice-btn">
        <span class="choice-btn-icon">
          <svg width="18" height="18" viewBox="0 0 24 24"><path fill="#4285F4" d="M23.5 12.27c0-.85-.08-1.66-.22-2.45H12v4.63h6.46c-.28 1.5-1.13 2.77-2.4 3.62v3h3.88c2.27-2.09 3.56-5.17 3.56-8.8z"/><path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.94-2.92l-3.88-3c-1.08.72-2.45 1.15-4.06 1.15-3.13 0-5.78-2.11-6.73-4.96H1.27v3.11C3.25 21.3 7.31 24 12 24z"/><path fill="#FBBC05" d="M5.27 14.27A7.2 7.2 0 0 1 4.9 12c0-.79.14-1.56.37-2.27V6.62H1.27A11.99 11.99 0 0 0 0 12c0 1.94.46 3.77 1.27 5.38z"/><path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.44-3.44C17.95 1.19 15.24 0 12 0 7.31 0 3.25 2.7 1.27 6.62l4 3.11C6.22 6.86 8.87 4.75 12 4.75z"/></svg>
        </span>
        <span class="choice-btn-text">
          <span class="choice-btn-title">{{ $sudahLoginPengguna ? 'Lanjut sebagai Pengguna' : 'Masuk dengan Google' }}</span>
          <span class="choice-btn-sub">Untuk pengunjung &amp; pengguna layanan</span>
        </span>
      </a>

      <button type="button" class="choice-btn" onclick="showAdminForm()">
        <span class="choice-btn-icon">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2"><path d="M12 15a4 4 0 1 0 0-8 4 4 0 0 0 0 8z"/><path d="M5.5 20a6.5 6.5 0 0 1 13 0"/></svg>
        </span>
        <span class="choice-btn-text">
          <span class="choice-btn-title">Masuk sebagai Admin</span>
          <span class="choice-btn-sub">Khusus staf/pengelola Dishub</span>
        </span>
      </button>
    </div>

    <!-- ═══ Layar 2: form Admin/Pengelola (disembunyikan sampai dipilih) ═══ -->
    <div id="adminFormView">
      <button type="button" class="back-link" onclick="showChoiceView()">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 18l-6-6 6-6"/></svg>
        Kembali
      </button>

      <div class="login-title">Masuk sebagai Admin</div>
      <div class="login-sub">Dashboard Admin</div>

@if ($error)
    <div class="error-box">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      {{ $error }}
    </div>
@endif
@error('throttle')
    <div class="error-box">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      {{ $message }}
    </div>
@enderror

      <form method="POST" action="/admin/login" autocomplete="off">
        @csrf
        <div class="field-group">
          <label for="username">Username</label>
          <input type="text" id="username" name="username" placeholder="Masukkan username"
            autocomplete="username" required value="{{ $oldUsername }}"/>
        </div>
        <div class="field-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password"
            placeholder="Masukkan password" autocomplete="current-password" required/>
        </div>
        <button type="submit" class="btn-login">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
          Login
        </button>
      </form>
    </div>

<div class="login-footer" id="loginFooter"></div>

@include('partials.dev-credit-script', ['containerId' => 'loginFooter', 'anchorId' => '', 'anchorClass' => 'text-blue-600 hover:text-blue-800'])

  <div style="text-align:center; margin-top:16px;">
    <a href="/" style="display:inline-flex;align-items:center;gap:6px;font-size:13px;color:var(--text4);text-decoration:none;transition:color .15s;">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9.5L12 3l9 6.5V20a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1z"/><polyline points="9 21 9 12 15 12 15 21"/></svg>
      Kembali ke Beranda
    </a>
  </div>
</div>

<script>
function showAdminForm() {
  document.getElementById('choiceView').style.display = 'none';
  document.getElementById('adminFormView').style.display = 'block';
  const u = document.getElementById('username');
  if (u) u.focus();
}
function showChoiceView() {
  document.getElementById('adminFormView').style.display = 'none';
  document.getElementById('choiceView').style.display = 'block';
}
// Kalau form admin sempat disubmit dan gagal (ada $error) atau username
// sudah pernah diisi, langsung buka layar form supaya pesan error/isian
// tidak "hilang" di balik layar pilihan.
@if ($error || $oldUsername !== '')
showAdminForm();
@endif
</script>

</body>
</html>
