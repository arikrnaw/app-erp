<template>

    <Head :title="`${analytics.title} - Business Analytics`" />
    <AppLayout>
        <template #header>
            <AppSidebarHeader :breadcrumbs="breadcrumbs" />
        </template>

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">{{ analytics.title }}</h1>
                    <p class="text-muted-foreground">
                        Analysis Code: {{ analytics.analysis_code }}
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <Link :href="route('reports.analytics.index')">
                    <Button variant="outline">
                        <ArrowLeft class="h-4 w-4 mr-2" />
                        Back to Analytics
                    </Button>
                    </Link>
                    <Link :href="route('reports.analytics.edit', analytics.id)">
                    <Button>
                        <Edit class="h-4 w-4 mr-2" />
                        Edit Analytics
                    </Button>
                    </Link>
                </div>
            </div>

            <!-- Overview Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center gap-2">
                            <TrendingUp class="h-5 w-5 text-blue-500" />
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Analysis Type</p>
                                <p class="text-lg font-semibold">{{ getAnalysisTypeLabel(analytics.analysis_type) }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center gap-2">
                            <Database class="h-5 w-5 text-green-500" />
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Data Source</p>
                                <p class="text-lg font-semibold">{{ getDataSourceLabel(analytics.data_source) }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center gap-2">
                            <AlertTriangle class="h-5 w-5 text-orange-500" />
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Priority</p>
                                <Badge :variant="getPriorityBadgeVariant(analytics.priority)">
                                    {{ getPriorityLabel(analytics.priority) }}
                                </Badge>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center gap-2">
                            <CheckCircle class="h-5 w-5 text-purple-500" />
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Status</p>
                                <Badge :variant="getStatusBadgeVariant(analytics.status)">
                                    {{ getStatusLabel(analytics.status) }}
                                </Badge>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Main Content -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Description -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <FileText class="h-5 w-5" />
                                Description
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p class="text-muted-foreground">
                                {{ analytics.description || 'No description provided.' }}
                            </p>
                        </CardContent>
                    </Card>

                    <!-- Key Metrics -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <BarChart3 class="h-5 w-5" />
                                Key Metrics
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div v-if="analytics.key_metrics && Object.keys(analytics.key_metrics).length > 0"
                                class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div v-for="(value, key) in analytics.key_metrics" :key="key"
                                    class="p-4 border rounded-lg">
                                    <p class="text-sm font-medium text-muted-foreground">{{ key }}</p>
                                    <p class="text-lg font-semibold">{{ value }}</p>
                                </div>
                            </div>
                            <p v-else class="text-muted-foreground">No key metrics available.</p>
                        </CardContent>
                    </Card>

                    <!-- Insights -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Lightbulb class="h-5 w-5" />
                                Insights
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div v-if="analytics.insights && Object.keys(analytics.insights).length > 0"
                                class="space-y-4">
                                <div v-for="(insight, index) in analytics.insights" :key="index"
                                    class="p-4 bg-blue-50 dark:bg-blue-950/20 rounded-lg">
                                    <p class="text-sm font-medium text-blue-700 dark:text-blue-300">{{ insight.title ||
                                        `Insight
                                        ${index + 1}` }}</p>
                                    <p class="text-sm text-blue-600 dark:text-blue-400 mt-1">{{ insight.description }}
                                    </p>
                                </div>
                            </div>
                            <p v-else class="text-muted-foreground">No insights available.</p>
                        </CardContent>
                    </Card>

                    <!-- Recommendations -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Target class="h-5 w-5" />
                                Recommendations
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div v-if="analytics.recommendations && Object.keys(analytics.recommendations).length > 0"
                                class="space-y-4">
                                <div v-for="(recommendation, index) in analytics.recommendations" :key="index"
                                    class="p-4 bg-green-50 dark:bg-green-950/20 rounded-lg">
                                    <p class="text-sm font-medium text-green-700 dark:text-green-300">{{
                                        recommendation.title ||
                                        `Recommendation ${index + 1}` }}</p>
                                    <p class="text-sm text-green-600 dark:text-green-400 mt-1">{{
                                        recommendation.description }}
                                    </p>
                                </div>
                            </div>
                            <p v-else class="text-muted-foreground">No recommendations available.</p>
                        </CardContent>
                    </Card>
                </div>

                <!-- Right Column -->
                <div class="space-y-6">
                    <!-- Analysis Details -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Calendar class="h-5 w-5" />
                                Analysis Details
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Analysis Date</p>
                                <p class="text-sm">{{ formatDate(analytics.analysis_date) }}</p>
                            </div>
                            <div v-if="analytics.data_start_date">
                                <p class="text-sm font-medium text-muted-foreground">Data Start Date</p>
                                <p class="text-sm">{{ formatDate(analytics.data_start_date) }}</p>
                            </div>
                            <div v-if="analytics.data_end_date">
                                <p class="text-sm font-medium text-muted-foreground">Data End Date</p>
                                <p class="text-sm">{{ formatDate(analytics.data_end_date) }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Created By</p>
                                <p class="text-sm">{{ analytics.creator?.name || 'Unknown' }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Created At</p>
                                <p class="text-sm">{{ formatDateTime(analytics.created_at) }}</p>
                            </div>
                            <div v-if="analytics.updated_at !== analytics.created_at">
                                <p class="text-sm font-medium text-muted-foreground">Last Updated</p>
                                <p class="text-sm">{{ formatDateTime(analytics.updated_at) }}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Visualization Data -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <PieChart class="h-5 w-5" />
                                Visualization Data
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div v-if="analytics.visualization_data && Object.keys(analytics.visualization_data).length > 0"
                                class="space-y-4">
                                <div v-for="(chart, key) in analytics.visualization_data" :key="key"
                                    class="p-4 border rounded-lg">
                                    <p class="text-sm font-medium text-muted-foreground mb-2">{{ key }}</p>
                                    <div class="text-xs text-muted-foreground">
                                        <pre class="whitespace-pre-wrap">{{ JSON.stringify(chart, null, 2) }}</pre>
                                    </div>
                                </div>
                            </div>
                            <p v-else class="text-muted-foreground">No visualization data available.</p>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { ArrowLeft, Edit, TrendingUp, Database, AlertTriangle, CheckCircle, FileText, BarChart3, Lightbulb, Target, Calendar, PieChart } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';

interface BusinessAnalytics {
    id: number;
    analysis_code: string;
    title: string;
    description: string;
    analysis_type: string;
    data_source: string;
    analysis_date: string;
    data_start_date?: string;
    data_end_date?: string;
    key_metrics: Record<string, any>;
    insights: Record<string, any>;
    recommendations: Record<string, any>;
    visualization_data: Record<string, any>;
    priority: string;
    status: string;
    created_by: number;
    company_id: number;
    created_at: string;
    updated_at: string;
    creator?: {
        id: number;
        name: string;
        email: string;
    };
    company?: {
        id: number;
        name: string;
    };
}

const props = defineProps<{
    analytics: BusinessAnalytics;
}>();

const getStatusBadgeVariant = (status: string) => {
    switch (status) {
        case 'published': return 'default';
        case 'draft': return 'secondary';
        case 'archived': return 'destructive';
        default: return 'outline';
    }
};

const getPriorityBadgeVariant = (priority: string) => {
    switch (priority) {
        case 'critical': return 'destructive';
        case 'high': return 'default';
        case 'medium': return 'secondary';
        case 'low': return 'outline';
        default: return 'outline';
    }
};

const getAnalysisTypeLabel = (type: string) => {
    const labels: Record<string, string> = {
        'sales_analysis': 'Sales Analysis',
        'customer_analysis': 'Customer Analysis',
        'product_analysis': 'Product Analysis',
        'market_analysis': 'Market Analysis',
        'performance_analysis': 'Performance Analysis',
        'trend_analysis': 'Trend Analysis',
        'forecasting': 'Forecasting',
        'custom': 'Custom Analysis',
    };
    return labels[type] || type;
};

const getDataSourceLabel = (source: string) => {
    const labels: Record<string, string> = {
        'sales_orders': 'Sales Orders',
        'customers': 'Customers',
        'products': 'Products',
        'inventory': 'Inventory',
        'financial_reports': 'Financial Reports',
        'external_api': 'External API',
        'custom': 'Custom Data',
    };
    return labels[source] || source;
};

const getPriorityLabel = (priority: string) => {
    const labels: Record<string, string> = {
        'low': 'Low',
        'medium': 'Medium',
        'high': 'High',
        'critical': 'Critical',
    };
    return labels[priority] || priority;
};

const getStatusLabel = (status: string) => {
    const labels: Record<string, string> = {
        'draft': 'Draft',
        'published': 'Published',
        'archived': 'Archived',
    };
    return labels[status] || status;
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const formatDateTime = (date: string) => {
    return new Date(date).toLocaleString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const breadcrumbs = [
    { name: 'Dashboard', href: route('dashboard') },
    { name: 'Reporting & Analytics', href: route('reports.index') },
    { name: 'Business Analytics', href: route('reports.analytics.index') },
    { name: props.analytics.title, href: '#' },
];
</script>
