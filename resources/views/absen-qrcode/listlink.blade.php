<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>List Link Absen Angkutan Sekolah</title>
  <link rel="icon" href="/favicon.ico"/>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --navy:  #1a1854;
      --navy2: #2e2a8a;
      --purple:#3b1f6e;
      --gold:  #d4af37;
      --gold2: #f0d060;
      --gold3: #b8941f;
    }

    body {
      font-family: 'Montserrat', sans-serif;
      background: var(--navy);
      background-image:
        radial-gradient(ellipse at top left,  var(--navy2) 0%, transparent 55%),
        radial-gradient(ellipse at bottom right, var(--purple) 0%, transparent 55%);
      min-height: 100vh;
      padding: 48px 16px 80px;
      position: relative;
      overflow-x: hidden;
    }

    /* Dekorasi lingkaran */
    body::before {
      content: '';
      position: fixed;
      width: 600px; height: 600px;
      border-radius: 50%;
      border: 70px solid rgba(212,175,55,.05);
      top: -200px; right: -200px;
      pointer-events: none;
    }
    body::after {
      content: '';
      position: fixed;
      width: 400px; height: 400px;
      border-radius: 50%;
      border: 50px solid rgba(212,175,55,.05);
      bottom: -120px; left: -120px;
      pointer-events: none;
    }

    /* ── HEADER ── */
    .header {
      text-align: center;
      margin-bottom: 44px;
      animation: fadeUp .7s cubic-bezier(.22,.61,.36,1) both;
    }

    .logo-ring {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 90px; height: 90px;
      border-radius: 50%;
      border: 2px solid rgba(212,175,55,.4);
      background: rgba(255,255,255,.05);
      margin-bottom: 20px;
      box-shadow: 0 0 30px rgba(212,175,55,.15);
    }
    .logo-ring img {
      width: 82px; height: 82px;
      border-radius: 50%;
      object-fit: cover;
    }

    h1 {
      font-family: 'Cormorant Garamond', serif;
      font-size: clamp(26px, 5vw, 38px);
      font-weight: 700;
      color: #fff;
      letter-spacing: .5px;
      line-height: 1.2;
    }

    .subtitle {
      margin-top: 10px;
      font-size: 13px;
      color: rgba(255,255,255,.45);
      letter-spacing: .3px;
    }

    .divider {
      width: 60px; height: 2px;
      background: linear-gradient(90deg, transparent, var(--gold), transparent);
      margin: 16px auto 0;
    }

    /* ── TAB SWITCH (Bus / MPU) ── */
    .tab-switch {
      display: flex;
      justify-content: center;
      gap: 8px;
      max-width: 400px;
      margin: 0 auto 20px;
      animation: fadeUp .7s .05s cubic-bezier(.22,.61,.36,1) both;
    }
    .tab-btn {
      flex: 1;
      background: rgba(255,255,255,.06);
      border: 1px solid rgba(212,175,55,.2);
      border-radius: 50px;
      padding: 9px 0;
      color: rgba(255,255,255,.55);
      font-family: 'Montserrat', sans-serif;
      font-size: 12px;
      font-weight: 600;
      letter-spacing: .5px;
      cursor: pointer;
      transition: all .25s;
    }
    .tab-btn:hover {
      border-color: rgba(212,175,55,.4);
      color: #fff;
    }
    .tab-btn.active {
      background: linear-gradient(135deg, var(--gold), var(--gold3));
      border-color: transparent;
      color: var(--navy);
      box-shadow: 0 4px 16px rgba(212,175,55,.3);
    }
    .tab-btn:disabled {
      cursor: default;
    }
    .tab-btn:disabled:hover {
      border-color: rgba(212,175,55,.2);
      color: rgba(255,255,255,.55);
    }
    .tab-btn.active:disabled:hover {
      color: var(--navy);
    }

    /* ── SEARCH ── */
    .search-wrap {
      max-width: 400px;
      margin: 0 auto 36px;
      position: relative;
      animation: fadeUp .7s .1s cubic-bezier(.22,.61,.36,1) both;
    }
    .search-wrap input {
      width: 100%;
      background: rgba(255,255,255,.07);
      border: 1px solid rgba(212,175,55,.25);
      border-radius: 50px;
      padding: 12px 20px 12px 46px;
      color: #fff;
      font-family: 'Montserrat', sans-serif;
      font-size: 13px;
      outline: none;
      transition: border-color .3s, background .3s;
    }
    .search-wrap input::placeholder { color: rgba(255,255,255,.3); }
    .search-wrap input:focus {
      border-color: rgba(212,175,55,.6);
      background: rgba(255,255,255,.1);
    }
    .search-icon {
      position: absolute;
      left: 16px; top: 50%;
      transform: translateY(-50%);
      color: rgba(212,175,55,.6);
      font-size: 15px;
      pointer-events: none;
    }

    /* ── COUNTER ── */
    .counter {
      text-align: center;
      font-size: 12px;
      color: rgba(255,255,255,.3);
      margin-bottom: 24px;
      letter-spacing: .5px;
    }
    .counter span { color: var(--gold); font-weight: 600; }

    /* ── GRID ── */
    #driverContainer {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
      gap: 20px;
      max-width: 1200px;
      margin: 0 auto;
    }

    /* ── CARD ── */
    .driver-card {
      background: rgba(255,255,255,.05);
      border: 1px solid rgba(212,175,55,.15);
      border-radius: 18px;
      padding: 24px 20px 20px;
      display: flex;
      flex-direction: column;
      gap: 16px;
      position: relative;
      overflow: hidden;
      transition: transform .3s ease, box-shadow .3s ease, border-color .3s ease;
      animation: fadeUp .6s cubic-bezier(.22,.61,.36,1) both;
    }
    .driver-card::before {
      content: '';
      position: absolute;
      top: 0; left: 0; right: 0;
      height: 2px;
      background: linear-gradient(90deg, transparent, var(--gold), transparent);
      opacity: 0;
      transition: opacity .3s;
    }
    .driver-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 16px 40px rgba(0,0,0,.35), 0 0 0 1px rgba(212,175,55,.3);
      border-color: rgba(212,175,55,.35);
    }
    .driver-card:hover::before { opacity: 1; }

    .card-number {
      position: absolute;
      top: 16px; right: 18px;
      font-size: 10px;
      font-weight: 700;
      color: rgba(212,175,55,.35);
      letter-spacing: 1px;
    }

    .card-name {
      font-size: 14px;
      font-weight: 600;
      color: #fff;
      line-height: 1.4;
    }
    .card-label {
      font-size: 10px;
      font-weight: 500;
      letter-spacing: 1.5px;
      color: rgba(212,175,55,.6);
      text-transform: uppercase;
      margin-bottom: 2px;
    }

    .btn-absen {
      display: block;
      text-align: center;
      padding: 10px 0;
      background: linear-gradient(135deg, var(--gold), var(--gold3));
      color: var(--navy);
      font-family: 'Montserrat', sans-serif;
      font-size: 12px;
      font-weight: 700;
      letter-spacing: .5px;
      text-decoration: none;
      border-radius: 50px;
      transition: all .3s;
      box-shadow: 0 4px 16px rgba(212,175,55,.25);
    }
    .btn-absen:hover {
      background: linear-gradient(135deg, var(--gold2), var(--gold));
      box-shadow: 0 6px 24px rgba(212,175,55,.45);
      transform: translateY(-1px);
    }

    /* ── SKELETON / LOADING ── */
    .skeleton-card {
      background: rgba(255,255,255,.04);
      border: 1px solid rgba(255,255,255,.07);
      border-radius: 18px;
      padding: 24px 20px 20px;
      display: flex; flex-direction: column; gap: 16px;
    }
    .sk { background: rgba(255,255,255,.08); border-radius: 6px; animation: pulse 1.5s infinite; }
    .sk-icon { width: 44px; height: 44px; border-radius: 12px; }
    .sk-line1 { height: 12px; width: 60%; }
    .sk-line2 { height: 12px; width: 90%; }
    .sk-btn { height: 36px; border-radius: 50px; }
    @keyframes pulse {
      0%, 100% { opacity: .5; }
      50%       { opacity: 1; }
    }

    /* ── EMPTY / ERROR STATE ── */
    .state-box {
      grid-column: 1/-1;
      text-align: center;
      padding: 60px 20px;
      color: rgba(255,255,255,.4);
      font-size: 14px;
    }
    .state-box .icon { font-size: 40px; margin-bottom: 12px; }
    .state-box.error { color: #f87171; }

    /* ── CREDIT ── */
    .dev-credit {
      position: fixed;
      bottom: 8px; right: 14px;
      font-size: 11px;
      color: rgba(255,255,255,.25);
      z-index: 999;
      transition: color .3s;
    }
    .dev-credit:hover { color: rgba(255,255,255,.6); }
    .dev-credit a {
      color: inherit;
      text-decoration: none;
      border-bottom: 1px dotted rgba(212,175,55,.3);
      transition: color .3s, border-color .3s;
    }
    .dev-credit a:hover {
      color: var(--gold);
      border-color: var(--gold);
    }

    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(24px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 480px) {
      #driverContainer { grid-template-columns: 1fr 1fr; gap: 14px; }
      .driver-card { padding: 18px 14px 16px; }
    }
  </style>
</head>
<body>

  <!-- HEADER -->
  <div class="header">
    <div class="logo-ring">
      <img src="/assets/dishub.png" alt="Logo Dishub">
    </div>
    <h1>Absen Angkutan<br>Sekolah Gratis</h1>
    <p class="subtitle">Pilih driver untuk memulai absensi</p>
    <div class="divider"></div>
  </div>

  <!-- TAB SWITCH -->
  <div class="tab-switch" id="tabSwitch">
    <button type="button" class="tab-btn active" data-jenis="bus">🚌 Bus</button>
    <button type="button" class="tab-btn" data-jenis="mpu">🚐 MPU</button>
  </div>

  <!-- SEARCH -->
  <div class="search-wrap">
    <span class="search-icon">🔍</span>
    <input type="text" id="searchInput" placeholder="Cari nama driver..." oninput="filterDrivers()">
  </div>

  <!-- COUNTER -->
  <div class="counter" id="counterText"></div>

  <!-- GRID KARTU -->
  <div id="driverContainer"></div>

  <script>
    const SB_URL  = {!! json_encode(config('services.supabase.url')) !!};
    const SB_ANON = {!! json_encode(config('services.supabase.anon_key')) !!};

    const sbHeaders = {
      'apikey'       : SB_ANON,
      'Authorization': `Bearer ${SB_ANON}`,
    };

    let allDrivers  = [];
    let currentJenis = 'bus';

    function showSkeletons(n = 8) {
      const c = document.getElementById("driverContainer");
      c.innerHTML = Array(n).fill(`
        <div class="skeleton-card">
          <div class="sk sk-icon"></div>
          <div class="sk sk-line1"></div>
          <div class="sk sk-line2"></div>
          <div class="sk sk-btn"></div>
        </div>`).join('');
    }

    function renderCards(drivers) {
      const c = document.getElementById("driverContainer");
      const counter = document.getElementById("counterText");

      if (!drivers.length) {
        c.innerHTML = `<div class="state-box"><div class="icon">🔍</div>Tidak ada driver ditemukan.</div>`;
        counter.innerHTML = '';
        return;
      }

      counter.innerHTML = `Menampilkan <span>${drivers.length}</span> driver`;
      c.innerHTML = '';

      drivers.forEach((driver, i) => {
        const card = document.createElement("div");
        card.className = "driver-card";
        card.style.animationDelay = `${i * 40}ms`;

        card.innerHTML = `
          <span class="card-number">#${String(i + 1).padStart(2, '0')}</span>
          <div>
            <div class="card-label">Driver ${driver.jenis === 'mpu' ? 'MPU' : 'Bus'}</div>
            <div class="card-name">${driver.nama || 'Driver'}</div>
          </div>
          <a href="${driver.link}" class="btn-absen">Absen Sekarang →</a>
        `;

        c.appendChild(card);
      });
    }

    function filterDrivers() {
      const q = document.getElementById("searchInput").value.trim().toLowerCase();
      let filtered;

      if (q) {
        // Mode pencarian: cari lintas KEDUA submenu, tab jadi indikator
        // otomatis mengikuti jenis driver yang ketemu (bukan lagi dikontrol
        // manual oleh klik user).
        filtered = allDrivers.filter(d => (d.nama || '').toLowerCase().includes(q));
        const jenisSet = new Set(filtered.map(d => d.jenis));
        updateTabsState(jenisSet);
      } else {
        // Mode normal: tab dikontrol manual oleh user lewat klik.
        filtered = allDrivers.filter(d => d.jenis === currentJenis);
        updateTabsState(null);
      }

      renderCards(filtered);
    }

    // jenisSet === null → mode manual (tab bisa diklik, ikut currentJenis).
    // jenisSet berisi Set → mode otomatis (tab jadi indikator, tidak bisa
    // diklik selama masih ada teks di kolom pencarian).
    function updateTabsState(jenisSet) {
      document.querySelectorAll('.tab-btn').forEach(btn => {
        if (jenisSet === null) {
          btn.disabled = false;
          btn.classList.toggle('active', btn.dataset.jenis === currentJenis);
        } else {
          btn.disabled = true;
          btn.classList.toggle('active', jenisSet.has(btn.dataset.jenis));
        }
      });
    }

    function setJenis(jenis) {
      currentJenis = jenis;
      document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.jenis === jenis);
      });
      filterDrivers();
    }

    document.getElementById('tabSwitch').addEventListener('click', (e) => {
      const btn = e.target.closest('.tab-btn');
      if (btn) setJenis(btn.dataset.jenis);
    });

    // Sekarang lewat php/cache_proxy.php (cache whole-table di server,
    // auto refresh 24 jam) — bukan fetch langsung ke Supabase dari browser.
    // Param `select` tetap dipakai untuk memangkas kolom yang dikirim balik.
    async function fetchAllRecords(table, select = '*') {
      const params = new URLSearchParams({ table, select });
      const res = await fetch(`/api/supabase-proxy?${params}`);
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      return await res.json();
    }

    // Sumber data LANGSUNG dari driver_bus & driver_mpu (kolom id + driver),
    // tabel `deeplink_absenangkutan` TIDAK dipakai lagi sama sekali di sini.
    // Link absen dibangun sendiri dari id driver ("/absenqrcode?id=" + id), jadi
    // datanya dijamin selalu match & realtime — begitu driver ditambah/diedit/
    // dihapus di Data Driver, halaman ini langsung ikut berubah.
    async function loadDrivers() {
      showSkeletons();
      try {
        const [driverBusRows, driverMpuRows] = await Promise.all([
          fetchAllRecords('driver_bus', 'id,driver'),
          fetchAllRecords('driver_mpu', 'id,driver'),
        ]);

        const busDrivers = driverBusRows
          .filter(d => d.id && d.driver)
          .map(d => ({ id: d.id, nama: d.driver, link: `/absenqrcode?id=${d.id}`, jenis: 'bus' }));

        const mpuDrivers = driverMpuRows
          .filter(d => d.id && d.driver)
          .map(d => ({ id: d.id, nama: d.driver, link: `/absenqrcode?id=${d.id}`, jenis: 'mpu' }));

        allDrivers = [...busDrivers, ...mpuDrivers];

        filterDrivers();
      } catch (err) {
        console.error(err);
        document.getElementById("driverContainer").innerHTML = `
          <div class="state-box error">
            <div class="icon">⚠️</div>
            Gagal memuat data driver. Silakan refresh halaman.
          </div>`;
        document.getElementById("counterText").innerHTML = '';
      }
    }

    loadDrivers();
  </script>

  <div class="dev-credit">
    @include('partials.dev-credit-html')
  </div>

</body>
</html>