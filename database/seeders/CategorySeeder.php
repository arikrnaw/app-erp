<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Company;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();

        // Main categories
        $electronics = Category::create([
            'company_id' => $company->id,
            'name' => 'Electronics',
            'code' => 'ELEC',
            'description' => 'Electronic products and gadgets',
            'status' => 'active',
        ]);

        $clothing = Category::create([
            'company_id' => $company->id,
            'name' => 'Clothing',
            'code' => 'CLOTH',
            'description' => 'Clothing and apparel',
            'status' => 'active',
        ]);

        $furniture = Category::create([
            'company_id' => $company->id,
            'name' => 'Furniture',
            'code' => 'FURN',
            'description' => 'Furniture and home decor',
            'status' => 'active',
        ]);

        // Sub-categories for Electronics
        Category::create([
            'company_id' => $company->id,
            'name' => 'Smartphones',
            'code' => 'ELEC-SMART',
            'description' => 'Mobile phones and smartphones',
            'parent_id' => $electronics->id,
            'status' => 'active',
        ]);

        Category::create([
            'company_id' => $company->id,
            'name' => 'Laptops',
            'code' => 'ELEC-LAPTOP',
            'description' => 'Laptop computers and accessories',
            'parent_id' => $electronics->id,
            'status' => 'active',
        ]);

        // Sub-categories for Clothing
        Category::create([
            'company_id' => $company->id,
            'name' => 'Men\'s Clothing',
            'code' => 'CLOTH-MEN',
            'description' => 'Clothing for men',
            'parent_id' => $clothing->id,
            'status' => 'active',
        ]);

        Category::create([
            'company_id' => $company->id,
            'name' => 'Women\'s Clothing',
            'code' => 'CLOTH-WOMEN',
            'description' => 'Clothing for women',
            'parent_id' => $clothing->id,
            'status' => 'active',
        ]);
    }
}
