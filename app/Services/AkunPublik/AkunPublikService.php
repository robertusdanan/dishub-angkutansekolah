<?php

namespace App\Services\AkunPublik;

use App\Services\Supabase\SupabaseClient;
use Illuminate\Support\Facades\Session;

/**
 * Port dari core/akun_publik_helper.php. Dipakai lintas layanan publik
 * (Trayek Wisata, Balik Gratis, shell admin "Tiket Saya") - akan dipakai
 * lagi saat modul-modul tsb dimigrasikan di tahap berikutnya.
 */
class AkunPublikService
{
    public function __construct(protected SupabaseClient $supabase)
    {
    }

    public function isLogin(): bool
    {
        return (bool) Session::get('akun_publik_id');
    }

    public function id(): ?string
    {
        return Session::get('akun_publik_id');
    }

    /**
     * @return array{lengkap:bool, kurang:string[]}
     */
    public function cekKelengkapan(array $requiredFields): array
    {
        $profil = $this->profil();
        if ($profil === null) {
            return ['lengkap' => false, 'kurang' => $requiredFields];
        }

        $kurang = [];
        foreach ($requiredFields as $field) {
            $val = $profil[$field] ?? null;
            if ($val === null || $val === '') {
                $kurang[] = $field;
            }
        }

        return ['lengkap' => count($kurang) === 0, 'kurang' => $kurang];
    }

    public function profil(): ?array
    {
        if (!$this->isLogin()) {
            return null;
        }

        [$code, $rows] = $this->supabase->select('akun_publik', ['id' => 'eq.'.$this->id()], 1);

        if ($code >= 200 && $code < 300 && is_array($rows) && count($rows) > 0) {
            return $rows[0];
        }

        return null;
    }

    public function labelField(string $field): string
    {
        $labels = [
            'nik' => 'NIK',
            'no_kk' => 'Nomor Kartu Keluarga',
            'jenis_kelamin' => 'Jenis Kelamin',
            'foto_ktp_url' => 'Foto KTP',
            'foto_kk_url' => 'Foto Kartu Keluarga',
            'alamat_kabupaten' => 'Kabupaten',
            'alamat_kecamatan' => 'Kecamatan',
            'alamat_kode_pos' => 'Kode Pos',
            'alamat_lengkap' => 'Alamat Lengkap',
            'no_hp' => 'Nomor HP',
            'nama' => 'Nama',
        ];

        return $labels[$field] ?? $field;
    }

    public function validNik(string $nik): bool
    {
        return (bool) preg_match('/^\d{16}$/', $nik);
    }

    public function validHp(string $hp): bool
    {
        return (bool) preg_match('/^\d{9,14}$/', $hp);
    }

    /**
     * Rate limit berbasis session - port apa adanya dari akun_rate_limit().
     * Melempar RateLimitExceededException (bukan langsung echo+exit seperti
     * kode lama) supaya controller pemanggil yang menentukan respons JSON.
     *
     * @throws RateLimitExceededException
     */
    public function rateLimit(string $key, int $maxAttempts, int $windowSeconds): void
    {
        $sessKey = 'akun_rl_'.$key;
        $now = time();

        $attempts = array_values(array_filter(
            Session::get($sessKey, []),
            fn ($t) => $t > $now - $windowSeconds
        ));

        if (count($attempts) >= $maxAttempts) {
            throw new RateLimitExceededException('Terlalu banyak percobaan dalam waktu singkat. Coba lagi sebentar lagi.');
        }

        $attempts[] = $now;
        Session::put($sessKey, $attempts);
    }

    public function clearSession(): void
    {
        foreach (['akun_publik_id', 'akun_publik_email', 'akun_publik_nama', 'akun_publik_avatar', 'akun_oauth_state'] as $k) {
            Session::forget($k);
        }
    }
}
