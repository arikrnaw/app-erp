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
import { Plus, Search, Filter, DollarSign, TrendingUp, TrendingDown, Eye, Edit } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

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
    invoice_number: string;
    project: {
        id: number;
        name: string;
        code: string;
    };
    approved_by: {
        id: number;
        name: string;
        email: string;
    };
}

interface Statistics {
    total_costs: number;
    total_estimated: number;
    total_actual: number;
    total_budgeted: number;
    approved_costs: number;
    pending_costs: number;
    cost_variance: number;
    cost_variance_percentage: number;
}

const props = defineProps<{
    costs?: ProjectCost[];
    statistics?: Statistics;
    pagination?: any;
}>();

const search = ref('');
const typeFilter = ref('');
const categoryFilter = ref('');
const statusFilter = ref('');
const projectFilter = ref('');

const filteredCosts = computed(() => {
    let filtered = props.costs || [];

    if (search.value) {
        filtered = filtered.filter(cost =>
            cost.cost_name.toLowerCase().includes(search.value.toLowerCase()) ||
            cost.description?.toLowerCase().includes(search.value.toLowerCase()) ||
            cost.vendor?.toLowerCase().includes(search.value.toLowerCase())
        );
    }

    if (typeFilter.value) {
        filtered = filtered.filter(cost => cost.cost_type === typeFilter.value);
    }

    if (categoryFilter.value) {
        filtered = filtered.filter(cost => cost.cost_category === categoryFilter.value);
    }

    if (statusFilter.value) {
        filtered = filtered.filter(cost => cost.status === statusFilter.value);
    }

    if (projectFilter.value) {
        filtered = filtered.filter(cost => cost.project.id.toString() === projectFilter.value);
    }

    return filtered;
});

const getStatusBadgeVariant = (status: string) => {
    switch (status) {
        case 'approved': return 'default';
        case 'pending': return 'secondary';
        case 'rejected': return 'destructive';
        case 'planned': return 'outline';
        case 'incurred': return 'secondary';
        default: return 'secondary';
    }
};

const getTypeBadgeVariant = (type: string) => {
    switch (type) {
        case 'labor': return 'default';
        case 'equipment': return 'secondary';
        case 'material': return 'outline';
        case 'software': return 'secondary';
        case 'travel': return 'outline';
        case 'overhead': return 'secondary';
        case 'other': return 'outline';
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

const getVarianceColor = (actual: number, estimated: number) => {
    const variance = actual - estimated;
    return variance > 0 ? 'text-red-500' : 'text-green-500';
};

const getVarianceIcon = (actual: number, estimated: number) => {
    const variance = actual - estimated;
    return variance > 0 ? TrendingUp : TrendingDown;
};

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Project Management', href: '/projects' },
    { title: 'Costs', href: '/projects/costs' },
];
</script>

<template>

    <Head title="Project Costs" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Project Costs</h1>
                    <p class="text-muted-foreground">
                        Track and manage project costs and expenses
                    </p>
                </div>
                <Link :href="route('projects.costs.create')">
                <Button>
                    <Plus class="mr-2 h-4 w-4" />
                    New Cost
                </Button>
                </Link>
            </div>

            <!-- Statistics Cards -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Actual Costs</CardTitle>
                        <DollarSign class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatCurrency(statistics?.total_actual || 0) }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ formatCurrency(statistics?.total_estimated || 0) }} estimated
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Cost Variance</CardTitle>
                        <component
                            :is="getVarianceIcon(statistics?.total_actual || 0, statistics?.total_estimated || 0)"
                            class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold"
                            :class="getVarianceColor(statistics?.total_actual || 0, statistics?.total_estimated || 0)">
                            {{ formatCurrency(statistics?.cost_variance || 0) }}
                        </div>
                        <p class="text-xs text-muted-foreground">
                            {{ statistics?.cost_variance_percentage || 0 }}% variance
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Approved Costs</CardTitle>
                        <TrendingUp class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ statistics?.approved_costs || 0 }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ statistics?.pending_costs || 0 }} pending
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Budgeted Costs</CardTitle>
                        <DollarSign class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatCurrency(statistics?.total_budgeted || 0) }}</div>
                        <p class="text-xs text-muted-foreground">
                            Total budgeted amount
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
                                <Input id="search" v-model="search" placeholder="Search costs..." class="pl-9" />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="type">Cost Type</Label>
                            <Select v-model="typeFilter">
                                <SelectTrigger>
                                    <SelectValue placeholder="All types" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">All types</SelectItem>
                                    <SelectItem value="labor">Labor</SelectItem>
                                    <SelectItem value="equipment">Equipment</SelectItem>
                                    <SelectItem value="material">Material</SelectItem>
                                    <SelectItem value="software">Software</SelectItem>
                                    <SelectItem value="travel">Travel</SelectItem>
                                    <SelectItem value="overhead">Overhead</SelectItem>
                                    <SelectItem value="other">Other</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="space-y-2">
                            <Label for="category">Category</Label>
                            <Select v-model="categoryFilter">
                                <SelectTrigger>
                                    <SelectValue placeholder="All categories" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">All categories</SelectItem>
                                    <SelectItem value="direct">Direct</SelectItem>
                                    <SelectItem value="indirect">Indirect</SelectItem>
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
                                    <SelectItem value="planned">Planned</SelectItem>
                                    <SelectItem value="incurred">Incurred</SelectItem>
                                    <SelectItem value="pending">Pending</SelectItem>
                                    <SelectItem value="approved">Approved</SelectItem>
                                    <SelectItem value="rejected">Rejected</SelectItem>
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

            <!-- Costs Table -->
            <Card>
                <CardHeader>
                    <CardTitle>Costs</CardTitle>
                    <CardDescription>
                        A list of all project costs and expenses
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Cost Item</TableHead>
                                <TableHead>Project</TableHead>
                                <TableHead>Type</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Estimated</TableHead>
                                <TableHead>Actual</TableHead>
                                <TableHead>Variance</TableHead>
                                <TableHead>Date</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="cost in filteredCosts" :key="cost.id">
                                <TableCell>
                                    <div>
                                        <div class="font-medium">{{ cost.cost_name }}</div>
                                        <div class="text-sm text-muted-foreground">{{ cost.description }}</div>
                                        <div v-if="cost.vendor" class="text-xs text-muted-foreground">
                                            Vendor: {{ cost.vendor }}
                                        </div>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div>
                                        <div class="font-medium">{{ cost.project.name }}</div>
                                        <div class="text-sm text-muted-foreground">{{ cost.project.code }}</div>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <Badge :variant="getTypeBadgeVariant(cost.cost_type)">
                                        {{ cost.cost_type.toUpperCase() }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <Badge :variant="getStatusBadgeVariant(cost.status)">
                                        {{ cost.status.toUpperCase() }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <div class="font-medium">{{ formatCurrency(cost.estimated_cost) }}</div>
                                </TableCell>
                                <TableCell>
                                    <div class="font-medium">{{ formatCurrency(cost.actual_cost) }}</div>
                                </TableCell>
                                <TableCell>
                                    <div class="flex items-center gap-1">
                                        <component :is="getVarianceIcon(cost.actual_cost, cost.estimated_cost)"
                                            class="h-3 w-3"
                                            :class="getVarianceColor(cost.actual_cost, cost.estimated_cost)" />
                                        <span :class="getVarianceColor(cost.actual_cost, cost.estimated_cost)">
                                            {{ formatCurrency(cost.actual_cost - cost.estimated_cost) }}
                                        </span>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div class="text-sm">{{ formatDate(cost.incurred_date) }}</div>
                                </TableCell>
                                <TableCell class="text-right">
                                    <div class="flex justify-end gap-2">
                                        <Link :href="route('projects.costs.show', cost.id)">
                                        <Button variant="outline" size="sm">
                                            <Eye class="h-4 w-4" />
                                        </Button>
                                        </Link>
                                        <Link :href="route('projects.costs.edit', cost.id)">
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
                    <div v-if="filteredCosts.length === 0" class="text-center py-8">
                        <DollarSign class="mx-auto h-12 w-12 text-muted-foreground" />
                        <h3 class="mt-4 text-lg font-semibold">No costs found</h3>
                        <p class="mt-2 text-muted-foreground">
                            Get started by adding your first cost entry.
                        </p>
                        <Link :href="route('projects.costs.create')" class="mt-4">
                        <Button>
                            <Plus class="mr-2 h-4 w-4" />
                            Add Cost
                        </Button>
                        </Link>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
