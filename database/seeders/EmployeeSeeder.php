<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Company;
use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();
        $adminDept = Department::where('code', 'ADMIN')->first();
        $salesDept = Department::where('code', 'SALES')->first();
        $financeDept = Department::where('code', 'FINANCE')->first();
        $opsDept = Department::where('code', 'OPS')->first();

        $admin = User::where('email', 'admin@erpdemo.com')->first();
        $manager = User::where('email', 'manager@erpdemo.com')->first();
        $employee = User::where('email', 'employee@erpdemo.com')->first();

        Employee::create([
            'user_id' => $admin->id,
            'company_id' => $company->id,
            'department_id' => $adminDept->id,
            'employee_id' => 'EMP001',
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@erpdemo.com',
            'phone' => '+1234567890',
            'hire_date' => now()->subYear(),
            'position' => 'System Administrator',
            'salary' => 75000.00,
            'employment_type' => 'full_time',
            'status' => 'active',
        ]);

        Employee::create([
            'user_id' => $manager->id,
            'company_id' => $company->id,
            'department_id' => $salesDept->id,
            'employee_id' => 'EMP002',
            'first_name' => 'Manager',
            'last_name' => 'User',
            'email' => 'manager@erpdemo.com',
            'phone' => '+1234567891',
            'hire_date' => now()->subMonths(6),
            'position' => 'Sales Manager',
            'salary' => 60000.00,
            'employment_type' => 'full_time',
            'status' => 'active',
        ]);

        Employee::create([
            'user_id' => $employee->id,
            'company_id' => $company->id,
            'department_id' => $opsDept->id,
            'employee_id' => 'EMP003',
            'first_name' => 'Employee',
            'last_name' => 'User',
            'email' => 'employee@erpdemo.com',
            'phone' => '+1234567892',
            'hire_date' => now()->subMonths(3),
            'position' => 'Operations Specialist',
            'salary' => 45000.00,
            'employment_type' => 'full_time',
            'status' => 'active',
        ]);
    }
}
