<template>
    <AppLayout title="Create Invoice">
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <Link :href="route('finance.accounts-receivable.invoices.index')"
                        class="flex items-center text-sm text-gray-500 hover:text-gray-700">
                    <ArrowLeft class="w-4 h-4 mr-2" />
                    Back to Invoices
                    </Link>
                    <h2 class="text-xl font-semibold">Create Invoice</h2>
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
                                    <Select v-model="form.customer_id" :disabled="loading">
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
                                    <Label for="invoice_date">Invoice Date</Label>
                                    <Input id="invoice_date" v-model="form.invoice_date" type="date"
                                        :disabled="loading" />
                                    <InputError :message="form.errors.invoice_date" class="mt-2" />
                                </div>

                                <div>
                                    <Label for="due_date">Due Date</Label>
                                    <Input id="due_date" v-model="form.due_date" type="date" :disabled="loading" />
                                    <InputError :message="form.errors.due_date" class="mt-2" />
                                </div>

                                <div>
                                    <Label for="reference_type">Reference Type</Label>
                                    <Select v-model="form.reference_type" :disabled="loading">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select reference type" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="sales_order">Sales Order</SelectItem>
                                            <SelectItem value="manual">Manual</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <InputError :message="form.errors.reference_type" class="mt-2" />
                                </div>

                                <div v-if="form.reference_type">
                                    <Label for="reference_id">Reference ID</Label>
                                    <Input id="reference_id" v-model="form.reference_id" type="number"
                                        placeholder="Enter reference ID" :disabled="loading" />
                                    <InputError :message="form.errors.reference_id" class="mt-2" />
                                </div>
                            </div>

                            <!-- Description -->
                            <div>
                                <Label for="description">Description</Label>
                                <textarea id="description" v-model="form.description" rows="3"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    placeholder="Enter invoice description" :disabled="loading" />
                                <InputError :message="form.errors.description" class="mt-2" />
                            </div>

                            <!-- Invoice Items -->
                            <div>
                                <div class="flex items-center justify-between mb-4">
                                    <Label>Invoice Items</Label>
                                    <Button type="button" @click="addItem" :disabled="loading" variant="outline"
                                        size="sm">
                                        <Plus class="w-4 h-4 mr-2" />
                                        Add Item
                                    </Button>
                                </div>

                                <div class="space-y-4">
                                    <div v-for="(item, index) in form.items" :key="index" class="border rounded-lg p-4">
                                        <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                                            <div class="md:col-span-2">
                                                <Label :for="`item_name_${index}`">Item Name</Label>
                                                <Input :id="`item_name_${index}`" v-model="item.item_name"
                                                    placeholder="Enter item name" :disabled="loading" />
                                            </div>

                                            <div>
                                                <Label :for="`product_id_${index}`">Product (Optional)</Label>
                                                <Select v-model="item.product_id" :disabled="loading">
                                                    <SelectTrigger>
                                                        <SelectValue placeholder="Select product" />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectItem value="">No Product</SelectItem>
                                                        <SelectItem v-for="product in products" :key="product.id"
                                                            :value="product.id.toString()">
                                                            {{ product.name }}
                                                        </SelectItem>
                                                    </SelectContent>
                                                </Select>
                                            </div>

                                            <div>
                                                <Label :for="`quantity_${index}`">Quantity</Label>
                                                <Input :id="`quantity_${index}`" v-model="item.quantity" type="number"
                                                    step="0.01" min="0.01" placeholder="0.00" :disabled="loading"
                                                    @input="calculateItemTotal(index)" />
                                            </div>

                                            <div>
                                                <Label :for="`unit_price_${index}`">Unit Price</Label>
                                                <Input :id="`unit_price_${index}`" v-model="item.unit_price"
                                                    type="number" step="0.01" min="0" placeholder="0.00"
                                                    :disabled="loading" @input="calculateItemTotal(index)" />
                                            </div>

                                            <div class="flex items-end space-x-2">
                                                <Button type="button" @click="removeItem(index)"
                                                    :disabled="loading || form.items.length <= 1" variant="outline"
                                                    size="sm">
                                                    <Trash2 class="w-4 h-4" />
                                                </Button>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">
                                            <div>
                                                <Label :for="`description_${index}`">Description</Label>
                                                <Input :id="`description_${index}`" v-model="item.description"
                                                    placeholder="Item description" :disabled="loading" />
                                            </div>

                                            <div>
                                                <Label :for="`tax_rate_${index}`">Tax Rate (%)</Label>
                                                <Input :id="`tax_rate_${index}`" v-model="item.tax_rate" type="number"
                                                    step="0.01" min="0" max="100" placeholder="0.00" :disabled="loading"
                                                    @input="calculateItemTotal(index)" />
                                            </div>

                                            <div>
                                                <Label :for="`discount_rate_${index}`">Discount Rate (%)</Label>
                                                <Input :id="`discount_rate_${index}`" v-model="item.discount_rate"
                                                    type="number" step="0.01" min="0" max="100" placeholder="0.00"
                                                    :disabled="loading" @input="calculateItemTotal(index)" />
                                            </div>

                                            <div>
                                                <Label>Total</Label>
                                                <div class="text-lg font-semibold">
                                                    {{ formatCurrency(item.total_amount || 0) }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <InputError :message="form.errors.items" class="mt-2" />
                            </div>

                            <!-- Totals -->
                            <div class="border-t pt-6">
                                <div class="flex justify-end">
                                    <div class="w-64 space-y-2">
                                        <div class="flex justify-between">
                                            <span class="text-white">Subtotal:</span>
                                            <span class="font-medium">{{ formatCurrency(subtotal) }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-white">Discount:</span>
                                            <span class="font-medium text-red-600">-{{ formatCurrency(discountAmount)
                                                }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-white">Tax:</span>
                                            <span class="font-medium">{{ formatCurrency(taxAmount) }}</span>
                                        </div>
                                        <div class="flex justify-between text-lg font-semibold border-t pt-2">
                                            <span>Total:</span>
                                            <span>{{ formatCurrency(totalAmount) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Notes -->
                            <div>
                                <Label for="notes">Notes</Label>
                                <textarea id="notes" v-model="form.notes" rows="3"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    placeholder="Enter additional notes" :disabled="loading" />
                                <InputError :message="form.errors.notes" class="mt-2" />
                            </div>

                            <!-- Submit -->
                            <div class="flex justify-end space-x-4">
                                <Link :href="route('finance.accounts-receivable.invoices.index')"
                                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Cancel
                                </Link>
                                <Button type="submit" :disabled="loading || form.processing">
                                    <Loader2 v-if="loading || form.processing" class="w-4 h-4 mr-2 animate-spin" />
                                    Create Invoice
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
import { ArrowLeft, Plus, Trash2, Loader2 } from 'lucide-vue-next'
import InputError from '@/components/InputError.vue'
import { apiService } from '@/services/api'
import type { Customer, Product } from '@/types/erp'

interface InvoiceItem {
    product_id: string
    item_name: string
    description: string
    quantity: number
    unit_price: number
    tax_rate: number
    discount_rate: number
    total_amount: number
}

const customers = ref<Customer[]>([])
const products = ref<Product[]>([])
const loading = ref(false)

const form = useForm({
    customer_id: '',
    invoice_date: new Date().toISOString().split('T')[0],
    due_date: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
    reference_type: '',
    reference_id: '',
    description: '',
    notes: '',
    items: [] as any[]
})

// Add initial item
const addItem = () => {
    form.items.push({
        product_id: '',
        item_name: '',
        description: '',
        quantity: 1,
        unit_price: 0,
        tax_rate: 0,
        discount_rate: 0,
        total_amount: 0
    })
}

const removeItem = (index: number) => {
    if (form.items.length > 1) {
        form.items.splice(index, 1)
    }
}

const calculateItemTotal = (index: number) => {
    const item = form.items[index]
    const lineTotal = (parseFloat(item.quantity.toString()) || 0) * (parseFloat(item.unit_price.toString()) || 0)
    const discount = lineTotal * (parseFloat(item.discount_rate.toString()) || 0) / 100
    const taxableAmount = lineTotal - discount
    const tax = taxableAmount * (parseFloat(item.tax_rate.toString()) || 0) / 100
    item.total_amount = lineTotal - discount + tax
}

const subtotal = computed(() => {
    return form.items.reduce((sum, item) => {
        const lineTotal = (parseFloat(item.quantity.toString()) || 0) * (parseFloat(item.unit_price.toString()) || 0)
        return sum + lineTotal
    }, 0)
})

const discountAmount = computed(() => {
    return form.items.reduce((sum, item) => {
        const lineTotal = (parseFloat(item.quantity.toString()) || 0) * (parseFloat(item.unit_price.toString()) || 0)
        const discount = lineTotal * (parseFloat(item.discount_rate.toString()) || 0) / 100
        return sum + discount
    }, 0)
})

const taxAmount = computed(() => {
    return form.items.reduce((sum, item) => {
        const lineTotal = (parseFloat(item.quantity.toString()) || 0) * (parseFloat(item.unit_price.toString()) || 0)
        const discount = lineTotal * (parseFloat(item.discount_rate.toString()) || 0) / 100
        const taxableAmount = lineTotal - discount
        const tax = taxableAmount * (parseFloat(item.tax_rate.toString()) || 0) / 100
        return sum + tax
    }, 0)
})

const totalAmount = computed(() => {
    return subtotal.value - discountAmount.value + taxAmount.value
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

const fetchProducts = async () => {
    try {
        const response = await apiService.getProducts({ page: 1 })
        products.value = response.data
    } catch (error) {
        console.error('Error fetching products:', error)
    }
}

const submit = () => {
    if (form.items.length === 0) {
        alert('At least one item is required')
        return
    }

    form.post(route('finance.accounts-receivable.invoices.store'))
}

onMounted(() => {
    fetchCustomers()
    fetchProducts()
    addItem()
})
</script>
