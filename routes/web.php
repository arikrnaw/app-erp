<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\WorkOS\Http\Middleware\ValidateSessionWithWorkOS;

Route::get('/', fn () => Inertia::render('Welcome'));

Route::middleware([
    'auth',
    ValidateSessionWithWorkOS::class,
])->group(function () {
    // Dashboard
    Route::get('dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // AI Chat
    Route::get('ai-chat', function () {
        return Inertia::render('AiChat/Index');
    })->name('ai-chat.index');

    // Categories
    Route::get('categories', function () {
        return Inertia::render('Categories/Index');
    })->name('categories.index');
    Route::get('categories/create', function () {
        return Inertia::render('Categories/Create');
    })->name('categories.create');
    Route::get('categories/{id}', function ($id) {
        return Inertia::render('Categories/Show', ['id' => $id]);
    })->name('categories.show');
    Route::get('categories/{id}/edit', function ($id) {
        return Inertia::render('Categories/Edit', ['id' => $id]);
    })->name('categories.edit');

    // Products
    Route::get('products', function () {
        return Inertia::render('Products/Index');
    })->name('products.index');
    Route::get('products/create', function () {
        return Inertia::render('Products/Create');
    })->name('products.create');
    Route::get('products/{id}', function ($id) {
        return Inertia::render('Products/Show', ['id' => $id]);
    })->name('products.show');
    Route::get('products/{id}/edit', function ($id) {
        return Inertia::render('Products/Edit', ['id' => $id]);
    })->name('products.edit');

    // CRM - Customers
    Route::get('crm/customers', function () {
        return Inertia::render('CRM/Customers/Index');
    })->name('crm.customers.index');
    Route::get('crm/customers/create', function () {
        return Inertia::render('CRM/Customers/Create');
    })->name('crm.customers.create');
    Route::get('crm/customers/{id}', function ($id) {
        return Inertia::render('CRM/Customers/Show', ['id' => $id]);
    })->name('crm.customers.show');
    Route::get('crm/customers/{id}/edit', function ($id) {
        return Inertia::render('CRM/Customers/Edit', ['id' => $id]);
    })->name('crm.customers.edit');

    // Suppliers
    Route::get('suppliers', function () {
        return Inertia::render('Suppliers/Index');
    })->name('suppliers.index');
    Route::get('suppliers/create', function () {
        return Inertia::render('Suppliers/Create');
    })->name('suppliers.create');
    Route::get('suppliers/{id}', function ($id) {
        return Inertia::render('Suppliers/Show', ['id' => $id]);
    })->name('suppliers.show');
    Route::get('suppliers/{id}/edit', function ($id) {
        return Inertia::render('Suppliers/Edit', ['id' => $id]);
    })->name('suppliers.edit');

    // Sales Orders
    Route::get('sales-orders', function () {
        return Inertia::render('SalesOrders/Index');
    })->name('sales-orders.index');
    Route::get('sales-orders/create', function () {
        return Inertia::render('SalesOrders/Create');
    })->name('sales-orders.create');
    Route::get('sales-orders/{id}', function ($id) {
        return Inertia::render('SalesOrders/Show', ['id' => $id]);
    })->name('sales-orders.show');
    Route::get('sales-orders/{id}/edit', function ($id) {
        return Inertia::render('SalesOrders/Edit', ['id' => $id]);
    })->name('sales-orders.edit');

    // Purchase Orders
    Route::get('purchase-orders', function () {
        return Inertia::render('PurchaseOrders/Index');
    })->name('purchase-orders.index');
    Route::get('purchase-orders/create', function () {
        return Inertia::render('PurchaseOrders/Create');
    })->name('purchase-orders.create');
    Route::get('purchase-orders/{id}', function ($id) {
        return Inertia::render('PurchaseOrders/Show', ['id' => $id]);
    })->name('purchase-orders.show');
    Route::get('purchase-orders/{id}/edit', function ($id) {
        return Inertia::render('PurchaseOrders/Edit', ['id' => $id]);
    })->name('purchase-orders.edit');

    // Purchasing Module Routes
    Route::prefix('purchasing')->group(function () {
        // Purchase Requests
        Route::get('purchase-requests', function () {
            return Inertia::render('Purchasing/PurchaseRequests/Index', [
                'requests' => [],
                'pagination' => null,
            ]);
        })->name('purchasing.purchase-requests.index');

        Route::get('purchase-requests/create', function () {
            return Inertia::render('Purchasing/PurchaseRequests/Create', [
                'request' => null,
            ]);
        })->name('purchasing.purchase-requests.create');

        Route::get('purchase-requests/{id}', function ($id) {
            return Inertia::render('Purchasing/PurchaseRequests/Show', [
                'request' => null,
            ]);
        })->name('purchasing.purchase-requests.show');

        Route::get('purchase-requests/{id}/edit', function ($id) {
            return Inertia::render('Purchasing/PurchaseRequests/Edit', [
                'request' => null,
            ]);
        })->name('purchasing.purchase-requests.edit');

        // Goods Receipts
        Route::get('goods-receipts', function () {
            return Inertia::render('Purchasing/GoodsReceipts/Index', [
                'receipts' => [],
                'pagination' => null,
            ]);
        })->name('purchasing.goods-receipts.index');

        Route::get('goods-receipts/create', function () {
            return Inertia::render('Purchasing/GoodsReceipts/Create', [
                'receipt' => null,
            ]);
        })->name('purchasing.goods-receipts.create');

        Route::get('goods-receipts/{id}', function ($id) {
            return Inertia::render('Purchasing/GoodsReceipts/Show', [
                'receipt' => null,
            ]);
        })->name('purchasing.goods-receipts.show');

        Route::get('goods-receipts/{id}/edit', function ($id) {
            return Inertia::render('Purchasing/GoodsReceipts/Edit', [
                'receipt' => null,
            ]);
        })->name('purchasing.goods-receipts.edit');

        // Purchase Returns
        Route::get('purchase-returns', function () {
            return Inertia::render('Purchasing/PurchaseReturns/Index', [
                'returns' => [],
                'pagination' => null,
            ]);
        })->name('purchasing.purchase-returns.index');

        Route::get('purchase-returns/create', function () {
            return Inertia::render('Purchasing/PurchaseReturns/Create', [
                'return' => null,
            ]);
        })->name('purchasing.purchase-returns.create');

        Route::get('purchase-returns/{id}', function ($id) {
            return Inertia::render('Purchasing/PurchaseReturns/Show', [
                'return' => null,
            ]);
        })->name('purchasing.purchase-returns.show');

        Route::get('purchase-returns/{id}/edit', function ($id) {
            return Inertia::render('Purchasing/PurchaseReturns/Edit', [
                'return' => null,
            ]);
        })->name('purchasing.purchase-returns.edit');
    });

    // CRM Module Routes
    Route::prefix('crm')->group(function () {
        // Prospects
        Route::get('prospects', function () {
            return Inertia::render('CRM/Prospects/Index');
        })->name('crm.prospects.index');

        Route::get('prospects/create', function () {
            return Inertia::render('CRM/Prospects/Create');
        })->name('crm.prospects.create');

        Route::get('prospects/{id}', function ($id) {
            return Inertia::render('CRM/Prospects/Show', ['id' => $id]);
        })->name('crm.prospects.show');

        Route::get('prospects/{id}/edit', function ($id) {
            return Inertia::render('CRM/Prospects/Edit', ['id' => $id]);
        })->name('crm.prospects.edit');

        // Follow-ups
        Route::get('follow-ups', function () {
            return Inertia::render('CRM/FollowUps/Index');
        })->name('crm.follow-ups.index');

        Route::get('follow-ups/create', function () {
            return Inertia::render('CRM/FollowUps/Create');
        })->name('crm.follow-ups.create');

        Route::get('follow-ups/{id}', function ($id) {
            return Inertia::render('CRM/FollowUps/Show', ['id' => $id]);
        })->name('crm.follow-ups.show');

        Route::get('follow-ups/{id}/edit', function ($id) {
            return Inertia::render('CRM/FollowUps/Edit', ['id' => $id]);
        })->name('crm.follow-ups.edit');

        // Support Tickets
        Route::get('support-tickets', function () {
            return Inertia::render('CRM/SupportTickets/Index');
        })->name('crm.support-tickets.index');

        Route::get('support-tickets/create', function () {
            return Inertia::render('CRM/SupportTickets/Create');
        })->name('crm.support-tickets.create');

        Route::get('support-tickets/{id}', function ($id) {
            return Inertia::render('CRM/SupportTickets/Show', ['id' => $id]);
        })->name('crm.support-tickets.show');

        Route::get('support-tickets/{id}/edit', function ($id) {
            return Inertia::render('CRM/SupportTickets/Edit', ['id' => $id]);
        })->name('crm.support-tickets.edit');

        // Customer Segments
        Route::get('customer-segments', function () {
            return Inertia::render('CRM/CustomerSegments/Index');
        })->name('crm.customer-segments.index');

        Route::get('customer-segments/create', function () {
            return Inertia::render('CRM/CustomerSegments/Create');
        })->name('crm.customer-segments.create');

        Route::get('customer-segments/{id}', function ($id) {
            return Inertia::render('CRM/CustomerSegments/Show', ['id' => $id]);
        })->name('crm.customer-segments.show');

        Route::get('customer-segments/{id}/edit', function ($id) {
            return Inertia::render('CRM/CustomerSegments/Edit', ['id' => $id]);
        })->name('crm.customer-segments.edit');
    });

    // Inventory - Warehouses
    Route::get('inventory/warehouses', function () {
        return Inertia::render('Inventory/Warehouses/Index');
    })->name('inventory.warehouses.index');
    Route::get('inventory/warehouses/create', function () {
        return Inertia::render('Inventory/Warehouses/Create');
    })->name('inventory.warehouses.create');
    Route::get('inventory/warehouses/{id}', function ($id) {
        return Inertia::render('Inventory/Warehouses/Show', ['id' => $id]);
    })->name('inventory.warehouses.show');
    Route::get('inventory/warehouses/{id}/edit', function ($id) {
        return Inertia::render('Inventory/Warehouses/Edit', ['id' => $id]);
    })->name('inventory.warehouses.edit');

    // Inventory - Warehouse Locations
    Route::get('inventory/warehouse-locations', function () {
        return Inertia::render('Inventory/WarehouseLocations/Index');
    })->name('inventory.warehouse-locations.index');
    Route::get('inventory/warehouse-locations/create', function () {
        return Inertia::render('Inventory/WarehouseLocations/Create');
    })->name('inventory.warehouse-locations.create');
    Route::get('inventory/warehouse-locations/{id}', function ($id) {
        return Inertia::render('Inventory/WarehouseLocations/Show', ['id' => $id]);
    })->name('inventory.warehouse-locations.show');
    Route::get('inventory/warehouse-locations/{id}/edit', function ($id) {
        return Inertia::render('Inventory/WarehouseLocations/Edit', ['id' => $id]);
    })->name('inventory.warehouse-locations.edit');

    // Inventory - Product Lots
    Route::get('inventory/product-lots', function () {
        return Inertia::render('Inventory/ProductLots/Index');
    })->name('inventory.product-lots.index');
    Route::get('inventory/product-lots/create', function () {
        return Inertia::render('Inventory/ProductLots/Create');
    })->name('inventory.product-lots.create');
    Route::get('inventory/product-lots/{id}', function ($id) {
        return Inertia::render('Inventory/ProductLots/Show', ['id' => $id]);
    })->name('inventory.product-lots.show');
    Route::get('inventory/product-lots/{id}/edit', function ($id) {
        return Inertia::render('Inventory/ProductLots/Edit', ['id' => $id]);
    })->name('inventory.product-lots.edit');

    // Inventory - Reorder Alerts
    Route::get('inventory/reorder-alerts', function () {
        return Inertia::render('Inventory/ReorderAlerts/Index');
    })->name('inventory.reorder-alerts.index');
    Route::get('inventory/reorder-alerts/create', function () {
        return Inertia::render('Inventory/ReorderAlerts/Create');
    })->name('inventory.reorder-alerts.create');
    Route::get('inventory/reorder-alerts/{id}', function ($id) {
        return Inertia::render('Inventory/ReorderAlerts/Show', ['id' => $id]);
    })->name('inventory.reorder-alerts.show');
    Route::get('inventory/reorder-alerts/{id}/edit', function ($id) {
        return Inertia::render('Inventory/ReorderAlerts/Edit', ['id' => $id]);
    })->name('inventory.reorder-alerts.edit');

    // Inventory - Main Routes (must be after specific routes)
    Route::get('inventory', function () {
        return Inertia::render('Inventory/Index');
    })->name('inventory.index');
    Route::get('inventory/create', function () {
        return Inertia::render('Inventory/Create');
    })->name('inventory.create');
    Route::get('inventory/{id}', function ($id) {
        return Inertia::render('Inventory/Show', [
            'id' => $id,
            'transaction' => null // Will be loaded via API in the frontend
        ]);
    })->name('inventory.show');
    Route::get('inventory/{id}/edit', function ($id) {
        return Inertia::render('Inventory/Edit', ['id' => $id]);
    })->name('inventory.edit');

    // Finance - Chart of Accounts
    Route::get('finance/chart-of-accounts', function () {
        return Inertia::render('Finance/ChartOfAccounts/Index');
    })->name('finance.chart-of-accounts.index');
    Route::get('finance/chart-of-accounts/create', function () {
        return Inertia::render('Finance/ChartOfAccounts/Create');
    })->name('finance.chart-of-accounts.create');
    Route::post('finance/chart-of-accounts', function () {
        // This will be handled by the API controller
        return redirect()->route('finance.chart-of-accounts.index');
    })->name('finance.chart-of-accounts.store');
    Route::get('finance/chart-of-accounts/{id}', function ($id) {
        return Inertia::render('Finance/ChartOfAccounts/Show', ['id' => $id]);
    })->name('finance.chart-of-accounts.show');
    Route::get('finance/chart-of-accounts/{id}/edit', function ($id) {
        return Inertia::render('Finance/ChartOfAccounts/Edit', ['id' => $id]);
    })->name('finance.chart-of-accounts.edit');
    Route::put('finance/chart-of-accounts/{id}', function ($id) {
        // This will be handled by the API controller
        return redirect()->route('finance.chart-of-accounts.index');
    })->name('finance.chart-of-accounts.update');
    Route::delete('finance/chart-of-accounts/{id}', function ($id) {
        // This will be handled by the API controller
        return redirect()->route('finance.chart-of-accounts.index');
    })->name('finance.chart-of-accounts.destroy');

    // Finance - Journal Entries
    Route::get('finance/journal-entries', function () {
        return Inertia::render('Finance/JournalEntries/Index');
    })->name('finance.journal-entries.index');
    Route::get('finance/journal-entries/create', function () {
        return Inertia::render('Finance/JournalEntries/Create');
    })->name('finance.journal-entries.create');
    Route::get('finance/journal-entries/{id}', function ($id) {
        return Inertia::render('Finance/JournalEntries/Show', ['id' => $id]);
    })->name('finance.journal-entries.show');
    Route::get('finance/journal-entries/{id}/edit', function ($id) {
        return Inertia::render('Finance/JournalEntries/Edit', ['id' => $id]);
    })->name('finance.journal-entries.edit');

    // Finance - General Ledger
    Route::get('finance/general-ledger', function () {
        return Inertia::render('Finance/GeneralLedger/Index');
    })->name('finance.general-ledger.index');

    // Finance - Trial Balance
    Route::get('finance/trial-balance', function () {
        return Inertia::render('Finance/TrialBalance/Index');
    })->name('finance.trial-balance.index');

    // Finance - Accounts Receivable - Invoices
    Route::get('finance/accounts-receivable/invoices', function () {
        return Inertia::render('Finance/AccountsReceivable/Invoices/Index');
    })->name('finance.accounts-receivable.invoices.index');
    Route::get('finance/accounts-receivable/invoices/create', function () {
        return Inertia::render('Finance/AccountsReceivable/Invoices/Create');
    })->name('finance.accounts-receivable.invoices.create');
    Route::get('finance/accounts-receivable/invoices/{id}', function ($id) {
        return Inertia::render('Finance/AccountsReceivable/Invoices/Show', ['id' => $id]);
    })->name('finance.accounts-receivable.invoices.show');
    Route::get('finance/accounts-receivable/invoices/{id}/edit', function ($id) {
        return Inertia::render('Finance/AccountsReceivable/Invoices/Edit', ['id' => $id]);
    })->name('finance.accounts-receivable.invoices.edit');

    // Finance - Accounts Receivable - Payments
    Route::get('finance/accounts-receivable/payments', function () {
        return Inertia::render('Finance/AccountsReceivable/Payments/Index');
    })->name('finance.accounts-receivable.payments.index');
    Route::get('finance/accounts-receivable/payments/create', function () {
        return Inertia::render('Finance/AccountsReceivable/Payments/Create');
    })->name('finance.accounts-receivable.payments.create');
    Route::get('finance/accounts-receivable/payments/{id}', function ($id) {
        return Inertia::render('Finance/AccountsReceivable/Payments/Show', ['id' => $id]);
    })->name('finance.accounts-receivable.payments.show');
    Route::get('finance/accounts-receivable/payments/{id}/edit', function ($id) {
        return Inertia::render('Finance/AccountsReceivable/Payments/Edit', ['id' => $id]);
    })->name('finance.accounts-receivable.payments.edit');

    // Finance - Accounts Payable - Bills
    Route::get('finance/accounts-payable/bills', function () {
        return Inertia::render('Finance/AccountsPayable/Bills/Index');
    })->name('finance.accounts-payable.bills.index');
    Route::get('finance/accounts-payable/bills/create', function () {
        return Inertia::render('Finance/AccountsPayable/Bills/Create');
    })->name('finance.accounts-payable.bills.create');
    Route::get('finance/accounts-payable/bills/{id}', function ($id) {
        return Inertia::render('Finance/AccountsPayable/Bills/Show', ['id' => $id]);
    })->name('finance.accounts-payable.bills.show');
    Route::get('finance/accounts-payable/bills/{id}/edit', function ($id) {
        return Inertia::render('Finance/AccountsPayable/Bills/Edit', ['id' => $id]);
    })->name('finance.accounts-payable.bills.edit');

    // Finance - Reports
    Route::get('finance/reports/balance-sheet', function () {
        return Inertia::render('Finance/Reports/BalanceSheet');
    })->name('finance.reports.balance-sheet');
    Route::get('finance/reports/income-statement', function () {
        return Inertia::render('Finance/Reports/IncomeStatement');
    })->name('finance.reports.income-statement');

    // Finance - Tax Management
    Route::prefix('finance/tax-management')->name('finance.tax-management.')->group(function () {
        // Tax Rates
        Route::get('tax-rates', function () {
            return Inertia::render('Finance/TaxManagement/TaxRates/Index');
        })->name('tax-rates.index');

        Route::get('tax-rates/create', function () {
            return Inertia::render('Finance/TaxManagement/TaxRates/Create');
        })->name('tax-rates.create');

        Route::get('tax-rates/{id}', function ($id) {
            return Inertia::render('Finance/TaxManagement/TaxRates/Show', ['id' => $id]);
        })->name('tax-rates.show');

        Route::get('tax-rates/{id}/edit', function ($id) {
            return Inertia::render('Finance/TaxManagement/TaxRates/Edit', ['id' => $id]);
        })->name('tax-rates.edit');

        // Tax Transactions
        Route::get('tax-transactions', function () {
            return Inertia::render('Finance/TaxManagement/TaxTransactions/Index');
        })->name('tax-transactions.index');

        Route::get('tax-transactions/create', function () {
            return Inertia::render('Finance/TaxManagement/TaxTransactions/Create');
        })->name('tax-transactions.create');

        Route::get('tax-transactions/{id}', function ($id) {
            return Inertia::render('Finance/TaxManagement/TaxTransactions/Show', ['id' => $id]);
        })->name('tax-transactions.show');

        Route::get('tax-transactions/{id}/edit', function ($id) {
            return Inertia::render('Finance/TaxManagement/TaxTransactions/Edit', ['id' => $id]);
        })->name('tax-transactions.edit');
    });

    // Manufacturing Module
    Route::prefix('manufacturing')->name('manufacturing.')->group(function () {
        // Bill of Materials
        Route::get('bill-of-materials', function () {
            return Inertia::render('Manufacturing/BillOfMaterials/Index');
        })->name('bill-of-materials.index');

        Route::get('bill-of-materials/create', function () {
            return Inertia::render('Manufacturing/BillOfMaterials/Create');
        })->name('bill-of-materials.create');

        Route::get('bill-of-materials/{id}', function ($id) {
            return Inertia::render('Manufacturing/BillOfMaterials/Show', ['id' => $id]);
        })->name('bill-of-materials.show');

        Route::get('bill-of-materials/{id}/edit', function ($id) {
            return Inertia::render('Manufacturing/BillOfMaterials/Edit', ['id' => $id]);
        })->name('bill-of-materials.edit');

        // Production Plans
        Route::get('production-plans', function () {
            return Inertia::render('Manufacturing/ProductionPlans/Index');
        })->name('production-plans.index');

        Route::get('production-plans/create', function () {
            return Inertia::render('Manufacturing/ProductionPlans/Create');
        })->name('production-plans.create');

        Route::get('production-plans/{id}', function ($id) {
            return Inertia::render('Manufacturing/ProductionPlans/Show', ['id' => $id]);
        })->name('production-plans.show');

        Route::get('production-plans/{id}/edit', function ($id) {
            return Inertia::render('Manufacturing/ProductionPlans/Edit', ['id' => $id]);
        })->name('production-plans.edit');

        // Work Orders
        Route::get('work-orders', function () {
            return Inertia::render('Manufacturing/WorkOrders/Index');
        })->name('work-orders.index');

        Route::get('work-orders/create', function () {
            return Inertia::render('Manufacturing/WorkOrders/Create');
        })->name('work-orders.create');

        Route::get('work-orders/{id}', function ($id) {
            return Inertia::render('Manufacturing/WorkOrders/Show', ['id' => $id]);
        })->name('work-orders.show');

        Route::get('work-orders/{id}/edit', function ($id) {
            return Inertia::render('Manufacturing/WorkOrders/Edit', ['id' => $id]);
        })->name('work-orders.edit');
    });

    // Project Management Module
    Route::prefix('projects')->name('projects.')->group(function () {
        // Projects
        Route::get('/', function () {
            return Inertia::render('Projects/Index');
        })->name('index');

        Route::get('/create', function () {
            return Inertia::render('Projects/Create');
        })->name('create');

        // Tasks
        Route::get('/tasks', function () {
            return Inertia::render('Projects/Tasks/Index');
        })->name('tasks.index');

        Route::get('/tasks/create', function () {
            return Inertia::render('Projects/Tasks/Create');
        })->name('tasks.create');

        Route::get('/tasks/{id}', function ($id) {
            return Inertia::render('Projects/Tasks/Show', ['id' => $id]);
        })->name('tasks.show');

        Route::get('/tasks/{id}/edit', function ($id) {
            return Inertia::render('Projects/Tasks/Edit', ['id' => $id]);
        })->name('tasks.edit');

        // Resources
        Route::get('/resources', function () {
            return Inertia::render('Projects/Resources/Index');
        })->name('resources.index');

        Route::get('/resources/create', function () {
            return Inertia::render('Projects/Resources/Create');
        })->name('resources.create');

        Route::get('/resources/{id}', function ($id) {
            return Inertia::render('Projects/Resources/Show', ['id' => $id]);
        })->name('resources.show');

        Route::get('/resources/{id}/edit', function ($id) {
            return Inertia::render('Projects/Resources/Edit', ['id' => $id]);
        })->name('resources.edit');

        // Costs
        Route::get('/costs', function () {
            return Inertia::render('Projects/Costs/Index');
        })->name('costs.index');

        Route::get('/costs/create', function () {
            return Inertia::render('Projects/Costs/Create');
        })->name('costs.create');

        Route::get('/costs/{id}', function ($id) {
            return Inertia::render('Projects/Costs/Show', ['id' => $id]);
        })->name('costs.show');

        Route::get('/costs/{id}/edit', function ($id) {
            return Inertia::render('Projects/Costs/Edit', ['id' => $id]);
        })->name('costs.edit');

        // Milestones
        Route::get('/milestones', function () {
            return Inertia::render('Projects/Milestones/Index');
        })->name('milestones.index');

        Route::get('/milestones/create', function () {
            return Inertia::render('Projects/Milestones/Create');
        })->name('milestones.create');

        Route::get('/milestones/{id}', function ($id) {
            return Inertia::render('Projects/Milestones/Show', ['id' => $id]);
        })->name('milestones.show');

        Route::get('/milestones/{id}/edit', function ($id) {
            return Inertia::render('Projects/Milestones/Edit', ['id' => $id]);
        })->name('milestones.edit');

        // Teams
        Route::get('/teams', function () {
            return Inertia::render('Projects/Teams/Index');
        })->name('teams.index');

        Route::get('/teams/create', function () {
            return Inertia::render('Projects/Teams/Create');
        })->name('teams.create');

        Route::get('/teams/{id}', function ($id) {
            return Inertia::render('Projects/Teams/Show', ['id' => $id]);
        })->name('teams.show');

        Route::get('/teams/{id}/edit', function ($id) {
            return Inertia::render('Projects/Teams/Edit', ['id' => $id]);
        })->name('teams.edit');

        // Project-specific routes (must come after all sub-module routes)
        Route::get('/{id}', function ($id) {
            return Inertia::render('Projects/Show', ['id' => $id]);
        })->name('show');

        Route::get('/{id}/edit', function ($id) {
            return Inertia::render('Projects/Edit', ['id' => $id]);
        })->name('edit');
    });

    // Finance - Bank Reconciliation
    Route::prefix('finance/bank-reconciliation')->name('finance.bank-reconciliation.')->group(function () {
        // Bank Accounts
        Route::get('bank-accounts', function () {
            return Inertia::render('Finance/BankReconciliation/BankAccounts/Index');
        })->name('bank-accounts.index');

        Route::get('bank-accounts/create', function () {
            return Inertia::render('Finance/BankReconciliation/BankAccounts/Create');
        })->name('bank-accounts.create');

        Route::get('bank-accounts/{id}', function ($id) {
            return Inertia::render('Finance/BankReconciliation/BankAccounts/Show', ['id' => $id]);
        })->name('bank-accounts.show');

        Route::get('bank-accounts/{id}/edit', function ($id) {
            return Inertia::render('Finance/BankReconciliation/BankAccounts/Edit', ['id' => $id]);
        })->name('bank-accounts.edit');

        // Bank Transactions
        Route::get('bank-transactions', function () {
            return Inertia::render('Finance/BankReconciliation/BankTransactions/Index');
        })->name('bank-transactions.index');

        Route::get('bank-transactions/create', function () {
            return Inertia::render('Finance/BankReconciliation/BankTransactions/Create');
        })->name('bank-transactions.create');

        Route::get('bank-transactions/{id}', function ($id) {
            return Inertia::render('Finance/BankReconciliation/BankTransactions/Show', ['id' => $id]);
        })->name('bank-transactions.show');

        Route::get('bank-transactions/{id}/edit', function ($id) {
            return Inertia::render('Finance/BankReconciliation/BankTransactions/Edit', ['id' => $id]);
        })->name('bank-transactions.edit');
    });

    // HR Module
    Route::prefix('hr')->name('hr.')->group(function () {
        // Leave Types
        Route::get('leave-types', function () {
            return Inertia::render('HR/LeaveTypes/Index');
        })->name('leave-types.index');

        Route::get('leave-types/create', function () {
            return Inertia::render('HR/LeaveTypes/Create');
        })->name('leave-types.create');

        Route::get('leave-types/{id}', function ($id) {
            return Inertia::render('HR/LeaveTypes/Show', ['id' => $id]);
        })->name('leave-types.show');

        Route::get('leave-types/{id}/edit', function ($id) {
            return Inertia::render('HR/LeaveTypes/Edit', ['id' => $id]);
        })->name('leave-types.edit');

        // Leave Requests
        Route::get('leave-requests', function () {
            return Inertia::render('HR/LeaveRequests/Index');
        })->name('leave-requests.index');

        Route::get('leave-requests/create', function () {
            return Inertia::render('HR/LeaveRequests/Create');
        })->name('leave-requests.create');

        Route::get('leave-requests/{id}', function ($id) {
            return Inertia::render('HR/LeaveRequests/Show', ['id' => $id]);
        })->name('leave-requests.show');

        Route::get('leave-requests/{id}/edit', function ($id) {
            return Inertia::render('HR/LeaveRequests/Edit', ['id' => $id]);
        })->name('leave-requests.edit');

        // Payroll Periods
        Route::get('payroll-periods', function () {
            return Inertia::render('HR/PayrollPeriods/Index');
        })->name('payroll-periods.index');

        Route::get('payroll-periods/create', function () {
            return Inertia::render('HR/PayrollPeriods/Create');
        })->name('payroll-periods.create');

        Route::get('payroll-periods/{id}', function ($id) {
            return Inertia::render('HR/PayrollPeriods/Show', ['id' => $id]);
        })->name('payroll-periods.show');

        Route::get('payroll-periods/{id}/edit', function ($id) {
            return Inertia::render('HR/PayrollPeriods/Edit', ['id' => $id]);
        })->name('payroll-periods.edit');

        // Employees
        Route::get('employees', function () {
            return Inertia::render('HR/Employees/Index');
        })->name('employees.index');

        Route::get('employees/create', function () {
            return Inertia::render('HR/Employees/Create');
        })->name('employees.create');

        Route::get('employees/{id}', function ($id) {
            return Inertia::render('HR/Employees/Show', ['id' => $id]);
        })->name('employees.show');

        Route::get('employees/{id}/edit', function ($id) {
            return Inertia::render('HR/Employees/Edit', ['id' => $id]);
        })->name('employees.edit');
    });

    // Reporting & Analytics Module
    Route::prefix('reports')->name('reports.')->group(function () {
        // Dashboard
        Route::get('/', function () {
            return Inertia::render('Reports/Index');
        })->name('index');

        // Financial Reports
        Route::get('/financial', function () {
            return Inertia::render('Reports/Financial/Index');
        })->name('financial.index');

        Route::get('/financial/create', function () {
            return Inertia::render('Reports/Financial/Create');
        })->name('financial.create');

        Route::get('/financial/{id}', function ($id) {
            return Inertia::render('Reports/Financial/Show', ['id' => $id]);
        })->name('financial.show');

        Route::get('/financial/{id}/edit', function ($id) {
            return Inertia::render('Reports/Financial/Edit', ['id' => $id]);
        })->name('financial.edit');

        // Business Analytics
        Route::get('/analytics', function () {
            return Inertia::render('Reports/Analytics/Index');
        })->name('analytics.index');

        Route::get('/analytics/create', function () {
            return Inertia::render('Reports/Analytics/Create');
        })->name('analytics.create');

        Route::get('/analytics/{id}', function ($id) {
            return Inertia::render('Reports/Analytics/Show', ['id' => $id]);
        })->name('analytics.show');

        Route::get('/analytics/{id}/edit', function ($id) {
            return Inertia::render('Reports/Analytics/Edit', ['id' => $id]);
        })->name('analytics.edit');

        // Report Schedules
        Route::get('/schedules', function () {
            return Inertia::render('Reports/Schedules/Index');
        })->name('schedules.index');

        Route::get('/schedules/create', function () {
            return Inertia::render('Reports/Schedules/Create');
        })->name('schedules.create');

        Route::get('/schedules/{id}', function ($id) {
            return Inertia::render('Reports/Schedules/Show', ['id' => $id]);
        })->name('schedules.show');

        Route::get('/schedules/{id}/edit', function ($id) {
            return Inertia::render('Reports/Schedules/Edit', ['id' => $id]);
        })->name('schedules.edit');
    });

    // Customer Service Module
    Route::prefix('customer-service')->name('customer-service.')->group(function () {
        // Dashboard
        Route::get('/', function () {
            return Inertia::render('CustomerService/Index');
        })->name('index');

        // Customer Complaints
        Route::get('/complaints', function () {
            return Inertia::render('CustomerService/Complaints/Index');
        })->name('complaints.index');

        Route::get('/complaints/create', function () {
            return Inertia::render('CustomerService/Complaints/Create');
        })->name('complaints.create');

        Route::get('/complaints/{id}', function ($id) {
            return Inertia::render('CustomerService/Complaints/Show', ['id' => $id]);
        })->name('complaints.show');

        Route::get('/complaints/{id}/edit', function ($id) {
            return Inertia::render('CustomerService/Complaints/Edit', ['id' => $id]);
        })->name('complaints.edit');

        // After-Sales Services
        Route::get('/after-sales-services', function () {
            return Inertia::render('CustomerService/AfterSalesServices/Index');
        })->name('after-sales-services.index');

        Route::get('/after-sales-services/create', function () {
            return Inertia::render('CustomerService/AfterSalesServices/Create');
        })->name('after-sales-services.create');

        Route::get('/after-sales-services/{id}', function ($id) {
            return Inertia::render('CustomerService/AfterSalesServices/Show', ['id' => $id]);
        })->name('after-sales-services.show');

        Route::get('/after-sales-services/{id}/edit', function ($id) {
            return Inertia::render('CustomerService/AfterSalesServices/Edit', ['id' => $id]);
        })->name('after-sales-services.edit');

        // Service Tickets
        Route::get('/service-tickets', function () {
            return Inertia::render('CustomerService/ServiceTickets/Index');
        })->name('service-tickets.index');

        Route::get('/service-tickets/create', function () {
            return Inertia::render('CustomerService/ServiceTickets/Create');
        })->name('service-tickets.create');

        Route::get('/service-tickets/{id}', function ($id) {
            return Inertia::render('CustomerService/ServiceTickets/Show', ['id' => $id]);
        })->name('service-tickets.show');

        Route::get('/service-tickets/{id}/edit', function ($id) {
            return Inertia::render('CustomerService/ServiceTickets/Edit', ['id' => $id]);
        })->name('service-tickets.edit');

        // Service Categories
        Route::get('/service-categories', function () {
            return Inertia::render('CustomerService/ServiceCategories/Index');
        })->name('service-categories.index');

        Route::get('/service-categories/create', function () {
            return Inertia::render('CustomerService/ServiceCategories/Create');
        })->name('service-categories.create');

        Route::get('/service-categories/{id}', function ($id) {
            return Inertia::render('CustomerService/ServiceCategories/Show', ['id' => $id]);
        })->name('service-categories.show');

        Route::get('/service-categories/{id}/edit', function ($id) {
            return Inertia::render('CustomerService/ServiceCategories/Edit', ['id' => $id]);
        })->name('service-categories.edit');
    });

    // Settings - RBAC Module
    Route::prefix('settings/rbac')->name('settings.rbac.')->group(function () {
        // Dashboard
        Route::get('/', function () {
            return Inertia::render('settings/Rbac/Index');
        })->name('index');

        // Roles
        Route::get('/roles', function () {
            return Inertia::render('settings/Rbac/Roles/Index');
        })->name('roles.index');

        Route::get('/roles/create', function () {
            return Inertia::render('settings/Rbac/Roles/Create');
        })->name('roles.create');

        Route::get('/roles/{id}', function ($id) {
            return Inertia::render('settings/Rbac/Roles/Show', ['id' => $id]);
        })->name('roles.show');

        Route::get('/roles/{id}/edit', function ($id) {
            return Inertia::render('settings/Rbac/Roles/Edit', ['id' => $id]);
        })->name('roles.edit');

        // Permissions
        Route::get('/permissions', function () {
            return Inertia::render('settings/Rbac/Permissions/Index');
        })->name('permissions.index');

        Route::get('/permissions/create', function () {
            return Inertia::render('settings/Rbac/Permissions/Create');
        })->name('permissions.create');

        Route::get('/permissions/{id}', function ($id) {
            return Inertia::render('settings/Rbac/Permissions/Show', ['id' => $id]);
        })->name('permissions.show');

        Route::get('/permissions/{id}/edit', function ($id) {
            return Inertia::render('settings/Rbac/Permissions/Edit', ['id' => $id]);
        })->name('permissions.edit');

        // Users
        Route::get('/users', function () {
            return Inertia::render('settings/Rbac/Users/Index');
        })->name('users.index');

        Route::get('/users/create', function () {
            return Inertia::render('settings/Rbac/Users/Create');
        })->name('users.create');

        Route::get('/users/{id}', function ($id) {
            return Inertia::render('settings/Rbac/Users/Show', ['id' => $id]);
        })->name('users.show');

        Route::get('/users/{id}/edit', function ($id) {
            return Inertia::render('settings/Rbac/Users/Edit', ['id' => $id]);
        })->name('users.edit');
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
