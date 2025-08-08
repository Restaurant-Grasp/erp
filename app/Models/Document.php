<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    protected $table = 'documents';
    
    protected $fillable = [
        'document_type',
        'reference_type',
        'reference_id',
        'title',
        'file_name',
        'file_path',
        'file_size',
        'file_type',
        'description',
        'uploaded_by'
    ];

    public $timestamps = false;

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getReferenceMorphAttribute()
    {
        $modelMap = [
            'customer' => Customer::class,
            'vendor' => Vendor::class,
            'invoice' => SalesInvoice::class,
            'quotation' => Quotation::class,
            'lead' => Lead::class
        ];

        $modelClass = $modelMap[$this->reference_type] ?? null;
        
        if ($modelClass) {
            return $modelClass::find($this->reference_id);
        }

        return null;
    }
}