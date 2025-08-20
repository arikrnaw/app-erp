<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Bill of Materials</h1>
                <p class="text-muted-foreground">
                    Manage your bill of materials and component structures
                </p>
            </div>
            <Button @click="navigateToCreate">
                <Plus class="h-4 w-4 mr-2" />
                New BOM
            </Button>
        </div>

        <!-- Filters -->
        <Card>
            <CardHeader>
                <CardTitle>Filters</CardTitle>
            </CardHeader>
            <CardContent>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="space-y-2">
                        <Label for="search">Search</Label>
                        <Input id="search" v-model="filters.search" placeholder="Search BOM number, name, or product..."
                            @input="debouncedSearch" />
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
                                <SelectItem value="active">Active</SelectItem>
                                <SelectItem value="inactive">Inactive</SelectItem>
                                <SelectItem value="archived">Archived</SelectItem>
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
                        <Label>&nbsp;</Label>
                        <Button @click="clearFilters" variant="outline" class="w-full">
                            <X class="h-4 w-4 mr-2" />
                            Clear
                        </Button>
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- BOM List -->
        <Card>
            <CardHeader>
                <CardTitle>Bill of Materials</CardTitle>
            </CardHeader>
            <CardContent>
                <div v-if="loading" class="flex justify-center py-8">
                    <Loader2 class="h-8 w-8 animate-spin" />
                </div>
                <div v-else-if="boms.length === 0" class="text-center py-8">
                    <Package class="h-12 w-12 mx-auto text-muted-foreground mb-4" />
                    <h3 class="text-lg font-semibold mb-2">No BOMs found</h3>
                    <p class="text-muted-foreground mb-4">
                        Get started by creating your first bill of materials.
                    </p>
                    <Button @click="navigateToCreate">
                        <Plus class="h-4 w-4 mr-2" />
                        Create BOM
                    </Button>
                </div>
                <div v-else class="space-y-4">
                    <div v-for="bom in boms" :key="bom.id"
                        class="border rounded-lg p-4 hover:bg-muted/50 transition-colors">
                        <div class="flex justify-between items-start">
                            <div class="space-y-2">
                                <div class="flex items-center gap-2">
                                    <h3 class="font-semibold">{{ bom.name }}</h3>
                                    <Badge :variant="getStatusVariant(bom.status)">
                                        {{ bom.status }}
                                    </Badge>
                                </div>
                                <p class="text-sm text-muted-foreground">
                                    {{ bom.bom_number }} • {{ bom.product?.name }}
                                </p>
                                <p class="text-sm text-muted-foreground">
                                    {{ bom.quantity_per_unit }} {{ bom.unit }} per unit
                                </p>
                                <div class="flex items-center gap-4 text-sm text-muted-foreground">
                                                                                <span>Total Cost: {{ formatCurrency(bom.total_cost) }}</span>
                                    <span>{{ bom.items?.length || 0 }} components</span>
                                    <span>Created: {{ formatDate(bom.created_at) }}</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <Button @click="navigateToShow(bom.id)" variant="outline" size="sm">
                                    <Eye class="h-4 w-4" />
                                </Button>
                                <Button @click="navigateToEdit(bom.id)" variant="outline" size="sm">
                                    <Edit class="h-4 w-4" />
                                </Button>
                                <Button @click="deleteBom(bom.id)" variant="outline" size="sm"
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
import { Plus, Eye, Edit, Trash2, Package, X, Loader2 } from 'lucide-vue-next';
import { DataPagination } from '@/components/ui/pagination';
import type { BillOfMaterial, Product } from '@/types/erp';
import type { PaginatedData } from '@/types/erp';
import apiService from '@/services/api';

import { useDebounce } from '@/composables/useDebounce';

// Data
const boms = ref<BillOfMaterial[]>([]);
const products = ref<Product[]>([]);
const loading = ref(false);
const pagination = ref<PaginatedData<BillOfMaterial> | null>(null);

// Filters
const filters = ref({
    search: '',
    status: '',
    product_id: '',
    page: 1,
    per_page: 15,
});

// Debounced search
const debouncedSearch = useDebounce(() => {
    filters.value.page = 1;
    fetchBoms();
}, 300);

// Methods
const fetchBoms = async () => {
    loading.value = true;
    try {
        const response = await apiService.getBillOfMaterials(filters.value);
        boms.value = response.data;
        pagination.value = response;
    } catch (error) {
        console.error('Error fetching BOMs:', error);
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

const clearFilters = () => {
    filters.value = {
        search: '',
        status: '',
        product_id: '',
        page: 1,
        per_page: 15,
    };
    fetchBoms();
};

const handlePageChange = (page: number) => {
    filters.value.page = page;
    fetchBoms();
};

const navigateToCreate = () => {
    router.visit('/manufacturing/bill-of-materials/create');
};

const navigateToShow = (id: number) => {
    router.visit(`/manufacturing/bill-of-materials/${id}`);
};

const navigateToEdit = (id: number) => {
    router.visit(`/manufacturing/bill-of-materials/${id}/edit`);
};

const deleteBom = async (id: number) => {
    if (!confirm('Are you sure you want to delete this BOM?')) return;

    try {
        await apiService.deleteBillOfMaterial(id);
        await fetchBoms();
    } catch (error) {
        console.error('Error deleting BOM:', error);
    }
};

const getStatusVariant = (status: string) => {
    switch (status) {
        case 'active':
            return 'default';
        case 'draft':
            return 'secondary';
        case 'inactive':
            return 'destructive';
        case 'archived':
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
        fetchBoms();
    }
);

watch(
    () => filters.value.product_id,
    () => {
        filters.value.page = 1;
        fetchBoms();
    }
);

// Lifecycle
onMounted(() => {
    fetchBoms();
    fetchProducts();
});
</script>
