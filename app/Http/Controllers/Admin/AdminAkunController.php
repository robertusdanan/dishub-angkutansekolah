<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminRoleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Port dari admin/pages/akun.php.
 */
class AdminAkunController extends Controller
{
    public function __construct(protected AdminRoleService $roles)
    {
    }

    public function index(): View|RedirectResponse
    {
        if ($r = $this->roles->requireNonGuest()) {
            return $r;
        }
        if ($r = $this->roles->requireAkunAccess()) {
            return $r;
        }

        return view('admin.akun', [
            'canCreate' => $this->roles->isSuperAdmin() || $this->roles->hasPermission('akun', 'create'),
            'canEdit' => $this->roles->isSuperAdmin() || $this->roles->hasPermission('akun', 'edit'),
            'canDeactivate' => $this->roles->isSuperAdmin() || $this->roles->hasPermission('akun', 'deactivate'),
            'canDelete' => $this->roles->isSuperAdmin() || $this->roles->hasPermission('akun', 'delete'),
            'myAccountId' => session('admin_account_id'),
            'isSuperAdmin' => $this->roles->isSuperAdmin(),
            'myLevel' => $this->roles->currentRoleLevel(),
        ]);
    }
}
