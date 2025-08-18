<template>

    <Head title="Goods Receipts Management" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl leading-tight">
                    Goods Receipts Management
                </h2>
                <Link :href="route('purchasing.goods-receipts.create')">
                <Button>
                    <Plus class="w-4 h-4 mr-2" />
                    Create Receipt
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
                                <Input v-model="searchQuery" placeholder="Search receipts..." class="w-full"
                                    @input="debouncedSearch" />
                            </div>
                            <div class="flex gap-2">
                                <select v-model="statusFilter"
                                    class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="">All Status</option>
                                    <option value="draft">Draft</option>
                                    <option value="received">Received</option>
                                    <option value="partially_received">Partially Received</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                                <select v-model="warehouseFilter"
                                    class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="">All Warehouses</option>
                                    <option v-for="warehouse in warehouses" :key="warehouse?.id" :value="warehouse?.id">
                                        {{ warehouse?.name || 'N/A' }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- Goods Receipts Table -->
                        <div class="rounded-md border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Receipt #</TableHead>
                                        <TableHead>PO Number</TableHead>
                                        <TableHead>Supplier</TableHead>
                                        <TableHead>Warehouse</TableHead>
                                        <TableHead>Received By</TableHead>
                                        <TableHead>Receipt Date</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead>Total Amount</TableHead>
                                        <TableHead class="text-right">Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-if="loading">
                                        <TableCell colspan="9" class="text-center py-8">
                                            <div class="flex items-center justify-center">
                                                <Loader2 class="w-6 h-6 animate-spin mr-2" />
                                                Loading...
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                    <TableRow v-else-if="receipts.length === 0">
                                        <TableCell colspan="9" class="text-center py-8 text-gray-500">
                                            No goods receipts found
                                        </TableCell>
                                    </TableRow>
                                    <TableRow v-else v-for="receipt in receipts" :key="receipt?.id">
                                        <TableCell>
                                            <div class="font-medium">{{ receipt?.receipt_number }}</div>
                                        </TableCell>
                                        <TableCell>
                                            <div class="text-sm">
                                                {{ receipt?.purchase_order?.po_number || 'N/A' }}
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div class="text-sm">
                                                {{ receipt?.supplier?.name || 'N/A' }}
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div class="text-sm">
                                                {{ receipt?.warehouse?.name || 'N/A' }}
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div class="text-sm">
                                                {{ receipt?.receivedBy?.name || 'N/A' }}
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div class="text-sm">{{ formatDate(receipt?.receipt_date) }}</div>
                                        </TableCell>
                                        <TableCell>
                                            <Badge :variant="getStatusVariant(receipt?.status)">
                                                {{ getStatusLabel(receipt?.status) }}
                                            </Badge>
                                        </TableCell>
                                        <TableCell>
                                            <div class="text-sm font-medium">
                                                {{ formatCurrency(receipt?.total_amount) }}
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
                                                        <Link
                                                            :href="route('purchasing.goods-receipts.show', receipt?.id)">
                                                        <Eye class="w-4 h-4 mr-2" />
                                                        View
                                                        </Link>
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem as-child v-if="receipt?.status === 'draft'">
                                                        <Link
                                                            :href="route('purchasing.goods-receipts.edit', receipt?.id)">
                                                        <Edit class="w-4 h-4 mr-2" />
                                                        Edit
                                                        </Link>
                                                    </DropdownMenuItem>
                                                    <DropdownMenuSeparator />
                                                    <DropdownMenuItem @click="deleteReceipt(receipt?.id)"
                                                        class="text-red-600" v-if="receipt?.status === 'draft'">
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
import type { GoodsReceipt, Warehouse, PaginatedData } from '@/types/erp'

interface Props {
    receipts?: GoodsReceipt[] | any
    pagination?: PaginatedData<GoodsReceipt>
}

const props = withDefaults(defineProps<Props>(), {
    receipts: () => [],
    pagination: undefined
})

const receipts = ref<GoodsReceipt[]>([])
const pagination = ref<PaginatedData<GoodsReceipt> | undefined>(props.pagination)
const loading = ref(false)
const searchQuery = ref('')
const statusFilter = ref('')
const warehouseFilter = ref('')
const warehouses = ref<Warehouse[]>([])

let searchTimeout: number | null = null

const debouncedSearch = () => {
    if (searchTimeout) {
        clearTimeout(searchTimeout)
    }
    searchTimeout = setTimeout(() => {
        fetchReceipts()
    }, 300)
}

const fetchReceipts = async () => {
    loading.value = true
    try {
        const params: any = {}
        if (searchQuery.value) params.search = searchQuery.value
        if (statusFilter.value) params.status = statusFilter.value
        if (warehouseFilter.value) params.warehouse_id = warehouseFilter.value

        const response = await apiService.getGoodsReceipts(params)
        console.log('Goods Receipts API Response:', response)

        if (response && response.data && Array.isArray(response.data)) {
            receipts.value = response.data.filter(receipt => receipt && typeof receipt === 'object')
        } else {
            receipts.value = []
        }

        if (response && response.meta) {
            pagination.value = {
                data: response.data || [],
                links: response.links || [],
                meta: response.meta
            }
        }
    } catch (error) {
        console.error('Error fetching goods receipts:', error)
        receipts.value = []
        pagination.value = undefined
    } finally {
        loading.value = false
    }
}

const fetchWarehouses = async () => {
    try {
        const response = await apiService.getWarehouses({ page: 1 })
        warehouses.value = response.data || []
    } catch (error) {
        console.error('Error fetching warehouses:', error)
        warehouses.value = []
    }
}

const changePage = (page: number) => {
    const params = new URLSearchParams()
    if (searchQuery.value) params.append('search', searchQuery.value)
    if (statusFilter.value) params.append('status', statusFilter.value)
    if (warehouseFilter.value) params.append('warehouse_id', warehouseFilter.value)
    params.append('page', page.toString())

    router.get(`/purchasing/goods-receipts?${params.toString()}`)
}

const deleteReceipt = async (id: number) => {
    if (!id) return

    if (confirm('Are you sure you want to delete this goods receipt?')) {
        try {
            await apiService.deleteGoodsReceipt(id)
            await fetchReceipts()
        } catch (error) {
            console.error('Error deleting goods receipt:', error)
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

const formatCurrency = (amount: number): string => {
    if (!amount) return '0.00'
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount)
}

const getStatusLabel = (status: string): string => {
    const labels = {
        draft: 'Draft',
        received: 'Received',
        partially_received: 'Partially Received',
        cancelled: 'Cancelled'
    }
    return labels[status as keyof typeof labels] || status
}

const getStatusVariant = (status: string): 'default' | 'secondary' | 'destructive' => {
    const variants = {
        draft: 'secondary',
        received: 'default',
        partially_received: 'default',
        cancelled: 'destructive'
    }
    return variants[status as keyof typeof variants] || 'default'
}

watch([statusFilter, warehouseFilter], () => {
    fetchReceipts()
})

onMounted(() => {
    if (receipts.value.length === 0) {
        fetchReceipts()
    }
    fetchWarehouses()
})
</script>
