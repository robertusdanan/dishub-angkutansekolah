<?php

/**
 * Port dari admin/config/menu_catalog.php — dipakai AdminRoleApiController
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
        'Lainnya' => [
            'rfid_writer' => 'RFID Writer',
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
            'balikgratis_dashboard' => 'Dashboard Balik Gratis',
            'balikgratis_pengaturan' => 'Pengaturan Kuota Tahun Ini',
            'balikgratis_pemesanan' => 'Data Pemesanan',
            'balikgratis_verifikasi' => 'Verifikasi Tiket',
        ],
    ],
];
