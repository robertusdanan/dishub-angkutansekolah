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
 * Port dari admin/api/angkutansekolah/absensi_foto_report.php.
 */
class AbsensiFotoReportController extends Controller
{
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
        if ($this->roles->isGuest()) {
            return response()->json(['error' => 'Forbidden.'], 403);
        }

        $tz = new DateTimeZone('Asia/Jakarta');
        $today = new DateTime('now', $tz);

        $start = null;
        $end = null;
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

        $filters = [
            'transportasi' => strtoupper(trim((string) $request->query('transportasi', ''))),
            'trayek' => trim((string) $request->query('trayek', '')),
            'plat' => trim((string) $request->query('plat', '')),
            'sesi' => strtoupper(trim((string) $request->query('sesi', ''))),
        ];

        $rows = $this->absensi->hybridRows('absensi_foto', $start, $end, ['transportasi', 'trayek', 'plat_driver', 'sesi', 'waktu', 'foto']);

        $result = [];
        foreach ($rows as $r) {
            if ($filters['transportasi'] !== '' && strcasecmp((string) ($r['transportasi'] ?? ''), $filters['transportasi']) !== 0) {
                continue;
            }
            if ($filters['trayek'] !== '' && (string) ($r['trayek'] ?? '') !== $filters['trayek']) {
                continue;
            }
            if ($filters['plat'] !== '' && stripos((string) ($r['plat_driver'] ?? ''), $filters['plat']) === false) {
                continue;
            }
            if ($filters['sesi'] !== '' && strcasecmp((string) ($r['sesi'] ?? ''), $filters['sesi']) !== 0) {
                continue;
            }
            $result[] = $r;
        }

        usort($result, fn ($a, $b) => strcmp((string) ($b['waktu'] ?? ''), (string) ($a['waktu'] ?? '')));

        return response()->json(['rows' => $result]);
    }
}
