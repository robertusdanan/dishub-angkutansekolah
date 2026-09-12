<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AuditLogService;
use App\Services\Supabase\SupabaseClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

/**
 * Port dari admin/change_credentials.php.
 */
class AdminAccountController extends Controller
{
    public function __construct(
        protected SupabaseClient $supabase,
        protected AuditLogService $audit,
    ) {
    }

    public function update(Request $request): JsonResponse
    {
        $action = (string) $request->input('action', '');
        $currentPass = (string) $request->input('current_password', '');
        $newPass = (string) $request->input('new_password', '');
        $confirmPass = (string) $request->input('confirm_password', '');
        $newUsername = trim((string) $request->input('username', ''));

        $currentAccountId = Session::get('admin_account_id', '');
        $currentUser = Session::get('admin_user', '');
        $currentRole = Session::get('admin_role', '');

        if ($currentAccountId === '') {
            return response()->json(['status' => 'error', 'message' => 'Sesi tidak valid - silakan login ulang.']);
        }

        [$code, $rows] = $this->supabase->rawRequest('GET', 'admin_accounts?id=eq.'.$currentAccountId.'&select=id,username,password_hash,is_active&limit=1');
        $account = ($code >= 200 && $code < 300 && !empty($rows)) ? $rows[0] : null;

        if (!$account || empty($account['is_active'])) {
            return response()->json(['status' => 'error', 'message' => 'Akun tidak ditemukan atau nonaktif.']);
        }

        if ($action === 'password_only') {
            if (!$currentPass) {
                return response()->json(['status' => 'error', 'message' => 'Masukkan password sekarang']);
            }
            if (strlen($newPass) < 8) {
                return response()->json(['status' => 'error', 'message' => 'Password baru minimal 8 karakter']);
            }
            if ($newPass !== $confirmPass) {
                return response()->json(['status' => 'error', 'message' => 'Konfirmasi password tidak cocok']);
            }
            if (!password_verify($currentPass, $account['password_hash'])) {
                sleep(1);

                return response()->json(['status' => 'error', 'message' => 'Password saat ini salah']);
            }

            $newHash = password_hash($newPass, PASSWORD_BCRYPT, ['cost' => 12]);
            [$patchCode] = $this->supabase->rawRequest('PATCH', 'admin_accounts?id=eq.'.$currentAccountId, ['password_hash' => $newHash]);
            if ($patchCode < 200 || $patchCode >= 300) {
                return response()->json(['status' => 'error', 'message' => 'Gagal menyimpan: HTTP '.$patchCode]);
            }

            $this->audit->log('admin_akun.self_password_change', ['account_id' => $currentAccountId]);

            return response()->json(['status' => 'ok', 'message' => 'Password berhasil diubah']);
        }

        if ($action === 'username') {
            if ($currentRole === 'superadmin') {
                return response()->json(['status' => 'error', 'message' => 'superadmin tidak dapat mengganti username']);
            }
            if (!$newUsername || !$currentPass) {
                return response()->json(['status' => 'error', 'message' => 'Username baru dan password saat ini wajib diisi']);
            }
            if (strlen($newUsername) < 4) {
                return response()->json(['status' => 'error', 'message' => 'Username minimal 4 karakter']);
            }
            if (!preg_match('/^[a-zA-Z0-9_\-.]+$/', $newUsername)) {
                return response()->json(['status' => 'error', 'message' => 'Username hanya boleh huruf, angka, underscore, strip, dan titik']);
            }
            if (!password_verify($currentPass, $account['password_hash'])) {
                sleep(1);

                return response()->json(['status' => 'error', 'message' => 'Password saat ini salah']);
            }

            if ($newUsername !== $currentUser) {
                [, $existingRows] = $this->supabase->rawRequest('GET', 'admin_accounts?username=eq.'.rawurlencode($newUsername).'&select=id&limit=1');
                if (!empty($existingRows)) {
                    return response()->json(['status' => 'error', 'message' => 'Username sudah digunakan oleh user lain']);
                }
            }

            [$patchCode] = $this->supabase->rawRequest('PATCH', 'admin_accounts?id=eq.'.$currentAccountId, ['username' => $newUsername]);
            if ($patchCode < 200 || $patchCode >= 300) {
                return response()->json(['status' => 'error', 'message' => 'Gagal menyimpan: HTTP '.$patchCode]);
            }

            $this->audit->log('admin_akun.self_username_change', ['account_id' => $currentAccountId, 'old_username' => $currentUser, 'new_username' => $newUsername]);

            Session::put('admin_user', $newUsername);

            return response()->json(['status' => 'ok', 'message' => 'Username berhasil diubah']);
        }

        return response()->json(['status' => 'error', 'message' => 'Aksi tidak valid']);
    }
}
