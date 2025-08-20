<template>

    <Head title="Sales Orders" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl leading-tight">
                    Sales Orders
                </h2>
                <Link :href="route('sales-orders.create')">
                <Button>
                    <Plus class="w-4 h-4 mr-2" />
                    Create Sales Order
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
                                <Input v-model="searchQuery" placeholder="Search sales orders..." class="w-full"
                                    @input="debouncedSearch" />
                            </div>
                            <div class="flex gap-2">
                                <select v-model="statusFilter"
                                    class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="">All Status</option>
                                    <option value="draft">Draft</option>
                                    <option value="confirmed">Confirmed</option>
                                    <option value="shipped">Shipped</option>
                                    <option value="delivered">Delivered</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                                <select v-model="dateFilter"
                                    class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="">All Dates</option>
                                    <option value="today">Today</option>
                                    <option value="week">This Week</option>
                                    <option value="month">This Month</option>
                                    <option value="year">This Year</option>
                                </select>
                            </div>
                        </div>

                        <!-- Sales Orders Table -->
                        <div class="rounded-md border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Order Number</TableHead>
                                        <TableHead>Customer</TableHead>
                                        <TableHead>Order Date</TableHead>
                                        <TableHead>Total Amount</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead>Created By</TableHead>
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
                                    <TableRow v-else-if="safeSalesOrders.length === 0">
                                        <TableCell colspan="7" class="text-center py-8 text-gray-500">
                                            No sales orders found
                                        </TableCell>
                                    </TableRow>
                                    <TableRow v-else v-for="order in safeSalesOrders" :key="order.id">
                                        <TableCell>
                                            <div>
                                                <div class="font-medium">{{ order.so_number }}</div>
                                                <div class="text-sm text-gray-500">#{{ order.id }}</div>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div>
                                                <div class="font-medium">{{ order.customer?.name }}</div>
                                                <div class="text-sm text-gray-500">{{ order.customer?.customer_code }}
                                                </div>
                                            </div>
                                        </TableCell>
                                        <TableCell>{{ formatDate(order.order_date) }}</TableCell>
                                        <TableCell>
                                            <span class="font-medium">{{ formatCurrency(order.total_amount) }}</span>
                                        </TableCell>
                                        <TableCell>
                                            <span :class="[
                                                'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                                                getStatusColor(order.status)
                                            ]">
                                                {{ order.status }}
                                            </span>
                                        </TableCell>
                                        <TableCell>
                                            <div class="text-sm">{{ order.created_by_user?.name }}</div>
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
                                                        <Link :href="route('sales-orders.show', order.id)">
                                                        <Eye class="w-4 h-4 mr-2" />
                                                        View
                                                        </Link>
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem as-child>
                                                        <Link :href="route('sales-orders.edit', order.id)">
                                                        <Edit class="w-4 h-4 mr-2" />
                                                        Edit
                                                        </Link>
                                                    </DropdownMenuItem>
                                                    <DropdownMenuSeparator />
                                                    <DropdownMenuItem @click="deleteSalesOrder(order.id)"
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
                        <div v-if="pagination && pagination.meta?.last_page > 1"
                            class="flex items-center justify-between mt-6">
                            <div class="text-sm text-gray-700">
                                Showing {{ pagination.meta?.from }} to {{ pagination.meta?.to }} of {{
                                    pagination.meta?.total }}
                                results
                            </div>
                            <div class="flex gap-2">
                                <Button variant="outline" size="sm" :disabled="pagination.meta?.current_page === 1"
                                    @click="changePage(pagination.meta?.current_page - 1)">
                                    Previous
                                </Button>
                                <Button variant="outline" size="sm"
                                    :disabled="pagination.meta?.current_page === pagination.meta?.last_page"
                                    @click="changePage(pagination.meta?.current_page + 1)">
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
import { ref, onMounted, watch, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import { Plus, MoreHorizontal, Eye, Edit, Trash2, Loader2 } from 'lucide-vue-next'
import { apiService } from '@/services/api'
import type { SalesOrder, PaginatedData, PaginationMeta } from '@/types/erp'

interface Props {
    salesOrders?: SalesOrder[]
    pagination?: PaginatedData<SalesOrder>
}

const props = withDefaults(defineProps<Props>(), {
    salesOrders: () => [],
    pagination: undefined
})

// Initialize with empty array to avoid any issues
const salesOrders = ref<SalesOrder[]>([])
const pagination = ref<PaginatedData<SalesOrder> | undefined>(props.pagination)
const loading = ref(false)
const searchQuery = ref('')
const statusFilter = ref('')
const dateFilter = ref('')

// Safe computed property that always returns an array
const safeSalesOrders = computed(() => {
    try {
        // Always ensure we have an array
        const orders = salesOrders.value || []

        // If it's not an array, return empty array
        if (!Array.isArray(orders)) {
            console.warn('salesOrders is not an array, returning empty array')
            return []
        }

        // Filter out null/undefined values
        return orders.filter(order => order != null)
    } catch (error) {
        console.error('Error in safeSalesOrders computed:', error)
        return []
    }
})

let searchTimeout: ReturnType<typeof setTimeout> | null = null

const debouncedSearch = () => {
    if (searchTimeout) {
        clearTimeout(searchTimeout)
    }
    searchTimeout = setTimeout(() => {
        fetchSalesOrders()
    }, 300)
}

const fetchSalesOrders = async () => {
    loading.value = true
    try {
        const params: any = {}
        if (searchQuery.value) params.search = searchQuery.value
        if (statusFilter.value) params.status = statusFilter.value
        if (dateFilter.value) params.date_filter = dateFilter.value

        const response = await apiService.getSalesOrders(params)

        // Safely handle the response data
        let responseData: SalesOrder[] = []
        if (response && response.data) {
            responseData = Array.isArray(response.data) ? response.data : []
        }

        // Filter out null values
        const validOrders = responseData.filter(order => order != null)

        // Set the sales orders
        salesOrders.value = validOrders

        // Update pagination
        pagination.value = {
            data: validOrders,
            links: response.links || [],
            meta: response.meta as PaginationMeta
        }
    } catch (error) {
        console.error('Error fetching sales orders:', error)
        // Set empty array on error
        salesOrders.value = []
    } finally {
        loading.value = false
    }
}

const changePage = (page: number) => {
    const params = new URLSearchParams()
    if (searchQuery.value) params.append('search', searchQuery.value)
    if (statusFilter.value) params.append('status', statusFilter.value)
    if (dateFilter.value) params.append('date_filter', dateFilter.value)
    params.append('page', page.toString())

    router.get(`/sales-orders?${params.toString()}`)
}

const deleteSalesOrder = async (id: number) => {
    if (confirm('Are you sure you want to delete this sales order?')) {
        try {
            await apiService.deleteSalesOrder(id)
            await fetchSalesOrders()
        } catch (error) {
            console.error('Error deleting sales order:', error)
        }
    }
}

const formatDate = (dateString: string): string => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    })
}

const formatCurrency = (amount: number): string => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(amount)
}

const getStatusColor = (status: string): string => {
    const colors = {
        draft: 'bg-gray-100',
        confirmed: 'bg-blue-100 text-blue-800',
        shipped: 'bg-yellow-100 text-yellow-800',
        delivered: 'bg-green-100 text-green-800',
        cancelled: 'bg-red-100 text-red-800'
    }
    return colors[status as keyof typeof colors] || 'bg-gray-100'
}

watch([statusFilter, dateFilter], () => {
    fetchSalesOrders()
})

onMounted(() => {
    // Initialize with props data if available
    if (props.salesOrders && Array.isArray(props.salesOrders)) {
        salesOrders.value = props.salesOrders.filter(order => order != null)
    }

    // Fetch data if we don't have any
    if (safeSalesOrders.value.length === 0) {
        fetchSalesOrders()
    }
})
</script>
