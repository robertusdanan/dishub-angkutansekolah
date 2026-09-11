<?php

namespace App\Services\AbsenQr;

use App\Services\Supabase\SupabaseClient;
use DateTime;
use DateTimeZone;

/**
 * Port dari pages/absenqrcode/php/{cek,status}.php.
 */
class AbsenQrService
{
    public function __construct(protected SupabaseClient $supabase)
    {
    }

    /**
     * @return array{0:?string,1:string} [shift|null, tanggal]
     */
    public function shiftSekarang(): array
    {
        $now = new DateTime('now', new DateTimeZone('Asia/Jakarta'));
        $h = (int) $now->format('G');
        $inPagi = $h >= 4 && $h < 11;
        $inSiang = $h >= 11 && $h < 20;
        $shift = $inPagi ? 'pagi' : ($inSiang ? 'siang' : null);

        return [$shift, $now->format('Y-m-d')];
    }

    /**
     * Setara php/cek.php.
     *
     * @return array{0:int,1:array}
     */
    public function cek(array $payload): array
    {
        $email = (string) ($payload['email'] ?? '');
        $name = (string) ($payload['nama'] ?? '');
        if (!$email || !$name) {
            return [400, ['status' => 'error', 'message' => 'Email atau nama tidak tersedia']];
        }

        [$uCode, $uJson] = $this->supabase->select('user_QRCode', ['email' => 'eq.'.$email], 1);
        if ($uCode !== 200) {
            return [500, ['status' => 'error', 'message' => 'Gagal cek user Supabase (HTTP '.$uCode.')']];
        }

        if (empty($uJson)) {
            [$cCode, $cJson] = $this->supabase->insert('user_QRCode', ['email' => $email, 'nama' => $name]);
            if ($cCode < 200 || $cCode >= 300) {
                return [500, ['status' => 'error', 'message' => 'Gagal membuat user: HTTP '.$cCode]];
            }
            $userId = (is_array($cJson) && isset($cJson[0]['id'])) ? $cJson[0]['id'] : ($cJson['id'] ?? null);
        } else {
            $userId = $uJson[0]['id'];
        }

        if (!$userId) {
            return [500, ['status' => 'error', 'message' => 'Gagal mendapatkan user ID']];
        }

        [$shift, $tanggal] = $this->shiftSekarang();
        if ($shift === null) {
            return [400, ['status' => 'error', 'message' => 'Absen hanya jam 04:00–11:00 atau 11:00–20:00']];
        }
        $unikKey = $userId.'_'.$tanggal.'_'.$shift;

        $record = [
            'user_id' => $userId,
            'nama' => $name,
            'email' => $email,
            'transportasi' => $payload['transportasi'] ?? '',
            'trayek' => $payload['trayek'] ?? '',
            'plat_driver' => $payload['plat_driver'] ?? '',
            'domisili' => $payload['domisili'] ?? '',
            'sekolah' => $payload['sekolah'] ?? '',
            'jenis_kelamin' => $payload['jenis_kelamin'] ?? '',
            'waktu' => (new DateTime('now', new DateTimeZone('UTC')))->format(DateTime::ATOM),
            'unikKey' => $unikKey,
        ];

        [$sCode, $sJson] = $this->supabase->insert('absensi_QRCode', $record);

        $isDup = $sCode === 409
            || (isset($sJson['code']) && $sJson['code'] === '23505')
            || (isset($sJson['message']) && stripos((string) ($sJson['message'] ?? ''), 'duplicate') !== false);

        if ($isDup) {
            return [409, ['status' => 'error', 'message' => 'Kamu sudah absen pada sesi ini']];
        }
        if ($sCode < 200 || $sCode >= 300) {
            return [500, ['status' => 'error', 'message' => 'Gagal simpan absensi: HTTP '.$sCode]];
        }

        $newId = (is_array($sJson) && isset($sJson[0]['id'])) ? $sJson[0]['id'] : ($sJson['id'] ?? null);

        return [200, ['status' => 'ok', 'id' => $newId]];
    }

    /**
     * Setara php/status.php.
     *
     * @return array{0:int,1:array}
     */
    public function status(string $email): array
    {
        if (!$email) {
            return [400, ['status' => 'error', 'message' => 'Email tidak tersedia']];
        }

        [$uCode, $uJson] = $this->supabase->select('user_QRCode', ['email' => 'eq.'.$email], 1);
        if ($uCode !== 200) {
            return [500, ['status' => 'error', 'message' => 'Gagal cek user Supabase']];
        }
        if (empty($uJson)) {
            return [200, ['status' => 'no_user', 'message' => 'User belum terdaftar di sistem']];
        }
        $userId = $uJson[0]['id'];

        [$shift, $tanggal] = $this->shiftSekarang();

        [, $pagiRows] = $this->supabase->select('absensi_QRCode', ['unikKey' => 'eq.'.$userId.'_'.$tanggal.'_pagi'], 1);
        [, $siangRows] = $this->supabase->select('absensi_QRCode', ['unikKey' => 'eq.'.$userId.'_'.$tanggal.'_siang'], 1);
        $absenPagi = !empty($pagiRows);
        $absenSiang = !empty($siangRows);

        if ($shift === null) {
            return [200, [
                'status' => 'invalid_time',
                'message' => 'Absen hanya boleh jam 04:00-11:00 atau 11:00-20:00',
                'absenPagi' => $absenPagi,
                'absenSiang' => $absenSiang,
            ]];
        }

        return [200, [
            'status' => 'ok',
            'shift' => $shift,
            'absenPagi' => $absenPagi,
            'absenSiang' => $absenSiang,
        ]];
    }
}
