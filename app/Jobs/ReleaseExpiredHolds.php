<?php
namespace App\Jobs;

use App\Models\Hold;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReleaseExpiredHolds implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public function handle()
    {
        $now = Carbon::now();

        $holds = Hold::where('status', 'active')
                     ->where('expires_at', '<=', $now)
                     ->get();

        foreach($holds as $hold){
            DB::transaction(function() use($hold){
                if($hold->status !== 'active') return;

                $hold->status = 'expired';
                $hold->save();

                Cache::decrement("product:{$hold->product_id}:reserved", $hold->qty);
            }, 5);
        }
    }
}
