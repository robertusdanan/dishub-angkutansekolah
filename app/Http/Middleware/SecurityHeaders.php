<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * REKOMENDASI KEAMANAN — TIDAK ADA di kode PHP asli. Header standar untuk
 * mengurangi risiko clickjacking, MIME-sniffing, kebocoran Referer.
 *
 * CSP SENGAJA longgar (bukan strict/nonce) karena banyak halaman pakai
 * inline <script> untuk data dari server (window.SB_URL dst.) — CSP ketat
 * butuh refactor besar. Ini titik awal aman, bisa diperketat bertahap.
 */
class SecurityHeaders
{
    /**
     * Domain pihak ketiga yang boleh dipanggil lewat fetch/XHR/WebSocket dari
     * browser. Sumber map (.js.map) dari CDN juga lewat sini — tanpa ini DevTools
     * memblokirnya dan console penuh error CSP.
     */
    protected const CONNECT_EXTRA = [
        'https://unpkg.com',
        'https://cdn.jsdelivr.net',
        'https://cdnjs.cloudflare.com',
        'https://accounts.google.com',
        // RFID Bridge agent lokal (halaman update-data RFID menulis ke PC admin)
        'http://localhost:7777',
        'ws://localhost:7777',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(self), camera=(self), microphone=()');

        if ($request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // Supabase REST + Realtime (wss) — domain diambil dari config supaya
        // pindah project Supabase tidak perlu sentuh middleware ini.
        $sbHost = parse_url((string) config('services.supabase.url'), PHP_URL_HOST);
        $connect = ["'self'"];
        if ($sbHost) {
            $connect[] = 'https://'.$sbHost;
            $connect[] = 'wss://'.$sbHost;
        }
        $connect = array_merge($connect, self::CONNECT_EXTRA);

        $response->headers->set(
            'Content-Security-Policy',
            "frame-ancestors 'self'; "
            ."object-src 'none'; "
            ."base-uri 'self'; "
            ."img-src 'self' data: blob: https:; "
            ."media-src 'self' blob: https:; "
            ."style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://unpkg.com; "
            ."font-src 'self' data: https://fonts.gstatic.com https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://unpkg.com; "
            ."script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://accounts.google.com https://cdnjs.cloudflare.com https://unpkg.com; "
            ."connect-src ".implode(' ', $connect)."; "
            ."frame-src https://accounts.google.com https://www.google.com https://online.anyflip.com;"
        );

        return $response;
    }
}
