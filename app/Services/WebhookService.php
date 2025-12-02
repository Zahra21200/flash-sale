<?php
namespace App\Services;

use App\Models\Order;
use App\Models\WebhookEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class WebhookService
{
    public function handleWebhook(string $idempotencyKey, array $payload)
    {
        return DB::transaction(function() use($idempotencyKey, $payload){
            $event = WebhookEvent::firstOrCreate(
                ['idempotency_key' => $idempotencyKey],
                ['payload' => json_encode($payload), 'status' => 'processing']
            );

            if($event->status === 'done'){
                return true; // already processed
            }

            $orderId = $payload['order_id'];
            $result = $payload['result']; // success | failed
            $order = Order::lockForUpdate()->find($orderId);

            if(!$order){
                $event->status = 'received';
                $event->save();
                // optionally dispatch job to retry processing later
                return false;
            }

            if($result === 'success' && $order->status !== 'paid'){
                $order->status = 'paid';
                $order->save();
                Cache::increment("product:{$order->product_id}:sold", $order->qty);
            }

            if($result === 'failed' && $order->status !== 'cancelled'){
                $order->status = 'cancelled';
                $order->save();
                Cache::increment("product:{$order->product_id}:reserved", $order->qty);
            }

            $event->status = 'done';
            $event->processed_at = now();
            $event->save();

            return true;
        }, 5);
    }
}
