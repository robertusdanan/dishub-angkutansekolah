<?php

namespace App\Services\BalikGratis;

use App\Services\Supabase\SupabaseClient;

/**
 * Port dari pages/balikgratis/inc/bg_helpers.php.
 */
class BalikGratisService
{
    public function __construct(protected SupabaseClient $supabase)
    {
    }

    public function tahunIni(): int
    {
        return (int) date('Y');
    }

    public function validNik(string $nik): bool
    {
        return (bool) preg_match('/^\d{16}$/', $nik);
    }

    public function validKk(string $kk): bool
    {
        return (bool) preg_match('/^\d{16}$/', $kk);
    }

    public function validHp(string $hp): bool
    {
        return (bool) preg_match('/^\d{9,14}$/', $hp);
    }

    /**
     * Fail-CLOSED (beda dari MenuLayananService yang fail-open): kalau
     * admin belum pernah membuka tahun ini sama sekali, publik memang
     * BELUM BOLEH mendaftar — bukan bug, ini keputusan desain yang
     * dipertahankan apa adanya dari kode lama.
     */
    public function pengaturanTahun(int $tahun): array
    {
        [$code, $rows] = $this->supabase->rawRequest('GET', 'balikgratis_pengaturan?tahun=eq.'.$tahun.'&select=*&limit=1');

        if ($code >= 200 && $code < 300 && is_array($rows) && count($rows) > 0) {
            return $rows[0];
        }

        return [
            'tahun' => $tahun,
            'kuota' => 0,
            'status' => 'tutup',
            'dibuka_at' => null,
            'ditutup_at' => null,
        ];
    }

    public function terisiTahun(int $tahun): int
    {
        [$code, $rows] = $this->supabase->rawRequest('GET', 'balikgratis_pemesanan?tahun=eq.'.$tahun.'&status=neq.dibatalkan&select=id');

        return ($code >= 200 && $code < 300 && is_array($rows)) ? count($rows) : 0;
    }

    /**
     * Nomor tiket unik: BG-{tahun}-{6 digit acak}, dicek sampai benar-benar
     * belum dipakai (maks. 8 percobaan, lalu fallback pakai komponen waktu).
     */
    public function generateNomorTiket(int $tahun): string
    {
        for ($i = 0; $i < 8; $i++) {
            $candidate = 'BG-'.$tahun.'-'.str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            [$code, $rows] = $this->supabase->rawRequest('GET', 'balikgratis_pemesanan?nomor_tiket=eq.'.$candidate.'&select=id&limit=1');
            if ($code >= 200 && $code < 300 && is_array($rows) && count($rows) === 0) {
                return $candidate;
            }
        }

        return 'BG-'.$tahun.'-'.substr((string) time(), -6);
    }

    public function generateQrToken(): string
    {
        return bin2hex(random_bytes(16));
    }
}
