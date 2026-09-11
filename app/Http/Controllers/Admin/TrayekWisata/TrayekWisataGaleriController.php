<?php

namespace App\Http\Controllers\Admin\TrayekWisata;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminRoleService;
use App\Services\Admin\TrayekWisata\TrayekWisataGaleriService;
use App\Services\Supabase\ReferenceCacheProxyService;
use App\Services\Supabase\SupabaseClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Port dari admin/api/trayekwisata/upload_galeri.php.
 */
class TrayekWisataGaleriController extends Controller
{
    public function __construct(
        protected AdminRoleService $roles,
        protected SupabaseClient $supabase,
        protected TrayekWisataGaleriService $galeri,
        protected ReferenceCacheProxyService $refCache,
    ) {
    }

    public function upload(Request $request): JsonResponse
    {
        if (!session('admin_logged_in') || $this->roles->isGuest()) {
            return response()->json(['error' => 'Unauthorized.'], 401);
        }

        $titikId = trim((string) $request->input('titik_id', ''));
        if (!preg_match('/^[0-9a-fA-F-]{36}$/', $titikId)) {
            return response()->json(['error' => 'titik_id tidak valid.'], 400);
        }

        $file = $request->file('file');
        if (!$file || !$file->isValid()) {
            return response()->json(['error' => 'File tidak ditemukan / gagal diupload.'], 400);
        }

        $mime = $file->getMimeType() ?: '';
        $isImage = str_starts_with($mime, 'image/');
        $maxBytes = ($isImage ? 15 : 200) * 1024 * 1024;
        if ($file->getSize() > $maxBytes) {
            return response()->json(['error' => 'Ukuran file terlalu besar (maks '.($isImage ? '15MB foto' : '200MB video').').'], 400);
        }

        [, $titikRows] = $this->supabase->rawRequest('GET', 'trayekwisata_titik?select=nama&id=eq.'.$titikId.'&limit=1');
        if (empty($titikRows)) {
            return response()->json(['error' => 'Titik lokasi tidak ditemukan.'], 404);
        }
        $namaTitik = (string) ($titikRows[0]['nama'] ?? '');

        try {
            $result = $this->galeri->upload($file, $titikId, $namaTitik);
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        [, $existing] = $this->supabase->rawRequest('GET', 'trayekwisata_titik_galeri?select=urutan&titik_id=eq.'.$titikId.'&order=urutan.desc&limit=1');
        $nextUrutan = (is_array($existing) && count($existing) > 0) ? ((int) $existing[0]['urutan'] + 1) : 0;

        [$code, $inserted] = $this->supabase->rawRequest('POST', 'trayekwisata_titik_galeri', [
            'titik_id' => $titikId,
            'tipe' => $result['tipe'],
            'url' => $result['url'],
            'urutan' => $nextUrutan,
        ]);

        if ($code < 200 || $code >= 300 || empty($inserted)) {
            $this->galeri->deleteFile($result['url']);

            return response()->json(['error' => 'File terupload tapi gagal mencatat ke database.'], 500);
        }

        $this->refCache->invalidate('trayekwisata_titik_galeri');

        return response()->json($inserted[0]);
    }
}
