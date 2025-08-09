<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryOrder extends Model
{
    protected $fillable = [
        'do_no',
        'do_date',
        'customer_id',
        'invoice_id',
        'delivery_address',
        'delivery_date',
        'delivered_by',
        'received_by',
        'status',
        'notes',
        'created_by'
    ];

    protected $casts = [
        'do_date' => 'date',
        'delivery_date' => 'date'
    ];

    /**
     * Relationships
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function invoice()
    {
        return $this->belongsTo(SalesInvoice::class, 'invoice_id');
    }

    public function items()
    {
        return $this->hasMany(DeliveryOrderItem::class, 'do_id')->orderBy('id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeInTransit($query)
    {
        return $query->where('status', 'in_transit');
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    /**
     * Accessors
     */
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'secondary',
            'in_transit' => 'info',
            'delivered' => 'success',
            'cancelled' => 'danger'
        ];
        return $badges[$this->status] ?? 'secondary';
    }

    public function getTotalItemsAttribute()
    {
        return $this->items->sum('quantity');
    }

    public function getTotalDeliveredAttribute()
    {
        return $this->items->sum('delivered_quantity');
    }

    public function getTotalDamagedAttribute()
    {
        return $this->items->sum('damaged_quantity');
    }

    /**
     * Generate DO number
     */
    public static function generateDoNumber()
    {
        $prefix = 'DO';
        $year = now()->year;
        $month = now()->format('m');
        
        $lastDo = static::where('do_no', 'like', "{$prefix}{$year}{$month}%")
                       ->orderBy('do_no', 'desc')
                       ->first();

        if ($lastDo) {
            $lastNumber = intval(substr($lastDo->do_no, strlen($prefix . $year . $month)));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . $year . $month . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Update delivery status
     */
    public function updateDeliveryStatus()
    {
        $totalItems = $this->items->count();
        $completedItems = $this->items->where('delivery_status', 'completed')->count();
        $partialItems = $this->items->where('delivery_status', 'partial')->count();

        if ($completedItems === $totalItems) {
            $this->update(['status' => 'delivered']);
        } elseif ($completedItems > 0 || $partialItems > 0) {
            $this->update(['status' => 'in_transit']);
        }

        // Update invoice delivery status
        if ($this->invoice) {
            $this->invoice->updateDeliveryStatus();
        }

        return $this;
    }
}



