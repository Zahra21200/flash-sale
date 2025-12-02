<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
   
    public function run(): void
    {
        Product::create([
            'name' => 'T-Shirt',
            'price_cents' => 1999,
            'stock' => 50
        ]);

    }
}
