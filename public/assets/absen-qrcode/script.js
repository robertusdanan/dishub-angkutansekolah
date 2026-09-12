const SB_URL  = window.SB_URL;
const SB_ANON = window.SB_ANON;

const sbHdr = (extra = {}) => ({
  'Content-Type' : 'application/json',
  'apikey'       : SB_ANON,
  'Authorization': `Bearer ${SB_ANON}`,
  ...extra,
});

// ====== FETCH SEMUA RECORD DARI SUPABASE (pagination via Range header) ======
async function sbFetchAll(table, extraParams = {}) {
  const allRows = [];
  const pageSize = 1000;
  let from = 0;
  while (true) {
    const params = new URLSearchParams({ select: '*', ...extraParams });
    const res = await fetch(`${SB_URL}/rest/v1/${table}?${params}`, {
      headers: { ...sbHdr(), 'Range': `${from}-${from + pageSize - 1}`, 'Range-Unit': 'items', 'Prefer': 'count=none' },
    });
    if (!res.ok) throw new Error(`Gagal mengambil data ${table} (HTTP ${res.status})`);
    const rows = await res.json();
    allRows.push(...rows);
    if (rows.length < pageSize) break;
    from += pageSize;
  }
  return allRows;
}

// ====== FETCH SATU RECORD BY ID ======
async function sbFetchRecord(table, id) {
  const params = new URLSearchParams({ select: '*', id: `eq.${id}`, limit: '1' });
  const res = await fetch(`${SB_URL}/rest/v1/${table}?${params}`, { headers: sbHdr() });
  if (!res.ok) return null;
  const rows = await res.json();
  return rows[0] || null;
}

// ====== FUNGSI ABSEN - via php/cek.php (server yang tentukan jam/tanggal) ======
// PENTING: dulu fungsi ini menghitung jam/tanggal/shift dari `new Date()` di
// BROWSER lalu menulis langsung ke Supabase pakai anon key. Masalahnya: jam
// & tanggal di HP siswa bisa saja salah - baik karena zona waktu device
// tidak di-set dengan benar, jam belum disinkronkan, atau sengaja diubah
// manual. Kalau nilai itu dipercaya begitu saja, absensi bisa tercatat di
// tanggal/shift yang salah (bahkan tanggal yang belum terjadi) - ini yang
// menyebabkan kasus "sudah absen tanggal besok padahal sekarang masih hari
// ini". php/cek.php menghitung jam/tanggal/shift dari clock SERVER (zona
// Asia/Jakarta, lihat $TIMEZONE di sana) yang tidak bisa dimanipulasi dari
// sisi klien, jadi ini yang jadi satu-satunya sumber kebenaran waktu absen.
async function kirimAbsen(payload) {
  const res  = await fetch('/absenqrcode/php/cek', {
    method : 'POST',
    headers: { 'Content-Type': 'application/json' },
    body   : JSON.stringify({ payload }),
  });
  const json = await res.json().catch(() => ({}));
  if (!res.ok || json.status !== 'ok') {
    throw new Error(json.message || `Gagal simpan absensi (HTTP ${res.status})`);
  }
  return json;
}

// ====== STYLE SPINNER & AUTOCOMPLETE & PROFIL ======
const style = document.createElement('style');
style.textContent = `
@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
.spinner { width:40px; height:40px; border:5px solid #ccc; border-top:5px solid #007bff; border-radius:50%; animation:spin 1s linear infinite; margin:0 auto; }
.spinner-small { display:inline-block; width:16px; height:16px; border:2px solid #ccc; border-top:2px solid #007bff; border-radius:50%; animation:spin 1s linear infinite; margin-left:6px; }
.autocomplete-item { padding: 6px 10px; cursor: pointer; }
.autocomplete-item:hover { background-color: #f0f0f0; }
.profile-box { display:flex; align-items:center; gap:10px; margin-bottom:15px; padding:10px; border:1px solid #ccc; border-radius:8px; background:#f8f9fa; justify-content: space-between; }
.profile-box img { border-radius:50%; width:40px; height:40px; }
.profile-box button { background:#dc3545; color:#fff; border:none; padding:5px 10px; border-radius:4px; cursor:pointer; }
.profile-box button:hover { background:#c82333; }
`;
document.head.appendChild(style);

// ====== HELPERS ======
function norm(s) { return (s || '').toString().trim().toLowerCase(); }
function waitForCondition(fn, interval = 50, timeout = 1500) {
  return new Promise(resolve => {
    const start = Date.now();
    const id = setInterval(() => {
      try { if (fn()) { clearInterval(id); resolve(true); return; } } catch(e){}
      if (Date.now() - start > timeout) { clearInterval(id); resolve(false); }
    }, interval);
  });
}
function parseJwt(token) {
  const base64Url = token.split('.')[1] || '';
  const base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
  const jsonPayload = decodeURIComponent(atob(base64 || '').split('').map(c =>
    '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2)
  ).join(''));
  return JSON.parse(jsonPayload);
}
function showLoading(containerId, message="Memuat...") {
  const el = document.getElementById(containerId);
  if (!el) return;
  el.innerHTML = `
    <div style="text-align:center;padding:30px;">
      <div class="spinner"></div>
      <p style="margin-top: 15px;">${message}</p>
      <p style="font-size: 0.9rem; color: #666;">Jika gagal, sistem akan mencoba ulang...</p>
    </div>`;
}

// ====== GOOGLE ONE TAP CALLBACK ======
async function handleCredentialResponse(response) {
  const jwt = parseJwt(response.credential);
  const email = jwt.email;
  const name = jwt.name;
  // Gunakan ukuran 48x48 (small icon)
  const picture = jwt.picture ? jwt.picture.replace(/=s96-c/, '=s48-c') : '';

  const now = Date.now();
  localStorage.setItem('nama', name);
  localStorage.setItem('email', email);
  localStorage.setItem('foto', picture);
  localStorage.setItem('lastLogin', now);

  try {
    // cek atau buat user di Supabase
    const res = await fetch(`${SB_URL}/rest/v1/user_QRCode?select=*&email=eq.${encodeURIComponent(email)}`, { headers: sbHdr() });
    const data = await res.json();
    let userId;

    if (data.length > 0) {
      userId = data[0].id;
      // Update nama & avatar jika berbeda
      const updatePayload = {};
      if (data[0].nama !== name) updatePayload.nama = name;
      if (data[0].avatar !== picture) updatePayload.avatar = picture;
      if (Object.keys(updatePayload).length > 0) {
        await fetch(`${SB_URL}/rest/v1/user_QRCode?id=eq.${userId}`, {
          method: 'PATCH',
          headers: sbHdr(),
          body: JSON.stringify(updatePayload)
        });
      }
    } else {
      // Buat user baru dengan avatar
      const createRes = await fetch(`${SB_URL}/rest/v1/user_QRCode`, {
        method: 'POST',
        headers: sbHdr({ 'Prefer': 'return=representation' }),
        body: JSON.stringify({ email, nama: name, avatar: picture })
      });
      const createData = await createRes.json();
      userId = createData[0]?.id;
    }
    localStorage.setItem('userId', userId);
  } catch (err) {
    console.warn('Gagal update user Supabase:', err);
  }

  renderProfile();
  location.reload();
}

// ====== RENDER PROFIL & LOGOUT ======
function renderProfile() {
  const container = document.getElementById('login-container');
  const nama = localStorage.getItem('nama');
  const email = localStorage.getItem('email');
  const foto = localStorage.getItem('foto');

  const googleButton = document.querySelector('.g_id_signin'); // tombol login Google
  if (nama && email) {
    if (window.google && google.accounts && google.accounts.id) {
      google.accounts.id.cancel();
    }
    if (googleButton) googleButton.style.display = 'none';

    container.innerHTML = `
      <div class="profile-box">
        <img src="${foto}" alt="Foto Profil"/>
        <div>
          <div><strong>${nama}</strong></div>
          <div style="font-size:0.85rem;color:#555;">${email}</div>
        </div>
        <button id="logoutButton">Logout</button>
      </div>
    `;
    document.getElementById('logoutButton').addEventListener('click', () => {
      localStorage.clear();
      location.reload();
    });
  } else {
    if (googleButton) googleButton.style.display = 'block';
    container.innerHTML = '';
  }
}

// ====== CEK STATUS ABSEN ======
// Server (php/status.php) yang tentukan jam/tanggal/shift, dengan cara
// PERSIS SAMA seperti php/cek.php yang menyimpan absensi - supaya pesan
// status di UI selalu sinkron dengan aturan yang benar-benar berlaku,
// tidak tergantung jam di HP siswa.
async function cekStatusAbsen(email) {
  const res  = await fetch('/absenqrcode/php/status', {
    method : 'POST',
    headers: { 'Content-Type': 'application/json' },
    body   : JSON.stringify({ email }),
  });
  const json = await res.json().catch(() => ({}));
  if (!res.ok && json.status !== 'invalid_time' && json.status !== 'no_user') {
    throw new Error(json.message || `Gagal mengambil status absen (HTTP ${res.status})`);
  }
  return json;
}

// ====== FETCH SUPABASE (LIST DATA) ======
// Tabel referensi (driver_bus, driver_mpu, domisili, sekolah) sekarang lewat
// php/cache_proxy.php - cache whole-table di server (folder cache/, auto
// refresh tiap 24 jam), bukan fetch langsung ke Supabase di tiap kunjungan
// halaman. Data absensi/user tetap langsung ke Supabase seperti biasa
// (lihat kirimAbsen, cekStatusAbsen, dsb. di atas) karena itu harus selalu
// live/realtime.
const CACHED_REF_TABLES = ['driver_bus', 'driver_mpu', 'domisili', 'sekolah'];

const fetchPB = async (table, retries = 3, delay = 1000) => {
  while (true) {
    try {
      if (CACHED_REF_TABLES.includes(table)) {
        const res = await fetch(`/api/supabase-proxy?table=${encodeURIComponent(table)}`);
        if (!res.ok) throw new Error(`Gagal mengambil data ${table} (HTTP ${res.status})`);
        return await res.json();
      }
      return await sbFetchAll(table);
    } catch (err) {
      if (retries-- > 0) await new Promise(r => setTimeout(r, delay));
      else throw err;
    }
  }
};

// ====== FETCH SATU RECORD ======
const fetchPBRecord = async (table, id) => {
  if (CACHED_REF_TABLES.includes(table)) {
    const res = await fetch(`/api/supabase-proxy?table=${encodeURIComponent(table)}&id=${encodeURIComponent(id)}`);
    if (!res.ok) return null;
    const data = await res.json();
    return data || null;
  }
  return await sbFetchRecord(table, id);
};

// ====== MAIN ======
(async function main() {
  renderProfile();
  showLoading('form-container', 'Memuat formulir absensi...');

  const namaUser = localStorage.getItem('nama');
  const emailUser = localStorage.getItem('email');
  const fotoUser = localStorage.getItem('foto');

  if (!namaUser || !emailUser) {
    document.getElementById('form-container').innerHTML =
      '<p>Silakan login terlebih dahulu untuk melanjutkan.</p>';
    return;
  }

const urlParams = new URLSearchParams(window.location.search);
const driverId = urlParams.get('id');
const container = document.getElementById('form-container');

if (!driverId) {
  container.innerHTML = errorIdHtml(); // tampilkan pesan error ID tidak ada
  return;
}

// validasi ID ke Supabase
const driverBus = await fetchPBRecord('driver_bus', driverId);
const driverMpu = !driverBus ? await fetchPBRecord('driver_mpu', driverId) : null;

// kalau tidak ditemukan di kedua koleksi
if (!driverBus && !driverMpu) {
  container.innerHTML = errorIdHtml(); 
  return;
}

// helper template pesan error
function errorIdHtml() {
  return `
    <div style="text-align:center;">
<h2 style="font-size: 1.5rem; margin-bottom: 10px; color: #ffffffff; text-shadow: 3px 3px 8px rgba(0,0,0,0.8);">
  ⚠️ <br> ID Driver tidak ditemukan
</h2>
<p style="margin-bottom: 10px; color: #ffffffff; text-shadow: 3px 3px 8px rgba(0,0,0,0.8); font-size: 23px;">
  Silakan klik tombol dibawah untuk mendapatkan link absen yang benar.
</p>

      <a href="/absenqrcode/listlink"
         style="
           display: inline-block;
           padding: 12px 24px;
           background: linear-gradient(135deg, #0e058aff);
           color: white;
           font-weight: 600;
           text-decoration: none;
           border-radius: 8px;
           transition: transform 0.2s, box-shadow 0.2s;
         "
         onmouseover="this.style.transform='scale(1.05)'; this.style.boxShadow='0 8px 20px rgba(0,0,0,0.15)';"
         onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='none';"
      >
        List Link Absen
      </a>
    </div>
  `;
}


  try {
    // Pastikan busRes global tersedia agar form.html tidak error
    window.busRes = await fetchPB('driver_bus');
    window.mpuRes = await fetchPB('driver_mpu');

    const formHtmlResp = await fetch('/absenqrcode/pages/form');
    if (!formHtmlResp.ok) throw new Error('Gagal mengambil form HTML');
    const formHtml = await formHtmlResp.text();

    showLoading('form-container', 'Memeriksa status absen & memuat data...');

    // Ambil data user dari Supabase
    let userData = await fetch(`${SB_URL}/rest/v1/user_QRCode?select=*&email=eq.${encodeURIComponent(emailUser)}`, { headers: sbHdr() }).then(r => r.json());
    let userRecord = userData?.[0] || null;

    // Jika user belum ada, buat baru sesuai akun Google
    if (!userRecord) {
      const createRes = await fetch(`${SB_URL}/rest/v1/user_QRCode`, {
        method: 'POST',
        headers: sbHdr({ 'Prefer': 'return=representation' }),
        body: JSON.stringify({ email: emailUser, nama: namaUser, avatar: fotoUser })
      });
      const createJson = await createRes.json();
      if (!createRes.ok) {
        const detail = createJson?.message || createJson?.hint || JSON.stringify(createJson);
        throw new Error('Gagal membuat user baru di Supabase: ' + detail);
      }
      userRecord = createJson[0];
      localStorage.setItem('userId', userRecord.id);
    } else {
      localStorage.setItem('userId', userRecord.id);
    }

    // Ambil data lain
    const [statusAbsen, domisiliData, sekolahData] = await Promise.all([
      cekStatusAbsen(emailUser),
      fetchPB('domisili'),
      fetchPB('sekolah')
    ]);

    if (statusAbsen.status === 'invalid_time') {
      container.innerHTML = `
        <div style="text-align:center;padding:20px; background-color: #fff3cd; border: 1px solid #ffeeba; border-radius: 8px;">
          <h2 style="color: #856404;">⚠️ Di luar jam absen!</h2>
          <p>${statusAbsen.message}</p>
        </div>`;
      return;
    }

    if ((statusAbsen.shift === 'pagi'  && statusAbsen.absenPagi) ||
        (statusAbsen.shift === 'siang' && statusAbsen.absenSiang)) {
      container.innerHTML = `
        <div style="text-align:center;padding:20px; background-color: #87CEEB; border: 1px solid blue; border-radius: 8px;">
          <h2 style="color: blue;">✅ Absen Berhasil!</h2>
        </div>`;
      return;
    }

    const isUserComplete =
      userRecord &&
      userRecord.jenis_kelamin?.trim() &&
      userRecord.domisili?.trim() &&
      userRecord.sekolah?.trim();

// === AUTO-ABSEN ===
if (isUserComplete) {
  try {
    let driverData;
    let selectedTransportasi;

    driverData = await fetchPBRecord('driver_bus', driverId);
    if (driverData && !driverData.code) selectedTransportasi = 'BUS';
    else {
      driverData = await fetchPBRecord('driver_mpu', driverId);
      if (driverData && !driverData.code) selectedTransportasi = 'MPU';
    }

    if (!driverData) throw new Error('Data driver tidak ditemukan');

    const payload = {
      transportasi: selectedTransportasi,
      trayek: driverData.trayek,
      plat_driver: `${driverData.plat} - ${driverData.driver}`,
      domisili: userRecord.domisili,
      sekolah: userRecord.sekolah,
      jenis_kelamin: userRecord.jenis_kelamin,
      nama: namaUser,
      email: emailUser
    };

    const json = await kirimAbsen(payload);
    if (json.status !== 'ok') throw new Error(json.message || 'Gagal auto-absen');

    container.innerHTML = `
      <div style="text-align:center;padding:20px; background-color: #d4edda; border: 1px solid green; border-radius: 8px;">
        <h2 style="color: green;">✅ Terimakasih, Absen telah berhasil</h2>
      </div>`;
    return;
  } catch (autoErr) {
    console.warn('Auto-absen gagal:', autoErr);

    // Jika gagal, kembali ke halaman login depan
    container.innerHTML = `
      <div style="text-align:center; padding: 5px;">
        <h2 style="font-size: 1.5rem; margin-bottom: 20px; color: #ffffffff;">⚠️ ID driver tidak ditemukan</h2>
        <p style="margin-bottom: 30px; color: #ffffffff;">Silakan klik tombol dibawah untuk mendapatkan link absen yang benar.</p>
        <a href="/absenqrcode/listlink"
           style="
             display: inline-block;
             padding: 12px 24px;
             background: linear-gradient(135deg, #0e058aff);
             color: white;
             font-weight: 600;
             text-decoration: none;
             border-radius: 8px;
             transition: transform 0.2s, box-shadow 0.2s;
           "
           onmouseover="this.style.transform='scale(1.05)'; this.style.boxShadow='0 8px 20px rgba(0,0,0,0.15)';"
           onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='none';"
        >
          List Link Absen
        </a>
      </div>
    `;
    return;
  }
}


    // === Fallback: render form manual ===
    container.innerHTML = formHtml;

    // ====== Ambil elemen DOM ======
    const namaInput = document.getElementById('namaInput');
    const transportasiSelect = document.getElementById('transportasi');
    const trayekSelect = document.getElementById('trayek');
    const infoAngkutan = document.getElementById('info-angkutan');
    const form = document.getElementById('absenForm');
    const platDriverSelect = document.getElementById('platDriver');
    const responseBox = document.getElementById('response');

    if (namaInput) namaInput.value = namaUser;
    const trayekData = { BUS: busRes, MPU: mpuRes };

    function selectOptionByTextOrValue(selectEl, wanted) {
      if (!selectEl || !wanted) return false;
      const w = norm(wanted);
      for (let i = 0; i < selectEl.options.length; i++) {
        const o = selectEl.options[i];
        if (norm(o.value) === w || norm(o.text || o.textContent) === w) {
          selectEl.value = o.value;
          return true;
        }
      }
      return false;
    }

    async function preselectById(id) {
      if (!id) return;
      let selectedItem = busRes.find(item => item.id === id);
      let transportasiType = 'BUS';
      if (!selectedItem) { selectedItem = mpuRes.find(item => item.id === id); transportasiType = selectedItem ? 'MPU' : null; }
      if (!selectedItem || !transportasiType) return;

      transportasiSelect.value = transportasiType;
      transportasiSelect.dispatchEvent(new Event('change', { bubbles: true }));

      const trayekFound = await waitForCondition(() => {
        return Array.from(trayekSelect.options).some(o => norm(o.value) === norm(selectedItem.trayek) || norm(o.text || o.textContent) === norm(selectedItem.trayek));
      }, 50, 1500);

      if (trayekFound) selectOptionByTextOrValue(trayekSelect, selectedItem.trayek);
      else {
        const opt = document.createElement('option');
        opt.value = selectedItem.trayek;
        opt.textContent = selectedItem.trayek;
        trayekSelect.appendChild(opt);
        trayekSelect.value = selectedItem.trayek;
      }
      trayekSelect.dispatchEvent(new Event('change', { bubbles: true }));

      if (transportasiType === 'MPU') {
        const expectedPlatDriverValue = `${selectedItem.plat} - ${selectedItem.driver}`;
        const platFound = await waitForCondition(() => {
          return Array.from(platDriverSelect.options).some(o => norm(o.value) === norm(expectedPlatDriverValue) || (norm(o.text || o.textContent).includes(norm(selectedItem.plat)) && norm(o.text || o.textContent).includes(norm(selectedItem.driver))));
        }, 50, 1500);

        if (platFound) {
          selectOptionByTextOrValue(platDriverSelect, expectedPlatDriverValue) ||
            selectOptionByTextOrValue(platDriverSelect, `${selectedItem.plat} (${selectedItem.driver})`);
        } else {
          const o = document.createElement('option');
          o.value = expectedPlatDriverValue;
          o.textContent = `${selectedItem.plat} (${selectedItem.driver})`;
          platDriverSelect.appendChild(o);
          platDriverSelect.value = expectedPlatDriverValue;
        }
        platDriverSelect.dispatchEvent(new Event('change', { bubbles: true }));
      }
    }

    const params = new URLSearchParams(window.location.search);
    const preselectId = params.get('id');
    if (preselectId) preselectById(preselectId).catch(err => console.warn('Preselect error:', err));

    // ====== EVENT TRANSPORTASI ======
    transportasiSelect.addEventListener('change', function () {
      const selectedTransportasi = this.value;
      trayekSelect.innerHTML = '';
      platDriverSelect.innerHTML = '';
      infoAngkutan.innerHTML = '';

      if (selectedTransportasi) {
        trayekSelect.disabled = false;
        trayekSelect.innerHTML = '<option value="">Pilih Trayek</option>';
        const uniqueTrayek = [];
        const list = trayekData[selectedTransportasi] || [];
        list.forEach(item => {
          const trayek = item.trayek?.trim();
          if (trayek && !uniqueTrayek.includes(trayek)) {
            uniqueTrayek.push(trayek);
            const option = document.createElement('option');
            option.value = trayek;
            option.textContent = trayek;
            trayekSelect.appendChild(option);
          }
        });
        if (selectedTransportasi === 'MPU') {
          platDriverSelect.style.display = 'block';
          platDriverSelect.disabled = false;
          platDriverSelect.innerHTML = '<option value="">Pilih Trayek Dulu</option>';
        } else platDriverSelect.style.display = 'none';
      } else {
        trayekSelect.disabled = true;
        trayekSelect.innerHTML = '<option value="">Pilih Transportasi Dulu</option>';
        platDriverSelect.style.display = 'none';
      }
    });

    // ====== EVENT TRAYEK ======
    trayekSelect.addEventListener('change', function () {
      const selectedTrayek = norm(this.value);
      const selectedTransportasi = transportasiSelect.value;
      const datas = (trayekData[selectedTransportasi] || []).filter(item => norm(item.trayek) === selectedTrayek);
      infoAngkutan.innerHTML = '';

      if (selectedTransportasi === 'MPU') {
        platDriverSelect.innerHTML = '<option value="">Pilih Data MPU</option>';
        datas.forEach(item => {
          const option = document.createElement('option');
          option.value = `${item.plat} - ${item.driver}`;
          option.textContent = `${item.plat} (${item.driver})`;
          platDriverSelect.appendChild(option);
        });
      }

      if (datas.length > 0) {
        const selectedData = datas[0];
        let infoHTML = '';
        if (selectedData.plat?.trim()) infoHTML += `<strong>Plat Nomor:</strong> ${selectedData.plat}<br>`;
        if (selectedData.driver?.trim()) infoHTML += `<strong>Driver:</strong> ${selectedData.driver}`;
        infoAngkutan.innerHTML = infoHTML;
      }
    });

    // ====== EVENT PLAT DRIVER ======
    platDriverSelect.addEventListener('change', function () {
      const selectedValue = this.value;
      if (!selectedValue) { infoAngkutan.innerHTML = ''; return; }
      const [plat, driver] = selectedValue.split(' - ');
      let infoHTML = '';
      if (plat?.trim()) infoHTML += `<strong>Plat Nomor:</strong> ${plat.trim()}<br>`;
      if (driver?.trim()) infoHTML += `<strong>Driver:</strong> ${driver.trim()}`;
      infoAngkutan.innerHTML = infoHTML;
    });

    // ====== AUTOCOMPLETE ======
// Ambil elemen input dan dropdown
const domisiliInput = document.getElementById('domisiliInput');
const domisiliDropdown = document.getElementById('domisiliDropdown');
const sekolahInput = document.getElementById('sekolahInput');
const sekolahDropdown = document.getElementById('sekolahDropdown');

// Tambahkan placeholder / hint text
domisiliInput.placeholder = "Ketik untuk mencari data kecamatan";
sekolahInput.placeholder = "Ketik untuk mencari data sekolah";

// Fungsi filter autocomplete
function filterAutocomplete(dataArray, field, query) {
  const q = query.trim().toLowerCase();
  if (!q) return []; // kosongkan dropdown jika belum mengetik
  return dataArray.filter(item => (item[field] || '').toLowerCase().includes(q)).slice(0, 10);
}

// Render dropdown
function renderDropdown(dropdownEl, items, field) {
  if (!items.length) {
    dropdownEl.style.display = 'none';
    dropdownEl.innerHTML = '';
    return;
  }
  dropdownEl.innerHTML = items.map(item => `<div class="autocomplete-item" data-value="${item[field]}">${item[field]}</div>`).join('');
  dropdownEl.style.display = 'block';
}

// Event input: filter dan tampilkan dropdown
domisiliInput.addEventListener('input', () => {
  const items = filterAutocomplete(domisiliData, 'kecamatan', domisiliInput.value);
  renderDropdown(domisiliDropdown, items, 'kecamatan');
});

sekolahInput.addEventListener('input', () => {
  const items = filterAutocomplete(sekolahData, 'sekolah', sekolahInput.value);
  renderDropdown(sekolahDropdown, items, 'sekolah');
});

// Event klik pada dropdown: pilih item
domisiliDropdown.addEventListener('click', e => {
  if (e.target.classList.contains('autocomplete-item')) {
    domisiliInput.value = e.target.dataset.value;
    domisiliDropdown.style.display = 'none';
  }
});
sekolahDropdown.addEventListener('click', e => {
  if (e.target.classList.contains('autocomplete-item')) {
    sekolahInput.value = e.target.dataset.value;
    sekolahDropdown.style.display = 'none';
  }
});

// Sembunyikan dropdown saat klik di luar
document.addEventListener('click', e => {
  if (!domisiliInput.contains(e.target) && !domisiliDropdown.contains(e.target)) domisiliDropdown.style.display = 'none';
  if (!sekolahInput.contains(e.target) && !sekolahDropdown.contains(e.target)) sekolahDropdown.style.display = 'none';
});


// Fungsi tampilkan error sementara
function showTempError(message) {
  const responseBox = document.getElementById('response');
  if (!responseBox) return;

  responseBox.innerText = "❌ " + message;
  responseBox.style.color = 'red';
  responseBox.style.backgroundColor = '#f8d7da';
  responseBox.style.border = '1px solid red';
  responseBox.style.padding = '10px';

  // hilangkan otomatis setelah 3 detik
  setTimeout(() => {
    responseBox.innerText = '';
    responseBox.style.backgroundColor = 'transparent';
    responseBox.style.border = 'none';
    responseBox.style.padding = '0';
  }, 3000);
}

// ====== SUBMIT FORM ======
form.addEventListener('submit', async (e) => {
  e.preventDefault();

  const submitButton = document.getElementById('submitButton');
  const buttonText = document.getElementById('buttonText');
  const responseBox = document.getElementById('response');
  submitButton.disabled = true;
  buttonText.innerHTML = 'Mengirim... <div class="spinner-small"></div>';

  try {
    const emailUser = localStorage.getItem('email');
    const namaUser = localStorage.getItem('nama');

    if (!emailUser || !namaUser) {
      showTempError("Profil Google tidak ditemukan. Silakan login ulang.");
      return;
    }

    // 🔹 Validasi domisili & sekolah agar harus dari dropdown
    const isValidDomisili = domisiliData.some(item => item.kecamatan === domisiliInput.value);
    const isValidSekolah = sekolahData.some(item => item.sekolah === sekolahInput.value);

    if (!isValidDomisili) {
      showTempError("Pilih domisili dari daftar yang tersedia (bukan ketik manual).");
      return;
    }
    if (!isValidSekolah) {
      showTempError("Pilih nama sekolah dari daftar yang tersedia (bukan ketik manual).");
      return;
    }

    // ambil plat_driver lengkap
    let platDriverValue = '';
    if (transportasiSelect.value === 'MPU') {
      platDriverValue = platDriverSelect?.value || '';
    } else if (transportasiSelect.value === 'BUS') {
      const selectedTrayek = trayekData['BUS'].find(item => norm(item.trayek) === norm(trayekSelect.value));
      if (selectedTrayek) platDriverValue = `${selectedTrayek.plat} - ${selectedTrayek.driver}`;
    }

    const jenisKelamin = document.getElementById('jenisKelamin')?.value || '';

    const payload = {
      transportasi: transportasiSelect.value,
      trayek: trayekSelect.value,
      plat_driver: platDriverValue,
      domisili: domisiliInput?.value || '',
      sekolah: sekolahInput?.value || '',
      jenis_kelamin: jenisKelamin,
      nama: namaUser,
      email: emailUser
    };

    // submit absen langsung ke Supabase
    const json = await kirimAbsen(payload);
    if (json.status !== 'ok') throw new Error(json.message || 'Gagal absen');

    // update user di Supabase
    const userId = localStorage.getItem('userId');
    if (userId) {
      await fetch(`${SB_URL}/rest/v1/user_QRCode?id=eq.${userId}`, {
        method: 'PATCH',
        headers: sbHdr(),
        body: JSON.stringify({
          domisili: payload.domisili,
          sekolah: payload.sekolah,
          jenis_kelamin: payload.jenis_kelamin
        })
      });
    }

    // setelah absen sukses, langsung ke tampilan login dengan pesan
    container.innerHTML = `
      <div style="text-align:center;padding:20px; background-color:#d4edda; border:1px solid green; border-radius:8px; margin-bottom:20px;">
        <h2 style="color:green;">✅ Terimakasih, Absen Telah Berhasil</h2>
      </div>
    `;

  } catch (err) {
    if (responseBox) {
      responseBox.innerText = "❌ " + err.message;
      responseBox.style.color = 'red';
      responseBox.style.backgroundColor = '#f8d7da';
      responseBox.style.border = '1px solid red';
      responseBox.style.padding = '10px';
    }
  } finally {
    submitButton.disabled = false;
    buttonText.innerHTML = 'Kirim Absen';
  }
});

document.addEventListener("DOMContentLoaded", function () {
  const devLink = document.getElementById("devLink");
  if (!devLink) return;

  devLink.addEventListener("click", function () {
    if (devLink.dataset.ig) {
      window.location.href = devLink.dataset.ig;
    }
  });
});


  } catch (err) {
    showLoading('form-container', '❌ Gagal memuat form absensi. Coba lagi nanti.');
    console.error('Gagal load pages/form.php:', err);
  }
})();