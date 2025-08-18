<template>

    <Head title="Purchase Orders" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="mb-6">
                <div class="flex justify-between items-center">
                    <h2 class="font-semibold text-xl text-white leading-tight">
                        Purchase Orders
                    </h2>
                    <Link :href="route('purchase-orders.create')">
                    <Button>
                        <Plus class="w-4 h-4 mr-2" />
                        Create Purchase Order
                    </Button>
                    </Link>
                </div>
            </div>

            <div class="max-w-7xl mx-auto">
                <!-- Search and Filters -->
                <div class="flex flex-col sm:flex-row gap-4 mb-6">
                    <div class="flex-1">
                        <Input v-model="searchQuery" placeholder="Search purchase orders..." class="w-full"
                            @input="debouncedSearch" />
                    </div>
                    <div class="flex gap-2">
                        <Select v-model="statusFilter" @update:model-value="fetchPurchaseOrders">
                            <SelectTrigger class="w-40">
                                <SelectValue placeholder="All Status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="">All Status</SelectItem>
                                <SelectItem value="draft">Draft</SelectItem>
                                <SelectItem value="confirmed">Confirmed</SelectItem>
                                <SelectItem value="received">Received</SelectItem>
                                <SelectItem value="cancelled">Cancelled</SelectItem>
                            </SelectContent>
                        </Select>
                        <Select v-model="dateFilter" @update:model-value="fetchPurchaseOrders">
                            <SelectTrigger class="w-40">
                                <SelectValue placeholder="All Dates" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="">All Dates</SelectItem>
                                <SelectItem value="today">Today</SelectItem>
                                <SelectItem value="week">This Week</SelectItem>
                                <SelectItem value="month">This Month</SelectItem>
                                <SelectItem value="year">This Year</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                <!-- Purchase Orders Table -->
                <Card class="overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="rounded-md border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Order Number</TableHead>
                                        <TableHead>Supplier</TableHead>
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
                                    <TableRow v-else-if="purchaseOrders.length === 0">
                                        <TableCell colspan="7" class="text-center py-8 text-gray-500">
                                            No purchase orders found
                                        </TableCell>
                                    </TableRow>
                                    <TableRow v-else v-for="(order, index) in purchaseOrders" :key="order?.id || index">
                                        <TableCell>
                                            <div>
                                                <div class="font-medium">{{ order?.po_number || 'N/A' }}</div>
                                                <div class="text-sm text-gray-500">#{{ order?.id || 'N/A' }}</div>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div>
                                                <div class="font-medium">{{ order?.supplier?.name || 'N/A' }}</div>
                                                <div class="text-sm text-gray-500">{{ order?.supplier?.code || 'N/A' }}
                                                </div>
                                            </div>
                                        </TableCell>
                                        <TableCell>{{ order?.order_date ? formatDate(order.order_date) : 'N/A' }}
                                        </TableCell>
                                        <TableCell>
                                            <span class="font-medium">{{ formatCurrency(order?.total_amount || 0)
                                                }}</span>
                                        </TableCell>
                                        <TableCell>
                                            <Badge :variant="getStatusVariant(order?.status || 'draft')">
                                                {{ order?.status || 'draft' }}
                                            </Badge>
                                        </TableCell>
                                        <TableCell>
                                            <div class="text-sm">{{ order?.created_by_user?.name || 'N/A' }}</div>
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
                                                        <Link :href="route('purchase-orders.show', order?.id)">
                                                        <Eye class="w-4 h-4 mr-2" />
                                                        View
                                                        </Link>
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem as-child>
                                                        <Link :href="route('purchase-orders.edit', order?.id)">
                                                        <Edit class="w-4 h-4 mr-2" />
                                                        Edit
                                                        </Link>
                                                    </DropdownMenuItem>
                                                    <DropdownMenuSeparator />
                                                    <DropdownMenuItem @click="deletePurchaseOrder(order?.id)"
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
                                    pagination.meta.total }} results
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
                </Card>
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
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Card } from '@/components/ui/card'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import { Plus, MoreHorizontal, Eye, Edit, Trash2, Loader2 } from 'lucide-vue-next'
import { apiService } from '@/services/api'
import type { PurchaseOrder, PaginatedData, PaginationLinks, PaginationMeta } from '@/types/erp'
import type { BreadcrumbItemType } from '@/types'

interface Props {
    purchaseOrders?: PurchaseOrder[]
    pagination?: PaginatedData<PurchaseOrder>
}

const props = withDefaults(defineProps<Props>(), {
    purchaseOrders: () => [],
    pagination: undefined
})

const purchaseOrders = ref<PurchaseOrder[]>(props.purchaseOrders || [])
const pagination = ref<PaginatedData<PurchaseOrder> | undefined>(props.pagination)
const loading = ref(false)
const searchQuery = ref('')
const statusFilter = ref('')
const dateFilter = ref('')

let searchTimeout: ReturnType<typeof setTimeout> | null = null

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Purchase Orders', href: '/purchase-orders' }
]

const debouncedSearch = () => {
    if (searchTimeout) {
        clearTimeout(searchTimeout)
    }
    searchTimeout = setTimeout(() => {
        fetchPurchaseOrders()
    }, 300)
}

const fetchPurchaseOrders = async () => {
    loading.value = true
    try {
        const params = {
            search: searchQuery.value,
            status: statusFilter.value,
            date_filter: dateFilter.value,
            page: pagination.value?.meta?.current_page || 1
        }

        const response = await apiService.getPurchaseOrders(params)

        // Ensure we have valid data
        if (response && response.data) {
            purchaseOrders.value = Array.isArray(response.data) ? response.data : []
            pagination.value = {
                data: response.data as PurchaseOrder[],
                links: response.links as PaginationLinks[],
                meta: response.meta as PaginationMeta
            }
        } else {
            purchaseOrders.value = []
            pagination.value = undefined
        }
    } catch (error) {
        console.error('Error fetching purchase orders:', error)
        purchaseOrders.value = []
        pagination.value = undefined
    } finally {
        loading.value = false
    }
}

const changePage = (page: number) => {
    if (pagination.value?.meta) {
        pagination.value.meta.current_page = page
        fetchPurchaseOrders()
    }
}

const deletePurchaseOrder = async (id: number | undefined) => {
    if (!id) {
        console.error('Cannot delete purchase order: ID is undefined')
        return
    }

    if (confirm('Are you sure you want to delete this purchase order?')) {
        try {
            await apiService.deletePurchaseOrder(id)
            await fetchPurchaseOrders()
        } catch (error) {
            console.error('Error deleting purchase order:', error)
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
    if (!amount || isNaN(amount)) return '$0.00'
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount)
}

const getStatusVariant = (status: string): 'default' | 'secondary' | 'destructive' => {
    const variants: Record<string, 'default' | 'secondary' | 'destructive'> = {
        draft: 'secondary',
        confirmed: 'default',
        received: 'default',
        cancelled: 'destructive'
    }
    return variants[status] || 'secondary'
}

watch([statusFilter, dateFilter], () => {
    fetchPurchaseOrders()
})

onMounted(() => {
    if (purchaseOrders.value.length === 0) {
        fetchPurchaseOrders()
    }
})
</script>
