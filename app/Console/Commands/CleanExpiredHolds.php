<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\ReleaseExpiredHolds;

class CleanExpiredHolds extends Command
{
    protected $signature = 'holds:clean-expired';
    protected $description = 'Release expired holds back to stock';

    public function handle()
    {
        ReleaseExpiredHolds::dispatch();
        $this->info('Dispatched ReleaseExpiredHolds job');
    }
}
 