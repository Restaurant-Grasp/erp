<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory;

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
        'tin_no',
        'category_id',
        'payment_terms',
        'credit_limit',
        'bank_name',
        'bank_account_no',
        'status',
        'notes',
        'created_by'
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'payment_terms' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the status badge class for display
     */
    public function getStatusBadgeAttribute()
    {
        switch ($this->status) {
            case 'active':
                return 'success';
            case 'inactive':
                return 'warning';
            case 'blocked':
                return 'danger';
            default:
                return 'secondary';
        }
    }

    /**
     * Get the full address
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
     * Get the outstanding balance (will be implemented when purchase orders are ready)
     */
    public function getOutstandingBalanceAttribute()
    {
        // This will be calculated from purchase orders/invoices when implemented
        return 0;
    }

    /**
     * Relationships
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function ledger()
    {
        return $this->belongsTo(Ledger::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function serviceTypes()
    {
        return $this->belongsToMany(ServiceType::class, 'vendor_service_types');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_vendors')
                    ->withPivot('vendor_product_code', 'vendor_price', 'lead_time_days', 'is_preferred');
    }

    // Future relationships (when modules are implemented)
    // public function purchaseOrders()
    // {
    //     return $this->hasMany(PurchaseOrder::class);
    // }

    // public function purchaseInvoices()
    // {
    //     return $this->hasMany(PurchaseInvoice::class);
    // }
}