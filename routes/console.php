<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// REKOMENDASI KEAMANAN & OPERASIONAL: cleanup data absensi (>6 bulan)
// dan pemesanan (>2 tahun) dijalankan otomatis bulanan.
Schedule::command('angkutan:cleanup')->monthly();
