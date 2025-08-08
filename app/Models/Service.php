<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $table = 'services';

    protected $fillable = [
        'service_type_id',
        'name',
        'code',
        'description',
        'base_price',
        'is_recurring',
        'billing_cycle',
        'status',
        'created_by',
    ];

    protected $casts = [
        'base_price' => 'float',
        'is_recurring' => 'boolean',
        'status' => 'boolean',
    ];
}
