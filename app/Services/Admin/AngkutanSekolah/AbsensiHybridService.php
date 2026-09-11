<?php

namespace App\Services\Admin\AngkutanSekolah;

use DateTime;
use DateTimeZone;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Port dari core/absensi_cache_helper.php. Model HYBRID untuk halaman Data
 * Absensi & laporan:
 *   - Data SEBELUM HARI INI → cache whole-table (24 jam, Cache facade,
 *     bukan `cache/absensi/*.json` seperti kode lama).
 *   - Data HARI INI → SELALU live langsung dari Supabase.
 * Filter & paginasi dikerjakan di PHP setelah data hybrid didapat, supaya
 * total & isi halaman tetap akurat walau sumbernya campuran cache+live.
 */
class AbsensiHybridService
{
    protected const TTL = 24 * 3600;

    protected function cacheKey(string $table): string
    {
        return 'absensi_whole_'.$table;
    }

    protected function fetchAllPaginated(string $table): ?array
    {
        $headers = [
            'apikey' => config('services.supabase.anon_key'),
            'Authorization' => 'Bearer '.config('services.supabase.anon_key'),
            'Accept' => 'application/json',
        ];
        $baseUrl = rtrim((string) config('services.supabase.url'), '/').'/rest/v1/'.rawurlencode($table)
            .'?select=*&order=waktu.asc';

        $all = [];
        $from = 0;
        $pageSize = 1000;

        while (true) {
            try {
                $response = Http::withHeaders($headers + [
                    'Range-Unit' => 'items',
                    'Range' => $from.'-'.($from + $pageSize - 1),
                ])->timeout(20)->get($baseUrl);
            } catch (\Throwable $e) {
                return $from === 0 ? null : $all;
            }

            if (!in_array($response->status(), [200, 206], true)) {
                return $from === 0 ? null : $all;
            }

            $rows = $response->json();
            if (!is_array($rows)) {
                return $from === 0 ? null : $all;
            }

            $all = array_merge($all, $rows);
            if (count($rows) < $pageSize) {
                break;
            }
            $from += $pageSize;
        }

        return $all;
    }

    protected function loadOrRefreshCache(string $table): array
    {
        $key = $this->cacheKey($table);
        $cached = Cache::get($key);
        if (is_array($cached)) {
            return $cached;
        }

        $fresh = $this->fetchAllPaginated($table);
        if ($fresh !== null) {
            Cache::put($key, $fresh, self::TTL);

            return $fresh;
        }

        return [];
    }

    /**
     * @return array<int,array>|null null kalau gagal total
     */
    protected function fetchRange(string $table, ?DateTime $start, ?DateTime $end): ?array
    {
        $headers = [
            'apikey' => config('services.supabase.anon_key'),
            'Authorization' => 'Bearer '.config('services.supabase.anon_key'),
            'Accept' => 'application/json',
        ];

        $params = ['select' => '*', 'order' => 'waktu.asc'];
        $extra = '';
        if ($start) {
            $extra .= '&waktu=gte.'.rawurlencode($start->format('Y-m-d\TH:i:sP'));
        }
        if ($end) {
            $extra .= '&waktu=lte.'.rawurlencode($end->format('Y-m-d\TH:i:sP'));
        }

        $baseUrl = rtrim((string) config('services.supabase.url'), '/').'/rest/v1/'.rawurlencode($table)
            .'?'.http_build_query($params).$extra;

        $all = [];
        $from = 0;
        $pageSize = 1000;

        while (true) {
            try {
                $response = Http::withHeaders($headers + [
                    'Range-Unit' => 'items',
                    'Range' => $from.'-'.($from + $pageSize - 1),
                ])->timeout(15)->get($baseUrl);
            } catch (\Throwable $e) {
                return $from === 0 ? null : $all;
            }

            if (!in_array($response->status(), [200, 206], true)) {
                return $from === 0 ? null : $all;
            }

            $rows = $response->json();
            if (!is_array($rows)) {
                return $from === 0 ? null : $all;
            }

            $all = array_merge($all, $rows);
            if (count($rows) < $pageSize) {
                break;
            }
            $from += $pageSize;
        }

        return $all;
    }

    public function todayStart(DateTimeZone $tz): DateTime
    {
        $now = new DateTime('now', $tz);

        return DateTime::createFromFormat('Y-m-d H:i:s', $now->format('Y-m-d').' 00:00:00', $tz);
    }

    /**
     * @param string[]|null $selectCols
     * @return array<int,array>
     */
    public function hybridRows(string $table, ?DateTime $start, ?DateTime $end, ?array $selectCols = null): array
    {
        $tz = new DateTimeZone('Asia/Jakarta');
        $todayStart = $this->todayStart($tz);

        $rows = [];

        $needPast = ($start === null) || ($start < $todayStart);
        if ($needPast) {
            $cacheUpperBound = ($end !== null && $end < $todayStart) ? $end : $todayStart;

            $cached = $this->loadOrRefreshCache($table);
            foreach ($cached as $r) {
                if (empty($r['waktu'])) {
                    continue;
                }
                try {
                    $wdt = new DateTime($r['waktu']);
                } catch (\Exception $e) {
                    continue;
                }
                $wdt->setTimezone($tz);

                if ($wdt >= $todayStart) {
                    continue;
                }
                if ($start !== null && $wdt < $start) {
                    continue;
                }
                if ($wdt >= $cacheUpperBound) {
                    continue;
                }

                $rows[] = $r;
            }
        }

        $needToday = ($end === null) || ($end >= $todayStart);
        if ($needToday) {
            $liveStart = ($start !== null && $start > $todayStart) ? $start : $todayStart;
            $liveRows = $this->fetchRange($table, $liveStart, $end);
            if (is_array($liveRows)) {
                $rows = array_merge($rows, $liveRows);
            }
        }

        if ($selectCols) {
            $rows = array_map(function ($row) use ($selectCols) {
                $out = [];
                foreach ($selectCols as $c) {
                    if (array_key_exists($c, $row)) {
                        $out[$c] = $row[$c];
                    }
                }

                return $out;
            }, $rows);
        }

        return $rows;
    }

    public function rowMatches(array $row, array $filters, string $idCol, DateTimeZone $tz): bool
    {
        if ($filters['transportasi'] !== '' && strcasecmp((string) ($row['transportasi'] ?? ''), $filters['transportasi']) !== 0) {
            return false;
        }
        if ($filters['trayek'] !== '' && (string) ($row['trayek'] ?? '') !== $filters['trayek']) {
            return false;
        }
        if ($filters['driver'] !== '' && stripos((string) ($row['plat_driver'] ?? ''), $filters['driver']) === false) {
            return false;
        }
        if ($filters['search'] !== '') {
            $needle = strtolower($filters['search']);
            $nama = strtolower((string) ($row['nama'] ?? ''));
            $idval = strtolower((string) ($row[$idCol] ?? ''));
            if (!str_contains($nama, $needle) && !str_contains($idval, $needle)) {
                return false;
            }
        }
        if ($filters['absen'] !== '' && !empty($row['waktu'])) {
            try {
                $wdt = new DateTime($row['waktu']);
                $wdt->setTimezone($tz);
                $h = (int) $wdt->format('G');
                if ($filters['absen'] === 'pagi' && !($h >= 4 && $h < 11)) {
                    return false;
                }
                if ($filters['absen'] === 'siang' && !($h >= 11 && $h < 20)) {
                    return false;
                }
            } catch (\Exception $e) {
                return false;
            }
        }

        return true;
    }

    /** @return array{0:?DateTime,1:?DateTime} */
    public function dayBounds(DateTimeZone $tz, int $y, int $m, int $d): array
    {
        $start = DateTime::createFromFormat('Y-m-d H:i:s', sprintf('%04d-%02d-%02d 00:00:00', $y, $m, $d), $tz);
        $end = DateTime::createFromFormat('Y-m-d H:i:s', sprintf('%04d-%02d-%02d 23:59:59', $y, $m, $d), $tz);

        return [$start, $end];
    }
}
