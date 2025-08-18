<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { ArrowLeft, Save, FileText, Calculator, TrendingUp, Calendar, DollarSign } from 'lucide-vue-next';
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
    financial_metrics: any;
    chart_data: any;
    status: string;
}

const props = defineProps<{
    report: FinancialReport;
}>();

const form = useForm({
    title: props.report.title,
    description: props.report.description,
    report_type: props.report.report_type,
    period_type: props.report.period_type,
    start_date: props.report.start_date,
    end_date: props.report.end_date,
    total_revenue: props.report.total_revenue,
    total_expenses: props.report.total_expenses,
    net_profit: props.report.net_profit,
    gross_margin: props.report.gross_margin,
    operating_margin: props.report.operating_margin,
    financial_metrics: props.report.financial_metrics,
    chart_data: props.report.chart_data,
    status: props.report.status,
});

const isCalculating = ref(false);

const calculateMetrics = () => {
    isCalculating.value = true;

    // Calculate net profit
    form.net_profit = form.total_revenue - form.total_expenses;

    // Calculate gross margin
    if (form.total_revenue > 0) {
        form.gross_margin = ((form.total_revenue - form.total_expenses) / form.total_revenue) * 100;
    }

    // Calculate operating margin (simplified)
    if (form.total_revenue > 0) {
        form.operating_margin = (form.net_profit / form.total_revenue) * 100;
    }

    // Update financial metrics
    form.financial_metrics = {
        revenue_growth: 0,
        expense_ratio: form.total_revenue > 0 ? (form.total_expenses / form.total_revenue) * 100 : 0,
        profit_margin: form.gross_margin,
        return_on_revenue: form.operating_margin,
    };

    // Generate sample chart data
    form.chart_data = {
        revenue_trend: [form.total_revenue * 0.8, form.total_revenue * 0.9, form.total_revenue],
        expense_trend: [form.total_expenses * 0.8, form.total_expenses * 0.9, form.total_expenses],
        profit_trend: [form.net_profit * 0.8, form.net_profit * 0.9, form.net_profit],
    };

    setTimeout(() => {
        isCalculating.value = false;
    }, 500);
};

const submit = () => {
    form.put(route('reports.financial.update', props.report.id), {
        onSuccess: () => {
            // Handle success
        },
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

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount);
};

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Reporting & Analytics', href: '/reports' },
    { title: 'Financial Reports', href: '/reports/financial' },
    { title: 'Edit Report', href: `/reports/financial/${props.report.id}/edit` },
];
</script>

<template>

    <Head :title="`Edit ${report.title} - Financial Report`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Edit Financial Report</h1>
                    <p class="text-muted-foreground">
                        Update the financial report: {{ report.title }}
                    </p>
                </div>
                <div class="flex gap-2">
                    <Link :href="route('reports.financial.show', report.id)">
                    <Button variant="outline">
                        <ArrowLeft class="mr-2 h-4 w-4" />
                        Back to Report
                    </Button>
                    </Link>
                    <Link :href="route('reports.financial.index')">
                    <Button variant="outline">
                        Back to Reports
                    </Button>
                    </Link>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Basic Information -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <FileText class="h-5 w-5" />
                            Basic Information
                        </CardTitle>
                        <CardDescription>
                            Update the basic details for your financial report
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="title">Report Title *</Label>
                                <Input id="title" v-model="form.title" placeholder="Enter report title"
                                    :class="{ 'border-red-500': form.errors.title }" />
                                <p v-if="form.errors.title" class="text-sm text-red-500">
                                    {{ form.errors.title }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="report_type">Report Type *</Label>
                                <Select v-model="form.report_type">
                                    <SelectTrigger :class="{ 'border-red-500': form.errors.report_type }">
                                        <SelectValue placeholder="Select report type" />
                                    </SelectTrigger>
                                    <SelectContent>
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
                                <p v-if="form.errors.report_type" class="text-sm text-red-500">
                                    {{ form.errors.report_type }}
                                </p>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="description">Description</Label>
                            <Textarea id="description" v-model="form.description" placeholder="Enter report description"
                                rows="3" />
                        </div>

                        <div class="grid gap-4 md:grid-cols-3">
                            <div class="space-y-2">
                                <Label for="period_type">Period Type *</Label>
                                <Select v-model="form.period_type">
                                    <SelectTrigger :class="{ 'border-red-500': form.errors.period_type }">
                                        <SelectValue placeholder="Select period type" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="daily">Daily</SelectItem>
                                        <SelectItem value="weekly">Weekly</SelectItem>
                                        <SelectItem value="monthly">Monthly</SelectItem>
                                        <SelectItem value="quarterly">Quarterly</SelectItem>
                                        <SelectItem value="yearly">Yearly</SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.period_type" class="text-sm text-red-500">
                                    {{ form.errors.period_type }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="start_date">Start Date *</Label>
                                <Input id="start_date" v-model="form.start_date" type="date"
                                    :class="{ 'border-red-500': form.errors.start_date }" />
                                <p v-if="form.errors.start_date" class="text-sm text-red-500">
                                    {{ form.errors.start_date }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="end_date">End Date *</Label>
                                <Input id="end_date" v-model="form.end_date" type="date"
                                    :class="{ 'border-red-500': form.errors.end_date }" />
                                <p v-if="form.errors.end_date" class="text-sm text-red-500">
                                    {{ form.errors.end_date }}
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Financial Data -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Calculator class="h-5 w-5" />
                            Financial Data
                        </CardTitle>
                        <CardDescription>
                            Update the financial figures for your report
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="total_revenue">Total Revenue (IDR) *</Label>
                                <Input id="total_revenue" v-model.number="form.total_revenue" type="number"
                                    placeholder="0" :class="{ 'border-red-500': form.errors.total_revenue }" />
                                <p v-if="form.errors.total_revenue" class="text-sm text-red-500">
                                    {{ form.errors.total_revenue }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="total_expenses">Total Expenses (IDR) *</Label>
                                <Input id="total_expenses" v-model.number="form.total_expenses" type="number"
                                    placeholder="0" :class="{ 'border-red-500': form.errors.total_expenses }" />
                                <p v-if="form.errors.total_expenses" class="text-sm text-red-500">
                                    {{ form.errors.total_expenses }}
                                </p>
                            </div>
                        </div>

                        <Button type="button" variant="outline" @click="calculateMetrics" :disabled="isCalculating"
                            class="w-full">
                            <Calculator class="mr-2 h-4 w-4" />
                            {{ isCalculating ? 'Calculating...' : 'Recalculate Metrics' }}
                        </Button>

                        <Separator />

                        <!-- Calculated Metrics -->
                        <div class="grid gap-4 md:grid-cols-3">
                            <div class="space-y-2">
                                <Label>Net Profit</Label>
                                <div class="text-2xl font-bold"
                                    :class="form.net_profit >= 0 ? 'text-green-600' : 'text-red-600'">
                                    {{ formatCurrency(form.net_profit) }}
                                </div>
                            </div>

                            <div class="space-y-2">
                                <Label>Gross Margin</Label>
                                <div class="text-2xl font-bold">
                                    {{ form.gross_margin.toFixed(1) }}%
                                </div>
                            </div>

                            <div class="space-y-2">
                                <Label>Operating Margin</Label>
                                <div class="text-2xl font-bold">
                                    {{ form.operating_margin.toFixed(1) }}%
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Report Settings -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Calendar class="h-5 w-5" />
                            Report Settings
                        </CardTitle>
                        <CardDescription>
                            Configure the report status and settings
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-2">
                            <Label for="status">Status</Label>
                            <Select v-model="form.status">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="draft">Draft</SelectItem>
                                    <SelectItem value="published">Published</SelectItem>
                                    <SelectItem value="archived">Archived</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </CardContent>
                </Card>

                <!-- Preview -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <TrendingUp class="h-5 w-5" />
                            Report Preview
                        </CardTitle>
                        <CardDescription>
                            Preview of your updated financial report
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-semibold">{{ form.title || 'Untitled Report' }}</h3>
                                    <p class="text-sm text-muted-foreground">{{ form.description || 'No description' }}
                                    </p>
                                </div>
                                <Badge variant="outline">
                                    {{ getReportTypeLabel(form.report_type) || 'No Type' }}
                                </Badge>
                            </div>

                            <div class="grid gap-4 md:grid-cols-3">
                                <div class="text-center p-4 bg-muted/20 rounded-lg">
                                    <div class="text-2xl font-bold text-green-600">
                                        {{ formatCurrency(form.total_revenue) }}
                                    </div>
                                    <div class="text-sm text-muted-foreground">Total Revenue</div>
                                </div>

                                <div class="text-center p-4 bg-muted/20 rounded-lg">
                                    <div class="text-2xl font-bold text-red-600">
                                        {{ formatCurrency(form.total_expenses) }}
                                    </div>
                                    <div class="text-sm text-muted-foreground">Total Expenses</div>
                                </div>

                                <div class="text-center p-4 bg-muted/20 rounded-lg">
                                    <div class="text-2xl font-bold"
                                        :class="form.net_profit >= 0 ? 'text-green-600' : 'text-red-600'">
                                        {{ formatCurrency(form.net_profit) }}
                                    </div>
                                    <div class="text-sm text-muted-foreground">Net Profit</div>
                                </div>
                            </div>

                            <div class="text-center p-8 bg-muted/10 rounded-lg">
                                <DollarSign class="mx-auto h-12 w-12 text-muted-foreground" />
                                <p class="mt-2 text-sm text-muted-foreground">
                                    Chart visualization will be displayed here
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Actions -->
                <div class="flex justify-end gap-4">
                    <Link :href="route('reports.financial.show', report.id)">
                    <Button type="button" variant="outline">
                        Cancel
                    </Button>
                    </Link>
                    <Button type="submit" :disabled="form.processing">
                        <Save class="mr-2 h-4 w-4" />
                        {{ form.processing ? 'Updating...' : 'Update Report' }}
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
