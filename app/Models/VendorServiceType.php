<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorServiceType extends Model
{
    use HasFactory;
 protected $table = 'vendor_service_types';
    protected $fillable = [
        'vendor_id',
        'service_type_id'
    ];

    public $timestamps = false;

    /**
     * Relationships
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class);
    }
}