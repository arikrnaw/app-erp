<template>

    <Head title="Create Inventory Transaction" />

    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl leading-tight">
                Create Inventory Transaction
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Transaction Details -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <!-- Product -->
                                <div>
                                    <Label for="product_id">Product</Label>
                                    <select id="product_id" v-model="form.product_id"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        required>
                                        <option value="">Select Product</option>
                                        <option v-for="product in products" :key="product?.id" :value="product?.id">
                                            {{ product?.name }} ({{ product?.sku }})
                                        </option>
                                    </select>
                                    <InputError :message="form.errors.product_id" class="mt-2" />
                                </div>

                                <!-- Transaction Type -->
                                <div>
                                    <Label for="type">Transaction Type</Label>
                                    <select id="type" v-model="form.type"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        required>
                                        <option value="">Select Type</option>
                                        <option value="in">Stock In</option>
                                        <option value="out">Stock Out</option>
                                        <option value="adjustment">Adjustment</option>
                                    </select>
                                    <InputError :message="form.errors.type" class="mt-2" />
                                </div>

                                <!-- Transaction Date -->
                                <div>
                                    <Label for="transaction_date">Transaction Date</Label>
                                    <Input id="transaction_date" type="date" class="mt-1"
                                        v-model="form.transaction_date" required />
                                    <InputError :message="form.errors.transaction_date" class="mt-2" />
                                </div>
                            </div>

                            <!-- Quantity and Reference -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <!-- Quantity -->
                                <div>
                                    <Label for="quantity">Quantity</Label>
                                    <Input id="quantity" type="number" min="1" step="1" class="mt-1"
                                        v-model="form.quantity" required />
                                    <InputError :message="form.errors.quantity" class="mt-2" />
                                </div>

                                <!-- Reference Type -->
                                <div>
                                    <Label for="reference_type">Reference Type</Label>
                                    <select id="reference_type" v-model="form.reference_type"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                        <option value="">No Reference</option>
                                        <option value="sales_order">Sales Order</option>
                                        <option value="purchase_order">Purchase Order</option>
                                        <option value="manual">Manual</option>
                                    </select>
                                    <InputError :message="form.errors.reference_type" class="mt-2" />
                                </div>

                                <!-- Reference ID -->
                                <div>
                                    <Label for="reference_id">Reference ID</Label>
                                    <Input id="reference_id" type="number" class="mt-1" v-model="form.reference_id"
                                        :disabled="!form.reference_type" />
                                    <InputError :message="form.errors.reference_id" class="mt-2" />
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
                                <Link :href="route('inventory.index')">
                                <Button variant="outline" type="button" class="mr-2">
                                    Cancel
                                </Button>
                                </Link>
                                <Button :class="{ 'opacity-25': form.processing }" :disabled="form.processing"
                                    type="submit">
                                    Create Transaction
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
import { apiService } from '@/services/api'
import type { Product } from '@/types/erp'

interface InventoryTransactionForm {
    product_id: number | string
    type: 'in' | 'out' | 'adjustment' | string
    quantity: number | string
    transaction_date: string
    reference_type?: 'sales_order' | 'purchase_order' | 'manual' | string
    reference_id?: number | string
    notes?: string
}

const products = ref<Product[]>([])

const form = useForm({
    product_id: '',
    type: '',
    quantity: 1,
    transaction_date: new Date().toISOString().split('T')[0],
    reference_type: '',
    reference_id: '',
    notes: ''
})

const fetchProducts = async () => {
    try {
        const response = await apiService.getProducts({ page: 1 })
        products.value = response.data || []
    } catch (error) {
        console.error('Error fetching products:', error)
        products.value = []
    }
}

const submit = (): void => {
    form.post(route('inventory.store'))
}

onMounted(() => {
    fetchProducts()
})
</script>
