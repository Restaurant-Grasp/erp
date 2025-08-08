<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryOrder extends Model
{
    protected $table = 'delivery_orders';

    protected $fillable = [
        'do_no',
        'do_date',
        'customer_id',
        'invoice_id',
        'delivery_address',
        'delivery_date',
        'delivered_by',
        'received_by',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'do_date' => 'date',
        'delivery_date' => 'date',
    ];


}
