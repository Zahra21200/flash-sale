<?php
namespace App\Services;

use App\Models\Order;
use App\Models\Hold;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class OrderService
{
    public function createOrder(int $holdId): Order
    {
        return DB::transaction(function() use($holdId){
            $hold = Hold::lockForUpdate()->findOrFail($holdId);

            if($hold->status !== 'active' || $hold->expires_at->lt(now())){
                throw new \Exception('Hold is invalid or expired');
            }

            $order = Order::create([
                'hold_id' => $hold->id,
                'product_id' => $hold->product_id,
                'qty' => $hold->qty,
                'amount_cents' => $hold->qty * $hold->product->price_cents,
                'status' => 'pending'
            ]);

            $hold->update([
                'status' => 'used',
                'used_for_order_id' => $order->id
            ]);

            Cache::decrement("product:{$hold->product_id}:reserved", $hold->qty);

            return $order;
        }, 5);
    }
}
