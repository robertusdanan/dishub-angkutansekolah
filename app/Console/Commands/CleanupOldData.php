<?php

namespace App\Console\Commands;

use App\Services\Admin\CleanupService;
use Illuminate\Console\Command;

/**
 * Pengganti mode CLI di admin/api/cleanup.php lama (dulu dipanggil lewat
 * cron: `php cleanup.php` langsung, tanpa token — akses CLI dianggap
 * tepercaya). Jadwalkan lewat routes/console.php atau cron server:
 *   php artisan angkutan:cleanup
 */
class CleanupOldData extends Command
{
    protected $signature = 'angkutan:cleanup';

    protected $description = 'Bersihkan data absensi >6 bulan & pemesanan trayek wisata >2 tahun (setara admin/api/cleanup.php lama)';

    public function handle(CleanupService $cleanup): int
    {
        $result = $cleanup->run();

        foreach ($result['log'] as $line) {
            $this->line($line);
        }

        return self::SUCCESS;
    }
}
