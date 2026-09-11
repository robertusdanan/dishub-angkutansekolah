<?php

namespace App\Services\Supabase;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Klien REST ke Supabase (PostgREST) — port 1:1 dari core/sb_client_helper.php
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
        return $this->request($method, '/rest/v1/'.$pathWithQuery, $body, [], $useService);
    }

    public function select(string $table, array $filters = [], ?int $limit = null, ?string $order = null, bool $useService = true): array
    {
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
        return $this->request('POST', "/rest/v1/{$table}", $data, [], $useService);
    }

    public function upsert(string $table, array $data, string $onConflict = 'id', bool $useService = true): array
    {
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
        return $this->request('PATCH', "/rest/v1/{$table}", $data, $filters, $useService, $minimal);
    }

    public function delete(string $table, array $filters, bool $useService = true): array
    {
        return $this->request('DELETE', "/rest/v1/{$table}", null, $filters, $useService);
    }
}
