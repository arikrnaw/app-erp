<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\SalesOrderController;
use App\Http\Controllers\Api\PurchaseOrderController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ChartOfAccountController;
use App\Http\Controllers\Api\JournalEntryController;
use App\Http\Controllers\Api\GeneralLedgerController;
use App\Http\Controllers\Api\TrialBalanceController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\BillController;
use App\Http\Controllers\Api\FinancialReportController;
use App\Http\Controllers\Api\TaxRateController;
use App\Http\Controllers\Api\TaxTransactionController;
use App\Http\Controllers\Api\BankAccountController;
use App\Http\Controllers\Api\BankTransactionController;
use App\Http\Controllers\Api\InventoryTransactionController;
use App\Http\Controllers\Api\WarehouseController;
use App\Http\Controllers\Api\WarehouseLocationController;
use App\Http\Controllers\Api\ProductLotController;
use App\Http\Controllers\Api\ReorderAlertController;
use App\Http\Controllers\Api\PurchaseRequestController;
use App\Http\Controllers\Api\GoodsReceiptController;
use App\Http\Controllers\Api\PurchaseReturnController;
use App\Http\Controllers\Api\BillOfMaterialController;
use App\Http\Controllers\Api\ProductionPlanController;
use App\Http\Controllers\Api\WorkOrderController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\UserManagementController;
use App\Http\Controllers\Api\AiChatController;
use App\Http\Controllers\Settings\ProfileController;
use Laravel\WorkOS\Http\Middleware\ValidateSessionWithWorkOS;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
// Protected routes
Route::middleware([
    'web', // Add web middleware to handle sessions
    'auth',
    ValidateSessionWithWorkOS::class,
])->group(function () {

    // User
    Route::get('/user', function () {
        return response()->json(Auth::user());
    });

    // Users
    Route::get('/users', function () {
        return response()->json([
            'data' => \App\Models\User::all()
        ]);
    });

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // profile edit
    Route::put('/profile', [ProfileController::class, 'update']);

    // Categories
    Route::apiResource('categories', CategoryController::class);

    // Products
    Route::apiResource('products', ProductController::class);

    // Customers
    Route::apiResource('customers', CustomerController::class);

    // CRM Module
    Route::prefix('crm')->group(function () {
        // Prospects
        Route::get('/prospects', [\App\Http\Controllers\Api\ProspectController::class, 'index']);
        Route::post('/prospects', [\App\Http\Controllers\Api\ProspectController::class, 'store']);
        Route::get('/prospects/statistics', [\App\Http\Controllers\Api\ProspectController::class, 'statistics']);
        Route::get('/prospects/assigned-users', [\App\Http\Controllers\Api\ProspectController::class, 'getAssignedUsers']);
        Route::get('/prospects/sources', [\App\Http\Controllers\Api\ProspectController::class, 'getSources']);
        Route::get('/prospects/industries', [\App\Http\Controllers\Api\ProspectController::class, 'getIndustries']);
        Route::get('/prospects/{prospect}', [\App\Http\Controllers\Api\ProspectController::class, 'show']);
        Route::put('/prospects/{prospect}', [\App\Http\Controllers\Api\ProspectController::class, 'update']);
        Route::delete('/prospects/{prospect}', [\App\Http\Controllers\Api\ProspectController::class, 'destroy']);

        // Follow-ups
        Route::get('/follow-ups', [\App\Http\Controllers\Api\FollowUpController::class, 'index']);
        Route::post('/follow-ups', [\App\Http\Controllers\Api\FollowUpController::class, 'store']);
        Route::get('/follow-ups/statistics', [\App\Http\Controllers\Api\FollowUpController::class, 'statistics']);
        Route::get('/follow-ups/upcoming', [\App\Http\Controllers\Api\FollowUpController::class, 'getUpcoming']);
        Route::get('/follow-ups/overdue', [\App\Http\Controllers\Api\FollowUpController::class, 'getOverdue']);
        Route::get('/follow-ups/assigned-users', [\App\Http\Controllers\Api\FollowUpController::class, 'getAssignedUsers']);
        Route::get('/follow-ups/{followUp}', [\App\Http\Controllers\Api\FollowUpController::class, 'show']);
        Route::put('/follow-ups/{followUp}', [\App\Http\Controllers\Api\FollowUpController::class, 'update']);
        Route::delete('/follow-ups/{followUp}', [\App\Http\Controllers\Api\FollowUpController::class, 'destroy']);
        Route::post('/follow-ups/{followUp}/complete', [\App\Http\Controllers\Api\FollowUpController::class, 'complete']);

        // Support Tickets
        Route::get('/support-tickets', [\App\Http\Controllers\Api\SupportTicketController::class, 'index']);
        Route::post('/support-tickets', [\App\Http\Controllers\Api\SupportTicketController::class, 'store']);
        Route::get('/support-tickets/statistics', [\App\Http\Controllers\Api\SupportTicketController::class, 'statistics']);
        Route::get('/support-tickets/open', [\App\Http\Controllers\Api\SupportTicketController::class, 'getOpenTickets']);
        Route::get('/support-tickets/urgent', [\App\Http\Controllers\Api\SupportTicketController::class, 'getUrgentTickets']);
        Route::get('/support-tickets/assigned-users', [\App\Http\Controllers\Api\SupportTicketController::class, 'getAssignedUsers']);
        Route::get('/support-tickets/categories', [\App\Http\Controllers\Api\SupportTicketController::class, 'getCategories']);
        Route::get('/support-tickets/{supportTicket}', [\App\Http\Controllers\Api\SupportTicketController::class, 'show']);
        Route::put('/support-tickets/{supportTicket}', [\App\Http\Controllers\Api\SupportTicketController::class, 'update']);
        Route::delete('/support-tickets/{supportTicket}', [\App\Http\Controllers\Api\SupportTicketController::class, 'destroy']);
        Route::post('/support-tickets/{supportTicket}/resolve', [\App\Http\Controllers\Api\SupportTicketController::class, 'resolve']);
        Route::post('/support-tickets/{supportTicket}/close', [\App\Http\Controllers\Api\SupportTicketController::class, 'close']);

        // Customer Segments
        Route::get('/customer-segments', [\App\Http\Controllers\Api\CustomerSegmentController::class, 'index']);
        Route::post('/customer-segments', [\App\Http\Controllers\Api\CustomerSegmentController::class, 'store']);
        Route::get('/customer-segments/active', [\App\Http\Controllers\Api\CustomerSegmentController::class, 'getActive']);
        Route::get('/customer-segments/{customerSegment}', [\App\Http\Controllers\Api\CustomerSegmentController::class, 'show']);
        Route::put('/customer-segments/{customerSegment}', [\App\Http\Controllers\Api\CustomerSegmentController::class, 'update']);
        Route::delete('/customer-segments/{customerSegment}', [\App\Http\Controllers\Api\CustomerSegmentController::class, 'destroy']);
        Route::post('/customer-segments/{customerSegment}/toggle-status', [\App\Http\Controllers\Api\CustomerSegmentController::class, 'toggleStatus']);
        Route::get('/customer-segments/{customerSegment}/customers', [\App\Http\Controllers\Api\CustomerSegmentController::class, 'getCustomers']);
    });

    // Suppliers
    Route::apiResource('suppliers', SupplierController::class);

    // Sales Orders
    Route::apiResource('sales-orders', SalesOrderController::class);

    // Purchase Orders
    Route::apiResource('purchase-orders', PurchaseOrderController::class);

    // Purchase Requests
    Route::apiResource('purchase-requests', PurchaseRequestController::class);
    Route::post('purchase-requests/generate-number', [PurchaseRequestController::class, 'generateNumber']);

    // Goods Receipts
    Route::apiResource('goods-receipts', GoodsReceiptController::class);
    Route::post('goods-receipts/generate-number', [GoodsReceiptController::class, 'generateNumber']);

    // Purchase Returns
    Route::apiResource('purchase-returns', PurchaseReturnController::class);
    Route::post('purchase-returns/generate-number', [PurchaseReturnController::class, 'generateNumber']);

    // Inventory Transactions (Direct route)
    Route::apiResource('inventory-transactions', InventoryTransactionController::class);

    // Manufacturing Module
    Route::prefix('manufacturing')->group(function () {
        // Bill of Materials
        Route::apiResource('bill-of-materials', BillOfMaterialController::class);
        Route::post('bill-of-materials/generate-number', [BillOfMaterialController::class, 'generateNumber']);
        Route::post('bill-of-materials/{billOfMaterial}/approve', [BillOfMaterialController::class, 'approve']);

        // Production Plans
        Route::apiResource('production-plans', ProductionPlanController::class);
        Route::post('production-plans/generate-number', [ProductionPlanController::class, 'generateNumber']);
        Route::post('production-plans/{productionPlan}/approve', [ProductionPlanController::class, 'approve']);

        // Work Orders
        Route::apiResource('work-orders', WorkOrderController::class);
        Route::post('work-orders/generate-number', [WorkOrderController::class, 'generateNumber']);
        Route::post('work-orders/{workOrder}/approve', [WorkOrderController::class, 'approve']);
        Route::post('work-orders/{workOrder}/start', [WorkOrderController::class, 'start']);
        Route::post('work-orders/{workOrder}/complete', [WorkOrderController::class, 'complete']);
    });

    // Work Centers
    Route::get('/work-centers', function () {
        return response()->json([
            'data' => \App\Models\WorkCenter::all()
        ]);
    });

    // Chart of Accounts
    Route::get('chart-of-accounts/export', [ChartOfAccountController::class, 'export']);
    Route::apiResource('chart-of-accounts', ChartOfAccountController::class);

    // Journal Entries
    Route::apiResource('journal-entries', JournalEntryController::class);
    Route::post('journal-entries/{journalEntry}/post', [JournalEntryController::class, 'post']);

    // General Ledger
    Route::get('finance/general-ledger', [GeneralLedgerController::class, 'index']);
    Route::get('finance/general-ledger/export', [GeneralLedgerController::class, 'export']);

    // Trial Balance
    Route::get('finance/trial-balance', [TrialBalanceController::class, 'index']);
    Route::get('finance/trial-balance/export', [TrialBalanceController::class, 'export']);

    // Invoices (Accounts Receivable)
    Route::apiResource('finance/accounts-receivable/invoices', InvoiceController::class);
    
    // Payments (Accounts Receivable)
    Route::apiResource('finance/accounts-receivable/payments', PaymentController::class);
    
    // Bills (Accounts Payable)
    Route::apiResource('finance/accounts-payable/bills', BillController::class);
    Route::get('finance/accounts-payable/bills/suppliers', [BillController::class, 'getSuppliers']);
    Route::get('finance/accounts-payable/bills/products', [BillController::class, 'getProducts']);
    
    // Financial Reports
    Route::get('finance/reports/balance-sheet', [FinancialReportController::class, 'balanceSheet']);
    Route::get('finance/reports/income-statement', [FinancialReportController::class, 'incomeStatement']);
    
    // Tax Management
    Route::prefix('tax')->group(function () {
        Route::get('/rates', [TaxRateController::class, 'index']);
        Route::post('/rates', [TaxRateController::class, 'store']);
        Route::get('/rates/active', [TaxRateController::class, 'getActive']);
        Route::get('/rates/{taxRate}', [TaxRateController::class, 'show']);
        Route::put('/rates/{taxRate}', [TaxRateController::class, 'update']);
        Route::delete('/rates/{taxRate}', [TaxRateController::class, 'destroy']);
        
        Route::get('/transactions', [TaxTransactionController::class, 'index']);
        Route::post('/transactions', [TaxTransactionController::class, 'store']);
        Route::get('/transactions/summary', [TaxTransactionController::class, 'getSummary']);
        Route::get('/transactions/tax-rates', [TaxTransactionController::class, 'getTaxRates']);
        Route::get('/transactions/{taxTransaction}', [TaxTransactionController::class, 'show']);
        Route::put('/transactions/{taxTransaction}', [TaxTransactionController::class, 'update']);
        Route::delete('/transactions/{taxTransaction}', [TaxTransactionController::class, 'destroy']);
    });

    // Bank Reconciliation
    Route::prefix('bank')->group(function () {
        Route::get('/accounts', [BankAccountController::class, 'index']);
        Route::post('/accounts', [BankAccountController::class, 'store']);
        Route::get('/accounts/active', [BankAccountController::class, 'getActive']);
        Route::get('/accounts/{bankAccount}/balance', [BankAccountController::class, 'getBalance']);
        Route::get('/accounts/{bankAccount}', [BankAccountController::class, 'show']);
        Route::put('/accounts/{bankAccount}', [BankAccountController::class, 'update']);
        Route::delete('/accounts/{bankAccount}', [BankAccountController::class, 'destroy']);
        
        Route::get('/transactions', [BankTransactionController::class, 'index']);
        Route::post('/transactions', [BankTransactionController::class, 'store']);
        Route::get('/transactions/unreconciled', [BankTransactionController::class, 'getUnreconciled']);
        Route::get('/transactions/bank-accounts', [BankTransactionController::class, 'getBankAccounts']);
        Route::get('/transactions/{bankTransaction}', [BankTransactionController::class, 'show']);
        Route::put('/transactions/{bankTransaction}', [BankTransactionController::class, 'update']);
        Route::delete('/transactions/{bankTransaction}', [BankTransactionController::class, 'destroy']);
    });

    // Inventory Management
    Route::prefix('inventory')->group(function () {
        // Inventory Transactions
        Route::get('/transactions', [InventoryTransactionController::class, 'index']);
        Route::post('/transactions', [InventoryTransactionController::class, 'store']);
        Route::get('/transactions/summary', [InventoryTransactionController::class, 'getSummary']);
        Route::get('/transactions/{inventoryTransaction}', [InventoryTransactionController::class, 'show']);
        Route::put('/transactions/{inventoryTransaction}', [InventoryTransactionController::class, 'update']);
        Route::delete('/transactions/{inventoryTransaction}', [InventoryTransactionController::class, 'destroy']);

        // Warehouses
        Route::get('/warehouses', [WarehouseController::class, 'index']);
        Route::post('/warehouses', [WarehouseController::class, 'store']);
        Route::get('/warehouses/active', [WarehouseController::class, 'getActive']);
        Route::get('/warehouses/{warehouse}', [WarehouseController::class, 'show']);
        Route::put('/warehouses/{warehouse}', [WarehouseController::class, 'update']);
        Route::delete('/warehouses/{warehouse}', [WarehouseController::class, 'destroy']);

        // Warehouse Locations
        Route::get('/warehouse-locations', [WarehouseLocationController::class, 'index']);
        Route::post('/warehouse-locations', [WarehouseLocationController::class, 'store']);
        Route::get('/warehouse-locations/warehouse/{warehouse}', [WarehouseLocationController::class, 'getByWarehouse']);
        Route::get('/warehouse-locations/{warehouseLocation}', [WarehouseLocationController::class, 'show']);
        Route::put('/warehouse-locations/{warehouseLocation}', [WarehouseLocationController::class, 'update']);
        Route::delete('/warehouse-locations/{warehouseLocation}', [WarehouseLocationController::class, 'destroy']);

        // Product Lots
        Route::get('/product-lots', [ProductLotController::class, 'index']);
        Route::post('/product-lots', [ProductLotController::class, 'store']);
        Route::get('/product-lots/product/{product}', [ProductLotController::class, 'getByProduct']);
        Route::get('/product-lots/expiring-soon', [ProductLotController::class, 'getExpiringSoon']);
        Route::get('/product-lots/{productLot}', [ProductLotController::class, 'show']);
        Route::put('/product-lots/{productLot}', [ProductLotController::class, 'update']);
        Route::delete('/product-lots/{productLot}', [ProductLotController::class, 'destroy']);

        // Reorder Alerts
        Route::get('/reorder-alerts', [ReorderAlertController::class, 'index']);
        Route::post('/reorder-alerts', [ReorderAlertController::class, 'store']);
        Route::get('/reorder-alerts/pending', [ReorderAlertController::class, 'getPending']);
        Route::get('/reorder-alerts/summary', [ReorderAlertController::class, 'getSummary']);
        Route::post('/reorder-alerts/generate', [ReorderAlertController::class, 'generateAlerts']);
        Route::post('/reorder-alerts/{reorderAlert}/process', [ReorderAlertController::class, 'process']);
        Route::get('/reorder-alerts/{reorderAlert}', [ReorderAlertController::class, 'show']);
        Route::put('/reorder-alerts/{reorderAlert}', [ReorderAlertController::class, 'update']);
        Route::delete('/reorder-alerts/{reorderAlert}', [ReorderAlertController::class, 'destroy']);
    });

    // Project Management
    Route::prefix('projects')->group(function () {
        // Projects
        Route::get('/', [\App\Http\Controllers\Api\ProjectController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Api\ProjectController::class, 'store']);
        Route::get('/statistics', [\App\Http\Controllers\Api\ProjectController::class, 'statistics']);
        Route::get('/project-managers', [\App\Http\Controllers\Api\ProjectController::class, 'getProjectManagers']);
        Route::get('/clients', [\App\Http\Controllers\Api\ProjectController::class, 'getClients']);
        Route::post('/generate-code', [\App\Http\Controllers\Api\ProjectController::class, 'generateCode']);
        Route::get('/{project}', [\App\Http\Controllers\Api\ProjectController::class, 'show']);
        Route::put('/{project}', [\App\Http\Controllers\Api\ProjectController::class, 'update']);
        Route::delete('/{project}', [\App\Http\Controllers\Api\ProjectController::class, 'destroy']);

        // Project Tasks
        Route::get('/tasks', [\App\Http\Controllers\Api\ProjectTaskController::class, 'index']);
        Route::post('/tasks', [\App\Http\Controllers\Api\ProjectTaskController::class, 'store']);
        Route::get('/tasks/statistics', [\App\Http\Controllers\Api\ProjectTaskController::class, 'statistics']);
        Route::get('/tasks/assigned-users', [\App\Http\Controllers\Api\ProjectTaskController::class, 'getAssignedUsers']);
        Route::get('/tasks/{task}', [\App\Http\Controllers\Api\ProjectTaskController::class, 'show']);
        Route::put('/tasks/{task}', [\App\Http\Controllers\Api\ProjectTaskController::class, 'update']);
        Route::delete('/tasks/{task}', [\App\Http\Controllers\Api\ProjectTaskController::class, 'destroy']);
        Route::post('/tasks/{task}/status', [\App\Http\Controllers\Api\ProjectTaskController::class, 'updateStatus']);

        // Project Resources
        Route::get('/resources', [\App\Http\Controllers\Api\ProjectResourceController::class, 'index']);
        Route::post('/resources', [\App\Http\Controllers\Api\ProjectResourceController::class, 'store']);
        Route::get('/resources/statistics', [\App\Http\Controllers\Api\ProjectResourceController::class, 'statistics']);
        Route::get('/resources/{resource}', [\App\Http\Controllers\Api\ProjectResourceController::class, 'show']);
        Route::put('/resources/{resource}', [\App\Http\Controllers\Api\ProjectResourceController::class, 'update']);
        Route::delete('/resources/{resource}', [\App\Http\Controllers\Api\ProjectResourceController::class, 'destroy']);

        // Project Costs
        Route::get('/costs', [\App\Http\Controllers\Api\ProjectCostController::class, 'index']);
        Route::post('/costs', [\App\Http\Controllers\Api\ProjectCostController::class, 'store']);
        Route::get('/costs/statistics', [\App\Http\Controllers\Api\ProjectCostController::class, 'statistics']);
        Route::get('/costs/{cost}', [\App\Http\Controllers\Api\ProjectCostController::class, 'show']);
        Route::put('/costs/{cost}', [\App\Http\Controllers\Api\ProjectCostController::class, 'update']);
        Route::delete('/costs/{cost}', [\App\Http\Controllers\Api\ProjectCostController::class, 'destroy']);

        // Project Milestones
        Route::get('/milestones', [\App\Http\Controllers\Api\ProjectMilestoneController::class, 'index']);
        Route::post('/milestones', [\App\Http\Controllers\Api\ProjectMilestoneController::class, 'store']);
        Route::get('/milestones/statistics', [\App\Http\Controllers\Api\ProjectMilestoneController::class, 'statistics']);
        Route::get('/milestones/{milestone}', [\App\Http\Controllers\Api\ProjectMilestoneController::class, 'show']);
        Route::put('/milestones/{milestone}', [\App\Http\Controllers\Api\ProjectMilestoneController::class, 'update']);
        Route::delete('/milestones/{milestone}', [\App\Http\Controllers\Api\ProjectMilestoneController::class, 'destroy']);

        // Project Teams
        Route::get('/teams', [\App\Http\Controllers\Api\ProjectTeamController::class, 'index']);
        Route::post('/teams', [\App\Http\Controllers\Api\ProjectTeamController::class, 'store']);
        Route::get('/teams/statistics', [\App\Http\Controllers\Api\ProjectTeamController::class, 'statistics']);
        Route::get('/teams/{team}', [\App\Http\Controllers\Api\ProjectTeamController::class, 'show']);
        Route::put('/teams/{team}', [\App\Http\Controllers\Api\ProjectTeamController::class, 'update']);
        Route::delete('/teams/{team}', [\App\Http\Controllers\Api\ProjectTeamController::class, 'destroy']);
    });

    // HR Management
    Route::prefix('hr')->group(function () {
        // Leave Types
        Route::get('/leave-types', [\App\Http\Controllers\Api\LeaveTypeController::class, 'index']);
        Route::post('/leave-types', [\App\Http\Controllers\Api\LeaveTypeController::class, 'store']);
        Route::get('/leave-types/active', [\App\Http\Controllers\Api\LeaveTypeController::class, 'active']);
        Route::get('/leave-types/{leaveType}', [\App\Http\Controllers\Api\LeaveTypeController::class, 'show']);
        Route::put('/leave-types/{leaveType}', [\App\Http\Controllers\Api\LeaveTypeController::class, 'update']);
        Route::delete('/leave-types/{leaveType}', [\App\Http\Controllers\Api\LeaveTypeController::class, 'destroy']);
        Route::get('/leave-types/{leaveType}/statistics', [\App\Http\Controllers\Api\LeaveTypeController::class, 'statistics']);
        Route::post('/leave-types/{leaveType}/toggle-status', [\App\Http\Controllers\Api\LeaveTypeController::class, 'toggleStatus']);

        // Leave Requests
        Route::get('/leave-requests', [\App\Http\Controllers\Api\LeaveRequestController::class, 'index']);
        Route::post('/leave-requests', [\App\Http\Controllers\Api\LeaveRequestController::class, 'store']);
        Route::get('/leave-requests/pending', [\App\Http\Controllers\Api\LeaveRequestController::class, 'pending']);
        Route::get('/leave-requests/urgent', [\App\Http\Controllers\Api\LeaveRequestController::class, 'urgent']);
        Route::get('/leave-requests/{leaveRequest}', [\App\Http\Controllers\Api\LeaveRequestController::class, 'show']);
        Route::put('/leave-requests/{leaveRequest}', [\App\Http\Controllers\Api\LeaveRequestController::class, 'update']);
        Route::delete('/leave-requests/{leaveRequest}', [\App\Http\Controllers\Api\LeaveRequestController::class, 'destroy']);
        Route::post('/leave-requests/{leaveRequest}/approve', [\App\Http\Controllers\Api\LeaveRequestController::class, 'approve']);
        Route::post('/leave-requests/{leaveRequest}/reject', [\App\Http\Controllers\Api\LeaveRequestController::class, 'reject']);
        Route::post('/leave-requests/{leaveRequest}/cancel', [\App\Http\Controllers\Api\LeaveRequestController::class, 'cancel']);

        // Payroll Periods
        Route::get('/payroll-periods', [\App\Http\Controllers\Api\PayrollPeriodController::class, 'index']);
        Route::post('/payroll-periods', [\App\Http\Controllers\Api\PayrollPeriodController::class, 'store']);
        Route::get('/payroll-periods/current', [\App\Http\Controllers\Api\PayrollPeriodController::class, 'current']);
        Route::get('/payroll-periods/upcoming', [\App\Http\Controllers\Api\PayrollPeriodController::class, 'upcoming']);
        Route::get('/payroll-periods/overdue', [\App\Http\Controllers\Api\PayrollPeriodController::class, 'overdue']);
        Route::get('/payroll-periods/{payrollPeriod}', [\App\Http\Controllers\Api\PayrollPeriodController::class, 'show']);
        Route::put('/payroll-periods/{payrollPeriod}', [\App\Http\Controllers\Api\PayrollPeriodController::class, 'update']);
        Route::delete('/payroll-periods/{payrollPeriod}', [\App\Http\Controllers\Api\PayrollPeriodController::class, 'destroy']);
        Route::post('/payroll-periods/{payrollPeriod}/process', [\App\Http\Controllers\Api\PayrollPeriodController::class, 'process']);
        Route::post('/payroll-periods/{payrollPeriod}/approve', [\App\Http\Controllers\Api\PayrollPeriodController::class, 'approve']);
        Route::post('/payroll-periods/{payrollPeriod}/mark-as-paid', [\App\Http\Controllers\Api\PayrollPeriodController::class, 'markAsPaid']);
        Route::post('/payroll-periods/{payrollPeriod}/cancel', [\App\Http\Controllers\Api\PayrollPeriodController::class, 'cancel']);
        Route::post('/payroll-periods/{payrollPeriod}/calculate-totals', [\App\Http\Controllers\Api\PayrollPeriodController::class, 'calculateTotals']);

        // Employees
        Route::get('/employees', [\App\Http\Controllers\Api\EmployeeController::class, 'index']);
        Route::post('/employees', [\App\Http\Controllers\Api\EmployeeController::class, 'store']);
        Route::get('/employees/active', [\App\Http\Controllers\Api\EmployeeController::class, 'active']);
        Route::get('/employees/statistics', [\App\Http\Controllers\Api\EmployeeController::class, 'statistics']);
        Route::get('/employees/by-department/{departmentId}', [\App\Http\Controllers\Api\EmployeeController::class, 'byDepartment']);
        Route::get('/employees/by-position/{positionId}', [\App\Http\Controllers\Api\EmployeeController::class, 'byPosition']);
        Route::get('/employees/{employee}', [\App\Http\Controllers\Api\EmployeeController::class, 'show']);
        Route::put('/employees/{employee}', [\App\Http\Controllers\Api\EmployeeController::class, 'update']);
        Route::delete('/employees/{employee}', [\App\Http\Controllers\Api\EmployeeController::class, 'destroy']);
        Route::post('/employees/{employee}/toggle-status', [\App\Http\Controllers\Api\EmployeeController::class, 'toggleStatus']);
    });

    // Departments
    Route::get('/departments', [\App\Http\Controllers\Api\DepartmentController::class, 'index']);
    Route::post('/departments', [\App\Http\Controllers\Api\DepartmentController::class, 'store']);
    Route::get('/departments/active', [\App\Http\Controllers\Api\DepartmentController::class, 'active']);
    Route::get('/departments/{department}', [\App\Http\Controllers\Api\DepartmentController::class, 'show']);
    Route::put('/departments/{department}', [\App\Http\Controllers\Api\DepartmentController::class, 'update']);
    Route::delete('/departments/{department}', [\App\Http\Controllers\Api\DepartmentController::class, 'destroy']);

    // Positions
    Route::get('/positions', [\App\Http\Controllers\Api\PositionController::class, 'index']);
    Route::post('/positions', [\App\Http\Controllers\Api\PositionController::class, 'store']);
    Route::get('/positions/active', [\App\Http\Controllers\Api\PositionController::class, 'active']);
    Route::get('/positions/{position}', [\App\Http\Controllers\Api\PositionController::class, 'show']);
    Route::put('/positions/{position}', [\App\Http\Controllers\Api\PositionController::class, 'update']);
    Route::delete('/positions/{position}', [\App\Http\Controllers\Api\PositionController::class, 'destroy']);
});

// Reporting & Analytics Module
Route::prefix('reports')->group(function () {
    // Financial Reports
    Route::apiResource('financial-reports', \App\Http\Controllers\Api\FinancialReportController::class);
    Route::get('financial-reports/statistics', [\App\Http\Controllers\Api\FinancialReportController::class, 'statistics']);
    Route::post('financial-reports/generate', [\App\Http\Controllers\Api\FinancialReportController::class, 'generateReport']);
    
    // Business Analytics
    Route::apiResource('business-analytics', \App\Http\Controllers\Api\BusinessAnalyticsController::class);
    Route::get('business-analytics/statistics', [\App\Http\Controllers\Api\BusinessAnalyticsController::class, 'statistics']);
    Route::post('business-analytics/generate', [\App\Http\Controllers\Api\BusinessAnalyticsController::class, 'generateAnalysis']);
    
    // Report Schedules
    Route::apiResource('schedules', \App\Http\Controllers\Api\ReportScheduleController::class);
});

// Customer Service Module
Route::prefix('customer-service')->group(function () {
    // Customer Complaints
    Route::apiResource('complaints', \App\Http\Controllers\Api\CustomerComplaintController::class);
    Route::get('complaints/statistics', [\App\Http\Controllers\Api\CustomerComplaintController::class, 'statistics']);
    
    // After-Sales Services
    Route::apiResource('after-sales-services', \App\Http\Controllers\Api\AfterSalesServiceController::class);
    Route::get('after-sales-services/statistics', [\App\Http\Controllers\Api\AfterSalesServiceController::class, 'statistics']);
    
    // Service Tickets
    Route::apiResource('service-tickets', \App\Http\Controllers\Api\ServiceTicketController::class);
    Route::get('service-tickets/statistics', [\App\Http\Controllers\Api\ServiceTicketController::class, 'statistics']);
    
    // Service Categories
    Route::apiResource('service-categories', \App\Http\Controllers\Api\ServiceCategoryController::class);
    Route::get('service-categories/tree', [\App\Http\Controllers\Api\ServiceCategoryController::class, 'tree']);
});

// Authentication Routes
Route::prefix('auth')->group(function () {
    Route::post('/login', [\App\Http\Controllers\Auth\AuthController::class, 'login']);
    Route::post('/workos-login', [\App\Http\Controllers\Auth\AuthController::class, 'workosLogin']);
    Route::post('/register', [\App\Http\Controllers\Auth\AuthController::class, 'register']);
    Route::post('/logout', [\App\Http\Controllers\Auth\AuthController::class, 'logout']);
    Route::get('/me', [\App\Http\Controllers\Auth\AuthController::class, 'me']);
    Route::put('/change-password', [\App\Http\Controllers\Auth\AuthController::class, 'changePassword']);
    Route::put('/update-profile', [\App\Http\Controllers\Auth\AuthController::class, 'updateProfile']);
    Route::post('/disconnect-workos', [\App\Http\Controllers\Auth\AuthController::class, 'disconnectWorkOS']);
});

// RBAC (Role-Based Access Control) Module
Route::prefix('rbac')->group(function () {
    // Roles
    Route::apiResource('roles', RoleController::class);
    Route::get('roles/statistics', [RoleController::class, 'statistics']);
    
    // Permissions
    Route::apiResource('permissions', PermissionController::class);
    Route::get('permissions/modules', [PermissionController::class, 'modules']);
    Route::get('permissions/actions', [PermissionController::class, 'actions']);
    Route::get('permissions/statistics', [PermissionController::class, 'statistics']);
    
    // User Management
    Route::apiResource('users', UserManagementController::class);
    Route::post('users/{user}/toggle-status', [UserManagementController::class, 'toggleStatus']);
    Route::post('users/{user}/assign-roles', [UserManagementController::class, 'assignRoles']);
    Route::get('users/available-roles', [UserManagementController::class, 'availableRoles']);
    Route::get('users/statistics', [UserManagementController::class, 'statistics']);
});

// AI Chat Module
Route::prefix('ai-chat')->group(function () {
    Route::post('/chat', [AiChatController::class, 'chat']);
    Route::get('/history', [AiChatController::class, 'getChatHistory']);
    Route::delete('/history', [AiChatController::class, 'clearChatHistory']);
});

// Finance - Cash Management Routes
Route::prefix('finance/cash-management')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Api\Finance\CashManagementController::class, 'dashboard']);
    Route::get('/bank-accounts', [App\Http\Controllers\Api\Finance\CashManagementController::class, 'getBankAccounts']);
    Route::post('/bank-accounts', [App\Http\Controllers\Api\Finance\CashManagementController::class, 'createBankAccount']);
    Route::get('/bank-accounts/{id}', [App\Http\Controllers\Api\Finance\CashManagementController::class, 'getBankAccount']);
    Route::put('/bank-accounts/{id}', [App\Http\Controllers\Api\Finance\CashManagementController::class, 'updateBankAccount']);
    Route::patch('/bank-accounts/{id}/status', [App\Http\Controllers\Api\Finance\CashManagementController::class, 'toggleBankAccountStatus']);
    Route::get('/bank-accounts/export', [App\Http\Controllers\Api\Finance\CashManagementController::class, 'exportBankAccounts']);
    
    Route::get('/petty-cash', [App\Http\Controllers\Api\Finance\CashManagementController::class, 'getPettyCashFunds']);
    Route::post('/petty-cash', [App\Http\Controllers\Api\Finance\CashManagementController::class, 'createPettyCashFund']);
    
    Route::get('/transactions/recent', [App\Http\Controllers\Api\Finance\CashManagementController::class, 'getRecentTransactions']);
    Route::post('/transactions', [App\Http\Controllers\Api\Finance\CashManagementController::class, 'createTransaction']);
    
    Route::get('/export/cash-flow', [App\Http\Controllers\Api\Finance\CashManagementController::class, 'exportCashFlow']);
});

// Finance - Fixed Assets Routes
Route::prefix('finance/fixed-assets')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Api\Finance\FixedAssetsController::class, 'dashboard']);
    Route::get('/', [App\Http\Controllers\Api\Finance\FixedAssetsController::class, 'getAssets']);
    Route::post('/', [App\Http\Controllers\Api\Finance\FixedAssetsController::class, 'createAsset']);
    Route::get('/{id}', [App\Http\Controllers\Api\Finance\FixedAssetsController::class, 'getAsset']);
    Route::put('/{id}', [App\Http\Controllers\Api\Finance\FixedAssetsController::class, 'updateAsset']);
    
    Route::get('/categories', [App\Http\Controllers\Api\Finance\FixedAssetsController::class, 'getCategories']);
    Route::post('/categories', [App\Http\Controllers\Api\Finance\FixedAssetsController::class, 'createCategory']);
    
    Route::get('/monthly-depreciation', [App\Http\Controllers\Api\Finance\FixedAssetsController::class, 'getMonthlyDepreciation']);
    Route::post('/{id}/calculate-depreciation', [App\Http\Controllers\Api\Finance\FixedAssetsController::class, 'calculateDepreciation']);
    
    Route::get('/export', [App\Http\Controllers\Api\Finance\FixedAssetsController::class, 'exportAssets']);
});

// Finance - Budgeting Routes
Route::prefix('finance/budgeting')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Api\Finance\BudgetingController::class, 'dashboard']);
    Route::get('/budgets', [App\Http\Controllers\Api\Finance\BudgetingController::class, 'getBudgets']);
    Route::post('/budgets', [App\Http\Controllers\Api\Finance\BudgetingController::class, 'createBudget']);
    
    Route::get('/periods', [App\Http\Controllers\Api\Finance\BudgetingController::class, 'getBudgetPeriods']);
    Route::post('/periods', [App\Http\Controllers\Api\Finance\BudgetingController::class, 'createBudgetPeriod']);
    
    Route::get('/categories', [App\Http\Controllers\Api\Finance\BudgetingController::class, 'getBudgetCategories']);
    Route::post('/categories', [App\Http\Controllers\Api\Finance\BudgetingController::class, 'createBudgetCategory']);
    
    Route::get('/variance-analysis', [App\Http\Controllers\Api\Finance\BudgetingController::class, 'getVarianceAnalysis']);
    Route::post('/variance-analysis', [App\Http\Controllers\Api\Finance\BudgetingController::class, 'createVarianceAnalysis']);
    
    Route::get('/export', [App\Http\Controllers\Api\Finance\BudgetingController::class, 'exportBudgetReport']);
    Route::get('/forecast', [App\Http\Controllers\Api\Finance\BudgetingController::class, 'getFinancialForecast']);
});

// Finance - Multi-Currency Routes
Route::prefix('finance/multi-currency')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Api\Finance\MultiCurrencyController::class, 'dashboard']);
    Route::get('/currencies', [App\Http\Controllers\Api\Finance\MultiCurrencyController::class, 'getCurrencies']);
    Route::post('/currencies', [App\Http\Controllers\Api\Finance\MultiCurrencyController::class, 'createCurrency']);
    Route::patch('/currencies/{id}', [App\Http\Controllers\Api\Finance\MultiCurrencyController::class, 'updateCurrency']);
    Route::patch('/currencies/{id}/toggle-status', [App\Http\Controllers\Api\Finance\MultiCurrencyController::class, 'toggleCurrencyStatus']);
    Route::get('/exchange-rates', [App\Http\Controllers\Api\Finance\MultiCurrencyController::class, 'getExchangeRates']);
    Route::patch('/exchange-rates/{id}', [App\Http\Controllers\Api\Finance\MultiCurrencyController::class, 'updateExchangeRate']);
    Route::post('/refresh-rates', [App\Http\Controllers\Api\Finance\MultiCurrencyController::class, 'refreshExchangeRates']);
    Route::get('/exchange-rate-history', [App\Http\Controllers\Api\Finance\MultiCurrencyController::class, 'getExchangeRateHistory']);
    Route::get('/export-history', [App\Http\Controllers\Api\Finance\MultiCurrencyController::class, 'exportHistory']);
});

// Finance - Approval Workflow Routes
Route::prefix('finance/approval-workflow')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Api\Finance\ApprovalWorkflowController::class, 'dashboard']);
    Route::get('/workflows', [App\Http\Controllers\Api\Finance\ApprovalWorkflowController::class, 'getWorkflows']);
    Route::post('/workflows', [App\Http\Controllers\Api\Finance\ApprovalWorkflowController::class, 'createWorkflow']);
    Route::patch('/workflows/{id}', [App\Http\Controllers\Api\Finance\ApprovalWorkflowController::class, 'updateWorkflow']);
    Route::get('/approval-requests', [App\Http\Controllers\Api\Finance\ApprovalWorkflowController::class, 'getApprovalRequests']);
    Route::post('/approval-requests', [App\Http\Controllers\Api\Finance\ApprovalWorkflowController::class, 'createApprovalRequest']);
    Route::patch('/approval-requests/{id}/process', [App\Http\Controllers\Api\Finance\ApprovalWorkflowController::class, 'processApproval']);
    Route::get('/approval-rules', [App\Http\Controllers\Api\Finance\ApprovalWorkflowController::class, 'getApprovalRules']);
    Route::get('/export-approvals', [App\Http\Controllers\Api\Finance\ApprovalWorkflowController::class, 'exportApprovals']);
});

// Purchasing - Purchase Order Routes
Route::prefix('purchasing/purchase-orders')->group(function () {
    Route::get('/', [App\Http\Controllers\Api\Purchasing\PurchaseOrderController::class, 'index']);
    Route::post('/', [App\Http\Controllers\Api\Purchasing\PurchaseOrderController::class, 'store']);
    Route::get('/{id}', [App\Http\Controllers\Api\Purchasing\PurchaseOrderController::class, 'show']);
    Route::patch('/{id}', [App\Http\Controllers\Api\Purchasing\PurchaseOrderController::class, 'update']);
    Route::post('/{id}/submit-approval', [App\Http\Controllers\Api\Purchasing\PurchaseOrderController::class, 'submitForApproval']);
    Route::post('/{id}/cancel', [App\Http\Controllers\Api\Purchasing\PurchaseOrderController::class, 'cancel']);
    Route::get('/pending-approval', [App\Http\Controllers\Api\Purchasing\PurchaseOrderController::class, 'pendingApproval']);
});

// Finance - Expense Routes
Route::prefix('finance/expenses')->group(function () {
    Route::get('/', [App\Http\Controllers\Api\Finance\ExpenseController::class, 'index']);
    Route::post('/', [App\Http\Controllers\Api\Finance\ExpenseController::class, 'store']);
    Route::get('/{id}', [App\Http\Controllers\Api\Finance\ExpenseController::class, 'show']);
    Route::patch('/{id}', [App\Http\Controllers\Api\Finance\ExpenseController::class, 'update']);
    Route::post('/{id}/submit-approval', [App\Http\Controllers\Api\Finance\ExpenseController::class, 'submitForApproval']);
    Route::post('/{id}/cancel', [App\Http\Controllers\Api\Finance\ExpenseController::class, 'cancel']);
    Route::get('/pending-approval', [App\Http\Controllers\Api\Finance\ExpenseController::class, 'pendingApproval']);
    Route::get('/analytics', [App\Http\Controllers\Api\Finance\ExpenseController::class, 'analytics']);
});

// Finance - Asset Purchase Routes
Route::prefix('finance/asset-purchases')->group(function () {
    Route::get('/', [App\Http\Controllers\Api\Finance\AssetPurchaseController::class, 'index']);
    Route::post('/', [App\Http\Controllers\Api\Finance\AssetPurchaseController::class, 'store']);
    Route::get('/{id}', [App\Http\Controllers\Api\Finance\AssetPurchaseController::class, 'show']);
    Route::patch('/{id}', [App\Http\Controllers\Api\Finance\AssetPurchaseController::class, 'update']);
    Route::post('/{id}/submit-approval', [App\Http\Controllers\Api\Finance\AssetPurchaseController::class, 'submitForApproval']);
    Route::post('/{id}/cancel', [App\Http\Controllers\Api\Finance\AssetPurchaseController::class, 'cancel']);
    Route::get('/pending-approval', [App\Http\Controllers\Api\Finance\AssetPurchaseController::class, 'pendingApproval']);
    Route::get('/analytics', [App\Http\Controllers\Api\Finance\AssetPurchaseController::class, 'analytics']);
});