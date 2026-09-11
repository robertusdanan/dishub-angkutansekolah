<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminRoleService;
use App\Services\Admin\AuditLogService;
use App\Services\MenuLayanan\MenuLayananService;
use App\Services\Supabase\SupabaseClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Port dari admin/api/menu_layanan.php.
 */
class MenuLayananAdminController extends Controller
{
    public function __construct(
        protected AdminRoleService $roles,
        protected SupabaseClient $supabase,
        protected MenuLayananService $menuLayanan,
        protected AuditLogService $audit,
    ) {
    }

    /** GET ?action=list */
    public function list(): JsonResponse
    {
        if (!$this->roles->isSuperAdmin()) {
            return response()->json(['error' => 'Forbidden: hanya superadmin yang boleh mengubah Menu Layanan.'], 403);
        }

        [$code, $rows] = $this->supabase->rawRequest('GET', 'menu_layanan?select=id,slug,nama,deskripsi,aktif,urutan&order=urutan.asc');
        if ($code < 200 || $code >= 300) {
            return response()->json(['error' => 'Gagal mengambil data menu layanan.'], 502);
        }

        return response()->json(['status' => 'ok', 'data' => $rows]);
    }

    /** POST ?action=toggle_active */
    public function toggleActive(Request $request): JsonResponse
    {
        if (!$this->roles->isSuperAdmin()) {
            return response()->json(['error' => 'Forbidden: hanya superadmin yang boleh mengubah Menu Layanan.'], 403);
        }

        $id = trim((string) $request->input('id', ''));
        $isActive = (bool) $request->input('aktif', true);
        // PERBAIKAN KEAMANAN (ditemukan saat audit ulang): validasi format
        // UUID, bukan cuma "tidak kosong" — lihat catatan sama di
        // AdminAkunApiController.
        if (!preg_match('/^[0-9a-fA-F-]{36}$/', $id)) {
            return response()->json(['error' => 'ID menu layanan tidak valid.'], 400);
        }

        [$code, $rows] = $this->supabase->rawRequest('PATCH', 'menu_layanan?id=eq.'.$id, ['aktif' => $isActive]);
        if ($code < 200 || $code >= 300) {
            return response()->json(['error' => 'Gagal mengubah status menu.'], 502);
        }
        if (empty($rows)) {
            return response()->json(['error' => 'Menu layanan tidak ditemukan.'], 404);
        }

        // Cache-nya dipakai bersama SEMUA pengunjung (bukan per-sesi), jadi
        // cukup diinvalidasi sekali di sini untuk langsung berlaku ke
        // seluruh pengunjung berikutnya.
        $this->menuLayanan->invalidateCache();

        $this->audit->log('menu_layanan.toggle', ['id' => $id, 'slug' => $rows[0]['slug'] ?? null, 'aktif' => $isActive]);

        return response()->json(['status' => 'ok', 'data' => $rows[0] ?? null]);
    }
}
