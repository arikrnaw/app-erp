<template>

    <Head title="Purchase Requests Management" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl leading-tight">
                    Purchase Requests Management
                </h2>
                <Link :href="route('purchasing.purchase-requests.create')">
                <Button>
                    <Plus class="w-4 h-4 mr-2" />
                    Create Request
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
                                <Input v-model="searchQuery" placeholder="Search requests..." class="w-full"
                                    @input="debouncedSearch" />
                            </div>
                            <div class="flex gap-2">
                                <select v-model="statusFilter"
                                    class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="">All Status</option>
                                    <option value="draft">Draft</option>
                                    <option value="submitted">Submitted</option>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                                <select v-model="priorityFilter"
                                    class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="">All Priorities</option>
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>
                        </div>

                        <!-- Purchase Requests Table -->
                        <div class="rounded-md border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Request #</TableHead>
                                        <TableHead>Requested By</TableHead>
                                        <TableHead>Department</TableHead>
                                        <TableHead>Request Date</TableHead>
                                        <TableHead>Required Date</TableHead>
                                        <TableHead>Priority</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead>Total Cost</TableHead>
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
                                    <TableRow v-else-if="requests.length === 0">
                                        <TableCell colspan="9" class="text-center py-8 text-gray-500">
                                            No purchase requests found
                                        </TableCell>
                                    </TableRow>
                                    <TableRow v-else v-for="request in requests" :key="request?.id">
                                        <TableCell>
                                            <div class="font-medium">{{ request?.request_number }}</div>
                                        </TableCell>
                                        <TableCell>
                                            <div class="text-sm">
                                                {{ request?.requestedBy?.name || 'N/A' }}
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div class="text-sm">
                                                {{ request?.department?.name || 'N/A' }}
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div class="text-sm">{{ formatDate(request?.request_date) }}</div>
                                        </TableCell>
                                        <TableCell>
                                            <div class="text-sm">{{ formatDate(request?.required_date) }}</div>
                                        </TableCell>
                                        <TableCell>
                                            <Badge :variant="getPriorityVariant(request?.priority)">
                                                {{ getPriorityLabel(request?.priority) }}
                                            </Badge>
                                        </TableCell>
                                        <TableCell>
                                            <Badge :variant="getStatusVariant(request?.status)">
                                                {{ getStatusLabel(request?.status) }}
                                            </Badge>
                                        </TableCell>
                                        <TableCell>
                                            <div class="text-sm font-medium">
                                                {{ formatCurrency(request?.total_estimated_cost) }}
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
                                                            :href="route('purchasing.purchase-requests.show', request?.id)">
                                                        <Eye class="w-4 h-4 mr-2" />
                                                        View
                                                        </Link>
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem as-child v-if="request?.status === 'draft'">
                                                        <Link
                                                            :href="route('purchasing.purchase-requests.edit', request?.id)">
                                                        <Edit class="w-4 h-4 mr-2" />
                                                        Edit
                                                        </Link>
                                                    </DropdownMenuItem>
                                                    <DropdownMenuSeparator />
                                                    <DropdownMenuItem @click="deleteRequest(request?.id)"
                                                        class="text-red-600" v-if="request?.status === 'draft'">
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
                        <div v-if="pagination && pagination.meta && pagination.meta.last_page > 1" class="mt-6">
                            <DataPagination :current-page="pagination.meta.current_page"
                                :total-pages="pagination.meta.last_page" :total-items="pagination.meta.total"
                                :per-page="pagination.meta.per_page" @page-change="changePage" />
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
import { DataPagination } from '@/components/ui/pagination'
import { apiService } from '@/services/api'
import type { PurchaseRequest, PaginatedData } from '@/types/erp'

interface Props {
    requests?: PurchaseRequest[] | any
    pagination?: PaginatedData<PurchaseRequest>
}

const props = withDefaults(defineProps<Props>(), {
    requests: () => [],
    pagination: undefined
})

const requests = ref<PurchaseRequest[]>([])
const pagination = ref<PaginatedData<PurchaseRequest> | undefined>(props.pagination)
const loading = ref(false)
const searchQuery = ref('')
const statusFilter = ref('')
const priorityFilter = ref('')

let searchTimeout: number | null = null

const debouncedSearch = () => {
    if (searchTimeout) {
        clearTimeout(searchTimeout)
    }
    searchTimeout = setTimeout(() => {
        fetchRequests()
    }, 300)
}

const fetchRequests = async () => {
    loading.value = true
    try {
        const params: any = {}
        if (searchQuery.value) params.search = searchQuery.value
        if (statusFilter.value) params.status = statusFilter.value
        if (priorityFilter.value) params.priority = priorityFilter.value

        const response = await apiService.getPurchaseRequests(params)
        console.log('Purchase Requests API Response:', response)

        if (response && response.data && Array.isArray(response.data)) {
            requests.value = response.data.filter(request => request && typeof request === 'object')
        } else {
            requests.value = []
        }

        if (response && response.meta) {
            pagination.value = {
                data: response.data || [],
                links: response.links || [],
                meta: response.meta
            }
        }
    } catch (error) {
        console.error('Error fetching purchase requests:', error)
        requests.value = []
        pagination.value = undefined
    } finally {
        loading.value = false
    }
}

const changePage = (page: number) => {
    const params = new URLSearchParams()
    if (searchQuery.value) params.append('search', searchQuery.value)
    if (statusFilter.value) params.append('status', statusFilter.value)
    if (priorityFilter.value) params.append('priority', priorityFilter.value)
    params.append('page', page.toString())

    router.get(`/purchasing/purchase-requests?${params.toString()}`)
}

const deleteRequest = async (id: number) => {
    if (!id) return

    if (confirm('Are you sure you want to delete this purchase request?')) {
        try {
            await apiService.deletePurchaseRequest(id)
            await fetchRequests()
        } catch (error) {
            console.error('Error deleting purchase request:', error)
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

const getPriorityLabel = (priority: string): string => {
    const labels = {
        low: 'Low',
        medium: 'Medium',
        high: 'High',
        urgent: 'Urgent'
    }
    return labels[priority as keyof typeof labels] || priority
}

const getPriorityVariant = (priority: string): 'default' | 'secondary' | 'destructive' => {
    const variants = {
        low: 'secondary',
        medium: 'default',
        high: 'destructive',
        urgent: 'destructive'
    }
    return variants[priority as keyof typeof variants] || 'default'
}

const getStatusLabel = (status: string): string => {
    const labels = {
        draft: 'Draft',
        submitted: 'Submitted',
        approved: 'Approved',
        rejected: 'Rejected',
        cancelled: 'Cancelled'
    }
    return labels[status as keyof typeof labels] || status
}

const getStatusVariant = (status: string): 'default' | 'secondary' | 'destructive' => {
    const variants = {
        draft: 'secondary',
        submitted: 'default',
        approved: 'default',
        rejected: 'destructive',
        cancelled: 'destructive'
    }
    return variants[status as keyof typeof variants] || 'default'
}

watch([statusFilter, priorityFilter], () => {
    fetchRequests()
})

onMounted(() => {
    if (requests.value.length === 0) {
        fetchRequests()
    }
})
</script>
