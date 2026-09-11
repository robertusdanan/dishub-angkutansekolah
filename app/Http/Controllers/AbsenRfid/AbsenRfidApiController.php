<?php

namespace App\Http\Controllers\AbsenRfid;

use App\Http\Controllers\Controller;
use App\Services\AbsenRfid\AbsenRfidService;
use App\Services\AbsenRfid\GpsTrackingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Port dari pages/absenrfid/php/{cek,cek_rfid,gps_write}.php.
 *
 * CATATAN MIGRASI: enforce_cfg_sync_api() (kill-switch remote) di kode lama
 * SENGAJA DIHAPUS, sama seperti modul lain.
 */
class AbsenRfidApiController extends Controller
{
    public function __construct(
        protected AbsenRfidService $absen,
        protected GpsTrackingService $gps,
    ) {
    }

    /** POST /absenrfid/php/cek — absen manual (input nama) */
    public function cek(Request $request): JsonResponse
    {
        $payload = (array) $request->input('payload', []);
        [$status, $body] = $this->absen->cekManual($payload);

        return response()->json($body, $status);
    }

    /** POST /absenrfid/php/cek-rfid — absen via kartu RFID */
    public function cekRfid(Request $request): JsonResponse
    {
        $payload = (array) $request->input('payload', []);
        [$status, $body] = $this->absen->cekRfid($payload);

        return response()->json($body, $status);
    }

    /** GET(action=init)/POST(action=save) /absenrfid/php/gps-write */
    public function gpsWrite(Request $request): JsonResponse
    {
        $table = (string) $request->query('table', '');
        $id = (string) $request->query('id', '');
        $action = (string) $request->query('action', '');

        if (!in_array($table, GpsTrackingService::ALLOWED_TABLES, true)) {
            return response()->json(['error' => 'Tabel tidak valid'], 400);
        }
        if ($id === '' || !ctype_alnum($id)) {
            return response()->json(['error' => 'ID tidak valid'], 400);
        }

        if ($action === 'init') {
            [$status, $body] = $this->gps->init($table, $id);

            return response()->json($body, $status);
        }

        if ($action === 'save') {
            $lat = $request->input('lat') !== null ? (float) $request->input('lat') : null;
            $lng = $request->input('lng') !== null ? (float) $request->input('lng') : null;
            [$status, $body] = $this->gps->save($table, $id, $lat, $lng);

            return response()->json($body, $status);
        }

        return response()->json(['error' => 'action tidak dikenali (pakai action=init atau action=save)'], 400);
    }
}
