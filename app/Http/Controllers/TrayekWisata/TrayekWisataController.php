<?php

namespace App\Http\Controllers\TrayekWisata;

use App\Http\Controllers\Controller;
use App\Services\MenuLayanan\MenuLayananService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Port dari pages/trayekwisata/{index,destinasi,faq,jadwal,rute,survei}.php.
 *
 * CATATAN MIGRASI: require_menu_layanan_aktif() dipertahankan (bukan
 * kill-switch — ini toggle admin yang sah), tapi tidak ada enforce_cfg_sync()
 * di halaman-halaman modul ini pada kode lama (beda dari ASDP/rute-sekolah),
 * jadi tidak ada yang perlu dihapus di sini.
 */
class TrayekWisataController extends Controller
{
    public function __construct(protected MenuLayananService $menuLayanan)
    {
    }

    public function index(): View
    {
        $this->menuLayanan->requireAktif('trayekwisata');

        return view('trayek-wisata.index');
    }

    public function destinasi(): View
    {
        $this->menuLayanan->requireAktif('trayekwisata');

        return view('trayek-wisata.destinasi');
    }

    public function faq(): View
    {
        $this->menuLayanan->requireAktif('trayekwisata');

        return view('trayek-wisata.faq');
    }

    public function jadwal(): View
    {
        $this->menuLayanan->requireAktif('trayekwisata');

        return view('trayek-wisata.jadwal');
    }

    public function rute(): View
    {
        $this->menuLayanan->requireAktif('trayekwisata');

        return view('trayek-wisata.rute');
    }

    public function survei(Request $request): View
    {
        $this->menuLayanan->requireAktif('trayekwisata');

        $pemesananId = preg_match('/^[0-9a-fA-F-]{36}$/', (string) $request->query('pemesanan_id', ''))
            ? $request->query('pemesanan_id')
            : '';

        return view('trayek-wisata.survei', ['pemesananId' => $pemesananId]);
    }
}
