<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Progress } from '@/components/ui/progress';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import {
    BarChart3,
    TrendingUp,
    TrendingDown,
    DollarSign,
    FileText,
    Clock,
    Users,
    Package2,
    ShoppingCart,
    AlertTriangle,
    Plus,
    Eye,
    Download,
    Calendar,
    Target,
    PieChart
} from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

interface DashboardStats {
    total_revenue: number;
    total_expenses: number;
    net_profit: number;
    profit_margin: number;
    revenue_growth: number;
    expense_growth: number;
    total_customers: number;
    total_orders: number;
    average_order_value: number;
    customer_growth: number;
    top_products: Array<{
        name: string;
        revenue: number;
        quantity: number;
    }>;
    recent_reports: Array<{
        id: number;
        title: string;
        type: string;
        status: string;
        created_at: string;
    }>;
    upcoming_schedules: Array<{
        id: number;
        name: string;
        frequency: string;
        next_generation: string;
    }>;
}

const props = defineProps<{
    stats?: DashboardStats;
}>();

const activeTab = ref('overview');

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount);
};

const formatPercentage = (value: number) => {
    return `${value > 0 ? '+' : ''}${value.toFixed(1)}%`;
};

const getGrowthColor = (value: number) => {
    return value >= 0 ? 'text-green-600' : 'text-red-600';
};

const getGrowthIcon = (value: number) => {
    return value >= 0 ? TrendingUp : TrendingDown;
};

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Reporting & Analytics', href: '/reports' },
];
</script>

<template>

    <Head title="Reporting & Analytics Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Reporting & Analytics</h1>
                    <p class="text-muted-foreground">
                        Comprehensive insights and analytics for your business
                    </p>
                </div>
                <div class="flex gap-2">
                    <Link :href="route('reports.financial.create')">
                    <Button>
                        <Plus class="mr-2 h-4 w-4" />
                        New Report
                    </Button>
                    </Link>
                </div>
            </div>

            <!-- Key Metrics Cards -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Revenue</CardTitle>
                        <DollarSign class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatCurrency(stats?.total_revenue || 0) }}</div>
                        <div class="flex items-center text-xs text-muted-foreground">
                            <component :is="getGrowthIcon(stats?.revenue_growth || 0)" class="mr-1 h-3 w-3"
                                :class="getGrowthColor(stats?.revenue_growth || 0)" />
                            {{ formatPercentage(stats?.revenue_growth || 0) }} from last month
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Net Profit</CardTitle>
                        <TrendingUp class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatCurrency(stats?.net_profit || 0) }}</div>
                        <div class="text-xs text-muted-foreground">
                            {{ stats?.profit_margin?.toFixed(1) || 0 }}% profit margin
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Customers</CardTitle>
                        <Users class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats?.total_customers || 0 }}</div>
                        <div class="flex items-center text-xs text-muted-foreground">
                            <component :is="getGrowthIcon(stats?.customer_growth || 0)" class="mr-1 h-3 w-3"
                                :class="getGrowthColor(stats?.customer_growth || 0)" />
                            {{ formatPercentage(stats?.customer_growth || 0) }} from last month
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Orders</CardTitle>
                        <ShoppingCart class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats?.total_orders || 0 }}</div>
                        <div class="text-xs text-muted-foreground">
                            {{ formatCurrency(stats?.average_order_value || 0) }} avg. order value
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Main Content Tabs -->
            <Tabs v-model="activeTab" class="space-y-4">
                <TabsList>
                    <TabsTrigger value="overview">Overview</TabsTrigger>
                    <TabsTrigger value="reports">Recent Reports</TabsTrigger>
                    <TabsTrigger value="schedules">Scheduled Reports</TabsTrigger>
                    <TabsTrigger value="analytics">Quick Analytics</TabsTrigger>
                </TabsList>

                <TabsContent value="overview" class="space-y-4">
                    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-7">
                        <!-- Revenue vs Expenses Chart -->
                        <Card class="col-span-4">
                            <CardHeader>
                                <CardTitle>Revenue vs Expenses</CardTitle>
                                <CardDescription>
                                    Monthly comparison of revenue and expenses
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div class="h-[300px] flex items-center justify-center bg-muted/20 rounded-lg">
                                    <div class="text-center">
                                        <BarChart3 class="mx-auto h-12 w-12 text-muted-foreground" />
                                        <p class="mt-2 text-sm text-muted-foreground">Chart visualization will be
                                            displayed here</p>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Quick Actions -->
                        <Card class="col-span-3">
                            <CardHeader>
                                <CardTitle>Quick Actions</CardTitle>
                                <CardDescription>
                                    Generate reports and analytics quickly
                                </CardDescription>
                            </CardHeader>
                            <CardContent class="space-y-3">
                                <Link :href="route('reports.financial.create')">
                                <Button variant="outline" class="w-full justify-start">
                                    <FileText class="mr-2 h-4 w-4" />
                                    Create Financial Report
                                </Button>
                                </Link>
                                <Link :href="route('reports.analytics.create')">
                                <Button variant="outline" class="w-full justify-start">
                                    <TrendingUp class="mr-2 h-4 w-4" />
                                    Generate Business Analytics
                                </Button>
                                </Link>
                                <Link :href="route('reports.schedules.create')">
                                <Button variant="outline" class="w-full justify-start">
                                    <Clock class="mr-2 h-4 w-4" />
                                    Schedule Report
                                </Button>
                                </Link>
                                <Button variant="outline" class="w-full justify-start">
                                    <Download class="mr-2 h-4 w-4" />
                                    Export Data
                                </Button>
                            </CardContent>
                        </Card>
                    </div>

                    <!-- Top Products -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Top Performing Products</CardTitle>
                            <CardDescription>
                                Best selling products by revenue
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Product</TableHead>
                                        <TableHead>Revenue</TableHead>
                                        <TableHead>Quantity Sold</TableHead>
                                        <TableHead>Performance</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="product in stats?.top_products || []" :key="product.name">
                                        <TableCell class="font-medium">{{ product.name }}</TableCell>
                                        <TableCell>{{ formatCurrency(product.revenue) }}</TableCell>
                                        <TableCell>{{ product.quantity }}</TableCell>
                                        <TableCell>
                                            <Progress :value="(product.revenue / (stats?.total_revenue || 1)) * 100"
                                                class="w-20" />
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </CardContent>
                    </Card>
                </TabsContent>

                <TabsContent value="reports" class="space-y-4">
                    <Card>
                        <CardHeader>
                            <CardTitle>Recent Reports</CardTitle>
                            <CardDescription>
                                Latest financial reports and analytics
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Report</TableHead>
                                        <TableHead>Type</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead>Created</TableHead>
                                        <TableHead class="text-right">Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="report in stats?.recent_reports || []" :key="report.id">
                                        <TableCell class="font-medium">{{ report.title }}</TableCell>
                                        <TableCell>
                                            <Badge variant="outline">{{ report.type }}</Badge>
                                        </TableCell>
                                        <TableCell>
                                            <Badge :variant="report.status === 'published' ? 'default' : 'secondary'">
                                                {{ report.status }}
                                            </Badge>
                                        </TableCell>
                                        <TableCell>{{ new Date(report.created_at).toLocaleDateString() }}</TableCell>
                                        <TableCell class="text-right">
                                            <div class="flex justify-end gap-2">
                                                <Button variant="outline" size="sm">
                                                    <Eye class="h-4 w-4" />
                                                </Button>
                                                <Button variant="outline" size="sm">
                                                    <Download class="h-4 w-4" />
                                                </Button>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </CardContent>
                    </Card>
                </TabsContent>

                <TabsContent value="schedules" class="space-y-4">
                    <Card>
                        <CardHeader>
                            <CardTitle>Scheduled Reports</CardTitle>
                            <CardDescription>
                                Automated report generation schedules
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Schedule Name</TableHead>
                                        <TableHead>Frequency</TableHead>
                                        <TableHead>Next Generation</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead class="text-right">Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="schedule in stats?.upcoming_schedules || []" :key="schedule.id">
                                        <TableCell class="font-medium">{{ schedule.name }}</TableCell>
                                        <TableCell>
                                            <Badge variant="outline">{{ schedule.frequency }}</Badge>
                                        </TableCell>
                                        <TableCell>{{ new Date(schedule.next_generation).toLocaleDateString() }}
                                        </TableCell>
                                        <TableCell>
                                            <Badge variant="default">Active</Badge>
                                        </TableCell>
                                        <TableCell class="text-right">
                                            <div class="flex justify-end gap-2">
                                                <Button variant="outline" size="sm">
                                                    <Eye class="h-4 w-4" />
                                                </Button>
                                                <Button variant="outline" size="sm">
                                                    <Clock class="h-4 w-4" />
                                                </Button>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </CardContent>
                    </Card>
                </TabsContent>

                <TabsContent value="analytics" class="space-y-4">
                    <div class="grid gap-4 md:grid-cols-2">
                        <!-- Sales Analytics -->
                        <Card>
                            <CardHeader>
                                <CardTitle>Sales Analytics</CardTitle>
                                <CardDescription>
                                    Key sales performance indicators
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div class="space-y-4">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-muted-foreground">Conversion Rate</span>
                                        <span class="text-sm font-medium">12.5%</span>
                                    </div>
                                    <Progress :value="12.5" />

                                    <div class="flex justify-between">
                                        <span class="text-sm text-muted-foreground">Customer Lifetime Value</span>
                                        <span class="text-sm font-medium">{{ formatCurrency(2500000) }}</span>
                                    </div>
                                    <Progress :value="75" />

                                    <div class="flex justify-between">
                                        <span class="text-sm text-muted-foreground">Repeat Purchase Rate</span>
                                        <span class="text-sm font-medium">68%</span>
                                    </div>
                                    <Progress :value="68" />
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Financial Health -->
                        <Card>
                            <CardHeader>
                                <CardTitle>Financial Health</CardTitle>
                                <CardDescription>
                                    Key financial metrics and ratios
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div class="space-y-4">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-muted-foreground">Gross Margin</span>
                                        <span class="text-sm font-medium">42.3%</span>
                                    </div>
                                    <Progress :value="42.3" />

                                    <div class="flex justify-between">
                                        <span class="text-sm text-muted-foreground">Operating Margin</span>
                                        <span class="text-sm font-medium">18.7%</span>
                                    </div>
                                    <Progress :value="18.7" />

                                    <div class="flex justify-between">
                                        <span class="text-sm text-muted-foreground">Cash Flow Ratio</span>
                                        <span class="text-sm font-medium">1.85</span>
                                    </div>
                                    <Progress :value="85" />
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </TabsContent>
            </Tabs>
        </div>
    </AppLayout>
</template>
