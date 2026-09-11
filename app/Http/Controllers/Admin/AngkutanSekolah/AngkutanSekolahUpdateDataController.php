<?php

namespace App\Http\Controllers\Admin\AngkutanSekolah;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminRoleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

/**
 * Port dari admin/pages/angkutansekolah/updatedata/{datasekolah,datadomisili,
 * datatrayek,datamap,datadriver,registrasi,rfidwriter}.php.
 */
class AngkutanSekolahUpdateDataController extends Controller
{
    public function __construct(protected AdminRoleService $roles)
    {
    }

    protected function guardSuperAdmin(): ?RedirectResponse
    {
        Session::put('admin_module', 'angkutansekolah');

        return $this->roles->requireSuperAdmin();
    }

    protected function guardAbsensiAccess(): ?RedirectResponse
    {
        Session::put('admin_module', 'angkutansekolah');

        return $this->roles->requireAbsensiAccess();
    }

    public function dataSekolah(): View|RedirectResponse
    {
        if ($r = $this->guardSuperAdmin()) {
            return $r;
        }

        return view('admin.angkutansekolah.updatedata.datasekolah');
    }

    public function dataDomisili(): View|RedirectResponse
    {
        if ($r = $this->guardSuperAdmin()) {
            return $r;
        }

        return view('admin.angkutansekolah.updatedata.datadomisili');
    }

    public function dataTrayek(): View|RedirectResponse
    {
        if ($r = $this->guardSuperAdmin()) {
            return $r;
        }

        return view('admin.angkutansekolah.updatedata.datatrayek');
    }

    public function dataMap(): View|RedirectResponse
    {
        if ($r = $this->guardSuperAdmin()) {
            return $r;
        }

        return view('admin.angkutansekolah.updatedata.datamap');
    }

    public function dataDriver(): View|RedirectResponse
    {
        if ($r = $this->guardSuperAdmin()) {
            return $r;
        }

        return view('admin.angkutansekolah.updatedata.datadriver');
    }

    public function registrasi(): View|RedirectResponse
    {
        if ($r = $this->guardAbsensiAccess()) {
            return $r;
        }

        return view('admin.angkutansekolah.updatedata.registrasi');
    }

    public function rfidWriter(): View|RedirectResponse
    {
        if ($r = $this->guardAbsensiAccess()) {
            return $r;
        }

        return view('admin.angkutansekolah.updatedata.rfidwriter');
    }

    public function absensiFoto(): View|RedirectResponse
    {
        if ($r = $this->guardAbsensiAccess()) {
            return $r;
        }

        return view('admin.angkutansekolah.updatedata.absensifoto', [
            'adminUser' => session('admin_user', 'admin'),
        ]);
    }
}
