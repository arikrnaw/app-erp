<template>

    <Head title="Bills" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="font-semibold text-xl leading-tight">
                    Bills (Accounts Payable)
                </h2>
                <Link :href="route('finance.accounts-payable.bills.create')">
                <Button>
                    <Plus class="w-4 h-4 mr-2" />
                    Add Bill
                </Button>
                </Link>
            </div>

            <div class="overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Search and Filters -->
                    <div class="flex flex-col sm:flex-row gap-4 mb-6">
                        <div class="flex-1">
                            <Input v-model="searchQuery" placeholder="Search bills..." class="w-full"
                                @input="debouncedSearch" />
                        </div>
                        <div class="flex gap-2">
                            <Select v-model="statusFilter">
                                <SelectTrigger class="w-[180px]">
                                    <SelectValue placeholder="Status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All Status</SelectItem>
                                    <SelectItem value="draft">Draft</SelectItem>
                                    <SelectItem value="received">Received</SelectItem>
                                    <SelectItem value="paid">Paid</SelectItem>
                                    <SelectItem value="overdue">Overdue</SelectItem>
                                    <SelectItem value="cancelled">Cancelled</SelectItem>
                                </SelectContent>
                            </Select>
                            <Input type="date" v-model="dateFilter" class="w-[180px]" />
                        </div>
                    </div>

                    <!-- Bills Table -->
                    <div class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Bill Number</TableHead>
                                    <TableHead>Supplier</TableHead>
                                    <TableHead>Bill Date</TableHead>
                                    <TableHead>Due Date</TableHead>
                                    <TableHead>Total Amount</TableHead>
                                    <TableHead>Paid Amount</TableHead>
                                    <TableHead>Balance</TableHead>
                                    <TableHead>Status</TableHead>
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
                                <TableRow v-else-if="bills.length === 0">
                                    <TableCell colspan="9" class="text-center py-8 text-gray-500">
                                        No bills found
                                    </TableCell>
                                </TableRow>
                                <TableRow v-else v-for="bill in bills.filter((b: any) => b && b.id)" :key="bill.id">
                                    <TableCell>
                                        <div class="font-mono text-sm">{{ bill.bill_number }}</div>
                                    </TableCell>
                                    <TableCell>
                                        <div class="text-sm">
                                            <div class="font-medium">{{ bill.supplier?.name || 'N/A' }}</div>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <div class="text-sm">{{ formatDate(bill.bill_date) }}</div>
                                    </TableCell>
                                    <TableCell>
                                        <div class="text-sm" :class="{ 'text-red-600 font-medium': bill.is_overdue }">
                                            {{ formatDate(bill.due_date) }}
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <div class="text-sm font-medium">
                                            {{ formatCurrency(bill.total_amount || 0) }}
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <div class="text-sm font-medium text-green-600">
                                            {{ formatCurrency(bill.paid_amount || 0) }}
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <div class="text-sm font-medium text-red-600">
                                            {{ formatCurrency(bill.balance_amount || 0) }}
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <Badge :variant="getStatusVariant(bill.status)">
                                            {{ getStatusLabel(bill.status) }}
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
                                                    <Link :href="route('finance.accounts-payable.bills.show', bill.id)">
                                                    <Eye class="w-4 h-4 mr-2" />
                                                    View
                                                    </Link>
                                                </DropdownMenuItem>
                                                <DropdownMenuItem as-child v-if="bill.status === 'draft'">
                                                    <Link :href="route('finance.accounts-payable.bills.edit', bill.id)">
                                                    <Edit class="w-4 h-4 mr-2" />
                                                    Edit
                                                    </Link>
                                                </DropdownMenuItem>
                                                <DropdownMenuSeparator />
                                                <DropdownMenuItem @click="deleteBill(bill.id)"
                                                    v-if="bill.status === 'draft'" class="text-red-600">
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
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Plus, MoreHorizontal, Eye, Edit, Trash2, Loader2 } from 'lucide-vue-next'
import { DataPagination } from '@/components/ui/pagination'
import { apiService } from '@/services/api'
import type { Bill, PaginatedData } from '@/types/erp'
import type { BreadcrumbItemType } from '@/types'

interface Props {
    bills?: Bill[] | any
    pagination?: PaginatedData<Bill>
}

const props = withDefaults(defineProps<Props>(), {
    bills: () => [],
    pagination: undefined
})

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Finance', href: '/finance' },
    { title: 'Accounts Payable', href: '/finance/accounts-payable' },
    { title: 'Bills', href: '/finance/accounts-payable/bills' }
]

const bills = ref<Bill[]>([])
const pagination = ref<PaginatedData<Bill> | undefined>(props.pagination)
const loading = ref(false)
const searchQuery = ref('')
const statusFilter = ref('all')
const dateFilter = ref('')

let searchTimeout: number | null = null

const debouncedSearch = () => {
    if (searchTimeout) {
        clearTimeout(searchTimeout)
    }
    searchTimeout = setTimeout(() => {
        fetchBills()
    }, 300)
}

const fetchBills = async () => {
    loading.value = true
    try {
        const params: any = {}
        if (searchQuery.value) params.search = searchQuery.value
        if (statusFilter.value && statusFilter.value !== 'all') params.status = statusFilter.value
        if (dateFilter.value) params.date = dateFilter.value

        const response = await apiService.getBills(params)
        console.log('Bills API Response:', response)

        if (response && response.data && Array.isArray(response.data)) {
            bills.value = response.data.filter(bill => bill && typeof bill === 'object')
        } else {
            bills.value = []
        }

        if (response && response.meta) {
            pagination.value = {
                data: response.data || [],
                links: response.links || [],
                meta: response.meta
            }
        }
    } catch (error) {
        console.error('Error fetching bills:', error)
        bills.value = []
        pagination.value = undefined
    } finally {
        loading.value = false
    }
}

const changePage = (page: number) => {
    const params = new URLSearchParams()
    if (searchQuery.value) params.append('search', searchQuery.value)
    if (statusFilter.value && statusFilter.value !== 'all') params.append('status', statusFilter.value)
    if (dateFilter.value) params.append('date', dateFilter.value)
    params.append('page', page.toString())

    router.get(`/finance/accounts-payable/bills?${params.toString()}`)
}

const deleteBill = async (id: number) => {
    if (!id) return

    if (confirm('Are you sure you want to delete this bill?')) {
        try {
            await apiService.deleteBill(id)
            await fetchBills()
        } catch (error) {
            console.error('Error deleting bill:', error)
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
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount)
}

const getStatusLabel = (status: string): string => {
    const labels: Record<string, string> = {
        'draft': 'Draft',
        'received': 'Received',
        'paid': 'Paid',
        'overdue': 'Overdue',
        'cancelled': 'Cancelled'
    }
    return labels[status] || status || 'N/A'
}

const getStatusVariant = (status: string): 'default' | 'secondary' | 'destructive' => {
    const variants: Record<string, 'default' | 'secondary' | 'destructive'> = {
        'draft': 'secondary',
        'received': 'default',
        'paid': 'default',
        'overdue': 'destructive',
        'cancelled': 'destructive'
    }
    return variants[status] || 'secondary'
}

watch([statusFilter, dateFilter], () => {
    fetchBills()
})

onMounted(() => {
    if (bills.value.length === 0) {
        fetchBills()
    }
})
</script>
