<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    // Table name (optional if model name matches table)
    protected $table = 'packages';

    // Mass assignable attributes
    protected $fillable = [
        'name',
        'code',
        'description',
        'package_price',
        'validity_days',
        'is_subscription',
        'subscription_cycle',
        'status',
        'created_by',
    ];

}
