<?php

namespace App\Services\Admin;

use App\Services\Supabase\SupabaseClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class AdminSessionManager
{
    public function __construct(
        protected SupabaseClient $supabase
    ) {
    }

    /**
     * Hash ringkas password untuk verifikasi konsistensi sesi.
     */
    public static function hashPasswordSignature(?string $passwordHash): string
    {
        return $passwordHash ? hash('sha256', $passwordHash) : '';
    }

    /**
     * Catat tanda tangan auth ke session dan cache lokal.
     */
    public function recordLogin(string $accountId, ?string $passwordHash): void
    {
        $sig = self::hashPasswordSignature($passwordHash);
        Session::put('admin_auth_hash', $sig);

        Cache::put('admin_acc_meta_'.$accountId, [
            'is_active' => true,
            'auth_hash' => $sig,
            'role_active' => true,
        ], now()->addMinutes(10));
    }

    /**
     * Validasi apakah sesi admin yang sedang berjalan masih sah.
     * Cek apakah:
     * 1. Akun masih ada dan aktif di Supabase.
     * 2. Role masih aktif.
     * 3. Password belum diubah sejak login terakhir.
     */
    public function isValidAdminSession(): bool
    {
        if (!Session::get('admin_logged_in')) {
            return false;
        }

        $accountId = Session::get('admin_account_id');
        if (!$accountId || !preg_match('/^[0-9a-fA-F-]{36}$/', $accountId)) {
            return false;
        }

        $cachedMeta = Cache::remember('admin_acc_meta_'.$accountId, 10, function () use ($accountId) {
            [$code, $rows] = $this->supabase->rawRequest(
                'GET',
                'admin_accounts?id=eq.'.$accountId.'&select=id,is_active,password_hash,admin_roles(role_key,role_name,level,permissions,is_active)&limit=1'
            );

            if ($code < 200 || $code >= 300 || empty($rows) || !is_array($rows)) {
                return null;
            }

            $acc = $rows[0];
            $roleData = $acc['admin_roles'] ?? [];
            $roleActive = !empty($roleData['is_active']);

            return [
                'is_active' => !empty($acc['is_active']),
                'auth_hash' => self::hashPasswordSignature($acc['password_hash'] ?? null),
                'role_active' => $roleActive,
                'role' => $roleData,
            ];
        });

        if (!$cachedMeta || empty($cachedMeta['is_active']) || empty($cachedMeta['role_active'])) {
            return false;
        }

        $sessionHash = Session::get('admin_auth_hash');
        if ($sessionHash && $sessionHash !== $cachedMeta['auth_hash']) {
            return false;
        }

        // Sinkronkan permissions dan role data terbaru jika ada pembaruan di database
        if (!empty($cachedMeta['role']) && is_array($cachedMeta['role'])) {
            $r = $cachedMeta['role'];
            if (isset($r['permissions']) && is_array($r['permissions'])) {
                Session::put('admin_permissions', $r['permissions']);
            }
            if (!empty($r['role_key'])) {
                Session::put('admin_role', $r['role_key']);
            }
            if (isset($r['role_name'])) {
                Session::put('admin_role_name', $r['role_name'] ?: ($r['role_key'] ?? ''));
            }
            if (isset($r['level'])) {
                Session::put('admin_role_level', (int) $r['level']);
            }
        }

        return true;
    }

    /**
     * Hancurkan semua file sesi di server milik $accountId.
     * Jika $exceptSessionId diberikan, sesi pemanggil tidak dihancurkan.
     */
    public function revokeAccountSessions(string $accountId, ?string $exceptSessionId = null): int
    {
        Cache::forget('admin_acc_meta_'.$accountId);

        $sessionPath = storage_path('framework/sessions');
        if (!is_dir($sessionPath)) {
            return 0;
        }

        $revokedCount = 0;
        $files = @glob($sessionPath.'/*') ?: [];

        foreach ($files as $file) {
            $sessId = basename($file);
            if ($sessId === '.gitignore' || ($exceptSessionId && $sessId === $exceptSessionId)) {
                continue;
            }

            $content = @file_get_contents($file);
            if (!$content || strpos($content, $accountId) === false) {
                continue;
            }

            $data = @unserialize($content);
            if (is_array($data) && ($data['admin_account_id'] ?? null) === $accountId) {
                @unlink($file);
                $revokedCount++;
            }
        }

        return $revokedCount;
    }
}
