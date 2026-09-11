<?php

namespace App\Http\Controllers\Admin\TrayekWisata;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminRoleService;
use App\Services\LocalMedia\DokumenService;
use App\Services\Supabase\SupabaseClient;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Port dari admin/api/trayekwisata/dokumen_view.php.
 */
class TrayekWisataDokumenViewController extends Controller
{
    public function __construct(
        protected AdminRoleService $roles,
        protected SupabaseClient $supabase,
        protected DokumenService $dokumen,
    ) {
    }

    public function show(Request $request): Response
    {
        if (!session('admin_logged_in') || $this->roles->isGuest()) {
            return response('Unauthorized.', 401);
        }
        if (!$this->roles->isSuperAdmin() && !$this->roles->canAccessMenu('trayekwisata_pemesanan')) {
            return response('Forbidden.', 403);
        }

        $profilId = (string) $request->query('profil_id', '');
        $jenis = (string) $request->query('jenis', '');
        if (!preg_match('/^[0-9a-fA-F-]{36}$/', $profilId) || !in_array($jenis, ['ktp', 'kk'], true)) {
            return response('Parameter tidak valid.', 400);
        }

        $column = $jenis === 'ktp' ? 'foto_ktp_url' : 'foto_kk_url';
        [, $rows] = $this->supabase->rawRequest('GET', 'akun_publik?id=eq.'.$profilId.'&select='.$column.'&limit=1');
        $filename = $rows[0][$column] ?? null;

        if (!$filename || !preg_match('/^[a-z0-9_.-]+$/i', $filename)) {
            return response('Dokumen tidak ditemukan.', 404);
        }

        $path = $this->dokumen->dir($jenis).'/'.$filename;
        if (!is_file($path)) {
            return response('Dokumen tidak ditemukan.', 404);
        }

        return response()->file($path, [
            'Content-Type' => 'image/jpeg',
            'Cache-Control' => 'private, no-store',
        ]);
    }
}
