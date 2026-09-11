<?php

namespace App\Http\Controllers\BalikGratis;

use App\Http\Controllers\Controller;
use App\Services\BalikGratis\BalikGratisService;
use App\Services\MenuLayanan\MenuLayananService;
use Illuminate\View\View;

/**
 * Port dari pages/balikgratis/index.php.
 */
class BalikGratisController extends Controller
{
    public function __construct(
        protected MenuLayananService $menuLayanan,
        protected BalikGratisService $bg,
    ) {
    }

    public function index(): View
    {
        $this->menuLayanan->requireAktif('balikgratis');

        $tahun = $this->bg->tahunIni();
        $pengaturan = $this->bg->pengaturanTahun($tahun);
        $buka = ($pengaturan['status'] ?? 'tutup') === 'buka';
        $kuota = (int) ($pengaturan['kuota'] ?? 0);
        $terisi = $buka ? $this->bg->terisiTahun($tahun) : 0;
        $sisa = max(0, $kuota - $terisi);

        return view('balik-gratis.index', [
            'bgTahun' => $tahun,
            'bgBuka' => $buka,
            'bgKuota' => $kuota,
            'bgTerisi' => $terisi,
            'bgSisa' => $sisa,
        ]);
    }
}
