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

        $response->headers->set(
            'Content-Security-Policy',
            "frame-ancestors 'self'; "
            ."object-src 'none'; "
            ."base-uri 'self'; "
            ."img-src 'self' data: blob: https:; "
            ."media-src 'self' blob: https:; "
            ."style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://unpkg.com; "
            ."font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com; "
            ."script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://accounts.google.com https://cdnjs.cloudflare.com https://unpkg.com https://cdn.tailwindcss.com; "
            ."connect-src 'self' https://*.supabase.co wss://*.supabase.co https://accounts.google.com; "
            ."frame-src https://accounts.google.com https://online.anyflip.com;"
        );

        return $response;
    }
}
