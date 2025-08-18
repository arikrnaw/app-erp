<template>
    <AppLayout title="Invoice Details">
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <Link :href="route('finance.accounts-receivable.invoices.index')"
                        class="flex items-center text-sm text-gray-500 hover:text-gray-700">
                    <ArrowLeft class="w-4 h-4 mr-2" />
                    Back to Invoices
                    </Link>
                    <h2 class="text-xl font-semibold">Invoice Details</h2>
                </div>
                <div class="flex items-center space-x-2">
                    <Link :href="route('finance.accounts-receivable.invoices.edit', invoice.id)"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">
                    <Edit class="w-4 h-4 mr-2" />
                    Edit
                    </Link>
                    <Link :href="route('finance.accounts-receivable.payments.create', { invoice_id: invoice.id })"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                    Record Payment
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Invoice Header -->
                <div class="overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3 class="text-lg font-medium mb-4">Invoice Information</h3>
                                <dl class="space-y-3">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Invoice Number</dt>
                                        <dd class="text-sm">{{ invoice.invoice_number }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                                        <dd>
                                            <Badge :variant="getStatusVariant(invoice.status)">
                                                {{ getStatusLabel(invoice.status) }}
                                            </Badge>
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Invoice Date</dt>
                                        <dd class="text-sm">{{ formatDate(invoice.invoice_date) }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Due Date</dt>
                                        <dd class="text-sm">{{ formatDate(invoice.due_date) }}</dd>
                                    </div>
                                    <div v-if="invoice.reference_type">
                                        <dt class="text-sm font-medium text-gray-500">Reference</dt>
                                        <dd class="text-sm">{{
                                            getReferenceTypeLabel(invoice.reference_type) }}
                                            #{{ invoice.reference_id }}</dd>
                                    </div>
                                </dl>
                            </div>

                            <div>
                                <h3 class="text-lg font-medium mb-4">Customer Information</h3>
                                <dl class="space-y-3">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Customer</dt>
                                        <dd class="text-sm">{{ invoice.customer?.name }}</dd>
                                    </div>
                                    <div v-if="invoice.customer?.email">
                                        <dt class="text-sm font-medium text-gray-500">Email</dt>
                                        <dd class="text-sm">{{ invoice.customer.email }}</dd>
                                    </div>
                                    <div v-if="invoice.customer?.phone">
                                        <dt class="text-sm font-medium text-gray-500">Phone</dt>
                                        <dd class="text-sm">{{ invoice.customer.phone }}</dd>
                                    </div>
                                    <div v-if="invoice.customer?.address">
                                        <dt class="text-sm font-medium text-gray-500">Address</dt>
                                        <dd class="text-sm">{{ invoice.customer.address }}</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        <div v-if="invoice.description" class="mt-6">
                            <h4 class="text-sm font-medium text-gray-500 mb-2">Description</h4>
                            <p class="text-sm">{{ invoice.description }}</p>
                        </div>
                    </div>
                </div>

                <!-- Invoice Items -->
                <div class="overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-medium mb-4">Invoice Items</h3>
                        <div class="overflow-x-auto">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Item</TableHead>
                                        <TableHead>Description</TableHead>
                                        <TableHead class="text-right">Quantity</TableHead>
                                        <TableHead class="text-right">Unit Price</TableHead>
                                        <TableHead class="text-right">Tax Rate</TableHead>
                                        <TableHead class="text-right">Discount</TableHead>
                                        <TableHead class="text-right">Total</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="item in invoice.items" :key="item.id">
                                        <TableCell>
                                            <div>
                                                <div class="font-medium">{{ item.item_name }}</div>
                                                <div v-if="item.product" class="text-sm text-gray-500">{{
                                                    item.product.name }}
                                                </div>
                                            </div>
                                        </TableCell>
                                        <TableCell>{{ item.description || '-' }}</TableCell>
                                        <TableCell class="text-right">{{ item.quantity }}</TableCell>
                                        <TableCell class="text-right">{{ formatCurrency(item.unit_price) }}</TableCell>
                                        <TableCell class="text-right">{{ item.tax_rate }}%</TableCell>
                                        <TableCell class="text-right">{{ formatCurrency(item.discount_amount) }}
                                        </TableCell>
                                        <TableCell class="text-right font-medium">{{ formatCurrency(item.total_amount)
                                            }}
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </div>
                    </div>
                </div>

                <!-- Invoice Summary -->
                <div class="overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-medium mb-4">Invoice Summary</h3>
                        <div class="flex justify-end">
                            <div class="w-64 space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-white">Subtotal:</span>
                                    <span class="font-medium">{{ formatCurrency(invoice.subtotal) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-white">Discount:</span>
                                    <span class="font-medium text-red-600">-{{ formatCurrency(invoice.discount_amount)
                                        }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-white">Tax:</span>
                                    <span class="font-medium">{{ formatCurrency(invoice.tax_amount) }}</span>
                                </div>
                                <div class="flex justify-between text-lg font-semibold border-t pt-2">
                                    <span>Total Amount:</span>
                                    <span>{{ formatCurrency(invoice.total_amount) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-white">Paid Amount:</span>
                                    <span class="font-medium text-green-600">{{ formatCurrency(invoice.paid_amount)
                                        }}</span>
                                </div>
                                <div class="flex justify-between text-lg font-semibold">
                                    <span>Balance:</span>
                                    <span :class="invoice.balance_amount > 0 ? 'text-red-600' : 'text-green-600'">
                                        {{ formatCurrency(invoice.balance_amount) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payments -->
                <div v-if="invoice.payments && invoice.payments.length > 0"
                    class="overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-medium mb-4">Payments</h3>
                        <div class="overflow-x-auto">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Payment Number</TableHead>
                                        <TableHead>Date</TableHead>
                                        <TableHead>Method</TableHead>
                                        <TableHead>Reference</TableHead>
                                        <TableHead class="text-right">Amount</TableHead>
                                        <TableHead>Status</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="payment in invoice.payments" :key="payment.id">
                                        <TableCell>{{ payment.payment_number }}</TableCell>
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
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div v-if="invoice.notes" class="overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium mb-4">Notes</h3>
                        <p class="text-sm">{{ invoice.notes }}</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Badge } from '@/components/ui/badge'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { ArrowLeft, Edit } from 'lucide-vue-next'
import type { Invoice } from '@/types/erp'

interface Props {
    invoice: Invoice
}

const props = defineProps<Props>()

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    })
}

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(amount)
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

const getReferenceTypeLabel = (type: string) => {
    const labels: Record<string, string> = {
        sales_order: 'Sales Order',
        manual: 'Manual'
    }
    return labels[type] || type
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
</script>
