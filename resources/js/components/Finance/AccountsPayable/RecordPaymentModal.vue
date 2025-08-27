<template>
    <Dialog :open="open" @update:open="$emit('close')">
        <DialogContent class="!max-w-2xl">
            <DialogHeader>
                <DialogTitle>Record Payment</DialogTitle>
                <DialogDescription>
                    Record payment for bill #{{ bill?.bill_number }}
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="handleSubmit" class="space-y-6">
                <!-- Payment Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <Label for="payment_date">Payment Date</Label>
                        <Input v-model="form.payment_date" type="date" required />
                        <p v-if="errors.payment_date" class="text-sm text-destructive">{{ errors.payment_date }}</p>
                    </div>

                    <div class="space-y-2">
                        <Label for="payment_method">Payment Method</Label>
                        <Select v-model="form.payment_method" required>
                            <SelectTrigger>
                                <SelectValue placeholder="Select payment method" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="cash">Cash</SelectItem>
                                <SelectItem value="bank_transfer">Bank Transfer</SelectItem>
                                <SelectItem value="check">Check</SelectItem>
                                <SelectItem value="credit_card">Credit Card</SelectItem>
                                <SelectItem value="other">Other</SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="errors.payment_method" class="text-sm text-destructive">{{ errors.payment_method }}</p>
                    </div>

                    <div class="space-y-2">
                        <Label for="reference_number">Reference Number</Label>
                        <Input v-model="form.reference_number" placeholder="Check number, transaction ID, etc." />
                        <p v-if="errors.reference_number" class="text-sm text-destructive">{{ errors.reference_number }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="amount">Payment Amount</Label>
                        <Input v-model.number="form.amount" type="number" min="0.01" step="0.01" required />
                        <p v-if="errors.amount" class="text-sm text-destructive">{{ errors.amount }}</p>
                        <p class="text-sm text-muted-foreground">
                            Balance due: {{ formatCurrency(bill?.balance_amount || 0) }}
                        </p>
                    </div>
                </div>

                <div class="space-y-2">
                    <Label for="description">Description</Label>
                    <Textarea v-model="form.description" placeholder="Payment description or notes..." />
                    <p v-if="errors.description" class="text-sm text-destructive">{{ errors.description }}</p>
                </div>

                <!-- Bill Summary -->
                <div class="border-t pt-4">
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-sm text-muted-foreground">Bill Total:</span>
                            <span class="font-medium">{{ formatCurrency(bill?.total_amount || 0) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-muted-foreground">Already Paid:</span>
                            <span class="font-medium text-green-600">{{ formatCurrency(bill?.paid_amount || 0) }}</span>
                        </div>
                        <div class="flex justify-between border-t pt-2">
                            <span class="text-base font-semibold">Balance Due:</span>
                            <span class="text-lg font-bold text-red-600">{{ formatCurrency(bill?.balance_amount || 0)
                                }}</span>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <DialogFooter>
                    <Button type="button" variant="outline" @click="$emit('close')">
                        Cancel
                    </Button>
                    <Button type="submit" :disabled="loading">
                        <Loader2 v-if="loading" class="w-4 h-4 mr-2 animate-spin" />
                        {{ loading ? 'Recording...' : 'Record Payment' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Loader2 } from 'lucide-vue-next'
import { apiService } from '@/services/api'
import type { Bill } from '@/types/erp'

interface PaymentForm {
    bill_id: number
    payment_date: string
    payment_method: string
    reference_number: string
    amount: number
    description: string
}

interface Props {
    open: boolean
    bill: Bill | null
}

interface Emits {
    (e: 'close'): void
    (e: 'payment-recorded', payment: any): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const loading = ref(false)
const errors = ref<Record<string, string>>({})

const form = ref<PaymentForm>({
    bill_id: 0,
    payment_date: new Date().toISOString().split('T')[0],
    payment_method: '',
    reference_number: '',
    amount: 0,
    description: ''
})

// Watch for bill changes to update form
watch(() => props.bill, (newBill) => {
    if (newBill) {
        form.value.bill_id = newBill.id
        form.value.amount = newBill.balance_amount || 0
    }
}, { immediate: true })

const handleSubmit = async () => {
    loading.value = true
    errors.value = {}

    try {
        const response = await apiService.recordBillPayment(form.value)

        if (response.success) {
            emit('payment-recorded', response.data)
            emit('close')
            resetForm()
        } else {
            if (response.errors) {
                errors.value = response.errors
            }
        }
    } catch (error: any) {
        console.error('Error recording payment:', error)
        if (error.response?.data?.errors) {
            errors.value = error.response.data.errors
        } else {
            errors.value = { general: 'An error occurred while recording the payment' }
        }
    } finally {
        loading.value = false
    }
}

const resetForm = () => {
    form.value = {
        bill_id: 0,
        payment_date: new Date().toISOString().split('T')[0],
        payment_method: '',
        reference_number: '',
        amount: 0,
        description: ''
    }
    errors.value = {}
}

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(amount)
}
</script>
