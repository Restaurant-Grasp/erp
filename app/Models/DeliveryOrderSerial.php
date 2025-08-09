<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class DeliveryOrderSerial extends Model
{
    protected $fillable = [
        'do_item_id',
        'serial_number_id',
        'status',
        'warranty_start_date',
        'warranty_end_date',
        'notes'
    ];

    protected $casts = [
        'warranty_start_date' => 'date',
        'warranty_end_date' => 'date'
    ];

    /**
     * Relationships
     */
    public function deliveryOrderItem()
    {
        return $this->belongsTo(DeliveryOrderItem::class, 'do_item_id');
    }

    public function serialNumber()
    {
        return $this->belongsTo(ProductSerialNumber::class, 'serial_number_id');
    }

    /**
     * Accessors
     */
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'delivered' => 'success',
            'damaged' => 'danger',
            'replaced' => 'warning'
        ];
        return $badges[$this->status] ?? 'secondary';
    }
}