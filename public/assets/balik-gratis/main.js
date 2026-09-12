
// CATATAN MIGRASI: helper baru (tidak ada di kode lama) - Laravel butuh
// CSRF token untuk request POST. Meta tag <meta name="csrf-token">
// disediakan di resources/views/balik-gratis/index.blade.php.
function bgCsrfToken() {
  const el = document.querySelector('meta[name="csrf-token"]');
  return el ? el.content : '';
}

document.addEventListener('DOMContentLoaded', () => {
  const loading = document.getElementById('pageLoading');
  if (loading) setTimeout(() => loading.classList.add('done'), 350);

  if (window.BG_BUKA) {
    refreshStatus();
    setInterval(refreshStatus, 30000);
  }

});

async function refreshStatus() {
  try {
    const res = await fetch('/balikgratis/api/status');
    const data = await res.json();
    if (!data || typeof data.sisa === 'undefined') return;

    const sisaEl = document.getElementById('sisaKuota');
    if (sisaEl) sisaEl.textContent = data.sisa;

    const banner = document.getElementById('kuotaHabisBanner');
    const btnDaftar = document.getElementById('btnDaftarChoice');
    if (data.sisa <= 0) {
      if (banner) banner.classList.add('show');
      if (btnDaftar) { btnDaftar.style.opacity = '.5'; btnDaftar.style.pointerEvents = 'none'; }
    } else {
      if (banner) banner.classList.remove('show');
      if (btnDaftar) { btnDaftar.style.opacity = ''; btnDaftar.style.pointerEvents = ''; }
    }
  } catch (e) { /* diamkan, badge kuota bukan hal kritikal */ }
}

let _bgAuth = null;
let _bgAllPax = [];

async function openDaftar() {
  document.getElementById('choiceScreen').classList.add('hidden');
  document.getElementById('cekTiketWrapper').classList.add('hidden');
  document.getElementById('daftarWrapper').classList.remove('hidden');

  const wrap = document.getElementById('paxPicker');
  wrap.innerHTML = '<p style="font-size:12.5px;color:var(--ink-3,#6b7280)">Memuat data akun…</p>';

  const res = await fetch('/balikgratis/api/auth-status');
  _bgAuth = await res.json();

  if (!_bgAuth.logged_in) {
    window.location.href = '/akun/masuk?next=' + encodeURIComponent('/balikgratis');
    return;
  }

  const banner = document.getElementById('lengkapiProfilBanner');
  const btn = document.getElementById('btnSubmit');
  if (!_bgAuth.profil_lengkap) {
    document.getElementById('lengkapiList').textContent = (_bgAuth.field_kurang || []).join(', ');
    banner.classList.remove('hidden');
    btn.disabled = true;
    wrap.innerHTML = '';
    return;
  }
  banner.classList.add('hidden');
  btn.disabled = false;

  // Anggota keluarga akun (sama dengan yang dipakai Trayek Wisata -
  // satu sumber data, lihat pages/akun/api/keluarga.php).
  const famRes = await fetch('/akun/api/keluarga');
  const family = famRes.ok ? await famRes.json() : [];

  _bgAllPax = [
    { nik: _bgAuth.nik, nama: _bgAuth.nama + ' (Anda)' },
    ...family.map((f) => ({ nik: f.nik, nama: `${f.nama} (${f.status_keluarga})` })),
  ];

  wrap.innerHTML = _bgAllPax.map((p, i) => `
    <div style="display:flex;align-items:center;gap:10px;padding:10px 12px;border:1.5px solid #e5e7eb;border-radius:10px;font-size:13px;">
      <input type="checkbox" class="bg-pax-chk" value="${i}" ${i === 0 ? 'checked' : ''}/>
      <span style="flex:1">${bgEsc(p.nama)}</span>
      <span style="font-family:monospace;font-size:11px;color:#6b7280;min-width:110px">${bgEsc(p.nik)}</span>
      <select class="inp bg-pax-kategori" data-idx="${i}" style="width:110px;padding:6px 8px;font-size:12px;">
        <option value="Dewasa">Dewasa</option>
        <option value="Anak-anak">Anak-anak</option>
      </select>
    </div>`).join('');
}

function openCekTiket() {
  document.getElementById('choiceScreen').classList.add('hidden');
  document.getElementById('daftarWrapper').classList.add('hidden');
  document.getElementById('cekTiketWrapper').classList.remove('hidden');
}

// "Cek Tiket Saya" - kalau sudah login, langsung ke halaman Tiket Saya
// di shell admin (role 'pengguna', terbatas hanya melihat tiket sendiri
// - lihat admin/pages/tiket-saya.php). Kalau belum login, jatuhkan ke
// pencarian manual berbasis NIK seperti sebelumnya.
async function cekTiketSaya() {
  try {
    const res = await fetch('/balikgratis/api/auth-status');
    const data = await res.json();
    if (data.logged_in) {
      window.location.href = '/admin/tiket-saya';
      return;
    }
  } catch (e) { /* jatuhkan ke pencarian manual di bawah */ }
  openCekTiket();
}

function backToChoice() {
  document.getElementById('daftarWrapper').classList.add('hidden');
  document.getElementById('cekTiketWrapper').classList.add('hidden');
  document.getElementById('choiceScreen').classList.remove('hidden');
}

function bgToast(msg, type = 'success') {
  const t = document.getElementById('bgToastEl');
  if (!t) return;
  t.textContent = msg;
  t.className = `bg-toast ${type} show`;
  clearTimeout(window._bgToastTimer);
  window._bgToastTimer = setTimeout(() => t.classList.remove('show'), 3400);
}

async function submitDaftar() {
  const btn = document.getElementById('btnSubmit');
  const errEl = document.getElementById('formErr');
  errEl.classList.remove('show');

  const checked = [...document.querySelectorAll('.bg-pax-chk:checked')].map((el) => {
    const idx = parseInt(el.value, 10);
    const kategoriSel = document.querySelector(`.bg-pax-kategori[data-idx="${idx}"]`);
    return { nik: _bgAllPax[idx].nik, kategori: kategoriSel ? kategoriSel.value : 'Dewasa' };
  });
  if (!checked.length) {
    errEl.textContent = 'Pilih minimal 1 penumpang.';
    errEl.classList.add('show');
    return;
  }

  btn.disabled = true;
  btn.textContent = 'Memproses…';

  try {
    const res = await fetch('/balikgratis/api/daftar', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': bgCsrfToken() },
      body: JSON.stringify({ penumpang: checked }),
    });
    const data = await res.json();
    if (!res.ok || data.error) throw new Error(data.error || 'Gagal mendaftar. Coba lagi.');

    backToChoice();
    showTiketModalMulti(data.tiket);
    refreshStatus();
    bgToast(`✓ ${data.tiket.length} tiket berhasil dibuat.`, 'success');
  } catch (e) {
    errEl.textContent = e.message;
    errEl.classList.add('show');
    bgToast(e.message, 'error');
  } finally {
    btn.disabled = false;
    btn.textContent = 'Daftar Sekarang →';
  }
}

async function cariTiketByNIK() {
  const nikInput = document.getElementById('nikCari');
  const resultArea = document.getElementById('hasilCekTiket');
  const nik = (nikInput.value || '').trim();

  if (!/^\d{16}$/.test(nik)) {
    resultArea.innerHTML = '<div class="empty-note">Masukkan NIK 16 digit yang valid.</div>';
    return;
  }
  resultArea.innerHTML = '<div class="empty-note">Mencari…</div>';

  try {
    const res = await fetch(`/balikgratis/api/cek-tiket?nik=${encodeURIComponent(nik)}`);
    const data = await res.json();
    if (!res.ok || data.error) throw new Error(data.error || 'Gagal mencari tiket.');

    const rows = data.tiket || [];
    if (rows.length === 0) {
      resultArea.innerHTML = '<div class="empty-note">Tidak ditemukan pendaftaran atas NIK ini.</div>';
      return;
    }
    resultArea.innerHTML = '';
    rows.forEach((t) => {
      const item = document.createElement('div');
      item.className = 'tiket-result-item';
      const pillClass = t.status === 'hadir' ? 'pill-hadir' : (t.status === 'dibatalkan' ? 'pill-dibatalkan' : 'pill-terdaftar');
      item.innerHTML = `
        <div>
          <div class="tri-name">${bgEsc(t.nama)}</div>
          <div class="tri-meta">${bgEsc(t.nomor_tiket)} · Balik Gratis ${t.tahun}</div>
        </div>
        <div style="display:flex;align-items:center;gap:10px">
          <span class="status-pill ${pillClass}">${bgEsc(t.status)}</span>
          <button type="button" class="btn-search" style="padding:8px 12px">Buka</button>
        </div>`;
      item.querySelector('button').addEventListener('click', () => showTiketModal(t));
      resultArea.appendChild(item);
    });
  } catch (e) {
    resultArea.innerHTML = `<div class="empty-note">${bgEsc(e.message)}</div>`;
  }
}

function bgEsc(s) {
  return String(s ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

// Menampilkan >1 tiket sekaligus (satu transaksi KAI-Access-style bisa
// menghasilkan beberapa tiket - pemilik akun + anggota keluarga).
function showTiketModalMulti(tikets) {
  if (!tikets || !tikets.length) return;
  if (tikets.length === 1) { showTiketModal(tikets[0]); return; }

  const overlay = document.getElementById('tiketOverlay');
  const box = document.getElementById('tiketBox');
  box.innerHTML = `
    <div class="tiket-head">
      <div class="th-title">${tikets.length} Tiket Balik Gratis</div>
      <div class="th-sub">Tulungagung › Surabaya · ${tikets[0].tahun}</div>
    </div>
    <div class="tiket-body" style="max-height:60vh;overflow-y:auto;display:flex;flex-direction:column;gap:14px;">
      ${tikets.map((t, i) => `
        <div style="border:1.5px solid #e5e7eb;border-radius:12px;padding:14px;">
          <div class="tiket-row"><span>No. Tiket</span><b>${bgEsc(t.nomor_tiket)}</b></div>
          <div class="tiket-row"><span>Nama</span><b>${bgEsc(t.nama)}</b></div>
          <div class="tiket-row"><span>Kategori</span><b>${bgEsc(t.kategori)}</b></div>
          <div class="tiket-row" style="border-bottom:none"><span>Status</span><b>${bgEsc(t.status || 'terdaftar')}</b></div>
          <div class="tiket-qr" id="tiketQrHolder${i}" style="margin-top:8px;"></div>
          <button type="button" class="btn-primary-solid bg-dl-btn" data-idx="${i}" style="margin-top:10px;width:100%;">Unduh PDF</button>
        </div>`).join('')}
    </div>
    <div class="tiket-foot">
      <button type="button" class="btn-ghost" id="btnTutupTiket">Tutup</button>
    </div>`;

  overlay.classList.add('show');
  document.getElementById('btnTutupTiket').addEventListener('click', () => overlay.classList.remove('show'));

  tikets.forEach((t, i) => {
    const holder = document.getElementById(`tiketQrHolder${i}`);
    let qrCanvas = null;
    if (window.QRCode && t.qr_token) {
      // eslint-disable-next-line no-new
      new QRCode(holder, { text: t.qr_token, width: 120, height: 120, colorDark: '#0A1F44', colorLight: '#ffffff' });
      qrCanvas = holder.querySelector('canvas');
    }
    const dlBtn = box.querySelector(`.bg-dl-btn[data-idx="${i}"]`);
    dlBtn.addEventListener('click', async () => {
      dlBtn.disabled = true; dlBtn.textContent = 'Menyiapkan…';
      try { await downloadTiketPDF({ ...t, qrCanvas }); }
      catch (e) { bgToast('Gagal membuat PDF: ' + e.message, 'error'); }
      finally { dlBtn.disabled = false; dlBtn.textContent = 'Unduh PDF'; }
    });
  });
}

async function showTiketModal(tiket) {
  const overlay = document.getElementById('tiketOverlay');
  const box = document.getElementById('tiketBox');

  box.innerHTML = `
    <div class="tiket-head">
      <div class="th-title">Tiket Balik Gratis</div>
      <div class="th-sub">Tulungagung › Surabaya · ${tiket.tahun}</div>
    </div>
    <div class="tiket-body">
      <div class="tiket-row"><span>No. Tiket</span><b>${bgEsc(tiket.nomor_tiket)}</b></div>
      <div class="tiket-row"><span>Nama</span><b>${bgEsc(tiket.nama)}</b></div>
      <div class="tiket-row"><span>NIK</span><b>${bgEsc(tiket.nik)}</b></div>
      <div class="tiket-row"><span>Kategori</span><b>${bgEsc(tiket.kategori)}</b></div>
      <div class="tiket-row" style="border-bottom:none"><span>Status</span><b>${bgEsc(tiket.status || 'terdaftar')}</b></div>
      <div class="tiket-qr" id="tiketQrHolder"></div>
    </div>
    <div class="tiket-foot">
      <button type="button" class="btn-ghost" id="btnTutupTiket">Tutup</button>
      <button type="button" class="btn-primary-solid" id="btnUnduhTiket">Unduh PDF</button>
    </div>`;

  overlay.classList.add('show');
  document.getElementById('btnTutupTiket').addEventListener('click', () => overlay.classList.remove('show'));

  const qrHolder = document.getElementById('tiketQrHolder');
  let qrCanvas = null;
  if (window.QRCode && tiket.qr_token) {
    // eslint-disable-next-line no-new
    new QRCode(qrHolder, { text: tiket.qr_token, width: 140, height: 140, colorDark: '#0A1F44', colorLight: '#ffffff' });
    qrCanvas = qrHolder.querySelector('canvas');
  }

  document.getElementById('btnUnduhTiket').addEventListener('click', async (ev) => {
    const btn = ev.currentTarget;
    btn.disabled = true;
    btn.textContent = 'Menyiapkan…';
    try {
      await downloadTiketPDF({ ...tiket, qrCanvas });
    } catch (e) {
      bgToast('Gagal membuat PDF: ' + e.message, 'error');
    } finally {
      btn.disabled = false;
      btn.textContent = 'Unduh PDF';
    }
  });
}
