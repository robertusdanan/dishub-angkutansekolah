<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AuditLogService;
use App\Services\Admin\TotpService;
use App\Services\Supabase\SupabaseClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

/**
 * Port dari admin/login.php dan admin/logout.php.
 * CATATAN MIGRASI: enforce_cfg_sync() (kill-switch) SENGAJA DIHAPUS.
 */
class AdminAuthController extends Controller
{
    public function __construct(
        protected SupabaseClient $supabase,
        protected AuditLogService $audit,
        protected TotpService $totp,
    ) {
    }

    /** GET/POST /admin/login */
    public function show(Request $request): View|RedirectResponse
    {
        $next = $request->query('next', '/');
        if (!is_string($next) || !str_starts_with($next, '/') || str_starts_with($next, '//')) {
            $next = '/';
        }

        $sudahLoginPengguna = Session::get('admin_logged_in') && Session::get('admin_role') === 'pengguna';

        if (Session::get('admin_logged_in') && !$sudahLoginPengguna) {
            $role = Session::get('admin_role');
            $dest = $role === 'guest' ? '/admin/data-absensi' : '/admin/';
            return redirect($dest);
        }

        if (Session::get('admin_2fa_pending_account_id')) {
            return redirect('/admin/login/verifikasi-2fa');
        }

        $error = '';
        if ($request->isMethod('post')) {
            $error = $this->attemptLogin($request);
            if ($error === '') {
                if (Session::get('admin_2fa_pending_account_id')) {
                    return redirect('/admin/login/verifikasi-2fa');
                }
                return redirect('/admin/');
            }
        }

        return view('admin.login', [
            'error' => $error,
            'sudahLoginPengguna' => $sudahLoginPengguna,
            'next' => $next,
            'oldUsername' => $request->old('username', ''),
        ]);
    }

    protected function attemptLogin(Request $request): string
    {
        $inputUser = trim((string) $request->input('username', ''));
        $inputPass = (string) $request->input('password', '');

        if (preg_match('/^[a-zA-Z0-9_.-]{3,50}$/', $inputUser)) {
            [$code, $rows] = $this->supabase->rawRequest('GET', 'admin_accounts?username=eq.'.rawurlencode($inputUser)
                .'&select=id,username,password_hash,full_name,is_active,admin_roles(id,role_key,role_name,level,permissions,is_active)'
                .'&limit=1');

            $account = ($code >= 200 && $code < 300 && is_array($rows) && count($rows) > 0) ? $rows[0] : null;

            if ($account && !empty($account['is_active']) && password_verify($inputPass, $account['password_hash'])) {
                $role = $account['admin_roles'] ?? null;
                if ($role && !empty($role['is_active'])) {
                    // Cek 2FA di query terpisah: kolom two_factor_* mungkin belum
                    // ada di schema Supabase (migration 002 belum dijalankan) —
                    // gagal query tidak boleh memblokir login.
                    $twoFactorAktif = false;
                    [$tCode, $tRows] = $this->supabase->rawRequest('GET', 'admin_accounts?id=eq.'.$account['id'].'&select=two_factor_secret,two_factor_enabled_at&limit=1');
                    if ($tCode >= 200 && $tCode < 300 && is_array($tRows) && !empty($tRows)
                        && !empty($tRows[0]['two_factor_enabled_at']) && !empty($tRows[0]['two_factor_secret'])) {
                        $twoFactorAktif = true;
                    }

                    if ($twoFactorAktif) {
                        $request->session()->regenerate();
                        Session::put('admin_2fa_pending_account_id', $account['id']);
                        Session::put('admin_2fa_pending_role', $role);
                        Session::put('admin_2fa_pending_username', $account['username']);
                        $this->audit->log('admin_login.password_ok_awaiting_2fa', ['username' => $account['username']]);
                        return '';
                    }

                    $this->completeLogin($request, $account, $role);
                    return '';
                }
            }
        }

        sleep(1);
        $this->audit->log('admin_login.failed', ['username_attempted' => $inputUser]);
        return 'Username atau password salah.';
    }

    protected function completeLogin(Request $request, array $account, array $role): void
    {
        $request->session()->regenerate();

        foreach (['akun_publik_id', 'akun_publik_email', 'akun_publik_nama', 'akun_publik_avatar'] as $k) {
            Session::forget($k);
        }

        Session::put('admin_logged_in', true);
        Session::put('admin_user', $account['username']);
        Session::put('admin_account_id', $account['id']);
        Session::put('admin_role', $role['role_key']);
        Session::put('admin_role_name', $role['role_name'] ?: $role['role_key']);
        Session::put('admin_role_level', (int) $role['level']);
        Session::put('admin_permissions', is_array($role['permissions']) ? $role['permissions'] : []);

        $this->supabase->rawRequest('PATCH', 'admin_accounts?id=eq.'.$account['id'], [
            'last_login_at' => gmdate('c'),
        ]);

        $this->audit->log('admin_login.success', ['username' => $account['username'], 'role' => $role['role_key']]);
    }

    /** GET /admin/login/verifikasi-2fa */
    public function showTwoFactorChallenge(Request $request): View|RedirectResponse
    {
        if (!Session::get('admin_2fa_pending_account_id')) {
            return redirect('/admin/login');
        }
        return view('admin.login-2fa', ['error' => '']);
    }

    /** POST /admin/login/verifikasi-2fa */
    public function verifyTwoFactorChallenge(Request $request): View|RedirectResponse
    {
        $accountId = Session::get('admin_2fa_pending_account_id');
        if (!$accountId) {
            return redirect('/admin/login');
        }

        $code = (string) $request->input('code', '');
        $recoveryCode = trim((string) $request->input('recovery_code', ''));

        [$sCode, $rows] = $this->supabase->rawRequest('GET', 'admin_accounts?id=eq.'.$accountId
            .'&select=id,username,is_active,two_factor_secret,two_factor_recovery_codes&limit=1');
        $account = ($sCode >= 200 && $sCode < 300 && !empty($rows)) ? $rows[0] : null;

        if (!$account || empty($account['is_active'])) {
            Session::forget(['admin_2fa_pending_account_id', 'admin_2fa_pending_role', 'admin_2fa_pending_username']);
            return redirect('/admin/login');
        }

        $ok = false;
        $usedRecoveryCode = null;

        if ($code !== '') {
            $ok = $this->totp->verify((string) $account['two_factor_secret'], $code);
        } elseif ($recoveryCode !== '') {
            $storedCodes = is_array($account['two_factor_recovery_codes']) ? $account['two_factor_recovery_codes'] : [];
            foreach ($storedCodes as $i => $hash) {
                if (password_verify(strtoupper($recoveryCode), $hash)) {
                    $ok = true;
                    $usedRecoveryCode = $i;
                    break;
                }
            }
        }

        if (!$ok) {
            sleep(1);
            $this->audit->log('admin_login.2fa_failed', ['username' => $account['username']]);
            return view('admin.login-2fa', ['error' => 'Kode tidak valid atau sudah kedaluwarsa.']);
        }

        if ($usedRecoveryCode !== null) {
            $storedCodes = $account['two_factor_recovery_codes'];
            unset($storedCodes[$usedRecoveryCode]);
            $this->supabase->rawRequest('PATCH', 'admin_accounts?id=eq.'.$accountId, [
                'two_factor_recovery_codes' => array_values($storedCodes),
            ]);
            $this->audit->log('admin_login.2fa_recovery_code_used', ['username' => $account['username']]);
        }

        $role = Session::pull('admin_2fa_pending_role');
        Session::forget(['admin_2fa_pending_account_id', 'admin_2fa_pending_username']);

        $this->completeLogin($request, $account, $role);
        return redirect('/admin/');
    }

    /** GET /admin/logout */
    public function logout(): RedirectResponse
    {
        $this->audit->log('admin_logout', ['username' => session('admin_user')]);
        Session::flush();
        return redirect('/admin/login');
    }
}
