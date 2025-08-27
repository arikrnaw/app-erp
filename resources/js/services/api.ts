import type {
    BillOfMaterial,
    Category,
    ChartOfAccount,
    Company,
    Customer,
    CustomerForm,
    DashboardStats,
    Department,
    Employee,
    EmployeeForm,
    GoodsReceipt,
    InventoryTransaction,
    JournalEntry,
    LeaveRequest,
    LeaveRequestForm,
    LeaveType,
    LeaveTypeForm,
    PaginatedData,
    PayrollPeriod,
    PayrollPeriodForm,
    Position,
    Product,
    ProductForm,
    ProductionPlan,
    ProductLot,
    PurchaseOrder,
    PurchaseRequest,
    PurchaseReturn,
    ReorderAlert,
    SalesOrder,
    Supplier,
    SupplierForm,
    User,
    Warehouse,
    WarehouseLocation,
    WorkCenter,
    WorkOrder,
} from '@/types/erp';
import axios, { AxiosInstance, AxiosResponse } from 'axios';

class ApiService {
    private api: AxiosInstance;

    constructor() {
        this.api = axios.create({
            baseURL: '/api',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
            },
            withCredentials: true, // Important for session-based authentication
        });

        // Add request interceptor to include CSRF token
        this.api.interceptors.request.use((config) => {
            // Get CSRF token from meta tag
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (token) {
                config.headers['X-CSRF-TOKEN'] = token;
            }
            return config;
        });

        // Add response interceptor to handle errors
        this.api.interceptors.response.use(
            (response) => response,
            (error) => {
                if (error.response?.status === 401) {
                    // Redirect to WorkOS login for session-based auth
                    window.location.href = '/login';
                } else if (error.response?.status === 419) {
                    // CSRF token mismatch - refresh the page
                    window.location.reload();
                }
                return Promise.reject(error);
            },
        );
    }

    // Generic HTTP methods
    async get(url: string, config?: any): Promise<AxiosResponse> {
        return this.api.get(url, config);
    }

    async post(url: string, data?: any, config?: any): Promise<AxiosResponse> {
        return this.api.post(url, data, config);
    }

    async put(url: string, data?: any, config?: any): Promise<AxiosResponse> {
        return this.api.put(url, data, config);
    }

    async patch(url: string, data?: any, config?: any): Promise<AxiosResponse> {
        return this.api.patch(url, data, config);
    }

    async delete(url: string, config?: any): Promise<AxiosResponse> {
        return this.api.delete(url, config);
    }

    // Authentication helpers for WorkOS
    async getCurrentUser(): Promise<any> {
        try {
            const response: AxiosResponse = await this.api.get('/user');
            return response.data;
        } catch (error) {
            return null;
        }
    }

    async logout(): Promise<void> {
        try {
            await this.api.post('/logout');
            window.location.href = '/';
        } catch (error) {
            // If logout fails, redirect anyway
            window.location.href = '/';
        }
    }

    // Dashboard
    async getDashboard(): Promise<{ company: Company; stats: DashboardStats }> {
        const response: AxiosResponse = await this.api.get('/dashboard');
        return response.data;
    }

    // Categories
    async getCategories(): Promise<Category[]> {
        const response: AxiosResponse = await this.api.get('/categories');
        return response.data.data;
    }

    async createCategory(data: { name: string; parent_id?: number }): Promise<Category> {
        const response: AxiosResponse = await this.api.post('/categories', data);
        return response.data.category;
    }

    async updateCategory(id: number, data: { name: string; parent_id?: number }): Promise<Category> {
        const response: AxiosResponse = await this.api.put(`/categories/${id}`, data);
        return response.data.category;
    }

    async deleteCategory(id: number): Promise<void> {
        await this.api.delete(`/categories/${id}`);
    }

    // Products
    async getProducts(params?: { search?: string; category_id?: number; page?: number }): Promise<PaginatedData<Product>> {
        const response: AxiosResponse = await this.api.get('/products', { params });
        return response.data;
    }

    async getProduct(id: number): Promise<Product> {
        const response: AxiosResponse = await this.api.get(`/products/${id}`);
        return response.data.data;
    }

    async createProduct(data: ProductForm): Promise<Product> {
        const response: AxiosResponse = await this.api.post('/products', data);
        return response.data.product;
    }

    async updateProduct(id: number, data: ProductForm): Promise<Product> {
        const response: AxiosResponse = await this.api.put(`/products/${id}`, data);
        return response.data.product;
    }

    async deleteProduct(id: number): Promise<void> {
        await this.api.delete(`/products/${id}`);
    }

    // Customers
    async getCustomers(params?: { search?: string; page?: number }): Promise<PaginatedData<Customer>> {
        const response: AxiosResponse = await this.api.get('/customers', { params });
        return response.data;
    }

    async getCustomer(id: number): Promise<Customer> {
        const response: AxiosResponse = await this.api.get(`/customers/${id}`);
        return response.data.data;
    }

    async createCustomer(data: CustomerForm): Promise<Customer> {
        const response: AxiosResponse = await this.api.post('/customers', data);
        return response.data.customer;
    }

    async updateCustomer(id: number, data: CustomerForm): Promise<Customer> {
        const response: AxiosResponse = await this.api.put(`/customers/${id}`, data);
        return response.data.customer;
    }

    async deleteCustomer(id: number): Promise<void> {
        await this.api.delete(`/customers/${id}`);
    }

    // Suppliers
    async getSuppliers(params?: { search?: string; page?: number }): Promise<PaginatedData<Supplier>> {
        const response: AxiosResponse = await this.api.get('/suppliers', { params });
        return response.data;
    }

    async getSupplier(id: number): Promise<Supplier> {
        const response: AxiosResponse = await this.api.get(`/suppliers/${id}`);
        return response.data.data;
    }

    async createSupplier(data: SupplierForm): Promise<Supplier> {
        const response: AxiosResponse = await this.api.post('/suppliers', data);
        return response.data.supplier;
    }

    async updateSupplier(id: number, data: SupplierForm): Promise<Supplier> {
        const response: AxiosResponse = await this.api.put(`/suppliers/${id}`, data);
        return response.data.supplier;
    }

    async deleteSupplier(id: number): Promise<void> {
        await this.api.delete(`/suppliers/${id}`);
    }

    // Sales Orders
    async getSalesOrders(params?: { search?: string; page?: number }): Promise<PaginatedData<SalesOrder>> {
        const response: AxiosResponse = await this.api.get('/sales-orders', { params });
        return response.data;
    }

    async getSalesOrder(id: number): Promise<SalesOrder> {
        const response: AxiosResponse = await this.api.get(`/sales-orders/${id}`);
        return response.data.data;
    }

    async createSalesOrder(data: any): Promise<SalesOrder> {
        const response: AxiosResponse = await this.api.post('/sales-orders', data);
        return response.data.sales_order;
    }

    async updateSalesOrder(id: number, data: any): Promise<SalesOrder> {
        const response: AxiosResponse = await this.api.put(`/sales-orders/${id}`, data);
        return response.data.sales_order;
    }

    async deleteSalesOrder(id: number): Promise<void> {
        await this.api.delete(`/sales-orders/${id}`);
    }

    // Purchase Orders
    async getPurchaseOrders(params?: { search?: string; page?: number }): Promise<PaginatedData<PurchaseOrder>> {
        const response: AxiosResponse = await this.api.get('/purchase-orders', { params });
        return response.data;
    }

    async getPurchaseOrder(id: number): Promise<PurchaseOrder> {
        const response: AxiosResponse = await this.api.get(`/purchase-orders/${id}`);
        return response.data.data;
    }

    async createPurchaseOrder(data: any): Promise<PurchaseOrder> {
        const response: AxiosResponse = await this.api.post('/purchase-orders', data);
        return response.data.purchase_order;
    }

    async updatePurchaseOrder(id: number, data: any): Promise<PurchaseOrder> {
        const response: AxiosResponse = await this.api.put(`/purchase-orders/${id}`, data);
        return response.data.purchase_order;
    }

    async deletePurchaseOrder(id: number): Promise<void> {
        await this.api.delete(`/purchase-orders/${id}`);
    }

    // Purchase Requests
    async getPurchaseRequests(params?: any): Promise<PaginatedData<PurchaseRequest>> {
        const response: AxiosResponse = await this.api.get('/purchase-requests', { params });
        return response.data;
    }

    async getPurchaseRequest(id: number): Promise<PurchaseRequest> {
        const response: AxiosResponse = await this.api.get(`/purchase-requests/${id}`);
        return response.data.data;
    }

    async createPurchaseRequest(data: any): Promise<PurchaseRequest> {
        const response: AxiosResponse = await this.api.post('/purchase-requests', data);
        return response.data.purchase_request;
    }

    async updatePurchaseRequest(id: number, data: any): Promise<PurchaseRequest> {
        const response: AxiosResponse = await this.api.put(`/purchase-requests/${id}`, data);
        return response.data.purchase_request;
    }

    async deletePurchaseRequest(id: number): Promise<void> {
        await this.api.delete(`/purchase-requests/${id}`);
    }

    async generatePurchaseRequestNumber(): Promise<{ request_number: string }> {
        const response: AxiosResponse = await this.api.post('/purchase-requests/generate-number');
        return response.data;
    }

    // Goods Receipts
    async getGoodsReceipts(params?: any): Promise<PaginatedData<GoodsReceipt>> {
        const response: AxiosResponse = await this.api.get('/goods-receipts', { params });
        return response.data;
    }

    async getGoodsReceipt(id: number): Promise<GoodsReceipt> {
        const response: AxiosResponse = await this.api.get(`/goods-receipts/${id}`);
        return response.data.data;
    }

    async createGoodsReceipt(data: any): Promise<GoodsReceipt> {
        const response: AxiosResponse = await this.api.post('/goods-receipts', data);
        return response.data.goods_receipt;
    }

    async updateGoodsReceipt(id: number, data: any): Promise<GoodsReceipt> {
        const response: AxiosResponse = await this.api.put(`/goods-receipts/${id}`, data);
        return response.data.goods_receipt;
    }

    async deleteGoodsReceipt(id: number): Promise<void> {
        await this.api.delete(`/goods-receipts/${id}`);
    }

    async generateGoodsReceiptNumber(): Promise<{ receipt_number: string }> {
        const response: AxiosResponse = await this.api.post('/goods-receipts/generate-number');
        return response.data;
    }

    // Purchase Returns
    async getPurchaseReturns(params?: any): Promise<PaginatedData<PurchaseReturn>> {
        const response: AxiosResponse = await this.api.get('/purchase-returns', { params });
        return response.data;
    }

    async getPurchaseReturn(id: number): Promise<PurchaseReturn> {
        const response: AxiosResponse = await this.api.get(`/purchase-returns/${id}`);
        return response.data.data;
    }

    async createPurchaseReturn(data: any): Promise<PurchaseReturn> {
        const response: AxiosResponse = await this.api.post('/purchase-returns', data);
        return response.data.purchase_return;
    }

    async updatePurchaseReturn(id: number, data: any): Promise<PurchaseReturn> {
        const response: AxiosResponse = await this.api.put(`/purchase-returns/${id}`, data);
        return response.data.purchase_return;
    }

    async deletePurchaseReturn(id: number): Promise<void> {
        await this.api.delete(`/purchase-returns/${id}`);
    }

    async generatePurchaseReturnNumber(): Promise<{ return_number: string }> {
        const response: AxiosResponse = await this.api.post('/purchase-returns/generate-number');
        return response.data;
    }

    // Inventory Transactions
    async getInventoryTransactions(params?: {
        search?: string;
        type?: string;
        product_id?: number;
        page?: number;
    }): Promise<PaginatedData<InventoryTransaction>> {
        const response: AxiosResponse = await this.api.get('/inventory-transactions', { params });
        return response.data;
    }

    async getInventoryTransaction(id: number): Promise<InventoryTransaction> {
        const response: AxiosResponse = await this.api.get(`/inventory-transactions/${id}`);
        return response.data.data;
    }

    async createInventoryTransaction(data: any): Promise<InventoryTransaction> {
        const response: AxiosResponse = await this.api.post('/inventory-transactions', data);
        return response.data.inventory_transaction;
    }

    async updateInventoryTransaction(id: number, data: any): Promise<InventoryTransaction> {
        const response: AxiosResponse = await this.api.put(`/inventory-transactions/${id}`, data);
        return response.data.inventory_transaction;
    }

    async deleteInventoryTransaction(id: number): Promise<void> {
        await this.api.delete(`/inventory-transactions/${id}`);
    }

    async getInventoryTransactionSummary(params?: any): Promise<any> {
        const response: AxiosResponse = await this.api.get('/inventory/transactions/summary', { params });
        return response.data;
    }

    // Warehouses
    async getWarehouses(params?: any): Promise<PaginatedData<Warehouse>> {
        const response: AxiosResponse = await this.api.get('/inventory/warehouses', { params });
        return response.data;
    }

    async getWarehouse(id: number): Promise<Warehouse> {
        const response: AxiosResponse = await this.api.get(`/inventory/warehouses/${id}`);
        return response.data.warehouse;
    }

    async createWarehouse(data: any): Promise<Warehouse> {
        const response: AxiosResponse = await this.api.post('/inventory/warehouses', data);
        return response.data.warehouse;
    }

    async updateWarehouse(id: number, data: any): Promise<Warehouse> {
        const response: AxiosResponse = await this.api.put(`/inventory/warehouses/${id}`, data);
        return response.data.warehouse;
    }

    async deleteWarehouse(id: number): Promise<void> {
        await this.api.delete(`/inventory/warehouses/${id}`);
    }

    async getActiveWarehouses(): Promise<Warehouse[]> {
        const response: AxiosResponse = await this.api.get('/inventory/warehouses/active');
        return response.data;
    }

    // Warehouse Locations
    async getWarehouseLocations(params?: any): Promise<PaginatedData<WarehouseLocation>> {
        const response: AxiosResponse = await this.api.get('/inventory/warehouse-locations', { params });
        return response.data;
    }

    async getWarehouseLocation(id: number): Promise<WarehouseLocation> {
        const response: AxiosResponse = await this.api.get(`/inventory/warehouse-locations/${id}`);
        return response.data.warehouse_location;
    }

    async createWarehouseLocation(data: any): Promise<WarehouseLocation> {
        const response: AxiosResponse = await this.api.post('/inventory/warehouse-locations', data);
        return response.data.warehouse_location;
    }

    async updateWarehouseLocation(id: number, data: any): Promise<WarehouseLocation> {
        const response: AxiosResponse = await this.api.put(`/inventory/warehouse-locations/${id}`, data);
        return response.data.warehouse_location;
    }

    async deleteWarehouseLocation(id: number): Promise<void> {
        await this.api.delete(`/inventory/warehouse-locations/${id}`);
    }

    async getWarehouseLocationsByWarehouse(warehouseId: number): Promise<WarehouseLocation[]> {
        const response: AxiosResponse = await this.api.get(`/inventory/warehouse-locations/warehouse/${warehouseId}`);
        return response.data;
    }

    // Product Lots
    async getProductLots(params?: any): Promise<PaginatedData<ProductLot>> {
        const response: AxiosResponse = await this.api.get('/inventory/product-lots', { params });
        return response.data;
    }

    async getProductLot(id: number): Promise<ProductLot> {
        const response: AxiosResponse = await this.api.get(`/inventory/product-lots/${id}`);
        return response.data.product_lot;
    }

    async createProductLot(data: any): Promise<ProductLot> {
        const response: AxiosResponse = await this.api.post('/inventory/product-lots', data);
        return response.data.product_lot;
    }

    async updateProductLot(id: number, data: any): Promise<ProductLot> {
        const response: AxiosResponse = await this.api.put(`/inventory/product-lots/${id}`, data);
        return response.data.product_lot;
    }

    async deleteProductLot(id: number): Promise<void> {
        await this.api.delete(`/inventory/product-lots/${id}`);
    }

    async getProductLotsByProduct(productId: number): Promise<ProductLot[]> {
        const response: AxiosResponse = await this.api.get(`/inventory/product-lots/product/${productId}`);
        return response.data;
    }

    async getExpiringLots(params?: any): Promise<ProductLot[]> {
        const response: AxiosResponse = await this.api.get('/inventory/product-lots/expiring-soon', { params });
        return response.data;
    }

    // Reorder Alerts
    async getReorderAlerts(params?: any): Promise<PaginatedData<ReorderAlert>> {
        const response: AxiosResponse = await this.api.get('/inventory/reorder-alerts', { params });
        return response.data;
    }

    async getReorderAlert(id: number): Promise<ReorderAlert> {
        const response: AxiosResponse = await this.api.get(`/inventory/reorder-alerts/${id}`);
        return response.data.reorder_alert;
    }

    async createReorderAlert(data: any): Promise<ReorderAlert> {
        const response: AxiosResponse = await this.api.post('/inventory/reorder-alerts', data);
        return response.data.reorder_alert;
    }

    async updateReorderAlert(id: number, data: any): Promise<ReorderAlert> {
        const response: AxiosResponse = await this.api.put(`/inventory/reorder-alerts/${id}`, data);
        return response.data.reorder_alert;
    }

    async deleteReorderAlert(id: number): Promise<void> {
        await this.api.delete(`/inventory/reorder-alerts/${id}`);
    }

    async getPendingReorderAlerts(): Promise<ReorderAlert[]> {
        const response: AxiosResponse = await this.api.get('/inventory/reorder-alerts/pending');
        return response.data;
    }

    async processReorderAlert(id: number): Promise<ReorderAlert> {
        const response: AxiosResponse = await this.api.post(`/inventory/reorder-alerts/${id}/process`);
        return response.data.reorder_alert;
    }

    async generateReorderAlerts(): Promise<any> {
        const response: AxiosResponse = await this.api.post('/inventory/reorder-alerts/generate');
        return response.data;
    }

    async getReorderAlertSummary(): Promise<any> {
        const response: AxiosResponse = await this.api.get('/inventory/reorder-alerts/summary');
        return response.data;
    }

    // Chart of Accounts
    async getChartOfAccounts(params?: { search?: string; type?: string; status?: string; page?: number }): Promise<PaginatedData<ChartOfAccount>> {
        const response: AxiosResponse = await this.api.get('/chart-of-accounts', { params });
        return response.data;
    }

    async getChartOfAccount(id: number): Promise<ChartOfAccount> {
        const response: AxiosResponse = await this.api.get(`/chart-of-accounts/${id}`);
        return response.data.data;
    }

    async createChartOfAccount(data: any): Promise<ChartOfAccount> {
        const response: AxiosResponse = await this.api.post('/chart-of-accounts', data);
        return response.data.chart_of_account;
    }

    async updateChartOfAccount(id: number, data: any): Promise<ChartOfAccount> {
        const response: AxiosResponse = await this.api.put(`/chart-of-accounts/${id}`, data);
        return response.data.chart_of_account;
    }

    async deleteChartOfAccount(id: number): Promise<void> {
        await this.api.delete(`/chart-of-accounts/${id}`);
    }

    async exportChartOfAccounts(params?: { search?: string; type?: string; status?: string }): Promise<Blob> {
        const response = await this.api.get('/chart-of-accounts/export', {
            params,
            responseType: 'blob',
        });
        return response.data;
    }

    // Journal Entries
    async getJournalEntries(params?: { search?: string; status?: string; date?: string; page?: number }): Promise<PaginatedData<JournalEntry>> {
        const response: AxiosResponse = await this.api.get('/journal-entries', { params });
        return response.data;
    }

    async getJournalEntry(id: number): Promise<JournalEntry> {
        const response: AxiosResponse = await this.api.get(`/journal-entries/${id}`);
        return response.data.data;
    }

    async createJournalEntry(data: any): Promise<JournalEntry> {
        const response: AxiosResponse = await this.api.post('/journal-entries', data);
        return response.data.journal_entry;
    }

    async updateJournalEntry(id: number, data: any): Promise<JournalEntry> {
        const response: AxiosResponse = await this.api.put(`/journal-entries/${id}`, data);
        return response.data.journal_entry;
    }

    async deleteJournalEntry(id: number): Promise<void> {
        await this.api.delete(`/journal-entries/${id}`);
    }

    // General Ledger
    async getGeneralLedger(params: any = {}): Promise<any> {
        const response = await this.api.get('/finance/general-ledger', { params });
        return response.data;
    }

    async exportGeneralLedger(params: any = {}): Promise<any> {
        const response = await this.api.get('/finance/general-ledger/export', {
            params,
            responseType: 'blob',
        });
        return response.data;
    }

    // Trial Balance
    async getTrialBalance(params: any = {}): Promise<any> {
        const response = await this.api.get('/finance/trial-balance', { params });
        return response.data;
    }

    async exportTrialBalance(params: any = {}): Promise<any> {
        const response = await this.api.get('/finance/trial-balance/export', {
            params,
            responseType: 'blob',
        });
        return response.data;
    }

    // Invoices (Accounts Receivable)
    async getInvoices(params: any = {}): Promise<any> {
        const response = await this.api.get('/finance/accounts-receivable/invoices', { params });
        return response.data;
    }

    async getInvoice(id: number): Promise<any> {
        const response = await this.api.get(`/finance/accounts-receivable/invoices/${id}`);
        return response.data;
    }

    async createInvoice(data: any): Promise<any> {
        const response = await this.api.post('/finance/accounts-receivable/invoices', data);
        return response.data;
    }

    async updateInvoice(id: number, data: any): Promise<any> {
        const response = await this.api.put(`/finance/accounts-receivable/invoices/${id}`, data);
        return response.data;
    }

    async deleteInvoice(id: number): Promise<any> {
        const response = await this.api.delete(`/finance/accounts-receivable/invoices/${id}`);
        return response.data;
    }

    async postInvoice(id: number): Promise<any> {
        const response = await this.api.post(`/finance/accounts-receivable/invoices/${id}/post`);
        return response.data;
    }

    // Payments (Accounts Receivable)
    async getPayments(params: any = {}): Promise<any> {
        const response = await this.api.get('/finance/accounts-receivable/payments', { params });
        return response.data;
    }

    async getPayment(id: number): Promise<any> {
        const response = await this.api.get(`/finance/accounts-receivable/payments/${id}`);
        return response.data;
    }

    async createPayment(data: any): Promise<any> {
        const response = await this.api.post('/finance/accounts-receivable/payments', data);
        return response.data;
    }

    async recordPayment(data: any): Promise<any> {
        const response = await this.api.post('/finance/accounts-receivable/payments', data);
        return response.data;
    }

    async updatePayment(id: number, data: any): Promise<any> {
        const response = await this.api.put(`/finance/accounts-receivable/payments/${id}`, data);
        return response.data;
    }

    async deletePayment(id: number): Promise<any> {
        const response = await this.api.delete(`/finance/accounts-receivable/payments/${id}`);
        return response.data;
    }

    // Bills (Accounts Payable)
    async getBills(params: any = {}): Promise<any> {
        const response = await this.api.get('/finance/accounts-payable/bills', { params });
        return response.data;
    }

    async getBill(id: number): Promise<any> {
        const response = await this.api.get(`/finance/accounts-payable/bills/${id}`);
        return response.data;
    }

    async createBill(data: any): Promise<any> {
        const response = await this.api.post('/finance/accounts-payable/bills', data);
        return response.data;
    }

    async updateBill(id: number, data: any): Promise<any> {
        const response = await this.api.put(`/finance/accounts-payable/bills/${id}`, data);
        return response.data;
    }

    async deleteBill(id: number): Promise<any> {
        const response = await this.api.delete(`/finance/accounts-payable/bills/${id}`);
        return response.data;
    }

    async getBillSuppliers(): Promise<any> {
        const response = await this.api.get('/finance/accounts-payable/bills/suppliers');
        return response.data;
    }

    async getBillProducts(): Promise<any> {
        const response = await this.api.get('/finance/accounts-payable/bills/products');
        return response.data;
    }

    // Financial Reports
    async getBalanceSheet(params: any = {}): Promise<any> {
        const response = await this.api.get('/finance/reports/balance-sheet', { params });
        return response.data;
    }

    async getIncomeStatement(params: any = {}): Promise<any> {
        const response = await this.api.get('/finance/reports/income-statement', { params });
        return response.data;
    }

    // Tax Management
    async getTaxRates(params: any = {}): Promise<any> {
        const response = await this.api.get('/tax/rates', { params });
        return response.data;
    }

    async getTaxRate(id: number): Promise<any> {
        const response = await this.api.get(`/tax/rates/${id}`);
        return response.data;
    }

    async createTaxRate(data: any): Promise<any> {
        const response = await this.api.post('/tax/rates', data);
        return response.data;
    }

    async updateTaxRate(id: number, data: any): Promise<any> {
        const response = await this.api.put(`/tax/rates/${id}`, data);
        return response.data;
    }

    async deleteTaxRate(id: number): Promise<any> {
        const response = await this.api.delete(`/tax/rates/${id}`);
        return response.data;
    }

    async getActiveTaxRates(): Promise<any> {
        const response = await this.api.get('/tax/rates/active');
        return response.data;
    }

    async getTaxTransactions(params: any = {}): Promise<any> {
        const response = await this.api.get('/tax/transactions', { params });
        return response.data;
    }

    async getTaxTransaction(id: number): Promise<any> {
        const response = await this.api.get(`/tax/transactions/${id}`);
        return response.data;
    }

    async createTaxTransaction(data: any): Promise<any> {
        const response = await this.api.post('/tax/transactions', data);
        return response.data;
    }

    async updateTaxTransaction(id: number, data: any): Promise<any> {
        const response = await this.api.put(`/tax/transactions/${id}`, data);
        return response.data;
    }

    async deleteTaxTransaction(id: number): Promise<any> {
        const response = await this.api.delete(`/tax/transactions/${id}`);
        return response.data;
    }

    async getTaxTransactionSummary(params: any = {}): Promise<any> {
        const response = await this.api.get('/tax/transactions/summary', { params });
        return response.data;
    }

    // Bank Reconciliation
    async getBankAccounts(params: any = {}): Promise<any> {
        const response = await this.api.get('/bank/accounts', { params });
        return response.data;
    }

    async getBankAccount(id: number): Promise<any> {
        const response = await this.api.get(`/bank/accounts/${id}`);
        return response.data;
    }

    async createBankAccount(data: any): Promise<any> {
        const response = await this.api.post('/bank/accounts', data);
        return response.data;
    }

    async updateBankAccount(id: number, data: any): Promise<any> {
        const response = await this.api.put(`/bank/accounts/${id}`, data);
        return response.data;
    }

    async deleteBankAccount(id: number): Promise<any> {
        const response = await this.api.delete(`/bank/accounts/${id}`);
        return response.data;
    }

    async getActiveBankAccounts(): Promise<any> {
        const response = await this.api.get('/bank/accounts/active');
        return response.data;
    }

    async getBankAccountBalance(id: number): Promise<any> {
        const response = await this.api.get(`/bank/accounts/${id}/balance`);
        return response.data;
    }

    async getBankTransactions(params: any = {}): Promise<any> {
        const response = await this.api.get('/bank/transactions', { params });
        return response.data;
    }

    async getBankTransaction(id: number): Promise<any> {
        const response = await this.api.get(`/bank/transactions/${id}`);
        return response.data;
    }

    async createBankTransaction(data: any): Promise<any> {
        const response = await this.api.post('/bank/transactions', data);
        return response.data;
    }

    async updateBankTransaction(id: number, data: any): Promise<any> {
        const response = await this.api.put(`/bank/transactions/${id}`, data);
        return response.data;
    }

    async deleteBankTransaction(id: number): Promise<any> {
        const response = await this.api.delete(`/bank/transactions/${id}`);
        return response.data;
    }

    async getUnreconciledTransactions(params: any = {}): Promise<any> {
        const response = await this.api.get('/bank/transactions/unreconciled', { params });
        return response.data;
    }

    // Manufacturing Module
    // Bill of Materials
    async getBillOfMaterials(params?: any): Promise<PaginatedData<BillOfMaterial>> {
        const response: AxiosResponse = await this.api.get('/manufacturing/bill-of-materials', { params });
        return response.data;
    }

    async getBillOfMaterial(id: number): Promise<BillOfMaterial> {
        const response: AxiosResponse = await this.api.get(`/manufacturing/bill-of-materials/${id}`);
        return response.data.data;
    }

    async createBillOfMaterial(data: any): Promise<BillOfMaterial> {
        const response: AxiosResponse = await this.api.post('/manufacturing/bill-of-materials', data);
        return response.data.bill_of_material;
    }

    async updateBillOfMaterial(id: number, data: any): Promise<BillOfMaterial> {
        const response: AxiosResponse = await this.api.put(`/manufacturing/bill-of-materials/${id}`, data);
        return response.data.bill_of_material;
    }

    async deleteBillOfMaterial(id: number): Promise<void> {
        await this.api.delete(`/manufacturing/bill-of-materials/${id}`);
    }

    async generateBillOfMaterialNumber(): Promise<{ bom_number: string }> {
        const response: AxiosResponse = await this.api.post('/manufacturing/bill-of-materials/generate-number');
        return response.data;
    }

    async approveBillOfMaterial(id: number, data: any): Promise<BillOfMaterial> {
        const response: AxiosResponse = await this.api.post(`/manufacturing/bill-of-materials/${id}/approve`, data);
        return response.data.bill_of_material;
    }

    // Production Plans
    async getProductionPlans(params?: any): Promise<PaginatedData<ProductionPlan>> {
        const response: AxiosResponse = await this.api.get('/manufacturing/production-plans', { params });
        return response.data;
    }

    async getProductionPlan(id: number): Promise<ProductionPlan> {
        const response: AxiosResponse = await this.api.get(`/manufacturing/production-plans/${id}`);
        return response.data.data;
    }

    async createProductionPlan(data: any): Promise<ProductionPlan> {
        const response: AxiosResponse = await this.api.post('/manufacturing/production-plans', data);
        return response.data.production_plan;
    }

    async updateProductionPlan(id: number, data: any): Promise<ProductionPlan> {
        const response: AxiosResponse = await this.api.put(`/manufacturing/production-plans/${id}`, data);
        return response.data.production_plan;
    }

    async deleteProductionPlan(id: number): Promise<void> {
        await this.api.delete(`/manufacturing/production-plans/${id}`);
    }

    async generateProductionPlanNumber(): Promise<{ plan_number: string }> {
        const response: AxiosResponse = await this.api.post('/manufacturing/production-plans/generate-number');
        return response.data;
    }

    async approveProductionPlan(id: number, data: any): Promise<ProductionPlan> {
        const response: AxiosResponse = await this.api.post(`/manufacturing/production-plans/${id}/approve`, data);
        return response.data.production_plan;
    }

    // Work Orders
    async getWorkOrders(params?: any): Promise<PaginatedData<WorkOrder>> {
        const response: AxiosResponse = await this.api.get('/manufacturing/work-orders', { params });
        return response.data;
    }

    async getWorkOrder(id: number): Promise<WorkOrder> {
        const response: AxiosResponse = await this.api.get(`/manufacturing/work-orders/${id}`);
        return response.data.data;
    }

    async createWorkOrder(data: any): Promise<WorkOrder> {
        const response: AxiosResponse = await this.api.post('/manufacturing/work-orders', data);
        return response.data.work_order;
    }

    async updateWorkOrder(id: number, data: any): Promise<WorkOrder> {
        const response: AxiosResponse = await this.api.put(`/manufacturing/work-orders/${id}`, data);
        return response.data.work_order;
    }

    async deleteWorkOrder(id: number): Promise<void> {
        await this.api.delete(`/manufacturing/work-orders/${id}`);
    }

    async generateWorkOrderNumber(): Promise<{ work_order_number: string }> {
        const response: AxiosResponse = await this.api.post('/manufacturing/work-orders/generate-number');
        return response.data;
    }

    async approveWorkOrder(id: number, data: any): Promise<WorkOrder> {
        const response: AxiosResponse = await this.api.post(`/manufacturing/work-orders/${id}/approve`, data);
        return response.data.work_order;
    }

    async startWorkOrder(id: number): Promise<WorkOrder> {
        const response: AxiosResponse = await this.api.post(`/manufacturing/work-orders/${id}/start`);
        return response.data.work_order;
    }

    async completeWorkOrder(id: number, data: any): Promise<WorkOrder> {
        const response: AxiosResponse = await this.api.post(`/manufacturing/work-orders/${id}/complete`, data);
        return response.data.work_order;
    }

    // Work Centers
    async getWorkCenters(params?: any): Promise<PaginatedData<WorkCenter>> {
        const response: AxiosResponse = await this.api.get('/work-centers', { params });
        return response.data;
    }

    // Users
    async getUsers(params?: any): Promise<PaginatedData<User>> {
        const response: AxiosResponse = await this.api.get('/users', { params });
        return response.data;
    }

    // HR Management - Leave Types
    async getLeaveTypes(params?: any): Promise<PaginatedData<LeaveType>> {
        const response: AxiosResponse = await this.api.get('/hr/leave-types', { params });
        return response.data;
    }

    async getLeaveType(id: number): Promise<LeaveType> {
        const response: AxiosResponse = await this.api.get(`/hr/leave-types/${id}`);
        return response.data.data;
    }

    async createLeaveType(data: LeaveTypeForm): Promise<LeaveType> {
        const response: AxiosResponse = await this.api.post('/hr/leave-types', data);
        return response.data.data;
    }

    async updateLeaveType(id: number, data: LeaveTypeForm): Promise<LeaveType> {
        const response: AxiosResponse = await this.api.put(`/hr/leave-types/${id}`, data);
        return response.data.data;
    }

    async deleteLeaveType(id: number): Promise<void> {
        await this.api.delete(`/hr/leave-types/${id}`);
    }

    async getActiveLeaveTypes(): Promise<LeaveType[]> {
        const response: AxiosResponse = await this.api.get('/hr/leave-types/active');
        return response.data.data;
    }

    async toggleLeaveTypeStatus(id: number): Promise<LeaveType> {
        const response: AxiosResponse = await this.api.post(`/hr/leave-types/${id}/toggle-status`);
        return response.data.data;
    }

    // HR Management - Leave Requests
    async getLeaveRequests(params?: any): Promise<PaginatedData<LeaveRequest>> {
        const response: AxiosResponse = await this.api.get('/hr/leave-requests', { params });
        return response.data;
    }

    async getLeaveRequest(id: number): Promise<LeaveRequest> {
        const response: AxiosResponse = await this.api.get(`/hr/leave-requests/${id}`);
        return response.data.data;
    }

    async createLeaveRequest(data: LeaveRequestForm): Promise<LeaveRequest> {
        const response: AxiosResponse = await this.api.post('/hr/leave-requests', data);
        return response.data.data;
    }

    async updateLeaveRequest(id: number, data: LeaveRequestForm): Promise<LeaveRequest> {
        const response: AxiosResponse = await this.api.put(`/hr/leave-requests/${id}`, data);
        return response.data.data;
    }

    async deleteLeaveRequest(id: number): Promise<void> {
        await this.api.delete(`/hr/leave-requests/${id}`);
    }

    async getPendingLeaveRequests(): Promise<LeaveRequest[]> {
        const response: AxiosResponse = await this.api.get('/hr/leave-requests/pending');
        return response.data.data;
    }

    async getUrgentLeaveRequests(): Promise<LeaveRequest[]> {
        const response: AxiosResponse = await this.api.get('/hr/leave-requests/urgent');
        return response.data.data;
    }

    async approveLeaveRequest(id: number, data: any): Promise<LeaveRequest> {
        const response: AxiosResponse = await this.api.post(`/hr/leave-requests/${id}/approve`, data);
        return response.data.data;
    }

    async rejectLeaveRequest(id: number, data: any): Promise<LeaveRequest> {
        const response: AxiosResponse = await this.api.post(`/hr/leave-requests/${id}/reject`, data);
        return response.data.data;
    }

    async cancelLeaveRequest(id: number): Promise<LeaveRequest> {
        const response: AxiosResponse = await this.api.post(`/hr/leave-requests/${id}/cancel`);
        return response.data.data;
    }

    // HR Management - Payroll Periods
    async getPayrollPeriods(params?: any): Promise<PaginatedData<PayrollPeriod>> {
        const response: AxiosResponse = await this.api.get('/hr/payroll-periods', { params });
        return response.data;
    }

    async getPayrollPeriod(id: number): Promise<PayrollPeriod> {
        const response: AxiosResponse = await this.api.get(`/hr/payroll-periods/${id}`);
        return response.data.data;
    }

    async createPayrollPeriod(data: PayrollPeriodForm): Promise<PayrollPeriod> {
        const response: AxiosResponse = await this.api.post('/hr/payroll-periods', data);
        return response.data.data;
    }

    async updatePayrollPeriod(id: number, data: PayrollPeriodForm): Promise<PayrollPeriod> {
        const response: AxiosResponse = await this.api.put(`/hr/payroll-periods/${id}`, data);
        return response.data.data;
    }

    async deletePayrollPeriod(id: number): Promise<void> {
        await this.api.delete(`/hr/payroll-periods/${id}`);
    }

    async getCurrentPayrollPeriod(): Promise<PayrollPeriod> {
        const response: AxiosResponse = await this.api.get('/hr/payroll-periods/current');
        return response.data.data;
    }

    async getUpcomingPayrollPeriods(): Promise<PayrollPeriod[]> {
        const response: AxiosResponse = await this.api.get('/hr/payroll-periods/upcoming');
        return response.data.data;
    }

    async getOverduePayrollPeriods(): Promise<PayrollPeriod[]> {
        const response: AxiosResponse = await this.api.get('/hr/payroll-periods/overdue');
        return response.data.data;
    }

    async processPayrollPeriod(id: number): Promise<PayrollPeriod> {
        const response: AxiosResponse = await this.api.post(`/hr/payroll-periods/${id}/process`);
        return response.data.data;
    }

    async approvePayrollPeriod(id: number, data: any): Promise<PayrollPeriod> {
        const response: AxiosResponse = await this.api.post(`/hr/payroll-periods/${id}/approve`, data);
        return response.data.data;
    }

    async markPayrollPeriodAsPaid(id: number): Promise<PayrollPeriod> {
        const response: AxiosResponse = await this.api.post(`/hr/payroll-periods/${id}/mark-as-paid`);
        return response.data.data;
    }

    async cancelPayrollPeriod(id: number): Promise<PayrollPeriod> {
        const response: AxiosResponse = await this.api.post(`/hr/payroll-periods/${id}/cancel`);
        return response.data.data;
    }

    async calculatePayrollPeriodTotals(id: number): Promise<PayrollPeriod> {
        const response: AxiosResponse = await this.api.post(`/hr/payroll-periods/${id}/calculate-totals`);
        return response.data.data;
    }

    // HR Management - Employees
    async getEmployees(params?: any): Promise<PaginatedData<Employee>> {
        const response: AxiosResponse = await this.api.get('/hr/employees', { params });
        return response.data;
    }

    async getEmployee(id: number): Promise<Employee> {
        const response: AxiosResponse = await this.api.get(`/hr/employees/${id}`);
        return response.data.data;
    }

    async createEmployee(data: EmployeeForm): Promise<Employee> {
        const response: AxiosResponse = await this.api.post('/hr/employees', data);
        return response.data.data;
    }

    async updateEmployee(id: number, data: EmployeeForm): Promise<Employee> {
        const response: AxiosResponse = await this.api.put(`/hr/employees/${id}`, data);
        return response.data.data;
    }

    async deleteEmployee(id: number): Promise<void> {
        await this.api.delete(`/hr/employees/${id}`);
    }

    async getActiveEmployees(): Promise<Employee[]> {
        const response: AxiosResponse = await this.api.get('/hr/employees/active');
        return response.data.data;
    }

    async getEmployeeStatistics(): Promise<any> {
        const response: AxiosResponse = await this.api.get('/hr/employees/statistics');
        return response.data.data;
    }

    async getEmployeesByDepartment(departmentId: number): Promise<Employee[]> {
        const response: AxiosResponse = await this.api.get(`/hr/employees/by-department/${departmentId}`);
        return response.data.data;
    }

    async getEmployeesByPosition(positionId: number): Promise<Employee[]> {
        const response: AxiosResponse = await this.api.get(`/hr/employees/by-position/${positionId}`);
        return response.data.data;
    }

    async toggleEmployeeStatus(id: number): Promise<Employee> {
        const response: AxiosResponse = await this.api.post(`/hr/employees/${id}/toggle-status`);
        return response.data.data;
    }

    // HR Management - Departments
    async getDepartments(params?: any): Promise<PaginatedData<Department>> {
        const response: AxiosResponse = await this.api.get('/departments', { params });
        return response.data;
    }

    async getDepartment(id: number): Promise<Department> {
        const response: AxiosResponse = await this.api.get(`/departments/${id}`);
        return response.data.data;
    }

    // HR Management - Positions
    async getPositions(params?: any): Promise<PaginatedData<Position>> {
        const response: AxiosResponse = await this.api.get('/positions', { params });
        return response.data;
    }

    async getPosition(id: number): Promise<Position> {
        const response: AxiosResponse = await this.api.get(`/positions/${id}`);
        return response.data.data;
    }
}

export const apiService = new ApiService();
export default apiService;
