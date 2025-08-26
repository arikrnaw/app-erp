// ERP System Types
export interface Company {
    id: number;
    name: string;
    address?: string;
    phone?: string;
    email?: string;
    website?: string;
    tax_number?: string;
    created_at: string;
    updated_at: string;
}

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
}

export interface Category {
    id: number;
    name: string;
    description?: string;
    parent_id?: number;
    parent?: Category;
    children?: Category[];
    created_at: string;
    updated_at: string;
}

export interface Product {
    id: number;
    name: string;
    description?: string;
    sku: string;
    barcode?: string;
    category_id: number;
    category?: Category;
    unit_price: number;
    cost_price: number;
    stock_quantity: number;
    min_stock_level: number;
    max_stock_level: number;
    reorder_point: number;
    reorder_quantity: number;
    track_lots: boolean;
    track_serials: boolean;
    auto_reorder: boolean;
    default_warehouse_id?: number;
    default_warehouse?: Warehouse;
    default_location_id?: number;
    default_location?: WarehouseLocation;
    average_cost: number;
    last_cost: number;
    last_stock_in_date?: string;
    last_stock_out_date?: string;
    unit: string;
    is_active: boolean;
    created_at: string;
    updated_at: string;
}

export interface ProductForm {
    name: string;
    description?: string;
    sku: string;
    barcode?: string;
    category_id: number;
    unit_price: number;
    cost_price: number;
    stock_quantity: number;
    min_stock_level: number;
    max_stock_level: number;
    unit: string;
    is_active: boolean;
}

export interface Customer {
    id: number;
    name: string;
    email?: string;
    phone?: string;
    address?: string;
    city?: string;
    state?: string;
    postal_code?: string;
    country?: string;
    tax_number?: string;
    credit_limit?: number;
    payment_terms?: number;
    is_active: boolean;
    created_at: string;
    updated_at: string;
}

export interface CustomerForm {
    name: string;
    email?: string;
    phone?: string;
    address?: string;
    city?: string;
    state?: string;
    postal_code?: string;
    country?: string;
    tax_number?: string;
    credit_limit?: number;
    payment_terms?: number;
    is_active: boolean;
}

export interface Supplier {
    id: number;
    name: string;
    email?: string;
    phone?: string;
    address?: string;
    city?: string;
    state?: string;
    postal_code?: string;
    country?: string;
    tax_number?: string;
    payment_terms?: number;
    is_active: boolean;
    created_at: string;
    updated_at: string;
}

export interface SupplierForm {
    name: string;
    email?: string;
    phone?: string;
    address?: string;
    city?: string;
    state?: string;
    postal_code?: string;
    country?: string;
    tax_number?: string;
    payment_terms?: number;
    is_active: boolean;
}

export interface SalesOrder {
    id: number;
    order_number: string;
    customer_id: number;
    customer?: Customer;
    order_date: string;
    delivery_date?: string;
    status: 'draft' | 'confirmed' | 'shipped' | 'delivered' | 'cancelled';
    subtotal: number;
    tax_amount: number;
    discount_amount: number;
    total_amount: number;
    notes?: string;
    created_at: string;
    updated_at: string;
    items?: SalesOrderItem[];
}

export interface SalesOrderItem {
    id: number;
    sales_order_id: number;
    product_id: number;
    product?: Product;
    quantity: number;
    unit_price: number;
    discount_percentage: number;
    tax_percentage: number;
    total_amount: number;
}

export interface PurchaseRequest {
    id: number;
    company_id: number;
    request_number: string;
    requested_by: number;
    requestedBy?: User;
    department_id?: number;
    department?: Department;
    request_date: string;
    required_date: string;
    priority: 'low' | 'medium' | 'high' | 'urgent';
    status: 'draft' | 'submitted' | 'approved' | 'rejected' | 'cancelled';
    purpose: string;
    notes?: string;
    total_estimated_cost: number;
    approved_by?: number;
    approvedBy?: User;
    approved_at?: string;
    approval_notes?: string;
    created_at: string;
    updated_at: string;
    items?: PurchaseRequestItem[];
}

export interface PurchaseRequestItem {
    id: number;
    purchase_request_id: number;
    purchase_request?: PurchaseRequest;
    product_id?: number;
    product?: Product;
    item_name: string;
    description?: string;
    specifications?: string;
    quantity: number;
    unit: string;
    estimated_unit_price: number;
    estimated_total_price: number;
    notes?: string;
    created_at: string;
    updated_at: string;
}

export interface PurchaseOrder {
    id: number;
    company_id: number;
    purchase_request_id?: number;
    purchase_request?: PurchaseRequest;
    supplier_id: number;
    supplier?: Supplier;
    warehouse_id?: number;
    warehouse?: Warehouse;
    po_number: string;
    reference_number?: string;
    order_date: string;
    expected_delivery_date?: string;
    payment_terms: 'immediate' | 'net_15' | 'net_30' | 'net_45' | 'net_60';
    shipping_address?: string;
    billing_address?: string;
    subtotal: number;
    tax_amount: number;
    discount_amount: number;
    shipping_cost: number;
    handling_cost: number;
    total_amount: number;
    delivery_method: 'pickup' | 'delivery' | 'express';
    carrier?: string;
    tracking_number?: string;
    status: 'draft' | 'sent' | 'confirmed' | 'received' | 'cancelled';
    notes?: string;
    created_by: number;
    createdBy?: User;
    approved_by?: number;
    approvedBy?: User;
    approved_at?: string;
    approval_notes?: string;
    updated_by?: number;
    updatedBy?: User;
    created_at: string;
    updated_at: string;
    deleted_at?: string;
    items?: PurchaseOrderItem[];
}

export interface PurchaseOrderItem {
    id: number;
    purchase_order_id: number;
    purchase_request_item_id?: number;
    purchase_request_item?: PurchaseRequestItem;
    product_id: number;
    product?: Product;
    item_description?: string;
    specifications?: string;
    quantity: number;
    unit: string;
    unit_price: number;
    discount_percentage: number;
    discount_amount: number;
    tax_percentage: number;
    tax_amount: number;
    total_price: number;
    received_quantity: number;
    returned_quantity: number;
    expected_delivery_date?: string;
    notes?: string;
    created_at: string;
    updated_at: string;
}

export interface GoodsReceipt {
    id: number;
    company_id: number;
    receipt_number: string;
    purchase_order_id: number;
    purchase_order?: PurchaseOrder;
    supplier_id: number;
    supplier?: Supplier;
    warehouse_id: number;
    warehouse?: Warehouse;
    received_by: number;
    receivedBy?: User;
    receipt_date: string;
    status: 'draft' | 'received' | 'partially_received' | 'cancelled';
    notes?: string;
    total_amount: number;
    delivery_note_number?: string;
    vehicle_number?: string;
    driver_name?: string;
    created_at: string;
    updated_at: string;
    items?: GoodsReceiptItem[];
}

export interface GoodsReceiptItem {
    id: number;
    goods_receipt_id: number;
    goods_receipt?: GoodsReceipt;
    purchase_order_item_id: number;
    purchase_order_item?: PurchaseOrderItem;
    product_id: number;
    product?: Product;
    ordered_quantity: number;
    received_quantity: number;
    unit_price: number;
    total_price: number;
    lot_number?: string;
    expiry_date?: string;
    notes?: string;
    created_at: string;
    updated_at: string;
}

export interface PurchaseReturn {
    id: number;
    company_id: number;
    return_number: string;
    purchase_order_id: number;
    purchase_order?: PurchaseOrder;
    goods_receipt_id?: number;
    goods_receipt?: GoodsReceipt;
    supplier_id: number;
    supplier?: Supplier;
    warehouse_id: number;
    warehouse?: Warehouse;
    returned_by: number;
    returnedBy?: User;
    return_date: string;
    status: 'draft' | 'submitted' | 'approved' | 'returned' | 'cancelled';
    return_type: 'defective' | 'wrong_item' | 'overstock' | 'other';
    reason: string;
    notes?: string;
    total_amount: number;
    approved_by?: number;
    approvedBy?: User;
    approved_at?: string;
    approval_notes?: string;
    created_at: string;
    updated_at: string;
    items?: PurchaseReturnItem[];
}

export interface PurchaseReturnItem {
    id: number;
    purchase_return_id: number;
    purchase_return?: PurchaseReturn;
    purchase_order_item_id: number;
    purchase_order_item?: PurchaseOrderItem;
    goods_receipt_item_id?: number;
    goods_receipt_item?: GoodsReceiptItem;
    product_id: number;
    product?: Product;
    received_quantity: number;
    return_quantity: number;
    unit_price: number;
    total_price: number;
    lot_number?: string;
    return_reason?: string;
    notes?: string;
    created_at: string;
    updated_at: string;
}

export interface Warehouse {
    id: number;
    company_id: number;
    name: string;
    code: string;
    address?: string;
    phone?: string;
    email?: string;
    manager_name?: string;
    description?: string;
    status: 'active' | 'inactive';
    created_at: string;
    updated_at: string;
    locations?: WarehouseLocation[];
}

export interface WarehouseLocation {
    id: number;
    warehouse_id: number;
    warehouse?: Warehouse;
    name: string;
    code: string;
    aisle?: string;
    rack?: string;
    level?: string;
    position?: string;
    description?: string;
    status: 'active' | 'inactive';
    created_at: string;
    updated_at: string;
}

export interface ProductLot {
    id: number;
    company_id: number;
    product_id: number;
    product?: Product;
    lot_number: string;
    batch_number?: string;
    manufacturing_date?: string;
    expiry_date?: string;
    initial_quantity: number;
    current_quantity: number;
    unit_cost: number;
    notes?: string;
    status: 'active' | 'expired' | 'depleted';
    created_at: string;
    updated_at: string;
    serials?: ProductSerial[];
}

export interface ProductSerial {
    id: number;
    company_id: number;
    product_id: number;
    product?: Product;
    product_lot_id?: number;
    product_lot?: ProductLot;
    serial_number: string;
    manufacturing_date?: string;
    expiry_date?: string;
    unit_cost: number;
    status: 'available' | 'reserved' | 'sold' | 'returned' | 'defective';
    notes?: string;
    created_at: string;
    updated_at: string;
}

export interface ReorderAlert {
    id: number;
    company_id: number;
    product_id: number;
    product?: Product;
    warehouse_id?: number;
    warehouse?: Warehouse;
    current_stock: number;
    reorder_point: number;
    suggested_quantity: number;
    status: 'pending' | 'processed' | 'cancelled';
    notes?: string;
    processed_at?: string;
    created_at: string;
    updated_at: string;
}

// Manufacturing Module Types
export interface BillOfMaterial {
    id: number;
    company_id: number;
    bom_number: string;
    name: string;
    description?: string;
    product_id: number;
    product?: Product;
    quantity_per_unit: number;
    unit: string;
    total_cost: number;
    status: 'draft' | 'active' | 'inactive' | 'archived';
    effective_date?: string;
    expiry_date?: string;
    created_by: number;
    created_by_user?: User;
    approved_by?: number;
    approved_by_user?: User;
    approved_at?: string;
    approval_notes?: string;
    notes?: string;
    items?: BomItem[];
    created_at: string;
    updated_at: string;
}

export interface BomItem {
    id: number;
    bill_of_material_id: number;
    product_id: number;
    product?: Product;
    item_name: string;
    description?: string;
    quantity_required: number;
    unit: string;
    unit_cost: number;
    total_cost: number;
    sequence: number;
    is_critical: boolean;
    notes?: string;
    created_at: string;
    updated_at: string;
}

export interface ProductionPlan {
    id: number;
    company_id: number;
    plan_number: string;
    name: string;
    description?: string;
    product_id: number;
    product?: Product;
    bill_of_material_id?: number;
    bill_of_material?: BillOfMaterial;
    planned_quantity: number;
    unit: string;
    start_date: string;
    end_date: string;
    due_date: string;
    priority: 'low' | 'medium' | 'high' | 'urgent';
    status: 'draft' | 'approved' | 'in_progress' | 'completed' | 'cancelled';
    estimated_cost: number;
    actual_cost: number;
    warehouse_id?: number;
    warehouse?: Warehouse;
    created_by: number;
    created_by_user?: User;
    approved_by?: number;
    approved_by_user?: User;
    approved_at?: string;
    approval_notes?: string;
    notes?: string;
    work_orders?: WorkOrder[];
    created_at: string;
    updated_at: string;
}

export interface WorkOrder {
    id: number;
    company_id: number;
    work_order_number: string;
    name: string;
    description?: string;
    production_plan_id?: number;
    production_plan?: ProductionPlan;
    product_id: number;
    product?: Product;
    bill_of_material_id?: number;
    bill_of_material?: BillOfMaterial;
    planned_quantity: number;
    completed_quantity: number;
    unit: string;
    start_date: string;
    due_date: string;
    priority: 'low' | 'medium' | 'high' | 'urgent';
    status: 'draft' | 'approved' | 'in_progress' | 'paused' | 'completed' | 'cancelled';
    estimated_hours: number;
    actual_hours: number;
    estimated_cost: number;
    actual_cost: number;
    warehouse_id?: number;
    warehouse?: Warehouse;
    work_center_id?: number;
    work_center?: WorkCenter;
    assigned_to?: number;
    assigned_to_user?: User;
    created_by: number;
    created_by_user?: User;
    approved_by?: number;
    approved_by_user?: User;
    approved_at?: string;
    started_at?: string;
    completed_at?: string;
    approval_notes?: string;
    notes?: string;
    production_tracking?: ProductionTracking[];
    production_costs?: ProductionCost[];
    created_at: string;
    updated_at: string;
}

export interface WorkCenter {
    id: number;
    company_id: number;
    work_center_code: string;
    name: string;
    description?: string;
    location?: string;
    type: 'machine' | 'assembly_line' | 'workstation' | 'department';
    capacity_per_hour: number;
    efficiency_rate: number;
    hourly_rate: number;
    setup_time: number;
    teardown_time: number;
    is_active: boolean;
    supervisor_id?: number;
    supervisor?: User;
    notes?: string;
    created_at: string;
    updated_at: string;
}

export interface ProductionTracking {
    id: number;
    company_id: number;
    work_order_id: number;
    work_order?: WorkOrder;
    work_center_id?: number;
    work_center?: WorkCenter;
    operator_id?: number;
    operator?: User;
    operation_type: 'setup' | 'production' | 'inspection' | 'maintenance' | 'break';
    start_time: string;
    end_time?: string;
    duration_minutes: number;
    quantity_produced: number;
    quantity_rejected: number;
    rejection_reason?: string;
    status: 'in_progress' | 'completed' | 'paused' | 'cancelled';
    notes?: string;
    production_costs?: ProductionCost[];
    created_at: string;
    updated_at: string;
}

export interface ProductionCost {
    id: number;
    company_id: number;
    work_order_id: number;
    work_order?: WorkOrder;
    production_tracking_id?: number;
    production_tracking?: ProductionTracking;
    cost_type: 'material' | 'labor' | 'overhead' | 'machine' | 'other';
    cost_category?: string;
    description: string;
    quantity: number;
    unit?: string;
    unit_cost: number;
    total_cost: number;
    cost_date: string;
    recorded_by: number;
    recorded_by_user?: User;
    notes?: string;
    created_at: string;
    updated_at: string;
}

export interface InventoryTransaction {
    id: number;
    company_id: number;
    product_id: number;
    product?: Product;
    created_by: number;
    createdBy?: User;
    transaction_number: string;
    type: 'in' | 'out' | 'adjustment' | 'transfer' | 'return' | 'damage';
    quantity: number;
    unit_cost?: number;
    total_cost?: number;
    notes?: string;
    reference_type?: string;
    reference_id?: number;
    transaction_date: string;
    warehouse_id?: number;
    warehouse?: Warehouse;
    warehouse_location_id?: number;
    warehouseLocation?: WarehouseLocation;
    product_lot_id?: number;
    productLot?: ProductLot;
    serial_numbers?: string[];
    triggers_reorder: boolean;
    created_at: string;
    updated_at: string;
}

export interface ChartOfAccount {
    id: number;
    account_code: string;
    name: string;
    type: 'asset' | 'liability' | 'equity' | 'revenue' | 'expense';
    parent_id?: number;
    parent?: ChartOfAccount;
    children?: ChartOfAccount[];
    description?: string;
    balance?: number;
    status: 'active' | 'inactive';
    created_by?: number;
    created_at: string;
    updated_at: string;
}

export interface JournalEntry {
    id: number;
    entry_number: string;
    entry_date: string;
    reference_type?: string;
    reference_id?: number;
    description?: string;
    total_debit: number;
    total_credit: number;
    status: 'draft' | 'posted' | 'cancelled';
    posted_at?: string;
    created_at: string;
    updated_at: string;
    created_by_user?: User;
    lines?: JournalEntryLine[];
}

export interface JournalEntryLine {
    id?: number;
    journal_entry_id?: number;
    account_id: number;
    account?: ChartOfAccount;
    description?: string;
    debit_amount: number;
    credit_amount: number;
}

export interface Invoice {
    id: number;
    invoice_number: string;
    customer_id: number;
    customer?: Customer;
    invoice_date: string;
    due_date: string;
    status: 'draft' | 'sent' | 'paid' | 'overdue' | 'cancelled';
    subtotal: number;
    tax_amount: number;
    discount_amount: number;
    total_amount: number;
    paid_amount: number;
    balance_amount: number;
    notes?: string;
    created_at: string;
    updated_at: string;
    items?: InvoiceItem[];
}

export interface InvoiceItem {
    id?: number;
    invoice_id?: number;
    product_id: number;
    product?: Product;
    description?: string;
    quantity: number;
    unit_price: number;
    discount_percentage: number;
    tax_percentage: number;
    total_amount: number;
}

export interface Payment {
    id: number;
    payment_number: string;
    customer_id: number;
    customer?: Customer;
    invoice_id?: number;
    invoice?: Invoice;
    payment_date: string;
    payment_method: 'cash' | 'check' | 'bank_transfer' | 'credit_card' | 'other';
    reference_number?: string;
    amount: number;
    notes?: string;
    created_at: string;
    updated_at: string;
}

// Accounts Payable Types
export interface Bill {
    id: number;
    bill_number: string;
    supplier_id: number;
    supplier?: Supplier;
    bill_date: string;
    due_date: string;
    status: 'draft' | 'received' | 'paid' | 'overdue' | 'cancelled';
    subtotal: number;
    tax_amount: number;
    discount_amount: number;
    total_amount: number;
    paid_amount: number;
    balance_amount: number;
    notes?: string;
    created_at: string;
    updated_at: string;
    items?: BillItem[];
}

export interface BillItem {
    id?: number;
    bill_id?: number;
    product_id: number;
    product?: Product;
    description?: string;
    quantity: number;
    unit_price: number;
    discount_percentage: number;
    tax_percentage: number;
    total_amount: number;
}

export interface BillPayment {
    id: number;
    payment_number: string;
    supplier_id: number;
    supplier?: Supplier;
    bill_id?: number;
    bill?: Bill;
    payment_date: string;
    payment_method: 'cash' | 'check' | 'bank_transfer' | 'credit_card' | 'other';
    reference_number?: string;
    amount: number;
    notes?: string;
    created_at: string;
    updated_at: string;
}

// Tax Management Types
export interface TaxRate {
    id: number;
    name: string;
    rate: number;
    description?: string;
    is_active: boolean;
    created_at: string;
    updated_at: string;
}

export interface TaxTransaction {
    id: number;
    transaction_number: string;
    transaction_type: 'sale' | 'purchase' | 'adjustment';
    reference_type: string;
    reference_id: number;
    tax_rate_id: number;
    tax_rate?: TaxRate;
    taxable_amount: number;
    tax_amount: number;
    transaction_date: string;
    status: 'pending' | 'filed' | 'paid';
    notes?: string;
    created_at: string;
    updated_at: string;
}

// Bank Reconciliation Types
export interface BankAccount {
    id: number;
    account_number: string;
    account_name: string;
    bank_name: string;
    account_type: 'checking' | 'savings' | 'credit';
    opening_balance: number;
    current_balance: number;
    is_active: boolean;
    created_at: string;
    updated_at: string;
}

export interface BankTransaction {
    id: number;
    transaction_number: string;
    bank_account_id: number;
    bank_account?: BankAccount;
    transaction_date: string;
    description: string;
    reference_number?: string;
    debit_amount: number;
    credit_amount: number;
    balance: number;
    is_reconciled: boolean;
    reconciled_at?: string;
    notes?: string;
    created_at: string;
    updated_at: string;
}

export interface BankReconciliation {
    id: number;
    reconciliation_number: string;
    bank_account_id: number;
    bank_account?: BankAccount;
    reconciliation_date: string;
    opening_balance: number;
    closing_balance: number;
    book_balance: number;
    bank_balance: number;
    difference: number;
    status: 'draft' | 'completed';
    notes?: string;
    created_at: string;
    updated_at: string;
    items?: BankReconciliationItem[];
}

export interface BankReconciliationItem {
    id: number;
    bank_reconciliation_id: number;
    bank_transaction_id?: number;
    bank_transaction?: BankTransaction;
    journal_entry_id?: number;
    journal_entry?: JournalEntry;
    description: string;
    amount: number;
    type: 'bank' | 'book';
    is_matched: boolean;
    matched_with?: number;
}

// Financial Reports Types
export interface BalanceSheet {
    assets: BalanceSheetSection;
    liabilities: BalanceSheetSection;
    equity: BalanceSheetSection;
    total_assets: number;
    total_liabilities: number;
    total_equity: number;
    report_date: string;
}

export interface BalanceSheetSection {
    accounts: BalanceSheetAccount[];
    total: number;
}

export interface BalanceSheetAccount {
    account_code: string;
    account_name: string;
    balance: number;
    account_type: string;
}

export interface IncomeStatement {
    revenue: IncomeStatementSection;
    expenses: IncomeStatementSection;
    gross_profit: number;
    net_income: number;
    report_period: string;
    start_date: string;
    end_date: string;
}

export interface IncomeStatementSection {
    accounts: IncomeStatementAccount[];
    total: number;
}

export interface IncomeStatementAccount {
    account_code: string;
    account_name: string;
    amount: number;
    account_type: string;
}

// Dashboard Types
export interface DashboardStats {
    total_products: number;
    total_customers: number;
    total_suppliers: number;
    total_sales_orders: number;
    total_purchase_orders: number;
    low_stock_products: number;
    total_sales: number;
    total_purchases: number;
    inventory_value: number;
    recent_sales_orders: SalesOrder[];
    low_stock_products_list: Product[];
    sales_trend: {
        months: string[];
        sales: number[];
    };
    recent_transactions: Array<{
        id: number;
        type: 'sale' | 'purchase';
        amount: number;
        description: string;
        customer?: string;
        supplier?: string;
        date: string;
        status: string;
    }>;
}

// Pagination Types
export interface PaginatedData<T> {
    data: T[];
    links: PaginationLinks[];
    meta: PaginationMeta;
}

export interface PaginationLinks {
    url?: string;
    label: string;
    active: boolean;
}

export interface PaginationMeta {
    current_page: number;
    from: number;
    last_page: number;
    per_page: number;
    to: number;
    total: number;
}

// HR Module Types
export interface Department {
    id: number;
    company_id: number;
    department_code: string;
    name: string;
    description?: string;
    manager_id?: number;
    manager?: Employee;
    location?: string;
    phone?: string;
    email?: string;
    budget?: number;
    is_active: boolean;
    created_at: string;
    updated_at: string;
}

export interface Position {
    id: number;
    company_id: number;
    position_code: string;
    title: string;
    description?: string;
    department_id?: number;
    department?: Department;
    job_level?: string;
    job_requirements?: string;
    responsibilities?: string;
    min_salary?: number;
    max_salary?: number;
    is_active: boolean;
    created_at: string;
    updated_at: string;
}

export interface Employee {
    id: number;
    company_id: number;
    employee_number: string;
    first_name: string;
    last_name: string;
    email: string;
    phone?: string;
    birth_date?: string;
    gender?: 'male' | 'female' | 'other';
    address?: string;
    city?: string;
    state?: string;
    postal_code?: string;
    country?: string;
    national_id?: string;
    tax_number?: string;
    bank_name?: string;
    bank_account_number?: string;
    bank_routing_number?: string;
    emergency_contact_name?: string;
    emergency_contact_phone?: string;
    emergency_contact_relationship?: string;
    department_id?: number;
    department?: Department;
    position_id?: number;
    position?: Position;
    supervisor_id?: number;
    supervisor?: Employee;
    hire_date: string;
    contract_start_date?: string;
    contract_end_date?: string;
    employment_status: 'active' | 'inactive' | 'terminated' | 'resigned' | 'retired';
    employment_type: 'full_time' | 'part_time' | 'contract' | 'intern' | 'temporary';
    base_salary: number;
    currency: string;
    pay_frequency: 'weekly' | 'bi_weekly' | 'monthly' | 'quarterly' | 'yearly';
    start_time?: string;
    end_time?: string;
    working_hours_per_day?: number;
    working_days_per_week?: number;
    work_location?: string;
    is_remote: boolean;
    user_id?: number;
    user?: User;
    is_active: boolean;
    created_at: string;
    updated_at: string;
}

export interface LeaveType {
    id: number;
    company_id: number;
    name: string;
    description?: string;
    color?: string;
    default_days_per_year: number;
    is_paid: boolean;
    requires_approval: boolean;
    requires_document: boolean;
    can_carry_forward: boolean;
    max_carry_forward_days?: number;
    is_active: boolean;
    sort_order: number;
    created_at: string;
    updated_at: string;
}

export interface LeaveRequest {
    id: number;
    company_id: number;
    employee_id: number;
    employee?: Employee;
    leave_type_id: number;
    leave_type?: LeaveType;
    request_number: string;
    start_date: string;
    end_date: string;
    start_time?: string;
    end_time?: string;
    total_days: number;
    total_hours?: number;
    leave_duration: 'full_day' | 'half_day' | 'hours';
    reason: string;
    additional_notes?: string;
    attachment_file?: string;
    status: 'pending' | 'approved' | 'rejected' | 'cancelled';
    approved_by?: number;
    approved_by_user?: User;
    approved_at?: string;
    approval_notes?: string;
    rejection_reason?: string;
    days_taken?: number;
    days_remaining?: number;
    days_carried_forward?: number;
    is_urgent: boolean;
    created_at: string;
    updated_at: string;
}

export interface PayrollPeriod {
    id: number;
    company_id: number;
    period_code: string;
    name: string;
    start_date: string;
    end_date: string;
    pay_date: string;
    frequency: 'daily' | 'weekly' | 'bi_weekly' | 'monthly' | 'quarterly' | 'yearly';
    status: 'draft' | 'processing' | 'approved' | 'paid' | 'cancelled';
    total_employees?: number;
    total_gross_pay?: number;
    total_net_pay?: number;
    total_tax?: number;
    total_deductions?: number;
    total_allowances?: number;
    notes?: string;
    created_by?: number;
    created_by_user?: User;
    approved_by?: number;
    approved_by_user?: User;
    approved_at?: string;
    created_at: string;
    updated_at: string;
}

export interface PayrollRecord {
    id: number;
    company_id: number;
    payroll_period_id: number;
    payroll_period?: PayrollPeriod;
    employee_id: number;
    employee?: Employee;
    payroll_number: string;
    base_salary: number;
    basic_pay: number;
    working_days: number;
    present_days: number;
    absent_days: number;
    leave_days: number;
    overtime_hours: number;
    overtime_pay: number;
    transport_allowance: number;
    meal_allowance: number;
    housing_allowance: number;
    medical_allowance: number;
    other_allowances: number;
    total_allowances: number;
    tax_deduction: number;
    social_security: number;
    health_insurance: number;
    loan_deduction: number;
    advance_deduction: number;
    other_deductions: number;
    total_deductions: number;
    gross_pay: number;
    net_pay: number;
    notes?: string;
    status: 'draft' | 'approved' | 'paid' | 'cancelled';
    approved_by?: number;
    approved_by_user?: User;
    approved_at?: string;
    payment_date?: string;
    payment_method?: string;
    bank_account?: string;
    created_at: string;
    updated_at: string;
}

export interface PerformanceReview {
    id: number;
    company_id: number;
    employee_id: number;
    employee?: Employee;
    review_number: string;
    title: string;
    description?: string;
    review_start_date: string;
    review_end_date: string;
    review_type: 'annual' | 'probation' | 'promotion' | 'special';
    status: 'draft' | 'in_progress' | 'completed' | 'approved' | 'cancelled';
    reviewer_id?: number;
    reviewer?: User;
    second_reviewer_id?: number;
    second_reviewer?: User;
    hr_reviewer_id?: number;
    hr_reviewer?: User;
    self_assessment?: string;
    self_rating?: number;
    manager_assessment?: string;
    manager_rating?: number;
    strengths?: string;
    areas_for_improvement?: string;
    goals_achieved?: string;
    goals_not_achieved?: string;
    final_rating?: number;
    performance_level?: string;
    overall_comments?: string;
    recommendations?: string;
    action_plan?: string;
    training_needs?: string;
    career_development?: string;
    salary_increase_percentage?: number;
    bonus_amount?: number;
    approved_by?: number;
    approved_by_user?: User;
    approved_at?: string;
    approval_notes?: string;
    employee_acknowledged: boolean;
    acknowledged_at?: string;
    employee_comments?: string;
    created_at: string;
    updated_at: string;
}

export interface EmployeeBenefit {
    id: number;
    company_id: number;
    employee_id: number;
    employee?: Employee;
    benefit_name: string;
    description?: string;
    benefit_type: 'health' | 'dental' | 'vision' | 'life' | 'disability' | 'retirement' | 'transport' | 'meal' | 'housing' | 'education' | 'other';
    calculation_type: 'fixed' | 'percentage';
    amount?: number;
    percentage?: number;
    currency: string;
    effective_date: string;
    expiry_date?: string;
    frequency: 'one_time' | 'monthly' | 'quarterly' | 'yearly';
    is_taxable: boolean;
    is_active: boolean;
    notes?: string;
    approved_by?: number;
    approved_by_user?: User;
    approved_at?: string;
    created_at: string;
    updated_at: string;
}

export interface TrainingProgram {
    id: number;
    company_id: number;
    program_code: string;
    title: string;
    description?: string;
    objectives?: string;
    curriculum?: string;
    training_type: 'internal' | 'external' | 'online' | 'workshop' | 'seminar' | 'certification';
    category: 'technical' | 'soft_skills' | 'leadership' | 'compliance' | 'safety' | 'other';
    instructor?: string;
    institution?: string;
    location?: string;
    duration_hours: number;
    max_participants?: number;
    cost_per_participant: number;
    currency: string;
    start_date: string;
    end_date: string;
    status: 'draft' | 'scheduled' | 'in_progress' | 'completed' | 'cancelled';
    is_mandatory: boolean;
    requires_certification: boolean;
    prerequisites?: string;
    materials?: string;
    evaluation_criteria?: string;
    created_by?: number;
    created_by_user?: User;
    created_at: string;
    updated_at: string;
}

export interface EmployeeDocument {
    id: number;
    company_id: number;
    employee_id: number;
    employee?: Employee;
    document_number: string;
    title: string;
    description?: string;
    document_type: 'contract' | 'id_card' | 'passport' | 'visa' | 'certificate' | 'diploma' | 'medical' | 'insurance' | 'tax' | 'other';
    file_name: string;
    file_path: string;
    file_type: string;
    file_size: number;
    mime_type: string;
    issue_date?: string;
    expiry_date?: string;
    is_required: boolean;
    is_verified: boolean;
    verified_by?: number;
    verified_by_user?: User;
    verified_at?: string;
    verification_notes?: string;
    status: 'active' | 'expired' | 'pending_verification' | 'rejected';
    notes?: string;
    uploaded_by?: number;
    uploaded_by_user?: User;
    created_at: string;
    updated_at: string;
}

// HR Form Types
export interface EmployeeForm {
    employee_number: string;
    first_name: string;
    last_name: string;
    email: string;
    phone?: string;
    birth_date?: string;
    gender?: 'male' | 'female' | 'other';
    address?: string;
    city?: string;
    state?: string;
    postal_code?: string;
    country?: string;
    national_id?: string;
    tax_number?: string;
    bank_name?: string;
    bank_account_number?: string;
    bank_routing_number?: string;
    emergency_contact_name?: string;
    emergency_contact_phone?: string;
    emergency_contact_relationship?: string;
    department_id?: number;
    position_id?: number;
    supervisor_id?: number;
    hire_date: string;
    contract_start_date?: string;
    contract_end_date?: string;
    employment_status: 'active' | 'inactive' | 'terminated' | 'resigned' | 'retired';
    employment_type: 'full_time' | 'part_time' | 'contract' | 'intern' | 'temporary';
    base_salary: number;
    currency: string;
    pay_frequency: 'weekly' | 'bi_weekly' | 'monthly' | 'quarterly' | 'yearly';
    start_time?: string;
    end_time?: string;
    working_hours_per_day?: number;
    working_days_per_week?: number;
    work_location?: string;
    is_remote: boolean;
    user_id?: number;
    is_active: boolean;
}

export interface LeaveTypeForm {
    name: string;
    description?: string;
    color?: string;
    default_days_per_year: number;
    is_paid: boolean;
    requires_approval: boolean;
    requires_document: boolean;
    can_carry_forward: boolean;
    max_carry_forward_days?: number;
    is_active: boolean;
    sort_order?: number;
}

export interface LeaveRequestForm {
    employee_id: number;
    leave_type_id: number;
    start_date: string;
    end_date: string;
    start_time?: string;
    end_time?: string;
    leave_duration: 'full_day' | 'half_day' | 'hours';
    reason: string;
    additional_notes?: string;
    attachment_file?: string;
    is_urgent: boolean;
}

export interface PayrollPeriodForm {
    period_code: string;
    name: string;
    start_date: string;
    end_date: string;
    pay_date: string;
    frequency: 'daily' | 'weekly' | 'bi_weekly' | 'monthly' | 'quarterly' | 'yearly';
    notes?: string;
}
