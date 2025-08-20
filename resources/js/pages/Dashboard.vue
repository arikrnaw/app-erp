<template>

    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Dashboard</h1>
                    <p class="text-muted-foreground">
                        Welcome back! Here's what's happening with your business today.
                    </p>
                </div>
                <div class="flex items-center space-x-2">
                    <Button variant="outline" size="sm">
                        <Download class="h-4 w-4 mr-2" />
                        Export Report
                    </Button>
                </div>
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="flex justify-center items-center py-12">
                <div class="flex items-center space-x-2">
                    <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-primary"></div>
                    <span class="text-muted-foreground">Loading dashboard data...</span>
                </div>
            </div>

            <!-- Error State -->
            <Alert v-else-if="error" variant="destructive">
                <AlertCircle class="h-4 w-4" />
                <AlertTitle>Error</AlertTitle>
                <AlertDescription>{{ error }}</AlertDescription>
            </Alert>

            <!-- Dashboard Content -->
            <div v-else class="space-y-6">
                <!-- Company Info Card -->
                <Card v-if="company" class="bg-gradient-to-r from-primary/10 to-primary/5 border-primary/20">
                    <CardContent class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-2xl font-bold text-primary">{{ company.name }}</h2>
                                <p class="text-muted-foreground mt-1">
                                    {{ company.email }} • {{ company.phone }}
                                </p>
                                <p class="text-muted-foreground text-sm">{{ company.address }}</p>
                            </div>
                            <div class="hidden md:block">
                                <Badge variant="secondary" class="text-sm">
                                    <CheckCircle class="h-3 w-3 mr-1" />
                                    Active
                                </Badge>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Key Metrics -->
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <Card>
                        <CardContent class="p-6">
                            <div class="flex items-center space-x-4">
                                <div class="p-2 bg-blue-100 dark:bg-blue-900/20 rounded-lg">
                                    <Package class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                                </div>
                                <div class="space-y-1">
                                    <p class="text-sm font-medium text-muted-foreground">Total Products</p>
                                    <p class="text-2xl font-bold">{{ stats?.total_products || 0 }}</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardContent class="p-6">
                            <div class="flex items-center space-x-4">
                                <div class="p-2 bg-green-100 dark:bg-green-900/20 rounded-lg">
                                    <Users class="h-6 w-6 text-green-600 dark:text-green-400" />
                                </div>
                                <div class="space-y-1">
                                    <p class="text-sm font-medium text-muted-foreground">Total Customers</p>
                                    <p class="text-2xl font-bold">{{ stats?.total_customers || 0 }}</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardContent class="p-6">
                            <div class="flex items-center space-x-4">
                                <div class="p-2 bg-purple-100 dark:bg-purple-900/20 rounded-lg">
                                    <Truck class="h-6 w-6 text-purple-600 dark:text-purple-400" />
                                </div>
                                <div class="space-y-1">
                                    <p class="text-sm font-medium text-muted-foreground">Total Suppliers</p>
                                    <p class="text-2xl font-bold">{{ stats?.total_suppliers || 0 }}</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardContent class="p-6">
                            <div class="flex items-center space-x-4">
                                <div class="p-2 bg-orange-100 dark:bg-orange-900/20 rounded-lg">
                                    <AlertTriangle class="h-6 w-6 text-orange-600 dark:text-orange-400" />
                                </div>
                                <div class="space-y-1">
                                    <p class="text-sm font-medium text-muted-foreground">Low Stock Items</p>
                                    <p class="text-2xl font-bold">{{ stats?.low_stock_products || 0 }}</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Financial Overview -->
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <Card>
                        <CardContent class="p-6">
                            <div class="flex items-center space-x-4">
                                <div class="p-2 bg-emerald-100 dark:bg-emerald-900/20 rounded-lg">
                                    <TrendingUp class="h-6 w-6 text-emerald-600 dark:text-emerald-400" />
                                </div>
                                <div class="space-y-1">
                                    <p class="text-sm font-medium text-muted-foreground">Total Sales</p>
                                    <p class="text-2xl font-bold">{{ formatCurrency(stats?.total_sales || 0) }}</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardContent class="p-6">
                            <div class="flex items-center space-x-4">
                                <div class="p-2 bg-rose-100 dark:bg-rose-900/20 rounded-lg">
                                    <TrendingDown class="h-6 w-6 text-rose-600 dark:text-rose-400" />
                                </div>
                                <div class="space-y-1">
                                    <p class="text-sm font-medium text-muted-foreground">Total Purchases</p>
                                    <p class="text-2xl font-bold">{{ formatCurrency(stats?.total_purchases || 0) }}</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardContent class="p-6">
                            <div class="flex items-center space-x-4">
                                <div class="p-2 bg-cyan-100 dark:bg-cyan-900/20 rounded-lg">
                                    <Warehouse class="h-6 w-6 text-cyan-600 dark:text-cyan-400" />
                                </div>
                                <div class="space-y-1">
                                    <p class="text-sm font-medium text-muted-foreground">Inventory Value</p>
                                    <p class="text-2xl font-bold">{{ formatCurrency(stats?.inventory_value || 0) }}</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardContent class="p-6">
                            <div class="flex items-center space-x-4">
                                <div class="p-2 bg-indigo-100 dark:bg-indigo-900/20 rounded-lg">
                                    <Calculator class="h-6 w-6 text-indigo-600 dark:text-indigo-400" />
                                </div>
                                <div class="space-y-1">
                                    <p class="text-sm font-medium text-muted-foreground">Net Income</p>
                                    <p class="text-2xl font-bold" :class="getNetIncomeColor()">
                                        {{ formatCurrency((stats?.total_sales || 0) - (stats?.total_purchases || 0)) }}
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Charts Section -->
                <div class="grid gap-6 lg:grid-cols-2">
                    <!-- Sales Trend Chart -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Sales Trend (Last 6 Months)</CardTitle>
                            <CardDescription>
                                Monthly sales performance overview
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="h-[300px]">
                                <AreaChart :data="salesChartData" :categories="['sales']" :index="'month'"
                                    :colors="['hsl(var(--chart-1))']" :y-formatter="formatChartCurrency"
                                    class="h-full" />
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Inventory Status Chart -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Inventory Status</CardTitle>
                            <CardDescription>
                                Current inventory levels and alerts
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="h-[300px]">
                                <AreaChart :data="inventoryChartData" :categories="['count']" :index="'status'"
                                    :colors="['hsl(var(--chart-2))', 'hsl(var(--chart-3))', 'hsl(var(--chart-4))']"
                                    class="h-full" />
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Recent Activity & Alerts -->
                <div class="grid gap-6 lg:grid-cols-2">
                    <!-- Recent Sales Orders -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Recent Sales Orders</CardTitle>
                            <CardDescription>
                                Latest sales transactions
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-4">
                                <div v-for="order in stats?.recent_sales_orders" :key="order.id"
                                    class="flex items-center space-x-4">
                                    <div class="p-2 bg-blue-100 dark:bg-blue-900/20 rounded-lg">
                                        <ShoppingCart class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                                    </div>
                                    <div class="flex-1 space-y-1">
                                        <p class="text-sm font-medium leading-none">{{ order.so_number }}</p>
                                        <p class="text-sm text-muted-foreground">{{ order.customer?.name }}</p>
                                        <p class="text-xs text-muted-foreground">{{ formatDate(order.order_date) }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-medium">{{ formatCurrency(order.total_amount) }}</p>
                                        <Badge :variant="getStatusVariant(order.status)" class="text-xs">
                                            {{ order.status }}
                                        </Badge>
                                    </div>
                                </div>
                                <div v-if="!stats?.recent_sales_orders?.length" class="text-center py-8">
                                    <Package class="h-8 w-8 text-muted-foreground mx-auto mb-2" />
                                    <p class="text-sm text-muted-foreground">No recent sales orders</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Low Stock Products -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Low Stock Alerts</CardTitle>
                            <CardDescription>
                                Products that need reordering
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-4">
                                <div v-for="product in stats?.low_stock_products_list" :key="product.id"
                                    class="flex items-center space-x-4">
                                    <div class="p-2 bg-orange-100 dark:bg-orange-900/20 rounded-lg">
                                        <AlertTriangle class="h-4 w-4 text-orange-600 dark:text-orange-400" />
                                    </div>
                                    <div class="flex-1 space-y-1">
                                        <p class="text-sm font-medium leading-none">{{ product.name }}</p>
                                        <p class="text-sm text-muted-foreground">{{ product.category?.name }}</p>
                                        <p class="text-xs text-muted-foreground">SKU: {{ product.sku }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-medium text-orange-600 dark:text-orange-400">
                                            {{ product.stock_quantity }} in stock
                                        </p>
                                        <p class="text-xs text-muted-foreground">Min: {{ product.min_stock_level }}</p>
                                    </div>
                                </div>
                                <div v-if="!stats?.low_stock_products_list?.length" class="text-center py-8">
                                    <CheckCircle class="h-8 w-8 text-muted-foreground mx-auto mb-2" />
                                    <p class="text-sm text-muted-foreground">All products are well stocked</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Recent Transactions -->
                <Card>
                    <CardHeader>
                        <CardTitle>Recent Transactions</CardTitle>
                        <CardDescription>
                            Latest business activities
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div v-for="transaction in stats?.recent_transactions" :key="transaction.id"
                                class="flex items-center space-x-4">
                                <div class="p-2 rounded-lg" :class="getTransactionIconClass(transaction.type)">
                                    <component :is="getTransactionIcon(transaction.type)" class="h-4 w-4" />
                                </div>
                                <div class="flex-1 space-y-1">
                                    <p class="text-sm font-medium leading-none">{{ transaction.description }}</p>
                                    <p class="text-sm text-muted-foreground">
                                        {{ transaction.type === 'sale' ? transaction.customer : transaction.supplier }}
                                    </p>
                                    <p class="text-xs text-muted-foreground">{{ formatDate(transaction.date) }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-medium"
                                        :class="transaction.type === 'sale' ? 'text-green-600' : 'text-red-600'">
                                        {{ transaction.type === 'sale' ? '+' : '-' }}{{
                                        formatCurrency(transaction.amount) }}
                                    </p>
                                    <Badge :variant="getStatusVariant(transaction.status)" class="text-xs">
                                        {{ transaction.status }}
                                    </Badge>
                                </div>
                            </div>
                            <div v-if="!stats?.recent_transactions?.length" class="text-center py-8">
                                <Activity class="h-8 w-8 text-muted-foreground mx-auto mb-2" />
                                <p class="text-sm text-muted-foreground">No recent transactions</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import type { DashboardStats, Company, SalesOrder } from '@/types/erp';
import type { BreadcrumbItemType } from '@/types';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
    CardDescription
} from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { AreaChart } from '@/components/ui/chart-area';
import {
    Package,
    Users,
    Truck,
    AlertTriangle,
    TrendingUp,
    TrendingDown,
    Warehouse,
    Calculator,
    CheckCircle,
    ShoppingCart,
    Download,
    AlertCircle,
    Activity
} from 'lucide-vue-next';
import apiService from '@/services/api';

const loading = ref<boolean>(true);
const error = ref<string>('');
const company = ref<Company | null>(null);
const stats = ref<DashboardStats | null>(null);

// Chart data computed from API
const salesChartData = computed(() => {
    if (!stats.value?.sales_trend) return [];

    return stats.value.sales_trend.months.map((month, index) => ({
        month,
        sales: stats.value.sales_trend.sales[index]
    }));
});

const inventoryChartData = computed(() => {
    const inStock = stats.value?.total_products ? stats.value.total_products - (stats.value.low_stock_products || 0) : 0;
    const lowStock = stats.value?.low_stock_products || 0;
    const outOfStock = 0; // Could be calculated from API if needed

    return [
        { status: 'In Stock', count: inStock },
        { status: 'Low Stock', count: lowStock },
        { status: 'Out of Stock', count: outOfStock }
    ];
});

const loadDashboard = async (): Promise<void> => {
    try {
        loading.value = true;
        error.value = '';
        const data = await apiService.getDashboard();
        company.value = data.company;
        stats.value = data.stats;
    } catch (err: any) {
        error.value = err.response?.data?.message || 'Failed to load dashboard data';
    } finally {
        loading.value = false;
    }
};

const formatCurrency = (amount: number): string => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(amount);
};

const formatChartCurrency = (tick: number | Date, i: number, ticks: (number | Date)[]): string => {
    if (typeof tick === 'number') {
        return formatCurrency(tick);
    }
    return tick.toString();
};

const formatDate = (date: string): string => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

const getStatusVariant = (status: string) => {
    const variants: Record<string, string> = {
        'draft': 'secondary',
        'confirmed': 'default',
        'shipped': 'default',
        'delivered': 'default',
        'cancelled': 'destructive'
    };
    return variants[status] || 'secondary';
};

const getNetIncomeColor = (): string => {
    const netIncome = (stats.value?.total_sales || 0) - (stats.value?.total_purchases || 0);
    return netIncome >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400';
};

const getTransactionIcon = (type: string) => {
    return type === 'sale' ? ShoppingCart : Truck;
};

const getTransactionIconClass = (type: string): string => {
    return type === 'sale'
        ? 'bg-green-100 dark:bg-green-900/20'
        : 'bg-blue-100 dark:bg-blue-900/20';
};

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Dashboard', href: '/dashboard' }
];

onMounted(() => {
    loadDashboard();
});
</script>
