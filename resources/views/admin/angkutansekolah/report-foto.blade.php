<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
<meta name="csrf-token" content="{{ csrf_token() }}"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Report Absensi Foto - Admin</title>
  <link rel="canonical" href="{{ url('/admin/absensi-foto') }}"/>
  <link rel="icon" href="/favicon.ico"/>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/admin/admin-shell.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">
  <style>
    .filter-card   { padding: 20px 24px; margin-bottom: 20px; }
    .filter-grid   { display: flex; flex-wrap: wrap; gap: 10px; align-items: flex-end; margin-bottom: 14px; }
    .filter-actions{ display: flex; flex-wrap: wrap; gap: 10px; align-items: center; padding-top: 14px; border-top: 1px solid var(--border); }

    /* Load badge */
    #loadBadge { display:none; align-items:center; gap:8px; background:#eff6ff; border:1px solid #bfdbfe; border-radius:10px; padding:8px 14px; font-size:13px; margin-bottom:14px; }
    #loadBadge.done { background:#f0fdf4; border-color:#bbf7d0; }
    .pulse-dot { width:7px; height:7px; border-radius:50%; background:var(--accent); flex-shrink:0; animation:pulseDot 1.2s ease-in-out infinite; }
    .pulse-dot.done { background:#22c55e; animation:none; }
    @keyframes pulseDot { 0%,100%{opacity:1;transform:scale(1);}50%{opacity:.25;transform:scale(.55);} }

    /* Progress */
    #progressContainer { margin-bottom:14px; display:none; }
    #progressContainer.visible { display:block; }
    .progress-track { background:var(--border); border-radius:999px; height:6px; overflow:hidden; }
    .progress-fill  { height:100%; background:linear-gradient(90deg,var(--accent),#93c5fd); border-radius:999px; transition:width .3s; width:0%; }
    .progress-label { font-size:12px; color:var(--text-4); margin-top:5px; }

    /* Table extras */
    .foto-thumb { width:56px; height:56px; border-radius:8px; object-fit:cover; cursor:pointer; border:2px solid var(--border); transition:transform .15s,box-shadow .15s; display:block; margin:0 auto; }
    .foto-thumb:hover { transform:scale(1.06); box-shadow:0 4px 12px rgba(0,0,0,.12); }
    .no-foto { font-size:11px; color:var(--text-4); font-style:italic; }
    .sesi-badge { display:inline-flex; padding:2px 10px; border-radius:999px; font-size:11.5px; font-weight:600; }
    .sesi-pagi  { background:#fff7ed; color:#c2410c; }
    .sesi-siang { background:#eff6ff; color:#1d4ed8; }

    /* Image Modal */
    #imageModal { display:none; position:fixed; inset:0; background:rgba(15,23,42,.85); z-index:9999; align-items:center; justify-content:center; backdrop-filter:blur(6px); }
    #imageModal.open { display:flex; }
    .modal-img-wrapper { position:relative; max-width:90vw; max-height:90vh; }
    #modalImage { max-width:100%; max-height:90vh; border-radius:12px; display:block; box-shadow:0 16px 48px rgba(0,0,0,.3); }
    .modal-close-btn { position:absolute; top:-14px; right:-14px; background:white; border:none; border-radius:50%; width:32px; height:32px; font-size:18px; cursor:pointer; display:flex; align-items:center; justify-content:center; box-shadow:0 2px 8px rgba(0,0,0,.2); color:var(--text-3); transition:background .15s,color .15s; line-height:1; }
    .modal-close-btn:hover { background:#ef4444; color:white; }

    @keyframes spin { to { transform:rotate(360deg); } }

    @media (max-width:640px) { .filter-grid { flex-direction:column; } .form-control { min-width:0 !important; width:100%; } }
  </style>
</head>
<body>
<div class="adm-shell">
  @include('admin.partials.sidebar', ['currentPage' => 'report_foto', 'currentModule' => 'angkutansekolah'])
  <main class="adm-main">
    <div class="adm-content">

      <div class="adm-page-header">
        <div>
          <h1 class="adm-page-title">Report Absensi Foto</h1>
          <p class="adm-page-subtitle">Data absensi berbasis foto angkutan sekolah</p>
        </div>
      </div>

      <!-- Filter Card -->
      <div class="card filter-card">
        <div class="filter-grid">
          <select id="filterTanggal" class="form-control" style="min-width:155px;">
            <option value="">Semua Tanggal</option>
            <option value="today">Hari Ini</option>
            <option value="week">Mingguan</option>
            <option value="month">Bulanan</option>
          </select>
          <input type="text" id="datePicker" placeholder="Pilih tanggal / bulan" class="form-control hidden" style="min-width:200px;" readonly/>
          <select id="filterTransportasi" class="form-control" style="min-width:165px;">
            <option value="">Semua Transportasi</option>
            <option value="BUS">BUS</option>
            <option value="MPU">MPU</option>
          </select>
          <select id="filterTrayek" class="form-control hidden" style="min-width:145px;"><option value="">Semua Trayek</option></select>
          <select id="filterPlat" class="form-control hidden" style="min-width:155px;"><option value="">Semua Plat</option></select>
          <select id="filterAbsen" class="form-control" style="min-width:145px;">
            <option value="">Pagi &amp; Siang</option>
            <option value="PAGI">Absen Pagi</option>
            <option value="SIANG">Absen Siang</option>
          </select>
        </div>
        <div class="filter-actions">
          <button type="button" id="btnApply" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
            Terapkan Filter
          </button>
          <button type="button" id="btnExport" class="btn btn-success">
            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Download Excel
          </button>
        </div>
      </div>

      <!-- Load Badge -->
      <div id="loadBadge">
        <div id="pulseDot" class="pulse-dot"></div>
        <span id="lbLabel">Memuat</span>
        <span id="loadedCount">0</span>/<span id="totalDataCount">...</span> data
      </div>

      <!-- Progress -->
      <div id="progressContainer">
        <div class="progress-track"><div id="progressBar" class="progress-fill"></div></div>
        <div id="progressText" class="progress-label">0%</div>
      </div>

      <!-- Table -->
      <div class="card table-wrapper">
        <table id="reportTable">
          <thead>
            <tr>
              <th>No</th><th>Transportasi</th><th>Trayek</th>
              <th>Plat Driver</th><th>Sesi</th><th>Waktu</th><th>Foto</th>
            </tr>
          </thead>
          <tbody id="tableBody">
            <tr><td colspan="7" style="padding:28px;text-align:center;color:var(--text-4);font-style:italic;">Memuat data...</td></tr>
          </tbody>
        </table>
      </div>

    </div><!-- /.adm-content -->
  </main>
</div><!-- /.adm-shell -->

<!-- Image Modal -->
<div id="imageModal">
  <div class="modal-img-wrapper">
    <button class="modal-close-btn" id="closeModal">&times;</button>
    <img id="modalImage" src="" alt="Foto Absensi"/>
  </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>
<script src="https://cdn.jsdelivr.net/npm/xlsx/dist/xlsx.full.min.js"></script>

<script src="/admin/assets/angkutansekolah/sb-secure.js"></script>
<script>
const SB_URL={!! json_encode(config('services.supabase.url')) !!};
const tableBody=document.getElementById("tableBody"),progressContainer=document.getElementById("progressContainer"),progressBar=document.getElementById("progressBar"),progressText=document.getElementById("progressText"),filterTanggal=document.getElementById("filterTanggal"),datePicker=document.getElementById("datePicker"),filterTransportasi=document.getElementById("filterTransportasi"),filterTrayek=document.getElementById("filterTrayek"),filterPlat=document.getElementById("filterPlat"),filterAbsen=document.getElementById("filterAbsen");
let fpInstance=null,driverData={bus:[],mpu:[]};
async function loadDrivers(){[driverData.bus,driverData.mpu]=await Promise.all([sbGetShared('driver_bus','select=trayek,plat,driver&order=trayek.asc'),sbGetShared('driver_mpu','select=trayek,plat,driver&order=trayek.asc')]);}
filterTransportasi.addEventListener("change",()=>{filterTrayek.innerHTML=`<option value="">Semua Trayek</option>`;filterPlat.innerHTML=`<option value="">Semua Plat</option>`;filterTrayek.classList.add("hidden");filterPlat.classList.add("hidden");let data=filterTransportasi.value==="BUS"?driverData.bus:driverData.mpu;if(data.length){filterTrayek.classList.remove("hidden");const trayeks=[...new Set(data.map(d=>d.trayek).filter(Boolean))];trayeks.forEach(t=>{const opt=document.createElement("option");opt.value=t;opt.textContent=t;filterTrayek.appendChild(opt);});}});
filterTrayek.addEventListener("change",()=>{filterPlat.innerHTML=`<option value="">Semua Plat</option>`;filterPlat.classList.add("hidden");let data=filterTransportasi.value==="BUS"?driverData.bus:driverData.mpu;if(data.length&&filterTrayek.value){filterPlat.classList.remove("hidden");data.filter(d=>d.trayek===filterTrayek.value).forEach(d=>{const opt=document.createElement("option");opt.value=d.plat;opt.textContent=`${d.plat} - ${d.driver}`;filterPlat.appendChild(opt);});}loadData();});
filterTanggal.addEventListener("change",()=>{const now=new Date();if(fpInstance){fpInstance.destroy();fpInstance=null;}datePicker.value="";datePicker.placeholder="Pilih tanggal / bulan";datePicker.classList.add("hidden");datePicker.removeAttribute("readonly");switch(filterTanggal.value){case"today":datePicker.classList.remove("hidden");datePicker.value=now.toISOString().split("T")[0];datePicker.setAttribute("readonly",true);break;case"week":datePicker.classList.remove("hidden");datePicker.placeholder="Pilih rentang tanggal (max 7 hari)";datePicker.setAttribute("readonly",true);const startDefault=new Date();startDefault.setDate(now.getDate()-6);fpInstance=flatpickr(datePicker,{mode:"range",dateFormat:"Y-m-d",defaultDate:[startDefault,now],allowInput:false,clickOpens:true,onChange:(dates)=>{if(dates.length===2){const diff=(dates[1]-dates[0])/(1000*60*60*24)+1;if(diff>7){fpInstance.clear();alert("Maksimal 7 hari!");}}}});break;case"month":datePicker.classList.remove("hidden");datePicker.placeholder="Pilih bulan & tahun";datePicker.setAttribute("readonly",true);fpInstance=flatpickr(datePicker,{plugins:[new monthSelectPlugin({shorthand:true,dateFormat:"Y-m",altFormat:"F Y"})],dateFormat:"Y-m",defaultDate:now,allowInput:false,clickOpens:true});break;default:datePicker.classList.add("hidden");datePicker.setAttribute("readonly",true);}});
async function loadData(){const loadBadge=document.getElementById("loadBadge"),pulseDot=document.getElementById("pulseDot"),lbLabel=document.getElementById("lbLabel"),loadedCountEl=document.getElementById("loadedCount"),totalDataCountEl=document.getElementById("totalDataCount");tableBody.innerHTML=`<tr><td colspan="7" style="padding:40px;text-align:center;color:var(--text-4);font-style:italic;"><div style="display:flex;flex-direction:column;align-items:center;gap:10px;"><div style="width:26px;height:26px;border:3px solid var(--border);border-top-color:var(--accent);border-radius:50%;animation:spin 0.7s linear infinite;"></div><span>Mengambil data...</span></div></td></tr>`;try{progressContainer.classList.add("visible");progressBar.style.width="0%";progressText.textContent="0%";loadBadge.style.display="flex";loadBadge.classList.remove("done");pulseDot.classList.remove("done");lbLabel.textContent="Mengambil";loadedCountEl.textContent="0";totalDataCountEl.textContent="...";const qs=new URLSearchParams();if(filterTransportasi.value)qs.set("transportasi",filterTransportasi.value);if(filterTrayek.value)qs.set("trayek",filterTrayek.value);if(filterPlat.value)qs.set("plat",filterPlat.value);if(filterAbsen.value)qs.set("sesi",filterAbsen.value);if(filterTanggal.value==="today"){qs.set("tanggal","today");}else if(filterTanggal.value==="week"&&fpInstance?.selectedDates.length===2){const[d1,d2]=fpInstance.selectedDates;const fmt=d=>`${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,"0")}-${String(d.getDate()).padStart(2,"0")}`;qs.set("tanggal","week");qs.set("range_start",fmt(d1));qs.set("range_end",fmt(d2));}else if(filterTanggal.value==="month"&&fpInstance?.selectedDates.length===1){const d=fpInstance.selectedDates[0];qs.set("tanggal","month");qs.set("range_month",`${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,"0")}`);}const res=await fetch(`/admin/api/angkutansekolah/absensi-foto-report?${qs.toString()}`);if(!res.ok){const errBody=await res.json().catch(()=>({}));throw new Error(errBody.error||`HTTP ${res.status}`);}const{rows:allRecords}=await res.json();totalDataCountEl.textContent=allRecords.length.toLocaleString("id-ID");loadedCountEl.textContent=allRecords.length.toLocaleString("id-ID");progressBar.style.width="100%";progressText.textContent="Merender tabel…";if(!allRecords.length){tableBody.innerHTML=`<tr><td colspan="7" style="padding:28px;text-align:center;color:var(--text-4);font-style:italic;">Tidak ada data ditemukan</td></tr>`;}else{tableBody.innerHTML=allRecords.map((rec,i)=>buildRow(rec,i+1)).join('');}progressText.textContent="Selesai";setTimeout(()=>progressContainer.classList.remove("visible"),1000);lbLabel.textContent="Selesai";loadBadge.classList.add("done");pulseDot.classList.add("done");setTimeout(()=>{loadBadge.style.display="none";},3000);}catch(err){tableBody.innerHTML=`<tr><td colspan="7" style="padding:28px;text-align:center;color:#ef4444;">Gagal memuat data: ${err.message}</td></tr>`;progressText.textContent="Gagal";loadBadge.style.display="none";console.error(err);}}
function buildRow(rec,rowNum){let dateStr='-';if(rec.waktu){const d=new Date(rec.waktu);const wibMs=d.getTime()+7*60*60*1000;const dWib=new Date(wibMs);const yyyy=dWib.getUTCFullYear(),mm=String(dWib.getUTCMonth()+1).padStart(2,'0'),dd=String(dWib.getUTCDate()).padStart(2,'0');const hh=String(dWib.getUTCHours()).padStart(2,'0'),min=String(dWib.getUTCMinutes()).padStart(2,'0');dateStr=`${yyyy}-${mm}-${dd} ${hh}:${min} WIB`;}const sesiClass=rec.sesi==='PAGI'?'sesi-pagi':rec.sesi==='SIANG'?'sesi-siang':'';return `<tr><td style="font-family:'DM Mono',monospace;font-size:12px;">${rowNum}</td><td>${rec.transportasi||'-'}</td><td>${rec.trayek||'-'}</td><td style="font-family:'DM Mono',monospace;font-size:12px;">${rec.plat_driver||'-'}</td><td><span class="sesi-badge ${sesiClass}">${rec.sesi||'-'}</span></td><td style="font-family:'DM Mono',monospace;font-size:12px;white-space:nowrap;">${dateStr}</td><td style="text-align:center;">${rec.foto?`<img src="/uploads/angkutansekolah/absensi-foto/${rec.foto}" class="foto-thumb" onclick="openModal('/uploads/angkutansekolah/absensi-foto/${rec.foto}')">`:`<span class="no-foto">Tidak ada foto</span>`}</td></tr>`;}
document.getElementById("btnExport").onclick=()=>{const table=document.getElementById('reportTable');const wb=XLSX.utils.book_new();const originalHeaders=Array.from(table.querySelectorAll('thead th')).map(th=>th.innerText);const headers=originalHeaders.map(h=>h==="Waktu"?["Tanggal","Jam"]:h).flat();const rows=Array.from(table.querySelectorAll('tbody tr')).map(tr=>{return Array.from(tr.querySelectorAll('td')).map((td,idx)=>{if(originalHeaders[idx]==="Waktu"){const parts=td.innerText.trim().split(" ");return[parts[0],parts[1]||""];}if(idx===6){const img=td.querySelector('img');return img?img.src:td.innerText;}return td.innerText;}).flat();});const ws=XLSX.utils.aoa_to_sheet([headers,...rows]);XLSX.utils.book_append_sheet(wb,ws,"Absensi Foto");XLSX.writeFile(wb,"report_absensi_foto.xlsx");};
function openModal(src){const modal=document.getElementById("imageModal"),modalImg=document.getElementById("modalImage");modalImg.src=src;modal.classList.add("open");}
document.getElementById("closeModal").onclick=()=>{document.getElementById("imageModal").classList.remove("open");};
document.getElementById("imageModal").addEventListener("click",function(e){if(e.target===this)this.classList.remove("open");});
document.addEventListener("keydown",(e)=>{if(e.key==="Escape")document.getElementById("imageModal").classList.remove("open");});
document.getElementById("btnApply").onclick=()=>loadData();
loadDrivers().then(()=>loadData());
</script>

<script>(function(){const s=document.getElementById('admSidebar'),o=document.getElementById('admOverlay'),ob=document.getElementById('sidebarOpen'),cb=document.getElementById('sidebarClose');function op(){s.classList.add('open');o.classList.add('show');document.body.style.overflow='hidden';}function cl(){s.classList.remove('open');o.classList.remove('show');document.body.style.overflow='';}if(ob)ob.addEventListener('click',op);if(cb)cb.addEventListener('click',cl);if(o)o.addEventListener('click',cl);})();</script>
</body>
</html>
