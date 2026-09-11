{{--
    Port dari pages/{absenrfid,absenqrcode}/error.php (identik byte-per-byte
    di kode lama). Props: $errorCode (int), $backUrl (string)
--}}
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Error {{ $errorCode }} - Absen Angkutan Sekolah Gratis</title>
<link rel="canonical" href="{{ url($backUrl) }}/error"/>
<link rel="icon" href="/favicon.ico"/>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Montserrat:wght@400;500;600&display=swap" rel="stylesheet">
<style>
body{margin:0;font-family:'Montserrat', sans-serif;background:linear-gradient(135deg,#f8f6f2,#ece8df);display:flex;align-items:center;justify-content:center;height:100vh;}
.modal{background:#fff;padding:50px 40px;border-radius:18px;box-shadow:0 20px 60px rgba(0,0,0,0.15);text-align:center;max-width:500px;width:90%;animation:fadeIn .6s ease;}
h1{font-family:'Cormorant Garamond', serif;font-size:72px;margin:0;color:#b1975a;}
h2{margin:10px 0 20px;font-weight:600;}
p{color:#666;font-size:15px;}
.btn{margin-top:25px;display:inline-block;padding:12px 28px;background:#b1975a;color:#fff;text-decoration:none;border-radius:30px;transition:.3s;font-weight:500;}
.btn:hover{background:#8e7745;}
.countdown{margin-top:15px;font-size:14px;color:#999;}
@keyframes fadeIn{from{opacity:0;transform:translateY(20px);}to{opacity:1;transform:translateY(0);}}
</style>
</head>
<body>
<div class="modal">
    <h1>{{ $errorCode }}</h1>
    <h2>Terjadi Kesalahan</h2>
    <p>Terjadi gangguan sistem.<br>Anda akan diarahkan kembali ke halaman pemilihan driver.</p>
    <a href="{{ $backUrl }}" class="btn">Halaman Pemilihan Driver</a>
    <div class="countdown">Redirect otomatis dalam <span id="timer">10</span> detik...</div>
</div>
<script>
let timeLeft = 10;
let timer = document.getElementById("timer");
let countdown = setInterval(function(){
    timeLeft--;
    timer.textContent = timeLeft;
    if(timeLeft <= 0){
        clearInterval(countdown);
        window.location.href = {!! json_encode($backUrl) !!};
    }
},1000);
</script>
</body>
</html>
