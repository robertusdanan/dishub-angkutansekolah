<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminRoleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Port dari admin/pages/manajemen-role.php.
 *
 * Anti-eskalasi izin: checkbox izin (menu & aksi akun/manajemen_role/
 * listlink/pengaturan) yang TIDAK dimiliki akun yang sedang login TIDAK
 * dirender sama sekali di form - dihitung di sini persis seperti fungsi
 * _iHaveX() di kode lama. Backend (AdminRoleApiController::clampPermissionsToOwn)
 * juga menegakkan ini sebagai defense in depth, jadi bukan cuma proteksi
 * tampilan.
 */
class AdminManajemenRoleController extends Controller
{
    public function __construct(protected AdminRoleService $roles)
    {
    }

    public function index(): View|RedirectResponse
    {
        if ($r = $this->roles->requireNonGuest()) {
            return $r;
        }
        if ($r = $this->roles->requireRoleManagementAccess()) {
            return $r;
        }

        $myPerms = $this->roles->currentPermissions();
        $myMenus = (array) ($myPerms['menus'] ?? []);
        $iHaveAllMenu = $this->roles->isSuperAdmin() || in_array('*', $myMenus, true);
        $myAkun = (array) ($myPerms['akun'] ?? []);
        $myRoleMgmt = (array) ($myPerms['manajemen_role'] ?? []);
        $myListlink = (array) ($myPerms['listlink'] ?? []);
        $myPengaturan = (array) ($myPerms['pengaturan'] ?? []);

        $iHaveMenu = fn (string $id): bool => $iHaveAllMenu || in_array($id, $myMenus, true);
        $iHaveAkun = fn (string $a): bool => $this->roles->isSuperAdmin() || !empty($myAkun[$a]);
        $iHaveRoleMgmt = fn (string $a): bool => $this->roles->isSuperAdmin() || !empty($myRoleMgmt[$a]);
        $iHaveListlink = fn (): bool => $this->roles->isSuperAdmin() || !empty($myListlink['access']);
        $iHavePengaturan = fn (string $a): bool => $this->roles->isSuperAdmin() || !empty($myPengaturan[$a]);

        $menuGroups = [];
        foreach (config('admin_menu_catalog.grup', []) as $groupName => $items) {
            $itemsIHave = array_filter($items, fn ($label, $id) => $iHaveMenu($id), ARRAY_FILTER_USE_BOTH);
            if (!empty($itemsIHave)) {
                $menuGroups[$groupName] = $itemsIHave;
            }
        }

        return view('admin.manajemen-role', [
            'canCreate' => $this->roles->isSuperAdmin() || $this->roles->hasPermission('manajemen_role', 'create'),
            'canEdit' => $this->roles->isSuperAdmin() || $this->roles->hasPermission('manajemen_role', 'edit'),
            'canDelete' => $this->roles->isSuperAdmin() || $this->roles->hasPermission('manajemen_role', 'delete'),
            'isSuperAdmin' => $this->roles->isSuperAdmin(),
            'iHaveAllMenu' => $iHaveAllMenu,
            'menuGroups' => $menuGroups,
            'akunActions' => array_filter(['view' => $iHaveAkun('view'), 'create' => $iHaveAkun('create'), 'edit' => $iHaveAkun('edit'), 'deactivate' => $iHaveAkun('deactivate'), 'delete' => $iHaveAkun('delete')]),
            'roleMgmtActions' => array_filter(['view' => $iHaveRoleMgmt('view'), 'create' => $iHaveRoleMgmt('create'), 'edit' => $iHaveRoleMgmt('edit'), 'delete' => $iHaveRoleMgmt('delete')]),
            'iHaveListlink' => $iHaveListlink(),
            'iHavePengaturanCleanup' => $iHavePengaturan('cleanup'),
        ]);
    }
}
