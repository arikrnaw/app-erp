<template>

    <Head title="Product Details" />

    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl leading-tight">
                Product Details
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-semibold">{{ product.name }}</h3>
                            <div class="flex space-x-2">
                                <Link :href="route('products.edit', product.id)"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Edit
                                </Link>
                                <Link :href="route('products.index')"
                                    class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Back to List
                                </Link>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Basic Information -->
                            <div class="space-y-4">
                                <h4 class="text-md font-semibold text-gray-700 border-b pb-2">Basic Information</h4>

                                <div>
                                    <label class="block text-sm font-medium text-gray-500">SKU</label>
                                    <p class="mt-1 text-sm">{{ product.sku }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Category</label>
                                    <p class="mt-1 text-sm">{{ product.category?.name || 'N/A' }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Description</label>
                                    <p class="mt-1 text-sm">{{ product.description || `No description
                                        available`
                                        }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Unit</label>
                                    <p class="mt-1 text-sm">{{ product.unit }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Status</label>
                                    <span
                                        class="mt-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                        :class="product.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                                        {{ product.status === 'active' ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                            </div>

                            <!-- Pricing & Inventory -->
                            <div class="space-y-4">
                                <h4 class="text-md font-semibold text-gray-700 border-b pb-2">Pricing & Inventory</h4>

                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Cost Price</label>
                                    <p class="mt-1 text-sm">{{ formatCurrency(product.cost_price) }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Selling Price</label>
                                    <p class="mt-1 text-sm">{{ formatCurrency(product.selling_price) }}
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Profit Margin</label>
                                    <p class="mt-1 text-sm">{{ product.profit_margin }}%</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Stock Quantity</label>
                                    <p class="mt-1 text-sm">{{ product.stock_quantity }} {{ product.unit
                                    }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Min Stock Level</label>
                                    <p class="mt-1 text-sm">{{ product.min_stock_level }} {{ product.unit
                                    }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Stock Status</label>
                                    <span
                                        class="mt-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                        :class="product.is_low_stock ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'">
                                        {{ product.is_low_stock ? 'Low Stock' : 'In Stock' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Timestamps -->
                        <div class="mt-8 pt-6 border-t">
                            <h4 class="text-md font-semibold text-gray-700 mb-4">Timestamps</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Created At</label>
                                    <p class="mt-1">{{ formatDate(product.created_at) }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Updated At</label>
                                    <p class="mt-1">{{ formatDate(product.updated_at) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps({
    product: {
        type: Object,
        required: true
    }
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(amount);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>
