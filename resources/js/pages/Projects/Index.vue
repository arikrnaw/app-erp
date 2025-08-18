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
import { Plus, Search, Filter, Target, Calendar, DollarSign, Users, TrendingUp, AlertTriangle, CheckCircle, Clock, XCircle } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';

interface Project {
    id: number;
    name: string;
    code: string;
    description: string;
    status: string;
    priority: string;
    start_date: string;
    end_date: string;
    budget: number;
    actual_cost: number;
    progress_percentage: number;
    project_manager: {
        id: number;
        name: string;
        email: string;
    };
    client: {
        id: number;
        name: string;
        company: string;
    };
    company: {
        id: number;
        name: string;
    };
    created_at: string;
    updated_at: string;
}

interface Statistics {
    total_projects: number;
    active_projects: number;
    completed_projects: number;
    overdue_projects: number;
    total_budget: number;
    total_actual_cost: number;
    average_progress: number;
}

const props = defineProps<{
    projects?: Project[];
    statistics?: Statistics;
    pagination?: any;
}>();

const search = ref('');
const statusFilter = ref('');
const priorityFilter = ref('');
const managerFilter = ref('');

const filteredProjects = computed(() => {
    let filtered = props.projects || [];

    if (search.value) {
        filtered = filtered.filter(project =>
            project.name.toLowerCase().includes(search.value.toLowerCase()) ||
            project.code.toLowerCase().includes(search.value.toLowerCase()) ||
            project.description?.toLowerCase().includes(search.value.toLowerCase())
        );
    }

    if (statusFilter.value) {
        filtered = filtered.filter(project => project.status === statusFilter.value);
    }

    if (priorityFilter.value) {
        filtered = filtered.filter(project => project.priority === priorityFilter.value);
    }

    if (managerFilter.value) {
        filtered = filtered.filter(project => project.project_manager.id.toString() === managerFilter.value);
    }

    return filtered;
});

const getStatusBadgeVariant = (status: string) => {
    switch (status) {
        case 'active': return 'default';
        case 'completed': return 'secondary';
        case 'planning': return 'outline';
        case 'on_hold': return 'destructive';
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
        case 'active': return TrendingUp;
        case 'completed': return CheckCircle;
        case 'planning': return Clock;
        case 'on_hold': return AlertTriangle;
        case 'cancelled': return XCircle;
        default: return Clock;
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

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Project Management', href: '/projects' },
    { title: 'Projects', href: '/projects' },
];
</script>

<template>

    <Head title="Projects" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Projects</h1>
                    <p class="text-muted-foreground">
                        Manage your projects, track progress, and monitor resources
                    </p>
                </div>
                <Link :href="route('projects.create')">
                <Button>
                    <Plus class="mr-2 h-4 w-4" />
                    New Project
                </Button>
                </Link>
            </div>

            <!-- Statistics Cards -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Projects</CardTitle>
                        <Target class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ statistics?.total_projects || 0 }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ statistics?.active_projects || 0 }} active
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Active Projects</CardTitle>
                        <TrendingUp class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ statistics?.active_projects || 0 }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ statistics?.overdue_projects || 0 }} overdue
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Budget</CardTitle>
                        <DollarSign class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatCurrency(statistics?.total_budget || 0) }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ formatCurrency(statistics?.total_actual_cost || 0) }} spent
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Average Progress</CardTitle>
                        <Users class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ Math.round(statistics?.average_progress || 0) }}%</div>
                        <p class="text-xs text-muted-foreground">
                            {{ statistics?.completed_projects || 0 }} completed
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
                                <Input id="search" v-model="search" placeholder="Search projects..." class="pl-9" />
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
                                    <SelectItem value="planning">Planning</SelectItem>
                                    <SelectItem value="active">Active</SelectItem>
                                    <SelectItem value="on_hold">On Hold</SelectItem>
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
                            <Label for="manager">Project Manager</Label>
                            <Select v-model="managerFilter">
                                <SelectTrigger>
                                    <SelectValue placeholder="All managers" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">All managers</SelectItem>
                                    <SelectItem value="1">John Doe</SelectItem>
                                    <SelectItem value="2">Jane Smith</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Projects Table -->
            <Card>
                <CardHeader>
                    <CardTitle>Projects</CardTitle>
                    <CardDescription>
                        A list of all projects in your organization
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Project</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Priority</TableHead>
                                <TableHead>Progress</TableHead>
                                <TableHead>Budget</TableHead>
                                <TableHead>Timeline</TableHead>
                                <TableHead>Manager</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="project in filteredProjects" :key="project.id">
                                <TableCell>
                                    <div>
                                        <div class="font-medium">{{ project.name }}</div>
                                        <div class="text-sm text-muted-foreground">{{ project.code }}</div>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <Badge :variant="getStatusBadgeVariant(project.status)">
                                        <component :is="getStatusIcon(project.status)" class="mr-1 h-3 w-3" />
                                        {{ project.status.replace('_', ' ').toUpperCase() }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <Badge :variant="getPriorityBadgeVariant(project.priority)">
                                        {{ project.priority.toUpperCase() }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <div class="flex items-center gap-2">
                                        <div class="w-16 bg-secondary rounded-full h-2">
                                            <div class="bg-primary h-2 rounded-full transition-all"
                                                :style="{ width: `${project.progress_percentage}%` }"></div>
                                        </div>
                                        <span class="text-sm">{{ Math.round(project.progress_percentage) }}%</span>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div>
                                        <div class="font-medium">{{ formatCurrency(project.budget) }}</div>
                                        <div class="text-sm text-muted-foreground">
                                            {{ formatCurrency(project.actual_cost) }} spent
                                        </div>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div>
                                        <div class="text-sm">{{ formatDate(project.start_date) }}</div>
                                        <div class="text-sm text-muted-foreground">
                                            to {{ formatDate(project.end_date) }}
                                        </div>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div>
                                        <div class="font-medium">{{ project.project_manager.name }}</div>
                                        <div class="text-sm text-muted-foreground">{{ project.project_manager.email }}
                                        </div>
                                    </div>
                                </TableCell>
                                <TableCell class="text-right">
                                    <div class="flex justify-end gap-2">
                                        <Link :href="route('projects.show', project.id)">
                                        <Button variant="outline" size="sm">View</Button>
                                        </Link>
                                        <Link :href="route('projects.edit', project.id)">
                                        <Button variant="outline" size="sm">Edit</Button>
                                        </Link>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <!-- Empty State -->
                    <div v-if="filteredProjects.length === 0" class="text-center py-8">
                        <Target class="mx-auto h-12 w-12 text-muted-foreground" />
                        <h3 class="mt-4 text-lg font-semibold">No projects found</h3>
                        <p class="mt-2 text-muted-foreground">
                            Get started by creating your first project.
                        </p>
                        <Link :href="route('projects.create')" class="mt-4">
                        <Button>
                            <Plus class="mr-2 h-4 w-4" />
                            Create Project
                        </Button>
                        </Link>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
