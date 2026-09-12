<?php

namespace App\Services\Admin\Asdp;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Port dari admin/api/asdp/upload_image.php.
 *
 * Gambar ASDP disimpan di disk "uploads" (public/uploads/asdp/) - sama
 * seperti kode lama yang menyimpan langsung ke filesystem lokal server,
 * BUKAN Supabase Storage. Nama file dibuat dari slug Nama Lokasi.
 */
class AsdpImageService
{
    protected const SUBDIR = 'asdp';

    protected const MAX_W = 1280;

    protected const MAX_H = 720;

    public function slugify(string $s): string
    {
        $s = trim($s);
        $translit = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
        if ($translit !== false && trim($translit) !== '') {
            $s = $translit;
        }
        $s = strtolower($s);
        $s = preg_replace('/[^a-z0-9]+/', '-', $s);
        $s = trim($s, '-');
        if ($s === '') {
            $s = 'lokasi';
        }
        if (strlen($s) > 80) {
            $s = trim(substr($s, 0, 80), '-');
        }

        return $s;
    }

    protected function dir(): string
    {
        return self::SUBDIR;
    }

    public function uniqueFilename(string $baseSlug, string $ext, string $keepIfSameAs = ''): string
    {
        $disk = Storage::disk('uploads');
        $candidate = $baseSlug.'.'.$ext;
        $i = 2;
        while ($disk->exists($this->dir().'/'.$candidate) && strcasecmp($candidate, $keepIfSameAs) !== 0) {
            $candidate = $baseSlug.'-'.$i.'.'.$ext;
            $i++;
        }

        return $candidate;
    }

    public function delete(string $filename): void
    {
        $disk = Storage::disk('uploads');
        $path = $this->dir().'/'.basename($filename);
        if ($disk->exists($path)) {
            $disk->delete($path);
        }
    }

    /**
     * @return array{filename:string, renamed:bool}
     */
    public function rename(string $oldFilename, string $newName): array
    {
        $disk = Storage::disk('uploads');
        $oldPath = $this->dir().'/'.$oldFilename;

        if (!$disk->exists($oldPath)) {
            return ['filename' => $oldFilename, 'renamed' => false];
        }

        $ext = strtolower((string) pathinfo($oldFilename, PATHINFO_EXTENSION));
        $baseSlug = $this->slugify($newName);
        $newFilename = $this->uniqueFilename($baseSlug, $ext, $oldFilename);

        if ($newFilename === $oldFilename) {
            return ['filename' => $oldFilename, 'renamed' => false];
        }

        $disk->move($oldPath, $this->dir().'/'.$newFilename);

        return ['filename' => $newFilename, 'renamed' => true];
    }

    /**
     * Kompres + resize (maks HD, hanya mengecilkan) + simpan sebagai JPEG.
     *
     * @return array{filename:string,url:string,width:int,height:int,original_width:int,original_height:int}
     *
     * @throws \RuntimeException
     */
    public function upload(UploadedFile $file, string $name, string $oldImage = ''): array
    {
        if (!extension_loaded('gd') || !function_exists('imagecreatefromstring')) {
            throw new \RuntimeException('Ekstensi GD PHP belum aktif di server ini.');
        }

        $tmpPath = $file->getRealPath();
        $info = @getimagesize($tmpPath);
        if ($info === false) {
            throw new \RuntimeException('File yang diupload bukan file gambar yang valid.');
        }
        [$origW, $origH, $imgType] = $info;

        $allowedTypes = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP];
        if (!in_array($imgType, $allowedTypes, true)) {
            throw new \RuntimeException('Format gambar tidak didukung. Gunakan JPG, PNG, WEBP, atau GIF.');
        }

        $src = @imagecreatefromstring((string) file_get_contents($tmpPath));
        if (!$src) {
            throw new \RuntimeException('Gagal membaca isi gambar. File mungkin rusak.');
        }

        if ($imgType === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
            $exif = @exif_read_data($tmpPath);
            $orientation = $exif['Orientation'] ?? 1;
            $rotated = match ((int) $orientation) {
                3 => imagerotate($src, 180, 0),
                6 => imagerotate($src, -90, 0),
                8 => imagerotate($src, 90, 0),
                default => null,
            };
            if ($rotated !== false && $rotated !== null) {
                imagedestroy($src);
                $src = $rotated;
                $origW = imagesx($src);
                $origH = imagesy($src);
            }
        }

        $ratio = min(1, self::MAX_W / $origW, self::MAX_H / $origH);
        $newW = max(1, (int) round($origW * $ratio));
        $newH = max(1, (int) round($origH * $ratio));

        $dst = imagecreatetruecolor($newW, $newH);
        $white = imagecolorallocate($dst, 255, 255, 255);
        imagefilledrectangle($dst, 0, 0, $newW, $newH, $white);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
        imagedestroy($src);

        if ($oldImage !== '' && !preg_match('/^[A-Za-z0-9_\-]+\.(jpg|jpeg|png|webp|gif)$/i', $oldImage)) {
            $oldImage = '';
        }

        $baseSlug = $this->slugify($name);
        $filename = $this->uniqueFilename($baseSlug, 'jpg', $oldImage);

        ob_start();
        imagejpeg($dst, null, 82);
        $jpegData = ob_get_clean();
        imagedestroy($dst);

        Storage::disk('uploads')->put($this->dir().'/'.$filename, $jpegData);

        if ($oldImage !== '' && $oldImage !== $filename) {
            $this->delete($oldImage);
        }

        return [
            'filename' => $filename,
            'url' => '/uploads/asdp/'.$filename,
            'width' => $newW,
            'height' => $newH,
            'original_width' => $origW,
            'original_height' => $origH,
        ];
    }
}
