<?php

namespace App\Http\Controllers\BalikGratis;

use App\Http\Controllers\Controller;
use App\Services\AkunPublik\AkunPublikService;
use App\Services\AkunPublik\RateLimitExceededException;
use App\Services\BalikGratis\BalikGratisService;
use App\Services\Supabase\SupabaseClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Port dari pages/balikgratis/api/*.php.
 */
class BalikGratisApiController extends Controller
{
    public function __construct(
        protected AkunPublikService $akun,
        protected BalikGratisService $bg,
        protected SupabaseClient $supabase,
    ) {
    }

    protected function rateLimit(string $key, int $max, int $windowSeconds): ?JsonResponse
    {
        try {
            $this->akun->rateLimit('bg_'.$key, $max, $windowSeconds);

            return null;
        } catch (RateLimitExceededException $e) {
            return response()->json(['error' => $e->getMessage()], 429);
        }
    }

    /** GET /balikgratis/api/status - setara api/status.php */
    public function status(): JsonResponse
    {
        $tahun = $this->bg->tahunIni();
        $pengaturan = $this->bg->pengaturanTahun($tahun);
        $buka = ($pengaturan['status'] ?? 'tutup') === 'buka';
        $kuota = (int) ($pengaturan['kuota'] ?? 0);
        $terisi = $buka ? $this->bg->terisiTahun($tahun) : 0;

        return response()->json([
            'tahun' => $tahun,
            'status' => $buka ? 'buka' : 'tutup',
            'kuota' => $kuota,
            'terisi' => $terisi,
            'sisa' => max(0, $kuota - $terisi),
        ]);
    }

    /** GET /balikgratis/api/auth-status - setara api/auth_status.php */
    public function authStatus(Request $request): JsonResponse
    {
        if (!$this->akun->isLogin()) {
            return response()->json(['logged_in' => false]);
        }

        $profil = $this->akun->profil();
        if ($profil === null) {
            return response()->json(['logged_in' => false]);
        }

        $wajib = ['nik', 'no_kk', 'jenis_kelamin', 'alamat_lengkap', 'no_hp', 'foto_ktp_url', 'foto_kk_url'];
        $cek = $this->akun->cekKelengkapan($wajib);

        return response()->json([
            'logged_in' => true,
            'nama' => $profil['nama'],
            'email' => $profil['email'],
            'avatar' => $request->session()->get('akun_publik_avatar', ''),
            'nik' => $profil['nik'],
            'profil_lengkap' => $cek['lengkap'],
            'field_kurang' => array_map(fn ($f) => $this->akun->labelField($f), $cek['kurang']),
        ]);
    }

    /** GET /balikgratis/api/cek-tiket - setara api/cek_tiket.php */
    public function cekTiket(Request $request): JsonResponse
    {
        if ($fail = $this->rateLimit('cek_tiket', 20, 300)) {
            return $fail;
        }

        $nik = trim((string) $request->query('nik', ''));
        if (!$this->bg->validNik($nik)) {
            return response()->json(['error' => 'NIK tidak valid.'], 422);
        }

        [$code, $rows] = $this->supabase->rawRequest('GET', 'balikgratis_pemesanan?nik=eq.'.rawurlencode($nik)
            .'&select=nomor_tiket,qr_token,tahun,nama,nik,kategori,jenis_kelamin,status,created_at'
            .'&order=tahun.desc');

        if ($code < 200 || $code >= 300 || !is_array($rows)) {
            return response()->json(['error' => 'Gagal mencari data tiket.'], 502);
        }

        return response()->json(['ok' => true, 'tiket' => $rows]);
    }

    /** POST /balikgratis/api/daftar - setara api/daftar.php */
    public function daftar(Request $request): JsonResponse
    {
        if (!$this->akun->isLogin()) {
            return response()->json([
                'error' => 'Silakan masuk dengan akun Google terlebih dahulu.',
                'login_url' => '/akun/masuk?next='.rawurlencode('/balikgratis'),
            ], 401);
        }
        if ($fail = $this->rateLimit('daftar', 6, 300)) {
            return $fail;
        }

        $akunId = $this->akun->id();
        $tahun = $this->bg->tahunIni();

        $pengaturan = $this->bg->pengaturanTahun($tahun);
        if (($pengaturan['status'] ?? 'tutup') !== 'buka') {
            return response()->json(['error' => "Pendaftaran Balik Gratis {$tahun} sedang tidak dibuka."], 422);
        }
        $kuota = (int) ($pengaturan['kuota'] ?? 0);

        $wajib = ['nik', 'no_kk', 'jenis_kelamin', 'alamat_lengkap', 'no_hp', 'foto_ktp_url', 'foto_kk_url'];
        $cek = $this->akun->cekKelengkapan($wajib);
        if (!$cek['lengkap']) {
            $labelKurang = array_map(fn ($f) => $this->akun->labelField($f), $cek['kurang']);

            return response()->json([
                'error' => 'Lengkapi dulu data akun Anda sebelum mendaftar: '.implode(', ', $labelKurang).'.',
                'field_kurang' => $cek['kurang'],
                'lengkapi_url' => '/akun/saya',
            ], 422);
        }
        $profil = $this->akun->profil();

        $penumpang = $request->input('penumpang', []);
        if (!is_array($penumpang) || count($penumpang) === 0 || count($penumpang) > 10) {
            return response()->json(['error' => 'Pilih 1-10 penumpang.'], 400);
        }

        [, $keluarga] = $this->supabase->rawRequest('GET', 'akun_publik_keluarga?akun_id=eq.'.$akunId.'&select=nik,nama,jenis_kelamin');
        $dataPerNik = [$profil['nik'] => ['nama' => $profil['nama'], 'jenis_kelamin' => $profil['jenis_kelamin']]];
        foreach ((is_array($keluarga) ? $keluarga : []) as $k) {
            $dataPerNik[$k['nik']] = ['nama' => $k['nama'], 'jenis_kelamin' => $k['jenis_kelamin']];
        }

        $daftarValid = [];
        foreach ($penumpang as $p) {
            $nik = trim((string) ($p['nik'] ?? ''));
            $kategori = trim((string) ($p['kategori'] ?? ''));
            if (!$this->bg->validNik($nik)) {
                return response()->json(['error' => 'Ada NIK penumpang yang formatnya tidak valid.'], 400);
            }
            if (!isset($dataPerNik[$nik])) {
                return response()->json(['error' => 'Penumpang harus akun Anda sendiri atau anggota keluarga yang sudah didaftarkan di halaman Akun Saya.'], 422);
            }
            if (!in_array($kategori, ['Dewasa', 'Anak-anak'], true)) {
                return response()->json(['error' => 'Pilih kategori (Dewasa/Anak-anak) untuk setiap penumpang.'], 400);
            }
            $daftarValid[] = ['nik' => $nik, 'kategori' => $kategori] + $dataPerNik[$nik];
        }

        $nikUnik = array_unique(array_column($daftarValid, 'nik'));
        if (count($nikUnik) !== count($daftarValid)) {
            return response()->json(['error' => 'Ada NIK penumpang yang dipilih dua kali.'], 400);
        }

        foreach ($daftarValid as $p) {
            [$dupCode, $dupRows] = $this->supabase->rawRequest('GET', 'balikgratis_pemesanan?nik=eq.'.rawurlencode($p['nik'])
                .'&tahun=eq.'.$tahun.'&status=neq.dibatalkan&select=id&limit=1');
            if ($dupCode >= 200 && $dupCode < 300 && is_array($dupRows) && count($dupRows) > 0) {
                return response()->json(['error' => "NIK {$p['nik']} ({$p['nama']}) sudah terdaftar untuk Balik Gratis {$tahun}."], 409);
            }
        }

        $terisi = $this->bg->terisiTahun($tahun);
        $sisaKuota = $kuota - $terisi;
        if ($sisaKuota < count($daftarValid)) {
            return response()->json(['error' => "Kuota tersisa hanya {$sisaKuota}, tidak cukup untuk ".count($daftarValid).' penumpang.'], 422);
        }

        $grupId = bin2hex(random_bytes(16));
        $grupId = sprintf('%s-%s-%s-%s-%s', substr($grupId, 0, 8), substr($grupId, 8, 4), substr($grupId, 12, 4), substr($grupId, 16, 4), substr($grupId, 20, 12));

        $tiketDibuat = [];
        $gagal = [];
        foreach ($daftarValid as $p) {
            $nomorTiket = $this->bg->generateNomorTiket($tahun);
            $qrToken = $this->bg->generateQrToken();

            [$insCode, $inserted] = $this->supabase->rawRequest('POST', 'balikgratis_pemesanan', [
                'tahun' => $tahun,
                'nomor_tiket' => $nomorTiket,
                'qr_token' => $qrToken,
                'nama' => $p['nama'],
                'no_hp' => $profil['no_hp'],
                'nik' => $p['nik'],
                'no_kk' => $profil['no_kk'],
                'jenis_kelamin' => $p['jenis_kelamin'],
                'kategori' => $p['kategori'],
                'alamat' => $profil['alamat_lengkap'],
                'dokumen_filename' => null, // foto KTP/KK dilihat dari akun_publik via akun_id
                'status' => 'terdaftar',
                'ip_pendaftar' => $request->ip(),
                'akun_id' => $akunId,
                'pemesanan_grup_id' => $grupId,
            ]);

            $isDup = $insCode === 409 || ($insCode >= 400 && is_array($inserted) && !empty($inserted['message']) && str_contains((string) $inserted['message'], 'duplicate'));
            if ($isDup || $insCode < 200 || $insCode >= 300 || empty($inserted)) {
                $gagal[] = $p['nama'].($isDup ? ' (NIK baru saja terpakai)' : '');

                continue;
            }
            $tiketDibuat[] = [
                'nomor_tiket' => $nomorTiket,
                'qr_token' => $qrToken,
                'tahun' => $tahun,
                'nama' => $p['nama'],
                'nik' => $p['nik'],
                'kategori' => $p['kategori'],
                'jenis_kelamin' => $p['jenis_kelamin'],
                'status' => 'terdaftar',
            ];
        }

        if (count($gagal) > 0) {
            $this->supabase->rawRequest('PATCH', 'balikgratis_pemesanan?pemesanan_grup_id=eq.'.$grupId, ['status' => 'dibatalkan']);

            return response()->json(['error' => 'Sebagian penumpang gagal didaftarkan barusan ('.implode(', ', $gagal).'). Semua tiket di transaksi ini dibatalkan otomatis, silakan coba lagi.'], 409);
        }

        return response()->json(['ok' => true, 'tiket' => $tiketDibuat]);
    }
}
