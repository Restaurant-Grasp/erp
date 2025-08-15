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

    public function scopePartial($query)
    {
        return $query->where('status', 'partial');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
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


    /**
     * Get the payments for this invoice
     */
    public function payments()
    {
        return $this->hasMany(SalesInvoicePayment::class, 'invoice_id');
    }

    /**
     * Get the accounting entry for this invoice
     */
    public function entry()
    {
        return $this->hasOne(Entry::class, 'inv_id')->where('inv_type', 1);
    }

    /**
     * Check if invoice can receive payments
     */
    public function getCanReceivePaymentsAttribute()
    {
        return in_array($this->status, ['pending', 'partial']) && $this->balance_amount > 0;
    }

    /**
     * Calculate and update invoice totals based on items
     */
    public function calculateTotals()
    {
        $subtotal = 0;
        $totalTax = 0;
        $totalDiscount = 0;

        foreach ($this->items as $item) {
            $lineTotal = $item->quantity * $item->unit_price;
            $subtotal += $lineTotal;

            // Calculate item discount
            $itemDiscount = 0;
            if ($item->discount_value > 0) {
                if ($item->discount_type === 'percentage') {
                    $itemDiscount = ($lineTotal * $item->discount_value) / 100;
                } else {
                    $itemDiscount = $item->discount_value;
                }
            }
            $totalDiscount += $itemDiscount;

            // Calculate item tax (on amount after discount)
            $taxableAmount = $lineTotal - $itemDiscount;
            if ($item->tax_rate > 0) {
                $itemTax = ($taxableAmount * $item->tax_rate) / 100;
                $totalTax += $itemTax;

                // Update item tax amount
                $item->update(['tax_amount' => $itemTax]);
            }

            // Update item total
            $item->update([
                'discount_amount' => $itemDiscount,
                'total_amount' => $taxableAmount + ($item->tax_amount ?? 0)
            ]);
        }

        // Calculate invoice level discount
        $invoiceDiscount = 0;
        if ($this->discount_value > 0) {
            if ($this->discount_type === 'percentage') {
                $invoiceDiscount = ($subtotal * $this->discount_value) / 100;
            } else {
                $invoiceDiscount = $this->discount_value;
            }
        }

        $totalAmount = $subtotal - $totalDiscount - $invoiceDiscount + $totalTax;
        $balanceAmount = $totalAmount - $this->paid_amount;

        $this->update([
            'subtotal' => $subtotal,
            'discount_amount' => $totalDiscount + $invoiceDiscount,
            'tax_amount' => $totalTax,
            'total_amount' => $totalAmount,
            'balance_amount' => $balanceAmount
        ]);
    }

    /**
     * Scope for overdue invoices
     */
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
            ->whereIn('status', ['pending', 'partial']);
    }

    /**
     * Scope for pending invoices
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Check if invoice is overdue
     */
    public function getIsOverdueAttribute()
    {
        return $this->due_date < now() && in_array($this->status, ['pending', 'partial']);
    }

    /**
     * Check if invoice can be cancelled
     */
    public function getCanBeCancelledAttribute()
    {
        return $this->paid_amount == 0 && in_array($this->status, ['pending', 'draft']);
    }

    /**
     * Cancel the invoice
     */
    public function cancel()
    {
        if (!$this->can_be_cancelled) {
            throw new \Exception('Invoice cannot be cancelled as payments have been made.');
        }

        $this->update(['status' => 'cancelled']);

        // TODO: Reverse accounting entries if needed
    }

    /**
     * Submit to e-invoice system (placeholder)
     */
    public function submitToEInvoice()
    {
        // Placeholder for e-invoice submission
        $this->update([
            'e_invoice_status' => 'submitted',
            'e_invoice_submission_date' => now(),
            'e_invoice_uuid' => 'UUID' . uniqid()
        ]);
    }
}
