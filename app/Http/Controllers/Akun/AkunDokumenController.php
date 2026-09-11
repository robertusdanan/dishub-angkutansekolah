<?php

namespace App\Http\Controllers\Akun;

use App\Http\Controllers\Controller;
use App\Services\AkunPublik\AkunPublikService;
use App\Services\LocalMedia\DokumenService;
use App\Services\Supabase\SupabaseClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Port dari pages/akun/api/upload_dokumen.php dan dokumen_view.php.
 */
class AkunDokumenController extends Controller
{
    public function __construct(
        protected AkunPublikService $akun,
        protected SupabaseClient $supabase,
        protected DokumenService $dokumen,
    ) {
    }

    protected function requireLogin(Request $request): ?JsonResponse
    {
        if ($this->akun->isLogin()) {
            return null;
        }

        return response()->json([
            'error' => 'Silakan masuk dengan akun Google terlebih dahulu.',
            'login_url' => '/akun/masuk?next='.rawurlencode($request->fullUrl()),
        ], 401);
    }

    /**
     * POST /akun/api/upload-dokumen
     */
    public function upload(Request $request): JsonResponse
    {
        if ($fail = $this->requireLogin($request)) {
            return $fail;
        }

        $jenis = (string) $request->input('jenis', '');
        if (!in_array($jenis, ['ktp', 'kk'], true)) {
            return response()->json(['error' => 'Jenis dokumen tidak valid.'], 400);
        }

        $file = $request->file('file');
        if (!$file || !$file->isValid()) {
            return response()->json(['error' => 'File tidak ditemukan / gagal diupload.'], 400);
        }

        $mimeType = $file->getMimeType() ?: 'image/jpeg';
        if (!str_starts_with($mimeType, 'image/')) {
            return response()->json(['error' => 'File harus berupa foto.'], 400);
        }
        if ($file->getSize() > 15 * 1024 * 1024) {
            return response()->json(['error' => 'Ukuran file maksimal 15MB.'], 400);
        }

        $akunId = $this->akun->id();
        $destDir = $this->dokumen->dir($jenis); // di luar web root
        $filename = $jenis.'_'.bin2hex(random_bytes(6)).'.jpg';
        $destPath = $destDir.'/'.$filename;

        if (!$this->dokumen->compressImage($file->getRealPath(), $destPath, 2000, 88)) {
            return response()->json(['error' => 'Gagal memproses foto. Pastikan hasil kamera berupa gambar valid.'], 500);
        }

        $column = $jenis === 'ktp' ? 'foto_ktp_url' : 'foto_kk_url';
        [$patchCode] = $this->supabase->update('akun_publik', [
            $column => $filename,
            'updated_at' => date('c'),
        ], ['id' => 'eq.'.$akunId]);

        if ($patchCode < 200 || $patchCode >= 300) {
            @unlink($destPath);

            return response()->json(['error' => 'Terupload tapi gagal disimpan ke profil.'], 502);
        }

        return response()->json(['ok' => true, 'path' => $filename]);
    }

    /**
     * GET /akun/api/dokumen-view — mengalirkan file foto KTP/KK milik akun
     * yang sedang login (file-nya tersimpan di luar web root).
     */
    public function view(Request $request): Response|StreamedResponse
    {
        if ($fail = $this->requireLogin($request)) {
            return $fail;
        }

        $jenis = (string) $request->query('jenis', '');
        if (!in_array($jenis, ['ktp', 'kk'], true)) {
            return response('Jenis tidak valid.', 400);
        }

        $akunId = $this->akun->id();
        $column = $jenis === 'ktp' ? 'foto_ktp_url' : 'foto_kk_url';

        [$code, $rows] = $this->supabase->select('akun_publik', ['id' => 'eq.'.$akunId, 'select' => $column], 1);
        $filename = ($code >= 200 && $code < 300 && !empty($rows[0][$column])) ? $rows[0][$column] : null;

        if (!$filename || !preg_match('/^[a-z0-9_.-]+$/i', $filename)) {
            return response('Dokumen tidak ditemukan.', 404);
        }

        $path = $this->dokumen->dir($jenis).'/'.$filename;
        if (!is_file($path)) {
            return response('Dokumen tidak ditemukan.', 404);
        }

        return response()->file($path, [
            'Content-Type' => 'image/jpeg',
            'Cache-Control' => 'private, no-store',
        ]);
    }
}
