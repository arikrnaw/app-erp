<template>

    <Head title="Purchase Order Details" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl leading-tight">
                    Purchase Order Details
                </h2>
                <div class="flex gap-2">
                    <Link :href="route('purchase-orders.edit', purchaseOrder.id)">
                    <Button variant="outline">
                        <Edit class="w-4 h-4 mr-2" />
                        Edit
                    </Button>
                    </Link>
                    <Link :href="route('purchase-orders.index')">
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
                        <!-- Order Header -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                            <Card>
                                <CardHeader>
                                    <CardTitle>Order Information</CardTitle>
                                </CardHeader>
                                <CardContent class="space-y-4">
                                    <div>
                                        <Label class="text-sm font-medium text-gray-500">Order Number</Label>
                                        <p class="text-lg font-semibold">{{ purchaseOrder.po_number }}</p>
                                    </div>
                                    <div>
                                        <Label class="text-sm font-medium text-gray-500">Order Date</Label>
                                        <p class="text-lg">{{ formatDate(purchaseOrder.order_date) }}</p>
                                    </div>
                                    <div>
                                        <Label class="text-sm font-medium text-gray-500">Status</Label>
                                        <span :class="[
                                            'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                                            getStatusColor(purchaseOrder.status)
                                        ]">
                                            {{ purchaseOrder.status }}
                                        </span>
                                    </div>
                                    <div>
                                        <Label class="text-sm font-medium text-gray-500">Total Amount</Label>
                                        <p class="text-lg font-bold">{{ formatCurrency(purchaseOrder.total_amount) }}
                                        </p>
                                    </div>
                                </CardContent>
                            </Card>

                            <Card>
                                <CardHeader>
                                    <CardTitle>Supplier Information</CardTitle>
                                </CardHeader>
                                <CardContent class="space-y-4">
                                    <div>
                                        <Label class="text-sm font-medium text-gray-500">Supplier Name</Label>
                                        <p class="text-lg font-semibold">{{ purchaseOrder.supplier?.name }}</p>
                                    </div>
                                    <div>
                                        <Label class="text-sm font-medium text-gray-500">Supplier Code</Label>
                                        <p class="text-lg">{{ purchaseOrder.supplier?.code }}</p>
                                    </div>
                                    <div>
                                        <Label class="text-sm font-medium text-gray-500">Email</Label>
                                        <p class="text-lg">{{ purchaseOrder.supplier?.email }}</p>
                                    </div>
                                    <div>
                                        <Label class="text-sm font-medium text-gray-500">Phone</Label>
                                        <p class="text-lg">{{ purchaseOrder.supplier?.phone }}</p>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>

                        <!-- Order Items -->
                        <Card class="mb-8">
                            <CardHeader>
                                <CardTitle>Order Items</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div class="rounded-md border">
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead>Product</TableHead>
                                                <TableHead>SKU</TableHead>
                                                <TableHead>Quantity</TableHead>
                                                <TableHead>Unit Price</TableHead>
                                                <TableHead class="text-right">Total Price</TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            <TableRow v-for="item in purchaseOrder.items" :key="item.id">
                                                <TableCell>
                                                    <div>
                                                        <div class="font-medium">{{ item.product?.name }}</div>
                                                    </div>
                                                </TableCell>
                                                <TableCell>{{ item.product?.sku }}</TableCell>
                                                <TableCell>{{ item.quantity }}</TableCell>
                                                <TableCell>{{ formatCurrency(item.unit_price) }}</TableCell>
                                                <TableCell class="text-right">{{ formatCurrency(item.total_price) }}
                                                </TableCell>
                                            </TableRow>
                                        </TableBody>
                                    </Table>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Additional Information -->
                        <Card>
                            <CardHeader>
                                <CardTitle>Additional Information</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <Label class="text-sm font-medium text-gray-500">Created By</Label>
                                        <p class="text-lg">{{ purchaseOrder.created_by_user?.name }}</p>
                                    </div>
                                    <div>
                                        <Label class="text-sm font-medium text-gray-500">Created At</Label>
                                        <p class="text-lg">{{ formatDate(purchaseOrder.created_at) }}</p>
                                    </div>
                                    <div>
                                        <Label class="text-sm font-medium text-gray-500">Updated At</Label>
                                        <p class="text-lg">{{ formatDate(purchaseOrder.updated_at) }}</p>
                                    </div>
                                </div>
                                <div v-if="purchaseOrder.notes" class="mt-4">
                                    <Label class="text-sm font-medium text-gray-500">Notes</Label>
                                    <p class="text-lg mt-1">{{ purchaseOrder.notes }}</p>
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
import { Edit, ArrowLeft } from 'lucide-vue-next'
import type { PurchaseOrder } from '@/types/erp'

interface Props {
    purchaseOrder: PurchaseOrder
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
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(amount)
}

const getStatusColor = (status: string): string => {
    const colors = {
        draft: 'bg-gray-100',
        confirmed: 'bg-blue-100 text-blue-800',
        received: 'bg-green-100 text-green-800',
        cancelled: 'bg-red-100 text-red-800'
    }
    return colors[status as keyof typeof colors] || 'bg-gray-100'
}
</script>
