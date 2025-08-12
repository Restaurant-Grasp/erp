<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
  
    protected $table = 'company';



    protected $fillable = [
        'name',
        'registration_no',
        'tin_number',
        'sst_no',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'postcode',
        'country',
        'phone',
        'email',
        'website',
        'logo',
    ];



}
