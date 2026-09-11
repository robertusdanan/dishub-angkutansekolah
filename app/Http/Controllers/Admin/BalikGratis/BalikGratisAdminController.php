<?php

namespace App\Http\Controllers\Admin\BalikGratis;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminRoleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

/**
 * Port dari admin/pages/balikgratis/{dashboard,pemesanan,pengaturan,verifikasi}.php.
 */
class BalikGratisAdminController extends Controller
{
    public function __construct(protected AdminRoleService $roles)
    {
    }

    protected function guard(string $menuId): ?RedirectResponse
    {
        Session::put('admin_module', 'balikgratis');

        return $this->roles->requireMenuAccess($menuId);
    }

    public function dashboard(): View|RedirectResponse
    {
        if ($r = $this->guard('balikgratis_dashboard')) {
            return $r;
        }

        return view('admin.balikgratis.dashboard');
    }

    public function pemesanan(): View|RedirectResponse
    {
        if ($r = $this->guard('balikgratis_pemesanan')) {
            return $r;
        }

        return view('admin.balikgratis.pemesanan');
    }

    public function pengaturan(): View|RedirectResponse
    {
        if ($r = $this->guard('balikgratis_pengaturan')) {
            return $r;
        }

        return view('admin.balikgratis.pengaturan');
    }

    public function verifikasi(): View|RedirectResponse
    {
        if ($r = $this->guard('balikgratis_verifikasi')) {
            return $r;
        }

        return view('admin.balikgratis.verifikasi');
    }
}
