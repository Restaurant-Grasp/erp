<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class GrnDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'grn_id',
        'original_name',
        'file_name',
        'file_path',
        'file_type',
        'mime_type',
        'file_size',
        'document_type',
        'description',
        'uploaded_by',
         'uploaded_at',
    ];

    protected $casts = [
        'file_size' => 'integer',
          'uploaded_at' => 'datetime',
    ];

    /**
     * Get the GRN that owns the document.
     */
    public function grn()
    {
        return $this->belongsTo(GoodsReceiptNote::class, 'grn_id');
    }

    /**
     * Get the user who uploaded the document.
     */
    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get the full URL for the document.
     */
    public function getUrlAttribute()
    {
        return Storage::url($this->file_path);
    }

    /**
     * Get formatted file size.
     */
    public function getFormattedSizeAttribute()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get file extension.
     */
    public function getExtensionAttribute()
    {
        return pathinfo($this->original_name, PATHINFO_EXTENSION);
    }

    /**
     * Check if file is an image.
     */
    public function getIsImageAttribute()
    {
        return in_array(strtolower($this->extension), ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp']);
    }

    /**
     * Check if file is a PDF.
     */
    public function getIsPdfAttribute()
    {
        return strtolower($this->extension) === 'pdf';
    }

    /**
     * Get icon class for file type.
     */
    public function getIconClassAttribute()
    {
        $extension = strtolower($this->extension);
        
        switch ($extension) {
            case 'pdf':
                return 'fas fa-file-pdf text-danger';
            case 'doc':
            case 'docx':
                return 'fas fa-file-word text-primary';
            case 'xls':
            case 'xlsx':
                return 'fas fa-file-excel text-success';
            case 'jpg':
            case 'jpeg':
            case 'png':
            case 'gif':
            case 'bmp':
            case 'webp':
                return 'fas fa-file-image text-info';
            default:
                return 'fas fa-file text-secondary';
        }
    }

    /**
     * Delete the file from storage when model is deleted.
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($document) {
            if (Storage::exists($document->file_path)) {
                Storage::delete($document->file_path);
            }
        });
    }
}