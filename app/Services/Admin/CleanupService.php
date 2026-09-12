<?php

namespace App\Services\Admin;

use App\Services\Supabase\ReferenceCacheProxyService;
use App\Services\Supabase\SupabaseClient;
use DateTime;
use DateTimeZone;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Port dari logic inti admin/api/cleanup.php (dipisah dari HTTP-handling
 * supaya bisa dipanggil dari controller web MAUPUN artisan command, setara
 * dual-mode CLI/web di kode lama).
 */
class CleanupService
{
    public const KEEP_MONTHS = 6;

    public const KEEP_MONTHS_PEMESANAN = 24;

    public function __construct(
        protected SupabaseClient $supabase,
        protected ReferenceCacheProxyService $refCache,
    ) {
    }

    /**
     * @return array{batas:string, batas_pemesanan:string, log:string[]}
     */
    public function run(): array
    {
        $batas = new DateTime('now', new DateTimeZone('UTC'));
        $batas->modify('-'.self::KEEP_MONTHS.' months');
        $batasISO = $batas->format(DateTime::ATOM);

        $batasPemesanan = new DateTime('now', new DateTimeZone('UTC'));
        $batasPemesanan->modify('-'.self::KEEP_MONTHS_PEMESANAN.' months');
        $batasPemesananISO = $batasPemesanan->format(DateTime::ATOM);

        $log = [];
        $log[] = '=== CLEANUP ABSENSI (retensi 6 bulan) ===';
        $log[] = 'Waktu jalan  : '.(new DateTime('now', new DateTimeZone('Asia/Jakarta')))->format('Y-m-d H:i:s').' WIB';
        $log[] = 'Hapus data < : '.(new DateTime($batasISO, new DateTimeZone('UTC')))->setTimezone(new DateTimeZone('Asia/Jakarta'))->format('Y-m-d H:i:s').' WIB';
        $log[] = '';

        foreach (['absensi_RFID', 'absensi_QRCode', 'absensi_foto'] as $tabel) {
            $result = $this->deleteOld($tabel, 'waktu', $batasISO);

            if ($result['error']) {
                $log[] = "[GAGAL] {$tabel} - HTTP {$result['http_code']}: {$result['error']}";
            } else {
                $log[] = "[OK]    {$tabel} - {$result['deleted']} baris dihapus";

                // Setara: hapus file cache/absensi/{tabel}.json lama.
                // Di Laravel, cache absensi (kalau ada) dikelola lewat
                // Cache facade dengan prefix per-tabel - invalidasi umum.
                Cache::forget('absensi_cache_'.$tabel);

                if ($tabel === 'absensi_foto' && !empty($result['rows'])) {
                    $fotoDeleted = 0;
                    foreach ($result['rows'] as $row) {
                        $foto = basename((string) ($row['foto'] ?? ''));
                        if ($foto === '') {
                            continue;
                        }
                        if (Storage::disk('uploads')->exists('angkutansekolah/absensi-foto/'.$foto)) {
                            Storage::disk('uploads')->delete('angkutansekolah/absensi-foto/'.$foto);
                            $fotoDeleted++;
                        }
                    }
                    if ($fotoDeleted > 0) {
                        $log[] = "        {$fotoDeleted} file foto lokal ikut dihapus";
                    }
                }
            }
        }

        $log[] = '';
        $log[] = '=== CLEANUP PEMESANAN TRAYEK WISATA (retensi 2 tahun) ===';
        $log[] = 'Hapus data < : '.(new DateTime($batasPemesananISO, new DateTimeZone('UTC')))->setTimezone(new DateTimeZone('Asia/Jakarta'))->format('Y-m-d H:i:s').' WIB';
        $log[] = '';

        [, $pemesananLama] = $this->supabase->rawRequest('GET', 'trayekwisata_pemesanan?select=id&created_at=lt.'.urlencode($batasPemesananISO));

        if (!is_array($pemesananLama)) {
            $log[] = '[GAGAL] trayekwisata_pemesanan - gagal mengambil daftar id lama dari Supabase.';
        } elseif (count($pemesananLama) === 0) {
            $log[] = '[OK]    trayekwisata_pemesanan_nik - 0 baris dihapus (tidak ada pemesanan lama)';
            $log[] = '[OK]    trayekwisata_pemesanan - 0 baris dihapus';
        } else {
            $idsLama = array_column($pemesananLama, 'id');

            $nikResult = $this->deleteByIds('trayekwisata_pemesanan_nik', 'pemesanan_id', $idsLama);
            if ($nikResult['error']) {
                $log[] = "[GAGAL] trayekwisata_pemesanan_nik - {$nikResult['error']}";
                $log[] = '        Dibatalkan: trayekwisata_pemesanan TIDAK dihapus supaya baris anak tidak jadi yatim.';
            } else {
                $log[] = "[OK]    trayekwisata_pemesanan_nik - {$nikResult['deleted']} baris dihapus";

                $indukResult = $this->deleteByIds('trayekwisata_pemesanan', 'id', $idsLama);
                if ($indukResult['error']) {
                    $log[] = "[GAGAL] trayekwisata_pemesanan - {$indukResult['error']}";
                } else {
                    $log[] = "[OK]    trayekwisata_pemesanan - {$indukResult['deleted']} baris dihapus";

                    $this->refCache->invalidate('trayekwisata_pemesanan');
                    Cache::forget('trayekwisata_stats');
                    $log[] = '        cache trayekwisata_pemesanan & stats_publik diinvalidasi';
                }
            }
        }

        $log[] = '';
        $log[] = 'Selesai: '.(new DateTime('now', new DateTimeZone('Asia/Jakarta')))->format('H:i:s').' WIB';
        $log[] = str_repeat('-', 40);

        return ['batas' => $batasISO, 'batas_pemesanan' => $batasPemesananISO, 'log' => $log];
    }

    /**
     * @return array{http_code:int, deleted:int, rows:array, error:?string}
     */
    protected function deleteOld(string $table, string $column, string $batasISO): array
    {
        [$code, $rows] = $this->supabase->rawRequest('DELETE', $table.'?'.$column.'=lt.'.urlencode($batasISO));

        $deleted = ($code >= 200 && $code < 300 && is_array($rows)) ? $rows : [];

        return [
            'http_code' => $code,
            'deleted' => count($deleted),
            'rows' => $deleted,
            'error' => ($code >= 200 && $code < 300) ? null : "HTTP {$code}",
        ];
    }

    /**
     * @return array{deleted:int, error:?string}
     */
    protected function deleteByIds(string $table, string $column, array $ids): array
    {
        $totalDeleted = 0;
        $error = null;

        foreach (array_chunk($ids, 300) as $chunk) {
            $filter = $column.'=in.('.implode(',', array_map('rawurlencode', $chunk)).')';
            [$code, $rows] = $this->supabase->rawRequest('DELETE', $table.'?'.$filter);

            if ($code < 200 || $code >= 300) {
                $error = "HTTP {$code}";

                break;
            }
            $totalDeleted += is_array($rows) ? count($rows) : 0;
        }

        return ['deleted' => $totalDeleted, 'error' => $error];
    }
}
