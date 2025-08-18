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
import { Plus, Search, Filter, TrendingUp, BarChart3, Eye, Edit, Download, Calendar, Target, Users, Activity } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

interface BusinessAnalytics {
    id: number;
    analysis_code: string;
    title: string;
    description: string;
    analysis_type: string;
    data_source: string;
    analysis_date: string;
    data_start_date: string;
    data_end_date: string;
    key_metrics: any;
    insights: any;
    recommendations: any;
    visualization_data: any;
    priority: string;
    status: string;
    created_at: string;
    creator: {
        id: number;
        name: string;
        email: string;
    };
}

interface Statistics {
    total_analytics: number;
    active_analytics: number;
    completed_analytics: number;
    pending_analytics: number;
    high_priority: number;
    medium_priority: number;
    low_priority: number;
    analytics_by_type: Array<{
        analysis_type: string;
        count: number;
    }>;
    analytics_by_source: Array<{
        data_source: string;
        count: number;
    }>;
}

const props = defineProps<{
    analytics?: BusinessAnalytics[];
    statistics?: Statistics;
    pagination?: any;
}>();

const search = ref('');
const analysisTypeFilter = ref('');
const statusFilter = ref('');
const priorityFilter = ref('');
const dataSourceFilter = ref('');
const dateFromFilter = ref('');
const dateToFilter = ref('');

const filteredAnalytics = computed(() => {
    let filtered = props.analytics || [];

    if (search.value) {
        filtered = filtered.filter(analysis =>
            analysis.title.toLowerCase().includes(search.value.toLowerCase()) ||
            analysis.analysis_code.toLowerCase().includes(search.value.toLowerCase()) ||
            analysis.description?.toLowerCase().includes(search.value.toLowerCase())
        );
    }

    if (analysisTypeFilter.value) {
        filtered = filtered.filter(analysis => analysis.analysis_type === analysisTypeFilter.value);
    }

    if (statusFilter.value) {
        filtered = filtered.filter(analysis => analysis.status === statusFilter.value);
    }

    if (priorityFilter.value) {
        filtered = filtered.filter(analysis => analysis.priority === priorityFilter.value);
    }

    if (dataSourceFilter.value) {
        filtered = filtered.filter(analysis => analysis.data_source === dataSourceFilter.value);
    }

    if (dateFromFilter.value) {
        filtered = filtered.filter(analysis => analysis.analysis_date >= dateFromFilter.value);
    }

    if (dateToFilter.value) {
        filtered = filtered.filter(analysis => analysis.analysis_date <= dateToFilter.value);
    }

    return filtered;
});

const getStatusBadgeVariant = (status: string) => {
    switch (status) {
        case 'pending': return 'outline';
        case 'active': return 'default';
        case 'completed': return 'secondary';
        case 'archived': return 'destructive';
        default: return 'secondary';
    }
};

const getPriorityBadgeVariant = (priority: string) => {
    switch (priority) {
        case 'high': return 'destructive';
        case 'medium': return 'default';
        case 'low': return 'secondary';
        default: return 'secondary';
    }
};

const getAnalysisTypeBadgeVariant = (type: string) => {
    switch (type) {
        case 'sales_analysis': return 'default';
        case 'customer_behavior': return 'secondary';
        case 'market_trends': return 'outline';
        case 'performance_metrics': return 'destructive';
        case 'predictive_analytics': return 'default';
        case 'competitive_analysis': return 'secondary';
        case 'operational_efficiency': return 'outline';
        case 'custom': return 'secondary';
        default: return 'secondary';
    }
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const getAnalysisTypeLabel = (type: string) => {
    const labels = {
        'sales_analysis': 'Sales Analysis',
        'customer_behavior': 'Customer Behavior',
        'market_trends': 'Market Trends',
        'performance_metrics': 'Performance Metrics',
        'predictive_analytics': 'Predictive Analytics',
        'competitive_analysis': 'Competitive Analysis',
        'operational_efficiency': 'Operational Efficiency',
        'custom': 'Custom Analysis',
    };
    return labels[type] || type.replace('_', ' ').toUpperCase();
};

const getDataSourceLabel = (source: string) => {
    const labels = {
        'sales_data': 'Sales Data',
        'customer_data': 'Customer Data',
        'market_data': 'Market Data',
        'operational_data': 'Operational Data',
        'financial_data': 'Financial Data',
        'external_api': 'External API',
        'manual_input': 'Manual Input',
        'custom': 'Custom Source',
    };
    return labels[source] || source.replace('_', ' ').toUpperCase();
};

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Reporting & Analytics', href: '/reports' },
    { title: 'Business Analytics', href: '/reports/analytics' },
];
</script>

<template>

    <Head title="Business Analytics" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Business Analytics</h1>
                    <p class="text-muted-foreground">
                        Analyze business data and generate insights for strategic decisions
                    </p>
                </div>
                <Link :href="route('reports.analytics.create')">
                <Button>
                    <Plus class="mr-2 h-4 w-4" />
                    New Analysis
                </Button>
                </Link>
            </div>

            <!-- Statistics Cards -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Analytics</CardTitle>
                        <BarChart3 class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ statistics?.total_analytics || 0 }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ statistics?.active_analytics || 0 }} active
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Completed</CardTitle>
                        <Target class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ statistics?.completed_analytics || 0 }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ statistics?.pending_analytics || 0 }} pending
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">High Priority</CardTitle>
                        <Activity class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ statistics?.high_priority || 0 }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ statistics?.medium_priority || 0 }} medium priority
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Recent Activity</CardTitle>
                        <TrendingUp class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">+12.5%</div>
                        <p class="text-xs text-muted-foreground">
                            vs last month
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
                                <Input id="search" v-model="search" placeholder="Search analytics..." class="pl-9" />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="analysis_type">Analysis Type</Label>
                            <Select v-model="analysisTypeFilter">
                                <SelectTrigger>
                                    <SelectValue placeholder="All types" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">All types</SelectItem>
                                    <SelectItem value="sales_analysis">Sales Analysis</SelectItem>
                                    <SelectItem value="customer_behavior">Customer Behavior</SelectItem>
                                    <SelectItem value="market_trends">Market Trends</SelectItem>
                                    <SelectItem value="performance_metrics">Performance Metrics</SelectItem>
                                    <SelectItem value="predictive_analytics">Predictive Analytics</SelectItem>
                                    <SelectItem value="competitive_analysis">Competitive Analysis</SelectItem>
                                    <SelectItem value="operational_efficiency">Operational Efficiency</SelectItem>
                                    <SelectItem value="custom">Custom Analysis</SelectItem>
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
                                    <SelectItem value="pending">Pending</SelectItem>
                                    <SelectItem value="active">Active</SelectItem>
                                    <SelectItem value="completed">Completed</SelectItem>
                                    <SelectItem value="archived">Archived</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="space-y-2">
                            <Label for="priority">Priority</Label>
                            <Select v-model="priorityFilter">
                                <SelectTrigger>
                                    <SelectValue placeholder="All priorities" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">All priorities</SelectItem>
                                    <SelectItem value="high">High</SelectItem>
                                    <SelectItem value="medium">Medium</SelectItem>
                                    <SelectItem value="low">Low</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="space-y-2">
                            <Label for="data_source">Data Source</Label>
                            <Select v-model="dataSourceFilter">
                                <SelectTrigger>
                                    <SelectValue placeholder="All sources" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">All sources</SelectItem>
                                    <SelectItem value="sales_data">Sales Data</SelectItem>
                                    <SelectItem value="customer_data">Customer Data</SelectItem>
                                    <SelectItem value="market_data">Market Data</SelectItem>
                                    <SelectItem value="operational_data">Operational Data</SelectItem>
                                    <SelectItem value="financial_data">Financial Data</SelectItem>
                                    <SelectItem value="external_api">External API</SelectItem>
                                    <SelectItem value="manual_input">Manual Input</SelectItem>
                                    <SelectItem value="custom">Custom Source</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="space-y-2">
                            <Label for="date_from">Date From</Label>
                            <Input id="date_from" v-model="dateFromFilter" type="date" />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Analytics Table -->
            <Card>
                <CardHeader>
                    <CardTitle>Business Analytics</CardTitle>
                    <CardDescription>
                        A list of all business analytics and insights
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Analysis</TableHead>
                                <TableHead>Type</TableHead>
                                <TableHead>Data Source</TableHead>
                                <TableHead>Priority</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Analysis Date</TableHead>
                                <TableHead>Created</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="analysis in filteredAnalytics" :key="analysis.id">
                                <TableCell>
                                    <div>
                                        <div class="font-medium">{{ analysis.title }}</div>
                                        <div class="text-sm text-muted-foreground">{{ analysis.analysis_code }}</div>
                                        <div class="text-xs text-muted-foreground">{{ analysis.description }}</div>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <Badge :variant="getAnalysisTypeBadgeVariant(analysis.analysis_type)">
                                        {{ getAnalysisTypeLabel(analysis.analysis_type) }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <div class="text-sm">{{ getDataSourceLabel(analysis.data_source) }}</div>
                                    <div class="text-xs text-muted-foreground">
                                        {{ formatDate(analysis.data_start_date) }} - {{
                                            formatDate(analysis.data_end_date) }}
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <Badge :variant="getPriorityBadgeVariant(analysis.priority)">
                                        {{ analysis.priority.toUpperCase() }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <Badge :variant="getStatusBadgeVariant(analysis.status)">
                                        {{ analysis.status.toUpperCase() }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <div class="text-sm">{{ formatDate(analysis.analysis_date) }}</div>
                                </TableCell>
                                <TableCell>
                                    <div>
                                        <div class="text-sm">{{ formatDate(analysis.created_at) }}</div>
                                        <div class="text-xs text-muted-foreground">{{ analysis.creator.name }}</div>
                                    </div>
                                </TableCell>
                                <TableCell class="text-right">
                                    <div class="flex justify-end gap-2">
                                        <Link :href="route('reports.analytics.show', analysis.id)">
                                        <Button variant="outline" size="sm">
                                            <Eye class="h-4 w-4" />
                                        </Button>
                                        </Link>
                                        <Link :href="route('reports.analytics.edit', analysis.id)">
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
                    <div v-if="filteredAnalytics.length === 0" class="text-center py-8">
                        <BarChart3 class="mx-auto h-12 w-12 text-muted-foreground" />
                        <h3 class="mt-4 text-lg font-semibold">No analytics found</h3>
                        <p class="mt-2 text-muted-foreground">
                            Get started by creating your first business analysis.
                        </p>
                        <Link :href="route('reports.analytics.create')" class="mt-4">
                        <Button>
                            <Plus class="mr-2 h-4 w-4" />
                            Create Analysis
                        </Button>
                        </Link>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
