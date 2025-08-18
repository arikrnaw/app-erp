<?php

namespace Database\Seeders;

use App\Models\Supplier;
use App\Models\Company;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();

        Supplier::create([
            'company_id' => $company->id,
            'name' => 'Tech Solutions Inc.',
            'code' => 'SUP001',
            'email' => 'orders@techsolutions.com',
            'phone' => '+1234567890',
            'address' => '456 Tech Street',
            'city' => 'Tech City',
            'state' => 'TC',
            'country' => 'United States',
            'postal_code' => '54321',
            'tax_number' => 'TAX987654321',
            'website' => 'https://techsolutions.com',
            'notes' => 'Primary electronics supplier',
            'status' => 'active',
        ]);

        Supplier::create([
            'company_id' => $company->id,
            'name' => 'Fashion Wholesale Co.',
            'code' => 'SUP002',
            'email' => 'orders@fashionwholesale.com',
            'phone' => '+1234567891',
            'address' => '789 Fashion Avenue',
            'city' => 'Fashion City',
            'state' => 'FC',
            'country' => 'United States',
            'postal_code' => '67890',
            'tax_number' => 'TAX123456789',
            'website' => 'https://fashionwholesale.com',
            'notes' => 'Clothing and apparel supplier',
            'status' => 'active',
        ]);

        Supplier::create([
            'company_id' => $company->id,
            'name' => 'Office Supplies Plus',
            'code' => 'SUP003',
            'email' => 'orders@officesupplies.com',
            'phone' => '+1234567892',
            'address' => '321 Office Drive',
            'city' => 'Office City',
            'state' => 'OC',
            'country' => 'United States',
            'postal_code' => '13579',
            'tax_number' => 'TAX456789123',
            'website' => 'https://officesupplies.com',
            'notes' => 'Office supplies and furniture',
            'status' => 'active',
        ]);
    }
}
