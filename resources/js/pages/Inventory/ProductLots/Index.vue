<template>

    <Head title="Product Lots Management" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl leading-tight">
                    Product Lots Management
                </h2>
                <Link :href="route('inventory.product-lots.create')">
                <Button>
                    <Plus class="w-4 h-4 mr-2" />
                    Add Product Lot
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
                                <Input v-model="searchQuery" placeholder="Search lots..." class="w-full"
                                    @input="debouncedSearch" />
                            </div>
                            <div class="flex gap-2">
                                <select v-model="statusFilter"
                                    class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="">All Status</option>
                                    <option value="active">Active</option>
                                    <option value="expired">Expired</option>
                                    <option value="depleted">Depleted</option>
                                </select>
                                <select v-model="productFilter"
                                    class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="">All Products</option>
                                    <option v-for="product in products" :key="product?.id" :value="product?.id">
                                        {{ product?.name || 'N/A' }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- Product Lots Table -->
                        <div class="rounded-md border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Lot Number</TableHead>
                                        <TableHead>Product</TableHead>
                                        <TableHead>Batch Number</TableHead>
                                        <TableHead>Quantity</TableHead>
                                        <TableHead>Expiry Date</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead class="text-right">Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-if="loading">
                                        <TableCell colspan="7" class="text-center py-8">
                                            <div class="flex items-center justify-center">
                                                <Loader2 class="w-6 h-6 animate-spin mr-2" />
                                                Loading...
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                    <TableRow v-else-if="lots.length === 0">
                                        <TableCell colspan="7" class="text-center py-8 text-gray-500">
                                            No product lots found
                                        </TableCell>
                                    </TableRow>
                                    <TableRow v-else v-for="lot in lots" :key="lot?.id">
                                        <TableCell>
                                            <div class="font-medium">{{ lot?.lot_number || 'N/A' }}</div>
                                        </TableCell>
                                        <TableCell>
                                            <div>
                                                <div class="font-medium">{{ lot?.product?.name || 'N/A' }}</div>
                                                <div class="text-sm text-gray-500">{{ lot?.product?.sku || 'N/A' }}
                                                </div>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div class="text-sm">
                                                {{ lot?.batch_number || 'N/A' }}
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div class="text-sm">
                                                <div class="font-medium">{{ lot?.current_quantity || 0 }}</div>
                                                <div class="text-gray-500">of {{ lot?.initial_quantity || 0 }}</div>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div class="text-sm">
                                                <span :class="getExpiryDateClass(lot?.expiry_date)">
                                                    {{ formatDate(lot?.expiry_date) }}
                                                </span>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <Badge :variant="getStatusVariant(lot?.status)">
                                                {{ getStatusLabel(lot?.status) }}
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
                                                        <Link :href="route('inventory.product-lots.show', lot?.id)">
                                                        <Eye class="w-4 h-4 mr-2" />
                                                        View
                                                        </Link>
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem as-child>
                                                        <Link :href="route('inventory.product-lots.edit', lot?.id)">
                                                        <Edit class="w-4 h-4 mr-2" />
                                                        Edit
                                                        </Link>
                                                    </DropdownMenuItem>
                                                    <DropdownMenuSeparator />
                                                    <DropdownMenuItem @click="deleteLot(lot?.id)" class="text-red-600">
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
                        <div v-if="pagination && pagination.meta && pagination.meta.last_page > 1"
                            class="flex items-center justify-between mt-6">
                            <div class="text-sm text-gray-700">
                                Showing {{ pagination.meta.from }} to {{ pagination.meta.to }} of {{
                                    pagination.meta.total }}
                                results
                            </div>
                            <div class="flex gap-2">
                                <Button variant="outline" size="sm" :disabled="pagination.meta.current_page === 1"
                                    @click="changePage(pagination.meta.current_page - 1)">
                                    Previous
                                </Button>
                                <Button variant="outline" size="sm"
                                    :disabled="pagination.meta.current_page === pagination.meta.last_page"
                                    @click="changePage(pagination.meta.current_page + 1)">
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
import { ref, onMounted, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Badge } from '@/components/ui/badge'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import { Plus, MoreHorizontal, Eye, Edit, Trash2, Loader2 } from 'lucide-vue-next'
import { apiService } from '@/services/api'
import type { ProductLot, Product, PaginatedData } from '@/types/erp'

interface Props {
    lots?: ProductLot[] | any
    pagination?: PaginatedData<ProductLot>
}

const props = withDefaults(defineProps<Props>(), {
    lots: () => [],
    pagination: undefined
})

const lots = ref<ProductLot[]>([])
const pagination = ref<PaginatedData<ProductLot> | undefined>(props.pagination)
const loading = ref(false)
const searchQuery = ref('')
const statusFilter = ref('')
const productFilter = ref('')
const products = ref<Product[]>([])

let searchTimeout: number | null = null

const debouncedSearch = () => {
    if (searchTimeout) {
        clearTimeout(searchTimeout)
    }
    searchTimeout = setTimeout(() => {
        fetchLots()
    }, 300)
}

const fetchLots = async () => {
    loading.value = true
    try {
        const params: any = {}
        if (searchQuery.value) params.search = searchQuery.value
        if (statusFilter.value) params.status = statusFilter.value
        if (productFilter.value) params.product_id = productFilter.value

        const response = await apiService.getProductLots(params)
        console.log('Product Lots API Response:', response)

        if (response && response.data && Array.isArray(response.data)) {
            lots.value = response.data.filter(lot => lot && typeof lot === 'object')
        } else {
            lots.value = []
        }

        if (response && response.meta) {
            pagination.value = {
                data: response.data || [],
                links: response.links || [],
                meta: response.meta
            }
        }
    } catch (error) {
        console.error('Error fetching product lots:', error)
        lots.value = []
        pagination.value = undefined
    } finally {
        loading.value = false
    }
}

const fetchProducts = async () => {
    try {
        const response = await apiService.getProducts({ page: 1 })
        products.value = response.data || []
    } catch (error) {
        console.error('Error fetching products:', error)
        products.value = []
    }
}

const changePage = (page: number) => {
    const params = new URLSearchParams()
    if (searchQuery.value) params.append('search', searchQuery.value)
    if (statusFilter.value) params.append('status', statusFilter.value)
    if (productFilter.value) params.append('product_id', productFilter.value)
    params.append('page', page.toString())

    router.get(`/inventory/product-lots?${params.toString()}`)
}

const deleteLot = async (id: number) => {
    if (!id) return

    if (confirm('Are you sure you want to delete this product lot?')) {
        try {
            await apiService.deleteProductLot(id)
            await fetchLots()
        } catch (error) {
            console.error('Error deleting product lot:', error)
        }
    }
}

const formatDate = (dateString: string): string => {
    if (!dateString) return 'N/A'
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    })
}

const getExpiryDateClass = (expiryDate: string): string => {
    if (!expiryDate) return ''

    const today = new Date()
    const expiry = new Date(expiryDate)
    const daysUntilExpiry = Math.ceil((expiry.getTime() - today.getTime()) / (1000 * 60 * 60 * 24))

    if (daysUntilExpiry < 0) return 'text-red-600 font-medium'
    if (daysUntilExpiry <= 30) return 'text-orange-600 font-medium'
    return ''
}

const getStatusLabel = (status: string): string => {
    const labels = {
        'active': 'Active',
        'expired': 'Expired',
        'depleted': 'Depleted'
    }
    return labels[status as keyof typeof labels] || status || 'N/A'
}

const getStatusVariant = (status: string): 'default' | 'secondary' | 'destructive' => {
    const variants: Record<string, 'default' | 'secondary' | 'destructive'> = {
        'active': 'default',
        'expired': 'destructive',
        'depleted': 'secondary'
    }
    return variants[status] || 'secondary'
}

watch([statusFilter, productFilter], () => {
    fetchLots()
})

onMounted(() => {
    if (lots.value.length === 0) {
        fetchLots()
    }
    fetchProducts()
})
</script>
