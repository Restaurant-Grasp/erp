<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
        'lead_id'
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'credit_days' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the ledger associated with the customer.
     */
    public function ledger(): BelongsTo
    {
        return $this->belongsTo(Ledger::class);
    }

    /**
     * Get the category associated with the customer.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the staff member assigned to the customer.
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'assigned_to');
    }

    /**
     * Get the user who created the customer.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the lead that was converted to this customer.
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    /**
     * Get the service types associated with the customer.
     */
    public function serviceTypes(): BelongsToMany
    {
        return $this->belongsToMany(ServiceType::class, 'customer_service_types', 'customer_id', 'service_type_id');
    }

    /**
     * Get all quotations for the customer.
     */
    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class);
    }

    /**
     * Get all sales invoices for the customer.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(SalesInvoice::class);
    }

    /**
     * Get all delivery orders for the customer.
     */
    public function deliveryOrders(): HasMany
    {
        return $this->hasMany(DeliveryOrder::class);
    }

    /**
     * Get all service tickets for the customer.
     */
    public function serviceTickets(): HasMany
    {
        return $this->hasMany(ServiceTicket::class);
    }

    /**
     * Get all AMC contracts for the customer.
     */
    public function amcContracts(): HasMany
    {
        return $this->hasMany(AmcContract::class);
    }

    /**
     * Get all appointments for the customer.
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Get all follow-ups for the customer.
     */
    public function followUps(): HasMany
    {
        return $this->hasMany(FollowUp::class);
    }

    /**
     * Get all communication history for the customer.
     */
    public function communicationHistory(): HasMany
    {
        return $this->hasMany(CommunicationHistory::class);
    }

    /**
     * Get all documents for the customer.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'reference_id')->where('reference_type', 'customer');
    }

    /**
     * Get all subscriptions for the customer.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(CustomerSubscription::class);
    }

    /**
     * Scope for active customers.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Get the status badge color.
     */
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'active' => 'success',
            'inactive' => 'warning',
            'blocked' => 'danger'
        ];

        return $badges[$this->status] ?? 'secondary';
    }

    /**
     * Get the full address.
     */
    public function getFullAddressAttribute()
    {
        $address = [];
        
        if ($this->address_line1) $address[] = $this->address_line1;
        if ($this->address_line2) $address[] = $this->address_line2;
        if ($this->city) $address[] = $this->city;
        if ($this->state) $address[] = $this->state;
        if ($this->postcode) $address[] = $this->postcode;
        if ($this->country) $address[] = $this->country;
        
        return implode(', ', $address);
    }

    /**
     * Get outstanding balance from invoices.
     */
    public function getOutstandingBalanceAttribute()
    {
        return $this->invoices()
            ->whereIn('status', ['pending', 'partial'])
            ->sum('balance_amount');
    }
}