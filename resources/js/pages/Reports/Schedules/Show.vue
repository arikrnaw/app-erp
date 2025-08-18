<template>

    <Head :title="`${schedule.name} - Report Schedule`" />
    <AppLayout>
        <template #header>
            <AppSidebarHeader :breadcrumbs="breadcrumbs" />
        </template>

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">{{ schedule.name }}</h1>
                    <p class="text-muted-foreground">
                        Schedule Code: {{ schedule.schedule_code }}
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <Link :href="route('reports.schedules.index')">
                    <Button variant="outline">
                        <ArrowLeft class="h-4 w-4 mr-2" />
                        Back to Schedules
                    </Button>
                    </Link>
                    <Link :href="route('reports.schedules.edit', schedule.id)">
                    <Button>
                        <Edit class="h-4 w-4 mr-2" />
                        Edit Schedule
                    </Button>
                    </Link>
                </div>
            </div>

            <!-- Overview Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center gap-2">
                            <FileText class="h-5 w-5 text-blue-500" />
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Report Type</p>
                                <p class="text-lg font-semibold">{{ getReportTypeLabel(schedule.report_type) }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center gap-2">
                            <Clock class="h-5 w-5 text-green-500" />
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Frequency</p>
                                <p class="text-lg font-semibold">{{ getFrequencyLabel(schedule.frequency) }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center gap-2">
                            <Send class="h-5 w-5 text-orange-500" />
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Delivery Method</p>
                                <p class="text-lg font-semibold">{{ getDeliveryMethodLabel(schedule.delivery_method) }}
                                </p>
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
                                <Badge :variant="getActiveBadgeVariant(schedule.is_active)">
                                    {{ schedule.is_active ? 'Active' : 'Inactive' }}
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
                                {{ schedule.description || 'No description provided.' }}
                            </p>
                        </CardContent>
                    </Card>

                    <!-- Schedule Details -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Calendar class="h-5 w-5" />
                                Schedule Details
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-4">
                                    <div>
                                        <p class="text-sm font-medium text-muted-foreground">Frequency</p>
                                        <p class="text-sm">{{ getFrequencyLabel(schedule.frequency) }}</p>
                                    </div>
                                    <div v-if="schedule.day_of_week">
                                        <p class="text-sm font-medium text-muted-foreground">Day of Week</p>
                                        <p class="text-sm">{{ getDayOfWeekLabel(schedule.day_of_week) }}</p>
                                    </div>
                                    <div v-if="schedule.day_of_month">
                                        <p class="text-sm font-medium text-muted-foreground">Day of Month</p>
                                        <p class="text-sm">{{ schedule.day_of_month }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-muted-foreground">Delivery Time</p>
                                        <p class="text-sm">{{ formatTime(schedule.delivery_time) }}</p>
                                    </div>
                                </div>
                                <div class="space-y-4">
                                    <div>
                                        <p class="text-sm font-medium text-muted-foreground">Report Type</p>
                                        <p class="text-sm">{{ getReportTypeLabel(schedule.report_type) }}</p>
                                    </div>
                                    <div v-if="schedule.report_template">
                                        <p class="text-sm font-medium text-muted-foreground">Report Template</p>
                                        <p class="text-sm">{{ schedule.report_template }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-muted-foreground">Delivery Method</p>
                                        <p class="text-sm">{{ getDeliveryMethodLabel(schedule.delivery_method) }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-muted-foreground">Active Status</p>
                                        <Badge :variant="getActiveBadgeVariant(schedule.is_active)">
                                            {{ schedule.is_active ? 'Active' : 'Inactive' }}
                                        </Badge>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Recipients -->
                    <Card v-if="schedule.recipients && schedule.recipients.length > 0">
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Users class="h-5 w-5" />
                                Recipients
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-2">
                                <div v-for="recipient in schedule.recipients" :key="recipient"
                                    class="flex items-center gap-2">
                                    <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                    <span class="text-sm">{{ recipient }}</span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Parameters -->
                    <Card v-if="schedule.parameters && Object.keys(schedule.parameters).length > 0">
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Settings class="h-5 w-5" />
                                Parameters
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-4">
                                <div v-for="(value, key) in schedule.parameters" :key="key"
                                    class="p-4 border rounded-lg">
                                    <p class="text-sm font-medium text-muted-foreground">{{ key }}</p>
                                    <p class="text-sm">{{ value }}</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Right Column -->
                <div class="space-y-6">
                    <!-- Schedule Information -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Info class="h-5 w-5" />
                                Schedule Information
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Created By</p>
                                <p class="text-sm">{{ schedule.creator?.name || 'Unknown' }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Created At</p>
                                <p class="text-sm">{{ formatDateTime(schedule.created_at) }}</p>
                            </div>
                            <div v-if="schedule.updated_at !== schedule.created_at">
                                <p class="text-sm font-medium text-muted-foreground">Last Updated</p>
                                <p class="text-sm">{{ formatDateTime(schedule.updated_at) }}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Generation History -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <History class="h-5 w-5" />
                                Generation History
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div v-if="schedule.last_generated_at">
                                <p class="text-sm font-medium text-muted-foreground">Last Generated</p>
                                <p class="text-sm">{{ formatDateTime(schedule.last_generated_at) }}</p>
                            </div>
                            <div v-if="schedule.next_generation_at">
                                <p class="text-sm font-medium text-muted-foreground">Next Generation</p>
                                <p class="text-sm">{{ formatDateTime(schedule.next_generation_at) }}</p>
                            </div>
                            <div v-if="!schedule.last_generated_at && !schedule.next_generation_at">
                                <p class="text-sm text-muted-foreground">No generation history available.</p>
                            </div>
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
import { ArrowLeft, Edit, FileText, Clock, Send, CheckCircle, Calendar, Users, Settings, Info, History } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';

interface ReportSchedule {
    id: number;
    schedule_code: string;
    name: string;
    description: string;
    report_type: string;
    report_template?: string;
    frequency: string;
    day_of_week?: string;
    day_of_month?: number;
    delivery_time: string;
    delivery_method: string;
    recipients: string[];
    parameters: Record<string, any>;
    is_active: boolean;
    last_generated_at?: string;
    next_generation_at?: string;
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
    schedule: ReportSchedule;
}>();

const getActiveBadgeVariant = (isActive: boolean) => {
    return isActive ? 'default' : 'secondary';
};

const getReportTypeLabel = (type: string) => {
    const labels: Record<string, string> = {
        'financial_report': 'Financial Report',
        'business_analytics': 'Business Analytics',
    };
    return labels[type] || type;
};

const getFrequencyLabel = (frequency: string) => {
    const labels: Record<string, string> = {
        'daily': 'Daily',
        'weekly': 'Weekly',
        'monthly': 'Monthly',
        'quarterly': 'Quarterly',
        'yearly': 'Yearly',
        'custom': 'Custom',
    };
    return labels[frequency] || frequency;
};

const getDeliveryMethodLabel = (method: string) => {
    const labels: Record<string, string> = {
        'email': 'Email',
        'dashboard': 'Dashboard',
        'pdf': 'PDF Download',
        'excel': 'Excel Download',
        'api': 'API',
    };
    return labels[method] || method;
};

const getDayOfWeekLabel = (day: string) => {
    const labels: Record<string, string> = {
        'monday': 'Monday',
        'tuesday': 'Tuesday',
        'wednesday': 'Wednesday',
        'thursday': 'Thursday',
        'friday': 'Friday',
        'saturday': 'Saturday',
        'sunday': 'Sunday',
    };
    return labels[day] || day;
};

const formatTime = (time: string) => {
    return new Date(`2000-01-01T${time}`).toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
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
    { name: 'Report Schedules', href: route('reports.schedules.index') },
    { name: props.schedule.name, href: '#' },
];
</script>
