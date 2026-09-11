<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"/>
<meta name="csrf-token" content="{{ csrf_token() }}"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Pengaturan Trayek Tahunan — Admin</title>
<link rel="icon" href="/favicon.ico"/>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/admin/admin-shell.css">
<style>
  .adm-shell { display:flex !important; } .adm-main { flex:1; min-width:0; }

  .planner-toolbar { display:flex; align-items:center; gap:10px; margin-bottom:16px; flex-wrap:wrap; }
  .month-nav { display:flex; align-items:center; gap:8px; background:var(--surface); border:1.5px solid var(--border); border-radius:10px; padding:6px 10px; }
  .month-nav button { background:none; border:none; cursor:pointer; color:var(--text-3); font-size:15px; padding:2px 6px; }
  .month-nav button:hover { color:var(--accent); }
  .month-label { font-weight:700; font-size:14.5px; color:var(--text-1); min-width:150px; text-align:center; }
  .spacer { flex:1; }

  .cal-grid { display:grid; grid-template-columns:repeat(7,1fr); gap:6px; }
  .cal-dow { text-align:center; font-size:11px; font-weight:700; color:var(--text-4); text-transform:uppercase; letter-spacing:.05em; padding-bottom:4px; }
  .cal-cell { min-height:96px; border-radius:10px; border:1.5px solid var(--border); background:var(--surface); padding:7px; display:flex; flex-direction:column; gap:4px; cursor:pointer; transition:border-color .15s; position:relative; }
  .cal-cell:hover { border-color:var(--accent); }
  .cal-cell.empty { background:transparent; border-color:transparent; cursor:default; }
  .cal-cell.weekend { background:#f0fdf4; border-color:#bbf7d0; }
  .cal-cell.weekend:hover { border-color:var(--emerald); }
  .cal-daynum { font-size:12px; font-weight:700; color:var(--text-2); }
  .cal-weeknum { position:absolute; top:6px; right:7px; font-size:9.5px; color:var(--text-4); font-family:'DM Mono',monospace; }
  .cal-chip { font-size:10px; padding:2px 6px; border-radius:6px; background:var(--accent); color:#fff; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
  .cal-chip.penuh { background:var(--rose); }
  .cal-more { font-size:10px; color:var(--text-4); }

  .side-panel { display:flex; flex-direction:column; gap:16px; margin-top:20px; }
  .panel-card { background:var(--surface); border:1.5px solid var(--border); border-radius:14px; padding:16px; }
  .panel-title { font-size:13.5px; font-weight:700; color:var(--text-1); margin-bottom:10px; display:flex; align-items:center; justify-content:space-between; }

  .trayek-item { display:flex; align-items:center; justify-content:space-between; padding:8px 10px; border-radius:9px; background:var(--surface-2); margin-bottom:6px; font-size:12.5px; }
  .trayek-dot { width:9px; height:9px; border-radius:50%; display:inline-block; margin-right:7px; }

  .modal-overlay { position:fixed; inset:0; background:rgba(10,14,28,.6); z-index:9998; display:none; align-items:flex-start; justify-content:center; padding:40px 16px; overflow-y:auto; }
  .modal-overlay.open { display:flex; }
  .modal-box { background:#fff; border-radius:16px; width:100%; max-width:680px; box-shadow:0 24px 70px rgba(0,0,0,.28); }
  .modal-head { padding:18px 22px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; }
  .modal-title { font-size:16px; font-weight:700; color:var(--text-1); }
  .modal-close { cursor:pointer; color:var(--text-4); background:none; border:none; font-size:18px; }
  .modal-body { padding:20px 22px; display:flex; flex-direction:column; gap:14px; max-height:70vh; overflow-y:auto; }
  .modal-foot { padding:16px 22px; border-top:1px solid var(--border); display:flex; justify-content:space-between; gap:8px; }

  .f-row { display:flex; flex-direction:column; gap:5px; }
  .f-label { font-size:12px; font-weight:600; color:var(--text-2); }
  .f-input, .f-select { padding:9px 11px; border:1.5px solid var(--border); border-radius:9px; font-size:13.5px; outline:none; }
  .f-input:focus, .f-select:focus { border-color:var(--accent); box-shadow:0 0 0 3px var(--accent-glow); }
  .f-two { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
  .f-three { display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px; }

  .jadwal-row { display:flex; align-items:center; gap:10px; padding:10px; border:1.5px solid var(--border); border-radius:10px; margin-bottom:8px; }
  .jadwal-row .jr-dot { width:9px; height:9px; border-radius:50%; flex-shrink:0; }
  .jadwal-row .jr-body { flex:1; font-size:12.5px; line-height:1.5; }
  .jadwal-row .jr-name { font-weight:700; color:var(--text-1); }
  .jadwal-row .jr-meta { color:var(--text-3); }
  .status-pill { font-size:10px; padding:2px 8px; border-radius:20px; font-weight:700; text-transform:uppercase; }
  .status-aktif { background:#dcfce7; color:#15803d; }
  .status-penuh { background:#fee2e2; color:#b91c1c; }
  .status-dibatalkan { background:#f1f5f9; color:#64748b; }

  .titik-picker { display:flex; flex-direction:column; gap:6px; max-height:200px; overflow-y:auto; border:1.5px solid var(--border); border-radius:10px; padding:8px; }
  .titik-picker label { display:flex; align-items:center; gap:8px; font-size:12.5px; padding:4px 2px; }

  .toast { position:fixed; bottom:24px; right:24px; z-index:9999; padding:11px 16px; border-radius:10px; font-size:13px; font-weight:500; box-shadow:0 8px 24px rgba(15,23,42,.15); border:1.5px solid transparent; opacity:0; transform:translateY(10px); transition:.2s; pointer-events:none; max-width:360px; }
  .toast.show { opacity:1; transform:translateY(0); }
  .toast.success { background:#f0fdf4; border-color:#bbf7d0; color:#15803d; }
  .toast.error { background:#fef2f2; border-color:#fecaca; color:#b91c1c; }
  .bspin { width:12px; height:12px; border:2px solid rgba(255,255,255,.3); border-top-color:#fff; border-radius:50%; animation:spin .6s linear infinite; display:inline-block; }
  @keyframes spin { to{transform:rotate(360deg)} }

  .chk-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:6px; }
  .chk-grid label { display:flex; align-items:center; gap:6px; font-size:12.5px; padding:6px 8px; border:1.5px solid var(--border); border-radius:8px; }
</style>
</head>
<body>
<div class="adm-shell">
  @include('admin.partials.sidebar', ['currentPage' => 'trayekwisata_planner', 'currentModule' => 'trayekwisata'])

  <main class="adm-main"><div class="adm-content">

    <div class="adm-page-header">
      <div>
        <h1 class="adm-page-title">Pengaturan Trayek Tahunan</h1>
        <p class="adm-page-subtitle">Kalender Sabtu &amp; Minggu — klik tanggal untuk mengatur trayek yang berangkat hari itu.</p>
      </div>
      <button class="btn btn-primary btn-sm" onclick="openTrayekManager()">Kelola Master Trayek</button>
    </div>

    <div class="planner-toolbar">
      <div class="month-nav">
        <button onclick="shiftMonth(-1)">‹</button>
        <span class="month-label" id="monthLabel">—</span>
        <button onclick="shiftMonth(1)">›</button>
      </div>
      <div class="spacer"></div>
      <button class="btn btn-secondary btn-sm" onclick="jumpToday()">Hari ini</button>
    </div>

    <div class="cal-dow"></div>
    <div class="cal-grid" id="calDow">
      <div class="cal-dow">Sen</div><div class="cal-dow">Sel</div><div class="cal-dow">Rab</div><div class="cal-dow">Kam</div><div class="cal-dow">Jum</div><div class="cal-dow">Sab</div><div class="cal-dow">Min</div>
    </div>
    <div class="cal-grid" id="calGrid" style="margin-top:6px"></div>

  </div></main>
</div>

<!-- ═══ Modal: Jadwal per Tanggal ═══ -->
<div class="modal-overlay" id="dayOverlay">
  <div class="modal-box">
    <div class="modal-head">
      <span class="modal-title" id="dayTitle">Trayek —</span>
      <button class="modal-close" onclick="closeDay()">✕</button>
    </div>
    <div class="modal-body">
      <div id="jadwalList"></div>

      <div class="panel-card" style="background:var(--surface-2)">
        <div class="panel-title">Tambah Trayek di Tanggal Ini</div>
        <div class="f-row"><span class="f-label">Trayek</span>
          <select class="f-select" id="nj_trayek"></select>
        </div>
        <div class="f-three" style="margin-top:10px">
          <div class="f-row"><span class="f-label">Jam Berangkat</span><input class="f-input" type="time" id="nj_berangkat" value="07:00"/></div>
          <div class="f-row"><span class="f-label">Jam Pulang</span><input class="f-input" type="time" id="nj_pulang" value="14:00"/></div>
          <div class="f-row"><span class="f-label">Bus</span><select class="f-select" id="nj_bus"></select></div>
        </div>
        <div class="f-two" style="margin-top:10px">
          <div class="f-row"><span class="f-label">Driver</span><select class="f-select" id="nj_driver"></select></div>
          <div class="f-row"><span class="f-label">Kuota</span><input class="f-input" type="number" id="nj_kuota" value="30"/></div>
        </div>
        <button class="btn btn-primary btn-sm" style="margin-top:12px" onclick="addJadwal()">+ Tambahkan Trayek</button>
      </div>
    </div>
    <div class="modal-foot">
      <button class="btn btn-secondary btn-sm" onclick="openCopyModal()">📋 Salin Minggu Ini…</button>
      <button class="btn btn-secondary btn-sm" onclick="closeDay()">Tutup</button>
    </div>
  </div>
</div>

<!-- ═══ Modal: Salin ke Bulan / Minggu ═══ -->
<div class="modal-overlay" id="copyOverlay">
  <div class="modal-box">
    <div class="modal-head"><span class="modal-title">Salin Trayek Minggu Ini</span><button class="modal-close" onclick="closeCopyModal()">✕</button></div>
    <div class="modal-body">
      <p style="font-size:12.5px;color:var(--text-3)">Menyalin semua trayek pada <strong id="copySourceLabel"></strong> ke tujuan berikut. Trayek yang sudah ada di tanggal tujuan otomatis dilewati (tidak dobel).</p>

      <div class="f-row"><span class="f-label">Mode Salin</span>
        <select class="f-select" id="copyMode" onchange="renderCopyTargets()">
          <option value="months">Salin ke Bulan (minggu ke-N yang sama)</option>
          <option value="weeks">Salin ke Minggu tertentu (bebas pilih)</option>
        </select>
      </div>

      <div id="copyTargetsWrap"></div>
    </div>
    <div class="modal-foot">
      <button class="btn btn-secondary btn-sm" onclick="closeCopyModal()">Batal</button>
      <button class="btn btn-primary btn-sm" id="btnDoCopy" onclick="doCopy()">Salin Sekarang</button>
    </div>
  </div>
</div>

<!-- ═══ Modal: Kelola Master Trayek ═══ -->
<div class="modal-overlay" id="trayekOverlay">
  <div class="modal-box">
    <div class="modal-head"><span class="modal-title" id="trayekFormTitle">Master Trayek</span><button class="modal-close" onclick="closeTrayekManager()">✕</button></div>
    <div class="modal-body">
      <div id="trayekListWrap"></div>
      <hr style="border:none;border-top:1px solid var(--border)"/>
      <input type="hidden" id="tm_id"/>
      <div class="f-row"><span class="f-label">Nama Trayek</span><input class="f-input" id="tm_nama" placeholder="mis. Trayek Pantai Gemah"/></div>
      <div class="f-two">
        <div class="f-row"><span class="f-label">Warna Aksen</span><input class="f-input" type="color" id="tm_warna" value="#1a56db"/></div>
        <div class="f-row"><span class="f-label">Deskripsi</span><input class="f-input" id="tm_deskripsi" placeholder="opsional"/></div>
      </div>
      <div class="f-row"><span class="f-label">Urutan Titik Lokasi yang Dilewati</span>
        <div class="titik-picker" id="titikPicker"></div>
      </div>
    </div>
    <div class="modal-foot">
      <button class="btn btn-secondary btn-sm" onclick="resetTrayekForm()">Form Baru</button>
      <button class="btn btn-primary btn-sm" onclick="saveTrayek()">Simpan Trayek</button>
    </div>
  </div>
</div>

<div class="toast" id="toast"></div>

<script src="/assets/admin/trayekwisata/sb-secure.js"></script>
<script>
// ── State ────────────────────────────────────────────────────────────
let viewYear = new Date().getFullYear();
let viewMonth = new Date().getMonth() + 1; // 1-12
let allTrayek = [], allBus = [], allDriver = [], allTitikList = [];
let jadwalCache = {}; // key `${y}-${m}` -> rows
let selectedDate = null;
let selectedTitikIds = []; // untuk form trayek

function twWeekOfMonth(y, m, d) {
  const first = new Date(y, m - 1, 1);
  let firstWeekday = first.getDay(); // 0=Min..6=Sab
  firstWeekday = firstWeekday === 0 ? 7 : firstWeekday; // 1=Sen..7=Min
  return Math.ceil((d + firstWeekday - 1) / 7);
}

// ── Kalender ─────────────────────────────────────────────────────────
async function loadMonthData() {
  const key = `${viewYear}-${viewMonth}`;
  if (!jadwalCache[key]) {
    jadwalCache[key] = await twGet('trayekwisata_jadwal', `tahun=eq.${viewYear}&bulan=eq.${viewMonth}&select=*,trayekwisata_trayek(nama,warna)&order=tanggal.asc,jam_berangkat.asc`);
  }
  renderCalendar();
}

function shiftMonth(delta) {
  viewMonth += delta;
  if (viewMonth < 1) { viewMonth = 12; viewYear--; }
  if (viewMonth > 12) { viewMonth = 1; viewYear++; }
  loadMonthData();
}
function jumpToday() {
  const t = new Date();
  viewYear = t.getFullYear(); viewMonth = t.getMonth() + 1;
  loadMonthData();
}

const MONTH_NAMES = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

function renderCalendar() {
  document.getElementById('monthLabel').textContent = `${MONTH_NAMES[viewMonth-1]} ${viewYear}`;
  const rows = jadwalCache[`${viewYear}-${viewMonth}`] || [];
  const byDate = {};
  rows.forEach(r => { (byDate[r.tanggal] ||= []).push(r); });

  const first = new Date(viewYear, viewMonth - 1, 1);
  let firstWeekday = first.getDay(); firstWeekday = firstWeekday === 0 ? 7 : firstWeekday;
  const daysInMonth = new Date(viewYear, viewMonth, 0).getDate();

  let html = '';
  for (let i = 1; i < firstWeekday; i++) html += `<div class="cal-cell empty"></div>`;

  for (let d = 1; d <= daysInMonth; d++) {
    const dow = new Date(viewYear, viewMonth - 1, d).getDay();
    const isWeekend = dow === 6 || dow === 0;
    const dateStr = `${viewYear}-${String(viewMonth).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
    const dayJadwal = byDate[dateStr] || [];
    const wk = twWeekOfMonth(viewYear, viewMonth, d);

    let chips = dayJadwal.slice(0, 3).map(j => {
      const nama = j.trayekwisata_trayek?.nama || 'Trayek';
      const cls = j.status === 'penuh' ? 'penuh' : '';
      return `<span class="cal-chip ${cls}" style="${j.trayekwisata_trayek?.warna ? `background:${j.trayekwisata_trayek.warna}` : ''}">${twEsc(nama)} · ${j.jam_berangkat?.slice(0,5)}</span>`;
    }).join('');
    if (dayJadwal.length > 3) chips += `<span class="cal-more">+${dayJadwal.length - 3} lagi</span>`;

    html += `<div class="cal-cell ${isWeekend ? 'weekend' : ''}" onclick="openDay('${dateStr}', ${wk})">
      ${isWeekend ? `<span class="cal-weeknum">mgg ${wk}</span>` : ''}
      <span class="cal-daynum">${d}</span>
      ${chips}
    </div>`;
  }
  document.getElementById('calGrid').innerHTML = html;
}

// ── Master data (trayek/bus/driver/titik) ───────────────────────────
async function loadMasters() {
  [allTrayek, allBus, allDriver, allTitikList] = await Promise.all([
    twGet('trayekwisata_trayek', 'select=*&order=nama.asc'),
    twGet('trayekwisata_bus', 'select=*&aktif=eq.true&order=nama.asc'),
    twGet('trayekwisata_driver', 'select=*&aktif=eq.true&order=nama.asc'),
    twGet('trayekwisata_titik', 'select=*&order=nama.asc'),
  ]);
  fillSelect('nj_trayek', allTrayek, r => r.nama);
  fillSelect('nj_bus', allBus, r => `${r.nama}${r.plat_nomor ? ' — '+r.plat_nomor : ''} (${r.kapasitas} kursi)`);
  fillSelect('nj_driver', allDriver, r => r.nama);
}
function fillSelect(id, rows, labelFn) {
  document.getElementById(id).innerHTML = rows.map(r => `<option value="${r.id}">${twEsc(labelFn(r))}</option>`).join('') || '<option value="">— tidak ada —</option>';
}

// ── Modal: Jadwal per tanggal ────────────────────────────────────────
function openDay(dateStr, wk) {
  selectedDate = { dateStr, wk, tahun: viewYear, bulan: viewMonth };
  const dow = new Date(dateStr).getDay();
  const hariLabel = dow === 6 ? 'Sabtu' : 'Minggu';
  document.getElementById('dayTitle').textContent = `Trayek — ${hariLabel}, ${dateStr}`;
  document.getElementById('dayOverlay').classList.add('open');
  renderJadwalList();
}
function closeDay() { document.getElementById('dayOverlay').classList.remove('open'); }

function renderJadwalList() {
  const rows = (jadwalCache[`${viewYear}-${viewMonth}`] || []).filter(r => r.tanggal === selectedDate.dateStr);
  const wrap = document.getElementById('jadwalList');
  if (!rows.length) { wrap.innerHTML = '<p style="font-size:12.5px;color:var(--text-4)">Belum ada trayek dijadwalkan di tanggal ini.</p>'; return; }
  wrap.innerHTML = rows.map(r => `
    <div class="jadwal-row">
      <span class="jr-dot" style="background:${r.trayekwisata_trayek?.warna || '#1a56db'}"></span>
      <div class="jr-body">
        <div class="jr-name">${twEsc(r.trayekwisata_trayek?.nama || 'Trayek')} <span class="status-pill status-${r.status}">${r.status}</span></div>
        <div class="jr-meta">Berangkat ${r.jam_berangkat?.slice(0,5)} · Pulang ${r.jam_pulang ? r.jam_pulang.slice(0,5) : '—'} · Kuota ${r.kuota_terisi}/${r.kuota_total}</div>
        <div class="jr-meta" id="wl-${r.id}" style="color:var(--accent2,#F59E0B)"></div>
      </div>
      <button class="btn btn-secondary btn-sm" onclick="deleteJadwal('${r.id}')">Hapus</button>
    </div>
  `).join('');

  rows.forEach(async r => {
    try {
      const wl = await twGet('trayekwisata_waitlist', `jadwal_id=eq.${r.id}&select=akun_publik(nama,no_hp)`);
      const el = document.getElementById(`wl-${r.id}`);
      if (el && wl.length) {
        el.textContent = `🔔 ${wl.length} orang di daftar tunggu: ` + wl.map(w => w.akun_publik?.nama).filter(Boolean).join(', ');
      }
    } catch (e) { /* diam-diam, bukan data kritikal */ }
  });
}

async function addJadwal() {
  const trayekId = document.getElementById('nj_trayek').value;
  if (!trayekId) return twToast('Pilih trayek terlebih dahulu (buat dulu di "Kelola Master Trayek")', 'error');
  const busId = document.getElementById('nj_bus').value || null;
  const kuota = parseInt(document.getElementById('nj_kuota').value || '0', 10) ||
                (allBus.find(b => b.id === busId)?.kapasitas ?? 30);

  const payload = {
    trayek_id: trayekId,
    bus_id: busId,
    driver_id: document.getElementById('nj_driver').value || null,
    tanggal: selectedDate.dateStr,
    minggu_ke: selectedDate.wk,
    hari: new Date(selectedDate.dateStr).getDay() === 6 ? 'SABTU' : 'MINGGU',
    jam_berangkat: document.getElementById('nj_berangkat').value,
    jam_pulang: document.getElementById('nj_pulang').value || null,
    kuota_total: kuota,
    kuota_terisi: 0,
    status: 'aktif',
  };
  try {
    const created = await twPost('trayekwisata_jadwal', payload);
    const trayek = allTrayek.find(t => t.id === trayekId);
    created[0].trayekwisata_trayek = trayek ? { nama: trayek.nama, warna: trayek.warna } : null;
    (jadwalCache[`${viewYear}-${viewMonth}`] ||= []).push(created[0]);
    renderJadwalList(); renderCalendar();
    twToast('✓ Trayek ditambahkan ke tanggal ini');
  } catch (e) { twToast('Gagal menambah: ' + e.message, 'error'); }
}

async function deleteJadwal(id) {
  if (!confirm('Hapus trayek ini dari jadwal?')) return;
  try {
    await twDelete('trayekwisata_jadwal', `id=eq.${id}`);
    const key = `${viewYear}-${viewMonth}`;
    jadwalCache[key] = (jadwalCache[key] || []).filter(r => r.id !== id);
    renderJadwalList(); renderCalendar();
    twToast('✓ Dihapus');
  } catch (e) { twToast('Gagal menghapus: ' + e.message, 'error'); }
}

// ── Modal: Salin minggu ─────────────────────────────────────────────
function openCopyModal() {
  document.getElementById('copySourceLabel').textContent = `Minggu ke-${selectedDate.wk}, ${MONTH_NAMES[selectedDate.bulan-1]} ${selectedDate.tahun}`;
  document.getElementById('copyMode').value = 'months';
  renderCopyTargets();
  document.getElementById('copyOverlay').classList.add('open');
}
function closeCopyModal() { document.getElementById('copyOverlay').classList.remove('open'); }

function renderCopyTargets() {
  const mode = document.getElementById('copyMode').value;
  const wrap = document.getElementById('copyTargetsWrap');
  if (mode === 'months') {
    wrap.innerHTML = `<div class="f-row"><span class="f-label">Pilih bulan tujuan (tahun ${selectedDate.tahun}) — minggu ke-${selectedDate.wk} di bulan tsb</span>
      <div class="chk-grid">${MONTH_NAMES.map((m,i) => i+1===selectedDate.bulan ? '' : `<label><input type="checkbox" class="copyMonthChk" value="${i+1}"/>${m}</label>`).join('')}</div>
    </div>`;
  } else {
    wrap.innerHTML = `<div class="f-row"><span class="f-label">Tambahkan target minggu satu per satu</span>
      <div class="f-three">
        <select class="f-select" id="wk_tahun">${[selectedDate.tahun-1, selectedDate.tahun, selectedDate.tahun+1].map(y=>`<option ${y===selectedDate.tahun?'selected':''}>${y}</option>`).join('')}</select>
        <select class="f-select" id="wk_bulan">${MONTH_NAMES.map((m,i)=>`<option value="${i+1}">${m}</option>`).join('')}</select>
        <select class="f-select" id="wk_minggu">${[1,2,3,4,5].map(w=>`<option value="${w}">Minggu ke-${w}</option>`).join('')}</select>
      </div>
      <button class="btn btn-secondary btn-sm" style="margin-top:8px" onclick="addWeekTarget()">+ Tambah ke daftar</button>
      <div id="weekTargetList" style="margin-top:8px;display:flex;flex-direction:column;gap:6px"></div>
    </div>`;
    window._weekTargets = [];
  }
}
function addWeekTarget() {
  const t = { tahun: parseInt(document.getElementById('wk_tahun').value), bulan: parseInt(document.getElementById('wk_bulan').value), minggu_ke: parseInt(document.getElementById('wk_minggu').value) };
  window._weekTargets.push(t);
  document.getElementById('weekTargetList').innerHTML = window._weekTargets.map((t,i) => `<div class="trayek-item">Minggu ke-${t.minggu_ke}, ${MONTH_NAMES[t.bulan-1]} ${t.tahun} <button class="btn btn-secondary btn-sm" onclick="window._weekTargets.splice(${i},1);renderCopyTargets()">✕</button></div>`).join('');
}

async function doCopy() {
  const mode = document.getElementById('copyMode').value;
  let targets = [];
  if (mode === 'months') {
    targets = Array.from(document.querySelectorAll('.copyMonthChk:checked')).map(el => ({ tahun: selectedDate.tahun, bulan: parseInt(el.value) }));
  } else {
    targets = window._weekTargets || [];
  }
  if (!targets.length) return twToast('Pilih minimal satu tujuan', 'error');

  const btn = document.getElementById('btnDoCopy');
  btn.disabled = true; btn.innerHTML = '<span class="bspin"></span>';
  try {
    const res = await fetch('/admin/api/trayekwisata/copy', {
      method: 'POST', headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
      body: JSON.stringify({ source: { tahun: selectedDate.tahun, bulan: selectedDate.bulan, minggu_ke: selectedDate.wk }, mode, targets }),
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.error || 'Gagal menyalin');
    twToast(`✓ ${data.created} trayek disalin${data.skipped?.length ? `, ${data.skipped.length} dilewati` : ''}`);
    jadwalCache = {}; // invalidate semua cache bulan supaya kalender ke-refresh saat dibuka lagi
    loadMonthData();
    closeCopyModal();
  } catch (e) {
    twToast('Gagal menyalin: ' + e.message, 'error');
  } finally {
    btn.disabled = false; btn.textContent = 'Salin Sekarang';
  }
}

// ── Modal: Kelola Master Trayek ─────────────────────────────────────
function openTrayekManager() {
  renderTrayekListInModal();
  renderTitikPicker();
  document.getElementById('trayekOverlay').classList.add('open');
}
function closeTrayekManager() { document.getElementById('trayekOverlay').classList.remove('open'); }

function renderTrayekListInModal() {
  document.getElementById('trayekListWrap').innerHTML = allTrayek.map(t => `
    <div class="trayek-item">
      <span><span class="trayek-dot" style="background:${t.warna}"></span>${twEsc(t.nama)}</span>
      <span>
        <button class="btn btn-secondary btn-sm" onclick="editTrayek('${t.id}')">Edit</button>
        <button class="btn btn-secondary btn-sm" style="color:#b91c1c" onclick="deleteTrayekMaster('${t.id}')">Hapus</button>
      </span>
    </div>`).join('') || '<p style="font-size:12.5px;color:var(--text-4)">Belum ada trayek. Buat lewat form di bawah.</p>';
}

function renderTitikPicker(selectedOrdered = []) {
  const wrap = document.getElementById('titikPicker');
  selectedTitikIds = selectedOrdered.length ? selectedOrdered : [];
  wrap.innerHTML = allTitikList.map(t => `
    <label><input type="checkbox" value="${t.id}" ${selectedTitikIds.includes(t.id) ? 'checked' : ''} onchange="toggleTitik('${t.id}', this.checked)"/> ${twEsc(t.nama)} <span style="color:var(--text-4)">(${t.jenis})</span></label>
  `).join('') || '<p style="font-size:12px;color:var(--text-4)">Belum ada Titik Lokasi. Tambahkan dulu di menu "Titik Lokasi".</p>';
}
function toggleTitik(id, checked) {
  if (checked && !selectedTitikIds.includes(id)) selectedTitikIds.push(id);
  if (!checked) selectedTitikIds = selectedTitikIds.filter(x => x !== id);
}

function resetTrayekForm() {
  document.getElementById('tm_id').value = '';
  document.getElementById('tm_nama').value = '';
  document.getElementById('tm_warna').value = '#1a56db';
  document.getElementById('tm_deskripsi').value = '';
  renderTitikPicker();
}

async function editTrayek(id) {
  const t = allTrayek.find(x => x.id === id);
  if (!t) return;
  document.getElementById('tm_id').value = t.id;
  document.getElementById('tm_nama').value = t.nama;
  document.getElementById('tm_warna').value = t.warna || '#1a56db';
  document.getElementById('tm_deskripsi').value = t.deskripsi || '';
  const rel = await twGet('trayekwisata_trayek_titik', `trayek_id=eq.${id}&order=urutan.asc`);
  renderTitikPicker(rel.map(r => r.titik_id));
}

async function saveTrayek() {
  const nama = document.getElementById('tm_nama').value.trim();
  if (!nama) return twToast('Nama trayek wajib diisi', 'error');
  const id = document.getElementById('tm_id').value;
  const payload = { nama, warna: document.getElementById('tm_warna').value, deskripsi: document.getElementById('tm_deskripsi').value.trim() };

  try {
    let trayekId = id;
    if (id) {
      await twPatch('trayekwisata_trayek', `id=eq.${id}`, { ...payload, updated_at: new Date().toISOString() });
    } else {
      const created = await twPost('trayekwisata_trayek', payload);
      trayekId = created[0].id;
    }

    // Sinkronkan urutan titik: hapus lalu insert ulang sesuai urutan checkbox saat ini
    await twDelete('trayekwisata_trayek_titik', `trayek_id=eq.${trayekId}`);
    for (let i = 0; i < selectedTitikIds.length; i++) {
      await twPost('trayekwisata_trayek_titik', { trayek_id: trayekId, titik_id: selectedTitikIds[i], urutan: i }, 'return=minimal');
    }

    allTrayek = await twGet('trayekwisata_trayek', 'select=*&order=nama.asc');
    fillSelect('nj_trayek', allTrayek, r => r.nama);
    renderTrayekListInModal();
    resetTrayekForm();
    twToast('✓ Trayek disimpan');
  } catch (e) { twToast('Gagal menyimpan trayek: ' + e.message, 'error'); }
}

async function deleteTrayekMaster(id) {
  if (!confirm('Hapus trayek ini? Jadwal yang sudah dibuat dari trayek ini ikut terhapus.')) return;
  try {
    await twDelete('trayekwisata_trayek', `id=eq.${id}`);
    allTrayek = allTrayek.filter(t => t.id !== id);
    fillSelect('nj_trayek', allTrayek, r => r.nama);
    renderTrayekListInModal();
    jadwalCache = {}; loadMonthData();
    twToast('✓ Trayek dihapus');
  } catch (e) { twToast('Gagal menghapus: ' + e.message, 'error'); }
}

// ── Init ─────────────────────────────────────────────────────────────
(async function init() {
  await loadMasters();
  await loadMonthData();
})();
</script>
</body>
</html>
