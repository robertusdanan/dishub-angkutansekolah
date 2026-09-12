<?php

namespace App\Services\Admin\TrayekWisata;

use App\Services\Supabase\ReferenceCacheProxyService;
use App\Services\Supabase\SupabaseClient;
use DateTime;

/**
 * Port dari admin/api/trayekwisata/copy.php.
 *
 * "Minggu ke-N" dihitung dengan definisi TETAP (lihat weekOfMonth()):
 * minggu Senin-Minggu, dipakai KONSISTEN di seluruh modul Trayek Wisata
 * (planner, dashboard, fitur salin ini) - supaya "Minggu ke-2 Januari" di
 * mana pun di aplikasi selalu merujuk ke rentang tanggal yang sama.
 */
class TrayekWisataCopyService
{
    public function __construct(
        protected SupabaseClient $supabase,
        protected ReferenceCacheProxyService $refCache,
    ) {
    }

    public function weekOfMonth(int $y, int $m, int $d): int
    {
        $first = new DateTime(sprintf('%04d-%02d-01', $y, $m));
        $firstWeekday = (int) $first->format('N');

        return (int) ceil(($d + $firstWeekday - 1) / 7);
    }

    /** @return array<string,string> ['SABTU' => 'Y-m-d', 'MINGGU' => 'Y-m-d'] */
    public function datesForWeek(int $y, int $m, int $wk): array
    {
        $out = [];
        $daysInMonth = (int) (new DateTime(sprintf('%04d-%02d-01', $y, $m)))->format('t');
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $dt = new DateTime(sprintf('%04d-%02d-%02d', $y, $m, $d));
            $n = (int) $dt->format('N');
            if ($n !== 6 && $n !== 7) {
                continue;
            }
            if ($this->weekOfMonth($y, $m, $d) !== $wk) {
                continue;
            }
            $out[$n === 6 ? 'SABTU' : 'MINGGU'] = $dt->format('Y-m-d');
        }

        return $out;
    }

    /**
     * @return array{created:int,skipped:string[],errors:string[]}
     */
    public function copy(array $source, string $mode, array $targets): array
    {
        [$code, $sourceRows] = $this->supabase->rawRequest('GET', sprintf(
            'trayekwisata_jadwal?tahun=eq.%d&bulan=eq.%d&minggu_ke=eq.%d&select=*',
            (int) $source['tahun'], (int) $source['bulan'], (int) $source['minggu_ke']
        ));
        if ($code < 200 || $code >= 300 || !is_array($sourceRows)) {
            throw new \RuntimeException('Gagal mengambil jadwal sumber dari Supabase.', 502);
        }
        if (count($sourceRows) === 0) {
            throw new \RuntimeException('Tidak ada jadwal pada minggu sumber tersebut untuk disalin.', 404);
        }

        $targetWeeks = [];
        if ($mode === 'months') {
            foreach ($targets as $t) {
                if (empty($t['tahun']) || empty($t['bulan'])) {
                    continue;
                }
                $targetWeeks[] = ['tahun' => (int) $t['tahun'], 'bulan' => (int) $t['bulan'], 'minggu_ke' => (int) $source['minggu_ke']];
            }
        } else {
            foreach ($targets as $t) {
                if (empty($t['tahun']) || empty($t['bulan']) || !isset($t['minggu_ke'])) {
                    continue;
                }
                $targetWeeks[] = ['tahun' => (int) $t['tahun'], 'bulan' => (int) $t['bulan'], 'minggu_ke' => (int) $t['minggu_ke']];
            }
        }

        $created = 0;
        $skipped = [];
        $errors = [];

        foreach ($targetWeeks as $tw) {
            $dateMap = $this->datesForWeek($tw['tahun'], $tw['bulan'], $tw['minggu_ke']);
            if (empty($dateMap)) {
                $skipped[] = "Minggu ke-{$tw['minggu_ke']} bulan {$tw['bulan']}/{$tw['tahun']} tidak ditemukan (di luar rentang bulan).";

                continue;
            }

            foreach ($sourceRows as $row) {
                $hari = $row['hari'];
                if (!isset($dateMap[$hari])) {
                    $skipped[] = "{$row['hari']} tidak ada pada minggu ke-{$tw['minggu_ke']} bulan {$tw['bulan']}/{$tw['tahun']}, trayek dilewati.";

                    continue;
                }
                $targetDate = $dateMap[$hari];

                [, $dupRows] = $this->supabase->rawRequest('GET', sprintf(
                    'trayekwisata_jadwal?trayek_id=eq.%s&tanggal=eq.%s&jam_berangkat=eq.%s&select=id',
                    rawurlencode($row['trayek_id']), rawurlencode($targetDate), rawurlencode($row['jam_berangkat'])
                ));
                if (is_array($dupRows) && count($dupRows) > 0) {
                    $skipped[] = "Trayek sudah ada di {$targetDate} jam {$row['jam_berangkat']}, dilewati.";

                    continue;
                }

                [$insCode, $insResult] = $this->supabase->rawRequest('POST', 'trayekwisata_jadwal', [
                    'trayek_id' => $row['trayek_id'],
                    'bus_id' => $row['bus_id'],
                    'driver_id' => $row['driver_id'],
                    'tanggal' => $targetDate,
                    'minggu_ke' => $tw['minggu_ke'],
                    'hari' => $hari,
                    'jam_berangkat' => $row['jam_berangkat'],
                    'jam_pulang' => $row['jam_pulang'],
                    'kuota_total' => $row['kuota_total'],
                    'kuota_terisi' => 0,
                    'status' => 'aktif',
                ]);
                if ($insCode >= 200 && $insCode < 300) {
                    $created++;
                } else {
                    $errors[] = "Gagal menyalin ke {$targetDate}: ".json_encode($insResult);
                }
            }
        }

        if ($created > 0) {
            $this->refCache->invalidate('trayekwisata_jadwal');
        }

        return ['created' => $created, 'skipped' => $skipped, 'errors' => $errors];
    }
}
