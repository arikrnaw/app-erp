<template>

    <Head title="Edit Product" />

    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl leading-tight">
                Edit Product
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <form @submit.prevent="submit" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Product Name -->
                                <div>
                                    <Label for="name">Product Name</Label>
                                    <Input id="name" type="text" class="mt-1" v-model="form.name" required autofocus />
                                    <InputError :message="form.errors.name" class="mt-2" />
                                </div>

                                <!-- SKU -->
                                <div>
                                    <Label for="sku">SKU</Label>
                                    <Input id="sku" type="text" class="mt-1" v-model="form.sku" required />
                                    <InputError :message="form.errors.sku" class="mt-2" />
                                </div>

                                <!-- Category -->
                                <div>
                                    <Label for="category_id">Category</Label>
                                    <select id="category_id" v-model="form.category_id"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        required>
                                        <option value="">Select Category</option>
                                        <option v-for="category in categories" :key="category.id" :value="category.id">
                                            {{ category.name }}
                                        </option>
                                    </select>
                                    <InputError :message="form.errors.category_id" class="mt-2" />
                                </div>

                                <!-- Description -->
                                <div>
                                    <Label for="description">Description</Label>
                                    <textarea id="description" v-model="form.description" rows="3"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
                                    <InputError :message="form.errors.description" class="mt-2" />
                                </div>

                                <!-- Cost Price -->
                                <div>
                                    <Label for="cost_price">Cost Price</Label>
                                    <Input id="cost_price" type="number" step="0.01" class="mt-1"
                                        v-model="form.cost_price" required />
                                    <InputError :message="form.errors.cost_price" class="mt-2" />
                                </div>

                                <!-- Selling Price -->
                                <div>
                                    <Label for="selling_price">Selling Price</Label>
                                    <Input id="selling_price" type="number" step="0.01" class="mt-1"
                                        v-model="form.selling_price" required />
                                    <InputError :message="form.errors.selling_price" class="mt-2" />
                                </div>

                                <!-- Stock Quantity -->
                                <div>
                                    <Label for="stock_quantity">Stock Quantity</Label>
                                    <Input id="stock_quantity" type="number" class="mt-1" v-model="form.stock_quantity"
                                        required />
                                    <InputError :message="form.errors.stock_quantity" class="mt-2" />
                                </div>

                                <!-- Min Stock Level -->
                                <div>
                                    <Label for="min_stock_level">Min Stock Level</Label>
                                    <Input id="min_stock_level" type="number" class="mt-1"
                                        v-model="form.min_stock_level" required />
                                    <InputError :message="form.errors.min_stock_level" class="mt-2" />
                                </div>
                            </div>

                            <!-- Unit -->
                            <div>
                                <Label for="unit">Unit</Label>
                                <Input id="unit" type="text" class="mt-1" v-model="form.unit"
                                    placeholder="e.g., pcs, kg, liter" required />
                                <InputError :message="form.errors.unit" class="mt-2" />
                            </div>

                            <!-- Status -->
                            <div>
                                <Label for="status">Status</Label>
                                <select id="status" v-model="form.status"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                                <InputError :message="form.errors.status" class="mt-2" />
                            </div>

                            <div class="flex items-center justify-end mt-4">
                                <Link :href="route('products.index')"
                                    class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 mr-2">
                                Cancel
                                </Link>
                                <Button :class="{ 'opacity-25': form.processing }" :disabled="form.processing"
                                    type="submit">
                                    Update Product
                                </Button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const props = defineProps({
    product: {
        type: Object,
        required: true
    },
    categories: {
        type: Array,
        required: true
    }
});

const form = useForm({
    name: props.product.name,
    sku: props.product.sku,
    category_id: props.product.category_id,
    description: props.product.description || '',
    cost_price: props.product.cost_price,
    selling_price: props.product.selling_price,
    stock_quantity: props.product.stock_quantity,
    min_stock_level: props.product.min_stock_level,
    unit: props.product.unit,
    status: props.product.status
});

const submit = () => {
    form.put(route('products.update', props.product.id));
};
</script>
