<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerSubscription extends Model
{
    protected $table = 'customer_subscriptions';
    
    protected $fillable = [
        'subscription_no',
        'customer_id',
        'plan_id',
        'start_date',
        'end_date',
        'next_billing_date',
        'last_billing_date',
        'billing_day',
        'custom_amount',
        'discount_percentage',
        'discount_amount',
        'discount_ledger_id',
        'advance_payment_ledger_id',
        'status',
        'auto_renew',
        'payment_method',
        'cancellation_date',
        'cancellation_reason',
        'activation_date',
        'trial_end_date',
        'notes',
        'created_by'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'next_billing_date' => 'date',
        'last_billing_date' => 'date',
        'cancellation_date' => 'date',
        'activation_date' => 'date',
        'trial_end_date' => 'date',
        'billing_day' => 'integer',
        'custom_amount' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'auto_renew' => 'boolean'
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    public function billings(): HasMany
    {
        return $this->hasMany(SubscriptionBilling::class);
    }
}