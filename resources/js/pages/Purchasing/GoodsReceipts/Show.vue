<template>

    <Head title="Goods Receipt Details" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl leading-tight">
                    Goods Receipt Details
                </h2>
                <div class="flex gap-2">
                    <Link :href="route('purchasing.goods-receipts.index')">
                    <Button variant="outline">
                        <ArrowLeft class="w-4 h-4 mr-2" />
                        Back to List
                    </Button>
                    </Link>
                    <Link v-if="receipt?.status === 'draft'"
                        :href="route('purchasing.goods-receipts.edit', receipt?.id)">
                    <Button>
                        <Edit class="w-4 h-4 mr-2" />
                        Edit
                    </Button>
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div v-if="loading" class="flex items-center justify-center py-8">
                            <Loader2 class="w-6 h-6 animate-spin mr-2" />
                            Loading...
                        </div>

                        <div v-else-if="!receipt" class="text-center py-8 text-gray-500">
                            Goods receipt not found
                        </div>

                        <div v-else class="space-y-6">
                            <!-- Header Information -->
                            <div class="bg-gray-50 rounded-lg p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                    <div>
                                        <Label class="text-sm font-medium text-gray-500">Receipt Number</Label>
                                        <div class="text-lg font-semibold">{{ receipt?.receipt_number }}</div>
                                    </div>
                                    <div>
                                        <Label class="text-sm font-medium text-gray-500">Status</Label>
                                        <div>
                                            <Badge :variant="getStatusVariant(receipt?.status)">
                                                {{ getStatusLabel(receipt?.status) }}
                                            </Badge>
                                        </div>
                                    </div>
                                    <div>
                                        <Label class="text-sm font-medium text-gray-500">Receipt Date</Label>
                                        <div>{{ formatDate(receipt?.receipt_date) }}</div>
                                    </div>
                                    <div>
                                        <Label class="text-sm font-medium text-gray-500">Purchase Order</Label>
                                        <div>{{ receipt?.purchase_order?.po_number || 'N/A' }}</div>
                                    </div>
                                    <div>
                                        <Label class="text-sm font-medium text-gray-500">Supplier</Label>
                                        <div>{{ receipt?.supplier?.name || 'N/A' }}</div>
                                    </div>
                                    <div>
                                        <Label class="text-sm font-medium text-gray-500">Total Amount</Label>
                                        <div class="text-lg font-semibold">{{ formatCurrency(receipt?.total_amount) }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Receipt Details -->
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                <div class="space-y-4">
                                    <div>
                                        <Label class="text-sm font-medium text-gray-500">Warehouse</Label>
                                        <div>{{ receipt?.warehouse?.name || 'N/A' }}</div>
                                    </div>
                                    <div>
                                        <Label class="text-sm font-medium text-gray-500">Received By</Label>
                                        <div>{{ receipt?.receivedBy?.name || 'N/A' }}</div>
                                    </div>
                                    <div v-if="receipt?.delivery_note_number">
                                        <Label class="text-sm font-medium text-gray-500">Delivery Note Number</Label>
                                        <div>{{ receipt?.delivery_note_number }}</div>
                                    </div>
                                    <div v-if="receipt?.vehicle_number">
                                        <Label class="text-sm font-medium text-gray-500">Vehicle Number</Label>
                                        <div>{{ receipt?.vehicle_number }}</div>
                                    </div>
                                    <div v-if="receipt?.driver_name">
                                        <Label class="text-sm font-medium text-gray-500">Driver Name</Label>
                                        <div>{{ receipt?.driver_name }}</div>
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    <div v-if="receipt?.notes">
                                        <Label class="text-sm font-medium text-gray-500">Notes</Label>
                                        <div class="whitespace-pre-wrap">{{ receipt?.notes }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Items Table -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-medium">Receipt Items</h3>
                                <div class="rounded-md border">
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead>Product</TableHead>
                                                <TableHead>Ordered Qty</TableHead>
                                                <TableHead>Received Qty</TableHead>
                                                <TableHead>Unit Price</TableHead>
                                                <TableHead>Total Price</TableHead>
                                                <TableHead>Lot Number</TableHead>
                                                <TableHead>Expiry Date</TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            <TableRow v-if="!receipt?.items || receipt.items.length === 0">
                                                <TableCell colspan="7" class="text-center py-8 text-gray-500">
                                                    No items found
                                                </TableCell>
                                            </TableRow>
                                            <TableRow v-else v-for="item in receipt.items" :key="item?.id">
                                                <TableCell>
                                                    <div>
                                                        <div class="font-medium">{{ item?.product?.name || 'N/A' }}
                                                        </div>
                                                        <div v-if="item?.notes" class="text-sm text-gray-500">
                                                            {{ item?.notes }}
                                                        </div>
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <div>{{ item?.ordered_quantity }}</div>
                                                </TableCell>
                                                <TableCell>
                                                    <div>{{ item?.received_quantity }}</div>
                                                </TableCell>
                                                <TableCell>
                                                    <div>{{ formatCurrency(item?.unit_price) }}</div>
                                                </TableCell>
                                                <TableCell>
                                                    <div class="font-medium">{{ formatCurrency(item?.total_price) }}
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <div>{{ item?.lot_number || 'N/A' }}</div>
                                                </TableCell>
                                                <TableCell>
                                                    <div>{{ item?.expiry_date ? formatDate(item?.expiry_date) : 'N/A' }}
                                                    </div>
                                                </TableCell>
                                            </TableRow>
                                        </TableBody>
                                    </Table>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div v-if="receipt?.status === 'draft'" class="flex justify-end gap-2">
                                <Button @click="receiveItems" :disabled="loading">
                                    <PackageCheck class="w-4 h-4 mr-2" />
                                    Receive Items
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Label } from '@/components/ui/label'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { ArrowLeft, Edit, Loader2, PackageCheck } from 'lucide-vue-next'
import { apiService } from '@/services/api'
import type { GoodsReceipt } from '@/types/erp'

interface Props {
    id: number
}

const props = defineProps<Props>()

const loading = ref(false)
const receipt = ref<GoodsReceipt | null>(null)

const fetchReceipt = async () => {
    loading.value = true
    try {
        const response = await apiService.getGoodsReceipt(props.id)
        receipt.value = response
    } catch (error) {
        console.error('Error fetching goods receipt:', error)
        receipt.value = null
    } finally {
        loading.value = false
    }
}

const receiveItems = async () => {
    if (!receipt.value) return

    loading.value = true
    try {
        await apiService.updateGoodsReceipt(receipt.value.id, {
            ...receipt.value,
            status: 'received'
        })
        await fetchReceipt()
    } catch (error) {
        console.error('Error receiving items:', error)
        alert('Error receiving items. Please try again.')
    } finally {
        loading.value = false
    }
}

const formatDate = (dateString: string): string => {
    if (!dateString) return 'N/A'
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    })
}

const formatCurrency = (amount: number): string => {
    if (!amount) return 'Rp 0'
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(amount)
}

const getStatusLabel = (status: string): string => {
    const labels = {
        draft: 'Draft',
        received: 'Received',
        partially_received: 'Partially Received',
        cancelled: 'Cancelled'
    }
    return labels[status as keyof typeof labels] || status
}

const getStatusVariant = (status: string): 'default' | 'secondary' | 'destructive' => {
    const variants = {
        draft: 'secondary',
        received: 'default',
        partially_received: 'default',
        cancelled: 'destructive'
    }
    return variants[status as keyof typeof variants] || 'default'
}

onMounted(() => {
    fetchReceipt()
})
</script>
