<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@payroll.com'],
            [
                'name' => 'Admin Payroll',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        User::updateOrCreate(
            ['email' => 'employee@payroll.com'],
            [
                'name' => 'Budi Employee',
                'password' => Hash::make('password'),
                'role' => 'employee',
            ]
        );
    }
}
