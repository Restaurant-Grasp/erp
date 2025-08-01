<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
  protected $table = 'groups';

    protected $fillable = [
        'parent_id',
        'name',
        'code',
        'fixed',
        'tc',
        'td',
        'ac',
        'pd',
        'added_by',
    ];
}
