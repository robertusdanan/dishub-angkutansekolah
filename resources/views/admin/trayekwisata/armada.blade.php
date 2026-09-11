<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"/>
<meta name="csrf-token" content="{{ csrf_token() }}"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Bus &amp; Driver — Admin</title>
<link rel="icon" href="/favicon.ico"/>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/admin/admin-shell.css">
<style>
  .adm-shell { display:flex !important; } .adm-main { flex:1; min-width:0; }
  .tabs { display:flex; gap:6px; margin-bottom:16px; border-bottom:1.5px solid var(--border); }
  .tab-btn { padding:10px 16px; font-size:13.5px; font-weight:600; color:var(--text-3); background:none; border:none; cursor:pointer; border-bottom:2.5px solid transparent; }
  .tab-btn.active { color:var(--accent); border-color:var(--accent); }
  .data-card { background:var(--surface); border:1.5px solid var(--border); border-radius:14px; overflow:hidden; }
  .data-table { width:100%; border-collapse:collapse; }
  .data-table thead tr { background:var(--surface-2); border-bottom:1.5px solid var(--border); }
  .data-table th { padding:10px 14px; text-align:left; font-size:10.5px; font-weight:700; letter-spacing:.07em; text-transform:uppercase; color:var(--text-3); }
  .data-table td { padding:10px 14px; font-size:13px; color:var(--text-1); border-bottom:1px solid var(--border); }
  .f-input { padding:7px 9px; border:1.5px solid var(--border); border-radius:8px; font-size:13px; width:100%; box-sizing:border-box; }
  .toast { position:fixed; bottom:24px; right:24px; z-index:9999; padding:11px 16px; border-radius:10px; font-size:13px; font-weight:500; box-shadow:0 8px 24px rgba(15,23,42,.15); border:1.5px solid transparent; opacity:0; transform:translateY(10px); transition:.2s; pointer-events:none; }
  .toast.show { opacity:1; transform:translateY(0); }
  .toast.success { background:#f0fdf4; border-color:#bbf7d0; color:#15803d; }
  .toast.error { background:#fef2f2; border-color:#fecaca; color:#b91c1c; }
  .row-new td { background:var(--surface-2); }
</style>
</head>
<body>
<div class="adm-shell">
  @include('admin.partials.sidebar', ['currentPage' => 'trayekwisata_armada', 'currentModule' => 'trayekwisata'])
  <main class="adm-main"><div class="adm-content">
    <div class="adm-page-header">
      <div><h1 class="adm-page-title">Bus &amp; Driver</h1><p class="adm-page-subtitle">Master armada &amp; pengemudi dipakai saat menjadwalkan trayek.</p></div>
    </div>

    <div class="tabs">
      <button class="tab-btn active" id="tabBus" onclick="switchTab('bus')">Bus</button>
      <button class="tab-btn" id="tabDriver" onclick="switchTab('driver')">Driver</button>
    </div>

    <div id="panelBus" class="data-card">
      <table class="data-table">
        <thead><tr><th>Nama Bus</th><th>Plat Nomor</th><th>Kapasitas</th><th style="width:70px">Aktif</th><th style="width:130px">Aksi</th></tr></thead>
        <tbody id="busBody"></tbody>
      </table>
    </div>

    <div id="panelDriver" class="data-card" style="display:none">
      <table class="data-table">
        <thead><tr><th>Nama Driver</th><th>No. HP</th><th style="width:70px">Aktif</th><th style="width:130px">Aksi</th></tr></thead>
        <tbody id="driverBody"></tbody>
      </table>
    </div>

  </div></main>
</div>
<div class="toast" id="toast"></div>
<script src="/assets/admin/trayekwisata/sb-secure.js"></script>
<script>
let busRows = [], driverRows = [];

function switchTab(t) {
  document.getElementById('panelBus').style.display    = t === 'bus' ? '' : 'none';
  document.getElementById('panelDriver').style.display  = t === 'driver' ? '' : 'none';
  document.getElementById('tabBus').classList.toggle('active', t === 'bus');
  document.getElementById('tabDriver').classList.toggle('active', t === 'driver');
}

async function loadBus() {
  busRows = await twGet('trayekwisata_bus', 'select=*&order=nama.asc');
  renderBus();
}
function renderBus() {
  document.getElementById('busBody').innerHTML = busRows.map(r => `
    <tr>
      <td><input class="f-input" value="${twEsc(r.nama)}" onchange="updBus('${r.id}','nama',this.value)"/></td>
      <td><input class="f-input" value="${twEsc(r.plat_nomor||'')}" onchange="updBus('${r.id}','plat_nomor',this.value)"/></td>
      <td><input class="f-input" type="number" value="${r.kapasitas}" style="width:80px" onchange="updBus('${r.id}','kapasitas',parseInt(this.value||0))"/></td>
      <td><input type="checkbox" ${r.aktif?'checked':''} onchange="updBus('${r.id}','aktif',this.checked)"/></td>
      <td><button class="btn btn-secondary btn-sm" style="color:#b91c1c" onclick="delBus('${r.id}')">Hapus</button></td>
    </tr>`).join('') + `
    <tr class="row-new">
      <td><input class="f-input" id="newBusNama" placeholder="Nama bus baru"/></td>
      <td><input class="f-input" id="newBusPlat" placeholder="Plat nomor"/></td>
      <td><input class="f-input" id="newBusKap" type="number" value="30" style="width:80px"/></td>
      <td></td>
      <td><button class="btn btn-primary btn-sm" onclick="addBus()">+ Tambah</button></td>
    </tr>`;
}
async function addBus() {
  const nama = document.getElementById('newBusNama').value.trim();
  if (!nama) return twToast('Nama bus wajib diisi', 'error');
  const row = await twPost('trayekwisata_bus', { nama, plat_nomor: document.getElementById('newBusPlat').value.trim(), kapasitas: parseInt(document.getElementById('newBusKap').value||30) });
  busRows.push(row[0]); renderBus(); twToast('✓ Bus ditambahkan');
}
async function updBus(id, field, val) {
  await twPatch('trayekwisata_bus', `id=eq.${id}`, { [field]: val });
  const r = busRows.find(x=>x.id===id); if (r) r[field]=val;
  twToast('✓ Tersimpan');
}
async function delBus(id) {
  if (!confirm('Hapus bus ini?')) return;
  await twDelete('trayekwisata_bus', `id=eq.${id}`);
  busRows = busRows.filter(r=>r.id!==id); renderBus(); twToast('✓ Dihapus');
}

async function loadDriver() {
  driverRows = await twGet('trayekwisata_driver', 'select=*&order=nama.asc');
  renderDriver();
}
function renderDriver() {
  document.getElementById('driverBody').innerHTML = driverRows.map(r => `
    <tr>
      <td><input class="f-input" value="${twEsc(r.nama)}" onchange="updDriver('${r.id}','nama',this.value)"/></td>
      <td><input class="f-input" value="${twEsc(r.no_hp||'')}" onchange="updDriver('${r.id}','no_hp',this.value)"/></td>
      <td><input type="checkbox" ${r.aktif?'checked':''} onchange="updDriver('${r.id}','aktif',this.checked)"/></td>
      <td><button class="btn btn-secondary btn-sm" style="color:#b91c1c" onclick="delDriver('${r.id}')">Hapus</button></td>
    </tr>`).join('') + `
    <tr class="row-new">
      <td><input class="f-input" id="newDrvNama" placeholder="Nama driver baru"/></td>
      <td><input class="f-input" id="newDrvHp" placeholder="No. HP"/></td>
      <td></td>
      <td><button class="btn btn-primary btn-sm" onclick="addDriver()">+ Tambah</button></td>
    </tr>`;
}
async function addDriver() {
  const nama = document.getElementById('newDrvNama').value.trim();
  if (!nama) return twToast('Nama driver wajib diisi', 'error');
  const row = await twPost('trayekwisata_driver', { nama, no_hp: document.getElementById('newDrvHp').value.trim() });
  driverRows.push(row[0]); renderDriver(); twToast('✓ Driver ditambahkan');
}
async function updDriver(id, field, val) {
  await twPatch('trayekwisata_driver', `id=eq.${id}`, { [field]: val });
  const r = driverRows.find(x=>x.id===id); if (r) r[field]=val;
  twToast('✓ Tersimpan');
}
async function delDriver(id) {
  if (!confirm('Hapus driver ini?')) return;
  await twDelete('trayekwisata_driver', `id=eq.${id}`);
  driverRows = driverRows.filter(r=>r.id!==id); renderDriver(); twToast('✓ Dihapus');
}

loadBus(); loadDriver();
</script>
</body>
</html>
