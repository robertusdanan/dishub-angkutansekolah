<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"/>
<meta name="csrf-token" content="{{ csrf_token() }}"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Dashboard Trayek Wisata - Admin</title>
<link rel="icon" href="/favicon.ico"/>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/admin/admin-shell.css">
<style>
  .adm-shell { display:flex !important; } .adm-main { flex:1; min-width:0; }

  .filter-bar { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:20px; }
  .filter-bar select { padding:8px 12px; border:1.5px solid var(--border); border-radius:9px; font-size:13px; background:var(--surface); color:var(--text-1); }

  .stat-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(170px,1fr)); gap:14px; margin-bottom:24px; }
  .stat-card { background:var(--surface); border:1.5px solid var(--border); border-radius:14px; padding:16px; }
  .stat-label { font-size:11.5px; color:var(--text-4); font-weight:600; text-transform:uppercase; letter-spacing:.04em; }
  .stat-value { font-size:26px; font-weight:800; color:var(--text-1); margin-top:6px; }
  .stat-sub { font-size:11.5px; color:var(--text-3); margin-top:2px; }

  .panel-card { background:var(--surface); border:1.5px solid var(--border); border-radius:14px; padding:20px; }
  .panel-title { font-size:14px; font-weight:700; color:var(--text-1); margin-bottom:16px; }

  .bar-chart { display:flex; align-items:flex-end; gap:8px; height:180px; }
  .bar-col { flex:1; display:flex; flex-direction:column; align-items:center; justify-content:flex-end; height:100%; gap:6px; }
  .bar-fill { width:100%; border-radius:6px 6px 0 0; background:linear-gradient(180deg,var(--accent-2),var(--accent)); position:relative; min-height:2px; }
  .bar-label { font-size:10.5px; color:var(--text-4); }
</style>
</head>
<body>
<div class="adm-shell">
  @include('admin.partials.sidebar', ['currentPage' => 'trayekwisata_dashboard', 'currentModule' => 'trayekwisata'])
  <main class="adm-main"><div class="adm-content">

    <div class="adm-page-header">
      <div><h1 class="adm-page-title">Dashboard Trayek Wisata</h1><p class="adm-page-subtitle">Ringkasan operasional trayek wisata gratis - filter mengikuti kalender WIB.</p></div>
    </div>

    <div class="filter-bar">
      <select id="fTahun" onchange="reload()"></select>
      <select id="fBulan" onchange="reload()"><option value="">Semua Bulan</option></select>
      <select id="fMinggu" onchange="reload()"><option value="">Semua Minggu</option></select>
      <select id="fHari" onchange="reload()"><option value="">Sabtu &amp; Minggu</option><option value="SABTU">Sabtu</option><option value="MINGGU">Minggu</option></select>
    </div>

    <div class="stat-grid">
      <div class="stat-card"><div class="stat-label">Jumlah Trayek</div><div class="stat-value" id="sJadwal">-</div><div class="stat-sub">jadwal keberangkatan</div></div>
      <div class="stat-card"><div class="stat-label">Bus Aktif</div><div class="stat-value" id="sBus">-</div><div class="stat-sub">armada terdaftar</div></div>
      <div class="stat-card"><div class="stat-label">Kuota</div><div class="stat-value" id="sKuota">-</div><div class="stat-sub">total kursi tersedia</div></div>
      <div class="stat-card"><div class="stat-label">Kuota Terisi</div><div class="stat-value" id="sTerisi">-</div><div class="stat-sub" id="sPersen">-</div></div>
      <div class="stat-card"><div class="stat-label">Sisa Kursi</div><div class="stat-value" id="sSisa">-</div><div class="stat-sub">masih bisa dipesan</div></div>
      <div class="stat-card"><div class="stat-label">Jumlah Pemesanan</div><div class="stat-value" id="sPesan">-</div><div class="stat-sub">transaksi terkonfirmasi</div></div>
    </div>

    <div class="panel-card">
      <div class="panel-title">Kuota Terisi per Bulan (Tahun Terpilih)</div>
      <div class="bar-chart" id="barChart"></div>
    </div>

  </div></main>
</div>
<script src="/assets/admin/trayekwisata/sb-secure.js"></script>
<script>
const MONTH_NAMES = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];

function initFilters() {
  const y = new Date().getFullYear();
  const sel = document.getElementById('fTahun');
  sel.innerHTML = [y-1,y,y+1].map(v => `<option value="${v}" ${v===y?'selected':''}>${v}</option>`).join('');
  document.getElementById('fBulan').innerHTML += MONTH_NAMES.map((m,i) => `<option value="${i+1}">${m}</option>`).join('');
  document.getElementById('fMinggu').innerHTML += [1,2,3,4,5].map(w => `<option value="${w}">Minggu ke-${w}</option>`).join('');
}

async function reload() {
  const tahun  = document.getElementById('fTahun').value;
  const bulan  = document.getElementById('fBulan').value;
  const minggu = document.getElementById('fMinggu').value;
  const hari   = document.getElementById('fHari').value;

  let qs = `tahun=eq.${tahun}&select=*`;
  if (bulan)  qs += `&bulan=eq.${bulan}`;
  if (minggu) qs += `&minggu_ke=eq.${minggu}`;
  if (hari)   qs += `&hari=eq.${hari}`;

  const [jadwal, bus, pemesanan] = await Promise.all([
    twGet('trayekwisata_jadwal', qs),
    twGet('trayekwisata_bus', 'select=id&aktif=eq.true'),
    twGet('trayekwisata_pemesanan', `select=id,jumlah_kursi,status&status=eq.terkonfirmasi`),
  ]);

  const kuotaTotal  = jadwal.reduce((a,r) => a + (r.kuota_total||0), 0);
  const kuotaTerisi = jadwal.reduce((a,r) => a + (r.kuota_terisi||0), 0);

  document.getElementById('sJadwal').textContent = jadwal.length;
  document.getElementById('sBus').textContent = bus.length;
  document.getElementById('sKuota').textContent = kuotaTotal;
  document.getElementById('sTerisi').textContent = kuotaTerisi;
  document.getElementById('sPersen').textContent = kuotaTotal ? `${Math.round(kuotaTerisi/kuotaTotal*100)}% terisi` : '-';
  document.getElementById('sSisa').textContent = Math.max(0, kuotaTotal - kuotaTerisi);
  document.getElementById('sPesan').textContent = pemesanan.length;

  // Grafik per bulan (tahun penuh, tidak terpengaruh filter bulan/minggu/hari)
  const yearRows = await twGet('trayekwisata_jadwal', `tahun=eq.${tahun}&select=bulan,kuota_terisi,kuota_total`);
  const perBulan = Array(12).fill(0).map(() => ({ terisi: 0, total: 0 }));
  yearRows.forEach(r => { const i = r.bulan - 1; perBulan[i].terisi += r.kuota_terisi||0; perBulan[i].total += r.kuota_total||0; });
  const maxVal = Math.max(1, ...perBulan.map(b => b.total));
  document.getElementById('barChart').innerHTML = perBulan.map((b,i) => `
    <div class="bar-col">
      <div style="font-size:10px;color:var(--text-3)">${b.terisi}</div>
      <div class="bar-fill" style="height:${Math.max(2, b.total ? (b.terisi/maxVal*140) : 0)}px"></div>
      <div class="bar-label">${MONTH_NAMES[i]}</div>
    </div>`).join('');
}

initFilters();
reload();
</script>
</body>
</html>
