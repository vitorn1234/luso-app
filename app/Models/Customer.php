<?php

namespace App\Models;

//use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    //use HasUuids;

    //Str::orderedUuid();
    /**
     * The table associated with the model.
     *
     * @var string
     */

    protected $table = 'orders';

    protected $fillable = [
        'customer_name',
        'customer_nif',
    ];


}
