<?php

namespace App\Http\Controllers\Admin\Asdp;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminRoleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

/**
 * Port dari admin/pages/asdp/daftarlist.php.
 */
class AsdpDaftarListController extends Controller
{
    public function __construct(protected AdminRoleService $roles)
    {
    }

    public function index(): View|RedirectResponse
    {
        Session::put('admin_module', 'asdp');
        if ($r = $this->roles->requireMenuAccess('asdp_daftar')) {
            return $r;
        }

        return view('admin.asdp.daftar-lokasi');
    }
}
