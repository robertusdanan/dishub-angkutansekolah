<?php

namespace App\Http\Controllers\AbsenRfid;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Port dari pages/absenrfid/{index,bus}.php.
 *
 * CATATAN MIGRASI: pages/absenrfid/oauth2callback.php dan redirect.php TIDAK
 * ikut dipindahkan - sudah dicek, keduanya dead code (tidak direferensikan
 * dari manapun; login sekarang lewat Google Identity Services client-side,
 * bukan redirect server-side). Lihat README untuk detail.
 */
class AbsenRfidController extends Controller
{
    /** GET /absenrfid - setara index.php (redirect ke bus atau listlink) */
    public function index(Request $request): RedirectResponse
    {
        $driverId = trim((string) $request->query('id', ''));

        if ($driverId !== '') {
            return redirect('/absenrfid/bus?id='.urlencode($driverId));
        }

        return redirect('/absenrfid/listlink');
    }

    /** GET /absenrfid/bus - setara bus.php (layar tempel-kartu di kendaraan) */
    public function bus(Request $request): View
    {
        return view('absen-rfid.bus', [
            'driverId' => (string) $request->query('id', ''),
        ]);
    }

    /** GET /absenrfid/listlink - setara pages/listlink.php */
    public function listlink(): View|RedirectResponse
    {
        if (!session('listlink_logged_in')) {
            session(['listlink_redirect' => '/absenrfid/listlink']);

            return redirect('/absenrfid/login');
        }

        return view('absen-rfid.listlink', [
            'listlinkUser' => session('listlink_user', ''),
        ]);
    }
}
