<?php

namespace App\Services\Admin\TrayekWisata;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class TrayekWisataHeroService
{
    private const DIR = 'trayekwisata/hero';
    public const MAX_VIDEO_BYTES = 2097152;

    public function upload(UploadedFile $file): array
    {
        $mime = strtolower((string) $file->getMimeType());
        $ext = strtolower((string) $file->getClientOriginalExtension());
        $isImage = str_starts_with($mime, 'image/');
        $isVideo = $mime === 'video/mp4' && $ext === 'mp4';

        if (!$isImage && !$isVideo) {
            throw new \RuntimeException('Gunakan gambar atau video MP4.');
        }
        if ($isVideo && $file->getSize() >= self::MAX_VIDEO_BYTES) {
            throw new \RuntimeException('Video harus di bawah 2 MB.');
        }
        if ($isImage) {
            return $this->saveImage($file);
        }

        $filename = bin2hex(random_bytes(12)).'.mp4';
        Storage::disk('uploads')->put(self::DIR.'/'.$filename, file_get_contents($file->getRealPath()));
        return ['tipe' => 'video', 'url' => '/uploads/'.self::DIR.'/'.$filename];
    }

    private function saveImage(UploadedFile $file): array
    {
        if (!extension_loaded('gd') || !function_exists('imagewebp')) {
            throw new \RuntimeException('Ekstensi GD dengan dukungan WebP belum aktif di server.');
        }
        $info = @getimagesize($file->getRealPath());
        if (!$info || !in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP, IMAGETYPE_GIF], true)) {
            throw new \RuntimeException('Format gambar tidak didukung. Gunakan JPG, PNG, GIF, atau WebP.');
        }
        $src = @imagecreatefromstring((string) file_get_contents($file->getRealPath()));
        if (!$src) {
            throw new \RuntimeException('Gambar rusak atau tidak dapat dibaca.');
        }
        $w = imagesx($src); $h = imagesy($src);
        $ratio = min(1, 1920 / max($w, 1), 900 / max($h, 1));
        $nw = max(1, (int) round($w * $ratio)); $nh = max(1, (int) round($h * $ratio));
        $dst = imagecreatetruecolor($nw, $nh);
        imagealphablending($dst, false); imagesavealpha($dst, true);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
        ob_start(); imagewebp($dst, null, 82); $data = ob_get_clean();
        imagedestroy($src); imagedestroy($dst);
        if (!$data) throw new \RuntimeException('Gagal mengonversi gambar ke WebP.');
        $filename = bin2hex(random_bytes(12)).'.webp';
        Storage::disk('uploads')->put(self::DIR.'/'.$filename, $data);
        return ['tipe' => 'gambar', 'url' => '/uploads/'.self::DIR.'/'.$filename];
    }

    public function delete(string $url): void
    {
        $relative = ltrim(str_replace('/uploads/', '', $url), '/');
        if (str_starts_with($relative, self::DIR.'/')) Storage::disk('uploads')->delete($relative);
    }
}
