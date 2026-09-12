<?php

namespace App\Http\Controllers\Admin\AngkutanSekolah;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminRoleService;
use App\Services\Supabase\SupabaseClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Port dari admin/pages/angkutansekolah/updatedata/upload_foto.php.
 */
class AbsensiFotoUploadController extends Controller
{
    protected const DISK_DIR = 'angkutansekolah/absensi-foto';

    public function __construct(
        protected AdminRoleService $roles,
        protected SupabaseClient $supabase,
    ) {
    }

    public function handle(Request $request): JsonResponse
    {
        if (!$this->roles->isSuperAdmin() && !$this->roles->isDishubta()) {
            return response()->json(['error' => 'Akses ditolak untuk role ini'], 403);
        }

        $action = (string) ($request->query('action') ?? $request->input('action', ''));

        if ($action === 'upload') {
            $file = $request->file('foto');
            if (!$file || !$file->isValid()) {
                return response()->json(['error' => 'No file uploaded'], 400);
            }

            // PERBAIKAN KEAMANAN (ditemukan saat audit ulang): kode lama
            // langsung menyimpan file dengan nama ASLI dari client tanpa
            // validasi isi/tipe sama sekali - berpotensi upload arbitrary
            // file (mis. menyamar sebagai foto tapi isinya PHP). Sekarang
            // isi file diverifikasi BENAR gambar (getimagesize, bukan cuma
            // percaya ekstensi/MIME dari client) dan nama file di-generate
            // server (bukan dari input pengguna).
            $imageInfo = @getimagesize($file->getRealPath());
            if ($imageInfo === false || !in_array($imageInfo[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP, IMAGETYPE_GIF], true)) {
                return response()->json(['error' => 'File yang diupload bukan gambar yang valid (JPG/PNG/WEBP/GIF).'], 400);
            }
            if ($file->getSize() > 15 * 1024 * 1024) {
                return response()->json(['error' => 'Ukuran file maksimal 15MB.'], 400);
            }

            $ext = match ($imageInfo[2]) {
                IMAGETYPE_PNG => 'png',
                IMAGETYPE_WEBP => 'webp',
                IMAGETYPE_GIF => 'gif',
                default => 'jpg',
            };
            $fotoName = bin2hex(random_bytes(10)).'.'.$ext;

            try {
                Storage::disk('uploads')->put(self::DISK_DIR.'/'.$fotoName, file_get_contents($file->getRealPath()));
            } catch (\Throwable $e) {
                return response()->json(['error' => 'Gagal menyimpan foto ke server.'], 500);
            }

            return response()->json(['status' => 'ok', 'filename' => $fotoName, 'url' => '/uploads/'.self::DISK_DIR.'/'.$fotoName]);
        }

        if ($action === 'cek_duplikat') {
            $input = $request->json()->all();
            [$code, $body] = $this->supabase->rawRequest('GET', 'absensi_foto'
                .'?trayek=eq.'.rawurlencode((string) ($input['trayek'] ?? ''))
                .'&plat_driver=eq.'.rawurlencode((string) ($input['plat_driver'] ?? ''))
                .'&sesi=eq.'.rawurlencode((string) ($input['sesi'] ?? ''))
                .'&waktu=gte.'.rawurlencode((string) ($input['start'] ?? ''))
                .'&waktu=lte.'.rawurlencode((string) ($input['end'] ?? ''))
                .'&select=id&limit=1');

            return response()->json($body, $code ?: 500);
        }

        if ($action === 'insert') {
            $input = $request->json()->all();
            if (!$input) {
                return response()->json(['error' => 'Invalid JSON body'], 400);
            }

            [$code, $body] = $this->supabase->request('POST', '/rest/v1/absensi_foto', $input, [], true, true);

            return response()->json($body ?: (object) [], $code ?: 500);
        }

        return response()->json(['error' => 'Unknown action'], 400);
    }
}
