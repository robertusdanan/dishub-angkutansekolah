<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <meta name="csrf-token" content="{{ csrf_token() }}"/>
  <title>Manajemen Role - Admin</title>
  <link rel="canonical" href="{{ url('/admin/manajemen-role') }}"/>
  <link rel="icon" href="/favicon.ico"/>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/admin/admin-shell.css">

  <style>
    .toolbar { display: flex; justify-content: flex-end; margin-bottom: 16px; }
    .role-key { font-family: 'DM Mono', monospace; font-size: 11.5px; color: var(--text-4); }
    .level-badge { display: inline-flex; align-items: center; justify-content: center; min-width: 30px; padding: 2px 8px; border-radius: 999px; font-size: 11.5px; font-weight: 700; background: var(--accent-glow, #eff6ff); color: var(--accent); }
    .sys-badge { font-size: 10.5px; font-weight: 700; color: var(--text-4); border: 1px solid var(--border); border-radius: 6px; padding: 1px 6px; margin-left: 6px; }
    .you-tag { font-size: 10.5px; font-weight: 700; color: var(--accent); margin-left: 6px; }
    .row-actions { display: flex; gap: 6px; flex-wrap: wrap; }
    .empty-state { text-align: center; padding: 40px 20px; color: var(--text-4); font-size: 13.5px; }
    .modal-overlay { display: none; position: fixed; inset: 0; z-index: 200; background: rgba(15,23,42,.55); backdrop-filter: blur(3px); align-items: center; justify-content: center; padding: 20px; }
    .modal-overlay.show { display: flex; }
    .modal-form-card { background: var(--surface); border-radius: 18px; box-shadow: 0 24px 60px rgba(15,23,42,.28); width: 100%; max-width: 560px; }
    .modal-form-header { padding: 20px 24px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
    .modal-form-header h3 { font-size: 16px; font-weight: 700; color: var(--text-1); }
    .modal-form-close { background: none; border: none; cursor: pointer; color: var(--text-4); padding: 4px; }
    .modal-form-body { padding: 20px 24px; display: flex; flex-direction: column; gap: 16px; max-height: 65vh; overflow-y: auto; }
    .modal-form-footer { padding: 16px 24px; border-top: 1px solid var(--border); display: flex; gap: 10px; justify-content: flex-end; }
    .field { display: flex; flex-direction: column; gap: 5px; }
    .field label { font-size: 11.5px; font-weight: 600; letter-spacing: .4px; text-transform: uppercase; color: var(--text-3); }
    .field .hint { font-size: 11px; color: var(--text-4); }
    .field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .perm-section { border: 1px solid var(--border); border-radius: 12px; padding: 14px 16px; }
    .perm-section h4 { font-size: 12.5px; font-weight: 700; color: var(--text-2); margin-bottom: 10px; text-transform: uppercase; letter-spacing: .4px; }
    .perm-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 8px; }
    .perm-check { display: flex; align-items: center; gap: 7px; font-size: 12.5px; color: var(--text-2); }
    .menu-group-title { font-size: 11.5px; font-weight: 700; color: var(--text-3); margin: 10px 0 6px; }
    .menu-all-check { font-size: 12.5px; font-weight: 600; color: var(--accent); display: flex; align-items: center; gap: 7px; margin-bottom: 8px; }
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

      <div class="adm-page-header">
        <div>
          <h1 class="adm-page-title">Manajemen Role</h1>
          <p class="adm-page-subtitle">Atur peran, izin menu, dan izin kelola Akun/Role</p>
        </div>
      </div>

      <div id="result-msg" class="alert" style="margin-bottom:16px;"></div>

      @if ($canCreate)
      <div class="toolbar">
        <button class="btn btn-primary" onclick="openCreateModal()">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Tambah Role
        </button>
      </div>
      @endif

      <div class="card table-wrapper">
        <table>
          <thead>
            <tr><th>Nama Role</th><th>Level</th><th>Menu Diizinkan</th><th>Deskripsi</th><th>Aksi</th></tr>
          </thead>
          <tbody id="roleBody">
            <tr><td colspan="5" class="empty-state">Memuat data…</td></tr>
          </tbody>
        </table>
      </div>

    </div>
  </main>
</div>

<!-- ═══════ Modal Tambah/Edit Role ═══════ -->
<div class="modal-overlay" id="roleFormModal">
  <div class="modal-form-card">
    <div class="modal-form-header">
      <h3 id="roleFormTitle">Tambah Role</h3>
      <button class="modal-form-close" onclick="closeModal('roleFormModal')">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="modal-form-body">
      <div id="roleFormMsg" class="alert" style="display:none;"></div>
      @unless ($isSuperAdmin)
      <div class="alert" style="background:#fffbeb;border:1px solid #fde68a;color:#92400e;display:flex;">
        Anda hanya bisa memberi izin yang Anda miliki sendiri. Jika role ini sebelumnya punya izin di luar milik Anda, izin tersebut akan hilang begitu Anda menyimpan perubahan pada role ini.
      </div>
      @endunless
      <input type="hidden" id="roleId" value="">
      <div class="field-row">
        <div class="field">
          <label for="roleKey">Role Key</label>
          <input type="text" id="roleKey" class="form-control" placeholder="mis. operator_absensi">
          <span class="hint">huruf kecil/angka/_, tidak bisa diubah setelah dibuat.</span>
        </div>
        <div class="field">
          <label for="roleLevel">Level Hierarki</label>
          <input type="number" id="roleLevel" class="form-control" min="0" placeholder="mis. 300">
          <span class="hint">semakin besar = semakin tinggi wewenangnya.</span>
        </div>
      </div>
      <div class="field">
        <label for="roleName">Nama Role</label>
        <input type="text" id="roleName" class="form-control" placeholder="mis. Operator Absensi">
      </div>
      <div class="field">
        <label for="roleDesc">Deskripsi</label>
        <input type="text" id="roleDesc" class="form-control" placeholder="Opsional">
      </div>

      <div class="perm-section">
        <h4>Menu yang Bisa Diakses</h4>
        @if ($iHaveAllMenu)
        <label class="menu-all-check">
          <input type="checkbox" id="menuAll" onchange="toggleAllMenus(this.checked)"> Semua menu (akses penuh)
        </label>
        @endif
        <div id="menuCheckboxes">
          @forelse ($menuGroups as $groupName => $items)
          <div class="menu-group-title">{{ $groupName }}</div>
          <div class="perm-grid">
            @foreach ($items as $id => $label)
            <label class="perm-check">
              <input type="checkbox" class="menu-item-check" value="{{ $id }}"> {{ $label }}
            </label>
            @endforeach
          </div>
          @empty
          <p class="hint">Anda tidak punya izin menu apa pun untuk diberikan ke role lain.</p>
          @endforelse
        </div>
      </div>

      <div class="perm-section">
        <h4>Izin Menu Akun</h4>
        <div class="perm-grid">
          @php
            $akunLabels = ['view' => 'Lihat', 'create' => 'Daftarkan akun', 'edit' => 'Edit akun', 'deactivate' => 'Nonaktifkan akun', 'delete' => 'Hapus akun'];
            $akunIds = ['view' => 'permAkunView', 'create' => 'permAkunCreate', 'edit' => 'permAkunEdit', 'deactivate' => 'permAkunDeactivate', 'delete' => 'permAkunDelete'];
          @endphp
          @forelse ($akunActions as $key => $val)
          <label class="perm-check"><input type="checkbox" id="{{ $akunIds[$key] }}"> {{ $akunLabels[$key] }}</label>
          @empty
          <p class="hint">Anda tidak punya izin menu Akun apa pun untuk diberikan.</p>
          @endforelse
        </div>
        <p class="hint" style="margin-top:8px;">Berlaku hanya ke akun dengan role setara/di bawah level role ini.</p>
      </div>

      <div class="perm-section">
        <h4>Izin Menu Manajemen Role</h4>
        <div class="perm-grid">
          @php
            $roleLabels = ['view' => 'Lihat', 'create' => 'Buat role', 'edit' => 'Edit role', 'delete' => 'Hapus role'];
            $roleIds = ['view' => 'permRoleView', 'create' => 'permRoleCreate', 'edit' => 'permRoleEdit', 'delete' => 'permRoleDelete'];
          @endphp
          @forelse ($roleMgmtActions as $key => $val)
          <label class="perm-check"><input type="checkbox" id="{{ $roleIds[$key] }}"> {{ $roleLabels[$key] }}</label>
          @empty
          <p class="hint">Anda tidak punya izin menu Manajemen Role apa pun untuk diberikan.</p>
          @endforelse
        </div>
        <p class="hint" style="margin-top:8px;">Berlaku hanya ke role dengan level setara/di bawah level role ini.</p>
      </div>

      @if ($iHaveListlink)
      <div class="perm-section">
        <h4>Izin List Link Absen Angkutan</h4>
        <div class="perm-grid">
          <label class="perm-check"><input type="checkbox" id="permListlinkAccess"> Boleh login ke List Link Absen Angkutan</label>
        </div>
        <p class="hint" style="margin-top:8px;">Mengatur siapa yang boleh masuk ke halaman terpisah "List Link Absen Angkutan" (di luar panel admin ini).</p>
      </div>
      @endif

      @if ($iHavePengaturanCleanup)
      <div class="perm-section">
        <h4>Izin Pengaturan</h4>
        <div class="perm-grid">
          <label class="perm-check"><input type="checkbox" id="permPengaturanCleanup"> Jalankan Pembersihan Data Lama</label>
        </div>
        <p class="hint" style="margin-top:8px;">Mengatur siapa yang boleh membuka &amp; menjalankan "Pembersihan Data Lama" (hapus permanen data absensi &gt; 6 bulan) di menu Pengaturan.</p>
      </div>
      @endif
    </div>
    <div class="modal-form-footer">
      <button class="btn btn-secondary" id="btnCloseRoleForm" onclick="closeModal('roleFormModal')">Batal</button>
      <button class="btn btn-primary" id="btnSaveRole" onclick="saveRole()">Simpan</button>
    </div>
  </div>
</div>

<!-- ═══════ Modal Konfirmasi Hapus ═══════ -->
<div class="confirm-modal-overlay" id="confirmModalOverlay">
  <div class="confirm-modal-box">
    <div class="confirm-modal-icon">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
    </div>
    <div class="confirm-modal-title">Hapus role ini?</div>
    <div class="confirm-modal-sub" id="confirmModalSub"></div>
    <div class="confirm-modal-actions">
      <button type="button" class="confirm-modal-cancel" id="confirmModalCancel">Batal</button>
      <button type="button" class="confirm-modal-danger" id="confirmModalOk">Ya, Hapus</button>
    </div>
  </div>
</div>

<script>
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;
const CAN_EDIT   = {{ $canEdit ? 'true' : 'false' }};
const CAN_DELETE = {{ $canDelete ? 'true' : 'false' }};

function showMsg(type, text) {
  const el = document.getElementById('result-msg');
  el.className = `alert alert-${type}`;
  el.textContent = text;
  el.style.display = 'flex';
  clearTimeout(window._msgTimer);
  window._msgTimer = setTimeout(() => { el.style.display = 'none'; }, 5000);
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

function toggleAllMenus(checked) {
  document.querySelectorAll('.menu-item-check').forEach(cb => { cb.checked = checked; cb.disabled = checked; });
}

async function loadRoles() {
  const tbody = document.getElementById('roleBody');
  try {
    const res = await fetch('/admin/api/roles?action=list');
    const json = await res.json();
    if (!res.ok) throw new Error(json.error || 'Gagal memuat role');
    const rows = json.data || [];
    if (rows.length === 0) {
      tbody.innerHTML = `<tr><td colspan="5" class="empty-state">Belum ada role.</td></tr>`;
      return;
    }
    tbody.innerHTML = rows.map(renderRow).join('');
  } catch (e) {
    tbody.innerHTML = `<tr><td colspan="5" class="empty-state">✗ ${escapeHtml(e.message)}</td></tr>`;
  }
}

function renderRow(r) {
  const perms = r.permissions || {};
  const menus = perms.menus || [];
  const menuLabel = menus.includes('*') ? 'Semua menu' : (menus.length ? `${menus.length} menu` : '-');

  const actions = [];
  if (r.is_mine) {
    actions.push(`<button class="btn btn-sm btn-secondary" onclick='openViewModal(${JSON.stringify(r)})'>Lihat</button>`);
  } else {
    if (CAN_EDIT) {
      actions.push(`<button class="btn btn-sm btn-secondary" onclick='openEditModal(${JSON.stringify(r)})'>Edit</button>`);
    }
    if (CAN_DELETE && !r.is_system) {
      actions.push(`<button class="btn btn-sm btn-danger" onclick="deleteRole('${r.id}', '${escapeHtml(r.role_name)}')">Hapus</button>`);
    }
  }

  return `
    <tr>
      <td>${escapeHtml(r.role_name)}<br><span class="role-key">${escapeHtml(r.role_key)}</span>${r.is_system ? '<span class="sys-badge">bawaan</span>' : ''}${r.is_mine ? '<span class="you-tag">(Role Anda)</span>' : ''}</td>
      <td><span class="level-badge">${r.level}</span></td>
      <td>${menuLabel}</td>
      <td style="max-width:220px;">${escapeHtml(r.description || '-')}</td>
      <td><div class="row-actions">${actions.join('') || '<span style="color:var(--text-4);font-size:12px;">-</span>'}</div></td>
    </tr>`;
}

function setChecked(id, val) { const el = document.getElementById(id); if (el) el.checked = val; }
function getChecked(id) { const el = document.getElementById(id); return el ? el.checked : false; }

function resetPermFields() {
  setChecked('menuAll', false);
  document.querySelectorAll('.menu-item-check').forEach(cb => { cb.checked = false; cb.disabled = false; });
  ['permAkunView','permAkunCreate','permAkunEdit','permAkunDeactivate','permAkunDelete',
   'permRoleView','permRoleCreate','permRoleEdit','permRoleDelete',
   'permListlinkAccess','permPengaturanCleanup'].forEach(id => setChecked(id, false));
}

function fillPermFields(permissions) {
  const p = permissions || {};
  const menus = p.menus || [];
  if (menus.includes('*') && document.getElementById('menuAll')) {
    setChecked('menuAll', true);
    toggleAllMenus(true);
  } else {
    document.querySelectorAll('.menu-item-check').forEach(cb => { cb.checked = menus.includes('*') || menus.includes(cb.value); });
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
  const menuAll = getChecked('menuAll');
  const menus = menuAll ? ['*'] : Array.from(document.querySelectorAll('.menu-item-check:checked')).map(cb => cb.value);
  return {
    menus,
    akun: {
      view: getChecked('permAkunView'), create: getChecked('permAkunCreate'), edit: getChecked('permAkunEdit'),
      deactivate: getChecked('permAkunDeactivate'), delete: getChecked('permAkunDelete'),
    },
    manajemen_role: {
      view: getChecked('permRoleView'), create: getChecked('permRoleCreate'), edit: getChecked('permRoleEdit'), delete: getChecked('permRoleDelete'),
    },
    listlink: { access: getChecked('permListlinkAccess') },
    pengaturan: { cleanup: getChecked('permPengaturanCleanup') },
  };
}

function openCreateModal() {
  document.getElementById('roleFormTitle').textContent = 'Tambah Role';
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
  document.getElementById('roleFormTitle').textContent = 'Edit Role';
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
  document.getElementById('roleFormTitle').textContent = 'Lihat Role - ' + r.role_name;
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
  document.querySelectorAll('.menu-item-check').forEach(cb => { cb.disabled = readonly; });
  const menuAll = document.getElementById('menuAll');
  if (menuAll) menuAll.disabled = readonly;
  document.querySelectorAll('.perm-check input[type="checkbox"]').forEach(cb => { cb.disabled = readonly; });
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

  btn.disabled = true; btn.textContent = 'Menyimpan…';
  clearModalMsg('roleFormMsg');
  try {
    const res = await fetch(`/admin/api/roles?action=${isCreate ? 'create' : 'update'}`, {
      method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN }, body: JSON.stringify(payload)
    });
    const json = await res.json();
    if (!res.ok) throw new Error(json.error || 'Gagal menyimpan role');
    showMsg('success', '✓ Role berhasil disimpan.');
    closeModal('roleFormModal');
    await loadRoles();
  } catch (e) {
    showModalMsg('roleFormMsg', 'error', '✗ ' + e.message);
  } finally {
    btn.disabled = false; btn.textContent = 'Simpan';
  }
}

async function deleteRole(id, name) {
  const ok = await confirmModal('Hapus role ini?', `"${name}" akan dihapus permanen. Role yang masih dipakai akun tidak bisa dihapus.`);
  if (!ok) return;
  try {
    const res = await fetch('/admin/api/roles?action=delete', {
      method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN }, body: JSON.stringify({ id })
    });
    const json = await res.json();
    if (!res.ok) throw new Error(json.error || 'Gagal menghapus role');
    showMsg('success', '✓ Role dihapus.');
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
