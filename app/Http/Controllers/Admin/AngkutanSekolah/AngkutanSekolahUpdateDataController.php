<?php

namespace App\Http\Controllers\Admin\AngkutanSekolah;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminRoleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

/**
 * Port dari admin/pages/angkutansekolah/updatedata/{datasekolah,datadomisili,
 * datatrayek,datamap,datadriver,registrasi}.php.
 */
class AngkutanSekolahUpdateDataController extends Controller
{
    public function __construct(protected AdminRoleService $roles)
    {
    }

    protected function guardMenu(string $menuId): ?RedirectResponse
    {
        Session::put('admin_module', 'angkutansekolah');

        return $this->roles->requireMenuAccess($menuId);
    }

    public function dataSekolah(): View|RedirectResponse
    {
        if ($r = $this->guardMenu('update_sekolah')) {
            return $r;
        }

        return view('admin.angkutansekolah.updatedata.datasekolah');
    }

    public function dataDomisili(): View|RedirectResponse
    {
        if ($r = $this->guardMenu('update_domisili')) {
            return $r;
        }

        return view('admin.angkutansekolah.updatedata.datadomisili');
    }

    public function dataTrayek(): View|RedirectResponse
    {
        if ($r = $this->guardMenu('update_trayek')) {
            return $r;
        }

        return view('admin.angkutansekolah.updatedata.datatrayek');
    }

    public function dataMap(): View|RedirectResponse
    {
        if ($r = $this->guardMenu('update_map')) {
            return $r;
        }

        return view('admin.angkutansekolah.updatedata.datamap');
    }

    public function dataDriver(): View|RedirectResponse
    {
        if ($r = $this->guardMenu('update_driver')) {
            return $r;
        }

        return view('admin.angkutansekolah.updatedata.datadriver');
    }

    public function registrasi(): View|RedirectResponse
    {
        if ($r = $this->guardMenu('update_siswa')) {
            return $r;
        }

        return view('admin.angkutansekolah.updatedata.registrasi');
    }

    public function absensiFoto(): View|RedirectResponse
    {
        if ($r = $this->guardMenu('tambah_foto')) {
            return $r;
        }

        return view('admin.angkutansekolah.updatedata.absensifoto', [
            'adminUser' => session('admin_user', 'admin'),
        ]);
    }
}
