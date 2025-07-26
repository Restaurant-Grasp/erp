<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Department;
use App\Models\User;

class Staff extends Model
{
    protected $table = 'staff';  
    protected $fillable = [
        'employee_id',
        'name',
        'email',
        'phone',
        'department_id',
        'designation',
        'date_of_joining',
        'date_of_birth',
        'gender',
        'address',
        'city',
        'state',
        'postcode',
        'country',
        'ic_number',
        'passport_number',
        'basic_salary',
        'epf_employee_percentage',
        'epf_employer_percentage',
        'socso_employee',
        'socso_employer',
        'commission_percentage',
        'fixed_commission',
        'bank_name',
        'bank_account_no',
        'status',
        'photo',
        'created_by',
    ];
    public function department()
{
    return $this->belongsTo(Department::class);
}
public function user()
{
    return $this->hasOne(User::class, 'staff_id'); 
}
}
