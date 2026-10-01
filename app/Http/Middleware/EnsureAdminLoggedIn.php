<?php

namespace App\Http\Middleware;

use App\Services\Admin\AdminSessionManager;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

/**
 * Memastikan sesi admin aktif dan kredensial/status akun masih sah.
 * Jika akun dinonaktifkan, dihapus, atau password diubah dari perangkat lain,
 * sesi langsung di-flush dan ditolak.
 */
class EnsureAdminLoggedIn
{
    public function __construct(
        protected AdminSessionManager $sessionManager
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (!Session::get('admin_logged_in') || !$this->sessionManager->isValidAdminSession()) {
            if (Session::get('admin_logged_in')) {
                Session::flush();
                $request->session()->regenerate(true);
            }

            if ($request->expectsJson() || $request->is('admin/api/*')) {
                return response()->json([
                    'error' => 'Sesi Anda telah berakhir atau akun telah dinonaktifkan/diubah. Silakan login kembali.',
                    'session_expired' => true,
                ], 401);
            }

            return redirect('/admin/login');
        }

        return $next($request);
    }
}
