<?php

namespace App\Services\Admin\TrayekWisata;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Port dari bagian galeri di core/local_media_helper.php - dipakai
 * admin/api/trayekwisata/upload_galeri.php.
 *
 * Disk "uploads" (public/uploads/) - folder galeri per titik dibuat dari
 * slug nama titik, dengan file penanda .titik_id (uuid) supaya tetap bisa
 * ditemukan lagi meskipun nama titiknya berubah setelah folder dibuat.
 */
class TrayekWisataGaleriService
{
    protected const BASE = 'trayekwisata/galeri';

    public function slugify(string $s, string $fallback = 'titik'): string
    {
        $s = trim($s);
        $translit = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
        if ($translit !== false && trim($translit) !== '') {
            $s = $translit;
        }
        $s = strtolower($s);
        $s = preg_replace('/[^a-z0-9]+/', '-', $s);
        $s = trim($s, '-');

        return $s !== '' ? (strlen($s) > 60 ? trim(substr($s, 0, 60), '-') : $s) : $fallback;
    }

    protected function findExistingDir(string $titikId): ?string
    {
        $disk = Storage::disk('uploads');
        if (!$disk->exists(self::BASE)) {
            return null;
        }
        foreach ($disk->directories(self::BASE) as $dir) {
            $marker = $dir.'/.titik_id';
            if ($disk->exists($marker) && trim($disk->get($marker)) === $titikId) {
                return $dir;
            }
        }

        return null;
    }

    /**
     * @return string path folder (relatif disk "uploads")
     */
    public function galeriDir(string $titikId, string $nama = ''): string
    {
        $existing = $this->findExistingDir($titikId);
        if ($existing !== null) {
            return $existing;
        }

        $disk = Storage::disk('uploads');
        $slug = $nama !== '' ? $this->slugify($nama) : substr($titikId, 0, 8);

        $candidate = $slug;
        $i = 2;
        while ($disk->exists(self::BASE.'/'.$candidate)) {
            $candidate = $slug.'-'.$i;
            $i++;
        }

        $dir = self::BASE.'/'.$candidate;
        $disk->put($dir.'/.titik_id', $titikId);

        return $dir;
    }

    public function galeriUrl(string $folderName, string $filename): string
    {
        return '/uploads/'.self::BASE.'/'.$folderName.'/'.$filename;
    }

    /**
     * Kompres & simpan gambar sebagai JPEG (resize proporsional + auto-rotate EXIF).
     */
    public function compressImage(string $srcPath, int $maxDim = 1920, int $quality = 82): ?string
    {
        $info = @getimagesize($srcPath);
        if ($info === false) {
            return null;
        }

        $src = match ($info['mime']) {
            'image/jpeg' => @imagecreatefromjpeg($srcPath),
            'image/png' => @imagecreatefrompng($srcPath),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($srcPath) : false,
            default => false,
        };
        if (!$src) {
            return null;
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
                $dst = match ((int) $exif['Orientation']) {
                    3 => imagerotate($dst, 180, 0),
                    6 => imagerotate($dst, -90, 0),
                    8 => imagerotate($dst, 90, 0),
                    default => $dst,
                };
            }
        }

        ob_start();
        imagejpeg($dst, null, $quality);
        $data = ob_get_clean();
        imagedestroy($src);
        imagedestroy($dst);

        return $data ?: null;
    }

    protected function findFfmpeg(): ?string
    {
        if (!function_exists('exec')) {
            return null;
        }
        $out = [];
        @exec('command -v ffmpeg 2>/dev/null', $out);

        return $out[0] ?? null;
    }

    /**
     * Simpan video - dikompres kalau ffmpeg tersedia di server, kalau
     * tidak disimpan apa adanya (tidak pernah gagal upload total).
     */
    public function saveVideo(string $srcPath, string $ext): string
    {
        $ffmpeg = $this->findFfmpeg();
        $tmpOut = tempnam(sys_get_temp_dir(), 'twvid_').'.'.$ext;

        if ($ffmpeg !== null) {
            $cmd = escapeshellcmd($ffmpeg).' -y -i '.escapeshellarg($srcPath)
                .' -vf '.escapeshellarg("scale='min(1280,iw)':-2")
                .' -c:v libx264 -crf 28 -preset veryfast -c:a aac -b:a 96k '
                .escapeshellarg($tmpOut).' 2>&1';
            @exec($cmd, $out, $code);

            if ($code === 0 && is_file($tmpOut) && filesize($tmpOut) > 0) {
                $data = file_get_contents($tmpOut);
                @unlink($tmpOut);

                return $data;
            }
            @unlink($tmpOut);
        }

        return (string) file_get_contents($srcPath);
    }

    /**
     * @return array{filename:string,folder:string,url:string,tipe:string}
     */
    public function upload(UploadedFile $file, string $titikId, string $namaTitik): array
    {
        $mime = $file->getMimeType() ?: 'application/octet-stream';
        $isImage = str_starts_with($mime, 'image/');
        $isVideo = str_starts_with($mime, 'video/');

        if (!$isImage && !$isVideo) {
            throw new \RuntimeException('Hanya file foto atau video yang diizinkan.');
        }

        $dir = $this->galeriDir($titikId, $namaTitik);
        $folderName = basename($dir);
        $disk = Storage::disk('uploads');

        if ($isImage) {
            $data = $this->compressImage($file->getRealPath());
            if ($data === null) {
                throw new \RuntimeException('Gagal memproses gambar. Pastikan format foto valid (JPEG/PNG/WebP).');
            }
            $filename = bin2hex(random_bytes(8)).'.jpg';
            $disk->put($dir.'/'.$filename, $data);
            $tipe = 'foto';
        } else {
            $ext = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $file->getClientOriginalExtension() ?: 'mp4')) ?: 'mp4';
            $filename = bin2hex(random_bytes(8)).'.'.$ext;
            $data = $this->saveVideo($file->getRealPath(), $ext);
            $disk->put($dir.'/'.$filename, $data);
            $tipe = 'video';
        }

        return [
            'filename' => $filename,
            'folder' => $folderName,
            'url' => $this->galeriUrl($folderName, $filename),
            'tipe' => $tipe,
        ];
    }

    public function deleteFile(string $url): void
    {
        $disk = Storage::disk('uploads');
        $rel = ltrim(str_replace('/uploads/', '', $url), '/');
        if ($disk->exists($rel)) {
            $disk->delete($rel);
        }
    }

    public function deleteDirByTitikId(string $titikId): void
    {
        $dir = $this->findExistingDir($titikId);
        if ($dir !== null) {
            Storage::disk('uploads')->deleteDirectory($dir);
        }
    }
}
