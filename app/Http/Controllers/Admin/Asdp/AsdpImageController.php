<?php

namespace App\Http\Controllers\Admin\Asdp;

use App\Http\Controllers\Controller;
use App\Services\Admin\Asdp\AsdpImageService;
use App\Services\Admin\AdminRoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Port dari admin/api/asdp/upload_image.php.
 */
class AsdpImageController extends Controller
{
    public function __construct(
        protected AdminRoleService $roles,
        protected AsdpImageService $images,
    ) {
    }

    public function handle(Request $request): JsonResponse
    {
        if (!$this->roles->canAccessMenu('asdp_daftar')) {
            return response()->json(['error' => 'Akses ditolak: Anda tidak punya izin mengelola gambar Layanan ASDP.'], 403);
        }

        $action = (string) $request->input('action', 'upload');

        if ($action === 'delete') {
            $filename = basename((string) $request->input('filename', ''));
            if ($filename === '' || !preg_match('/^[A-Za-z0-9_\-]+\.(jpg|jpeg|png|webp|gif)$/i', $filename)) {
                return response()->json(['error' => 'Nama file tidak valid.'], 400);
            }
            $this->images->delete($filename);

            return response()->json(['status' => 'ok', 'deleted' => $filename]);
        }

        if ($action === 'rename') {
            $oldFilename = basename((string) $request->input('old_filename', ''));
            $newName = trim((string) $request->input('name', ''));

            if ($oldFilename === '' || !preg_match('/^[A-Za-z0-9_\-]+\.(jpg|jpeg|png|webp|gif)$/i', $oldFilename)) {
                return response()->json(['error' => 'Nama file lama tidak valid.'], 400);
            }
            if ($newName === '') {
                return response()->json(['error' => 'Nama lokasi tidak boleh kosong.'], 400);
            }

            $result = $this->images->rename($oldFilename, $newName);

            return response()->json(['status' => 'ok', ...$result]);
        }

        if ($action !== 'upload') {
            return response()->json(['error' => 'Action tidak dikenal: '.$action], 400);
        }

        $name = trim((string) $request->input('name', ''));
        if ($name === '') {
            return response()->json(['error' => 'Nama lokasi tidak boleh kosong (dipakai sebagai nama file gambar).'], 400);
        }

        $file = $request->file('image');
        if (!$file || !$file->isValid()) {
            return response()->json(['error' => 'Tidak ada file gambar yang diupload / gagal diupload.'], 400);
        }

        try {
            $oldImage = trim((string) $request->input('old_image', ''));
            $result = $this->images->upload($file, $name, $oldImage);

            return response()->json(['status' => 'ok', ...$result]);
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
