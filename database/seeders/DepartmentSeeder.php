<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();
        $admin = User::where('email', 'admin@erpdemo.com')->first();
        $manager = User::where('email', 'manager@erpdemo.com')->first();

        Department::firstOrCreate(
            ['code' => 'ADMIN'],
            [
                'company_id' => $company->id,
                'name' => 'Administration',
                'description' => 'Administrative department',
                'is_active' => true,
            ]
        );

        Department::firstOrCreate(
            ['code' => 'SALES'],
            [
                'company_id' => $company->id,
                'name' => 'Sales',
                'description' => 'Sales and marketing department',
                'is_active' => true,
            ]
        );

        Department::firstOrCreate(
            ['code' => 'FINANCE'],
            [
                'company_id' => $company->id,
                'name' => 'Finance',
                'description' => 'Finance and accounting department',
                'is_active' => true,
            ]
        );

        Department::firstOrCreate(
            ['code' => 'OPS'],
            [
                'company_id' => $company->id,
                'name' => 'Operations',
                'description' => 'Operations and logistics department',
                'is_active' => true,
            ]
        );
    }
}
