<template>

    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="mb-6">
                <h2 class="font-semibold text-xl text-white leading-tight">
                    Dashboard
                </h2>
            </div>

            <div class="max-w-7xl mx-auto">
                <!-- Loading State -->
                <div v-if="loading" class="flex justify-center items-center py-12">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500"></div>
                </div>

                <!-- Error State -->
                <div v-else-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    {{ error }}
                </div>

                <!-- Dashboard Content -->
                <div v-else>
                    <!-- Company Info -->
                    <Card class="overflow-hidden shadow-sm sm:rounded-lg mb-6">
                        <CardContent class="p-6 text-white">
                            <h3 class="text-lg font-semibold mb-4 text-white">{{ company?.name }}</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
                                <div>
                                    <span class="font-medium">Email:</span> {{ company?.email }}
                                </div>
                                <div>
                                    <span class="font-medium">Phone:</span> {{ company?.phone }}
                                </div>
                                <div>
                                    <span class="font-medium">Address:</span> {{ company?.address }}
                                </div>
                                <div>
                                    <span class="font-medium">Status:</span>
                                    <span :class="company?.status === 'active' ? 'text-green-600' : 'text-red-600'">
                                        {{ company?.status }}
                                    </span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Statistics Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                        <Card class="overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-500">Total Products</div>
                                        <div class="text-2xl font-semibold">{{ stats?.total_products }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </Card>

                        <Card class="overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-500">Total Customers</div>
                                        <div class="text-2xl font-semibold">{{ stats?.total_customers }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </Card>

                        <Card class="overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-500">Total Suppliers</div>
                                        <div class="text-2xl font-semibold">{{ stats?.total_suppliers }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </Card>

                        <Card class="overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 bg-red-500 rounded-md flex items-center justify-center">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-500">Low Stock Products</div>
                                        <div class="text-2xl font-semibold">{{ stats?.low_stock_products
                                        }}</div>
                                    </div>
                                </div>
                            </div>
                        </Card>
                    </div>

                    <!-- Charts Section -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                        <!-- Sales Trend Chart -->
                        <Card class="overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold mb-4">Sales Trend (Last 6 Months)</h3>
                                <div class="h-64">
                                    <AreaChart :data="salesChartData" :categories="['sales']" :index="'month'"
                                        :colors="['blue']" :y-formatter="formatChartCurrency" class="h-full" />
                                </div>
                            </div>
                        </Card>

                        <!-- Inventory Status Chart -->
                        <Card class="overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold mb-4">Inventory Status</h3>
                                <div class="h-64">
                                    <AreaChart :data="inventoryChartData" :categories="['count']" :index="'status'"
                                        :colors="['green', 'yellow', 'red']" class="h-full" />
                                </div>
                            </div>
                        </Card>
                    </div>

                    <!-- Recent Activity -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Recent Sales Orders -->
                        <Card class="overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold mb-4">Recent Sales Orders</h3>

                                <!-- Sales Orders Chart -->
                                <div class="h-48 mb-4">
                                    <AreaChart :data="recentSalesChartData" :categories="['amount']" :index="'order'"
                                        :colors="['blue']" :y-formatter="formatChartCurrency" :show-legend="false"
                                        class="h-full" />
                                </div>

                                <!-- Sales Orders List -->
                                <div class="space-y-4">
                                    <div v-for="order in stats?.recent_sales_orders" :key="order.id"
                                        class="border-b border-gray-200 pb-4 last:border-b-0">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <div class="font-medium">{{ order.so_number }}</div>
                                                <div class="text-sm text-gray-500">{{ order.customer?.name }}</div>
                                                <div class="text-sm text-gray-500">{{ formatDate(order.order_date) }}
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <div class="font-medium">{{
                                                    formatCurrency(order.total_amount) }}
                                                </div>
                                                <span :class="getStatusColor(order.status)"
                                                    class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                                                    {{ order.status }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </Card>

                        <!-- Low Stock Products -->
                        <Card class="overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold mb-4">Low Stock Products</h3>

                                <!-- Stock Level Chart -->
                                <div class="h-48 mb-4">
                                    <AreaChart :data="lowStockChartData" :categories="['stock', 'min_stock']"
                                        :index="'product'" :colors="['red', 'orange']" :show-legend="true"
                                        class="h-full" />
                                </div>

                                <!-- Low Stock Products List -->
                                <div class="space-y-4">
                                    <div v-for="product in stats?.low_stock_products_list" :key="product.id"
                                        class="border-b border-gray-200 pb-4 last:border-b-0">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <div class="font-medium">{{ product.name }}</div>
                                                <div class="text-sm text-gray-500">{{ product.category?.name }}</div>
                                                <div class="text-sm text-gray-500">SKU: {{ product.sku }}</div>
                                            </div>
                                            <div class="text-right">
                                                <div class="font-medium text-red-600">{{ product.stock_quantity }} in
                                                    stock
                                                </div>
                                                <div class="text-sm text-gray-500">Min: {{ product.min_stock_level }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </Card>
                    </div>
                </div>
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
import { Card, CardContent } from '@/components/ui/card';
import { AreaChart } from '@/components/ui/chart-area';
import apiService from '@/services/api';

const loading = ref<boolean>(true);
const error = ref<string>('');
const company = ref<Company | null>(null);
const stats = ref<DashboardStats | null>(null);

// Chart data
const salesChartData = computed(() => {
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
    const sales = [12000, 19000, 15000, 25000, 22000, 30000]; // Sample data

    return months.map((month, index) => ({
        month,
        sales: sales[index]
    }));
});

const inventoryChartData = computed(() => {
    const inStock = stats.value?.total_products ? stats.value.total_products - (stats.value.low_stock_products || 0) : 0;
    const lowStock = stats.value?.low_stock_products || 0;
    const outOfStock = 5; // Sample data

    return [
        { status: 'In Stock', count: inStock },
        { status: 'Low Stock', count: lowStock },
        { status: 'Out of Stock', count: outOfStock }
    ];
});

const recentSalesChartData = computed(() => {
    if (!stats.value?.recent_sales_orders) return [];

    return stats.value.recent_sales_orders.map((order, index) => ({
        order: `SO-${order.so_number}`,
        amount: order.total_amount
    }));
});

const lowStockChartData = computed(() => {
    if (!stats.value?.low_stock_products_list) return [];

    return stats.value.low_stock_products_list.map((product, index) => ({
        product: product.name.substring(0, 15) + (product.name.length > 15 ? '...' : ''),
        stock: product.stock_quantity,
        min_stock: product.min_stock_level
    }));
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
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
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

const getStatusColor = (status: SalesOrder['status']): string => {
    const colors: Record<SalesOrder['status'], string> = {
        'draft': 'bg-gray-100',
        'confirmed': 'bg-blue-100 text-blue-800',
        'shipped': 'bg-yellow-100 text-yellow-800',
        'delivered': 'bg-green-100 text-green-800',
        'cancelled': 'bg-red-100 text-red-800'
    };
    return colors[status] || 'bg-gray-100';
};

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Dashboard', href: '/dashboard' }
];

onMounted(() => {
    loadDashboard();
});
</script>
