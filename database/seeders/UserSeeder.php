<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Company;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();

        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@erpdemo.com',
            'workos_id' => 'admin-' . uniqid(),
            'avatar' => '',
        ]);

        // Create manager user
        User::create([
            'name' => 'Manager User',
            'email' => 'manager@erpdemo.com',
            'workos_id' => 'manager-' . uniqid(),
            'avatar' => '',
        ]);

        // Create employee user
        User::create([
            'name' => 'Employee User',
            'email' => 'employee@erpdemo.com',
            'workos_id' => 'employee-' . uniqid(),
            'avatar' => '',
        ]);
    }
}
