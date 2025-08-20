<template>

    <Head title="Purchase Return Details" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl leading-tight">
                    Purchase Return Details
                </h2>
                <div class="flex gap-2">
                    <Link :href="route('purchasing.purchase-returns.index')">
                    <Button variant="outline">
                        <ArrowLeft class="w-4 h-4 mr-2" />
                        Back to List
                    </Button>
                    </Link>
                    <Link v-if="purchaseReturn?.status === 'draft'"
                        :href="route('purchasing.purchase-returns.edit', purchaseReturn?.id)">
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

                        <div v-else-if="!purchaseReturn" class="text-center py-8 text-gray-500">
                            Purchase return not found
                        </div>

                        <div v-else class="space-y-6">
                            <!-- Header Information -->
                            <div class="bg-gray-50 rounded-lg p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                    <div>
                                        <Label class="text-sm font-medium text-gray-500">Return Number</Label>
                                        <div class="text-lg font-semibold">{{ purchaseReturn?.return_number }}</div>
                                    </div>
                                    <div>
                                        <Label class="text-sm font-medium text-gray-500">Status</Label>
                                        <div>
                                            <Badge :variant="getStatusVariant(purchaseReturn?.status)">
                                                {{ getStatusLabel(purchaseReturn?.status) }}
                                            </Badge>
                                        </div>
                                    </div>
                                    <div>
                                        <Label class="text-sm font-medium text-gray-500">Return Type</Label>
                                        <div>
                                            <Badge :variant="getReturnTypeVariant(purchaseReturn?.return_type)">
                                                {{ getReturnTypeLabel(purchaseReturn?.return_type) }}
                                            </Badge>
                                        </div>
                                    </div>
                                    <div>
                                        <Label class="text-sm font-medium text-gray-500">Return Date</Label>
                                        <div>{{ formatDate(purchaseReturn?.return_date) }}</div>
                                    </div>
                                    <div>
                                        <Label class="text-sm font-medium text-gray-500">Purchase Order</Label>
                                        <div>{{ purchaseReturn?.purchase_order?.po_number || 'N/A' }}</div>
                                    </div>
                                    <div>
                                        <Label class="text-sm font-medium text-gray-500">Total Amount</Label>
                                        <div class="text-lg font-semibold">{{
                                            formatCurrency(purchaseReturn?.total_amount) }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Return Details -->
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                <div class="space-y-4">
                                    <div>
                                        <Label class="text-sm font-medium text-gray-500">Supplier</Label>
                                        <div>{{ purchaseReturn?.supplier?.name || 'N/A' }}</div>
                                    </div>
                                    <div>
                                        <Label class="text-sm font-medium text-gray-500">Warehouse</Label>
                                        <div>{{ purchaseReturn?.warehouse?.name || 'N/A' }}</div>
                                    </div>
                                    <div>
                                        <Label class="text-sm font-medium text-gray-500">Returned By</Label>
                                        <div>{{ purchaseReturn?.returnedBy?.name || 'N/A' }}</div>
                                    </div>
                                    <div v-if="purchaseReturn?.goods_receipt">
                                        <Label class="text-sm font-medium text-gray-500">Goods Receipt</Label>
                                        <div>{{ purchaseReturn?.goods_receipt?.receipt_number || 'N/A' }}</div>
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    <div>
                                        <Label class="text-sm font-medium text-gray-500">Return Reason</Label>
                                        <div class="whitespace-pre-wrap">{{ purchaseReturn?.reason || 'N/A' }}</div>
                                    </div>
                                    <div v-if="purchaseReturn?.notes">
                                        <Label class="text-sm font-medium text-gray-500">Notes</Label>
                                        <div class="whitespace-pre-wrap">{{ purchaseReturn?.notes }}</div>
                                    </div>
                                    <div v-if="purchaseReturn?.approvedBy">
                                        <Label class="text-sm font-medium text-gray-500">Approved By</Label>
                                        <div>{{ purchaseReturn?.approvedBy?.name || 'N/A' }}</div>
                                    </div>
                                    <div v-if="purchaseReturn?.approved_at">
                                        <Label class="text-sm font-medium text-gray-500">Approved At</Label>
                                        <div>{{ formatDateTime(purchaseReturn?.approved_at) }}</div>
                                    </div>
                                    <div v-if="purchaseReturn?.approval_notes">
                                        <Label class="text-sm font-medium text-gray-500">Approval Notes</Label>
                                        <div class="whitespace-pre-wrap">{{ purchaseReturn?.approval_notes }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Items Table -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-medium">Return Items</h3>
                                <div class="rounded-md border">
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead>Product</TableHead>
                                                <TableHead>Received Qty</TableHead>
                                                <TableHead>Return Qty</TableHead>
                                                <TableHead>Unit Price</TableHead>
                                                <TableHead>Total Price</TableHead>
                                                <TableHead>Lot Number</TableHead>
                                                <TableHead>Return Reason</TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            <TableRow
                                                v-if="!purchaseReturn?.items || purchaseReturn.items.length === 0">
                                                <TableCell colspan="7" class="text-center py-8 text-gray-500">
                                                    No items found
                                                </TableCell>
                                            </TableRow>
                                            <TableRow v-else v-for="item in purchaseReturn.items" :key="item?.id">
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
                                                    <div>{{ item?.received_quantity }}</div>
                                                </TableCell>
                                                <TableCell>
                                                    <div>{{ item?.return_quantity }}</div>
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
                                                    <div>{{ item?.return_reason || 'N/A' }}</div>
                                                </TableCell>
                                            </TableRow>
                                        </TableBody>
                                    </Table>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div v-if="purchaseReturn?.status === 'draft'" class="flex justify-end gap-2">
                                <Button @click="submitReturn" :disabled="loading">
                                    <Send class="w-4 h-4 mr-2" />
                                    Submit Return
                                </Button>
                            </div>

                            <div v-if="purchaseReturn?.status === 'submitted' && canApprove"
                                class="flex justify-end gap-2">
                                <Button @click="approveReturn" variant="outline" :disabled="loading">
                                    <Check class="w-4 h-4 mr-2" />
                                    Approve
                                </Button>
                                <Button @click="rejectReturn" variant="destructive" :disabled="loading">
                                    <X class="w-4 h-4 mr-2" />
                                    Reject
                                </Button>
                            </div>

                            <div v-if="purchaseReturn?.status === 'approved'" class="flex justify-end gap-2">
                                <Button @click="processReturn" :disabled="loading">
                                    <RotateCcw class="w-4 h-4 mr-2" />
                                    Process Return
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
import { ref, computed, onMounted } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Label } from '@/components/ui/label'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { ArrowLeft, Edit, Loader2, Send, Check, X, RotateCcw } from 'lucide-vue-next'
import { apiService } from '@/services/api'
import type { PurchaseReturn } from '@/types/erp'

interface Props {
    id: number
}

const props = defineProps<Props>()

const loading = ref(false)
const purchaseReturn = ref<PurchaseReturn | null>(null)

const canApprove = computed(() => {
    // Add logic to check if current user can approve
    return true
})

const fetchReturn = async () => {
    loading.value = true
    try {
        const response = await apiService.getPurchaseReturn(props.id)
        purchaseReturn.value = response
    } catch (error) {
        console.error('Error fetching purchase return:', error)
        purchaseReturn.value = null
    } finally {
        loading.value = false
    }
}

const submitReturn = async () => {
    if (!purchaseReturn.value) return

    loading.value = true
    try {
        await apiService.updatePurchaseReturn(purchaseReturn.value.id, {
            ...purchaseReturn.value,
            status: 'submitted'
        })
        await fetchReturn()
    } catch (error) {
        console.error('Error submitting return:', error)
        alert('Error submitting return. Please try again.')
    } finally {
        loading.value = false
    }
}

const approveReturn = async () => {
    if (!purchaseReturn.value) return

    const approvalNotes = prompt('Enter approval notes (optional):')

    loading.value = true
    try {
        await apiService.updatePurchaseReturn(purchaseReturn.value.id, {
            ...purchaseReturn.value,
            status: 'approved',
            approval_notes: approvalNotes
        })
        await fetchReturn()
    } catch (error) {
        console.error('Error approving return:', error)
        alert('Error approving return. Please try again.')
    } finally {
        loading.value = false
    }
}

const rejectReturn = async () => {
    if (!purchaseReturn.value) return

    const approvalNotes = prompt('Enter rejection reason:')
    if (!approvalNotes) return

    loading.value = true
    try {
        await apiService.updatePurchaseReturn(purchaseReturn.value.id, {
            ...purchaseReturn.value,
            status: 'rejected',
            approval_notes: approvalNotes
        })
        await fetchReturn()
    } catch (error) {
        console.error('Error rejecting return:', error)
        alert('Error rejecting return. Please try again.')
    } finally {
        loading.value = false
    }
}

const processReturn = async () => {
    if (!purchaseReturn.value) return

    loading.value = true
    try {
        await apiService.updatePurchaseReturn(purchaseReturn.value.id, {
            ...purchaseReturn.value,
            status: 'returned'
        })
        await fetchReturn()
    } catch (error) {
        console.error('Error processing return:', error)
        alert('Error processing return. Please try again.')
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

const formatDateTime = (dateString: string): string => {
    if (!dateString) return 'N/A'
    return new Date(dateString).toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
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
        submitted: 'Submitted',
        approved: 'Approved',
        returned: 'Returned',
        rejected: 'Rejected',
        cancelled: 'Cancelled'
    }
    return labels[status as keyof typeof labels] || status
}

const getStatusVariant = (status: string): 'default' | 'secondary' | 'destructive' => {
    const variants = {
        draft: 'secondary',
        submitted: 'default',
        approved: 'default',
        returned: 'default',
        rejected: 'destructive',
        cancelled: 'destructive'
    }
    return variants[status as keyof typeof variants] || 'default'
}

const getReturnTypeLabel = (returnType: string): string => {
    const labels = {
        defective: 'Defective',
        wrong_item: 'Wrong Item',
        overstock: 'Overstock',
        other: 'Other'
    }
    return labels[returnType as keyof typeof labels] || returnType
}

const getReturnTypeVariant = (returnType: string): 'default' | 'secondary' | 'destructive' => {
    const variants = {
        defective: 'destructive',
        wrong_item: 'secondary',
        overstock: 'default',
        other: 'secondary'
    }
    return variants[returnType as keyof typeof variants] || 'default'
}

onMounted(() => {
    fetchReturn()
})
</script>
