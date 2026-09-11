<?php

namespace App\Http\Controllers\Asdp;

use App\Http\Controllers\Controller;
use App\Services\MenuLayanan\MenuLayananService;
use Illuminate\View\View;

/**
 * Port dari pages/asdp/index.php.
 *
 * CATATAN MIGRASI: enforce_cfg_sync() (kill-switch remote) SENGAJA DIHAPUS,
 * sama seperti modul lain. File pages/asdp/style.css di kode lama TIDAK
 * ikut dipindahkan — sudah dicek, file itu tidak di-<link> di manapun
 * (dead code / sisa desain lama sebelum CSS-nya dipindah inline).
 */
class AsdpController extends Controller
{
    public function __construct(protected MenuLayananService $menuLayanan)
    {
    }

    /** GET /asdp/ — setara pages/asdp/index.php */
    public function index(): View
    {
        $this->menuLayanan->requireAktif('asdp');

        return $this->noCache(view('asdp.index'));
    }

    protected function noCache(View $view)
    {
        return response($view)->withHeaders([
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
}
