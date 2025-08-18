<template>

    <Head title="Inventory Transaction Details" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl leading-tight">
                    Inventory Transaction Details
                </h2>
                <div class="flex gap-2">
                    <Link :href="route('inventory.edit', transaction?.id)">
                    <Button variant="outline">
                        <Edit class="w-4 h-4 mr-2" />
                        Edit
                    </Button>
                    </Link>
                    <Link :href="route('inventory.index')">
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
                        <!-- Loading State -->
                        <div v-if="loading" class="flex items-center justify-center py-8">
                            <Loader2 class="w-6 h-6 animate-spin mr-2" />
                            Loading transaction details...
                        </div>

                        <!-- Error State -->
                        <div v-else-if="!transaction" class="text-center py-8">
                            <p class="text-gray-500">Transaction not found</p>
                        </div>

                        <!-- Transaction Details -->
                        <div v-else>
                            <!-- Transaction Information -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                                <Card>
                                    <CardHeader>
                                        <CardTitle>Transaction Information</CardTitle>
                                    </CardHeader>
                                    <CardContent class="space-y-4">
                                        <div>
                                            <Label class="text-sm font-medium text-gray-500">Transaction ID</Label>
                                            <p class="text-lg font-semibold">#{{ transaction?.id }}</p>
                                        </div>
                                        <div>
                                            <Label class="text-sm font-medium text-gray-500">Transaction Date</Label>
                                            <p class="text-lg">{{ formatDate(transaction?.transaction_date) }}</p>
                                        </div>
                                        <div>
                                            <Label class="text-sm font-medium text-gray-500">Type</Label>
                                            <Badge :variant="getTypeVariant(transaction?.type)">
                                                {{ getTypeLabel(transaction?.type) }}
                                            </Badge>
                                        </div>
                                        <div>
                                            <Label class="text-sm font-medium text-gray-500">Quantity</Label>
                                            <p class="text-lg font-bold"
                                                :class="transaction?.type === 'in' ? 'text-green-600' : 'text-red-600'">
                                                {{ transaction?.type === 'in' ? '+' : '-' }}{{ transaction?.quantity }}
                                            </p>
                                        </div>
                                    </CardContent>
                                </Card>

                                <Card>
                                    <CardHeader>
                                        <CardTitle>Product Information</CardTitle>
                                    </CardHeader>
                                    <CardContent class="space-y-4">
                                        <div>
                                            <Label class="text-sm font-medium text-gray-500">Product Name</Label>
                                            <p class="text-lg font-semibold">{{ transaction?.product?.name || 'N/A' }}
                                            </p>
                                        </div>
                                        <div>
                                            <Label class="text-sm font-medium text-gray-500">SKU</Label>
                                            <p class="text-lg">{{ transaction?.product?.sku || 'N/A' }}</p>
                                        </div>
                                        <div>
                                            <Label class="text-sm font-medium text-gray-500">Current Stock</Label>
                                            <p class="text-lg">{{ transaction?.product?.stock_quantity || 0 }} {{
                                                transaction?.product?.unit || 'N/A' }}</p>
                                        </div>
                                        <div>
                                            <Label class="text-sm font-medium text-gray-500">Category</Label>
                                            <p class="text-lg">{{ transaction?.product?.category?.name || 'N/A' }}</p>
                                        </div>
                                    </CardContent>
                                </Card>
                            </div>

                            <!-- Reference Information -->
                            <Card v-if="transaction?.reference_type" class="mb-8">
                                <CardHeader>
                                    <CardTitle>Reference Information</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <Label class="text-sm font-medium text-gray-500">Reference Type</Label>
                                            <p class="text-lg font-semibold">{{
                                                getReferenceTypeLabel(transaction?.reference_type) }}
                                            </p>
                                        </div>
                                        <div>
                                            <Label class="text-sm font-medium text-gray-500">Reference ID</Label>
                                            <p class="text-lg">#{{ transaction?.reference_id }}</p>
                                        </div>
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
                                            <p class="text-lg">{{ transaction?.created_by_user?.name || 'N/A' }}</p>
                                        </div>
                                        <div>
                                            <Label class="text-sm font-medium text-gray-500">Created At</Label>
                                            <p class="text-lg">{{ formatDate(transaction?.created_at) }}</p>
                                        </div>
                                        <div>
                                            <Label class="text-sm font-medium text-gray-500">Updated At</Label>
                                            <p class="text-lg">{{ formatDate(transaction?.updated_at) }}</p>
                                        </div>
                                    </div>
                                    <div v-if="transaction?.notes" class="mt-4">
                                        <Label class="text-sm font-medium text-gray-500">Notes</Label>
                                        <p class="text-lg mt-1">{{ transaction?.notes }}</p>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Label } from '@/components/ui/label'
import { Edit, ArrowLeft, Loader2 } from 'lucide-vue-next'
import { apiService } from '@/services/api'
import type { InventoryTransaction } from '@/types/erp'

interface Props {
    id: number
    transaction?: InventoryTransaction | null
}

const props = defineProps<Props>()

const transaction = ref<InventoryTransaction | null>(props.transaction)
const loading = ref(!props.transaction)

const fetchTransaction = async () => {
    if (props.transaction) return

    loading.value = true
    try {
        const response = await apiService.getInventoryTransaction(props.id)
        transaction.value = response
    } catch (error) {
        console.error('Error fetching inventory transaction:', error)
        transaction.value = null
    } finally {
        loading.value = false
    }
}

onMounted(() => {
    if (!props.transaction) {
        fetchTransaction()
    }
})

const formatDate = (dateString: string): string => {
    if (!dateString) return 'N/A'
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const getTypeLabel = (type: string): string => {
    const labels = {
        'in': 'Stock In',
        'out': 'Stock Out',
        'adjustment': 'Adjustment'
    }
    return labels[type as keyof typeof labels] || type || 'N/A'
}

const getTypeVariant = (type: string): 'default' | 'secondary' | 'destructive' => {
    const variants: Record<string, 'default' | 'secondary' | 'destructive'> = {
        'in': 'default',
        'out': 'destructive',
        'adjustment': 'secondary'
    }
    return variants[type] || 'secondary'
}

const getReferenceTypeLabel = (referenceType: string): string => {
    const labels = {
        'sales_order': 'Sales Order',
        'purchase_order': 'Purchase Order',
        'manual': 'Manual'
    }
    return labels[referenceType as keyof typeof labels] || referenceType || 'N/A'
}
</script>
