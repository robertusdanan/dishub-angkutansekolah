<?php

if (!function_exists('asset_url')) {
    /**
     * Port dari core/cache_helper.php (asset_url()) - URL asset dengan
     * ?v=filemtime otomatis untuk cache-busting.
     */
    function asset_url(string $path): string
    {
        $clean = ltrim($path, '/');
        $fullPath = public_path($clean);

        $version = is_file($fullPath) ? filemtime($fullPath) : time();

        return '/'.$clean.'?v='.$version;
    }
}
