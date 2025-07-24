<?php

namespace App\Models;

//use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */

    protected $table = 'orders';

    protected $fillable = [
        'customer_name',
        'customer_nif',
        'total',
        'currency',
        'number',
        'uuid'
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
