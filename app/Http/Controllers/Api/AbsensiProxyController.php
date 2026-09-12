<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Admin\AngkutanSekolah\AbsensiHybridService;
use DateTime;
use DateTimeZone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Port dari api/absensi_proxy.php.
 *
 * CATATAN MIGRASI: enforce_cfg_sync_api() (kill-switch) SENGAJA DIHAPUS,
 * sama seperti modul lain. Endpoint ini memang PUBLIK (tidak ada
 * pengecekan admin_logged_in) persis seperti kode lama - halaman
 * pemanggilnya (Rekap Operasional) yang dijaga login admin, bukan
 * endpoint datanya sendiri.
 */
class AbsensiProxyController extends Controller
{
    protected const ALLOWED_TABLES = ['absensi_RFID', 'absensi_QRCode', 'absensi_foto'];

    public function __construct(protected AbsensiHybridService $absensi)
    {
    }

    public function show(Request $request): JsonResponse
    {
        $table = trim((string) $request->query('table', ''));
        if (!in_array($table, self::ALLOWED_TABLES, true)) {
            return response()->json(['error' => 'Tabel tidak diizinkan: '.$table], 400);
        }

        $startRaw = trim((string) $request->query('start', ''));
        $endRaw = trim((string) $request->query('end', ''));
        $dateRe = '/^\d{4}-\d{2}-\d{2}$/';
        if (!preg_match($dateRe, $startRaw) || !preg_match($dateRe, $endRaw)) {
            return response()->json(['error' => 'Param start/end wajib format YYYY-MM-DD'], 400);
        }

        $tz = new DateTimeZone('Asia/Jakarta');
        $start = DateTime::createFromFormat('Y-m-d H:i:s', $startRaw.' 00:00:00', $tz);
        $end = DateTime::createFromFormat('Y-m-d H:i:s', $endRaw.' 23:59:59', $tz);

        if (!$start || !$end || $start > $end) {
            return response()->json(['error' => 'Rentang tanggal tidak valid'], 400);
        }

        if ($start->diff($end)->days > 400) {
            return response()->json(['error' => 'Rentang tanggal terlalu panjang (maks 400 hari)'], 400);
        }

        $selectCols = null;
        if ($request->filled('select')) {
            $selectCols = array_values(array_filter(array_map('trim', explode(',', (string) $request->query('select')))));
        }

        return response()->json($this->absensi->hybridRows($table, $start, $end, $selectCols))
            ->header('Access-Control-Allow-Origin', url('/'));
    }
}
