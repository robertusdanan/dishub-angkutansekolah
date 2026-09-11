<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminRoleService;
use App\Services\AkunPublik\AkunPublikService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Port dari admin/pages/profil-saya.php.
 *
 * Halaman ini secara fungsional IDENTIK dengan /akun/saya (form data diri,
 * dokumen, keluarga) — cuma dibungkus shell admin (sidebar) alih-alih
 * header/footer publik, dan datanya via endpoint /akun/api/* yang SAMA
 * (satu sumber data, tidak digandakan).
 */
class AdminProfilSayaController extends Controller
{
    public function __construct(
        protected AdminRoleService $roles,
        protected AkunPublikService $akun,
    ) {
    }

    public function index(Request $request): View|RedirectResponse
    {
        if (!$this->roles->isPenggunaPublik()) {
            return redirect('/admin/');
        }
        if (!$this->akun->isLogin()) {
            return redirect('/akun/masuk?next='.rawurlencode('/admin/profil-saya'));
        }

        $next = $request->query('next', '');
        if (!is_string($next) || !str_starts_with($next, '/') || str_starts_with($next, '//')) {
            $next = '';
        }

        return view('admin.profil-saya', [
            'next' => $next,
            'welcome' => $request->boolean('welcome'),
            'namaAkun' => session('akun_publik_nama', session('admin_user', 'Pengguna')),
            'emailAkun' => session('akun_publik_email', ''),
        ]);
    }
}
