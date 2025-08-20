<template>

    <Head title="Purchase Returns Management" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl leading-tight">
                    Purchase Returns Management
                </h2>
                <Link :href="route('purchasing.purchase-returns.create')">
                <Button>
                    <Plus class="w-4 h-4 mr-2" />
                    Create Return
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
                                <Input v-model="searchQuery" placeholder="Search returns..." class="w-full"
                                    @input="debouncedSearch" />
                            </div>
                            <div class="flex gap-2">
                                <select v-model="statusFilter"
                                    class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="">All Status</option>
                                    <option value="draft">Draft</option>
                                    <option value="submitted">Submitted</option>
                                    <option value="approved">Approved</option>
                                    <option value="returned">Returned</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                                <select v-model="returnTypeFilter"
                                    class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="">All Types</option>
                                    <option value="defective">Defective</option>
                                    <option value="wrong_item">Wrong Item</option>
                                    <option value="overstock">Overstock</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>

                        <!-- Purchase Returns Table -->
                        <div class="rounded-md border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Return #</TableHead>
                                        <TableHead>PO Number</TableHead>
                                        <TableHead>Supplier</TableHead>
                                        <TableHead>Warehouse</TableHead>
                                        <TableHead>Returned By</TableHead>
                                        <TableHead>Return Date</TableHead>
                                        <TableHead>Type</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead>Total Amount</TableHead>
                                        <TableHead class="text-right">Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-if="loading">
                                        <TableCell colspan="10" class="text-center py-8">
                                            <div class="flex items-center justify-center">
                                                <Loader2 class="w-6 h-6 animate-spin mr-2" />
                                                Loading...
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                    <TableRow v-else-if="returns.length === 0">
                                        <TableCell colspan="10" class="text-center py-8 text-gray-500">
                                            No purchase returns found
                                        </TableCell>
                                    </TableRow>
                                    <TableRow v-else v-for="purchaseReturn in returns" :key="purchaseReturn?.id">
                                        <TableCell>
                                            <div class="font-medium">{{ purchaseReturn?.return_number }}</div>
                                        </TableCell>
                                        <TableCell>
                                            <div class="text-sm">
                                                {{ purchaseReturn?.purchase_order?.po_number || 'N/A' }}
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div class="text-sm">
                                                {{ purchaseReturn?.supplier?.name || 'N/A' }}
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div class="text-sm">
                                                {{ purchaseReturn?.warehouse?.name || 'N/A' }}
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div class="text-sm">
                                                {{ purchaseReturn?.returnedBy?.name || 'N/A' }}
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div class="text-sm">{{ formatDate(purchaseReturn?.return_date) }}</div>
                                        </TableCell>
                                        <TableCell>
                                            <Badge :variant="getReturnTypeVariant(purchaseReturn?.return_type)">
                                                {{ getReturnTypeLabel(purchaseReturn?.return_type) }}
                                            </Badge>
                                        </TableCell>
                                        <TableCell>
                                            <Badge :variant="getStatusVariant(purchaseReturn?.status)">
                                                {{ getStatusLabel(purchaseReturn?.status) }}
                                            </Badge>
                                        </TableCell>
                                        <TableCell>
                                            <div class="text-sm font-medium">
                                                {{ formatCurrency(purchaseReturn?.total_amount) }}
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
                                                            :href="route('purchasing.purchase-returns.show', purchaseReturn?.id)">
                                                        <Eye class="w-4 h-4 mr-2" />
                                                        View
                                                        </Link>
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem as-child
                                                        v-if="purchaseReturn?.status === 'draft'">
                                                        <Link
                                                            :href="route('purchasing.purchase-returns.edit', purchaseReturn?.id)">
                                                        <Edit class="w-4 h-4 mr-2" />
                                                        Edit
                                                        </Link>
                                                    </DropdownMenuItem>
                                                    <DropdownMenuSeparator />
                                                    <DropdownMenuItem @click="deleteReturn(purchaseReturn?.id)"
                                                        class="text-red-600" v-if="purchaseReturn?.status === 'draft'">
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
import type { PurchaseReturn, PaginatedData } from '@/types/erp'

interface Props {
    returns?: PurchaseReturn[] | any
    pagination?: PaginatedData<PurchaseReturn>
}

const props = withDefaults(defineProps<Props>(), {
    returns: () => [],
    pagination: undefined
})

const returns = ref<PurchaseReturn[]>([])
const pagination = ref<PaginatedData<PurchaseReturn> | undefined>(props.pagination)
const loading = ref(false)
const searchQuery = ref('')
const statusFilter = ref('')
const returnTypeFilter = ref('')

let searchTimeout: number | null = null

const debouncedSearch = () => {
    if (searchTimeout) {
        clearTimeout(searchTimeout)
    }
    searchTimeout = setTimeout(() => {
        fetchReturns()
    }, 300)
}

const fetchReturns = async () => {
    loading.value = true
    try {
        const params: any = {}
        if (searchQuery.value) params.search = searchQuery.value
        if (statusFilter.value) params.status = statusFilter.value
        if (returnTypeFilter.value) params.return_type = returnTypeFilter.value

        const response = await apiService.getPurchaseReturns(params)
        console.log('Purchase Returns API Response:', response)

        if (response && response.data && Array.isArray(response.data)) {
            returns.value = response.data.filter(returnItem => returnItem && typeof returnItem === 'object')
        } else {
            returns.value = []
        }

        if (response && response.meta) {
            pagination.value = {
                data: response.data || [],
                links: response.links || [],
                meta: response.meta
            }
        }
    } catch (error) {
        console.error('Error fetching purchase returns:', error)
        returns.value = []
        pagination.value = undefined
    } finally {
        loading.value = false
    }
}

const changePage = (page: number) => {
    const params = new URLSearchParams()
    if (searchQuery.value) params.append('search', searchQuery.value)
    if (statusFilter.value) params.append('status', statusFilter.value)
    if (returnTypeFilter.value) params.append('return_type', returnTypeFilter.value)
    params.append('page', page.toString())

    router.get(`/purchasing/purchase-returns?${params.toString()}`)
}

const deleteReturn = async (id: number) => {
    if (!id) return

    if (confirm('Are you sure you want to delete this purchase return?')) {
        try {
            await apiService.deletePurchaseReturn(id)
            await fetchReturns()
        } catch (error) {
            console.error('Error deleting purchase return:', error)
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
    if (!amount) return 'Rp 0'
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(amount)
}

const getReturnTypeLabel = (returnType: string): string => {
    const labels = {
        defective: 'Defective',
        wrong_item: 'Wrong Item',
        overstock: 'Overstock',
        other: 'Other'
    }
    return labels[returnType as keyof typeof labels] || returnType
}

const getReturnTypeVariant = (returnType: string): 'default' | 'secondary' | 'destructive' => {
    const variants = {
        defective: 'destructive',
        wrong_item: 'secondary',
        overstock: 'default',
        other: 'secondary'
    }
    return variants[returnType as keyof typeof variants] || 'default'
}

const getStatusLabel = (status: string): string => {
    const labels = {
        draft: 'Draft',
        submitted: 'Submitted',
        approved: 'Approved',
        returned: 'Returned',
        cancelled: 'Cancelled'
    }
    return labels[status as keyof typeof labels] || status
}

const getStatusVariant = (status: string): 'default' | 'secondary' | 'destructive' => {
    const variants = {
        draft: 'secondary',
        submitted: 'default',
        approved: 'default',
        returned: 'default',
        cancelled: 'destructive'
    }
    return variants[status as keyof typeof variants] || 'default'
}

watch([statusFilter, returnTypeFilter], () => {
    fetchReturns()
})

onMounted(() => {
    if (returns.value.length === 0) {
        fetchReturns()
    }
})
</script>
