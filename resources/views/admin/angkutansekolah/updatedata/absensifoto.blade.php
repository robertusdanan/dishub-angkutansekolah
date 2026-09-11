<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
<meta name="csrf-token" content="{{ csrf_token() }}"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Tambah Absensi Foto — Admin</title>
  <link rel="canonical" href="angkutan/admin/tambah-absen-foto"/>
  <link rel="icon" href="/favicon.ico"/>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/admin/admin-shell.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    /* ── Layout two-col ── */
    .page-layout {
      display: grid;
      grid-template-columns: 420px 1fr;
      gap: 24px;
      align-items: start;
    }
    @media (max-width: 900px) {
      .page-layout { grid-template-columns: 1fr; }
    }

    /* ── Form panel ── */
    .form-panel {
      background: var(--surface);
      border: 1.5px solid var(--border);
      border-radius: 16px;
      overflow: hidden;
      position: sticky;
      top: 20px;
    }
    .form-panel-hd {
      background: var(--navy);
      padding: 20px 24px;
      background-image: radial-gradient(ellipse 80% 50% at 50% -20%, rgba(59,130,246,.18) 0%, transparent 70%);
    }
    .form-panel-hd h2 { color: #fff; font-size: 15px; font-weight: 700; }
    .form-panel-hd p  { color: rgba(255,255,255,.5); font-size: 12px; margin-top: 3px; }
    .form-panel-bd { padding: 20px 24px; display: flex; flex-direction: column; gap: 14px; }

    /* ── Fields ── */
    .form-group { display: flex; flex-direction: column; gap: 5px; }
    .form-label { font-size: 11px; font-weight: 700; color: var(--text-3); letter-spacing: .06em; text-transform: uppercase; }
    .form-select, .form-input {
      width: 100%; background: var(--surface-2); border: 1.5px solid var(--border);
      border-radius: 9px; padding: 9px 12px; font-size: 13px;
      font-family: 'DM Sans', sans-serif; color: var(--text-1); outline: none;
      transition: border-color .15s, box-shadow .15s; appearance: none; -webkit-appearance: none;
      box-sizing: border-box;
    }
    .form-select {
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='13' height='13' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
      background-repeat: no-repeat; background-position: right 10px center; padding-right: 30px; cursor: pointer;
    }
    .form-select:focus, .form-input:focus {
      border-color: var(--accent); box-shadow: 0 0 0 3px var(--accent-glow); background: #fff;
    }

    /* ── Row fields ── */
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }

    /* ── File drop ── */
    .file-drop-area {
      border: 2px dashed var(--border); border-radius: 10px; padding: 16px 12px;
      text-align: center; cursor: pointer; transition: border-color .15s, background .15s;
      background: var(--surface-2); position: relative;
    }
    .file-drop-area:hover, .file-drop-area.drag-over { border-color: var(--accent); background: #eff6ff; }
    .file-drop-area input[type="file"] { position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%; }
    .file-drop-icon { font-size: 22px; margin-bottom: 5px; }
    .file-drop-text { font-size: 12px; color: var(--text-4); }
    .file-drop-text strong { color: var(--accent); }
    .file-preview { display: none; border-radius: 9px; overflow: hidden; border: 1.5px solid var(--border); }
    .file-preview img { width: 100%; max-height: 140px; object-fit: cover; display: block; }
    .file-preview-foot {
      padding: 7px 10px; font-size: 11.5px; color: var(--text-3); background: var(--surface-2);
      display: flex; align-items: center; justify-content: space-between; gap: 8px;
    }
    .file-preview-foot span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .btn-remove-foto {
      background: none; border: none; cursor: pointer; color: #ef4444; font-size: 11px;
      font-weight: 700; padding: 2px 7px; border-radius: 5px; transition: background .15s; flex-shrink: 0;
    }
    .btn-remove-foto:hover { background: #fef2f2; }

    /* ── Add to queue button ── */
    .btn-add-queue {
      width: 100%; padding: 11px; background: var(--accent); color: #fff; border: none;
      border-radius: 10px; font-family: 'DM Sans', sans-serif; font-size: 14px; font-weight: 600;
      cursor: pointer; transition: background .15s, box-shadow .15s;
      display: flex; align-items: center; justify-content: center; gap: 8px;
    }
    .btn-add-queue:hover { background: #2563eb; box-shadow: 0 4px 14px rgba(59,130,246,.3); }
    .btn-add-queue:disabled { opacity: .55; cursor: not-allowed; }

    /* ── Queue panel ── */
    .queue-panel {
      display: flex; flex-direction: column; gap: 16px;
    }
    .queue-header {
      display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;
    }
    .queue-title { font-size: 15px; font-weight: 700; color: var(--text-1); display: flex; align-items: center; gap: 10px; }
    .queue-badge {
      display: inline-flex; align-items: center; justify-content: center;
      background: var(--accent); color: #fff; font-size: 11px; font-weight: 700;
      border-radius: 99px; min-width: 22px; height: 22px; padding: 0 7px;
    }
    .queue-badge.empty { background: var(--surface-2); color: var(--text-4); border: 1.5px solid var(--border); }
    .btn-send-all {
      padding: 9px 20px; background: #15803d; color: #fff; border: none; border-radius: 10px;
      font-family: 'DM Sans', sans-serif; font-size: 13px; font-weight: 600; cursor: pointer;
      transition: background .15s, box-shadow .15s; display: flex; align-items: center; gap: 7px;
    }
    .btn-send-all:hover:not(:disabled) { background: #166534; box-shadow: 0 4px 14px rgba(21,128,61,.3); }
    .btn-send-all:disabled { opacity: .55; cursor: not-allowed; }

    /* ── Queue empty state ── */
    .queue-empty {
      background: var(--surface); border: 1.5px dashed var(--border); border-radius: 14px;
      padding: 52px 20px; text-align: center; color: var(--text-4);
    }
    .queue-empty svg { margin-bottom: 12px; opacity: .3; }
    .queue-empty p { font-size: 13px; margin-top: 4px; }
    .queue-empty .queue-empty-hint { font-size: 12px; color: var(--text-4); margin-top: 4px; }

    /* ── Queue list ── */
    .queue-list { display: flex; flex-direction: column; gap: 10px; }

    /* ── Queue item card ── */
    .queue-item {
      background: var(--surface); border: 1.5px solid var(--border); border-radius: 13px;
      overflow: hidden; transition: border-color .15s, box-shadow .15s;
    }
    .queue-item.success { border-color: #86efac; background: #f0fdf4; }
    .queue-item.error   { border-color: #fca5a5; background: #fff5f5; }
    .queue-item.sending { border-color: #93c5fd; background: #eff6ff; opacity: .8; }

    .qi-body { display: flex; gap: 0; }
    .qi-thumb {
      width: 90px; min-width: 90px; background: var(--surface-2);
      position: relative; overflow: hidden; flex-shrink: 0;
    }
    .qi-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; min-height: 80px; }
    .qi-thumb-empty {
      width: 100%; height: 100%; min-height: 80px; display: flex; align-items: center;
      justify-content: center; font-size: 22px; color: var(--text-4);
    }
    .qi-info { flex: 1; padding: 11px 14px; display: flex; flex-direction: column; gap: 5px; min-width: 0; }
    .qi-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 8px; }
    .qi-plat {
      font-family: 'DM Mono', monospace; font-size: 12.5px; font-weight: 700;
      background: var(--navy); color: #fff; padding: 2px 9px; border-radius: 5px; white-space: nowrap;
    }
    .qi-plat.bus  { background: #1d4ed8; }
    .qi-plat.mpu  { background: #6d28d9; }
    .qi-actions { display: flex; gap: 5px; flex-shrink: 0; }
    .qi-btn {
      width: 28px; height: 28px; border-radius: 7px; border: 1.5px solid var(--border);
      background: var(--surface-2); cursor: pointer; display: flex; align-items: center;
      justify-content: center; color: var(--text-3); transition: all .14s;
    }
    .qi-btn:hover { border-color: var(--accent); color: var(--accent); background: #eff6ff; }
    .qi-btn.del:hover { border-color: #fecaca; color: #ef4444; background: #fef2f2; }
    .qi-meta { display: flex; flex-wrap: wrap; gap: 5px; }
    .qi-tag {
      font-size: 11px; padding: 2px 7px; border-radius: 5px; font-weight: 600;
      background: var(--surface-2); color: var(--text-3); border: 1px solid var(--border);
      white-space: nowrap;
    }
    .qi-tag.pagi  { background: #fef9c3; color: #854d0e; border-color: #fde68a; }
    .qi-tag.siang { background: #fce7f3; color: #9d174d; border-color: #fbcfe8; }
    .qi-trayek { font-size: 12px; color: var(--text-2); font-weight: 600; }
    .qi-driver { font-size: 11.5px; color: var(--text-4); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

    /* ── Status overlay on card ── */
    .qi-status {
      padding: 7px 14px; font-size: 12px; font-weight: 600; display: none;
      align-items: center; gap: 7px; border-top: 1px solid var(--border);
    }
    .queue-item.success .qi-status { display: flex; color: #15803d; border-color: #bbf7d0; }
    .queue-item.error   .qi-status { display: flex; color: #dc2626; border-color: #fecaca; }
    .queue-item.sending .qi-status { display: flex; color: #1d4ed8; border-color: #bfdbfe; }

    /* ── Progress bar ── */
    .send-progress {
      background: var(--surface); border: 1.5px solid var(--border); border-radius: 13px;
      padding: 16px 20px; display: none; flex-direction: column; gap: 10px;
    }
    .send-progress.visible { display: flex; }
    .send-progress-label { font-size: 12.5px; font-weight: 600; color: var(--text-2); display: flex; justify-content: space-between; }
    .progress-bar-wrap { background: var(--surface-2); border-radius: 99px; height: 8px; overflow: hidden; border: 1px solid var(--border); }
    .progress-bar-fill { height: 100%; background: var(--accent); border-radius: 99px; transition: width .3s; }

    /* ── Summary ── */
    .send-summary {
      background: var(--surface); border: 1.5px solid var(--border); border-radius: 13px;
      padding: 16px 20px; display: none; gap: 14px; flex-direction: column;
    }
    .send-summary.visible { display: flex; }
    .summary-title { font-size: 13px; font-weight: 700; color: var(--text-1); }
    .summary-stats { display: flex; gap: 10px; flex-wrap: wrap; }
    .summary-stat {
      flex: 1; min-width: 80px; padding: 10px 14px; border-radius: 10px;
      text-align: center; border: 1.5px solid var(--border);
    }
    .summary-stat.ok   { background: #f0fdf4; border-color: #bbf7d0; }
    .summary-stat.fail { background: #fff5f5; border-color: #fecaca; }
    .summary-stat.dup  { background: #fffbeb; border-color: #fde68a; }
    .summary-stat-num  { font-size: 22px; font-weight: 700; }
    .summary-stat.ok   .summary-stat-num { color: #15803d; }
    .summary-stat.fail .summary-stat-num { color: #dc2626; }
    .summary-stat.dup  .summary-stat-num { color: #d97706; }
    .summary-stat-lbl  { font-size: 11px; color: var(--text-4); margin-top: 2px; }
    .btn-clear-done {
      padding: 8px 18px; background: transparent; border: 1.5px solid var(--border); border-radius: 9px;
      font-family: 'DM Sans', sans-serif; font-size: 12.5px; font-weight: 600; color: var(--text-2);
      cursor: pointer; transition: all .14s; align-self: flex-start;
    }
    .btn-clear-done:hover { background: var(--surface-2); border-color: var(--text-3); }

    @keyframes spin { to { transform: rotate(360deg); } }
    .spin { animation: spin .7s linear infinite; display: inline-block; }
  </style>
</head>
<body>
<div class="adm-shell">
  @include('admin.partials.sidebar', ['currentPage' => 'tambah_foto', 'currentModule' => 'angkutansekolah'])
  <main class="adm-main">
    <div class="adm-content">

      <div class="adm-page-header">
        <div>
          <h1 class="adm-page-title">Tambah Absensi Foto</h1>
          <p class="adm-page-subtitle">Input batch — tambahkan beberapa data ke antrian lalu kirim sekaligus</p>
        </div>
      </div>

      <div class="page-layout">

        <!-- ══ FORM PANEL (kiri / atas) ══ -->
        <div class="form-panel">
          <div class="form-panel-hd">
            <h2>Form Input</h2>
            <p>Isi data, tambahkan ke antrian</p>
          </div>
          <div class="form-panel-bd">

            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Transportasi</label>
                <select id="transportasi" class="form-select">
                  <option value="BUS">BUS</option>
                  <option value="MPU">MPU</option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">Sesi</label>
                <select id="sesi" class="form-select">
                  <option value="PAGI">Pagi</option>
                  <option value="SIANG">Siang</option>
                </select>
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">Trayek</label>
              <select id="trayek" class="form-select"></select>
            </div>

            <div class="form-group">
              <label class="form-label">Plat — Driver</label>
              <select id="platDriver" class="form-select"></select>
            </div>

            <div class="form-group">
              <label class="form-label">Tanggal Absensi</label>
              <input type="date" id="tanggalInput" class="form-input"/>
            </div>

            <div class="form-group">
              <label class="form-label">Foto Absensi</label>
              <div class="file-drop-area" id="fileDropArea">
                <input type="file" id="foto" accept="image/*" capture="environment"/>
                <div class="file-drop-icon">📷</div>
                <div class="file-drop-text">
                  <strong>Pilih foto</strong> atau kamera<br>
                  <span style="font-size:11px;">JPG / PNG — maks. 5MB</span>
                </div>
              </div>
              <div class="file-preview" id="filePreview">
                <img id="previewImg" src="" alt="Preview"/>
                <div class="file-preview-foot">
                  <span id="previewName"></span>
                  <button type="button" class="btn-remove-foto" onclick="removeFoto()">✕ Hapus</button>
                </div>
              </div>
            </div>

            <button class="btn-add-queue" id="btnAddQueue" type="button">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
              Tambah ke Antrian
            </button>

          </div>
        </div>

        <!-- ══ QUEUE PANEL (kanan / bawah) ══ -->
        <div class="queue-panel">

          <div class="queue-header">
            <div class="queue-title">
              Antrian Absensi
              <span class="queue-badge empty" id="queueBadge">0</span>
            </div>
            <button class="btn-send-all" id="btnSendAll" disabled>
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
              Kirim Semua
            </button>
          </div>

          <!-- Progress -->
          <div class="send-progress" id="sendProgress">
            <div class="send-progress-label">
              <span id="progressLabel">Mengirim...</span>
              <span id="progressPct">0%</span>
            </div>
            <div class="progress-bar-wrap">
              <div class="progress-bar-fill" id="progressFill" style="width:0%"></div>
            </div>
          </div>

          <!-- Summary -->
          <div class="send-summary" id="sendSummary">
            <div class="summary-title">Hasil Pengiriman</div>
            <div class="summary-stats">
              <div class="summary-stat ok">
                <div class="summary-stat-num" id="sumOk">0</div>
                <div class="summary-stat-lbl">Berhasil</div>
              </div>
              <div class="summary-stat dup">
                <div class="summary-stat-num" id="sumDup">0</div>
                <div class="summary-stat-lbl">Duplikat</div>
              </div>
              <div class="summary-stat fail">
                <div class="summary-stat-num" id="sumFail">0</div>
                <div class="summary-stat-lbl">Gagal</div>
              </div>
            </div>
            <button class="btn-clear-done" onclick="clearDone()">Bersihkan yang sudah terkirim</button>
          </div>

          <!-- Empty state -->
          <div class="queue-empty" id="queueEmpty">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><line x1="9" y1="12" x2="15" y2="12"/><line x1="9" y1="16" x2="12" y2="16"/></svg>
            <p style="font-weight:600;color:var(--text-3)">Antrian masih kosong</p>
            <p class="queue-empty-hint">Isi form di sebelah kiri, lalu klik "Tambah ke Antrian"</p>
          </div>

          <!-- List -->
          <div class="queue-list" id="queueList"></div>

        </div>
      </div>
    </div>
  </main>
</div>

<script src="/assets/admin/angkutansekolah/sb-secure.js"></script>
<script>
const JAM_SESI   = { pagi: '09:00:00', siang: '15:00:00' };
const ADMIN_USER = {!! json_encode($adminUser) !!};
const SB_URL     = {!! json_encode(config('services.supabase.url')) !!};
const SB_ANON    = {!! json_encode(config('services.supabase.anon_key')) !!};
const sbHdr      = () => ({ 'apikey': SB_ANON, 'Authorization': `Bearer ${SB_ANON}` });

let dataBus  = [];
let dataMpu  = [];
let queue    = [];   // { id, transportasi, trayek, platDriver, sesi, tanggal, fotoFile, fotoDataUrl, status:'pending'|'sending'|'success'|'error'|'duplicate', statusMsg }
let queueSeq = 0;

// ══════════════════════════════════════════
//  INIT
// ══════════════════════════════════════════
window.addEventListener('DOMContentLoaded', async () => {
  document.getElementById('tanggalInput').value = todayWIB();
  await loadData();
  updateTrayekDanDriver();
  setupFilePreview();
});

function todayWIB() {
  return new Date(new Date().toLocaleString('en-US',{timeZone:'Asia/Jakarta'}))
    .toISOString().split('T')[0];
}

// ── Load driver data ──────────────────────────────────────────────
async function loadData() {
  // Hanya trayek/plat/driver yang benar-benar dipakai untuk isi dropdown
  // di halaman ini — select=* sebelumnya ikut menarik kolom lain yang
  // tidak pernah dibaca (halaman ini dibuka tiap hari untuk absen foto).
  [dataBus, dataMpu] = await Promise.all([
    sbGetShared('driver_bus', 'select=trayek,plat,driver&order=trayek.asc'),
    sbGetShared('driver_mpu', 'select=trayek,plat,driver&order=trayek.asc'),
  ]);
}

// ── Update trayek + driver dropdowns ─────────────────────────────
function updateTrayekDanDriver() {
  const transportasi = document.getElementById('transportasi').value;
  const trayekSel    = document.getElementById('trayek');
  const platSel      = document.getElementById('platDriver');
  const data         = transportasi === 'BUS' ? dataBus : dataMpu;
  const trayekSet    = [...new Set(data.map(d => d.trayek))];

  trayekSel.innerHTML = trayekSet.map(tr => `<option value="${tr}">${tr}</option>`).join('');

  const fillPlat = () => {
    const sel = trayekSel.value;
    platSel.innerHTML = data
      .filter(d => d.trayek === sel)
      .map(d => `<option value="${d.plat} - ${d.driver}">${d.plat} — ${d.driver}</option>`)
      .join('');
  };
  trayekSel.onchange = fillPlat;
  fillPlat();
}

document.getElementById('transportasi').addEventListener('change', updateTrayekDanDriver);

// ── File preview ──────────────────────────────────────────────────
function setupFilePreview() {
  const input    = document.getElementById('foto');
  const dropArea = document.getElementById('fileDropArea');
  input.addEventListener('change', () => { if (input.files[0]) showPreview(input.files[0]); });
  dropArea.addEventListener('dragover', e => { e.preventDefault(); dropArea.classList.add('drag-over'); });
  dropArea.addEventListener('dragleave', () => dropArea.classList.remove('drag-over'));
  dropArea.addEventListener('drop', e => {
    e.preventDefault(); dropArea.classList.remove('drag-over');
    const file = e.dataTransfer.files[0];
    if (file?.type.startsWith('image/')) {
      const dt = new DataTransfer(); dt.items.add(file); input.files = dt.files;
      showPreview(file);
    }
  });
}

function showPreview(file) {
  const reader = new FileReader();
  reader.onload = e => {
    document.getElementById('previewImg').src = e.target.result;
    document.getElementById('previewName').textContent = file.name + ' (' + (file.size/1024).toFixed(1) + ' KB)';
    document.getElementById('filePreview').style.display  = 'block';
    document.getElementById('fileDropArea').style.display = 'none';
  };
  reader.readAsDataURL(file);
}

function removeFoto() {
  document.getElementById('foto').value = '';
  document.getElementById('filePreview').style.display  = 'none';
  document.getElementById('fileDropArea').style.display = 'block';
  document.getElementById('previewImg').src = '';
}

// ══════════════════════════════════════════
//  ANTRIAN
// ══════════════════════════════════════════

// ── Tambah ke antrian ─────────────────────────────────────────────
document.getElementById('btnAddQueue').addEventListener('click', () => {
  const transportasi = document.getElementById('transportasi').value.toUpperCase();
  const trayek       = document.getElementById('trayek').value.trim();
  const platDriver   = document.getElementById('platDriver').value.trim();
  const sesi         = document.getElementById('sesi').value.trim();
  const tanggal      = document.getElementById('tanggalInput').value;
  const fileInput    = document.getElementById('foto');

  if (!trayek || !platDriver || !sesi || !tanggal) {
    showFormError('Lengkapi semua field sebelum menambah ke antrian.'); return;
  }
  if (!fileInput.files[0]) {
    showFormError('Pilih foto terlebih dahulu.'); return;
  }

  const file   = fileInput.files[0];
  const dataUrl = document.getElementById('previewImg').src;

  // Cek duplikat dalam antrian (same platDriver + sesi + tanggal)
  const isDupInQueue = queue.some(q =>
    q.platDriver === platDriver && q.sesi === sesi && q.tanggal === tanggal && q.status !== 'error'
  );
  if (isDupInQueue) {
    showFormError(`${platDriver} sudah ada di antrian untuk sesi ${sesi} tanggal ${tanggal}.`); return;
  }

  const item = { id: ++queueSeq, transportasi, trayek, platDriver, sesi, tanggal, fotoFile: file, fotoDataUrl: dataUrl, status: 'pending', statusMsg: '' };
  queue.push(item);
  renderQueue();
  resetForm();
});

function showFormError(msg) {
  Swal.fire({ icon: 'warning', title: 'Periksa Data', text: msg, confirmButtonColor: '#f59e0b', toast: false });
}

// ── Reset form setelah tambah ─────────────────────────────────────
function resetForm() {
  removeFoto();
  // Jangan reset tanggal/sesi/transportasi — biarkan untuk entri berikutnya
}

// ── Hapus dari antrian ────────────────────────────────────────────
function removeFromQueue(id) {
  queue = queue.filter(q => q.id !== id);
  renderQueue();
}

// ── Render antrian ────────────────────────────────────────────────
function renderQueue() {
  const listEl  = document.getElementById('queueList');
  const emptyEl = document.getElementById('queueEmpty');
  const badge   = document.getElementById('queueBadge');
  const sendBtn = document.getElementById('btnSendAll');

  const pending = queue.filter(q => q.status === 'pending').length;
  badge.textContent = queue.length;
  badge.className   = queue.length > 0 ? 'queue-badge' : 'queue-badge empty';
  sendBtn.disabled  = pending === 0;

  emptyEl.style.display = queue.length === 0 ? '' : 'none';

  listEl.innerHTML = queue.map(item => {
    const sesiBadge = item.sesi.toLowerCase();
    const srcClass  = item.transportasi.toLowerCase();
    const platShort = item.platDriver.split(' - ')[0] || item.platDriver;
    const driverName = item.platDriver.split(' - ').slice(1).join(' - ') || '';

    let statusHtml = '';
    let stateClass = '';
    if (item.status === 'sending') {
      stateClass = 'sending';
      statusHtml = `<div class="qi-status"><span class="spin">⏳</span> Mengirim...</div>`;
    } else if (item.status === 'success') {
      stateClass = 'success';
      statusHtml = `<div class="qi-status">✅ Berhasil disimpan</div>`;
    } else if (item.status === 'duplicate') {
      stateClass = 'error';
      statusHtml = `<div class="qi-status">⚠️ Duplikat — sudah absen ${item.sesi} di tanggal ini</div>`;
    } else if (item.status === 'error') {
      stateClass = 'error';
      statusHtml = `<div class="qi-status">❌ ${esc(item.statusMsg || 'Gagal')}</div>`;
    }

    const canDelete = item.status === 'pending' || item.status === 'error' || item.status === 'duplicate';
    const canRetry  = item.status === 'error';

    return `<div class="queue-item ${stateClass}" id="qi-${item.id}">
      <div class="qi-body">
        <div class="qi-thumb">
          ${item.fotoDataUrl
            ? `<img src="${item.fotoDataUrl}" alt="foto"/>`
            : `<div class="qi-thumb-empty">📷</div>`
          }
        </div>
        <div class="qi-info">
          <div class="qi-top">
            <span class="qi-plat ${srcClass}">${esc(platShort)}</span>
            <div class="qi-actions">
              ${canRetry ? `<button class="qi-btn" onclick="retrySingle(${item.id})" title="Coba lagi">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-4.5"/></svg>
              </button>` : ''}
              ${canDelete ? `<button class="qi-btn del" onclick="removeFromQueue(${item.id})" title="Hapus">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
              </button>` : ''}
            </div>
          </div>
          <div class="qi-meta">
            <span class="qi-tag">${esc(item.transportasi)}</span>
            <span class="qi-tag ${sesiBadge}">${esc(item.sesi)}</span>
            <span class="qi-tag">${esc(item.tanggal)}</span>
          </div>
          <div class="qi-trayek">${esc(item.trayek)}</div>
          ${driverName ? `<div class="qi-driver">${esc(driverName)}</div>` : ''}
        </div>
      </div>
      ${statusHtml}
    </div>`;
  }).join('');
}

// ══════════════════════════════════════════
//  KIRIM SEMUA
// ══════════════════════════════════════════

document.getElementById('btnSendAll').addEventListener('click', sendAll);

async function sendAll() {
  const toSend = queue.filter(q => q.status === 'pending');
  if (!toSend.length) return;

  document.getElementById('btnSendAll').disabled    = true;
  document.getElementById('btnAddQueue').disabled   = true;
  document.getElementById('sendProgress').classList.add('visible');
  document.getElementById('sendSummary').classList.remove('visible');

  let ok = 0, dup = 0, fail = 0;

  for (let i = 0; i < toSend.length; i++) {
    const item = toSend[i];
    setItemStatus(item.id, 'sending', '');
    updateProgress(i, toSend.length, `Mengirim ${i+1} dari ${toSend.length}...`);
    renderQueue();

    const result = await sendSingle(item);
    if (result === 'success')   { ok++;  setItemStatus(item.id, 'success', ''); }
    else if (result === 'dup')  { dup++; setItemStatus(item.id, 'duplicate', ''); }
    else                        { fail++; setItemStatus(item.id, 'error', result); }
    renderQueue();
  }

  updateProgress(toSend.length, toSend.length, 'Selesai!');
  setTimeout(() => document.getElementById('sendProgress').classList.remove('visible'), 800);

  document.getElementById('sumOk').textContent   = ok;
  document.getElementById('sumDup').textContent  = dup;
  document.getElementById('sumFail').textContent = fail;
  document.getElementById('sendSummary').classList.add('visible');

  document.getElementById('btnAddQueue').disabled = false;
  renderQueue(); // update send button state
}

// ── Retry satu item ───────────────────────────────────────────────
async function retrySingle(id) {
  const item = queue.find(q => q.id === id);
  if (!item) return;
  setItemStatus(id, 'sending', '');
  renderQueue();
  const result = await sendSingle(item);
  if (result === 'success')  setItemStatus(id, 'success', '');
  else if (result === 'dup') setItemStatus(id, 'duplicate', '');
  else                       setItemStatus(id, 'error', result);
  renderQueue();
}

// ── Kirim satu item → 'success' | 'dup' | error message ──────────
async function sendSingle(item) {
  try {
    const tanggal    = item.tanggal;
    const startOfDay = new Date(`${tanggal}T00:00:00+07:00`).toISOString();
    const endOfDay   = new Date(`${tanggal}T23:59:59.999+07:00`).toISOString();

    // 1. Cek duplikat
    const dupRes  = await fetch('/admin/api/angkutansekolah/upload-foto?action=cek_duplikat', {
      method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
      body:   JSON.stringify({ trayek: item.trayek, plat_driver: item.platDriver, sesi: item.sesi, start: startOfDay, end: endOfDay }),
    });
    const dupRows = await dupRes.json();
    if (Array.isArray(dupRows) && dupRows.length > 0) return 'dup';

    // 2. Resize + upload foto
    const fotoBlob = await resizeImage(item.fotoFile);
    const formData = new FormData();
    formData.append('action', 'upload');
    formData.append('foto', fotoBlob, `absen_${Date.now()}_${item.id}.jpg`);

    const uploadRes = await fetch('/admin/api/angkutansekolah/upload-foto?action=upload', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }, body: formData });
    if (!uploadRes.ok) throw new Error(`Upload gagal (${uploadRes.status})`);
    // PENTING: pakai nama file HASIL server (server yang generate nama
    // final demi keamanan — lihat AbsensiFotoUploadController), BUKAN
    // ditebak di client, supaya kolom `foto` di database selalu cocok
    // dengan file yang benar-benar tersimpan di disk.
    const uploadJson = await uploadRes.json();
    const fotoName = uploadJson.filename;

    // 3. Insert record
    const jamDefault = item.sesi.toLowerCase() === 'pagi' ? '09:00:00' : '15:00:00';
    const waktu      = new Date(`${tanggal}T${jamDefault}+07:00`).toISOString();

    const insRes = await fetch('/admin/api/angkutansekolah/upload-foto?action=insert', {
      method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
      body: JSON.stringify({
        id: crypto.randomUUID(), nama: ADMIN_USER,
        waktu, trayek: item.trayek, plat_driver: item.platDriver,
        transportasi: item.transportasi, sesi: item.sesi, foto: fotoName,
      }),
    });
    if (!insRes.ok) { const t = await insRes.text(); throw new Error(`Simpan gagal (${insRes.status}): ${t}`); }

    return 'success';
  } catch (err) {
    console.error(err);
    return err.message || 'Terjadi kesalahan';
  }
}

// ── Helpers ───────────────────────────────────────────────────────
function setItemStatus(id, status, msg) {
  const item = queue.find(q => q.id === id);
  if (item) { item.status = status; item.statusMsg = msg; }
}

function updateProgress(done, total, label) {
  const pct = total ? Math.round((done / total) * 100) : 0;
  document.getElementById('progressLabel').textContent = label;
  document.getElementById('progressPct').textContent   = pct + '%';
  document.getElementById('progressFill').style.width  = pct + '%';
}

function clearDone() {
  queue = queue.filter(q => q.status === 'pending' || q.status === 'error');
  document.getElementById('sendSummary').classList.remove('visible');
  renderQueue();
}

function resizeImage(file, maxSize = 720) {
  return new Promise((resolve, reject) => {
    const img = new Image(), reader = new FileReader();
    reader.onload  = e => { img.src = e.target.result; };
    reader.onerror = reject;
    img.onload = () => {
      const canvas = document.createElement('canvas'), ctx = canvas.getContext('2d');
      const ratio  = Math.min(maxSize / img.width, maxSize / img.height, 1);
      canvas.width = img.width * ratio; canvas.height = img.height * ratio;
      ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
      canvas.toBlob(blob => resolve(blob), 'image/jpeg', 0.8);
    };
    reader.readAsDataURL(file);
  });
}

function esc(s) {
  return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>

<script>
(function () {
  const s = document.getElementById('admSidebar'),
        o = document.getElementById('admOverlay'),
        ob = document.getElementById('sidebarOpen'),
        cb = document.getElementById('sidebarClose');
  function op() { s.classList.add('open'); o.classList.add('show'); document.body.style.overflow = 'hidden'; }
  function cl() { s.classList.remove('open'); o.classList.remove('show'); document.body.style.overflow = ''; }
  if (ob) ob.addEventListener('click', op);
  if (cb) cb.addEventListener('click', cl);
  if (o)  o.addEventListener('click', cl);
})();
</script>
</body>
</html>