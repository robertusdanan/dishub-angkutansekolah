<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"/>
<meta name="csrf-token" content="{{ csrf_token() }}"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Verifikasi Tiket — Admin Balik Gratis</title>
<link rel="icon" href="/favicon.ico"/>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/admin/admin-shell.css">
<style>
  .adm-shell { display:flex !important; } .adm-main { flex:1; min-width:0; }
  .verif-grid{ display:grid; grid-template-columns:380px 1fr; gap:20px; align-items:start; }
  @media(max-width:900px){ .verif-grid{ grid-template-columns:1fr; } }
  .scan-card{ background:var(--surface); border:1.5px solid var(--border); border-radius:14px; padding:18px; }
  .scan-stage{ position:relative; background:#000; border-radius:12px; overflow:hidden; aspect-ratio:1/1; }
  .scan-video{ width:100%; height:100%; object-fit:cover; }
  .scan-frame{ position:absolute; inset:14%; border:3px solid rgba(255,255,255,.7); border-radius:16px; pointer-events:none; }
  .scan-frame.ok{ border-color:#22c55e; }
  .manual-row{ display:flex; gap:8px; margin-top:14px; }
  .manual-row input{ flex:1; padding:9px 12px; border:1.5px solid var(--border); border-radius:9px; font-size:13px; }
  .manual-hint{ font-size:11.5px; color:var(--text-4); margin-top:8px; }
  .result-card{ background:var(--surface); border:1.5px solid var(--border); border-radius:14px; padding:24px; min-height:300px; }
  .result-empty{ color:var(--text-4); font-size:13.5px; text-align:center; padding:80px 20px; }
  .result-head{ display:flex; align-items:center; justify-content:space-between; margin-bottom:16px; }
  .result-nama{ font-size:20px; font-weight:700; color:var(--text-1); }
  .result-meta{ font-size:13px; color:var(--text-3); margin-top:4px; font-family:'DM Mono',monospace; }
  .result-pill{ font-size:11px; font-weight:700; padding:4px 12px; border-radius:100px; text-transform:uppercase; }
  .pill-terdaftar{ background:#e0f2fe; color:#0369a1; } .pill-hadir{ background:#dcfce7; color:#15803d; } .pill-dibatalkan{ background:#f1f5f9; color:#64748b; }
  .detail-table{ width:100%; border-collapse:collapse; margin-top:16px; }
  .detail-table th{ text-align:left; font-size:10.5px; text-transform:uppercase; color:var(--text-3); padding:6px 0; border-bottom:1px solid var(--border); width:110px; }
  .detail-table td{ padding:8px 0; font-size:13px; border-bottom:1px solid var(--border); }
  .result-actions{ margin-top:20px; display:flex; gap:10px; }
  .toast { position:fixed; bottom:24px; right:24px; z-index:9999; padding:11px 16px; border-radius:10px; font-size:13px; font-weight:500; box-shadow:0 8px 24px rgba(15,23,42,.15); border:1.5px solid transparent; opacity:0; transform:translateY(10px); transition:.2s; pointer-events:none; }
  .toast.show { opacity:1; transform:translateY(0); }
  .toast.success { background:#f0fdf4; border-color:#bbf7d0; color:#15803d; }
  .toast.error { background:#fef2f2; border-color:#fecaca; color:#b91c1c; }
</style>
</head>
<body>
<div class="adm-shell">
  @include('admin.partials.sidebar', ['currentPage' => 'balikgratis_verifikasi', 'currentModule' => 'balikgratis'])
  <main class="adm-main"><div class="adm-content">
    <div class="adm-page-header">
      <div><h1 class="adm-page-title">Verifikasi Tiket</h1><p class="adm-page-subtitle">Pindai QR tiket penumpang di titik keberangkatan, atau cari manual pakai NIK / nomor tiket.</p></div>
    </div>

    <div class="verif-grid">
      <div class="scan-card">
        <div class="scan-stage">
          <video class="scan-video" id="scanVideo" autoplay muted playsinline></video>
          <div class="scan-frame" id="scanFrame"></div>
        </div>
        <canvas id="scanCanvas" style="display:none"></canvas>
        <div class="manual-row">
          <input type="text" id="manualQ" placeholder="Cari manual: NIK / No. Tiket..." onkeydown="if(event.key==='Enter')searchManual()"/>
          <button class="btn btn-secondary btn-sm" onclick="searchManual()">Cari</button>
        </div>
        <div class="manual-hint">Kamera otomatis mendeteksi QR tiket. Kalau kamera tidak tersedia, gunakan pencarian manual.</div>
      </div>

      <div class="result-card" id="resultCard">
        <div class="result-empty">Arahkan kamera ke QR tiket, atau cari manual di sebelah kiri.</div>
      </div>
    </div>
  </div></main>
</div>
<div class="toast" id="toast"></div>
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
<script src="/assets/admin/balikgratis/sb-secure.js"></script>
<script>
let scanning = true;
let lastToken = null;

async function initScanner() {
  if (typeof jsQR !== 'function') {
    document.getElementById('resultCard').innerHTML = '<div class="result-empty" style="color:#b91c1c">Library pemindai QR gagal dimuat. Gunakan pencarian manual di sebelah kiri.</div>';
    bgAdmToast('Library QR gagal dimuat — pakai pencarian manual', 'error');
    return;
  }
  const video = document.getElementById('scanVideo');
  const canvas = document.getElementById('scanCanvas');
  const ctx = canvas.getContext('2d', { willReadFrequently: true });
  try {
    const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
    video.srcObject = stream;
  } catch (e) {
    bgAdmToast('Tidak bisa akses kamera: ' + e.message, 'error');
    return;
  }
  requestAnimationFrame(tick);

  function tick() {
    if (video.readyState === video.HAVE_ENOUGH_DATA) {
      canvas.width = video.videoWidth; canvas.height = video.videoHeight;
      ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
      const imgData = ctx.getImageData(0, 0, canvas.width, canvas.height);
      const code = jsQR(imgData.data, imgData.width, imgData.height);
      if (code && scanning && code.data !== lastToken) {
        lastToken = code.data;
        scanning = false;
        document.getElementById('scanFrame').classList.add('ok');
        lookupToken(code.data);
        setTimeout(() => { scanning = true; document.getElementById('scanFrame').classList.remove('ok'); }, 2500);
      }
    }
    requestAnimationFrame(tick);
  }
}

async function lookupToken(token) {
  await doSearch(token);
}

async function searchManual() {
  const q = document.getElementById('manualQ').value.trim();
  if (!q) return;
  await doSearch(q);
}

async function doSearch(q) {
  const card = document.getElementById('resultCard');
  card.innerHTML = '<div class="result-empty">Mencari…</div>';
  try {
    const res = await fetch(`/admin/api/balikgratis/verifikasi?action=cari&q=${encodeURIComponent(q)}`);
    const json = await res.json();
    if (!res.ok || json.status !== 'ok') throw new Error(json.error || 'Tiket tidak ditemukan.');
    const rows = Array.isArray(json.data) ? json.data : [json.data];
    renderResult(rows);
  } catch (e) {
    card.innerHTML = `<div class="result-empty" style="color:#b91c1c">${bgEscAdm(e.message)}</div>`;
    bgAdmToast(e.message, 'error');
  }
}

function renderResult(rows) {
  const card = document.getElementById('resultCard');
  if (!rows.length) { card.innerHTML = '<div class="result-empty">Tiket tidak ditemukan.</div>'; return; }

  card.innerHTML = rows.map(r => `
    <div style="margin-bottom:22px;padding-bottom:22px;border-bottom:1px solid var(--border)">
      <div class="result-head">
        <div><div class="result-nama">${bgEscAdm(r.nama)}</div><div class="result-meta">${bgEscAdm(r.nomor_tiket)} · Balik Gratis ${r.tahun}</div></div>
        <span class="result-pill pill-${r.status}">${r.status}</span>
      </div>
      <table class="detail-table">
        <tr><th>NIK</th><td class="mono">${bgEscAdm(r.nik)}</td></tr>
        <tr><th>No. HP</th><td>${bgEscAdm(r.no_hp)}</td></tr>
        <tr><th>Kategori</th><td>${bgEscAdm(r.kategori)} · ${bgEscAdm(r.jenis_kelamin)}</td></tr>
        <tr><th>Alamat</th><td>${bgEscAdm(r.alamat)}</td></tr>
        ${r.verifikasi_at ? `<tr><th>Diverifikasi</th><td>${new Date(r.verifikasi_at).toLocaleString('id-ID')} oleh ${bgEscAdm(r.verifikasi_oleh||'-')}</td></tr>` : ''}
      </table>
      <div class="result-actions">
        ${r.status === 'terdaftar' ? `<button class="btn btn-primary btn-sm" onclick="tandaiHadir('${r.id}')">✓ Tandai Hadir / Boarding</button>` : ''}
        <a class="btn btn-secondary btn-sm" href="/admin/api/balikgratis/dokumen-view?id=${r.id}" target="_blank">Lihat Foto KTP/KK</a>
      </div>
    </div>`).join('');
}

async function tandaiHadir(id) {
  try {
    const res = await fetch('/admin/api/balikgratis/verifikasi?action=hadir', {
      method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }, body: JSON.stringify({ id }),
    });
    const json = await res.json();
    if (!res.ok || json.status !== 'ok') throw new Error(json.error || 'Gagal menandai hadir.');
    bgAdmToast('✓ Penumpang ditandai hadir.', 'success');
    const [fresh] = await bgGet('balikgratis_pemesanan', `id=eq.${id}&select=*`);
    if (fresh) renderResult([fresh]);
  } catch (e) {
    bgAdmToast(e.message, 'error');
  }
}

initScanner();
</script>
</body>
</html>
