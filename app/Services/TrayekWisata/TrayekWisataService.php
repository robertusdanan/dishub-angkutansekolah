<?php

namespace App\Services\TrayekWisata;

use App\Services\AkunPublik\AkunPublikService;
use App\Services\Supabase\SupabaseClient;

/**
 * Port dari pages/trayekwisata/inc/tw_helpers.php.
 */
class TrayekWisataService
{
    public function __construct(
        protected AkunPublikService $akun,
        protected SupabaseClient $supabase,
    ) {
    }

    public function isLoggedIn(): bool
    {
        return $this->akun->isLogin();
    }

    /**
     * Aturan inti Trayek Wisata: 1 NIK hanya boleh 1 tiket / 4 minggu (28
     * hari), berlaku utk pemilik akun & semua anggota keluarga, LINTAS akun
     * (dicek murni dari NIK-nya, bukan siapa pemesannya) — mencegah celah
     * "Akun A daftarkan NIK B, lalu NIK B bikin akun sendiri".
     *
     * @return array{blocked:bool, tanggal_terakhir:?string}
     */
    public function cekNik4Minggu(string $nik): array
    {
        $cutoff = date('Y-m-d', strtotime('-28 days'));
        [$code, $rows] = $this->supabase->rawRequest('GET', 'trayekwisata_pemesanan_nik?nik=eq.'.rawurlencode($nik)
            .'&tanggal_trayek=gte.'.$cutoff.'&select=tanggal_trayek&order=tanggal_trayek.desc&limit=1');

        if ($code >= 200 && $code < 300 && is_array($rows) && count($rows) > 0) {
            return ['blocked' => true, 'tanggal_terakhir' => $rows[0]['tanggal_trayek']];
        }

        return ['blocked' => false, 'tanggal_terakhir' => null];
    }

    public function validNik(string $nik): bool
    {
        return (bool) preg_match('/^\d{11,14}$/', $nik) || (bool) preg_match('/^\d{16}$/', $nik);
    }

    public function validHp(string $hp): bool
    {
        return (bool) preg_match('/^\d{9,14}$/', $hp);
    }
}
