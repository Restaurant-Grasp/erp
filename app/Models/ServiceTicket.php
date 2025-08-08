<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceTicket extends Model
{
    protected $table = 'service_tickets';

    protected $fillable = [
        'ticket_no',
        'ticket_date',
        'customer_id',
        'product_id',
        'serial_number_id',
        'issue_type',
        'priority',
        'issue_description',
        'assigned_to',
        'status',
        'resolution',
        'spare_machine_id',
        'estimated_completion',
        'actual_completion',
        'service_charge',
        'parts_charge',
        'total_charge',
        'invoice_id',
        'created_by',
    ];

    protected $casts = [
        'ticket_date' => 'datetime',
        'estimated_completion' => 'date',
        'actual_completion' => 'datetime',
        'service_charge' => 'float',
        'parts_charge' => 'float',
        'total_charge' => 'float',
    ];

  
}
