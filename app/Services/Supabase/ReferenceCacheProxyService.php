<?php

namespace App\Services\Supabase;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Port dari core/cache_helper.php (reference_table_cache_category(),
 * sb_cache_category(), sb_cache_ttl_for_category(), sb_fetch_with_cache())
 * - dipakai oleh core/reference_cache_proxy_endpoint.php (sekarang
 * ReferenceCacheProxyController).
 *
 * Beda dari kode lama: cache file lokal manual (di folder /cache/...)
 * diganti Cache facade Laravel (driver "file" bawaan - perilaku setara,
 * cuma lokasinya di storage/framework/cache/data, bukan /cache/ di web root).
 */
class ReferenceCacheProxyService
{
    protected const SAFETY_TTL = 24 * 3600; // sama seperti SB_CACHE_SAFETY_TTL lama

    /**
     * @return array<string,string> tabel => kategori cache
     */
    protected function categories(): array
    {
        return [
            'user_RFID' => 'user',
            'domisili' => 'user',
            'sekolah' => 'user',
            'list_tambangan' => 'user',
            'trayek_bus' => 'datatrayek',
            'trayek_mpu' => 'datatrayek',
            'map' => 'datatrayek',
            'driver_bus' => 'datatrayek',
            'driver_mpu' => 'datatrayek',
            'trayekwisata_titik' => 'trayekwisata',
            'trayekwisata_titik_galeri' => 'trayekwisata',
            'trayekwisata_bus' => 'trayekwisata',
            'trayekwisata_driver' => 'trayekwisata',
            'trayekwisata_trayek' => 'trayekwisata',
            'trayekwisata_trayek_titik' => 'trayekwisata',
            'trayekwisata_jadwal' => 'trayekwisata_jadwal',
        ];
    }

    public function categoryFor(string $table, string $select = ''): string
    {
        $positionTables = ['driver_bus', 'driver_mpu'];
        if (in_array($table, $positionTables, true) && preg_match('/\b(lat|lng)\b/i', $select)) {
            return 'live';
        }

        return $this->categories()[$table] ?? 'live';
    }

    public function ttlFor(string $category): int
    {
        return $category === 'live' ? 5 : self::SAFETY_TTL;
    }

    /**
     * Efek berantai - port dari reference_table_side_effects(). Contoh:
     * invalidasi 'trayekwisata_pemesanan' (aksi publik: pesan/batalkan
     * tiket) ikut menginvalidasi cache 'trayekwisata_jadwal' (kuota_terisi
     * berubah lewat trigger DB begitu baris pemesanan berubah).
     */
    protected function sideEffects(): array
    {
        return [
            'trayekwisata_pemesanan' => ['trayekwisata_jadwal'],
        ];
    }

    /**
     * Port dari invalidate_reference_cache(). Beda dari kode lama (hapus
     * file cache per tabel langsung): karena cache key Laravel di sini
     * mengandung hash query lengkap (bisa banyak variasi per tabel), kita
     * pakai pola "cache version" - menaikkan versi tabel bikin SEMUA cache
     * key lama untuk tabel itu otomatis tidak terpakai lagi (dianggap miss),
     * tanpa perlu tahu persis query apa saja yang pernah di-cache.
     */
    public function invalidate(string $table): void
    {
        $categories = $this->categories();
        if (isset($categories[$table]) || $table === 'live') {
            $this->bumpVersion($table);
        }

        foreach ($this->sideEffects()[$table] ?? [] as $affected) {
            $this->invalidate($affected);
        }
    }

    protected function versionKey(string $table): string
    {
        return 'ref_proxy_version_'.$table;
    }

    public function version(string $table): int
    {
        return (int) Cache::get($this->versionKey($table), 1);
    }

    protected function bumpVersion(string $table): void
    {
        Cache::forever($this->versionKey($table), $this->version($table) + 1);
    }

    /**
     * @return array{status:int, body:string, source:string}
     */
    public function fetchWithCache(string $table, string $url, array $headers, int $ttl): array
    {
        $cacheKey = 'ref_proxy_'.$table.'_v'.$this->version($table).'_'.md5($url);

        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return ['status' => 200, 'body' => $cached, 'source' => 'hit'];
        }

        try {
            $response = Http::withHeaders($headers)->timeout(15)->get($url);

            if (!$response->successful()) {
                // Best-effort: kalau Supabase gagal tapi cache lama (walau
                // sudah lewat TTL & terhapus dari store) tidak ada lagi di
                // sini karena Cache::get sudah expired-nya beneran hilang -
                // beda dari file lama yang tetap ada fisiknya walau basi.
                // Untuk kesetiaan penuh ke perilaku "stale-fallback", kita
                // simpan salinan permanen terpisah sebagai fallback.
                $stale = Cache::get($cacheKey.'_stale');
                if ($stale !== null) {
                    return ['status' => 200, 'body' => $stale, 'source' => 'stale-fallback'];
                }

                return [
                    'status' => $response->status() ?: 502,
                    'body' => $response->body() ?: json_encode(['error' => 'Gagal mengambil data dari Supabase']),
                    'source' => 'error',
                ];
            }

            $body = $response->body();
            Cache::put($cacheKey, $body, $ttl);
            // Salinan "stale" bertahan lebih lama sebagai jaring pengaman kalau Supabase down.
            Cache::put($cacheKey.'_stale', $body, self::SAFETY_TTL * 7);

            return ['status' => 200, 'body' => $body, 'source' => 'miss'];
        } catch (\Throwable $e) {
            $stale = Cache::get($cacheKey.'_stale');
            if ($stale !== null) {
                return ['status' => 200, 'body' => $stale, 'source' => 'stale-fallback'];
            }

            return [
                'status' => 502,
                'body' => json_encode(['error' => $e->getMessage()]),
                'source' => 'error',
            ];
        }
    }
}
