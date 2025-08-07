<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
class LeadActivity extends Model
{
      protected $table = 'lead_activities';
      protected $fillable = [
        'lead_id',
        'activity_type',
        'activity_date',
        'subject',
        'description',
        'outcome',
        'next_action',
        'created_by',
    ];
        public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
