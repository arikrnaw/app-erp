<template>

    <Head title="Reconciliation Adjustments" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 space-y-6">
            <!-- Header Section -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Reconciliation Adjustments</h1>
                    <p class="text-muted-foreground mt-1">
                        Manage bank charges, interest, and service fees
                    </p>
                </div>
                <div class="flex items-center space-x-2">
                    <Button @click="showCreateModal = true">
                        <Plus class="h-4 w-4 mr-2" />
                        New Adjustment
                    </Button>
                </div>
            </div>

            <!-- Filter Tabs -->
            <div>
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex space-x-8">
                        <button v-for="tab in tabs" :key="tab.id" @click="activeTab = tab.id" :class="[
                            activeTab === tab.id
                                ? 'border-primary text-primary bg-primary/5'
                                : 'border-transparent text-muted-foreground hover:text-foreground hover:border-border hover:bg-muted/50',
                            'whitespace-nowrap py-2 px-3 border-b-2 font-medium text-sm rounded-t-lg transition-all duration-200'
                        ]">
                            {{ tab.name }}
                            <Badge :variant="activeTab === tab.id ? 'default' : 'secondary'" class="ml-2">
                                {{ tab.count }}
                            </Badge>
                        </button>
                    </nav>
                </div>
            </div>

            <!-- Adjustments List -->
            <Card>
                <CardHeader>
                    <CardTitle>{{ activeTabName }} Adjustments</CardTitle>
                    <CardDescription>{{ activeTabDescription }}</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <div v-for="adjustment in filteredAdjustments" :key="adjustment.id"
                            class="p-4 border rounded-lg hover:bg-muted/50 transition-colors">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-3">
                                        <div class="flex-shrink-0">
                                            <div :class="[
                                                'w-10 h-10 rounded-full flex items-center justify-center',
                                                getAdjustmentIconBg(adjustment.type)
                                            ]">
                                                <component :is="getAdjustmentIcon(adjustment.type)"
                                                    :class="getAdjustmentIconColor(adjustment.type)" />
                                            </div>
                                        </div>
                                        <div>
                                            <h4 class="text-sm font-medium text-foreground">{{ adjustment.description }}
                                            </h4>
                                            <p class="text-sm text-muted-foreground">{{ adjustment.date }} - {{
                                                adjustment.reference }}</p>
                                            <div class="flex items-center space-x-2 mt-1">
                                                <Badge :variant="getAdjustmentVariant(adjustment.type)">
                                                    {{ adjustment.type.replace('_', ' ').toUpperCase() }}
                                                </Badge>
                                                <Badge v-if="adjustment.approved" variant="default">Approved</Badge>
                                                <Badge v-else variant="secondary">Pending</Badge>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex flex-col items-end space-y-2">
                                    <div class="text-right">
                                        <p class="text-lg font-semibold" :class="getAmountColor(adjustment.amount)">
                                            {{ formatCurrency(adjustment.amount) }}
                                        </p>
                                        <p class="text-sm text-muted-foreground">{{ adjustment.bank_account_name }}</p>
                                    </div>

                                    <div class="flex space-x-2">
                                        <Button @click="editAdjustment(adjustment)" size="sm" variant="outline">
                                            <Edit3 class="h-4 w-4" />
                                        </Button>
                                        <Button @click="deleteAdjustment(adjustment.id)" size="sm"
                                            variant="destructive">
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="filteredAdjustments.length === 0" class="text-center py-8">
                            <div class="text-muted-foreground">
                                <FileText class="mx-auto h-12 w-12 text-muted-foreground" />
                                <h3 class="mt-2 text-sm font-medium text-foreground">No adjustments found</h3>
                                <p class="mt-1 text-sm text-muted-foreground">Get started by creating a new adjustment.
                                </p>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Create/Edit Modal -->
        <Dialog v-model:open="showCreateModal">
            <DialogContent class="max-w-md">
                <DialogHeader>
                    <DialogTitle>{{ editingAdjustment ? 'Edit' : 'New' }} Adjustment</DialogTitle>
                    <DialogDescription>
                        {{ editingAdjustment ? 'Update' : 'Create' }} a reconciliation adjustment
                    </DialogDescription>
                </DialogHeader>

                <form @submit.prevent="saveAdjustment" class="space-y-4">
                    <div>
                        <Label for="type">Adjustment Type</Label>
                        <Select v-model="form.type" required>
                            <SelectTrigger class="w-full">
                                <SelectValue placeholder="Select type" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="bank_charge">Bank Charge</SelectItem>
                                <SelectItem value="interest_earned">Interest Earned</SelectItem>
                                <SelectItem value="service_fee">Service Fee</SelectItem>
                                <SelectItem value="other">Other</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div>
                        <Label for="description">Description</Label>
                        <Input id="description" v-model="form.description" required />
                    </div>

                    <div>
                        <Label for="amount">Amount</Label>
                        <Input id="amount" v-model="form.amount" type="number" step="0.01" required />
                    </div>

                    <div>
                        <Label for="date">Date</Label>
                        <Input id="date" v-model="form.date" type="date" required />
                    </div>

                    <div>
                        <Label for="reference">Reference</Label>
                        <Input id="reference" v-model="form.reference" />
                    </div>

                    <div>
                        <Label for="bank_account_id">Bank Account</Label>
                        <Select v-model="form.bank_account_id" required>
                            <SelectTrigger class="w-full">
                                <SelectValue placeholder="Select bank account" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="account in bankAccounts" :key="account.id" :value="account.id">
                                    {{ account.name }} - {{ account.account_number }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div>
                        <Label for="notes">Notes</Label>
                        <Textarea id="notes" v-model="form.notes" rows="3" />
                    </div>
                </form>

                <DialogFooter>
                    <Button variant="outline" @click="closeModal">Cancel</Button>
                    <Button @click="saveAdjustment" :loading="saving">
                        {{ editingAdjustment ? 'Update' : 'Create' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Textarea } from '@/components/ui/textarea'
import { Plus, Edit3, Trash2, FileText, CreditCard, TrendingUp, AlertTriangle } from 'lucide-vue-next'
import { useApi } from '@/composables/useApi'
import AppLayout from '@/layouts/AppLayout.vue'

// Breadcrumbs
const breadcrumbs = [
    { title: 'Finance', href: '/finance' },
    { title: 'Bank Reconciliation', href: '/finance/bank-reconciliation' },
    { title: 'Adjustments', href: '/finance/bank-reconciliation/adjustments' }
]

const api = useApi()

// State
const showCreateModal = ref(false)
const editingAdjustment = ref<any | null>(null)
const saving = ref(false)
const activeTab = ref('all')

// Data
const adjustments = ref<any[]>([])
const bankAccounts = ref<any[]>([])

// Form
const form = ref({
    type: '',
    description: '',
    amount: '',
    date: '',
    reference: '',
    bank_account_id: '',
    notes: ''
})

// Tabs
const tabs = computed(() => [
    { id: 'all', name: 'All Adjustments', count: adjustments.value.length },
    { id: 'bank_charge', name: 'Bank Charges', count: adjustments.value.filter((a: any) => a.type === 'bank_charge').length },
    { id: 'interest_earned', name: 'Interest Earned', count: adjustments.value.filter((a: any) => a.type === 'interest_earned').length },
    { id: 'service_fee', name: 'Service Fees', count: adjustments.value.filter((a: any) => a.type === 'service_fee').length },
    { id: 'other', name: 'Other', count: adjustments.value.filter((a: any) => a.type === 'other').length }
])

const activeTabName = computed(() => {
    const tab = tabs.value.find(t => t.id === activeTab.value)
    return tab ? tab.name : 'All Adjustments'
})

const activeTabDescription = computed(() => {
    switch (activeTab.value) {
        case 'bank_charge': return 'Bank charges and fees from your bank'
        case 'interest_earned': return 'Interest earned on your bank accounts'
        case 'service_fee': return 'Service fees and maintenance charges'
        case 'other': return 'Other miscellaneous adjustments'
        default: return 'All reconciliation adjustments'
    }
})

const filteredAdjustments = computed(() => {
    if (activeTab.value === 'all') return adjustments.value
    return adjustments.value.filter((a: any) => a.type === activeTab.value)
})

// Methods
const fetchData = async () => {
    try {
        const [adjustmentsResponse, accountsResponse] = await Promise.all([
            api.get('/api/finance/bank-reconciliation/adjustments'),
            api.get('/api/finance/bank-reconciliation/bank-accounts')
        ])

        if (adjustmentsResponse.data?.success) {
            adjustments.value = adjustmentsResponse.data.data
        }
        if (accountsResponse.data?.success) {
            bankAccounts.value = accountsResponse.data.data
        }
    } catch (error) {
        console.error('Error fetching data:', error)
    }
}

const getAdjustmentIcon = (type: string) => {
    switch (type) {
        case 'bank_charge': return CreditCard
        case 'interest_earned': return TrendingUp
        case 'service_fee': return AlertTriangle
        default: return FileText
    }
}

const getAdjustmentIconBg = (type: string) => {
    switch (type) {
        case 'bank_charge': return 'bg-destructive/10'
        case 'interest_earned': return 'bg-green-500/10'
        case 'service_fee': return 'bg-yellow-500/10'
        default: return 'bg-muted'
    }
}

const getAdjustmentIconColor = (type: string) => {
    switch (type) {
        case 'bank_charge': return 'text-destructive'
        case 'interest_earned': return 'text-green-600'
        case 'service_fee': return 'text-yellow-600'
        default: return 'text-muted-foreground'
    }
}

const getAdjustmentVariant = (type: string) => {
    switch (type) {
        case 'bank_charge': return 'destructive'
        case 'interest_earned': return 'default'
        case 'service_fee': return 'secondary'
        default: return 'outline'
    }
}

const editAdjustment = (adjustment: any) => {
    editingAdjustment.value = adjustment
    form.value = { ...adjustment }
    showCreateModal.value = true
}

const closeModal = () => {
    showCreateModal.value = false
    editingAdjustment.value = null
    form.value = {
        type: '',
        description: '',
        amount: '',
        date: '',
        reference: '',
        bank_account_id: '',
        notes: ''
    }
}

const saveAdjustment = async () => {
    saving.value = true

    try {
        if (editingAdjustment.value) {
            await api.put(`/api/finance/bank-reconciliation/adjustments/${editingAdjustment.value.id}`, form.value)
        } else {
            await api.post('/api/finance/bank-reconciliation/adjustments', form.value)
        }

        await fetchData()
        closeModal()
    } catch (error) {
        console.error('Error saving adjustment:', error)
    } finally {
        saving.value = false
    }
}

const deleteAdjustment = async (id: number) => {
    if (!confirm('Are you sure you want to delete this adjustment?')) return

    try {
        await api.delete(`/api/finance/bank-reconciliation/adjustments/${id}`)
        await fetchData()
    } catch (error) {
        console.error('Error deleting adjustment:', error)
    }
}

const formatCurrency = (amount: number | null | undefined) => {
    // Handle null, undefined, or NaN values
    if (amount === null || amount === undefined || isNaN(amount)) {
        return 'Rp 0'
    }

    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(amount)
}

const getAmountColor = (amount: number | null | undefined) => {
    if (amount === null || amount === undefined || isNaN(amount)) {
        return 'text-muted-foreground'
    }
    return amount > 0 ? 'text-green-600' : 'text-destructive'
}

// Lifecycle
onMounted(() => {
    fetchData()
})
</script>
