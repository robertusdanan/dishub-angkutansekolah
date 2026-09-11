<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"/>
<meta name="csrf-token" content="{{ csrf_token() }}"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Pengaturan Kuota — Admin Balik Gratis</title>
<link rel="icon" href="/favicon.ico"/>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/admin/admin-shell.css">
<style>
  .adm-shell { display:flex !important; } .adm-main { flex:1; min-width:0; }

  .pengaturan-wrap { max-width:560px; }
  .status-hero { border-radius:18px; padding:26px 24px; margin-bottom:20px; border:1.5px solid var(--border); background:var(--surface); text-align:center; }
  .status-hero .tahun-label { font-family:'DM Mono',monospace; font-size:12px; color:var(--text-4); text-transform:uppercase; letter-spacing:.08em; margin-bottom:6px; }
  .status-hero .status-badge { display:inline-flex; align-items:center; gap:8px; padding:8px 18px; border-radius:100px; font-weight:800; font-size:14px; margin:6px 0 16px; }
  .status-badge.on { background:#dcfce7; color:#15803d; }
  .status-badge.off { background:#fee2e2; color:#b91c1c; }
  .status-badge .dot { width:8px; height:8px; border-radius:50%; background:currentColor; }
  .kuota-summary { display:flex; justify-content:center; gap:26px; margin-top:10px; }
  .kuota-summary div { text-align:center; }
  .kuota-summary .k-val { font-size:22px; font-weight:800; color:var(--text-1); }
  .kuota-summary .k-lbl { font-size:11px; color:var(--text-4); text-transform:uppercase; }

  .panel-card { background:var(--surface); border:1.5px solid var(--border); border-radius:14px; padding:22px; margin-bottom:16px; }
  .panel-title { font-size:14px; font-weight:700; color:var(--text-1); margin-bottom:4px; }
  .panel-sub { font-size:12px; color:var(--text-3); margin-bottom:16px; }
  .f-row { display:flex; flex-direction:column; gap:6px; margin-bottom:16px; }
  .f-label { font-size:12.5px; font-weight:600; color:var(--text-2); }
  .f-input { padding:11px 13px; border:1.5px solid var(--border); border-radius:10px; font-size:16px; outline:none; font-family:'DM Mono',monospace; font-weight:700; }
  .f-input:focus { border-color:var(--accent); box-shadow:0 0 0 3px var(--accent-glow); }
  .f-hint { font-size:11.5px; color:var(--text-4); }

  .btn-block { width:100%; padding:13px; border-radius:11px; border:none; font-weight:700; font-size:14px; cursor:pointer; }
  .btn-open { background:#16a34a; color:#fff; }
  .btn-open:hover { background:#15803d; }
  .btn-close { background:#fff; color:#b91c1c; border:1.5px solid #fecaca; }
  .btn-close:hover { background:#fef2f2; }
  .btn-block:disabled { opacity:.5; cursor:not-allowed; }

  .meta-line { font-size:11.5px; color:var(--text-4); margin-top:10px; text-align:center; }

  .toast { position:fixed; bottom:24px; right:24px; z-index:9999; padding:11px 16px; border-radius:10px; font-size:13px; font-weight:500; box-shadow:0 8px 24px rgba(15,23,42,.15); border:1.5px solid transparent; opacity:0; transform:translateY(10px); transition:.2s; pointer-events:none; }
  .toast.show { opacity:1; transform:translateY(0); }
  .toast.success { background:#f0fdf4; border-color:#bbf7d0; color:#15803d; }
  .toast.error { background:#fef2f2; border-color:#fecaca; color:#b91c1c; }
</style>
</head>
<body>
<div class="adm-shell">
  @include('admin.partials.sidebar', ['currentPage' => 'balikgratis_pengaturan', 'currentModule' => 'balikgratis'])
  <main class="adm-main"><div class="adm-content">

    <div class="adm-page-header">
      <div>
        <h1 class="adm-page-title">Pengaturan Kuota Tahun Ini</h1>
        <p class="adm-page-subtitle">Tahun terdeteksi otomatis mengikuti tahun kalender berjalan — tidak perlu diganti manual setiap tahun.</p>
      </div>
    </div>

    <div class="pengaturan-wrap">

      <div class="status-hero">
        <div class="tahun-label" id="tahunLabel">Balik Gratis —</div>
        <div class="status-badge off" id="statusBadge"><span class="dot"></span><span id="statusText">Memuat…</span></div>
        <div class="kuota-summary">
          <div><div class="k-val" id="kKuota">—</div><div class="k-lbl">Kuota</div></div>
          <div><div class="k-val" id="kTerisi">—</div><div class="k-lbl">Terisi</div></div>
          <div><div class="k-val" id="kSisa">—</div><div class="k-lbl">Sisa</div></div>
        </div>
        <div class="meta-line" id="metaLine"></div>
      </div>

      <div class="panel-card">
        <div class="panel-title" id="panelTitleKuota">Buka Pendaftaran</div>
        <div class="panel-sub">Tentukan jumlah kuota tiket yang dibuka untuk tahun ini, lalu klik mulai buka kuota.</div>
        <div class="f-row">
          <label class="f-label" for="inputKuota">Jumlah Kuota</label>
          <input type="number" id="inputKuota" class="f-input" min="1" placeholder="mis. 200">
          <span class="f-hint">Bisa diubah lagi kapan saja selama pendaftaran masih berjalan.</span>
        </div>
        <button class="btn-block btn-open" id="btnBuka" onclick="bukaKuota()">Mulai Buka Kuota</button>
      </div>

      <div class="panel-card">
        <div class="panel-title">Tutup Pendaftaran</div>
        <div class="panel-sub">Menutup pendaftaran Balik Gratis tahun ini. Data yang sudah terdaftar tidak akan terhapus, hanya pendaftaran baru yang dihentikan.</div>
        <button class="btn-block btn-close" id="btnTutup" onclick="tutupKuota()">Tutup Pendaftaran</button>
      </div>

    </div>

  </div></main>
</div>
<div class="toast" id="toast"></div>
<script src="/assets/admin/balikgratis/sb-secure.js"></script>
<script>
let currentStatus = null;

function fmtDate(iso) {
  if (!iso) return '-';
  const d = new Date(iso);
  return d.toLocaleString('id-ID', { day:'2-digit', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit' });
}

async function loadStatus() {
  try {
    const res = await fetch('/admin/api/balikgratis/pengaturan?action=status');
    const json = await res.json();
    if (!res.ok || json.status !== 'ok') throw new Error(json.error || 'Gagal memuat status.');
    currentStatus = json.data;
    render();
  } catch (e) {
    bgAdmToast(e.message, 'error');
  }
}

function render() {
  const s = currentStatus;
  document.getElementById('tahunLabel').textContent = 'Balik Gratis — Tahun ' + s.tahun;

  const badge = document.getElementById('statusBadge');
  const buka = s.status === 'buka';
  badge.className = 'status-badge ' + (buka ? 'on' : 'off');
  document.getElementById('statusText').textContent = buka ? 'Pendaftaran Dibuka' : 'Pendaftaran Ditutup';

  document.getElementById('kKuota').textContent = s.kuota;
  document.getElementById('kTerisi').textContent = s.terisi;
  document.getElementById('kSisa').textContent = s.sisa;

  document.getElementById('inputKuota').value = s.kuota || '';
  document.getElementById('panelTitleKuota').textContent = buka ? 'Ubah Kuota' : 'Buka Pendaftaran';
  document.getElementById('btnBuka').textContent = buka ? 'Simpan Perubahan Kuota' : 'Mulai Buka Kuota';
  document.getElementById('btnTutup').disabled = !buka;

  let meta = '';
  if (s.dibuka_at) meta += `Dibuka: ${fmtDate(s.dibuka_at)}${s.dibuka_oleh ? ' oleh ' + s.dibuka_oleh : ''}`;
  if (!buka && s.ditutup_at) meta += (meta ? ' · ' : '') + `Ditutup: ${fmtDate(s.ditutup_at)}`;
  document.getElementById('metaLine').textContent = meta;
}

async function bukaKuota() {
  const kuota = parseInt(document.getElementById('inputKuota').value, 10);
  if (!kuota || kuota < 1) { bgAdmToast('Masukkan jumlah kuota yang valid.', 'error'); return; }

  const btn = document.getElementById('btnBuka');
  btn.disabled = true;
  const action = (currentStatus && currentStatus.status === 'buka') ? 'ubah_kuota' : 'buka';
  try {
    const res = await fetch(`/admin/api/balikgratis/pengaturan?action=${action}`, {
      method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
      body: JSON.stringify({ kuota }),
    });
    const json = await res.json();
    if (!res.ok || json.status !== 'ok') throw new Error(json.error || 'Gagal menyimpan.');
    bgAdmToast(action === 'buka' ? 'Pendaftaran berhasil dibuka.' : 'Kuota berhasil diperbarui.', 'success');
    await loadStatus();
  } catch (e) {
    bgAdmToast(e.message, 'error');
  } finally {
    btn.disabled = false;
  }
}

async function tutupKuota() {
  if (!confirm('Tutup pendaftaran Balik Gratis tahun ini? Warga tidak akan bisa mendaftar lagi sampai dibuka ulang.')) return;
  const btn = document.getElementById('btnTutup');
  btn.disabled = true;
  try {
    const res = await fetch('/admin/api/balikgratis/pengaturan?action=tutup', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }, body: '{}' });
    const json = await res.json();
    if (!res.ok || json.status !== 'ok') throw new Error(json.error || 'Gagal menutup pendaftaran.');
    bgAdmToast('Pendaftaran telah ditutup.', 'success');
    await loadStatus();
  } catch (e) {
    bgAdmToast(e.message, 'error');
    btn.disabled = false;
  }
}

loadStatus();
</script>
</body>
</html>
