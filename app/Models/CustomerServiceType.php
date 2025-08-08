<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerServiceType extends Model
{
    protected $table = 'customer_service_types';

    protected $fillable = [
        'customer_id',
        'service_type_id',
    ];

}
