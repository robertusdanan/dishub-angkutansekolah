<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link rel="icon" href="/favicon.ico"/>
  <title>{{ $cfg['title'] }}</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/tailwind.min.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
    :root {
      --navy: #0a1f44;
      --accent1: {{ $cfg['accent1'] }};
      --accent2: {{ $cfg['accent2'] }};
    }
    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      background: {{ $cfg['body_bg'] }};
      min-height: 100vh;
      margin: 0;
    }

    .top-bar {
      height: 5px;
      background: linear-gradient(90deg, var(--accent1), var(--accent2));
    }

    .page-header {
      background: #fff;
      border-bottom: 1px solid #e8edf5;
      padding: 20px 2rem;
      position: sticky;
      top: 0;
      z-index: 50;
      box-shadow: 0 1px 12px rgba(10,31,68,0.06);
    }
    .page-header-inner {
      max-width: 860px;
      margin: 0 auto;
      display: flex;
      align-items: center;
      gap: 16px;
    }
    .back-btn {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      font-size: 13px;
      font-weight: 600;
      color: var(--accent1);
      text-decoration: none;
      padding: 7px 16px;
      border-radius: 100px;
      border: 1.5px solid {{ $cfg['back_btn_border'] }};
      background: {{ $cfg['back_btn_bg'] }};
      transition: all 0.2s ease;
    }
    .back-btn:hover {
      background: var(--accent1);
      color: #fff;
      border-color: var(--accent1);
      transform: translateX(-2px);
    }
    .page-title {
      font-size: 18px;
      font-weight: 800;
      color: var(--navy);
      margin: 0;
    }
    .{{ $cfg['badge_class'] }} {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      background: {{ $cfg['badge_bg'] }};
      color: var(--accent1);
      font-size: 11px;
      font-weight: 700;
      padding: 4px 12px;
      border-radius: 100px;
      letter-spacing: 0.5px;
    }

    .content-wrap {
      max-width: 860px;
      margin: 0 auto;
      padding: 36px 2rem 60px;
    }

    #rute-detail > div {
      background: #fff;
      border-radius: 18px;
      padding: 28px;
      border: 1px solid {{ $cfg['card_border'] }};
      box-shadow: 0 2px 12px rgba({{ $cfg['shadow_rgb'] }},0.05);
      transition: box-shadow 0.25s ease;
    }
    #rute-detail > div:hover {
      box-shadow: 0 8px 30px rgba({{ $cfg['shadow_rgb'] }},0.1);
    }

    .site-footer {
      background: var(--navy);
      padding: {{ $cfg['footer_padding_top'] }} 2rem 16px;
    }
    .footer-inner {
      max-width: 860px;
      margin: 0 auto;
    }
    .footer-divider {
      border-top: 1px solid rgba(255,255,255,0.07);
      padding-top: 16px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 10px;
      position: relative;
    }
    #ad-container {
      position: absolute;
      inset: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      opacity: 0.12;
      pointer-events: none;
      overflow: hidden;
      border-radius: 8px;
      z-index: 0;
      filter: blur(0.5px) grayscale(30%);
      transition: opacity 0.3s ease;
    }
    .footer-divider:hover #ad-container { opacity: 0.25; pointer-events: auto; }
    .footer-copy {
      position: relative;
      z-index: 1;
      font-size: 12px;
      color: rgba(255,255,255,0.3);
    }
    .dev-credit {
      position: relative;
      z-index: 1;
      font-size: 11.5px;
      color: rgba(255,255,255,0.28);
      transition: color 0.2s;
    }
    .dev-credit:hover { color: rgba(255,255,255,0.55); }
    .dev-credit a {
      color: inherit;
      text-decoration: none;
      border-bottom: 1px dotted rgba(255,255,255,0.2);
    }
    .dev-credit a:hover { color: var(--accent2); border-bottom-color: var(--accent2); }
  </style>
</head>
<body>
  <div class="top-bar"></div>

  <header class="page-header">
    <div class="page-header-inner">
      <a href="/" class="back-btn">
        <i class="fa-solid fa-arrow-left fa-xs"></i> Kembali
      </a>
      <h1 class="page-title">{{ $cfg['heading'] }}</h1>
      <span class="{{ $cfg['badge_class'] }}">{{ $cfg['badge_label'] }}</span>
    </div>
  </header>

  <div class="content-wrap">
    <div id="rute-detail">
      <div style="animation: pulse 1.5s ease-in-out infinite; opacity: 0.6;">
        <p style="color:#94a3b8; font-size:14px;">Memuat data rute...</p>
      </div>
    </div>
  </div>

  <script type="module" src="{{ asset_url($cfg['js_file']) }}"></script>
</body>
</html>
