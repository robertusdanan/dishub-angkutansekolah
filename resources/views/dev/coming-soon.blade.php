<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Belum dimigrasikan - {{ $modul }}</title>
  <meta name="robots" content="noindex"/>
  <style>
    body { font-family: system-ui, sans-serif; background:#0f172a; color:#e2e8f0; display:flex; align-items:center; justify-content:center; min-height:100vh; margin:0; }
    .box { max-width:520px; padding:32px; text-align:center; }
    code { background:#1e293b; padding:2px 8px; border-radius:6px; color:#38bdf8; }
    a { color:#f59e0b; }
  </style>
</head>
<body>
  <div class="box">
    <h1>🚧 Modul <code>{{ $modul }}</code></h1>
    <p>Route sudah disiapkan di <code>routes/web.php</code>, tapi logic modul ini belum dipindah dari PHP native. Menyusul di tahap migrasi berikutnya.</p>
    <p><a href="/">← Kembali ke beranda</a></p>
  </div>
</body>
</html>
