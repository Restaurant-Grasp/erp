<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
     protected $table = 'vendors';
    protected $fillable = [
        'vendor_code',
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
        'payment_terms',
        'credit_limit',
        'bank_name',
        'bank_account_no',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'ledger_id'        => 'integer',
        'category_id'      => 'integer',
        'payment_terms'    => 'integer',
        'credit_limit'     => 'decimal:2',
        'created_by'       => 'integer',
    ];

    protected $attributes = [
        'country'      => 'Malaysia',
        'payment_terms'=> 30,
        'credit_limit' => 0.00,
        'status'       => 'active',
    ];
}
