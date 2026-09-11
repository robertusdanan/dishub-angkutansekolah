<?php

namespace App\Services\AbsenRfid;

use App\Services\Supabase\SupabaseClient;

/**
 * Port dari pages/absenrfid/php/gps_write.php.
 */
class GpsTrackingService
{
    public const ALLOWED_TABLES = ['driver_bus', 'driver_mpu'];

    public function __construct(protected SupabaseClient $supabase)
    {
    }

    /**
     * @return array{0:int,1:array}
     */
    public function init(string $table, string $id): array
    {
        [$code, $rows] = $this->supabase->select($table, ['id' => 'eq.'.$id], 1);
        if ($code >= 300 || empty($rows)) {
            return [404, ['error' => 'Driver tidak ditemukan']];
        }
        $row = $rows[0];
        $trayekNama = $row['trayek'] ?? null;

        $codeMap = null;
        if ($trayekNama) {
            $trayekTable = $table === 'driver_bus' ? 'trayek_bus' : 'trayek_mpu';
            [, $trRows] = $this->supabase->select($trayekTable, ['nama' => 'eq.'.$trayekNama], 1);
            if (!empty($trRows)) {
                $idTrayek = $trRows[0]['id'];
                [, $mapRows] = $this->supabase->select('map', ['id_trayek' => 'eq.'.$idTrayek], 1);
                if (!empty($mapRows)) {
                    $codeMap = $mapRows[0]['code_map'] ?? null;
                }
            }
        }

        if ($codeMap !== null && $codeMap !== ($row['code_map'] ?? null)) {
            $this->supabase->update($table, ['code_map' => $codeMap], ['id' => 'eq.'.$id], true);
        }

        return [200, [
            'plat' => $row['plat'] ?? null,
            'driver' => $row['driver'] ?? null,
            'trayek' => $trayekNama,
            'code_map' => $codeMap,
        ]];
    }

    /**
     * @return array{0:int,1:array}
     */
    public function save(string $table, string $id, ?float $lat, ?float $lng): array
    {
        if ($lat === null || $lng === null || !is_finite($lat) || !is_finite($lng)) {
            return [400, ['error' => 'lat/lng tidak valid']];
        }
        if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
            return [400, ['error' => 'lat/lng di luar jangkauan']];
        }

        [$code] = $this->supabase->update($table, [
            'lat' => $lat,
            'lng' => $lng,
            'update_at' => gmdate('Y-m-d\TH:i:s\Z'),
        ], ['id' => 'eq.'.$id], true);

        if ($code >= 300) {
            return [500, ['error' => 'Gagal menyimpan lokasi']];
        }

        return [200, ['ok' => true]];
    }
}
