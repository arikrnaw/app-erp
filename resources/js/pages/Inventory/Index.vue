<template>

    <Head title="Inventory Transactions" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl leading-tight">
                    Inventory Transactions
                </h2>
                <Link :href="route('inventory.create')">
                <Button>
                    <Plus class="w-4 h-4 mr-2" />
                    Add Transaction
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
                                <Input v-model="searchQuery" placeholder="Search transactions..." class="w-full"
                                    @input="debouncedSearch" />
                            </div>
                            <div class="flex gap-2">
                                <select v-model="typeFilter"
                                    class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="">All Types</option>
                                    <option value="in">Stock In</option>
                                    <option value="out">Stock Out</option>
                                    <option value="adjustment">Adjustment</option>
                                    <option value="transfer">Transfer</option>
                                    <option value="return">Return</option>
                                    <option value="damage">Damage</option>
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

                        <!-- Transactions Table -->
                        <div class="rounded-md border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Date</TableHead>
                                        <TableHead>Product</TableHead>
                                        <TableHead>Type</TableHead>
                                        <TableHead>Quantity</TableHead>
                                        <TableHead>Reference</TableHead>
                                        <TableHead>Notes</TableHead>
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
                                    <TableRow v-else-if="transactions.length === 0">
                                        <TableCell colspan="7" class="text-center py-8 text-gray-500">
                                            No transactions found
                                        </TableCell>
                                    </TableRow>
                                    <TableRow v-else v-for="transaction in transactions"
                                        :key="transaction?.id || Math.random()">
                                        <TableCell>{{ formatDate(transaction?.transaction_date) }}</TableCell>
                                        <TableCell>
                                            <div>
                                                <div class="font-medium">{{ transaction?.product?.name || 'N/A' }}</div>
                                                <div class="text-sm text-gray-500">{{ transaction?.product?.sku || 'N/A'
                                                }}
                                                </div>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <Badge :variant="getTypeVariant(transaction?.type)">
                                                {{ getTypeLabel(transaction?.type) }}
                                            </Badge>
                                        </TableCell>
                                        <TableCell>
                                            <span
                                                :class="transaction?.type === 'in' ? 'text-green-600' : 'text-red-600'">
                                                {{ transaction?.type === 'in' ? '+' : '-' }}{{ transaction?.quantity ||
                                                    0 }}
                                            </span>
                                        </TableCell>
                                        <TableCell>
                                            <div class="text-sm">
                                                <div>{{ transaction?.reference_type || 'N/A' }}</div>
                                                <div class="text-gray-500">#{{ transaction?.reference_id || 'N/A' }}
                                                </div>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div class="text-sm text-white max-w-xs truncate">
                                                {{ transaction?.notes || 'No notes' }}
                                            </div>
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
                                                        <Link :href="route('inventory.show', transaction?.id)">
                                                        <Eye class="w-4 h-4 mr-2" />
                                                        View
                                                        </Link>
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem as-child>
                                                        <Link :href="route('inventory.edit', transaction?.id)">
                                                        <Edit class="w-4 h-4 mr-2" />
                                                        Edit
                                                        </Link>
                                                    </DropdownMenuItem>
                                                    <DropdownMenuSeparator />
                                                    <DropdownMenuItem @click="deleteTransaction(transaction?.id)"
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
import type { InventoryTransaction, Product, PaginatedData } from '@/types/erp'

interface Props {
    transactions?: InventoryTransaction[] | any
    pagination?: PaginatedData<InventoryTransaction>
}

const props = withDefaults(defineProps<Props>(), {
    transactions: () => [],
    pagination: undefined
})

const transactions = ref<InventoryTransaction[]>([])
const pagination = ref<PaginatedData<InventoryTransaction> | undefined>(props.pagination)
const loading = ref(false)
const searchQuery = ref('')
const typeFilter = ref('')
const productFilter = ref('')
const products = ref<Product[]>([])

let searchTimeout: number | null = null

const debouncedSearch = () => {
    if (searchTimeout) {
        clearTimeout(searchTimeout)
    }
    searchTimeout = setTimeout(() => {
        fetchTransactions()
    }, 300)
}

const fetchTransactions = async () => {
    loading.value = true
    try {
        const params: any = {}
        if (searchQuery.value) params.search = searchQuery.value
        if (typeFilter.value) params.type = typeFilter.value
        if (productFilter.value) params.product_id = productFilter.value

        const response = await apiService.getInventoryTransactions(params)
        console.log('Inventory API Response:', response)

        if (response && response.data && Array.isArray(response.data)) {
            transactions.value = response.data.filter(transaction => transaction && typeof transaction === 'object')
        } else {
            transactions.value = []
        }

        if (response && response.meta) {
            pagination.value = {
                data: response.data || [],
                links: response.links || [],
                meta: response.meta
            }
        }
    } catch (error) {
        console.error('Error fetching inventory transactions:', error)
        transactions.value = []
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
    if (typeFilter.value) params.append('type', typeFilter.value)
    if (productFilter.value) params.append('product_id', productFilter.value)
    params.append('page', page.toString())

    router.get(`/inventory?${params.toString()}`)
}

const deleteTransaction = async (id: number) => {
    if (!id) return

    if (confirm('Are you sure you want to delete this transaction?')) {
        try {
            await apiService.deleteInventoryTransaction(id)
            await fetchTransactions()
        } catch (error) {
            console.error('Error deleting transaction:', error)
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

const getTypeLabel = (type: string): string => {
    const labels = {
        'in': 'Stock In',
        'out': 'Stock Out',
        'adjustment': 'Adjustment',
        'transfer': 'Transfer',
        'return': 'Return',
        'damage': 'Damage'
    }
    return labels[type as keyof typeof labels] || type || 'N/A'
}

const getTypeVariant = (type: string): 'default' | 'secondary' | 'destructive' => {
    const variants: Record<string, 'default' | 'secondary' | 'destructive'> = {
        'in': 'default',
        'out': 'destructive',
        'adjustment': 'secondary',
        'transfer': 'secondary',
        'return': 'default',
        'damage': 'destructive'
    }
    return variants[type] || 'secondary'
}

watch([typeFilter, productFilter], () => {
    fetchTransactions()
})

onMounted(() => {
    if (transactions.value.length === 0) {
        fetchTransactions()
    }
    fetchProducts()
})
</script>
