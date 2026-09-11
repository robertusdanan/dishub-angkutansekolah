<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"/>
<meta name="csrf-token" content="{{ csrf_token() }}"/>
<meta name="viewport" content="width=device-width, initial-scale=1"/>
<title>Verifikasi 2FA — Admin</title>
<link rel="icon" href="/favicon.ico"/>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=DM+Mono:wght@500&display=swap" rel="stylesheet">
<style>
  * { box-sizing: border-box; }
  body {
    margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center;
    font-family: 'DM Sans', sans-serif; background: linear-gradient(160deg, #0a1f44 0%, #060f24 100%);
    padding: 20px;
  }
  .box { background: #fff; border-radius: 20px; padding: 36px 32px; max-width: 380px; width: 100%; box-shadow: 0 24px 60px rgba(0,0,0,.35); }
  .icon { width: 52px; height: 52px; border-radius: 14px; background: #eff6ff; color: #1a56db; display: flex; align-items: center; justify-content: center; margin: 0 auto 18px; }
  h1 { font-size: 18px; font-weight: 700; text-align: center; color: #0f172a; margin: 0 0 6px; }
  p.sub { font-size: 13px; color: #64748b; text-align: center; margin: 0 0 24px; }
  .error-box { background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; font-size: 13px; padding: 10px 14px; border-radius: 9px; margin-bottom: 16px; }
  input[type=text] {
    width: 100%; padding: 14px; border: 1.5px solid #e2e8f0; border-radius: 12px; font-size: 22px;
    text-align: center; letter-spacing: 8px; font-family: 'DM Mono', monospace; margin-bottom: 14px;
  }
  input[type=text]:focus { outline: none; border-color: #1a56db; box-shadow: 0 0 0 3px rgba(26,86,219,.12); }
  button { width: 100%; padding: 13px; border: none; border-radius: 12px; background: #1a56db; color: #fff; font-weight: 700; font-size: 14px; cursor: pointer; }
  button:hover { background: #1544ab; }
  .toggle-recovery { display: block; text-align: center; margin-top: 16px; font-size: 12.5px; color: #64748b; text-decoration: underline; cursor: pointer; background: none; border: none; }
  .recovery-row { display: none; }
  .recovery-row.show { display: block; }
</style>
</head>
<body>
<div class="box">
  <div class="icon">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="11" width="18" height="10" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
  </div>
  <h1>Verifikasi Dua Langkah</h1>
  <p class="sub">Masukkan kode 6 digit dari aplikasi otentikator Anda untuk <strong>{{ session('admin_2fa_pending_username') }}</strong></p>

  @if ($error)
  <div class="error-box">{{ $error }}</div>
  @endif

  <form method="POST" action="/admin/login/verifikasi-2fa" id="totpForm">
    @csrf
    <div id="codeRow">
      <input type="text" name="code" inputmode="numeric" pattern="[0-9]*" maxlength="6" autocomplete="one-time-code" autofocus placeholder="000000"/>
    </div>
    <div class="recovery-row" id="recoveryRow">
      <input type="text" name="recovery_code" maxlength="10" placeholder="Kode pemulihan" style="letter-spacing:2px;font-size:15px;"/>
    </div>
    <button type="submit">Verifikasi</button>
  </form>
  <button type="button" class="toggle-recovery" onclick="toggleRecovery()">Kehilangan HP? Pakai kode pemulihan</button>
</div>
<script>
function toggleRecovery() {
  const codeRow = document.getElementById('codeRow');
  const recRow = document.getElementById('recoveryRow');
  const usingRecovery = recRow.classList.toggle('show');
  codeRow.style.display = usingRecovery ? 'none' : 'block';
  document.querySelector('.toggle-recovery').textContent = usingRecovery ? 'Pakai kode OTP dari aplikasi' : 'Kehilangan HP? Pakai kode pemulihan';
}
</script>
</body>
</html>
