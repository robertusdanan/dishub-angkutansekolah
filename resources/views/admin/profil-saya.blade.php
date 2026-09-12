<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<meta name="csrf-token" content="{{ csrf_token() }}"/>
<title>Profil Saya - Dishub Tulungagung</title>
<link rel="canonical" href="{{ url('/admin/profil-saya') }}"/>
<link rel="icon" href="/favicon.ico"/>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/admin/admin-shell.css">

<style>
  .profil-head { display: flex; align-items: center; gap: 16px; padding: 20px 22px; margin-bottom: 20px; }
  .profil-avatar { width: 52px; height: 52px; border-radius: 50%; background: linear-gradient(135deg, var(--accent), #818cf8); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: 700; flex-shrink: 0; }
  .profil-name { font-size: 16.5px; font-weight: 700; color: var(--text-1); }
  .profil-email { font-size: 12.5px; color: var(--text-4); margin-top: 2px; }
  .profil-note { font-size: 12.5px; color: var(--text-4); margin: -6px 0 20px; }
  .profil-note a { color: var(--accent); font-weight: 600; text-decoration: none; }
  .welcome-banner { background: #eff6ff; border: 1.5px solid #bfdbfe; border-radius: 14px; padding: 16px 20px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
  .welcome-banner-text { font-size: 13.5px; color: var(--text-1); }
  .welcome-banner-text b { display: block; font-size: 15px; margin-bottom: 2px; }
  .profil-card { padding: 24px; margin-bottom: 20px; }
  .profil-card-title { font-size: 15.5px; font-weight: 700; color: var(--text-1); }
  .profil-card-sub { font-size: 12.5px; color: var(--text-4); margin: 4px 0 18px; }
  .field { display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; }
  .field label { font-size: 12px; font-weight: 600; color: var(--text-2); }
  .field .hint { font-size: 11.5px; color: var(--text-4); }
  .field .hint.err { color: var(--rose); }
  .field .hint.ok  { color: var(--emerald); }
  .form-control.invalid { border-color: var(--rose); }
  .field-checkbox { display: flex; align-items: center; gap: 8px; font-size: 12.5px; color: var(--text-2); margin-top: -4px; }
  .field-two { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
  @media (max-width: 640px) { .field-two { grid-template-columns: 1fr; } }
  .doc-row { display: flex; align-items: center; gap: 16px; padding: 14px; border: 1.5px solid var(--border); border-radius: 12px; margin-bottom: 12px; }
  .doc-thumb { width: 64px; height: 44px; border-radius: 8px; background: var(--surface-2) center/cover no-repeat; flex-shrink: 0; display: flex; align-items: center; justify-content: center; color: var(--text-4); font-size: 10px; font-weight: 700; }
  .doc-info { flex: 1; }
  .doc-name { font-weight: 600; font-size: 13.5px; color: var(--text-1); }
  .doc-status { font-size: 11.5px; margin-top: 2px; }
  .doc-status.done { color: var(--emerald); font-weight: 600; }
  .doc-status.pending { color: var(--text-4); }
  .fam-item { display: flex; align-items: center; gap: 12px; padding: 12px 14px; border: 1.5px solid var(--border); border-radius: 12px; margin-bottom: 10px; }
  .fam-body { flex: 1; font-size: 13.5px; color: var(--text-1); }
  .fam-nik { font-family: 'DM Mono', monospace; font-size: 11.5px; color: var(--text-4); }
  .fam-tag { font-size: 10.5px; padding: 2px 9px; border-radius: 100px; background: var(--accent-glow); color: var(--accent); font-weight: 700; text-transform: capitalize; flex-shrink: 0; }
  .fam-add-grid { display: grid; grid-template-columns: 1fr 1fr 120px auto; gap: 10px; margin-top: 14px; }
  @media (max-width: 760px) { .fam-add-grid { grid-template-columns: 1fr; } }
  .fam-empty { font-size: 12.5px; color: var(--text-4); }
  .btn-ghost  { background: var(--surface-2); color: var(--text-2); border: 1.5px solid var(--border); }
  .btn-ghost:hover  { background: var(--border); }
  .btn-solid  { background: var(--accent); color: #fff; }
  .btn-solid:hover  { background: #2563eb; }
  .modal-overlay { display: none; position: fixed; inset: 0; z-index: 300; background: rgba(15,23,42,.55); backdrop-filter: blur(3px); align-items: center; justify-content: center; padding: 20px; }
  .modal-overlay.open { display: flex; }
  .modal-box { background: var(--surface); border-radius: 18px; width: 100%; max-width: 440px; padding: 24px; box-shadow: 0 24px 60px rgba(15,23,42,.28); }
  .profil-toast { position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%) translateY(20px); z-index: 999; background: var(--text-1); color: #fff; padding: 12px 20px; border-radius: 100px; font-size: 13px; font-weight: 600; opacity: 0; pointer-events: none; transition: .3s; }
  .profil-toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
</style>
</head>
<body>

<div class="adm-shell">
  @include('admin.partials.sidebar', ['currentPage' => 'profil_saya', 'currentModule' => null])

  <main class="adm-main">
    <div class="adm-content" style="max-width:820px;">

      <div class="adm-page-header">
        <div>
          <h1 class="adm-page-title">Profil Saya</h1>
          <p class="adm-page-subtitle">Kelola data diri, dokumen, dan anggota keluarga atas nama {{ $namaAkun }}</p>
        </div>
      </div>

      @if ($welcome)
      <div class="welcome-banner" id="welcomeBanner">
        <div class="welcome-banner-text">
          <b>Selamat datang! Lengkapi dulu Profil Saya.</b>
          Isi Data Diri di bawah supaya akun Anda siap dipakai untuk memesan tiket.
        </div>
        @if ($next !== '')
        <button type="button" class="btn btn-primary btn-sm" onclick="lanjutkanSetelahProfil()">Lanjutkan →</button>
        @endif
      </div>
      @endif

      <div class="card profil-head">
        <div class="profil-avatar" id="avatarWrap">{{ strtoupper(mb_substr($namaAkun, 0, 1)) }}</div>
        <div>
          <div class="profil-name">{{ $namaAkun }}</div>
          <div class="profil-email">{{ $emailAkun }}</div>
        </div>
      </div>
      <p class="profil-note">
        Akun ini satu untuk semua layanan - data di halaman ini juga dipakai saat memesan
        <a href="/balikgratis">Balik Gratis</a> dan <a href="/trayek-wisata">Trayek Wisata</a>.
      </p>

      <!-- ── Data Diri ─────────────────────────────────────────────── -->
      <div class="card profil-card">
        <div class="profil-card-title">Data Diri</div>
        <div class="profil-card-sub">Wajib diisi &amp; sesuai KTP sebelum bisa memesan kursi.</div>

        <div class="field">
          <label for="p_nama">Nama Lengkap</label>
          <input class="form-control" id="p_nama" placeholder="Nama sesuai KTP"/>
          <label class="field-checkbox">
            <input type="checkbox" id="p_nama_sesuai"/> Nama di atas sudah sama persis dengan KTP saya
          </label>
        </div>

        <div class="field-two">
          <div class="field">
            <label for="p_nik">NIK</label>
            <input class="form-control" id="p_nik" placeholder="16 digit sesuai KTP" inputmode="numeric" maxlength="16" oninput="validateNikLive()"/>
            <span class="hint" id="p_nik_hint">16 digit angka.</span>
          </div>
          <div class="field">
            <label for="p_hp">Nomor HP</label>
            <input class="form-control" id="p_hp" placeholder="08xxxxxxxxxx" inputmode="numeric" maxlength="14" oninput="validateHpLive()"/>
            <span class="hint" id="p_hp_hint">Angka saja, 9-14 digit.</span>
          </div>
        </div>

        <div class="field-two">
          <div class="field">
            <label for="p_jk">Jenis Kelamin</label>
            <select class="form-control" id="p_jk">
              <option value="">- Pilih -</option>
              <option value="Laki-laki">Laki-laki</option>
              <option value="Perempuan">Perempuan</option>
            </select>
          </div>
          <div class="field">
            <label for="p_no_kk">Nomor Kartu Keluarga <span style="color:var(--text-4);font-weight:400">(untuk Balik Gratis)</span></label>
            <input class="form-control" id="p_no_kk" placeholder="16 digit nomor KK" inputmode="numeric" maxlength="16"/>
          </div>
        </div>

        <div class="field">
          <label for="p_alamat">Alamat Lengkap</label>
          <input class="form-control" id="p_alamat" placeholder="Nama jalan, RT/RW, desa/kelurahan"/>
        </div>
        <div class="field-two">
          <div class="field">
            <label for="p_kabupaten">Kabupaten/Kota</label>
            <input class="form-control" id="p_kabupaten" placeholder="mis. Tulungagung"/>
          </div>
          <div class="field">
            <label for="p_kecamatan">Kecamatan</label>
            <input class="form-control" id="p_kecamatan" placeholder="mis. Kedungwaru"/>
          </div>
        </div>
        <div class="field" style="max-width:180px">
          <label for="p_kodepos">Kode Pos</label>
          <input class="form-control" id="p_kodepos" placeholder="66xxx" inputmode="numeric" maxlength="5"/>
        </div>

        <button class="btn btn-primary btn-sm" onclick="saveProfil()" id="btnSaveProfil">Simpan Data Diri</button>
      </div>

      <!-- ── Dokumen ───────────────────────────────────────────────── -->
      <div class="card profil-card">
        <div class="profil-card-title">Verifikasi Dokumen</div>
        <div class="profil-card-sub">Ambil foto langsung lewat kamera - tidak bisa upload dari galeri.</div>

        <div class="doc-row">
          <div class="doc-thumb" id="thumbKtp">KTP</div>
          <div class="doc-info"><div class="doc-name">Foto KTP</div><div class="doc-status pending" id="statusKtp">Belum diambil</div></div>
          <button class="btn btn-secondary btn-sm" onclick="openScan('ktp')">Ambil Foto</button>
        </div>
        <div class="doc-row">
          <div class="doc-thumb" id="thumbKk">KK</div>
          <div class="doc-info"><div class="doc-name">Foto Kartu Keluarga <span style="color:var(--text-4);font-weight:400">(opsional)</span></div><div class="doc-status pending" id="statusKk">Belum diambil</div></div>
          <button class="btn btn-secondary btn-sm" onclick="openScan('kk')">Ambil Foto</button>
        </div>
      </div>

      <!-- ── Anggota Keluarga ──────────────────────────────────────── -->
      <div class="card profil-card">
        <div class="profil-card-title">Anggota Keluarga</div>
        <div class="profil-card-sub">Bisa ikut dipesankan kursi - tiap NIK tetap tunduk aturan 1 tiket / 4 minggu.</div>
        <div id="famList"></div>
        <div class="fam-add-grid">
          <input class="form-control" id="fam_nama" placeholder="Nama anggota"/>
          <input class="form-control" id="fam_nik" placeholder="NIK (16 digit)" inputmode="numeric" maxlength="16"/>
          <select class="form-control" id="fam_jk">
            <option value="">Kelamin</option><option value="Laki-laki">L</option><option value="Perempuan">P</option>
          </select>
          <select class="form-control" id="fam_status">
            <option value="anak">Anak</option><option value="istri">Istri</option><option value="suami">Suami</option><option value="orang_tua">Orang Tua</option><option value="saudara">Saudara</option><option value="lainnya">Lainnya</option>
          </select>
          <button class="btn btn-primary btn-sm" onclick="addFamily()">+ Tambah</button>
        </div>
      </div>

    </div>
  </main>
</div>

<!-- ═══ Modal Scan Kamera ═══ -->
<div class="modal-overlay" id="scanOverlay">
  <div class="modal-box">
    <h3 style="font-size:16px;font-weight:700;color:var(--text-1);margin:0 0 14px">Scan Dokumen</h3>
    <div id="scanContainer"></div>
  </div>
</div>

<div class="profil-toast" id="profilToastEl"></div>

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

<script>
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;

function lanjutkanSetelahProfil(){
  window.location.href = {!! json_encode($next !== '' ? $next : '/') !!};
}

function profilToast(msg){ const el=document.getElementById('profilToastEl'); el.textContent=msg; el.classList.add('show'); clearTimeout(window._tt); window._tt=setTimeout(()=>el.classList.remove('show'),3200); }

let currentProfil = null;
let currentFamily = [];

async function loadProfil() {
  const res = await fetch('/akun/api/profil');
  if (!res.ok) return;
  currentProfil = await res.json();
  document.getElementById('p_nama').value = currentProfil.nama || '';
  document.getElementById('p_nama_sesuai').checked = !!currentProfil.nama_sesuai_ktp;
  document.getElementById('p_nik').value = currentProfil.nik || '';
  document.getElementById('p_hp').value = currentProfil.no_hp || '';
  document.getElementById('p_jk').value = currentProfil.jenis_kelamin || '';
  document.getElementById('p_no_kk').value = currentProfil.no_kk || '';
  document.getElementById('p_alamat').value = currentProfil.alamat_lengkap || '';
  document.getElementById('p_kabupaten').value = currentProfil.alamat_kabupaten || '';
  document.getElementById('p_kecamatan').value = currentProfil.alamat_kecamatan || '';
  document.getElementById('p_kodepos').value = currentProfil.alamat_kode_pos || '';
  validateNikLive(); validateHpLive();

  updateDocStatus('ktp', currentProfil.foto_ktp_url);
  updateDocStatus('kk', currentProfil.foto_kk_url);
}

function updateDocStatus(jenis, path) {
  const statusEl = document.getElementById('status' + (jenis === 'ktp' ? 'Ktp' : 'Kk'));
  const thumbEl = document.getElementById('thumb' + (jenis === 'ktp' ? 'Ktp' : 'Kk'));
  statusEl.textContent = path ? '✓ Sudah diambil' : 'Belum diambil';
  statusEl.className = 'doc-status ' + (path ? 'done' : 'pending');
  if (path) {
    thumbEl.style.backgroundImage = `url('/akun/api/dokumen-view?jenis=${jenis}&t=${Date.now()}')`;
    thumbEl.textContent = '';
  }
}

function validateNikLive() {
  const v = document.getElementById('p_nik').value.replace(/\D/g,'');
  document.getElementById('p_nik').value = v;
  const ok = /^\d{16}$/.test(v);
  const hint = document.getElementById('p_nik_hint');
  document.getElementById('p_nik').classList.toggle('invalid', v.length > 0 && !ok);
  hint.textContent = v.length === 0 ? '16 digit angka.' : (ok ? '✓ Format valid' : `${v.length}/16 digit`);
  hint.className = 'hint ' + (v.length === 0 ? '' : (ok ? 'ok' : 'err'));
  return ok;
}
function validateHpLive() {
  const v = document.getElementById('p_hp').value.replace(/\D/g,'');
  document.getElementById('p_hp').value = v;
  const ok = /^\d{9,14}$/.test(v);
  const hint = document.getElementById('p_hp_hint');
  document.getElementById('p_hp').classList.toggle('invalid', v.length > 0 && !ok);
  hint.textContent = v.length === 0 ? 'Angka saja, 9-14 digit.' : (ok ? '✓ Format valid' : `${v.length}/9-14 digit`);
  hint.className = 'hint ' + (v.length === 0 ? '' : (ok ? 'ok' : 'err'));
  return ok;
}

async function saveProfil() {
  const nama = document.getElementById('p_nama').value.trim();
  if (!nama) return profilToast('Nama wajib diisi');
  if (!validateNikLive()) return profilToast('NIK belum valid');
  if (!validateHpLive()) return profilToast('Nomor HP belum valid');

  const btn = document.getElementById('btnSaveProfil');
  btn.disabled = true; btn.textContent = 'Menyimpan...';
  try {
    const res = await fetch('/akun/api/profil', {
      method: 'POST', headers: {'Content-Type':'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN},
      body: JSON.stringify({
        nama, nama_sesuai_ktp: document.getElementById('p_nama_sesuai').checked,
        nik: document.getElementById('p_nik').value, no_hp: document.getElementById('p_hp').value,
        jenis_kelamin: document.getElementById('p_jk').value,
        no_kk: document.getElementById('p_no_kk').value,
        alamat_lengkap: document.getElementById('p_alamat').value,
        alamat_kabupaten: document.getElementById('p_kabupaten').value,
        alamat_kecamatan: document.getElementById('p_kecamatan').value,
        alamat_kode_pos: document.getElementById('p_kodepos').value,
      }),
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.error);
    profilToast('✓ Data diri tersimpan');
  } catch (e) { profilToast('Gagal: ' + e.message); }
  finally { btn.disabled = false; btn.textContent = 'Simpan Data Diri'; }
}

function openScan(jenis) {
  const overlay = document.getElementById('scanOverlay');
  const container = document.getElementById('scanContainer');
  overlay.classList.add('open');
  const scanner = createCameraScan({
    container, jenis,
    onCapture: async (blob) => {
      overlay.classList.remove('open');
      if (!blob) return;
      const fd = new FormData();
      fd.append('jenis', jenis);
      fd.append('file', blob, jenis + '.jpg');
      profilToast('Mengunggah...');
      try {
        const res = await fetch('/akun/api/upload-dokumen', { method:'POST', headers: {'X-CSRF-TOKEN': CSRF_TOKEN}, body: fd });
        const data = await res.json();
        if (!res.ok) throw new Error(data.error);
        updateDocStatus(jenis, data.path);
        profilToast('✓ Foto tersimpan');
      } catch (e) { profilToast('Gagal upload: ' + e.message); }
    },
  });
}

async function loadFamily() {
  const res = await fetch('/akun/api/keluarga');
  currentFamily = res.ok ? await res.json() : [];
  renderFamily();
}
function renderFamily() {
  const wrap = document.getElementById('famList');
  wrap.innerHTML = currentFamily.map(f => `
    <div class="fam-item">
      <span class="fam-tag">${f.status_keluarga}</span>
      <div class="fam-body"><div>${f.nama} ${f.jenis_kelamin ? '· ' + (f.jenis_kelamin === 'Laki-laki' ? 'L' : 'P') : ''}</div><div class="fam-nik">${f.nik}</div></div>
      <button class="btn btn-secondary btn-sm" onclick="delFamily('${f.id}')">Hapus</button>
    </div>`).join('') || '<p class="fam-empty">Belum ada anggota keluarga ditambahkan.</p>';
}
async function addFamily() {
  const nama = document.getElementById('fam_nama').value.trim();
  const nik  = document.getElementById('fam_nik').value.trim();
  const jenis_kelamin = document.getElementById('fam_jk').value;
  const status = document.getElementById('fam_status').value;
  if (!nama || !/^\d{16}$/.test(nik)) return profilToast('Nama & NIK (16 digit) wajib diisi benar');
  try {
    const res = await fetch('/akun/api/keluarga', { method:'POST', headers:{'Content-Type':'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN}, body: JSON.stringify({nama,nik,jenis_kelamin,status}) });
    const data = await res.json();
    if (!res.ok) throw new Error(data.error);
    currentFamily.push(data.anggota ?? data); renderFamily();
    document.getElementById('fam_nama').value = ''; document.getElementById('fam_nik').value = '';
    profilToast('✓ Anggota keluarga ditambahkan');
  } catch (e) { profilToast('Gagal: ' + e.message); }
}
async function delFamily(id) {
  if (!confirm('Hapus anggota keluarga ini?')) return;
  await fetch('/akun/api/keluarga?id=' + id, { method: 'DELETE', headers: {'X-CSRF-TOKEN': CSRF_TOKEN} });
  currentFamily = currentFamily.filter(f => f.id !== id); renderFamily();
}

loadProfil(); loadFamily();
</script>
<script src="/assets/akun/camera-scan.js"></script>
</body>
</html>
