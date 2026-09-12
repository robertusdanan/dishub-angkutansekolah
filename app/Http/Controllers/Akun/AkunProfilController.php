<?php

namespace App\Http\Controllers\Akun;

use App\Http\Controllers\Controller;
use App\Services\AkunPublik\AkunPublikService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Port dari pages/akun/saya.php (bagian render halaman - logic form/JS-nya
 * tetap di client seperti kode lama, cuma endpoint API-nya pindah ke
 * AkunProfilApiController).
 */
class AkunProfilController extends Controller
{
    public function __construct(protected AkunPublikService $akun)
    {
    }

    public function index(Request $request): View|RedirectResponse
    {
        $next = $request->query('next', '');
        if (!is_string($next) || !str_starts_with($next, '/') || str_starts_with($next, '//')) {
            $next = '';
        }

        if (!$this->akun->isLogin()) {
            $loginTarget = $next !== '' ? $next : '/akun/saya';

            return redirect('/akun/masuk?'.http_build_query(['next' => $loginTarget]));
        }

        return view('akun.saya', [
            'next' => $next,
            'welcome' => $request->boolean('welcome'),
        ]);
    }
}
