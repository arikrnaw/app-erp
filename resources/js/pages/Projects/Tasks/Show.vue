<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Progress } from '@/components/ui/progress';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { ArrowLeft, Edit, ClipboardList, Calendar, User, Clock, AlertTriangle, CheckCircle, XCircle, MapPin, Phone, Mail, User as UserIcon, Building, Target, Eye, TrendingUp } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Label } from '@/components/ui/label';

interface ProjectTask {
    id: number;
    name: string;
    description: string;
    status: string;
    priority: string;
    type: string;
    project: {
        id: number;
        name: string;
        code: string;
        description?: string;
        status?: string;
        start_date?: string;
        end_date?: string;
    };
    parent_task?: {
        id: number;
        name: string;
    };
    assigned_to?: {
        id: number;
        name: string;
        email: string;
        avatar?: string;
    };
    created_by?: {
        id: number;
        name: string;
        email: string;
        avatar?: string;
    };
    start_date: string;
    due_date: string;
    estimated_hours: number;
    actual_hours: number;
    progress_percentage: number;
    created_at: string;
    updated_at: string;
}

const props = defineProps<{
    task: ProjectTask;
}>();

const getStatusBadgeVariant = (status: string) => {
    switch (status) {
        case 'todo': return 'outline';
        case 'in_progress': return 'default';
        case 'review': return 'secondary';
        case 'testing': return 'secondary';
        case 'completed': return 'secondary';
        case 'cancelled': return 'destructive';
        default: return 'secondary';
    }
};

const getPriorityBadgeVariant = (priority: string) => {
    switch (priority) {
        case 'urgent': return 'destructive';
        case 'high': return 'default';
        case 'medium': return 'secondary';
        case 'low': return 'outline';
        default: return 'secondary';
    }
};

const getStatusIcon = (status: string) => {
    switch (status) {
        case 'todo': return Clock;
        case 'in_progress': return AlertTriangle;
        case 'review': return Eye;
        case 'testing': return Eye;
        case 'completed': return CheckCircle;
        case 'cancelled': return XCircle;
        default: return Clock;
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

const isOverdue = (dueDate: string, status: string) => {
    if (status === 'completed') return false;
    return new Date(dueDate) < new Date();
};

const getDaysRemaining = (dueDate: string) => {
    const today = new Date();
    const due = new Date(dueDate);
    const diffTime = due.getTime() - today.getTime();
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays;
};

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Project Management', href: '/projects' },
    { title: 'Tasks', href: '/projects/tasks' },
    { title: props.task?.name || 'Task Details', href: '#' },
];
</script>

<template>

    <Head :title="`${task?.name || 'Task'} - Task Details`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link :href="route('projects.tasks.index')">
                    <Button variant="outline" size="sm">
                        <ArrowLeft class="mr-2 h-4 w-4" />
                        Back to Tasks
                    </Button>
                    </Link>
                    <div>
                        <h1 class="text-3xl font-bold tracking-tight">{{ task?.name || 'Task Details' }}</h1>
                        <p class="text-muted-foreground">
                            {{ task?.description || 'No description available' }}
                        </p>
                    </div>
                </div>
                <Link :href="route('projects.tasks.edit', task?.id)">
                <Button>
                    <Edit class="mr-2 h-4 w-4" />
                    Edit Task
                </Button>
                </Link>
            </div>

            <!-- Overview Cards -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Status</CardTitle>
                        <component :is="getStatusIcon(task?.status)" class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <Badge :variant="getStatusBadgeVariant(task?.status)">
                            {{ task?.status?.replace('_', ' ').toUpperCase() || 'UNKNOWN' }}
                        </Badge>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Priority</CardTitle>
                        <AlertTriangle class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <Badge :variant="getPriorityBadgeVariant(task?.priority)">
                            {{ task?.priority?.toUpperCase() || 'MEDIUM' }}
                        </Badge>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Progress</CardTitle>
                        <TrendingUp class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ Math.round(task?.progress_percentage || 0) }}%</div>
                        <Progress :value="task?.progress_percentage || 0" class="mt-2" />
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Hours</CardTitle>
                        <Clock class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ task?.actual_hours || 0 }}h</div>
                        <p class="text-xs text-muted-foreground">
                            {{ task?.estimated_hours || 0 }}h estimated
                        </p>
                    </CardContent>
                </Card>
            </div>

            <div class="grid gap-6 md:grid-cols-3">
                <!-- Basic Information -->
                <div class="md:col-span-2 space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>Task Information</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <Label class="text-sm font-medium">Task Name</Label>
                                    <p class="text-sm text-muted-foreground">{{ task?.name || 'N/A' }}</p>
                                </div>
                                <div>
                                    <Label class="text-sm font-medium">Type</Label>
                                    <p class="text-sm text-muted-foreground">{{ task?.type || 'N/A' }}</p>
                                </div>
                                <div>
                                    <Label class="text-sm font-medium">Start Date</Label>
                                    <p class="text-sm text-muted-foreground">{{ task?.start_date ?
                                        formatDate(task.start_date) : 'N/A' }}</p>
                                </div>
                                <div>
                                    <Label class="text-sm font-medium">Due Date</Label>
                                    <p class="text-sm"
                                        :class="{ 'text-red-500 font-medium': task?.due_date && isOverdue(task.due_date, task.status) }">
                                        {{ task?.due_date ? formatDate(task.due_date) : 'N/A' }}
                                        <span v-if="task?.due_date && isOverdue(task.due_date, task.status)"
                                            class="text-xs text-red-500 ml-2">
                                            (Overdue)
                                        </span>
                                    </p>
                                </div>
                                <div>
                                    <Label class="text-sm font-medium">Estimated Hours</Label>
                                    <p class="text-sm text-muted-foreground">{{ task?.estimated_hours || 0 }} hours</p>
                                </div>
                                <div>
                                    <Label class="text-sm font-medium">Actual Hours</Label>
                                    <p class="text-sm text-muted-foreground">{{ task?.actual_hours || 0 }} hours</p>
                                </div>
                            </div>
                            <div>
                                <Label class="text-sm font-medium">Description</Label>
                                <p class="text-sm text-muted-foreground mt-1">{{ task?.description || `No description
                                    available` }}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Project Information -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Target class="h-5 w-5" />
                                Project Information
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div v-if="task?.project" class="space-y-4">
                                <div class="grid gap-4 md:grid-cols-2">
                                    <div>
                                        <Label class="text-sm font-medium">Project Name</Label>
                                        <p class="text-sm text-muted-foreground">{{ task.project.name }}</p>
                                    </div>
                                    <div>
                                        <Label class="text-sm font-medium">Project Code</Label>
                                        <p class="text-sm text-muted-foreground">{{ task.project.code }}</p>
                                    </div>
                                    <div>
                                        <Label class="text-sm font-medium">Project Status</Label>
                                        <p class="text-sm text-muted-foreground">{{ task.project.status || 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <Label class="text-sm font-medium">Project Timeline</Label>
                                        <p class="text-sm text-muted-foreground">
                                            {{ task.project.start_date ? formatDate(task.project.start_date) : 'N/A' }}
                                            -
                                            {{ task.project.end_date ? formatDate(task.project.end_date) : 'N/A' }}
                                        </p>
                                    </div>
                                </div>
                                <div v-if="task.project.description">
                                    <Label class="text-sm font-medium">Project Description</Label>
                                    <p class="text-sm text-muted-foreground mt-1">{{ task.project.description }}</p>
                                </div>
                            </div>
                            <div v-else class="text-center py-4">
                                <p class="text-muted-foreground">No project information available</p>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Team & Contact -->
                <div class="space-y-6">
                    <!-- Assigned To -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <User class="h-5 w-5" />
                                Assigned To
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div v-if="task?.assigned_to" class="space-y-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center">
                                        <UserIcon class="h-5 w-5 text-primary" />
                                    </div>
                                    <div>
                                        <p class="font-medium">{{ task.assigned_to.name }}</p>
                                        <p class="text-sm text-muted-foreground">{{ task.assigned_to.email }}</p>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-center py-4">
                                <User class="mx-auto h-8 w-8 text-muted-foreground" />
                                <p class="mt-2 text-sm text-muted-foreground">Unassigned</p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Created By -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <User class="h-5 w-5" />
                                Created By
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div v-if="task?.created_by" class="space-y-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center">
                                        <UserIcon class="h-5 w-5 text-primary" />
                                    </div>
                                    <div>
                                        <p class="font-medium">{{ task.created_by.name }}</p>
                                        <p class="text-sm text-muted-foreground">{{ task.created_by.email }}</p>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-center py-4">
                                <User class="mx-auto h-8 w-8 text-muted-foreground" />
                                <p class="mt-2 text-sm text-muted-foreground">Unknown</p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Parent Task -->
                    <Card v-if="task?.parent_task">
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <ClipboardList class="h-5 w-5" />
                                Parent Task
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-2">
                                <p class="font-medium">{{ task.parent_task.name }}</p>
                                <Link :href="route('projects.tasks.show', task.parent_task.id)"
                                    class="text-sm text-primary hover:underline">
                                View Parent Task
                                </Link>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Timeline -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Calendar class="h-5 w-5" />
                                Timeline
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="space-y-3">
                                <div class="flex justify-between text-sm">
                                    <span class="text-muted-foreground">Created</span>
                                    <span>{{ task?.created_at ? formatDateTime(task.created_at) : 'N/A' }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-muted-foreground">Last Updated</span>
                                    <span>{{ task?.updated_at ? formatDateTime(task.updated_at) : 'N/A' }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-muted-foreground">Days Remaining</span>
                                    <span
                                        :class="{ 'text-red-500 font-medium': task?.due_date && getDaysRemaining(task.due_date) < 0 }">
                                        {{ task?.due_date ? getDaysRemaining(task.due_date) : 'N/A' }}
                                    </span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
