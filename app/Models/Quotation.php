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

    public function invoice()
    {
        return $this->belongsTo(SalesInvoice::class, 'converted_to_invoice_id');
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function items()
    {
        return $this->hasMany(QuotationItem::class)->orderBy('sort_order');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function convertedInvoice()
    {
        return $this->belongsTo(SalesInvoice::class, 'converted_to_invoice_id');
    }

    public function parentQuotation()
    {
        return $this->belongsTo(Quotation::class, 'parent_quotation_id');
    }

    public function revisions()
    {
        return $this->hasMany(Quotation::class, 'parent_quotation_id')->orderBy('revision_number');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['draft', 'sent', 'accepted']);
    }

    public function scopePendingApproval($query)
    {
        return $query->where('approval_status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('approval_status', 'approved');
    }

    /**
     * Accessors
     */
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

    public function getApprovalStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger'
        ];
        return $badges[$this->approval_status] ?? 'warning';
    }

    public function getIsExpiredAttribute()
    {
        return $this->valid_until && $this->valid_until->isPast() && $this->status !== 'converted';
    }

    public function getCanBeEditedAttribute()
    {
        return $this->approval_status === 'pending' && !$this->is_expired;
    }

    public function getCanBeApprovedAttribute()
    {
        return $this->approval_status === 'pending' && !$this->is_expired;
    }

    public function getCanBeDeletedAttribute()
    {
        return $this->approval_status === 'pending' && $this->status !== 'converted';
    }

    /**
     * Calculate totals
     */
    public function calculateTotals()
    {
        $subtotal = $this->items->sum(function ($item) {
            return $item->quantity * $item->unit_price - $item->discount_amount;
        });

        $taxAmount = $this->items->sum('tax_amount');
        $totalAmount = $subtotal + $taxAmount;

        $this->update([
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount
        ]);

        return $this;
    }

    /**
     * Create revision
     */
    public function createRevision()
    {
        $revision = $this->replicate([
            'quotation_no',
            'status',
            'approval_status',
            'approved_by',
            'approved_date',
            'sent_date',
            'accepted_date'
        ]);

        $revision->parent_quotation_id = $this->id;
        $revision->is_revised = false;
        $revision->status = 'draft';
        $revision->approval_status = 'pending';
        $revision->save();

        // Copy items
        foreach ($this->items as $item) {
            $newItem = $item->replicate();
            $newItem->quotation_id = $revision->id;
            $newItem->save();
        }

        // Mark original as revised
        $this->update(['is_revised' => true]);

        return $revision;
    }

    /**
     * Approve quotation
     */
    public function approve($userId)
    {
        $this->update([
            'approval_status' => 'approved',
            'approved_by' => $userId,
            'approved_date' => now()
        ]);

        // Auto-convert to invoice
        return $this->convertToInvoice();
    }

    /**
     * Convert to invoice
     */
    public function convertToInvoice()
    {
        $invoice = SalesInvoice::create([
            'invoice_date' => now()->toDateString(),
            'customer_id' => $this->customer_id,
            'quotation_id' => $this->id,
            'reference_no' => $this->reference_no,
            'currency' => $this->currency,
            'exchange_rate' => $this->exchange_rate,
            'subtotal' => $this->subtotal,
            'discount_type' => $this->discount_type,
            'discount_value' => $this->discount_value,
            'discount_amount' => $this->discount_amount,
            'tax_amount' => $this->tax_amount,
            'total_amount' => $this->total_amount,
            'payment_terms' => $this->customer->credit_days ?? 30,
            'due_date' => now()->addDays($this->customer->credit_days ?? 30),
            'status' => 'pending',
            'balance_amount' => $this->total_amount,
            'created_by' => auth()->id()
        ]);

        // Copy items
        foreach ($this->items as $item) {
            $invoice->items()->create([
                'item_type' => $item->item_type,
                'item_id' => $item->item_id,
                'description' => $item->description,
                'quantity' => $item->quantity,
                'delivered_quantity' => 0,
                'delivery_status' => 'not_delivered',
                'uom_id' => $item->uom_id,
                'unit_price' => $item->unit_price,
                'discount_type' => $item->discount_type,
                'discount_value' => $item->discount_value,
                'discount_amount' => $item->discount_amount,
                'tax_id' => $item->tax_id,
                'tax_rate' => $item->tax_rate,
                'tax_amount' => $item->tax_amount,
                'total_amount' => $item->total_amount,
                'sort_order' => $item->sort_order
            ]);
        }

        // Update quotation status
        $this->update([
            'status' => 'converted',
            'converted_to_invoice_id' => $invoice->id
        ]);

        return $invoice;
    }
}