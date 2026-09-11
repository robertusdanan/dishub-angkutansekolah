<?php

return [

    'default' => env('CACHE_STORE', 'file'),

    'stores' => [

        'array' => [
            'driver' => 'array',
            'serialize' => false,
        ],

        'file' => [
            'driver' => 'file',
            'path' => storage_path('framework/cache/data'),
            'lock_path' => storage_path('framework/cache/data'),
        ],

    ],

    // Prefix dipakai untuk semua key cache aplikasi (menu layanan, kredit
    // footer, dsb.) supaya tidak bentrok dengan store lain.
    'prefix' => env('CACHE_PREFIX', 'angkutan_cache'),

];
