<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FollowUpReminderLog extends Model
{
    protected $fillable = [
        'follow_up_id',
        'reminder_type',
        'sent_to',
        'sent_date',
        'status',
        'error_message'
    ];

    protected $casts = [
        'sent_date' => 'datetime',
        'created_at' => 'datetime'
    ];

    public function followUp(): BelongsTo
    {
        return $this->belongsTo(FollowUp::class);
    }
}
