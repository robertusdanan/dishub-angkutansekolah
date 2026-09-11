<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminRoleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Port dari admin/settings.php (requireNonGuest() sudah ditegakkan lewat
 * middleware admin.auth + pengecekan di route, bukan di sini).
 */
class SettingsController extends Controller
{
    public function __construct(protected AdminRoleService $roles)
    {
    }

    public function index(): View|RedirectResponse
    {
        if ($r = $this->roles->requireNonGuest()) {
            return $r;
        }

        return view('admin.settings', [
            'isSuperAdmin' => $this->roles->isSuperAdmin(),
            'hasCleanupPermission' => $this->roles->hasPermission('pengaturan', 'cleanup'),
            'cleanupToken' => (string) config('services.cleanup_token', ''),
        ]);
    }
}
