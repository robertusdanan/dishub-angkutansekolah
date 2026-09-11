<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Supabase (pengganti "private/config.php" -> supabase_url / anon / service)
    |--------------------------------------------------------------------------
    | Tetap dipakai sebagai backend data lewat REST API (bukan Eloquent),
    | persis seperti core/sb_client_helper.php di kode lama.
    */
    'supabase' => [
        'url' => env('SUPABASE_URL'),
        'anon_key' => env('SUPABASE_ANON_KEY'),
        'service_key' => env('SUPABASE_SERVICE_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Google OAuth
    |--------------------------------------------------------------------------
    | google_redirect_uri_akun (login akun publik terpusat) dan
    | google_redirect_uri (dipakai modul absenqrcode) dipisah karena
    | keduanya didaftarkan sebagai redirect URI berbeda di Google Cloud
    | Console — sama seperti kode lama, JANGAN digabung.
    */
    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect_uri_akun' => env('GOOGLE_REDIRECT_URI_AKUN'),
        'redirect_uri_absenqrcode' => env('GOOGLE_REDIRECT_URI_ABSENQRCODE'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Token internal lain dari private/config.php lama
    |--------------------------------------------------------------------------
    */
    'cleanup_token' => env('CLEANUP_TOKEN'),
    'password_secret_token' => env('PASSWORD_SECRET_TOKEN'),

    // Dipakai CacheInvalidateController (webhook dipanggil Supabase Database
    // Trigger server-to-server, BUKAN dari browser). Nilainya hardcoded di
    // kode lama (core/secret_cache.php, disamarkan base64 ganda + strrev),
    // dipertahankan sama supaya trigger Supabase yang sudah ada tidak perlu
    // diubah sisi konfigurasinya.
    'cache_webhook_secret' => env('CACHE_WEBHOOK_SECRET', '753214@rjunA'),

    /*
    |--------------------------------------------------------------------------
    | Kredit developer di footer (get_credit_data() lama)
    |--------------------------------------------------------------------------
    | Sengaja DIPERTAHANKAN sesuai instruksi — mengambil data kredit dari
    | project Supabase LAIN (milik pembuat template), bukan project Supabase
    | utama aplikasi ini. Nilainya sudah tidak lagi disembunyikan lewat
    | base64/eval seperti versi lama, tapi perilakunya sama persis.
    */
    'site_credit' => [
        'url' => env('SITE_CREDIT_SUPABASE_URL', 'https://uedvihbrcusjjrfwduxp.supabase.co'),
        'anon_key' => env('SITE_CREDIT_SUPABASE_ANON_KEY'),
    ],

];
