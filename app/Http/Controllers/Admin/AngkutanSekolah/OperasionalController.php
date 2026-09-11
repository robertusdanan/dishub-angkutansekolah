<?php

namespace App\Http\Controllers\Admin\AngkutanSekolah;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminRoleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

/**
 * Port dari admin/pages/angkutansekolah/operasional.php.
 */
class OperasionalController extends Controller
{
    public function __construct(protected AdminRoleService $roles)
    {
    }

    public function index(): View|RedirectResponse
    {
        Session::put('admin_module', 'angkutansekolah');
        if ($r = $this->roles->requireNonGuest()) {
            return $r;
        }

        return view('admin.angkutansekolah.operasional');
    }
}
