<template>

    <Head title="Create Goods Receipt" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl leading-tight">
                    Create Goods Receipt
                </h2>
                <Link :href="route('purchasing.goods-receipts.index')">
                <Button variant="outline">
                    <ArrowLeft class="w-4 h-4 mr-2" />
                    Back to List
                </Button>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <form @submit.prevent="submitForm" class="space-y-6">
                            <!-- Header Information -->
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                <div>
                                    <Label for="receipt_number">Receipt Number</Label>
                                    <Input id="receipt_number" v-model="form.receipt_number" readonly />
                                </div>
                                <div>
                                    <Label for="receipt_date">Receipt Date</Label>
                                    <Input id="receipt_date" type="date" v-model="form.receipt_date" required />
                                </div>
                                <div>
                                    <Label for="purchase_order_id">Purchase Order</Label>
                                    <select id="purchase_order_id" v-model="form.purchase_order_id"
                                        @change="onPurchaseOrderChange"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                        <option value="">Select Purchase Order</option>
                                        <option v-for="po in purchaseOrders" :key="po?.id" :value="po?.id">
                                            {{ po?.po_number || 'N/A' }} - {{ po?.supplier?.name || 'N/A' }}
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <Label for="supplier_id">Supplier</Label>
                                    <Input id="supplier_id" v-model="selectedSupplier" readonly />
                                </div>
                                <div>
                                    <Label for="warehouse_id">Warehouse</Label>
                                    <select id="warehouse_id" v-model="form.warehouse_id"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                        <option value="">Select Warehouse</option>
                                        <option v-for="warehouse in warehouses" :key="warehouse?.id"
                                            :value="warehouse?.id">
                                            {{ warehouse?.name || 'N/A' }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <!-- Delivery Information -->
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                <div>
                                    <Label for="delivery_note_number">Delivery Note Number</Label>
                                    <Input id="delivery_note_number" v-model="form.delivery_note_number" />
                                </div>
                                <div>
                                    <Label for="vehicle_number">Vehicle Number</Label>
                                    <Input id="vehicle_number" v-model="form.vehicle_number" />
                                </div>
                                <div>
                                    <Label for="driver_name">Driver Name</Label>
                                    <Input id="driver_name" v-model="form.driver_name" />
                                </div>
                            </div>

                            <!-- Notes -->
                            <div>
                                <Label for="notes">Notes</Label>
                                <Textarea id="notes" v-model="form.notes" rows="3" />
                            </div>

                            <!-- Items Section -->
                            <div class="space-y-4">
                                <div class="flex justify-between items-center">
                                    <h3 class="text-lg font-medium">Receipt Items</h3>
                                    <Button type="button" @click="addItem" variant="outline"
                                        :disabled="!form.purchase_order_id">
                                        <Plus class="w-4 h-4 mr-2" />
                                        Add Item
                                    </Button>
                                </div>

                                <div v-if="!form.purchase_order_id" class="text-center py-8 text-gray-500">
                                    Please select a Purchase Order first to add items.
                                </div>

                                <div v-else-if="form.items.length === 0" class="text-center py-8 text-gray-500">
                                    No items added yet. Click "Add Item" to start.
                                </div>

                                <div v-else class="space-y-4">
                                    <div v-for="(item, index) in form.items" :key="index" class="border rounded-lg p-4">
                                        <div class="flex justify-between items-start mb-4">
                                            <h4 class="font-medium">Item #{{ index + 1 }}</h4>
                                            <Button type="button" @click="removeItem(index)" variant="ghost" size="sm"
                                                class="text-red-600">
                                                <Trash2 class="w-4 h-4" />
                                            </Button>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                            <div>
                                                <Label :for="`purchase_order_item_id_${index}`">PO Item</Label>
                                                <select :id="`purchase_order_item_id_${index}`"
                                                    v-model="item.purchase_order_item_id" @change="onItemChange(index)"
                                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                                    <option value="">Select PO Item</option>
                                                    <option v-for="poItem in availablePOItems" :key="poItem?.id"
                                                        :value="poItem?.id">
                                                        {{ poItem?.product?.name || poItem?.item_description || 'N/A' }}
                                                    </option>
                                                </select>
                                            </div>
                                            <div>
                                                <Label :for="`product_id_${index}`">Product</Label>
                                                <Input :id="`product_id_${index}`" v-model="item.product_name"
                                                    readonly />
                                            </div>
                                            <div>
                                                <Label :for="`ordered_quantity_${index}`">Ordered Quantity</Label>
                                                <Input :id="`ordered_quantity_${index}`" v-model="item.ordered_quantity"
                                                    readonly />
                                            </div>
                                            <div>
                                                <Label :for="`received_quantity_${index}`">Received Quantity</Label>
                                                <Input :id="`received_quantity_${index}`" type="number"
                                                    v-model="item.received_quantity" min="1"
                                                    @input="calculateItemTotal(index)" required />
                                            </div>
                                            <div>
                                                <Label :for="`unit_price_${index}`">Unit Price</Label>
                                                <Input :id="`unit_price_${index}`" type="number" step="0.01"
                                                    v-model="item.unit_price" @input="calculateItemTotal(index)"
                                                    min="0" />
                                            </div>
                                            <div>
                                                <Label :for="`total_price_${index}`">Total Price</Label>
                                                <Input :id="`total_price_${index}`" type="number" step="0.01"
                                                    v-model="item.total_price" readonly />
                                            </div>
                                        </div>

                                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <Label :for="`lot_number_${index}`">Lot Number</Label>
                                                <Input :id="`lot_number_${index}`" v-model="item.lot_number" />
                                            </div>
                                            <div>
                                                <Label :for="`expiry_date_${index}`">Expiry Date</Label>
                                                <Input :id="`expiry_date_${index}`" type="date"
                                                    v-model="item.expiry_date" />
                                            </div>
                                        </div>

                                        <div class="mt-4">
                                            <Label :for="`notes_${index}`">Notes</Label>
                                            <Textarea :id="`notes_${index}`" v-model="item.notes" rows="2" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Summary -->
                            <div class="border-t pt-6">
                                <div class="flex justify-between items-center">
                                    <div class="text-lg font-medium">
                                        Total Amount: {{ formatCurrency(totalAmount) }}
                                    </div>
                                    <div class="space-x-2">
                                        <Button type="button" @click="saveDraft" variant="outline" :disabled="loading">
                                            <Save class="w-4 h-4 mr-2" />
                                            Save Draft
                                        </Button>
                                        <Button type="submit" :disabled="loading">
                                            <Send class="w-4 h-4 mr-2" />
                                            {{ loading ? 'Creating...' : 'Create Receipt' }}
                                        </Button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { ArrowLeft, Plus, Trash2, Save, Send } from 'lucide-vue-next'
import { apiService } from '@/services/api'
import type { PurchaseOrder, PurchaseOrderItem, Warehouse } from '@/types/erp'

interface FormItem {
    purchase_order_item_id?: number
    product_id?: number
    product_name: string
    ordered_quantity: number
    received_quantity: number
    unit_price: number
    total_price: number
    lot_number?: string
    expiry_date?: string
    notes?: string
}

const loading = ref(false)
const purchaseOrders = ref<PurchaseOrder[]>([])
const warehouses = ref<Warehouse[]>([])
const selectedPurchaseOrder = ref<PurchaseOrder | null>(null)
const availablePOItems = ref<PurchaseOrderItem[]>([])

const form = ref({
    receipt_number: '',
    purchase_order_id: '',
    supplier_id: '',
    warehouse_id: '',
    receipt_date: new Date().toISOString().split('T')[0],
    delivery_note_number: '',
    vehicle_number: '',
    driver_name: '',
    notes: '',
    items: [] as FormItem[]
})

const selectedSupplier = computed(() => {
    return selectedPurchaseOrder.value?.supplier?.name || ''
})

const totalAmount = computed(() => {
    return form.value.items.reduce((total, item) => total + (item.total_price || 0), 0)
})

const addItem = () => {
    form.value.items.push({
        purchase_order_item_id: undefined,
        product_id: undefined,
        product_name: '',
        ordered_quantity: 0,
        received_quantity: 1,
        unit_price: 0,
        total_price: 0,
        lot_number: '',
        expiry_date: '',
        notes: ''
    })
}

const removeItem = (index: number) => {
    form.value.items.splice(index, 1)
}

const calculateItemTotal = (index: number) => {
    const item = form.value.items[index]
    if (item) {
        item.total_price = (item.received_quantity || 0) * (item.unit_price || 0)
    }
}

const onPurchaseOrderChange = async () => {
    if (!form.value.purchase_order_id) {
        selectedPurchaseOrder.value = null
        availablePOItems.value = []
        form.value.items = []
        return
    }

    try {
        const response = await apiService.getPurchaseOrder(parseInt(form.value.purchase_order_id))
        selectedPurchaseOrder.value = response
        availablePOItems.value = response.items || []
        form.value.supplier_id = response.supplier_id?.toString() || ''
    } catch (error) {
        console.error('Error fetching purchase order:', error)
    }
}

const onItemChange = (index: number) => {
    const item = form.value.items[index]
    if (!item.purchase_order_item_id) return

    const poItem = availablePOItems.value.find(poi => poi.id === item.purchase_order_item_id)
    if (poItem) {
        item.product_id = poItem.product_id
        item.product_name = poItem.product?.name || poItem.item_description || ''
        item.ordered_quantity = poItem.quantity || 0
        item.unit_price = poItem.unit_price || 0
        item.received_quantity = poItem.quantity || 1
        calculateItemTotal(index)
    }
}

const generateReceiptNumber = async () => {
    try {
        const response = await apiService.generateGoodsReceiptNumber()
        form.value.receipt_number = response.receipt_number || ''
    } catch (error) {
        console.error('Error generating receipt number:', error)
        form.value.receipt_number = 'GR' + new Date().getFullYear() + String(new Date().getMonth() + 1).padStart(2, '0') + String(new Date().getDate()).padStart(2, '0') + String(Math.floor(Math.random() * 1000)).padStart(3, '0')
    }
}

const fetchPurchaseOrders = async () => {
    try {
        const response = await apiService.getPurchaseOrders({ page: 1, per_page: 100, status: 'approved' })
        purchaseOrders.value = response.data || []
    } catch (error) {
        console.error('Error fetching purchase orders:', error)
        purchaseOrders.value = []
    }
}

const fetchWarehouses = async () => {
    try {
        const response = await apiService.getWarehouses({ page: 1, per_page: 100 })
        warehouses.value = response.data || []
    } catch (error) {
        console.error('Error fetching warehouses:', error)
        warehouses.value = []
    }
}

const submitForm = async () => {
    if (form.value.items.length === 0) {
        alert('Please add at least one item to the receipt.')
        return
    }

    loading.value = true
    try {
        const receiptData = {
            ...form.value,
            status: 'received',
            total_amount: totalAmount.value
        }

        await apiService.createGoodsReceipt(receiptData)
        router.visit(route('purchasing.goods-receipts.index'))
    } catch (error) {
        console.error('Error creating goods receipt:', error)
        alert('Error creating goods receipt. Please try again.')
    } finally {
        loading.value = false
    }
}

const saveDraft = async () => {
    loading.value = true
    try {
        const receiptData = {
            ...form.value,
            status: 'draft',
            total_amount: totalAmount.value
        }

        await apiService.createGoodsReceipt(receiptData)
        router.visit(route('purchasing.goods-receipts.index'))
    } catch (error) {
        console.error('Error saving draft:', error)
        alert('Error saving draft. Please try again.')
    } finally {
        loading.value = false
    }
}

const formatCurrency = (amount: number): string => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(amount)
}

onMounted(async () => {
    await Promise.all([
        generateReceiptNumber(),
        fetchPurchaseOrders(),
        fetchWarehouses()
    ])
})
</script>
