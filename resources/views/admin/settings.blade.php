<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Pengaturan Akun — Admin</title>
  <meta name="csrf-token" content="{{ csrf_token() }}"/>
  <link rel="canonical" href="{{ url('/admin/settings') }}"/>
  <link rel="icon" href="/favicon.ico"/>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/admin/admin-shell.css">
  <link rel="stylesheet" href="/assets/admin/settings.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
</head>
<body>
<div class="adm-shell">

  @include('admin.partials.sidebar', ['currentPage' => 'settings', 'currentModule' => session('admin_module')])

  <main class="adm-main">
    <div class="adm-content">

      <div class="adm-page-header">
        <div>
          <h1 class="adm-page-title">Pengaturan Akun</h1>
          <p class="adm-page-subtitle">Kelola kredensial login {{ $isSuperAdmin ? 'administrator' : 'akun Anda' }}</p>
        </div>
      </div>

      <div id="result-msg" class="alert" style="margin-bottom:20px;max-width:860px;"></div>

      <div class="settings-grid">

        @unless ($isSuperAdmin)
        <div class="card settings-card">
          <div class="card-header">
            <h2>Ganti Username</h2>
            <p>Ubah nama login akun Anda</p>
          </div>
          <div class="card-body">
            <div class="info-banner">
              <span class="icon">ℹ️</span>
              <div>Username saat ini: <strong>{{ session('admin_user', 'admin') }}</strong></div>
            </div>
            <div class="field">
              <label>Username Baru</label>
              <input type="text" id="newUsernameField" placeholder="Masukkan username baru" autocomplete="off"/>
            </div>
            <div class="field">
              <label>Password Sekarang (konfirmasi)</label>
              <input type="password" id="usernameConfirmPass" placeholder="Konfirmasi dengan password" autocomplete="current-password"/>
            </div>
          </div>
          <div class="card-footer">
            <button type="button" class="btn btn-primary" id="btnSaveUser" onclick="simpanUsername()">Simpan Username</button>
          </div>
        </div>
        @endunless

        <div class="card settings-card">
          <div class="card-header">
            <h2>Ganti Password</h2>
            <p>Perbarui kata sandi admin</p>
          </div>
          <div class="card-body">
            <div class="field">
              <label>Password Sekarang</label>
              <input type="password" id="currentPasswordField" placeholder="Password lama" autocomplete="current-password"/>
            </div>
            <div class="field">
              <label>Password Baru</label>
              <input type="password" id="newPassword" placeholder="Minimal 8 karakter" autocomplete="new-password"/>
              <div class="pw-strength"><div id="pwStrengthFill" class="pw-strength-fill"></div></div>
            </div>
            <div class="field">
              <label>Konfirmasi Password Baru</label>
              <input type="password" id="confirmPassword" placeholder="Ulangi password baru" autocomplete="new-password"/>
            </div>
          </div>
          <div class="card-footer">
            <button type="button" class="btn btn-primary" id="btnSavePass" onclick="simpanPassword()">Simpan Password</button>
          </div>
        </div>

      </div><!-- /.settings-grid -->

      <div class="card settings-card" style="max-width:860px;margin-top:22px;">
        <div class="card-header">
          <h2>Keamanan Login Akun (2FA)</h2>
          <p>Verifikasi dua langkah pakai aplikasi otentikator (Google Authenticator, Authy, dsb.)</p>
        </div>
        <div class="card-body">
          <div id="tfaLoading" style="font-size:13px;color:var(--text-4);">Memuat status...</div>

          <div id="tfaDisabledState" style="display:none;">
            <div class="info-banner">
              <span class="icon">🔓</span>
              <div>2FA belum aktif untuk akun Anda. Sangat disarankan untuk meningkatkan keamanan akun admin.</div>
            </div>
            <div id="tfaSetupStep1" style="margin-top:16px;">
              <button type="button" class="btn btn-primary" onclick="mulaiSetup2fa()">Aktifkan 2FA</button>
            </div>
            <div id="tfaSetupStep2" style="display:none;margin-top:16px;">
              <p style="font-size:13px;color:var(--text-2);">1. Scan QR code ini pakai aplikasi otentikator:</p>
              <div id="tfaQrCode" style="margin:14px 0;padding:16px;background:#fff;border:1px solid var(--border);border-radius:12px;display:inline-block;"></div>
              <p style="font-size:12px;color:var(--text-4);">Tidak bisa scan? Masukkan manual: <code id="tfaSecretText" style="font-family:'DM Mono',monospace;"></code></p>
              <p style="font-size:13px;color:var(--text-2);margin-top:14px;">2. Masukkan kode 6 digit yang muncul di aplikasi:</p>
              <div class="field" style="max-width:200px;">
                <input type="text" id="tfaConfirmCode" inputmode="numeric" maxlength="6" placeholder="000000" style="font-family:'DM Mono',monospace;font-size:18px;letter-spacing:4px;text-align:center;"/>
              </div>
              <button type="button" class="btn btn-primary" style="margin-top:10px;" onclick="konfirmasiSetup2fa()">Konfirmasi & Aktifkan</button>
            </div>
            <div id="tfaRecoveryCodes" style="display:none;margin-top:16px;">
              <div class="info-banner" style="background:#fff7ed;border-color:#fed7aa;color:#9a3412;">
                <span class="icon">⚠️</span>
                <div>
                  <strong>Simpan 8 kode pemulihan ini di tempat aman.</strong> Dipakai kalau HP/aplikasi
                  otentikator Anda hilang. Kode ini HANYA ditampilkan sekali.
                </div>
              </div>
              <div id="tfaRecoveryList" style="font-family:'DM Mono',monospace;font-size:14px;line-height:2;margin-top:10px;"></div>
              <button type="button" class="btn btn-secondary" style="margin-top:10px;" onclick="document.getElementById('tfaRecoveryCodes').style.display='none';loadTfaStatus();">Sudah saya simpan, selesai</button>
            </div>
          </div>

          <div id="tfaEnabledState" style="display:none;">
            <div class="info-banner" style="background:#f0fdf4;border-color:#bbf7d0;color:#15803d;">
              <span class="icon">🔒</span>
              <div>2FA sudah aktif untuk akun Anda.</div>
            </div>
            <div class="field" style="max-width:280px;margin-top:16px;">
              <label>Password (konfirmasi untuk menonaktifkan)</label>
              <input type="password" id="tfaDisablePassword" placeholder="Password Anda"/>
            </div>
            <button type="button" class="btn btn-secondary" style="margin-top:10px;" onclick="nonaktifkan2fa()">Nonaktifkan 2FA</button>
          </div>
        </div>
      </div>

      @if ($isSuperAdmin)
      <div class="card settings-card" style="max-width:860px;margin-top:22px;">
        <div class="card-header">
          <h2>Menu Layanan</h2>
          <p>Atur kartu layanan mana yang tampil di halaman utama website publik ({{ request()->getHost() }})</p>
        </div>
        <div class="card-body">
          <div id="layananLoading" class="layanan-loading">Memuat data...</div>
          <div id="layananEmpty" class="layanan-empty" style="display:none;">Belum ada data menu layanan. Jalankan SQL setup di Supabase terlebih dahulu.</div>
          <div id="layananList" class="layanan-list"></div>
        </div>
      </div>
      @endif

      @if ($isSuperAdmin || $hasCleanupPermission)
      <div class="card settings-card cleanup-card">
        <div class="card-header cleanup-header">
          <div class="cleanup-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>
          </div>
          <div>
            <h2>Pembersihan Data Lama</h2>
            <p>Hapus otomatis data absensi (6 bulan) & pemesanan trayek wisata (2 tahun)</p>
          </div>
        </div>
        <div class="card-body">
          <div class="info-banner" style="background:#fff7ed;border-color:#fed7aa;color:#9a3412;">
            <span class="icon">⚠️</span>
            <div>
              Membersihkan Data Absensi dan Absensi Foto yang sudah lebih dari <strong>6 bulan</strong>,
              serta Data Pemesanan Trayek Wisata (beserta rincian NIK penumpangnya) yang sudah
              lebih dari <strong>2 tahun</strong>.
              Tindakan ini bersifat <strong>permanen</strong> dan tidak dapat dibatalkan.
            </div>
          </div>
          @if ($cleanupToken === '')
          <div class="info-banner" style="background:#fef2f2;border-color:#fecaca;color:#991b1b;margin-top:10px;">
            <span class="icon">⛔</span>
            <div>
              <strong>CLEANUP_TOKEN</strong> belum diisi di <code>.env</code>. Tombol
              Pembersihan dinonaktifkan sampai kuncinya diisi.
            </div>
          </div>
          @endif

          <div id="cleanupResult" class="cleanup-result"></div>
        </div>
        <div class="card-footer">
          <span id="cleanupMeta" class="cleanup-meta">Belum pernah dijalankan pada sesi ini</span>
          <button type="button" class="btn btn-warning" id="btnCleanup" onclick="openCleanupModal()" {{ $cleanupToken === '' ? 'disabled' : '' }}>
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>
            Jalankan Pembersihan
          </button>
        </div>
      </div>

      <div class="modal-overlay" id="cleanupConfirmModal">
        <div class="modal-card">
          <div class="modal-icon warn">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
          </div>
          <h3>Konfirmasi Pembersihan Data</h3>
          <p>Anda akan membersihkan:</p>
          <ul style="margin:0 0 4px;padding-left:20px;line-height:1.7;">
            <li><strong>Data Absensi</strong> dan <strong>Absensi Foto</strong> yang sudah berumur lebih dari <strong>6 bulan</strong></li>
            <li><strong>Data Pemesanan Trayek Wisata</strong> (beserta rincian NIK penumpangnya) yang sudah berumur lebih dari <strong>2 tahun</strong></li>
          </ul>
          <p>Proses ini akan menghapus data langsung dari basis data.</p>
          <div class="modal-actions">
            <button type="button" class="btn btn-secondary" onclick="closeModal('cleanupConfirmModal')">Batal</button>
            <button type="button" class="btn btn-warning" onclick="goToFinalWarning()">Lanjutkan</button>
          </div>
        </div>
      </div>

      <div class="modal-overlay" id="cleanupFinalModal">
        <div class="modal-card modal-card-danger">
          <div class="modal-icon danger">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>
          </div>
          <h3>Peringatan Terakhir</h3>
          <p>Data yang terhapus <strong>tidak dapat dipulihkan kembali</strong>, termasuk dari cadangan (backup) sekalipun.</p>
          <div class="modal-note">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            <span>Pastikan Anda benar-benar sudah melakukan <strong>export data Excel</strong> untuk kebutuhan laporan absensi 6 bulan terakhir dan laporan pemesanan trayek wisata 2 tahun terakhir.</span>
          </div>
          <label class="modal-check">
            <input type="checkbox" id="cleanupAckCheck" onchange="document.getElementById('btnCleanupFinal').disabled = !this.checked;"/>
            <span>Saya memahami dan tetap ingin melanjutkan penghapusan.</span>
          </label>
          <div class="modal-actions">
            <button type="button" class="btn btn-secondary" onclick="closeModal('cleanupFinalModal')">Batal</button>
            <button type="button" class="btn btn-danger" id="btnCleanupFinal" disabled onclick="confirmCleanupExecute()">
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>
              Ya, Hapus Sekarang
            </button>
          </div>
        </div>
      </div>
      @endif

    </div>
  </main>
</div>

<script>
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;

const newPass = document.getElementById('newPassword');
const fill    = document.getElementById('pwStrengthFill');
if (newPass) newPass.addEventListener('input', () => {
  const v = newPass.value, len = v.length;
  let score = 0;
  if (len >= 8)  score++;
  if (len >= 12) score++;
  if (/[A-Z]/.test(v) && /[a-z]/.test(v)) score++;
  if (/[0-9]/.test(v)) score++;
  if (/[^A-Za-z0-9]/.test(v)) score++;
  const pct  = Math.min(100, score * 20);
  const clr  = score <= 1 ? '#ef4444' : score <= 2 ? '#f59e0b' : score <= 3 ? '#3b82f6' : '#10b981';
  fill.style.width = pct + '%'; fill.style.background = clr;
});

const confPass = document.getElementById('confirmPassword');
function checkMatch() {
  if (!confPass || !newPass) return;
  const ok = confPass.value === newPass.value;
  confPass.style.borderColor = confPass.value ? (ok ? 'var(--emerald)' : 'var(--rose)') : '';
}
if (confPass) { confPass.addEventListener('input', checkMatch); newPass.addEventListener('input', checkMatch); }

async function loadLayanan() {
  const loadingEl = document.getElementById('layananLoading');
  const emptyEl   = document.getElementById('layananEmpty');
  const listEl    = document.getElementById('layananList');
  if (!listEl) return;

  loadingEl.style.display = 'block';
  emptyEl.style.display   = 'none';
  listEl.innerHTML = '';

  try {
    const res  = await fetch('/admin/api/menu-layanan?action=list');
    const json = await res.json();
    if (json.status !== 'ok') throw new Error(json.error || 'Gagal memuat data.');

    const rows = json.data || [];
    loadingEl.style.display = 'none';

    if (rows.length === 0) {
      emptyEl.style.display = 'block';
      return;
    }

    listEl.innerHTML = rows.map(r => `
      <div class="layanan-row">
        <div class="layanan-info">
          <h4>${escapeHtml(r.nama)}</h4>
          <p>${escapeHtml(r.deskripsi || '')}</p>
        </div>
        <label class="switch">
          <input type="checkbox" data-id="${r.id}" ${r.aktif ? 'checked' : ''} onchange="toggleLayanan(this)"/>
          <span class="switch-track"></span>
        </label>
      </div>`).join('');
  } catch (e) {
    loadingEl.style.display = 'none';
    showMsg('error', '✗ ' + e.message);
  }
}

function escapeHtml(s) {
  const d = document.createElement('div');
  d.textContent = s;
  return d.innerHTML;
}

async function toggleLayanan(checkbox) {
  const id     = checkbox.dataset.id;
  const aktif  = checkbox.checked;
  checkbox.disabled = true;

  try {
    const res  = await fetch('/admin/api/menu-layanan?action=toggle_active', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
      body: JSON.stringify({ id, aktif })
    });
    const json = await res.json();
    if (json.status !== 'ok') throw new Error(json.error || 'Gagal mengubah status.');
    showMsg('success', aktif ? '✓ Menu diaktifkan.' : '✓ Menu dinonaktifkan.');
  } catch (e) {
    checkbox.checked = !aktif;
    showMsg('error', '✗ ' + e.message);
  } finally {
    checkbox.disabled = false;
  }
}

async function loadTfaStatus() {
  const l = document.getElementById('tfaLoading');
  const d = document.getElementById('tfaDisabledState');
  const en = document.getElementById('tfaEnabledState');
  if (!l || !d || !en) return;
  l.style.display = 'block';
  d.style.display = 'none';
  en.style.display = 'none';
  try {
    const res = await fetch('/admin/2fa/status');
    const json = await res.json();
    l.style.display = 'none';
    if (json.enabled) {
      en.style.display = 'block';
    } else {
      d.style.display = 'block';
      document.getElementById('tfaSetupStep1').style.display = 'block';
      document.getElementById('tfaSetupStep2').style.display = 'none';
    }
  } catch (e) {
    l.textContent = 'Gagal memuat status 2FA.';
  }
}

async function mulaiSetup2fa() {
  try {
    const res = await fetch('/admin/2fa/setup', { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF_TOKEN } });
    const json = await res.json();
    if (json.status !== 'ok') throw new Error(json.error || 'Gagal memulai setup.');

    document.getElementById('tfaSetupStep1').style.display = 'none';
    document.getElementById('tfaSetupStep2').style.display = 'block';
    document.getElementById('tfaSecretText').textContent = json.secret;

    const qrEl = document.getElementById('tfaQrCode');
    qrEl.innerHTML = '';
    new QRCode(qrEl, { text: json.uri, width: 200, height: 200 });
  } catch (e) {
    showMsg('error', '✗ ' + e.message);
  }
}

async function konfirmasiSetup2fa() {
  const code = document.getElementById('tfaConfirmCode').value.trim();
  if (!/^\d{6}$/.test(code)) { showMsg('error', '✗ Masukkan 6 digit kode.'); return; }
  try {
    const res = await fetch('/admin/2fa/confirm', {
      method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
      body: JSON.stringify({ code })
    });
    const json = await res.json();
    if (json.status !== 'ok') throw new Error(json.error || 'Kode salah.');

    document.getElementById('tfaSetupStep2').style.display = 'none';
    document.getElementById('tfaRecoveryCodes').style.display = 'block';
    document.getElementById('tfaRecoveryList').innerHTML = json.recovery_codes.map(c => `<div>${c}</div>`).join('');
    showMsg('success', '✓ 2FA berhasil diaktifkan.');
  } catch (e) {
    showMsg('error', '✗ ' + e.message);
  }
}

async function nonaktifkan2fa() {
  const password = document.getElementById('tfaDisablePassword').value;
  if (!password) { showMsg('error', '✗ Masukkan password.'); return; }
  if (!confirm('Yakin ingin menonaktifkan 2FA? Akun Anda hanya akan dilindungi password setelah ini.')) return;
  try {
    const res = await fetch('/admin/2fa/disable', {
      method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
      body: JSON.stringify({ password })
    });
    const json = await res.json();
    if (json.status !== 'ok') throw new Error(json.error || 'Gagal menonaktifkan.');
    showMsg('success', '✓ 2FA dinonaktifkan.');
    loadTfaStatus();
  } catch (e) {
    showMsg('error', '✗ ' + e.message);
  }
}

loadTfaStatus();

@if ($isSuperAdmin)
loadLayanan();
@endif

async function simpanUsername() {
  const newUser = document.getElementById('newUsernameField').value.trim();
  const curPw   = document.getElementById('usernameConfirmPass').value;

  if (!newUser) { showMsg('error', '✗ Masukkan username baru.'); return; }
  if (!curPw)   { showMsg('error', '✗ Masukkan password untuk konfirmasi.'); return; }

  const btn = document.getElementById('btnSaveUser');
  btn.disabled = true;
  btn.textContent = 'Menyimpan...';

  try {
    const res = await fetch('/admin/ganti-password', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
      body: JSON.stringify({ action: 'username', username: newUser, current_password: curPw })
    });
    const json = await res.json();
    if (json.status === 'ok') {
      showMsg('success', '✓ Username berhasil diubah.');
      document.getElementById('newUsernameField').value = '';
      document.getElementById('usernameConfirmPass').value = '';
    } else {
      showMsg('error', '✗ ' + (json.message || 'Gagal mengubah username.'));
    }
  } catch (e) {
    showMsg('error', '✗ Error: ' + e.message);
  } finally {
    btn.disabled = false;
    btn.textContent = 'Simpan Username';
  }
}

async function simpanPassword() {
  const curPw  = document.getElementById('currentPasswordField').value;
  const newPw  = document.getElementById('newPassword').value;
  const conPw  = document.getElementById('confirmPassword').value;

  if (!curPw)  { showMsg('error', '✗ Masukkan password sekarang.'); return; }
  if (!newPw)  { showMsg('error', '✗ Masukkan password baru.'); return; }
  if (newPw.length < 8) { showMsg('error', '✗ Password baru minimal 8 karakter.'); return; }
  if (newPw !== conPw) { showMsg('error', '✗ Konfirmasi password tidak cocok.'); return; }

  const btn = document.getElementById('btnSavePass');
  btn.disabled = true;
  btn.textContent = 'Menyimpan...';

  try {
    const res = await fetch('/admin/ganti-password', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
      body: JSON.stringify({ action: 'password_only', current_password: curPw, new_password: newPw, confirm_password: conPw })
    });
    const json = await res.json();
    if (json.status === 'ok') {
      showMsg('success', '✓ Password berhasil diubah.');
      document.getElementById('currentPasswordField').value = '';
      document.getElementById('newPassword').value = '';
      document.getElementById('confirmPassword').value = '';
      if (fill) { fill.style.width = '0'; }
      confPass.style.borderColor = '';
    } else {
      showMsg('error', '✗ ' + (json.message || 'Gagal mengubah password.'));
    }
  } catch (e) {
    showMsg('error', '✗ Error: ' + e.message);
  } finally {
    btn.disabled = false;
    btn.textContent = 'Simpan Password';
  }
}

function openModal(id) {
  const el = document.getElementById(id);
  if (!el) return;
  el.classList.add('show');
  document.body.style.overflow = 'hidden';
}
function closeModal(id) {
  const el = document.getElementById(id);
  if (!el) return;
  el.classList.remove('show');
  document.body.style.overflow = '';
}
document.querySelectorAll('.modal-overlay').forEach(overlay => {
  overlay.addEventListener('click', e => { if (e.target === overlay) closeModal(overlay.id); });
});
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') document.querySelectorAll('.modal-overlay.show').forEach(o => closeModal(o.id));
});

function openCleanupModal() { openModal('cleanupConfirmModal'); }

function goToFinalWarning() {
  closeModal('cleanupConfirmModal');
  const check = document.getElementById('cleanupAckCheck');
  const finalBtn = document.getElementById('btnCleanupFinal');
  if (check) check.checked = false;
  if (finalBtn) finalBtn.disabled = true;
  openModal('cleanupFinalModal');
}

function confirmCleanupExecute() {
  closeModal('cleanupFinalModal');
  jalankanCleanup();
}

async function jalankanCleanup() {
  const btn        = document.getElementById('btnCleanup');
  const resultBox  = document.getElementById('cleanupResult');
  const meta       = document.getElementById('cleanupMeta');
  const originalHTML = btn.innerHTML;

  btn.disabled = true;
  btn.innerHTML = '<div class="spinner" style="width:14px;height:14px;border-width:2px;"></div> Memproses...';
  resultBox.style.display = 'none';
  resultBox.innerHTML = '';

  try {
    const res  = await fetch('/admin/api/cleanup?token={{ urlencode($cleanupToken) }}');
    const json = await res.json();

    if (json.status === 'ok') {
      const rows = (json.log || [])
        .map(l => l.match(/^\[(OK|GAGAL)\]\s+(\S+)\s+—\s+(.*)$/))
        .filter(Boolean)
        .map(m => ({ status: m[1], table: m[2], detail: m[3] }));

      resultBox.innerHTML = rows.map((r, i) => `
        <div class="cleanup-row ${r.status === 'OK' ? 'ok' : 'fail'}" style="animation-delay:${i * 60}ms;">
          <span class="tbl">${r.table}</span>
          <span class="${r.status === 'OK' ? 'badge-ok' : 'badge-fail'}">
            ${r.status === 'OK'
              ? `<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> ${r.detail}`
              : `<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg> Gagal`}
          </span>
        </div>`).join('');
      resultBox.style.display = 'flex';

      const batasStr = new Date(json.batas).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
      const jamStr   = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
      meta.textContent = `Terakhir dijalankan hari ini pukul ${jamStr} · menghapus data sebelum ${batasStr}`;

      showMsg('success', '✓ Pembersihan data selesai.');
    } else {
      showMsg('error', '✗ ' + (json.message || 'Pembersihan data gagal.'));
    }
  } catch (e) {
    showMsg('error', '✗ Error: ' + e.message);
  } finally {
    btn.disabled = false;
    btn.innerHTML = originalHTML;
  }
}

function showMsg(type, text) {
  const el = document.getElementById('result-msg');
  el.className = `alert alert-${type}`;
  el.textContent = text;
  el.style.display = 'flex';
  el.style.opacity = '1';
  el.style.transition = '';
  clearTimeout(window._msgTimer);
  window._msgTimer = setTimeout(() => {
    el.style.transition = 'opacity .5s';
    el.style.opacity = '0';
    setTimeout(() => { el.style.display = 'none'; }, 500);
  }, 5000);
}
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
