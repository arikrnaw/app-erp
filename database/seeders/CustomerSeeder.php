<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Company;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();

        Customer::create([
            'company_id' => $company->id,
            'name' => 'John Smith',
            'code' => 'CUST001',
            'email' => 'john.smith@email.com',
            'phone' => '+1234567890',
            'address' => '123 Customer Street',
            'city' => 'Customer City',
            'state' => 'CC',
            'country' => 'United States',
            'postal_code' => '11111',
            'tax_number' => 'CUST123456',
            'website' => null,
            'notes' => 'Regular customer',
            'customer_type' => 'individual',
            'status' => 'active',
        ]);

        Customer::create([
            'company_id' => $company->id,
            'name' => 'ABC Corporation',
            'code' => 'CUST002',
            'email' => 'orders@abccorp.com',
            'phone' => '+1234567891',
            'address' => '456 Business Avenue',
            'city' => 'Business City',
            'state' => 'BC',
            'country' => 'United States',
            'postal_code' => '22222',
            'tax_number' => 'CORP789012',
            'website' => 'https://abccorp.com',
            'notes' => 'Corporate client',
            'customer_type' => 'company',
            'status' => 'active',
        ]);

        Customer::create([
            'company_id' => $company->id,
            'name' => 'Sarah Johnson',
            'code' => 'CUST003',
            'email' => 'sarah.johnson@email.com',
            'phone' => '+1234567892',
            'address' => '789 Personal Drive',
            'city' => 'Personal City',
            'state' => 'PC',
            'country' => 'United States',
            'postal_code' => '33333',
            'tax_number' => 'PERS345678',
            'website' => null,
            'notes' => 'Online customer',
            'customer_type' => 'individual',
            'status' => 'active',
        ]);

        Customer::create([
            'company_id' => $company->id,
            'name' => 'XYZ Enterprises',
            'code' => 'CUST004',
            'email' => 'purchasing@xyzenterprises.com',
            'phone' => '+1234567893',
            'address' => '321 Enterprise Road',
            'city' => 'Enterprise City',
            'state' => 'EC',
            'country' => 'United States',
            'postal_code' => '44444',
            'tax_number' => 'ENT901234',
            'website' => 'https://xyzenterprises.com',
            'notes' => 'Bulk order customer',
            'customer_type' => 'company',
            'status' => 'active',
        ]);
    }
}