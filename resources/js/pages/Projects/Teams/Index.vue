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
import { Plus, Search, Filter, Users, Calendar, DollarSign, Clock, Eye, Edit } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

interface ProjectTeam {
    id: number;
    role: string;
    status: string;
    joined_date: string;
    left_date: string;
    allocation_percentage: number;
    hourly_rate: number;
    project: {
        id: number;
        name: string;
        code: string;
    };
    user: {
        id: number;
        name: string;
        email: string;
        avatar: string;
    };
}

interface Statistics {
    total_members: number;
    active_members: number;
    inactive_members: number;
    total_hourly_cost: number;
    average_allocation: number;
    total_projects: number;
    roles_distribution: Record<string, number>;
}

const props = defineProps<{
    teams?: ProjectTeam[];
    statistics?: Statistics;
    pagination?: any;
}>();

const search = ref('');
const roleFilter = ref('');
const statusFilter = ref('');
const projectFilter = ref('');

const filteredTeams = computed(() => {
    let filtered = props.teams || [];

    if (search.value) {
        filtered = filtered.filter(team =>
            team.user.name.toLowerCase().includes(search.value.toLowerCase()) ||
            team.user.email.toLowerCase().includes(search.value.toLowerCase()) ||
            team.project.name.toLowerCase().includes(search.value.toLowerCase())
        );
    }

    if (roleFilter.value) {
        filtered = filtered.filter(team => team.role === roleFilter.value);
    }

    if (statusFilter.value) {
        filtered = filtered.filter(team => team.status === statusFilter.value);
    }

    if (projectFilter.value) {
        filtered = filtered.filter(team => team.project.id.toString() === projectFilter.value);
    }

    return filtered;
});

const getStatusBadgeVariant = (status: string) => {
    switch (status) {
        case 'active': return 'default';
        case 'inactive': return 'secondary';
        case 'on_leave': return 'outline';
        case 'terminated': return 'destructive';
        default: return 'secondary';
    }
};

const getRoleBadgeVariant = (role: string) => {
    switch (role) {
        case 'project_manager': return 'default';
        case 'developer': return 'secondary';
        case 'designer': return 'outline';
        case 'tester': return 'secondary';
        case 'analyst': return 'outline';
        case 'consultant': return 'secondary';
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

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Project Management', href: '/projects' },
    { title: 'Teams', href: '/projects/teams' },
];
</script>

<template>

    <Head title="Project Teams" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Project Teams</h1>
                    <p class="text-muted-foreground">
                        Manage project teams and team members
                    </p>
                </div>
                <Link :href="route('projects.teams.create')">
                <Button>
                    <Plus class="mr-2 h-4 w-4" />
                    Add Team Member
                </Button>
                </Link>
            </div>

            <!-- Statistics Cards -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Members</CardTitle>
                        <Users class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ statistics?.total_members || 0 }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ statistics?.active_members || 0 }} active
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Active Members</CardTitle>
                        <Clock class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ statistics?.active_members || 0 }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ statistics?.inactive_members || 0 }} inactive
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Hourly Cost</CardTitle>
                        <DollarSign class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatCurrency(statistics?.total_hourly_cost || 0) }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ Math.round(statistics?.average_allocation || 0) }}% avg allocation
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Projects</CardTitle>
                        <Calendar class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ statistics?.total_projects || 0 }}</div>
                        <p class="text-xs text-muted-foreground">
                            Active projects
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
                                <Input id="search" v-model="search" placeholder="Search team members..." class="pl-9" />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="role">Role</Label>
                            <Select v-model="roleFilter">
                                <SelectTrigger>
                                    <SelectValue placeholder="All roles" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">All roles</SelectItem>
                                    <SelectItem value="project_manager">Project Manager</SelectItem>
                                    <SelectItem value="developer">Developer</SelectItem>
                                    <SelectItem value="designer">Designer</SelectItem>
                                    <SelectItem value="tester">Tester</SelectItem>
                                    <SelectItem value="analyst">Analyst</SelectItem>
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
                                    <SelectItem value="active">Active</SelectItem>
                                    <SelectItem value="inactive">Inactive</SelectItem>
                                    <SelectItem value="on_leave">On Leave</SelectItem>
                                    <SelectItem value="terminated">Terminated</SelectItem>
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

            <!-- Teams Table -->
            <Card>
                <CardHeader>
                    <CardTitle>Team Members</CardTitle>
                    <CardDescription>
                        A list of all project team members
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Member</TableHead>
                                <TableHead>Project</TableHead>
                                <TableHead>Role</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Allocation</TableHead>
                                <TableHead>Hourly Rate</TableHead>
                                <TableHead>Joined Date</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="team in filteredTeams" :key="team.id">
                                <TableCell>
                                    <div class="flex items-center gap-3">
                                        <div class="h-8 w-8 rounded-full bg-muted flex items-center justify-center">
                                            <span class="text-sm font-medium">{{ team.user.name.charAt(0) }}</span>
                                        </div>
                                        <div>
                                            <div class="font-medium">{{ team.user.name }}</div>
                                            <div class="text-sm text-muted-foreground">{{ team.user.email }}</div>
                                        </div>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div>
                                        <div class="font-medium">{{ team.project.name }}</div>
                                        <div class="text-sm text-muted-foreground">{{ team.project.code }}</div>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <Badge :variant="getRoleBadgeVariant(team.role)">
                                        {{ team.role.replace('_', ' ').toUpperCase() }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <Badge :variant="getStatusBadgeVariant(team.status)">
                                        {{ team.status.replace('_', ' ').toUpperCase() }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <div class="flex items-center gap-2">
                                        <div class="w-16 bg-secondary rounded-full h-2">
                                            <div class="bg-primary h-2 rounded-full transition-all"
                                                :style="{ width: `${team.allocation_percentage}%` }"></div>
                                        </div>
                                        <span class="text-sm">{{ team.allocation_percentage }}%</span>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div class="font-medium">{{ formatCurrency(team.hourly_rate) }}</div>
                                </TableCell>
                                <TableCell>
                                    <div class="text-sm">{{ formatDate(team.joined_date) }}</div>
                                    <div v-if="team.left_date" class="text-xs text-muted-foreground">
                                        Left: {{ formatDate(team.left_date) }}
                                    </div>
                                </TableCell>
                                <TableCell class="text-right">
                                    <div class="flex justify-end gap-2">
                                        <Link :href="route('projects.teams.show', team.id)">
                                        <Button variant="outline" size="sm">
                                            <Eye class="h-4 w-4" />
                                        </Button>
                                        </Link>
                                        <Link :href="route('projects.teams.edit', team.id)">
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
                    <div v-if="filteredTeams.length === 0" class="text-center py-8">
                        <Users class="mx-auto h-12 w-12 text-muted-foreground" />
                        <h3 class="mt-4 text-lg font-semibold">No team members found</h3>
                        <p class="mt-2 text-muted-foreground">
                            Get started by adding your first team member.
                        </p>
                        <Link :href="route('projects.teams.create')" class="mt-4">
                        <Button>
                            <Plus class="mr-2 h-4 w-4" />
                            Add Team Member
                        </Button>
                        </Link>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
