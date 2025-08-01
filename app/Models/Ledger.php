<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ledger extends Model
{
    protected $table = 'ledgers';
    public $timestamps = true;
    protected $fillable = [
        'group_id',
        'name',
        'type',
        'reconciliation',
        'pa',
        'hb',
        'aging',
        'credit_aging',
        'iv',
        'notes',
        'left_code',
        'right_code',
        'is_migrate',
    ];

}
