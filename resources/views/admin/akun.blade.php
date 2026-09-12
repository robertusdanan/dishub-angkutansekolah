<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Akun - Admin</title>
  <link rel="canonical" href="{{ url('/admin/akun') }}"/>
  <link rel="icon" href="/favicon.ico"/>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/admin/admin-shell.css">

  <style>
    .toolbar { display: flex; justify-content: flex-end; margin-bottom: 16px; }

    .role-badge {
      display: inline-flex; align-items: center; gap: 5px;
      padding: 3px 10px; border-radius: 999px;
      font-size: 11.5px; font-weight: 600;
      background: var(--surface-2); color: var(--text-2); border: 1px solid var(--border);
    }
    .status-badge { display: inline-flex; align-items: center; gap: 5px; font-size: 12px; font-weight: 600; }
    .status-badge.on  { color: #15803d; }
    .status-badge.off { color: #94a3b8; }
    .status-dot { width: 7px; height: 7px; border-radius: 50%; background: currentColor; }

    .row-actions { display: flex; gap: 6px; flex-wrap: wrap; }
    .row-actions button { white-space: nowrap; }

    .you-tag { font-size: 10.5px; font-weight: 700; color: var(--accent); margin-left: 6px; }

    .empty-state { text-align: center; padding: 40px 20px; color: var(--text-4); font-size: 13.5px; }

    /* ── Modal form (Tambah/Edit Akun & Reset Password) ── */
    .modal-overlay { display: none; position: fixed; inset: 0; z-index: 200; background: rgba(15,23,42,.55); backdrop-filter: blur(3px); align-items: center; justify-content: center; padding: 20px; }
    .modal-overlay.show { display: flex; }
    .modal-form-card { background: var(--surface); border-radius: 18px; box-shadow: 0 24px 60px rgba(15,23,42,.28); width: 100%; max-width: 440px; }
    .modal-form-header { padding: 20px 24px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
    .modal-form-header h3 { font-size: 16px; font-weight: 700; color: var(--text-1); }
    .modal-form-close { background: none; border: none; cursor: pointer; color: var(--text-4); padding: 4px; }
    .modal-form-body { padding: 20px 24px; display: flex; flex-direction: column; gap: 14px; max-height: 60vh; overflow-y: auto; }
    .modal-form-footer { padding: 16px 24px; border-top: 1px solid var(--border); display: flex; gap: 10px; justify-content: flex-end; }
    .field { display: flex; flex-direction: column; gap: 5px; }
    .field label { font-size: 11.5px; font-weight: 600; letter-spacing: .4px; text-transform: uppercase; color: var(--text-3); }
    .field .hint { font-size: 11px; color: var(--text-4); }
    .field-checkbox { display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--text-2); }
    #result-msg { display: none; }
    .alert { border-radius: 10px; padding: 12px 16px; font-size: 13px; font-weight: 500; display: flex; align-items: center; gap: 10px; }
    .alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #15803d; }
    .alert-error   { background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; }
  </style>
</head>
<body>

<div class="adm-shell">
  @include('admin.partials.sidebar', ['currentPage' => 'akun', 'currentModule' => session('admin_module')])

  <main class="adm-main">
    <div class="adm-content">

      <div class="adm-page-header">
        <div>
          <h1 class="adm-page-title">Akun</h1>
          <p class="adm-page-subtitle">Kelola akun login admin - daftar, nonaktifkan, ubah role</p>
        </div>
      </div>

      <div id="result-msg" class="alert" style="margin-bottom:16px;"></div>

      @if ($canCreate)
      <div class="toolbar">
        <button class="btn btn-primary" onclick="openCreateModal()">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Tambah Akun
        </button>
      </div>
      @endif

      <div class="card table-wrapper">
        <table>
          <thead>
            <tr>
              <th>Username</th>
              <th>Nama Lengkap</th>
              <th>Role</th>
              <th>Status</th>
              <th>Login Terakhir</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody id="akunBody">
            <tr><td colspan="6" class="empty-state">Memuat data…</td></tr>
          </tbody>
        </table>
      </div>

    </div>
  </main>
</div>

<!-- ═══════ Modal Tambah/Edit Akun ═══════ -->
<div class="modal-overlay" id="akunFormModal">
  <div class="modal-form-card">
    <div class="modal-form-header">
      <h3 id="akunFormTitle">Tambah Akun</h3>
      <button class="modal-form-close" onclick="closeModal('akunFormModal')">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="modal-form-body">
      <div id="akunFormMsg" class="alert" style="display:none;"></div>
      <input type="hidden" id="akunId" value="">
      <div class="field">
        <label for="akunUsername">Username</label>
        <input type="text" id="akunUsername" class="form-control" placeholder="mis. operator1" autocomplete="off">
        <span class="hint">3-50 karakter, huruf/angka/._- saja. Tidak bisa diubah setelah dibuat.</span>
      </div>
      <div class="field">
        <label for="akunFullName">Nama Lengkap</label>
        <input type="text" id="akunFullName" class="form-control" placeholder="mis. Budi Santoso">
      </div>
      <div class="field" id="akunPasswordField">
        <label for="akunPassword">Password</label>
        <input type="password" id="akunPassword" class="form-control" placeholder="Minimal 8 karakter" autocomplete="new-password">
      </div>
      <div class="field">
        <label for="akunRole">Role</label>
        <select id="akunRole" class="form-control"></select>
      </div>
      <label class="field-checkbox">
        <input type="checkbox" id="akunMustChange">
        Wajib ganti password saat login berikutnya
      </label>
    </div>
    <div class="modal-form-footer">
      <button class="btn btn-secondary" onclick="closeModal('akunFormModal')">Batal</button>
      <button class="btn btn-primary" id="btnSaveAkun" onclick="saveAkun()">Simpan</button>
    </div>
  </div>
</div>

<!-- ═══════ Modal Reset Password ═══════ -->
<div class="modal-overlay" id="resetPassModal">
  <div class="modal-form-card">
    <div class="modal-form-header">
      <h3>Reset Password</h3>
      <button class="modal-form-close" onclick="closeModal('resetPassModal')">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="modal-form-body">
      <div id="resetPassMsg" class="alert" style="display:none;"></div>
      <input type="hidden" id="resetPassId" value="">
      <p style="font-size:13px;color:var(--text-3);">Mengatur password baru untuk <strong id="resetPassUsername"></strong>.</p>
      <div class="field">
        <label for="resetPassValue">Password Baru</label>
        <input type="password" id="resetPassValue" class="form-control" placeholder="Minimal 8 karakter" autocomplete="new-password">
      </div>
    </div>
    <div class="modal-form-footer">
      <button class="btn btn-secondary" onclick="closeModal('resetPassModal')">Batal</button>
      <button class="btn btn-warning" id="btnResetPass" onclick="submitResetPassword()">Reset Password</button>
    </div>
  </div>
</div>

<!-- ═══════ Modal Konfirmasi (hapus / nonaktifkan) ═══════ -->
<div class="confirm-modal-overlay" id="confirmModalOverlay">
  <div class="confirm-modal-box">
    <div class="confirm-modal-icon">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
    </div>
    <div class="confirm-modal-title" id="confirmModalTitle">Yakin?</div>
    <div class="confirm-modal-sub" id="confirmModalSub"></div>
    <div class="confirm-modal-actions">
      <button type="button" class="confirm-modal-cancel" id="confirmModalCancel">Batal</button>
      <button type="button" class="confirm-modal-danger" id="confirmModalOk">Ya, Lanjutkan</button>
    </div>
  </div>
</div>

<script>
const CAN_EDIT       = {{ $canEdit ? 'true' : 'false' }};
const CAN_DEACTIVATE = {{ $canDeactivate ? 'true' : 'false' }};
const CAN_DELETE     = {{ $canDelete ? 'true' : 'false' }};
const MY_ACCOUNT_ID  = {!! json_encode($myAccountId) !!};
const IS_SUPERADMIN  = {{ $isSuperAdmin ? 'true' : 'false' }};
const MY_LEVEL        = {{ $myLevel }};

let rolesCache = [];

function showMsg(type, text) {
  const el = document.getElementById('result-msg');
  el.className = `alert alert-${type}`;
  el.textContent = text;
  el.style.display = 'flex';
  clearTimeout(window._msgTimer);
  window._msgTimer = setTimeout(() => { el.style.display = 'none'; }, 5000);
}

// Pesan di DALAM modal - dipakai untuk error validasi (mis. "Password
// minimal 8 karakter") supaya tetap terlihat SELAGI modal masih terbuka.
// #result-msg di halaman utama tidak cukup karena tertutup backdrop modal.
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

function confirmModal(title, sub, okLabel = 'Ya, Lanjutkan') {
  const overlay = document.getElementById('confirmModalOverlay');
  document.getElementById('confirmModalTitle').textContent = title;
  document.getElementById('confirmModalSub').textContent = sub;
  document.getElementById('confirmModalOk').textContent = okLabel;
  overlay.classList.add('open');
  return new Promise(resolve => {
    const okBtn = document.getElementById('confirmModalOk');
    const cancelBtn = document.getElementById('confirmModalCancel');
    const cleanup = (r) => {
      overlay.classList.remove('open');
      okBtn.removeEventListener('click', onOk);
      cancelBtn.removeEventListener('click', onCancel);
      resolve(r);
    };
    const onOk = () => cleanup(true);
    const onCancel = () => cleanup(false);
    okBtn.addEventListener('click', onOk);
    cancelBtn.addEventListener('click', onCancel);
  });
}

async function loadRoles() {
  const res = await fetch('/admin/api/roles?action=list');
  const json = await res.json();
  if (!res.ok) throw new Error(json.error || 'Gagal memuat role');
  // roles.php list sekarang juga menyertakan role yang sedang dipakai
  // akun sendiri (is_mine, read-only) supaya terlihat di Manajemen
  // Role - tapi role itu TIDAK BOLEH dipilih di sini saat membuat/
  // memindahkan akun (backend menolaknya, canManageLevelStrict butuh
  // level LEBIH RENDAH, bukan setara), jadi disaring dari dropdown.
  rolesCache = (json.data || []).filter(r => !r.is_mine);
  const sel = document.getElementById('akunRole');
  sel.innerHTML = rolesCache.map(r => `<option value="${r.id}">${escapeHtml(r.role_name)} (level ${r.level})</option>`).join('');
}

async function loadAkun() {
  const tbody = document.getElementById('akunBody');
  try {
    const res = await fetch('/admin/api/akun?action=list');
    const json = await res.json();
    if (!res.ok) throw new Error(json.error || 'Gagal memuat akun');
    const rows = json.data || [];
    if (rows.length === 0) {
      tbody.innerHTML = `<tr><td colspan="6" class="empty-state">Belum ada akun.</td></tr>`;
      return;
    }
    tbody.innerHTML = rows.map(renderRow).join('');
  } catch (e) {
    tbody.innerHTML = `<tr><td colspan="6" class="empty-state">✗ ${escapeHtml(e.message)}</td></tr>`;
  }
}

function renderRow(a) {
  const role = a.admin_roles || {};
  const isMe = a.id === MY_ACCOUNT_ID;
  const roleLevel = Number(role.level ?? 0);
  // Akun dengan level SETARA (bukan lebih rendah) hanya boleh dilihat,
  // tidak bisa dikelola - sesuai backend (canManageLevelStrict butuh
  // level LEBIH RENDAH, bukan setara). Superadmin selalu bebas dari
  // batasan ini.
  const isManageable = IS_SUPERADMIN || roleLevel < MY_LEVEL;
  const lastLogin = a.last_login_at
    ? new Date(a.last_login_at).toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })
    : '-';

  const actions = [];
  // Non-superadmin tidak bisa lagi mengedit/reset password akun sendiri
  // lewat panel ini (backend sekarang mewajibkan level LEBIH TINGGI dari
  // target, dan level akun sendiri tidak pernah lebih tinggi dari
  // dirinya sendiri) - arahkan ke menu Pengaturan untuk kelola akun
  // sendiri. Superadmin dikecualikan (selalu lolos di backend).
  if (isManageable) {
    if (CAN_EDIT && (!isMe || IS_SUPERADMIN)) {
      actions.push(`<button class="btn btn-sm btn-secondary" onclick='openEditModal(${JSON.stringify(a)})'>Edit</button>`);
      actions.push(`<button class="btn btn-sm btn-secondary" onclick="openResetPasswordModal('${a.id}', '${escapeHtml(a.username)}')">Reset Password</button>`);
    }
    if (CAN_DEACTIVATE && !isMe) {
      actions.push(a.is_active
        ? `<button class="btn btn-sm btn-warning" onclick="toggleActive('${a.id}', false, '${escapeHtml(a.username)}')">Nonaktifkan</button>`
        : `<button class="btn btn-sm btn-success" onclick="toggleActive('${a.id}', true, '${escapeHtml(a.username)}')">Aktifkan</button>`);
    }
    if (CAN_DELETE && !isMe) {
      actions.push(`<button class="btn btn-sm btn-danger" onclick="deleteAkun('${a.id}', '${escapeHtml(a.username)}')">Hapus</button>`);
    }
  }

  const actionsHtml = actions.length
    ? actions.join('')
    : (!isManageable
        ? '<span style="color:var(--text-4);font-size:12px;"></span>'
        : '<span style="color:var(--text-4);font-size:12px;">-</span>');

  return `
    <tr>
      <td>${escapeHtml(a.username)}${isMe ? '<span class="you-tag">(Anda)</span>' : ''}</td>
      <td>${escapeHtml(a.full_name || '-')}</td>
      <td><span class="role-badge">${escapeHtml(role.role_name || '-')}</span></td>
      <td><span class="status-badge ${a.is_active ? 'on' : 'off'}"><span class="status-dot"></span>${a.is_active ? 'Aktif' : 'Nonaktif'}</span></td>
      <td>${lastLogin}</td>
      <td><div class="row-actions">${actionsHtml}</div></td>
    </tr>`;
}

function escapeHtml(s) {
  return String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
}

// ── Create / Edit ──
function openCreateModal() {
  document.getElementById('akunFormTitle').textContent = 'Tambah Akun';
  clearModalMsg('akunFormMsg');
  document.getElementById('akunId').value = '';
  document.getElementById('akunUsername').value = '';
  document.getElementById('akunUsername').disabled = false;
  document.getElementById('akunFullName').value = '';
  document.getElementById('akunPassword').value = '';
  document.getElementById('akunPasswordField').style.display = '';
  document.getElementById('akunMustChange').checked = false;
  openModal('akunFormModal');
}

function openEditModal(a) {
  document.getElementById('akunFormTitle').textContent = 'Edit Akun';
  clearModalMsg('akunFormMsg');
  document.getElementById('akunId').value = a.id;
  document.getElementById('akunUsername').value = a.username;
  document.getElementById('akunUsername').disabled = true;
  document.getElementById('akunFullName').value = a.full_name || '';
  document.getElementById('akunPasswordField').style.display = 'none';
  document.getElementById('akunRole').value = a.role_id;
  document.getElementById('akunMustChange').checked = !!a.must_change_password;
  openModal('akunFormModal');
}

async function saveAkun() {
  const id = document.getElementById('akunId').value;
  const username = document.getElementById('akunUsername').value.trim();
  const fullName = document.getElementById('akunFullName').value.trim();
  const roleId = document.getElementById('akunRole').value;
  const mustChange = document.getElementById('akunMustChange').checked;
  const btn = document.getElementById('btnSaveAkun');

  const isCreate = !id;
  const payload = isCreate
    ? { username, password: document.getElementById('akunPassword').value, full_name: fullName, role_id: roleId }
    : { id, full_name: fullName, role_id: roleId, must_change_password: mustChange };

  btn.disabled = true; btn.textContent = 'Menyimpan…';
  clearModalMsg('akunFormMsg');
  try {
    const res = await fetch(`/admin/api/akun?action=${isCreate ? 'create' : 'update'}`, {
      method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload)
    });
    const json = await res.json();
    if (!res.ok) throw new Error(json.error || 'Gagal menyimpan akun');
    showMsg('success', '✓ Akun berhasil disimpan.');
    closeModal('akunFormModal');
    await loadAkun();
  } catch (e) {
    showModalMsg('akunFormMsg', 'error', '✗ ' + e.message);
  } finally {
    btn.disabled = false; btn.textContent = 'Simpan';
  }
}

// ── Reset password ──
function openResetPasswordModal(id, username) {
  clearModalMsg('resetPassMsg');
  document.getElementById('resetPassId').value = id;
  document.getElementById('resetPassUsername').textContent = username;
  document.getElementById('resetPassValue').value = '';
  openModal('resetPassModal');
}

async function submitResetPassword() {
  const id = document.getElementById('resetPassId').value;
  const newPassword = document.getElementById('resetPassValue').value;
  const btn = document.getElementById('btnResetPass');
  clearModalMsg('resetPassMsg');
  if (newPassword.length < 8) { showModalMsg('resetPassMsg', 'error', '✗ Password minimal 8 karakter.'); return; }

  btn.disabled = true; btn.textContent = 'Memproses…';
  try {
    const res = await fetch('/admin/api/akun?action=reset_password', {
      method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id, new_password: newPassword })
    });
    const json = await res.json();
    if (!res.ok) throw new Error(json.error || 'Gagal reset password');
    showMsg('success', '✓ Password berhasil direset.');
    closeModal('resetPassModal');
  } catch (e) {
    showModalMsg('resetPassMsg', 'error', '✗ ' + e.message);
  } finally {
    btn.disabled = false; btn.textContent = 'Reset Password';
  }
}

// ── Toggle active / Delete ──
async function toggleActive(id, makeActive, username) {
  const ok = await confirmModal(
    makeActive ? 'Aktifkan akun ini?' : 'Nonaktifkan akun ini?',
    makeActive ? `"${username}" akan bisa login kembali.` : `"${username}" tidak akan bisa login sampai diaktifkan kembali.`,
    makeActive ? 'Ya, Aktifkan' : 'Ya, Nonaktifkan'
  );
  if (!ok) return;
  try {
    const res = await fetch('/admin/api/akun?action=toggle_active', {
      method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id, is_active: makeActive })
    });
    const json = await res.json();
    if (!res.ok) throw new Error(json.error || 'Gagal mengubah status');
    showMsg('success', '✓ Status akun diperbarui.');
    await loadAkun();
  } catch (e) {
    showMsg('error', '✗ ' + e.message);
  }
}

async function deleteAkun(id, username) {
  const ok = await confirmModal('Hapus akun ini?', `"${username}" akan dihapus permanen dan tidak bisa dikembalikan.`, 'Ya, Hapus');
  if (!ok) return;
  try {
    const res = await fetch('/admin/api/akun?action=delete', {
      method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id })
    });
    const json = await res.json();
    if (!res.ok) throw new Error(json.error || 'Gagal menghapus akun');
    showMsg('success', '✓ Akun dihapus.');
    await loadAkun();
  } catch (e) {
    showMsg('error', '✗ ' + e.message);
  }
}

(async function init() {
  try {
    await loadRoles();
  } catch (e) {
    showMsg('error', '✗ ' + e.message);
  }
  await loadAkun();
})();
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
