<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Quotation extends Model
{
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
        'approval_status',
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
        'revision_number',
        'cloud_server_hosting'
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

    /**
     * Relationships
     */
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
        return $this->valid_until && $this->status !== 'converted';
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
        DB::beginTransaction();
        try {
            $customerId = $this->customer_id;
            
            // If quotation is for a lead, convert lead to customer first
            if (!$customerId && $this->lead_id) {
                $customerId = $this->convertLeadToCustomer();
            }
            
            if (!$customerId) {
                throw new \Exception('No customer available for invoice creation');
            }

            // Get customer for payment terms
            $customer = Customer::find($customerId);
            $paymentTerms = $customer ? $customer->credit_days : 30;

            $invoice = SalesInvoice::create([
                'invoice_date' => now()->toDateString(),
                'customer_id' => $customerId,
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
                'payment_terms' => $paymentTerms,
                'due_date' => now()->addDays($paymentTerms),
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
                'converted_to_invoice_id' => $invoice->id,
                'customer_id' => $customerId // Update with the customer ID if it was converted from lead
            ]);

            DB::commit();
            return $invoice;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Convert lead to customer
     */
    private function convertLeadToCustomer()
    {
        if (!$this->lead_id || !$this->lead) {
            throw new \Exception('No lead found for conversion');
        }

        $lead = $this->lead;
        
        // Check if lead is already converted to customer
        if ($lead->converted_to_customer_id) {
            return $lead->converted_to_customer_id;
        }

        // Check if trade debtors group exists
        $tradeDebtorGroup = Group::where('td', 1)->first();
        if (!$tradeDebtorGroup) {
            throw new \Exception('Trade Debtors group not configured');
        }

        // Generate customer code
        $customerCode = $this->generateCustomerCode();
        
        // Create ledger for customer
        $ledgerName = ($lead->company_name ?: $lead->contact_person) . ' (' . $customerCode . ')';
        $ledger = Ledger::create([
            'group_id' => $tradeDebtorGroup->id,
            'name' => $ledgerName,
            'type' => 0,
            'reconciliation' => 0,
            'aging' => 1,
            'credit_aging' => 0
        ]);

        // Create customer from lead data
        $customer = Customer::create([
            'customer_code' => $customerCode,
            'ledger_id' => $ledger->id,
            'company_name' => $lead->company_name ?: $lead->contact_person,
            'contact_person' => $lead->contact_person,
            'email' => $lead->email,
            'phone' => $lead->phone,
            'mobile' => $lead->mobile,
            'address_line1' => $lead->address,
            'city' => $lead->city,
            'state' => $lead->state,
            'country' => $lead->country ?: 'Malaysia',
            'source' => 'lead_conversion', // Now a valid enum value
            'reference_by' => 'Lead: ' . $lead->lead_no,
            'assigned_to' => $lead->assigned_to,
            'status' => 'active',
            'notes' => 'Converted from lead: ' . $lead->lead_no,
            'lead_id' => $lead->id,
            'created_by' => auth()->id()
        ]);

        // Update lead with conversion details
        $lead->update([
            'lead_status' => 'won',
            'converted_to_customer_id' => $customer->id,
            'conversion_date' => now()
        ]);

        return $customer->id;
    }

    /**
     * Generate unique customer code
     */
    private function generateCustomerCode()
    {
        $prefix = 'CU';
        $newNumber = 1;

        do {
            $code = $prefix . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
            $exists = Customer::where('customer_code', $code)->exists();
            $newNumber++;
        } while ($exists);

        return $code;
    }
 



}