<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Uom extends Model
{
    protected $table = 'uom';  
    protected $fillable = [
        'name',
        'status',
        'created_by',
        'updated_by',
    ];
}
