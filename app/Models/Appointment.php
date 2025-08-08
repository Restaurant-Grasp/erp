<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $table = 'appointments';

    protected $fillable = [
        'appointment_no',
        'lead_id',
        'customer_id',
        'appointment_type',
        'scheduled_date',
        'duration_minutes',
        'location',
        'online_meeting_link',
        'assigned_staff_id',
        'status',
        'reminder_sent',
        'notes',
        'outcome',
        'created_by',
        'quotation_id',
    ];

    protected $casts = [
        'scheduled_date' => 'datetime',
        'reminder_sent' => 'boolean',
        'duration_minutes' => 'integer',
    ];
}
