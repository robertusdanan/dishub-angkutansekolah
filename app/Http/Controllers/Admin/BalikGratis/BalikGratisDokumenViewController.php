<?php

namespace App\Http\Controllers\Admin\BalikGratis;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminRoleService;
use App\Services\LocalMedia\DokumenService;
use App\Services\Supabase\SupabaseClient;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Port dari admin/api/balikgratis/dokumen_view.php.
 */
class BalikGratisDokumenViewController extends Controller
{
    public function __construct(
        protected AdminRoleService $roles,
        protected SupabaseClient $supabase,
        protected DokumenService $dokumen,
    ) {
    }

    public function show(Request $request): Response
    {
        if ($this->roles->isGuest()) {
            return response('Forbidden.', 403);
        }
        if (!$this->roles->isSuperAdmin() && !$this->roles->canAccessMenu('balikgratis_pemesanan') && !$this->roles->canAccessMenu('balikgratis_verifikasi')) {
            return response('Forbidden.', 403);
        }

        $id = trim((string) $request->query('id', ''));
        if (!preg_match('/^[0-9a-fA-F-]{36}$/', $id)) {
            return response('ID tidak valid.', 400);
        }

        [$code, $rows] = $this->supabase->rawRequest('GET', 'balikgratis_pemesanan?id=eq.'.$id.'&select=dokumen_filename,akun_id&limit=1');
        if ($code < 200 || $code >= 300 || empty($rows)) {
            return response('Pendaftaran tidak ditemukan.', 404);
        }
        $row = $rows[0];

        if (!empty($row['dokumen_filename'])) {
            // Baris LAMA (sebelum fitur akun terpusat).
            $filename = $row['dokumen_filename'];
            if (!preg_match('/^[a-z0-9_.-]+$/i', $filename)) {
                return response('Dokumen tidak ditemukan.', 404);
            }
            $path = $this->dokumen->dirLegacyBalikGratis().'/'.$filename;
        } elseif (!empty($row['akun_id'])) {
            // Baris BARU — dokumen (foto KTP/KK) hidup di profil akun_publik.
            $jenis = $request->query('jenis', 'ktp') === 'kk' ? 'kk' : 'ktp';
            $column = $jenis === 'ktp' ? 'foto_ktp_url' : 'foto_kk_url';
            [$aCode, $aRows] = $this->supabase->rawRequest('GET', 'akun_publik?id=eq.'.$row['akun_id'].'&select='.$column.'&limit=1');
            $filename = ($aCode >= 200 && $aCode < 300 && !empty($aRows[0][$column])) ? $aRows[0][$column] : null;
            if (!$filename || !preg_match('/^[a-z0-9_.-]+$/i', $filename)) {
                return response('Dokumen tidak ditemukan.', 404);
            }
            $path = $this->dokumen->dir($jenis).'/'.$filename;
        } else {
            return response('Dokumen tidak ditemukan.', 404);
        }

        if (!is_file($path)) {
            return response('Dokumen tidak ditemukan.', 404);
        }

        return response()->file($path, [
            'Content-Type' => 'image/jpeg',
            'Cache-Control' => 'private, no-store',
        ]);
    }
}
