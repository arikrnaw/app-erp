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
import { Plus, Search, Filter, Clock, Calendar, Eye, Edit, Download, Play, Pause, Settings, Bell } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

interface ReportSchedule {
    id: number;
    schedule_code: string;
    name: string;
    description: string;
    report_type: string;
    report_template: string;
    frequency: string;
    day_of_week: string | null;
    day_of_month: number | null;
    delivery_time: string;
    delivery_method: string;
    recipients: any;
    parameters: any;
    is_active: boolean;
    last_generated_at: string | null;
    next_generation_at: string | null;
    created_at: string;
    creator: {
        id: number;
        name: string;
        email: string;
    };
}

interface Statistics {
    total_schedules: number;
    active_schedules: number;
    inactive_schedules: number;
    schedules_by_frequency: Array<{
        frequency: string;
        count: number;
    }>;
    schedules_by_type: Array<{
        report_type: string;
        count: number;
    }>;
    next_generations: Array<{
        id: number;
        name: string;
        next_generation_at: string;
    }>;
}

const props = defineProps<{
    schedules?: ReportSchedule[];
    statistics?: Statistics;
    pagination?: any;
}>();

const search = ref('');
const reportTypeFilter = ref('');
const frequencyFilter = ref('');
const statusFilter = ref('');
const deliveryMethodFilter = ref('');

const filteredSchedules = computed(() => {
    let filtered = props.schedules || [];

    if (search.value) {
        filtered = filtered.filter(schedule =>
            schedule.name.toLowerCase().includes(search.value.toLowerCase()) ||
            schedule.schedule_code.toLowerCase().includes(search.value.toLowerCase()) ||
            schedule.description?.toLowerCase().includes(search.value.toLowerCase())
        );
    }

    if (reportTypeFilter.value) {
        filtered = filtered.filter(schedule => schedule.report_type === reportTypeFilter.value);
    }

    if (frequencyFilter.value) {
        filtered = filtered.filter(schedule => schedule.frequency === frequencyFilter.value);
    }

    if (statusFilter.value) {
        if (statusFilter.value === 'active') {
            filtered = filtered.filter(schedule => schedule.is_active);
        } else if (statusFilter.value === 'inactive') {
            filtered = filtered.filter(schedule => !schedule.is_active);
        }
    }

    if (deliveryMethodFilter.value) {
        filtered = filtered.filter(schedule => schedule.delivery_method === deliveryMethodFilter.value);
    }

    return filtered;
});

const getStatusBadgeVariant = (isActive: boolean) => {
    return isActive ? 'default' : 'secondary';
};

const getFrequencyBadgeVariant = (frequency: string) => {
    switch (frequency) {
        case 'daily': return 'default';
        case 'weekly': return 'secondary';
        case 'monthly': return 'outline';
        case 'quarterly': return 'destructive';
        case 'yearly': return 'secondary';
        default: return 'secondary';
    }
};

const getReportTypeBadgeVariant = (type: string) => {
    switch (type) {
        case 'financial_report': return 'default';
        case 'business_analytics': return 'secondary';
        case 'operational_report': return 'outline';
        case 'custom_report': return 'destructive';
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
        'financial_report': 'Financial Report',
        'business_analytics': 'Business Analytics',
        'operational_report': 'Operational Report',
        'custom_report': 'Custom Report',
    };
    return labels[type] || type.replace('_', ' ').toUpperCase();
};

const getFrequencyLabel = (frequency: string) => {
    const labels = {
        'daily': 'Daily',
        'weekly': 'Weekly',
        'monthly': 'Monthly',
        'quarterly': 'Quarterly',
        'yearly': 'Yearly',
    };
    return labels[frequency] || frequency.toUpperCase();
};

const getDeliveryMethodLabel = (method: string) => {
    const labels = {
        'email': 'Email',
        'dashboard': 'Dashboard',
        'api': 'API',
        'file_download': 'File Download',
        'notification': 'Notification',
    };
    return labels[method] || method.replace('_', ' ').toUpperCase();
};

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Reporting & Analytics', href: '/reports' },
    { title: 'Report Schedules', href: '/reports/schedules' },
];
</script>

<template>

    <Head title="Report Schedules" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Report Schedules</h1>
                    <p class="text-muted-foreground">
                        Manage automated report generation schedules
                    </p>
                </div>
                <Link :href="route('reports.schedules.create')">
                <Button>
                    <Plus class="mr-2 h-4 w-4" />
                    New Schedule
                </Button>
                </Link>
            </div>

            <!-- Statistics Cards -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Schedules</CardTitle>
                        <Clock class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ statistics?.total_schedules || 0 }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ statistics?.active_schedules || 0 }} active
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Active Schedules</CardTitle>
                        <Play class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ statistics?.active_schedules || 0 }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ statistics?.inactive_schedules || 0 }} inactive
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Next Generation</CardTitle>
                        <Calendar class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">
                            {{ statistics?.next_generations?.length || 0 }}
                        </div>
                        <p class="text-xs text-muted-foreground">
                            scheduled today
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Delivery Methods</CardTitle>
                        <Bell class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">5</div>
                        <p class="text-xs text-muted-foreground">
                            different methods
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
                    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                        <div class="space-y-2">
                            <Label for="search">Search</Label>
                            <div class="relative">
                                <Search class="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                                <Input id="search" v-model="search" placeholder="Search schedules..." class="pl-9" />
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
                                    <SelectItem value="financial_report">Financial Report</SelectItem>
                                    <SelectItem value="business_analytics">Business Analytics</SelectItem>
                                    <SelectItem value="operational_report">Operational Report</SelectItem>
                                    <SelectItem value="custom_report">Custom Report</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="space-y-2">
                            <Label for="frequency">Frequency</Label>
                            <Select v-model="frequencyFilter">
                                <SelectTrigger>
                                    <SelectValue placeholder="All frequencies" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">All frequencies</SelectItem>
                                    <SelectItem value="daily">Daily</SelectItem>
                                    <SelectItem value="weekly">Weekly</SelectItem>
                                    <SelectItem value="monthly">Monthly</SelectItem>
                                    <SelectItem value="quarterly">Quarterly</SelectItem>
                                    <SelectItem value="yearly">Yearly</SelectItem>
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
                                    <SelectItem value="active">Active</SelectItem>
                                    <SelectItem value="inactive">Inactive</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Schedules Table -->
            <Card>
                <CardHeader>
                    <CardTitle>Report Schedules</CardTitle>
                    <CardDescription>
                        A list of all automated report generation schedules
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Schedule</TableHead>
                                <TableHead>Report Type</TableHead>
                                <TableHead>Frequency</TableHead>
                                <TableHead>Delivery</TableHead>
                                <TableHead>Next Generation</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Created</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="schedule in filteredSchedules" :key="schedule.id">
                                <TableCell>
                                    <div>
                                        <div class="font-medium">{{ schedule.name }}</div>
                                        <div class="text-sm text-muted-foreground">{{ schedule.schedule_code }}</div>
                                        <div class="text-xs text-muted-foreground">{{ schedule.description }}</div>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <Badge :variant="getReportTypeBadgeVariant(schedule.report_type)">
                                        {{ getReportTypeLabel(schedule.report_type) }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <div>
                                        <Badge :variant="getFrequencyBadgeVariant(schedule.frequency)">
                                            {{ getFrequencyLabel(schedule.frequency) }}
                                        </Badge>
                                        <div class="text-xs text-muted-foreground mt-1">
                                            {{ schedule.delivery_time }}
                                        </div>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div class="text-sm">{{ getDeliveryMethodLabel(schedule.delivery_method) }}</div>
                                    <div class="text-xs text-muted-foreground">
                                        {{ schedule.recipients?.length || 0 }} recipients
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div class="text-sm">
                                        {{ schedule.next_generation_at ? formatDateTime(schedule.next_generation_at) :
                                            'Not scheduled' }}
                                    </div>
                                    <div class="text-xs text-muted-foreground">
                                        Last: {{ schedule.last_generated_at ? formatDate(schedule.last_generated_at) :
                                            'Never' }}
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div class="flex items-center gap-2">
                                        <Badge :variant="getStatusBadgeVariant(schedule.is_active)">
                                            {{ schedule.is_active ? 'ACTIVE' : 'INACTIVE' }}
                                        </Badge>
                                        <Button variant="ghost" size="sm"
                                            :class="schedule.is_active ? 'text-green-600' : 'text-gray-400'">
                                            <component :is="schedule.is_active ? Play : Pause" class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div>
                                        <div class="text-sm">{{ formatDate(schedule.created_at) }}</div>
                                        <div class="text-xs text-muted-foreground">{{ schedule.creator.name }}</div>
                                    </div>
                                </TableCell>
                                <TableCell class="text-right">
                                    <div class="flex justify-end gap-2">
                                        <Link :href="route('reports.schedules.show', schedule.id)">
                                        <Button variant="outline" size="sm">
                                            <Eye class="h-4 w-4" />
                                        </Button>
                                        </Link>
                                        <Link :href="route('reports.schedules.edit', schedule.id)">
                                        <Button variant="outline" size="sm">
                                            <Edit class="h-4 w-4" />
                                        </Button>
                                        </Link>
                                        <Button variant="outline" size="sm">
                                            <Settings class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <!-- Empty State -->
                    <div v-if="filteredSchedules.length === 0" class="text-center py-8">
                        <Clock class="mx-auto h-12 w-12 text-muted-foreground" />
                        <h3 class="mt-4 text-lg font-semibold">No schedules found</h3>
                        <p class="mt-2 text-muted-foreground">
                            Get started by creating your first report schedule.
                        </p>
                        <Link :href="route('reports.schedules.create')" class="mt-4">
                        <Button>
                            <Plus class="mr-2 h-4 w-4" />
                            Create Schedule
                        </Button>
                        </Link>
                    </div>
                </CardContent>
            </Card>

            <!-- Upcoming Generations -->
            <Card>
                <CardHeader>
                    <CardTitle>Upcoming Report Generations</CardTitle>
                    <CardDescription>
                        Next scheduled report generations
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <div v-for="generation in statistics?.next_generations || []" :key="generation.id"
                            class="flex items-center justify-between p-4 border rounded-lg">
                            <div class="flex items-center gap-4">
                                <div class="p-2 bg-blue-100 dark:bg-blue-900/20 rounded-full">
                                    <Calendar class="h-4 w-4 text-blue-600" />
                                </div>
                                <div>
                                    <div class="font-medium">{{ generation.name }}</div>
                                    <div class="text-sm text-muted-foreground">
                                        {{ formatDateTime(generation.next_generation_at) }}
                                    </div>
                                </div>
                            </div>
                            <Button variant="outline" size="sm">
                                <Play class="mr-2 h-4 w-4" />
                                Run Now
                            </Button>
                        </div>

                        <div v-if="!statistics?.next_generations?.length" class="text-center py-8">
                            <Calendar class="mx-auto h-8 w-8 text-muted-foreground" />
                            <p class="mt-2 text-sm text-muted-foreground">
                                No upcoming report generations
                            </p>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
