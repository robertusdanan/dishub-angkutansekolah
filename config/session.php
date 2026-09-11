<?php

use Illuminate\Support\Str;

return [

    // Kode lama memakai session file PHP native (session_start()).
    // "file" driver di Laravel = perilaku paling setara.
    'driver' => env('SESSION_DRIVER', 'file'),

    // Kode lama: ini_set('session.gc_maxlifetime', 10 * 365 * 24 * 60 * 60) — sangat panjang.
    // Kita pakai nilai wajar (2 minggu) kecuali di-override lewat .env, supaya
    // sesi admin/akun publik tidak "abadi" tanpa alasan.
    'lifetime' => (int) env('SESSION_LIFETIME', 20160),

    'expire_on_close' => false,

    'encrypt' => false,

    'files' => storage_path('framework/sessions'),

    'connection' => env('SESSION_CONNECTION'),

    'table' => env('SESSION_TABLE', 'sessions'),

    'store' => env('SESSION_STORE'),

    'lottery' => [2, 100],

    'cookie' => env(
        'SESSION_COOKIE',
        Str::slug(env('APP_NAME', 'laravel'), '_').'_session'
    ),

    'path' => '/',

    'domain' => env('SESSION_DOMAIN'),

    // Kode lama pakai cookie_httponly=true & cookie_samesite=Lax — dipertahankan.
    'secure' => env('SESSION_SECURE_COOKIE'),

    'http_only' => true,

    'same_site' => 'lax',

    'partitioned' => false,

];
