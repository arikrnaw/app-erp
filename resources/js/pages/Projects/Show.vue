<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Progress } from '@/components/ui/progress';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { ArrowLeft, Edit, Target, Calendar, DollarSign, Users, TrendingUp, AlertTriangle, CheckCircle, Clock, XCircle, MapPin, Phone, Mail, User, Building } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Project {
    id: number;
    name: string;
    code: string;
    description: string;
    status: string;
    priority: string;
    start_date: string;
    end_date: string;
    actual_start_date: string;
    actual_end_date: string;
    budget: number;
    actual_cost: number;
    progress_percentage: number;
    project_manager: {
        id: number;
        name: string;
        email: string;
        avatar: string;
    };
    client: {
        id: number;
        name: string;
        email: string;
        company: string;
    };
    company: {
        id: number;
        name: string;
    };
    location: string;
    contact_person: string;
    contact_email: string;
    contact_phone: string;
    created_at: string;
    updated_at: string;
}

interface ProjectTask {
    id: number;
    name: string;
    description: string;
    status: string;
    priority: string;
    type: string;
    assigned_to: {
        id: number;
        name: string;
        email: string;
    };
    start_date: string;
    due_date: string;
    estimated_hours: number;
    actual_hours: number;
    progress_percentage: number;
}

interface ProjectResource {
    id: number;
    resource_name: string;
    description: string;
    role: string;
    hourly_rate: number;
    allocated_hours: number;
    actual_hours: number;
    availability: string;
    status: string;
    utilization_percentage: number;
}

interface ProjectCost {
    id: number;
    cost_name: string;
    description: string;
    cost_type: string;
    cost_category: string;
    estimated_cost: number;
    actual_cost: number;
    budgeted_cost: number;
    incurred_date: string;
    status: string;
    vendor: string;
}

interface ProjectMilestone {
    id: number;
    name: string;
    description: string;
    status: string;
    planned_date: string;
    actual_date: string;
    progress_percentage: number;
    priority: string;
    responsible_person: {
        id: number;
        name: string;
        email: string;
    };
}

const props = defineProps<{
    project: Project;
    tasks?: ProjectTask[];
    resources?: ProjectResource[];
    costs?: ProjectCost[];
    milestones?: ProjectMilestone[];
}>();

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

const formatDateTime = (date: string) => {
    return new Date(date).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Project Management', href: '/projects' },
    { title: 'Projects', href: '/projects' },
    { title: props.project.name, href: `/projects/${props.project.id}` },
];

const projectStats = computed(() => ({
    totalTasks: props.tasks?.length || 0,
    completedTasks: props.tasks?.filter(task => task.status === 'completed').length || 0,
    totalCosts: props.costs?.reduce((sum, cost) => sum + cost.actual_cost, 0) || 0,
    totalResources: props.resources?.length || 0,
}));
</script>

<template>

    <Head :title="`${project.name} - Project Details`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">{{ project.name }}</h1>
                    <p class="text-muted-foreground">
                        Project Code: {{ project.code }}
                    </p>
                </div>
                <div class="flex gap-2">
                    <Link :href="route('projects.index')">
                    <Button variant="outline">
                        <ArrowLeft class="mr-2 h-4 w-4" />
                        Back to Projects
                    </Button>
                    </Link>
                    <Link :href="route('projects.edit', project.id)">
                    <Button>
                        <Edit class="mr-2 h-4 w-4" />
                        Edit Project
                    </Button>
                    </Link>
                </div>
            </div>

            <!-- Project Overview -->
            <div class="grid gap-6 md:grid-cols-3">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Status</CardTitle>
                        <component :is="getStatusIcon(project.status)" class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <Badge :variant="getStatusBadgeVariant(project.status)">
                            {{ project.status.replace('_', ' ').toUpperCase() }}
                        </Badge>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Progress</CardTitle>
                        <Target class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ Math.round(project.progress_percentage) }}%</div>
                        <Progress :value="project.progress_percentage" class="mt-2" />
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Budget</CardTitle>
                        <DollarSign class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatCurrency(project.budget) }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ formatCurrency(project.actual_cost) }} spent
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Project Details -->
            <div class="grid gap-6 md:grid-cols-2">
                <!-- Basic Information -->
                <Card>
                    <CardHeader>
                        <CardTitle>Basic Information</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground">Description</Label>
                            <p class="mt-1">{{ project.description || 'No description provided' }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <Label class="text-sm font-medium text-muted-foreground">Priority</Label>
                                <div class="mt-1">
                                    <Badge :variant="getPriorityBadgeVariant(project.priority)">
                                        {{ project.priority.toUpperCase() }}
                                    </Badge>
                                </div>
                            </div>
                            <div>
                                <Label class="text-sm font-medium text-muted-foreground">Timeline</Label>
                                <p class="mt-1 text-sm">
                                    {{ formatDate(project.start_date) }} - {{ formatDate(project.end_date) }}
                                </p>
                            </div>
                        </div>

                        <div v-if="project.location">
                            <Label class="text-sm font-medium text-muted-foreground">Location</Label>
                            <div class="mt-1 flex items-center gap-2">
                                <MapPin class="h-4 w-4 text-muted-foreground" />
                                <span>{{ project.location }}</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Team & Contact -->
                <Card>
                    <CardHeader>
                        <CardTitle>Team & Contact</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground">Project Manager</Label>
                            <div class="mt-1 flex items-center gap-2">
                                <User class="h-4 w-4 text-muted-foreground" />
                                <span>{{ project.project_manager.name }}</span>
                            </div>
                            <p class="text-sm text-muted-foreground">{{ project.project_manager.email }}</p>
                        </div>

                        <div v-if="project.client">
                            <Label class="text-sm font-medium text-muted-foreground">Client</Label>
                            <div class="mt-1 flex items-center gap-2">
                                <Building class="h-4 w-4 text-muted-foreground" />
                                <span>{{ project.client.name }}</span>
                            </div>
                            <p class="text-sm text-muted-foreground">{{ project.client.email }}</p>
                        </div>

                        <div v-if="project.contact_person">
                            <Label class="text-sm font-medium text-muted-foreground">Contact Person</Label>
                            <div class="mt-1 space-y-1">
                                <div class="flex items-center gap-2">
                                    <User class="h-4 w-4 text-muted-foreground" />
                                    <span>{{ project.contact_person }}</span>
                                </div>
                                <div v-if="project.contact_email" class="flex items-center gap-2">
                                    <Mail class="h-4 w-4 text-muted-foreground" />
                                    <span class="text-sm">{{ project.contact_email }}</span>
                                </div>
                                <div v-if="project.contact_phone" class="flex items-center gap-2">
                                    <Phone class="h-4 w-4 text-muted-foreground" />
                                    <span class="text-sm">{{ project.contact_phone }}</span>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Project Statistics -->
            <Card>
                <CardHeader>
                    <CardTitle>Project Statistics</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-4 md:grid-cols-4">
                        <div class="text-center">
                            <div class="text-2xl font-bold">{{ projectStats.totalTasks }}</div>
                            <p class="text-sm text-muted-foreground">Total Tasks</p>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold">{{ projectStats.completedTasks }}</div>
                            <p class="text-sm text-muted-foreground">Completed Tasks</p>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold">{{ formatCurrency(projectStats.totalCosts) }}</div>
                            <p class="text-sm text-muted-foreground">Total Costs</p>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold">{{ projectStats.totalResources }}</div>
                            <p class="text-sm text-muted-foreground">Resources</p>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Project Details Tabs -->
            <Tabs default-value="tasks" class="space-y-4">
                <TabsList>
                    <TabsTrigger value="tasks">Tasks</TabsTrigger>
                    <TabsTrigger value="resources">Resources</TabsTrigger>
                    <TabsTrigger value="costs">Costs</TabsTrigger>
                    <TabsTrigger value="milestones">Milestones</TabsTrigger>
                </TabsList>

                <TabsContent value="tasks" class="space-y-4">
                    <Card>
                        <CardHeader>
                            <CardTitle>Project Tasks</CardTitle>
                            <CardDescription>
                                All tasks associated with this project
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Task</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead>Priority</TableHead>
                                        <TableHead>Assigned To</TableHead>
                                        <TableHead>Progress</TableHead>
                                        <TableHead>Due Date</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="task in tasks" :key="task.id">
                                        <TableCell>
                                            <div>
                                                <div class="font-medium">{{ task.name }}</div>
                                                <div class="text-sm text-muted-foreground">{{ task.description }}</div>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <Badge :variant="getStatusBadgeVariant(task.status)">
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
                                                <div class="text-sm text-muted-foreground">{{ task.assigned_to.email }}
                                                </div>
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
                                            <span v-if="task.due_date">{{ formatDate(task.due_date) }}</span>
                                            <span v-else class="text-muted-foreground">No due date</span>
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </CardContent>
                    </Card>
                </TabsContent>

                <TabsContent value="resources" class="space-y-4">
                    <Card>
                        <CardHeader>
                            <CardTitle>Project Resources</CardTitle>
                            <CardDescription>
                                Resources allocated to this project
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Resource</TableHead>
                                        <TableHead>Role</TableHead>
                                        <TableHead>Hourly Rate</TableHead>
                                        <TableHead>Allocated Hours</TableHead>
                                        <TableHead>Actual Hours</TableHead>
                                        <TableHead>Utilization</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="resource in resources" :key="resource.id">
                                        <TableCell>
                                            <div>
                                                <div class="font-medium">{{ resource.resource_name }}</div>
                                                <div class="text-sm text-muted-foreground">{{ resource.description }}
                                                </div>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <Badge variant="outline">{{ resource.role }}</Badge>
                                        </TableCell>
                                        <TableCell>{{ formatCurrency(resource.hourly_rate) }}</TableCell>
                                        <TableCell>{{ resource.allocated_hours }}h</TableCell>
                                        <TableCell>{{ resource.actual_hours }}h</TableCell>
                                        <TableCell>
                                            <div class="flex items-center gap-2">
                                                <div class="w-16 bg-secondary rounded-full h-2">
                                                    <div class="bg-primary h-2 rounded-full transition-all"
                                                        :style="{ width: `${resource.utilization_percentage}%` }"></div>
                                                </div>
                                                <span class="text-sm">{{ Math.round(resource.utilization_percentage)
                                                    }}%</span>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </CardContent>
                    </Card>
                </TabsContent>

                <TabsContent value="costs" class="space-y-4">
                    <Card>
                        <CardHeader>
                            <CardTitle>Project Costs</CardTitle>
                            <CardDescription>
                                Cost tracking for this project
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Cost Item</TableHead>
                                        <TableHead>Type</TableHead>
                                        <TableHead>Estimated</TableHead>
                                        <TableHead>Actual</TableHead>
                                        <TableHead>Variance</TableHead>
                                        <TableHead>Status</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="cost in costs" :key="cost.id">
                                        <TableCell>
                                            <div>
                                                <div class="font-medium">{{ cost.cost_name }}</div>
                                                <div class="text-sm text-muted-foreground">{{ cost.description }}</div>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <Badge variant="outline">{{ cost.cost_type }}</Badge>
                                        </TableCell>
                                        <TableCell>{{ formatCurrency(cost.estimated_cost) }}</TableCell>
                                        <TableCell>{{ formatCurrency(cost.actual_cost) }}</TableCell>
                                        <TableCell>
                                            <span
                                                :class="cost.actual_cost > cost.estimated_cost ? 'text-red-500' : 'text-green-500'">
                                                {{ formatCurrency(cost.actual_cost - cost.estimated_cost) }}
                                            </span>
                                        </TableCell>
                                        <TableCell>
                                            <Badge :variant="cost.status === 'approved' ? 'default' : 'outline'">
                                                {{ cost.status.toUpperCase() }}
                                            </Badge>
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </CardContent>
                    </Card>
                </TabsContent>

                <TabsContent value="milestones" class="space-y-4">
                    <Card>
                        <CardHeader>
                            <CardTitle>Project Milestones</CardTitle>
                            <CardDescription>
                                Key milestones for this project
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Milestone</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead>Priority</TableHead>
                                        <TableHead>Planned Date</TableHead>
                                        <TableHead>Actual Date</TableHead>
                                        <TableHead>Progress</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="milestone in milestones" :key="milestone.id">
                                        <TableCell>
                                            <div>
                                                <div class="font-medium">{{ milestone.name }}</div>
                                                <div class="text-sm text-muted-foreground">{{ milestone.description }}
                                                </div>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <Badge :variant="getStatusBadgeVariant(milestone.status)">
                                                {{ milestone.status.replace('_', ' ').toUpperCase() }}
                                            </Badge>
                                        </TableCell>
                                        <TableCell>
                                            <Badge :variant="getPriorityBadgeVariant(milestone.priority)">
                                                {{ milestone.priority.toUpperCase() }}
                                            </Badge>
                                        </TableCell>
                                        <TableCell>{{ formatDate(milestone.planned_date) }}</TableCell>
                                        <TableCell>
                                            <span v-if="milestone.actual_date">{{ formatDate(milestone.actual_date)
                                                }}</span>
                                            <span v-else class="text-muted-foreground">Not completed</span>
                                        </TableCell>
                                        <TableCell>
                                            <div class="flex items-center gap-2">
                                                <div class="w-16 bg-secondary rounded-full h-2">
                                                    <div class="bg-primary h-2 rounded-full transition-all"
                                                        :style="{ width: `${milestone.progress_percentage}%` }"></div>
                                                </div>
                                                <span class="text-sm">{{ Math.round(milestone.progress_percentage)
                                                    }}%</span>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </CardContent>
                    </Card>
                </TabsContent>
            </Tabs>
        </div>
    </AppLayout>
</template>
