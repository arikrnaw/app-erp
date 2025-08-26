<template>

    <Head title="Bank Accounts" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="font-semibold text-xl leading-tight text-foreground">
                    Bank Accounts
                </h2>
                <Button @click="openCreateDialog">
                    <Plus class="w-4 h-4 mr-2" />
                    Add Bank Account
                </Button>
            </div>

            <div class="overflow-hidden shadow-sm sm:rounded-lg border border-border bg-card">
                <div class="p-6">
                    <!-- Search and Filters -->
                    <div class="flex flex-col sm:flex-row gap-4 mb-6">
                        <div class="flex-1">
                            <Input v-model="searchQuery" placeholder="Search bank accounts..." class="w-full"
                                @input="debouncedSearch" />
                        </div>
                        <div class="flex gap-2">
                            <Select v-model="statusFilter">
                                <SelectTrigger class="w-[180px]">
                                    <SelectValue placeholder="Status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All Status</SelectItem>
                                    <SelectItem value="true">Active</SelectItem>
                                    <SelectItem value="false">Inactive</SelectItem>
                                </SelectContent>
                            </Select>
                            <Select v-model="typeFilter">
                                <SelectTrigger class="w-[180px]">
                                    <SelectValue placeholder="Account Type" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All Types</SelectItem>
                                    <SelectItem value="checking">Checking</SelectItem>
                                    <SelectItem value="savings">Savings</SelectItem>
                                    <SelectItem value="credit">Credit</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>

                    <!-- Bank Accounts Table -->
                    <div class="rounded-md border border-border">
                        <Table>
                            <TableHeader>
                                <TableRow class="bg-muted/50">
                                    <TableHead class="text-muted-foreground font-medium">Account Number</TableHead>
                                    <TableHead class="text-muted-foreground font-medium">Account Name</TableHead>
                                    <TableHead class="text-muted-foreground font-medium">Bank Name</TableHead>
                                    <TableHead class="text-muted-foreground font-medium">Type</TableHead>
                                    <TableHead class="text-muted-foreground font-medium">Current Balance</TableHead>
                                    <TableHead class="text-muted-foreground font-medium">Status</TableHead>
                                    <TableHead class="text-right text-muted-foreground font-medium">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-if="loading">
                                    <TableCell colspan="7" class="text-center py-8">
                                        <div class="flex items-center justify-center text-muted-foreground">
                                            <Loader2 class="w-6 h-6 animate-spin mr-2" />
                                            Loading...
                                        </div>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-else-if="bankAccounts.length === 0">
                                    <TableCell colspan="7" class="text-center py-8 text-muted-foreground">
                                        No bank accounts found
                                    </TableCell>
                                </TableRow>
                                <TableRow v-else v-for="account in bankAccounts.filter((ba: any) => ba && ba.id)"
                                    :key="account.id" class="hover:bg-muted/50">
                                    <TableCell>
                                        <div class="font-mono text-sm text-foreground">{{ account.account_number }}
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <div class="font-medium text-foreground">{{ account.account_name }}</div>
                                    </TableCell>
                                    <TableCell>
                                        <div class="text-sm text-muted-foreground">{{ account.bank_name }}</div>
                                    </TableCell>
                                    <TableCell>
                                        <Badge :variant="getTypeVariant(account.account_type)">
                                            {{ getTypeLabel(account.account_type) }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        <div class="text-sm font-mono font-medium"
                                            :class="account.current_balance >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
                                            {{ formatCurrency(account.current_balance || 0) }}
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <Badge :variant="account.is_active ? 'default' : 'secondary'">
                                            {{ account.is_active ? 'Active' : 'Inactive' }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <DropdownMenu>
                                            <DropdownMenuTrigger as-child>
                                                <Button variant="ghost" class="h-8 w-8 p-0 hover:bg-muted">
                                                    <MoreHorizontal class="h-4 w-4" />
                                                </Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end" class="bg-popover border-border">
                                                <DropdownMenuItem @click="openShowDialog(account)"
                                                    class="hover:bg-accent">
                                                    <Eye class="w-4 h-4 mr-2" />
                                                    View
                                                </DropdownMenuItem>
                                                <DropdownMenuItem @click="openEditDialog(account)"
                                                    class="hover:bg-accent">
                                                    <Edit class="w-4 h-4 mr-2" />
                                                    Edit
                                                </DropdownMenuItem>
                                                <DropdownMenuSeparator class="bg-border" />
                                                <DropdownMenuItem @click="deleteBankAccount(account.id)"
                                                    class="text-destructive hover:bg-destructive/10">
                                                    <Trash2 class="w-4 h-4 mr-2" />
                                                    Delete
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="pagination && pagination.meta && pagination.meta.last_page > 1"
                        class="flex items-center justify-between mt-6">
                        <div class="text-sm text-muted-foreground">
                            Showing {{ pagination.meta.from }} to {{ pagination.meta.to }} of {{ pagination.meta.total
                            }}
                            results
                        </div>
                        <div class="flex gap-2">
                            <Button variant="outline" size="sm" :disabled="pagination.meta.current_page === 1"
                                @click="changePage(pagination.meta.current_page - 1)">
                                Previous
                            </Button>
                            <Button variant="outline" size="sm"
                                :disabled="pagination.meta.current_page === pagination.meta.last_page"
                                @click="changePage(pagination.meta.current_page + 1)">
                                Next
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Bank Account Dialog -->
        <Dialog :open="showCreateDialog" @update:open="showCreateDialog = $event">
            <DialogContent class="sm:max-w-[600px] bg-card border-border">
                <DialogHeader>
                    <DialogTitle class="text-foreground">Create New Bank Account</DialogTitle>
                    <DialogDescription class="text-muted-foreground">
                        Create a new bank account for your business operations
                    </DialogDescription>
                </DialogHeader>

                <form @submit.prevent="submitForm" class="space-y-4">
                    <!-- Account Number -->
                    <div class="space-y-2">
                        <Label for="account_number" class="text-foreground">Account Number *</Label>
                        <Input id="account_number" v-model="form.account_number" placeholder="e.g., 1234567890"
                            :class="{ 'border-destructive ring-destructive': errors.account_number }" required />
                        <p v-if="errors.account_number" class="text-sm text-destructive">{{ errors.account_number }}</p>
                    </div>

                    <!-- Account Name -->
                    <div class="space-y-2">
                        <Label for="account_name" class="text-foreground">Account Name *</Label>
                        <Input id="account_name" v-model="form.account_name" placeholder="e.g., Main Business Account"
                            :class="{ 'border-destructive ring-destructive': errors.account_name }" required />
                        <p v-if="errors.account_name" class="text-sm text-destructive">{{ errors.account_name }}</p>
                    </div>

                    <!-- Bank Name -->
                    <div class="space-y-2">
                        <Label for="bank_name" class="text-foreground">Bank Name *</Label>
                        <Input id="bank_name" v-model="form.bank_name" placeholder="e.g., Bank Central Asia"
                            :class="{ 'border-destructive ring-destructive': errors.bank_name }" required />
                        <p v-if="errors.bank_name" class="text-sm text-destructive">{{ errors.bank_name }}</p>
                    </div>

                    <!-- Account Type -->
                    <div class="space-y-2">
                        <Label for="account_type" class="text-foreground">Account Type *</Label>
                        <Select v-model="form.account_type">
                            <SelectTrigger :class="{ 'border-destructive ring-destructive': errors.account_type }">
                                <SelectValue placeholder="Select account type" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="checking">Checking</SelectItem>
                                <SelectItem value="savings">Savings</SelectItem>
                                <SelectItem value="credit">Credit</SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="errors.account_type" class="text-sm text-destructive">{{ errors.account_type }}</p>
                    </div>

                    <!-- Current Balance -->
                    <div class="space-y-2">
                        <Label for="current_balance" class="text-foreground">Current Balance (Optional)</Label>
                        <Input id="current_balance" v-model="form.current_balance" type="number" step="0.01"
                            placeholder="Leave empty to use opening balance" />
                        <p v-if="errors.current_balance" class="text-sm text-destructive">{{ errors.current_balance }}
                        </p>
                        <p class="text-xs text-muted-foreground">If left empty, will use opening balance as current
                            balance</p>
                    </div>

                    <!-- Opening Balance -->
                    <div class="space-y-2">
                        <Label for="opening_balance" class="text-foreground">Opening Balance *</Label>
                        <Input id="opening_balance" v-model="form.opening_balance" type="number" step="0.01" min="0"
                            placeholder="0.00"
                            :class="{ 'border-destructive ring-destructive': errors.opening_balance }" required />
                        <p v-if="errors.opening_balance" class="text-sm text-destructive">{{ errors.opening_balance }}
                        </p>
                    </div>

                    <!-- Is Active -->
                    <div class="flex items-center space-x-2">
                        <Checkbox id="is_active" :checked="form.is_active"
                            @update:checked="(checked: boolean) => form.is_active = checked" />
                        <Label for="is_active" class="text-foreground">Active</Label>
                    </div>

                    <!-- Error Message -->
                    <div v-if="errors.general"
                        class="text-sm text-destructive bg-destructive/10 p-3 rounded-md border border-destructive/20">
                        {{ errors.general }}
                    </div>
                </form>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="closeCreateDialog">
                        Cancel
                    </Button>
                    <Button type="submit" @click="submitForm" :disabled="loading">
                        <Loader2 v-if="loading" class="w-4 h-4 mr-2 animate-spin" />
                        <Save v-else class="w-4 h-4 mr-2" />
                        Create Bank Account
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Edit Bank Account Dialog -->
        <Dialog :open="showEditDialog" @update:open="showEditDialog = $event">
            <DialogContent class="sm:max-w-[600px] bg-card border-border">
                <DialogHeader>
                    <DialogTitle class="text-foreground">Edit Bank Account: {{ editForm.account_name }}</DialogTitle>
                    <DialogDescription class="text-muted-foreground">
                        Update the bank account information
                    </DialogDescription>
                </DialogHeader>

                <form @submit.prevent="submitEditForm" class="space-y-4">
                    <!-- Account Number -->
                    <div class="space-y-2">
                        <Label for="edit_account_number" class="text-foreground">Account Number *</Label>
                        <Input id="edit_account_number" v-model="editForm.account_number" placeholder="e.g., 1234567890"
                            :class="{ 'border-destructive ring-destructive': editErrors.account_number }" required />
                        <p v-if="editErrors.account_number" class="text-sm text-destructive">{{
                            editErrors.account_number }}
                        </p>
                    </div>

                    <!-- Account Name -->
                    <div class="space-y-2">
                        <Label for="edit_account_name" class="text-foreground">Account Name *</Label>
                        <Input id="edit_account_name" v-model="editForm.account_name"
                            placeholder="e.g., Main Business Account"
                            :class="{ 'border-destructive ring-destructive': editErrors.account_name }" required />
                        <p v-if="editErrors.account_name" class="text-sm text-destructive">{{ editErrors.account_name }}
                        </p>
                    </div>

                    <!-- Bank Name -->
                    <div class="space-y-2">
                        <Label for="edit_bank_name" class="text-foreground">Bank Name *</Label>
                        <Input id="edit_bank_name" v-model="editForm.bank_name" placeholder="e.g., Bank Central Asia"
                            :class="{ 'border-destructive ring-destructive': editErrors.bank_name }" required />
                        <p v-if="editErrors.bank_name" class="text-sm text-destructive">{{ editErrors.bank_name }}</p>
                    </div>

                    <!-- Account Type -->
                    <div class="space-y-2">
                        <Label for="edit_account_type" class="text-foreground">Account Type *</Label>
                        <Select v-model="editForm.account_type">
                            <SelectTrigger :class="{ 'border-destructive ring-destructive': editErrors.account_type }">
                                <SelectValue placeholder="Select account type" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="checking">Checking</SelectItem>
                                <SelectItem value="savings">Savings</SelectItem>
                                <SelectItem value="credit">Credit</SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="editErrors.account_type" class="text-sm text-destructive">{{ editErrors.account_type }}
                        </p>
                    </div>

                    <!-- Current Balance -->
                    <div class="space-y-2">
                        <Label for="edit_current_balance" class="text-foreground">Current Balance (Optional)</Label>
                        <Input id="edit_current_balance" v-model="editForm.current_balance" type="number" step="0.01"
                            placeholder="Leave empty to use opening balance" />
                        <p v-if="editErrors.current_balance" class="text-sm text-destructive">{{
                            editErrors.current_balance
                        }}</p>
                        <p class="text-xs text-muted-foreground">If left empty, will use opening balance as current
                            balance</p>
                    </div>

                    <!-- Opening Balance -->
                    <div class="space-y-2">
                        <Label for="edit_opening_balance" class="text-foreground">Opening Balance *</Label>
                        <Input id="edit_opening_balance" v-model="editForm.opening_balance" type="number" step="0.01"
                            min="0" placeholder="0.00"
                            :class="{ 'border-destructive ring-destructive': editErrors.opening_balance }" required />
                        <p v-if="editErrors.opening_balance" class="text-sm text-destructive">{{
                            editErrors.opening_balance
                        }}</p>
                    </div>

                    <!-- Is Active -->
                    <div class="flex items-center space-x-2">
                        <Checkbox id="edit_is_active" v-model="editIsActive" />
                        <Label for="edit_is_active" class="text-foreground">Active</Label>
                    </div>

                    <!-- Error Message -->
                    <div v-if="editErrors.general"
                        class="text-sm text-destructive bg-destructive/10 p-3 rounded-md border border-destructive/20">
                        {{ editErrors.general }}
                    </div>
                </form>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="closeEditDialog">
                        Cancel
                    </Button>
                    <Button type="submit" @click="submitEditForm" :disabled="editLoading">
                        <Loader2 v-if="editLoading" class="w-4 h-4 mr-2 animate-spin" />
                        <Save v-else class="w-4 h-4 mr-2" />
                        Update Bank Account
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Show Bank Account Dialog -->
        <Dialog :open="showViewDialog" @update:open="showViewDialog = $event">
            <DialogContent class="sm:max-w-[700px] bg-card border-border">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2 text-foreground">
                        <FileSpreadsheet class="w-5 h-5" />
                        {{ viewBankAccount?.account_name }}
                    </DialogTitle>
                    <DialogDescription class="text-muted-foreground">
                        Bank account details and information
                    </DialogDescription>
                </DialogHeader>

                <div v-if="viewBankAccount" class="space-y-6">
                    <!-- Basic Information -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground">Account Number</Label>
                            <p class="text-lg font-mono font-bold text-primary">{{ viewBankAccount.account_number }}
                            </p>
                        </div>
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground">Bank Name</Label>
                            <p class="text-lg font-medium text-foreground">{{ viewBankAccount.bank_name }}</p>
                        </div>
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground">Account Type</Label>
                            <Badge :variant="getTypeVariant(viewBankAccount.account_type)">
                                {{ getTypeLabel(viewBankAccount.account_type) }}
                            </Badge>
                        </div>
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground">Status</Label>
                            <Badge :variant="viewBankAccount.is_active ? 'default' : 'secondary'">
                                {{ viewBankAccount.is_active ? 'Active' : 'Inactive' }}
                            </Badge>
                        </div>
                    </div>

                    <!-- Current Balance -->
                    <div>
                        <Label class="text-sm font-medium text-muted-foreground">Current Balance</Label>
                        <p class="text-3xl font-bold font-mono"
                            :class="viewBankAccount.current_balance >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
                            {{ formatCurrency(viewBankAccount.current_balance || 0) }}
                        </p>
                    </div>

                    <!-- Opening Balance -->
                    <div>
                        <Label class="text-sm font-medium text-muted-foreground">Opening Balance</Label>
                        <p class="text-2xl font-bold font-mono text-blue-600 dark:text-blue-400">
                            {{ formatCurrency(viewBankAccount.opening_balance || 0) }}
                        </p>
                    </div>

                    <!-- System Information -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground">Created</Label>
                            <p class="text-foreground">{{ formatDate(viewBankAccount.created_at) }}</p>
                        </div>
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground">Last Updated</Label>
                            <p class="text-foreground">{{ formatDate(viewBankAccount.updated_at) }}</p>
                        </div>
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground">ID</Label>
                            <p class="font-mono text-foreground">{{ viewBankAccount.id }}</p>
                        </div>
                    </div>
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="closeViewDialog">
                        Close
                    </Button>
                    <Button @click="openEditDialog(viewBankAccount)" v-if="viewBankAccount">
                        <Edit class="w-4 h-4 mr-2" />
                        Edit
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, watch, computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Badge } from '@/components/ui/badge'
import { Label } from '@/components/ui/label'
import { Checkbox } from '@/components/ui/checkbox'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { Plus, MoreHorizontal, Eye, Edit, Trash2, Loader2, Save, FileSpreadsheet } from 'lucide-vue-next'
import { apiService } from '@/services/api'
import type { BankAccount, PaginatedData } from '@/types/erp'
import type { BreadcrumbItemType } from '@/types'

interface Props {
    bankAccounts?: BankAccount[] | any
    pagination?: PaginatedData<BankAccount>
}

const props = withDefaults(defineProps<Props>(), {
    bankAccounts: () => [],
    pagination: undefined
})

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Finance', href: '/finance' },
    { title: 'Bank Reconciliation', href: '/finance/bank-reconciliation' },
    { title: 'Bank Accounts', href: '/finance/bank-reconciliation/bank-accounts' }
]

const bankAccounts = ref<BankAccount[]>([])
const pagination = ref<PaginatedData<BankAccount> | undefined>(props.pagination)
const loading = ref(false)
const searchQuery = ref('')
const statusFilter = ref('all')
const typeFilter = ref('all')

// Dialog states
const showCreateDialog = ref(false)
const showEditDialog = ref(false)
const showViewDialog = ref(false)
const errors = ref<Record<string, string>>({})
const editErrors = ref<Record<string, string>>({})
const editLoading = ref(false)

// Forms
const form = ref({
    account_number: '',
    account_name: '',
    bank_name: '',
    account_type: '',
    current_balance: '',
    opening_balance: '',
    is_active: true
})

const editForm = ref({
    id: 0,
    account_number: '',
    account_name: '',
    bank_name: '',
    account_type: '',
    current_balance: '',
    opening_balance: '',
    is_active: true
})

const viewBankAccount = ref<BankAccount | null>(null)

// Computed properties for checkbox binding
const editIsActive = computed({
    get: () => editForm.value.is_active,
    set: (value: boolean) => {
        editForm.value.is_active = value
    }
})

let searchTimeout: number | null = null

const openCreateDialog = () => {
    showCreateDialog.value = true
    resetForm()
}

const closeCreateDialog = () => {
    showCreateDialog.value = false
    resetForm()
}

const openEditDialog = (bankAccount: BankAccount | any) => {
    viewBankAccount.value = bankAccount

    const isActiveValue = Boolean(bankAccount.is_active)

    editForm.value = {
        id: bankAccount.id,
        account_number: bankAccount.account_number,
        account_name: bankAccount.account_name,
        bank_name: bankAccount.bank_name,
        account_type: bankAccount.account_type,
        current_balance: bankAccount.current_balance?.toString() || '',
        opening_balance: bankAccount.opening_balance?.toString() || '',
        is_active: isActiveValue
    }

    showEditDialog.value = true
    showViewDialog.value = false
    editErrors.value = {}
}

const closeEditDialog = () => {
    showEditDialog.value = false
    resetEditForm()
}

const openShowDialog = (bankAccount: BankAccount | any) => {
    viewBankAccount.value = bankAccount
    showViewDialog.value = true
}

const closeViewDialog = () => {
    showViewDialog.value = false
    viewBankAccount.value = null
}

const resetForm = () => {
    form.value = {
        account_number: '',
        account_name: '',
        bank_name: '',
        account_type: '',
        current_balance: '',
        opening_balance: '',
        is_active: true
    }
    errors.value = {}
}

const resetEditForm = () => {
    editForm.value = {
        id: 0,
        account_number: '',
        account_name: '',
        bank_name: '',
        account_type: '',
        current_balance: '',
        opening_balance: '',
        is_active: true
    }
    editErrors.value = {}
}

const debouncedSearch = () => {
    if (searchTimeout) {
        clearTimeout(searchTimeout)
    }
    searchTimeout = setTimeout(() => {
        fetchBankAccounts()
    }, 300)
}

const fetchBankAccounts = async () => {
    loading.value = true
    try {
        const params: any = {}
        if (searchQuery.value) params.search = searchQuery.value
        if (statusFilter.value && statusFilter.value !== 'all') params.is_active = statusFilter.value
        if (typeFilter.value && typeFilter.value !== 'all') params.account_type = typeFilter.value

        const response = await apiService.getBankAccounts(params)
        console.log('Bank Accounts API Response:', response)

        if (response && response.data && Array.isArray(response.data)) {
            bankAccounts.value = response.data.filter((account: any) => account && typeof account === 'object')
        } else {
            bankAccounts.value = []
        }

        if (response && response.meta) {
            pagination.value = {
                data: response.data || [],
                links: response.links || [],
                meta: response.meta
            }
        }
    } catch (error) {
        console.error('Error fetching bank accounts:', error)
        bankAccounts.value = []
        pagination.value = undefined
    } finally {
        loading.value = false
    }
}

const submitForm = async () => {
    loading.value = true
    errors.value = {}

    try {
        // Allow users to set current_balance and opening_balance separately
        const formData = {
            ...form.value,
            // If current_balance is not set, use opening_balance as default
            current_balance: form.value.current_balance || form.value.opening_balance
        }

        await apiService.createBankAccount(formData)

        // Close dialog and refresh data
        closeCreateDialog()
        await fetchBankAccounts()

        // Show success message (you can implement a toast notification here)
        console.log('Bank account created successfully')
    } catch (error: any) {
        if (error.response?.data?.errors) {
            errors.value = error.response.data.errors
        } else {
            errors.value = { general: 'An error occurred while creating the bank account' }
        }
    } finally {
        loading.value = false
    }
}

const submitEditForm = async () => {
    editLoading.value = true
    editErrors.value = {}

    try {
        // Ensure current_balance is set if not provided
        const editData = {
            ...editForm.value,
            current_balance: editForm.value.current_balance || editForm.value.opening_balance
        }

        await apiService.updateBankAccount(editForm.value.id, editData)

        // Close dialog and refresh data
        closeEditDialog()
        await fetchBankAccounts()

        // Show success message
        console.log('Bank account updated successfully')
    } catch (error: any) {
        if (error.response?.data?.errors) {
            editErrors.value = error.response.data.errors
        } else {
            editErrors.value = { general: 'An error occurred while updating the bank account' }
        }
    } finally {
        editLoading.value = false
    }
}

const changePage = (page: number) => {
    const params = new URLSearchParams()
    if (searchQuery.value) params.append('search', searchQuery.value)
    if (statusFilter.value && statusFilter.value !== 'all') params.append('is_active', statusFilter.value)
    if (typeFilter.value && typeFilter.value !== 'all') params.append('account_type', typeFilter.value)
    params.append('page', page.toString())

    router.get(`/finance/bank-reconciliation/bank-accounts?${params.toString()}`)
}

const deleteBankAccount = async (id: number) => {
    if (!id) return

    if (confirm('Are you sure you want to delete this bank account?')) {
        try {
            await apiService.deleteBankAccount(id)
            await fetchBankAccounts()
        } catch (error) {
            console.error('Error deleting bank account:', error)
        }
    }
}

const formatCurrency = (amount: number): string => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(amount)
}

const formatDate = (dateString: string): string => {
    if (!dateString) return 'N/A'
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    })
}

const getTypeLabel = (type: string): string => {
    const labels: Record<string, string> = {
        'checking': 'Checking',
        'savings': 'Savings',
        'credit': 'Credit'
    }
    return labels[type] || type || 'N/A'
}

const getTypeVariant = (type: string): 'default' | 'secondary' | 'destructive' | 'outline' => {
    const variants: Record<string, 'default' | 'secondary' | 'destructive' | 'outline'> = {
        'checking': 'default',      // Primary color - for main/primary account type
        'savings': 'outline',       // Outline style - for secondary account type
        'credit': 'destructive'     // Destructive color - for credit accounts (debt)
    }
    return variants[type] || 'secondary'
}

watch([statusFilter, typeFilter], () => {
    fetchBankAccounts()
})

onMounted(() => {
    if (bankAccounts.value.length === 0) {
        fetchBankAccounts()
    }
})
</script>
