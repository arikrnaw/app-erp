<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\SalesOrder;
use App\Models\PurchaseOrder;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        // Get the first company (assuming single company for now)
        $company = Company::first();

        // Get all products for low stock calculation
        $products = Product::all();
        $lowStockProducts = $products->filter(function ($product) {
            return $product->stock_quantity <= $product->min_stock_level;
        });

        // Get statistics
        $stats = [
            'total_products' => Product::count(),
            'total_customers' => Customer::count(),
            'total_suppliers' => Supplier::count(),
            'total_sales_orders' => SalesOrder::count(),
            'total_purchase_orders' => PurchaseOrder::count(),
            'low_stock_products' => $lowStockProducts->count(),
            'recent_sales_orders' => SalesOrder::with(['customer', 'created_by_user'])
                ->latest()
                ->take(5)
                ->get(),
            'low_stock_products_list' => $lowStockProducts->take(5)->values(),
        ];

        return response()->json([
            'company' => $company,
            'stats' => $stats,
        ]);
    }
}
