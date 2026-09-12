<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

/**
 * Placeholder INTERNAL untuk modul yang route-nya sudah disiapkan tapi
 * logic-nya belum dipindah dari PHP native (menyusul di tahap migrasi
 * berikutnya). BUKAN pengganti halaman publik "Segera Hadir" -
 * lihat MenuLayananService::requireAktif() / errors.menu-nonaktif untuk itu.
 */
class ComingSoonController extends Controller
{
    public function show(string $modul): View
    {
        return view('dev.coming-soon', ['modul' => $modul]);
    }
}
