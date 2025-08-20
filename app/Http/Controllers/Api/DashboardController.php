<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\SalesOrder;
use App\Models\PurchaseOrder;
use App\Models\InventoryTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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

        // Calculate financial metrics
        $totalSales = SalesOrder::where('status', '!=', 'cancelled')->sum('total_amount');
        $totalPurchases = PurchaseOrder::where('status', '!=', 'cancelled')->sum('total_amount');
        
        // Calculate inventory value
        $inventoryValue = $products->sum(function ($product) {
            return $product->stock_quantity * $product->average_cost;
        });

        // Get sales trend for last 6 months
        $salesTrend = $this->getSalesTrend();

        // Get recent transactions
        $recentTransactions = $this->getRecentTransactions();

        // Get statistics
        $stats = [
            'total_products' => Product::count(),
            'total_customers' => Customer::count(),
            'total_suppliers' => Supplier::count(),
            'total_sales_orders' => SalesOrder::count(),
            'total_purchase_orders' => PurchaseOrder::count(),
            'low_stock_products' => $lowStockProducts->count(),
            'total_sales' => $totalSales,
            'total_purchases' => $totalPurchases,
            'inventory_value' => $inventoryValue,
            'recent_sales_orders' => SalesOrder::with(['customer', 'created_by_user'])
                ->latest()
                ->take(5)
                ->get(),
            'low_stock_products_list' => $lowStockProducts->take(5)->values(),
            'sales_trend' => $salesTrend,
            'recent_transactions' => $recentTransactions,
        ];

        return response()->json([
            'company' => $company,
            'stats' => $stats,
        ]);
    }

    private function getSalesTrend(): array
    {
        $months = [];
        $sales = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months[] = $date->format('M');
            
            $monthSales = SalesOrder::where('status', '!=', 'cancelled')
                ->whereYear('order_date', $date->year)
                ->whereMonth('order_date', $date->month)
                ->sum('total_amount');
            
            $sales[] = $monthSales;
        }

        return [
            'months' => $months,
            'sales' => $sales
        ];
    }

    private function getRecentTransactions(): array
    {
        $transactions = [];
        
        // Get recent sales orders
        $recentSales = SalesOrder::with('customer')
            ->latest()
            ->take(3)
            ->get();
            
        foreach ($recentSales as $sale) {
            $transactions[] = [
                'id' => $sale->id,
                'type' => 'sale',
                'amount' => $sale->total_amount,
                'description' => 'Sales Order: ' . $sale->so_number,
                'customer' => $sale->customer->name ?? 'N/A',
                'date' => $sale->order_date,
                'status' => $sale->status
            ];
        }

        // Get recent purchase orders
        $recentPurchases = PurchaseOrder::with('supplier')
            ->latest()
            ->take(3)
            ->get();
            
        foreach ($recentPurchases as $purchase) {
            $transactions[] = [
                'id' => $purchase->id,
                'type' => 'purchase',
                'amount' => $purchase->total_amount,
                'description' => 'Purchase Order: ' . $purchase->po_number,
                'supplier' => $purchase->supplier->name ?? 'N/A',
                'date' => $purchase->order_date,
                'status' => $purchase->status
            ];
        }

        // Sort by date and take top 5
        usort($transactions, function ($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });

        return array_slice($transactions, 0, 5);
    }
}
