<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<meta name="csrf-token" content="{{ csrf_token() }}"/>
<title>Profil Saya - Dishub Kabupaten Tulungagung</title>
<link rel="icon" href="/favicon.ico"/>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,300..700;1,9..144,400..600&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/akun/style.css"/>
<style>
  body{ background:var(--paper-2); }
  .akun-shell{ max-width:880px; margin:0 auto; padding:110px 28px 100px; }
  .akun-head{ display:flex; align-items:center; gap:16px; margin-bottom:36px; }
  .akun-avatar{ width:58px; height:58px; border-radius:50%; background:var(--forest); display:flex; align-items:center; justify-content:center; color:#fff; font-family:var(--f-display); font-size:22px; overflow:hidden; }
  .akun-avatar img{ width:100%; height:100%; object-fit:cover; }
  .akun-name{ font-family:var(--f-display); font-size:24px; color:var(--ink); }
  .akun-email{ font-size:13px; color:var(--ink-3); }
  .akun-card{ background:#fff; border:1.5px solid var(--line); border-radius:var(--r-lg); padding:28px; margin-bottom:22px; box-shadow:var(--shadow-sm); }
  .akun-card-title{ font-family:var(--f-display); font-size:19px; color:var(--ink); margin-bottom:4px; }
  .akun-card-sub{ font-size:12.5px; color:var(--ink-3); margin-bottom:20px; }
  .f-row{ display:flex; flex-direction:column; gap:6px; margin-bottom:16px; }
  .f-label{ font-size:12.5px; font-weight:700; color:var(--ink-2); }
  .f-input{ padding:12px 14px; border:1.5px solid var(--line); border-radius:11px; font-family:var(--f-body); font-size:14px; outline:none; }
  .f-input:focus{ border-color:var(--forest); box-shadow:0 0 0 3px var(--forest-tint); }
  .f-input.invalid{ border-color:#F26D6D; }
  .f-hint{ font-size:11.5px; color:var(--ink-3); }
  .f-hint.err{ color:#C23434; }
  .f-hint.ok{ color:#1F8A50; }
  .f-two{ display:grid; grid-template-columns:1fr 1fr; gap:16px; }
  @media(max-width:600px){ .f-two{ grid-template-columns:1fr; } }
  .doc-row{ display:flex; align-items:center; gap:16px; padding:14px; border:1.5px solid var(--line); border-radius:12px; margin-bottom:12px; }
  .doc-thumb{ width:64px; height:44px; border-radius:8px; background:var(--mist) center/cover no-repeat; flex-shrink:0; display:flex; align-items:center; justify-content:center; color:var(--ink-3); font-size:10px; }
  .doc-info{ flex:1; }
  .doc-name{ font-weight:700; font-size:13.5px; color:var(--ink); }
  .doc-status{ font-size:11.5px; margin-top:2px; }
  .doc-status.done{ color:#1F8A50; } .doc-status.pending{ color:var(--ink-3); }
  .fam-item{ display:flex; align-items:center; gap:12px; padding:12px 14px; border:1.5px solid var(--line); border-radius:12px; margin-bottom:10px; }
  .fam-body{ flex:1; font-size:13.5px; }
  .fam-nik{ font-family:var(--f-mono); font-size:11.5px; color:var(--ink-3); }
  .fam-tag{ font-size:10.5px; padding:2px 9px; border-radius:100px; background:var(--forest-tint); color:var(--forest-deep); font-weight:700; text-transform:capitalize; }
  .fam-add-grid{ display:grid; grid-template-columns:1fr 1fr 120px auto; gap:10px; margin-top:14px; }
  @media(max-width:700px){ .fam-add-grid{ grid-template-columns:1fr; } }
  .modal-overlay{ position:fixed; inset:0; background:rgba(10,20,16,.6); z-index:9998; display:none; align-items:center; justify-content:center; padding:20px; }
  .modal-overlay.open{ display:flex; }
  .modal-box{ background:#fff; border-radius:20px; width:100%; max-width:440px; padding:24px; box-shadow:var(--shadow-lg); }
  .toast{ position:fixed; bottom:24px; left:50%; transform:translateX(-50%) translateY(20px); z-index:9999; background:var(--ink); color:#fff; padding:12px 20px; border-radius:100px; font-size:13px; font-weight:600; opacity:0; pointer-events:none; transition:.3s; }
  .toast.show{ opacity:1; transform:translateX(-50%) translateY(0); }
  .welcome-banner{ background:var(--forest-tint,#e9f3ee); border:1.5px solid var(--forest,#2F6B57); border-radius:14px; padding:16px 20px; margin-bottom:24px; display:flex; align-items:center; justify-content:space-between; gap:16px; flex-wrap:wrap; }
  .welcome-banner-text{ font-size:13.5px; color:var(--ink); }
  .welcome-banner-text b{ display:block; font-size:15px; margin-bottom:2px; }
</style>
</head>
<body>
<nav class="akun-nav">
  <a href="/" class="akun-nav-brand">
    <img src="/assets/dishub.png" alt="Logo Dishub" class="akun-nav-logo-img"/>
    Dishub Kabupaten Tulungagung
  </a>
  <div class="akun-nav-cta">
    <a class="btn btn-ghost btn-sm" href="/">← Beranda</a>
    <a class="btn btn-ghost btn-sm" href="/admin/tiket-saya">Tiket Saya</a>
    <a class="btn btn-solid btn-sm" href="/akun/keluar">Keluar</a>
  </div>
</nav>

<div class="akun-shell">
  <div class="akun-head">
    <div class="akun-avatar" id="avatarWrap">{{ strtoupper(mb_substr(session('akun_publik_nama', 'U'), 0, 1)) }}</div>
    <div>
      <div class="akun-name">{{ session('akun_publik_nama', '') }}</div>
      <div class="akun-email">{{ session('akun_publik_email', '') }}</div>
    </div>
  </div>

  <p style="font-size:12.5px;color:var(--ink-3);margin:-24px 0 28px;">
    Akun ini satu untuk semua layanan - data di halaman ini juga dipakai saat memesan
    <a href="/balikgratis" style="color:var(--forest);font-weight:600;">Balik Gratis</a>.
  </p>

  @if ($welcome)
  <div class="welcome-banner" id="welcomeBanner">
    <div class="welcome-banner-text">
      <b>Selamat datang! Lengkapi dulu Profil Saya.</b>
      Isi Data Diri di bawah supaya akun Anda siap dipakai untuk memesan tiket.
    </div>
    @if ($next !== '')
    <button type="button" class="btn btn-solid btn-sm" onclick="lanjutkanSetelahProfil()">Lanjutkan →</button>
    @endif
  </div>
  @endif

  <!-- ── Data Diri ─────────────────────────────────────────────── -->
  <div class="akun-card">
    <div class="akun-card-title">Data Diri</div>
    <div class="akun-card-sub">Wajib diisi &amp; sesuai KTP sebelum bisa memesan kursi.</div>

    <div class="f-row">
      <span class="f-label">Nama Lengkap</span>
      <input class="f-input" id="p_nama" placeholder="Nama sesuai KTP"/>
      <label style="display:flex;align-items:center;gap:8px;font-size:12.5px;color:var(--ink-2);margin-top:4px">
        <input type="checkbox" id="p_nama_sesuai"/> Nama di atas sudah sama persis dengan KTP saya
      </label>
    </div>

    <div class="f-two">
      <div class="f-row">
        <span class="f-label">NIK</span>
        <input class="f-input" id="p_nik" placeholder="16 digit sesuai KTP" inputmode="numeric" maxlength="16" oninput="validateNikLive()"/>
        <span class="f-hint" id="p_nik_hint">16 digit angka.</span>
      </div>
      <div class="f-row">
        <span class="f-label">Nomor HP</span>
        <input class="f-input" id="p_hp" placeholder="08xxxxxxxxxx" inputmode="numeric" maxlength="14" oninput="validateHpLive()"/>
        <span class="f-hint" id="p_hp_hint">Angka saja, 9-14 digit.</span>
      </div>
    </div>

    <div class="f-two">
      <div class="f-row">
        <span class="f-label">Jenis Kelamin</span>
        <select class="f-input" id="p_jk">
          <option value="">- Pilih -</option>
          <option value="Laki-laki">Laki-laki</option>
          <option value="Perempuan">Perempuan</option>
        </select>
      </div>
      <div class="f-row">
        <span class="f-label">Nomor Kartu Keluarga <span style="color:var(--ink-3);font-weight:400">(untuk Balik Gratis)</span></span>
        <input class="f-input" id="p_no_kk" placeholder="16 digit nomor KK" inputmode="numeric" maxlength="16"/>
      </div>
    </div>

    <div class="f-row">
      <span class="f-label">Alamat Lengkap</span>
      <input class="f-input" id="p_alamat" placeholder="Nama jalan, RT/RW, desa/kelurahan"/>
    </div>
    <div class="f-two">
      <div class="f-row">
        <span class="f-label">Kabupaten/Kota</span>
        <input class="f-input" id="p_kabupaten" placeholder="mis. Tulungagung"/>
      </div>
      <div class="f-row">
        <span class="f-label">Kecamatan</span>
        <input class="f-input" id="p_kecamatan" placeholder="mis. Kedungwaru"/>
      </div>
    </div>
    <div class="f-row" style="max-width:180px">
      <span class="f-label">Kode Pos</span>
      <input class="f-input" id="p_kodepos" placeholder="66xxx" inputmode="numeric" maxlength="5"/>
    </div>

    <button class="btn btn-solid btn-sm" onclick="saveProfil()" id="btnSaveProfil">Simpan Data Diri</button>
  </div>

  <!-- ── Dokumen ───────────────────────────────────────────────── -->
  <div class="akun-card">
    <div class="akun-card-title">Verifikasi Dokumen</div>
    <div class="akun-card-sub">Ambil foto langsung lewat kamera - tidak bisa upload dari galeri.</div>

    <div class="doc-row">
      <div class="doc-thumb" id="thumbKtp">KTP</div>
      <div class="doc-info"><div class="doc-name">Foto KTP</div><div class="doc-status pending" id="statusKtp">Belum diambil</div></div>
      <button class="btn btn-ghost btn-sm" onclick="openScan('ktp')">Ambil Foto</button>
    </div>
    <div class="doc-row">
      <div class="doc-thumb" id="thumbKk">KK</div>
      <div class="doc-info"><div class="doc-name">Foto Kartu Keluarga <span style="color:var(--ink-3);font-weight:400">(opsional)</span></div><div class="doc-status pending" id="statusKk">Belum diambil</div></div>
      <button class="btn btn-ghost btn-sm" onclick="openScan('kk')">Ambil Foto</button>
    </div>
  </div>

  <!-- ── Anggota Keluarga ──────────────────────────────────────── -->
  <div class="akun-card">
    <div class="akun-card-title">Anggota Keluarga</div>
    <div class="akun-card-sub">Bisa ikut dipesankan kursi - tiap NIK tetap tunduk aturan 1 tiket / 4 minggu.</div>
    <div id="famList"></div>
    <div class="fam-add-grid">
      <input class="f-input" id="fam_nama" placeholder="Nama anggota"/>
      <input class="f-input" id="fam_nik" placeholder="NIK (16 digit)" inputmode="numeric" maxlength="16"/>
      <select class="f-input" id="fam_jk">
        <option value="">Kelamin</option><option value="Laki-laki">L</option><option value="Perempuan">P</option>
      </select>
      <select class="f-input" id="fam_status">
        <option value="anak">Anak</option><option value="istri">Istri</option><option value="suami">Suami</option><option value="orang_tua">Orang Tua</option><option value="saudara">Saudara</option><option value="lainnya">Lainnya</option>
      </select>
      <button class="btn btn-solid btn-sm" onclick="addFamily()">+ Tambah</button>
    </div>
  </div>

  <!-- ── Tiket & Pemesanan: SENGAJA tidak ditampilkan di sini - itu
       logika masing-masing layanan, bukan logika akun. Lihat gabungan
       tiket Trayek Wisata & Balik Gratis di /admin/tiket-saya. ────── -->
  <a href="/admin/tiket-saya" class="akun-card" style="display:flex;align-items:center;justify-content:space-between;text-decoration:none;">
    <div>
      <div class="akun-card-title">Tiket Saya</div>
      <div class="akun-card-sub">Lihat semua tiket Trayek Wisata &amp; Balik Gratis Anda.</div>
    </div>
    <span style="font-size:20px;color:var(--forest);">→</span>
  </a>
</div>

<!-- ═══ Modal Scan Kamera ═══ -->
<div class="modal-overlay" id="scanOverlay">
  <div class="modal-box">
    <h3 style="font-family:var(--f-display);font-size:19px;margin:0 0 14px">Scan Dokumen</h3>
    <div id="scanContainer"></div>
  </div>
</div>

<div class="toast" id="akunToastEl"></div>

<script>
function lanjutkanSetelahProfil(){
  window.location.href = {!! json_encode($next !== '' ? $next : '/') !!};
}

const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;

function akunToast(msg){ const el=document.getElementById('akunToastEl'); el.textContent=msg; el.classList.add('show'); clearTimeout(window._tt); window._tt=setTimeout(()=>el.classList.remove('show'),3200); }

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
  hint.className = 'f-hint ' + (v.length === 0 ? '' : (ok ? 'ok' : 'err'));
  return ok;
}
function validateHpLive() {
  const v = document.getElementById('p_hp').value.replace(/\D/g,'');
  document.getElementById('p_hp').value = v;
  const ok = /^\d{9,14}$/.test(v);
  const hint = document.getElementById('p_hp_hint');
  document.getElementById('p_hp').classList.toggle('invalid', v.length > 0 && !ok);
  hint.textContent = v.length === 0 ? 'Angka saja, 9-14 digit.' : (ok ? '✓ Format valid' : `${v.length}/9-14 digit`);
  hint.className = 'f-hint ' + (v.length === 0 ? '' : (ok ? 'ok' : 'err'));
  return ok;
}

async function saveProfil() {
  const nama = document.getElementById('p_nama').value.trim();
  if (!nama) return akunToast('Nama wajib diisi');
  if (!validateNikLive()) return akunToast('NIK belum valid');
  if (!validateHpLive()) return akunToast('Nomor HP belum valid');

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
    akunToast('✓ Data diri tersimpan');
  } catch (e) { akunToast('Gagal: ' + e.message); }
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
      akunToast('Mengunggah...');
      try {
        const res = await fetch('/akun/api/upload-dokumen', { method:'POST', headers: {'X-CSRF-TOKEN': CSRF_TOKEN}, body: fd });
        const data = await res.json();
        if (!res.ok) throw new Error(data.error);
        updateDocStatus(jenis, data.path);
        akunToast('✓ Foto tersimpan');
      } catch (e) { akunToast('Gagal upload: ' + e.message); }
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
      <button class="btn btn-ghost btn-sm" onclick="delFamily('${f.id}')">Hapus</button>
    </div>`).join('') || '<p style="font-size:12.5px;color:var(--ink-3)">Belum ada anggota keluarga ditambahkan.</p>';
}
async function addFamily() {
  const nama = document.getElementById('fam_nama').value.trim();
  const nik  = document.getElementById('fam_nik').value.trim();
  const jenis_kelamin = document.getElementById('fam_jk').value;
  const status = document.getElementById('fam_status').value;
  if (!nama || !/^\d{16}$/.test(nik)) return akunToast('Nama & NIK (16 digit) wajib diisi benar');
  try {
    const res = await fetch('/akun/api/keluarga', { method:'POST', headers:{'Content-Type':'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN}, body: JSON.stringify({nama,nik,jenis_kelamin,status}) });
    const data = await res.json();
    if (!res.ok) throw new Error(data.error);
    currentFamily.push(data.anggota ?? data); renderFamily();
    document.getElementById('fam_nama').value = ''; document.getElementById('fam_nik').value = '';
    akunToast('✓ Anggota keluarga ditambahkan');
  } catch (e) { akunToast('Gagal: ' + e.message); }
}
async function delFamily(id) {
  if (!confirm('Hapus anggota keluarga ini?')) return;
  // CATATAN MIGRASI: kode lama mengirim method:'POST' + header
  // X-HTTP-Method-Override, tapi server TIDAK PERNAH membaca header itu
  // (langsung cek $_SERVER['REQUEST_METHOD']) - jadi hapus anggota
  // keluarga selalu gagal (400 "Body tidak valid") di versi PHP native.
  // Di Laravel ini diperbaiki jadi DELETE request yang sesungguhnya.
  await fetch('/akun/api/keluarga?id=' + id, { method: 'DELETE', headers: {'X-CSRF-TOKEN': CSRF_TOKEN} });
  currentFamily = currentFamily.filter(f => f.id !== id); renderFamily();
}

loadProfil(); loadFamily();

</script>
<script src="/assets/akun/camera-scan.js"></script>
</body>
</html>
