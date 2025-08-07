<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
        protected $table = 'quotations';
      protected $fillable = [
        'quotation_no',
        'quotation_date',
        'customer_id',
        'lead_id',
        'valid_until',
        'reference_no',
        'subject',
        'currency',
        'exchange_rate',
        'subtotal',
        'discount_type',
        'discount_value',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'terms_conditions',
        'internal_notes',
        'status',
        'sent_date',
        'accepted_date',
        'rejected_reason',
        'converted_to_invoice_id',
        'template_id',
        'created_by',
        'approved_by',
        'approved_date',
    ];
}
