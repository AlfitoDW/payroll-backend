<?php

namespace Database\Seeders;

use App\Models\Payroll;
use App\Models\Employee;
use Illuminate\Database\Seeder;

class PayrollSeeder extends Seeder
{
    public function run(): void
    {
        $employee = Employee::first();
        if (!$employee) return;

        Payroll::updateOrCreate(
            [
                'employee_id' => $employee->id,
                'month' => '2026-01'
            ],
            [
                'basic_salary' => 5000000,
                'allowance' => 1000000,
                'deduction' => 500000,
                'total_salary' => 5500000,
                'status' => 'paid',
                'paid_at' => now(),
            ]
        );
    }
}
