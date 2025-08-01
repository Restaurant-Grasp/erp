<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'customers';

    protected $fillable = [
        'customer_code',
        'ledger_id',
        'company_name',
        'contact_person',
        'email',
        'phone',
        'mobile',
        'fax',
        'website',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'postcode',
        'country',
        'registration_no',
        'tax_no',
        'category_id',
        'credit_limit',
        'credit_days',
        'discount_percentage',
        'source',
        'reference_by',
        'assigned_to',
        'status',
        'notes',
        'created_by',
    ];
}
