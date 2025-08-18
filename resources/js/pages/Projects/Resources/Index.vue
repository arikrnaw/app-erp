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
import { Plus, Search, Filter, Users2, Calendar, DollarSign, Clock, Eye, Edit } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

interface ProjectResource {
    id: number;
    resource_name: string;
    description: string;
    resource_type: string;
    role: string;
    hourly_rate: number;
    allocated_hours: number;
    actual_hours: number;
    availability: string;
    status: string;
    utilization_percentage: number;
    start_date: string;
    end_date: string;
    project: {
        id: number;
        name: string;
        code: string;
    };
    user: {
        id: number;
        name: string;
        email: string;
    };
}

interface Statistics {
    total_resources: number;
    human_resources: number;
    equipment_resources: number;
    available_resources: number;
    allocated_resources: number;
    total_hourly_cost: number;
    total_allocated_hours: number;
    total_actual_hours: number;
}

const props = defineProps<{
    resources?: ProjectResource[];
    statistics?: Statistics;
    pagination?: any;
}>();

const search = ref('');
const typeFilter = ref('');
const roleFilter = ref('');
const statusFilter = ref('');
const availabilityFilter = ref('');
const projectFilter = ref('');

const filteredResources = computed(() => {
    let filtered = props.resources || [];

    if (search.value) {
        filtered = filtered.filter(resource =>
            resource.resource_name.toLowerCase().includes(search.value.toLowerCase()) ||
            resource.description?.toLowerCase().includes(search.value.toLowerCase()) ||
            resource.project.name.toLowerCase().includes(search.value.toLowerCase())
        );
    }

    if (typeFilter.value) {
        filtered = filtered.filter(resource => resource.resource_type === typeFilter.value);
    }

    if (roleFilter.value) {
        filtered = filtered.filter(resource => resource.role === roleFilter.value);
    }

    if (statusFilter.value) {
        filtered = filtered.filter(resource => resource.status === statusFilter.value);
    }

    if (availabilityFilter.value) {
        filtered = filtered.filter(resource => resource.availability === availabilityFilter.value);
    }

    if (projectFilter.value) {
        filtered = filtered.filter(resource => resource.project.id.toString() === projectFilter.value);
    }

    return filtered;
});

const getStatusBadgeVariant = (status: string) => {
    switch (status) {
        case 'available': return 'default';
        case 'allocated': return 'secondary';
        case 'unavailable': return 'destructive';
        case 'overallocated': return 'destructive';
        default: return 'secondary';
    }
};

const getAvailabilityBadgeVariant = (availability: string) => {
    switch (availability) {
        case 'full_time': return 'default';
        case 'part_time': return 'secondary';
        case 'on_demand': return 'outline';
        default: return 'secondary';
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
    { title: 'Resources', href: '/projects/resources' },
];
</script>

<template>

    <Head title="Project Resources" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Project Resources</h1>
                    <p class="text-muted-foreground">
                        Manage and allocate resources for your projects
                    </p>
                </div>
                <Link :href="route('projects.resources.create')">
                <Button>
                    <Plus class="mr-2 h-4 w-4" />
                    New Resource
                </Button>
                </Link>
            </div>

            <!-- Statistics Cards -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Resources</CardTitle>
                        <Users2 class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ statistics?.total_resources || 0 }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ statistics?.human_resources || 0 }} human, {{ statistics?.equipment_resources || 0 }}
                            equipment
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Available</CardTitle>
                        <Clock class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ statistics?.available_resources || 0 }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ statistics?.allocated_resources || 0 }} allocated
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
                            {{ statistics?.total_allocated_hours || 0 }}h allocated
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Hourly Cost</CardTitle>
                        <DollarSign class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatCurrency(statistics?.total_hourly_cost || 0) }}</div>
                        <p class="text-xs text-muted-foreground">
                            Total hourly rate
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
                                <Input id="search" v-model="search" placeholder="Search resources..." class="pl-9" />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="type">Resource Type</Label>
                            <Select v-model="typeFilter">
                                <SelectTrigger>
                                    <SelectValue placeholder="All types" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">All types</SelectItem>
                                    <SelectItem value="human">Human</SelectItem>
                                    <SelectItem value="equipment">Equipment</SelectItem>
                                    <SelectItem value="material">Material</SelectItem>
                                    <SelectItem value="software">Software</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="space-y-2">
                            <Label for="role">Role</Label>
                            <Select v-model="roleFilter">
                                <SelectTrigger>
                                    <SelectValue placeholder="All roles" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">All roles</SelectItem>
                                    <SelectItem value="developer">Developer</SelectItem>
                                    <SelectItem value="designer">Designer</SelectItem>
                                    <SelectItem value="tester">Tester</SelectItem>
                                    <SelectItem value="analyst">Analyst</SelectItem>
                                    <SelectItem value="manager">Manager</SelectItem>
                                    <SelectItem value="consultant">Consultant</SelectItem>
                                    <SelectItem value="other">Other</SelectItem>
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
                                    <SelectItem value="available">Available</SelectItem>
                                    <SelectItem value="allocated">Allocated</SelectItem>
                                    <SelectItem value="unavailable">Unavailable</SelectItem>
                                    <SelectItem value="overallocated">Overallocated</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="space-y-2">
                            <Label for="availability">Availability</Label>
                            <Select v-model="availabilityFilter">
                                <SelectTrigger>
                                    <SelectValue placeholder="All availability" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">All availability</SelectItem>
                                    <SelectItem value="full_time">Full Time</SelectItem>
                                    <SelectItem value="part_time">Part Time</SelectItem>
                                    <SelectItem value="on_demand">On Demand</SelectItem>
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

            <!-- Resources Table -->
            <Card>
                <CardHeader>
                    <CardTitle>Resources</CardTitle>
                    <CardDescription>
                        A list of all project resources
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Resource</TableHead>
                                <TableHead>Project</TableHead>
                                <TableHead>Type</TableHead>
                                <TableHead>Role</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Hourly Rate</TableHead>
                                <TableHead>Hours</TableHead>
                                <TableHead>Utilization</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="resource in filteredResources" :key="resource.id">
                                <TableCell>
                                    <div>
                                        <div class="font-medium">{{ resource.resource_name }}</div>
                                        <div class="text-sm text-muted-foreground">{{ resource.description }}</div>
                                        <div v-if="resource.user" class="text-xs text-muted-foreground">
                                            {{ resource.user.name }} ({{ resource.user.email }})
                                        </div>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div>
                                        <div class="font-medium">{{ resource.project.name }}</div>
                                        <div class="text-sm text-muted-foreground">{{ resource.project.code }}</div>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <Badge variant="outline">{{ resource.resource_type }}</Badge>
                                </TableCell>
                                <TableCell>
                                    <Badge variant="outline">{{ resource.role }}</Badge>
                                </TableCell>
                                <TableCell>
                                    <Badge :variant="getStatusBadgeVariant(resource.status)">
                                        {{ resource.status.toUpperCase() }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <div class="font-medium">{{ formatCurrency(resource.hourly_rate) }}</div>
                                </TableCell>
                                <TableCell>
                                    <div>
                                        <div class="text-sm">{{ resource.actual_hours }}h actual</div>
                                        <div class="text-xs text-muted-foreground">{{ resource.allocated_hours }}h
                                            allocated</div>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div class="flex items-center gap-2">
                                        <div class="w-16 bg-secondary rounded-full h-2">
                                            <div class="bg-primary h-2 rounded-full transition-all"
                                                :style="{ width: `${resource.utilization_percentage}%` }"></div>
                                        </div>
                                        <span class="text-sm">{{ Math.round(resource.utilization_percentage) }}%</span>
                                    </div>
                                </TableCell>
                                <TableCell class="text-right">
                                    <div class="flex justify-end gap-2">
                                        <Link :href="route('projects.resources.show', resource.id)">
                                        <Button variant="outline" size="sm">
                                            <Eye class="h-4 w-4" />
                                        </Button>
                                        </Link>
                                        <Link :href="route('projects.resources.edit', resource.id)">
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
                    <div v-if="filteredResources.length === 0" class="text-center py-8">
                        <Users2 class="mx-auto h-12 w-12 text-muted-foreground" />
                        <h3 class="mt-4 text-lg font-semibold">No resources found</h3>
                        <p class="mt-2 text-muted-foreground">
                            Get started by adding your first resource.
                        </p>
                        <Link :href="route('projects.resources.create')" class="mt-4">
                        <Button>
                            <Plus class="mr-2 h-4 w-4" />
                            Add Resource
                        </Button>
                        </Link>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
