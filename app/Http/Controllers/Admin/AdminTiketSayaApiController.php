<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminRoleService;
use App\Services\AkunPublik\AkunPublikService;
use App\Services\Supabase\SupabaseClient;
use Illuminate\Http\JsonResponse;

/**
 * Port dari admin/api/tiket_saya.php.
 *
 * SENGAJA TIDAK memakai requireMenuAccess()/canAccessMenu() milik staf -
 * ini murni self-service, cukup isPenggunaPublik(). Data difilter dari
 * session (akun_id), TIDAK menerima id dari client sama sekali - sama
 * seperti kode lama, supaya tidak mungkin dipakai mengintip tiket akun lain.
 */
class AdminTiketSayaApiController extends Controller
{
    public function __construct(
        protected AdminRoleService $roles,
        protected AkunPublikService $akun,
        protected SupabaseClient $supabase,
    ) {
    }

    public function index(): JsonResponse
    {
        if (!$this->roles->isPenggunaPublik()) {
            return response()->json(['error' => 'Halaman ini hanya untuk akun pengguna.'], 403);
        }

        $akunId = $this->akun->id();
        if (!$akunId) {
            return response()->json(['error' => 'Sesi tidak valid.'], 401);
        }

        [, $twRows] = $this->supabase->rawRequest('GET', 'trayekwisata_pemesanan?profil_id=eq.'.$akunId
            .'&select=id,status,jumlah_kursi,created_at,checked_in_at,trayekwisata_jadwal(tanggal,jam_berangkat,trayekwisata_trayek(nama))'
            .'&order=created_at.desc');

        $twTickets = [];
        foreach ((array) $twRows as $r) {
            $twTickets[] = [
                'layanan' => 'trayekwisata',
                'label' => 'Trayek Wisata',
                'id' => $r['id'],
                'nama_trayek' => $r['trayekwisata_jadwal']['trayekwisata_trayek']['nama'] ?? '-',
                'tanggal' => $r['trayekwisata_jadwal']['tanggal'] ?? null,
                'jam' => $r['trayekwisata_jadwal']['jam_berangkat'] ?? null,
                'jumlah_kursi' => $r['jumlah_kursi'] ?? null,
                'status' => $r['status'] ?? '-',
                'checked_in' => !empty($r['checked_in_at']),
                'created_at' => $r['created_at'] ?? null,
            ];
        }

        [, $bgRows] = $this->supabase->rawRequest('GET', 'balikgratis_pemesanan?akun_id=eq.'.$akunId
            .'&select=id,tahun,nomor_tiket,kategori,status,created_at&order=created_at.desc');

        $bgTickets = [];
        foreach ((array) $bgRows as $r) {
            $bgTickets[] = [
                'layanan' => 'balikgratis',
                'label' => 'Balik Gratis',
                'id' => $r['id'],
                'nomor_tiket' => $r['nomor_tiket'] ?? '-',
                'tahun' => $r['tahun'] ?? null,
                'kategori' => $r['kategori'] ?? null,
                'status' => $r['status'] ?? '-',
                'created_at' => $r['created_at'] ?? null,
            ];
        }

        return response()->json(['trayekwisata' => $twTickets, 'balikgratis' => $bgTickets]);
    }
}
