<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"/>
<meta name="csrf-token" content="{{ csrf_token() }}"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Survei Kepuasan - Trayek Wisata Gratis</title>
<link rel="icon" href="/favicon.ico"/>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,300..700;1,9..144,400..600&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset_url('/assets/trayek-wisata/style.css') }}"/>
<style>
  body{ background:var(--paper-2); display:flex; align-items:center; justify-content:center; min-height:100vh; padding:24px; }
  .survei-card{ background:#fff; border-radius:var(--r-xl); max-width:480px; width:100%; padding:40px 34px; box-shadow:var(--shadow-lg); text-align:center; }
  .survei-icon{ width:56px; height:56px; border-radius:16px; background:var(--forest-tint); color:var(--forest); display:flex; align-items:center; justify-content:center; margin:0 auto 20px; }
  .survei-title{ font-family:var(--f-display); font-size:24px; color:var(--ink); }
  .survei-sub{ font-size:13.5px; color:var(--ink-3); margin-top:8px; }
  .survei-stars{ display:flex; justify-content:center; gap:8px; margin:28px 0 8px; }
  .survei-star{ font-size:36px; cursor:pointer; color:var(--line); transition:.15s; background:none; border:none; }
  .survei-star.on{ color:var(--sunset); }
  .survei-star-label{ font-size:12.5px; color:var(--ink-3); min-height:18px; margin-bottom:20px; }
  .survei-textarea{ width:100%; box-sizing:border-box; padding:12px 14px; border:1.5px solid var(--line); border-radius:12px; font-family:var(--f-body); font-size:13.5px; min-height:90px; resize:vertical; }
  .survei-done{ display:none; }
  .survei-done.show{ display:block; }
  .survei-form.hide{ display:none; }
</style>
</head>
<body>
<div class="survei-card">
  <div class="survei-form" id="surveiForm">
    <div class="survei-icon"><svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 21s7-4.35 7-11a7 7 0 1 0-14 0c0 6.65 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/></svg></div>
    <div class="survei-title">Bagaimana pengalaman Anda?</div>
    <p class="survei-sub">Terima kasih sudah ikut Trayek Wisata Gratis Dishub Kabupaten Tulungagung - masukan Anda membantu kami memperbaiki layanan.</p>

    <div class="survei-stars" id="surveiStars">
      <button class="survei-star" data-v="1">★</button>
      <button class="survei-star" data-v="2">★</button>
      <button class="survei-star" data-v="3">★</button>
      <button class="survei-star" data-v="4">★</button>
      <button class="survei-star" data-v="5">★</button>
    </div>
    <div class="survei-star-label" id="surveiStarLabel">Ketuk bintang untuk menilai</div>

    <textarea class="survei-textarea" id="surveiKomentar" placeholder="Ceritakan pengalaman Anda (opsional)..."></textarea>
    <button class="btn btn-solid" style="width:100%;justify-content:center;margin-top:16px" onclick="kirimSurvei()" id="btnKirimSurvei">Kirim Masukan</button>
  </div>

  <div class="survei-done" id="surveiDone">
    <div class="survei-icon" style="background:#dcfce7;color:#15803d">✓</div>
    <div class="survei-title">Terima kasih!</div>
    <p class="survei-sub">Masukan Anda sudah kami terima. Sampai jumpa di trayek wisata berikutnya 🚌</p>
  </div>
</div>

<script>
let rating = 0;
const pemesananId = {!! json_encode($pemesananId) !!};
const labels = { 1:'Sangat kurang', 2:'Kurang', 3:'Cukup', 4:'Baik', 5:'Sangat baik' };

document.querySelectorAll('.survei-star').forEach(btn => {
  btn.addEventListener('click', () => {
    rating = parseInt(btn.dataset.v);
    document.querySelectorAll('.survei-star').forEach(s => s.classList.toggle('on', parseInt(s.dataset.v) <= rating));
    document.getElementById('surveiStarLabel').textContent = labels[rating];
  });
});

async function kirimSurvei() {
  if (!rating) { document.getElementById('surveiStarLabel').textContent = 'Pilih rating dulu ya 🙂'; document.getElementById('surveiStarLabel').style.color = '#C23434'; return; }
  const btn = document.getElementById('btnKirimSurvei');
  btn.disabled = true; btn.textContent = 'Mengirim...';
  try {
    const res = await fetch('/trayek-wisata/api/survei', {
      method: 'POST', headers: {'Content-Type':'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content},
      body: JSON.stringify({ rating, komentar: document.getElementById('surveiKomentar').value.trim(), pemesanan_id: pemesananId }),
    });
    if (!res.ok) throw new Error((await res.json()).error || 'Gagal mengirim');
    document.getElementById('surveiForm').classList.add('hide');
    document.getElementById('surveiDone').classList.add('show');
  } catch (e) {
    btn.disabled = false; btn.textContent = 'Kirim Masukan';
    alert('Gagal mengirim: ' + e.message);
  }
}
</script>
</body>
</html>
