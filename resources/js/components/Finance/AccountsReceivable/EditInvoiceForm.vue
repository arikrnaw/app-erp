<template>
    <div class="space-y-6">
        <form @submit.prevent="submit" class="space-y-6">
            <!-- Basic Information -->
            <div class="space-y-4">
                <h3 class="text-lg font-medium text-card-foreground">Edit Invoice</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="space-y-3">
                        <Label for="customer_id" class="text-sm font-medium">Customer *</Label>
                        <Select v-model="form.customer_id" :disabled="loading">
                            <SelectTrigger class="h-10 w-full">
                                <SelectValue placeholder="Select customer" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="customer in customers" :key="customer.id"
                                    :value="customer.id.toString()">
                                    {{ customer.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="space-y-3">
                        <Label for="invoice_date" class="text-sm font-medium">Invoice Date *</Label>
                        <Input id="invoice_date" v-model="form.invoice_date" type="date" required class="h-10"
                            :disabled="loading" />
                    </div>

                    <div class="space-y-3">
                        <Label for="due_date" class="text-sm font-medium">Due Date *</Label>
                        <Input id="due_date" v-model="form.due_date" type="date" required class="h-10"
                            :disabled="loading" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-3">
                        <Label for="reference_type" class="text-sm font-medium">Reference Type</Label>
                        <Select v-model="form.reference_type" :disabled="loading">
                            <SelectTrigger class="h-10 w-full">
                                <SelectValue placeholder="Select reference type" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="sales_order">Sales Order</SelectItem>
                                <SelectItem value="manual">Manual</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div v-if="form.reference_type" class="space-y-3">
                        <Label for="reference_id" class="text-sm font-medium">Reference ID</Label>
                        <Input id="reference_id" v-model="form.reference_id" type="number"
                            placeholder="Enter reference ID" class="h-10" :disabled="loading" />
                    </div>
                </div>
            </div>

            <!-- Description -->
            <div class="space-y-3">
                <Label for="description" class="text-sm font-medium">Description</Label>
                <Textarea id="description" v-model="form.description" rows="3" placeholder="Enter invoice description"
                    class="h-20" :disabled="loading" />
                <p class="text-sm text-muted-foreground">
                    Brief description of the invoice
                </p>
            </div>

            <!-- Invoice Items -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-card-foreground">Invoice Items</h3>
                    <Button type="button" @click="addItem" :disabled="loading" variant="outline" size="sm" class="h-9">
                        <Plus class="w-4 h-4 mr-2" />
                        Add Item
                    </Button>
                </div>

                <div class="space-y-4">
                    <div v-for="(item, index) in form.items" :key="`item-${index}`"
                        class="grid grid-cols-1 md:grid-cols-4 gap-4 p-4 border border-border rounded-lg bg-card">
                        <div class="space-y-3">
                            <Label :for="`item_name_${index}`" class="text-sm font-medium">Item Name *</Label>
                            <Input :id="`item_name_${index}`" v-model="item.item_name" placeholder="Enter item name"
                                class="h-10" :disabled="loading" />
                        </div>

                        <div class="space-y-3">
                            <Label :for="`product_id_${index}`" class="text-sm font-medium">Product (Optional)</Label>
                            <Select v-model="item.product_id" :disabled="loading">
                                <SelectTrigger class="h-10 w-full">
                                    <SelectValue placeholder="Select product" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="no_product">No Product</SelectItem>
                                    <SelectItem v-for="product in products" :key="product.id"
                                        :value="product.id.toString()">
                                        {{ product.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="space-y-3">
                            <Label :for="`quantity_${index}`" class="text-sm font-medium">Quantity *</Label>
                            <Input :id="`quantity_${index}`" v-model="item.quantity" type="number" step="0.01"
                                min="0.01" placeholder="0.00" class="h-10" :disabled="loading"
                                @input="calculateItemTotal(index)" />
                        </div>

                        <div class="flex space-x-3">
                            <div class="flex-1 space-y-3">
                                <Label :for="`unit_price_${index}`" class="text-sm font-medium">Unit Price *</Label>
                                <Input :id="`unit_price_${index}`" v-model="item.unit_price" type="number" step="0.01"
                                    min="0" placeholder="0.00" class="h-10" :disabled="loading"
                                    @input="calculateItemTotal(index)" />
                            </div>
                            <div class="flex items-center mt-6">
                                <Button type="button" @click="removeItem(index)"
                                    :disabled="loading || form.items.length <= 1" variant="destructive" size="sm"
                                    class="h-10 w-10 p-0">
                                    <Trash2 class="w-4 h-4" />
                                </Button>
                            </div>
                        </div>

                        <!-- Additional Item Details -->
                        <div class="md:col-span-4 grid grid-cols-1 md:grid-cols-3 gap-4 pt-4 border-t border-border">
                            <div class="space-y-3">
                                <Label :for="`description_${index}`" class="text-sm font-medium">Description</Label>
                                <Input :id="`description_${index}`" v-model="item.description"
                                    placeholder="Item description" class="h-10" :disabled="loading" />
                            </div>

                            <div class="space-y-3">
                                <Label :for="`tax_rate_${index}`" class="text-sm font-medium">Tax Rate (%)</Label>
                                <Input :id="`tax_rate_${index}`" v-model="item.tax_rate" type="number" step="0.01"
                                    min="0" max="100" placeholder="0.00" class="h-10" :disabled="loading"
                                    @input="calculateItemTotal(index)" />
                            </div>

                            <div class="space-y-3">
                                <Label :for="`discount_rate_${index}`" class="text-sm font-medium">Discount Rate
                                    (%)</Label>
                                <Input :id="`discount_rate_${index}`" v-model="item.discount_rate" type="number"
                                    step="0.01" min="0" max="100" placeholder="0.00" class="h-10" :disabled="loading"
                                    @input="calculateItemTotal(index)" />
                            </div>
                        </div>

                        <!-- Item Total -->
                        <div class="md:col-span-4 flex justify-end">
                            <div class="text-lg font-semibold text-primary bg-primary/10 px-4 py-2 rounded-md">
                                Total: {{ formatCurrency(item.total_amount || 0) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Totals -->
            <div class="flex justify-end">
                <div
                    class="flex justify-end space-x-4 text-lg font-semibold p-4 bg-muted/30 rounded-lg border border-border">
                    <div class="text-card-foreground">Subtotal: {{ formatCurrency(subtotal) }}</div>
                    <div class="text-card-foreground">Discount: -{{ formatCurrency(discountAmount) }}</div>
                    <div class="text-card-foreground">Tax: {{ formatCurrency(taxAmount) }}</div>
                    <div class="text-primary">Total: {{ formatCurrency(totalAmount) }}</div>
                </div>
            </div>

            <!-- Notes -->
            <div class="space-y-3">
                <Label for="notes" class="text-sm font-medium">Notes</Label>
                <Textarea id="notes" v-model="form.notes" rows="3" placeholder="Enter additional notes" class="h-20"
                    :disabled="loading" />
                <p class="text-sm text-muted-foreground">
                    Additional notes or comments for this invoice
                </p>
            </div>

            <!-- Submit Button -->
            <div class="flex items-center justify-end space-x-4 pt-6 border-t border-border">
                <Button type="button" variant="outline" @click="$emit('cancel')" class="h-10 px-4">
                    Cancel
                </Button>
                <Button type="submit" :disabled="loading" class="h-10 px-6">
                    <Loader2 v-if="loading" class="w-4 h-4 mr-2 animate-spin" />
                    Update Invoice
                </Button>
            </div>
        </form>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, nextTick } from 'vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Textarea } from '@/components/ui/textarea'
import { Plus, Trash2, Loader2 } from 'lucide-vue-next'
import { apiService } from '@/services/api'
import type { Customer, Product, Invoice } from '@/types/erp'

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

interface Props {
    invoice: Invoice
}

interface Emits {
    (e: 'invoice-updated'): void
    (e: 'cancel'): void
}

const emit = defineEmits<Emits>()
const props = defineProps<Props>()

const customers = ref<Customer[]>([])
const products = ref<Product[]>([])
const loading = ref(false)

const form = ref({
    customer_id: props.invoice.customer_id.toString(),
    invoice_date: props.invoice.invoice_date,
    due_date: props.invoice.due_date,
    reference_type: '',
    reference_id: '',
    description: props.invoice.description || 'No description',
    notes: props.invoice.notes || 'No notes',
    items: [] as InvoiceItem[]
})

// Initialize form with existing invoice data
const initializeForm = () => {
    if (props.invoice.items && props.invoice.items.length > 0) {
        form.value.items = props.invoice.items.map(item => ({
            product_id: item.product_id?.toString() || '',
            item_name: item.item_name || '',
            description: item.description || '',
            quantity: item.quantity,
            unit_price: item.unit_price,
            tax_rate: item.tax_percentage || 0,
            discount_rate: item.discount_percentage || 0,
            total_amount: item.total_amount
        }))
    } else {
        addItem()
    }
}

// Add initial item
const addItem = async () => {
    const newItem: InvoiceItem = {
        product_id: '',
        item_name: '',
        description: '',
        quantity: 1,
        unit_price: 0,
        tax_rate: 0,
        discount_rate: 0,
        total_amount: 0
    }

    form.value.items.push(newItem)

    // Wait for DOM update before calculating
    await nextTick()
    calculateItemTotal(form.value.items.length - 1)
}

const removeItem = async (index: number) => {
    if (form.value.items.length > 1) {
        form.value.items.splice(index, 1)
        // Recalculate totals after removal
        await nextTick()
        recalculateAllTotals()
    }
}

const calculateItemTotal = (index: number) => {
    if (index >= 0 && index < form.value.items.length) {
        const item = form.value.items[index]
        const quantity = parseFloat(item.quantity?.toString() || '0')
        const unitPrice = parseFloat(item.unit_price?.toString() || '0')
        const taxRate = parseFloat(item.tax_rate?.toString() || '0')
        const discountRate = parseFloat(item.discount_rate?.toString() || '0')

        const lineTotal = quantity * unitPrice
        const discount = lineTotal * discountRate / 100
        const taxableAmount = lineTotal - discount
        const tax = taxableAmount * taxRate / 100

        item.total_amount = lineTotal - discount + tax
    }
}

const recalculateAllTotals = () => {
    form.value.items.forEach((_, index) => {
        calculateItemTotal(index)
    })
}

const subtotal = computed(() => {
    return form.value.items.reduce((sum, item) => {
        const quantity = parseFloat(item.quantity?.toString() || '0')
        const unitPrice = parseFloat(item.unit_price?.toString() || '0')
        return sum + (quantity * unitPrice)
    }, 0)
})

const discountAmount = computed(() => {
    return form.value.items.reduce((sum, item) => {
        const quantity = parseFloat(item.quantity?.toString() || '0')
        const unitPrice = parseFloat(item.unit_price?.toString() || '0')
        const discountRate = parseFloat(item.discount_rate?.toString() || '0')
        const lineTotal = quantity * unitPrice
        const discount = lineTotal * discountRate / 100
        return sum + discount
    }, 0)
})

const taxAmount = computed(() => {
    return form.value.items.reduce((sum, item) => {
        const quantity = parseFloat(item.quantity?.toString() || '0')
        const unitPrice = parseFloat(item.unit_price?.toString() || '0')
        const discountRate = parseFloat(item.discount_rate?.toString() || '0')
        const taxRate = parseFloat(item.tax_rate?.toString() || '0')

        const lineTotal = quantity * unitPrice
        const discount = lineTotal * discountRate / 100
        const taxableAmount = lineTotal - discount
        const tax = taxableAmount * taxRate / 100

        return sum + tax
    }, 0)
})

const totalAmount = computed(() => {
    return subtotal.value - discountAmount.value + taxAmount.value
})

const formatCurrency = (amount: number) => {
    if (isNaN(amount) || !isFinite(amount)) return 'IDR 0'

    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(amount)
}

const fetchCustomers = async () => {
    try {
        loading.value = true
        const response = await apiService.getCustomers({ page: 1 })
        if (response.data) {
            customers.value = response.data
        }
    } catch (error) {
        console.error('Error fetching customers:', error)
    } finally {
        loading.value = false
    }
}

const fetchProducts = async () => {
    try {
        loading.value = true
        const response = await apiService.getProducts({ page: 1 })
        if (response.data) {
            products.value = response.data
        }
    } catch (error) {
        console.error('Error fetching products:', error)
    } finally {
        loading.value = false
    }
}

const submit = async () => {
    if (form.value.items.length === 0) {
        alert('At least one item is required')
        return
    }

    // Validate required fields
    if (!form.value.customer_id) {
        alert('Please select a customer')
        return
    }

    if (!form.value.invoice_date) {
        alert('Please select invoice date')
        return
    }

    if (!form.value.due_date) {
        alert('Please select due date')
        return
    }

    // Validate items
    for (let i = 0; i < form.value.items.length; i++) {
        const item = form.value.items[i]
        if (!item.item_name || !item.quantity || !item.unit_price) {
            alert(`Please fill in all required fields for item ${i + 1}`)
            return
        }
    }

    try {
        loading.value = true

        // Prepare data for API
        const invoiceData = {
            customer_id: parseInt(form.value.customer_id),
            invoice_date: form.value.invoice_date,
            due_date: form.value.due_date,
            reference_type: form.value.reference_type || null,
            reference_id: form.value.reference_id ? parseInt(form.value.reference_id) : null,
            description: form.value.description,
            notes: form.value.notes,
            items: form.value.items.map(item => ({
                product_id: item.product_id && item.product_id !== 'no_product' ? parseInt(item.product_id) : null,
                item_name: item.item_name,
                description: item.description,
                quantity: parseFloat(item.quantity?.toString() || '0'),
                unit_price: parseFloat(item.unit_price?.toString() || '0'),
                tax_rate: parseFloat(item.tax_rate?.toString() || '0'),
                discount_rate: parseFloat(item.discount_rate?.toString() || '0'),
                total_amount: parseFloat(item.total_amount?.toString() || '0')
            }))
        }

        console.log('Updating invoice data:', invoiceData)

        // Call API to update invoice
        const response = await apiService.updateInvoice(props.invoice.id, invoiceData)

        console.log('API response:', response)

        if (response.success) {
            // Emit success event
            emit('invoice-updated')
        } else {
            alert(`Error: ${response.message || 'Failed to update invoice'}`)
        }
    } catch (error: any) {
        console.error('Error updating invoice:', error)

        if (error.response?.data?.message) {
            alert(`Error: ${error.response.data.message}`)
        } else if (error.response?.data?.errors) {
            // Handle validation errors
            const errorMessages = Object.values(error.response.data.errors).flat()
            alert(`Validation errors:\n${errorMessages.join('\n')}`)
        } else {
            alert('Failed to update invoice. Please try again.')
        }
    } finally {
        loading.value = false
    }
}

onMounted(async () => {
    try {
        await Promise.all([
            fetchCustomers(),
            fetchProducts()
        ])
        initializeForm()
    } catch (error) {
        console.error('Error during component mount:', error)
    }
})
</script>
