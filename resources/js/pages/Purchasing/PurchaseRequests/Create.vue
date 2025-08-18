<template>

    <Head title="Create Purchase Request" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl leading-tight">
                    Create Purchase Request
                </h2>
                <Link :href="route('purchasing.purchase-requests.index')">
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
                                    <Label for="request_number">Request Number</Label>
                                    <Input id="request_number" v-model="form.request_number" readonly />
                                </div>
                                <div>
                                    <Label for="request_date">Request Date</Label>
                                    <Input id="request_date" type="date" v-model="form.request_date" required />
                                </div>
                                <div>
                                    <Label for="required_date">Required Date</Label>
                                    <Input id="required_date" type="date" v-model="form.required_date" required />
                                </div>
                                <div>
                                    <Label for="priority">Priority</Label>
                                    <select id="priority" v-model="form.priority"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                        <option value="low">Low</option>
                                        <option value="medium">Medium</option>
                                        <option value="high">High</option>
                                        <option value="urgent">Urgent</option>
                                    </select>
                                </div>
                                <div>
                                    <Label for="department_id">Department</Label>
                                    <select id="department_id" v-model="form.department_id"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                        <option value="">Select Department</option>
                                        <option v-for="department in departments" :key="department?.id"
                                            :value="department?.id">
                                            {{ department?.name || 'N/A' }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <!-- Purpose and Notes -->
                            <div class="space-y-4">
                                <div>
                                    <Label for="purpose">Purpose</Label>
                                    <Textarea id="purpose" v-model="form.purpose" rows="3" required />
                                </div>
                                <div>
                                    <Label for="notes">Notes</Label>
                                    <Textarea id="notes" v-model="form.notes" rows="3" />
                                </div>
                            </div>

                            <!-- Items Section -->
                            <div class="space-y-4">
                                <div class="flex justify-between items-center">
                                    <h3 class="text-lg font-medium">Request Items</h3>
                                    <Button type="button" @click="addItem" variant="outline">
                                        <Plus class="w-4 h-4 mr-2" />
                                        Add Item
                                    </Button>
                                </div>

                                <div v-if="form.items.length === 0" class="text-center py-8 text-gray-500">
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
                                                <Label :for="`item_name_${index}`">Item Name</Label>
                                                <Input :id="`item_name_${index}`" v-model="item.item_name" required />
                                            </div>
                                            <div>
                                                <Label :for="`product_id_${index}`">Product (Optional)</Label>
                                                <select :id="`product_id_${index}`" v-model="item.product_id"
                                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                                    <option value="">Select Product</option>
                                                    <option v-for="product in products" :key="product?.id"
                                                        :value="product?.id">
                                                        {{ product?.name || 'N/A' }}
                                                    </option>
                                                </select>
                                            </div>
                                            <div>
                                                <Label :for="`quantity_${index}`">Quantity</Label>
                                                <Input :id="`quantity_${index}`" type="number" v-model="item.quantity"
                                                    min="1" required />
                                            </div>
                                            <div>
                                                <Label :for="`unit_${index}`">Unit</Label>
                                                <Input :id="`unit_${index}`" v-model="item.unit" required />
                                            </div>
                                            <div>
                                                <Label :for="`estimated_unit_price_${index}`">Estimated Unit
                                                    Price</Label>
                                                <Input :id="`estimated_unit_price_${index}`" type="number" step="0.01"
                                                    v-model="item.estimated_unit_price" min="0" />
                                            </div>
                                            <div>
                                                <Label :for="`estimated_total_price_${index}`">Estimated Total
                                                    Price</Label>
                                                <Input :id="`estimated_total_price_${index}`" type="number" step="0.01"
                                                    v-model="item.estimated_total_price" min="0" readonly />
                                            </div>
                                        </div>

                                        <div class="mt-4 space-y-2">
                                            <div>
                                                <Label :for="`description_${index}`">Description</Label>
                                                <Textarea :id="`description_${index}`" v-model="item.description"
                                                    rows="2" />
                                            </div>
                                            <div>
                                                <Label :for="`specifications_${index}`">Specifications</Label>
                                                <Input :id="`specifications_${index}`" v-model="item.specifications" />
                                            </div>
                                            <div>
                                                <Label :for="`notes_${index}`">Notes</Label>
                                                <Textarea :id="`notes_${index}`" v-model="item.notes" rows="2" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Summary -->
                            <div class="border-t pt-6">
                                <div class="flex justify-between items-center">
                                    <div class="text-lg font-medium">
                                        Total Estimated Cost: {{ formatCurrency(totalEstimatedCost) }}
                                    </div>
                                    <div class="space-x-2">
                                        <Button type="button" @click="saveDraft" variant="outline" :disabled="loading">
                                            <Save class="w-4 h-4 mr-2" />
                                            Save Draft
                                        </Button>
                                        <Button type="submit" :disabled="loading">
                                            <Send class="w-4 h-4 mr-2" />
                                            {{ loading ? 'Submitting...' : 'Submit Request' }}
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
import type { PurchaseRequestItem, Product, Department } from '@/types/erp'

interface FormItem {
    item_name: string
    product_id?: number
    description?: string
    specifications?: string
    quantity: number
    unit: string
    estimated_unit_price: number
    estimated_total_price: number
    notes?: string
}

const loading = ref(false)
const products = ref<Product[]>([])
const departments = ref<Department[]>([])

const form = ref({
    request_number: '',
    request_date: new Date().toISOString().split('T')[0],
    required_date: '',
    priority: 'medium',
    department_id: '',
    purpose: '',
    notes: '',
    items: [] as FormItem[]
})

const totalEstimatedCost = computed(() => {
    return form.value.items.reduce((total, item) => total + (item.estimated_total_price || 0), 0)
})

const addItem = () => {
    form.value.items.push({
        item_name: '',
        product_id: undefined,
        description: '',
        specifications: '',
        quantity: 1,
        unit: 'pcs',
        estimated_unit_price: 0,
        estimated_total_price: 0,
        notes: ''
    })
}

const removeItem = (index: number) => {
    form.value.items.splice(index, 1)
}

const calculateItemTotal = (item: FormItem) => {
    item.estimated_total_price = (item.quantity || 0) * (item.estimated_unit_price || 0)
}

const watchItemChanges = () => {
    form.value.items.forEach((item, index) => {
        watch(() => [item.quantity, item.estimated_unit_price], () => {
            calculateItemTotal(item)
        })
    })
}

const fetchProducts = async () => {
    try {
        const response = await apiService.getProducts({ page: 1, per_page: 100 })
        products.value = response.data || []
    } catch (error) {
        console.error('Error fetching products:', error)
        products.value = []
    }
}

const fetchDepartments = async () => {
    try {
        const response = await apiService.getDepartments({ page: 1, per_page: 100 })
        departments.value = response.data || []
    } catch (error) {
        console.error('Error fetching departments:', error)
        departments.value = []
    }
}

const generateRequestNumber = async () => {
    try {
        const response = await apiService.generatePurchaseRequestNumber()
        form.value.request_number = response.request_number || ''
    } catch (error) {
        console.error('Error generating request number:', error)
        form.value.request_number = 'PR' + new Date().getFullYear() + String(new Date().getMonth() + 1).padStart(2, '0') + String(new Date().getDate()).padStart(2, '0') + String(Math.floor(Math.random() * 1000)).padStart(3, '0')
    }
}

const submitForm = async () => {
    if (form.value.items.length === 0) {
        alert('Please add at least one item to the request.')
        return
    }

    loading.value = true
    try {
        const requestData = {
            ...form.value,
            status: 'submitted',
            total_estimated_cost: totalEstimatedCost.value
        }

        await apiService.createPurchaseRequest(requestData)
        router.visit(route('purchasing.purchase-requests.index'))
    } catch (error) {
        console.error('Error creating purchase request:', error)
        alert('Error creating purchase request. Please try again.')
    } finally {
        loading.value = false
    }
}

const saveDraft = async () => {
    loading.value = true
    try {
        const requestData = {
            ...form.value,
            status: 'draft',
            total_estimated_cost: totalEstimatedCost.value
        }

        await apiService.createPurchaseRequest(requestData)
        router.visit(route('purchasing.purchase-requests.index'))
    } catch (error) {
        console.error('Error saving draft:', error)
        alert('Error saving draft. Please try again.')
    } finally {
        loading.value = false
    }
}

const formatCurrency = (amount: number): string => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount)
}

onMounted(async () => {
    await Promise.all([
        fetchProducts(),
        fetchDepartments(),
        generateRequestNumber()
    ])
    addItem() // Add initial item
    watchItemChanges()
})
</script>
