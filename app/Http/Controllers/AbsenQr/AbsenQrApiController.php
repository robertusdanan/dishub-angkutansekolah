<?php

namespace App\Http\Controllers\AbsenQr;

use App\Http\Controllers\Controller;
use App\Services\AbsenQr\AbsenQrService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Port dari pages/absenqrcode/php/{cek,status}.php.
 */
class AbsenQrApiController extends Controller
{
    public function __construct(protected AbsenQrService $absen)
    {
    }

    /** POST /absenqrcode/php/cek */
    public function cek(Request $request): JsonResponse
    {
        $payload = (array) $request->input('payload', []);
        [$status, $body] = $this->absen->cek($payload);

        return response()->json($body, $status);
    }

    /** GET/POST /absenqrcode/php/status */
    public function status(Request $request): JsonResponse
    {
        $email = trim((string) ($request->input('email') ?? $request->query('email', '')));
        [$status, $body] = $this->absen->status($email);

        return response()->json($body, $status);
    }
}
