<template>

    <Head title="Bank Reconciliations" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 space-y-6">
            <!-- Header Section -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Bank Reconciliations</h1>
                    <p class="text-muted-foreground mt-1">
                        Manage bank reconciliation processes and status
                    </p>
                </div>
                <div class="flex items-center space-x-2">
                    <Button @click="showNewReconciliationModal = true" variant="default">
                        <Plus class="h-4 w-4 mr-2" />
                        New Reconciliation
                    </Button>
                    <Button @click="showBulkActionModal = true" variant="outline">
                        <Settings class="h-4 w-4 mr-2" />
                        Bulk Actions
                    </Button>
                    <Button @click="exportReconciliations" variant="outline">
                        <Download class="h-4 w-4 mr-2" />
                        Export
                    </Button>
                </div>
            </div>

            <!-- Filters -->
            <Card>
                <CardHeader>
                    <CardTitle>Filters</CardTitle>
                    <CardDescription>Filter reconciliations by various criteria</CardDescription>
                </CardHeader>
                <CardContent class="mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="space-y-2">
                            <Label for="bank_account">Bank Account</Label>
                            <Select v-model="filters.bank_account_id">
                                <SelectTrigger class="w-full">
                                    <SelectValue placeholder="All accounts" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All accounts</SelectItem>
                                    <SelectItem v-for="account in bankAccounts" :key="account.id" :value="account.id">
                                        {{ account.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="space-y-2">
                            <Label for="status">Status</Label>
                            <Select v-model="filters.status">
                                <SelectTrigger class="w-full">
                                    <SelectValue placeholder="All statuses" />
                                </SelectTrigger>
                                <SelectContent class="w-full">
                                    <SelectItem value="all">All statuses</SelectItem>
                                    <SelectItem value="pending">Pending</SelectItem>
                                    <SelectItem value="in_progress">In Progress</SelectItem>
                                    <SelectItem value="completed">Completed</SelectItem>
                                    <SelectItem value="cancelled">Cancelled</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="space-y-2">
                            <Label for="period_start">Start Date</Label>
                            <Input id="period_start" type="date" v-model="filters.period_start" />
                        </div>

                        <div class="space-y-2">
                            <Label for="period_end">End Date</Label>
                            <Input id="period_end" type="date" v-model="filters.period_end" />
                        </div>
                    </div>

                    <div class="flex justify-between items-center mt-4">
                        <Button @click="applyFilters" variant="default">
                            <Search class="h-4 w-4 mr-2" />
                            Apply Filters
                        </Button>
                        <Button @click="clearFilters" variant="outline">
                            Clear Filters
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <!-- Reconciliations Table -->
            <Card>
                <CardHeader>
                    <CardTitle>Reconciliations</CardTitle>
                    <CardDescription>
                        {{ reconciliations.length }} reconciliations found
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div v-if="loading" class="flex justify-center py-8">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
                    </div>

                    <div v-else-if="reconciliations.length === 0" class="text-center py-8 text-muted-foreground">
                        No reconciliations found
                    </div>

                    <div v-else class="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Bank Account</TableHead>
                                    <TableHead>Period</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead>Difference</TableHead>
                                    <TableHead>Created</TableHead>
                                    <TableHead>Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="reconciliation in reconciliations" :key="reconciliation.id"
                                    class="hover:bg-muted/50 transition-colors">
                                    <TableCell class="font-medium">
                                        {{ reconciliation.bank_account?.name }}
                                    </TableCell>
                                    <TableCell>
                                        {{ formatDate(reconciliation.period_start) }} - {{
                                            formatDate(reconciliation.period_end) }}
                                    </TableCell>
                                    <TableCell>
                                        <Badge :variant="getStatusVariant(reconciliation.status)">
                                            {{ getStatusLabel(reconciliation.status) }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        <span
                                            :class="reconciliation.difference > 0 ? 'text-red-600' : 'text-green-600'">
                                            {{ formatCurrency(reconciliation.difference) }}
                                        </span>
                                    </TableCell>
                                    <TableCell>
                                        {{ formatDate(reconciliation.created_at) }}
                                    </TableCell>
                                    <TableCell>
                                        <DropdownMenu>
                                            <DropdownMenuTrigger asChild>
                                                <Button variant="ghost" size="sm">
                                                    <MoreHorizontal class="h-4 w-4" />
                                                </Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent>
                                                <DropdownMenuItem @click="viewReconciliation(reconciliation.id)">
                                                    <Eye class="h-4 w-4 mr-2" />
                                                    View
                                                </DropdownMenuItem>
                                                <DropdownMenuItem @click="editReconciliation(reconciliation.id)">
                                                    <Edit class="h-4 w-4 mr-2" />
                                                    Edit
                                                </DropdownMenuItem>
                                                <DropdownMenuItem v-if="reconciliation.status === 'in_progress'"
                                                    @click="completeReconciliation(reconciliation.id)">
                                                    <CheckCircle class="h-4 w-4 mr-2" />
                                                    Complete
                                                </DropdownMenuItem>
                                                <DropdownMenuSeparator />
                                                <DropdownMenuItem v-if="reconciliation.status !== 'completed'"
                                                    @click="deleteReconciliation(reconciliation.id)"
                                                    class="text-red-600">
                                                    <Trash2 class="h-4 w-4 mr-2" />
                                                    Delete
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- New Reconciliation Modal -->
        <Dialog v-model:open="showNewReconciliationModal">
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>New Reconciliation</DialogTitle>
                    <DialogDescription>Start a new bank reconciliation process</DialogDescription>
                </DialogHeader>
                <form @submit.prevent="createReconciliation" class="space-y-4">
                    <div class="space-y-2">
                        <Label for="bank_account">Bank Account</Label>
                        <Select v-model="newReconciliation.bank_account_id" required>
                            <SelectTrigger class="w-full">
                                <SelectValue placeholder="Select account" />
                            </SelectTrigger>
                            <SelectContent class="w-full">
                                <SelectItem v-for="account in bankAccounts" :key="account.id" :value="account.id">
                                    {{ account.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label for="period_start">Start Date</Label>
                            <Input id="period_start" type="date" v-model="newReconciliation.period_start" required />
                        </div>
                        <div class="space-y-2">
                            <Label for="period_end">End Date</Label>
                            <Input id="period_end" type="date" v-model="newReconciliation.period_end" required />
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label for="notes">Notes (Optional)</Label>
                        <Textarea id="notes" v-model="newReconciliation.notes"
                            placeholder="Add any notes about this reconciliation..." />
                    </div>

                    <DialogFooter>
                        <Button type="button" variant="outline" @click="showNewReconciliationModal = false">
                            Cancel
                        </Button>
                        <Button type="submit" :disabled="creating">
                            {{ creating ? 'Creating...' : 'Create Reconciliation' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Bulk Actions Modal -->
        <Dialog v-model:open="showBulkActionModal">
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>Bulk Actions</DialogTitle>
                    <DialogDescription>Perform actions on multiple reconciliations</DialogDescription>
                </DialogHeader>

                <div class="space-y-4">
                    <div class="space-y-2">
                        <Label>Select Action</Label>
                        <Select v-model="bulkAction.action">
                            <SelectTrigger class="w-full">
                                <SelectValue placeholder="Choose action" />
                            </SelectTrigger>
                            <SelectContent class="w-full">
                                <SelectItem value="complete">Complete Selected</SelectItem>
                                <SelectItem value="export">Export Selected</SelectItem>
                                <SelectItem value="delete">Delete Selected</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="space-y-2">
                        <Label>Select Reconciliations</Label>
                        <div class="space-y-2 max-h-40 overflow-y-auto">
                            <div v-for="reconciliation in reconciliations" :key="reconciliation.id"
                                class="flex items-center space-x-2">
                                <input type="checkbox" :id="`reconciliation-${reconciliation.id}`"
                                    :value="reconciliation.id" v-model="bulkAction.selectedIds" />
                                <label :for="`reconciliation-${reconciliation.id}`" class="text-sm">
                                    {{ reconciliation.bank_account?.name }} - {{ formatDate(reconciliation.period_start)
                                    }}
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="showBulkActionModal = false">
                        Cancel
                    </Button>
                    <Button @click="performBulkAction"
                        :disabled="!bulkAction.action || bulkAction.selectedIds.length === 0">
                        Perform Action
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import { router } from '@inertiajs/vue3'
import { useApi } from '@/composables/useApi'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Textarea } from '@/components/ui/textarea'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'

import {
    Plus,
    Download,
    Search,
    Eye,
    Edit,
    CheckCircle,
    Trash2,
    MoreHorizontal,
    Settings
} from 'lucide-vue-next'

// Breadcrumbs
const breadcrumbs = [
    { title: 'Finance', href: '/finance' },
    { title: 'Bank Reconciliation', href: '/finance/bank-reconciliation' },
    { title: 'Reconciliations', href: '/finance/bank-reconciliation/reconciliations' }
]

// API
const { get, post, put, delete: del } = useApi()

// Reactive data
const loading = ref(false)
const creating = ref(false)
const showNewReconciliationModal = ref(false)
const showBulkActionModal = ref(false)

// Data
const reconciliations = ref<any[]>([])
const bankAccounts = ref<any[]>([])

// Filters
const filters = ref({
    bank_account_id: 'all',
    status: 'all',
    period_start: '',
    period_end: ''
})

// Form data
const newReconciliation = ref({
    bank_account_id: '',
    period_start: '',
    period_end: '',
    notes: ''
})

// Bulk actions
const bulkAction = ref({
    action: '',
    selectedIds: []
})

// Methods
const fetchReconciliations = async () => {
    try {
        loading.value = true

        const params = new URLSearchParams()
        if (filters.value.bank_account_id && filters.value.bank_account_id !== 'all') {
            params.append('bank_account_id', filters.value.bank_account_id)
        }
        if (filters.value.status && filters.value.status !== 'all') {
            params.append('status', filters.value.status)
        }
        if (filters.value.period_start) params.append('period_start', filters.value.period_start)
        if (filters.value.period_end) params.append('period_end', filters.value.period_end)

        const response = await get(`/api/finance/bank-reconciliation?${params.toString()}`)
        if (response.data?.success) {
            reconciliations.value = response.data.data
        }
    } catch (error) {
        console.error('Error fetching reconciliations:', error)
    } finally {
        loading.value = false
    }
}

const fetchBankAccounts = async () => {
    try {
        const response = await get('/api/bank/accounts')
        if (response.data?.data) {
            bankAccounts.value = response.data.data
        }
    } catch (error) {
        console.error('Error fetching bank accounts:', error)
    }
}

const applyFilters = () => {
    fetchReconciliations()
}

const clearFilters = () => {
    filters.value = {
        bank_account_id: 'all',
        status: 'all',
        period_start: '',
        period_end: ''
    }
    fetchReconciliations()
}

const createReconciliation = async () => {
    try {
        creating.value = true

        const response = await post('/api/finance/bank-reconciliation', newReconciliation.value)

        if (response.data) {
            showNewReconciliationModal.value = false
            await fetchReconciliations()

            // Reset form
            newReconciliation.value = {
                bank_account_id: '',
                period_start: '',
                period_end: '',
                notes: ''
            }
        }
    } catch (error) {
        console.error('Error creating reconciliation:', error)
    } finally {
        creating.value = false
    }
}

const viewReconciliation = (id: number) => {
    router.visit(`/finance/bank-reconciliation/reconciliation/${id}`)
}

const editReconciliation = (id: number) => {
    router.visit(`/finance/bank-reconciliation/reconciliation/${id}/edit`)
}

const completeReconciliation = async (id: number) => {
    try {
        const response = await post(`/api/finance/bank-reconciliation/${id}/complete`, {
            reconciliation_date: new Date().toISOString().split('T')[0]
        })

        if (response.data) {
            await fetchReconciliations()
        }
    } catch (error) {
        console.error('Error completing reconciliation:', error)
    }
}

const deleteReconciliation = async (id: number) => {
    if (!confirm('Are you sure you want to delete this reconciliation?')) return

    try {
        const response = await del(`/api/finance/bank-reconciliation/${id}`)

        if (response.data) {
            await fetchReconciliations()
        }
    } catch (error) {
        console.error('Error deleting reconciliation:', error)
    }
}

const exportReconciliations = () => {
    // TODO: Implement export functionality
    console.log('Export reconciliations')
}

const performBulkAction = async () => {
    if (!bulkAction.value.action || bulkAction.value.selectedIds.length === 0) return

    try {
        switch (bulkAction.value.action) {
            case 'complete':
                await Promise.all(
                    bulkAction.value.selectedIds.map(id =>
                        post(`/api/finance/bank-reconciliation/${id}/complete`, {
                            reconciliation_date: new Date().toISOString().split('T')[0]
                        })
                    )
                )
                break
            case 'export':
                // TODO: Implement bulk export
                console.log('Bulk export:', bulkAction.value.selectedIds)
                break
            case 'delete':
                if (confirm(`Are you sure you want to delete ${bulkAction.value.selectedIds.length} reconciliations?`)) {
                    await Promise.all(
                        bulkAction.value.selectedIds.map(id =>
                            del(`/api/finance/bank-reconciliation/${id}`)
                        )
                    )
                }
                break
        }

        // Refresh data and close modal
        await fetchReconciliations()
        showBulkActionModal.value = false

        // Reset bulk action
        bulkAction.value = {
            action: '',
            selectedIds: []
        }

    } catch (error) {
        console.error('Error performing bulk action:', error)
    }
}

const getStatusVariant = (status: string) => {
    switch (status) {
        case 'completed': return 'default'
        case 'in_progress': return 'secondary'
        case 'pending': return 'outline'
        case 'cancelled': return 'destructive'
        default: return 'outline'
    }
}

const getStatusLabel = (status: string) => {
    switch (status) {
        case 'completed': return 'Completed'
        case 'in_progress': return 'In Progress'
        case 'pending': return 'Pending'
        case 'cancelled': return 'Cancelled'
        default: return status
    }
}

const formatDate = (dateString: string) => {
    if (!dateString) return '-'
    return new Date(dateString).toLocaleDateString()
}

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(amount)
}

// Lifecycle
onMounted(() => {
    fetchReconciliations()
    fetchBankAccounts()
})
</script>
