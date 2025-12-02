<?php
namespace App\Services;

use App\Models\Hold;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class HoldService
{
    public function createHold(int $productId, int $qty): Hold
    {
        return DB::transaction(function() use($productId, $qty){
            // Lock product row
            $product = Product::where('id', $productId)->lockForUpdate()->firstOrFail();

            // compute available
            $reserved = DB::table('holds')
                ->where('product_id', $productId)
                ->where('status', 'active')
                ->sum('qty');

            $sold = DB::table('orders')
                ->where('product_id', $productId)
                ->where('status', 'paid')
                ->sum('qty');

            $available = $product->stock - $reserved - $sold;

            if ($available < $qty) {
                throw new \Exception('Insufficient stock');
            }

            $hold = Hold::create([
                'product_id' => $productId,
                'qty' => $qty,
                'expires_at' => Carbon::now()->addMinutes(2),
                'status' => 'active'
            ]);

            // atomic cache update
            Cache::increment("product:{$productId}:reserved", $qty);

            return $hold;
        }, 5); // retry 5 times if deadlock
    }
}
