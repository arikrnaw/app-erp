<?php

namespace Database\Seeders;

use App\Models\Position;
use App\Models\Company;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();

        Position::firstOrCreate(
            ['position_code' => 'CEO'],
            [
                'company_id' => $company->id,
                'title' => 'Chief Executive Officer',
                'description' => 'Top executive position',
                'department_id' => 9, // Administration department
                'job_level' => 'Executive',
                'is_active' => true,
            ]
        );

        Position::firstOrCreate(
            ['position_code' => 'MANAGER'],
            [
                'company_id' => $company->id,
                'title' => 'Manager',
                'description' => 'Department manager position',
                'department_id' => 9, // Administration department
                'job_level' => 'Manager',
                'is_active' => true,
            ]
        );

        Position::firstOrCreate(
            ['position_code' => 'SUPERVISOR'],
            [
                'company_id' => $company->id,
                'title' => 'Supervisor',
                'description' => 'Team supervisor position',
                'department_id' => 10, // Sales department
                'job_level' => 'Supervisor',
                'is_active' => true,
            ]
        );

        Position::firstOrCreate(
            ['position_code' => 'STAFF'],
            [
                'company_id' => $company->id,
                'title' => 'Staff',
                'description' => 'Regular staff position',
                'department_id' => 11, // Finance department
                'job_level' => 'Staff',
                'is_active' => true,
            ]
        );

        Position::firstOrCreate(
            ['position_code' => 'INTERN'],
            [
                'company_id' => $company->id,
                'title' => 'Intern',
                'description' => 'Internship position',
                'department_id' => 12, // Operations department
                'job_level' => 'Intern',
                'is_active' => true,
            ]
        );
    }
}
