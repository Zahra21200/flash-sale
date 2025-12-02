<?php
namespace App\Http\Controllers;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function store(Request $request, OrderService $orderService)
    {
        $data = $request->validate(['hold_id'=>'required|int']);

        try {
            $order = $orderService->createOrder($data['hold_id']);
        } catch (\Exception $e) {
            return response()->json(['message'=>$e->getMessage()], 409);
        }

        return response()->json([
            'order_id' => $order->id,
            'status' => $order->status,
            'amount_cents' => $order->amount_cents
        ], 201);
    }

}