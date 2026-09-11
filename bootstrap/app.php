<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        // "health" sengaja tidak dipakai — nanti "/" ditangani penuh oleh HomeController.
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Session, cookie, CSRF dsb. sudah otomatis aktif untuk grup "web"
        // bawaan Laravel — setara dengan session_start() manual di kode lama.

        $middleware->alias([
            'admin.auth' => \App\Http\Middleware\EnsureAdminLoggedIn::class,
        ]);

        // REKOMENDASI KEAMANAN: header standar untuk SEMUA response.
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);

        // PENGECUALIAN CSRF yang DISENGAJA: endpoint absensi RFID/QR Code
        // dipanggil dari perangkat kiosk PUBLIK tanpa login (tempel kartu,
        // update GPS tiap ~10 detik), yang halamannya bisa terbuka
        // berjam-jam tanpa reload. Proteksi CSRF berbasis session-token
        // tidak cocok untuk pola ini (beda dengan akun/saya yang berbasis
        // sesi user login) — kode PHP lama juga tidak pernah punya proteksi
        // CSRF di endpoint-endpoint ini. Endpoint publik lain yang aksinya
        // terikat ke SESI LOGIN (akun/saya, trayek-wisata/api, balikgratis/api)
        // TETAP diproteksi CSRF seperti biasa.
        $middleware->validateCsrfTokens(except: [
            'absenrfid/php/*',
            'absenqrcode/php/*',
            'api/cache-invalidate',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Supaya limit percobaan login tampil sebagai pesan ramah di
        // form, bukan halaman error 429 generik.
        $exceptions->render(function (\Illuminate\Http\Exceptions\ThrottleRequestsException $e, \Illuminate\Http\Request $request) {
            if (!$request->expectsJson()) {
                return back()->withErrors([
                    'throttle' => 'Terlalu banyak percobaan login. Coba lagi dalam beberapa saat.',
                ])->withInput($request->except('password'));
            }
        });
    })->create();
