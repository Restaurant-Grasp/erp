<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Contact extends Model
{
    protected $fillable = [
        'entity_id',
        'entity_type',
        'name',
        'email',
        'phone',
        'is_primary',
        'is_billing_contact',
        'is_technical_contact'
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'is_billing_contact' => 'boolean',
        'is_technical_contact' => 'boolean',
    ];

    /**
     * Get the entity that owns the contact (polymorphic relationship)
     */
    public function entity(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'entity_type', 'entity_id');
    }

    /**
     * Scope to get primary contacts
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Scope to get billing contacts
     */
    public function scopeBilling($query)
    {
        return $query->where('is_billing_contact', true);
    }

    /**
     * Scope to get technical contacts
     */
    public function scopeTechnical($query)
    {
        return $query->where('is_technical_contact', true);
    }

    /**
     * Get contact types as array
     */
    public function getContactTypesAttribute()
    {
        $types = [];
        if ($this->is_primary) $types[] = 'Primary';
        if ($this->is_billing_contact) $types[] = 'Billing';
        if ($this->is_technical_contact) $types[] = 'Technical';
        return $types;
    }

    /**
     * Get contact types as string
     */
    public function getContactTypesStringAttribute()
    {
        return implode(', ', $this->contact_types);
    }
}