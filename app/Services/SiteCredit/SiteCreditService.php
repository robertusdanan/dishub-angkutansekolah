<?php

namespace App\Services\SiteCredit;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Port dari core/footer.php - sebelumnya kode ini di-obfuscate lewat
 * eval(gzuncompress(base64_decode(...))). Isinya sudah didekode dan
 * fungsinya di sini SAMA PERSIS: mengambil baris kredit developer
 * (developer, text, link, org_name) dari tabel `site_credit` di project
 * Supabase LAIN (milik pembuat template, bukan project Supabase utama
 * aplikasi ini), lalu ditampilkan di footer.
 *
 * DIPERTAHANKAN sesuai instruksi pemilik project (berbeda dengan mekanisme
 * kill-switch di cache_helper.php/license.php yang sudah dihapus).
 */
class SiteCreditService
{
    protected const CACHE_KEY = 'site_credit_data';

    protected const CACHE_TTL_SECONDS = 86400; // sama seperti $__kv_ttl lama

    protected const SHAPE = ['developer' => '', 'text' => '', 'link' => '', 'org_name' => ''];

    /**
     * @return array{developer:string,text:string,link:string,org_name:string}
     */
    public function get(): array
    {
        $cached = Cache::get(self::CACHE_KEY);
        if ($this->isValid($cached)) {
            return array_merge(self::SHAPE, array_intersect_key($cached, self::SHAPE));
        }

        $fresh = $this->fetchFromSupabase();

        if ($this->isValid($fresh)) {
            Cache::put(self::CACHE_KEY, $fresh, self::CACHE_TTL_SECONDS);

            return array_merge(self::SHAPE, array_intersect_key($fresh, self::SHAPE));
        }

        // Fallback: kalau fetch gagal tapi masih ada cache lama (walau
        // sudah lewat TTL Cache::get di atas tidak akan mengembalikannya
        // lagi), tetap kembalikan shape kosong seperti kode lama.
        return self::SHAPE;
    }

    protected function fetchFromSupabase(): ?array
    {
        $url = rtrim((string) config('services.site_credit.url'), '/')
            .'/rest/v1/site_credit'
            .'?select=developer,text,link,org_name&id=eq.1';

        $key = (string) config('services.site_credit.anon_key');

        try {
            $response = Http::withHeaders([
                'apikey' => $key,
                'Authorization' => 'Bearer '.$key,
                'Accept' => 'application/json',
            ])->timeout(6)->connectTimeout(4)->get($url);

            if (!$response->successful()) {
                Log::warning('[site_credit] request gagal, http_code='.$response->status());

                return null;
            }

            $rows = $response->json();

            return is_array($rows) && isset($rows[0]) ? $rows[0] : null;
        } catch (\Throwable $e) {
            Log::warning('[site_credit] exception: '.$e->getMessage());

            return null;
        }
    }

    protected function isValid(mixed $data): bool
    {
        return is_array($data) && !empty($data['developer']) && !empty($data['text']) && !empty($data['link']);
    }
}
