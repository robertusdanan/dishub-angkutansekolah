<?php

namespace App\Services\MenuLayanan;

use App\Services\Supabase\SupabaseClient;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

/**
 * Port dari core/menu_layanan_helper.php.
 *
 * CATATAN MIGRASI (atas instruksi pemilik project): mekanisme remote
 * kill-switch/license (cfg_sync_state() di core/cache_helper.php dan
 * license_check() di core/license.php) SENGAJA TIDAK dipindahkan ke sini
 * maupun ke bagian manapun di Laravel. Kontrol aktif/nonaktif tiap layanan
 * SEPENUHNYA lewat toggle admin (tabel `menu_layanan` di Supabase) seperti
 * yang sudah tersedia di halaman admin.
 */
class MenuLayananService
{
    protected const CACHE_KEY = 'menu_layanan_status';

    protected const CACHE_TTL_SECONDS = 24 * 3600; // sama seperti SB_CACHE_SAFETY_TTL lama

    public function __construct(protected SupabaseClient $supabase)
    {
    }

    /**
     * @return array<string,bool> peta slug => aktif
     */
    public function status(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL_SECONDS, function () {
            [$code, $rows] = $this->supabase->select('menu_layanan', ['select' => 'slug,aktif']);

            $result = [];
            if ($code >= 200 && $code < 300 && is_array($rows)) {
                foreach ($rows as $row) {
                    if (isset($row['slug'])) {
                        $result[$row['slug']] = (bool) ($row['aktif'] ?? true);
                    }
                }
            }

            return $result;
        });
    }

    public function isAktif(string $slug): bool
    {
        return $this->status()[$slug] ?? true;
    }

    public function invalidateCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public function isPublicSuperadmin(): bool
    {
        return (bool) Session::get('admin_logged_in')
            && Session::get('admin_role') === 'superadmin';
    }

    /**
     * Guard untuk dipakai di controller: kalau menu nonaktif (dan bukan
     * superadmin), lempar 404 dengan halaman "Segera Hadir" bermerek —
     * setara render_menu_layanan_nonaktif() + require_menu_layanan_aktif()
     * di kode lama.
     */
    public function requireAktif(string $slug): void
    {
        if ($this->isPublicSuperadmin()) {
            return;
        }

        if (!$this->isAktif($slug)) {
            abort(response()->view('errors.menu-nonaktif', [], 404));
        }
    }
}
