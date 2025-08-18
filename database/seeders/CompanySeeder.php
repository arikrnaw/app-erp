<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        Company::create([
            'name' => 'ERP Demo Company',
            'code' => 'DEMO001',
            'email' => 'info@erpdemo.com',
            'phone' => '+1234567890',
            'address' => '123 Business Street',
            'city' => 'Business City',
            'state' => 'BC',
            'country' => 'United States',
            'postal_code' => '12345',
            'tax_number' => 'TAX123456789',
            'website' => 'https://erpdemo.com',
            'status' => 'active',
        ]);
    }
}
