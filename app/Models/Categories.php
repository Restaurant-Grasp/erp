<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categories extends Model
{
    protected $table = 'categories';

    protected $fillable = [
        'parent_id',
        'name',
        'code',
        'description',
        'type',
        'status',
        'created_by',
    ];

    public function parentCategories()
    {
        return $this->belongsTo(Categories::class, 'parent_id');
    }

    public function childrenCategories()
    {
        return $this->hasMany(Categories::class, 'parent_id');
    }
    
}
