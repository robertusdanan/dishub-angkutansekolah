<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Supabase\ReferenceCacheProxyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Port dari core/reference_cache_proxy_endpoint.php (dipanggil lewat
 * pages/[modul]/partials/supabase_proxy_client.js - sekarang di /api/supabase-proxy).
 */
class ReferenceCacheProxyController extends Controller
{
    protected const ALLOWED_TABLES = [
        'driver_bus',
        'driver_mpu',
        'domisili',
        'sekolah',
        'list_tambangan',
        'trayek_bus',
        'trayek_mpu',
        'map',
        'user_RFID',
        'trayekwisata_titik',
        'trayekwisata_titik_galeri',
        'trayekwisata_bus',
        'trayekwisata_driver',
        'trayekwisata_trayek',
        'trayekwisata_trayek_titik',
        'trayekwisata_jadwal',
    ];

    public function __construct(protected ReferenceCacheProxyService $cache)
    {
    }

    public function show(Request $request): JsonResponse
    {
        $table = trim((string) $request->query('table', ''));
        if (!in_array($table, self::ALLOWED_TABLES, true)) {
            return response()->json(['error' => 'Tabel tidak diizinkan: '.$table], 400);
        }

        $category = $this->cache->categoryFor($table);
        $url = rtrim((string) config('services.supabase.url'), '/').'/rest/v1/'.rawurlencode($table).'?select=*';
        $headers = [
            'apikey' => config('services.supabase.anon_key'),
            'Authorization' => 'Bearer '.config('services.supabase.anon_key'),
            'Accept' => 'application/json',
        ];

        $result = $this->cache->fetchWithCache($table, $url, $headers, $this->cache->ttlFor($category));

        $rows = json_decode($result['body'], true);
        if (!is_array($rows)) {
            $rows = [];
        }

        if ($request->filled('id')) {
            $id = $request->query('id');
            $match = null;
            foreach ($rows as $r) {
                if (isset($r['id']) && (string) $r['id'] === (string) $id) {
                    $match = $r;
                    break;
                }
            }
            $response = response()->json($match);
        } else {
            // Filter col[] & val[] jika dikirim frontend
            $cols = $request->query('col');
            $vals = $request->query('val');
            if (is_array($cols) && is_array($vals) && count($cols) === count($vals)) {
                $rows = array_values(array_filter($rows, function ($row) use ($cols, $vals) {
                    foreach ($cols as $idx => $c) {
                        $v = $vals[$idx] ?? null;
                        if (!isset($row[$c]) || (string) $row[$c] !== (string) $v) {
                            return false;
                        }
                    }

                    return true;
                }));
            }

            // Sorting order=col.asc,col.desc jika dikirim
            if ($request->filled('order')) {
                $orderSpecs = explode(',', (string) $request->query('order'));
                usort($rows, function ($a, $b) use ($orderSpecs) {
                    foreach ($orderSpecs as $spec) {
                        $parts = explode('.', trim($spec));
                        $col = $parts[0];
                        $dir = strtolower($parts[1] ?? 'asc');
                        $valA = $a[$col] ?? '';
                        $valB = $b[$col] ?? '';
                        if ($valA == $valB) {
                            continue;
                        }
                        $cmp = strnatcasecmp((string) $valA, (string) $valB);

                        return $dir === 'desc' ? -$cmp : $cmp;
                    }

                    return 0;
                });
            }

            // Selection & Alias col (misal select=sopir:driver,nopol:plat)
            if ($request->filled('select')) {
                $selectRaw = (string) $request->query('select');
                $cols = array_filter(array_map('trim', explode(',', $selectRaw)));
                $rows = array_map(function ($row) use ($cols) {
                    $out = [];
                    foreach ($cols as $c) {
                        if (str_contains($c, ':')) {
                            [$alias, $orig] = explode(':', $c, 2);
                            $alias = trim($alias);
                            $orig = trim($orig);
                            if (array_key_exists($orig, $row)) {
                                $out[$alias] = $row[$orig];
                            }
                        } else {
                            if (array_key_exists($c, $row)) {
                                $out[$c] = $row[$c];
                            }
                        }
                    }

                    return $out;
                }, $rows);
            }

            $response = response()->json($rows);
        }

        return $response->header('X-SB-Cache', $result['source']);
    }
}
