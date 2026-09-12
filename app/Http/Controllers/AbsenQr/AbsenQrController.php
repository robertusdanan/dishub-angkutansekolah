<?php

namespace App\Http\Controllers\AbsenQr;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

/**
 * Port dari pages/absenqrcode/index.php.
 *
 * CATATAN MIGRASI: pages/absenqrcode/oauth2callback.php TIDAK ikut
 * dipindahkan - sudah dicek, dead code (login sekarang lewat Google
 * Identity Services client-side yang decode JWT langsung di browser,
 * bukan redirect server-side ke oauth2callback.php). Lihat README.
 */
class AbsenQrController extends Controller
{
    /** GET /absenqrcode/ - setara index.php */
    public function index(): View
    {
        return view('absen-qrcode.index');
    }

    /** GET /absenqrcode/pages/form - setara pages/form.php (fragment HTML, di-fetch via JS) */
    public function form(): View
    {
        return view('absen.form-partial');
    }

    /**
     * GET /absenqrcode/listlink - setara pages/listlink.php.
     * CATATAN: kode lama TIDAK memberi gate login apapun di sini (beda
     * dengan versi RFID) - halaman ini publik, cuma menampilkan nama
     * driver + link absen. Dipertahankan apa adanya.
     */
    public function listlink(): View
    {
        return view('absen-qrcode.listlink');
    }
}
