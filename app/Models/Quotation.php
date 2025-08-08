<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'is_revised',
        'parent_quotation_id',
        'revision_number'
    ];

    protected $casts = [
        'quotation_date' => 'date',
        'valid_until' => 'date',
        'sent_date' => 'datetime',
        'accepted_date' => 'datetime',
        'approved_date' => 'datetime',
        'exchange_rate' => 'decimal:4',
        'subtotal' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'is_revised' => 'boolean',
        'revision_number' => 'integer'
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class);
    }

    public function invoice()
    {
        return $this->belongsTo(SalesInvoice::class, 'converted_to_invoice_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function parentQuotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class, 'parent_quotation_id');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(Quotation::class, 'parent_quotation_id');
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'draft' => 'secondary',
            'sent' => 'info',
            'accepted' => 'success',
            'rejected' => 'danger',
            'expired' => 'warning',
            'converted' => 'primary'
        ];

        return $badges[$this->status] ?? 'secondary';
    }
}