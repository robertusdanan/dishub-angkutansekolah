<?php

namespace App\Http\Controllers\Admin\AngkutanSekolah;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminRoleService;
use App\Services\Admin\AngkutanSekolah\AbsensiHybridService;
use DateTime;
use DateTimeZone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Port dari admin/api/angkutansekolah/absensi_report.php.
 */
class AbsensiReportController extends Controller
{
    protected const SOURCES = [
        ['table' => 'absensi_RFID', 'source' => 'absensi_RFID', 'id_col' => 'nik'],
        ['table' => 'absensi_QRCode', 'source' => 'absensi_QRCode', 'id_col' => 'email'],
    ];

    public function __construct(
        protected AdminRoleService $roles,
        protected AbsensiHybridService $absensi,
    ) {
    }

    public function handle(Request $request): JsonResponse
    {
        if (!session('admin_logged_in')) {
            return response()->json(['error' => 'Unauthorized: silakan login ulang.'], 401);
        }

        $tz = new DateTimeZone('Asia/Jakarta');
        $today = new DateTime('now', $tz);
        $isGuest = $this->roles->isGuest();

        $start = null;
        $end = null;

        if ($isGuest) {
            [$start, $end] = $this->absensi->dayBounds($tz, (int) $today->format('Y'), (int) $today->format('n'), (int) $today->format('j'));
        } else {
            $tanggal = trim((string) $request->query('tanggal', ''));

            if ($tanggal === 'today') {
                [$start, $end] = $this->absensi->dayBounds($tz, (int) $today->format('Y'), (int) $today->format('n'), (int) $today->format('j'));
            } elseif ($tanggal === 'week') {
                $rs = trim((string) $request->query('range_start', ''));
                $re = trim((string) $request->query('range_end', ''));
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $rs) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $re)) {
                    $sd = DateTime::createFromFormat('Y-m-d', $rs, $tz);
                    $ed = DateTime::createFromFormat('Y-m-d', $re, $tz);
                    if ($sd && $ed && $sd <= $ed) {
                        [$start] = $this->absensi->dayBounds($tz, (int) $sd->format('Y'), (int) $sd->format('n'), (int) $sd->format('j'));
                        [, $end] = $this->absensi->dayBounds($tz, (int) $ed->format('Y'), (int) $ed->format('n'), (int) $ed->format('j'));
                    }
                }
            } elseif ($tanggal === 'month') {
                $rm = trim((string) $request->query('range_month', ''));
                if (preg_match('/^\d{4}-\d{2}$/', $rm)) {
                    [$y, $m] = array_map('intval', explode('-', $rm));
                    $lastDay = (int) DateTime::createFromFormat('Y-m-d', sprintf('%04d-%02d-01', $y, $m), $tz)->format('t');
                    [$start] = $this->absensi->dayBounds($tz, $y, $m, 1);
                    [, $end] = $this->absensi->dayBounds($tz, $y, $m, $lastDay);
                }
            }
        }

        $filters = [
            'transportasi' => strtoupper(trim((string) $request->query('transportasi', ''))),
            'trayek' => trim((string) $request->query('trayek', '')),
            'driver' => trim((string) $request->query('driver', '')),
            'search' => trim((string) $request->query('search', '')),
            'absen' => trim((string) $request->query('absen', '')),
        ];
        if ($isGuest) {
            $filters['trayek'] = '';
            $filters['driver'] = '';
        }

        $merged = [];
        foreach (self::SOURCES as $src) {
            $rows = $this->absensi->hybridRows($src['table'], $start, $end);
            foreach ($rows as $r) {
                if (!$this->absensi->rowMatches($r, $filters, $src['id_col'], $tz)) {
                    continue;
                }
                $r['_source'] = $src['source'];
                $merged[] = $r;
            }
        }

        usort($merged, fn ($a, $b) => strcmp((string) ($b['waktu'] ?? ''), (string) ($a['waktu'] ?? '')));

        if ($isGuest) {
            foreach ($merged as &$r) {
                unset($r['nik'], $r['email']);
            }
            unset($r);
        }

        if ($request->query('action', 'page') === 'export') {
            return response()->json(['rows' => $merged]);
        }

        $total = count($merged);
        $page = max(1, (int) $request->query('page', 1));
        $pageSize = max(1, min(500, (int) $request->query('pageSize', 50)));
        $offset = ($page - 1) * $pageSize;

        return response()->json([
            'rows' => array_slice($merged, $offset, $pageSize),
            'total' => $total,
        ]);
    }
}
