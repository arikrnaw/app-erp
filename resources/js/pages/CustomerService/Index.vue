<template>

    <Head title="Customer Service Dashboard" />
    <AppLayout>
        <template #header>
            <AppSidebarHeader :breadcrumbs="breadcrumbs" />
        </template>

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Customer Service Dashboard</h1>
                    <p class="text-muted-foreground">
                        Monitor and manage customer service activities, complaints, and support tickets.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="outline">
                        <Download class="h-4 w-4 mr-2" />
                        Export Report
                    </Button>
                    <Button>
                        <Plus class="h-4 w-4 mr-2" />
                        New Ticket
                    </Button>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center gap-2">
                            <div class="p-2 bg-red-100 dark:bg-red-900/20 rounded-lg">
                                <AlertTriangle class="h-5 w-5 text-red-600 dark:text-red-400" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Open Complaints</p>
                                <p class="text-2xl font-bold">24</p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <p class="text-xs text-muted-foreground">
                                <span class="text-red-600 dark:text-red-400">+12%</span> from last week
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center gap-2">
                            <div class="p-2 bg-blue-100 dark:bg-blue-900/20 rounded-lg">
                                <Shield class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Active Services</p>
                                <p class="text-2xl font-bold">18</p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <p class="text-xs text-muted-foreground">
                                <span class="text-green-600 dark:text-green-400">-5%</span> from last week
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center gap-2">
                            <div class="p-2 bg-green-100 dark:bg-green-900/20 rounded-lg">
                                <Ticket class="h-5 w-5 text-green-600 dark:text-green-400" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Service Tickets</p>
                                <p class="text-2xl font-bold">156</p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <p class="text-xs text-muted-foreground">
                                <span class="text-green-600 dark:text-green-400">+8%</span> from last week
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center gap-2">
                            <div class="p-2 bg-purple-100 dark:bg-purple-900/20 rounded-lg">
                                <Smile class="h-5 w-5 text-purple-600 dark:text-purple-400" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Satisfaction</p>
                                <p class="text-2xl font-bold">4.8</p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <p class="text-xs text-muted-foreground">
                                <span class="text-green-600 dark:text-green-400">+0.2</span> from last week
                            </p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Recent Activities -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Quick Actions -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Zap class="h-5 w-5" />
                                Quick Actions
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <Link :href="route('customer-service.complaints.create')">
                                <Button variant="outline" class="w-full h-20 flex-col">
                                    <AlertTriangle class="h-5 w-5 mb-2" />
                                    <span class="text-sm">New Complaint</span>
                                </Button>
                                </Link>
                                <Link :href="route('customer-service.after-sales-services.create')">
                                <Button variant="outline" class="w-full h-20 flex-col">
                                    <Shield class="h-5 w-5 mb-2" />
                                    <span class="text-sm">New Service</span>
                                </Button>
                                </Link>
                                <Link :href="route('customer-service.service-tickets.create')">
                                <Button variant="outline" class="w-full h-20 flex-col">
                                    <Ticket class="h-5 w-5 mb-2" />
                                    <span class="text-sm">New Ticket</span>
                                </Button>
                                </Link>
                                <Link :href="route('customer-service.service-categories.create')">
                                <Button variant="outline" class="w-full h-20 flex-col">
                                    <Tag class="h-5 w-5 mb-2" />
                                    <span class="text-sm">New Category</span>
                                </Button>
                                </Link>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Recent Complaints -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <AlertTriangle class="h-5 w-5" />
                                Recent Complaints
                            </CardTitle>
                            <CardDescription>
                                Latest customer complaints that need attention
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-4">
                                <div v-for="complaint in recentComplaints" :key="complaint.id"
                                    class="flex items-center justify-between p-4 border rounded-lg">
                                    <div class="flex items-center gap-3">
                                        <div class="p-2 bg-red-100 dark:bg-red-900/20 rounded-lg">
                                            <AlertTriangle class="h-4 w-4 text-red-600 dark:text-red-400" />
                                        </div>
                                        <div>
                                            <p class="font-medium">{{ complaint.title }}</p>
                                            <p class="text-sm text-muted-foreground">{{ complaint.customer_name }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <Badge :variant="getPriorityVariant(complaint.priority)">
                                            {{ complaint.priority }}
                                        </Badge>
                                        <Badge :variant="getStatusVariant(complaint.status)">
                                            {{ complaint.status }}
                                        </Badge>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4">
                                <Link :href="route('customer-service.complaints.index')">
                                <Button variant="outline" class="w-full">
                                    View All Complaints
                                </Button>
                                </Link>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Service Performance -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <BarChart3 class="h-5 w-5" />
                                Service Performance
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium">Response Time</span>
                                    <span class="text-sm text-muted-foreground">2.3 hours</span>
                                </div>
                                <Progress :value="85" class="h-2" />

                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium">Resolution Rate</span>
                                    <span class="text-sm text-muted-foreground">94%</span>
                                </div>
                                <Progress :value="94" class="h-2" />

                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium">Customer Satisfaction</span>
                                    <span class="text-sm text-muted-foreground">4.8/5</span>
                                </div>
                                <Progress :value="96" class="h-2" />
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Right Sidebar -->
                <div class="space-y-6">
                    <!-- Upcoming Services -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Calendar class="h-5 w-5" />
                                Upcoming Services
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-4">
                                <div v-for="service in upcomingServices" :key="service.id"
                                    class="flex items-center gap-3 p-3 border rounded-lg">
                                    <div class="p-2 bg-blue-100 dark:bg-blue-900/20 rounded-lg">
                                        <Shield class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-medium text-sm">{{ service.title }}</p>
                                        <p class="text-xs text-muted-foreground">{{ service.customer_name }}</p>
                                        <p class="text-xs text-muted-foreground">{{ formatDate(service.scheduled_date)
                                        }}</p>
                                    </div>
                                    <Badge :variant="getServiceStatusVariant(service.status)">
                                        {{ service.status }}
                                    </Badge>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Team Activity -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Users class="h-5 w-5" />
                                Team Activity
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-4">
                                <div v-for="activity in teamActivities" :key="activity.id"
                                    class="flex items-center gap-3">
                                    <div
                                        class="w-8 h-8 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center">
                                        <span class="text-xs font-medium">{{ activity.user.charAt(0) }}</span>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium">{{ activity.user }}</p>
                                        <p class="text-xs text-muted-foreground">{{ activity.action }}</p>
                                    </div>
                                    <span class="text-xs text-muted-foreground">{{ activity.time }}</span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Quick Stats -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <TrendingUp class="h-5 w-5" />
                                Quick Stats
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-4">
                                <div class="flex justify-between">
                                    <span class="text-sm">Critical Issues</span>
                                    <span class="text-sm font-medium text-red-600">3</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm">Pending Escalations</span>
                                    <span class="text-sm font-medium text-orange-600">7</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm">SLA Breaches</span>
                                    <span class="text-sm font-medium text-red-600">2</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm">Completed Today</span>
                                    <span class="text-sm font-medium text-green-600">15</span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Progress } from '@/components/ui/progress';
import {
    AlertTriangle,
    Shield,
    Ticket,
    Smile,
    Download,
    Plus,
    Zap,
    Tag,
    BarChart3,
    Calendar,
    Users,
    TrendingUp
} from 'lucide-vue-next';

// Mock data
const recentComplaints = [
    {
        id: 1,
        title: 'Product not working as expected',
        customer_name: 'John Doe',
        priority: 'high',
        status: 'open'
    },
    {
        id: 2,
        title: 'Billing issue with monthly subscription',
        customer_name: 'Jane Smith',
        priority: 'medium',
        status: 'in_progress'
    },
    {
        id: 3,
        title: 'Delivery delayed by 3 days',
        customer_name: 'Mike Johnson',
        priority: 'critical',
        status: 'escalated'
    }
];

const upcomingServices = [
    {
        id: 1,
        title: 'Warranty Repair',
        customer_name: 'Sarah Wilson',
        scheduled_date: '2025-08-19',
        status: 'scheduled'
    },
    {
        id: 2,
        title: 'Installation Service',
        customer_name: 'Tom Brown',
        scheduled_date: '2025-08-20',
        status: 'pending'
    }
];

const teamActivities = [
    {
        id: 1,
        user: 'Alex Chen',
        action: 'Resolved complaint #1234',
        time: '2 min ago'
    },
    {
        id: 2,
        user: 'Maria Garcia',
        action: 'Created service ticket #5678',
        time: '5 min ago'
    },
    {
        id: 3,
        user: 'David Kim',
        action: 'Updated after-sales service #9012',
        time: '10 min ago'
    }
];

const getPriorityVariant = (priority: string) => {
    switch (priority) {
        case 'critical': return 'destructive';
        case 'high': return 'default';
        case 'medium': return 'secondary';
        case 'low': return 'outline';
        default: return 'outline';
    }
};

const getStatusVariant = (status: string) => {
    switch (status) {
        case 'open': return 'default';
        case 'in_progress': return 'secondary';
        case 'resolved': return 'outline';
        case 'closed': return 'outline';
        case 'escalated': return 'destructive';
        default: return 'outline';
    }
};

const getServiceStatusVariant = (status: string) => {
    switch (status) {
        case 'scheduled': return 'default';
        case 'pending': return 'secondary';
        case 'in_progress': return 'secondary';
        case 'completed': return 'outline';
        default: return 'outline';
    }
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric'
    });
};

const breadcrumbs = [
    { name: 'Dashboard', href: route('dashboard') },
    { name: 'Customer Service', href: '#' },
];
</script>
