<template>
    <div class="space-y-6">
        <form @submit.prevent="submit" class="space-y-6">
            <!-- Invoice Summary -->
            <div class="p-4 bg-muted/30 rounded-lg border border-border">
                <h4 class="font-medium text-card-foreground mb-3">Invoice Summary</h4>

                <!-- Warning if invoice is already fully paid -->
                <div v-if="maxPaymentAmount <= 0" class="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded-md">
                    <div class="flex items-center">
                        <AlertTriangle class="h-4 w-4 text-yellow-600 mr-2" />
                        <span class="text-yellow-800 text-sm font-medium">
                            This invoice is already fully paid and cannot accept additional payments.
                        </span>
                    </div>
                </div>

                <!-- Warning if invoice status doesn't allow payments -->
                <div v-if="!canReceivePayments" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-md">
                    <div class="flex items-center">
                        <AlertTriangle class="h-4 w-4 text-red-600 mr-2" />
                        <span class="text-red-800 text-sm font-medium">
                            This invoice cannot receive payments. Only sent/open invoices can receive payments.
                            Current status: <span class="font-bold">{{ invoice.status }}</span>
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-muted-foreground">Invoice Number:</span>
                        <span class="ml-2 font-medium">{{ invoice.invoice_number }}</span>
                    </div>
                    <div>
                        <span class="text-muted-foreground">Customer:</span>
                        <span class="ml-2 font-medium">{{ invoice.customer?.name }}</span>
                    </div>
                    <div>
                        <span class="text-muted-foreground">Total Amount:</span>
                        <span class="ml-2 font-medium text-primary">{{ formatCurrency(invoice.total_amount) }}</span>
                    </div>
                    <div>
                        <span class="text-muted-foreground">Paid Amount:</span>
                        <span class="ml-2 font-medium text-green-600">{{ formatCurrency(invoice.paid_amount) }}</span>
                    </div>
                    <div>
                        <span class="text-muted-foreground">Balance Due:</span>
                        <span class="ml-2 font-medium"
                            :class="maxPaymentAmount > 0 ? 'text-red-600' : 'text-green-600'">
                            {{ formatCurrency(maxPaymentAmount) }}
                        </span>
                    </div>
                    <div>
                        <span class="text-muted-foreground">Status:</span>
                        <span class="ml-2 font-medium" :class="getStatusColor(invoice.status)">
                            {{ invoice.status }}
                        </span>
                    </div>
                    <div>
                        <span class="text-muted-foreground">Due Date:</span>
                        <span class="ml-2 font-medium">{{ formatDate(invoice.due_date) }}</span>
                    </div>
                </div>
            </div>

            <!-- Payment Information -->
            <div class="space-y-4">
                <h3 class="text-lg font-medium text-card-foreground">Payment Information</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-3">
                        <Label for="payment_date" class="text-sm font-medium">Payment Date *</Label>
                        <Input id="payment_date" v-model="form.payment_date" type="date" required class="h-10"
                            :disabled="loading || !canReceivePayments || maxPaymentAmount <= 0" />
                    </div>

                    <div class="space-y-3">
                        <Label for="payment_method" class="text-sm font-medium">Payment Method *</Label>
                        <Select v-model="form.payment_method"
                            :disabled="loading || !canReceivePayments || maxPaymentAmount <= 0">
                            <SelectTrigger class="h-10 w-full">
                                <SelectValue placeholder="Select payment method" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="cash">Cash</SelectItem>
                                <SelectItem value="bank_transfer">Bank Transfer</SelectItem>
                                <SelectItem value="credit_card">Credit Card</SelectItem>
                                <SelectItem value="check">Check</SelectItem>
                                <SelectItem value="other">Other</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-3">
                        <Label for="amount" class="text-sm font-medium">Payment Amount *</Label>
                        <Input id="amount" v-model="form.amount" type="number" step="0.01" min="0.01"
                            :max="maxPaymentAmount" placeholder="0.00" class="h-10" required
                            :disabled="loading || !canReceivePayments || maxPaymentAmount <= 0"
                            @input="validateAmount" />
                        <p class="text-sm text-muted-foreground">
                            Maximum payment: {{ formatCurrency(maxPaymentAmount) }}
                        </p>
                        <div v-if="amountError" class="text-sm text-red-600">
                            {{ amountError }}
                        </div>
                    </div>

                    <div class="space-y-3">
                        <Label for="reference_number" class="text-sm font-medium">Reference Number</Label>
                        <Input id="reference_number" v-model="form.reference_number" type="text"
                            placeholder="e.g., Check #123, Transaction ID" class="h-10"
                            :disabled="loading || !canReceivePayments || maxPaymentAmount <= 0" />
                        <p class="text-sm text-muted-foreground">
                            Optional reference for this payment
                        </p>
                    </div>
                </div>

                <div class="space-y-3">
                    <Label for="notes" class="text-sm font-medium">Payment Notes</Label>
                    <Textarea id="notes" v-model="form.notes" rows="3" placeholder="Enter payment notes or comments"
                        class="h-20" :disabled="loading || !canReceivePayments || maxPaymentAmount <= 0" />
                </div>
            </div>

            <!-- Payment Summary -->
            <div class="p-4 bg-muted/30 rounded-lg border border-border">
                <h4 class="font-medium text-card-foreground mb-3">Payment Summary</h4>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Invoice Total:</span>
                        <span class="font-medium">{{ formatCurrency(invoice.total_amount) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Already Paid:</span>
                        <span class="font-medium text-green-600">{{ formatCurrency(invoice.paid_amount) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">This Payment:</span>
                        <span class="font-medium text-blue-600">{{ formatCurrency(paymentAmount) }}</span>
                    </div>
                    <div class="border-t pt-2">
                        <div class="flex justify-between">
                            <span class="text-muted-foreground font-medium">Remaining Balance:</span>
                            <span class="font-medium" :class="remainingBalance > 0 ? 'text-red-600' : 'text-green-600'">
                                {{ formatCurrency(remainingBalance) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex items-center justify-end space-x-4 pt-6 border-t border-border">
                <Button type="button" variant="outline" @click="$emit('cancel')" class="h-10 px-4">
                    Cancel
                </Button>
                <Button type="submit" :disabled="loading || !isValid" class="h-10 px-6">
                    <Loader2 v-if="loading" class="w-4 h-4 mr-2 animate-spin" />
                    <span v-if="!canReceivePayments">Invoice Status Invalid</span>
                    <span v-else-if="maxPaymentAmount <= 0">Invoice Already Paid</span>
                    <span v-else>Record Payment</span>
                </Button>
            </div>
        </form>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Textarea } from '@/components/ui/textarea'
import { Loader2 } from 'lucide-vue-next'
import { apiService } from '@/services/api'
import type { Invoice } from '@/types/erp'
import { AlertTriangle } from 'lucide-vue-next'

interface Props {
    invoice: Invoice
}

interface Emits {
    (e: 'payment-recorded'): void
    (e: 'cancel'): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const loading = ref(false)
const amountError = ref('')

const form = ref({
    payment_date: new Date().toISOString().split('T')[0],
    payment_method: '',
    amount: '',
    reference_number: '',
    notes: ''
})

const maxPaymentAmount = computed(() => {
    // If balance_amount is 0 or invalid, check if invoice is already fully paid
    const balance = props.invoice.balance_amount || 0
    const totalAmount = props.invoice.total_amount || 0
    const paidAmount = props.invoice.paid_amount || 0

    // Calculate actual remaining balance
    const actualBalance = totalAmount - paidAmount

    // Return the higher value between balance_amount and calculated balance
    return Math.max(balance, actualBalance)
})

const paymentAmount = computed(() => {
    return parseFloat(form.value.amount) || 0
})

const remainingBalance = computed(() => {
    return maxPaymentAmount.value - paymentAmount.value
})

const canReceivePayments = computed(() => {
    // Only sent invoices can receive payments (based on backend validation)
    return props.invoice.status === 'sent'
})

const getStatusColor = (status: string) => {
    switch (status) {
        case 'draft':
            return 'text-gray-600'
        case 'sent':
            return 'text-blue-600'
        case 'paid':
            return 'text-green-600'
        case 'overdue':
            return 'text-red-600'
        case 'cancelled':
            return 'text-gray-500'
        default:
            return 'text-muted-foreground'
    }
}

const isValid = computed(() => {
    // Check if invoice can accept payments
    if (!canReceivePayments.value || maxPaymentAmount.value <= 0) {
        return false
    }

    return form.value.payment_date &&
        form.value.payment_method &&
        form.value.amount &&
        parseFloat(form.value.amount) > 0 &&
        parseFloat(form.value.amount) <= maxPaymentAmount.value &&
        !amountError.value
})

const validateAmount = () => {
    const amount = parseFloat(form.value.amount)

    if (isNaN(amount) || amount <= 0) {
        amountError.value = 'Payment amount must be greater than 0'
        return
    }

    if (amount > maxPaymentAmount.value) {
        amountError.value = `Payment amount cannot exceed balance due (${formatCurrency(maxPaymentAmount.value)})`
        return
    }

    amountError.value = ''
}

const formatCurrency = (amount: number) => {
    if (isNaN(amount) || !isFinite(amount)) return 'IDR 0'

    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(amount)
}

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    })
}

const submit = async () => {
    if (!isValid.value) {
        return
    }

    // Double-check invoice status before submitting
    if (!canReceivePayments.value) {
        alert('This invoice cannot receive payments. Only sent invoices can receive payments.')
        return
    }

    if (maxPaymentAmount.value <= 0) {
        alert('This invoice is already fully paid and cannot accept additional payments.')
        return
    }

    try {
        loading.value = true

        const paymentData = {
            invoice_id: props.invoice.id,
            payment_date: form.value.payment_date,
            payment_method: form.value.payment_method,
            amount: parseFloat(form.value.amount),
            reference_number: form.value.reference_number || null,
            notes: form.value.notes || null
        }

        console.log('Recording payment:', paymentData)

        const response = await apiService.recordPayment(paymentData)

        console.log('Payment response:', response)

        if (response.success) {
            // Emit success event
            emit('payment-recorded')
        } else {
            alert(`Error: ${response.message || 'Failed to record payment'}`)
        }
    } catch (error: any) {
        console.error('Error recording payment:', error)

        if (error.response?.data?.message) {
            alert(`Error: ${error.response.data.message}`)
        } else if (error.response?.data?.errors) {
            const errorMessages = Object.values(error.response.data.errors).flat()
            alert(`Validation errors:\n${errorMessages.join('\n')}`)
        } else {
            alert('Failed to record payment. Please try again.')
        }
    } finally {
        loading.value = false
    }
}

onMounted(() => {
    // Set default payment method
    if (!form.value.payment_method) {
        form.value.payment_method = 'bank_transfer'
    }
})
</script>
