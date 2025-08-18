<template>

    <Head title="Category Details" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl leading-tight">
                    Category Details
                </h2>
                <div class="flex gap-2">
                    <Link :href="route('categories.edit', category.id)">
                    <Button variant="outline">
                        <Edit class="w-4 h-4 mr-2" />
                        Edit
                    </Button>
                    </Link>
                    <Link :href="route('categories.index')">
                    <Button variant="outline">
                        <ArrowLeft class="w-4 h-4 mr-2" />
                        Back
                    </Button>
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <!-- Category Information -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                            <Card>
                                <CardHeader>
                                    <CardTitle>Category Information</CardTitle>
                                </CardHeader>
                                <CardContent class="space-y-4">
                                    <div>
                                        <Label class="text-sm font-medium text-gray-500">Category Name</Label>
                                        <p class="text-lg font-semibold">{{ category.name }}</p>
                                    </div>
                                    <div>
                                        <Label class="text-sm font-medium text-gray-500">Parent Category</Label>
                                        <p class="text-lg">{{ category.parent?.name || 'No Parent Category' }}</p>
                                    </div>
                                    <div>
                                        <Label class="text-sm font-medium text-gray-500">Created At</Label>
                                        <p class="text-lg">{{ formatDate(category.created_at) }}</p>
                                    </div>
                                    <div>
                                        <Label class="text-sm font-medium text-gray-500">Updated At</Label>
                                        <p class="text-lg">{{ formatDate(category.updated_at) }}</p>
                                    </div>
                                </CardContent>
                            </Card>

                            <Card>
                                <CardHeader>
                                    <CardTitle>Statistics</CardTitle>
                                </CardHeader>
                                <CardContent class="space-y-4">
                                    <div>
                                        <Label class="text-sm font-medium text-gray-500">Total Products</Label>
                                        <p class="text-lg font-semibold">{{ category.products_count || 0 }}</p>
                                    </div>
                                    <div>
                                        <Label class="text-sm font-medium text-gray-500">Sub Categories</Label>
                                        <p class="text-lg font-semibold">{{ category.children?.length || 0 }}</p>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>

                        <!-- Sub Categories -->
                        <Card v-if="category.children && category.children.length > 0" class="mb-8">
                            <CardHeader>
                                <CardTitle>Sub Categories</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div class="rounded-md border">
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead>Name</TableHead>
                                                <TableHead>Products Count</TableHead>
                                                <TableHead>Created At</TableHead>
                                                <TableHead class="text-right">Actions</TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            <TableRow v-for="child in category.children" :key="child.id">
                                                <TableCell>
                                                    <div class="font-medium">{{ child.name }}</div>
                                                </TableCell>
                                                <TableCell>{{ child.products_count || 0 }}</TableCell>
                                                <TableCell>{{ formatDate(child.created_at) }}</TableCell>
                                                <TableCell class="text-right">
                                                    <Link :href="route('categories.show', child.id)">
                                                    <Button variant="ghost" size="sm">
                                                        <Eye class="w-4 h-4 mr-2" />
                                                        View
                                                    </Button>
                                                    </Link>
                                                </TableCell>
                                            </TableRow>
                                        </TableBody>
                                    </Table>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Products in this Category -->
                        <Card v-if="category.products && category.products.length > 0">
                            <CardHeader>
                                <CardTitle>Products in this Category</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div class="rounded-md border">
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead>Product Name</TableHead>
                                                <TableHead>SKU</TableHead>
                                                <TableHead>Stock</TableHead>
                                                <TableHead>Price</TableHead>
                                                <TableHead>Status</TableHead>
                                                <TableHead class="text-right">Actions</TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            <TableRow v-for="product in category.products" :key="product.id">
                                                <TableCell>
                                                    <div class="font-medium">{{ product.name }}</div>
                                                </TableCell>
                                                <TableCell>{{ product.sku }}</TableCell>
                                                <TableCell>{{ product.stock_quantity }}</TableCell>
                                                <TableCell>${{ formatCurrency(product.selling_price) }}</TableCell>
                                                <TableCell>
                                                    <span :class="[
                                                        'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                                                        product.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                                                    ]">
                                                        {{ product.status }}
                                                    </span>
                                                </TableCell>
                                                <TableCell class="text-right">
                                                    <Link :href="route('products.show', product.id)">
                                                    <Button variant="ghost" size="sm">
                                                        <Eye class="w-4 h-4 mr-2" />
                                                        View
                                                    </Button>
                                                    </Link>
                                                </TableCell>
                                            </TableRow>
                                        </TableBody>
                                    </Table>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Label } from '@/components/ui/label'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Edit, ArrowLeft, Eye } from 'lucide-vue-next'
import type { Category } from '@/types/erp'

interface Props {
    category: Category
}

const props = defineProps<Props>()

const formatDate = (dateString: string): string => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const formatCurrency = (amount: number): string => {
    return amount.toFixed(2)
}
</script>
