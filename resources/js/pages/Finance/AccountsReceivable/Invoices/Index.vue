<template>

    <Head title="Invoices - Accounts Receivable" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 space-y-6">
            <!-- Header Section -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Invoices</h1>
                    <p class="text-muted-foreground mt-1">
                        Manage customer invoices and accounts receivable
                    </p>
                </div>
                <div class="flex items-center space-x-2">
                    <Button variant="outline" size="sm">
                        <Download class="h-4 w-4 mr-2" />
                        Export
                    </Button>
                    <Button @click="showCreateInvoiceModal = true">
                        <Plus class="w-4 h-4 mr-2" />
                        Create Invoice
                    </Button>
                </div>
            </div>

            <!-- Summary Cards -->
            <!-- Row 1: 3 Cards -->
            <div class="grid gap-4 md:grid-cols-3">
                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center space-x-4">
                            <div class="p-2 bg-blue-100 dark:bg-blue-900/20 rounded-lg">
                                <FileText class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-muted-foreground">Total Invoices</p>
                                <p class="text-2xl font-bold">{{ summary.total_invoices }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center space-x-4">
                            <div class="p-2 bg-green-100 dark:bg-green-900/20 rounded-lg">
                                <TrendingUp class="h-6 w-6 text-green-600 dark:text-green-400" />
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
            </div>

            <!-- Row 2: 2 Cards -->
            <div class="grid gap-4 md:grid-cols-2">
                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center space-x-4">
                            <div class="p-2 bg-purple-100 dark:bg-purple-900/20 rounded-lg">
                                <CheckCircle class="h-6 w-6 text-purple-600 dark:text-purple-400" />
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-muted-foreground">Paid Amount</p>
                                <p class="text-2xl font-bold">{{ formatCurrency(summary.paid_amount) }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center space-x-4">
                            <div class="p-2 bg-red-100 dark:bg-red-900/20 rounded-lg">
                                <AlertTriangle class="h-6 w-6 text-red-600 dark:text-red-400" />
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-muted-foreground">Outstanding Amount</p>
                                <p class="text-2xl font-bold">{{ formatCurrency(summary.outstanding_amount) }}</p>
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
                                    placeholder="Search invoices by number, customer, or description..." class="pl-10"
                                    @input="debouncedSearch" />
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <Select v-model="filters.status" @update:model-value="fetchInvoices">
                                <SelectTrigger class="w-[180px]">
                                    <SelectValue placeholder="All Status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All Status</SelectItem>
                                    <SelectItem value="draft">Draft</SelectItem>
                                    <SelectItem value="sent">Sent</SelectItem>
                                    <SelectItem value="partial">Partial</SelectItem>
                                    <SelectItem value="paid">Paid</SelectItem>
                                    <SelectItem value="cancelled">Cancelled</SelectItem>
                                </SelectContent>
                            </Select>
                            <Input type="date" v-model="filters.date_from" @change="fetchInvoices" class="w-[180px]" />
                            <Input type="date" v-model="filters.date_to" @change="fetchInvoices" class="w-[180px]" />
                            <Button variant="outline" @click="clearFilters">
                                <X class="h-4 w-4 mr-2" />
                                Clear
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Invoices Table -->
            <Card>
                <CardHeader>
                    <CardTitle>Invoices</CardTitle>
                    <CardDescription>
                        View and manage all customer invoices
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Invoice Number</TableHead>
                                    <TableHead>Customer</TableHead>
                                    <TableHead>Date</TableHead>
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
                                            <span class="text-muted-foreground">Loading invoices...</span>
                                        </div>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-else-if="invoices.length === 0">
                                    <TableCell colspan="9" class="text-center py-12">
                                        <div class="flex flex-col items-center space-y-2">
                                            <FileText class="h-12 w-12 text-muted-foreground" />
                                            <div class="text-center">
                                                <h3 class="text-lg font-medium">No invoices found</h3>
                                                <p class="text-muted-foreground">
                                                    {{ filters.search || filters.status !== 'all' || filters.date_from
                                                        || filters.date_to
                                                        ? 'Try adjusting your search or filters'
                                                        : 'Get started by creating your first invoice' }}
                                                </p>
                                            </div>
                                        </div>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-else v-for="invoice in invoices" :key="invoice.id">
                                    <TableCell>
                                        <div class="font-mono text-sm font-medium">{{ invoice.invoice_number }}</div>
                                    </TableCell>
                                    <TableCell>
                                        <div class="space-y-1">
                                            <div class="font-medium">{{ invoice.customer?.name || 'N/A' }}</div>
                                            <div class="text-sm text-muted-foreground">{{ invoice.customer?.email ||
                                                'N/A' }}</div>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <div class="text-sm">{{ formatDate(invoice.invoice_date) }}</div>
                                    </TableCell>
                                    <TableCell>
                                        <div class="text-sm"
                                            :class="isOverdue(invoice.due_date) ? 'text-red-600 dark:text-red-400' : ''">
                                            {{ formatDate(invoice.due_date) }}
                                        </div>
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <div class="font-medium">{{ formatCurrency(invoice.total_amount) }}</div>
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <div class="font-medium text-green-600 dark:text-green-400">
                                            {{ formatCurrency(invoice.paid_amount) }}
                                        </div>
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <div class="font-medium"
                                            :class="invoice.balance_amount > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400'">
                                            {{ formatCurrency(invoice.balance_amount) }}
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <Badge :variant="getStatusVariant(invoice.status)"
                                            :class="getStatusColor(invoice.status)">
                                            {{ invoice.status }}
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
                                                <DropdownMenuItem @click="openViewInvoiceModal(invoice)">
                                                    <Eye class="w-4 h-4 mr-2" />
                                                    View Details
                                                </DropdownMenuItem>
                                                <DropdownMenuItem @click="postInvoice(invoice.id)"
                                                    v-if="invoice.status === 'draft'">
                                                    <Send class="w-4 h-4 mr-2" />
                                                    Post Invoice
                                                </DropdownMenuItem>
                                                <DropdownMenuItem @click="openEditInvoiceModal(invoice)"
                                                    v-if="invoice.status === 'draft'">
                                                    <Edit class="w-4 h-4 mr-2" />
                                                    Edit Invoice
                                                </DropdownMenuItem>
                                                <DropdownMenuItem @click="openRecordPaymentModal(invoice)"
                                                    v-if="invoice.status === 'sent'">
                                                    <CreditCard class="w-4 h-4 mr-2" />
                                                    Record Payment
                                                </DropdownMenuItem>
                                                <DropdownMenuSeparator />
                                                <DropdownMenuItem @click="deleteInvoice(invoice.id)"
                                                    class="text-destructive">
                                                    <Trash2 class="w-4 h-4 mr-2" />
                                                    Delete Invoice
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

        <!-- Create Invoice Modal -->
        <Dialog v-model:open="showCreateInvoiceModal">
            <DialogContent class="!max-w-6xl max-h-[90vh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle>Create New Invoice</DialogTitle>
                    <DialogDescription>
                        Create a new invoice with customer details and line items
                    </DialogDescription>
                </DialogHeader>

                <!-- Create Invoice Form -->
                <CreateInvoiceForm v-if="showCreateInvoiceModal" @invoice-created="onInvoiceCreated"
                    @cancel="showCreateInvoiceModal = false" />
            </DialogContent>
        </Dialog>

        <!-- Record Payment Modal -->
        <Dialog v-model:open="showRecordPaymentModal">
            <DialogContent class="max-w-4xl max-h-[90vh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle>Record Payment</DialogTitle>
                    <DialogDescription>
                        Record a payment for invoice {{ selectedInvoice?.invoice_number }}
                    </DialogDescription>
                </DialogHeader>
                <RecordPaymentForm v-if="showRecordPaymentModal && selectedInvoice" :invoice="selectedInvoice"
                    @payment-recorded="onPaymentRecorded" @cancel="showRecordPaymentModal = false" />
            </DialogContent>
        </Dialog>

        <!-- Edit Invoice Modal -->
        <Dialog v-model:open="showEditInvoiceModal">
            <DialogContent class="!max-w-6xl max-h-[90vh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle>Edit Invoice</DialogTitle>
                    <DialogDescription>
                        Edit invoice {{ selectedInvoice?.invoice_number }}
                    </DialogDescription>
                </DialogHeader>
                <EditInvoiceForm v-if="showEditInvoiceModal && selectedInvoice" :invoice="selectedInvoice"
                    @invoice-updated="onInvoiceUpdated" @cancel="showEditInvoiceModal = false" />
            </DialogContent>
        </Dialog>

        <!-- View Invoice Details Modal -->
        <Dialog v-model:open="showViewInvoiceModal">
            <DialogContent class="!max-w-6xl max-h-[90vh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle>Invoice Details</DialogTitle>
                    <DialogDescription>
                        View details for invoice {{ selectedInvoice?.invoice_number }}
                    </DialogDescription>
                </DialogHeader>
                <ViewInvoiceDetails v-if="showViewInvoiceModal && selectedInvoice" :invoice="selectedInvoice"
                    @close="showViewInvoiceModal = false" @edit="openEditInvoiceModal(selectedInvoice)"
                    @record-payment="openRecordPaymentModal(selectedInvoice)" />
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Badge } from '@/components/ui/badge'
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog'
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
    TrendingUp,
    AlertTriangle,
    CheckCircle,
    Download,
    ChevronLeft,
    ChevronRight,
    CreditCard,
    Send
} from 'lucide-vue-next'
import { apiService } from '@/services/api'
import type { Invoice, PaginatedData } from '@/types/erp'
import type { BreadcrumbItemType } from '@/types'
import CreateInvoiceForm from '@/components/Finance/AccountsReceivable/CreateInvoiceForm.vue'
import RecordPaymentForm from '@/components/Finance/AccountsReceivable/RecordPaymentForm.vue'
import EditInvoiceForm from '@/components/Finance/AccountsReceivable/EditInvoiceForm.vue'
import ViewInvoiceDetails from '@/components/Finance/AccountsReceivable/ViewInvoiceDetails.vue'

interface InvoiceSummary {
    total_invoices: number
    total_amount: number
    overdue_amount: number
    paid_amount: number
    outstanding_amount: number
}

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Finance', href: '/finance' },
    { title: 'Accounts Receivable', href: '/finance/accounts-receivable' },
    { title: 'Invoices', href: '/finance/accounts-receivable/invoices' }
]

const loading = ref(false)
const invoices = ref<Invoice[]>([])
const pagination = ref<PaginatedData<Invoice> | null>(null)
const searchQuery = ref('')
const statusFilter = ref('all')

// Modal states
const showCreateInvoiceModal = ref(false)
const showRecordPaymentModal = ref(false)
const showEditInvoiceModal = ref(false)
const showViewInvoiceModal = ref(false)
const selectedInvoice = ref<Invoice | null>(null)

const summary = ref<InvoiceSummary>({
    total_invoices: 0,
    total_amount: 0,
    overdue_amount: 0,
    paid_amount: 0,
    outstanding_amount: 0
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

const fetchInvoices = async () => {
    loading.value = true
    try {
        const response = await apiService.getInvoices(filters.value)
        invoices.value = response.data || []
        pagination.value = response.pagination || null
        summary.value = response.summary || {
            total_invoices: 0,
            total_amount: 0,
            overdue_amount: 0,
            paid_amount: 0,
            outstanding_amount: 0
        }
    } catch (error) {
        console.error('Error fetching invoices:', error)
        invoices.value = []
    } finally {
        loading.value = false
    }
}

const deleteInvoice = async (id: number) => {
    if (confirm('Are you sure you want to delete this invoice?')) {
        try {
            await apiService.deleteInvoice(id)
            router.reload()
        } catch (error) {
            console.error('Error deleting invoice:', error)
        }
    }
}

const changePage = (page: number) => {
    router.get(route('finance.accounts-receivable.invoices.index'), { page }, { preserveState: true })
}

const getStatusVariant = (status: string): "default" | "secondary" | "outline" | "destructive" => {
    switch (status) {
        case 'draft':
            return 'secondary'
        case 'sent':
            return 'default'
        case 'open':
            return 'default'
        case 'partial':
            return 'outline'
        case 'paid':
            return 'default'
        case 'overdue':
            return 'destructive'
        case 'cancelled':
            return 'destructive'
        default:
            return 'secondary'
    }
}

const getStatusColor = (status: string) => {
    switch (status) {
        case 'draft':
            return 'text-gray-600 bg-gray-100'
        case 'sent':
            return 'text-blue-600 bg-blue-100'
        case 'open':
            return 'text-blue-600 bg-blue-100'
        case 'partial':
            return 'text-orange-600 bg-orange-100'
        case 'paid':
            return 'text-green-600 bg-green-100'
        case 'overdue':
            return 'text-white bg-red-100'
        case 'cancelled':
            return 'text-white bg-red-100'
        default:
            return 'text-gray-600 bg-gray-100'
    }
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

const openRecordPaymentModal = (invoice: Invoice) => {
    selectedInvoice.value = invoice
    showViewInvoiceModal.value = false
    showRecordPaymentModal.value = true
}

const openEditInvoiceModal = (invoice: Invoice) => {
    selectedInvoice.value = invoice
    showEditInvoiceModal.value = true
}

const openViewInvoiceModal = (invoice: Invoice) => {
    selectedInvoice.value = invoice
    showViewInvoiceModal.value = true
}

const onInvoiceCreated = () => {
    showCreateInvoiceModal.value = false
    // Refresh the invoices list
    fetchInvoices()
}

const onPaymentRecorded = () => {
    showRecordPaymentModal.value = false
    selectedInvoice.value = null
    // Refresh the invoices list
    fetchInvoices()
}

const onInvoiceUpdated = () => {
    showEditInvoiceModal.value = false
    selectedInvoice.value = null
    // Refresh the invoices list
    fetchInvoices()
}

const postInvoice = async (id: number) => {
    if (confirm('Are you sure you want to post this invoice? This will change the status from draft to sent.')) {
        try {
            loading.value = true
            const response = await apiService.postInvoice(id)

            if (response.success) {
                // Refresh the invoices list
                fetchInvoices()
                // Show success message
                alert('Invoice posted successfully! Status changed to sent.')
            } else {
                alert(`Error: ${response.message || 'Failed to post invoice'}`)
            }
        } catch (error: any) {
            console.error('Error posting invoice:', error)

            if (error.response?.data?.message) {
                alert(`Error: ${error.response.data.message}`)
            } else {
                alert('Failed to post invoice. Please try again.')
            }
        } finally {
            loading.value = false
        }
    }
}

onMounted(() => {
    fetchInvoices()
})
</script>
