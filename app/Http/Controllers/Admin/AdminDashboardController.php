<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminRoleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Port dari admin/index.php (Dashboard = halaman pilih modul).
 */
class AdminDashboardController extends Controller
{
    public function __construct(protected AdminRoleService $roles)
    {
    }

    public function index(): View|RedirectResponse
    {
        $adminRole = session('admin_role', 'viewer');
        $isGuest = $adminRole === 'guest';

        if ($isGuest) {
            return redirect('/admin/data-absensi');
        }
        if ($adminRole === 'pengguna') {
            return redirect('/admin/tiket-saya');
        }

        $modules = [
            [
                'id' => 'angkutansekolah',
                'label' => 'Angkutan Sekolah Gratis',
                'description' => 'Data absensi siswa, absensi foto, rekap operasional, data trayek/driver, GPS driver, dan RFID writer.',
                'href' => '/admin/data-absensi',
                'icon' => '<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M8 6v6M2 12h19.6M2 12c0-3.3 0-5 .8-6.1S5 4 8.5 4h7c3.5 0 4.9.8 5.7 1.9S22 8.7 22 12v3.5c0 1.2 0 1.8-.4 2.2s-1 .4-2.2.4H4.6c-1.2 0-1.8 0-2.2-.4S2 16.7 2 15.5z"/><circle cx="7" cy="19.5" r="1.6"/><circle cx="17" cy="19.5" r="1.6"/></svg>',
            ],
        ];

        if ($this->roles->canAccessMenu('asdp_daftar') || $this->roles->canAccessMenu('asdp_koordinat')) {
            $modules[] = [
                'id' => 'asdp',
                'label' => 'Layanan ASDP',
                'description' => 'Kelola daftar lokasi penyeberangan (tambangan) ASDP: nama, deskripsi, foto, dan koordinat peta.',
                'href' => '/admin/asdp/daftar-lokasi',
                'icon' => '<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2 20.5c1.8 1.2 3.6 1.2 5.4 0 1.8 1.2 3.6 1.2 5.4 0 1.8 1.2 3.6 1.2 5.4 0 1.8 1.2 3.6 1.2 5.4 0"/><path d="M4 14.5V6a1 1 0 0 1 1-1h3V3h4v2h3a1 1 0 0 1 1 1v8.5"/><path d="M3 14.5h18l-2 4H5z"/></svg>',
            ];
        }

        if ($this->roles->canAccessMenu('trayekwisata_dashboard') || $this->roles->canAccessMenu('trayekwisata_planner') || $this->roles->canAccessMenu('trayekwisata_titik') || $this->roles->canAccessMenu('trayekwisata_armada') || $this->roles->canAccessMenu('trayekwisata_pemesanan') || $this->roles->canAccessMenu('trayekwisata_verifikasi')) {
            $modules[] = [
                'id' => 'trayekwisata',
                'label' => 'Trayek Wisata Gratis',
                'description' => 'Kelola titik lokasi, jadwal trayek tahunan (Sabtu/Minggu), armada bus & driver, dan pemesanan kursi.',
                'href' => '/admin/trayekwisata',
                'icon' => '<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 21s7-4.35 7-11a7 7 0 1 0-14 0c0 6.65 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>',
            ];
        }

        if ($this->roles->canAccessMenu('balikgratis_dashboard') || $this->roles->canAccessMenu('balikgratis_pengaturan') || $this->roles->canAccessMenu('balikgratis_pemesanan') || $this->roles->canAccessMenu('balikgratis_verifikasi')) {
            $modules[] = [
                'id' => 'balikgratis',
                'label' => 'Balik Gratis',
                'description' => 'Kelola pendaftaran Balik Gratis Tulungagung–Surabaya: buka/tutup kuota tahun berjalan, data pemesanan, dan verifikasi tiket.',
                'href' => '/admin/balikgratis',
                'icon' => '<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M8 6v6M2 12h19.6M2 12c0-3.3 0-5 .8-6.1S5 4 8.5 4h7c3.5 0 4.9.8 5.7 1.9S22 8.7 22 12v3.5c0 1.2 0 1.8-.4 2.2s-1 .4-2.2.4H4.6c-1.2 0-1.8 0-2.2-.4S2 16.7 2 15.5z"/><path d="M12 2 8 6M12 2l4 4"/></svg>',
            ];
        }

        return view('admin.dashboard', [
            'modules' => $modules,
            'isGuest' => $isGuest,
            'isSuperAdmin' => $adminRole === 'superadmin',
        ]);
    }
}
