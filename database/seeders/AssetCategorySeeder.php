<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Finance\AssetCategory;
use App\Models\Company;

class AssetCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first company or create one if none exists
        $company = Company::first();
        
        if (!$company) {
            $company = Company::create([
                'name' => 'Default Company',
                'code' => 'DEFAULT',
                'status' => 'active',
            ]);
        }

        $categories = [
            [
                'name' => 'Buildings',
                'code' => 'BUILDINGS',
                'description' => 'Office buildings, warehouses, and other structures',
                'depreciation_method' => 'straight_line',
                'expected_life_years' => 30,
                'salvage_value_percentage' => 10.00,
            ],
            [
                'name' => 'Machinery & Equipment',
                'code' => 'MACHINERY',
                'description' => 'Production machinery and industrial equipment',
                'depreciation_method' => 'straight_line',
                'expected_life_years' => 10,
                'salvage_value_percentage' => 5.00,
            ],
            [
                'name' => 'Vehicles',
                'code' => 'VEHICLES',
                'description' => 'Company cars, trucks, and delivery vehicles',
                'depreciation_method' => 'straight_line',
                'expected_life_years' => 5,
                'salvage_value_percentage' => 15.00,
            ],
            [
                'name' => 'Computers & IT',
                'code' => 'COMPUTERS',
                'description' => 'Computers, servers, and IT infrastructure',
                'depreciation_method' => 'straight_line',
                'expected_life_years' => 3,
                'salvage_value_percentage' => 0.00,
            ],
            [
                'name' => 'Office Furniture',
                'code' => 'FURNITURE',
                'description' => 'Desks, chairs, and office furnishings',
                'depreciation_method' => 'straight_line',
                'expected_life_years' => 7,
                'salvage_value_percentage' => 10.00,
            ],
            [
                'name' => 'Software',
                'code' => 'SOFTWARE',
                'description' => 'Licensed software and applications',
                'depreciation_method' => 'straight_line',
                'expected_life_years' => 3,
                'salvage_value_percentage' => 0.00,
            ],
        ];

        foreach ($categories as $categoryData) {
            AssetCategory::create(array_merge($categoryData, [
                'company_id' => $company->id,
                'status' => 'active',
            ]));
        }
    }
}
