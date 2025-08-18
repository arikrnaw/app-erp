<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Company;
use App\Models\Category;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();
        $smartphones = Category::where('code', 'ELEC-SMART')->first();
        $laptops = Category::where('code', 'ELEC-LAPTOP')->first();
        $mensClothing = Category::where('code', 'CLOTH-MEN')->first();
        $womensClothing = Category::where('code', 'CLOTH-WOMEN')->first();

        // Electronics - Smartphones
        Product::create([
            'company_id' => $company->id,
            'category_id' => $smartphones->id,
            'name' => 'iPhone 15 Pro',
            'sku' => 'IPHONE-15-PRO',
            'barcode' => '1234567890123',
            'description' => 'Latest iPhone with advanced features',
            'cost_price' => 800.00,
            'selling_price' => 999.00,
            'stock_quantity' => 50,
            'min_stock_level' => 10,
            'unit' => 'pcs',
            'status' => 'active',
        ]);

        Product::create([
            'company_id' => $company->id,
            'category_id' => $smartphones->id,
            'name' => 'Samsung Galaxy S24',
            'sku' => 'SAMSUNG-S24',
            'barcode' => '1234567890124',
            'description' => 'Premium Android smartphone',
            'cost_price' => 700.00,
            'selling_price' => 899.00,
            'stock_quantity' => 30,
            'min_stock_level' => 8,
            'unit' => 'pcs',
            'status' => 'active',
        ]);

        // Electronics - Laptops
        Product::create([
            'company_id' => $company->id,
            'category_id' => $laptops->id,
            'name' => 'MacBook Pro 14"',
            'sku' => 'MACBOOK-PRO-14',
            'barcode' => '1234567890125',
            'description' => 'Professional laptop for developers',
            'cost_price' => 1800.00,
            'selling_price' => 2199.00,
            'stock_quantity' => 20,
            'min_stock_level' => 5,
            'unit' => 'pcs',
            'status' => 'active',
        ]);

        Product::create([
            'company_id' => $company->id,
            'category_id' => $laptops->id,
            'name' => 'Dell XPS 13',
            'sku' => 'DELL-XPS-13',
            'barcode' => '1234567890126',
            'description' => 'Ultrabook for business users',
            'cost_price' => 1200.00,
            'selling_price' => 1499.00,
            'stock_quantity' => 25,
            'min_stock_level' => 6,
            'unit' => 'pcs',
            'status' => 'active',
        ]);

        // Clothing - Men's
        Product::create([
            'company_id' => $company->id,
            'category_id' => $mensClothing->id,
            'name' => 'Men\'s Casual T-Shirt',
            'sku' => 'MEN-TSHIRT-001',
            'barcode' => '1234567890127',
            'description' => 'Comfortable cotton t-shirt',
            'cost_price' => 15.00,
            'selling_price' => 29.99,
            'stock_quantity' => 100,
            'min_stock_level' => 20,
            'unit' => 'pcs',
            'status' => 'active',
        ]);

        // Clothing - Women's
        Product::create([
            'company_id' => $company->id,
            'category_id' => $womensClothing->id,
            'name' => 'Women\'s Blouse',
            'sku' => 'WOMEN-BLOUSE-001',
            'barcode' => '1234567890128',
            'description' => 'Elegant office blouse',
            'cost_price' => 25.00,
            'selling_price' => 49.99,
            'stock_quantity' => 75,
            'min_stock_level' => 15,
            'unit' => 'pcs',
            'status' => 'active',
        ]);
    }
}
