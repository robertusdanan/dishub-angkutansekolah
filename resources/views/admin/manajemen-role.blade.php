<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <meta name="csrf-token" content="{{ csrf_token() }}"/>
  <title>Manajemen Role - Admin</title>
  <link rel="canonical" href="{{ url('/admin/manajemen-role') }}"/>
  <link rel="icon" href="/favicon.ico"/>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/admin/admin-shell.css">

  <style>
    :root {
      --rm-primary: #2563eb;
      --rm-primary-hover: #1d4ed8;
      --rm-surface: #ffffff;
      --rm-card-bg: #f8fafc;
      --rm-border: #e2e8f0;
      --rm-text-main: #0f172a;
      --rm-text-muted: #64748b;
      --rm-success: #16a34a;
      --rm-warning: #d97706;
      --rm-danger: #dc2626;
      --rm-info: #0284c7;
    }

    /* Top Stats & Toolbar */
    .role-stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 14px;
      margin-bottom: 20px;
    }
    .stat-card {
      background: var(--rm-surface);
      border: 1px solid var(--rm-border);
      border-radius: 14px;
      padding: 14px 18px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    }
    .stat-label { font-size: 11.5px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: var(--rm-text-muted); }
    .stat-val { font-size: 22px; font-weight: 700; color: var(--rm-text-main); margin-top: 2px; }
    .stat-icon {
      width: 40px; height: 40px; border-radius: 10px;
      display: flex; align-items: center; justify-content: center;
      background: #eff6ff; color: var(--rm-primary);
    }

    .toolbar-bar {
      display: flex;
      flex-wrap: wrap;
      justify-content: space-between;
      align-items: center;
      gap: 12px;
      margin-bottom: 16px;
    }
    .search-box {
      position: relative;
      min-width: 260px;
      flex: 1;
      max-width: 380px;
    }
    .search-box svg {
      position: absolute;
      left: 12px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--rm-text-muted);
      pointer-events: none;
    }
    .search-box input {
      width: 100%;
      padding: 9px 12px 9px 36px;
      border: 1px solid var(--rm-border);
      border-radius: 10px;
      font-size: 13px;
      background: var(--rm-surface);
      color: var(--rm-text-main);
      outline: none;
      transition: border-color .15s, box-shadow .15s;
    }
    .search-box input:focus {
      border-color: var(--rm-primary);
      box-shadow: 0 0 0 3px rgba(37,99,235,0.12);
    }

    /* Table & Role Items */
    .role-table-card {
      background: var(--rm-surface);
      border: 1px solid var(--rm-border);
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    }
    .role-key-badge {
      font-family: 'DM Mono', monospace;
      font-size: 11px;
      font-weight: 500;
      color: #475569;
      background: #f1f5f9;
      padding: 2px 7px;
      border-radius: 5px;
      display: inline-block;
      margin-top: 3px;
    }
    .level-badge {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-width: 32px;
      padding: 3px 9px;
      border-radius: 999px;
      font-size: 11.5px;
      font-weight: 700;
      background: #eff6ff;
      color: #1d4ed8;
      border: 1px solid #bfdbfe;
    }
    .sys-pill {
      font-size: 10px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .4px;
      padding: 2px 7px;
      border-radius: 6px;
      background: #f8fafc;
      color: #64748b;
      border: 1px solid #cbd5e1;
    }
    .custom-pill {
      font-size: 10px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .4px;
      padding: 2px 7px;
      border-radius: 6px;
      background: #ecfdf5;
      color: #047857;
      border: 1px solid #a7f3d0;
    }
    .you-pill {
      font-size: 10px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .4px;
      padding: 2px 7px;
      border-radius: 6px;
      background: #fef3c7;
      color: #b45309;
      border: 1px solid #fde68a;
    }

    .perm-summary-box {
      display: flex;
      flex-direction: column;
      gap: 3px;
    }
    .perm-summary-main {
      font-size: 12.5px;
      font-weight: 600;
      color: var(--rm-text-main);
    }
    .perm-summary-detail {
      font-size: 11px;
      color: var(--rm-text-muted);
    }

    /* Modal Form Styles */
    .modal-overlay {
      display: none;
      position: fixed;
      inset: 0;
      z-index: 220;
      background: rgba(15,23,42,.6);
      backdrop-filter: blur(4px);
      align-items: center;
      justify-content: center;
      padding: 20px;
    }
    .modal-overlay.show { display: flex; }
    .modal-form-card {
      background: var(--rm-surface);
      border-radius: 20px;
      box-shadow: 0 25px 65px rgba(15,23,42,.35);
      width: 100%;
      max-width: 920px;
      max-height: 90vh;
      display: flex;
      flex-direction: column;
      overflow: hidden;
      animation: modalFadeIn .2s ease-out;
    }
    @keyframes modalFadeIn {
      from { opacity: 0; transform: scale(.97); }
      to { opacity: 1; transform: scale(1); }
    }
    .modal-form-header {
      padding: 18px 24px;
      border-bottom: 1px solid var(--rm-border);
      display: flex;
      justify-content: space-between;
      align-items: center;
      background: #ffffff;
    }
    .modal-form-header h3 { font-size: 17px; font-weight: 700; color: var(--rm-text-main); margin: 0; }
    .modal-form-header p { font-size: 12px; color: var(--rm-text-muted); margin: 3px 0 0; }
    .modal-form-close {
      background: none; border: none; cursor: pointer; color: var(--rm-text-muted);
      width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center;
      transition: background .15s, color .15s;
    }
    .modal-form-close:hover { background: #f1f5f9; color: var(--rm-text-main); }
    .modal-form-body {
      padding: 20px 24px;
      overflow-y: auto;
      display: flex;
      flex-direction: column;
      gap: 22px;
      background: #fbfcfe;
    }
    .modal-form-footer {
      padding: 16px 24px;
      border-top: 1px solid var(--rm-border);
      display: flex;
      gap: 12px;
      justify-content: flex-end;
      background: #ffffff;
    }

    /* Modal Form Sections */
    .form-section {
      background: #ffffff;
      border: 1px solid var(--rm-border);
      border-radius: 14px;
      padding: 18px 20px;
      box-shadow: 0 1px 2px rgba(0,0,0,0.02);
    }
    .form-section-title {
      font-size: 13px;
      font-weight: 700;
      letter-spacing: .4px;
      text-transform: uppercase;
      color: #334155;
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 14px;
      padding-bottom: 10px;
      border-bottom: 1px solid #f1f5f9;
    }

    .field-grid-2 {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 14px;
    }
    @media (max-width: 640px) {
      .field-grid-2 { grid-template-columns: 1fr; }
    }
    .field-group { display: flex; flex-direction: column; gap: 6px; }
    .field-group label { font-size: 12px; font-weight: 600; color: #475569; }
    .field-group .hint { font-size: 11px; color: var(--rm-text-muted); }
    .form-input {
      width: 100%;
      padding: 9px 12px;
      border: 1px solid var(--rm-border);
      border-radius: 8px;
      font-size: 13px;
      color: var(--rm-text-main);
      background: #ffffff;
      outline: none;
      transition: border-color .15s, box-shadow .15s;
    }
    .form-input:focus {
      border-color: var(--rm-primary);
      box-shadow: 0 0 0 3px rgba(37,99,235,0.12);
    }
    .form-input:disabled {
      background: #f1f5f9;
      color: #94a3b8;
      cursor: not-allowed;
    }

    /* CRUD Matrix Table */
    .matrix-toolbar {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      justify-content: space-between;
      gap: 8px;
      margin-bottom: 12px;
      padding: 8px 12px;
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 10px;
    }
    .matrix-tools-left { display: flex; align-items: center; gap: 8px; font-size: 12px; font-weight: 600; color: #334155; }
    .matrix-tools-right { display: flex; align-items: center; gap: 6px; }
    .btn-quick {
      font-size: 11.5px;
      font-weight: 600;
      padding: 4px 10px;
      border-radius: 6px;
      border: 1px solid #cbd5e1;
      background: #ffffff;
      color: #475569;
      cursor: pointer;
      transition: all .15s;
    }
    .btn-quick:hover {
      background: #f1f5f9;
      color: #0f172a;
      border-color: #94a3b8;
    }

    .module-group-block {
      margin-bottom: 16px;
      border: 1px solid var(--rm-border);
      border-radius: 12px;
      overflow: hidden;
      background: #ffffff;
    }
    .module-header {
      padding: 10px 14px;
      background: #f8fafc;
      border-bottom: 1px solid var(--rm-border);
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .module-title { font-size: 12.5px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px; }
    .btn-mod-toggle {
      font-size: 11px;
      font-weight: 600;
      color: var(--rm-primary);
      background: none;
      border: none;
      cursor: pointer;
      padding: 2px 6px;
      border-radius: 4px;
    }
    .btn-mod-toggle:hover { background: #eff6ff; }

    .matrix-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 12.5px;
    }
    .matrix-table th {
      padding: 8px 12px;
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .4px;
      color: #64748b;
      background: #fbfcfe;
      border-bottom: 1px solid #e2e8f0;
      text-align: center;
    }
    .matrix-table th:first-child { text-align: left; }
    .matrix-table td {
      padding: 9px 12px;
      border-bottom: 1px solid #f1f5f9;
      vertical-align: middle;
      text-align: center;
    }
    .matrix-table td:first-child { text-align: left; }
    .matrix-table tr:last-child td { border-bottom: none; }
    .matrix-table tr:hover td { background: #f8fafc; }

    .menu-name-cell {
      display: flex;
      align-items: center;
      gap: 10px;
      font-weight: 500;
      color: #1e293b;
    }
    .menu-badge-id {
      font-family: 'DM Mono', monospace;
      font-size: 10px;
      color: #94a3b8;
    }

    /* Checkbox & Badge Styles */
    .crud-check-label {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 4px;
      cursor: pointer;
      user-select: none;
    }
    .crud-check-label input[type="checkbox"] {
      width: 16px;
      height: 16px;
      cursor: pointer;
      accent-color: var(--rm-primary);
    }
    .crud-check-label.disabled {
      opacity: 0.4;
      cursor: not-allowed;
    }
    .crud-check-label.disabled input { cursor: not-allowed; }

    .pill-create { color: #15803d; font-weight: 600; font-size: 11px; }
    .pill-edit   { color: #0369a1; font-weight: 600; font-size: 11px; }
    .pill-delete { color: #b91c1c; font-weight: 600; font-size: 11px; }

    .dash-na {
      color: #94a3b8;
      font-size: 13px;
      font-weight: 600;
      user-select: none;
    }
    .badge-ro {
      font-size: 9.5px;
      font-weight: 700;
      color: #0369a1;
      background: #f0f9ff;
      border: 1px solid #bae6fd;
      padding: 1px 6px;
      border-radius: 4px;
      text-transform: uppercase;
      letter-spacing: .3px;
    }

    /* System Permissions Grid */
    .system-perms-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
      gap: 14px;
    }
    .system-card {
      border: 1px solid #e2e8f0;
      border-radius: 10px;
      padding: 12px 14px;
      background: #f8fafc;
    }
    .system-card-title {
      font-size: 12px;
      font-weight: 700;
      color: #334155;
      margin-bottom: 8px;
      padding-bottom: 6px;
      border-bottom: 1px solid #e2e8f0;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .system-check-list {
      display: flex;
      flex-direction: column;
      gap: 6px;
    }
    .sys-check-item {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 12px;
      color: #1e293b;
      cursor: pointer;
    }

    /* Modal Confirmation */
    .confirm-modal-overlay {
      display: none;
      position: fixed;
      inset: 0;
      z-index: 250;
      background: rgba(15,23,42,.65);
      backdrop-filter: blur(3px);
      align-items: center;
      justify-content: center;
      padding: 20px;
    }
    .confirm-modal-overlay.open { display: flex; }
    .confirm-modal-box {
      background: #ffffff;
      border-radius: 16px;
      padding: 24px;
      max-width: 420px;
      width: 100%;
      text-align: center;
      box-shadow: 0 20px 50px rgba(0,0,0,0.3);
    }
    .confirm-modal-icon {
      width: 48px;
      height: 48px;
      border-radius: 999px;
      background: #fee2e2;
      color: #dc2626;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 14px;
    }
    .confirm-modal-title { font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 6px; }
    .confirm-modal-sub { font-size: 13px; color: #64748b; margin-bottom: 20px; line-height: 1.5; }
    .confirm-modal-actions { display: flex; gap: 10px; justify-content: center; }
    .btn-danger-confirm {
      background: #dc2626; color: #ffffff; border: none; border-radius: 8px;
      padding: 9px 18px; font-size: 13px; font-weight: 600; cursor: pointer;
    }
    .btn-danger-confirm:hover { background: #b91c1c; }
    .btn-cancel-confirm {
      background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; border-radius: 8px;
      padding: 9px 18px; font-size: 13px; font-weight: 600; cursor: pointer;
    }
    .btn-cancel-confirm:hover { background: #e2e8f0; }

    #result-msg { display: none; }
    .alert { border-radius: 10px; padding: 12px 16px; font-size: 13px; font-weight: 500; display: flex; align-items: center; gap: 10px; }
    .alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #15803d; }
    .alert-error   { background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; }
  </style>
</head>
<body>

<div class="adm-shell">
  @include('admin.partials.sidebar', ['currentPage' => 'manajemen_role', 'currentModule' => session('admin_module')])

  <main class="adm-main">
    <div class="adm-content">

      <!-- Page Header -->
      <div class="adm-page-header">
        <div>
          <h1 class="adm-page-title">Manajemen Role &amp; Hak Akses</h1>
          <p class="adm-page-subtitle">Kelola hierarki level akun, hak akses menu, dan izin aksi Tambah, Edit, Hapus (CRUD)</p>
        </div>
      </div>

      <div id="result-msg" class="alert" style="margin-bottom:16px;"></div>

      <!-- Quick Stats -->
      <div class="role-stats-grid">
        <div class="stat-card">
          <div>
            <div class="stat-label">Total Role</div>
            <div class="stat-val" id="statTotalRoles">-</div>
          </div>
          <div class="stat-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          </div>
        </div>
        <div class="stat-card">
          <div>
            <div class="stat-label">Role Bawaan Sistem</div>
            <div class="stat-val" id="statSysRoles">-</div>
          </div>
          <div class="stat-icon" style="background:#fef2f2; color:#dc2626;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
          </div>
        </div>
        <div class="stat-card">
          <div>
            <div class="stat-label">Role Kustom</div>
            <div class="stat-val" id="statCustomRoles">-</div>
          </div>
          <div class="stat-icon" style="background:#ecfdf5; color:#059669;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
          </div>
        </div>
      </div>

      <!-- Toolbar -->
      <div class="toolbar-bar">
        <div class="search-box">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input type="text" id="roleSearchInput" placeholder="Cari role berdasarkan nama / key…" oninput="filterRolesTable()">
        </div>

        @if ($canCreate)
        <button class="btn btn-primary" onclick="openCreateModal()">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Tambah Role Baru
        </button>
        @endif
      </div>

      <!-- Table Card -->
      <div class="role-table-card">
        <table class="adm-table" style="width:100%;">
          <thead>
            <tr>
              <th style="width:28%;">Peran (Role)</th>
              <th style="width:10%; text-align:center;">Level</th>
              <th style="width:34%;">Izin Menu &amp; Aksi (CRUD)</th>
              <th style="width:16%;">Tipe</th>
              <th style="width:12%; text-align:right;">Aksi</th>
            </tr>
          </thead>
          <tbody id="roleBody">
            <tr><td colspan="5" style="text-align:center; padding:40px; color:var(--rm-text-muted);">Memuat data peran…</td></tr>
          </tbody>
        </table>
      </div>

    </div>
  </main>
</div>

<!-- ═══════ Modal Tambah / Edit Role ═══════ -->
<div class="modal-overlay" id="roleFormModal">
  <div class="modal-form-card">
    <div class="modal-form-header">
      <div>
        <h3 id="roleFormTitle">Tambah Role Baru</h3>
        <p id="roleFormSubtitle">Konfigurasi nama, hierarki, dan hak akses CRUD per menu</p>
      </div>
      <button class="modal-form-close" onclick="closeModal('roleFormModal')" title="Tutup">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>

    <div class="modal-form-body">
      <div id="roleFormMsg" class="alert" style="display:none;"></div>

      @unless ($isSuperAdmin)
      <div class="alert" style="background:#fffbeb;border:1px solid #fde68a;color:#92400e;display:flex;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <span>Anda hanya bisa memberi izin yang Anda miliki sendiri. Izin di luar wewenang Anda tidak akan disertakan.</span>
      </div>
      @endunless

      <input type="hidden" id="roleId" value="">

      <!-- Section 1: Profil Pokok -->
      <div class="form-section">
        <div class="form-section-title">
          <span>1. Informasi Pokok Role</span>
        </div>
        <div class="field-grid-2">
          <div class="field-group">
            <label for="roleKey">Role Key (Identifier Unik)</label>
            <input type="text" id="roleKey" class="form-input" placeholder="mis. staf_angkutan">
            <span class="hint">Huruf kecil, angka, garis bawah (_). Tidak dapat diubah setelah dibuat.</span>
          </div>
          <div class="field-group">
            <label for="roleLevel">Level Wewenang (Hierarki)</label>
            <input type="number" id="roleLevel" class="form-input" min="0" placeholder="mis. 100">
            <span class="hint">Semakin tinggi angkanya, semakin tinggi wewenangnya atas role lain.</span>
          </div>
          <div class="field-group">
            <label for="roleName">Nama Tampilan Role</label>
            <input type="text" id="roleName" class="form-input" placeholder="mis. Staf Angkutan Sekolah">
          </div>
          <div class="field-group">
            <label for="roleDesc">Deskripsi Ringkas</label>
            <input type="text" id="roleDesc" class="form-input" placeholder="mis. Mengelola data absensi dan driver">
          </div>
        </div>
      </div>

      <!-- Section 2: Matriks Menu & CRUD -->
      <div class="form-section">
        <div class="form-section-title">
          <span>2. Hak Akses Menu &amp; Logika Aksi (Tambah, Edit, Hapus)</span>
        </div>

        <div class="matrix-toolbar">
          <div class="matrix-tools-left">
            @if ($iHaveAllMenu)
            <label class="crud-check-label" style="font-weight:700; color:var(--rm-primary);">
              <input type="checkbox" id="menuAll" onchange="toggleSuperAllMenus(this.checked)">
              Semua Menu &amp; Aksi (Akses Penuh Super)
            </label>
            @else
            <span>Pilihan Hak Akses Menu</span>
            @endif
          </div>
          <div class="matrix-tools-right">
            <button type="button" class="btn-quick" onclick="quickSelectAll(true)">Pilih Semua</button>
            <button type="button" class="btn-quick" onclick="quickSelectAll(false)">Kosongkan</button>
            <button type="button" class="btn-quick" onclick="quickSelectViewOnly()">Hanya Lihat</button>
          </div>
        </div>

        <div id="matrixModuleContainer">
          @forelse ($menuGroups as $groupName => $items)
          <div class="module-group-block" data-module-block>
            <div class="module-header">
              <div class="module-title">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                <span>{{ $groupName }}</span>
              </div>
              <button type="button" class="btn-mod-toggle" onclick="toggleModuleGroup(this)">Toggle Semua di Grup Ini</button>
            </div>

            <table class="matrix-table">
              <thead>
                <tr>
                  <th style="width:40%;">Menu</th>
                  <th style="width:15%;">Akses / Lihat</th>
                  <th style="width:15%; color:#15803d;">Tambah</th>
                  <th style="width:15%; color:#0369a1;">Edit</th>
                  <th style="width:15%; color:#b91c1c;">Hapus</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($items as $mId => $label)
                @php
                  $caps = $menuActionCaps[$mId] ?? ['create' => true, 'edit' => true, 'delete' => true];
                  $supported = $menuSupportedActions[$mId] ?? [];
                  $isViewOnly = empty($supported);
                  $hasCreate = in_array('create', $supported, true);
                  $hasEdit   = in_array('edit', $supported, true);
                  $hasDelete = in_array('delete', $supported, true);
                @endphp
                <tr data-menu-row="{{ $mId }}">
                  <td>
                    <div class="menu-name-cell">
                      <span>{{ $label }}</span>
                      <span class="menu-badge-id">#{{ $mId }}</span>
                      @if ($isViewOnly)
                      <span class="badge-ro" title="Menu ini bersifat laporan atau pemantauan data">Hanya Lihat</span>
                      @endif
                    </div>
                  </td>
                  <td>
                    <label class="crud-check-label">
                      <input type="checkbox" class="menu-access-check" data-mid="{{ $mId }}" onchange="handleMenuAccessChange('{{ $mId }}', this.checked)">
                      <span style="font-weight:600; font-size:11.5px;">Buka</span>
                    </label>
                  </td>
                  <td style="text-align:center;">
                    @if ($hasCreate)
                    <label class="crud-check-label crud-action-label {{ empty($caps['create']) ? 'disabled' : '' }}" title="{{ empty($caps['create']) ? 'Anda tidak memiliki wewenang ini' : 'Izin Tambah' }}">
                      <input type="checkbox" class="menu-act-check act-create" data-mid="{{ $mId }}" data-act="create" {{ empty($caps['create']) ? 'disabled' : '' }}>
                      <span class="pill-create">+ Tambah</span>
                    </label>
                    @else
                    <span class="dash-na" title="Menu ini tidak memiliki fungsi Tambah">-</span>
                    @endif
                  </td>
                  <td style="text-align:center;">
                    @if ($hasEdit)
                    <label class="crud-check-label crud-action-label {{ empty($caps['edit']) ? 'disabled' : '' }}" title="{{ empty($caps['edit']) ? 'Anda tidak memiliki wewenang ini' : 'Izin Edit' }}">
                      <input type="checkbox" class="menu-act-check act-edit" data-mid="{{ $mId }}" data-act="edit" {{ empty($caps['edit']) ? 'disabled' : '' }}>
                      <span class="pill-edit">✏️ Edit</span>
                    </label>
                    @else
                    <span class="dash-na" title="Menu ini tidak memiliki fungsi Edit">-</span>
                    @endif
                  </td>
                  <td style="text-align:center;">
                    @if ($hasDelete)
                    <label class="crud-check-label crud-action-label {{ empty($caps['delete']) ? 'disabled' : '' }}" title="{{ empty($caps['delete']) ? 'Anda tidak memiliki wewenang ini' : 'Izin Hapus' }}">
                      <input type="checkbox" class="menu-act-check act-delete" data-mid="{{ $mId }}" data-act="delete" {{ empty($caps['delete']) ? 'disabled' : '' }}>
                      <span class="pill-delete">🗑️ Hapus</span>
                    </label>
                    @else
                    <span class="dash-na" title="Menu ini tidak memiliki fungsi Hapus">-</span>
                    @endif
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          @empty
          <p class="hint" style="padding:16px;">Anda tidak memiliki izin menu apa pun untuk didelegasikan.</p>
          @endforelse
        </div>
      </div>

      <!-- Section 3: Izin Khusus & Administrasi Sistem -->
      <div class="form-section">
        <div class="form-section-title">
          <span>3. Izin Khusus &amp; Modul Administrasi</span>
        </div>

        <div class="system-perms-grid">
          <!-- Manajemen Akun -->
          <div class="system-card">
            <div class="system-card-title">
              <span>Manajemen Akun</span>
              <span style="font-size:10px; font-weight:normal; color:#64748b;">(Halaman /admin/akun)</span>
            </div>
            <div class="system-check-list">
              @php
                $akunLabels = ['view' => 'Lihat Daftar Akun', 'create' => 'Tambah / Daftarkan Akun', 'edit' => 'Ubah Data & Password', 'deactivate' => 'Nonaktifkan Akun', 'delete' => 'Hapus Akun Permanen'];
                $akunIds = ['view' => 'permAkunView', 'create' => 'permAkunCreate', 'edit' => 'permAkunEdit', 'deactivate' => 'permAkunDeactivate', 'delete' => 'permAkunDelete'];
              @endphp
              @forelse ($akunActions as $key => $val)
              <label class="sys-check-item">
                <input type="checkbox" id="{{ $akunIds[$key] }}">
                <span>{{ $akunLabels[$key] }}</span>
              </label>
              @empty
              <span class="hint">Tidak ada wewenang akun yang bisa dibagikan.</span>
              @endforelse
            </div>
          </div>

          <!-- Manajemen Role -->
          <div class="system-card">
            <div class="system-card-title">
              <span>Manajemen Role</span>
              <span style="font-size:10px; font-weight:normal; color:#64748b;">(Halaman /admin/manajemen-role)</span>
            </div>
            <div class="system-check-list">
              @php
                $roleLabels = ['view' => 'Lihat Daftar Role', 'create' => 'Buat Role Baru', 'edit' => 'Edit Hak Akses Role', 'delete' => 'Hapus Role'];
                $roleIds = ['view' => 'permRoleView', 'create' => 'permRoleCreate', 'edit' => 'permRoleEdit', 'delete' => 'permRoleDelete'];
              @endphp
              @forelse ($roleMgmtActions as $key => $val)
              <label class="sys-check-item">
                <input type="checkbox" id="{{ $roleIds[$key] }}">
                <span>{{ $roleLabels[$key] }}</span>
              </label>
              @empty
              <span class="hint">Tidak ada wewenang role yang bisa dibagikan.</span>
              @endforelse
            </div>
          </div>

          <!-- Fitur Ekstra -->
          <div class="system-card">
            <div class="system-card-title">
              <span>Fitur &amp; Utilitas Tambahan</span>
            </div>
            <div class="system-check-list">
              @if ($iHaveListlink)
              <label class="sys-check-item">
                <input type="checkbox" id="permListlinkAccess">
                <span>Login ke List Link Absen Angkutan</span>
              </label>
              @endif
              @if ($iHavePengaturanCleanup)
              <label class="sys-check-item">
                <input type="checkbox" id="permPengaturanCleanup">
                <span>Eksekusi Pembersihan Data Lama (&gt; 6 bln)</span>
              </label>
              @endif
              @if (!$iHaveListlink && !$iHavePengaturanCleanup)
              <span class="hint">Tidak ada fitur tambahan yang tersedia.</span>
              @endif
            </div>
          </div>
        </div>
      </div>

    </div>

    <div class="modal-form-footer">
      <button class="btn btn-secondary" id="btnCloseRoleForm" onclick="closeModal('roleFormModal')">Batal</button>
      <button class="btn btn-primary" id="btnSaveRole" onclick="saveRole()">Simpan Konfigurasi Role</button>
    </div>
  </div>
</div>

<!-- ═══════ Modal Konfirmasi Hapus ═══════ -->
<div class="confirm-modal-overlay" id="confirmModalOverlay">
  <div class="confirm-modal-box">
    <div class="confirm-modal-icon">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
    </div>
    <div class="confirm-modal-title">Hapus Role Ini?</div>
    <div class="confirm-modal-sub" id="confirmModalSub"></div>
    <div class="confirm-modal-actions">
      <button type="button" class="btn-cancel-confirm" id="confirmModalCancel">Batal</button>
      <button type="button" class="btn-danger-confirm" id="confirmModalOk">Ya, Hapus Role</button>
    </div>
  </div>
</div>

<script>
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;
const CAN_EDIT   = {{ $canEdit ? 'true' : 'false' }};
const CAN_DELETE = {{ $canDelete ? 'true' : 'false' }};
const SUPPORTED_ACTIONS = @json($menuSupportedActions);
let allRolesData = [];

function showMsg(type, text) {
  const el = document.getElementById('result-msg');
  el.className = `alert alert-${type}`;
  el.textContent = text;
  el.style.display = 'flex';
  clearTimeout(window._msgTimer);
  window._msgTimer = setTimeout(() => { el.style.display = 'none'; }, 6000);
}

function showModalMsg(msgElId, type, text) {
  const el = document.getElementById(msgElId);
  if (!el) return;
  el.className = `alert alert-${type}`;
  el.textContent = text;
  el.style.display = 'flex';
}

function clearModalMsg(msgElId) {
  const el = document.getElementById(msgElId);
  if (el) { el.style.display = 'none'; el.textContent = ''; }
}

function openModal(id)  { document.getElementById(id).classList.add('show'); }
function closeModal(id) { document.getElementById(id).classList.remove('show'); }
document.querySelectorAll('.modal-overlay').forEach(o => o.addEventListener('click', e => { if (e.target === o) closeModal(o.id); }));

function confirmModal(title, sub) {
  const overlay = document.getElementById('confirmModalOverlay');
  overlay.querySelector('.confirm-modal-title').textContent = title;
  document.getElementById('confirmModalSub').textContent = sub;
  overlay.classList.add('open');
  return new Promise(resolve => {
    const okBtn = document.getElementById('confirmModalOk');
    const cancelBtn = document.getElementById('confirmModalCancel');
    const cleanup = (r) => { overlay.classList.remove('open'); okBtn.removeEventListener('click', onOk); cancelBtn.removeEventListener('click', onCancel); resolve(r); };
    const onOk = () => cleanup(true);
    const onCancel = () => cleanup(false);
    okBtn.addEventListener('click', onOk);
    cancelBtn.addEventListener('click', onCancel);
  });
}

function escapeHtml(s) {
  return String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
}

/* ──────────────────────────────────────────────────────────
   Logika Matriks Checkbox CRUD
   ────────────────────────────────────────────────────────── */
function handleMenuAccessChange(mid, checked) {
  const row = document.querySelector(`tr[data-menu-row="${mid}"]`);
  if (!row) return;

  const actChecks = row.querySelectorAll('.menu-act-check');
  actChecks.forEach(cb => {
    // Jika menu diaktifkan, aktifkan aksi default jika sebelumnya mati
    if (!cb.disabled) {
      if (!checked) {
        cb.checked = false;
      } else {
        cb.checked = true; // default berikan semua aksi saat menu diaktifkan
      }
    }
  });
}

function toggleSuperAllMenus(checked) {
  document.querySelectorAll('.menu-access-check').forEach(cb => {
    cb.checked = checked;
    handleMenuAccessChange(cb.dataset.mid, checked);
  });
}

function quickSelectAll(checked) {
  document.querySelectorAll('.menu-access-check').forEach(cb => {
    cb.checked = checked;
    handleMenuAccessChange(cb.dataset.mid, checked);
  });
}

function quickSelectViewOnly() {
  document.querySelectorAll('.menu-access-check').forEach(cb => {
    cb.checked = true;
  });
  document.querySelectorAll('.menu-act-check').forEach(cb => {
    cb.checked = false;
  });
}

function toggleModuleGroup(btn) {
  const block = btn.closest('[data-module-block]');
  if (!block) return;
  const accessChecks = block.querySelectorAll('.menu-access-check');
  const someUnchecked = Array.from(accessChecks).some(cb => !cb.checked);

  accessChecks.forEach(cb => {
    cb.checked = someUnchecked;
    handleMenuAccessChange(cb.dataset.mid, someUnchecked);
  });
}

function setChecked(id, val) { const el = document.getElementById(id); if (el) el.checked = val; }
function getChecked(id) { const el = document.getElementById(id); return el ? el.checked : false; }

function resetPermFields() {
  setChecked('menuAll', false);
  document.querySelectorAll('.menu-access-check').forEach(cb => { cb.checked = false; cb.disabled = false; });
  document.querySelectorAll('.menu-act-check').forEach(cb => { cb.checked = false; cb.disabled = false; });

  ['permAkunView','permAkunCreate','permAkunEdit','permAkunDeactivate','permAkunDelete',
   'permRoleView','permRoleCreate','permRoleEdit','permRoleDelete',
   'permListlinkAccess','permPengaturanCleanup'].forEach(id => setChecked(id, false));
}

function fillPermFields(permissions) {
  const p = permissions || {};
  const menus = p.menus || [];
  const menuActions = p.menu_actions || {};
  const isAll = menus.includes('*');

  if (isAll && document.getElementById('menuAll')) {
    setChecked('menuAll', true);
    toggleSuperAllMenus(true);
  } else {
    document.querySelectorAll('.menu-access-check').forEach(cb => {
      const mid = cb.dataset.mid;
      const active = isAll || menus.includes(mid);
      cb.checked = active;

      const row = cb.closest('tr');
      if (row) {
        const createCb = row.querySelector('.act-create');
        const editCb = row.querySelector('.act-edit');
        const deleteCb = row.querySelector('.act-delete');

        // Jika menu_actions ada untuk mid tersebut, gunakan settingannya
        if (menuActions[mid]) {
          if (createCb && !createCb.disabled) createCb.checked = !!menuActions[mid].create;
          if (editCb && !editCb.disabled) editCb.checked = !!menuActions[mid].edit;
          if (deleteCb && !deleteCb.disabled) deleteCb.checked = !!menuActions[mid].delete;
        } else {
          // Fallback kompatibel: jika menu aktif tapi menu_actions belum ada, beri akses penuh
          if (createCb && !createCb.disabled) createCb.checked = active;
          if (editCb && !editCb.disabled) editCb.checked = active;
          if (deleteCb && !deleteCb.disabled) deleteCb.checked = active;
        }
      }
    });
  }

  const akun = p.akun || {};
  setChecked('permAkunView', !!akun.view);
  setChecked('permAkunCreate', !!akun.create);
  setChecked('permAkunEdit', !!akun.edit);
  setChecked('permAkunDeactivate', !!akun.deactivate);
  setChecked('permAkunDelete', !!akun.delete);

  const rm = p.manajemen_role || {};
  setChecked('permRoleView', !!rm.view);
  setChecked('permRoleCreate', !!rm.create);
  setChecked('permRoleEdit', !!rm.edit);
  setChecked('permRoleDelete', !!rm.delete);

  const ll = p.listlink || {};
  setChecked('permListlinkAccess', !!ll.access);

  const pg = p.pengaturan || {};
  setChecked('permPengaturanCleanup', !!pg.cleanup);
}

function collectPermFields() {
  const isSuper = getChecked('menuAll');
  const menus = isSuper ? ['*'] : Array.from(document.querySelectorAll('.menu-access-check:checked')).map(cb => cb.dataset.mid);

  const menuActions = {};
  document.querySelectorAll('tr[data-menu-row]').forEach(row => {
    const mid = row.dataset.menuRow;
    const accessCb = row.querySelector('.menu-access-check');
    if (accessCb && accessCb.checked) {
      const createCb = row.querySelector('.act-create');
      const editCb   = row.querySelector('.act-edit');
      const deleteCb = row.querySelector('.act-delete');

      menuActions[mid] = {
        view: true,
        create: createCb ? createCb.checked : false,
        edit:   editCb   ? editCb.checked   : false,
        delete: deleteCb ? deleteCb.checked : false,
      };
    }
  });

  return {
    menus,
    menu_actions: menuActions,
    akun: {
      view: getChecked('permAkunView'),
      create: getChecked('permAkunCreate'),
      edit: getChecked('permAkunEdit'),
      deactivate: getChecked('permAkunDeactivate'),
      delete: getChecked('permAkunDelete'),
    },
    manajemen_role: {
      view: getChecked('permRoleView'),
      create: getChecked('permRoleCreate'),
      edit: getChecked('permRoleEdit'),
      delete: getChecked('permRoleDelete'),
    },
    listlink: { access: getChecked('permListlinkAccess') },
    pengaturan: { cleanup: getChecked('permPengaturanCleanup') },
  };
}

/* ──────────────────────────────────────────────────────────
   Load & Render Roles Table
   ────────────────────────────────────────────────────────── */
async function loadRoles() {
  const tbody = document.getElementById('roleBody');
  try {
    const res = await fetch('/admin/api/roles?action=list');
    const json = await res.json();
    if (!res.ok) throw new Error(json.error || 'Gagal memuat role');
    allRolesData = json.data || [];

    // Hitung Stats
    document.getElementById('statTotalRoles').textContent = allRolesData.length;
    document.getElementById('statSysRoles').textContent = allRolesData.filter(r => r.is_system).length;
    document.getElementById('statCustomRoles').textContent = allRolesData.filter(r => !r.is_system).length;

    renderFilteredTable(allRolesData);
  } catch (e) {
    tbody.innerHTML = `<tr><td colspan="5" style="text-align:center; padding:30px; color:#dc2626;">✗ ${escapeHtml(e.message)}</td></tr>`;
  }
}

function filterRolesTable() {
  const q = (document.getElementById('roleSearchInput').value || '').trim().toLowerCase();
  if (!q) {
    renderFilteredTable(allRolesData);
    return;
  }
  const filtered = allRolesData.filter(r =>
    (r.role_name || '').toLowerCase().includes(q) ||
    (r.role_key || '').toLowerCase().includes(q) ||
    (r.description || '').toLowerCase().includes(q)
  );
  renderFilteredTable(filtered);
}

function renderFilteredTable(rows) {
  const tbody = document.getElementById('roleBody');
  if (rows.length === 0) {
    tbody.innerHTML = `<tr><td colspan="5" style="text-align:center; padding:40px; color:var(--rm-text-muted);">Tidak ada data role yang cocok.</td></tr>`;
    return;
  }
  tbody.innerHTML = rows.map(renderRow).join('');
}

function renderRow(r) {
  const perms = r.permissions || {};
  const menus = perms.menus || [];
  const menuActions = perms.menu_actions || {};

  let menuLabel = '';
  let actionDetail = '';

  if (menus.includes('*')) {
    menuLabel = '<span style="color:#2563eb; font-weight:700;">Akses Penuh Seluruh Menu</span>';
    actionDetail = '<span style="color:#16a34a; font-size:11px;">✓ Tambah, Edit, Hapus di Semua Menu</span>';
  } else if (menus.length === 0) {
    menuLabel = '<span style="color:#94a3b8;">Tidak ada menu</span>';
    actionDetail = '-';
  } else {
    menuLabel = `<strong>${menus.length} Menu Diizinkan</strong>`;

    // Hitung ringkasan aksi hanya untuk menu yang mendukung aksi tersebut
    let totalCreate = 0, totalEdit = 0, totalDelete = 0;
    menus.forEach(mid => {
      const supported = SUPPORTED_ACTIONS[mid] || [];
      if (supported.length === 0) return; // View only, skip penghitungan CRUD

      const act = menuActions[mid];
      if (act) {
        if (supported.includes('create') && act.create) totalCreate++;
        if (supported.includes('edit') && act.edit) totalEdit++;
        if (supported.includes('delete') && act.delete) totalDelete++;
      } else {
        if (supported.includes('create')) totalCreate++;
        if (supported.includes('edit')) totalEdit++;
        if (supported.includes('delete')) totalDelete++;
      }
    });

    const actionParts = [];
    if (totalCreate > 0) actionParts.push(`<span style="color:#15803d;">+${totalCreate} Tambah</span>`);
    if (totalEdit > 0) actionParts.push(`<span style="color:#0369a1;">✏️${totalEdit} Edit</span>`);
    if (totalDelete > 0) actionParts.push(`<span style="color:#b91c1c;">🗑️${totalDelete} Hapus</span>`);

    actionDetail = actionParts.length ? actionParts.join(' &bull; ') : '<span style="color:#64748b;">Hanya Lihat (View Only)</span>';
  }

  const actions = [];
  if (r.is_mine) {
    actions.push(`<button class="btn btn-sm btn-secondary" onclick='openViewModal(${JSON.stringify(r)})'>Lihat Detail</button>`);
  } else {
    if (CAN_EDIT) {
      actions.push(`<button class="btn btn-sm btn-primary" style="padding:4px 10px; font-size:12px;" onclick='openEditModal(${JSON.stringify(r)})'>Edit Izin</button>`);
    } else {
      actions.push(`<button class="btn btn-sm btn-secondary" onclick='openViewModal(${JSON.stringify(r)})'>Lihat</button>`);
    }
    if (CAN_DELETE && !r.is_system) {
      actions.push(`<button class="btn btn-sm btn-danger" style="padding:4px 10px; font-size:12px;" onclick="deleteRole('${r.id}', '${escapeHtml(r.role_name)}')">Hapus</button>`);
    }
  }

  const typePill = r.is_system
    ? '<span class="sys-pill">Bawaan Sistem</span>'
    : '<span class="custom-pill">Kustom</span>';
  const youTag = r.is_mine ? '<span class="you-pill">Role Anda</span>' : '';

  return `
    <tr>
      <td>
        <div style="font-weight:700; color:#0f172a; font-size:13.5px;">${escapeHtml(r.role_name)} ${youTag}</div>
        <div class="role-key-badge">${escapeHtml(r.role_key)}</div>
        ${r.description ? `<div style="font-size:11.5px; color:#64748b; margin-top:4px;">${escapeHtml(r.description)}</div>` : ''}
      </td>
      <td style="text-align:center;">
        <span class="level-badge">${r.level}</span>
      </td>
      <td>
        <div class="perm-summary-box">
          <div class="perm-summary-main">${menuLabel}</div>
          <div class="perm-summary-detail">${actionDetail}</div>
        </div>
      </td>
      <td>${typePill}</td>
      <td style="text-align:right;">
        <div style="display:inline-flex; gap:6px; flex-wrap:wrap; justify-content:flex-end;">${actions.join('')}</div>
      </td>
    </tr>`;
}

/* ──────────────────────────────────────────────────────────
   Modal Handlers (Create, Edit, View, Save, Delete)
   ────────────────────────────────────────────────────────── */
function openCreateModal() {
  document.getElementById('roleFormTitle').textContent = 'Tambah Role Baru';
  document.getElementById('roleFormSubtitle').textContent = 'Konfigurasi nama, level hierarki, dan hak akses CRUD per menu';
  clearModalMsg('roleFormMsg');
  document.getElementById('roleId').value = '';
  document.getElementById('roleKey').value = '';
  document.getElementById('roleKey').disabled = false;
  document.getElementById('roleLevel').value = '';
  document.getElementById('roleLevel').disabled = false;
  document.getElementById('roleName').value = '';
  document.getElementById('roleDesc').value = '';
  resetPermFields();
  setModalReadonly(false);
  openModal('roleFormModal');
}

function openEditModal(r) {
  document.getElementById('roleFormTitle').textContent = 'Edit Role - ' + r.role_name;
  document.getElementById('roleFormSubtitle').textContent = 'Perbarui level dan matriks izin aksi (Tambah, Edit, Hapus)';
  clearModalMsg('roleFormMsg');
  document.getElementById('roleId').value = r.id;
  document.getElementById('roleKey').value = r.role_key;
  document.getElementById('roleKey').disabled = true;
  document.getElementById('roleLevel').value = r.level;
  document.getElementById('roleLevel').disabled = !!r.is_system;
  document.getElementById('roleName').value = r.role_name;
  document.getElementById('roleDesc').value = r.description || '';
  resetPermFields();
  fillPermFields(r.permissions);
  setModalReadonly(false);
  openModal('roleFormModal');
}

function openViewModal(r) {
  document.getElementById('roleFormTitle').textContent = 'Lihat Detail Role - ' + r.role_name;
  document.getElementById('roleFormSubtitle').textContent = 'Mode lihat detail wewenang dan izin aksi';
  clearModalMsg('roleFormMsg');
  document.getElementById('roleId').value = r.id;
  document.getElementById('roleKey').value = r.role_key;
  document.getElementById('roleKey').disabled = true;
  document.getElementById('roleLevel').value = r.level;
  document.getElementById('roleLevel').disabled = true;
  document.getElementById('roleName').value = r.role_name;
  document.getElementById('roleDesc').value = r.description || '';
  resetPermFields();
  fillPermFields(r.permissions);
  setModalReadonly(true);
  openModal('roleFormModal');
}

function setModalReadonly(readonly) {
  document.getElementById('roleName').disabled = readonly;
  document.getElementById('roleDesc').disabled = readonly;
  document.querySelectorAll('.menu-access-check').forEach(cb => { cb.disabled = readonly; });
  document.querySelectorAll('.menu-act-check').forEach(cb => { cb.disabled = readonly; });
  const menuAll = document.getElementById('menuAll');
  if (menuAll) menuAll.disabled = readonly;
  document.querySelectorAll('.sys-check-item input').forEach(cb => { cb.disabled = readonly; });
  document.querySelectorAll('.btn-quick, .btn-mod-toggle').forEach(b => { b.style.display = readonly ? 'none' : ''; });

  document.getElementById('btnSaveRole').style.display = readonly ? 'none' : '';
  document.getElementById('btnCloseRoleForm').textContent = readonly ? 'Tutup' : 'Batal';
}

async function saveRole() {
  const id = document.getElementById('roleId').value;
  const isCreate = !id;
  const btn = document.getElementById('btnSaveRole');

  const payload = isCreate
    ? {
        role_key: document.getElementById('roleKey').value.trim(),
        role_name: document.getElementById('roleName').value.trim(),
        description: document.getElementById('roleDesc').value.trim(),
        level: parseInt(document.getElementById('roleLevel').value || '0', 10),
        permissions: collectPermFields(),
      }
    : {
        id,
        role_name: document.getElementById('roleName').value.trim(),
        description: document.getElementById('roleDesc').value.trim(),
        permissions: collectPermFields(),
        ...(document.getElementById('roleLevel').disabled ? {} : { level: parseInt(document.getElementById('roleLevel').value || '0', 10) }),
      };

  btn.disabled = true;
  btn.textContent = 'Menyimpan Konfigurasi…';
  clearModalMsg('roleFormMsg');

  try {
    const res = await fetch(`/admin/api/roles?action=${isCreate ? 'create' : 'update'}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
      body: JSON.stringify(payload)
    });
    const json = await res.json();
    if (!res.ok) throw new Error(json.error || 'Gagal menyimpan role');

    showMsg('success', '✓ Konfigurasi role berhasil disimpan.');
    closeModal('roleFormModal');
    await loadRoles();
  } catch (e) {
    showModalMsg('roleFormMsg', 'error', '✗ ' + e.message);
  } finally {
    btn.disabled = false;
    btn.textContent = 'Simpan Konfigurasi Role';
  }
}

async function deleteRole(id, name) {
  const ok = await confirmModal('Hapus Role Ini?', `"${name}" akan dihapus permanen. Role yang masih digunakan oleh akun aktif tidak dapat dihapus.`);
  if (!ok) return;

  try {
    const res = await fetch('/admin/api/roles?action=delete', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
      body: JSON.stringify({ id })
    });
    const json = await res.json();
    if (!res.ok) throw new Error(json.error || 'Gagal menghapus role');

    showMsg('success', '✓ Role berhasil dihapus.');
    await loadRoles();
  } catch (e) {
    showMsg('error', '✗ ' + e.message);
  }
}

loadRoles();
</script>

<script>
(function () {
  const sidebar  = document.getElementById('admSidebar');
  const overlay  = document.getElementById('admOverlay');
  const openBtn  = document.getElementById('sidebarOpen');
  const closeBtn = document.getElementById('sidebarClose');
  function open()  { sidebar.classList.add('open');  overlay.classList.add('show'); document.body.style.overflow = 'hidden'; }
  function close() { sidebar.classList.remove('open'); overlay.classList.remove('show'); document.body.style.overflow = ''; }
  if (openBtn)  openBtn.addEventListener('click', open);
  if (closeBtn) closeBtn.addEventListener('click', close);
  if (overlay)  overlay.addEventListener('click', close);
})();
</script>
</body>
</html>
