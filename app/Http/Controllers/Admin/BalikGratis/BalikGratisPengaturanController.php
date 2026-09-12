<?php

namespace App\Http\Controllers\Admin\BalikGratis;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminRoleService;
use App\Services\Supabase\SupabaseClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Port dari admin/api/balikgratis/pengaturan.php.
 */
class BalikGratisPengaturanController extends Controller
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
        if (!$this->roles->isSuperAdmin() && !$this->roles->canAccessMenu('balikgratis_pengaturan')) {
            return response()->json(['error' => 'Forbidden: tidak punya izin ke menu Pengaturan Kuota.'], 403);
        }

        return null;
    }

    protected function ambilPengaturan(int $tahun): ?array
    {
        [$code, $rows] = $this->supabase->rawRequest('GET', 'balikgratis_pengaturan?tahun=eq.'.$tahun.'&select=*&limit=1');

        return ($code >= 200 && $code < 300 && !empty($rows)) ? $rows[0] : null;
    }

    protected function hitungTerisi(int $tahun): int
    {
        [$code, $rows] = $this->supabase->rawRequest('GET', 'balikgratis_pemesanan?tahun=eq.'.$tahun.'&status=neq.dibatalkan&select=id');

        return ($code >= 200 && $code < 300 && is_array($rows)) ? count($rows) : 0;
    }

    /** GET ?action=status */
    public function status(): JsonResponse
    {
        if ($fail = $this->requireAccess()) {
            return $fail;
        }

        $tahunIni = (int) date('Y');
        $pengaturan = $this->ambilPengaturan($tahunIni);
        $terisi = $this->hitungTerisi($tahunIni);

        return response()->json(['status' => 'ok', 'data' => [
            'tahun' => $tahunIni,
            'kuota' => $pengaturan['kuota'] ?? 0,
            'status' => $pengaturan['status'] ?? 'tutup',
            'dibuka_at' => $pengaturan['dibuka_at'] ?? null,
            'dibuka_oleh' => $pengaturan['dibuka_oleh'] ?? null,
            'ditutup_at' => $pengaturan['ditutup_at'] ?? null,
            'terisi' => $terisi,
            'sisa' => max(0, (int) ($pengaturan['kuota'] ?? 0) - $terisi),
        ]]);
    }

    /** POST ?action=buka|ubah_kuota|tutup */
    public function update(Request $request): JsonResponse
    {
        if ($fail = $this->requireAccess()) {
            return $fail;
        }

        $tahunIni = (int) date('Y');
        $adminUser = session('admin_user', 'admin');
        $action = (string) $request->query('action', '');

        if ($action === 'buka') {
            $kuota = (int) $request->input('kuota', 0);
            if ($kuota < 1) {
                return response()->json(['error' => 'Kuota harus lebih dari 0.'], 422);
            }

            $existing = $this->ambilPengaturan($tahunIni);
            $payload = [
                'tahun' => $tahunIni,
                'kuota' => $kuota,
                'status' => 'buka',
                'dibuka_at' => date('c'),
                'dibuka_oleh' => $adminUser,
                'updated_at' => date('c'),
            ];

            [$code] = $existing
                ? $this->supabase->rawRequest('PATCH', 'balikgratis_pengaturan?tahun=eq.'.$tahunIni, $payload)
                : $this->supabase->rawRequest('POST', 'balikgratis_pengaturan', $payload);

            if ($code < 200 || $code >= 300) {
                return response()->json(['error' => 'Gagal membuka pendaftaran.'], 502);
            }

            return response()->json(['status' => 'ok', 'data' => ['ok' => true]]);
        }

        if ($action === 'ubah_kuota') {
            $kuota = (int) $request->input('kuota', -1);
            if ($kuota < 0) {
                return response()->json(['error' => 'Kuota tidak valid.'], 422);
            }
            if (!$this->ambilPengaturan($tahunIni)) {
                return response()->json(['error' => 'Pengaturan tahun ini belum ada - buka pendaftaran dulu.'], 404);
            }

            [$code] = $this->supabase->rawRequest('PATCH', 'balikgratis_pengaturan?tahun=eq.'.$tahunIni, [
                'kuota' => $kuota,
                'updated_at' => date('c'),
            ]);
            if ($code < 200 || $code >= 300) {
                return response()->json(['error' => 'Gagal mengubah kuota.'], 502);
            }

            return response()->json(['status' => 'ok', 'data' => ['ok' => true]]);
        }

        if ($action === 'tutup') {
            if (!$this->ambilPengaturan($tahunIni)) {
                return response()->json(['error' => 'Pengaturan tahun ini belum ada.'], 404);
            }

            [$code] = $this->supabase->rawRequest('PATCH', 'balikgratis_pengaturan?tahun=eq.'.$tahunIni, [
                'status' => 'tutup',
                'ditutup_at' => date('c'),
                'ditutup_oleh' => $adminUser,
                'updated_at' => date('c'),
            ]);
            if ($code < 200 || $code >= 300) {
                return response()->json(['error' => 'Gagal menutup pendaftaran.'], 502);
            }

            return response()->json(['status' => 'ok', 'data' => ['ok' => true]]);
        }

        return response()->json(['error' => 'Aksi tidak dikenal.'], 400);
    }
}
