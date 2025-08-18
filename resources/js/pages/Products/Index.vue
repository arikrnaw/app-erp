<template>

    <Head title="Products" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl leading-tight">
                    Products
                </h2>
                <Link :href="route('products.create')">
                <Button>
                    <Plus class="w-4 h-4 mr-2" />
                    Add Product
                </Button>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <!-- Search and Filters -->
                        <div class="flex flex-col sm:flex-row gap-4 mb-6">
                            <div class="flex-1">
                                <Input v-model="search" placeholder="Search products..." class="w-full"
                                    @input="debouncedSearch" />
                            </div>
                            <div class="sm:w-48">
                                <select v-model="selectedCategory"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    @change="fetchProducts">
                                    <option value="">All Categories</option>
                                    <option v-for="category in categories" :key="category?.id" :value="category?.id">
                                        {{ category?.name || 'N/A' }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- Products Table -->
                        <div class="rounded-md border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Product</TableHead>
                                        <TableHead>Category</TableHead>
                                        <TableHead>Stock</TableHead>
                                        <TableHead>Price</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead class="text-right">Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-if="loading">
                                        <TableCell colspan="6" class="text-center py-8">
                                            <div class="flex items-center justify-center">
                                                <Loader2 class="w-6 h-6 animate-spin mr-2" />
                                                Loading...
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                    <TableRow v-else-if="!products?.data || products.data.length === 0">
                                        <TableCell colspan="6" class="text-center py-8 text-gray-500">
                                            No products found
                                        </TableCell>
                                    </TableRow>
                                    <TableRow v-else v-for="product in products.data"
                                        :key="product?.id || Math.random()">
                                        <TableCell>
                                            <div>
                                                <div class="font-medium">{{ product?.name || 'N/A' }}</div>
                                                <div class="text-sm text-gray-500">{{ product?.sku || 'N/A' }}</div>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div class="text-sm">{{ product?.category?.name || 'N/A' }}
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div class="text-sm">
                                                {{ product?.stock_quantity || 0 }} {{ product?.unit || 'N/A' }}
                                            </div>
                                            <div v-if="product?.is_low_stock" class="text-xs text-red-600">
                                                Low Stock
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div class="text-sm">${{ formatCurrency(product?.selling_price
                                                || 0)
                                            }}</div>
                                            <div class="text-xs text-gray-500">Cost: ${{
                                                formatCurrency(product?.cost_price ||
                                                    0) }}</div>
                                        </TableCell>
                                        <TableCell>
                                            <Badge :variant="product?.status === 'active' ? 'default' : 'secondary'">
                                                {{ product?.status || 'N/A' }}
                                            </Badge>
                                        </TableCell>
                                        <TableCell class="text-right">
                                            <DropdownMenu>
                                                <DropdownMenuTrigger as-child>
                                                    <Button variant="ghost" class="h-8 w-8 p-0">
                                                        <MoreHorizontal class="h-4 w-4" />
                                                    </Button>
                                                </DropdownMenuTrigger>
                                                <DropdownMenuContent align="end">
                                                    <DropdownMenuItem as-child>
                                                        <Link :href="route('products.show', product?.id)">
                                                        <Eye class="w-4 h-4 mr-2" />
                                                        View
                                                        </Link>
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem as-child>
                                                        <Link :href="route('products.edit', product?.id)">
                                                        <Edit class="w-4 h-4 mr-2" />
                                                        Edit
                                                        </Link>
                                                    </DropdownMenuItem>
                                                    <DropdownMenuSeparator />
                                                    <DropdownMenuItem @click="deleteProduct(product?.id)"
                                                        class="text-red-600">
                                                        <Trash2 class="w-4 h-4 mr-2" />
                                                        Delete
                                                    </DropdownMenuItem>
                                                </DropdownMenuContent>
                                            </DropdownMenu>
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </div>

                        <!-- Pagination -->
                        <div v-if="products?.meta && products.meta.last_page > 1"
                            class="flex items-center justify-between mt-6">
                            <div class="text-sm text-gray-700">
                                Showing {{ products.meta.from }} to {{ products.meta.to }} of {{ products.meta.total }}
                                results
                            </div>
                            <div class="flex gap-2">
                                <Button variant="outline" size="sm" :disabled="products.meta.current_page === 1"
                                    @click="changePage(products.meta.current_page - 1)">
                                    Previous
                                </Button>
                                <Button variant="outline" size="sm"
                                    :disabled="products.meta.current_page === products.meta.last_page"
                                    @click="changePage(products.meta.current_page + 1)">
                                    Next
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { apiService } from '@/services/api';
import type { PaginatedData, Product, Category } from '@/types/erp';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { MoreHorizontal, Eye, Edit, Trash2, Plus, Loader2 } from 'lucide-vue-next';

const loading = ref<boolean>(true);
const error = ref<string>('');
const products = ref<PaginatedData<Product> | null>(null);
const categories = ref<Category[]>([]);
const search = ref<string>('');
const selectedCategory = ref<string>('');

let searchTimeout: number | null = null;

const debouncedSearch = () => {
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }
    searchTimeout = setTimeout(() => {
        fetchProducts();
    }, 300);
};

const fetchProducts = async (): Promise<void> => {
    try {
        loading.value = true;
        error.value = '';

        const params: any = {};
        if (search.value) params.search = search.value;
        if (selectedCategory.value) params.category_id = selectedCategory.value;

        products.value = await apiService.getProducts(params);
    } catch (err: any) {
        error.value = err.response?.data?.message || 'Failed to load products';
        products.value = null;
    } finally {
        loading.value = false;
    }
};

const loadCategories = async (): Promise<void> => {
    try {
        categories.value = await apiService.getCategories();
    } catch (err: any) {
        console.error('Failed to load categories:', err);
        categories.value = [];
    }
};

const formatCurrency = (amount: number): string => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
};

const changePage = (page: number) => {
    const params = new URLSearchParams();
    if (search.value) params.append('search', search.value);
    if (selectedCategory.value) params.append('category_id', selectedCategory.value);
    params.append('page', page.toString());

    router.get(`/products?${params.toString()}`);
};

const deleteProduct = async (id: number): Promise<void> => {
    if (!id) return;

    if (confirm('Are you sure you want to delete this product?')) {
        try {
            await apiService.deleteProduct(id);
            await fetchProducts(); // Reload the list
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Failed to delete product';
        }
    }
};

// Watch for category changes
watch(selectedCategory, () => {
    fetchProducts();
});

onMounted(() => {
    fetchProducts();
    loadCategories();
});
</script>
