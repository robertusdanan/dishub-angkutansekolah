<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"/>
<meta name="csrf-token" content="{{ csrf_token() }}"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Titik Lokasi Wisata — Admin</title>
<link rel="icon" href="/favicon.ico"/>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/admin/admin-shell.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.css"/>
<style>
  .adm-shell { display:flex !important; }
  .adm-main  { flex:1; min-width:0; }

  .toolbar { display:flex; gap:10px; align-items:center; margin-bottom:16px; flex-wrap:wrap; justify-content:space-between; }
  .search-wrap { position:relative; flex:1; min-width:160px; max-width:320px; }
  .search-wrap svg { position:absolute; left:11px; top:50%; transform:translateY(-50%); color:var(--text-4); pointer-events:none; }
  .search-input { width:100%; padding:8px 11px 8px 34px; border:1.5px solid var(--border); border-radius:9px; font-size:13px; color:var(--text-1); background:var(--surface-2); outline:none; }
  .search-input:focus { border-color:var(--accent); box-shadow:0 0 0 3px var(--accent-glow); background:#fff; }

  .grid-titik { display:grid; grid-template-columns:repeat(auto-fill,minmax(260px,1fr)); gap:16px; }
  .card-titik { background:var(--surface); border:1.5px solid var(--border); border-radius:14px; overflow:hidden; display:flex; flex-direction:column; }
  .card-titik-cover { height:140px; background:var(--surface-2) center/cover no-repeat; position:relative; display:flex; align-items:center; justify-content:center; color:var(--text-4); font-size:12px; }
  .card-titik-badge { position:absolute; top:8px; left:8px; background:rgba(15,23,42,.72); color:#fff; font-size:10.5px; font-weight:600; padding:3px 8px; border-radius:20px; text-transform:uppercase; letter-spacing:.04em; }
  .card-titik-body { padding:14px 14px 12px; display:flex; flex-direction:column; gap:6px; flex:1; }
  .card-titik-name { font-weight:700; font-size:14.5px; color:var(--text-1); }
  .card-titik-coord { font-family:'DM Mono',monospace; font-size:11.5px; color:var(--text-3); }
  .card-titik-desc { font-size:12.5px; color:var(--text-3); line-height:1.5; max-height:38px; overflow:hidden; }
  .card-titik-foot { display:flex; gap:6px; margin-top:auto; padding-top:8px; }
  .card-titik-foot .btn { flex:1; }

  .modal-overlay { position:fixed; inset:0; background:rgba(10,14,28,.6); z-index:9998; display:none; align-items:flex-start; justify-content:center; padding:40px 16px; overflow-y:auto; }
  .modal-overlay.open { display:flex; }
  .modal-box { background:#fff; border-radius:16px; width:100%; max-width:640px; box-shadow:0 24px 70px rgba(0,0,0,.28); }
  .modal-head { padding:18px 22px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; }
  .modal-title { font-size:16px; font-weight:700; color:var(--text-1); }
  .modal-close { cursor:pointer; color:var(--text-4); background:none; border:none; font-size:18px; }
  .modal-body { padding:20px 22px; display:flex; flex-direction:column; gap:14px; max-height:70vh; overflow-y:auto; }
  .modal-foot { padding:16px 22px; border-top:1px solid var(--border); display:flex; justify-content:flex-end; gap:8px; }

  .f-row { display:flex; flex-direction:column; gap:5px; }
  .f-label { font-size:12px; font-weight:600; color:var(--text-2); }
  .f-input, .f-textarea, .f-select { padding:9px 11px; border:1.5px solid var(--border); border-radius:9px; font-family:'DM Sans',sans-serif; font-size:13.5px; color:var(--text-1); outline:none; }
  .f-input:focus, .f-textarea:focus, .f-select:focus { border-color:var(--accent); box-shadow:0 0 0 3px var(--accent-glow); }
  .f-textarea { resize:vertical; min-height:64px; }
  .f-two { display:grid; grid-template-columns:1fr 1fr; gap:12px; }

  #miniMap { height:220px; border-radius:10px; border:1.5px solid var(--border); }

  .gal-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(96px,1fr)); gap:8px; }
  .gal-item { position:relative; border-radius:9px; overflow:hidden; background:var(--surface-2); aspect-ratio:1/1; border:1.5px solid var(--border); }
  .gal-item img, .gal-item video { width:100%; height:100%; object-fit:cover; }
  .gal-item .gal-del { position:absolute; top:4px; right:4px; width:22px; height:22px; border-radius:50%; background:rgba(15,23,42,.75); color:#fff; border:none; cursor:pointer; font-size:13px; display:flex; align-items:center; justify-content:center; }
  .gal-add { display:flex; align-items:center; justify-content:center; border:1.5px dashed var(--border-2); border-radius:9px; aspect-ratio:1/1; cursor:pointer; color:var(--text-4); font-size:12px; flex-direction:column; gap:4px; }
  .gal-add:hover { border-color:var(--accent); color:var(--accent); }

  .toast { position:fixed; bottom:24px; right:24px; z-index:9999; display:flex; align-items:center; gap:8px; padding:11px 16px; border-radius:10px; font-size:13px; font-weight:500; box-shadow:0 8px 24px rgba(15,23,42,.15); border:1.5px solid transparent; opacity:0; transform:translateY(10px); transition:opacity .2s,transform .2s; pointer-events:none; max-width:340px; }
  .toast.show    { opacity:1; transform:translateY(0); }
  .toast.success { background:#f0fdf4; border-color:#bbf7d0; color:#15803d; }
  .toast.error   { background:#fef2f2; border-color:#fecaca; color:#b91c1c; }
  .bspin { width:12px; height:12px; border:2px solid rgba(255,255,255,.3); border-top-color:#fff; border-radius:50%; animation:spin .6s linear infinite; display:inline-block; }
  @keyframes spin { to{transform:rotate(360deg)} }
  .empty-state { grid-column:1/-1; text-align:center; padding:48px; color:var(--text-4); font-size:13.5px; }
</style>
</head>
<body>
<div class="adm-shell">
  @include('admin.partials.sidebar', ['currentPage' => 'trayekwisata_titik', 'currentModule' => 'trayekwisata'])

  <main class="adm-main">
    <div class="adm-content">

      <div class="adm-page-header">
        <div>
          <h1 class="adm-page-title">Titik Lokasi Wisata</h1>
          <p class="adm-page-subtitle">Master pantai/terminal/transit — koordinat &amp; galeri di sini dipakai peta rute publik dan Pengaturan Trayek Tahunan.</p>
        </div>
        <button class="btn btn-primary btn-sm" onclick="openForm()">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Tambah Titik
        </button>
      </div>

      <div class="toolbar">
        <div class="search-wrap">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input type="text" id="searchInput" class="search-input" placeholder="Cari nama titik..." oninput="renderGrid()"/>
        </div>
      </div>

      <div class="grid-titik" id="gridTitik">
        <div class="empty-state">Memuat data…</div>
      </div>

    </div>
  </main>
</div>

<!-- ═══ Modal Form Titik ═══ -->
<div class="modal-overlay" id="formOverlay">
  <div class="modal-box">
    <div class="modal-head">
      <span class="modal-title" id="formTitle">Tambah Titik Lokasi</span>
      <button class="modal-close" onclick="closeForm()">✕</button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="f_id"/>
      <div class="f-row">
        <span class="f-label">Nama Titik</span>
        <input class="f-input" id="f_nama" placeholder="mis. Pantai Gemah"/>
      </div>
      <div class="f-two">
        <div class="f-row">
          <span class="f-label">Jenis</span>
          <select class="f-select" id="f_jenis">
            <option value="wisata">Wisata (pantai/objek)</option>
            <option value="terminal">Terminal Keberangkatan</option>
            <option value="transit">Titik Transit / JLS</option>
          </select>
        </div>
        <div class="f-row">
          <span class="f-label">Status</span>
          <select class="f-select" id="f_aktif">
            <option value="true">Aktif (tampil di website)</option>
            <option value="false">Nonaktif</option>
          </select>
        </div>
      </div>
      <div class="f-row">
        <span class="f-label">Deskripsi Singkat</span>
        <textarea class="f-textarea" id="f_deskripsi" placeholder="Deskripsi untuk ditampilkan saat marker peta diklik..."></textarea>
      </div>
      <div class="f-row">
        <span class="f-label">Koordinat — klik peta atau isi manual, lalu geser marker jika perlu</span>
        <div id="miniMap"></div>
        <div class="f-two">
          <input class="f-input" id="f_lat" placeholder="Latitude, mis. -8.1936" inputmode="decimal"/>
          <input class="f-input" id="f_lng" placeholder="Longitude, mis. 111.8817" inputmode="decimal"/>
        </div>
      </div>
      <div class="f-row">
        <span class="f-label">Jumlah Kunjungan (ditampilkan di peta)</span>
        <input class="f-input" id="f_kunjungan" type="number" min="0" value="0"/>
      </div>

      <div class="f-row" id="galeriSection" style="display:none">
        <span class="f-label">Galeri Foto &amp; Video</span>
        <div class="gal-grid" id="galGrid"></div>
        <input type="file" id="galFileInput" accept="image/*,video/*" style="display:none" onchange="uploadGaleri(this.files[0])"/>
      </div>
    </div>
    <div class="modal-foot">
      <button class="btn btn-secondary btn-sm" onclick="closeForm()">Batal</button>
      <button class="btn btn-primary btn-sm" id="btnSaveForm" onclick="saveForm()">Simpan</button>
    </div>
  </div>
</div>

<div class="toast" id="toast"></div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.js"></script>
<script src="/assets/admin/trayekwisata/sb-secure.js"></script>
<script>
let allTitik = [];
let miniMap, miniMarker;
let currentGaleri = [];

async function loadData() {
  document.getElementById('gridTitik').innerHTML = '<div class="empty-state">Memuat data…</div>';
  try {
    allTitik = await twGet('trayekwisata_titik', 'select=*&order=urutan.asc,nama.asc');
    renderGrid();
  } catch (e) {
    document.getElementById('gridTitik').innerHTML = `<div class="empty-state" style="color:#ef4444">Gagal memuat: ${twEsc(e.message)}</div>`;
  }
}

function renderGrid() {
  const q = document.getElementById('searchInput').value.toLowerCase().trim();
  const rows = q ? allTitik.filter(r => (r.nama||'').toLowerCase().includes(q)) : allTitik;
  const grid = document.getElementById('gridTitik');

  if (!rows.length) {
    grid.innerHTML = `<div class="empty-state">Belum ada titik lokasi. Klik "Tambah Titik" untuk mulai.</div>`;
    return;
  }

  grid.innerHTML = rows.map(r => `
    <div class="card-titik">
      <div class="card-titik-cover" style="${r._cover ? `background-image:url('${twEsc(r._cover)}')` : ''}">
        <span class="card-titik-badge">${twEsc(r.jenis)}</span>
        ${r._cover ? '' : '<span>Belum ada foto</span>'}
      </div>
      <div class="card-titik-body">
        <span class="card-titik-name">${twEsc(r.nama)}</span>
        <span class="card-titik-coord">${r.lat && r.lng ? `${r.lat}, ${r.lng}` : 'Koordinat belum diatur'}</span>
        <span class="card-titik-desc">${twEsc(r.deskripsi || '—')}</span>
        <div class="card-titik-foot">
          <button class="btn btn-secondary btn-sm" onclick="openForm('${r.id}')">Kelola</button>
          <button class="btn btn-secondary btn-sm" style="color:#b91c1c" onclick="deleteTitik('${r.id}','${twEsc(r.nama)}')">Hapus</button>
        </div>
      </div>
    </div>
  `).join('');

  // Ambil cover (foto pertama) untuk tiap kartu secara lazy, sekali per load.
  rows.forEach(async r => {
    if (r._cover !== undefined) return;
    try {
      const gal = await twGet('trayekwisata_titik_galeri', `titik_id=eq.${r.id}&tipe=eq.foto&order=urutan.asc&limit=1`);
      r._cover = gal[0]?.url || null;
      if (r._cover) renderGrid();
    } catch (e) { r._cover = null; }
  });
}

function initMiniMap() {
  if (typeof L === 'undefined' || typeof L.map !== 'function') {
    console.error('Leaflet belum termuat — peta pemilih koordinat tidak bisa ditampilkan.');
    const el = document.getElementById('miniMap');
    if (el) el.innerHTML = '<div style="display:flex;align-items:center;justify-content:center;height:100%;color:var(--text-4);font-size:12px;padding:12px;text-align:center">Peta gagal dimuat. Coba muat ulang halaman, atau isi koordinat manual di bawah.</div>';
    return;
  }
  if (miniMap) return;
  miniMap = L.map('miniMap', { zoomControl: true }).setView([-8.0654, 111.9022], 10); // pusat Tulungagung
  // Tile Google Maps asli — pola yang sama dengan peta publik ASDP Anda
  // (pages/asdp/index.html), supaya tampilan peta konsisten di seluruh
  // aplikasi dan benar-benar terlihat seperti Google Maps.
  L.tileLayer('http://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
    maxZoom: 20, subdomains: ['mt0','mt1','mt2','mt3']
  }).addTo(miniMap);
  miniMap.on('click', (e) => setMarker(e.latlng.lat, e.latlng.lng));
}

function setMarker(lat, lng) {
  document.getElementById('f_lat').value = lat.toFixed(6);
  document.getElementById('f_lng').value = lng.toFixed(6);
  if (miniMarker) { miniMarker.setLatLng([lat, lng]); }
  else { miniMarker = L.marker([lat, lng], {draggable:true}).addTo(miniMap); miniMarker.on('dragend', () => { const p = miniMarker.getLatLng(); setMarker(p.lat, p.lng); }); }
  miniMap.panTo([lat, lng]);
}

async function openForm(id) {
  document.getElementById('formOverlay').classList.add('open');
  setTimeout(initMiniMap, 60);

  if (!id) {
    document.getElementById('formTitle').textContent = 'Tambah Titik Lokasi';
    document.getElementById('f_id').value = '';
    document.getElementById('f_nama').value = '';
    document.getElementById('f_jenis').value = 'wisata';
    document.getElementById('f_aktif').value = 'true';
    document.getElementById('f_deskripsi').value = '';
    document.getElementById('f_lat').value = '';
    document.getElementById('f_lng').value = '';
    document.getElementById('f_kunjungan').value = 0;
    document.getElementById('galeriSection').style.display = 'none';
    currentGaleri = [];
    if (miniMarker) { miniMap.removeLayer(miniMarker); miniMarker = null; }
    return;
  }

  const row = allTitik.find(r => r.id === id);
  if (!row) return;
  document.getElementById('formTitle').textContent = 'Kelola: ' + row.nama;
  document.getElementById('f_id').value = row.id;
  document.getElementById('f_nama').value = row.nama;
  document.getElementById('f_jenis').value = row.jenis;
  document.getElementById('f_aktif').value = String(row.aktif);
  document.getElementById('f_deskripsi').value = row.deskripsi || '';
  document.getElementById('f_lat').value = row.lat ?? '';
  document.getElementById('f_lng').value = row.lng ?? '';
  document.getElementById('f_kunjungan').value = row.jumlah_kunjungan ?? 0;
  document.getElementById('galeriSection').style.display = '';

  if (row.lat && row.lng) setTimeout(() => setMarker(parseFloat(row.lat), parseFloat(row.lng)), 80);

  try {
    currentGaleri = await twGet('trayekwisata_titik_galeri', `titik_id=eq.${id}&order=urutan.asc`);
  } catch(e) { currentGaleri = []; }
  renderGaleri();
}

function renderGaleri() {
  const grid = document.getElementById('galGrid');
  grid.innerHTML = currentGaleri.map(g => `
    <div class="gal-item">
      ${g.tipe === 'video' ? `<video src="${twEsc(g.url)}" muted></video>` : `<img src="${twEsc(g.url)}" loading="lazy"/>`}
      <button class="gal-del" onclick="deleteGaleri('${g.id}')">✕</button>
    </div>
  `).join('') + `<div class="gal-add" onclick="document.getElementById('galFileInput').click()">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    Tambah
  </div>`;
}

async function uploadGaleri(file) {
  const id = document.getElementById('f_id').value;
  if (!id) { twToast('Simpan titik lokasi dulu sebelum menambah galeri.', 'error'); return; }
  if (!file) return;
  const fd = new FormData();
  fd.append('titik_id', id);
  fd.append('file', file);
  twToast('Mengupload...', 'success');
  try {
    const res = await fetch('/admin/api/trayekwisata/upload-galeri', { method:'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }, body: fd });
    const data = await res.json();
    if (!res.ok) throw new Error(data.error || 'Upload gagal');
    currentGaleri.push(data);
    renderGaleri();
    twToast('✓ File berhasil ditambahkan');
  } catch (e) {
    twToast('Gagal upload: ' + e.message, 'error');
  }
  document.getElementById('galFileInput').value = '';
}

async function deleteGaleri(galId) {
  if (!confirm('Hapus file ini dari galeri?')) return;
  try {
    await twDelete('trayekwisata_titik_galeri', `id=eq.${galId}`);
    currentGaleri = currentGaleri.filter(g => g.id !== galId);
    renderGaleri();
    twToast('✓ File dihapus');
  } catch (e) { twToast('Gagal menghapus: ' + e.message, 'error'); }
}

function closeForm() {
  document.getElementById('formOverlay').classList.remove('open');
}

async function saveForm() {
  const id   = document.getElementById('f_id').value;
  const nama = document.getElementById('f_nama').value.trim();
  if (!nama) { twToast('Nama titik wajib diisi', 'error'); return; }

  const lat = document.getElementById('f_lat').value.trim();
  const lng = document.getElementById('f_lng').value.trim();

  const payload = {
    nama,
    jenis: document.getElementById('f_jenis').value,
    aktif: document.getElementById('f_aktif').value === 'true',
    deskripsi: document.getElementById('f_deskripsi').value.trim(),
    lat: lat === '' ? null : parseFloat(lat),
    lng: lng === '' ? null : parseFloat(lng),
    jumlah_kunjungan: parseInt(document.getElementById('f_kunjungan').value || '0', 10),
  };

  const btn = document.getElementById('btnSaveForm');
  btn.disabled = true; btn.innerHTML = '<span class="bspin"></span>';

  try {
    if (id) {
      await twPatch('trayekwisata_titik', `id=eq.${id}`, { ...payload, updated_at: new Date().toISOString() });
      const idx = allTitik.findIndex(r => r.id === id);
      if (idx > -1) allTitik[idx] = { ...allTitik[idx], ...payload };
      twToast('✓ Titik lokasi diperbarui');
      renderGrid();
    } else {
      const created = await twPost('trayekwisata_titik', payload);
      allTitik.unshift(created[0]);
      twToast('✓ Titik lokasi ditambahkan — sekarang tambahkan galeri fotonya');
      renderGrid();
      openForm(created[0].id); // buka lagi supaya bisa langsung upload galeri
    }
  } catch (e) {
    twToast('Gagal menyimpan: ' + e.message, 'error');
  } finally {
    btn.disabled = false; btn.textContent = 'Simpan';
  }
}

async function deleteTitik(id, nama) {
  if (!confirm(`Hapus titik "${nama}"? Galeri &amp; keterkaitan trayek ikut terhapus.`)) return;
  try {
    await twDelete('trayekwisata_titik', `id=eq.${id}`);
    allTitik = allTitik.filter(r => r.id !== id);
    renderGrid();
    twToast('✓ Titik lokasi dihapus');
  } catch (e) { twToast('Gagal menghapus: ' + e.message, 'error'); }
}

loadData();
</script>
</body>
</html>
