<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hold extends Model
{
    protected $fillable = ['product_id','qty','expires_at','status','used_for_order_id'];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function product() {
        return $this->belongsTo(Product::class);
    }

    public function order() {
        return $this->hasOne(Order::class);
    }
}

