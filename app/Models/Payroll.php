<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    protected $fillable = [
        'employee_id',
        'month',
        'basic_salary',
        'allowance',
        'deduction',
        'total_salary',
        'status',
        'paid_at',
    ];

    
    protected $casts = [
        'basic_salary' => 'float',
        'allowance' => 'float',
        'deduction' => 'float',
        'total_salary' => 'float',
        'paid_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
