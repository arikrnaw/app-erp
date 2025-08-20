<template>

    <Head title="Edit Purchase Order" />

    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl leading-tight">
                Edit Purchase Order
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Order Header -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <!-- Supplier -->
                                <div>
                                    <Label for="supplier_id">Supplier</Label>
                                    <select id="supplier_id" v-model="form.supplier_id"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        required>
                                        <option value="">Select Supplier</option>
                                        <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">
                                            {{ supplier.name }} ({{ supplier.code }})
                                        </option>
                                    </select>
                                    <InputError :message="form.errors.supplier_id" class="mt-2" />
                                </div>

                                <!-- Order Date -->
                                <div>
                                    <Label for="order_date">Order Date</Label>
                                    <Input id="order_date" type="date" class="mt-1" v-model="form.order_date"
                                        required />
                                    <InputError :message="form.errors.order_date" class="mt-2" />
                                </div>

                                <!-- Status -->
                                <div>
                                    <Label for="status">Status</Label>
                                    <select id="status" v-model="form.status"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        required>
                                        <option value="draft">Draft</option>
                                        <option value="confirmed">Confirmed</option>
                                        <option value="received">Received</option>
                                        <option value="cancelled">Cancelled</option>
                                    </select>
                                    <InputError :message="form.errors.status" class="mt-2" />
                                </div>
                            </div>

                            <!-- Order Items -->
                            <div>
                                <div class="flex justify-between items-center mb-4">
                                    <Label class="text-lg font-medium">Order Items</Label>
                                    <Button type="button" variant="outline" @click="addItem">
                                        <Plus class="w-4 h-4 mr-2" />
                                        Add Item
                                    </Button>
                                </div>

                                <div class="space-y-4">
                                    <div v-for="(item, index) in form.items" :key="index" class="border rounded-lg p-4">
                                        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                                            <!-- Product -->
                                            <div>
                                                <Label :for="`product_id_${index}`">Product</Label>
                                                <select :id="`product_id_${index}`" v-model="item.product_id"
                                                    @change="updateProductInfo(index)"
                                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                                    required>
                                                    <option value="">Select Product</option>
                                                    <option v-for="product in products" :key="product.id"
                                                        :value="product.id">
                                                        {{ product.name }} ({{ product.sku }})
                                                    </option>
                                                </select>
                                            </div>

                                            <!-- Quantity -->
                                            <div>
                                                <Label :for="`quantity_${index}`">Quantity</Label>
                                                <Input :id="`quantity_${index}`" type="number" min="1" class="mt-1"
                                                    v-model="item.quantity" @input="calculateItemTotal(index)"
                                                    required />
                                            </div>

                                            <!-- Unit Price -->
                                            <div>
                                                <Label :for="`unit_price_${index}`">Unit Price</Label>
                                                <Input :id="`unit_price_${index}`" type="number" step="0.01"
                                                    class="mt-1" v-model="item.unit_price"
                                                    @input="calculateItemTotal(index)" required />
                                            </div>

                                            <!-- Total Price -->
                                            <div>
                                                <Label :for="`total_price_${index}`">Total Price</Label>
                                                <Input :id="`total_price_${index}`" type="number" step="0.01"
                                                    class="mt-1" v-model="item.total_price" readonly />
                                            </div>

                                            <!-- Remove Button -->
                                            <div class="flex items-end">
                                                <Button type="button" variant="outline" @click="removeItem(index)"
                                                    class="text-red-600 hover:text-red-700">
                                                    <Trash2 class="w-4 h-4" />
                                                </Button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Order Summary -->
                            <div class="border-t pt-6">
                                <div class="flex justify-end">
                                    <div class="w-64 space-y-2">
                                        <div class="flex justify-between">
                                            <span class="font-medium">Subtotal:</span>
                                            <span>{{ formatCurrency(calculateSubtotal()) }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="font-medium">Total Amount:</span>
                                            <span class="text-lg font-bold">{{ formatCurrency(calculateTotal())
                                                }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Notes -->
                            <div>
                                <Label for="notes">Notes</Label>
                                <textarea id="notes" v-model="form.notes" rows="3"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    placeholder="Enter any additional notes..."></textarea>
                                <InputError :message="form.errors.notes" class="mt-2" />
                            </div>

                            <div class="flex items-center justify-end mt-4">
                                <Link :href="route('purchase-orders.index')">
                                <Button variant="outline" type="button" class="mr-2">
                                    Cancel
                                </Button>
                                </Link>
                                <Button :class="{ 'opacity-25': form.processing }" :disabled="form.processing"
                                    type="submit">
                                    Update Purchase Order
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
import { ref, onMounted } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import InputError from '@/components/InputError.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Plus, Trash2 } from 'lucide-vue-next'
import { apiService } from '@/services/api'
import type { Supplier, Product, PurchaseOrder } from '@/types/erp'

interface OrderItem {
    product_id: number | string
    quantity: number | string
    unit_price: number | string
    total_price: number | string
}

interface Props {
    purchaseOrder: PurchaseOrder
}

const props = defineProps<Props>()

const suppliers = ref<Supplier[]>([])
const products = ref<Product[]>([])

const form = useForm({
    supplier_id: props.purchaseOrder.supplier_id,
    order_date: props.purchaseOrder.order_date.split('T')[0],
    status: props.purchaseOrder.status,
    notes: props.purchaseOrder.notes || '',
    items: props.purchaseOrder.items?.map(item => ({
        product_id: item.product_id,
        quantity: item.quantity,
        unit_price: item.unit_price,
        total_price: item.total_price
    })) || [] as any[]
})

const addItem = () => {
    form.items.push({
        product_id: '',
        quantity: 1,
        unit_price: 0,
        total_price: 0
    })
}

const removeItem = (index: number) => {
    form.items.splice(index, 1)
    calculateTotal()
}

const updateProductInfo = (index: number) => {
    const item = form.items[index]
    const product = products.value.find(p => p.id === Number(item.product_id))
    if (product) {
        item.unit_price = product.cost_price
        calculateItemTotal(index)
    }
}

const calculateItemTotal = (index: number) => {
    const item = form.items[index]
    const quantity = Number(item.quantity) || 0
    const unitPrice = Number(item.unit_price) || 0
    item.total_price = (quantity * unitPrice).toFixed(2)
    calculateTotal()
}

const calculateSubtotal = (): number => {
    return form.items.reduce((total, item) => {
        return total + (Number(item.total_price) || 0)
    }, 0)
}

const calculateTotal = (): number => {
    return calculateSubtotal()
}

const formatCurrency = (amount: number): string => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(amount)
}

const fetchSuppliers = async () => {
    try {
        const response = await apiService.getSuppliers({ page: 1 })
        suppliers.value = response.data
    } catch (error) {
        console.error('Error fetching suppliers:', error)
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

const submit = (): void => {
    form.put(route('purchase-orders.update', props.purchaseOrder.id))
}

onMounted(() => {
    fetchSuppliers()
    fetchProducts()
    if (form.items.length === 0) {
        addItem()
    }
})
</script>
