<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Models extends Model
{
    protected $table = 'models';
    protected $fillable = [
        'brand_id',
        'name',
        'code',
        'specifications',
        'status',
        'created_by',
    ];
    public function followUps()
    {
        return $this->hasMany(FollowUp::class);
    }

    public function communicationHistory()
    {
        return $this->hasMany(CommunicationHistory::class);
    }
}
