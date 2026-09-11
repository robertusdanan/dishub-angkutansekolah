<?php

namespace App\Http\Controllers\Admin\BalikGratis;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminRoleService;
use App\Services\Supabase\SupabaseClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Port dari admin/api/balikgratis/verifikasi.php.
 */
class BalikGratisVerifikasiController extends Controller
{
    public function __construct(
        protected AdminRoleService $roles,
        protected SupabaseClient $supabase,
    ) {
    }

    protected function requireAccess(): ?JsonResponse
    {
        if ($this->roles->isGuest()) {
            return response()->json(['error' => 'Forbidden.'], 403);
        }
        if (!$this->roles->isSuperAdmin() && !$this->roles->canAccessMenu('balikgratis_verifikasi')) {
            return response()->json(['error' => 'Forbidden: tidak punya izin ke menu Verifikasi Tiket.'], 403);
        }

        return null;
    }

    /** GET ?action=cari&q=... */
    public function cari(Request $request): JsonResponse
    {
        if ($fail = $this->requireAccess()) {
            return $fail;
        }

        $q = trim((string) $request->query('q', ''));
        if ($q === '') {
            return response()->json(['error' => 'Kata kunci pencarian kosong.'], 422);
        }

        if (preg_match('/^[0-9a-f]{32}$/i', $q)) {
            $filter = 'qr_token=eq.'.rawurlencode($q);
        } elseif (preg_match('/^\d{16}$/', $q)) {
            $filter = 'nik=eq.'.rawurlencode($q).'&order=tahun.desc&limit=5';
        } else {
            // PERBAIKAN KEAMANAN (ditemukan saat audit ulang): kode lama
            // menyusun filter ini lewat array (otomatis di-encode oleh
            // helper Supabase-nya) — saat porting sempat tertulis ulang
            // jadi string mentah TANPA rawurlencode(), sehingga nilai $q
            // di cabang ini (nomor tiket, format bebas) bisa dipakai untuk
            // menyisipkan parameter query PostgREST tambahan (filter
            // injection). Sudah ditambahkan rawurlencode() di semua cabang.
            $filter = 'nomor_tiket=eq.'.rawurlencode(strtoupper($q));
        }

        [$code, $rows] = $this->supabase->rawRequest('GET', 'balikgratis_pemesanan?'.$filter.'&select=*');
        if ($code < 200 || $code >= 300) {
            return response()->json(['error' => 'Gagal mencari data tiket.'], 502);
        }
        if (empty($rows)) {
            return response()->json(['error' => 'Tiket tidak ditemukan.'], 404);
        }

        return response()->json(['status' => 'ok', 'data' => $rows]);
    }

    /** POST ?action=hadir */
    public function tandaiHadir(Request $request): JsonResponse
    {
        if ($fail = $this->requireAccess()) {
            return $fail;
        }

        $id = trim((string) $request->input('id', ''));
        if (!preg_match('/^[0-9a-fA-F-]{36}$/', $id)) {
            return response()->json(['error' => 'ID tiket tidak valid.'], 422);
        }

        [$getCode, $getRows] = $this->supabase->rawRequest('GET', 'balikgratis_pemesanan?id=eq.'.$id.'&select=status&limit=1');
        if ($getCode < 200 || $getCode >= 300 || empty($getRows)) {
            return response()->json(['error' => 'Tiket tidak ditemukan.'], 404);
        }
        if ($getRows[0]['status'] === 'dibatalkan') {
            return response()->json(['error' => 'Tiket ini sudah dibatalkan, tidak bisa diverifikasi hadir.'], 422);
        }
        if ($getRows[0]['status'] === 'hadir') {
            return response()->json(['error' => 'Tiket ini sudah pernah diverifikasi hadir sebelumnya.'], 409);
        }

        [$code, $result] = $this->supabase->rawRequest('PATCH', 'balikgratis_pemesanan?id=eq.'.$id, [
            'status' => 'hadir',
            'verifikasi_at' => date('c'),
            'verifikasi_oleh' => session('admin_user', 'admin'),
            'updated_at' => date('c'),
        ]);
        if ($code < 200 || $code >= 300) {
            return response()->json(['error' => 'Gagal menandai kehadiran.'], 502);
        }

        return response()->json(['status' => 'ok', 'data' => ['ok' => true, 'data' => $result[0] ?? null]]);
    }
}
