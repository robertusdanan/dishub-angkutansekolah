<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
<meta name="csrf-token" content="{{ csrf_token() }}"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>RFID Writer — Admin</title>
  <link rel="canonical" href="/admin/rfid-writer"/>
  <link rel="icon" href="/favicon.ico"/>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/admin/admin-shell.css">
  <style>
    /* ── Layout ── */
    .adm-shell { display:flex!important; }
    .adm-main  { flex:1; min-width:0; }

    /* ── Two-column layout ── */
    .rfid-layout {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
      align-items: start;
    }
    @media (max-width: 900px) {
      .rfid-layout { grid-template-columns: 1fr; }
    }

    /* ── Search & select panel ── */
    .panel-title {
      font-size: 13px; font-weight: 700; text-transform: uppercase;
      letter-spacing: .06em; color: var(--text-3);
      padding: 16px 20px 10px;
      border-bottom: 1px solid var(--border);
    }

    /* ── Search bar ── */
    .search-wrap {
      position: relative; padding: 14px 16px;
      border-bottom: 1px solid var(--border);
    }
    .search-wrap svg {
      position: absolute; left: 27px; top: 50%; transform: translateY(-50%);
      color: var(--text-4); pointer-events: none;
    }
    .search-input {
      width: 100%; padding: 9px 12px 9px 36px;
      border: 1.5px solid var(--border); border-radius: 9px;
      font-family: 'DM Sans', sans-serif; font-size: 13.5px;
      color: var(--text-1); background: var(--surface-2); outline: none;
      transition: border-color .15s, box-shadow .15s;
    }
    .search-input:focus {
      border-color: var(--accent); box-shadow: 0 0 0 3px var(--accent-glow);
      background: #fff;
    }

    /* ── Student list ── */
    .student-list {
      max-height: 480px; overflow-y: auto;
    }
    .student-item {
      display: flex; align-items: center; gap: 12px;
      padding: 11px 16px; cursor: pointer;
      border-bottom: 1px solid var(--border);
      transition: background .12s;
    }
    .student-item:last-child { border-bottom: none; }
    .student-item:hover { background: var(--surface-2); }
    .student-item.selected {
      background: #eff6ff; border-left: 3px solid var(--accent);
    }
    .student-avatar {
      width: 36px; height: 36px; border-radius: 50%;
      background: linear-gradient(135deg, #dbeafe, #bfdbfe);
      display: flex; align-items: center; justify-content: center;
      font-size: 13px; font-weight: 700; color: #1d4ed8; flex-shrink: 0;
      border: 1.5px solid #bfdbfe;
    }
    .student-avatar.female {
      background: linear-gradient(135deg, #fce7f3, #fbcfe8);
      color: #9d174d; border-color: #fbcfe8;
    }
    .student-info { flex: 1; min-width: 0; }
    .student-name {
      font-size: 13.5px; font-weight: 600; color: var(--text-1);
      white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .student-meta {
      font-size: 11.5px; color: var(--text-4); font-family: 'DM Mono', monospace;
      margin-top: 2px;
    }
    .student-school {
      font-size: 11px; color: var(--text-3); margin-top: 2px;
      white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .student-check {
      width: 20px; height: 20px; border-radius: 50%;
      border: 2px solid var(--border); display: flex;
      align-items: center; justify-content: center;
      flex-shrink: 0; transition: all .15s;
    }
    .student-item.selected .student-check {
      background: var(--accent); border-color: var(--accent);
    }
    .student-check svg { display: none; }
    .student-item.selected .student-check svg { display: block; }

    /* ── Empty + loading states ── */
    .list-empty {
      padding: 48px 20px; text-align: center;
      color: var(--text-4); font-size: 13.5px;
    }
    .list-empty svg { margin-bottom: 10px; opacity: .4; }
    .list-loading {
      padding: 40px; display: flex; align-items: center;
      justify-content: center; gap: 10px;
      color: var(--text-4); font-size: 13px;
    }
    .spinner {
      width: 20px; height: 20px; border: 2.5px solid var(--border);
      border-top-color: var(--accent); border-radius: 50%;
      animation: spin .7s linear infinite; flex-shrink: 0;
    }
    .spinner-sm {
      width: 14px; height: 14px; border: 2px solid rgba(255,255,255,.3);
      border-top-color: #fff; border-radius: 50%;
      animation: spin .7s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }

    /* ── Writer panel ── */
    .writer-card {
      background: var(--surface); border: 1.5px solid var(--border);
      border-radius: 16px; overflow: hidden;
    }

    /* Selected student preview */
    .selected-preview {
      padding: 20px; border-bottom: 1px solid var(--border);
    }
    .preview-label {
      font-size: 10.5px; font-weight: 700; text-transform: uppercase;
      letter-spacing: .07em; color: var(--text-4); margin-bottom: 12px;
    }
    .preview-empty {
      background: var(--surface-2); border: 1.5px dashed var(--border);
      border-radius: 12px; padding: 24px; text-align: center;
      color: var(--text-4); font-size: 13px;
    }
    .preview-empty svg { opacity: .35; margin-bottom: 8px; }

    .preview-card {
      background: linear-gradient(135deg, #1e3a5f 0%, #1d4ed8 100%);
      border-radius: 14px; padding: 18px 20px; color: #fff;
      position: relative; overflow: hidden;
    }
    .preview-card::before {
      content: '';
      position: absolute; top: -30px; right: -30px;
      width: 120px; height: 120px; border-radius: 50%;
      background: rgba(255,255,255,.06);
    }
    .preview-card::after {
      content: '';
      position: absolute; bottom: -40px; left: -20px;
      width: 140px; height: 140px; border-radius: 50%;
      background: rgba(255,255,255,.04);
    }
    .preview-card-name {
      font-size: 16px; font-weight: 700; margin-bottom: 4px; position: relative;
    }
    .preview-card-school {
      font-size: 11.5px; opacity: .75; margin-bottom: 14px; position: relative;
    }
    .preview-nik-label {
      font-size: 9.5px; font-weight: 700; letter-spacing: .1em;
      text-transform: uppercase; opacity: .6; position: relative;
    }
    .preview-nik-value {
      font-family: 'DM Mono', monospace; font-size: 15px; font-weight: 600;
      letter-spacing: .12em; margin-top: 2px; position: relative;
    }
    .preview-badges {
      position: absolute; top: 16px; right: 18px; display: flex;
      flex-direction: column; align-items: flex-end; gap: 4px;
    }
    .preview-badge {
      font-size: 9.5px; font-weight: 700; padding: 2px 7px;
      border-radius: 99px; letter-spacing: .04em;
    }
    .badge-jk {
      background: rgba(255,255,255,.18); color: rgba(255,255,255,.9);
    }
    .badge-dom {
      background: rgba(255,255,255,.12); color: rgba(255,255,255,.75);
    }
    .nfc-icon {
      position: absolute; bottom: 14px; right: 18px; opacity: .18;
    }

    /* ── Write section ── */
    .write-section { padding: 20px; }

    .device-status {
      display: flex; align-items: center; gap: 10px;
      padding: 11px 14px; border-radius: 10px;
      border: 1.5px solid var(--border); background: var(--surface-2);
      font-size: 13px; margin-bottom: 16px; transition: all .25s;
    }
    .device-status.connected {
      background: #f0fdf4; border-color: #bbf7d0;
    }
    .device-status.error {
      background: #fff0f0; border-color: #fecaca;
    }
    .device-status.writing {
      background: #eff6ff; border-color: #bfdbfe;
    }
    .status-dot {
      width: 9px; height: 9px; border-radius: 50%; flex-shrink: 0;
      background: var(--text-4);
    }
    .device-status.connected .status-dot { background: #22c55e; }
    .device-status.error     .status-dot { background: #ef4444; }
    .device-status.writing   .status-dot {
      background: var(--accent);
      animation: pulseDot 1s ease-in-out infinite;
    }
    @keyframes pulseDot {
      0%, 100% { opacity: 1; transform: scale(1); }
      50%       { opacity: 0.3; transform: scale(0.6); }
    }
    .status-text { flex: 1; color: var(--text-2); }
    .device-status.connected .status-text { color: #15803d; font-weight: 500; }
    .device-status.error     .status-text { color: #b91c1c; }
    .device-status.writing   .status-text { color: #1d4ed8; font-weight: 500; }

    /* ── Write button ── */
    .write-btn {
      width: 100%; padding: 13px 20px;
      background: var(--accent); color: #fff; border: none;
      border-radius: 12px; font-family: 'DM Sans', sans-serif;
      font-size: 15px; font-weight: 700; cursor: pointer;
      display: flex; align-items: center; justify-content: center; gap: 10px;
      transition: all .18s; letter-spacing: .01em;
    }
    .write-btn:hover:not(:disabled) {
      background: #2563eb; box-shadow: 0 6px 20px rgba(59,130,246,.4);
      transform: translateY(-1px);
    }
    .write-btn:active:not(:disabled) { transform: translateY(0); }
    .write-btn:disabled {
      opacity: .5; cursor: not-allowed; transform: none; box-shadow: none;
    }
    .write-btn.writing {
      background: #1d4ed8; pointer-events: none;
    }
    .write-btn.success {
      background: var(--emerald);
    }
    .write-btn.fail {
      background: var(--rose);
    }

    /* ── Payload preview ── */
    .payload-box {
      margin-top: 16px; padding: 14px;
      background: var(--navy); border-radius: 10px;
      font-family: 'DM Mono', monospace; font-size: 12px; color: #93c5fd;
      line-height: 1.7; display: none;
    }
    .payload-box.show { display: block; }
    .payload-key { color: #64748b; }
    .payload-val { color: #e2e8f0; }
    .payload-nik { color: #60a5fa; font-weight: 700; font-size: 13px; }

    /* ── Log ── */
    .write-log {
      margin-top: 16px; max-height: 180px; overflow-y: auto;
      border: 1px solid var(--border); border-radius: 10px;
      background: #0b1120;
    }
    .log-item {
      display: flex; align-items: flex-start; gap: 10px;
      padding: 8px 12px; border-bottom: 1px solid rgba(255,255,255,.04);
      font-size: 12px; font-family: 'DM Mono', monospace;
    }
    .log-item:last-child { border-bottom: none; }
    .log-time { color: #475569; flex-shrink: 0; }
    .log-msg  { color: #94a3b8; flex: 1; }
    .log-item.log-ok   .log-msg { color: #4ade80; }
    .log-item.log-err  .log-msg { color: #f87171; }
    .log-item.log-info .log-msg { color: #60a5fa; }
    .log-item.log-warn .log-msg { color: #fbbf24; }
    .log-empty {
      padding: 16px 12px; font-size: 12px; color: #475569;
      font-family: 'DM Mono', monospace; text-align: center;
    }

    /* ── How-to guide ── */
    .how-to {
      margin-top: 20px; padding: 16px;
      background: #fffbeb; border: 1.5px solid #fde68a; border-radius: 12px;
    }
    .how-to-title {
      display: flex; align-items: center; gap: 7px;
      font-size: 12.5px; font-weight: 700; color: #92400e; margin-bottom: 10px;
    }
    .how-to ol {
      padding-left: 18px; margin: 0;
      font-size: 12.5px; color: #78350f; line-height: 1.75;
    }
    .how-to li { margin-bottom: 2px; }
    .how-to code {
      background: #fef3c7; padding: 1px 5px; border-radius: 4px;
      font-family: 'DM Mono', monospace; font-size: 11px;
    }

    /* ── Toast ── */
    #toast {
      position: fixed; bottom: 24px; right: 24px;
      background: var(--navy); color: #fff;
      padding: 12px 20px; border-radius: 10px;
      font-size: 13.5px; font-weight: 500;
      box-shadow: 0 8px 24px rgba(0,0,0,.25); z-index: 9999;
      transform: translateY(80px); opacity: 0;
      transition: all .3s cubic-bezier(.22,.61,.36,1);
      max-width: 320px;
    }
    #toast.show  { transform: translateY(0); opacity: 1; }
    #toast.success { background: #15803d; }
    #toast.error   { background: #b91c1c; }
    #toast.warning { background: #b45309; }

    /* ── Filter bar ── */
    .filter-row {
      display: flex; gap: 8px; align-items: center; flex-wrap: wrap;
      padding: 10px 16px; border-bottom: 1px solid var(--border);
    }
    .filter-select-wrap { position: relative; }
    .filter-select-wrap::after {
      content: '▾'; position: absolute; right: 9px; top: 50%;
      transform: translateY(-50%); color: var(--text-4);
      font-size: 11px; pointer-events: none;
    }
    .filter-select-wrap select {
      padding: 6px 24px 6px 10px; border: 1.5px solid var(--border);
      border-radius: 8px; font-family: 'DM Sans', sans-serif;
      font-size: 12.5px; color: var(--text-1); background: var(--surface-2);
      outline: none; cursor: pointer; appearance: none;
      transition: border-color .15s;
    }
    .filter-select-wrap select:focus { border-color: var(--accent); }
    .filter-count {
      font-size: 11.5px; color: var(--text-4); margin-left: auto;
    }

    /* ── Pagination mini ── */
    .mini-pg {
      display: flex; align-items: center; justify-content: space-between;
      padding: 10px 14px; border-top: 1px solid var(--border);
      font-size: 12px; color: var(--text-4);
    }
    .mini-pg-btns { display: flex; gap: 4px; }
    .mini-pg-btn {
      width: 28px; height: 28px; border-radius: 6px;
      border: 1.5px solid var(--border); background: var(--surface);
      color: var(--text-3); font-size: 12px; cursor: pointer;
      display: flex; align-items: center; justify-content: center;
      transition: all .12s; font-family: 'DM Sans', sans-serif;
    }
    .mini-pg-btn:hover:not(:disabled) { border-color: var(--accent); color: var(--accent); }
    .mini-pg-btn:disabled { opacity: .35; cursor: default; }
  </style>
</head>
<body>
<div class="adm-shell">
  @include('admin.partials.sidebar', ['currentPage' => 'rfid_writer', 'currentModule' => 'angkutansekolah'])

  <main class="adm-main">
    <div class="adm-content">

      <!-- Page Header -->
      <div class="adm-page-header">
        <div>
          <h1 class="adm-page-title">RFID Writer</h1>
          <p class="adm-page-subtitle">Tulis data NIK siswa ke kartu RFID menggunakan ACR122U</p>
        </div>
        <div style="display:flex;align-items:center;gap:8px;">
          <div id="readerStatus" class="device-status" style="width:auto;margin-bottom:0;padding:8px 14px;font-size:12.5px;">
            <div class="status-dot"></div>
            <span class="status-text">Mendeteksi reader...</span>
          </div>
        </div>
      </div>

      <!-- Main layout -->
      <div class="rfid-layout">

        <!-- LEFT: Student selector -->
        <div class="card" style="overflow:hidden;padding:0;">
          <div class="panel-title">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline;vertical-align:middle;margin-right:6px;"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            Pilih Siswa
          </div>

          <!-- Search -->
          <div class="search-wrap">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input
              type="text" id="studentSearch"
              class="search-input"
              placeholder="Cari nama atau NIK..."
              autocomplete="off"
              oninput="onSearch()"
            />
          </div>

          <!-- Filters -->
          <div class="filter-row">
            <div class="filter-select-wrap">
              <select id="filterSekolah" onchange="onFilter()">
                <option value="">Semua Sekolah</option>
              </select>
            </div>
            <div class="filter-select-wrap">
              <select id="filterJk" onchange="onFilter()">
                <option value="">Semua JK</option>
                <option value="Laki-laki">Laki-laki</option>
                <option value="Perempuan">Perempuan</option>
              </select>
            </div>
            <span class="filter-count" id="listCount"></span>
          </div>

          <!-- List -->
          <div class="student-list" id="studentList">
            <div class="list-loading" id="listLoading">
              <div class="spinner"></div> Memuat data siswa...
            </div>
          </div>

          <!-- Pagination -->
          <div class="mini-pg" id="miniPg" style="display:none;">
            <span id="pgInfo" style="color:var(--text-4);font-size:12px;"></span>
            <div class="mini-pg-btns">
              <button class="mini-pg-btn" id="pgPrev" onclick="changePage(-1)" disabled>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
              </button>
              <button class="mini-pg-btn" id="pgNext" onclick="changePage(1)">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
              </button>
            </div>
          </div>
        </div>

        <!-- RIGHT: Writer panel -->
        <div class="writer-card">
          <div class="panel-title">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline;vertical-align:middle;margin-right:6px;"><path d="M12 22s8-4.5 8-11.8A8 8 0 0 0 4 10.2C4 17.5 12 22 12 22z"/><circle cx="12" cy="10" r="3"/></svg>
            Write ke Kartu RFID
          </div>

          <!-- Selected student preview -->
          <div class="selected-preview">
            <div class="preview-label">Siswa yang dipilih</div>
            <div id="previewEmpty" class="preview-empty">
              <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
              <div>Pilih siswa dari daftar kiri</div>
              <div style="font-size:11px;margin-top:4px;">Data NIK akan diwrite ke kartu RFID</div>
            </div>
            <div id="previewCard" class="preview-card" style="display:none;">
              <div class="preview-badges">
                <span class="preview-badge badge-jk" id="prevJk">—</span>
                <span class="preview-badge badge-dom" id="prevDom">—</span>
              </div>
              <div class="preview-card-name" id="prevNama">—</div>
              <div class="preview-card-school" id="prevSekolah">—</div>
              <div class="preview-nik-label">NIK</div>
              <div class="preview-nik-value" id="prevNIK">— — — — — — — —</div>
              <div class="nfc-icon">
                <svg width="52" height="52" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.2">
                  <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"/>
                  <path d="M8.5 8.5 C8.5 8.5 7 10 7 12 C7 14 8.5 15.5 8.5 15.5"/>
                  <path d="M15.5 8.5 C15.5 8.5 17 10 17 12 C17 14 15.5 15.5 15.5 15.5"/>
                  <path d="M10.5 10.5 C10.5 10.5 9.5 11 9.5 12 C9.5 13 10.5 13.5 10.5 13.5"/>
                  <path d="M13.5 10.5 C13.5 10.5 14.5 11 14.5 12 C14.5 13 13.5 13.5 13.5 13.5"/>
                  <circle cx="12" cy="12" r="1.2" fill="white" stroke="none"/>
                </svg>
              </div>
            </div>
          </div>

          <!-- Write section -->
          <div class="write-section">

            <!-- Reader/writer status -->
            <div id="writeStatus" class="device-status">
              <div class="status-dot"></div>
              <span class="status-text" id="writeStatusText">Mendeteksi perangkat ACR122U...</span>
            </div>

            <!-- Write button -->
            <button id="writeBtn" class="write-btn" onclick="doWrite()" disabled>
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <path d="M12 22s8-4.5 8-11.8A8 8 0 0 0 4 10.2C4 17.5 12 22 12 22z"/>
                <circle cx="12" cy="10" r="3"/>
              </svg>
              <span id="writeBtnText">Tempel Kartu & Write NIK</span>
            </button>

            <!-- Payload preview -->
            <div id="payloadBox" class="payload-box">
              <div><span class="payload-key">// Data yang akan ditulis ke RFID</span></div>
              <div><span class="payload-key">nik      : </span><span class="payload-nik" id="plNik">—</span></div>
              <div><span class="payload-key">nama     : </span><span class="payload-val" id="plNama">—</span></div>
              <div><span class="payload-key">sekolah  : </span><span class="payload-val" id="plSekolah">—</span></div>
              <div><span class="payload-key">domisili : </span><span class="payload-val" id="plDomisili">—</span></div>
              <div><span class="payload-key">format   : </span><span class="payload-val">NDEF / Plain Text (UTF-8)</span></div>
            </div>

            <!-- Write log -->
            <div class="write-log" id="writeLog">
              <div class="log-empty" id="logEmpty">Log aktivitas write akan muncul di sini</div>
            </div>

            <!-- How-to guide -->
            <div class="how-to">
              <div class="how-to-title">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#92400e" stroke-width="2.2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                Cara Penggunaan
              </div>
              <ol>
                <li>Pastikan <strong>ACR122U</strong> sudah terhubung ke PC via USB</li>
                <li>Install <strong>RFID Bridge</strong> (agen lokal) di PC — download di <code>localhost:7777/setup</code></li>
                <li>Cari dan <strong>pilih siswa</strong> dari daftar di sebelah kiri</li>
                <li>Tempelkan <strong>kartu RFID kosong</strong> ke reader ACR122U</li>
                <li>Klik tombol <strong>"Tempel Kartu & Write NIK"</strong></li>
                <li>Tunggu hingga lampu hijau dan bunyi <em>beep</em> — write berhasil</li>
              </ol>
            </div>

          </div><!-- /write-section -->
        </div><!-- /writer-card -->

      </div><!-- /rfid-layout -->

    </div><!-- /adm-content -->
  </main>
</div>

<div id="toast"></div>

<script src="/assets/admin/angkutansekolah/sb-secure.js"></script>
<script>
/* ══════════════════════════════════════════════════════════════════
   RFID Writer — halaman admin
   Alat: ACS ACR122U-A9 (USB NFC/RFID Reader-Writer)
   Protokol: RFID Bridge agent berjalan di localhost:7777
             menggantikan akses Web NFC API (terbatas ke Android)
   Data yang ditulis: NIK siswa (16 digit) + nama
   ══════════════════════════════════════════════════════════════════ */

const SB_URL  = {!! json_encode(config('services.supabase.url')) !!};
const SB_ANON = {!! json_encode(config('services.supabase.anon_key')) !!};
const sbHdr   = () => ({ 'apikey': SB_ANON, 'Authorization': `Bearer ${SB_ANON}` });

/* ── Konstanta ── */
const BRIDGE_URL   = 'http://localhost:7777'; // RFID Bridge agent
const PAGE_SIZE    = 15;

/* ── State ── */
let currentStudents = [];  // hasil halaman yang sedang tampil (maks PAGE_SIZE baris)
let totalCount       = 0;  // total hasil sesuai filter (dari Supabase, bukan dihitung di browser)
let currentPage       = 1;
let selectedSiswa     = null;
let bridgeOnline       = false;
let pollTimer          = null;

/* ═════════════════════════════════════════════════
   1.  CARI DATA SISWA (server-side search + pagination)
   ─────────────────────────────────────────────────
   PENTING: TIDAK PERNAH mengunduh seluruh tabel siswa.
   Hanya ambil maks PAGE_SIZE baris yang cocok dengan
   pencarian/filter aktif — supaya tetap ringan walau
   data sudah puluhan ribu baris.
   ═════════════════════════════════════════════════ */
async function searchStudents() {
  const q    = document.getElementById('studentSearch').value.trim();
  const fSek = document.getElementById('filterSekolah').value;
  const fJk  = document.getElementById('filterJk').value;

  const params = new URLSearchParams({
    select : 'nik,nama,sekolah,jenis_kelamin,domisili',
    order  : 'nama.asc',
  });
  if (q) {
    // Cari di kolom nama ATAU nik sekaligus
    const qSafe = q.replace(/[(),]/g, ''); // hindari karakter yang bentrok sintaks PostgREST
    params.set('or', `(nama.ilike.*${qSafe}*,nik.ilike.*${qSafe}*)`);
  }
  if (fSek) params.set('sekolah', `eq.${fSek}`);
  if (fJk)  params.set('jenis_kelamin', `eq.${fJk}`);

  const from = (currentPage - 1) * PAGE_SIZE;
  const to   = from + PAGE_SIZE - 1;

  try {
    const res = await fetch(`${SB_URL}/rest/v1/user_RFID?${params}`, {
      headers: {
        ...sbHdr(),
        'Range'     : `${from}-${to}`,
        'Range-Unit': 'items',
        'Prefer'    : 'count=exact',
      }
    });
    if (!res.ok) {
      const errBody = await res.json().catch(() => ({}));
      throw new Error(errBody.message || `HTTP ${res.status}`);
    }
    currentStudents = await res.json();

    const range = res.headers.get('content-range'); // format: "0-14/12345"
    totalCount  = (range && range.includes('/'))
      ? (parseInt(range.split('/')[1], 10) || 0)
      : currentStudents.length;

    document.getElementById('listCount').textContent =
      `${totalCount.toLocaleString('id-ID')} siswa`;

    renderList();
  } catch (err) {
    addLog('err', 'Gagal memuat data siswa: ' + err.message);
    document.getElementById('studentList').innerHTML =
      `<div class="list-empty"><svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg><div>Gagal memuat data</div></div>`;
  }
}

/**
 * Daftar sekolah untuk dropdown filter — diambil dari tabel `sekolah`
 * yang khusus & kecil (bukan dari data siswa), jadi tetap ringan
 * walau data siswa sudah sangat besar.
 */
async function loadSekolahOptions() {
  try {
    const rows = await sbGetShared('sekolah', 'select=sekolah&order=sekolah.asc');
    const sel  = document.getElementById('filterSekolah');
    rows.forEach(r => {
      if (!r.sekolah) return;
      const opt = document.createElement('option');
      opt.value = r.sekolah; opt.textContent = r.sekolah;
      sel.appendChild(opt);
    });
  } catch (_) { /* dropdown filter opsional, tidak fatal kalau gagal */ }
}


/* ═════════════════════════════════════════════════
   2.  FILTER & SEARCH (server-side, lihat searchStudents di atas)
   ═════════════════════════════════════════════════ */
let searchTimer = null;
function onSearch() {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(() => { currentPage = 1; searchStudents(); }, 300);
}
function onFilter() { currentPage = 1; searchStudents(); }

/* ═════════════════════════════════════════════════
   3.  RENDER LIST
   ═════════════════════════════════════════════════ */
function renderList() {
  const list  = document.getElementById('studentList');
  const pgDiv = document.getElementById('miniPg');

  if (!currentStudents.length) {
    list.innerHTML = `<div class="list-empty">
      <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
      <div>Tidak ada siswa ditemukan</div>
    </div>`;
    pgDiv.style.display = 'none';
    return;
  }

  const totalPages = Math.max(1, Math.ceil(totalCount / PAGE_SIZE));

  list.innerHTML = currentStudents.map(s => {
    const isSel   = selectedSiswa && selectedSiswa.nik === s.nik;
    const isF     = s.jenis_kelamin === 'Perempuan';
    const initial = (s.nama || '?').charAt(0).toUpperCase();
    return `<div class="student-item ${isSel ? 'selected' : ''}" onclick="selectStudent('${esc(s.nik)}')">
      <div class="student-avatar ${isF ? 'female' : ''}">${initial}</div>
      <div class="student-info">
        <div class="student-name">${esc(s.nama)}</div>
        <div class="student-meta">${s.nik || '—'}</div>
        <div class="student-school">${esc(s.sekolah || '—')}</div>
      </div>
      <div class="student-check">
        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
      </div>
    </div>`;
  }).join('');

  // Pagination
  if (totalPages > 1) {
    pgDiv.style.display = 'flex';
    document.getElementById('pgInfo').textContent =
      `Hal. ${currentPage} / ${totalPages}`;
    document.getElementById('pgPrev').disabled = currentPage <= 1;
    document.getElementById('pgNext').disabled = currentPage >= totalPages;
  } else {
    pgDiv.style.display = 'none';
  }
}

function changePage(dir) {
  const totalPages = Math.max(1, Math.ceil(totalCount / PAGE_SIZE));
  currentPage = Math.max(1, Math.min(totalPages, currentPage + dir));
  searchStudents();
}

/* ═════════════════════════════════════════════════
   4.  SELECT STUDENT
   ═════════════════════════════════════════════════ */
function selectStudent(nik) {
  const siswa = currentStudents.find(s => s.nik === nik);
  if (!siswa) return;

  selectedSiswa = siswa;
  renderList();
  updatePreview(siswa);
  updateWriteReady();
}

function updatePreview(s) {
  document.getElementById('previewEmpty').style.display = 'none';
  document.getElementById('previewCard').style.display  = 'block';
  document.getElementById('prevNama').textContent    = s.nama    || '—';
  document.getElementById('prevSekolah').textContent = s.sekolah || '—';
  document.getElementById('prevJk').textContent      = s.jenis_kelamin || '—';
  document.getElementById('prevDom').textContent     = s.domisili || '—';

  // Format NIK with spaces every 4 digits
  const nik = s.nik || '';
  document.getElementById('prevNIK').textContent =
    nik.match(/.{1,4}/g)?.join(' ') || nik;

  // Payload box
  document.getElementById('plNik').textContent      = s.nik || '—';
  document.getElementById('plNama').textContent     = s.nama || '—';
  document.getElementById('plSekolah').textContent  = s.sekolah || '—';
  document.getElementById('plDomisili').textContent = s.domisili || '—';
  document.getElementById('payloadBox').classList.add('show');
}

/* ═════════════════════════════════════════════════
   5.  RFID BRIDGE — deteksi & polling
   ═════════════════════════════════════════════════ */
async function checkBridge() {
  try {
    const res = await fetch(`${BRIDGE_URL}/status`, {
      signal: AbortSignal.timeout(2500)
    });
    if (res.ok) {
      const data = await res.json();
      bridgeOnline = true;
      setReaderStatus('connected',
        data.reader ? `Reader: ${data.reader}` : 'ACR122U terdeteksi');
      setWriteStatus('connected',
        data.reader ? `Siap — ${data.reader}` : 'Reader siap digunakan');
      updateWriteReady();
      return true;
    }
  } catch (_) {}

  bridgeOnline = false;
  setReaderStatus('error', 'RFID Bridge tidak terdeteksi');
  setWriteStatus('error', 'Bridge offline — jalankan agen di PC');
  document.getElementById('writeBtn').disabled = true;
  return false;
}

function setReaderStatus(type, msg) {
  const el = document.getElementById('readerStatus');
  el.className = `device-status ${type}`;
  el.querySelector('.status-text').textContent = msg;
}

function setWriteStatus(type, msg) {
  const el = document.getElementById('writeStatus');
  el.className = `device-status ${type}`;
  el.querySelector('#writeStatusText').textContent = msg;
}

function updateWriteReady() {
  const btn = document.getElementById('writeBtn');
  const ready = bridgeOnline && selectedSiswa;
  btn.disabled = !ready;
}

// Poll bridge setiap 4 detik
function startBridgePoll() {
  checkBridge();
  pollTimer = setInterval(checkBridge, 4000);
}

/* ═════════════════════════════════════════════════
   6.  WRITE KE RFID
   ═════════════════════════════════════════════════ */
async function doWrite() {
  if (!selectedSiswa) { toast('Pilih siswa terlebih dahulu', 'warning'); return; }
  if (!bridgeOnline)  { toast('RFID Bridge tidak aktif', 'error');       return; }

  const { nik, nama, sekolah, jenis_kelamin, domisili } = selectedSiswa;

  if (!nik || nik.length !== 16) {
    toast('NIK siswa tidak valid (harus 16 digit)', 'error');
    addLog('err', `NIK tidak valid: "${nik}"`);
    return;
  }

  // UI: writing state
  const btn      = document.getElementById('writeBtn');
  const btnText  = document.getElementById('writeBtnText');
  btn.disabled   = true;
  btn.className  = 'write-btn writing';
  btnText.innerHTML = '<div class="spinner-sm"></div> Menunggu kartu...';
  setWriteStatus('writing', 'Tempelkan kartu RFID ke reader...');
  addLog('info', `Menulis NIK ${nik} (${nama}) ke kartu RFID...`);

  try {
    /* Kirim ke RFID Bridge agen di localhost.
       Bridge memerintahkan ACR122U menulis NIK ke kartu
       sebagai NDEF Text Record — hanya 16 digit NIK. */
    const payload = {
      action      : 'write',
      format      : 'ndef_text',
      text_record : nik,   // ← satu-satunya data yang ditulis ke chip kartu
    };

    const res = await fetch(`${BRIDGE_URL}/write`, {
      method  : 'POST',
      headers : { 'Content-Type': 'application/json' },
      body    : JSON.stringify(payload),
      signal  : AbortSignal.timeout(15000), // 15s — kartu mungkin belum ditempel
    });

    const result = await res.json();

    if (res.ok && result.status === 'ok') {
      // Sukses
      btn.className = 'write-btn success';
      btnText.innerHTML = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> Write Berhasil!`;
      setWriteStatus('connected', `✓ NIK ${nik} berhasil ditulis`);
      addLog('ok', `✓ Write sukses — NIK: ${nik} | UID kartu: ${result.uid || 'N/A'}`);
      toast(`NIK ${nik} berhasil ditulis ke kartu RFID`, 'success');

      // Reset setelah 3 detik
      setTimeout(() => {
        btn.className = 'write-btn';
        btnText.innerHTML = `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 22s8-4.5 8-11.8A8 8 0 0 0 4 10.2C4 17.5 12 22 12 22z"/><circle cx="12" cy="10" r="3"/></svg> Tempel Kartu & Write NIK`;
        btn.disabled = false;
        setWriteStatus('connected', 'Siap untuk write berikutnya');
      }, 3000);

    } else {
      throw new Error(result.message || result.error || `HTTP ${res.status}`);
    }

  } catch (err) {
    const msg = err.name === 'TimeoutError'
      ? 'Timeout — kartu tidak terdeteksi dalam 15 detik'
      : err.message;

    btn.className = 'write-btn fail';
    btnText.innerHTML = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> Gagal — Coba Lagi`;
    setWriteStatus('error', msg);
    addLog('err', `✗ Gagal: ${msg}`);
    toast(msg, 'error');

    setTimeout(() => {
      btn.className  = 'write-btn';
      btnText.innerHTML = `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 22s8-4.5 8-11.8A8 8 0 0 0 4 10.2C4 17.5 12 22 12 22z"/><circle cx="12" cy="10" r="3"/></svg> Tempel Kartu & Write NIK`;
      btn.disabled = !bridgeOnline;
    }, 3000);
  }
}

/* ═════════════════════════════════════════════════
   7.  LOG
   ═════════════════════════════════════════════════ */
function addLog(type, msg) {
  const log = document.getElementById('writeLog');
  const empty = document.getElementById('logEmpty');
  if (empty) empty.remove();

  const now  = new Date();
  const time = now.toTimeString().slice(0, 8);
  const item = document.createElement('div');
  item.className = `log-item log-${type}`;
  item.innerHTML = `<span class="log-time">${time}</span><span class="log-msg">${esc(msg)}</span>`;
  log.appendChild(item);
  log.scrollTop = log.scrollHeight;
}

/* ═════════════════════════════════════════════════
   8.  UTIL
   ═════════════════════════════════════════════════ */
function esc(s) {
  if (!s) return '';
  return String(s)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');
}

let toastTimer = null;
function toast(msg, type = '') {
  const el = document.getElementById('toast');
  el.textContent = msg;
  el.className   = `show ${type}`;
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => { el.className = type; }, 3500);
}

/* ═════════════════════════════════════════════════
   INIT
   ═════════════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', () => {
  loadSekolahOptions();
  searchStudents();
  startBridgePoll();
});
</script>
</body>
</html>