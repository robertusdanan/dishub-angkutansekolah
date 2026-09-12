<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"/>
<meta name="csrf-token" content="{{ csrf_token() }}"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Verifikasi Tiket - Admin Trayek Wisata</title>
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
  .result-card{ background:var(--surface); border:1.5px solid var(--border); border-radius:14px; padding:24px; min-height:300px; }
  .result-empty{ color:var(--text-4); font-size:13.5px; text-align:center; padding:80px 20px; }
  .result-head{ display:flex; align-items:center; justify-content:space-between; margin-bottom:16px; }
  .result-trayek{ font-size:20px; font-weight:700; color:var(--text-1); }
  .result-meta{ font-size:13px; color:var(--text-3); margin-top:4px; }
  .result-pill{ font-size:11px; font-weight:700; padding:4px 12px; border-radius:100px; text-transform:uppercase; }
  .pill-ok{ background:#dcfce7; color:#15803d; } .pill-warn{ background:#fef3c7; color:#92400e; } .pill-bad{ background:#fee2e2; color:#b91c1c; }
  .pax-table{ width:100%; border-collapse:collapse; margin-top:16px; }
  .pax-table th{ text-align:left; font-size:10.5px; text-transform:uppercase; color:var(--text-3); padding:6px 0; border-bottom:1px solid var(--border); }
  .pax-table td{ padding:8px 0; font-size:13px; border-bottom:1px solid var(--border); }
  .result-actions{ margin-top:20px; }
  .toast { position:fixed; bottom:24px; right:24px; z-index:9999; padding:11px 16px; border-radius:10px; font-size:13px; font-weight:500; box-shadow:0 8px 24px rgba(15,23,42,.15); border:1.5px solid transparent; opacity:0; transform:translateY(10px); transition:.2s; pointer-events:none; }
  .toast.show { opacity:1; transform:translateY(0); }
  .toast.success { background:#f0fdf4; border-color:#bbf7d0; color:#15803d; }
  .toast.error { background:#fef2f2; border-color:#fecaca; color:#b91c1c; }
</style>
</head>
<body>
<div class="adm-shell">
  @include('admin.partials.sidebar', ['currentPage' => 'trayekwisata_verifikasi', 'currentModule' => 'trayekwisata'])
  <main class="adm-main"><div class="adm-content">
    <div class="adm-page-header">
      <div><h1 class="adm-page-title">Verifikasi Tiket</h1><p class="adm-page-subtitle">Pindai QR tiket penumpang di titik keberangkatan, atau cari manual pakai NIK.</p></div>
    </div>

    <div class="verif-grid">
      <div class="scan-card">
        <div class="scan-stage">
          <video class="scan-video" id="scanVideo" autoplay muted playsinline></video>
          <div class="scan-frame" id="scanFrame"></div>
        </div>
        <canvas id="scanCanvas" style="display:none"></canvas>
        <div class="manual-row">
          <input type="text" id="manualNik" placeholder="Atau cari manual pakai NIK..." onkeydown="if(event.key==='Enter')searchByNik()"/>
          <button class="btn btn-secondary btn-sm" onclick="searchByNik()">Cari</button>
        </div>
      </div>

      <div class="result-card" id="resultCard">
        <div class="result-empty">Arahkan kamera ke QR tiket, atau cari manual pakai NIK penumpang di sebelah kiri.</div>
      </div>
    </div>
  </div></main>
</div>
<div class="toast" id="toast"></div>
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
<script src="/assets/admin/trayekwisata/sb-secure.js"></script>
<script>
let scanning = true;

async function initScanner() {
  if (typeof jsQR !== 'function') {
    document.getElementById('resultCard').innerHTML = '<div class="result-empty" style="color:#b91c1c">Library pemindai QR gagal dimuat. Coba muat ulang halaman, atau gunakan pencarian manual pakai NIK di sebelah kiri.</div>';
    twToast('Library QR gagal dimuat - pakai pencarian manual NIK', 'error');
    return;
  }
  const video = document.getElementById('scanVideo');
  const canvas = document.getElementById('scanCanvas');
  const ctx = canvas.getContext('2d', { willReadFrequently: true });
  try {
    const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
    video.srcObject = stream;
  } catch (e) {
    twToast('Tidak bisa akses kamera: ' + e.message, 'error');
    return;
  }
  requestAnimationFrame(tick);

  function tick() {
    if (scanning && video.readyState === video.HAVE_ENOUGH_DATA) {
      canvas.width = video.videoWidth; canvas.height = video.videoHeight;
      ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
      const imgData = ctx.getImageData(0, 0, canvas.width, canvas.height);
      const code = jsQR(imgData.data, imgData.width, imgData.height);
      if (code && code.data.startsWith('TW:')) {
        document.getElementById('scanFrame').classList.add('ok');
        scanning = false;
        loadPemesanan(code.data.replace('TW:', ''));
        setTimeout(() => { document.getElementById('scanFrame').classList.remove('ok'); scanning = true; }, 2500);
      }
    }
    requestAnimationFrame(tick);
  }
}

async function searchByNik() {
  const nik = document.getElementById('manualNik').value.trim();
  if (!nik) return;
  try {
    const rows = await twGet('trayekwisata_pemesanan_nik', `nik=ilike.*${nik}*&select=pemesanan_id&order=created_at.desc&limit=1`);
    if (!rows.length) { twToast('NIK tidak ditemukan di pemesanan mana pun', 'error'); return; }
    loadPemesanan(rows[0].pemesanan_id);
  } catch (e) { twToast('Gagal mencari: ' + e.message, 'error'); }
}

async function loadPemesanan(id) {
  const card = document.getElementById('resultCard');
  card.innerHTML = '<div class="result-empty">Memuat…</div>';
  try {
    const rows = await twGet('trayekwisata_pemesanan',
      `id=eq.${id}&select=*,trayekwisata_jadwal(tanggal,hari,jam_berangkat,trayekwisata_trayek(nama)),akun_publik(nama,email,no_hp)`);
    if (!rows.length) { card.innerHTML = '<div class="result-empty" style="color:#b91c1c">Tiket tidak ditemukan.</div>'; return; }
    const r = rows[0];
    const pax = await twGet('trayekwisata_pemesanan_nik', `pemesanan_id=eq.${id}&select=nama,nik`);

    const jadwal = r.trayekwisata_jadwal;
    const trayek = jadwal?.trayekwisata_trayek;
    const tgl = jadwal ? new Date(jadwal.tanggal).toLocaleDateString('id-ID', { weekday:'long', day:'numeric', month:'long' }) : '-';

    let pillClass = 'pill-ok', pillText = 'Terkonfirmasi';
    if (r.status === 'dibatalkan') { pillClass = 'pill-bad'; pillText = 'Dibatalkan'; }
    else if (r.checked_in_at) { pillClass = 'pill-warn'; pillText = 'Sudah Check-in'; }

    card.innerHTML = `
      <div class="result-head">
        <div><div class="result-trayek">${twEsc(trayek?.nama || 'Trayek')}</div><div class="result-meta">${jadwal?.hari === 'SABTU' ? 'Sabtu' : 'Minggu'}, ${tgl} · ${(jadwal?.jam_berangkat||'').slice(0,5)}</div></div>
        <span class="result-pill ${pillClass}">${pillText}</span>
      </div>
      <div class="result-meta">Pemesan: <strong>${twEsc(r.akun_publik?.nama||'-')}</strong> · ${twEsc(r.akun_publik?.no_hp||'')}</div>
      <table class="pax-table">
        <thead><tr><th>Nama Penumpang</th><th>NIK</th></tr></thead>
        <tbody>${pax.map(p => `<tr><td>${twEsc(p.nama)}</td><td style="font-family:'DM Mono',monospace">${twEsc(p.nik)}</td></tr>`).join('')}</tbody>
      </table>
      <div class="result-actions">
        ${r.status === 'dibatalkan' ? '<p style="color:#b91c1c;font-size:13px">Tiket ini sudah dibatalkan, tidak bisa check-in.</p>' :
          r.checked_in_at ? `<p style="color:#92400e;font-size:13px">Sudah check-in pada ${new Date(r.checked_in_at).toLocaleString('id-ID')}</p>` :
          `<button class="btn btn-primary btn-sm" onclick="checkIn('${r.id}')">✓ Tandai Hadir</button>`}
      </div>`;
  } catch (e) {
    card.innerHTML = `<div class="result-empty" style="color:#b91c1c">Gagal memuat: ${twEsc(e.message)}</div>`;
  }
}

async function checkIn(id) {
  try {
    await twPatch('trayekwisata_pemesanan', `id=eq.${id}`, { checked_in_at: new Date().toISOString() });
    twToast('✓ Penumpang ditandai hadir');
    loadPemesanan(id);
  } catch (e) { twToast('Gagal: ' + e.message, 'error'); }
}

initScanner();
</script>
</body>
</html>
