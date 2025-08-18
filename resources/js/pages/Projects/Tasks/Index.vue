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
import { Plus, Search, Filter, ClipboardList, Calendar, User, Clock, AlertTriangle, CheckCircle, XCircle, Eye, Edit } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

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
    };
    assigned_to: {
        id: number;
        name: string;
        email: string;
    };
    created_by: {
        id: number;
        name: string;
        email: string;
    };
    start_date: string;
    due_date: string;
    estimated_hours: number;
    actual_hours: number;
    progress_percentage: number;
    created_at: string;
    updated_at: string;
}

interface Statistics {
    total_tasks: number;
    todo_tasks: number;
    in_progress_tasks: number;
    completed_tasks: number;
    overdue_tasks: number;
    high_priority_tasks: number;
    urgent_tasks: number;
    total_estimated_hours: number;
    total_actual_hours: number;
}

const props = defineProps<{
    tasks?: ProjectTask[];
    statistics?: Statistics;
    pagination?: any;
}>();

const search = ref('');
const statusFilter = ref('');
const priorityFilter = ref('');
const typeFilter = ref('');
const projectFilter = ref('');
const assignedFilter = ref('');

const filteredTasks = computed(() => {
    let filtered = props.tasks || [];

    if (search.value) {
        filtered = filtered.filter(task =>
            task.name.toLowerCase().includes(search.value.toLowerCase()) ||
            task.description?.toLowerCase().includes(search.value.toLowerCase()) ||
            task.project.name.toLowerCase().includes(search.value.toLowerCase())
        );
    }

    if (statusFilter.value) {
        filtered = filtered.filter(task => task.status === statusFilter.value);
    }

    if (priorityFilter.value) {
        filtered = filtered.filter(task => task.priority === priorityFilter.value);
    }

    if (typeFilter.value) {
        filtered = filtered.filter(task => task.type === typeFilter.value);
    }

    if (projectFilter.value) {
        filtered = filtered.filter(task => task.project.id.toString() === projectFilter.value);
    }

    if (assignedFilter.value) {
        filtered = filtered.filter(task => task.assigned_to?.id.toString() === assignedFilter.value);
    }

    return filtered;
});

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

const isOverdue = (dueDate: string, status: string) => {
    if (status === 'completed') return false;
    return new Date(dueDate) < new Date();
};

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Project Management', href: '/projects' },
    { title: 'Tasks', href: '/projects/tasks' },
];
</script>

<template>

    <Head title="Project Tasks" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Project Tasks</h1>
                    <p class="text-muted-foreground">
                        Manage and track all project tasks
                    </p>
                </div>
                <Link :href="route('projects.tasks.create')">
                <Button>
                    <Plus class="mr-2 h-4 w-4" />
                    New Task
                </Button>
                </Link>
            </div>

            <!-- Statistics Cards -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Tasks</CardTitle>
                        <ClipboardList class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ statistics?.total_tasks || 0 }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ statistics?.completed_tasks || 0 }} completed
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">In Progress</CardTitle>
                        <AlertTriangle class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ statistics?.in_progress_tasks || 0 }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ statistics?.todo_tasks || 0 }} pending
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Overdue Tasks</CardTitle>
                        <Clock class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-red-500">{{ statistics?.overdue_tasks || 0 }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ statistics?.urgent_tasks || 0 }} urgent
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Hours</CardTitle>
                        <Calendar class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ statistics?.total_actual_hours || 0 }}h</div>
                        <p class="text-xs text-muted-foreground">
                            {{ statistics?.total_estimated_hours || 0 }}h estimated
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
                                <Input id="search" v-model="search" placeholder="Search tasks..." class="pl-9" />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="status">Status</Label>
                            <Select v-model="statusFilter">
                                <SelectTrigger>
                                    <SelectValue placeholder="All statuses" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">All statuses</SelectItem>
                                    <SelectItem value="todo">To Do</SelectItem>
                                    <SelectItem value="in_progress">In Progress</SelectItem>
                                    <SelectItem value="review">Review</SelectItem>
                                    <SelectItem value="testing">Testing</SelectItem>
                                    <SelectItem value="completed">Completed</SelectItem>
                                    <SelectItem value="cancelled">Cancelled</SelectItem>
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
                                    <SelectItem value="low">Low</SelectItem>
                                    <SelectItem value="medium">Medium</SelectItem>
                                    <SelectItem value="high">High</SelectItem>
                                    <SelectItem value="urgent">Urgent</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="space-y-2">
                            <Label for="type">Type</Label>
                            <Select v-model="typeFilter">
                                <SelectTrigger>
                                    <SelectValue placeholder="All types" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">All types</SelectItem>
                                    <SelectItem value="feature">Feature</SelectItem>
                                    <SelectItem value="bug">Bug</SelectItem>
                                    <SelectItem value="improvement">Improvement</SelectItem>
                                    <SelectItem value="documentation">Documentation</SelectItem>
                                    <SelectItem value="testing">Testing</SelectItem>
                                    <SelectItem value="other">Other</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="space-y-2">
                            <Label for="project">Project</Label>
                            <Select v-model="projectFilter">
                                <SelectTrigger>
                                    <SelectValue placeholder="All projects" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">All projects</SelectItem>
                                    <SelectItem value="1">E-commerce Platform</SelectItem>
                                    <SelectItem value="2">Mobile App</SelectItem>
                                    <SelectItem value="3">ERP System</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="space-y-2">
                            <Label for="assigned">Assigned To</Label>
                            <Select v-model="assignedFilter">
                                <SelectTrigger>
                                    <SelectValue placeholder="All users" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">All users</SelectItem>
                                    <SelectItem value="1">John Doe</SelectItem>
                                    <SelectItem value="2">Jane Smith</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Tasks Table -->
            <Card>
                <CardHeader>
                    <CardTitle>Tasks</CardTitle>
                    <CardDescription>
                        A list of all project tasks
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Task</TableHead>
                                <TableHead>Project</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Priority</TableHead>
                                <TableHead>Assigned To</TableHead>
                                <TableHead>Progress</TableHead>
                                <TableHead>Due Date</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="task in filteredTasks" :key="task.id">
                                <TableCell>
                                    <div>
                                        <div class="font-medium">{{ task.name }}</div>
                                        <div class="text-sm text-muted-foreground">{{ task.description }}</div>
                                        <div class="text-xs text-muted-foreground mt-1">
                                            {{ task.estimated_hours }}h estimated, {{ task.actual_hours }}h actual
                                        </div>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div>
                                        <div class="font-medium">{{ task.project.name }}</div>
                                        <div class="text-sm text-muted-foreground">{{ task.project.code }}</div>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <Badge :variant="getStatusBadgeVariant(task.status)">
                                        <component :is="getStatusIcon(task.status)" class="mr-1 h-3 w-3" />
                                        {{ task.status.replace('_', ' ').toUpperCase() }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <Badge :variant="getPriorityBadgeVariant(task.priority)">
                                        {{ task.priority.toUpperCase() }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <div v-if="task.assigned_to">
                                        <div class="font-medium">{{ task.assigned_to.name }}</div>
                                        <div class="text-sm text-muted-foreground">{{ task.assigned_to.email }}</div>
                                    </div>
                                    <span v-else class="text-muted-foreground">Unassigned</span>
                                </TableCell>
                                <TableCell>
                                    <div class="flex items-center gap-2">
                                        <div class="w-16 bg-secondary rounded-full h-2">
                                            <div class="bg-primary h-2 rounded-full transition-all"
                                                :style="{ width: `${task.progress_percentage}%` }"></div>
                                        </div>
                                        <span class="text-sm">{{ Math.round(task.progress_percentage) }}%</span>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div>
                                        <div class="text-sm"
                                            :class="{ 'text-red-500 font-medium': isOverdue(task.due_date, task.status) }">
                                            {{ formatDate(task.due_date) }}
                                        </div>
                                        <div v-if="isOverdue(task.due_date, task.status)" class="text-xs text-red-500">
                                            Overdue
                                        </div>
                                    </div>
                                </TableCell>
                                <TableCell class="text-right">
                                    <div class="flex justify-end gap-2">
                                        <Link :href="route('projects.tasks.show', task.id)">
                                        <Button variant="outline" size="sm">
                                            <Eye class="h-4 w-4" />
                                        </Button>
                                        </Link>
                                        <Link :href="route('projects.tasks.edit', task.id)">
                                        <Button variant="outline" size="sm">
                                            <Edit class="h-4 w-4" />
                                        </Button>
                                        </Link>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <!-- Empty State -->
                    <div v-if="filteredTasks.length === 0" class="text-center py-8">
                        <ClipboardList class="mx-auto h-12 w-12 text-muted-foreground" />
                        <h3 class="mt-4 text-lg font-semibold">No tasks found</h3>
                        <p class="mt-2 text-muted-foreground">
                            Get started by creating your first task.
                        </p>
                        <Link :href="route('projects.tasks.create')" class="mt-4">
                        <Button>
                            <Plus class="mr-2 h-4 w-4" />
                            Create Task
                        </Button>
                        </Link>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
