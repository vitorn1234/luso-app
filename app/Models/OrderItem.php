<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'sku',
        'qty',
        'unit_price',
    ];

    // Optional: Relationships
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
