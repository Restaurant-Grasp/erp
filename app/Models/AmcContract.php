<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AmcContract extends Model
{
    protected $table = 'amc_contracts';

    protected $fillable = [
        'contract_no',
        'customer_id',
        'start_date',
        'end_date',
        'contract_value',
        'payment_terms',
        'services_included',
        'terms_conditions',
        'status',
        'renewal_reminder_days',
        'auto_renew',
        'renewed_to_contract_id',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'contract_value' => 'decimal:2',
        'auto_renew' => 'boolean',
        'renewal_reminder_days' => 'integer',
    ];

}
