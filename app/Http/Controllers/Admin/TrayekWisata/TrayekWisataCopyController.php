<?php

namespace App\Http\Controllers\Admin\TrayekWisata;

use App\Http\Controllers\Controller;
use App\Services\Admin\TrayekWisata\TrayekWisataCopyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Port dari admin/api/trayekwisata/copy.php.
 */
class TrayekWisataCopyController extends Controller
{
    public function __construct(protected TrayekWisataCopyService $copy)
    {
    }

    public function handle(Request $request): JsonResponse
    {
        if (!session('admin_logged_in') || session('admin_role') === 'guest') {
            return response()->json(['error' => 'Unauthorized.'], 401);
        }

        $source = $request->input('source');
        $mode = (string) $request->input('mode', '');
        $targets = $request->input('targets', []);

        if (!is_array($source) || empty($source['tahun']) || empty($source['bulan']) || !isset($source['minggu_ke'])) {
            return response()->json(['error' => 'Parameter source tidak lengkap.'], 400);
        }
        if (!in_array($mode, ['months', 'weeks'], true) || !is_array($targets) || count($targets) === 0) {
            return response()->json(['error' => 'Parameter mode/targets tidak valid.'], 400);
        }

        try {
            $result = $this->copy->copy($source, $mode, $targets);

            return response()->json($result);
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }
}
