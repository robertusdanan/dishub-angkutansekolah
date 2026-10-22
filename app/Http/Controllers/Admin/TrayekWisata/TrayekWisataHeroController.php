<?php

namespace App\Http\Controllers\Admin\TrayekWisata;

use App\Http\Controllers\Controller;
use App\Models\TrayekWisataHero;
use App\Services\Admin\AdminRoleService;
use App\Services\Admin\TrayekWisata\TrayekWisataHeroService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TrayekWisataHeroController extends Controller
{
    public function __construct(
        protected AdminRoleService $roles,
        protected TrayekWisataHeroService $hero,
    ) {}

    private function guard(): ?JsonResponse
    {
        if (!session('admin_logged_in') || !$this->roles->canMenuAction('trayekwisata_hero', 'view')) {
            return response()->json(['error' => 'Unauthorized.'], 401);
        }
        return null;
    }

    public function index(): JsonResponse
    {
        if ($r = $this->guard()) return $r;
        return response()->json(TrayekWisataHero::query()->orderBy('urutan')->get());
    }

    public function upload(Request $request): JsonResponse
    {
        if ($r = $this->guard()) return $r;
        if (!$this->roles->canMenuAction('trayekwisata_hero', 'create')) return response()->json(['error' => 'Forbidden.'], 403);
        $file = $request->file('file');
        if (!$file || !$file->isValid()) return response()->json(['error' => 'File gagal diupload.'], 422);
        try { $media = $this->hero->upload($file); } catch (\RuntimeException $e) { return response()->json(['error' => $e->getMessage()], 422); }
        $urutan = ((int) TrayekWisataHero::max('urutan')) + 1;
        try {
            $row = TrayekWisataHero::create(['tipe' => $media['tipe'], 'url' => $media['url'], 'urutan' => $urutan, 'aktif' => true]);
        } catch (\Throwable $e) {
            $this->hero->delete($media['url']);
            return response()->json(['error' => 'Media tersimpan, tetapi gagal dicatat ke database lokal.'], 500);
        }
        return response()->json($row);
    }

    public function reorder(Request $request): JsonResponse
    {
        if ($r = $this->guard()) return $r;
        if (!$this->roles->canMenuAction('trayekwisata_hero', 'edit')) return response()->json(['error' => 'Forbidden.'], 403);
        $ids = $request->input('ids');
        if (!is_array($ids) || count($ids) > 100) return response()->json(['error' => 'Urutan tidak valid.'], 422);
        foreach ($ids as $i => $id) {
            if (!preg_match('/^[0-9a-fA-F-]{36}$/', (string) $id)) return response()->json(['error' => 'ID media tidak valid.'], 422);
            TrayekWisataHero::whereKey($id)->update(['urutan' => $i]);
        }
        return response()->json(['ok' => true]);
    }

    public function delete(Request $request): JsonResponse
    {
        if ($r = $this->guard()) return $r;
        if (!$this->roles->canMenuAction('trayekwisata_hero', 'delete')) return response()->json(['error' => 'Forbidden.'], 403);
        $id = (string) $request->input('id');
        if (!preg_match('/^[0-9a-fA-F-]{36}$/', $id)) return response()->json(['error' => 'ID media tidak valid.'], 422);
        $row = TrayekWisataHero::find($id);
        if (!$row) return response()->json(['error' => 'Media tidak ditemukan.'], 404);
        $row->delete();
        $this->hero->delete($row->url);
        return response()->json(['ok' => true]);
    }
}
