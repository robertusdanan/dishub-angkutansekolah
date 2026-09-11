<?php

namespace App\Http\Controllers;

use App\Services\MenuLayanan\MenuLayananService;
use Illuminate\View\View;

/**
 * Port dari index.php (root).
 *
 * CATATAN MIGRASI: blok "cfg_sync_state()" (kill-switch remote) yang ada
 * di index.php lama SENGAJA DIHAPUS, sesuai instruksi — kontrol aktif/
 * nonaktif layanan sekarang murni lewat toggle admin (menu_layanan).
 */
class HomeController extends Controller
{
    public function __construct(protected MenuLayananService $menuLayanan)
    {
    }

    public function index(): View
    {
        $status = $this->menuLayanan->status();

        // Urutan "asli" (prioritas default) tiap layanan — sama seperti
        // array $_menuItems di index.php lama.
        $menuItems = [
            [
                'slug' => 'rute_sekolah',
                'href' => '/rute-sekolah',
                'icon' => 'fa-solid fa-bus-simple',
                'title' => 'Rute Angkutan Sekolah',
                'desc' => 'Lihat daftar trayek BUS dan MPU.',
            ],
            [
                'slug' => 'asdp',
                'href' => '/asdp/',
                'icon' => 'fa-solid fa-ferry',
                'title' => 'Peta Interaktif ASDP',
                'desc' => 'Angkutan Sungai, Danau dan Penyeberangan.',
            ],
            [
                'slug' => 'trayekwisata',
                'href' => '/trayek-wisata',
                'icon' => 'fa-solid fa-umbrella-beach',
                'title' => 'Trayek Wisata Gratis',
                'desc' => 'Jelajahi Pantai Selatan gratis tiap Sabtu & Minggu.',
            ],
            [
                'slug' => 'balikgratis',
                'href' => '/balikgratis',
                'icon' => 'fa-solid fa-bus',
                'title' => 'Balik Gratis',
                'desc' => 'Pendaftaran mudik/balik gratis Tulungagung–Surabaya.',
            ],
        ];

        foreach ($menuItems as &$item) {
            $item['aktif'] = $status[$item['slug']] ?? true;
        }
        unset($item);

        // Item aktif ditaruh lebih dulu, "Segera Hadir" digeser ke belakang
        // — usort STABIL, urutan relatif antar item berstatus sama tetap
        // mengikuti urutan asli array di atas (sama seperti kode lama).
        usort($menuItems, static fn (array $a, array $b): int => (int) $b['aktif'] <=> (int) $a['aktif']);

        return view('home', [
            'menuItems' => $menuItems,
            'isSuperAdminView' => $this->menuLayanan->isPublicSuperadmin(),
        ]);
    }
}
