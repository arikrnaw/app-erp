<template>
    <AppLayout title="Invoices">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl leading-tight">
                    Invoices (Accounts Receivable)
                </h2>
                <Link :href="route('finance.accounts-receivable.invoices.create')"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                <Plus class="w-4 h-4 mr-2" />
                Create Invoice
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Filters -->
                <div class="overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <Label for="search">Search</Label>
                                <Input id="search" v-model="filters.search" placeholder="Invoice number, customer..."
                                    @input="debouncedSearch" class="mt-1" />
                            </div>

                            <div>
                                <Label for="status_filter">Status</Label>
                                <Select v-model="filters.status" @update:model-value="fetchInvoices">
                                    <SelectTrigger class="mt-1">
                                        <SelectValue placeholder="All Status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All Status</SelectItem>
                                        <SelectItem value="draft">Draft</SelectItem>
                                        <SelectItem value="sent">Sent</SelectItem>
                                        <SelectItem value="paid">Paid</SelectItem>
                                        <SelectItem value="overdue">Overdue</SelectItem>
                                        <SelectItem value="cancelled">Cancelled</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div>
                                <Label for="date_from">Date From</Label>
                                <Input id="date_from" v-model="filters.date_from" type="date" @change="fetchInvoices"
                                    class="mt-1" />
                            </div>

                            <div>
                                <Label for="date_to">Date To</Label>
                                <Input id="date_to" v-model="filters.date_to" type="date" @change="fetchInvoices"
                                    class="mt-1" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                    <div class="overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                            </path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Total Invoices</p>
                                    <p class="text-lg font-semibold">{{ summary.total_invoices }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1">
                                            </path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Total Amount</p>
                                    <p class="text-lg font-semibold">{{
                                        formatCurrency(summary.total_amount) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Outstanding</p>
                                    <p class="text-lg font-semibold">{{
                                        formatCurrency(summary.outstanding_amount)
                                    }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-red-500 rounded-md flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z">
                                            </path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Overdue</p>
                                    <p class="text-lg font-semibold">{{
                                        formatCurrency(summary.overdue_amount) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Invoices Table -->
                <div class="overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Invoice #</TableHead>
                                        <TableHead>Customer</TableHead>
                                        <TableHead>Date</TableHead>
                                        <TableHead>Due Date</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead class="text-right">Total Amount</TableHead>
                                        <TableHead class="text-right">Paid Amount</TableHead>
                                        <TableHead class="text-right">Balance</TableHead>
                                        <TableHead>Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="invoice in invoices" :key="invoice.id">
                                        <TableCell class="font-medium">{{ invoice.invoice_number }}</TableCell>
                                        <TableCell>{{ invoice.customer?.name || 'N/A' }}</TableCell>
                                        <TableCell>{{ formatDate(invoice.invoice_date) }}</TableCell>
                                        <TableCell>
                                            <span :class="isInvoiceOverdue(invoice) ? 'text-red-600 font-medium' : ''">
                                                {{ formatDate(invoice.due_date) }}
                                            </span>
                                        </TableCell>
                                        <TableCell>
                                            <Badge :variant="getStatusVariant(invoice.status)">
                                                {{ getStatusLabel(invoice.status) }}
                                            </Badge>
                                        </TableCell>
                                        <TableCell class="text-right">{{ formatCurrency(invoice.total_amount) }}
                                        </TableCell>
                                        <TableCell class="text-right">{{ formatCurrency(invoice.paid_amount) }}
                                        </TableCell>
                                        <TableCell class="text-right font-semibold">{{
                                            formatCurrency(invoice.balance_amount) }}
                                        </TableCell>
                                        <TableCell>
                                            <DropdownMenu>
                                                <DropdownMenuTrigger asChild>
                                                    <Button variant="ghost" class="h-8 w-8 p-0">
                                                        <MoreHorizontal class="h-4 w-4" />
                                                    </Button>
                                                </DropdownMenuTrigger>
                                                <DropdownMenuContent align="end">
                                                    <DropdownMenuItem @click="viewInvoice(invoice.id)">
                                                        <Eye class="mr-2 h-4 w-4" />
                                                        View
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem @click="editInvoice(invoice.id)"
                                                        v-if="invoice.status === 'draft'">
                                                        <Edit class="mr-2 h-4 w-4" />
                                                        Edit
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem @click="recordPayment(invoice.id)"
                                                        v-if="invoice.balance_amount > 0">
                                                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1">
                                                            </path>
                                                        </svg>
                                                        Record Payment
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem @click="deleteInvoice(invoice.id)"
                                                        v-if="invoice.status === 'draft'" class="text-red-600">
                                                        <Trash2 class="mr-2 h-4 w-4" />
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
                        <div v-if="pagination" class="flex items-center justify-between mt-6">
                            <div class="text-sm text-gray-700">
                                Showing {{ pagination.meta.from }} to {{ pagination.meta.to }} of {{
                                    pagination.meta.total }}
                                results
                            </div>
                            <div class="flex items-center space-x-2">
                                <Button :disabled="pagination.meta.current_page === 1"
                                    @click="changePage(pagination.meta.current_page - 1)" variant="outline" size="sm">
                                    Previous
                                </Button>
                                <span class="text-sm text-gray-700">
                                    Page {{ pagination.meta.current_page }} of {{ pagination.meta.last_page }}
                                </span>
                                <Button :disabled="pagination.meta.current_page === pagination.meta.last_page"
                                    @click="changePage(pagination.meta.current_page + 1)" variant="outline" size="sm">
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
import { ref, onMounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Badge } from '@/components/ui/badge'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import { Plus, MoreHorizontal, Eye, Edit, Trash2 } from 'lucide-vue-next'
import { apiService } from '@/services/api'
import type { Invoice } from '@/types/erp'

interface Summary {
    total_invoices: number
    total_amount: number
    outstanding_amount: number
    overdue_amount: number
}

const invoices = ref<Invoice[]>([])
const pagination = ref<any>(null)
const summary = ref<Summary>({
    total_invoices: 0,
    total_amount: 0,
    outstanding_amount: 0,
    overdue_amount: 0
})
const loading = ref(false)

const filters = ref({
    search: '',
    status: 'all',
    date_from: '',
    date_to: ''
})

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

const getStatusLabel = (status: string) => {
    const labels: Record<string, string> = {
        draft: 'Draft',
        sent: 'Sent',
        paid: 'Paid',
        overdue: 'Overdue',
        cancelled: 'Cancelled'
    }
    return labels[status] || status
}

const getStatusVariant = (status: string): 'default' | 'secondary' | 'destructive' => {
    const variants: Record<string, 'default' | 'secondary' | 'destructive'> = {
        draft: 'secondary',
        sent: 'default',
        paid: 'default',
        overdue: 'destructive',
        cancelled: 'destructive'
    }
    return variants[status] || 'default'
}

const fetchInvoices = async () => {
    try {
        loading.value = true
        const params = {
            page: pagination.value?.meta?.current_page || 1,
            search: filters.value.search,
            status: filters.value.status === 'all' ? '' : filters.value.status,
            date_from: filters.value.date_from,
            date_to: filters.value.date_to
        }

        const response = await apiService.getInvoices(params)
        invoices.value = response.data
        pagination.value = response
        summary.value = response.summary || summary.value
    } catch (error) {
        console.error('Error fetching invoices:', error)
    } finally {
        loading.value = false
    }
}

const debouncedSearch = () => {
    clearTimeout(searchTimeout.value)
    searchTimeout.value = setTimeout(() => {
        fetchInvoices()
    }, 500)
}

const changePage = (page: number) => {
    pagination.value.meta.current_page = page
    fetchInvoices()
}

const viewInvoice = (id: number) => {
    router.visit(route('finance.accounts-receivable.invoices.show', id))
}

const editInvoice = (id: number) => {
    router.visit(route('finance.accounts-receivable.invoices.edit', id))
}

const recordPayment = (id: number) => {
    router.visit(route('finance.accounts-receivable.payments.create', { invoice_id: id }))
}

const deleteInvoice = async (id: number) => {
    if (!confirm('Are you sure you want to delete this invoice?')) {
        return
    }

    try {
        await apiService.deleteInvoice(id)
        fetchInvoices()
    } catch (error) {
        console.error('Error deleting invoice:', error)
    }
}

const searchTimeout = ref<number | null>(null)

const isInvoiceOverdue = (invoice: Invoice): boolean => {
    return new Date(invoice.due_date) < new Date() &&
        invoice.status !== 'paid' &&
        invoice.status !== 'cancelled'
}

const clearTimeout = (timeout: number | null) => {
    if (timeout) {
        window.clearTimeout(timeout)
    }
}

onMounted(() => {
    fetchInvoices()
})
</script>
