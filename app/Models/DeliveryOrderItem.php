<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class DeliveryOrderItem extends Model
{
    protected $fillable = [
        'do_id',
        'invoice_item_id',
        'product_id',
        'quantity',
        'delivered_quantity',
        'damaged_quantity',
        'replacement_quantity',
        'uom_id',
        'warranty_start_date',
        'warranty_end_date',
        'delivery_status',
        'serial_numbers',
        'notes'
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'delivered_quantity' => 'decimal:2',
        'damaged_quantity' => 'decimal:2',
        'replacement_quantity' => 'decimal:2',
        'warranty_start_date' => 'date',
        'warranty_end_date' => 'date',
        'serial_numbers' => 'array'
    ];

    /**
     * Relationships
     */
    public function deliveryOrder()
    {
        return $this->belongsTo(DeliveryOrder::class, 'do_id');
    }

    public function invoiceItem()
    {
        return $this->belongsTo(SalesInvoiceItem::class, 'invoice_item_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function uom()
    {
        return $this->belongsTo(Uom::class, 'uom_id');
    }

    public function serialNumbers()
    {
        return $this->hasMany(DeliveryOrderSerial::class, 'do_item_id');
    }

    /**
     * Accessors
     */
    public function getDeliveryStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'secondary',
            'partial' => 'warning',
            'completed' => 'success',
            'damaged' => 'danger'
        ];
        return $badges[$this->delivery_status] ?? 'secondary';
    }

    public function getRemainingQuantityAttribute()
    {
        return $this->quantity - $this->delivered_quantity - $this->damaged_quantity;
    }

    public function getHasSerialTrackingAttribute()
    {
        return $this->product && $this->product->has_serial_number;
    }

    public function getHasWarrantyAttribute()
    {
        return $this->product && $this->product->has_warranty;
    }

    /**
     * Create serial numbers for delivered items
     */
    public function createSerialNumbers($serialData)
    {
        foreach ($serialData as $data) {
            // Create or find serial number
            $serialNumber = ProductSerialNumber::firstOrCreate([
                'product_id' => $this->product_id,
                'serial_number' => $data['serial_number']
            ], [
                'purchase_date' => $data['purchase_date'] ?? null,
                'warranty_start_date' => $data['warranty_start_date'] ?? null,
                'warranty_end_date' => $data['warranty_end_date'] ?? null,
                'current_status' => 'sold',
                'customer_id' => $this->deliveryOrder->customer_id,
                'sales_invoice_id' => $this->deliveryOrder->invoice_id
            ]);

            // Create delivery order serial record
            DeliveryOrderSerial::create([
                'do_item_id' => $this->id,
                'serial_number_id' => $serialNumber->id,
                'status' => $data['status'] ?? 'delivered',
                'warranty_start_date' => $data['warranty_start_date'] ?? null,
                'warranty_end_date' => $data['warranty_end_date'] ?? null,
                'notes' => $data['notes'] ?? null
            ]);
        }

        return $this;
    }

    /**
     * Create replacement serial numbers
     */
    public function createReplacements($replacementData)
    {
        foreach ($replacementData as $data) {
            // Create new serial number for replacement
            $newSerial = ProductSerialNumber::create([
                'product_id' => $this->product_id,
                'serial_number' => $this->generateSerialNumber(),
                'warranty_start_date' => $data['warranty_start_date'],
                'warranty_end_date' => $data['warranty_end_date'],
                'current_status' => 'sold',
                'customer_id' => $this->deliveryOrder->customer_id,
                'sales_invoice_id' => $this->deliveryOrder->invoice_id,
                'is_replacement' => true,
                'original_serial_id' => $data['original_serial_id'],
                'replacement_reason' => $data['replacement_reason'],
                'replacement_date' => now()->toDateString()
            ]);

            // Update original serial status
            if (isset($data['original_serial_id'])) {
                ProductSerialNumber::find($data['original_serial_id'])
                    ->update(['current_status' => 'returned']);
            }

            // Create delivery order serial record
            DeliveryOrderSerial::create([
                'do_item_id' => $this->id,
                'serial_number_id' => $newSerial->id,
                'status' => 'delivered',
                'warranty_start_date' => $data['warranty_start_date'],
                'warranty_end_date' => $data['warranty_end_date'],
                'notes' => 'Replacement for damaged item'
            ]);
        }

        return $this;
    }

    /**
     * Generate serial number
     */
    private function generateSerialNumber()
    {
        $prefix = 'PROD';
        $year = now()->year;
        
        $lastSerial = ProductSerialNumber::where('serial_number', 'like', "{$prefix}-{$year}-%")
                                        ->orderBy('serial_number', 'desc')
                                        ->first();

        if ($lastSerial) {
            $lastNumber = intval(substr($lastSerial->serial_number, strrpos($lastSerial->serial_number, '-') + 1));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . '-' . $year . '-' . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Update delivery status based on quantities
     */
    public function updateDeliveryStatus()
    {
        if ($this->delivered_quantity == 0 && $this->damaged_quantity == 0) {
            $status = 'pending';
        } elseif ($this->delivered_quantity + $this->damaged_quantity >= $this->quantity) {
            $status = 'completed';
        } elseif ($this->damaged_quantity > 0) {
            $status = 'damaged';
        } else {
            $status = 'partial';
        }

        $this->update(['delivery_status' => $status]);
        $this->deliveryOrder->updateDeliveryStatus();

        return $this;
    }
}