<template>
    <AppLayout title="Payments">
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <h2 class="text-xl font-semibold">Payments</h2>
                </div>
                <Link :href="route('finance.accounts-receivable.payments.create')"
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                <Plus class="w-4 h-4 mr-2" />
                New Payment
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                    <div class="overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                        <span class="text-white text-sm font-medium">P</span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-500">Total Payments</div>
                                    <div class="text-lg font-semibold">{{ summary.total_payments }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                                        <span class="text-white text-sm font-medium">C</span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-500">Completed</div>
                                    <div class="text-lg font-semibold">{{ summary.completed_payments }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                                        <span class="text-white text-sm font-medium">P</span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-500">Pending</div>
                                    <div class="text-lg font-semibold">{{ summary.pending_payments }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-green-600 rounded-md flex items-center justify-center">
                                        <span class="text-white text-sm font-medium">$</span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-500">Total Amount</div>
                                    <div class="text-lg font-semibold">{{
                                        formatCurrency(summary.total_amount) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <Label for="search">Search</Label>
                                <Input id="search" v-model="filters.search" placeholder="Search payments..."
                                    @input="debouncedSearch" />
                            </div>

                            <div>
                                <Label for="status">Status</Label>
                                <Select v-model="filters.status">
                                    <SelectTrigger>
                                        <SelectValue placeholder="All Status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All Status</SelectItem>
                                        <SelectItem value="pending">Pending</SelectItem>
                                        <SelectItem value="completed">Completed</SelectItem>
                                        <SelectItem value="cancelled">Cancelled</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div>
                                <Label for="date_from">Date From</Label>
                                <Input id="date_from" v-model="filters.date_from" type="date" />
                            </div>

                            <div>
                                <Label for="date_to">Date To</Label>
                                <Input id="date_to" v-model="filters.date_to" type="date" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payments Table -->
                <div class="overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Payment Number</TableHead>
                                        <TableHead>Customer</TableHead>
                                        <TableHead>Invoice</TableHead>
                                        <TableHead>Date</TableHead>
                                        <TableHead>Method</TableHead>
                                        <TableHead>Reference</TableHead>
                                        <TableHead class="text-right">Amount</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead class="text-right">Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="payment in payments" :key="payment.id">
                                        <TableCell class="font-medium">{{ payment.payment_number }}</TableCell>
                                        <TableCell>{{ payment.customer?.name }}</TableCell>
                                        <TableCell>
                                            <Link v-if="payment.invoice"
                                                :href="route('finance.accounts-receivable.invoices.show', payment.invoice.id)"
                                                class="text-indigo-600 hover:text-indigo-900">
                                            {{ payment.invoice.invoice_number }}
                                            </Link>
                                            <span v-else class="text-gray-500">-</span>
                                        </TableCell>
                                        <TableCell>{{ formatDate(payment.payment_date) }}</TableCell>
                                        <TableCell>{{ getPaymentMethodLabel(payment.payment_method) }}</TableCell>
                                        <TableCell>{{ payment.reference_number || '-' }}</TableCell>
                                        <TableCell class="text-right font-medium">{{ formatCurrency(payment.amount) }}
                                        </TableCell>
                                        <TableCell>
                                            <Badge :variant="getPaymentStatusVariant(payment.status)">
                                                {{ getPaymentStatusLabel(payment.status) }}
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
                                                    <DropdownMenuItem @click="viewPayment(payment.id)">
                                                        <Eye class="mr-2 h-4 w-4" />
                                                        View
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem @click="editPayment(payment.id)">
                                                        <Edit class="mr-2 h-4 w-4" />
                                                        Edit
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem @click="deletePayment(payment.id)"
                                                        class="text-red-600">
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
                        <div v-if="pagination" class="mt-6 flex items-center justify-between">
                            <div class="text-sm text-gray-700">
                                Showing {{ pagination.meta.from }} to {{ pagination.meta.to }} of {{
                                    pagination.meta.total }}
                                results
                            </div>
                            <div class="flex space-x-2">
                                <Button v-for="link in pagination.links" :key="link.label"
                                    :disabled="!link.url || link.active" @click="changePage(link.url)" variant="outline"
                                    size="sm" v-html="link.label" />
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
import type { Payment } from '@/types/erp'

interface Summary {
    total_payments: number
    completed_payments: number
    pending_payments: number
    total_amount: number
}

const payments = ref<Payment[]>([])
const pagination = ref<any>(null)
const summary = ref<Summary>({
    total_payments: 0,
    completed_payments: 0,
    pending_payments: 0,
    total_amount: 0
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

const getPaymentMethodLabel = (method: string) => {
    const labels: Record<string, string> = {
        cash: 'Cash',
        bank_transfer: 'Bank Transfer',
        credit_card: 'Credit Card',
        check: 'Check',
        other: 'Other'
    }
    return labels[method] || method
}

const getPaymentStatusLabel = (status: string) => {
    const labels: Record<string, string> = {
        pending: 'Pending',
        completed: 'Completed',
        cancelled: 'Cancelled'
    }
    return labels[status] || status
}

const getPaymentStatusVariant = (status: string): 'default' | 'secondary' | 'destructive' => {
    const variants: Record<string, 'default' | 'secondary' | 'destructive'> = {
        pending: 'secondary',
        completed: 'default',
        cancelled: 'destructive'
    }
    return variants[status] || 'default'
}

const fetchPayments = async () => {
    try {
        loading.value = true
        const params = {
            page: pagination.value?.meta?.current_page || 1,
            search: filters.value.search,
            status: filters.value.status === 'all' ? '' : filters.value.status,
            date_from: filters.value.date_from,
            date_to: filters.value.date_to
        }

        const response = await apiService.getPayments(params)
        payments.value = response.data
        pagination.value = response
        summary.value = response.summary || summary.value
    } catch (error) {
        console.error('Error fetching payments:', error)
    } finally {
        loading.value = false
    }
}

const debouncedSearch = () => {
    clearTimeout(searchTimeout.value)
    searchTimeout.value = setTimeout(() => {
        fetchPayments()
    }, 500)
}

const changePage = (url: string) => {
    if (url) {
        const page = new URL(url).searchParams.get('page')
        if (page) {
            pagination.value.meta.current_page = parseInt(page)
            fetchPayments()
        }
    }
}

const viewPayment = (id: number) => {
    router.visit(route('finance.accounts-receivable.payments.show', id))
}

const editPayment = (id: number) => {
    router.visit(route('finance.accounts-receivable.payments.edit', id))
}

const deletePayment = async (id: number) => {
    if (!confirm('Are you sure you want to delete this payment?')) {
        return
    }

    try {
        await apiService.deletePayment(id)
        fetchPayments()
    } catch (error) {
        console.error('Error deleting payment:', error)
    }
}

const searchTimeout = ref<number | null>(null)

const clearTimeout = (timeout: number | null) => {
    if (timeout) {
        window.clearTimeout(timeout)
    }
}

onMounted(() => {
    fetchPayments()
})
</script>
