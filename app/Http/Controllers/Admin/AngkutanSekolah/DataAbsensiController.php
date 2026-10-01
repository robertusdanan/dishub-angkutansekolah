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
 * pengguna (akun publik Google) - sejak tombol Tamu dihapus, keduanya
 * digabung jadi satu perilaku (lihat komentar asli di kode lama).
 */
class DataAbsensiController extends Controller
{
    public function __construct(protected AdminRoleService $roles)
    {
    }

    public function index(): View|\Illuminate\Http\RedirectResponse
    {
        Session::put('admin_module', 'angkutansekolah');

        if (!$this->roles->isGuest() && !$this->roles->isPenggunaPublik()) {
            if ($r = $this->roles->requireMenuAccess('data_absensi')) {
                return $r;
            }
        }

        return view('admin.angkutansekolah.dataabsensi', [
            'isGuest' => $this->roles->isGuest() || $this->roles->isPenggunaPublik(),
        ]);
    }
}
