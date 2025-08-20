<template>

    <Head title="Purchase Request Details" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl leading-tight">
                    Purchase Request Details
                </h2>
                <div class="flex gap-2">
                    <Link :href="route('purchasing.purchase-requests.index')">
                    <Button variant="outline">
                        <ArrowLeft class="w-4 h-4 mr-2" />
                        Back to List
                    </Button>
                    </Link>
                    <Link v-if="request?.status === 'draft'"
                        :href="route('purchasing.purchase-requests.edit', request?.id)">
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

                        <div v-else-if="!request" class="text-center py-8 text-gray-500">
                            Purchase request not found
                        </div>

                        <div v-else class="space-y-6">
                            <!-- Header Information -->
                            <div class="bg-gray-50 rounded-lg p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                    <div>
                                        <Label class="text-sm font-medium text-gray-500">Request Number</Label>
                                        <div class="text-lg font-semibold">{{ request?.request_number }}</div>
                                    </div>
                                    <div>
                                        <Label class="text-sm font-medium text-gray-500">Status</Label>
                                        <div>
                                            <Badge :variant="getStatusVariant(request?.status)">
                                                {{ getStatusLabel(request?.status) }}
                                            </Badge>
                                        </div>
                                    </div>
                                    <div>
                                        <Label class="text-sm font-medium text-gray-500">Priority</Label>
                                        <div>
                                            <Badge :variant="getPriorityVariant(request?.priority)">
                                                {{ getPriorityLabel(request?.priority) }}
                                            </Badge>
                                        </div>
                                    </div>
                                    <div>
                                        <Label class="text-sm font-medium text-gray-500">Request Date</Label>
                                        <div>{{ formatDate(request?.request_date) }}</div>
                                    </div>
                                    <div>
                                        <Label class="text-sm font-medium text-gray-500">Required Date</Label>
                                        <div>{{ formatDate(request?.required_date) }}</div>
                                    </div>
                                    <div>
                                        <Label class="text-sm font-medium text-gray-500">Total Estimated Cost</Label>
                                        <div class="text-lg font-semibold">{{
                                            formatCurrency(request?.total_estimated_cost) }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Request Details -->
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                <div class="space-y-4">
                                    <div>
                                        <Label class="text-sm font-medium text-gray-500">Requested By</Label>
                                        <div>{{ request?.requestedBy?.name || 'N/A' }}</div>
                                    </div>
                                    <div>
                                        <Label class="text-sm font-medium text-gray-500">Department</Label>
                                        <div>{{ request?.department?.name || 'N/A' }}</div>
                                    </div>
                                    <div>
                                        <Label class="text-sm font-medium text-gray-500">Purpose</Label>
                                        <div class="whitespace-pre-wrap">{{ request?.purpose || 'N/A' }}</div>
                                    </div>
                                    <div v-if="request?.notes">
                                        <Label class="text-sm font-medium text-gray-500">Notes</Label>
                                        <div class="whitespace-pre-wrap">{{ request?.notes }}</div>
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    <div v-if="request?.approvedBy">
                                        <Label class="text-sm font-medium text-gray-500">Approved By</Label>
                                        <div>{{ request?.approvedBy?.name || 'N/A' }}</div>
                                    </div>
                                    <div v-if="request?.approved_at">
                                        <Label class="text-sm font-medium text-gray-500">Approved At</Label>
                                        <div>{{ formatDateTime(request?.approved_at) }}</div>
                                    </div>
                                    <div v-if="request?.approval_notes">
                                        <Label class="text-sm font-medium text-gray-500">Approval Notes</Label>
                                        <div class="whitespace-pre-wrap">{{ request?.approval_notes }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Items Table -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-medium">Request Items</h3>
                                <div class="rounded-md border">
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead>Item Name</TableHead>
                                                <TableHead>Product</TableHead>
                                                <TableHead>Quantity</TableHead>
                                                <TableHead>Unit</TableHead>
                                                <TableHead>Unit Price</TableHead>
                                                <TableHead>Total Price</TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            <TableRow v-if="!request?.items || request.items.length === 0">
                                                <TableCell colspan="6" class="text-center py-8 text-gray-500">
                                                    No items found
                                                </TableCell>
                                            </TableRow>
                                            <TableRow v-else v-for="item in request.items" :key="item?.id">
                                                <TableCell>
                                                    <div>
                                                        <div class="font-medium">{{ item?.item_name }}</div>
                                                        <div v-if="item?.description" class="text-sm text-gray-500">
                                                            {{ item?.description }}
                                                        </div>
                                                        <div v-if="item?.specifications" class="text-sm text-gray-500">
                                                            {{ item?.specifications }}
                                                        </div>
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <div>{{ item?.product?.name || 'N/A' }}</div>
                                                </TableCell>
                                                <TableCell>
                                                    <div>{{ item?.quantity }}</div>
                                                </TableCell>
                                                <TableCell>
                                                    <div>{{ item?.unit }}</div>
                                                </TableCell>
                                                <TableCell>
                                                    <div>{{ formatCurrency(item?.estimated_unit_price) }}</div>
                                                </TableCell>
                                                <TableCell>
                                                    <div class="font-medium">{{
                                                        formatCurrency(item?.estimated_total_price) }}
                                                    </div>
                                                </TableCell>
                                            </TableRow>
                                        </TableBody>
                                    </Table>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div v-if="request?.status === 'draft'" class="flex justify-end gap-2">
                                <Button @click="submitRequest" :disabled="loading">
                                    <Send class="w-4 h-4 mr-2" />
                                    Submit Request
                                </Button>
                            </div>

                            <div v-if="request?.status === 'submitted' && canApprove" class="flex justify-end gap-2">
                                <Button @click="approveRequest" variant="outline" :disabled="loading">
                                    <Check class="w-4 h-4 mr-2" />
                                    Approve
                                </Button>
                                <Button @click="rejectRequest" variant="destructive" :disabled="loading">
                                    <X class="w-4 h-4 mr-2" />
                                    Reject
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
import { ArrowLeft, Edit, Loader2, Send, Check, X } from 'lucide-vue-next'
import { apiService } from '@/services/api'
import type { PurchaseRequest } from '@/types/erp'

interface Props {
    id: number
}

const props = defineProps<Props>()

const loading = ref(false)
const request = ref<PurchaseRequest | null>(null)

const canApprove = computed(() => {
    // Add logic to check if current user can approve
    return true
})

const fetchRequest = async () => {
    loading.value = true
    try {
        const response = await apiService.getPurchaseRequest(props.id)
        request.value = response
    } catch (error) {
        console.error('Error fetching purchase request:', error)
        request.value = null
    } finally {
        loading.value = false
    }
}

const submitRequest = async () => {
    if (!request.value) return

    loading.value = true
    try {
        await apiService.updatePurchaseRequest(request.value.id, {
            ...request.value,
            status: 'submitted'
        })
        await fetchRequest()
    } catch (error) {
        console.error('Error submitting request:', error)
        alert('Error submitting request. Please try again.')
    } finally {
        loading.value = false
    }
}

const approveRequest = async () => {
    if (!request.value) return

    const approvalNotes = prompt('Enter approval notes (optional):')

    loading.value = true
    try {
        await apiService.updatePurchaseRequest(request.value.id, {
            ...request.value,
            status: 'approved',
            approval_notes: approvalNotes
        })
        await fetchRequest()
    } catch (error) {
        console.error('Error approving request:', error)
        alert('Error approving request. Please try again.')
    } finally {
        loading.value = false
    }
}

const rejectRequest = async () => {
    if (!request.value) return

    const approvalNotes = prompt('Enter rejection reason:')
    if (!approvalNotes) return

    loading.value = true
    try {
        await apiService.updatePurchaseRequest(request.value.id, {
            ...request.value,
            status: 'rejected',
            approval_notes: approvalNotes
        })
        await fetchRequest()
    } catch (error) {
        console.error('Error rejecting request:', error)
        alert('Error rejecting request. Please try again.')
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
        rejected: 'destructive',
        cancelled: 'destructive'
    }
    return variants[status as keyof typeof variants] || 'default'
}

const getPriorityLabel = (priority: string): string => {
    const labels = {
        low: 'Low',
        medium: 'Medium',
        high: 'High',
        urgent: 'Urgent'
    }
    return labels[priority as keyof typeof labels] || priority
}

const getPriorityVariant = (priority: string): 'default' | 'secondary' | 'destructive' => {
    const variants = {
        low: 'secondary',
        medium: 'default',
        high: 'destructive',
        urgent: 'destructive'
    }
    return variants[priority as keyof typeof variants] || 'default'
}

onMounted(() => {
    fetchRequest()
})
</script>
