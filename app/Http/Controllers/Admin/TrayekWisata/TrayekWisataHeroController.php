<?php

namespace App\Http\Controllers\Admin\TrayekWisata;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminRoleService;
use App\Services\Admin\TrayekWisata\TrayekWisataHeroService;
use App\Services\Supabase\ReferenceCacheProxyService;
use App\Services\Supabase\SupabaseClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TrayekWisataHeroController extends Controller
{
    public function __construct(
        protected AdminRoleService $roles,
        protected SupabaseClient $supabase,
        protected TrayekWisataHeroService $hero,
        protected ReferenceCacheProxyService $cache,
    ) {}

    private function guard(): ?JsonResponse
    {
        if (!session('admin_logged_in') || !$this->roles->canMenuAction('trayekwisata_hero', 'view')) {
            return response()->json(['error' => 'Unauthorized.'], 401);
        }
        return null;
    }

    public function upload(Request $request): JsonResponse
    {
        if ($r = $this->guard()) return $r;
        if (!$this->roles->canMenuAction('trayekwisata_hero', 'create')) return response()->json(['error' => 'Forbidden.'], 403);
        $file = $request->file('file');
        if (!$file || !$file->isValid()) return response()->json(['error' => 'File gagal diupload.'], 422);
        try { $media = $this->hero->upload($file); } catch (\RuntimeException $e) { return response()->json(['error' => $e->getMessage()], 422); }
        [, $last] = $this->supabase->rawRequest('GET', 'trayekwisata_hero?select=urutan&order=urutan.desc&limit=1');
        $urutan = empty($last) ? 0 : ((int) $last[0]['urutan'] + 1);
        [$code, $rows] = $this->supabase->rawRequest('POST', 'trayekwisata_hero', [['tipe' => $media['tipe'], 'url' => $media['url'], 'urutan' => $urutan, 'aktif' => true]]);
        if ($code < 200 || $code >= 300 || empty($rows)) { $this->hero->delete($media['url']); return response()->json(['error' => 'Media tersimpan, tetapi gagal dicatat.'], 500); }
        $this->cache->invalidate('trayekwisata_hero');
        return response()->json($rows[0]);
    }

    public function reorder(Request $request): JsonResponse
    {
        if ($r = $this->guard()) return $r;
        if (!$this->roles->canMenuAction('trayekwisata_hero', 'edit')) return response()->json(['error' => 'Forbidden.'], 403);
        $ids = $request->input('ids');
        if (!is_array($ids) || count($ids) > 100) return response()->json(['error' => 'Urutan tidak valid.'], 422);
        foreach ($ids as $i => $id) {
            if (!preg_match('/^[0-9a-fA-F-]{36}$/', (string) $id)) return response()->json(['error' => 'ID media tidak valid.'], 422);
            $this->supabase->rawRequest('PATCH', 'trayekwisata_hero?id=eq.'.rawurlencode($id), ['urutan' => $i]);
        }
        $this->cache->invalidate('trayekwisata_hero');
        return response()->json(['ok' => true]);
    }

    public function delete(Request $request): JsonResponse
    {
        if ($r = $this->guard()) return $r;
        if (!$this->roles->canMenuAction('trayekwisata_hero', 'delete')) return response()->json(['error' => 'Forbidden.'], 403);
        $id = (string) $request->input('id');
        if (!preg_match('/^[0-9a-fA-F-]{36}$/', $id)) return response()->json(['error' => 'ID media tidak valid.'], 422);
        [, $rows] = $this->supabase->rawRequest('GET', 'trayekwisata_hero?id=eq.'.rawurlencode($id).'&select=url');
        $this->supabase->rawRequest('DELETE', 'trayekwisata_hero?id=eq.'.rawurlencode($id));
        if (!empty($rows[0]['url'])) $this->hero->delete($rows[0]['url']);
        $this->cache->invalidate('trayekwisata_hero');
        return response()->json(['ok' => true]);
    }
}
