<?php

namespace App\Services\AbsenRfid;

use App\Services\Supabase\SupabaseClient;

/**
 * Menjaga kolom driver_bus.code_map / driver_mpu.code_map tetap sinkron.
 *
 * Kenapa perlu: halaman peta publik (rute_map.js, peta_gabungan.js) mencari
 * posisi driver lewat filter `driver_*.code_map = map.code_map`. Tapi kolom
 * code_map di tabel driver TIDAK diisi saat admin menambah/mengedit driver di
 * halaman Update Data - hanya diisi oleh GpsTrackingService::init(), yaitu
 * ketika driver itu sendiri membuka halaman GPS-nya. Akibatnya driver yang
 * datanya baru dibuat (atau trayeknya baru diganti) tidak pernah muncul di
 * peta, dan console memunculkan "[driver] Tidak ada data driver untuk
 * codeMap: ...".
 *
 * Rantai relasinya: driver.trayek (nama) -> trayek_*.id -> map.code_map.
 *
 * syncAll() dipanggil otomatis setiap kali admin menulis ke tabel
 * driver_bus/driver_mpu/map/trayek_bus/trayek_mpu, jadi tidak ada lagi
 * langkah manual dan data tidak bisa "bocor" tidak sinkron.
 */
class DriverCodeMapSync
{
    /** @var array<string,string> tabel driver => tabel trayek pasangannya */
    protected const PAIRS = [
        'driver_bus' => 'trayek_bus',
        'driver_mpu' => 'trayek_mpu',
    ];

    public function __construct(protected SupabaseClient $supabase)
    {
    }

    /**
     * @return int jumlah baris driver yang diperbaiki
     */
    public function syncAll(): int
    {
        $fixed = 0;

        foreach (self::PAIRS as $driverTable => $trayekTable) {
            // Peta nama trayek => code_map, dibangun dari tabel map.
            $codeMapByTrayekNama = $this->codeMapByTrayekNama($trayekTable);

            [$code, $drivers] = $this->supabase->rawRequest(
                'GET',
                $driverTable.'?select=id,trayek,code_map&limit=5000'
            );
            if ($code < 200 || $code >= 300 || !is_array($drivers)) {
                continue;
            }

            foreach ($drivers as $d) {
                $expected = $codeMapByTrayekNama[$d['trayek'] ?? ''] ?? null;
                if ($expected === null || $expected === ($d['code_map'] ?? null)) {
                    continue;
                }
                $this->supabase->update($driverTable, ['code_map' => $expected], ['id' => 'eq.'.$d['id']], true);
                $fixed++;
            }
        }

        return $fixed;
    }

    /**
     * @return array<string,string> nama trayek => code_map
     */
    protected function codeMapByTrayekNama(string $trayekTable): array
    {
        $trayeks = $this->fetchAll($trayekTable.'?select=id,nama');
        $maps = $this->fetchAll('map?select=id_trayek,code_map');

        $codeMapByIdTrayek = [];
        foreach ($maps as $m) {
            if (!empty($m['id_trayek']) && !empty($m['code_map'])) {
                $codeMapByIdTrayek[$m['id_trayek']] = $m['code_map'];
            }
        }

        $out = [];
        foreach ($trayeks as $t) {
            if (!empty($t['nama']) && isset($codeMapByIdTrayek[$t['id']])) {
                $out[$t['nama']] = $codeMapByIdTrayek[$t['id']];
            }
        }

        return $out;
    }

    /**
     * PostgREST memotong hasil di baris ke-1000 secara diam-diam, jadi dibaca
     * per halaman. $pathWithQuery tidak boleh sudah mengandung limit/offset.
     *
     * @return array<int,array<string,mixed>>
     */
    protected function fetchAll(string $pathWithQuery, int $pageSize = 1000): array
    {
        $all = [];
        $sep = str_contains($pathWithQuery, '?') ? '&' : '?';

        for ($offset = 0; ; $offset += $pageSize) {
            [$code, $rows] = $this->supabase->rawRequest(
                'GET',
                $pathWithQuery.$sep.'limit='.$pageSize.'&offset='.$offset
            );
            if ($code < 200 || $code >= 300 || !is_array($rows)) {
                break;
            }
            $all = array_merge($all, $rows);
            if (count($rows) < $pageSize) {
                break;
            }
        }

        return $all;
    }
}
