<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Balik Gratis {{ $bgTahun }} – Tulungagung › Surabaya</title>
  <meta name="description" content="Pendaftaran Balik Gratis Tulungagung–Surabaya {{ $bgTahun }}, Dinas Perhubungan Kabupaten Tulungagung.">
  <link rel="icon" href="/favicon.ico"/>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
  <link rel="stylesheet" href="{{ asset_url('/assets/balik-gratis/style.css') }}">
</head>
<body>

<div class="page-bg" aria-hidden="true">
  <div class="bg-img"></div>
  <div class="orb orb-1"></div>
  <div class="orb orb-2"></div>
</div>

<div id="pageLoading">
  <div class="loader-ring"></div>
  <p class="loader-text">Memuat layanan Balik Gratis…</p>
</div>

@include('partials.balik-gratis.nav', ['bgTahun' => $bgTahun])

<div class="page-wrap">
<main class="content-box">

  <header class="site-header">
    <h1 class="header-title">Balik <span class="accent">Gratis</span> {{ $bgTahun }}</h1>
    <p class="header-route">
      <span>Tulungagung</span>
      <span class="route-sep">›</span>
      <span>Surabaya</span>
    </p>
    @if ($bgBuka)
    <div class="quota-badge">
      <div class="q-live">
        <div class="q-dot"></div>
        <span>Kuota Tersisa</span>
      </div>
      <span class="q-num" id="sisaKuota">{{ $bgSisa }}</span>
      <span>/ {{ $bgKuota }} tiket</span>
    </div>
    @endif
  </header>

  @if (!$bgBuka)

  <div class="tutup-card">
    <div class="tutup-icon">🚌</div>
    <div class="tutup-title">Pendaftaran Belum Dibuka</div>
    <div class="tutup-desc">
      Pendaftaran Balik Gratis {{ $bgTahun }} belum dibuka oleh Dinas Perhubungan Kabupaten Tulungagung.
      Silakan pantau kembali halaman ini, atau ikuti kanal resmi Dishub Tulungagung untuk info jadwal pembukaan kuota.
    </div>
  </div>

  @else

  <div id="choiceScreen">
    <div class="card">
      <div id="kuotaHabisBanner" class="kuota-habis-banner">
        <div class="khb-icon">⚠️</div>
        <div class="khb-body">
          <div class="khb-title">Kuota Penuh</div>
          <div class="khb-desc">Kuota Balik Gratis {{ $bgTahun }} sudah terpenuhi. Silakan pantau pengumuman berikutnya dari Dishub Tulungagung.</div>
        </div>
      </div>

      <div class="choice-grid">
        <button type="button" id="btnDaftarChoice" onclick="openDaftar()" class="choice-btn primary">
          <div class="choice-icon-wrap">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#38bdf8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z"/><path d="M13 5v2"/><path d="M13 17v2"/><path d="M13 11v2"/></svg>
          </div>
          <div class="choice-text">
            <div class="choice-title">Daftar Sekarang</div>
            <div class="choice-sub">Ambil tiket Balik Gratis</div>
          </div>
        </button>
        <button type="button" onclick="cekTiketSaya()" class="choice-btn secondary">
          <div class="choice-icon-wrap">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
          </div>
          <div class="choice-text">
            <div class="choice-title">Cek Tiket Saya</div>
            <div class="choice-sub">Lihat &amp; unduh tiket</div>
          </div>
        </button>
      </div>
    </div>
  </div>

  <div id="cekTiketWrapper" class="hidden">
    <div class="card"><div class="card-inner">
      <button type="button" onclick="backToChoice()" class="btn-back">← Kembali</button>
      <div class="form-head">
        <div class="form-head-icon">🔍</div>
        <div>
          <div class="form-head-title">Cek Tiket Saya</div>
          <div class="form-head-sub">Masukkan NIK terdaftar</div>
        </div>
      </div>
      <div class="search-row">
        <input id="nikCari" inputmode="numeric" maxlength="16" placeholder="16 digit NIK" class="inp">
        <button type="button" onclick="cariTiketByNIK()" class="btn-search">Cari</button>
      </div>
      <div id="hasilCekTiket" class="search-result-area"></div>
    </div></div>
  </div>

  <div id="daftarWrapper" class="hidden" id="daftar">
    <div class="card"><div class="card-inner">
      <button type="button" onclick="backToChoice()" class="btn-back">← Kembali</button>
      <div class="form-head">
        <div class="form-head-icon">🎫</div>
        <div>
          <div class="form-head-title">Formulir Pendaftaran</div>
          <div class="form-head-sub">Isi semua data dengan benar dan lengkap</div>
        </div>
      </div>

      <!-- Pendaftaran gaya "KAI Access": pemilik akun jadi penumpang
           default, bisa ditambah anggota keluarga yang sudah terdaftar
           di halaman Akun Saya. Data identitas & foto KTP/KK TIDAK
           diinput ulang di sini — semua diambil dari akun yang login. -->
      <div id="lengkapiProfilBanner" class="hidden" style="background:#fff4e5;border:1.5px solid #f5c98c;border-radius:12px;padding:14px 16px;margin-bottom:16px;font-size:13px;color:#7a4a0a;">
        Lengkapi dulu data akun Anda sebelum bisa mendaftar: <strong id="lengkapiList"></strong>.
        <a id="lengkapiLink" href="/akun/saya" style="color:#7a4a0a;font-weight:700;text-decoration:underline;">Lengkapi sekarang →</a>
      </div>

      <div id="paxPickerLabel" class="field-label" style="margin-bottom:8px;">Pilih Penumpang</div>
      <div id="paxPicker" style="display:flex;flex-direction:column;gap:8px;margin-bottom:18px;"></div>
      <p style="font-size:12px;color:var(--ink-3,#6b7280);margin:-8px 0 18px;">
        Anggota keluarga belum kelihatan di sini? Tambahkan dulu di
        <a href="/akun/saya" style="color:inherit;font-weight:600;">halaman Akun Saya</a>.
        Setiap NIK hanya bisa 1 tiket Balik Gratis per tahun.
      </p>

      <div class="form-err" id="formErr"></div>
      <button id="btnSubmit" type="button" onclick="submitDaftar()" class="btn-submit">Daftar Sekarang →</button>
    </div></div>
  </div>

  @endif

</main>
</div>

@include('partials.balik-gratis.footer')

<!-- Ticket modal -->
<div class="tiket-overlay" id="tiketOverlay">
  <div class="tiket-box" id="tiketBox"></div>
</div>

<script>window.BG_TAHUN = {{ $bgTahun }}; window.BG_BUKA = {{ $bgBuka ? 'true' : 'false' }};</script>
<script src="{{ asset_url('/assets/balik-gratis/main.js') }}"></script>
<script src="{{ asset_url('/assets/balik-gratis/ticketpdf.js') }}"></script>
</body>
</html>
