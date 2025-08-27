<template>
    <div class="space-y-6">
        <!-- Invoice Header -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Invoice Info -->
            <div class="space-y-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Invoice Information</h3>
                    <div class="mt-2 space-y-2">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Invoice Number:</span>
                            <span class="text-sm font-medium">{{ invoice.invoice_number }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Status:</span>
                            <Badge :variant="getStatusVariant(invoice.status)" :class="getStatusColor(invoice.status)">
                                {{ invoice.status }}
                            </Badge>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Invoice Date:</span>
                            <span class="text-sm font-medium">{{ formatDate(invoice.invoice_date) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Due Date:</span>
                            <span class="text-sm font-medium"
                                :class="isOverdue(invoice.due_date) ? 'text-red-600 dark:text-red-400' : ''">
                                {{ formatDate(invoice.due_date) }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Created:</span>
                            <span class="text-sm font-medium">{{ formatDate(invoice.created_at) }}</span>
                        </div>
                        <div v-if="invoice.posted_at" class="flex justify-between">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Posted:</span>
                            <span class="text-sm font-medium">{{ formatDate(invoice.posted_at) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer Info -->
            <div class="space-y-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Customer Information</h3>
                    <div class="mt-2 space-y-2">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Customer:</span>
                            <span class="text-sm font-medium">{{ invoice.customer?.name || 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Email:</span>
                            <span class="text-sm font-medium">{{ invoice.customer?.email || 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Phone:</span>
                            <span class="text-sm font-medium">{{ invoice.customer?.phone || 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Address:</span>
                            <span class="text-sm font-medium">{{ invoice.customer?.address || 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Invoice Items -->
        <div class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Invoice Items</h3>
            <div class="rounded-md border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Item</TableHead>
                            <TableHead>Description</TableHead>
                            <TableHead class="text-right">Qty</TableHead>
                            <TableHead class="text-right">Unit Price</TableHead>
                            <TableHead class="text-right">Tax</TableHead>
                            <TableHead class="text-right">Discount</TableHead>
                            <TableHead class="text-right">Total</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="item in invoice.items" :key="item.id">
                            <TableCell class="font-medium">{{ item.item_name }}</TableCell>
                            <TableCell>{{ item.description || '-' }}</TableCell>
                            <TableCell class="text-right">{{ item.quantity }}</TableCell>
                            <TableCell class="text-right">{{ formatCurrency(item.unit_price) }}</TableCell>
                            <TableCell class="text-right">{{ formatCurrency(item.tax_amount) }}</TableCell>
                            <TableCell class="text-right">{{ formatCurrency(item.discount_amount) }}</TableCell>
                            <TableCell class="text-right font-medium">{{ formatCurrency(item.total_amount) }}
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>

        <!-- Invoice Totals -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Financial Summary -->
            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Financial Summary</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Subtotal:</span>
                        <span class="text-sm font-medium">{{ formatCurrency(invoice.subtotal) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Tax Amount:</span>
                        <span class="text-sm font-medium">{{ formatCurrency(invoice.tax_amount) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Discount Amount:</span>
                        <span class="text-sm font-medium">{{ formatCurrency(invoice.discount_amount) }}</span>
                    </div>
                    <div class="flex justify-between border-t pt-2">
                        <span class="text-base font-semibold">Total Amount:</span>
                        <span class="text-base font-bold">{{ formatCurrency(invoice.total_amount) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Paid Amount:</span>
                        <span class="text-sm font-medium text-green-600 dark:text-green-400">
                            {{ formatCurrency(invoice.paid_amount) }}
                        </span>
                    </div>
                    <div class="flex justify-between border-t pt-2">
                        <span class="text-base font-semibold">Balance Due:</span>
                        <span class="text-base font-bold"
                            :class="invoice.balance_amount > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400'">
                            {{ formatCurrency(invoice.balance_amount) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Payment History -->
            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Payment History</h3>
                <div v-if="invoice.payments && invoice.payments.length > 0" class="space-y-3">
                    <div v-for="payment in invoice.payments" :key="payment.id" class="p-3 border rounded-lg space-y-2">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium">{{ payment.payment_number }}</span>
                            <Badge variant="outline" class="text-xs">
                                {{ payment.status }}
                            </Badge>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">{{ formatDate(payment.payment_date) }}</span>
                            <span class="font-medium">{{ formatCurrency(payment.amount) }}</span>
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            {{ payment.payment_method }} - {{ payment.reference_number || 'No reference' }}
                        </div>
                    </div>
                </div>
                <div v-else class="text-center py-4 text-gray-500 dark:text-gray-400">
                    <CreditCard class="h-8 w-8 mx-auto mb-2 opacity-50" />
                    <p class="text-sm">No payments recorded yet</p>
                </div>
            </div>
        </div>

        <!-- Notes -->
        <div v-if="invoice.notes" class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Notes</h3>
            <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                <p class="text-sm text-gray-700 dark:text-gray-300">{{ invoice.notes }}</p>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-end space-x-3 pt-6 border-t">
            <Button variant="outline" @click="$emit('close')">
                Close
            </Button>
            <Button v-if="invoice.status === 'draft'" @click="$emit('edit')">
                <Edit class="w-4 h-4 mr-2" />
                Edit Invoice
            </Button>
            <Button v-if="invoice.status === 'sent'" @click="$emit('record-payment')">
                <CreditCard class="w-4 h-4 mr-2" />
                Record Payment
            </Button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Edit, CreditCard } from 'lucide-vue-next'
import type { Invoice } from '@/types/erp'

interface Props {
    invoice: Invoice
}

interface Emits {
    (e: 'close'): void
    (e: 'edit'): void
    (e: 'record-payment'): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

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
</script>
