<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;



class Lead extends Model
{
    protected $table = 'leads';
    protected $fillable = [
        'lead_no',
        'company_name',
        'contact_person',
        'email',
        'phone',
        'mobile',
        'address',
        'city',
        'state',
        'country',
        'temple_category_id',
        'temple_size',
        'source',
        'source_details',
        'interested_in',
        'lead_status',
        'lead_score',
        'assigned_to',
        'last_contact_date',
        'next_followup_date',
        'follow_up_scheduled',
        'reminder_sent_date',
        'converted_to_customer_id',
        'conversion_date',
        'lost_reason',
        'notes',
        'created_by',
        'follow_up_count',
        'last_activity_date',
        'demo_requested',
        'demo_completed',
        'quotation_sent',
        'quotation_count',
        'last_quotation_date',
        'total_quoted_value'
    ];

    protected $casts = [
        'last_contact_date' => 'datetime',
        'next_followup_date' => 'date',
        'conversion_date' => 'datetime',
        'last_activity_date' => 'datetime',
        'last_quotation_date' => 'datetime',
        'reminder_sent_date' => 'datetime',
        'demo_requested' => 'boolean',
        'demo_completed' => 'boolean',
        'quotation_sent' => 'boolean',
        'follow_up_scheduled' => 'boolean',
        'total_quoted_value' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($lead) {
            if (empty($lead->lead_no)) {
                $lead->lead_no = self::generateLeadNumber();
            }
        });
    }

    public static function generateLeadNumber()
    {
        $prefix = 'LEAD';
        $year = date('Y');
        $lastLead = self::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        if ($lastLead) {
            $lastNumber = intval(substr($lastLead->lead_no, -4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . $year . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    public function templeCategory(): BelongsTo
    {
        return $this->belongsTo(TempleCategory::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'assigned_to');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'converted_to_customer_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(LeadActivity::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(LeadDocument::class);
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class);
    }

    public function reminderLogs(): HasMany
    {
        return $this->hasMany(LeadReminderLog::class);
    }

    public function scopeNeedingFollowUp($query)
    {
        return $query->where('lead_status', '!=', 'won')
            ->where('lead_status', '!=', 'lost')
            ->whereNull('converted_to_customer_id')
            ->where('follow_up_scheduled', 0)
            ->whereRaw('DATEDIFF(NOW(), created_at) >= 7');
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn('lead_status', ['won', 'lost'])
            ->whereNull('converted_to_customer_id');
    }

    public function getIsConvertedAttribute()
    {
        return !is_null($this->converted_to_customer_id);
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'new' => 'primary',
            'contacted' => 'info',
            'qualified' => 'warning',
            'proposal' => 'secondary',
            'negotiation' => 'dark',
            'won' => 'success',
            'lost' => 'danger'
        ];

        return $badges[$this->lead_status] ?? 'secondary';
    }
    public function followUps()
    {
        return $this->hasMany(FollowUp::class);
    }

    public function communicationHistory()
    {
        return $this->hasMany(CommunicationHistory::class);
    }
}
