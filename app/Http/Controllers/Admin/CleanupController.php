<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminRoleService;
use App\Services\Admin\AuditLogService;
use App\Services\Admin\CleanupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

/**
 * Port dari bagian HTTP admin/api/cleanup.php (bagian CLI ada di
 * app/Console/Commands/CleanupOldData.php).
 */
class CleanupController extends Controller
{
    public function __construct(
        protected CleanupService $cleanup,
        protected AdminRoleService $roles,
        protected AuditLogService $audit,
    ) {
    }

    public function run(Request $request): JsonResponse
    {
        $cleanupToken = (string) config('services.cleanup_token', '');
        $token = (string) $request->query('token', '');

        if ($cleanupToken === '') {
            return response()->json(['status' => 'error', 'message' => 'Konfigurasi belum lengkap: CLEANUP_TOKEN belum diisi di .env.'], 500);
        }
        if ($token === '' || !hash_equals($cleanupToken, $token)) {
            return response()->json(['status' => 'error', 'message' => 'Akses ditolak'], 403);
        }

        // Token URL saja TIDAK cukup (defense in depth) - wajib juga login
        // sebagai admin dengan izin pengaturan.cleanup, sama seperti kode lama.
        if (!Session::get('admin_logged_in') || (!$this->roles->isSuperAdmin() && !$this->roles->hasPermission('pengaturan', 'cleanup'))) {
            return response()->json(['status' => 'error', 'message' => 'Akses ditolak: Anda tidak punya izin menjalankan Pembersihan Data Lama.'], 403);
        }

        $result = $this->cleanup->run();

        $this->audit->log('admin.cleanup_executed', ['batas' => $result['batas'], 'batas_pemesanan' => $result['batas_pemesanan']]);

        return response()->json([
            'status' => 'ok',
            'batas' => $result['batas'],
            'batas_pemesanan' => $result['batas_pemesanan'],
            'log' => $result['log'],
        ]);
    }
}
