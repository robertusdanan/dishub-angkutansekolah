<?php

namespace App\Http\Controllers\TrayekWisata;

use App\Http\Controllers\Controller;
use App\Services\AkunPublik\AkunPublikService;
use App\Services\AkunPublik\RateLimitExceededException;
use App\Services\Supabase\ReferenceCacheProxyService;
use App\Services\Supabase\SupabaseClient;
use App\Services\TrayekWisata\TrayekWisataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Port dari pages/trayekwisata/api/*.php.
 */
class TrayekWisataApiController extends Controller
{
    public function __construct(
        protected AkunPublikService $akun,
        protected TrayekWisataService $tw,
        protected SupabaseClient $supabase,
        protected ReferenceCacheProxyService $refCache,
    ) {
    }

    protected function requireLogin(): ?JsonResponse
    {
        if ($this->tw->isLoggedIn()) {
            return null;
        }

        return response()->json(['error' => 'Silakan masuk terlebih dahulu.', 'login_url' => '/trayek-wisata/auth/login'], 401);
    }

    protected function rateLimit(Request $request, string $key, int $max, int $windowSeconds): ?JsonResponse
    {
        try {
            $this->akun->rateLimit('tw_'.$key, $max, $windowSeconds);

            return null;
        } catch (RateLimitExceededException $e) {
            return response()->json(['error' => $e->getMessage()], 429);
        }
    }

    /** GET /trayek-wisata/api/auth-status - setara api/auth_status.php */
    public function authStatus(Request $request): JsonResponse
    {
        if (!$this->tw->isLoggedIn()) {
            return response()->json(['logged_in' => false]);
        }

        $profil = $this->akun->profil();
        if ($profil === null) {
            return response()->json(['logged_in' => false]);
        }

        $cekLengkap = $this->akun->cekKelengkapan(['nik', 'no_hp', 'foto_ktp_url']);

        return response()->json([
            'logged_in' => true,
            'nama' => $profil['nama'],
            'email' => $profil['email'],
            'avatar' => $request->session()->get('akun_publik_avatar', ''),
            'nik' => $profil['nik'],
            'no_hp' => $profil['no_hp'],
            'punya_ktp' => !empty($profil['foto_ktp_url']),
            'profil_lengkap' => $cekLengkap['lengkap'],
            'field_kurang' => $cekLengkap['kurang'],
        ]);
    }

    /** POST /trayek-wisata/api/cek-nik - setara api/cek_nik.php */
    public function cekNik(Request $request): JsonResponse
    {
        if ($fail = $this->requireLogin()) {
            return $fail;
        }
        if ($fail = $this->rateLimit($request, 'cek_nik', 20, 300)) {
            return $fail;
        }

        $penumpang = $request->input('penumpang', []);
        if (!is_array($penumpang) || count($penumpang) === 0) {
            return response()->json(['error' => 'Pilih minimal 1 penumpang.'], 400);
        }

        $hasil = [];
        foreach ($penumpang as $p) {
            $nik = trim((string) ($p['nik'] ?? ''));
            if (!$this->tw->validNik($nik)) {
                $hasil[] = ['nik' => $nik, 'nama' => $p['nama'] ?? '', 'blocked' => true, 'alasan' => 'Format NIK tidak valid'];

                continue;
            }
            $cek = $this->tw->cekNik4Minggu($nik);
            $hasil[] = [
                'nik' => $nik,
                'nama' => $p['nama'] ?? '',
                'blocked' => $cek['blocked'],
                'alasan' => $cek['blocked'] ? ('NIK ini sudah memperoleh tiket pada '.$cek['tanggal_terakhir'].' (kurang dari 4 minggu terakhir)') : null,
            ];
        }

        return response()->json(['hasil' => $hasil]);
    }

    /** GET /trayek-wisata/api/stats-publik - setara api/stats_publik.php */
    public function statsPublik(): JsonResponse
    {
        $cached = Cache::get('trayekwisata_stats');
        if ($cached !== null) {
            return response()->json($cached);
        }

        [$code, $rows] = $this->supabase->rawRequest('GET', 'trayekwisata_pemesanan?status=eq.terkonfirmasi&select=jumlah_kursi');
        $totalPeserta = 0;
        if ($code >= 200 && $code < 300 && is_array($rows)) {
            foreach ($rows as $r) {
                $totalPeserta += (int) ($r['jumlah_kursi'] ?? 0);
            }
        }

        [, $trayekRows] = $this->supabase->rawRequest('GET', 'trayekwisata_jadwal?select=id&status=neq.dibatalkan');
        $totalTrayek = is_array($trayekRows) ? count($trayekRows) : 0;

        $result = ['total_peserta' => $totalPeserta, 'total_trayek' => $totalTrayek];
        Cache::put('trayekwisata_stats', $result, 24 * 3600);

        return response()->json($result);
    }

    /** GET/POST /trayek-wisata/api/waitlist - setara api/waitlist.php */
    public function waitlist(Request $request): JsonResponse
    {
        if ($fail = $this->requireLogin()) {
            return $fail;
        }

        $profilId = $this->akun->id();

        if ($request->isMethod('get')) {
            [$code, $rows] = $this->supabase->rawRequest('GET', 'trayekwisata_waitlist?profil_id=eq.'.$profilId.'&select=jadwal_id');
            if ($code < 200 || $code >= 300) {
                return response()->json(['error' => 'Gagal memuat daftar tunggu.'], 502);
            }

            return response()->json(['jadwal_ids' => array_column($rows ?: [], 'jadwal_id')]);
        }

        if ($fail = $this->rateLimit($request, 'waitlist', 15, 300)) {
            return $fail;
        }

        $jadwalId = (string) $request->input('jadwal_id', '');
        if (!preg_match('/^[0-9a-fA-F-]{36}$/', $jadwalId)) {
            return response()->json(['error' => 'Jadwal tidak valid.'], 400);
        }

        [$code] = $this->supabase->request(
            'POST',
            '/rest/v1/trayekwisata_waitlist',
            ['jadwal_id' => $jadwalId, 'profil_id' => $profilId],
            [],
            true
        );
        // Prefer resolution=ignore-duplicates setara kode lama - di sini
        // cukup andalkan constraint unik DB; kode status non-2xx karena
        // duplikat dianggap "sudah terdaftar", bukan error keras.
        if ($code < 200 || $code >= 300) {
            return response()->json(['ok' => true, 'note' => 'Mungkin sudah terdaftar sebelumnya.']);
        }

        return response()->json(['ok' => true]);
    }

    /** POST /trayek-wisata/api/pesan - setara api/pesan.php */
    public function pesan(Request $request): JsonResponse
    {
        if ($fail = $this->requireLogin()) {
            return $fail;
        }
        if ($fail = $this->rateLimit($request, 'pesan', 8, 300)) {
            return $fail;
        }

        $profilId = $this->akun->id();
        $jadwalId = (string) $request->input('jadwal_id', '');
        $penumpang = $request->input('penumpang', []);

        if (!preg_match('/^[0-9a-fA-F-]{36}$/', $jadwalId)) {
            return response()->json(['error' => 'Jadwal tidak valid.'], 400);
        }
        if (!is_array($penumpang) || count($penumpang) === 0 || count($penumpang) > 10) {
            return response()->json(['error' => 'Pilih 1-10 penumpang.'], 400);
        }

        // 1. Profil harus lengkap & terverifikasi
        $profil = $this->akun->profil();
        if ($profil === null) {
            return response()->json(['error' => 'Profil tidak ditemukan.'], 404);
        }
        if (empty($profil['nik']) || empty($profil['no_hp']) || empty($profil['foto_ktp_url'])) {
            return response()->json(['error' => 'Lengkapi dan verifikasi profil Anda (NIK, No. HP, foto KTP) sebelum memesan.'], 422);
        }

        // 2. Validasi NIK penumpang harus akun sendiri / keluarga terdaftar
        [, $keluarga] = $this->supabase->rawRequest('GET', 'akun_publik_keluarga?akun_id=eq.'.$profilId.'&select=nik,nama');
        $keluargaNik = is_array($keluarga) ? array_column($keluarga, 'nik') : [];
        $daftarNikSah = array_merge([$profil['nik']], $keluargaNik);

        foreach ($penumpang as $p) {
            $nik = trim((string) ($p['nik'] ?? ''));
            if (!$this->tw->validNik($nik)) {
                return response()->json(['error' => 'Ada NIK penumpang yang formatnya tidak valid.'], 400);
            }
            if (!in_array($nik, $daftarNikSah, true)) {
                return response()->json(['error' => 'Penumpang harus akun Anda sendiri atau anggota keluarga yang sudah didaftarkan di halaman Akun.'], 422);
            }
        }

        // 3. Ambil jadwal & cek kuota
        [$jCode, $jRows] = $this->supabase->rawRequest('GET', 'trayekwisata_jadwal?id=eq.'.$jadwalId.'&select=*&limit=1');
        if ($jCode < 200 || $jCode >= 300 || empty($jRows)) {
            return response()->json(['error' => 'Jadwal tidak ditemukan.'], 404);
        }
        $jadwal = $jRows[0];

        if ($jadwal['status'] !== 'aktif') {
            return response()->json(['error' => 'Trayek ini sudah tidak menerima pemesanan.'], 422);
        }
        if (strtotime($jadwal['tanggal']) < strtotime(date('Y-m-d'))) {
            return response()->json(['error' => 'Trayek ini sudah lewat tanggalnya.'], 422);
        }

        $sisaKuota = (int) $jadwal['kuota_total'] - (int) $jadwal['kuota_terisi'];
        if ($sisaKuota < count($penumpang)) {
            return response()->json(['error' => "Kuota kursi tidak cukup. Sisa kursi: {$sisaKuota}."], 422);
        }

        // 4. Validasi ulang aturan 1 NIK / 4 minggu (final guard di server;
        //    constraint DB juga menjaga ini secara atomik).
        foreach ($penumpang as $p) {
            $nik = trim((string) $p['nik']);
            $cek = $this->tw->cekNik4Minggu($nik);
            if ($cek['blocked']) {
                return response()->json(['error' => "NIK {$nik} sudah memperoleh tiket pada {$cek['tanggal_terakhir']} (belum genap 4 minggu)."], 422);
            }
        }

        // 5. Buat pemesanan
        [$insCode, $pemesanan] = $this->supabase->rawRequest('POST', 'trayekwisata_pemesanan', [
            'jadwal_id' => $jadwalId,
            'profil_id' => $profilId,
            'jumlah_kursi' => count($penumpang),
            'status' => 'terkonfirmasi',
        ]);
        if ($insCode < 200 || $insCode >= 300 || empty($pemesanan)) {
            return response()->json(['error' => 'Gagal membuat pemesanan. Coba lagi.'], 502);
        }
        $pemesananId = $pemesanan[0]['id'];

        // 6. Catat tiap penumpang (trigger DB akan menolak kalau ternyata
        //    ada NIK yang baru saja "kebobolan" race condition).
        $gagalNik = [];
        foreach ($penumpang as $p) {
            [$nCode] = $this->supabase->rawRequest('POST', 'trayekwisata_pemesanan_nik', [
                'pemesanan_id' => $pemesananId,
                'jadwal_id' => $jadwalId,
                'nik' => trim((string) $p['nik']),
                'nama' => trim((string) ($p['nama'] ?? '')),
                'tanggal_trayek' => $jadwal['tanggal'],
            ]);
            if ($nCode < 200 || $nCode >= 300) {
                $gagalNik[] = $p;
            }
        }

        if (count($gagalNik) > 0) {
            $this->supabase->rawRequest('PATCH', 'trayekwisata_pemesanan?id=eq.'.$pemesananId, [
                'status' => 'dibatalkan',
                'catatan_admin' => 'Otomatis dibatalkan: sebagian NIK gagal validasi 4 minggu saat proses akhir.',
            ]);

            return response()->json(['error' => 'Sebagian NIK ternyata sudah dipakai tiket lain barusan. Pemesanan dibatalkan otomatis, silakan coba lagi.'], 409);
        }

        // Invalidasi cache: kuota_terisi berubah lewat trigger DB begitu
        // baris pemesanan/pemesanan_nik ini tercatat, statistik publik
        // (stats_publik) juga jadi basi. Dihapus di sini (aksi PUBLIK),
        // bukan cuma saat admin mengubah, supaya pengunjung lain langsung
        // melihat sisa kuota yang akurat.
        $this->refCache->invalidate('trayekwisata_pemesanan');
        Cache::forget('trayekwisata_stats');

        return response()->json(['ok' => true, 'pemesanan_id' => $pemesananId]);
    }

    /** POST /trayek-wisata/api/batalkan-pesanan - setara api/batalkan_pesanan.php */
    public function batalkanPesanan(Request $request): JsonResponse
    {
        if ($fail = $this->requireLogin()) {
            return $fail;
        }

        $profilId = $this->akun->id();
        $pemesananId = (string) $request->input('pemesanan_id', '');
        if (!preg_match('/^[0-9a-fA-F-]{36}$/', $pemesananId)) {
            return response()->json(['error' => 'ID tidak valid.'], 400);
        }

        // Pastikan pemesanan ini benar milik akun yang login, dan masih terkonfirmasi.
        [$code, $rows] = $this->supabase->rawRequest('GET', 'trayekwisata_pemesanan?id=eq.'.$pemesananId.'&profil_id=eq.'.$profilId.'&select=*,trayekwisata_jadwal(tanggal)&limit=1');
        if ($code < 200 || $code >= 300 || empty($rows)) {
            return response()->json(['error' => 'Pemesanan tidak ditemukan.'], 404);
        }
        $pemesanan = $rows[0];

        if ($pemesanan['status'] !== 'terkonfirmasi') {
            return response()->json(['error' => 'Pemesanan ini sudah tidak aktif.'], 422);
        }
        if (!empty($pemesanan['checked_in_at'])) {
            return response()->json(['error' => 'Tidak bisa membatalkan - Anda sudah tercatat hadir di titik keberangkatan.'], 422);
        }
        if (strtotime($pemesanan['trayekwisata_jadwal']['tanggal'] ?? 'now') < strtotime(date('Y-m-d'))) {
            return response()->json(['error' => 'Trayek ini sudah lewat, tidak bisa dibatalkan lagi.'], 422);
        }

        [$patchCode] = $this->supabase->rawRequest('PATCH', 'trayekwisata_pemesanan?id=eq.'.$pemesananId, [
            'status' => 'dibatalkan',
            'catatan_admin' => 'Dibatalkan sendiri oleh pemesan.',
        ]);
        if ($patchCode < 200 || $patchCode >= 300) {
            return response()->json(['error' => 'Gagal membatalkan pemesanan.'], 502);
        }

        $this->refCache->invalidate('trayekwisata_pemesanan');
        Cache::forget('trayekwisata_stats');

        return response()->json(['ok' => true]);
    }

    /** GET /trayek-wisata/api/pemesanan-saya - setara api/pemesanan_saya.php */
    public function pemesananSaya(): JsonResponse
    {
        if ($fail = $this->requireLogin()) {
            return $fail;
        }

        $profilId = $this->akun->id();

        [$code, $rows] = $this->supabase->rawRequest('GET', 'trayekwisata_pemesanan?profil_id=eq.'.$profilId
            .'&select=*,trayekwisata_jadwal(tanggal,hari,jam_berangkat,jam_pulang,trayekwisata_trayek(nama,warna))'
            .'&order=created_at.desc');
        if ($code < 200 || $code >= 300 || !is_array($rows)) {
            return response()->json(['error' => 'Gagal memuat pemesanan.'], 502);
        }

        $ids = array_column($rows, 'id');
        $nikByPemesanan = [];
        if (count($ids) > 0) {
            [, $nikRows] = $this->supabase->rawRequest('GET', 'trayekwisata_pemesanan_nik?pemesanan_id=in.('.implode(',', $ids).')&select=pemesanan_id,nama,nik');
            foreach ((is_array($nikRows) ? $nikRows : []) as $n) {
                $nikByPemesanan[$n['pemesanan_id']][] = $n;
            }
        }

        foreach ($rows as &$r) {
            $r['penumpang'] = $nikByPemesanan[$r['id']] ?? [];
        }
        unset($r);

        return response()->json($rows);
    }

    /** POST /trayek-wisata/api/survei - setara api/survei.php */
    public function survei(Request $request): JsonResponse
    {
        if ($fail = $this->rateLimit($request, 'survei', 5, 600)) {
            return $fail;
        }

        $rating = (int) $request->input('rating', 0);
        $komentar = trim((string) $request->input('komentar', ''));
        $pemesananId = (string) $request->input('pemesanan_id', '');

        if ($rating < 1 || $rating > 5) {
            return response()->json(['error' => 'Beri rating 1-5 bintang.'], 400);
        }
        if (strlen($komentar) > 1000) {
            $komentar = substr($komentar, 0, 1000);
        }

        $payload = ['rating' => $rating, 'komentar' => $komentar];
        if (preg_match('/^[0-9a-fA-F-]{36}$/', $pemesananId)) {
            $payload['pemesanan_id'] = $pemesananId;
        }

        [$code] = $this->supabase->rawRequest('POST', 'trayekwisata_survei', $payload);
        if ($code < 200 || $code >= 300) {
            return response()->json(['error' => 'Gagal mengirim, coba lagi.'], 502);
        }

        return response()->json(['ok' => true]);
    }
}
