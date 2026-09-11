<?php

namespace App\Http\Controllers\RuteSekolah;

use App\Http\Controllers\Controller;
use App\Services\MenuLayanan\MenuLayananService;
use Illuminate\View\View;

/**
 * Port dari pages/ruteangkutansekolah/pages/*.php.
 *
 * CATATAN MIGRASI: enforce_cfg_sync() (kill-switch remote) di tiap halaman
 * lama SENGAJA DIHAPUS, sama seperti modul lain — lihat MenuLayananService.
 */
class RuteSekolahController extends Controller
{
    public function __construct(protected MenuLayananService $menuLayanan)
    {
    }

    /** GET /rute-sekolah — setara rute-bus-sekolah.php */
    public function index(): View
    {
        $this->menuLayanan->requireAktif('rute_sekolah');

        return $this->noCache(view('rute-sekolah.index'));
    }

    /** GET /rute-sekolah/peta — setara peta-gabungan.php */
    public function petaGabungan(): View
    {
        $this->menuLayanan->requireAktif('rute_sekolah');

        return $this->noCache(view('rute-sekolah.peta-gabungan'));
    }

    /** GET /rute-sekolah/lihat-peta — setara rute.php */
    public function liveMap(): View
    {
        $this->menuLayanan->requireAktif('rute_sekolah');

        return $this->noCache(view('rute-sekolah.live-map'));
    }

    /** GET /rute-sekolah/bus — setara rutebus.php (+ _rute_detail_shell.php) */
    public function detailBus(): View
    {
        $this->menuLayanan->requireAktif('rute_sekolah');

        return $this->noCache(view('rute-sekolah.detail-shell', ['cfg' => [
            'title' => 'Detail Rute Bus Sekolah — Dishub Tulungagung',
            'heading' => 'Detail Rute Bus',
            'badge_class' => 'badge-bus',
            'badge_label' => 'BUS',
            'badge_bg' => '#e8f0fe',
            'accent1' => '#1a56db',
            'accent2' => '#38bdf8',
            'body_bg' => '#f0f5ff',
            'back_btn_border' => '#d1e0ff',
            'back_btn_bg' => '#f0f5ff',
            'card_border' => '#e8edf5',
            'shadow_rgb' => '10,31,68',
            'footer_padding_top' => '0px',
            'js_file' => '/assets/rute-sekolah/partials/rute_bus.js',
        ]]));
    }

    /** GET /rute-sekolah/mpu — setara rutempu.php (+ _rute_detail_shell.php) */
    public function detailMpu(): View
    {
        $this->menuLayanan->requireAktif('rute_sekolah');

        return $this->noCache(view('rute-sekolah.detail-shell', ['cfg' => [
            'title' => 'Detail Rute MPU — Dishub Tulungagung',
            'heading' => 'Detail Rute MPU',
            'badge_class' => 'badge-mpu',
            'badge_label' => 'MPU',
            'badge_bg' => '#dcfce7',
            'accent1' => '#059669',
            'accent2' => '#2dd4bf',
            'body_bg' => '#f0fdf8',
            'back_btn_border' => '#bbf7d0',
            'back_btn_bg' => '#f0fdf4',
            'card_border' => '#e2f5ee',
            'shadow_rgb' => '5,150,105',
            'footer_padding_top' => '24px',
            'js_file' => '/assets/rute-sekolah/partials/rute_mpu.js',
        ]]));
    }

    /**
     * Setara send_html_headers() lama — dipasang di semua halaman modul ini
     * karena datanya (posisi live driver dkk.) tidak boleh di-cache browser.
     */
    protected function noCache(View $view)
    {
        return response($view)->withHeaders([
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
}
