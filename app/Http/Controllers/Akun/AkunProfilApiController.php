<?php

namespace App\Http\Controllers\Akun;

use App\Http\Controllers\Controller;
use App\Services\AkunPublik\AkunPublikService;
use App\Services\Supabase\SupabaseClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Port dari pages/akun/api/profil.php dan pages/akun/api/keluarga.php.
 */
class AkunProfilApiController extends Controller
{
    public function __construct(
        protected AkunPublikService $akun,
        protected SupabaseClient $supabase,
    ) {
    }

    /**
     * Setara akun_require_login() - dipanggil di awal tiap method.
     */
    protected function requireLogin(Request $request): ?JsonResponse
    {
        if ($this->akun->isLogin()) {
            return null;
        }

        return response()->json([
            'error' => 'Silakan masuk dengan akun Google terlebih dahulu.',
            'login_url' => '/akun/masuk?next='.rawurlencode($request->fullUrl()),
        ], 401);
    }

    /**
     * GET/POST /akun/api/profil - setara profil.php
     */
    public function profil(Request $request): JsonResponse
    {
        if ($fail = $this->requireLogin($request)) {
            return $fail;
        }

        $akunId = $this->akun->id();

        if ($request->isMethod('get')) {
            [$code, $rows] = $this->supabase->select('akun_publik', ['id' => 'eq.'.$akunId], 1);
            if ($code < 200 || $code >= 300 || empty($rows)) {
                return response()->json(['error' => 'Profil tidak ditemukan.'], 404);
            }

            return response()->json($rows[0]);
        }

        // POST
        $input = $request->all();

        $nama = trim((string) ($input['nama'] ?? ''));
        $namaSesuaiKtp = (bool) ($input['nama_sesuai_ktp'] ?? false);
        $nik = trim((string) ($input['nik'] ?? ''));
        $noHp = trim((string) ($input['no_hp'] ?? ''));
        $jk = (string) ($input['jenis_kelamin'] ?? '');
        $noKk = trim((string) ($input['no_kk'] ?? ''));
        $alamat = trim((string) ($input['alamat_lengkap'] ?? ''));
        $kabupaten = trim((string) ($input['alamat_kabupaten'] ?? ''));
        $kecamatan = trim((string) ($input['alamat_kecamatan'] ?? ''));
        $kodepos = trim((string) ($input['alamat_kode_pos'] ?? ''));

        if ($nama === '') {
            return response()->json(['error' => 'Nama wajib diisi.'], 400);
        }
        if ($nik !== '' && !$this->akun->validNik($nik)) {
            return response()->json(['error' => 'NIK harus 16 digit angka.'], 400);
        }
        if ($noHp !== '' && !$this->akun->validHp($noHp)) {
            return response()->json(['error' => 'Nomor HP harus angka, 9-14 digit.'], 400);
        }
        if (!in_array($jk, ['Laki-laki', 'Perempuan'], true)) {
            $jk = null;
        }

        // NIK adalah identitas unik satu akun - cegah dipakai akun lain.
        if ($nik !== '') {
            [, $dup] = $this->supabase->select('akun_publik', [
                'nik' => 'eq.'.$nik,
                'id' => 'neq.'.$akunId,
                'select' => 'id',
            ], 1);
            if (is_array($dup) && count($dup) > 0) {
                return response()->json(['error' => 'NIK ini sudah terdaftar di akun lain.'], 422);
            }
        }

        $payload = [
            'nama' => $nama,
            'nama_sesuai_ktp' => $namaSesuaiKtp,
            'nik' => $nik !== '' ? $nik : null,
            'no_hp' => $noHp !== '' ? $noHp : null,
            'jenis_kelamin' => $jk,
            'no_kk' => $noKk !== '' ? $noKk : null,
            'alamat_lengkap' => $alamat !== '' ? $alamat : null,
            'alamat_kabupaten' => $kabupaten !== '' ? $kabupaten : null,
            'alamat_kecamatan' => $kecamatan !== '' ? $kecamatan : null,
            'alamat_kode_pos' => $kodepos !== '' ? $kodepos : null,
            'updated_at' => date('c'),
        ];

        [$code, $result] = $this->supabase->update('akun_publik', $payload, ['id' => 'eq.'.$akunId]);
        if ($code < 200 || $code >= 300) {
            return response()->json(['error' => 'Gagal menyimpan profil.'], 502);
        }

        $request->session()->put('akun_publik_nama', $nama);

        return response()->json(['ok' => true, 'profil' => $result[0] ?? null]);
    }

    /**
     * GET/POST/DELETE /akun/api/keluarga - setara keluarga.php
     */
    public function keluarga(Request $request): JsonResponse
    {
        if ($fail = $this->requireLogin($request)) {
            return $fail;
        }

        $akunId = $this->akun->id();

        if ($request->isMethod('get')) {
            [$code, $rows] = $this->supabase->select(
                'akun_publik_keluarga',
                ['akun_id' => 'eq.'.$akunId],
                null,
                'created_at.asc'
            );
            if ($code < 200 || $code >= 300) {
                return response()->json(['error' => 'Gagal memuat data keluarga.'], 502);
            }

            return response()->json($rows ?: []);
        }

        if ($request->isMethod('post')) {
            $nama = trim((string) $request->input('nama', ''));
            $nik = trim((string) $request->input('nik', ''));
            $jk = (string) $request->input('jenis_kelamin', '');
            $status = (string) $request->input('status', 'anak');

            if ($nama === '') {
                return response()->json(['error' => 'Nama anggota keluarga wajib diisi.'], 400);
            }
            if (!$this->akun->validNik($nik)) {
                return response()->json(['error' => 'NIK harus 16 digit angka.'], 400);
            }
            if (!in_array($jk, ['Laki-laki', 'Perempuan'], true)) {
                $jk = null;
            }
            if (!in_array($status, ['istri', 'suami', 'anak', 'orang_tua', 'saudara', 'lainnya'], true)) {
                $status = 'lainnya';
            }

            // NIK tidak boleh sama dengan akun sendiri atau anggota keluarga akun lain.
            $profil = $this->akun->profil();
            if ($nik === ($profil['nik'] ?? null)) {
                return response()->json(['error' => 'NIK ini sama dengan NIK akun Anda sendiri.'], 422);
            }

            [, $existing] = $this->supabase->select('akun_publik_keluarga', [
                'nik' => 'eq.'.$nik,
                'select' => 'id',
            ], 1);
            if (is_array($existing) && count($existing) > 0) {
                return response()->json(['error' => 'NIK ini sudah terdaftar sebagai anggota keluarga (di akun manapun).'], 422);
            }

            [$code, $result] = $this->supabase->insert('akun_publik_keluarga', [
                'akun_id' => $akunId,
                'nama' => $nama,
                'nik' => $nik,
                'jenis_kelamin' => $jk,
                'status_keluarga' => $status,
            ]);
            if ($code < 200 || $code >= 300 || empty($result)) {
                return response()->json(['error' => 'Gagal menambahkan anggota keluarga.'], 502);
            }

            return response()->json(['ok' => true, 'anggota' => $result[0]]);
        }

        // DELETE
        $id = $request->query('id', '');
        if ($id === '') {
            return response()->json(['error' => 'ID tidak lengkap.'], 400);
        }

        // Filter akun_id=eq.$akunId adalah PENJAGA UTAMA supaya seseorang
        // tidak bisa menghapus anggota keluarga akun ORANG LAIN hanya
        // dengan menebak/mengubah ID di request - bukan sekadar validasi UI.
        [$code] = $this->supabase->delete('akun_publik_keluarga', [
            'id' => 'eq.'.$id,
            'akun_id' => 'eq.'.$akunId,
        ]);
        if ($code < 200 || $code >= 300) {
            return response()->json(['error' => 'Gagal menghapus.'], 502);
        }

        return response()->json(['ok' => true]);
    }
}
