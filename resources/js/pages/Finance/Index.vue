<template>
    <Head title="Finance Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 space-y-6">
            <!-- Header Section -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Finance Dashboard</h1>
                    <p class="text-muted-foreground mt-1">
                        Overview of your company's financial performance and accounting data
                    </p>
                </div>
                <div class="flex items-center space-x-2">
                    <Button variant="outline" size="sm">
                        <Download class="h-4 w-4 mr-2" />
                        Export Report
                    </Button>
                    <Button variant="outline" size="sm">
                        <Calendar class="h-4 w-4 mr-2" />
                        This Month
                    </Button>
                </div>
            </div>

            <!-- Key Financial Metrics -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center space-x-4">
                            <div class="p-2 bg-green-100 dark:bg-green-900/20 rounded-lg">
                                <TrendingUp class="h-6 w-6 text-green-600 dark:text-green-400" />
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-muted-foreground">Total Revenue</p>
                                <p class="text-2xl font-bold">{{ formatCurrency(totalRevenue) }}</p>
                                <p class="text-xs text-green-600 dark:text-green-400">+12.5% from last month</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center space-x-4">
                            <div class="p-2 bg-red-100 dark:bg-red-900/20 rounded-lg">
                                <TrendingDown class="h-6 w-6 text-red-600 dark:text-red-400" />
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-muted-foreground">Total Expenses</p>
                                <p class="text-2xl font-bold">{{ formatCurrency(totalExpenses) }}</p>
                                <p class="text-xs text-red-600 dark:text-red-400">+8.2% from last month</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center space-x-4">
                            <div class="p-2 bg-blue-100 dark:bg-blue-900/20 rounded-lg">
                                <Calculator class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-muted-foreground">Net Income</p>
                                <p class="text-2xl font-bold" :class="netIncomeColor">{{ formatCurrency(netIncome) }}</p>
                                <p class="text-xs" :class="netIncomeColor">{{ netIncomePercentage }}% from last month</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center space-x-4">
                            <div class="p-2 bg-purple-100 dark:bg-purple-900/20 rounded-lg">
                                <Wallet class="h-6 w-6 text-purple-600 dark:text-purple-400" />
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-muted-foreground">Cash Balance</p>
                                <p class="text-2xl font-bold">{{ formatCurrency(cashBalance) }}</p>
                                <p class="text-xs text-purple-600 dark:text-purple-400">Current balance</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Charts Section -->
            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Revenue vs Expenses Chart -->
                <Card>
                    <CardHeader>
                        <CardTitle>Revenue vs Expenses</CardTitle>
                        <CardDescription>
                            Monthly comparison of revenue and expenses
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="h-[300px]">
                            <AreaChart 
                                :data="revenueExpensesData" 
                                :categories="['revenue', 'expenses']" 
                                :index="'month'"
                                :colors="['hsl(var(--chart-1))', 'hsl(var(--chart-2))']" 
                                :y-formatter="formatChartCurrency" 
                                class="h-full" 
                            />
                        </div>
                    </CardContent>
                </Card>

                <!-- Account Balances Chart -->
                <Card>
                    <CardHeader>
                        <CardTitle>Account Balances</CardTitle>
                        <CardDescription>
                            Distribution of account balances by type
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="h-[300px]">
                            <AreaChart 
                                :data="accountBalancesData" 
                                :categories="['balance']" 
                                :index="'type'"
                                :colors="['hsl(var(--chart-3))', 'hsl(var(--chart-4))', 'hsl(var(--chart-5))']" 
                                class="h-full" 
                            />
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Quick Actions -->
            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Recent Transactions -->
                <Card>
                    <CardHeader>
                        <CardTitle>Recent Transactions</CardTitle>
                        <CardDescription>
                            Latest journal entries and financial activities
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div v-for="transaction in recentTransactions" :key="transaction.id" class="flex items-center space-x-4">
                                <div class="p-2 rounded-lg" :class="getTransactionIconClass(transaction.type)">
                                    <component :is="getTransactionIcon(transaction.type)" class="h-4 w-4" />
                                </div>
                                <div class="flex-1 space-y-1">
                                    <p class="text-sm font-medium leading-none">{{ transaction.description }}</p>
                                    <p class="text-sm text-muted-foreground">{{ transaction.date }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-medium" :class="transaction.amount > 0 ? 'text-green-600' : 'text-red-600'">
                                        {{ transaction.amount > 0 ? '+' : '' }}{{ formatCurrency(transaction.amount) }}
                                    </p>
                                    <Badge :variant="getStatusVariant(transaction.status)" class="text-xs">
                                        {{ transaction.status }}
                                    </Badge>
                                </div>
                            </div>
                            <div v-if="recentTransactions.length === 0" class="text-center py-8">
                                <FileText class="h-8 w-8 text-muted-foreground mx-auto mb-2" />
                                <p class="text-sm text-muted-foreground">No recent transactions</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Quick Actions -->
                <Card>
                    <CardHeader>
                        <CardTitle>Quick Actions</CardTitle>
                        <CardDescription>
                            Common finance tasks and shortcuts
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="grid gap-3">
                            <Link :href="route('finance.journal-entries.create')">
                                <Button variant="outline" class="w-full justify-start">
                                    <Plus class="h-4 w-4 mr-2" />
                                    Create Journal Entry
                                </Button>
                            </Link>
                            <Link :href="route('finance.chart-of-accounts.create')">
                                <Button variant="outline" class="w-full justify-start">
                                    <BookOpen class="h-4 w-4 mr-2" />
                                    Add Account
                                </Button>
                            </Link>
                            <Link :href="route('finance.general-ledger.index')">
                                <Button variant="outline" class="w-full justify-start">
                                    <FileText class="h-4 w-4 mr-2" />
                                    View General Ledger
                                </Button>
                            </Link>
                            <Link :href="route('finance.trial-balance.index')">
                                <Button variant="outline" class="w-full justify-start">
                                    <Calculator class="h-4 w-4 mr-2" />
                                    Trial Balance
                                </Button>
                            </Link>
                            <Link :href="route('finance.reports.index')">
                                <Button variant="outline" class="w-full justify-start">
                                    <BarChart3 class="h-4 w-4 mr-2" />
                                    Financial Reports
                                </Button>
                            </Link>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Finance Modules Grid -->
            <Card>
                <CardHeader>
                    <CardTitle>Finance Modules</CardTitle>
                    <CardDescription>
                        Access all finance and accounting features
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                        <Link :href="route('finance.chart-of-accounts.index')">
                            <Card class="hover:shadow-md transition-shadow cursor-pointer">
                                <CardContent class="p-6">
                                    <div class="flex items-center space-x-4">
                                        <div class="p-2 bg-blue-100 dark:bg-blue-900/20 rounded-lg">
                                            <BookOpen class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                                        </div>
                                        <div>
                                            <h3 class="font-semibold">Chart of Accounts</h3>
                                            <p class="text-sm text-muted-foreground">Manage accounts</p>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        </Link>

                        <Link :href="route('finance.journal-entries.index')">
                            <Card class="hover:shadow-md transition-shadow cursor-pointer">
                                <CardContent class="p-6">
                                    <div class="flex items-center space-x-4">
                                        <div class="p-2 bg-green-100 dark:bg-green-900/20 rounded-lg">
                                            <FileText class="h-6 w-6 text-green-600 dark:text-green-400" />
                                        </div>
                                        <div>
                                            <h3 class="font-semibold">Journal Entries</h3>
                                            <p class="text-sm text-muted-foreground">Record transactions</p>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        </Link>

                        <Link :href="route('finance.general-ledger.index')">
                            <Card class="hover:shadow-md transition-shadow cursor-pointer">
                                <CardContent class="p-6">
                                    <div class="flex items-center space-x-4">
                                        <div class="p-2 bg-purple-100 dark:bg-purple-900/20 rounded-lg">
                                            <BookOpen class="h-6 w-6 text-purple-600 dark:text-purple-400" />
                                        </div>
                                        <div>
                                            <h3 class="font-semibold">General Ledger</h3>
                                            <p class="text-sm text-muted-foreground">View account details</p>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        </Link>

                        <Link :href="route('finance.trial-balance.index')">
                            <Card class="hover:shadow-md transition-shadow cursor-pointer">
                                <CardContent class="p-6">
                                    <div class="flex items-center space-x-4">
                                        <div class="p-2 bg-orange-100 dark:bg-orange-900/20 rounded-lg">
                                            <Calculator class="h-6 w-6 text-orange-600 dark:text-orange-400" />
                                        </div>
                                        <div>
                                            <h3 class="font-semibold">Trial Balance</h3>
                                            <p class="text-sm text-muted-foreground">Balance verification</p>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        </Link>

                        <Link :href="route('finance.accounts-receivable.index')">
                            <Card class="hover:shadow-md transition-shadow cursor-pointer">
                                <CardContent class="p-6">
                                    <div class="flex items-center space-x-4">
                                        <div class="p-2 bg-emerald-100 dark:bg-emerald-900/20 rounded-lg">
                                            <TrendingUp class="h-6 w-6 text-emerald-600 dark:text-emerald-400" />
                                        </div>
                                        <div>
                                            <h3 class="font-semibold">Accounts Receivable</h3>
                                            <p class="text-sm text-muted-foreground">Customer invoices</p>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        </Link>

                        <Link :href="route('finance.accounts-payable.index')">
                            <Card class="hover:shadow-md transition-shadow cursor-pointer">
                                <CardContent class="p-6">
                                    <div class="flex items-center space-x-4">
                                        <div class="p-2 bg-red-100 dark:bg-red-900/20 rounded-lg">
                                            <TrendingDown class="h-6 w-6 text-red-600 dark:text-red-400" />
                                        </div>
                                        <div>
                                            <h3 class="font-semibold">Accounts Payable</h3>
                                            <p class="text-sm text-muted-foreground">Supplier bills</p>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        </Link>

                        <Link :href="route('finance.cash-management.index')">
                            <Card class="hover:shadow-md transition-shadow cursor-pointer">
                                <CardContent class="p-6">
                                    <div class="flex items-center space-x-4">
                                        <div class="p-2 bg-blue-100 dark:bg-blue-900/20 rounded-lg">
                                            <Wallet class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                                        </div>
                                        <div>
                                            <h3 class="font-semibold">Cash Management</h3>
                                            <p class="text-sm text-muted-foreground">Bank accounts & petty cash</p>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        </Link>

                        <Link :href="route('finance.fixed-assets.index')">
                            <Card class="hover:shadow-md transition-shadow cursor-pointer">
                                <CardContent class="p-6">
                                    <div class="flex items-center space-x-4">
                                        <div class="p-2 bg-indigo-100 dark:bg-indigo-900/20 rounded-lg">
                                            <Building class="h-6 w-6 text-indigo-600 dark:text-indigo-400" />
                                        </div>
                                        <div>
                                            <h3 class="font-semibold">Fixed Assets</h3>
                                            <p class="text-sm text-muted-foreground">Asset management & depreciation</p>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        </Link>

                        <Link :href="route('finance.budgeting.index')">
                            <Card class="hover:shadow-md transition-shadow cursor-pointer">
                                <CardContent class="p-6">
                                    <div class="flex items-center space-x-4">
                                        <div class="p-2 bg-yellow-100 dark:bg-yellow-900/20 rounded-lg">
                                            <PieChart class="h-6 w-6 text-yellow-600 dark:text-yellow-400" />
                                        </div>
                                        <div>
                                            <h3 class="font-semibold">Budgeting</h3>
                                            <p class="text-sm text-muted-foreground">Budget planning & analysis</p>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        </Link>

                        <Link :href="route('finance.multi-currency.index')">
                            <Card class="hover:shadow-md transition-shadow cursor-pointer">
                                <CardContent class="p-6">
                                    <div class="flex items-center space-x-4">
                                        <div class="p-2 bg-green-100 dark:bg-green-900/20 rounded-lg">
                                            <Globe class="h-6 w-6 text-green-600 dark:text-green-400" />
                                        </div>
                                        <div>
                                            <h3 class="font-semibold">Multi-Currency</h3>
                                            <p class="text-sm text-muted-foreground">Exchange rates & foreign currency</p>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        </Link>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
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
import { AreaChart } from '@/components/ui/chart-area';
import { 
    TrendingUp, 
    TrendingDown, 
    Calculator, 
    Wallet, 
    Download, 
    Calendar,
    Plus,
    BookOpen,
    FileText,
    BarChart3,
    Eye,
    Edit,
    Building,
    PieChart,
    Globe
} from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Finance', href: '/finance' }
];

// Sample data - replace with real API data
const totalRevenue = ref(1250000);
const totalExpenses = ref(850000);
const cashBalance = ref(450000);

const netIncome = computed(() => totalRevenue.value - totalExpenses.value);
const netIncomeColor = computed(() => netIncome.value >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400');
const netIncomePercentage = computed(() => {
    const percentage = ((netIncome.value / totalRevenue.value) * 100).toFixed(1);
    return netIncome.value >= 0 ? `+${percentage}` : percentage;
});

// Chart data
const revenueExpensesData = computed(() => {
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
    return months.map((month, index) => ({
        month,
        revenue: 120000 + (index * 15000),
        expenses: 80000 + (index * 8000)
    }));
});

const accountBalancesData = computed(() => [
    { type: 'Assets', balance: 2500000 },
    { type: 'Liabilities', balance: 1200000 },
    { type: 'Equity', balance: 1300000 }
]);

// Recent transactions
const recentTransactions = ref([
    {
        id: 1,
        type: 'credit',
        description: 'Customer Payment - INV-001',
        amount: 50000,
        date: '2024-01-15',
        status: 'completed'
    },
    {
        id: 2,
        type: 'debit',
        description: 'Office Supplies',
        amount: -15000,
        date: '2024-01-14',
        status: 'completed'
    },
    {
        id: 3,
        type: 'credit',
        description: 'Service Revenue',
        amount: 75000,
        date: '2024-01-13',
        status: 'pending'
    }
]);

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

const getTransactionIcon = (type: string) => {
    return type === 'credit' ? TrendingUp : TrendingDown;
};

const getTransactionIconClass = (type: string): string => {
    return type === 'credit' 
        ? 'bg-green-100 dark:bg-green-900/20' 
        : 'bg-red-100 dark:bg-red-900/20';
};

const getStatusVariant = (status: string): string => {
    const variants: Record<string, string> = {
        'completed': 'default',
        'pending': 'secondary',
        'cancelled': 'destructive'
    };
    return variants[status] || 'secondary';
};
</script>
