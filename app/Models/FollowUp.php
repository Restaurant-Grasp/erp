<?php

// app/Models/FollowUp.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class FollowUp extends Model
{
    protected $fillable = [
        'lead_id',
        'customer_id',
        'follow_up_type',
        'priority',
        'scheduled_date',
        'completed_date',
        'status',
        'outcome',
        'subject',
        'description',
        'notes',
        'assigned_to',
        'reminder_sent',
        'reminder_sent_date',
        'template_id',
        'is_recurring',
        'recurring_pattern',
        'recurring_interval',
        'recurring_end_date',
        'parent_follow_up_id',
        'created_by'
    ];

    protected $casts = [
        'scheduled_date' => 'datetime',
        'completed_date' => 'datetime',
        'reminder_sent_date' => 'datetime',
        'recurring_end_date' => 'date',
        'reminder_sent' => 'boolean',
        'is_recurring' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected static function boot()
    {
        parent::boot();

        // Auto-update status to overdue
        static::retrieved(function ($followUp) {
            if ($followUp->status === 'scheduled' && $followUp->scheduled_date->isPast()) {
                $followUp->update(['status' => 'overdue']);
            }
        });

        // Update lead/customer stats when follow-up is created
        static::created(function ($followUp) {
            if ($followUp->lead_id) {
                $followUp->lead->increment('total_follow_ups');
            } elseif ($followUp->customer_id) {
                // Update customer follow-up count if needed
            }
        });

        // Create recurring follow-ups
        static::updated(function ($followUp) {
            if ($followUp->status === 'completed' && $followUp->is_recurring && !$followUp->parent_follow_up_id) {
                $followUp->createNextRecurringFollowUp();
            }
        });
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'assigned_to');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(FollowUpTemplate::class, 'template_id');
    }

    public function parentFollowUp(): BelongsTo
    {
        return $this->belongsTo(FollowUp::class, 'parent_follow_up_id');
    }

    public function childFollowUps(): HasMany
    {
        return $this->hasMany(FollowUp::class, 'parent_follow_up_id');
    }

    public function reminderLogs(): HasMany
    {
        return $this->hasMany(FollowUpReminderLog::class);
    }

    public function communicationHistory(): HasMany
    {
        return $this->hasMany(CommunicationHistory::class);
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue')
            ->orWhere(function ($q) {
                $q->where('status', 'scheduled')
                  ->where('scheduled_date', '<', now());
            });
    }

    public function scopeUpcoming($query, $days = 7)
    {
        return $query->where('status', 'scheduled')
            ->whereBetween('scheduled_date', [now(), now()->addDays($days)]);
    }

    public function scopeNeedingReminder($query)
    {
        $reminderDate = now()->addDays(3);
        
        return $query->where('status', 'scheduled')
            ->where('reminder_sent', false)
            ->whereDate('scheduled_date', '<=', $reminderDate->toDateString())
            ->where('scheduled_date', '>', now());
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function getEntityAttribute()
    {
        return $this->lead ?: $this->customer;
    }

    public function getEntityTypeAttribute()
    {
        return $this->lead_id ? 'lead' : 'customer';
    }

    public function getEntityNameAttribute()
    {
        if ($this->lead) {
            return $this->lead->company_name ?: $this->lead->contact_person;
        } elseif ($this->customer) {
            return $this->customer->company_name;
        }
        return null;
    }

    public function getPriorityColorAttribute()
    {
        return [
            'low' => 'secondary',
            'medium' => 'primary',
            'high' => 'warning',
            'urgent' => 'danger'
        ][$this->priority] ?? 'secondary';
    }

    public function getTypeIconAttribute()
    {
        return [
            'phone_call' => 'fa-phone',
            'email' => 'fa-envelope',
            'whatsapp' => 'fa-whatsapp',
            'in_person_meeting' => 'fa-handshake',
            'video_call' => 'fa-video',
            'other' => 'fa-comment-dots'
        ][$this->follow_up_type] ?? 'fa-calendar';
    }

    public function createNextRecurringFollowUp()
    {
        if (!$this->is_recurring || ($this->recurring_end_date && $this->recurring_end_date->isPast())) {
            return null;
        }

        $nextDate = $this->calculateNextRecurringDate();
        
        if (!$nextDate || ($this->recurring_end_date && $nextDate->isAfter($this->recurring_end_date))) {
            return null;
        }

        return self::create([
            'lead_id' => $this->lead_id,
            'customer_id' => $this->customer_id,
            'follow_up_type' => $this->follow_up_type,
            'priority' => $this->priority,
            'scheduled_date' => $nextDate,
            'subject' => $this->subject,
            'description' => $this->description,
            'assigned_to' => $this->assigned_to,
            'template_id' => $this->template_id,
            'is_recurring' => true,
            'recurring_pattern' => $this->recurring_pattern,
            'recurring_interval' => $this->recurring_interval,
            'recurring_end_date' => $this->recurring_end_date,
            'parent_follow_up_id' => $this->parent_follow_up_id ?: $this->id,
            'created_by' => $this->created_by
        ]);
    }

    protected function calculateNextRecurringDate()
    {
        $baseDate = $this->scheduled_date;
        
        switch ($this->recurring_pattern) {
            case 'daily':
                return $baseDate->addDays($this->recurring_interval ?: 1);
            case 'weekly':
                return $baseDate->addWeeks($this->recurring_interval ?: 1);
            case 'monthly':
                return $baseDate->addMonths($this->recurring_interval ?: 1);
            case 'custom':
                return $baseDate->addDays($this->recurring_interval ?: 1);
            default:
                return null;
        }
    }
}
