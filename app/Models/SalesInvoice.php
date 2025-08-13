<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesInvoice extends Model
{
    protected $table = 'sales_invoices';
    
    protected $fillable = [
        'invoice_no',
        'invoice_date',
        'customer_id',
        'quotation_id',
        'reference_no',
        'po_no',
        'currency',
        'exchange_rate',
        'subtotal',
        'discount_type',
        'discount_value',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'payment_terms',
        'due_date',
        'status',
        'paid_amount',
        'balance_amount',
        'e_invoice_status',
        'e_invoice_uuid',
        'e_invoice_submission_date',
        'notes',
        'entry_id',
        'created_by'
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'e_invoice_submission_date' => 'datetime',
        'exchange_rate' => 'decimal:4',
        'subtotal' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance_amount' => 'decimal:2',
        'payment_terms' => 'integer'
    ];

    public function scopeUnpaid($query)
    {
        return $query->whereIn('status', ['pending', 'partial', 'overdue']);
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function items()
    {
        return $this->hasMany(SalesInvoiceItem::class, 'invoice_id')->orderBy('sort_order');
    }

    public function deliveryOrders()
    {
        return $this->hasMany(DeliveryOrder::class, 'invoice_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function entry()
    {
        return $this->belongsTo(Entry::class, 'entry_id');
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePartial($query)
    {
        return $query->where('status', 'partial');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
                    ->whereIn('status', ['pending', 'partial']);
    }

    /**
     * Accessors
     */
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'draft' => 'secondary',
            'pending' => 'warning',
            'partial' => 'info',
            'paid' => 'success',
            'overdue' => 'danger',
            'cancelled' => 'dark'
        ];
        return $badges[$this->status] ?? 'secondary';
    }

    public function getEInvoiceStatusBadgeAttribute()
    {
        $badges = [
            'not_submitted' => 'secondary',
            'submitted' => 'info',
            'validated' => 'success',
            'rejected' => 'danger'
        ];
        return $badges[$this->e_invoice_status] ?? 'secondary';
    }

    public function getIsOverdueAttribute()
    {
        return $this->due_date && $this->due_date->isPast() && in_array($this->status, ['pending', 'partial']);
    }

    public function getDeliveryStatusAttribute()
    {
        $totalItems = $this->items->count();
        $deliveredItems = $this->items->where('delivery_status', 'delivered')->count();
        $partialItems = $this->items->where('delivery_status', 'partial')->count();

        if ($deliveredItems === $totalItems) {
            return 'delivered';
        } elseif ($deliveredItems > 0 || $partialItems > 0) {
            return 'partial';
        } else {
            return 'not_delivered';
        }
    }

    public function getDeliveryStatusBadgeAttribute()
    {
        $badges = [
            'not_delivered' => 'secondary',
            'partial' => 'warning',
            'delivered' => 'success'
        ];
        return $badges[$this->delivery_status] ?? 'secondary';
    }

    public function getCanBeCancelledAttribute()
    {
        return $this->status !== 'cancelled' && $this->paid_amount == 0;
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
            'total_amount' => $totalAmount,
            'balance_amount' => $totalAmount - $this->paid_amount
        ]);

        return $this;
    }

    /**
     * Update delivery status based on items
     */
    public function updateDeliveryStatus()
    {
        foreach ($this->items as $item) {
            $deliveredQty = $item->deliveryOrderItems->sum('delivered_quantity');
            
            if ($deliveredQty == 0) {
                $item->update(['delivery_status' => 'not_delivered']);
            } elseif ($deliveredQty >= $item->quantity) {
                $item->update([
                    'delivered_quantity' => $item->quantity,
                    'delivery_status' => 'delivered'
                ]);
            } else {
                $item->update([
                    'delivered_quantity' => $deliveredQty,
                    'delivery_status' => 'partial'
                ]);
            }
        }

        return $this;
    }

    /**
     * Cancel invoice
     */
    public function cancel()
    {
        if (!$this->can_be_cancelled) {
            throw new \Exception('Invoice cannot be cancelled as payments have been made.');
        }

        $this->update(['status' => 'cancelled']);

        // TODO: Reverse accounting entries
        $this->reverseAccountingEntries();

        return $this;
    }

    /**
     * Submit to e-invoice system
     */
    public function submitToEInvoice()
    {
        // TODO: Implement e-invoice submission
        // This will be implemented based on Malaysia e-invoice API requirements
        
        $this->update([
            'e_invoice_status' => 'submitted',
            'e_invoice_submission_date' => now()
        ]);

        return $this;
    }

    /**
     * Create accounting entries
     */
    public function createAccountingEntries()
    {
        // TODO: Implement accounting integration
        // This will create journal entries for the invoice
        
        return $this;
    }

    /**
     * Reverse accounting entries
     */
    public function reverseAccountingEntries()
    {
        // TODO: Implement accounting reversal
        // This will reverse journal entries when invoice is cancelled
        
        return $this;
    }
    public function payments()
{
    return $this->hasMany(SalesInvoicePayment::class, 'invoice_id');
}
}