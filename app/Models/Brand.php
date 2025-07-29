<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Models;
class Brand extends Model
{
    protected $table = 'brands';

    protected $fillable = [
        'name',
        'code',
        'logo',
        'status',
        'created_by',
    ];
    public function models()
{
    return $this->hasMany(Models::class, 'brand_id');
}

}
