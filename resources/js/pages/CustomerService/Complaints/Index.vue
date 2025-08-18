<template>

    <Head title="Customer Complaints" />
    <AppLayout>
        <template #header>
            <AppSidebarHeader :breadcrumbs="breadcrumbs" />
        </template>

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Customer Complaints</h1>
                    <p class="text-muted-foreground">
                        Manage and track customer complaints and issues
                    </p>
                </div>
                <Link :href="route('customer-service.complaints.create')">
                <Button>
                    <Plus class="h-4 w-4 mr-2" />
                    New Complaint
                </Button>
                </Link>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center gap-2">
                            <div class="p-2 bg-red-100 dark:bg-red-900/20 rounded-lg">
                                <AlertTriangle class="h-5 w-5 text-red-600 dark:text-red-400" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Total Complaints</p>
                                <p class="text-2xl font-bold">{{ statistics.total || 0 }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center gap-2">
                            <div class="p-2 bg-orange-100 dark:bg-orange-900/20 rounded-lg">
                                <Clock class="h-5 w-5 text-orange-600 dark:text-orange-400" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Open</p>
                                <p class="text-2xl font-bold">{{ statistics.open || 0 }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center gap-2">
                            <div class="p-2 bg-green-100 dark:bg-green-900/20 rounded-lg">
                                <CheckCircle class="h-5 w-5 text-green-600 dark:text-green-400" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Resolved</p>
                                <p class="text-2xl font-bold">{{ statistics.resolved || 0 }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center gap-2">
                            <div class="p-2 bg-red-100 dark:bg-red-900/20 rounded-lg">
                                <AlertOctagon class="h-5 w-5 text-red-600 dark:text-red-400" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Critical</p>
                                <p class="text-2xl font-bold">{{ statistics.critical || 0 }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Filters -->
            <Card>
                <CardHeader>
                    <CardTitle>Filters</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div class="space-y-2">
                            <Label for="search">Search</Label>
                            <Input id="search" v-model="filters.search" placeholder="Search complaints..."
                                @input="debouncedSearch" />
                        </div>

                        <div class="space-y-2">
                            <Label for="status">Status</Label>
                            <Select v-model="filters.status">
                                <SelectTrigger>
                                    <SelectValue placeholder="All statuses" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">All statuses</SelectItem>
                                    <SelectItem value="open">Open</SelectItem>
                                    <SelectItem value="in_progress">In Progress</SelectItem>
                                    <SelectItem value="resolved">Resolved</SelectItem>
                                    <SelectItem value="closed">Closed</SelectItem>
                                    <SelectItem value="escalated">Escalated</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="space-y-2">
                            <Label for="priority">Priority</Label>
                            <Select v-model="filters.priority">
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
                            <Label for="complaint_type">Type</Label>
                            <Select v-model="filters.complaint_type">
                                <SelectTrigger>
                                    <SelectValue placeholder="All types" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">All types</SelectItem>
                                    <SelectItem value="product_issue">Product Issue</SelectItem>
                                    <SelectItem value="service_issue">Service Issue</SelectItem>
                                    <SelectItem value="billing_issue">Billing Issue</SelectItem>
                                    <SelectItem value="delivery_issue">Delivery Issue</SelectItem>
                                    <SelectItem value="technical_issue">Technical Issue</SelectItem>
                                    <SelectItem value="quality_issue">Quality Issue</SelectItem>
                                    <SelectItem value="communication_issue">Communication Issue</SelectItem>
                                    <SelectItem value="other">Other</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 mt-4">
                        <Button @click="applyFilters" :disabled="loading">
                            <Search class="h-4 w-4 mr-2" />
                            Apply Filters
                        </Button>
                        <Button variant="outline" @click="clearFilters" :disabled="loading">
                            <X class="h-4 w-4 mr-2" />
                            Clear
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <!-- Complaints Table -->
            <Card>
                <CardHeader>
                    <CardTitle>Complaints</CardTitle>
                    <CardDescription>
                        {{ complaints.data?.length || 0 }} complaints found
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div v-if="loading" class="flex items-center justify-center py-8">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
                    </div>

                    <div v-else-if="complaints.data?.length === 0" class="text-center py-8">
                        <AlertTriangle class="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                        <h3 class="text-lg font-medium mb-2">No complaints found</h3>
                        <p class="text-muted-foreground mb-4">
                            No complaints match your current filters.
                        </p>
                        <Button @click="clearFilters">Clear filters</Button>
                    </div>

                    <div v-else class="space-y-4">
                        <div v-for="complaint in complaints.data" :key="complaint.id"
                            class="border rounded-lg p-4 hover:bg-muted/50 transition-colors">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <h3 class="font-medium">{{ complaint.title }}</h3>
                                        <Badge :variant="getPriorityVariant(complaint.priority)">
                                            {{ complaint.priority }}
                                        </Badge>
                                        <Badge :variant="getStatusVariant(complaint.status)">
                                            {{ complaint.status }}
                                        </Badge>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-muted-foreground">
                                        <div>
                                            <span class="font-medium">Customer:</span> {{ complaint.customer_name }}
                                        </div>
                                        <div>
                                            <span class="font-medium">Type:</span> {{ complaint.complaint_type_label }}
                                        </div>
                                        <div>
                                            <span class="font-medium">Incident Date:</span> {{
                                                formatDate(complaint.incident_date) }}
                                        </div>
                                    </div>

                                    <p class="text-sm text-muted-foreground mt-2 line-clamp-2">
                                        {{ complaint.description }}
                                    </p>
                                </div>

                                <div class="flex items-center gap-2 ml-4">
                                    <Link :href="route('customer-service.complaints.show', complaint.id)">
                                    <Button variant="outline" size="sm">
                                        <Eye class="h-4 w-4" />
                                    </Button>
                                    </Link>
                                    <Link :href="route('customer-service.complaints.edit', complaint.id)">
                                    <Button variant="outline" size="sm">
                                        <Edit class="h-4 w-4" />
                                    </Button>
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="complaints.data?.length > 0" class="mt-6">
                        <div class="flex items-center justify-between">
                            <p class="text-sm text-muted-foreground">
                                Showing {{ complaints.from || 0 }} to {{ complaints.to || 0 }} of {{ complaints.total ||
                                    0 }}
                                results
                            </p>
                            <div class="flex items-center gap-2">
                                <Button variant="outline" size="sm" :disabled="!complaints.prev_page_url"
                                    @click="loadPage(complaints.current_page - 1)">
                                    Previous
                                </Button>
                                <span class="text-sm">
                                    Page {{ complaints.current_page || 1 }} of {{ complaints.last_page || 1 }}
                                </span>
                                <Button variant="outline" size="sm" :disabled="!complaints.next_page_url"
                                    @click="loadPage(complaints.current_page + 1)">
                                    Next
                                </Button>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, reactive, onMounted } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import {
    AlertTriangle,
    Plus,
    Search,
    X,
    Eye,
    Edit,
    Clock,
    CheckCircle,
    AlertOctagon
} from 'lucide-vue-next';

// Props
const props = defineProps<{
    complaints?: any;
    statistics?: any;
}>();

// Reactive data
const loading = ref(false);
const complaints = ref(props.complaints || { data: [] });
const statistics = ref(props.statistics || {});

const filters = reactive({
    search: '',
    status: '',
    priority: '',
    complaint_type: '',
});

// Methods
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

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const loadComplaints = async (params = {}) => {
    loading.value = true;
    try {
        const response = await fetch(`/api/customer-service/complaints?${new URLSearchParams(params)}`);
        const data = await response.json();
        complaints.value = data.data;
        statistics.value = data.statistics;
    } catch (error) {
        console.error('Error loading complaints:', error);
    } finally {
        loading.value = false;
    }
};

const applyFilters = () => {
    const params: any = {};
    if (filters.search) params.search = filters.search;
    if (filters.status) params.status = filters.status;
    if (filters.priority) params.priority = filters.priority;
    if (filters.complaint_type) params.complaint_type = filters.complaint_type;

    loadComplaints(params);
};

const clearFilters = () => {
    filters.search = '';
    filters.status = '';
    filters.priority = '';
    filters.complaint_type = '';
    loadComplaints();
};

const loadPage = (page: number) => {
    const params: any = { page };
    if (filters.search) params.search = filters.search;
    if (filters.status) params.status = filters.status;
    if (filters.priority) params.priority = filters.priority;
    if (filters.complaint_type) params.complaint_type = filters.complaint_type;

    loadComplaints(params);
};

// Debounced search
let searchTimeout: any;
const debouncedSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 500);
};

// Load initial data
onMounted(() => {
    loadComplaints();
});

const breadcrumbs = [
    { name: 'Dashboard', href: route('dashboard') },
    { name: 'Customer Service', href: route('customer-service.index') },
    { name: 'Complaints', href: '#' },
];
</script>
