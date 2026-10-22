<?php

namespace App\Services\Supabase;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Database\QueryException;

// Local-domain tables are intentionally routed to Laravel SQLite. Admin and
// /rute-sekolah tables remain on Supabase.
const LOCAL_TABLES = [
    'akun_publik' => 'akun_publik', 'akun_publik_keluarga' => 'akun_publik_keluarga',
    'balikgratis_pemesanan' => 'balikgratis_pemesanan', 'balikgratis_pengaturan' => 'balikgratis_pengaturan',
    'trayekwisata_trayek' => 'trayekwisata_trayek', 'trayekwisata_titik' => 'trayekwisata_titik',
    'trayekwisata_titik_galeri' => 'trayekwisata_titik_galeri', 'trayekwisata_bus' => 'trayekwisata_bus',
    'trayekwisata_jadwal' => 'trayekwisata_jadwal', 'trayekwisata_pemesanan' => 'trayekwisata_pemesanan',
    'trayekwisata_pemesanan_nik' => 'trayekwisata_pemesanan_nik', 'trayekwisata_waitlist' => 'trayekwisata_waitlist',
    'trayekwisata_hero' => 'trayekwisata_hero', 'list_tambangan' => 'asdp_lokasi',
];

/**
 * Klien REST ke Supabase (PostgREST) - port 1:1 dari core/sb_client_helper.php
 * (fungsi sb_request / sb_select / sb_insert / sb_upsert / sb_update / sb_delete).
 *
 * Perilaku dipertahankan sama persis, termasuk:
 * - default $useService = true (pakai service_role key, bypass RLS)
 * - query params di-encode manual (bukan http_build_query) supaya filter
 *   PostgREST seperti "eq.nilai" TIDAK ikut ter-encode titiknya
 * - opsi $minimal untuk "Prefer: return=minimal" (write yang response-nya
 *   tidak pernah dibaca, mis. heartbeat GPS)
 */
class SupabaseClient
{
    protected string $url;
    protected string $anonKey;
    protected string $serviceKey;

    public function __construct()
    {
        $this->url = rtrim((string) config('services.supabase.url'), '/');
        $this->anonKey = (string) config('services.supabase.anon_key');
        $this->serviceKey = (string) config('services.supabase.service_key');
    }

    protected function key(bool $useService): string
    {
        return $useService ? $this->serviceKey : $this->anonKey;
    }

    /**
     * @return array{0:int,1:mixed} [$httpCode, $responseArray]
     */
    public function request(
        string $method,
        string $endpoint,
        ?array $body = null,
        array $params = [],
        bool $useService = true,
        bool $minimal = false
    ): array {
        if (str_starts_with($endpoint, '/rest/v1/')) {
            $localPath = substr($endpoint, strlen('/rest/v1/'));
            [$localTable] = explode('?', $localPath, 2);
            if (isset(LOCAL_TABLES[$localTable])) {
                return $this->rawRequest($method, $localPath, $body, $useService);
            }
        }

        $key = $this->key($useService);

        // Sama seperti kode lama: encode manual, JANGAN pakai http_build_query,
        // supaya filter Supabase seperti "eq.nilai" tidak jadi "eq%2Enilai".
        $url = $this->url.$endpoint;
        if (!empty($params)) {
            $parts = [];
            foreach ($params as $k => $v) {
                $parts[] = rawurlencode((string) $k).'='.rawurlencode((string) $v);
            }
            $url .= '?'.implode('&', $parts);
        }

        $headers = [
            'Content-Type' => 'application/json',
            'apikey' => $key,
            'Authorization' => 'Bearer '.$key,
        ];

        if (in_array($method, ['POST', 'PATCH'], true)) {
            $headers['Prefer'] = $minimal ? 'return=minimal' : 'return=representation';
        }

        try {
            $response = Http::withHeaders($headers)
                ->timeout(15)
                ->send($method, $url, $body !== null ? ['json' => $body] : []);

            return [$response->status(), $response->json()];
        } catch (\Throwable $e) {
            Log::error('SupabaseClient request gagal: '.$e->getMessage(), [
                'method' => $method,
                'endpoint' => $endpoint,
            ]);

            return [0, null];
        }
    }

    /**
     * Setara akun_sb()/tw_sb() lama: $pathWithQuery sudah berisi tabel +
     * query string PostgREST lengkap (mis. 'akun_publik?id=eq.123&select=*'),
     * bukan dipecah table+filters seperti select()/update() di atas.
     *
     * @return array{0:int,1:mixed}
     */
    public function rawRequest(string $method, string $pathWithQuery, mixed $body = null, bool $useService = true): array
    {
        [$table, $query] = array_pad(explode('?', $pathWithQuery, 2), 2, '');
        if (!isset(LOCAL_TABLES[$table])) {
            return $this->request($method, '/rest/v1/'.$pathWithQuery, $body, [], $useService);
        }

        try {
            $q = DB::table(LOCAL_TABLES[$table]);
            parse_str(str_replace('&', '&', $query), $params);
            foreach ($params as $key => $value) {
                if (in_array($key, ['select', 'order', 'limit', 'offset'], true)) continue;
                if (str_contains($key, '.')) continue;
                [$operator, $operand] = array_pad(explode('.', (string) $value, 2), 2, '');
                $operand = rawurldecode($operand);
                if ($operator === 'eq') $q->where($key, '=', $operand);
                elseif ($operator === 'neq') $q->where($key, '!=', $operand);
                elseif ($operator === 'gte') $q->where($key, '>=', $operand);
                elseif ($operator === 'gt') $q->where($key, '>', $operand);
                elseif ($operator === 'lte') $q->where($key, '<=', $operand);
                elseif ($operator === 'lt') $q->where($key, '<', $operand);
                elseif ($operator === 'is' && $operand === 'null') $q->whereNull($key);
                elseif ($operator === 'in') $q->whereIn($key, array_filter(explode(',', trim($operand, '()'))));
            }
            if (!empty($params['order'])) {
                foreach (explode(',', $params['order']) as $order) { [$column, $direction] = array_pad(explode('.', $order), 2, 'asc'); $q->orderBy($column, strtolower($direction) === 'desc' ? 'desc' : 'asc'); }
            }
            if (isset($params['offset'])) $q->offset((int) $params['offset']);
            if (isset($params['limit'])) $q->limit((int) $params['limit']);
            if ($method === 'GET') {
                $rows = array_map(fn ($r) => (array) $r, $q->get()->all());
                if ($table === 'trayekwisata_pemesanan' && isset($params['select']) && str_contains($params['select'], 'trayekwisata_jadwal')) {
                    foreach ($rows as &$row) {
                        $jadwal = DB::table('trayekwisata_jadwal')->where('id', $row['jadwal_id'] ?? '')->first();
                        $jadwal = $jadwal ? (array) $jadwal : [];
                        if (preg_match('/trayekwisata_jadwal\\(([^)]*)\\)/', $params['select'], $match)) {
                            $fields = array_filter(array_map('trim', explode(',', $match[1])));
                            $jadwal = array_intersect_key($jadwal, array_flip(array_filter($fields, fn ($f) => !str_contains($f, '('))));
                            if (str_contains($match[1], 'trayekwisata_trayek')) {
                                $trayek = DB::table('trayekwisata_trayek')->where('id', $jadwal['trayek_id'] ?? '')->first();
                                $jadwal['trayekwisata_trayek'] = $trayek ? (array) $trayek : null;
                            }
                        }
                        $row['trayekwisata_jadwal'] = $jadwal;
                    }
                    unset($row);
                }
                return [200, $rows];
            }
            if ($method === 'POST') { $rows = is_array($body) && array_is_list($body) ? $body : [$body]; foreach ($rows as &$row) { $row['id'] ??= (string) Str::uuid(); $q->insert($row); } return [201, $rows]; }
            if ($method === 'PATCH') { $count = $q->update((array) $body); return [204, []]; }
            if ($method === 'DELETE') { $q->delete(); return [204, []]; }
        } catch (QueryException $e) { Log::error('Local SQLite request gagal', ['table' => $table, 'error' => $e->getMessage()]); return [500, ['message' => 'Local database error']]; }
        return [405, ['message' => 'Method not allowed']];
    }

    public function select(string $table, array $filters = [], ?int $limit = null, ?string $order = null, bool $useService = true): array
    {
        if (isset(LOCAL_TABLES[$table])) {
            $query = [];
            foreach ($filters as $key => $value) $query[$key] = $value;
            if ($limit) $query['limit'] = $limit;
            if ($order) $query['order'] = $order;
            return $this->rawRequest('GET', $table.'?'.http_build_query($query), null, $useService);
        }
        $params = $filters;
        if ($limit) {
            $params['limit'] = $limit;
        }
        if ($order) {
            $params['order'] = $order;
        }
        $params['select'] = '*';

        return $this->request('GET', "/rest/v1/{$table}", null, $params, $useService);
    }

    public function insert(string $table, array $data, bool $useService = true): array
    {
        if (isset(LOCAL_TABLES[$table])) return $this->rawRequest('POST', $table, $data, $useService);
        return $this->request('POST', "/rest/v1/{$table}", $data, [], $useService);
    }

    public function upsert(string $table, array $data, string $onConflict = 'id', bool $useService = true): array
    {
        if (isset(LOCAL_TABLES[$table])) {
            $rows = is_array($data) && array_is_list($data) ? $data : [$data];
            foreach ($rows as $row) {
                $existing = DB::table(LOCAL_TABLES[$table])->where($onConflict, $row[$onConflict] ?? null)->first();
                if ($existing) DB::table(LOCAL_TABLES[$table])->where($onConflict, $row[$onConflict] ?? null)->update($row);
                else DB::table(LOCAL_TABLES[$table])->insert($row + ['id' => (string) Str::uuid()]);
            }
            return [200, $rows];
        }
        $key = $this->key($useService);
        $url = "{$this->url}/rest/v1/{$table}?on_conflict=".rawurlencode($onConflict);

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'apikey' => $key,
                'Authorization' => 'Bearer '.$key,
                'Prefer' => 'resolution=merge-duplicates,return=representation',
            ])->timeout(15)->post($url, $data);

            return [$response->status(), $response->json()];
        } catch (\Throwable $e) {
            Log::error('SupabaseClient upsert gagal: '.$e->getMessage(), ['table' => $table]);

            return [0, null];
        }
    }

    /**
     * @param bool $minimal lihat catatan $minimal di request() di atas.
     */
    public function update(string $table, array $data, array $filters, bool $minimal = false, bool $useService = true): array
    {
        if (isset(LOCAL_TABLES[$table])) {
            $query = [];
            foreach ($filters as $key => $value) $query[$key] = $value;
            return $this->rawRequest('PATCH', $table.'?'.http_build_query($query), $data, $useService);
        }
        return $this->request('PATCH', "/rest/v1/{$table}", $data, $filters, $useService, $minimal);
    }

    public function delete(string $table, array $filters, bool $useService = true): array
    {
        if (isset(LOCAL_TABLES[$table])) {
            $query = [];
            foreach ($filters as $key => $value) $query[$key] = $value;
            return $this->rawRequest('DELETE', $table.'?'.http_build_query($query), null, $useService);
        }
        return $this->request('DELETE', "/rest/v1/{$table}", null, $filters, $useService);
    }
}
