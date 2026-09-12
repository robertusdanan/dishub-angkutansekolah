<?php

namespace App\Http\Controllers\AbsenRfid;

use App\Http\Controllers\Controller;
use App\Services\Supabase\SupabaseClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

/**
 * Port dari pages/absenrfid/pages/{login_listlink,logout_listlink}.php.
 *
 * CATATAN MIGRASI: kode lama pakai session PHP TERPISAH (cookie
 * `listlink_session`, benar-benar independen dari sesi admin panel utama)
 * supaya teknisi yang cuma butuh akses "List Link" tidak perlu login penuh
 * ke Admin. Laravel secara default satu sesi per aplikasi - di sini
 * disederhanakan jadi KEY sesi terpisah (`listlink_logged_in` dkk) di
 * DALAM sesi Laravel yang sama, bukan cookie session yang benar-benar
 * terpisah. Efeknya: kalau seseorang sudah login ke Admin panel utama di
 * browser yang sama, session ID-nya sama (bukan lagi dua session_id
 * berbeda) - tapi status login listlink tetap independen (key sendiri,
 * guard sendiri), jadi perilaku fungsionalnya tetap sama.
 */
class ListlinkAuthController extends Controller
{
    public function __construct(protected SupabaseClient $supabase)
    {
    }

    public function show(Request $request): View|RedirectResponse
    {
        if (Session::get('listlink_logged_in')) {
            return redirect('/absenrfid/listlink');
        }

        $error = '';
        if ($request->isMethod('post')) {
            $error = $this->attemptLogin($request);
            if ($error === '') {
                $redirect = Session::pull('listlink_redirect', '/absenrfid/listlink');

                return redirect($redirect);
            }
        }

        return view('absen-rfid.listlink-login', [
            'error' => $error,
            'oldUsername' => $request->old('username', ''),
        ]);
    }

    protected function attemptLogin(Request $request): string
    {
        $inputUser = trim((string) $request->input('username', ''));
        $inputPass = (string) $request->input('password', '');

        if (preg_match('/^[a-zA-Z0-9_.-]{3,50}$/', $inputUser)) {
            [$code, $rows] = $this->supabase->rawRequest('GET', 'admin_accounts?username=eq.'.rawurlencode($inputUser)
                .'&select=id,username,password_hash,is_active,admin_roles(role_key,permissions,is_active)&limit=1');
            $account = ($code >= 200 && $code < 300 && !empty($rows)) ? $rows[0] : null;

            if ($account && !empty($account['is_active']) && password_verify($inputPass, $account['password_hash'])) {
                $role = $account['admin_roles'] ?? null;

                if ($role && !empty($role['is_active'])) {
                    $roleKey = $role['role_key'] ?? '';
                    $perms = is_array($role['permissions'] ?? null) ? $role['permissions'] : [];
                    $canListlink = ($roleKey === 'superadmin') || !empty($perms['listlink']['access']);

                    if ($canListlink) {
                        $request->session()->regenerate();
                        Session::put('listlink_logged_in', true);
                        Session::put('listlink_user', $account['username']);
                        Session::put('listlink_role', $roleKey);

                        return '';
                    }

                    sleep(1);

                    return 'Login berhasil, tapi role "'.($roleKey ?: '-').'" Anda belum diberi izin akses List Link Absen Angkutan. Minta superadmin mengaktifkannya lewat menu Manajemen Role.';
                }

                sleep(1);

                return 'Akun Anda valid, tapi role akun ini sedang nonaktif. Hubungi superadmin.';
            }
        }

        sleep(1);

        return 'Username atau password salah.';
    }

    public function logout(): RedirectResponse
    {
        foreach (['listlink_logged_in', 'listlink_user', 'listlink_role'] as $k) {
            Session::forget($k);
        }

        return redirect('/absenrfid/login');
    }
}
