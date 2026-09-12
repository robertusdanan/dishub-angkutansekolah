<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AuditLogService;
use App\Services\Admin\TotpService;
use App\Services\Supabase\SupabaseClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

/**
 * REKOMENDASI KEAMANAN - enrollment 2FA (TOTP) untuk akun admin, dipasang
 * dari halaman Pengaturan. Wajib jalankan
 * database/supabase-sql/002_two_factor_auth.sql dulu di Supabase.
 */
class AdminTwoFactorController extends Controller
{
    public function __construct(
        protected TotpService $totp,
        protected SupabaseClient $supabase,
        protected AuditLogService $audit,
    ) {
    }

    public function setup(): JsonResponse
    {
        $accountId = Session::get('admin_account_id');
        if (!$accountId) {
            return response()->json(['error' => 'Sesi tidak valid.'], 401);
        }

        $secret = $this->totp->generateSecret();
        Session::put('admin_2fa_setup_secret', $secret);

        $username = session('admin_user', 'admin');
        $uri = $this->totp->provisioningUri($secret, $username);

        return response()->json(['status' => 'ok', 'secret' => $secret, 'uri' => $uri]);
    }

    public function confirm(Request $request): JsonResponse
    {
        $accountId = Session::get('admin_account_id');
        $secret = Session::get('admin_2fa_setup_secret');
        if (!$accountId || !$secret) {
            return response()->json(['error' => 'Sesi setup 2FA tidak ditemukan. Mulai ulang dari awal.'], 400);
        }

        $code = (string) $request->input('code', '');
        if (!$this->totp->verify($secret, $code)) {
            return response()->json(['error' => 'Kode tidak valid. Pastikan waktu HP Anda akurat, lalu coba lagi.'], 422);
        }

        $recoveryCodes = $this->totp->generateRecoveryCodes();
        $hashedCodes = array_map(fn ($c) => password_hash($c, PASSWORD_BCRYPT, ['cost' => 10]), $recoveryCodes);

        [$patchCode] = $this->supabase->rawRequest('PATCH', 'admin_accounts?id=eq.'.$accountId, [
            'two_factor_secret' => $secret,
            'two_factor_enabled_at' => gmdate('c'),
            'two_factor_recovery_codes' => $hashedCodes,
        ]);

        Session::forget('admin_2fa_setup_secret');

        if ($patchCode < 200 || $patchCode >= 300) {
            return response()->json(['error' => 'Gagal menyimpan pengaturan 2FA: HTTP '.$patchCode], 502);
        }

        $this->audit->log('admin_2fa.enabled', ['account_id' => $accountId]);

        return response()->json(['status' => 'ok', 'recovery_codes' => $recoveryCodes]);
    }

    public function disable(Request $request): JsonResponse
    {
        $accountId = Session::get('admin_account_id');
        if (!$accountId) {
            return response()->json(['error' => 'Sesi tidak valid.'], 401);
        }

        $password = (string) $request->input('password', '');
        [$code, $rows] = $this->supabase->rawRequest('GET', 'admin_accounts?id=eq.'.$accountId.'&select=password_hash&limit=1');
        if ($code < 200 || $code >= 300 || empty($rows) || !password_verify($password, $rows[0]['password_hash'])) {
            sleep(1);
            return response()->json(['error' => 'Password salah.'], 422);
        }

        [$patchCode] = $this->supabase->rawRequest('PATCH', 'admin_accounts?id=eq.'.$accountId, [
            'two_factor_secret' => null,
            'two_factor_enabled_at' => null,
            'two_factor_recovery_codes' => null,
        ]);
        if ($patchCode < 200 || $patchCode >= 300) {
            return response()->json(['error' => 'Gagal menonaktifkan 2FA: HTTP '.$patchCode], 502);
        }

        $this->audit->log('admin_2fa.disabled', ['account_id' => $accountId]);

        return response()->json(['status' => 'ok']);
    }

    public function status(): JsonResponse
    {
        $accountId = Session::get('admin_account_id');
        if (!$accountId) {
            return response()->json(['error' => 'Sesi tidak valid.'], 401);
        }

        [$code, $rows] = $this->supabase->rawRequest('GET', 'admin_accounts?id=eq.'.$accountId.'&select=two_factor_enabled_at&limit=1');
        $enabled = ($code >= 200 && $code < 300 && !empty($rows[0]['two_factor_enabled_at']));

        return response()->json(['status' => 'ok', 'enabled' => $enabled]);
    }
}
