<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class LeadDocument extends Model
{
    protected $fillable = [
        'lead_id',
        'document_name',
        'file_name',
        'file_path',
        'file_size',
        'file_type',
        'uploaded_by'
    ];

    protected $casts = [
        'created_at' => 'datetime'
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getFileSizeFormattedAttribute()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getDownloadUrlAttribute()
    {
        return route('leads.documents.download', ['lead' => $this->lead_id, 'document' => $this->id]);
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($document) {
            // Delete the physical file when deleting the record
            if (Storage::exists($document->file_path)) {
                Storage::delete($document->file_path);
            }
        });
    }
}
