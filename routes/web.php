<?php

use App\Http\Controllers\Admin\AdminAccountController;
use App\Http\Controllers\Admin\AdminAkunApiController;
use App\Http\Controllers\Admin\AdminAkunController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminManajemenRoleController;
use App\Http\Controllers\Admin\AdminProfilSayaController;
use App\Http\Controllers\Admin\AdminRoleApiController;
use App\Http\Controllers\Admin\AdminSupabaseWriteProxyController;
use App\Http\Controllers\Admin\AdminTiketSayaApiController;
use App\Http\Controllers\Admin\AdminTiketSayaController;
use App\Http\Controllers\Admin\Asdp\AsdpDaftarListController;
use App\Http\Controllers\Admin\Asdp\AsdpLokasiController;
use App\Http\Controllers\Admin\CleanupController;
use App\Http\Controllers\Admin\MenuLayananAdminController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Akun\AkunDokumenController;
use App\Http\Controllers\Akun\AkunProfilApiController;
use App\Http\Controllers\Akun\AkunProfilController;
use App\Http\Controllers\AbsenQr\AbsenQrApiController;
use App\Http\Controllers\AbsenQr\AbsenQrController;
use App\Http\Controllers\AbsenRfid\AbsenRfidApiController;
use App\Http\Controllers\AbsenRfid\AbsenRfidController;
use App\Http\Controllers\Api\ReferenceCacheProxyController;
use App\Http\Controllers\Api\SupabaseGenericProxyController;
use App\Http\Controllers\Asdp\AsdpController;
use App\Http\Controllers\Auth\AkunAuthController;
use App\Http\Controllers\BalikGratis\BalikGratisApiController;
use App\Http\Controllers\BalikGratis\BalikGratisController;
use App\Http\Controllers\ComingSoonController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RuteSekolah\RuteSekolahController;
use App\Http\Controllers\TrayekWisata\TrayekWisataApiController;
use App\Http\Controllers\TrayekWisata\TrayekWisataController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Peta rute di bawah ini adalah port 1:1 dari daftar RewriteRule di .htaccess
| lama (root). Setiap modul dikomentari dengan nama modul & status migrasi
| supaya mudah dilacak progresnya. Modul yang belum dimigrasikan sengaja
| diarahkan ke ComingSoonController (BUKAN dihapus/dibiarkan 404) supaya
| struktur URL akhir tetap lengkap sejak awal.
|
*/

// ── Beranda ── [SUDAH dimigrasikan]
Route::get('/', [HomeController::class, 'index'])->name('home');

// ── Akun Pengguna Terpusat (login Google, dipakai lintas layanan) ──
// [SUDAH dimigrasikan: alur login/callback/logout]
// "akun/callback" HARUS SAMA PERSIS dengan redirect_uri yang didaftarkan di
// Google Cloud Console (services.google.redirect_uri_akun / .env GOOGLE_REDIRECT_URI_AKUN).
Route::prefix('akun')->name('akun.')->group(function () {
    Route::get('masuk', [AkunAuthController::class, 'redirectToGoogle'])->name('masuk');
    Route::get('callback', [AkunAuthController::class, 'callback'])->name('callback');
    Route::get('keluar', [AkunAuthController::class, 'logout'])->name('keluar');

    // ── "akun/saya" (halaman profil) — [SUDAH dimigrasikan, Tahap 2] ──
    Route::get('saya', [AkunProfilController::class, 'index'])->name('saya');

    Route::prefix('api')->name('api.')->group(function () {
        Route::match(['get', 'post'], 'profil', [AkunProfilApiController::class, 'profil'])->name('profil');
        Route::match(['get', 'post'], 'keluarga', [AkunProfilApiController::class, 'keluarga'])->name('keluarga.index');
        Route::delete('keluarga', [AkunProfilApiController::class, 'keluarga'])->name('keluarga.delete');
        Route::post('upload-dokumen', [AkunDokumenController::class, 'upload'])->name('upload-dokumen');
        Route::get('dokumen-view', [AkunDokumenController::class, 'view'])->name('dokumen-view');
    });
});

/*
|--------------------------------------------------------------------------
| Modul: Rute Angkutan Sekolah (peta live bus/MPU)  — [SUDAH dimigrasikan]
|--------------------------------------------------------------------------
*/
Route::prefix('rute-sekolah')->controller(RuteSekolahController::class)->group(function () {
    Route::get('/', 'index');
    Route::get('peta', 'petaGabungan');
    Route::get('bus', 'detailBus');
    Route::get('mpu', 'detailMpu');
    Route::get('lihat-peta', 'liveMap');
});

// Dipakai lintas modul (JS partial supabase_proxy_client.js) — cache lokal
// data referensi (driver_bus, driver_mpu, domisili, sekolah) supaya tidak
// membebani Supabase. Port dari core/reference_cache_proxy_endpoint.php
// (sebelumnya /api/supabase_proxy.php).
Route::get('/api/supabase-proxy', [ReferenceCacheProxyController::class, 'show'])->name('api.supabase-proxy');

// Port dari api/absensi_proxy.php — publik (tidak login-gated), sama seperti
// kode lama. Halaman pemanggilnya (Rekap Operasional) yang dijaga login admin.
Route::get('/api/absensi-proxy', [\App\Http\Controllers\Api\AbsensiProxyController::class, 'show'])->name('api.absensi-proxy');

// Webhook Supabase Database Trigger (server-to-server, dilindungi
// X-Cache-Secret, BUKAN sesi/CSRF) — port dari api/cache_invalidate.php.
Route::post('/api/cache-invalidate', [\App\Http\Controllers\Api\CacheInvalidateController::class, 'handle'])->name('api.cache-invalidate');

/*
|--------------------------------------------------------------------------
| Modul: Peta Interaktif ASDP  — [SUDAH dimigrasikan]
|--------------------------------------------------------------------------
| Kode lama WAJIB trailing slash karena asset relatif (images/x.png,
| *.geojson) dimuat relatif terhadap /asdp/. Di Laravel semua asset itu
| sudah dipindah ke /assets/asdp/ dengan path absolut di Blade-nya, jadi
| trailing slash tidak lagi krusial.
|
| CATATAN: jangan tambah Route::redirect('/asdp', '/asdp/') di sini.
| Router Laravel sudah mencocokkan /asdp dan /asdp/ secara identik, dan
| begitu route:cache aktif redirect itu menimpa route aslinya sehingga
| /asdp/ me-redirect ke dirinya sendiri (ERR_TOO_MANY_REDIRECTS).
*/
Route::get('/asdp', [AsdpController::class, 'index']);

/*
|--------------------------------------------------------------------------
| Modul: Absensi RFID Angkutan Sekolah  — [SUDAH dimigrasikan sebagian:
| alur scan/absen publik. Halaman admin listlink/login/logout DITUNDA ke
| tahap Panel Admin — satu sistem RBAC yang sama dengan admin_roles]
|--------------------------------------------------------------------------
*/
Route::prefix('absenrfid')->group(function () {
    Route::get('/', [AbsenRfidController::class, 'index']);
    Route::get('bus', [AbsenRfidController::class, 'bus']);

    Route::prefix('php')->controller(AbsenRfidApiController::class)->group(function () {
        Route::post('cek', 'cek');
        Route::post('cek-rfid', 'cekRfid');
        Route::match(['get', 'post'], 'gps-write', 'gpsWrite');
    });
    Route::get('error', fn () => view('absen.error', ['errorCode' => request()->query('code', 500), 'backUrl' => '/absenrfid/listlink']));

    // Halaman admin (link kartu RFID ke bus/driver) — DITUNDA ke tahap Panel Admin.
    // Halaman admin (link kartu RFID ke bus/driver) — [SUDAH dimigrasikan]
    Route::get('listlink', [AbsenRfidController::class, 'listlink']);
    Route::get('login', [\App\Http\Controllers\AbsenRfid\ListlinkAuthController::class, 'show']);
    Route::post('login', [\App\Http\Controllers\AbsenRfid\ListlinkAuthController::class, 'show'])
        ->middleware('throttle:listlink-login');
    Route::get('logout', [\App\Http\Controllers\AbsenRfid\ListlinkAuthController::class, 'logout']);
});

/*
|--------------------------------------------------------------------------
| Modul: Absensi QR Code Angkutan Sekolah  — [SUDAH dimigrasikan sebagian,
| sama seperti absenrfid: listlink DITUNDA ke tahap Panel Admin]
|--------------------------------------------------------------------------
*/
Route::prefix('absenqrcode')->group(function () {
    Route::get('/', [AbsenQrController::class, 'index']);
    Route::get('pages/form', [AbsenQrController::class, 'form']);

    Route::prefix('php')->controller(AbsenQrApiController::class)->group(function () {
        Route::post('cek', 'cek');
        Route::match(['get', 'post'], 'status', 'status');
    });
    Route::any('php/proxy', [SupabaseGenericProxyController::class, 'handle']);

    Route::get('error', fn () => view('absen.error', ['errorCode' => request()->query('code', 500), 'backUrl' => '/absenqrcode/listlink']));

    // Halaman admin (link akun Google ke bus/driver) — DITUNDA ke tahap Panel Admin.
    // Halaman admin (link akun Google ke bus/driver) — [SUDAH dimigrasikan, publik]
    Route::get('listlink', [AbsenQrController::class, 'listlink']);
});

/*
|--------------------------------------------------------------------------
| Modul: Trayek Wisata Gratis  — [SUDAH dimigrasikan]
|--------------------------------------------------------------------------
*/
Route::prefix('trayek-wisata')->controller(TrayekWisataController::class)->group(function () {
    Route::get('/', 'index');
    Route::get('rute', 'rute');
    Route::get('destinasi', 'destinasi');
    Route::get('jadwal', 'jadwal');
    Route::get('faq', 'faq');
    Route::get('survei', 'survei');
});

// pages/trayekwisata/auth/{login,callback,logout}.php lama cuma redirect
// tipis ke sistem akun terpusat — dipertahankan sebagai redirect murni,
// tidak perlu controller.
Route::get('/trayek-wisata/auth/login', function () {
    $next = request()->query('next', '/trayek-wisata');
    $next = (is_string($next) && str_starts_with($next, '/')) ? $next : '/trayek-wisata';

    return redirect('/akun/masuk?'.http_build_query(['next' => $next]));
});
Route::get('/trayek-wisata/auth/callback', fn () => redirect('/akun/masuk'));
Route::get('/trayek-wisata/auth/logout', function () {
    $next = request()->query('next', '/trayek-wisata');
    $next = (is_string($next) && str_starts_with($next, '/')) ? $next : '/trayek-wisata';

    return redirect('/akun/keluar?'.http_build_query(['next' => $next]));
});

Route::prefix('trayek-wisata/api')->controller(TrayekWisataApiController::class)->group(function () {
    Route::get('auth-status', 'authStatus');
    Route::post('cek-nik', 'cekNik');
    Route::get('stats-publik', 'statsPublik');
    Route::match(['get', 'post'], 'waitlist', 'waitlist');
    Route::post('pesan', 'pesan');
    Route::post('batalkan-pesanan', 'batalkanPesanan');
    Route::get('pemesanan-saya', 'pemesananSaya');
    Route::post('survei', 'survei');
});

/*
|--------------------------------------------------------------------------
| Modul: Balik Gratis  — [SUDAH dimigrasikan]
|--------------------------------------------------------------------------
*/
Route::get('/balikgratis', [BalikGratisController::class, 'index']);
Route::prefix('balikgratis/api')->controller(BalikGratisApiController::class)->group(function () {
    Route::get('status', 'status');
    Route::get('auth-status', 'authStatus');
    Route::get('cek-tiket', 'cekTiket');
    Route::post('daftar', 'daftar');
});

/*
|--------------------------------------------------------------------------
| Panel Admin  — [Tahap 1+2 SUDAH dimigrasikan: shell, RBAC inti, Tiket
| Saya, Profil Saya, Akun, Manajemen Role]
|--------------------------------------------------------------------------
| Modul terbesar, dipecah jadi beberapa tahap (lihat README-MIGRASI.md).
| Sisanya (ASDP, Trayek Wisata, Balik Gratis, Angkutan Sekolah) masih
| placeholder, menyusul tahap berikutnya.
*/
Route::prefix('admin')->group(function () {
    // "login" TIDAK pakai middleware admin.auth (justru sebaliknya — kalau
    // sudah login, di dalam controller-nya sendiri yang redirect ke dashboard).
    Route::get('login', [AdminAuthController::class, 'show']);
    Route::post('login', [AdminAuthController::class, 'show'])->middleware('throttle:admin-login');
    Route::get('login/verifikasi-2fa', [AdminAuthController::class, 'showTwoFactorChallenge']);
    Route::post('login/verifikasi-2fa', [AdminAuthController::class, 'verifyTwoFactorChallenge'])->middleware('throttle:admin-login');

    Route::middleware('admin.auth')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index']);
        Route::get('logout', [AdminAuthController::class, 'logout']);
        Route::get('settings', [SettingsController::class, 'index']);
        Route::post('ganti-password', [AdminAccountController::class, 'update']);

        // REKOMENDASI KEAMANAN: enrollment 2FA (TOTP) untuk akun admin.
        Route::get('2fa/status', [\App\Http\Controllers\Admin\AdminTwoFactorController::class, 'status']);
        Route::post('2fa/setup', [\App\Http\Controllers\Admin\AdminTwoFactorController::class, 'setup']);
        Route::post('2fa/confirm', [\App\Http\Controllers\Admin\AdminTwoFactorController::class, 'confirm']);
        Route::post('2fa/disable', [\App\Http\Controllers\Admin\AdminTwoFactorController::class, 'disable']);

        Route::get('tiket-saya', [AdminTiketSayaController::class, 'index']);
        Route::get('profil-saya', [AdminProfilSayaController::class, 'index']);
        Route::get('akun', [AdminAkunController::class, 'index']);
        Route::get('manajemen-role', [AdminManajemenRoleController::class, 'index']);

        Route::get('asdp/daftar-lokasi', [AsdpDaftarListController::class, 'index']);
        Route::get('asdp/koordinat', [AsdpLokasiController::class, 'index']);

        Route::prefix('balikgratis')->controller(\App\Http\Controllers\Admin\BalikGratis\BalikGratisAdminController::class)->group(function () {
            Route::get('/', 'dashboard');
            Route::get('pemesanan', 'pemesanan');
            Route::get('pengaturan', 'pengaturan');
            Route::get('verifikasi', 'verifikasi');
        });

        Route::prefix('trayekwisata')->controller(\App\Http\Controllers\Admin\TrayekWisata\TrayekWisataAdminController::class)->group(function () {
            Route::get('/', 'dashboard');
            Route::get('planner', 'planner');
            Route::get('titik', 'titik');
            Route::get('armada', 'armada');
            Route::get('pemesanan', 'pemesanan');
            Route::get('verifikasi', 'verifikasi');
        });

        Route::get('data-absensi', [\App\Http\Controllers\Admin\AngkutanSekolah\DataAbsensiController::class, 'index']);
        Route::get('operasional', [\App\Http\Controllers\Admin\AngkutanSekolah\OperasionalController::class, 'index']);
        Route::get('absensi-foto', [\App\Http\Controllers\Admin\AngkutanSekolah\ReportFotoController::class, 'index']);

        Route::controller(\App\Http\Controllers\Admin\AngkutanSekolah\AngkutanSekolahUpdateDataController::class)->group(function () {
            Route::get('data-sekolah', 'dataSekolah');
            Route::get('data-domisili', 'dataDomisili');
            Route::get('data-trayek', 'dataTrayek');
            Route::get('rute-map', 'dataMap');
            Route::get('data-driver', 'dataDriver');
            Route::get('registrasi-siswa', 'registrasi');
            Route::get('rfid-writer', 'rfidWriter');
            Route::get('tambah-absen-foto', 'absensiFoto');
        });

        Route::prefix('api')->group(function () {
            Route::get('menu-layanan', [MenuLayananAdminController::class, 'list']);
            Route::post('menu-layanan', [MenuLayananAdminController::class, 'toggleActive']);
            Route::get('cleanup', [CleanupController::class, 'run']);
            Route::get('tiket-saya', [AdminTiketSayaApiController::class, 'index']);

            Route::get('akun', [AdminAkunApiController::class, 'list']);
            Route::post('akun', function (\Illuminate\Http\Request $request) {
                return match ($request->query('action')) {
                    'create' => app(AdminAkunApiController::class)->create($request),
                    'update' => app(AdminAkunApiController::class)->update($request),
                    'toggle_active' => app(AdminAkunApiController::class)->toggleActive($request),
                    'reset_password' => app(AdminAkunApiController::class)->resetPassword($request),
                    'delete' => app(AdminAkunApiController::class)->delete($request),
                    default => response()->json(['error' => 'Aksi tidak dikenal.'], 400),
                };
            });

            Route::get('roles', [AdminRoleApiController::class, 'list']);
            Route::post('roles', function (\Illuminate\Http\Request $request) {
                return match ($request->query('action')) {
                    'create' => app(AdminRoleApiController::class)->create($request),
                    'update' => app(AdminRoleApiController::class)->update($request),
                    'delete' => app(AdminRoleApiController::class)->delete($request),
                    default => response()->json(['error' => 'Aksi tidak dikenal.'], 400),
                };
            });

            // Proxy tulis Supabase (service_role) per modul — setara
            // admin/api/{modul}/db.php lama. Dipanggil dari sb-secure.js.
            Route::match(['get', 'post'], 'angkutansekolah/db', [AdminSupabaseWriteProxyController::class, 'angkutansekolah']);
            Route::post('angkutansekolah/invalidate-cache', [AdminSupabaseWriteProxyController::class, 'invalidateCache']);
            Route::match(['get', 'post'], 'balikgratis/db', [AdminSupabaseWriteProxyController::class, 'balikgratis']);
            Route::match(['get', 'post'], 'trayekwisata/db', [AdminSupabaseWriteProxyController::class, 'trayekwisata']);

            Route::post('asdp/upload-image', [\App\Http\Controllers\Admin\Asdp\AsdpImageController::class, 'handle']);

            Route::get('balikgratis/pengaturan', [\App\Http\Controllers\Admin\BalikGratis\BalikGratisPengaturanController::class, 'status']);
            Route::post('balikgratis/pengaturan', [\App\Http\Controllers\Admin\BalikGratis\BalikGratisPengaturanController::class, 'update']);
            Route::get('balikgratis/verifikasi', function (\Illuminate\Http\Request $request) {
                return app(\App\Http\Controllers\Admin\BalikGratis\BalikGratisVerifikasiController::class)->cari($request);
            });
            Route::post('balikgratis/verifikasi', function (\Illuminate\Http\Request $request) {
                return app(\App\Http\Controllers\Admin\BalikGratis\BalikGratisVerifikasiController::class)->tandaiHadir($request);
            });
            Route::get('balikgratis/dokumen-view', [\App\Http\Controllers\Admin\BalikGratis\BalikGratisDokumenViewController::class, 'show']);

            Route::get('trayekwisata/dokumen-view', [\App\Http\Controllers\Admin\TrayekWisata\TrayekWisataDokumenViewController::class, 'show']);
            Route::post('trayekwisata/upload-galeri', [\App\Http\Controllers\Admin\TrayekWisata\TrayekWisataGaleriController::class, 'upload']);
            Route::post('trayekwisata/copy', [\App\Http\Controllers\Admin\TrayekWisata\TrayekWisataCopyController::class, 'handle']);

            Route::get('angkutansekolah/absensi-report', [\App\Http\Controllers\Admin\AngkutanSekolah\AbsensiReportController::class, 'handle']);
            Route::get('angkutansekolah/absensi-foto-report', [\App\Http\Controllers\Admin\AngkutanSekolah\AbsensiFotoReportController::class, 'handle']);
            Route::any('angkutansekolah/upload-foto', [\App\Http\Controllers\Admin\AngkutanSekolah\AbsensiFotoUploadController::class, 'handle']);
        });
    });

    // ── Sisanya (belum dimigrasikan) ────────────────────────────────
    Route::middleware('admin.auth')->any('{any}', [ComingSoonController::class, 'show'])
        ->defaults('modul', 'admin (tahap berikutnya)')
        ->where('any', '.*');
});
