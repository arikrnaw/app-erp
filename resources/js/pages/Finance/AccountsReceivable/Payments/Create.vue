<template>
    <AppLayout title="Create Payment">
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <Link :href="route('finance.accounts-receivable.payments.index')"
                        class="flex items-center text-sm text-gray-500 hover:text-gray-700">
                    <ArrowLeft class="w-4 h-4 mr-2" />
                    Back to Payments
                    </Link>
                    <h2 class="text-xl font-semibold">Create Payment</h2>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Basic Information -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <Label for="customer_id">Customer</Label>
                                    <Select v-model="form.customer_id" :disabled="loading"
                                        @update:model-value="onCustomerChange">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select customer" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="customer in customers" :key="customer.id"
                                                :value="customer.id.toString()">
                                                {{ customer.name }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <InputError :message="form.errors.customer_id" class="mt-2" />
                                </div>

                                <div>
                                    <Label for="invoice_id">Invoice (Optional)</Label>
                                    <Select v-model="form.invoice_id" :disabled="loading || !form.customer_id">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select invoice" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="">No Invoice</SelectItem>
                                            <SelectItem v-for="invoice in customerInvoices" :key="invoice.id"
                                                :value="invoice.id.toString()">
                                                {{ invoice.invoice_number }} - {{ formatCurrency(invoice.balance_amount)
                                                }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <InputError :message="form.errors.invoice_id" class="mt-2" />
                                </div>

                                <div>
                                    <Label for="payment_date">Payment Date</Label>
                                    <Input id="payment_date" v-model="form.payment_date" type="date"
                                        :disabled="loading" />
                                    <InputError :message="form.errors.payment_date" class="mt-2" />
                                </div>

                                <div>
                                    <Label for="payment_method">Payment Method</Label>
                                    <Select v-model="form.payment_method" :disabled="loading">
                                        <SelectTrigger>
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
                                    <InputError :message="form.errors.payment_method" class="mt-2" />
                                </div>

                                <div>
                                    <Label for="amount">Amount</Label>
                                    <Input id="amount" v-model="form.amount" type="number" step="0.01" min="0.01"
                                        placeholder="0.00" :disabled="loading" />
                                    <InputError :message="form.errors.amount" class="mt-2" />
                                </div>

                                <div>
                                    <Label for="reference_number">Reference Number</Label>
                                    <Input id="reference_number" v-model="form.reference_number"
                                        placeholder="Check number, transaction ID, etc." :disabled="loading" />
                                    <InputError :message="form.errors.reference_number" class="mt-2" />
                                </div>
                            </div>

                            <!-- Invoice Information (if selected) -->
                            <div v-if="selectedInvoice" class="bg-gray-50 rounded-lg p-4">
                                <h3 class="text-lg font-medium mb-4">Invoice Information</h3>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <Label class="text-sm font-medium text-gray-500">Invoice Number</Label>
                                        <div class="text-sm">{{ selectedInvoice.invoice_number }}</div>
                                    </div>
                                    <div>
                                        <Label class="text-sm font-medium text-gray-500">Total Amount</Label>
                                        <div class="text-sm">{{
                                            formatCurrency(selectedInvoice.total_amount) }}
                                        </div>
                                    </div>
                                    <div>
                                        <Label class="text-sm font-medium text-gray-500">Balance Amount</Label>
                                        <div class="text-sm">{{
                                            formatCurrency(selectedInvoice.balance_amount) }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Notes -->
                            <div>
                                <Label for="notes">Notes</Label>
                                <textarea id="notes" v-model="form.notes" rows="3"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    placeholder="Enter payment notes" :disabled="loading" />
                                <InputError :message="form.errors.notes" class="mt-2" />
                            </div>

                            <!-- Submit -->
                            <div class="flex justify-end space-x-4">
                                <Link :href="route('finance.accounts-receivable.payments.index')"
                                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Cancel
                                </Link>
                                <Button type="submit" :disabled="loading || form.processing">
                                    <Loader2 v-if="loading || form.processing" class="w-4 h-4 mr-2 animate-spin" />
                                    Create Payment
                                </Button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { ArrowLeft, Loader2 } from 'lucide-vue-next'
import InputError from '@/components/InputError.vue'
import { apiService } from '@/services/api'
import type { Customer, Invoice } from '@/types/erp'

const customers = ref<Customer[]>([])
const customerInvoices = ref<Invoice[]>([])
const loading = ref(false)

const form = useForm({
    customer_id: '',
    invoice_id: '',
    payment_date: new Date().toISOString().split('T')[0],
    payment_method: '',
    reference_number: '',
    amount: '',
    notes: ''
})

const selectedInvoice = computed(() => {
    if (!form.invoice_id) return null
    return customerInvoices.value.find(invoice => invoice.id.toString() === form.invoice_id)
})

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(amount)
}

const fetchCustomers = async () => {
    try {
        const response = await apiService.getCustomers({ page: 1 })
        customers.value = response.data
    } catch (error) {
        console.error('Error fetching customers:', error)
    }
}

const fetchCustomerInvoices = async (customerId: string) => {
    if (!customerId) {
        customerInvoices.value = []
        return
    }

    try {
        const response = await apiService.getInvoices({
            customer_id: customerId,
            status: 'sent,overdue',
            page: 1
        })
        customerInvoices.value = response.data
    } catch (error) {
        console.error('Error fetching customer invoices:', error)
    }
}

const onCustomerChange = (customerId: any) => {
    form.invoice_id = ''
    if (customerId) {
        fetchCustomerInvoices(customerId.toString())
    } else {
        customerInvoices.value = []
    }
}

const submit = () => {
    form.post(route('finance.accounts-receivable.payments.store'))
}

onMounted(() => {
    fetchCustomers()
})
</script>
