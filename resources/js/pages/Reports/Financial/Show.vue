<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Progress } from '@/components/ui/progress';
import { Separator } from '@/components/ui/separator';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import {
    ArrowLeft,
    Edit,
    Download,
    Share2,
    FileText,
    TrendingUp,
    TrendingDown,
    DollarSign,
    Calendar,
    User,
    BarChart3,
    PieChart,
    Activity,
    Target
} from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';

interface FinancialReport {
    id: number;
    report_code: string;
    title: string;
    description: string;
    report_type: string;
    period_type: string;
    start_date: string;
    end_date: string;
    total_revenue: number;
    total_expenses: number;
    net_profit: number;
    gross_margin: number;
    operating_margin: number;
    financial_metrics: any;
    chart_data: any;
    status: string;
    created_at: string;
    updated_at: string;
    creator: {
        id: number;
        name: string;
        email: string;
    };
    company: {
        id: number;
        name: string;
    };
}

const props = defineProps<{
    report: FinancialReport;
}>();

const getStatusBadgeVariant = (status: string) => {
    switch (status) {
        case 'draft': return 'outline';
        case 'published': return 'default';
        case 'archived': return 'secondary';
        default: return 'secondary';
    }
};

const getReportTypeBadgeVariant = (type: string) => {
    switch (type) {
        case 'income_statement': return 'default';
        case 'balance_sheet': return 'secondary';
        case 'cash_flow': return 'outline';
        case 'profit_loss': return 'destructive';
        case 'revenue_analysis': return 'default';
        case 'expense_analysis': return 'secondary';
        case 'budget_variance': return 'outline';
        case 'custom': return 'secondary';
        default: return 'secondary';
    }
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount);
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const formatDateTime = (date: string) => {
    return new Date(date).toLocaleString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getReportTypeLabel = (type: string) => {
    const labels = {
        'income_statement': 'Income Statement',
        'balance_sheet': 'Balance Sheet',
        'cash_flow': 'Cash Flow',
        'profit_loss': 'Profit & Loss',
        'revenue_analysis': 'Revenue Analysis',
        'expense_analysis': 'Expense Analysis',
        'budget_variance': 'Budget Variance',
        'custom': 'Custom Report',
    };
    return labels[type] || type.replace('_', ' ').toUpperCase();
};

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Reporting & Analytics', href: '/reports' },
    { title: 'Financial Reports', href: '/reports/financial' },
    { title: props.report.title, href: `/reports/financial/${props.report.id}` },
];
</script>

<template>

    <Head :title="`${report.title} - Financial Report`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">{{ report.title }}</h1>
                    <p class="text-muted-foreground">
                        {{ report.description || 'Financial report details' }}
                    </p>
                </div>
                <div class="flex gap-2">
                    <Link :href="route('reports.financial.index')">
                    <Button variant="outline">
                        <ArrowLeft class="mr-2 h-4 w-4" />
                        Back to Reports
                    </Button>
                    </Link>
                    <Link :href="route('reports.financial.edit', report.id)">
                    <Button variant="outline">
                        <Edit class="mr-2 h-4 w-4" />
                        Edit
                    </Button>
                    </Link>
                    <Button variant="outline">
                        <Download class="mr-2 h-4 w-4" />
                        Export
                    </Button>
                    <Button variant="outline">
                        <Share2 class="mr-2 h-4 w-4" />
                        Share
                    </Button>
                </div>
            </div>

            <!-- Report Info -->
            <div class="grid gap-4 md:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Report Code</CardTitle>
                        <FileText class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ report.report_code }}</div>
                        <p class="text-xs text-muted-foreground">
                            <Badge :variant="getReportTypeBadgeVariant(report.report_type)">
                                {{ getReportTypeLabel(report.report_type) }}
                            </Badge>
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Period</CardTitle>
                        <Calendar class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ report.period_type.toUpperCase() }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ formatDate(report.start_date) }} - {{ formatDate(report.end_date) }}
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Status</CardTitle>
                        <Target class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">
                            <Badge :variant="getStatusBadgeVariant(report.status)">
                                {{ report.status.toUpperCase() }}
                            </Badge>
                        </div>
                        <p class="text-xs text-muted-foreground">
                            Created {{ formatDateTime(report.created_at) }}
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Created By</CardTitle>
                        <User class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ report.creator.name }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ report.creator.email }}
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Main Content Tabs -->
            <Tabs default-value="overview" class="space-y-4">
                <TabsList>
                    <TabsTrigger value="overview">Overview</TabsTrigger>
                    <TabsTrigger value="financials">Financial Data</TabsTrigger>
                    <TabsTrigger value="metrics">Metrics</TabsTrigger>
                    <TabsTrigger value="charts">Charts</TabsTrigger>
                    <TabsTrigger value="details">Details</TabsTrigger>
                </TabsList>

                <TabsContent value="overview" class="space-y-4">
                    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-7">
                        <!-- Key Financial Metrics -->
                        <Card class="col-span-4">
                            <CardHeader>
                                <CardTitle>Key Financial Metrics</CardTitle>
                                <CardDescription>
                                    Summary of financial performance
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div class="grid gap-4 md:grid-cols-3">
                                    <div class="text-center p-4 bg-green-50 dark:bg-green-950/20 rounded-lg">
                                        <div class="text-3xl font-bold text-green-600">
                                            {{ formatCurrency(report.total_revenue) }}
                                        </div>
                                        <div class="text-sm text-muted-foreground">Total Revenue</div>
                                        <div class="flex items-center justify-center mt-2">
                                            <TrendingUp class="h-4 w-4 text-green-600 mr-1" />
                                            <span class="text-xs text-green-600">+12.5%</span>
                                        </div>
                                    </div>

                                    <div class="text-center p-4 bg-red-50 dark:bg-red-950/20 rounded-lg">
                                        <div class="text-3xl font-bold text-red-600">
                                            {{ formatCurrency(report.total_expenses) }}
                                        </div>
                                        <div class="text-sm text-muted-foreground">Total Expenses</div>
                                        <div class="flex items-center justify-center mt-2">
                                            <TrendingDown class="h-4 w-4 text-red-600 mr-1" />
                                            <span class="text-xs text-red-600">-8.2%</span>
                                        </div>
                                    </div>

                                    <div class="text-center p-4 bg-blue-50 dark:bg-blue-950/20 rounded-lg">
                                        <div class="text-3xl font-bold"
                                            :class="report.net_profit >= 0 ? 'text-green-600' : 'text-red-600'">
                                            {{ formatCurrency(report.net_profit) }}
                                        </div>
                                        <div class="text-sm text-muted-foreground">Net Profit</div>
                                        <div class="flex items-center justify-center mt-2">
                                            <component :is="report.net_profit >= 0 ? TrendingUp : TrendingDown"
                                                class="h-4 w-4 mr-1"
                                                :class="report.net_profit >= 0 ? 'text-green-600' : 'text-red-600'" />
                                            <span class="text-xs"
                                                :class="report.net_profit >= 0 ? 'text-green-600' : 'text-red-600'">
                                                {{ report.net_profit >= 0 ? '+' : '' }}{{ ((report.net_profit /
                                                    report.total_revenue) * 100).toFixed(1) }}%
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <Separator class="my-6" />

                                <div class="grid gap-4 md:grid-cols-2">
                                    <div class="space-y-2">
                                        <div class="flex justify-between text-sm">
                                            <span>Gross Margin</span>
                                            <span class="font-medium">{{ report.gross_margin.toFixed(1) }}%</span>
                                        </div>
                                        <Progress :value="report.gross_margin" class="h-2" />
                                    </div>

                                    <div class="space-y-2">
                                        <div class="flex justify-between text-sm">
                                            <span>Operating Margin</span>
                                            <span class="font-medium">{{ report.operating_margin.toFixed(1) }}%</span>
                                        </div>
                                        <Progress :value="report.operating_margin" class="h-2" />
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Quick Actions -->
                        <Card class="col-span-3">
                            <CardHeader>
                                <CardTitle>Quick Actions</CardTitle>
                                <CardDescription>
                                    Common actions for this report
                                </CardDescription>
                            </CardHeader>
                            <CardContent class="space-y-3">
                                <Button variant="outline" class="w-full justify-start">
                                    <Download class="mr-2 h-4 w-4" />
                                    Download PDF
                                </Button>
                                <Button variant="outline" class="w-full justify-start">
                                    <Share2 class="mr-2 h-4 w-4" />
                                    Share Report
                                </Button>
                                <Button variant="outline" class="w-full justify-start">
                                    <Edit class="mr-2 h-4 w-4" />
                                    Edit Report
                                </Button>
                                <Button variant="outline" class="w-full justify-start">
                                    <BarChart3 class="mr-2 h-4 w-4" />
                                    Generate Charts
                                </Button>
                            </CardContent>
                        </Card>
                    </div>
                </TabsContent>

                <TabsContent value="financials" class="space-y-4">
                    <Card>
                        <CardHeader>
                            <CardTitle>Financial Breakdown</CardTitle>
                            <CardDescription>
                                Detailed financial data and analysis
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Category</TableHead>
                                        <TableHead>Amount</TableHead>
                                        <TableHead>Percentage</TableHead>
                                        <TableHead>Trend</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow>
                                        <TableCell class="font-medium">Total Revenue</TableCell>
                                        <TableCell class="font-medium text-green-600">
                                            {{ formatCurrency(report.total_revenue) }}
                                        </TableCell>
                                        <TableCell>100%</TableCell>
                                        <TableCell>
                                            <div class="flex items-center">
                                                <TrendingUp class="h-4 w-4 text-green-600 mr-1" />
                                                <span class="text-sm text-green-600">+12.5%</span>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                    <TableRow>
                                        <TableCell class="font-medium">Total Expenses</TableCell>
                                        <TableCell class="font-medium text-red-600">
                                            {{ formatCurrency(report.total_expenses) }}
                                        </TableCell>
                                        <TableCell>{{ ((report.total_expenses / report.total_revenue) * 100).toFixed(1)
                                            }}%</TableCell>
                                        <TableCell>
                                            <div class="flex items-center">
                                                <TrendingDown class="h-4 w-4 text-red-600 mr-1" />
                                                <span class="text-sm text-red-600">-8.2%</span>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                    <TableRow>
                                        <TableCell class="font-medium">Net Profit</TableCell>
                                        <TableCell class="font-medium"
                                            :class="report.net_profit >= 0 ? 'text-green-600' : 'text-red-600'">
                                            {{ formatCurrency(report.net_profit) }}
                                        </TableCell>
                                        <TableCell>{{ ((report.net_profit / report.total_revenue) * 100).toFixed(1) }}%
                                        </TableCell>
                                        <TableCell>
                                            <div class="flex items-center">
                                                <component :is="report.net_profit >= 0 ? TrendingUp : TrendingDown"
                                                    class="h-4 w-4 mr-1"
                                                    :class="report.net_profit >= 0 ? 'text-green-600' : 'text-red-600'" />
                                                <span class="text-sm"
                                                    :class="report.net_profit >= 0 ? 'text-green-600' : 'text-red-600'">
                                                    {{ report.net_profit >= 0 ? '+' : '' }}{{ ((report.net_profit /
                                                        report.total_revenue) * 100).toFixed(1) }}%
                                                </span>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </CardContent>
                    </Card>
                </TabsContent>

                <TabsContent value="metrics" class="space-y-4">
                    <div class="grid gap-4 md:grid-cols-2">
                        <Card>
                            <CardHeader>
                                <CardTitle>Profitability Metrics</CardTitle>
                                <CardDescription>
                                    Key profitability indicators
                                </CardDescription>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div class="space-y-2">
                                    <div class="flex justify-between text-sm">
                                        <span>Gross Margin</span>
                                        <span class="font-medium">{{ report.gross_margin.toFixed(1) }}%</span>
                                    </div>
                                    <Progress :value="report.gross_margin" />
                                </div>

                                <div class="space-y-2">
                                    <div class="flex justify-between text-sm">
                                        <span>Operating Margin</span>
                                        <span class="font-medium">{{ report.operating_margin.toFixed(1) }}%</span>
                                    </div>
                                    <Progress :value="report.operating_margin" />
                                </div>

                                <div class="space-y-2">
                                    <div class="flex justify-between text-sm">
                                        <span>Net Profit Margin</span>
                                        <span class="font-medium">{{ ((report.net_profit / report.total_revenue) *
                                            100).toFixed(1) }}%</span>
                                    </div>
                                    <Progress :value="(report.net_profit / report.total_revenue) * 100" />
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>Efficiency Metrics</CardTitle>
                                <CardDescription>
                                    Operational efficiency indicators
                                </CardDescription>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div class="space-y-2">
                                    <div class="flex justify-between text-sm">
                                        <span>Expense Ratio</span>
                                        <span class="font-medium">{{ ((report.total_expenses / report.total_revenue) *
                                            100).toFixed(1) }}%</span>
                                    </div>
                                    <Progress :value="(report.total_expenses / report.total_revenue) * 100" />
                                </div>

                                <div class="space-y-2">
                                    <div class="flex justify-between text-sm">
                                        <span>Revenue per Period</span>
                                        <span class="font-medium">{{ formatCurrency(report.total_revenue) }}</span>
                                    </div>
                                    <Progress :value="75" />
                                </div>

                                <div class="space-y-2">
                                    <div class="flex justify-between text-sm">
                                        <span>Cost Efficiency</span>
                                        <span class="font-medium">{{ (100 - (report.total_expenses /
                                            report.total_revenue) * 100).toFixed(1) }}%</span>
                                    </div>
                                    <Progress :value="100 - (report.total_expenses / report.total_revenue) * 100" />
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </TabsContent>

                <TabsContent value="charts" class="space-y-4">
                    <div class="grid gap-4 md:grid-cols-2">
                        <Card>
                            <CardHeader>
                                <CardTitle>Revenue vs Expenses</CardTitle>
                                <CardDescription>
                                    Monthly comparison chart
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

                        <Card>
                            <CardHeader>
                                <CardTitle>Profit Trend</CardTitle>
                                <CardDescription>
                                    Profitability over time
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div class="h-[300px] flex items-center justify-center bg-muted/20 rounded-lg">
                                    <div class="text-center">
                                        <TrendingUp class="mx-auto h-12 w-12 text-muted-foreground" />
                                        <p class="mt-2 text-sm text-muted-foreground">Trend chart will be displayed here
                                        </p>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </TabsContent>

                <TabsContent value="details" class="space-y-4">
                    <Card>
                        <CardHeader>
                            <CardTitle>Report Details</CardTitle>
                            <CardDescription>
                                Complete information about this report
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="grid gap-6 md:grid-cols-2">
                                <div class="space-y-4">
                                    <div>
                                        <h4 class="font-semibold mb-2">Basic Information</h4>
                                        <div class="space-y-2 text-sm">
                                            <div class="flex justify-between">
                                                <span class="text-muted-foreground">Report Code:</span>
                                                <span class="font-medium">{{ report.report_code }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-muted-foreground">Report Type:</span>
                                                <span class="font-medium">{{ getReportTypeLabel(report.report_type)
                                                    }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-muted-foreground">Period Type:</span>
                                                <span class="font-medium">{{ report.period_type.toUpperCase() }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-muted-foreground">Status:</span>
                                                <Badge :variant="getStatusBadgeVariant(report.status)">
                                                    {{ report.status.toUpperCase() }}
                                                </Badge>
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <h4 class="font-semibold mb-2">Date Range</h4>
                                        <div class="space-y-2 text-sm">
                                            <div class="flex justify-between">
                                                <span class="text-muted-foreground">Start Date:</span>
                                                <span class="font-medium">{{ formatDate(report.start_date) }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-muted-foreground">End Date:</span>
                                                <span class="font-medium">{{ formatDate(report.end_date) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    <div>
                                        <h4 class="font-semibold mb-2">Creation Details</h4>
                                        <div class="space-y-2 text-sm">
                                            <div class="flex justify-between">
                                                <span class="text-muted-foreground">Created By:</span>
                                                <span class="font-medium">{{ report.creator.name }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-muted-foreground">Created At:</span>
                                                <span class="font-medium">{{ formatDateTime(report.created_at) }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-muted-foreground">Last Updated:</span>
                                                <span class="font-medium">{{ formatDateTime(report.updated_at) }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-muted-foreground">Company:</span>
                                                <span class="font-medium">{{ report.company.name }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <h4 class="font-semibold mb-2">Description</h4>
                                        <p class="text-sm text-muted-foreground">
                                            {{ report.description || 'No description provided for this report.' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </TabsContent>
            </Tabs>
        </div>
    </AppLayout>
</template>
