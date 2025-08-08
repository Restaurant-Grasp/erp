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
    protected $casts = [
        'date_of_joining' => 'date',
        'date_of_birth' => 'date',
        'basic_salary' => 'decimal:2',
        'epf_employee_percentage' => 'decimal:2',
        'epf_employer_percentage' => 'decimal:2',
        'socso_employee' => 'decimal:2',
        'socso_employer' => 'decimal:2',
        'commission_percentage' => 'decimal:2',
        'fixed_commission' => 'decimal:2'
    ];
    public function department()
    {
        return $this->belongsTo(Department::class);
    }
    public function user()
    {
        return $this->hasOne(User::class, 'staff_id');
    }
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
