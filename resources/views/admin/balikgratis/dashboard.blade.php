<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"/>
<meta name="csrf-token" content="{{ csrf_token() }}"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Dashboard Balik Gratis — Admin</title>
<link rel="icon" href="/favicon.ico"/>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/admin/admin-shell.css">
<style>
  .adm-shell { display:flex !important; } .adm-main { flex:1; min-width:0; }
  .filter-bar { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:20px; align-items:center; }
  .filter-bar select { padding:8px 12px; border:1.5px solid var(--border); border-radius:9px; font-size:13px; background:var(--surface); color:var(--text-1); }
  .stat-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(170px,1fr)); gap:14px; margin-bottom:24px; }
  .stat-card { background:var(--surface); border:1.5px solid var(--border); border-radius:14px; padding:16px; }
  .stat-label { font-size:11.5px; color:var(--text-4); font-weight:600; text-transform:uppercase; letter-spacing:.04em; }
  .stat-value { font-size:26px; font-weight:800; color:var(--text-1); margin-top:6px; }
  .stat-sub { font-size:11.5px; color:var(--text-3); margin-top:2px; }
  .status-banner { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:14px 18px; border-radius:12px; margin-bottom:20px; border:1.5px solid var(--border); }
  .status-banner.on { background:#dcfce7; border-color:#bbf7d0; color:#15803d; }
  .status-banner.off { background:#fef2f2; border-color:#fecaca; color:#b91c1c; }
  .panel-card { background:var(--surface); border:1.5px solid var(--border); border-radius:14px; padding:20px; }
  .panel-title { font-size:14px; font-weight:700; color:var(--text-1); margin-bottom:16px; }
  .breakdown-row { display:flex; justify-content:space-between; padding:9px 0; border-bottom:1px solid var(--border); font-size:13px; }
  .breakdown-row:last-child { border-bottom:none; }
</style>
</head>
<body>
<div class="adm-shell">
  @include('admin.partials.sidebar', ['currentPage' => 'balikgratis_dashboard', 'currentModule' => 'balikgratis'])
  <main class="adm-main"><div class="adm-content">

    <div class="adm-page-header">
      <div><h1 class="adm-page-title">Dashboard Balik Gratis</h1><p class="adm-page-subtitle">Ringkasan pendaftaran Balik Gratis per tahun.</p></div>
    </div>

    <div class="filter-bar">
      <label style="font-size:12.5px;color:var(--text-3)">Tahun:</label>
      <select id="fTahun" onchange="reload()"></select>
    </div>

    <div id="statusBanner" class="status-banner off">Memuat status pendaftaran…</div>

    <div class="stat-grid">
      <div class="stat-card"><div class="stat-label">Kuota Dibuka</div><div class="stat-value" id="sKuota">—</div><div class="stat-sub">tiket tersedia</div></div>
      <div class="stat-card"><div class="stat-label">Terisi</div><div class="stat-value" id="sTerisi">—</div><div class="stat-sub" id="sPersen">—</div></div>
      <div class="stat-card"><div class="stat-label">Sisa Kuota</div><div class="stat-value" id="sSisa">—</div><div class="stat-sub">masih bisa daftar</div></div>
      <div class="stat-card"><div class="stat-label">Sudah Hadir</div><div class="stat-value" id="sHadir">—</div><div class="stat-sub">terverifikasi boarding</div></div>
      <div class="stat-card"><div class="stat-label">Dibatalkan</div><div class="stat-value" id="sBatal">—</div><div class="stat-sub">tiket batal</div></div>
    </div>

    <div class="panel-card">
      <div class="panel-title">Rincian per Kategori &amp; Jenis Kelamin</div>
      <div id="breakdownArea"></div>
    </div>

  </div></main>
</div>
<script src="/assets/admin/balikgratis/sb-secure.js"></script>
<script>
function initFilters() {
  const y = new Date().getFullYear();
  const sel = document.getElementById('fTahun');
  sel.innerHTML = [y-2,y-1,y,y+1].map(v => `<option value="${v}" ${v===y?'selected':''}>${v}${v===y?' (tahun ini)':''}</option>`).join('');
}

async function reload() {
  const tahun = document.getElementById('fTahun').value;
  const banner = document.getElementById('statusBanner');

  try {
    const [pengaturan] = await bgGet('balikgratis_pengaturan', `tahun=eq.${tahun}&select=*&limit=1`);
    const kuota = pengaturan ? pengaturan.kuota : 0;
    const buka = pengaturan && pengaturan.status === 'buka';
    banner.className = 'status-banner ' + (buka ? 'on' : 'off');
    banner.textContent = buka
      ? `Pendaftaran ${tahun} SEDANG DIBUKA — kuota ${kuota} tiket.`
      : `Pendaftaran ${tahun} sedang TUTUP.`;
    document.getElementById('sKuota').textContent = kuota;
  } catch (e) {
    banner.className = 'status-banner off';
    banner.textContent = 'Belum ada pengaturan kuota untuk tahun ini.';
    document.getElementById('sKuota').textContent = 0;
  }

  const rows = await bgGet('balikgratis_pemesanan', `tahun=eq.${tahun}&select=status,kategori,jenis_kelamin`);
  const terisi = rows.filter(r => r.status !== 'dibatalkan').length;
  const hadir = rows.filter(r => r.status === 'hadir').length;
  const batal = rows.filter(r => r.status === 'dibatalkan').length;
  const kuotaVal = parseInt(document.getElementById('sKuota').textContent) || 0;

  document.getElementById('sTerisi').textContent = terisi;
  document.getElementById('sPersen').textContent = kuotaVal ? `${Math.round(terisi/kuotaVal*100)}% terisi` : '—';
  document.getElementById('sSisa').textContent = Math.max(0, kuotaVal - terisi);
  document.getElementById('sHadir').textContent = hadir;
  document.getElementById('sBatal').textContent = batal;

  const groups = {};
  rows.filter(r => r.status !== 'dibatalkan').forEach(r => {
    const key = `${r.kategori} · ${r.jenis_kelamin}`;
    groups[key] = (groups[key] || 0) + 1;
  });
  const area = document.getElementById('breakdownArea');
  const keys = Object.keys(groups);
  area.innerHTML = keys.length
    ? keys.map(k => `<div class="breakdown-row"><span>${k}</span><b>${groups[k]}</b></div>`).join('')
    : '<div class="breakdown-row" style="color:var(--text-4)">Belum ada data pendaftaran.</div>';
}

initFilters();
reload();
</script>
</body>
</html>
