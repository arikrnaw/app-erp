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
import { Plus, Search, Filter, Calendar, CheckCircle, Clock, AlertTriangle, Eye, Edit } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

interface ProjectMilestone {
    id: number;
    name: string;
    description: string;
    status: string;
    planned_date: string;
    actual_date: string;
    progress_percentage: number;
    priority: string;
    project: {
        id: number;
        name: string;
        code: string;
    };
    responsible_person: {
        id: number;
        name: string;
        email: string;
    };
}

interface Statistics {
    total_milestones: number;
    completed_milestones: number;
    in_progress_milestones: number;
    planned_milestones: number;
    overdue_milestones: number;
    high_priority_milestones: number;
    critical_milestones: number;
    average_progress: number;
}

const props = defineProps<{
    milestones?: ProjectMilestone[];
    statistics?: Statistics;
    pagination?: any;
}>();

const search = ref('');
const statusFilter = ref('');
const priorityFilter = ref('');
const projectFilter = ref('');

const filteredMilestones = computed(() => {
    let filtered = props.milestones || [];

    if (search.value) {
        filtered = filtered.filter(milestone =>
            milestone.name.toLowerCase().includes(search.value.toLowerCase()) ||
            milestone.description?.toLowerCase().includes(search.value.toLowerCase()) ||
            milestone.project.name.toLowerCase().includes(search.value.toLowerCase())
        );
    }

    if (statusFilter.value) {
        filtered = filtered.filter(milestone => milestone.status === statusFilter.value);
    }

    if (priorityFilter.value) {
        filtered = filtered.filter(milestone => milestone.priority === priorityFilter.value);
    }

    if (projectFilter.value) {
        filtered = filtered.filter(milestone => milestone.project.id.toString() === projectFilter.value);
    }

    return filtered;
});

const getStatusBadgeVariant = (status: string) => {
    switch (status) {
        case 'completed': return 'secondary';
        case 'in_progress': return 'default';
        case 'planned': return 'outline';
        case 'overdue': return 'destructive';
        case 'cancelled': return 'destructive';
        default: return 'secondary';
    }
};

const getPriorityBadgeVariant = (priority: string) => {
    switch (priority) {
        case 'critical': return 'destructive';
        case 'high': return 'default';
        case 'medium': return 'secondary';
        case 'low': return 'outline';
        default: return 'secondary';
    }
};

const getStatusIcon = (status: string) => {
    switch (status) {
        case 'completed': return CheckCircle;
        case 'in_progress': return Clock;
        case 'planned': return Calendar;
        case 'overdue': return AlertTriangle;
        case 'cancelled': return AlertTriangle;
        default: return Calendar;
    }
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const isOverdue = (plannedDate: string, status: string) => {
    if (status === 'completed') return false;
    return new Date(plannedDate) < new Date();
};

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Project Management', href: '/projects' },
    { title: 'Milestones', href: '/projects/milestones' },
];
</script>

<template>

    <Head title="Project Milestones" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Project Milestones</h1>
                    <p class="text-muted-foreground">
                        Track and manage project milestones and deliverables
                    </p>
                </div>
                <Link :href="route('projects.milestones.create')">
                <Button>
                    <Plus class="mr-2 h-4 w-4" />
                    New Milestone
                </Button>
                </Link>
            </div>

            <!-- Statistics Cards -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Milestones</CardTitle>
                        <Calendar class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ statistics?.total_milestones || 0 }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ statistics?.completed_milestones || 0 }} completed
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">In Progress</CardTitle>
                        <Clock class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ statistics?.in_progress_milestones || 0 }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ statistics?.planned_milestones || 0 }} planned
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Overdue</CardTitle>
                        <AlertTriangle class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-red-500">{{ statistics?.overdue_milestones || 0 }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ statistics?.critical_milestones || 0 }} critical
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Average Progress</CardTitle>
                        <CheckCircle class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ Math.round(statistics?.average_progress || 0) }}%</div>
                        <p class="text-xs text-muted-foreground">
                            Overall completion rate
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
                                <Input id="search" v-model="search" placeholder="Search milestones..." class="pl-9" />
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
                                    <SelectItem value="planned">Planned</SelectItem>
                                    <SelectItem value="in_progress">In Progress</SelectItem>
                                    <SelectItem value="completed">Completed</SelectItem>
                                    <SelectItem value="overdue">Overdue</SelectItem>
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
                                    <SelectItem value="critical">Critical</SelectItem>
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
                    </div>
                </CardContent>
            </Card>

            <!-- Milestones Table -->
            <Card>
                <CardHeader>
                    <CardTitle>Milestones</CardTitle>
                    <CardDescription>
                        A list of all project milestones
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Milestone</TableHead>
                                <TableHead>Project</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Priority</TableHead>
                                <TableHead>Responsible</TableHead>
                                <TableHead>Progress</TableHead>
                                <TableHead>Planned Date</TableHead>
                                <TableHead>Actual Date</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="milestone in filteredMilestones" :key="milestone.id">
                                <TableCell>
                                    <div>
                                        <div class="font-medium">{{ milestone.name }}</div>
                                        <div class="text-sm text-muted-foreground">{{ milestone.description }}</div>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div>
                                        <div class="font-medium">{{ milestone.project.name }}</div>
                                        <div class="text-sm text-muted-foreground">{{ milestone.project.code }}</div>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <Badge :variant="getStatusBadgeVariant(milestone.status)">
                                        <component :is="getStatusIcon(milestone.status)" class="mr-1 h-3 w-3" />
                                        {{ milestone.status.replace('_', ' ').toUpperCase() }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <Badge :variant="getPriorityBadgeVariant(milestone.priority)">
                                        {{ milestone.priority.toUpperCase() }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <div v-if="milestone.responsible_person">
                                        <div class="font-medium">{{ milestone.responsible_person.name }}</div>
                                        <div class="text-sm text-muted-foreground">{{ milestone.responsible_person.email
                                        }}</div>
                                    </div>
                                    <span v-else class="text-muted-foreground">Unassigned</span>
                                </TableCell>
                                <TableCell>
                                    <div class="flex items-center gap-2">
                                        <div class="w-16 bg-secondary rounded-full h-2">
                                            <div class="bg-primary h-2 rounded-full transition-all"
                                                :style="{ width: `${milestone.progress_percentage}%` }"></div>
                                        </div>
                                        <span class="text-sm">{{ Math.round(milestone.progress_percentage) }}%</span>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div>
                                        <div class="text-sm"
                                            :class="{ 'text-red-500 font-medium': isOverdue(milestone.planned_date, milestone.status) }">
                                            {{ formatDate(milestone.planned_date) }}
                                        </div>
                                        <div v-if="isOverdue(milestone.planned_date, milestone.status)"
                                            class="text-xs text-red-500">
                                            Overdue
                                        </div>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div v-if="milestone.actual_date" class="text-sm">
                                        {{ formatDate(milestone.actual_date) }}
                                    </div>
                                    <span v-else class="text-muted-foreground">Not completed</span>
                                </TableCell>
                                <TableCell class="text-right">
                                    <div class="flex justify-end gap-2">
                                        <Link :href="route('projects.milestones.show', milestone.id)">
                                        <Button variant="outline" size="sm">
                                            <Eye class="h-4 w-4" />
                                        </Button>
                                        </Link>
                                        <Link :href="route('projects.milestones.edit', milestone.id)">
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
                    <div v-if="filteredMilestones.length === 0" class="text-center py-8">
                        <Calendar class="mx-auto h-12 w-12 text-muted-foreground" />
                        <h3 class="mt-4 text-lg font-semibold">No milestones found</h3>
                        <p class="mt-2 text-muted-foreground">
                            Get started by creating your first milestone.
                        </p>
                        <Link :href="route('projects.milestones.create')" class="mt-4">
                        <Button>
                            <Plus class="mr-2 h-4 w-4" />
                            Create Milestone
                        </Button>
                        </Link>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
