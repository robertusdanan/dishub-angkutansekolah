<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // SupabaseClient, MenuLayananService, SiteCreditService, dan
        // GoogleOAuthService semuanya di-resolve otomatis oleh service
        // container (constructor tanpa dependensi eksternal), jadi tidak
        // perlu di-bind manual di sini.
    }

    public function boot(): void
    {
        // REKOMENDASI KEAMANAN: kode PHP lama cuma sleep(1) tiap login
        // gagal - masih memungkinkan brute-force ~60 percobaan/menit.
        // Dibatasi jadi 5 percobaan/menit per kombinasi IP + username.
        RateLimiter::for('admin-login', function ($request) {
            $key = strtolower((string) $request->input('username', '')).'|'.$request->ip();
            return Limit::perMinute(5)->by($key);
        });

        RateLimiter::for('listlink-login', function ($request) {
            $key = strtolower((string) $request->input('username', '')).'|'.$request->ip();
            return Limit::perMinute(5)->by($key);
        });
    }
}
