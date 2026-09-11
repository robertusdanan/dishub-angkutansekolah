<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

/**
 * Port dari bagian "wajib login" di admin/auth.php — dipasang di semua
 * route /admin/* KECUALI /admin/login (lihat routes/web.php).
 */
class EnsureAdminLoggedIn
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Session::get('admin_logged_in')) {
            return redirect('/admin/login');
        }

        return $next($request);
    }
}
