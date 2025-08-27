<template>
    <Dialog :open="open" @update:open="$emit('close')">
        <DialogContent class="!max-w-4xl max-h-[90vh] overflow-y-auto">
            <DialogHeader>
                <DialogTitle>Bill Details</DialogTitle>
                <DialogDescription>
                    View complete bill information and details
                </DialogDescription>
            </DialogHeader>

            <div class="space-y-6">
                <!-- Bill Header -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Bill Info -->
                    <div class="space-y-4">
                        <div>
                            <h3 class="text-lg font-semibold">Bill Information</h3>
                            <div class="mt-2 space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-sm text-muted-foreground">Bill Number:</span>
                                    <span class="text-sm font-medium">{{ bill.bill_number }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-muted-foreground">Status:</span>
                                    <Badge :variant="getStatusVariant(bill.status)"
                                        :class="getStatusColor(bill.status)">
                                        {{ bill.status }}
                                    </Badge>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-muted-foreground">Bill Date:</span>
                                    <span class="text-sm font-medium">{{ formatDate(bill.bill_date)
                                    }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-muted-foreground">Due Date:</span>
                                    <span class="text-sm font-medium"
                                        :class="isOverdue(bill.due_date) ? 'text-destructive' : ''">
                                        {{ formatDate(bill.due_date) }}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-muted-foreground">Created:</span>
                                    <span class="text-sm font-medium">{{ formatDate(bill.created_at)
                                    }}</span>
                                </div>
                                <div v-if="bill.posted_at" class="flex justify-between">
                                    <span class="text-sm text-muted-foreground">Posted:</span>
                                    <span class="text-sm font-medium">{{ formatDate(bill.posted_at)
                                    }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Supplier Info -->
                    <div class="space-y-4">
                        <div>
                            <h3 class="text-lg font-semibold">Supplier Information</h3>
                            <div class="mt-2 space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-sm text-muted-foreground">Supplier:</span>
                                    <span class="text-sm font-medium">{{ bill.supplier?.name || 'N/A'
                                    }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-muted-foreground">Email:</span>
                                    <span class="text-sm font-medium">{{ bill.supplier?.email || 'N/A'
                                    }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-muted-foreground">Phone:</span>
                                    <span class="text-sm font-medium">{{ bill.supplier?.phone || 'N/A'
                                    }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-muted-foreground">Address:</span>
                                    <span class="text-sm font-medium">{{ bill.supplier?.address || 'N/A'
                                    }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bill Items -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold">Bill Items</h3>
                    <div v-if="bill.items && bill.items.length > 0" class="rounded-lg border bg-muted/30">
                        <div class="p-4">
                            <div class="grid grid-cols-6 gap-4 font-medium text-sm text-muted-foreground pb-3 border-b">
                                <div class="col-span-2">Description</div>
                                <div class="text-center">Quantity</div>
                                <div class="text-center">Unit Price</div>
                                <div class="text-center">Tax</div>
                                <div class="text-center">Total</div>
                            </div>
                            <div class="space-y-3">
                                <div v-for="item in bill.items" :key="item.id"
                                    class="grid grid-cols-6 gap-4 py-3 border-b last:border-b-0">
                                    <div class="col-span-2 font-medium">
                                        {{ item.description || 'No description' }}
                                    </div>
                                    <div class="text-center text-muted-foreground">
                                        {{ item.quantity || 0 }}
                                    </div>
                                    <div class="text-center text-muted-foreground">
                                        {{ formatCurrency(item.unit_price || 0) }}
                                    </div>
                                    <div class="text-center text-muted-foreground">
                                        {{ formatCurrency(item.tax_amount || 0) }}
                                    </div>
                                    <div class="text-center font-semibold">
                                        {{ formatCurrency(item.total_amount || 0) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-8 text-muted-foreground border rounded-lg">
                        <p class="text-sm">No bill items found</p>
                    </div>
                </div>

                <!-- Bill Totals -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Financial Summary -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold">Financial Summary</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-sm text-muted-foreground">Subtotal:</span>
                                <span class="text-sm font-medium">{{ formatCurrency(bill.subtotal)
                                }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-muted-foreground">Tax Amount:</span>
                                <span class="text-sm font-medium">{{ formatCurrency(bill.tax_amount ||
                                    0) }}</span>
                            </div>
                            <div class="flex justify-between border-t pt-2">
                                <span class="text-base font-semibold">Total Amount:</span>
                                <span class="text-base font-bold">{{ formatCurrency(bill.total_amount)
                                }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-muted-foreground">Paid Amount:</span>
                                <span class="text-sm font-medium text-green-600 dark:text-green-400">
                                    {{ formatCurrency(bill.paid_amount || 0) }}
                                </span>
                            </div>
                            <div class="flex justify-between border-t pt-2">
                                <span class="text-base font-semibold">Balance Due:</span>
                                <span class="text-base font-bold"
                                    :class="bill.balance_amount > 0 ? 'text-destructive' : 'text-green-600 dark:text-green-400'">
                                    {{ formatCurrency(bill.balance_amount || 0) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Payment History -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold">Payment History</h3>
                        <div v-if="bill.payments && bill.payments.length > 0" class="space-y-3">
                            <div v-for="payment in bill.payments" :key="payment.id"
                                class="p-3 border rounded-lg space-y-2 bg-muted/30">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium">{{ payment.payment_number
                                    }}</span>
                                    <Badge variant="outline" class="text-xs">
                                        {{ payment.status }}
                                    </Badge>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-muted-foreground">{{ formatDate(payment.payment_date)
                                    }}</span>
                                    <span class="font-medium">{{ formatCurrency(payment.amount)
                                    }}</span>
                                </div>
                                <div class="text-xs text-muted-foreground">
                                    {{ payment.payment_method }} - {{ payment.reference_number || 'No reference' }}
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-4 text-muted-foreground">
                            <CreditCard class="h-8 w-8 mx-auto mb-2 opacity-50" />
                            <p class="text-sm">No payments recorded yet</p>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div v-if="bill.notes" class="space-y-4">
                    <h3 class="text-lg font-semibold">Notes</h3>
                    <div class="p-4 bg-muted/30 border rounded-lg">
                        <p class="text-sm">{{ bill.notes }}</p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end space-x-3 pt-6 border-t">
                    <Button variant="outline" @click="$emit('close')">
                        Close
                    </Button>
                    <Button v-if="bill.status === 'draft'" @click="$emit('edit')">
                        <Edit class="w-4 h-4 mr-2" />
                        Edit Bill
                    </Button>
                    <Button v-if="bill.status === 'posted'" @click="$emit('record-payment')">
                        <CreditCard class="w-4 h-4 mr-2" />
                        Record Payment
                    </Button>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Edit, CreditCard } from 'lucide-vue-next'
import type { Bill } from '@/types/erp'

interface Props {
    open: boolean
    bill: Bill
}

interface Emits {
    (e: 'close'): void
    (e: 'edit'): void
    (e: 'record-payment'): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const getStatusVariant = (status: string): "default" | "secondary" | "outline" | "destructive" => {
    const variants: Record<string, "default" | "secondary" | "outline" | "destructive"> = {
        'draft': 'secondary',
        'posted': 'default',
        'paid': 'default',
        'overdue': 'destructive',
        'cancelled': 'outline'
    }
    return variants[status] || 'secondary'
}

const getStatusColor = (status: string): string => {
    const colors: Record<string, string> = {
        'draft': 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200',
        'posted': 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
        'paid': 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        'overdue': 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
        'cancelled': 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200'
    }
    return colors[status] || ''
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
