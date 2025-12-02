<?php
namespace App\Http\Controllers;
use App\Services\StockService;
use Illuminate\Http\Request;

class ProductController extends Controller
{

    public function show($id, StockService $stock)
    {
        $product = \App\Models\Product::findOrFail($id);
        $available = $stock->getAvailable($id);

        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'price_cents' => $product->price_cents,
            'available' => $available,
        ]);
    }

}