<template>

    <Head title="Reorder Alerts Management" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl leading-tight">
                    Reorder Alerts Management
                </h2>
                <div class="flex gap-2">
                    <Button @click="generateAlerts" :disabled="generating">
                        <RefreshCw class="w-4 h-4 mr-2" :class="{ 'animate-spin': generating }" />
                        Generate Alerts
                    </Button>
                    <Link :href="route('inventory.reorder-alerts.create')">
                    <Button>
                        <Plus class="w-4 h-4 mr-2" />
                        Add Alert
                    </Button>
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="p-4 rounded-lg shadow">
                        <div class="text-sm text-white">Total Alerts</div>
                        <div class="text-2xl font-bold">{{ summary.total_alerts || 0 }}</div>
                    </div>
                    <div class="p-4 rounded-lg shadow">
                        <div class="text-sm text-white">Pending</div>
                        <div class="text-2xl font-bold text-orange-600">{{ summary.pending_alerts || 0 }}</div>
                    </div>
                    <div class="p-4 rounded-lg shadow">
                        <div class="text-sm text-white">Processed</div>
                        <div class="text-2xl font-bold text-green-600">{{ summary.processed_alerts || 0 }}</div>
                    </div>
                    <div class="p-4 rounded-lg shadow">
                        <div class="text-sm text-white">Cancelled</div>
                        <div class="text-2xl font-bold text-red-600">{{ summary.cancelled_alerts || 0 }}</div>
                    </div>
                </div>

                <div class="overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <!-- Search and Filters -->
                        <div class="flex flex-col sm:flex-row gap-4 mb-6">
                            <div class="flex-1">
                                <Input v-model="searchQuery" placeholder="Search alerts..." class="w-full"
                                    @input="debouncedSearch" />
                            </div>
                            <div class="flex gap-2">
                                <select v-model="statusFilter"
                                    class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="">All Status</option>
                                    <option value="pending">Pending</option>
                                    <option value="processed">Processed</option>
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

                        <!-- Reorder Alerts Table -->
                        <div class="rounded-md border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Product</TableHead>
                                        <TableHead>Current Stock</TableHead>
                                        <TableHead>Reorder Point</TableHead>
                                        <TableHead>Suggested Qty</TableHead>
                                        <TableHead>Warehouse</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead>Created</TableHead>
                                        <TableHead class="text-right">Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-if="loading">
                                        <TableCell colspan="8" class="text-center py-8">
                                            <div class="flex items-center justify-center">
                                                <Loader2 class="w-6 h-6 animate-spin mr-2" />
                                                Loading...
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                    <TableRow v-else-if="alerts.length === 0">
                                        <TableCell colspan="8" class="text-center py-8 text-gray-500">
                                            No reorder alerts found
                                        </TableCell>
                                    </TableRow>
                                    <TableRow v-else v-for="alert in alerts" :key="alert?.id">
                                        <TableCell>
                                            <div>
                                                <div class="font-medium">{{ alert?.product?.name || 'N/A' }}</div>
                                                <div class="text-sm text-gray-500">{{ alert?.product?.sku || 'N/A' }}
                                                </div>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div class="text-sm">
                                                <span
                                                    :class="getStockClass(alert?.current_stock, alert?.reorder_point)">
                                                    {{ alert?.current_stock || 0 }}
                                                </span>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div class="text-sm">{{ alert?.reorder_point || 0 }}</div>
                                        </TableCell>
                                        <TableCell>
                                            <div class="text-sm font-medium">{{ alert?.suggested_quantity || 0 }}</div>
                                        </TableCell>
                                        <TableCell>
                                            <div class="text-sm">{{ alert?.warehouse?.name || 'All Warehouses' }}</div>
                                        </TableCell>
                                        <TableCell>
                                            <Badge :variant="getStatusVariant(alert?.status)">
                                                {{ getStatusLabel(alert?.status) }}
                                            </Badge>
                                        </TableCell>
                                        <TableCell>
                                            <div class="text-sm">{{ formatDate(alert?.created_at) }}</div>
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
                                                        <Link :href="route('inventory.reorder-alerts.show', alert?.id)">
                                                        <Eye class="w-4 h-4 mr-2" />
                                                        View
                                                        </Link>
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem as-child>
                                                        <Link :href="route('inventory.reorder-alerts.edit', alert?.id)">
                                                        <Edit class="w-4 h-4 mr-2" />
                                                        Edit
                                                        </Link>
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem v-if="alert?.status === 'pending'"
                                                        @click="processAlert(alert?.id)">
                                                        <Check class="w-4 h-4 mr-2" />
                                                        Process
                                                    </DropdownMenuItem>
                                                    <DropdownMenuSeparator />
                                                    <DropdownMenuItem @click="deleteAlert(alert?.id)"
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
import { Plus, MoreHorizontal, Eye, Edit, Trash2, Loader2, RefreshCw, Check } from 'lucide-vue-next'
import { apiService } from '@/services/api'
import type { ReorderAlert, Warehouse, PaginatedData } from '@/types/erp'

interface Props {
    alerts?: ReorderAlert[] | any
    pagination?: PaginatedData<ReorderAlert>
}

const props = withDefaults(defineProps<Props>(), {
    alerts: () => [],
    pagination: undefined
})

const alerts = ref<ReorderAlert[]>([])
const pagination = ref<PaginatedData<ReorderAlert> | undefined>(props.pagination)
const loading = ref(false)
const generating = ref(false)
const searchQuery = ref('')
const statusFilter = ref('')
const warehouseFilter = ref('')
const warehouses = ref<Warehouse[]>([])
const summary = ref({
    total_alerts: 0,
    pending_alerts: 0,
    processed_alerts: 0,
    cancelled_alerts: 0
})

let searchTimeout: number | null = null

const debouncedSearch = () => {
    if (searchTimeout) {
        clearTimeout(searchTimeout)
    }
    searchTimeout = setTimeout(() => {
        fetchAlerts()
    }, 300)
}

const fetchAlerts = async () => {
    loading.value = true
    try {
        const params: any = {}
        if (searchQuery.value) params.search = searchQuery.value
        if (statusFilter.value) params.status = statusFilter.value
        if (warehouseFilter.value) params.warehouse_id = warehouseFilter.value

        const response = await apiService.getReorderAlerts(params)
        console.log('Reorder Alerts API Response:', response)

        if (response && response.data && Array.isArray(response.data)) {
            alerts.value = response.data.filter(alert => alert && typeof alert === 'object')
        } else {
            alerts.value = []
        }

        if (response && response.meta) {
            pagination.value = {
                data: response.data || [],
                links: response.links || [],
                meta: response.meta
            }
        }
    } catch (error) {
        console.error('Error fetching reorder alerts:', error)
        alerts.value = []
        pagination.value = undefined
    } finally {
        loading.value = false
    }
}

const fetchSummary = async () => {
    try {
        const response = await apiService.getReorderAlertSummary()
        summary.value = response
    } catch (error) {
        console.error('Error fetching summary:', error)
    }
}

const fetchWarehouses = async () => {
    try {
        const response = await apiService.getActiveWarehouses()
        warehouses.value = response || []
    } catch (error) {
        console.error('Error fetching warehouses:', error)
        warehouses.value = []
    }
}

const generateAlerts = async () => {
    generating.value = true
    try {
        const response = await apiService.generateReorderAlerts()
        alert(`Generated ${response.generated_count} new alerts`)
        await fetchAlerts()
        await fetchSummary()
    } catch (error) {
        console.error('Error generating alerts:', error)
        alert('Error generating alerts')
    } finally {
        generating.value = false
    }
}

const processAlert = async (id: number) => {
    if (!id) return

    if (confirm('Are you sure you want to process this alert?')) {
        try {
            await apiService.processReorderAlert(id)
            await fetchAlerts()
            await fetchSummary()
        } catch (error) {
            console.error('Error processing alert:', error)
        }
    }
}

const changePage = (page: number) => {
    const params = new URLSearchParams()
    if (searchQuery.value) params.append('search', searchQuery.value)
    if (statusFilter.value) params.append('status', statusFilter.value)
    if (warehouseFilter.value) params.append('warehouse_id', warehouseFilter.value)
    params.append('page', page.toString())

    router.get(`/inventory/reorder-alerts?${params.toString()}`)
}

const deleteAlert = async (id: number) => {
    if (!id) return

    if (confirm('Are you sure you want to delete this alert?')) {
        try {
            await apiService.deleteReorderAlert(id)
            await fetchAlerts()
            await fetchSummary()
        } catch (error) {
            console.error('Error deleting alert:', error)
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

const getStockClass = (currentStock: number, reorderPoint: number): string => {
    if (currentStock <= reorderPoint) return 'text-red-600 font-medium'
    if (currentStock <= reorderPoint * 1.5) return 'text-orange-600 font-medium'
    return ''
}

const getStatusLabel = (status: string): string => {
    const labels = {
        'pending': 'Pending',
        'processed': 'Processed',
        'cancelled': 'Cancelled'
    }
    return labels[status as keyof typeof labels] || status || 'N/A'
}

const getStatusVariant = (status: string): 'default' | 'secondary' | 'destructive' => {
    const variants: Record<string, 'default' | 'secondary' | 'destructive'> = {
        'pending': 'destructive',
        'processed': 'default',
        'cancelled': 'secondary'
    }
    return variants[status] || 'secondary'
}

watch([statusFilter, warehouseFilter], () => {
    fetchAlerts()
})

onMounted(() => {
    if (alerts.value.length === 0) {
        fetchAlerts()
    }
    fetchSummary()
    fetchWarehouses()
})
</script>
