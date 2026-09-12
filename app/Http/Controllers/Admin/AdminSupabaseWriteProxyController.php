<?php

namespace App\Http\Controllers\Admin;

use App\Services\Admin\AdminRoleService;
use App\Services\Supabase\ReferenceCacheProxyService;
use App\Services\Supabase\SupabaseClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Port dari core/admin_supabase_proxy_helper.php (bagian generik) +
 * admin/api/{angkutansekolah,balikgratis,trayekwisata}/db.php (bagian
 * spesifik per modul: whitelist tabel, aturan role, cleanup file lokal).
 *
 * Satu-satunya tempat di seluruh aplikasi Laravel yang boleh memakai
 * SUPABASE_SERVICE_KEY untuk permintaan yang dipicu dari BROWSER admin
 * (via fetch, session cookie) — sama seperti filosofi kode lama.
 */
class AdminSupabaseWriteProxyController
{
    public function __construct(
        protected AdminRoleService $roles,
        protected SupabaseClient $supabase,
        protected ReferenceCacheProxyService $refCache,
    ) {
    }

    protected function resolveMethod(Request $request): string
    {
        $method = $request->method();
        if ($method === 'POST') {
            $override = strtoupper((string) $request->header('X-HTTP-Method-Override', ''));
            if (in_array($override, ['PATCH', 'DELETE'], true)) {
                $method = $override;
            }
        }

        return $method;
    }

    /**
     * Inti generik — setara admin_proxy_*() di kode lama. $config berisi
     * bagian spesifik per modul.
     */
    protected function proxy(Request $request, array $config): JsonResponse
    {
        if (!session('admin_logged_in')) {
            return response()->json(['error' => 'Unauthorized: silakan login ulang.'], 401);
        }

        $table = trim((string) $request->query('table', ''));
        if (!in_array($table, $config['allowed_tables'], true)) {
            return response()->json(['error' => 'Tabel tidak diizinkan lewat endpoint ini: '.$table], 400);
        }

        if ($fail = $config['authorize']($table)) {
            return $fail;
        }

        $qs = (string) $request->query('qs', '');
        if ($qs !== '' && !preg_match('/^[A-Za-z0-9_.,;:=&%\-*() +]*$/', $qs)) {
            return response()->json(['error' => 'Query tidak valid.'], 400);
        }

        $method = $this->resolveMethod($request);

        // Baca cache (kalau tabel ini termasuk yang di-cache modul ini, GET saja).
        $cacheableCategory = $config['cacheable_tables'][$table] ?? null;
        $cacheKey = null;
        if ($method === 'GET' && $cacheableCategory !== null) {
            $cacheKey = 'admwrite_'.$table.'_v'.$this->refCache->version($table).'_'.md5($qs);
            $cached = Cache::get($cacheKey);
            if ($cached !== null) {
                return response()->json(json_decode($cached, true))->header('X-Admin-Cache', 'hit');
            }
        }

        // Kumpulkan file lokal yang perlu dibersihkan SEBELUM baris terhapus.
        $filesToCleanup = [];
        if ($method === 'DELETE' && isset($config['cleanup_before_delete'])) {
            $filesToCleanup = $config['cleanup_before_delete']($table, $qs);
        }

        $body = null;
        if (in_array($method, ['POST', 'PATCH'], true)) {
            $raw = $request->getContent();
            if ($raw !== '') {
                json_decode($raw);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return response()->json(['error' => 'Body JSON tidak valid.'], 400);
                }
                $body = json_decode($raw, true);
            }
        }

        [$status, $responseBody] = $this->supabase->rawRequest($method, $table.($qs !== '' ? '?'.$qs : ''), $body, true);

        if ($status < 200 || $status >= 300) {
            // Fallback ke cache lama kalau Supabase gagal & tabel ini di-cache.
            if ($cacheKey !== null) {
                $stale = Cache::get($cacheKey.'_stale');
                if ($stale !== null) {
                    return response()->json(json_decode($stale, true))->header('X-Admin-Cache', 'stale-fallback');
                }
            }

            return response()->json($responseBody ?? ['error' => 'Gagal menghubungi Supabase'], $status ?: 502);
        }

        if ($cacheKey !== null) {
            $json = json_encode($responseBody);
            Cache::forever($cacheKey, $json);
            Cache::forever($cacheKey.'_stale', $json);
        }

        if ($method !== 'GET') {
            // code_map driver harus ikut berubah begitu trayek/titik rute/trayek
            // berubah — lihat App\Services\AbsenRfid\DriverCodeMapSync.
            // Jalan SEBELUM invalidasi cache supaya baris hasil sync yang baru
            // tidak ikut tersimpan basi di cache referensi.
            $touchedDriverCache = false;
            if (in_array($table, ['driver_bus', 'driver_mpu', 'map', 'trayek_bus', 'trayek_mpu'], true)) {
                app(\App\Services\AbsenRfid\DriverCodeMapSync::class)->syncAll();
                $touchedDriverCache = true;
            }

            $this->refCache->invalidate($table);
            if ($touchedDriverCache) {
                $this->refCache->invalidate('driver_bus');
                $this->refCache->invalidate('driver_mpu');
            }

            if (isset($config['after_write'])) {
                $config['after_write']($table);
            }
            foreach ($filesToCleanup as $f) {
                $this->cleanupFile($f);
            }
        }

        return response()->json($responseBody);
    }

    protected function cleanupFile(array $f): void
    {
        $disk = Storage::disk('uploads');
        if (isset($f['file']) && $disk->exists($f['file'])) {
            $disk->delete($f['file']);
        } elseif (isset($f['dir'])) {
            foreach ($disk->files($f['dir']) as $ff) {
                $disk->delete($ff);
            }
        }
    }

    /**
     * Peta tabel → menu yang mengatur akses tulisnya — dipakai supaya
     * proxy tulis KONSISTEN dengan canAccessMenu() yang sudah mengatur
     * visibilitas halamannya (lihat sidebar & requireMenuAccess() di
     * masing-masing controller halaman). Beberapa tabel dijaga LEBIH DARI
     * SATU menu (mis. list_tambangan dipakai 2 halaman ASDP) — cukup salah
     * satu izin menu itu untuk boleh menulis ke tabelnya.
     *
     * PERBAIKAN (atas permintaan pemilik project): kode lama mengunci
     * SELURUH tabel di endpoint ini ke role 'superadmin' secara blanket,
     * tidak peduli menu apa yang sudah diberikan lewat Manajemen Role —
     * jadi kalau ada role non-superadmin diberi akses menu (mis. hanya
     * "Lokasi ASDP"), mereka BISA lihat halamannya tapi SELALU gagal (403)
     * saat menyimpan. Sekarang proxy ini ikut memeriksa canAccessMenu()
     * per tabel, sama seperti yang mengatur visibilitas halaman.
     */
    protected function angkutansekolahTableMenus(): array
    {
        return [
            'map' => ['update_map'],
            'trayek_bus' => ['update_trayek'],
            'trayek_mpu' => ['update_trayek'],
            'driver_bus' => ['update_driver'],
            'driver_mpu' => ['update_driver'],
            'sekolah' => ['update_sekolah'],
            'domisili' => ['update_domisili'],
            'deeplink_absenangkutan' => ['update_siswa'],
            'absensi_RFID' => ['data_absensi'],
            'absensi_QRCode' => ['data_absensi'],
            'user_RFID' => ['update_siswa'],
            'absensi_foto' => ['tambah_foto', 'report_foto'],
            'list_tambangan' => ['asdp_daftar', 'asdp_koordinat'],
        ];
    }

    /** POST/GET /admin/api/angkutansekolah/db — setara admin/api/angkutansekolah/db.php */
    public function angkutansekolah(Request $request): JsonResponse
    {
        return $this->proxy($request, [
            'authorize' => function (string $table) {
                $requiredMenus = $this->angkutansekolahTableMenus()[$table] ?? [];
                $allowed = empty($requiredMenus)
                    ? $this->roles->isSuperAdmin() // tabel tak dikenal: fallback aman, superadmin saja
                    : collect($requiredMenus)->contains(fn ($m) => $this->roles->canAccessMenu($m));

                if (!$allowed) {
                    return response()->json(['error' => 'Forbidden: Anda tidak punya izin menu yang mengatur data ini.'], 403);
                }

                return null;
            },
            'allowed_tables' => [
                'map', 'trayek_bus', 'trayek_mpu', 'driver_bus', 'driver_mpu',
                'sekolah', 'domisili', 'deeplink_absenangkutan',
                'absensi_RFID', 'absensi_QRCode', 'absensi_foto', 'user_RFID',
                'list_tambangan',
            ],
            'cacheable_tables' => [],
        ]);
    }

    /** POST/GET /admin/api/balikgratis/db — setara admin/api/balikgratis/db.php */
    public function balikgratis(Request $request): JsonResponse
    {
        return $this->proxy($request, [
            'authorize' => function () {
                if (session('admin_role') === 'guest') {
                    return response()->json(['error' => 'Forbidden.'], 403);
                }

                return null;
            },
            'allowed_tables' => ['balikgratis_pemesanan', 'balikgratis_pengaturan'],
            'cacheable_tables' => [],
            'cleanup_before_delete' => function (string $table, string $qs) {
                if ($table !== 'balikgratis_pemesanan') {
                    return [];
                }
                [, $rows] = $this->supabase->rawRequest('GET', $table.'?'.$qs.'&select=dokumen_filename');
                $files = [];
                foreach ((array) $rows as $r) {
                    if (!empty($r['dokumen_filename'])) {
                        $files[] = ['file' => 'balikgratis/'.$r['dokumen_filename']];
                    }
                }

                return $files;
            },
        ]);
    }

    /** POST/GET /admin/api/trayekwisata/db — setara admin/api/trayekwisata/db.php */
    public function trayekwisata(Request $request): JsonResponse
    {
        $cacheableTables = [
            'trayekwisata_titik' => 'trayekwisata',
            'trayekwisata_titik_galeri' => 'trayekwisata',
            'trayekwisata_bus' => 'trayekwisata',
            'trayekwisata_driver' => 'trayekwisata',
            'trayekwisata_trayek' => 'trayekwisata',
            'trayekwisata_trayek_titik' => 'trayekwisata',
        ];

        return $this->proxy($request, [
            'authorize' => function () {
                if (session('admin_role') === 'guest') {
                    return response()->json(['error' => 'Forbidden.'], 403);
                }

                return null;
            },
            'allowed_tables' => [
                'trayekwisata_titik', 'trayekwisata_titik_galeri', 'trayekwisata_bus',
                'trayekwisata_driver', 'trayekwisata_trayek', 'trayekwisata_trayek_titik',
                'trayekwisata_jadwal', 'trayekwisata_pemesanan', 'trayekwisata_pemesanan_nik',
                'akun_publik', 'trayekwisata_keluarga', 'trayekwisata_waitlist', 'trayekwisata_survei',
            ],
            'cacheable_tables' => $cacheableTables,
            'after_write' => function (string $table) {
                if ($table === 'trayekwisata_pemesanan') {
                    Cache::forget('trayekwisata_stats');
                }
            },
            'cleanup_before_delete' => function (string $table, string $qs) {
                if ($table === 'trayekwisata_titik') {
                    [, $rows] = $this->supabase->rawRequest('GET', $table.'?'.$qs.'&select=id');
                    foreach ((array) $rows as $r) {
                        if (!empty($r['id'])) {
                            app(\App\Services\Admin\TrayekWisata\TrayekWisataGaleriService::class)->deleteDirByTitikId($r['id']);
                        }
                    }

                    return [];
                }
                if ($table === 'trayekwisata_titik_galeri') {
                    [, $rows] = $this->supabase->rawRequest('GET', $table.'?'.$qs.'&select=url');
                    foreach ((array) $rows as $r) {
                        if (!empty($r['url'])) {
                            app(\App\Services\Admin\TrayekWisata\TrayekWisataGaleriService::class)->deleteFile($r['url']);
                        }
                    }
                }

                return [];
            },
        ]);
    }

    /** POST /admin/api/{modul}/invalidate-cache */
    public function invalidateCache(Request $request): JsonResponse
    {
        if (!session('admin_logged_in')) {
            return response()->json(['error' => 'Unauthorized: silakan login ulang.'], 401);
        }
        $table = trim((string) $request->input('table', ''));
        if ($table === '') {
            return response()->json(['error' => 'Parameter table wajib diisi.'], 400);
        }
        $this->refCache->invalidate($table);

        return response()->json(['status' => 'ok']);
    }
}
