<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"/>
<meta name="csrf-token" content="{{ csrf_token() }}"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover"/>
<title>Tanya Jawab — Trayek Wisata Gratis | Dishub Kabupaten Tulungagung</title>
<meta name="description" content="Pertanyaan yang sering diajukan seputar Trayek Wisata Gratis Dishub Kabupaten Tulungagung — aturan pemesanan, verifikasi, dan kuota."/>
<meta name="theme-color" content="#0A1F44"/>
<link rel="icon" href="/favicon.ico"/>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,300..700;1,9..144,400..600&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset_url('/assets/trayek-wisata/style.css') }}"/>
</head>
<body>
<script src="{{ asset_url('/assets/trayek-wisata/nav-progress.js') }}"></script>
<a href="#main" class="skip-link">Lompat ke konten utama</a>

@include('partials.trayek-wisata.nav', ['twNavSolid' => false])

@include('partials.trayek-wisata.page-header', [
    'twPageEyebrow' => 'Tanya Jawab',
    'twPageTitle' => 'Yang sering<br/>ditanyakan.',
    'twPageDesc' => 'Semua yang perlu Anda tahu sebelum ikut Trayek Wisata Gratis — dari aturan pemesanan sampai verifikasi dokumen.',
])

<main id="main">
<section class="faq" style="padding-top:70px">
  <div class="wrap">
    <div class="faq-list" id="faqList" style="margin-top:0">
      <div class="faq-item">
        <button class="faq-q" onclick="toggleFaq(this)" aria-expanded="false"><span>Apakah benar-benar gratis?</span><span class="faq-q-icon">+</span></button>
        <div class="faq-a"><div class="faq-a-inner">Ya. Trayek Wisata ini adalah alih fungsi layanan Angkutan Sekolah Gratis milik Dinas Perhubungan Kabupaten Tulungagung yang dioperasikan setiap Sabtu &amp; Minggu — tidak ada biaya tiket sama sekali.</div></div>
      </div>
      <div class="faq-item">
        <button class="faq-q" onclick="toggleFaq(this)" aria-expanded="false"><span>Apakah saya bisa memesan lebih dari 1 kali dalam sebulan?</span><span class="faq-q-icon">+</span></button>
        <div class="faq-a"><div class="faq-a-inner">Setiap NIK — baik pemilik akun maupun anggota keluarga yang didaftarkan — hanya bisa memperoleh 1 tiket setiap 4 minggu, dihitung dari tanggal pemesanan terakhir. Ini berlaku lintas akun untuk mencegah penyalahgunaan kuota.</div></div>
      </div>
      <div class="faq-item">
        <button class="faq-q" onclick="toggleFaq(this)" aria-expanded="false"><span>Apa saja yang perlu disiapkan sebelum memesan?</span><span class="faq-q-icon">+</span></button>
        <div class="faq-a"><div class="faq-a-inner">Akun Google, KTP (untuk verifikasi lewat kamera langsung — tidak bisa unggah dari galeri), dan nomor HP aktif. Anggota keluarga yang ikut juga perlu dicatat NIK-nya.</div></div>
      </div>
      <div class="faq-item">
        <button class="faq-q" onclick="toggleFaq(this)" aria-expanded="false"><span>Bagaimana jika kuota trayek pilihan saya sudah penuh?</span><span class="faq-q-icon">+</span></button>
        <div class="faq-a"><div class="faq-a-inner">Coba pilih trayek atau tanggal lain di jadwal 4 minggu ke depan — status kursi ditampilkan realtime (hijau: longgar, kuning: hampir penuh, merah: penuh). Anda juga bisa daftar "Beri Tahu Saya" supaya dihubungi kalau ada kursi kosong dari pembatalan.</div></div>
      </div>
      <div class="faq-item">
        <button class="faq-q" onclick="toggleFaq(this)" aria-expanded="false"><span>Bisakah saya membatalkan pemesanan?</span><span class="faq-q-icon">+</span></button>
        <div class="faq-a"><div class="faq-a-inner">Bisa, selama belum lewat tanggal keberangkatan dan belum check-in di titik keberangkatan. Buka halaman Akun → Pemesanan Saya untuk membatalkan.</div></div>
      </div>
      <div class="faq-item">
        <button class="faq-q" onclick="toggleFaq(this)" aria-expanded="false"><span>Apa yang perlu dibawa saat berangkat?</span><span class="faq-q-icon">+</span></button>
        <div class="faq-a"><div class="faq-a-inner">Tunjukkan e-tiket (QR code) yang muncul setelah memesan — bisa lewat screenshot atau dibuka langsung di halaman Akun → Pemesanan Saya. Datang 10-15 menit sebelum jam keberangkatan di titik yang tertera.</div></div>
      </div>
    </div>
  </div>
</section>

<section style="background:var(--paper);padding:0 0 100px;text-align:center">
  <a href="/trayek-wisata/jadwal" class="btn btn-solid">Sudah siap? Pesan Kursi →</a>
</section>
</main>

@include('partials.trayek-wisata.footer')

<script src="https://unpkg.com/lenis@1.1.13/dist/lenis.min.js"></script>
<script src="{{ asset_url('/assets/trayek-wisata/tw-core.js') }}"></script>
<script>twLoadAuth();</script>
</body>
</html>
