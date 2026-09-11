<?php

namespace App\Services\AbsenRfid;

use App\Services\Supabase\SupabaseClient;
use DateTime;
use DateTimeZone;

/**
 * Port dari logic bersama pages/absenrfid/php/{cek,cek_rfid,gps_write}.php.
 */
class AbsenRfidService
{
    public function __construct(protected SupabaseClient $supabase)
    {
    }

    /**
     * Jam absen: 04:00-11:00 (pagi) atau 11:00-20:00 (siang) waktu Asia/Jakarta.
     *
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

    protected function waktuUtcIso(): string
    {
        return (new DateTime('now', new DateTimeZone('UTC')))->format(DateTime::ATOM);
    }

    /**
     * Setara php/cek.php — absen manual (input nama, tanpa kartu RFID),
     * auto-membuat baris user_RFID kalau namanya belum pernah tercatat.
     *
     * @return array{0:int,1:array} [http_status, body]
     */
    public function cekManual(array $payload): array
    {
        $name = trim((string) ($payload['nama'] ?? ''));
        if ($name === '') {
            return [400, ['status' => 'error', 'message' => 'Nama tidak boleh kosong']];
        }

        [$uCode, $uJson] = $this->supabase->select('user_RFID', ['nama' => 'eq.'.$name], 1);
        if ($uCode !== 200) {
            return [500, ['status' => 'error', 'message' => 'Gagal cek user Supabase']];
        }

        if (empty($uJson)) {
            [$cCode, $cJson] = $this->supabase->insert('user_RFID', [
                'nama' => $name,
                'jenis_kelamin' => $payload['jenis_kelamin'] ?? '',
                'domisili' => $payload['domisili'] ?? '',
                'sekolah' => $payload['sekolah'] ?? '',
            ]);
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

        $updateData = [];
        foreach (['domisili', 'sekolah', 'jenis_kelamin'] as $f) {
            if (!empty($payload[$f])) {
                $updateData[$f] = $payload[$f];
            }
        }
        if (!empty($updateData)) {
            $this->supabase->update('user_RFID', $updateData, ['id' => 'eq.'.$userId], true);
        }

        [$shift, $tanggal] = $this->shiftSekarang();
        if ($shift === null) {
            return [400, ['status' => 'error', 'message' => 'Absen hanya jam 04:00-11:00 atau 11:00-20:00']];
        }
        $unikKey = $userId.'_'.$tanggal.'_'.$shift;

        $record = [
            'user' => $userId,
            'transportasi' => $payload['transportasi'] ?? '',
            'trayek' => $payload['trayek'] ?? '',
            'plat_driver' => $payload['plat_driver'] ?? '',
            'domisili' => $payload['domisili'] ?? '',
            'sekolah' => $payload['sekolah'] ?? '',
            'jenis_kelamin' => $payload['jenis_kelamin'] ?? '',
            'nama' => $name,
            'waktu' => $this->waktuUtcIso(),
            'shift' => $shift,
            'unikKey' => $unikKey,
            'metode' => 'manual',
        ];

        [$sCode, $sJson] = $this->supabase->insert('absensi_RFID', $record);

        if ($this->isDuplicate($sCode, $sJson)) {
            return [409, ['status' => 'error', 'message' => 'Kamu sudah absen pada sesi ini']];
        }
        if ($sCode < 200 || $sCode >= 300) {
            return [500, ['status' => 'error', 'message' => 'Gagal simpan absensi: HTTP '.$sCode]];
        }

        $newId = (is_array($sJson) && isset($sJson[0]['id'])) ? $sJson[0]['id'] : ($sJson['id'] ?? null);

        return [200, ['status' => 'ok', 'id' => $newId]];
    }

    /**
     * Setara php/cek_rfid.php — absen via kartu RFID (NIK sudah pasti
     * terdaftar di user_RFID, TIDAK auto-membuat user baru seperti cekManual).
     *
     * @return array{0:int,1:array}
     */
    public function cekRfid(array $payload): array
    {
        $nik = trim((string) ($payload['nik'] ?? ''));
        $nama = trim((string) ($payload['nama'] ?? ''));
        if (!$nik || !$nama) {
            return [400, ['status' => 'error', 'message' => 'NIK atau nama tidak tersedia']];
        }

        [$shift, $tanggal] = $this->shiftSekarang();
        if ($shift === null) {
            return [400, ['status' => 'error', 'message' => 'Di luar jam absen (04:00-11:00 atau 11:00-20:00)']];
        }
        $unikKey = $nik.'_'.$tanggal.'_'.$shift;

        [$dc, $dj] = $this->supabase->select('absensi_RFID', ['unikKey' => 'eq.'.$unikKey], 1);
        if ($dc === 200 && !empty($dj)) {
            return [200, ['status' => 'duplicate', 'message' => 'Sudah absen sesi ini']];
        }

        [$uc, $uj] = $this->supabase->select('user_RFID', ['nik' => 'eq.'.$nik], 1);
        if ($uc !== 200 || !is_array($uj) || count($uj) === 0) {
            return [404, [
                'status' => 'error',
                'message' => 'Siswa tidak ditemukan (NIK: '.$nik.')',
                'debug' => ['http_code' => $uc, 'rows' => $uj],
            ]];
        }
        $userRow = $uj[0];

        $record = [
            'nik' => $nik,
            'nama' => $userRow['nama'] ?? $nama,
            'jenis_kelamin' => $userRow['jenis_kelamin'] ?? '',
            'domisili' => $userRow['domisili'] ?? '',
            'sekolah' => $userRow['sekolah'] ?? '',
            'transportasi' => $payload['transportasi'] ?? '',
            'trayek' => $payload['trayek'] ?? '',
            'plat_driver' => $payload['plat_driver'] ?? '',
            'waktu' => $this->waktuUtcIso(),
            'shift' => $shift,
            'unikKey' => $unikKey,
            'metode' => 'rfid',
        ];

        [$sc, $sj] = $this->supabase->insert('absensi_RFID', $record);

        if ($this->isDuplicate($sc, $sj)) {
            return [200, ['status' => 'duplicate', 'message' => 'Sudah absen sesi ini']];
        }
        if ($sc < 200 || $sc >= 300) {
            return [500, [
                'status' => 'error',
                'message' => "Gagal simpan: HTTP {$sc}",
                'debug' => ['supabase_response' => $sj, 'record_sent' => $record],
            ]];
        }

        $newId = (is_array($sj) && isset($sj[0]['id'])) ? $sj[0]['id'] : ($sj['id'] ?? null);

        return [200, ['status' => 'ok', 'id' => $newId]];
    }

    protected function isDuplicate(int $code, mixed $body): bool
    {
        return $code === 409
            || (isset($body['code']) && $body['code'] === '23505')
            || (isset($body['message']) && str_contains((string) $body['message'], 'duplicate'));
    }
}
