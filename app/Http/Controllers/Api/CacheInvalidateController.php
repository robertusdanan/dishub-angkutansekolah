<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Supabase\ReferenceCacheProxyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Port dari api/cache_invalidate.php - webhook dipanggil Supabase Database
 * Trigger (pg_net.http_post) tiap ada INSERT/UPDATE/DELETE di tabel yang
 * dipasangi trigger. TIDAK PERNAH dipanggil dari browser/JS, server-to-server
 * saja - dilindungi header X-Cache-Secret (bukan CSRF, makanya route ini
 * juga masuk daftar pengecualian CSRF di bootstrap/app.php).
 */
class CacheInvalidateController extends Controller
{
    protected const ABSENSI_TABLES = ['absensi_RFID', 'absensi_QRCode', 'absensi_foto'];

    public function __construct(protected ReferenceCacheProxyService $refCache)
    {
    }

    public function handle(Request $request): JsonResponse
    {
        $given = (string) $request->header('X-Cache-Secret', '');
        $expected = (string) config('services.cache_webhook_secret', '');

        if ($expected === '' || !hash_equals($expected, $given)) {
            return response()->json(['error' => 'Token tidak valid'], 403);
        }

        $data = $request->json()->all();
        if (!is_array($data) || empty($data['table'])) {
            return response()->json(['error' => 'Payload tidak valid'], 400);
        }
        $table = (string) $data['table'];

        if (in_array($table, self::ABSENSI_TABLES, true)) {
            // Setara hapus cache/absensi/{table}.json lama - cache
            // whole-table absensi sekarang dikelola AbsensiHybridService
            // lewat Cache facade dengan key 'absensi_whole_{table}'.
            $deleted = Cache::forget('absensi_whole_'.$table) ? [$table] : [];

            return response()->json(['status' => 'ok', 'table' => $table, 'deleted' => $deleted]);
        }

        $this->refCache->invalidate($table);

        return response()->json(['status' => 'ok', 'table' => $table]);
    }
}
