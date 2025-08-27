<template>

    <Head title="Bills - Accounts Payable" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 space-y-6">
            <!-- Header Section -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Bills</h1>
                    <p class="text-muted-foreground mt-1">
                        Manage supplier bills and accounts payable
                    </p>
                </div>
                <div class="flex items-center space-x-2">
                    <Button variant="outline" size="sm">
                        <Download class="h-4 w-4 mr-2" />
                        Export
                    </Button>
                    <Button @click="openCreateModal">
                        <Plus class="w-4 h-4 mr-2" />
                        Create Bill
                    </Button>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center space-x-4">
                            <div class="p-2 bg-blue-100 dark:bg-blue-900/20 rounded-lg">
                                <FileText class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-muted-foreground">Total Bills</p>
                                <p class="text-2xl font-bold">{{ summary.total_bills }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center space-x-4">
                            <div class="p-2 bg-red-100 dark:bg-red-900/20 rounded-lg">
                                <TrendingDown class="h-6 w-6 text-red-600 dark:text-red-400" />
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-muted-foreground">Total Amount</p>
                                <p class="text-2xl font-bold">{{ formatCurrency(summary.total_amount) }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center space-x-4">
                            <div class="p-2 bg-orange-100 dark:bg-orange-900/20 rounded-lg">
                                <AlertTriangle class="h-6 w-6 text-orange-600 dark:text-orange-400" />
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-muted-foreground">Overdue Amount</p>
                                <p class="text-2xl font-bold">{{ formatCurrency(summary.overdue_amount) }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center space-x-4">
                            <div class="p-2 bg-green-100 dark:bg-green-900/20 rounded-lg">
                                <CheckCircle class="h-6 w-6 text-green-600 dark:text-green-400" />
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-muted-foreground">Paid Amount</p>
                                <p class="text-2xl font-bold">{{ formatCurrency(summary.paid_amount) }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Search and Filters -->
            <Card>
                <CardContent class="p-6">
                    <div class="flex flex-col lg:flex-row gap-4">
                        <div class="flex-1">
                            <div class="relative">
                                <Search
                                    class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                                <Input v-model="filters.search"
                                    placeholder="Search bills by number, supplier, or description..." class="pl-10"
                                    @input="debouncedSearch" />
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <Select v-model="filters.status" @update:model-value="fetchBills">
                                <SelectTrigger class="w-[180px]">
                                    <SelectValue placeholder="All Status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All Status</SelectItem>
                                    <SelectItem value="draft">Draft</SelectItem>
                                    <SelectItem value="received">Received</SelectItem>
                                    <SelectItem value="posted">Posted</SelectItem>
                                    <SelectItem value="paid">Paid</SelectItem>
                                    <SelectItem value="overdue">Overdue</SelectItem>
                                    <SelectItem value="cancelled">Cancelled</SelectItem>
                                </SelectContent>
                            </Select>
                            <Input type="date" v-model="filters.date_from" @change="fetchBills" class="w-[180px]" />
                            <Input type="date" v-model="filters.date_to" @change="fetchBills" class="w-[180px]" />
                            <Button variant="outline" @click="clearFilters">
                                <X class="h-4 w-4 mr-2" />
                                Clear
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Bills Table -->
            <Card>
                <CardHeader>
                    <CardTitle>Bills</CardTitle>
                    <CardDescription>
                        View and manage all supplier bills
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Bill Number</TableHead>
                                    <TableHead>Supplier</TableHead>
                                    <TableHead>Bill Date</TableHead>
                                    <TableHead>Due Date</TableHead>
                                    <TableHead class="text-right">Amount</TableHead>
                                    <TableHead class="text-right">Paid</TableHead>
                                    <TableHead class="text-right">Balance</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead class="text-right">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-if="loading">
                                    <TableCell colspan="9" class="text-center py-12">
                                        <div class="flex items-center justify-center space-x-2">
                                            <Loader2 class="w-6 h-6 animate-spin" />
                                            <span class="text-muted-foreground">Loading bills...</span>
                                        </div>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-else-if="bills.length === 0">
                                    <TableCell colspan="9" class="text-center py-12">
                                        <div class="flex flex-col items-center space-y-2">
                                            <FileText class="h-12 w-12 text-muted-foreground" />
                                            <div class="text-center">
                                                <h3 class="text-lg font-medium">No bills found</h3>
                                                <p class="text-muted-foreground">
                                                    {{ filters.search || filters.status !== 'all' || filters.date_from
                                                        || filters.date_to
                                                        ? 'Try adjusting your search or filters'
                                                        : 'Get started by creating your first bill' }}
                                                </p>
                                            </div>
                                        </div>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-else v-for="bill in bills" :key="bill.id">
                                    <TableCell>
                                        <div class="font-mono text-sm font-medium">{{ bill.bill_number }}</div>
                                    </TableCell>
                                    <TableCell>
                                        <div class="space-y-1">
                                            <div class="font-medium">{{ bill.supplier?.name || 'N/A' }}</div>
                                            <div class="text-sm text-muted-foreground">{{ bill.supplier?.email || 'N/A'
                                            }}</div>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <div class="text-sm">{{ formatDate(bill.bill_date) }}</div>
                                    </TableCell>
                                    <TableCell>
                                        <div class="text-sm"
                                            :class="isOverdue(bill.due_date) ? 'text-red-600 dark:text-red-400' : ''">
                                            {{ formatDate(bill.due_date) }}
                                        </div>
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <div class="font-medium">{{ formatCurrency(bill.total_amount) }}</div>
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <div class="font-medium text-green-600 dark:text-green-400">
                                            {{ formatCurrency(bill.paid_amount) }}
                                        </div>
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <div class="font-medium"
                                            :class="bill.balance_amount > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400'">
                                            {{ formatCurrency(bill.balance_amount) }}
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <Badge :variant="getStatusVariant(bill.status)" class="capitalize">
                                            {{ bill.status }}
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
                                                <DropdownMenuItem @click="openViewModal(bill)">
                                                    <Eye class="w-4 h-4 mr-2" />
                                                    View Details
                                                </DropdownMenuItem>
                                                <DropdownMenuItem v-if="bill.status === 'draft'"
                                                    @click="openEditModal(bill)">
                                                    <Edit class="w-4 h-4 mr-2" />
                                                    Edit Bill
                                                </DropdownMenuItem>
                                                <DropdownMenuItem v-if="bill.status === 'draft'"
                                                    @click="postBill(bill.id)">
                                                    <CheckCircle class="w-4 h-4 mr-2" />
                                                    Post Bill
                                                </DropdownMenuItem>
                                                <DropdownMenuItem v-if="bill.status === 'posted'"
                                                    @click="openRecordPaymentModal(bill)">
                                                    <CreditCard class="w-4 h-4 mr-2" />
                                                    Record Payment
                                                </DropdownMenuItem>
                                                <DropdownMenuSeparator />
                                                <DropdownMenuItem @click="deleteBill(bill.id)" class="text-destructive">
                                                    <Trash2 class="w-4 h-4 mr-2" />
                                                    Delete Bill
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
                        <div class="text-sm text-muted-foreground">
                            Showing {{ pagination.meta.from }} to {{ pagination.meta.to }} of {{ pagination.meta.total
                            }} results
                        </div>
                        <div class="flex gap-2">
                            <Button variant="outline" size="sm" :disabled="pagination.meta.current_page === 1"
                                @click="changePage(pagination.meta.current_page - 1)">
                                <ChevronLeft class="h-4 w-4 mr-1" />
                                Previous
                            </Button>
                            <Button variant="outline" size="sm"
                                :disabled="pagination.meta.current_page === pagination.meta.last_page"
                                @click="changePage(pagination.meta.current_page + 1)">
                                Next
                                <ChevronRight class="h-4 w-4 ml-1" />
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Create Bill Modal -->
        <CreateBillForm :open="showCreateModal" :suppliers="suppliers" @close="closeCreateModal"
            @created="handleBillCreated" />

        <!-- Edit Bill Modal -->
        <EditBillForm :open="showEditModal" :bill="selectedBill" :suppliers="suppliers" @close="closeEditModal"
            @updated="handleBillUpdated" />

        <!-- View Bill Details Modal -->
        <ViewBillDetails v-if="selectedBill" :open="showViewModal" :bill="selectedBill" @close="closeViewModal"
            @edit="handleEditFromView" @record-payment="() => { }" />

        <!-- Record Payment Modal -->
        <RecordPaymentModal v-if="selectedBill" :open="showRecordPaymentModal" :bill="selectedBill"
            @close="closeRecordPaymentModal" @payment-recorded="handlePaymentRecorded" />
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Badge } from '@/components/ui/badge'
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import {
    Plus,
    MoreHorizontal,
    Eye,
    Edit,
    Trash2,
    Loader2,
    Search,
    X,
    FileText,
    TrendingDown,
    AlertTriangle,
    CheckCircle,
    Download,
    ChevronLeft,
    ChevronRight,
    CreditCard
} from 'lucide-vue-next'
import { apiService } from '@/services/api'
import type { Bill, PaginatedData } from '@/types/erp'
import type { BreadcrumbItemType } from '@/types'
import CreateBillForm from '@/components/Finance/AccountsPayable/CreateBillForm.vue'
import EditBillForm from '@/components/Finance/AccountsPayable/EditBillForm.vue'
import ViewBillDetails from '@/components/Finance/AccountsPayable/ViewBillDetails.vue'
import RecordPaymentModal from '@/components/Finance/AccountsPayable/RecordPaymentModal.vue'

interface BillSummary {
    total_bills: number
    total_amount: number
    overdue_amount: number
    paid_amount: number
}

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Finance', href: '/finance' },
    { title: 'Accounts Payable', href: '/finance/accounts-payable' },
    { title: 'Bills', href: '/finance/accounts-payable/bills' }
]

const loading = ref(false)
const bills = ref<Bill[]>([])
const pagination = ref<PaginatedData<Bill> | null>(null)
const suppliers = ref<any[]>([])

// Modal states
const showCreateModal = ref(false)
const showEditModal = ref(false)
const showViewModal = ref(false)
const showRecordPaymentModal = ref(false)
const selectedBill = ref<Bill | null>(null)

const summary = ref<BillSummary>({
    total_bills: 0,
    total_amount: 0,
    overdue_amount: 0,
    paid_amount: 0
})

const filters = ref({
    search: '',
    status: 'all',
    date_from: '',
    date_to: ''
})

const debouncedSearch = () => {
    // Implement debounced search if needed
}

const clearFilters = () => {
    filters.value.search = ''
    filters.value.status = 'all'
    filters.value.date_from = ''
    filters.value.date_to = ''
}

// Modal methods
const openCreateModal = () => {
    showCreateModal.value = true
}

const openEditModal = (bill: Bill) => {
    selectedBill.value = bill
    showEditModal.value = true
}

const openViewModal = (bill: Bill) => {
    selectedBill.value = bill
    showViewModal.value = true
}

const closeCreateModal = () => {
    showCreateModal.value = false
}

const closeEditModal = () => {
    showEditModal.value = false
    selectedBill.value = null
}

const closeViewModal = () => {
    showViewModal.value = false
    selectedBill.value = null
}

const closeRecordPaymentModal = () => {
    showRecordPaymentModal.value = false
    selectedBill.value = null
}

const handleBillCreated = (bill: Bill) => {
    bills.value.unshift(bill)
    summary.value.total_bills++
    summary.value.total_amount += bill.total_amount
}

const handleBillUpdated = (updatedBill: Bill) => {
    const index = bills.value.findIndex(b => b.id === updatedBill.id)
    if (index !== -1) {
        bills.value[index] = updatedBill
        // Recalculate summary
        fetchBills()
    }
}

const handleEditFromView = () => {
    if (selectedBill.value) {
        closeViewModal()
        openEditModal(selectedBill.value)
    }
}

const handlePaymentRecorded = (payment: any) => {
    // Refresh bills to get updated status and amounts
    fetchBills()
}

const fetchBills = async () => {
    loading.value = true
    try {
        const response = await apiService.getBills(filters.value)
        bills.value = response.data || []
        pagination.value = response.pagination || null
        summary.value = response.summary || {
            total_bills: 0,
            total_amount: 0,
            overdue_amount: 0,
            paid_amount: 0
        }
    } catch (error) {
        console.error('Error fetching bills:', error)
        bills.value = []
    } finally {
        loading.value = false
    }
}

const fetchSuppliers = async () => {
    try {
        const response = await apiService.getSuppliers()
        suppliers.value = response.data || []
    } catch (error) {
        console.error('Error fetching suppliers:', error)
        suppliers.value = []
    }
}

const deleteBill = async (id: number) => {
    if (confirm('Are you sure you want to delete this bill?')) {
        try {
            await apiService.deleteBill(id)
            router.reload()
        } catch (error) {
            console.error('Error deleting bill:', error)
        }
    }
}

const postBill = async (id: number) => {
    if (confirm('Are you sure you want to post this bill? This action cannot be undone.')) {
        try {
            await apiService.postBill(id)
            // Refresh bills to get updated status
            fetchBills()
        } catch (error) {
            console.error('Error posting bill:', error)
        }
    }
}

const openRecordPaymentModal = (bill: Bill) => {
    selectedBill.value = bill
    showRecordPaymentModal.value = true
}

const changePage = (page: number) => {
    router.get(route('finance.accounts-payable.bills.index'), { page }, { preserveState: true })
}

const getStatusVariant = (status: string): "default" | "secondary" | "outline" | "destructive" => {
    const variants: Record<string, "default" | "secondary" | "outline" | "destructive"> = {
        'draft': 'secondary',
        'received': 'default',
        'posted': 'default',
        'paid': 'default',
        'overdue': 'destructive',
        'cancelled': 'outline'
    }
    return variants[status] || 'secondary'
}

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(amount)
}

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    })
}

const isOverdue = (dueDate: string) => {
    return new Date(dueDate) < new Date()
}

onMounted(() => {
    fetchBills()
    fetchSuppliers()
})
</script>
