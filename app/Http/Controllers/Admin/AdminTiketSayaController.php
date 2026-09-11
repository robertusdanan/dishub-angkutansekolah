<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminRoleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Port dari admin/pages/tiket-saya.php — KHUSUS role 'pengguna'.
 */
class AdminTiketSayaController extends Controller
{
    public function __construct(protected AdminRoleService $roles)
    {
    }

    public function index(): View|RedirectResponse
    {
        if (!$this->roles->isPenggunaPublik()) {
            return redirect('/admin/');
        }

        return view('admin.tiket-saya', [
            'namaAkun' => session('admin_user', 'Pengguna'),
        ]);
    }
}
