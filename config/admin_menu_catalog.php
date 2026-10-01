<?php

/**
 * Port dari admin/config/menu_catalog.php - dipakai AdminRoleApiController
 * untuk memvalidasi isi permissions.menus (mencegah id menu ngasal lewat
 * request manual) dan untuk render UI checklist menu di Manajemen Role.
 */
return [
    'grup' => [
        'Angkutan Sekolah Gratis' => [
            'data_absensi' => 'Absensi',
            'report_foto' => 'Absensi Foto',
            'operasional' => 'Rekap Operasional',
        ],
        'Update Data Absensi' => [
            'update_domisili' => 'Data Domisili',
            'update_sekolah' => 'Data Sekolah',
            'update_siswa' => 'Registrasi Siswa',
            'tambah_foto' => 'Tambah Absen Foto',
        ],
        'Update Data Trayek' => [
            'update_trayek' => 'Data Trayek',
            'update_map' => 'Rute Web',
            'update_driver' => 'Data Driver',
        ],
        'Layanan ASDP' => [
            'asdp_daftar' => 'Daftar Lokasi ASDP',
            'asdp_koordinat' => 'Lokasi ASDP',
        ],
        'Trayek Wisata' => [
            'trayekwisata_dashboard' => 'Dashboard Trayek',
            'trayekwisata_planner' => 'Pengaturan Trayek Tahunan',
            'trayekwisata_titik' => 'Titik Lokasi',
            'trayekwisata_armada' => 'Bus & Driver',
            'trayekwisata_pemesanan' => 'Pemesanan',
            'trayekwisata_verifikasi' => 'Verifikasi Tiket',
        ],
        'Balik Gratis' => [
            'balikgratis_dashboard' => 'Dashboard',
            'balikgratis_pengaturan' => 'Pengaturan Kuota',
            'balikgratis_pemesanan' => 'Data Pemesanan',
            'balikgratis_verifikasi' => 'Verifikasi Berkas',
        ],
    ],

    /**
     * Pemetaan aksi nyata yang memang didukung/dibutuhkan tiap menu di UI.
     * Menu dengan array kosong [] berarti menu read-only (hanya lihat / laporan / dashboard),
     * sehingga di Manajemen Role tidak perlu menampilkan opsi Tambah, Edit, atau Hapus.
     */
    'menu_supported_actions' => [
        // Angkutan Sekolah Gratis (Hanya monitoring & rekap)
        'data_absensi' => [],
        'report_foto' => [],
        'operasional' => [],

        // Update Data Absensi
        'update_domisili' => ['create', 'edit', 'delete'],
        'update_sekolah' => ['create', 'edit', 'delete'],
        'update_siswa' => ['create', 'edit', 'delete'],
        'tambah_foto' => ['create'], // upload foto & submit absen

        // Update Data Trayek
        'update_trayek' => ['create', 'edit', 'delete'],
        'update_map' => ['create', 'edit', 'delete'],
        'update_driver' => ['create', 'edit', 'delete'],

        // Layanan ASDP
        'asdp_daftar' => ['create', 'edit', 'delete'],
        'asdp_koordinat' => ['edit'], // hanya atur koordinat lat/lng

        // Trayek Wisata
        'trayekwisata_dashboard' => [],
        'trayekwisata_planner' => ['create', 'edit', 'delete'],
        'trayekwisata_titik' => ['create', 'edit', 'delete'],
        'trayekwisata_armada' => ['create', 'edit', 'delete'],
        'trayekwisata_pemesanan' => [], // data pemesanan publik & PDF
        'trayekwisata_verifikasi' => ['edit'], // check-in tiket

        // Balik Gratis
        'balikgratis_dashboard' => [],
        'balikgratis_pengaturan' => ['edit'], // simpan kuota & jadwal
        'balikgratis_pemesanan' => ['edit'], // batalkan tiket
        'balikgratis_verifikasi' => ['edit'], // verifikasi kehadiran
    ],
];
