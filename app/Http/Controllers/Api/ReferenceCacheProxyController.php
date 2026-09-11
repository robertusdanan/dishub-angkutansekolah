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
    // CATATAN MIGRASI — BUG DIPERBAIKI: di kode lama (core/reference_cache_
    // proxy_endpoint.php), 'list_tambangan' punya kategori cache sendiri
    // (reference_table_cache_category()) tapi TIDAK PERNAH ditambahkan ke
    // $allowed_tables endpoint-nya. Akibatnya halaman ASDP (yang fetch
    // table=list_tambangan) selalu gagal "Tabel tidak diizinkan" di versi
    // PHP native. Ditambahkan di sini supaya peta ASDP benar-benar
    // menampilkan data. Beri tahu saya kalau perilaku lama (gagal) justru
    // yang diinginkan.
    protected const ALLOWED_TABLES = ['driver_bus', 'driver_mpu', 'domisili', 'sekolah', 'list_tambangan'];

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

        $response = null;

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
            if ($request->filled('select')) {
                $cols = array_filter(array_map('trim', explode(',', (string) $request->query('select'))));
                $rows = array_map(function ($row) use ($cols) {
                    $out = [];
                    foreach ($cols as $c) {
                        if (array_key_exists($c, $row)) {
                            $out[$c] = $row[$c];
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
