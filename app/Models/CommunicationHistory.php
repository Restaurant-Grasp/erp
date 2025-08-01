<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunicationHistory extends Model
{
    protected $table = 'communication_history';
    
    protected $fillable = [
        'lead_id',
        'customer_id',
        'follow_up_id',
        'communication_type',
        'direction',
        'contact_person',
        'contact_number',
        'duration_minutes',
        'subject',
        'content',
        'outcome',
        'recorded_by',
        'communication_date'
    ];

    protected $casts = [
        'communication_date' => 'datetime',
        'created_at' => 'datetime'
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function followUp(): BelongsTo
    {
        return $this->belongsTo(FollowUp::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function getEntityAttribute()
    {
        return $this->lead ?: $this->customer;
    }

    public function getTypeIconAttribute()
    {
        return [
            'phone_call' => 'fa-phone',
            'email' => 'fa-envelope',
            'whatsapp' => 'fa-whatsapp',
            'sms' => 'fa-sms',
            'meeting' => 'fa-handshake',
            'other' => 'fa-comment'
        ][$this->communication_type] ?? 'fa-comment';
    }

    public function getDirectionIconAttribute()
    {
        return $this->direction === 'incoming' ? 'fa-arrow-down' : 'fa-arrow-up';
    }

    public function getDirectionColorAttribute()
    {
        return $this->direction === 'incoming' ? 'success' : 'primary';
    }
}