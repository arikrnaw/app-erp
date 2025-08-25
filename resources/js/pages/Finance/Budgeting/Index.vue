<template>
    <Head title="Budget Management" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 space-y-6">
            <!-- Header Section -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Budget Management</h1>
                    <p class="text-muted-foreground mt-1">
                        Create, monitor, and analyze your company's budgets and forecasts
                    </p>
                </div>
                <div class="flex items-center space-x-2">
                    <Button variant="outline" size="sm" @click="exportBudgetReport" :disabled="loading">
                        <Download class="h-4 w-4 mr-2" />
                        Export Report
                    </Button>
                    <Link :href="route('finance.budgeting.create')">
                        <Button>
                            <Plus class="w-4 h-4 mr-2" />
                            Create Budget
                        </Button>
                    </Link>
                </div>
            </div>

            <!-- Budget Overview Cards -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center space-x-4">
                            <div class="p-2 bg-blue-100 dark:bg-blue-900/20 rounded-lg">
                                <FileText class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-muted-foreground">Active Budgets</p>
                                <p class="text-2xl font-bold">{{ activeBudgetsCount }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center space-x-4">
                            <div class="p-2 bg-green-100 dark:bg-green-900/20 rounded-lg">
                                <Calculator class="h-6 w-6 text-green-600 dark:text-green-400" />
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-muted-foreground">Total Budget</p>
                                <p class="text-2xl font-bold text-green-600 dark:text-green-400">
                                    {{ formatCurrency(totalBudget) }}
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center space-x-4">
                            <div class="p-2 bg-purple-100 dark:bg-purple-900/20 rounded-lg">
                                <TrendingUp class="h-6 w-6 text-purple-600 dark:text-purple-400" />
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-muted-foreground">Total Spent</p>
                                <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">
                                    {{ formatCurrency(totalSpent) }}
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center space-x-4">
                            <div class="p-2 bg-orange-100 dark:bg-orange-900/20 rounded-lg">
                                <Wallet class="h-6 w-6 text-orange-600 dark:text-orange-400" />
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-muted-foreground">Remaining</p>
                                <p class="text-2xl font-bold" :class="remainingBudgetColor">
                                    {{ formatCurrency(remainingBudget) }}
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Budget Performance Charts -->
            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Budget vs Actual Chart -->
                <Card>
                    <CardHeader>
                        <CardTitle>Budget vs Actual</CardTitle>
                        <CardDescription>
                            Monthly comparison of budgeted vs actual expenses
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="h-[300px]">
                            <AreaChart 
                                :data="budgetVsActualData" 
                                :categories="['budget', 'actual']" 
                                :index="'month'"
                                :colors="['hsl(var(--chart-1))', 'hsl(var(--chart-2))']" 
                                :y-formatter="formatChartCurrency" 
                                class="h-full" 
                            />
                        </div>
                    </CardContent>
                </Card>

                <!-- Budget Utilization by Category -->
                <Card>
                    <CardHeader>
                        <CardTitle>Budget Utilization by Category</CardTitle>
                        <CardDescription>
                            How much of each budget category has been utilized
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div v-for="category in budgetCategories" :key="category.id" 
                                 class="space-y-2">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="font-medium">{{ category.name }}</span>
                                    <span class="text-muted-foreground">
                                        {{ formatCurrency(category.spent) }} / {{ formatCurrency(category.budget) }}
                                    </span>
                                </div>
                                <div class="w-full bg-muted rounded-full h-2">
                                    <div 
                                        class="h-2 rounded-full transition-all duration-300"
                                        :class="getUtilizationColor(category.utilization)"
                                        :style="{ width: `${Math.min(category.utilization, 100)}%` }"
                                    ></div>
                                </div>
                                <div class="flex items-center justify-between text-xs text-muted-foreground">
                                    <span>{{ category.utilization.toFixed(1) }}% utilized</span>
                                    <span v-if="category.utilization > 100" class="text-red-500">
                                        {{ formatCurrency(category.overage) }} over budget
                                    </span>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Budget Periods -->
            <Card>
                <CardHeader>
                    <CardTitle>Budget Periods</CardTitle>
                    <CardDescription>
                        Manage your budget periods and fiscal years
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <div v-for="period in budgetPeriods" :key="period.id" 
                             class="flex items-center justify-between p-4 border rounded-lg hover:bg-muted/50">
                            <div class="flex items-center space-x-4">
                                <div class="p-2 bg-blue-100 dark:bg-blue-900/20 rounded-lg">
                                    <Calendar class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                                </div>
                                <div>
                                    <p class="font-medium">{{ period.name }}</p>
                                    <p class="text-sm text-muted-foreground">
                                        {{ formatDate(period.start_date) }} - {{ formatDate(period.end_date) }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-4">
                                <div class="text-right">
                                    <p class="font-semibold">{{ formatCurrency(period.total_budget) }}</p>
                                    <p class="text-sm text-muted-foreground">Total Budget</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold" :class="period.status === 'active' ? 'text-green-600' : 'text-muted-foreground'">
                                        {{ period.status }}
                                    </p>
                                    <p class="text-sm text-muted-foreground">{{ period.progress }}% complete</p>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <Link :href="route('finance.budgeting.show', period.id)">
                                        <Button variant="ghost" size="sm">
                                            <Eye class="h-4 w-4" />
                                        </Button>
                                    </Link>
                                    <Link :href="route('finance.budgeting.edit', period.id)">
                                        <Button variant="ghost" size="sm">
                                            <Edit class="h-4 w-4" />
                                        </Button>
                                    </Link>
                                </div>
                            </div>
                        </div>
                        <div v-if="budgetPeriods.length === 0" class="text-center py-8">
                            <Calendar class="h-8 w-8 text-muted-foreground mx-auto mb-2" />
                            <p class="text-muted-foreground">No budget periods found</p>
                            <Link :href="route('finance.budgeting.create')">
                                <Button variant="outline" size="sm" class="mt-2">
                                    <Plus class="h-4 w-4 mr-2" />
                                    Create First Budget
                                </Button>
                            </Link>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Variance Analysis -->
            <Card>
                <CardHeader>
                    <CardTitle>Variance Analysis</CardTitle>
                    <CardDescription>
                        Budget variances and performance indicators
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Category</TableHead>
                                    <TableHead class="text-right">Budget</TableHead>
                                    <TableHead class="text-right">Actual</TableHead>
                                    <TableHead class="text-right">Variance</TableHead>
                                    <TableHead class="text-right">Variance %</TableHead>
                                    <TableHead>Status</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="variance in varianceAnalysis" :key="variance.id" class="hover:bg-muted/50">
                                    <TableCell>
                                        <div class="flex items-center space-x-2">
                                            <div class="p-1 rounded" :class="getVarianceIconClass(variance.variance_percentage)">
                                                <component :is="getVarianceIcon(variance.variance_percentage)" class="h-3 w-3" />
                                            </div>
                                            <span>{{ variance.category }}</span>
                                        </div>
                                    </TableCell>
                                    <TableCell class="text-right">
                                        {{ formatCurrency(variance.budget) }}
                                    </TableCell>
                                    <TableCell class="text-right">
                                        {{ formatCurrency(variance.actual) }}
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <span :class="getVarianceColor(variance.variance)">
                                            {{ formatCurrency(variance.variance) }}
                                        </span>
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <span :class="getVarianceColor(variance.variance_percentage)">
                                            {{ variance.variance_percentage > 0 ? '+' : '' }}{{ variance.variance_percentage.toFixed(1) }}%
                                        </span>
                                    </TableCell>
                                    <TableCell>
                                        <Badge :variant="getVarianceStatusVariant(variance.variance_percentage)">
                                            {{ getVarianceStatus(variance.variance_percentage) }}
                                        </Badge>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </CardContent>
            </Card>

            <!-- Quick Actions -->
            <Card>
                <CardHeader>
                    <CardTitle>Quick Actions</CardTitle>
                    <CardDescription>
                        Common budgeting tasks and shortcuts
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-4">
                        <Link :href="route('finance.budgeting.create')">
                            <Button variant="outline" class="w-full justify-start">
                                <Plus class="h-4 w-4 mr-2" />
                                Create Budget
                            </Button>
                        </Link>
                        <Link :href="route('finance.budgeting.forecast')">
                            <Button variant="outline" class="w-full justify-start">
                                <TrendingUp class="h-4 w-4 mr-2" />
                                Financial Forecast
                            </Button>
                        </Link>
                        <Link :href="route('finance.budgeting.reports.variance')">
                            <Button variant="outline" class="w-full justify-start">
                                <BarChart3 class="h-4 w-4 mr-2" />
                                Variance Report
                            </Button>
                        </Link>
                        <Link :href="route('finance.budgeting.settings')">
                            <Button variant="outline" class="w-full justify-start">
                                <Settings class="h-4 w-4 mr-2" />
                                Budget Settings
                            </Button>
                        </Link>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
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
import { 
    Table, 
    TableBody, 
    TableCell, 
    TableHead, 
    TableHeader, 
    TableRow 
} from '@/components/ui/table';
import { AreaChart } from '@/components/ui/chart-area';
import { 
    FileText, 
    Calculator, 
    TrendingUp, 
    Wallet, 
    Download, 
    Plus,
    Eye,
    Edit,
    Calendar,
    Settings,
    BarChart3,
    CheckCircle,
    AlertTriangle,
    XCircle
} from 'lucide-vue-next';
import { useApi } from '@/composables/useApi';

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Finance', href: '/finance' },
    { title: 'Budget Management', href: '/finance/budgeting' }
];

const { api } = useApi();
const loading = ref(false);

// Data
const budgets = ref([]);
const budgetPeriods = ref([]);
const budgetCategories = ref([]);
const varianceAnalysis = ref([]);

// Computed
const activeBudgetsCount = computed(() => 
    budgets.value.filter(b => b.status === 'active').length
);

const totalBudget = computed(() => 
    budgets.value.reduce((sum, budget) => sum + budget.amount, 0)
);

const totalSpent = computed(() => 
    budgets.value.reduce((sum, budget) => sum + budget.spent, 0)
);

const remainingBudget = computed(() => totalBudget.value - totalSpent.value);

const remainingBudgetColor = computed(() => 
    remainingBudget.value >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'
);

// Chart data
const budgetVsActualData = computed(() => {
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
    return months.map((month, index) => ({
        month,
        budget: 100000 + (index * 15000),
        actual: 95000 + (index * 18000)
    }));
});

// Methods
const fetchData = async () => {
    loading.value = true;
    try {
        const [budgetsResponse, periodsResponse, categoriesResponse, varianceResponse] = await Promise.all([
            api.get('/api/finance/budgeting/budgets'),
            api.get('/api/finance/budgeting/periods'),
            api.get('/api/finance/budgeting/categories'),
            api.get('/api/finance/budgeting/variance-analysis')
        ]);
        
        budgets.value = budgetsResponse.data;
        budgetPeriods.value = periodsResponse.data;
        budgetCategories.value = categoriesResponse.data;
        varianceAnalysis.value = varianceResponse.data;
    } catch (error) {
        console.error('Error fetching budgeting data:', error);
    } finally {
        loading.value = false;
    }
};

const getUtilizationColor = (utilization: number): string => {
    if (utilization <= 80) return 'bg-green-500';
    if (utilization <= 100) return 'bg-yellow-500';
    return 'bg-red-500';
};

const getVarianceIcon = (variance: number) => {
    if (variance <= 5) return CheckCircle;
    if (variance <= 15) return AlertTriangle;
    return XCircle;
};

const getVarianceIconClass = (variance: number): string => {
    if (variance <= 5) return 'bg-green-100 dark:bg-green-900/20';
    if (variance <= 15) return 'bg-yellow-100 dark:bg-yellow-900/20';
    return 'bg-red-100 dark:bg-red-900/20';
};

const getVarianceColor = (variance: number): string => {
    if (variance <= 0) return 'text-green-600 dark:text-green-400';
    return 'text-red-600 dark:text-red-400';
};

const getVarianceStatus = (variance: number): string => {
    if (variance <= 5) return 'On Track';
    if (variance <= 15) return 'Watch';
    return 'Over Budget';
};

const getVarianceStatusVariant = (variance: number): string => {
    if (variance <= 5) return 'default';
    if (variance <= 15) return 'secondary';
    return 'destructive';
};

const exportBudgetReport = async () => {
    try {
        const response = await api.get('/api/finance/budgeting/export', {
            responseType: 'blob'
        });
        
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', 'budget-report.xlsx');
        document.body.appendChild(link);
        link.click();
        link.remove();
    } catch (error) {
        console.error('Error exporting budget report:', error);
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
    return new Date(date).toLocaleDateString('id-ID');
};

onMounted(() => {
    fetchData();
});
</script>
