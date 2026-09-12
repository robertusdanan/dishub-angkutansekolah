<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
<meta name="csrf-token" content="{{ csrf_token() }}"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Rekap Operasional - Admin</title>
  <link rel="canonical" href="{{ url('/admin/operasional') }}"/>
  <link rel="icon" href="/favicon.ico"/>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/admin/admin-shell.css">
  <link rel="stylesheet" href="/assets/css/tailwind.min.css"/>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.4/jspdf.plugin.autotable.min.js"></script>
  <style>
    /* Override Tailwind conflict with our shell */
    .adm-shell { display: flex !important; }
    .adm-main  { flex: 1; min-width: 0; }

    /* Filter selects matching admin-shell style */
    #transportasi, #bulan, #tahun, #searchInput {
      padding: 9px 14px; border: 1.5px solid var(--border); border-radius: 9px;
      font-family: 'DM Sans', sans-serif; font-size: 13.5px; color: var(--text-1);
      background: var(--surface-2); outline: none;
      transition: border-color .15s, box-shadow .15s;
      appearance: none; -webkit-appearance: none;
    }
    #transportasi:focus, #bulan:focus, #tahun:focus, #searchInput:focus {
      border-color: var(--accent); box-shadow: 0 0 0 3px var(--accent-glow); background: #fff;
    }
    #transportasi, #bulan { background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 10px center; padding-right: 34px; }

    /* Progress */
    #progressContainer { margin-bottom: 14px; display: none; }
    #progressContainer.visible { display: block; }
    .progress-track { background: var(--border); border-radius: 999px; height: 6px; overflow: hidden; }
    .progress-fill  { height: 100%; background: linear-gradient(90deg, var(--accent), #93c5fd); border-radius: 999px; transition: width 0.3s; width: 0%; }
    .progress-label { font-size: 12px; color: var(--text-4); margin-top: 5px; }

    /* Load badge */
    .load-badge { display: none; align-items: center; gap: 8px; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 10px; padding: 8px 14px; font-size: 13px; }
    .load-badge.done { background: #f0fdf4; border-color: #bbf7d0; }
    .pulse-dot { width: 7px; height: 7px; border-radius: 50%; background: var(--accent); flex-shrink: 0; animation: pulseDot 1.2s ease-in-out infinite; }
    .pulse-dot.done { background: #22c55e; animation: none; }
    @keyframes pulseDot { 0%, 100% { opacity:1; transform:scale(1); } 50% { opacity:0.25; transform:scale(0.55); } }

    /* Table */
    #rekapTable thead { background: var(--navy); color: rgba(255,255,255,.85); }
    #rekapTable thead th { padding: 10px 8px; font-size: 11px; font-weight: 600; letter-spacing: 0.04em; text-transform: uppercase; white-space: nowrap; }
    #rekapTable tbody tr { transition: background 0.1s; }
    #rekapTable tbody tr:hover { background: #f0f7ff !important; }
    #rekapTable tbody tr:nth-child(even) { background: var(--surface-2); }
    #rekapTable tbody td { border: 1px solid var(--border); }

    /* Legend chips */
    .legend-chip { display: inline-flex; align-items: center; gap: 5px; font-size: 12px; color: var(--text-3); }
    .legend-dot  { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
    .legend-code { display: inline-flex; align-items: center; justify-content: center; width: 20px; height: 16px; border-radius: 3px; background: var(--navy); color: white; font-size: 9px; font-weight: 700; font-family: 'DM Mono', monospace; }
  </style>
</head>
<body>
<div class="adm-shell">
  @include('admin.partials.sidebar', ['currentPage' => 'operasional', 'currentModule' => 'angkutansekolah'])

  <main class="adm-main">
    <div class="adm-content">

      <div class="adm-page-header">
        <div>
          <h1 class="adm-page-title">Rekap Operasional</h1>
          <p class="adm-page-subtitle">Angkutan Sekolah - Dinas Perhubungan Kab. Tulungagung</p>
        </div>
      </div>

      <!-- Filter Card -->
      <div class="card" style="padding:20px 24px;margin-bottom:20px;">
        <div style="display:flex;flex-direction:column;gap:14px;">
          <div style="display:flex;flex-wrap:wrap;align-items:center;gap:10px;">
            <select id="transportasi" style="min-width:170px;">
              <option value="all">Semua Transportasi</option>
              <option value="Bus">Bus</option>
              <option value="MPU">MPU</option>
            </select>
            <select id="bulan" style="min-width:140px;"></select>
            <select id="tahun" style="min-width:110px;"></select>
            <input type="text" id="searchInput" placeholder="Cari driver / plat / trayek..." style="min-width:220px;"/>
            <button id="btnFilter" class="btn btn-primary">
              <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
              Terapkan Filter
              <span id="btnLoading" class="hidden"><svg class="animate-spin" xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/><path d="M3 12a9 9 0 019-9"/></svg></span>
            </button>
          </div>
          <div style="display:flex;flex-wrap:wrap;align-items:center;gap:10px;">
            <button id="btnDownloadPDF" class="btn btn-success">
              <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
              Download PDF
            </button>
          </div>
          <div style="border-top:1px solid var(--border);padding-top:12px;display:flex;flex-wrap:wrap;gap:14px;align-items:center;">
            <span style="font-size:11px;font-weight:600;color:var(--text-4);letter-spacing:.06em;">KETERANGAN</span>
            <span class="legend-chip"><span class="legend-dot" style="background:#22c55e;"></span>Beroperasi</span>
            <span class="legend-chip"><span class="legend-dot" style="background:#ef4444;"></span>Tidak Beroperasi</span>
            <span class="legend-chip"><span class="legend-code">P</span> Pagi (04:00–11:00)</span>
            <span class="legend-chip"><span class="legend-code">S</span> Siang (11:00–20:00)</span>
            <span class="legend-chip"><span class="legend-code">(F)</span> Absensi via Foto</span>
            <span class="legend-chip"><span class="legend-code">n</span> Jumlah absen</span>
          </div>
        </div>
      </div>

      <!-- Load Badge -->
      <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:10px;">
        <div id="loadBadge" class="load-badge">
          <div id="pulseDot" class="pulse-dot"></div>
          <span id="lbLabel">Memuat</span>
          <span id="loadedCount">0</span>/<span id="totalDataCount">...</span> kendaraan
        </div>
      </div>

      <!-- Progress -->
      <div id="progressContainer">
        <div class="progress-track"><div id="progressBar" class="progress-fill"></div></div>
        <div id="progressText" class="progress-label">0%</div>
      </div>

      <!-- Table -->
      <div style="overflow-x:auto;" class="card">
        <table id="rekapTable" class="min-w-full text-xs md:text-sm text-center border-collapse">
          <thead>
            <tr>
              <th rowspan="2" class="px-4 py-3 border border-slate-700 text-left" style="border-color:rgba(255,255,255,0.1);">Kendaraan</th>
              <th colspan="31" id="bulanHeader" class="px-3 py-3 border border-slate-700" style="border-color:rgba(255,255,255,0.1);">Tanggal</th>
            </tr>
            <tr id="tanggalHeader"></tr>
          </thead>
          <tbody id="rekapBody"></tbody>
        </table>
      </div>

    </div><!-- /.adm-content -->
  </main>
</div><!-- /.adm-shell -->

<!-- Photo Modal -->
<div id="photoModal" class="fixed inset-0 hidden flex items-center justify-center z-50 p-4" style="background:rgba(15,23,42,0.85);backdrop-filter:blur(6px);">
  <div id="photoModalContent" class="bg-white rounded-xl shadow-2xl max-w-lg w-full relative transform transition-all scale-95 opacity-0">
    <button id="photoModalCloseBtn" style="position:absolute;top:-14px;right:-14px;background:white;border-radius:50%;width:32px;height:32px;display:flex;align-items:center;justify-content:center;border:none;cursor:pointer;color:var(--text-3);box-shadow:0 2px 8px rgba(0,0,0,0.2);transition:all 0.15s;" onmouseover="this.style.background='#ef4444';this.style.color='white'" onmouseout="this.style.background='white';this.style.color='var(--text-3)'">
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>
    <div class="p-3">
      <div style="background:var(--surface-2);border-radius:10px;min-height:200px;display:flex;align-items:center;justify-content:center;">
        <img id="photoModalImage" src="" alt="Foto Absensi" class="hidden max-h-[70vh] object-contain rounded-lg"/>
        <div id="photoModalSpinner" style="width:32px;height:32px;border:4px solid var(--border);border-top-color:var(--accent);border-radius:50%;" class="animate-spin"></div>
      </div>
    </div>
    <div id="photoModalCaption" style="padding:12px 16px;text-align:center;font-size:13px;color:var(--text-3);border-top:1px solid var(--border);"></div>
  </div>
</div>


<script src="/admin/assets/angkutansekolah/sb-secure.js"></script>
<script>
const SB_URL={!! json_encode(config('services.supabase.url')) !!};
const SB_ANON={!! json_encode(config('services.supabase.anon_key')) !!};
const sbHdr=()=>({"apikey":SB_ANON,"Authorization":`Bearer ${SB_ANON}`});
const bulanNama=["Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember"];
const GREEN=[34,197,94],RED=[239,68,68];
const bulanSelect=document.getElementById("bulan"),tahunSelect=document.getElementById("tahun"),transportasiSelect=document.getElementById("transportasi"),btnFilter=document.getElementById("btnFilter"),btnLoading=document.getElementById("btnLoading"),rekapBody=document.getElementById("rekapBody"),searchInput=document.getElementById("searchInput"),progressContainer=document.getElementById("progressContainer"),progressBar=document.getElementById("progressBar"),progressText=document.getElementById("progressText"),photoModal=document.getElementById("photoModal"),photoModalContent=document.getElementById("photoModalContent"),photoModalCloseBtn=document.getElementById("photoModalCloseBtn"),photoModalImage=document.getElementById("photoModalImage"),photoModalSpinner=document.getElementById("photoModalSpinner"),photoModalCaption=document.getElementById("photoModalCaption");
let kendaraanList=[],gridStatus=[],allBus=[],allMpu=[],progressStartTime=0;
/* ── DYNAMIC YEAR/MONTH DROPDOWNS ─────────────────────────────────────────
   Query Supabase for distinct waktu values (year-month level) so only
   years/months that actually have at least 1 absen record are shown.
   Strategy: fetch only the `waktu` column with a very small page size isn't
   efficient for large tables, so we use a GROUP-BY trick via PostgREST
   (select distinct year-month via RPC isn't always available).
   Instead we fetch just the `waktu` column with select=waktu and use the
   Prefer: count=none header, paging through all rows to collect unique
   year-month combos. For large datasets we limit fields to waktu only.
   ─────────────────────────────────────────────────────────────────────── */
let availableYearMonths = {}; // { year: Set<monthIndex0> }

async function fetchAvailableYearMonths() {
  const WIB_OFFSET = 7 * 60 * 60 * 1000;
  availableYearMonths = {}; // reset setiap kali dipanggil

  function parseWaktu(waktuStr) {
    if (!waktuStr) return null;
    const ms = new Date(waktuStr).getTime();
    if (isNaN(ms)) return null;
    const d = new Date(ms + WIB_OFFSET);
    return { y: d.getUTCFullYear(), m: d.getUTCMonth() };
  }

  function addEntry(waktuStr) {
    const p = parseWaktu(waktuStr);
    if (!p) return;
    if (!availableYearMonths[p.y]) availableYearMonths[p.y] = new Set();
    availableYearMonths[p.y].add(p.m);
  }

  async function fetchTable(tableName) {
    let from = 0;
    while (true) {
      const res = await fetch(
        `${SB_URL}/rest/v1/${tableName}?select=waktu&order=waktu.asc`,
        { headers: { ...sbHdr(), 'Range': `${from}-${from+999}`, 'Range-Unit': 'items', 'Prefer': 'count=none' } }
      );
      if (!res.ok) throw new Error(`fetchAvailableYearMonths(${tableName}) HTTP ${res.status}`);
      const rows = await res.json();
      for (const r of rows) addEntry(r.waktu);
      if (rows.length < 1000) break;
      from += 1000;
    }
  }

  // Query ketiga tabel sekaligus (absensi_RFID RFID, absensi_QRCode QR Code, absensi_foto)
  await Promise.all([fetchTable('absensi_RFID'), fetchTable('absensi_QRCode'), fetchTable('absensi_foto')]);
  console.log('[AvailableYearMonths]', JSON.stringify(
    Object.fromEntries(Object.entries(availableYearMonths).map(([y,s])=>[y,[...s].sort((a,b)=>a-b)]))
  ));
}

function isiDropdownTahun(selectedYear) {
  tahunSelect.innerHTML = '';
  const years = Object.keys(availableYearMonths).map(Number).sort((a, b) => a - b);
  if (years.length === 0) {
    // Fallback: show current year only
    const now = new Date().getFullYear();
    const opt = document.createElement('option');
    opt.value = now; opt.text = now; opt.selected = true;
    tahunSelect.appendChild(opt);
    return;
  }
  const nowYear = selectedYear ?? new Date().getFullYear();
  let bestYear = years.includes(nowYear) ? nowYear : years[years.length - 1];
  years.forEach(y => {
    const opt = document.createElement('option');
    opt.value = y; opt.text = y;
    if (y === bestYear) opt.selected = true;
    tahunSelect.appendChild(opt);
  });
}

function isiDropdownBulan(forYear, selectedMonth) {
  bulanSelect.innerHTML = '';
  const year = forYear ?? parseInt(tahunSelect.value);
  const months = availableYearMonths[year]
    ? [...availableYearMonths[year]].sort((a, b) => a - b)
    : [];
  if (months.length === 0) {
    // Fallback: show current month
    const nowMonth = new Date().getMonth();
    const opt = document.createElement('option');
    opt.value = nowMonth; opt.text = bulanNama[nowMonth]; opt.selected = true;
    bulanSelect.appendChild(opt);
    return;
  }
  const nowMonth = selectedMonth ?? new Date().getMonth();
  // Pick best: current month if available, else last available month in that year
  let bestMonth = months.includes(nowMonth) ? nowMonth : months[months.length - 1];
  months.forEach(m => {
    const opt = document.createElement('option');
    opt.value = m; opt.text = bulanNama[m];
    if (m === bestMonth) opt.selected = true;
    bulanSelect.appendChild(opt);
  });
}

/* When user changes year, rebuild bulan dropdown for that year */
function onTahunChange() {
  const y = parseInt(tahunSelect.value);
  isiDropdownBulan(y, null);
}
function showProgress(){progressBar.style.width="0%";progressText.textContent="0%";progressContainer.classList.add("visible");const lb=document.getElementById("loadBadge");lb.style.display="flex";lb.classList.remove("done");document.getElementById("pulseDot").classList.remove("done");document.getElementById("lbLabel").textContent="Memuat";document.getElementById("loadedCount").textContent="0";document.getElementById("totalDataCount").textContent="...";}
function hideProgress(){progressText.textContent="Selesai";setTimeout(()=>progressContainer.classList.remove("visible"),1000);const lb=document.getElementById("loadBadge");document.getElementById("lbLabel").textContent="Selesai";lb.classList.add("done");document.getElementById("pulseDot").classList.add("done");setTimeout(()=>{lb.style.display="none";},3000);}
function updateProgress(current,total,label=""){if(total===0)return;const percent=Math.min(100,Math.round((current/total)*100));progressBar.style.width=percent+"%";if(label&&current<total){const elapsed=Date.now()-progressStartTime;const etrMs=current>0?(elapsed/current)*(total-current):0;let estText="menghitung...";if(etrMs>=1000){const s=Math.round(etrMs/1000),m=Math.floor(s/60),rs=s%60;estText=m>0?`${m}mnt ${rs}dtk`:`${rs}dtk`;}progressText.textContent=`${percent}% - sisa ~${estText}`;}else{progressText.textContent=`${percent}%`;}document.getElementById("loadedCount").textContent=current.toLocaleString("id-ID");}
function buatNamaFile(prefix){const tr=transportasiSelect.value,b=parseInt(bulanSelect.value),t=parseInt(tahunSelect.value);return `${prefix}_${tr==="all"?"bus-mpu":tr.toLowerCase()}_${bulanNama[b]}_${t}`;}
async function loadDrivers(){if(allBus.length||allMpu.length)return;progressText.textContent="Memuat data kendaraan...";[allBus,allMpu]=await Promise.all([sbGetShared('driver_bus','select=trayek,plat,driver'),sbGetShared('driver_mpu','select=trayek,plat,driver')]);kendaraanList=[];allBus.forEach(d=>kendaraanList.push({transportasi:"Bus",trayek:d.trayek||"-",plat:d.plat,driver:d.driver,platDriver:`${d.plat} - ${d.driver}`}));allMpu.forEach(d=>kendaraanList.push({transportasi:"MPU",trayek:d.trayek||"-",plat:d.plat,driver:d.driver,platDriver:`${d.plat} - ${d.driver}`}));kendaraanList.sort((a,b)=>{if(a.transportasi==="Bus"&&b.transportasi==="MPU")return -1;if(a.transportasi==="MPU"&&b.transportasi==="Bus")return 1;return a.plat.localeCompare(b.plat);});}
async function loadData(){progressStartTime=Date.now();showProgress();btnFilter.disabled=true;btnLoading.classList.remove('hidden');const b=parseInt(bulanSelect.value),t=parseInt(tahunSelect.value),filterTr=transportasiSelect.value;const WIB_OFFSET=7*60*60*1000;const lastDay=new Date(t,b+1,0).getDate();const startISO=new Date(Date.UTC(t,b,1,0,0,0)-WIB_OFFSET).toISOString();const endISO=new Date(Date.UTC(t,b,lastDay,23,59,59,999)-WIB_OFFSET).toISOString();function getWIBParts(isoStr){const ms=new Date(isoStr).getTime()+WIB_OFFSET;const d=new Date(ms);return{date:d.getUTCDate(),hours:d.getUTCHours()};}
try{progressText.textContent="Memeriksa data kendaraan...";progressBar.style.width="10%";await loadDrivers();progressText.textContent="Mengambil data absensi...";progressBar.style.width="30%";
// Rentang tanggal (kalender WIB) untuk absensi_proxy.php - cache per-hari,
// jadi hari-hari lama dalam bulan ini tidak ditarik ulang dari Supabase
// tiap kali admin buka/filter ulang halaman ini.
const pad2=n=>String(n).padStart(2,'0');
const dateStart=`${t}-${pad2(b+1)}-01`;
const dateEnd=`${t}-${pad2(b+1)}-${pad2(lastDay)}`;
const fetchAbsensiDaily=async(table,select)=>{const res=await fetch(`/api/absensi-proxy?table=${encodeURIComponent(table)}&start=${dateStart}&end=${dateEnd}&select=${encodeURIComponent(select)}`);if(!res.ok)throw new Error(`absensi_proxy(${table}) HTTP ${res.status}`);return res.json();};
const[absensiBulan,absensiLamaBulan,absensiFotoBulan]=await Promise.all([fetchAbsensiDaily('absensi_RFID','waktu,plat_driver'),fetchAbsensiDaily('absensi_QRCode','waktu,plat_driver'),fetchAbsensiDaily('absensi_foto','id,foto,waktu,plat_driver,sesi')]);const absensiGabungan=[...absensiBulan,...absensiLamaBulan];progressBar.style.width="60%";progressText.textContent="Memproses data...";const daysInMonth=lastDay;document.getElementById("bulanHeader").textContent=`${bulanNama[b]} ${t}`;document.getElementById("bulanHeader").colSpan=daysInMonth;const headerRow=document.getElementById("tanggalHeader");headerRow.innerHTML="";for(let i=1;i<=daysInMonth;i++)headerRow.innerHTML+=`<th class="p-2 border font-medium w-12" style="border-color:rgba(255,255,255,0.1);font-size:11px;">${i}</th>`;const tbody=document.getElementById("rekapBody");tbody.innerHTML="";gridStatus=[];const searchTerm=searchInput.value.trim().toLowerCase();const kendaraanToProcess=kendaraanList.filter(k=>{const matchTransport=(filterTr==='all'||k.transportasi===filterTr);const matchSearch=!searchTerm||(k.plat.toLowerCase().includes(searchTerm)||k.driver.toLowerCase().includes(searchTerm)||k.trayek.toLowerCase().includes(searchTerm));return matchTransport&&matchSearch;});document.getElementById("totalDataCount").textContent=kendaraanToProcess.length.toLocaleString("id-ID");document.getElementById("lbLabel").textContent="Memproses";document.getElementById("loadedCount").textContent="0";const allRowsHTML=[];for(let i=0;i<kendaraanToProcess.length;i++){const k=kendaraanToProcess[i];const absensiByDate={};for(const r of absensiGabungan){if(!r.plat_driver)continue;const rPlat=r.plat_driver.trim();if(rPlat!==k.platDriver&&!rPlat.includes(k.plat))continue;const{date,hours}=getWIBParts(r.waktu);if(!absensiByDate[date])absensiByDate[date]=[];absensiByDate[date].push({hours});}const fotoByDate={};for(const r of absensiFotoBulan){if(!r.plat_driver)continue;const rPlat=r.plat_driver.trim();if(rPlat!==k.platDriver&&!rPlat.includes(k.plat))continue;const{date}=getWIBParts(r.waktu);if(!fotoByDate[date])fotoByDate[date]={};if(r.sesi)fotoByDate[date][r.sesi.toUpperCase()]=r;}let rowHTML=`<tr style="transition:background 0.1s;"><td class="px-3 py-2 border text-left" style="border-color:var(--border);min-width:140px;"><div style="font-weight:600;color:var(--navy);font-family:'DM Mono',monospace;font-size:12px;">${k.plat}</div><div style="font-size:11px;color:var(--text-4);margin-top:1px;">${k.transportasi} · ${k.trayek}</div><div style="font-size:11px;color:var(--text-4);">${k.driver}</div></td>`;const statuses=[];for(let tgl=1;tgl<=daysInMonth;tgl++){const absensiList=absensiByDate[tgl]||[];const fotoRec=fotoByDate[tgl]||{};const pagiAbsensi=absensiList.filter(r=>r.hours>=4&&r.hours<11);const siangAbsensi=absensiList.filter(r=>r.hours>=11&&r.hours<=19);const pagiFotoRec=fotoRec['PAGI']||null;const siangFotoRec=fotoRec['SIANG']||null;const pagiCount=pagiAbsensi.length;const siangCount=siangAbsensi.length;const pagiAktif=pagiCount>0||!!pagiFotoRec;const siangAktif=siangCount>0||!!siangFotoRec;const pagiInd=pagiCount>0?`${pagiCount}${pagiFotoRec?'(F)':''}`:(pagiFotoRec?'(F)':'');const siangInd=siangCount>0?`${siangCount}${siangFotoRec?'(F)':''}`:(siangFotoRec?'(F)':'');statuses.push({pagi:pagiAktif,siang:siangAktif,pagiCount,siangCount,pagiFoto:!!pagiFotoRec,siangFoto:!!siangFotoRec,pagiFotoRecord:pagiFotoRec,siangFotoRecord:siangFotoRec});const pTrig=pagiFotoRec?`class="photo-trigger cursor-pointer hover:opacity-80" data-record-id="${pagiFotoRec.id}" data-filename="${pagiFotoRec.foto}"`:'';const sTrig=siangFotoRec?`class="photo-trigger cursor-pointer hover:opacity-80" data-record-id="${siangFotoRec.id}" data-filename="${siangFotoRec.foto}"`:'';rowHTML+=`<td class="p-1 border" style="border-color:var(--border);"><div class="flex flex-col items-center justify-center h-full gap-1"><div ${pTrig} style="width:100%;"><span style="font-size:9px;color:var(--text-4);font-family:'DM Mono',monospace;display:block;height:12px;">${pagiInd}</span><div style="width:83%;height:16px;margin:0 auto;border-radius:4px;display:flex;align-items:center;justify-content:center;background:${pagiAktif?'#22c55e':'#ef4444'};"><span style="color:white;font-weight:700;font-size:9px;">P</span></div></div><div ${sTrig} style="width:100%;"><span style="font-size:9px;color:var(--text-4);font-family:'DM Mono',monospace;display:block;height:12px;">${siangInd}</span><div style="width:83%;height:16px;margin:0 auto;border-radius:4px;display:flex;align-items:center;justify-content:center;background:${siangAktif?'#22c55e':'#ef4444'};"><span style="color:white;font-weight:700;font-size:9px;">S</span></div></div></div></td>`;}rowHTML+=`</tr>`;allRowsHTML.push(rowHTML);gridStatus.push({meta:k,statuses});if(i%10===0||i===kendaraanToProcess.length-1){updateProgress(i+1,kendaraanToProcess.length,`Memproses ${k.plat}...`);await new Promise(r=>setTimeout(r,0));}}tbody.innerHTML=allRowsHTML.join('')||`<tr><td colspan="${daysInMonth+1}" style="padding:28px;text-align:center;color:var(--text-4);font-style:italic;">Tidak ada data</td></tr>`;updateProgress(kendaraanToProcess.length,kendaraanToProcess.length);setTimeout(hideProgress,800);}catch(err){console.error(err);alert("Gagal memuat data: "+err.message);progressContainer.classList.remove("visible");document.getElementById("loadBadge").style.display="none";}finally{btnFilter.disabled=false;btnLoading.classList.add('hidden');}}
function downloadPDF() {
  if (!gridStatus.length) { alert("Data belum siap."); return; }

  const { jsPDF } = window.jspdf;
  const doc = new jsPDF({ orientation: "landscape", unit: "mm", format: "a4" });
  const PW = doc.internal.pageSize.getWidth();   // 297
  const PH = doc.internal.pageSize.getHeight();  // 210

  const b = parseInt(bulanSelect.value);
  const t = parseInt(tahunSelect.value);
  const daysInMonth = new Date(t, b + 1, 0).getDate();
  const filterTr = transportasiSelect.value;
  const filterLabel = filterTr === 'all' ? 'Semua Transportasi' : filterTr;

  // ── PALETTE ──────────────────────────────────────────────
  const NAVY    = [15, 23, 42];
  const NAVY2   = [30, 41, 59];      // subheader
  const SILVER  = [241, 245, 249];   // alt row bg
  const BORDER  = [203, 213, 225];
  const GRN     = [34, 197, 94];
  const RED_C   = [239, 68, 68];
  const GRN_BG  = [220, 252, 231];   // light green bg for cell
  const RED_BG  = [254, 226, 226];   // light red bg for cell
  const GRN_TXT = [22, 163, 74];
  const RED_TXT = [185, 28, 28];
  const WHITE   = [255, 255, 255];
  const GRAY    = [100, 116, 139];
  const DARK    = [30, 41, 59];

  // ── HEADER BLOCK ─────────────────────────────────────────
  // Top navy bar
  doc.setFillColor(...NAVY);
  doc.rect(0, 0, PW, 22, "F");

  // Instansi title
  doc.setFont("helvetica", "bold");
  doc.setFontSize(13);
  doc.setTextColor(...WHITE);
  doc.text("DINAS PERHUBUNGAN KABUPATEN TULUNGAGUNG", PW / 2, 9, { align: "center" });

  doc.setFont("helvetica", "normal");
  doc.setFontSize(8);
  doc.setTextColor(148, 163, 184);
  doc.text("Rekap Operasional Angkutan Sekolah", PW / 2, 15, { align: "center" });

  // ── SUB HEADER ───────────────────────────────────────────
  doc.setFillColor(...SILVER);
  doc.rect(0, 22, PW, 13, "F");
  doc.setDrawColor(...BORDER);
  doc.setLineWidth(0.3);
  doc.line(0, 22, PW, 22);
  doc.line(0, 35, PW, 35);

  const now = new Date();
  const periodeStr  = `${bulanNama[b]} ${t}`;
  const dicetak     = now.toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
  const jam         = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

  doc.setFont("helvetica", "bold");
  doc.setFontSize(9);
  doc.setTextColor(...DARK);
  doc.text("Periode:", 14, 29.5);
  doc.text("Jenis:", 80, 29.5);
  doc.text("Jumlah Kendaraan:", 140, 29.5);
  doc.text("Dicetak:", 220, 29.5);

  doc.setFont("helvetica", "normal");
  doc.setFontSize(9);
  doc.setTextColor(...GRAY);
  doc.text(periodeStr, 32, 29.5);
  doc.text(filterLabel, 92, 29.5);
  doc.text(String(gridStatus.length), 174, 29.5);
  doc.text(`${dicetak}, ${jam}`, 235, 29.5);

  // ── LEGEND ───────────────────────────────────────────────
  let lx = 14, ly = 38.5;
  doc.setFontSize(7.5);

  // Beroperasi
  doc.setFillColor(...GRN_BG);
  doc.roundedRect(lx, ly - 3, 28, 5, 1, 1, "F");
  doc.setFillColor(...GRN);
  doc.circle(lx + 3, ly - 0.5, 1.5, "F");
  doc.setTextColor(...GRN_TXT);
  doc.setFont("helvetica", "bold");
  doc.text("Beroperasi", lx + 6, ly);

  // Tidak Beroperasi
  lx += 34;
  doc.setFillColor(...RED_BG);
  doc.roundedRect(lx, ly - 3, 34, 5, 1, 1, "F");
  doc.setFillColor(...RED_C);
  doc.circle(lx + 3, ly - 0.5, 1.5, "F");
  doc.setTextColor(...RED_TXT);
  doc.text("Tidak Beroperasi", lx + 6, ly);

  // Labels
  doc.setFont("helvetica", "normal");
  doc.setTextColor(...GRAY);
  lx += 40;
  doc.text("P = Pagi (04:00–11:00)", lx, ly);
  lx += 48;
  doc.text("S = Siang (11:00–20:00)", lx, ly);
  lx += 50;
  doc.text("(F) = Via Foto", lx, ly);
  lx += 30;
  doc.text("n = Jumlah absen", lx, ly);

  // ── TABLE ────────────────────────────────────────────────
  const tableStartY = 45;

  // Column widths: kendaraan fixed, day columns fill the rest
  const kendaraanW = 55;
  const remainW = PW - 14 - 14 - kendaraanW; // left+right margin
  const dayW = Math.min(7.5, remainW / daysInMonth);

  const head = [["Kendaraan", ...Array.from({ length: daysInMonth }, (_, i) => `${i + 1}`)]];
  const body = gridStatus.map(r => {
    return ["", // kolom kendaraan dikosongkan; didDrawCell yang menggambar
            ...Array.from({ length: daysInMonth }, () => "")];
  });

  // Row alternating colors
  const rowColors = gridStatus.map((_, i) => i % 2 === 0 ? WHITE : SILVER);

  doc.autoTable({
    head,
    body,
    startY: tableStartY,
    margin: { left: 14, right: 14 },
    theme: "plain",
    tableWidth: "auto",
    pageBreak: "auto",
    rowPageBreak: "avoid",
    headStyles: {
      fillColor: NAVY2,
      textColor: WHITE,
      fontStyle: "bold",
      halign: "center",
      valign: "middle",
      fontSize: 7,
      cellPadding: { top: 3, bottom: 3, left: 1, right: 1 },
      lineWidth: 0,
    },
    bodyStyles: {
      textColor: DARK,
      fontSize: 6,
      valign: "top",
      cellPadding: 0,
      lineWidth: 0.15,
      lineColor: BORDER,
      minCellHeight: 20,
      overflow: "linebreak",
    },
    columnStyles: {
      0: {
        cellWidth: kendaraanW,
        halign: "left",
        valign: "middle",
        cellPadding: 0,   // kita gambar manual di didDrawCell
        fontSize: 1,      // font sangat kecil agar teks kosong tidak makan ruang
        overflow: "hidden",
        fontStyle: "normal",
      },
      ...Object.fromEntries(
        Array.from({ length: daysInMonth }, (_, i) => [
          i + 1,
          { cellWidth: dayW, cellPadding: 0, halign: "center" }
        ])
      )
    },
    willDrawCell: function(data) {
      if (data.section === "body") {
        const ri = data.row.index;
        doc.setFillColor(...(ri % 2 === 0 ? WHITE : SILVER));
        doc.rect(data.cell.x, data.cell.y, data.cell.width, data.cell.height, "F");
      }
    },
    didDrawCell: function(data) {
      // ── Kendaraan column: layout tetap dari atas ──
      if (data.section === "body" && data.column.index === 0) {
        const ri = data.row.index;
        if (!gridStatus[ri]) return;
        const k = gridStatus[ri].meta;
        const { x, y, width: w, height: h } = data.cell;

        // Semua posisi relatif dari y (atas sel) + padding vertikal
        const padL = x + 3;
        const badgeW = w - 6;
        const LINE_H = 4.2; // jarak antar baris teks

        // ── Baris 1: Badge plat ──
        const badgeY = y + 2.5;
        const badgeH = 5.2;
        doc.setFillColor(...NAVY);
        doc.roundedRect(padL, badgeY, badgeW, badgeH, 1, 1, "F");
        doc.setFont("helvetica", "bold");
        doc.setFontSize(7);
        doc.setTextColor(...WHITE);
        doc.text(k.plat, padL + badgeW / 2, badgeY + badgeH / 2, { align: "center", baseline: "middle" });

        // ── Baris 2: Jenis · Trayek (wrap otomatis) ──
        const maxTextW = badgeW - 1; // lebar maksimal teks = lebar badge
        doc.setFont("helvetica", "normal");
        doc.setFontSize(5.5);
        doc.setTextColor(...GRAY);
        const trayekStr = `${k.transportasi} \u00B7 ${k.trayek}`;
        const trayekLines = doc.splitTextToSize(trayekStr, maxTextW);
        const trayekY = badgeY + badgeH + 2.5;
        doc.text(trayekLines, padL, trayekY, { baseline: "top" });

        // ── Baris 3: Driver (wrap otomatis, tepat di bawah trayek) ──
        const trayekBlockH = trayekLines.length * LINE_H;
        const driverY = trayekY + trayekBlockH + 0.5;
        doc.setFont("helvetica", "bold");
        doc.setFontSize(5.5);
        doc.setTextColor(51, 65, 85);
        const driverLines = doc.splitTextToSize(k.driver, maxTextW);
        doc.text(driverLines, padL, driverY, { baseline: "top" });
        return;
      }

      // ── Day columns ──
      if (data.section === "body" && data.column.index > 0) {
        const rowIdx = data.row.index;
        if (!gridStatus[rowIdx]) return;
        const dayIdx = data.column.index - 1;
        const st = gridStatus[rowIdx].statuses[dayIdx];
        if (!st) return;

        const { x, y, width: w, height: h } = data.cell;
        const cx = x + w / 2;
        // Bagi sel jadi 2 bagian sama rata, masing-masing dapat: gap atas, label kecil, pill
        const GAP = 0.8;         // gap dari tepi atas/tengah
        const LABEL_H = 2.8;     // tinggi area label angka
        const halfH = h / 2;
        const pillW = w - 1.6;
        const pillX = x + 0.8;
        const radius = 0.7;

        // ── PAGI ──
        const pagiAktif = st.pagi || st.pagiFoto;
        const pLabelY = y + GAP;                        // label angka di sini
        const pPillY  = pLabelY + LABEL_H;              // pill mulai di sini
        const pPillH  = halfH - GAP - LABEL_H - GAP;   // tinggi pill, sisakan gap bawah

        // Label angka DI ATAS pill (bukan di dalam)
        const pagiLabel = st.pagiCount > 0 ? String(st.pagiCount) : (st.pagiFoto ? "F" : "");
        if (pagiLabel) {
          doc.setFont("helvetica", "bold");
          doc.setFontSize(4.5);
          doc.setTextColor(...(pagiAktif ? GRN_TXT : RED_TXT));
          doc.text(pagiLabel, cx, pLabelY, { align: "center", baseline: "top" });
        }

        // Pill background (soft)
        doc.setFillColor(...(pagiAktif ? GRN_BG : RED_BG));
        doc.roundedRect(pillX, pPillY, pillW, pPillH, radius, radius, "F");
        // Pill solid (bawah 60%)
        doc.setFillColor(...(pagiAktif ? GRN : RED_C));
        const pSolidY = pPillY + pPillH * 0.38;
        doc.roundedRect(pillX, pSolidY, pillW, pPillH - pPillH * 0.38 + radius, radius, radius, "F");
        doc.rect(pillX, pSolidY, pillW, pPillH * 0.2, "F"); // tutup ujung atas solid
        // Huruf P
        doc.setFont("helvetica", "bold");
        doc.setFontSize(5);
        doc.setTextColor(...WHITE);
        doc.text("P", cx, pPillY + pPillH * 0.7, { align: "center", baseline: "middle" });

        // ── SIANG ──
        const siangAktif = st.siang || st.siangFoto;
        const sLabelY = y + halfH + GAP;
        const sPillY  = sLabelY + LABEL_H;
        const sPillH  = halfH - GAP - LABEL_H - GAP;

        const siangLabel = st.siangCount > 0 ? String(st.siangCount) : (st.siangFoto ? "F" : "");
        if (siangLabel) {
          doc.setFont("helvetica", "bold");
          doc.setFontSize(4.5);
          doc.setTextColor(...(siangAktif ? GRN_TXT : RED_TXT));
          doc.text(siangLabel, cx, sLabelY, { align: "center", baseline: "top" });
        }

        doc.setFillColor(...(siangAktif ? GRN_BG : RED_BG));
        doc.roundedRect(pillX, sPillY, pillW, sPillH, radius, radius, "F");
        doc.setFillColor(...(siangAktif ? GRN : RED_C));
        const sSolidY = sPillY + sPillH * 0.38;
        doc.roundedRect(pillX, sSolidY, pillW, sPillH - sPillH * 0.38 + radius, radius, radius, "F");
        doc.rect(pillX, sSolidY, pillW, sPillH * 0.2, "F");
        doc.setFont("helvetica", "bold");
        doc.setFontSize(5);
        doc.setTextColor(...WHITE);
        doc.text("S", cx, sPillY + sPillH * 0.7, { align: "center", baseline: "middle" });
      }

      // ── Header day cells: weekend highlight ──
      if (data.section === "head" && data.column.index > 0) {
        const dayNum = data.column.index; // 1-based
        const dow = new Date(t, b, dayNum).getDay(); // 0=Sun,6=Sat
        if (dow === 0 || dow === 6) {
          doc.setFillColor(51, 65, 85);
          doc.rect(data.cell.x, data.cell.y, data.cell.width, data.cell.height, "F");
          doc.setFont("helvetica", "bold");
          doc.setFontSize(7);
          doc.setTextColor(148, 163, 184);
          doc.text(String(dayNum), data.cell.x + data.cell.width / 2, data.cell.y + data.cell.height / 2, { align: "center", baseline: "middle" });
        }
      }
    }
  });

  // ── FOOTER on every page ─────────────────────────────────
  const pageCount = doc.internal.getNumberOfPages();
  for (let i = 1; i <= pageCount; i++) {
    doc.setPage(i);
    const footerY = PH - 8;
    doc.setFillColor(...NAVY);
    doc.rect(0, PH - 11, PW, 11, "F");
    doc.setFont("helvetica", "normal");
    doc.setFontSize(7.5);
    doc.setTextColor(148, 163, 184);
    doc.text(`Dicetak: ${dicetak} pukul ${jam}  -  Periode: ${periodeStr}  -  ${filterLabel}`, 14, footerY);
    doc.setFont("helvetica", "bold");
    doc.setTextColor(...WHITE);
    doc.text(`${i} / ${pageCount}`, PW - 14, footerY, { align: "right" });
  }

  doc.save(`${buatNamaFile("rekap_operasional")}.pdf`);
}
function openPhotoModal(recordId,filename){photoModalSpinner.classList.remove('hidden');photoModalImage.classList.add('hidden');photoModalImage.src='';const imageUrl=`/uploads/angkutansekolah/absensi-foto/${filename}`;photoModalImage.src=imageUrl;photoModalCaption.textContent='';photoModal.classList.remove('hidden');setTimeout(()=>{photoModalContent.classList.remove('scale-95','opacity-0');photoModalContent.classList.add('scale-100','opacity-100');},10);photoModalImage.onload=()=>{photoModalSpinner.classList.add('hidden');photoModalImage.classList.remove('hidden');};photoModalImage.onerror=()=>{photoModalSpinner.classList.add('hidden');photoModalCaption.textContent='Gagal memuat gambar.';};}
function closePhotoModal(){photoModalContent.classList.add('scale-95','opacity-0');setTimeout(()=>{photoModal.classList.add('hidden');},200);}
document.addEventListener("DOMContentLoaded", async () => {
  // Show a subtle loading state on the selects while we fetch
  bulanSelect.innerHTML = '<option value="">Memuat\u2026</option>';
  tahunSelect.innerHTML = '<option value="">Memuat\u2026</option>';
  btnFilter.disabled = true;

  try {
    await fetchAvailableYearMonths();
  } catch(e) {
    console.warn("Gagal fetch year-months, pakai fallback:", e);
  }

  isiDropdownTahun();
  isiDropdownBulan(parseInt(tahunSelect.value), null);

  // Rebuild bulan whenever tahun changes
  tahunSelect.addEventListener("change", onTahunChange);

  btnFilter.disabled = false;
  btnFilter.addEventListener("click", loadData);
  document.getElementById("btnDownloadPDF").addEventListener("click", downloadPDF);

  rekapBody.addEventListener('click', (e) => {
    const trigger = e.target.closest('.photo-trigger');
    if (trigger) {
      const { recordId, filename } = trigger.dataset;
      if (recordId && filename) openPhotoModal(recordId, filename);
    }
  });

  photoModalCloseBtn.addEventListener('click', closePhotoModal);
  photoModal.addEventListener('click', (e) => { if (e.target === photoModal) closePhotoModal(); });
  document.addEventListener('keydown', (e) => {
    if (e.key === "Escape" && !photoModal.classList.contains('hidden')) closePhotoModal();
  });

  loadData();
});
searchInput.addEventListener("keydown",(e)=>{if(e.key==="Enter")loadData();});
</script>
<script>(function(){const s=document.getElementById('admSidebar'),o=document.getElementById('admOverlay'),ob=document.getElementById('sidebarOpen'),cb=document.getElementById('sidebarClose');function op(){s.classList.add('open');o.classList.add('show');document.body.style.overflow='hidden';}function cl(){s.classList.remove('open');o.classList.remove('show');document.body.style.overflow='';}if(ob)ob.addEventListener('click',op);if(cb)cb.addEventListener('click',cl);if(o)o.addEventListener('click',cl);})();</script>
</body>
</html>