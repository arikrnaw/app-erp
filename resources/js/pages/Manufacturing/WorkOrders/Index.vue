<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Work Orders</h1>
                <p class="text-muted-foreground">
                    Manage your production work orders and operations
                </p>
            </div>
            <Button @click="navigateToCreate">
                <Plus class="h-4 w-4 mr-2" />
                New Work Order
            </Button>
        </div>

        <!-- Filters -->
        <Card>
            <CardHeader>
                <CardTitle>Filters</CardTitle>
            </CardHeader>
            <CardContent>
                <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                    <div class="space-y-2">
                        <Label for="search">Search</Label>
                        <Input id="search" v-model="filters.search"
                            placeholder="Search work order number, name, or product..." @input="debouncedSearch" />
                    </div>
                    <div class="space-y-2">
                        <Label for="status">Status</Label>
                        <Select v-model="filters.status">
                            <SelectTrigger>
                                <SelectValue placeholder="All Status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="">All Status</SelectItem>
                                <SelectItem value="draft">Draft</SelectItem>
                                <SelectItem value="approved">Approved</SelectItem>
                                <SelectItem value="in_progress">In Progress</SelectItem>
                                <SelectItem value="paused">Paused</SelectItem>
                                <SelectItem value="completed">Completed</SelectItem>
                                <SelectItem value="cancelled">Cancelled</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="space-y-2">
                        <Label for="priority">Priority</Label>
                        <Select v-model="filters.priority">
                            <SelectTrigger>
                                <SelectValue placeholder="All Priority" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="">All Priority</SelectItem>
                                <SelectItem value="low">Low</SelectItem>
                                <SelectItem value="medium">Medium</SelectItem>
                                <SelectItem value="high">High</SelectItem>
                                <SelectItem value="urgent">Urgent</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="space-y-2">
                        <Label for="product">Product</Label>
                        <Select v-model="filters.product_id">
                            <SelectTrigger>
                                <SelectValue placeholder="All Products" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="">All Products</SelectItem>
                                <SelectItem v-for="product in products" :key="product.id"
                                    :value="product.id.toString()">
                                    {{ product.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="space-y-2">
                        <Label for="work_center">Work Center</Label>
                        <Select v-model="filters.work_center_id">
                            <SelectTrigger>
                                <SelectValue placeholder="All Work Centers" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="">All Work Centers</SelectItem>
                                <SelectItem v-for="workCenter in workCenters" :key="workCenter.id"
                                    :value="workCenter.id.toString()">
                                    {{ workCenter.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="space-y-2">
                        <Label>&nbsp;</Label>
                        <Button @click="clearFilters" variant="outline" class="w-full">
                            <X class="h-4 w-4 mr-2" />
                            Clear
                        </Button>
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- Work Orders List -->
        <Card>
            <CardHeader>
                <CardTitle>Work Orders</CardTitle>
            </CardHeader>
            <CardContent>
                <div v-if="loading" class="flex justify-center py-8">
                    <Loader2 class="h-8 w-8 animate-spin" />
                </div>
                <div v-else-if="workOrders.length === 0" class="text-center py-8">
                    <Wrench class="h-12 w-12 mx-auto text-muted-foreground mb-4" />
                    <h3 class="text-lg font-semibold mb-2">No work orders found</h3>
                    <p class="text-muted-foreground mb-4">
                        Get started by creating your first work order.
                    </p>
                    <Button @click="navigateToCreate">
                        <Plus class="h-4 w-4 mr-2" />
                        Create Work Order
                    </Button>
                </div>
                <div v-else class="space-y-4">
                    <div v-for="workOrder in workOrders" :key="workOrder.id"
                        class="border rounded-lg p-4 hover:bg-muted/50 transition-colors">
                        <div class="flex justify-between items-start">
                            <div class="space-y-2">
                                <div class="flex items-center gap-2">
                                    <h3 class="font-semibold">{{ workOrder.name }}</h3>
                                    <Badge :variant="getStatusVariant(workOrder.status)">
                                        {{ workOrder.status }}
                                    </Badge>
                                    <Badge :variant="getPriorityVariant(workOrder.priority)">
                                        {{ workOrder.priority }}
                                    </Badge>
                                </div>
                                <p class="text-sm text-muted-foreground">
                                    {{ workOrder.work_order_number }} • {{ workOrder.product?.name }}
                                </p>
                                <p class="text-sm text-muted-foreground">
                                    {{ workOrder.planned_quantity }} {{ workOrder.unit }}
                                </p>
                                <div class="flex items-center gap-4 text-sm text-muted-foreground">
                                    <span>Progress: {{ workOrder.completed_quantity || 0 }} / {{
                                        workOrder.planned_quantity }}</span>
                                    <span>Due: {{ formatDate(workOrder.due_date) }}</span>
                                    <span>Work Center: {{ workOrder.work_center?.name || 'Not assigned' }}</span>
                                </div>
                                <div class="flex items-center gap-4 text-sm text-muted-foreground">
                                    <span>Est. Hours: {{ workOrder.estimated_hours || 0 }}h</span>
                                    <span>Actual Hours: {{ workOrder.actual_hours || 0 }}h</span>
                                    <span>Est. Cost: ${{ formatCurrency(workOrder.estimated_cost) }}</span>
                                    <span>Created: {{ formatDate(workOrder.created_at) }}</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <Button @click="navigateToShow(workOrder.id)" variant="outline" size="sm">
                                    <Eye class="h-4 w-4" />
                                </Button>
                                <Button @click="navigateToEdit(workOrder.id)" variant="outline" size="sm">
                                    <Edit class="h-4 w-4" />
                                </Button>
                                <Button @click="deleteWorkOrder(workOrder.id)" variant="outline" size="sm"
                                    class="text-destructive hover:text-destructive">
                                    <Trash2 class="h-4 w-4" />
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="pagination && pagination.meta && pagination.meta.last_page > 1" class="mt-6">
                    <DataPagination
                        :current-page="pagination.meta.current_page"
                        :total-pages="pagination.meta.last_page"
                        :total-items="pagination.meta.total"
                        :per-page="pagination.meta.per_page"
                        @page-change="handlePageChange"
                    />
                </div>
            </CardContent>
        </Card>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Plus, Eye, Edit, Trash2, Wrench, X, Loader2 } from 'lucide-vue-next';
import { DataPagination } from '@/components/ui/pagination';
import type { WorkOrder, Product, WorkCenter } from '@/types/erp';
import type { PaginatedData } from '@/types/erp';
import apiService from '@/services/api';

import { useDebounce } from '@/composables/useDebounce';

// Data
const workOrders = ref<WorkOrder[]>([]);
const products = ref<Product[]>([]);
const workCenters = ref<WorkCenter[]>([]);
const loading = ref(false);
const pagination = ref<PaginatedData<WorkOrder> | null>(null);

// Filters
const filters = ref({
    search: '',
    status: '',
    priority: '',
    product_id: '',
    work_center_id: '',
    page: 1,
    per_page: 15,
});

// Debounced search
const debouncedSearch = useDebounce(() => {
    filters.value.page = 1;
    fetchWorkOrders();
}, 300);

// Methods
const fetchWorkOrders = async () => {
    loading.value = true;
    try {
        const response = await apiService.getWorkOrders(filters.value);
        workOrders.value = response.data;
        pagination.value = response;
    } catch (error) {
        console.error('Error fetching work orders:', error);
    } finally {
        loading.value = false;
    }
};

const fetchProducts = async () => {
    try {
        const response = await apiService.getProducts();
        products.value = response.data;
    } catch (error) {
        console.error('Error fetching products:', error);
    }
};

const fetchWorkCenters = async () => {
    try {
        const response = await apiService.getWorkCenters();
        workCenters.value = response.data;
    } catch (error) {
        console.error('Error fetching work centers:', error);
    }
};

const clearFilters = () => {
    filters.value = {
        search: '',
        status: '',
        priority: '',
        product_id: '',
        work_center_id: '',
        page: 1,
        per_page: 15,
    };
    fetchWorkOrders();
};

const handlePageChange = (page: number) => {
    filters.value.page = page;
    fetchWorkOrders();
};

const navigateToCreate = () => {
    router.visit('/manufacturing/work-orders/create');
};

const navigateToShow = (id: number) => {
    router.visit(`/manufacturing/work-orders/${id}`);
};

const navigateToEdit = (id: number) => {
    router.visit(`/manufacturing/work-orders/${id}/edit`);
};

const deleteWorkOrder = async (id: number) => {
    if (!confirm('Are you sure you want to delete this work order?')) return;

    try {
        await apiService.deleteWorkOrder(id);
        await fetchWorkOrders();
    } catch (error) {
        console.error('Error deleting work order:', error);
    }
};

const getStatusVariant = (status: string) => {
    switch (status) {
        case 'approved':
            return 'default';
        case 'draft':
            return 'secondary';
        case 'in_progress':
            return 'default';
        case 'paused':
            return 'outline';
        case 'completed':
            return 'default';
        case 'cancelled':
            return 'destructive';
        default:
            return 'secondary';
    }
};

const getPriorityVariant = (priority: string) => {
    switch (priority) {
        case 'urgent':
            return 'destructive';
        case 'high':
            return 'default';
        case 'medium':
            return 'secondary';
        case 'low':
            return 'outline';
        default:
            return 'secondary';
    }
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(amount);
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString();
};

// Watchers
watch(
    () => filters.value.status,
    () => {
        filters.value.page = 1;
        fetchWorkOrders();
    }
);

watch(
    () => filters.value.priority,
    () => {
        filters.value.page = 1;
        fetchWorkOrders();
    }
);

watch(
    () => filters.value.product_id,
    () => {
        filters.value.page = 1;
        fetchWorkOrders();
    }
);

watch(
    () => filters.value.work_center_id,
    () => {
        filters.value.page = 1;
        fetchWorkOrders();
    }
);

// Lifecycle
onMounted(() => {
    fetchWorkOrders();
    fetchProducts();
    fetchWorkCenters();
});
</script>
