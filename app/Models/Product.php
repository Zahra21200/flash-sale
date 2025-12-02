<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['name','price_cents','stock'];

    public function holds() {
        return $this->hasMany(Hold::class);
    }

    public function orders() {
        return $this->hasMany(Order::class);
    }
}

