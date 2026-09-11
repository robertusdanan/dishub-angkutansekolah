{{--
    Port dari core/absen_form_partial.php — dipakai bersama oleh
    /absenqrcode/pages/form dan /absenrfid/pages/form (sebelumnya dua file
    identik byte-per-byte, sekarang satu sumber Blade).
--}}
<div class="form-container"></div>

<form id="absenForm">
  <!-- Nama -->
  <label></label>
  <input type="text" name="nama" id="namaInput" required readonly><br>

  <!-- Jenis Kelamin -->
  <label for="jenisKelamin" class="block font-medium mb-1">Jenis Kelamin:</label>
  <select id="jenisKelamin" name="jenis_kelamin" required class="w-full border border-gray-300 rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
    <option value="">Pilih Jenis Kelamin</option>
    <option value="Laki-laki">Laki-laki</option>
    <option value="Perempuan">Perempuan</option>
  </select><br>

  <!-- Domisili (autocomplete) -->
  <label>Domisili (Kecamatan):</label>
  <div class="autocomplete-wrapper" style="position: relative;">
    <input type="text" id="domisiliInput" name="domisili" autocomplete="off" required />
    <div id="domisiliDropdown" class="autocomplete-items"></div>
  </div><br>

  <!-- Sekolah (autocomplete) -->
  <label>Nama Sekolah:</label>
  <div class="autocomplete-wrapper" style="position: relative;">
    <input type="text" name="sekolah" id="sekolahInput" autocomplete="off" required />
    <div id="sekolahDropdown" class="autocomplete-items"></div>
  </div><br>

  <!-- Transportasi -->
  <label>Transportasi:</label>
  <select id="transportasi" disabled>
    <option value="">Memuat...</option>
    <option value="BUS">BUS</option>
    <option value="MPU">MPU</option>
  </select>
  <input type="hidden" name="transportasi" id="transportasiHidden" required><br>

  <!-- Trayek -->
  <label>Trayek:</label>
  <select id="trayek" disabled>
    <option value="">Memuat</option>
  </select>
  <input type="hidden" name="trayek" id="trayekHidden" required><br>

  <!-- Plat Driver -->
  <label></label>
  <select name="plat_driver" id="platDriver" style="display:none;">
    <option value="">Pilih Trayek Dulu</option>
  </select>

  <div id="info-angkutan"></div><br>

  <!-- Submit -->
  <button type="submit" id="submitButton">
    <span id="buttonText">Kirim Absen</span>
  </button>
</form>
