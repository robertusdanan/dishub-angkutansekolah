<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AkunPublik\AkunPublikService;
use App\Services\Supabase\SupabaseClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

/**
 * Port dari pages/akun/login.php, pages/akun/callback.php, pages/akun/logout.php.
 * Ini login akun publik terpusat (Google OAuth) - dipakai lintas layanan
 * (Trayek Wisata, Balik Gratis, "Tiket Saya" di shell admin).
 */
class AkunAuthController extends Controller
{
    public function __construct(
        protected AkunPublikService $akun,
        protected SupabaseClient $supabase,
    ) {
    }

    /**
     * GET /akun/masuk - setara login.php
     */
    public function redirectToGoogle(Request $request): RedirectResponse
    {
        $clientId = config('services.google.client_id');
        $redirectUri = config('services.google.redirect_uri_akun');

        if (empty($clientId) || empty($redirectUri)) {
            abort(500, 'Konfigurasi Google OAuth untuk akun pengguna belum diatur (GOOGLE_CLIENT_ID / GOOGLE_REDIRECT_URI_AKUN di .env).');
        }

        $next = $request->query('next', '/akun/saya');
        if (!is_string($next) || !str_starts_with($next, '/') || str_starts_with($next, '//')) {
            $next = '/akun/saya'; // cegah open-redirect
        }

        $state = base64_encode(json_encode(['next' => $next, 'nonce' => bin2hex(random_bytes(8))]));
        Session::put('akun_oauth_state', $state);

        $params = [
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'access_type' => 'online',
            'prompt' => 'select_account',
            'state' => $state,
        ];

        return redirect()->away('https://accounts.google.com/o/oauth2/v2/auth?'.http_build_query($params));
    }

    /**
     * GET /akun/callback - setara callback.php
     */
    public function callback(Request $request)
    {
        if ($request->has('error')) {
            return $this->authFail('Login dibatalkan atau ditolak oleh Google.');
        }

        if (!$request->has('code') || !$request->has('state')) {
            return $this->authFail('Permintaan tidak lengkap dari Google.');
        }

        $sessionState = Session::get('akun_oauth_state');
        if (empty($sessionState) || !hash_equals($sessionState, (string) $request->query('state'))) {
            return $this->authFail('Sesi login tidak valid (state mismatch). Silakan coba login ulang.');
        }
        Session::forget('akun_oauth_state');

        $stateData = json_decode(base64_decode((string) $request->query('state')), true);
        $next = (is_array($stateData) && !empty($stateData['next']) && str_starts_with($stateData['next'], '/') && !str_starts_with($stateData['next'], '//'))
            ? $stateData['next']
            : '/akun/saya';

        $tokenResponse = Http::asForm()->timeout(15)->post('https://oauth2.googleapis.com/token', [
            'code' => $request->query('code'),
            'client_id' => config('services.google.client_id'),
            'client_secret' => config('services.google.client_secret'),
            'redirect_uri' => config('services.google.redirect_uri_akun'),
            'grant_type' => 'authorization_code',
        ]);
        $tokenData = $tokenResponse->json();

        if (empty($tokenData['access_token'])) {
            return $this->authFail('Gagal mendapatkan akses dari Google. Coba beberapa saat lagi.');
        }

        $userResponse = Http::withToken($tokenData['access_token'])
            ->timeout(15)
            ->get('https://www.googleapis.com/oauth2/v2/userinfo');
        $userData = $userResponse->json();

        if (empty($userData['id']) || empty($userData['email'])) {
            return $this->authFail('Gagal mengambil data akun Google Anda.');
        }

        $googleSub = (string) $userData['id'];
        $email = (string) $userData['email'];
        $namaGoogle = (string) ($userData['name'] ?? explode('@', $email)[0]);

        [$code, $existing] = $this->supabase->select('akun_publik', ['google_sub' => 'eq.'.$googleSub], 1);

        $akunBaru = false;

        if ($code >= 200 && $code < 300 && is_array($existing) && count($existing) > 0) {
            $akunData = $existing[0];

            if ($akunData['email'] !== $email) {
                $this->supabase->update('akun_publik', ['email' => $email, 'updated_at' => date('c')], ['id' => 'eq.'.$akunData['id']]);
            }
        } else {
            [$insCode, $insResult] = $this->supabase->insert('akun_publik', [
                'google_sub' => $googleSub,
                'email' => $email,
                'nama' => $namaGoogle,
            ]);

            if ($insCode < 200 || $insCode >= 300 || !is_array($insResult) || count($insResult) === 0) {
                return $this->authFail('Gagal menyiapkan akun Anda di sistem. Coba lagi.');
            }

            $akunData = $insResult[0];
            $akunBaru = true;
        }

        $request->session()->regenerate();

        Session::put('akun_publik_id', $akunData['id']);
        Session::put('akun_publik_email', $akunData['email']);
        Session::put('akun_publik_nama', $akunData['nama']);
        Session::put('akun_publik_avatar', $userData['picture'] ?? '');

        $currentAdminRole = Session::get('admin_role', '');
        if (!Session::get('admin_logged_in') || $currentAdminRole === 'pengguna') {
            Session::put('admin_logged_in', true);
            Session::put('admin_user', $akunData['nama']);
            Session::put('admin_role', 'pengguna');
            Session::put('admin_role_name', 'Pengguna');
        }

        if ($akunBaru) {
            return redirect('/akun/saya?'.http_build_query(['next' => $next, 'welcome' => 1]));
        }

        return redirect($next);
    }

    /**
     * GET /akun/keluar - setara logout.php
     */
    public function logout(Request $request): RedirectResponse
    {
        $this->akun->clearSession();

        if (Session::get('admin_role') === 'pengguna') {
            foreach (['admin_logged_in', 'admin_user', 'admin_role', 'admin_role_name'] as $k) {
                Session::forget($k);
            }
        }

        $request->session()->regenerate();

        $next = $request->query('next', '/');
        if (!is_string($next) || !str_starts_with($next, '/') || str_starts_with($next, '//')) {
            $next = '/';
        }

        return redirect($next);
    }

    protected function authFail(string $message)
    {
        return response()->view('auth.akun-gagal', ['message' => $message], 400);
    }
}
