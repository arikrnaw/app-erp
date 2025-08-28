<template>

    <Head title="Bank Reconciliation Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 space-y-6">
            <!-- Header Section -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Bank Reconciliation Dashboard</h1>
                    <p class="text-muted-foreground mt-1">
                        Overview of bank reconciliation activities and status
                    </p>
                </div>
                <div class="flex items-center space-x-2">
                    <Button variant="outline" size="sm" @click="refreshData" :disabled="loading">
                        <RefreshCw class="h-4 w-4 mr-2" />
                        Refresh
                    </Button>
                    <Button @click="showNewReconciliationModal = true" variant="default">
                        <Plus class="h-4 w-4 mr-2" />
                        New Reconciliation
                    </Button>
                    <Button @click="showImportModal = true" variant="outline">
                        <Upload class="h-4 w-4 mr-2" />
                        Import Statement
                    </Button>
                </div>
            </div>

            <!-- Overview Cards -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center space-x-4">
                            <div class="p-2 bg-blue-100 dark:bg-blue-900/20 rounded-lg">
                                <Banknote class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-muted-foreground">Active Accounts</p>
                                <p class="text-2xl font-bold">{{ overview.active_accounts || 0 }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center space-x-4">
                            <div class="p-2 bg-green-100 dark:bg-green-900/20 rounded-lg">
                                <CheckCircle class="h-6 w-6 text-green-600 dark:text-green-400" />
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-muted-foreground">Completed</p>
                                <p class="text-2xl font-bold">{{ overview.reconciled_count || 0 }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center space-x-4">
                            <div class="p-2 bg-yellow-100 dark:bg-yellow-900/20 rounded-lg">
                                <Clock class="h-6 w-6 text-yellow-600 dark:text-yellow-400" />
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-muted-foreground">Pending</p>
                                <p class="text-2xl font-bold">{{ overview.pending_count || 0 }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center space-x-4">
                            <div class="p-2 bg-red-100 dark:bg-red-900/20 rounded-lg">
                                <AlertTriangle class="h-6 w-6 text-red-600 dark:text-red-400" />
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-muted-foreground">Discrepancies</p>
                                <p class="text-2xl font-bold">{{ overview.discrepancies_count || 0 }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Recent Reconciliations -->
            <Card>
                <CardHeader>
                    <CardTitle>Recent Reconciliations</CardTitle>
                    <CardDescription>Latest bank reconciliation activities</CardDescription>
                </CardHeader>
                <CardContent>
                    <div v-if="loading" class="flex justify-center py-8">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
                    </div>

                    <div v-else-if="recentReconciliations.length === 0" class="text-center py-8 text-muted-foreground">
                        No reconciliations found
                    </div>

                    <div v-else class="space-y-4">
                        <div v-for="reconciliation in recentReconciliations" :key="reconciliation.id"
                            class="flex items-center justify-between p-4 border rounded-lg hover:bg-muted/50 transition-colors">
                            <div class="flex-1">
                                <div class="flex items-center space-x-3">
                                    <Badge :variant="getStatusVariant(reconciliation.status)">
                                        {{ getStatusLabel(reconciliation.status) }}
                                    </Badge>
                                    <span class="font-medium">{{ reconciliation.bank_account_name }}</span>
                                </div>
                                <div class="text-sm text-muted-foreground mt-1">
                                    Period: {{ formatDate(reconciliation.period_start) }} - {{
                                        formatDate(reconciliation.period_end) }}
                                </div>
                            </div>

                            <div class="text-right">
                                <div class="text-lg font-semibold"
                                    :class="reconciliation.difference > 0 ? 'text-red-600' : 'text-green-600'">
                                    {{ formatCurrency(reconciliation.difference) }}
                                </div>
                                <div class="text-sm text-muted-foreground">
                                    {{ reconciliation.transactions_count }} transactions
                                </div>
                            </div>

                            <Button variant="ghost" size="sm" @click="viewReconciliation(reconciliation.id)">
                                <Eye class="h-4 w-4" />
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Quick Actions & Bank Accounts -->
            <div class="grid gap-6 md:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle>Quick Actions</CardTitle>
                        <CardDescription>Common reconciliation tasks</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <Button variant="outline" class="w-full justify-start"
                            @click="showNewReconciliationModal = true">
                            <Plus class="h-4 w-4 mr-2" />
                            Start New Reconciliation
                        </Button>
                        <Button variant="outline" class="w-full justify-start" @click="showImportModal = true">
                            <Upload class="h-4 w-4 mr-2" />
                            Import Bank Statement
                        </Button>
                        <Button variant="outline" class="w-full justify-start" @click="navigateToReports">
                            <BarChart3 class="h-4 w-4 mr-2" />
                            View Reports
                        </Button>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Bank Accounts</CardTitle>
                        <CardDescription>Manage your bank accounts</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div v-if="bankAccounts.length === 0" class="text-center py-4 text-muted-foreground">
                            No bank accounts found
                        </div>
                        <div v-else class="space-y-3">
                            <div v-for="account in bankAccounts.slice(0, 3)" :key="account.id"
                                class="flex items-center justify-between p-3 border rounded-lg">
                                <div>
                                    <div class="font-medium">{{ account.name }}</div>
                                    <div class="text-sm text-muted-foreground">{{ account.account_number }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="font-semibold">{{ formatCurrency(account.balance) }}</div>
                                    <Badge :variant="account.status === 'active' ? 'default' : 'secondary'">
                                        {{ account.status }}
                                    </Badge>
                                </div>
                            </div>

                            <Button variant="outline" class="w-full" @click="navigateToBankAccounts">
                                View All Accounts
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>
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
                        <Select v-model="newReconciliation.bank_account_id">
                            <SelectTrigger>
                                <SelectValue placeholder="Select account" />
                            </SelectTrigger>
                            <SelectContent>
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

        <!-- Import Statement Modal -->
        <Dialog v-model:open="showImportModal">
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>Import Bank Statement</DialogTitle>
                    <DialogDescription>Upload a bank statement file to start reconciliation</DialogDescription>
                </DialogHeader>
                <div class="space-y-4">
                    <div class="space-y-2">
                        <Label for="import_account">Bank Account</Label>
                        <Select v-model="importData.bank_account_id">
                            <SelectTrigger>
                                <SelectValue placeholder="Select account" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="account in bankAccounts" :key="account.id" :value="account.id">
                                    {{ account.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="space-y-2">
                        <Label for="statement_file">Statement File</Label>
                        <Input id="statement_file" type="file" accept=".csv,.xlsx,.pdf" @change="handleFileUpload" />
                        <p class="text-xs text-muted-foreground">Supported formats: CSV, Excel, PDF</p>
                    </div>

                    <div class="space-y-2">
                        <Label for="statement_date">Statement Date</Label>
                        <Input id="statement_date" type="date" v-model="importData.statement_date" required />
                    </div>

                    <DialogFooter>
                        <Button type="button" variant="outline" @click="showImportModal = false">
                            Cancel
                        </Button>
                        <Button @click="importStatement" :disabled="importing">
                            {{ importing ? 'Importing...' : 'Import Statement' }}
                        </Button>
                    </DialogFooter>
                </div>
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
import {
    Plus,
    Upload,
    Eye,
    Banknote,
    CheckCircle,
    Clock,
    AlertTriangle,
    BarChart3,
    RefreshCw
} from 'lucide-vue-next'

// Breadcrumbs
const breadcrumbs = [
    { title: 'Finance', href: '/finance' },
    { title: 'Bank Reconciliation', href: '/finance/bank-reconciliation' },
    { title: 'Dashboard', href: '/finance/bank-reconciliation/dashboard' }
]

// API
const { get, post } = useApi()

// Reactive data
const loading = ref(false)
const creating = ref(false)
const importing = ref(false)
const showNewReconciliationModal = ref(false)
const showImportModal = ref(false)

// Data
const overview = ref({
    active_accounts: 0,
    reconciled_count: 0,
    pending_count: 0,
    discrepancies_count: 0
})

const recentReconciliations = ref<any[]>([])
const bankAccounts = ref<any[]>([])

// Form data
const newReconciliation = ref({
    bank_account_id: '',
    period_start: '',
    period_end: '',
    notes: ''
})

const importData = ref({
    bank_account_id: '',
    statement_date: '',
    file: null as File | null
})

// Computed
const canCreateReconciliation = computed(() => {
    return newReconciliation.value.bank_account_id &&
        newReconciliation.value.period_start &&
        newReconciliation.value.period_end
})

// Methods
const fetchDashboardData = async () => {
    try {
        loading.value = true

        // Fetch overview data
        try {
            const overviewResponse = await get('/api/finance/bank-reconciliation/dashboard')
            if (overviewResponse.data?.success) {
                overview.value = overviewResponse.data.data
            }
        } catch (error) {
            // Set default values if API fails
            overview.value = {
                active_accounts: 0,
                reconciled_count: 0,
                pending_count: 0,
                discrepancies_count: 0
            }
        }

        // Fetch recent reconciliations
        try {
            const recentResponse = await get('/api/finance/bank-reconciliation/recent')
            if (recentResponse.data?.success) {
                recentReconciliations.value = recentResponse.data.data
            }
        } catch (error) {
            // Set empty array if API fails
            recentReconciliations.value = []
        }

        // Fetch bank accounts for dashboard
        try {
            const accountsResponse = await get('/api/finance/bank-reconciliation/bank-accounts/dashboard')
            if (accountsResponse.data?.success) {
                bankAccounts.value = accountsResponse.data.data
            }
        } catch (error) {
            // Set empty array if API fails
            bankAccounts.value = []
        }

    } catch (error) {
        // Handle any unexpected errors silently
    } finally {
        loading.value = false
    }
}

const refreshData = () => {
    fetchDashboardData()
}

const createReconciliation = async () => {
    if (!canCreateReconciliation.value) return

    try {
        creating.value = true

        const response = await post('/api/finance/bank-reconciliation', newReconciliation.value)

        if (response.data?.success) {
            showNewReconciliationModal.value = false
            await fetchDashboardData()

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

const importStatement = async () => {
    if (!importData.value.bank_account_id || !importData.value.statement_date || !importData.value.file) {
        return
    }

    try {
        importing.value = true

        const formData = new FormData()
        formData.append('bank_account_id', importData.value.bank_account_id)
        formData.append('statement_date', importData.value.statement_date)
        formData.append('file', importData.value.file)

        const response = await post('/api/finance/bank-reconciliation/import/statement', formData)

        if (response.data?.success) {
            showImportModal.value = false
            await fetchDashboardData()

            // Reset form
            importData.value = {
                bank_account_id: '',
                statement_date: '',
                file: null
            }
        }
    } catch (error) {
        console.error('Error importing statement:', error)
    } finally {
        importing.value = false
    }
}

const handleFileUpload = (event: Event) => {
    const target = event.target as HTMLInputElement
    if (target.files && target.files[0]) {
        importData.value.file = target.files[0]
    }
}

const viewReconciliation = (id: number) => {
    router.visit(`/finance/bank-reconciliation/reconciliation/${id}`)
}

const navigateToBankAccounts = () => {
    router.visit('/finance/bank-reconciliation/bank-accounts')
}

const navigateToReports = () => {
    router.visit('/finance/bank-reconciliation/reports')
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
    fetchDashboardData()
})
</script>
