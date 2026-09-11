<?php

namespace App\Http\Controllers\Admin\AngkutanSekolah;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminRoleService;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

/**
 * Port dari admin/pages/angkutansekolah/dataabsensi.php.
 *
 * Mode "hari ini" read-only berlaku untuk role guest (lama) MAUPUN
 * pengguna (akun publik Google) — sejak tombol Tamu dihapus, keduanya
 * digabung jadi satu perilaku (lihat komentar asli di kode lama).
 */
class DataAbsensiController extends Controller
{
    public function __construct(protected AdminRoleService $roles)
    {
    }

    public function index(): View
    {
        Session::put('admin_module', 'angkutansekolah');

        return view('admin.angkutansekolah.dataabsensi', [
            'isGuest' => $this->roles->isGuest() || $this->roles->isPenggunaPublik(),
        ]);
    }
}
