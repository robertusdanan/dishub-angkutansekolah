<?php

namespace App\Services\LocalMedia;

/**
 * Port dari bagian core/local_media_helper.php yang dipakai modul akun
 * (akun_media_dokumen_dir, akun_compress_image / tw_compress_image,
 * tw_media_apply_orientation, tw_media_ensure_dir).
 *
 * Path penyimpanan: storage/app/private/dokumen/{ktp|kk} - setara
 * "private/dokumen/{jenis}" di kode lama (di luar web root, tidak bisa
 * diakses langsung lewat URL). Ini adalah root disk "local" bawaan
 * Laravel 11 (lihat config/filesystems.php).
 */
class DokumenService
{
    public function dir(string $jenis): string
    {
        $dir = storage_path('app/private/dokumen/'.$jenis);
        $this->ensureDir($dir);

        return $dir;
    }

    /**
     * Direktori LEGACY khusus Balik Gratis (data lama SEBELUM fitur akun
     * terpusat ada - baris balikgratis_pemesanan.dokumen_filename).
     * Data baru sudah pakai dir('ktp'/'kk') via akun_publik.
     */
    public function dirLegacyBalikGratis(): string
    {
        $dir = storage_path('app/private/dokumen/balikgratis');
        $this->ensureDir($dir);

        return $dir;
    }

    protected function ensureDir(string $dir): void
    {
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        $htaccess = $dir.'/.htaccess';
        if (!is_file($htaccess)) {
            @file_put_contents(
                $htaccess,
                "Options -Indexes\n<FilesMatch \"\\.(php|phtml|php\\d)$\">\nRequire all denied\n</FilesMatch>\n<FilesMatch \"^\\.\">\nRequire all denied\n</FilesMatch>\n"
            );
        }
    }

    /**
     * Kompres & simpan gambar sebagai JPEG (resize proporsional + auto-rotate
     * EXIF) - port 1:1 dari tw_compress_image().
     */
    public function compressImage(string $srcPath, string $destPath, int $maxDim = 2000, int $quality = 88): bool
    {
        $info = @getimagesize($srcPath);
        if ($info === false) {
            return false;
        }

        $src = match ($info['mime']) {
            'image/jpeg' => @imagecreatefromjpeg($srcPath),
            'image/png' => @imagecreatefrompng($srcPath),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($srcPath) : false,
            default => false,
        };
        if (!$src) {
            return false;
        }

        $w = imagesx($src);
        $h = imagesy($src);
        $ratio = min(1, $maxDim / max($w, $h));
        $newW = max(1, (int) round($w * $ratio));
        $newH = max(1, (int) round($h * $ratio));

        $dst = imagecreatetruecolor($newW, $newH);
        $white = imagecolorallocate($dst, 255, 255, 255);
        imagefill($dst, 0, 0, $white);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $w, $h);

        if (function_exists('exif_read_data') && $info['mime'] === 'image/jpeg') {
            $exif = @exif_read_data($srcPath);
            if (!empty($exif['Orientation'])) {
                $dst = $this->applyOrientation($dst, (int) $exif['Orientation']);
            }
        }

        $ok = imagejpeg($dst, $destPath, $quality);
        imagedestroy($src);
        imagedestroy($dst);

        return $ok;
    }

    protected function applyOrientation($img, int $orientation)
    {
        return match ($orientation) {
            3 => imagerotate($img, 180, 0),
            6 => imagerotate($img, -90, 0),
            8 => imagerotate($img, 90, 0),
            default => $img,
        };
    }
}
