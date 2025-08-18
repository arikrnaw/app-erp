<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Plus, Search, Filter, FileText, TrendingUp, TrendingDown, Eye, Edit, Download, Calendar, DollarSign } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

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
    status: string;
    created_at: string;
    creator: {
        id: number;
        name: string;
        email: string;
    };
}

interface Statistics {
    total_reports: number;
    published_reports: number;
    draft_reports: number;
    archived_reports: number;
    total_revenue: number;
    total_expenses: number;
    total_profit: number;
    avg_gross_margin: number;
    reports_by_type: Array<{
        report_type: string;
        count: number;
    }>;
    reports_by_period: Array<{
        period_type: string;
        count: number;
    }>;
}

const props = defineProps<{
    reports?: FinancialReport[];
    statistics?: Statistics;
    pagination?: any;
}>();

const search = ref('');
const reportTypeFilter = ref('');
const statusFilter = ref('');
const periodTypeFilter = ref('');
const dateFromFilter = ref('');
const dateToFilter = ref('');

const filteredReports = computed(() => {
    let filtered = props.reports || [];

    if (search.value) {
        filtered = filtered.filter(report =>
            report.title.toLowerCase().includes(search.value.toLowerCase()) ||
            report.report_code.toLowerCase().includes(search.value.toLowerCase()) ||
            report.description?.toLowerCase().includes(search.value.toLowerCase())
        );
    }

    if (reportTypeFilter.value) {
        filtered = filtered.filter(report => report.report_type === reportTypeFilter.value);
    }

    if (statusFilter.value) {
        filtered = filtered.filter(report => report.status === statusFilter.value);
    }

    if (periodTypeFilter.value) {
        filtered = filtered.filter(report => report.period_type === periodTypeFilter.value);
    }

    if (dateFromFilter.value) {
        filtered = filtered.filter(report => report.start_date >= dateFromFilter.value);
    }

    if (dateToFilter.value) {
        filtered = filtered.filter(report => report.end_date <= dateToFilter.value);
    }

    return filtered;
});

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
        month: 'short',
        day: 'numeric',
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
];
</script>

<template>

    <Head title="Financial Reports" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Financial Reports</h1>
                    <p class="text-muted-foreground">
                        Manage and generate comprehensive financial reports
                    </p>
                </div>
                <Link :href="route('reports.financial.create')">
                <Button>
                    <Plus class="mr-2 h-4 w-4" />
                    New Report
                </Button>
                </Link>
            </div>

            <!-- Statistics Cards -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Reports</CardTitle>
                        <FileText class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ statistics?.total_reports || 0 }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ statistics?.published_reports || 0 }} published
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Revenue</CardTitle>
                        <TrendingUp class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatCurrency(statistics?.total_revenue || 0) }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ statistics?.total_profit || 0 }} net profit
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Expenses</CardTitle>
                        <TrendingDown class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatCurrency(statistics?.total_expenses || 0) }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ statistics?.avg_gross_margin?.toFixed(1) || 0 }}% avg margin
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Draft Reports</CardTitle>
                        <Calendar class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ statistics?.draft_reports || 0 }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ statistics?.archived_reports || 0 }} archived
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Filters -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Filter class="h-5 w-5" />
                        Filters
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                        <div class="space-y-2">
                            <Label for="search">Search</Label>
                            <div class="relative">
                                <Search class="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                                <Input id="search" v-model="search" placeholder="Search reports..." class="pl-9" />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="report_type">Report Type</Label>
                            <Select v-model="reportTypeFilter">
                                <SelectTrigger>
                                    <SelectValue placeholder="All types" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">All types</SelectItem>
                                    <SelectItem value="income_statement">Income Statement</SelectItem>
                                    <SelectItem value="balance_sheet">Balance Sheet</SelectItem>
                                    <SelectItem value="cash_flow">Cash Flow</SelectItem>
                                    <SelectItem value="profit_loss">Profit & Loss</SelectItem>
                                    <SelectItem value="revenue_analysis">Revenue Analysis</SelectItem>
                                    <SelectItem value="expense_analysis">Expense Analysis</SelectItem>
                                    <SelectItem value="budget_variance">Budget Variance</SelectItem>
                                    <SelectItem value="custom">Custom Report</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="space-y-2">
                            <Label for="status">Status</Label>
                            <Select v-model="statusFilter">
                                <SelectTrigger>
                                    <SelectValue placeholder="All statuses" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">All statuses</SelectItem>
                                    <SelectItem value="draft">Draft</SelectItem>
                                    <SelectItem value="published">Published</SelectItem>
                                    <SelectItem value="archived">Archived</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="space-y-2">
                            <Label for="period_type">Period Type</Label>
                            <Select v-model="periodTypeFilter">
                                <SelectTrigger>
                                    <SelectValue placeholder="All periods" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">All periods</SelectItem>
                                    <SelectItem value="daily">Daily</SelectItem>
                                    <SelectItem value="weekly">Weekly</SelectItem>
                                    <SelectItem value="monthly">Monthly</SelectItem>
                                    <SelectItem value="quarterly">Quarterly</SelectItem>
                                    <SelectItem value="yearly">Yearly</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="space-y-2">
                            <Label for="date_from">Date From</Label>
                            <Input id="date_from" v-model="dateFromFilter" type="date" />
                        </div>

                        <div class="space-y-2">
                            <Label for="date_to">Date To</Label>
                            <Input id="date_to" v-model="dateToFilter" type="date" />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Reports Table -->
            <Card>
                <CardHeader>
                    <CardTitle>Financial Reports</CardTitle>
                    <CardDescription>
                        A list of all financial reports
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Report</TableHead>
                                <TableHead>Type</TableHead>
                                <TableHead>Period</TableHead>
                                <TableHead>Revenue</TableHead>
                                <TableHead>Expenses</TableHead>
                                <TableHead>Profit</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Created</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="report in filteredReports" :key="report.id">
                                <TableCell>
                                    <div>
                                        <div class="font-medium">{{ report.title }}</div>
                                        <div class="text-sm text-muted-foreground">{{ report.report_code }}</div>
                                        <div class="text-xs text-muted-foreground">{{ report.description }}</div>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <Badge :variant="getReportTypeBadgeVariant(report.report_type)">
                                        {{ getReportTypeLabel(report.report_type) }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <div>
                                        <div class="text-sm">{{ report.period_type.toUpperCase() }}</div>
                                        <div class="text-xs text-muted-foreground">
                                            {{ formatDate(report.start_date) }} - {{ formatDate(report.end_date) }}
                                        </div>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div class="font-medium">{{ formatCurrency(report.total_revenue) }}</div>
                                    <div class="text-xs text-muted-foreground">
                                        {{ report.gross_margin.toFixed(1) }}% margin
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div class="font-medium">{{ formatCurrency(report.total_expenses) }}</div>
                                </TableCell>
                                <TableCell>
                                    <div class="font-medium"
                                        :class="report.net_profit >= 0 ? 'text-green-600' : 'text-red-600'">
                                        {{ formatCurrency(report.net_profit) }}
                                    </div>
                                    <div class="text-xs text-muted-foreground">
                                        {{ report.operating_margin.toFixed(1) }}% op. margin
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <Badge :variant="getStatusBadgeVariant(report.status)">
                                        {{ report.status.toUpperCase() }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <div>
                                        <div class="text-sm">{{ formatDate(report.created_at) }}</div>
                                        <div class="text-xs text-muted-foreground">{{ report.creator.name }}</div>
                                    </div>
                                </TableCell>
                                <TableCell class="text-right">
                                    <div class="flex justify-end gap-2">
                                        <Link :href="route('reports.financial.show', report.id)">
                                        <Button variant="outline" size="sm">
                                            <Eye class="h-4 w-4" />
                                        </Button>
                                        </Link>
                                        <Link :href="route('reports.financial.edit', report.id)">
                                        <Button variant="outline" size="sm">
                                            <Edit class="h-4 w-4" />
                                        </Button>
                                        </Link>
                                        <Button variant="outline" size="sm">
                                            <Download class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <!-- Empty State -->
                    <div v-if="filteredReports.length === 0" class="text-center py-8">
                        <FileText class="mx-auto h-12 w-12 text-muted-foreground" />
                        <h3 class="mt-4 text-lg font-semibold">No reports found</h3>
                        <p class="mt-2 text-muted-foreground">
                            Get started by creating your first financial report.
                        </p>
                        <Link :href="route('reports.financial.create')" class="mt-4">
                        <Button>
                            <Plus class="mr-2 h-4 w-4" />
                            Create Report
                        </Button>
                        </Link>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
