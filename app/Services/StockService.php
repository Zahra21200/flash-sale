<?php
namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Cache;

class StockService
{
    public function getAvailable(int $productId): int
    {
        $product = Cache::remember("product:{$productId}:base", 30, function() use($productId) {
            return Product::findOrFail($productId);
        });

        $reserved = Cache::get("product:{$productId}:reserved", 0);
        $sold = Cache::get("product:{$productId}:sold", 0);

        return max(0, $product->stock - $reserved - $sold);
    }
}
