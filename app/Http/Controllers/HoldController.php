<?php
namespace App\Http\Controllers;
use App\Services\HoldService;
use Illuminate\Http\Request;

class HoldController extends Controller
{
    public function store(Request $request, HoldService $holdService)
    {
        $data = $request->validate(['product_id'=>'required|int','qty'=>'required|int|min:1']);

        try {
            $hold = $holdService->createHold($data['product_id'], $data['qty']);
        } catch (\Exception $e) {
            return response()->json(['message'=>$e->getMessage()], 409);
        }

        return response()->json([
            'hold_id' => $hold->id,
            'expires_at' => $hold->expires_at->toIsoString()
        ], 201);
    }
}
