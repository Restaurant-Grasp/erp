<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryTransaction extends Model
{     

    protected $table = 'inventory_transactions';

    protected $fillable = [
        'transaction_date',
        'transaction_type',
        'reference_type',
        'reference_id',
        'product_id',
        'quantity',
        'unit_cost',
        'total_cost',
        'warehouse_id',
        'notes',
        'created_by',
    ];
}
