<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Absensi Angkutan Sekolah</title>
  <link rel="canonical" href="{{ url('/absenrfid/bus') }}"/>
  <link rel="icon" href="/favicon.ico"/>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset_url('/assets/absen-rfid/bus.css') }}"/>
</head>
<body>

  <!-- ══ STANDBY SCREEN ══ -->
  <div id="screen-standby" class="screen active">
    <div class="standby-inner">
      <div class="logo-ring">
        <img src="/assets/dishub.png" alt="Logo Dishub"/>
      </div>
      <h1 class="standby-title">Absensi<br>Angkutan Sekolah Gratis</h1>
      <p class="standby-sub">Dinas Perhubungan Kabupaten Tulungagung</p>
      <div class="divider"></div>
      <div class="clock" id="clock">00:00:00</div>
      <div class="date-label" id="date-label">—</div>
      <div class="tap-hint">
        <span class="tap-icon">⬡</span>
        Tempelkan kartu untuk absen
      </div>

      <!-- ══ INPUT MANUAL NIK ══ -->
      <div class="manual-nik-wrap">
        <div class="manual-nik-label">atau ketik NIK manual</div>
        <div class="manual-nik-field-wrap">
          <input
            id="manual-nik-input"
            type="text"
            inputmode="numeric"
            pattern="[0-9]*"
            maxlength="16"
            placeholder="NIK 16 digit"
            autocomplete="off"
            spellcheck="false"
          />
          <div class="nik-char-count" id="nik-char-count">0 / 16</div>
        </div>
        <div class="nik-feedback" id="nik-feedback"></div>
      </div>

      <div id="offline-badge" class="offline-badge hidden">● Offline — data tersimpan lokal</div>

      <!-- ══ INDIKATOR KENDARAAN ══ -->
      <div id="vehicle-indicator" class="vehicle-indicator hidden">
        <div class="vehicle-plat" id="vehicle-plat">—</div>
        <div class="vehicle-divider"></div>
        <div class="vehicle-driver" id="vehicle-driver">—</div>
      </div>
    </div>
  </div>

  <!-- ══ RESULT SCREEN ══ -->
  <div id="screen-result" class="screen">
    <div class="result-inner">
      <div class="result-name" id="result-name">—</div>
      <div class="result-meta" id="result-meta">—</div>
      <div class="result-status" id="result-status">
        <div class="status-icon" id="status-icon">✓</div>
        <div class="status-text" id="status-text">Absen Berhasil</div>
      </div>
      <div class="result-shift" id="result-shift"></div>
      <div class="result-time" id="result-time"></div>
      <div class="progress-bar-wrap">
        <div class="progress-bar" id="progress-bar"></div>
      </div>
      <div class="result-hint">Layar akan kembali otomatis...</div>
    </div>
  </div>

  <!-- Hidden input menangkap ketikan HID reader -->
  <input id="rfid-input" type="text" autocomplete="off"
         style="position:fixed;opacity:0;width:1px;height:1px;left:-9999px;top:0"/>

  <script>
    window.DRIVER_ID     = {!! json_encode($driverId) !!};
    window.SB_URL        = {!! json_encode(config('services.supabase.url')) !!};
    window.SB_ANON       = {!! json_encode(config('services.supabase.anon_key')) !!};
    window.GPS_PROXY_URL = '/absenrfid/php/gps-write';
  </script>
  <script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>
  <script src="{{ asset_url('/assets/absen-rfid/bus.js') }}"></script>
</body>
</html>
