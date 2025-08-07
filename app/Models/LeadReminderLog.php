<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class LeadReminderLog extends Model
{
    protected $fillable = [
        'lead_id',
        'reminder_type',
        'sent_date',
        'email_sent_to',
        'status',
        'error_message'
    ];

    protected $casts = [
        'sent_date' => 'datetime',
        'created_at' => 'datetime'
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }
}
