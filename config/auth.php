<?php

return [

    // CATATAN: Aplikasi ini TIDAK memakai sistem Auth bawaan Laravel
    // (Auth::user() dsb). Login admin & akun publik memakai session key
    // manual (admin_logged_in, akun_publik_id, dst.) persis seperti kode
    // lama - lihat App\Services\AkunPublik\AkunPublikService dan
    // App\Services\MenuLayanan\MenuLayananService::isPublicSuperadmin().
    // Config ini hanya disediakan supaya provider bawaan Laravel tidak error.
    'defaults' => [
        'guard' => 'web',
        'passwords' => 'users',
    ],

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],
    ],

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => 10800,

];
