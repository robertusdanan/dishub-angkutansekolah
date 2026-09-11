<?php

namespace App\Http\Controllers\Admin\TrayekWisata;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminRoleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

/**
 * Port dari admin/pages/trayekwisata/{dashboard,armada,verifikasi,pemesanan,titik,planner}.php.
 */
class TrayekWisataAdminController extends Controller
{
    public function __construct(protected AdminRoleService $roles)
    {
    }

    protected function guard(string $menuId): ?RedirectResponse
    {
        Session::put('admin_module', 'trayekwisata');

        return $this->roles->requireMenuAccess($menuId);
    }

    public function dashboard(): View|RedirectResponse
    {
        if ($r = $this->guard('trayekwisata_dashboard')) {
            return $r;
        }

        return view('admin.trayekwisata.dashboard');
    }

    public function armada(): View|RedirectResponse
    {
        if ($r = $this->guard('trayekwisata_armada')) {
            return $r;
        }

        return view('admin.trayekwisata.armada');
    }

    public function verifikasi(): View|RedirectResponse
    {
        if ($r = $this->guard('trayekwisata_verifikasi')) {
            return $r;
        }

        return view('admin.trayekwisata.verifikasi');
    }

    public function pemesanan(): View|RedirectResponse
    {
        if ($r = $this->guard('trayekwisata_pemesanan')) {
            return $r;
        }

        return view('admin.trayekwisata.pemesanan');
    }

    public function titik(): View|RedirectResponse
    {
        if ($r = $this->guard('trayekwisata_titik')) {
            return $r;
        }

        return view('admin.trayekwisata.titik');
    }

    public function planner(): View|RedirectResponse
    {
        if ($r = $this->guard('trayekwisata_planner')) {
            return $r;
        }

        return view('admin.trayekwisata.planner');
    }
}
