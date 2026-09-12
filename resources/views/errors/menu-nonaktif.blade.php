<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Segera Hadir - Dishub Kabupaten Tulungagung</title>
<meta name="robots" content="noindex"/>
<link rel="icon" href="/favicon.ico"/>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=DM+Mono:wght@500&display=swap" rel="stylesheet">
<style>
  *,*::before,*::after{ box-sizing:border-box; }
  body{
    margin:0; min-height:100vh; display:flex; flex-direction:column; align-items:center; justify-content:center;
    font-family:'DM Sans',-apple-system,'Segoe UI',Roboto,Arial,sans-serif; color:#fff; text-align:center; padding:32px 24px;
    background:radial-gradient(circle at 15% 0%,#0d2a5c 0%,#0A1F44 45%,#060F24 100%);
    position:relative; overflow:hidden;
  }
  .orb{ position:absolute; border-radius:50%; filter:blur(90px); opacity:.30; pointer-events:none; }
  .orb-1{ width:460px; height:460px; background:#1A56DB; top:-160px; right:-120px; }
  .orb-2{ width:380px; height:380px; background:#F59E0B; bottom:-150px; left:-110px; opacity:.16; }
  .brand{ position:relative; display:inline-flex; align-items:center; gap:10px; margin-bottom:56px; }
  .brand img{ width:26px; height:26px; border-radius:6px; }
  .brand-text{ text-align:left; }
  .brand-text b{ display:block; font-size:13px; font-weight:700; color:rgba(255,255,255,.92); }
  .brand-text span{ display:block; font-size:11px; color:rgba(255,255,255,.5); }
  .box{ position:relative; max-width:460px; }
  .badge{
    width:60px; height:60px; margin:0 auto 28px; border-radius:18px;
    background:rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.12);
    display:flex; align-items:center; justify-content:center;
  }
  .badge svg{ width:26px; height:26px; }
  .eyebrow{
    font-family:'DM Mono',monospace; font-size:11.5px; letter-spacing:.14em; text-transform:uppercase;
    color:#38BDF8; margin:0 0 14px; font-weight:500;
  }
  h1{ font-size:clamp(30px,6vw,40px); font-weight:800; letter-spacing:-.02em; margin:0 0 16px; line-height:1.15; }
  h1 .accent{ color:#F59E0B; }
  p.desc{ color:rgba(255,255,255,.62); margin:0 0 34px; line-height:1.75; font-size:14.5px; }
  a.cta{
    display:inline-flex; align-items:center; gap:8px; background:#F59E0B; color:#3a1f00; text-decoration:none;
    padding:13px 28px; border-radius:999px; font-weight:700; font-size:13.5px; transition:.15s ease;
    box-shadow:0 10px 30px rgba(245,158,11,.25);
  }
  a.cta:hover{ background:#f6a800; transform:translateY(-1px); box-shadow:0 14px 36px rgba(245,158,11,.32); }
  a.cta svg{ width:15px; height:15px; }
</style>
</head>
<body>
  <div class="orb orb-1" aria-hidden="true"></div>
  <div class="orb orb-2" aria-hidden="true"></div>

  <div class="brand">
    <img src="/assets/dishub.png" alt="Logo Dishub"/>
    <div class="brand-text"><b>Dinas Perhubungan</b><span>Kabupaten Tulungagung</span></div>
  </div>

  <div class="box">
    <div class="badge">
      <svg viewBox="0 0 24 24" fill="none" stroke="#38BDF8" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="9"/>
        <path d="M12 7v5l3.2 2"/>
      </svg>
    </div>
    <p class="eyebrow">Layanan Dalam Persiapan</p>
    <h1>Segera <span class="accent">Hadir</span></h1>
    <p class="desc"></p>
    <a class="cta" href="/">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
      Kembali ke Beranda
    </a>
  </div>
</body>
</html>
