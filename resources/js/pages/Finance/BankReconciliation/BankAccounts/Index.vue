<template>

    <Head title="Bank Accounts" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 space-y-6">
            <!-- Header Section -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Bank Accounts</h1>
                    <p class="text-muted-foreground mt-1">
                        Manage your bank accounts for reconciliation
                    </p>
                </div>
                <div class="flex items-center space-x-2">
                    <Button variant="outline" size="sm" @click="refreshData" :disabled="loading">
                        <RefreshCw class="h-4 w-4 mr-2" />
                        Refresh
                    </Button>
                    <Button @click="showNewAccountModal = true" variant="default">
                        <Plus class="h-4 w-4 mr-2" />
                        New Account
                    </Button>
                </div>
            </div>

            <!-- Bank Accounts List -->
            <Card>
                <CardHeader>
                    <CardTitle>Bank Accounts</CardTitle>
                    <CardDescription>All registered bank accounts</CardDescription>
                </CardHeader>
                <CardContent class="m-6">
                    <div v-if="loading" class="flex justify-center py-8">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
                    </div>

                    <div v-else-if="bankAccounts.length === 0" class="text-center py-8 text-muted-foreground">
                        No bank accounts found
                    </div>

                    <div v-else class="space-y-4">
                        <div v-for="account in bankAccounts" :key="account.id"
                            class="flex items-center justify-between p-4 border rounded-lg hover:bg-muted/50 transition-colors">
                            <div class="flex-1">
                                <div class="flex items-center space-x-3">
                                    <Badge :variant="account.status === 'active' ? 'default' : 'secondary'">
                                        {{ account.status }}
                                    </Badge>
                                    <Badge variant="outline" class="text-xs">
                                        {{ account.account_type }}
                                    </Badge>
                                    <span class="font-medium">{{ account.name }}</span>
                                </div>
                                <div class="text-sm text-muted-foreground mt-1">
                                    {{ account.account_number }} • {{ account.bank_name }}
                                    <span v-if="account.bank_branch">• {{ account.bank_branch }}</span>
                                </div>
                                <div v-if="account.description" class="text-xs text-muted-foreground mt-1">
                                    {{ account.description }}
                                </div>
                            </div>

                            <div class="text-right">
                                <div class="font-semibold">{{ formatCurrency(account.balance) }}</div>
                                <div class="text-sm text-muted-foreground">{{ account.currency }}</div>
                                <div class="text-xs text-muted-foreground">
                                    Opening: {{ formatCurrency(account.opening_balance) }}
                                </div>
                                <div v-if="account.reconcile_automatically" class="text-xs text-green-600 mt-1">
                                    Auto Reconcile
                                </div>
                            </div>

                            <Button variant="ghost" size="sm" @click="viewAccount(account)">
                                <Eye class="h-4 w-4" />
                            </Button>
                        </div>
                    </div>
                </CardContent>
                <CardFooter v-if="bankAccounts.length > 0" class="border-t py-4">
                    <div class="text-sm text-muted-foreground">
                        Showing {{ bankAccounts.length }} bank accounts
                    </div>
                </CardFooter>
            </Card>
        </div>

        <!-- New Account Modal -->
        <Dialog v-model:open="showNewAccountModal">
            <DialogContent class="!sm:max-w-[425px] max-h-[90vh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle>New Bank Account</DialogTitle>
                    <DialogDescription>Add a new bank account for reconciliation</DialogDescription>
                </DialogHeader>
                <form @submit.prevent="createAccount" class="space-y-4">
                    <div class="space-y-2">
                        <Label for="name">Account Name</Label>
                        <Input id="name" v-model="newAccount.name" required />
                    </div>

                    <div class="space-y-2">
                        <Label for="account_number">Account Number</Label>
                        <Input id="account_number" v-model="newAccount.account_number" required />
                    </div>

                    <div class="space-y-2">
                        <Label for="bank_name">Bank Name</Label>
                        <Input id="bank_name" v-model="newAccount.bank_name" required />
                    </div>

                    <div class="space-y-2">
                        <Label for="bank_branch">Bank Branch</Label>
                        <Input id="bank_branch" v-model="newAccount.bank_branch" />
                    </div>

                    <div class="space-y-2">
                        <Label for="account_type">Account Type</Label>
                        <Select v-model="newAccount.account_type">
                            <SelectTrigger>
                                <SelectValue placeholder="Select account type" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="checking">Checking</SelectItem>
                                <SelectItem value="savings">Savings</SelectItem>
                                <SelectItem value="credit">Credit</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="space-y-2">
                        <Label for="opening_balance">Opening Balance</Label>
                        <Input id="opening_balance" type="number" step="0.01" v-model="newAccount.opening_balance"
                            required />
                    </div>

                    <div class="space-y-2">
                        <Label for="currency">Currency</Label>
                        <Input id="currency" v-model="newAccount.currency" required />
                    </div>

                    <div class="space-y-2">
                        <Label for="description">Description</Label>
                        <Input id="description" v-model="newAccount.description" />
                    </div>

                    <div class="space-y-2">
                        <Label for="swift_code">Swift Code</Label>
                        <Input id="swift_code" v-model="newAccount.swift_code" />
                    </div>

                    <div class="space-y-2">
                        <Label for="iban">IBAN</Label>
                        <Input id="iban" v-model="newAccount.iban" />
                    </div>

                    <div class="space-y-2">
                        <Label for="opening_date">Opening Date</Label>
                        <Input id="opening_date" type="date" v-model="newAccount.opening_date" />
                    </div>

                    <div class="space-y-2">
                        <Label for="reconcile_automatically">Auto Reconcile</Label>
                        <Select v-model="newAccount.reconcile_automatically">
                            <SelectTrigger>
                                <SelectValue placeholder="Select auto reconcile" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="true">Yes</SelectItem>
                                <SelectItem value="false">No</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="space-y-2">
                        <Label for="allow_overdraft">Allow Overdraft</Label>
                        <Select v-model="newAccount.allow_overdraft">
                            <SelectTrigger>
                                <SelectValue placeholder="Select allow overdraft" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="true">Yes</SelectItem>
                                <SelectItem value="false">No</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="space-y-2">
                        <Label for="include_in_cash_flow">Include in Cash Flow</Label>
                        <Select v-model="newAccount.include_in_cash_flow">
                            <SelectTrigger>
                                <SelectValue placeholder="Select include in cash flow" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="true">Yes</SelectItem>
                                <SelectItem value="false">No</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="space-y-2">
                        <Label for="notes">Notes</Label>
                        <Input id="notes" v-model="newAccount.notes" />
                    </div>

                    <DialogFooter>
                        <Button type="button" variant="outline" @click="showNewAccountModal = false">
                            Cancel
                        </Button>
                        <Button type="submit" :disabled="creating">
                            {{ creating ? 'Creating...' : 'Create Account' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- View Account Modal -->
        <Dialog v-model:open="showViewAccountModal">
            <DialogContent class="!sm:max-w-[425px] max-h-[90vh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle>View Bank Account</DialogTitle>
                    <DialogDescription>Details of the selected bank account</DialogDescription>
                </DialogHeader>
                <div class="space-y-4">
                    <div class="space-y-2">
                        <Label for="view_name">Account Name</Label>
                        <Input id="view_name" v-model="selectedAccount.name" disabled />
                    </div>
                    <div class="space-y-2">
                        <Label for="view_account_number">Account Number</Label>
                        <Input id="view_account_number" v-model="selectedAccount.account_number" disabled />
                    </div>
                    <div class="space-y-2">
                        <Label for="view_bank_name">Bank Name</Label>
                        <Input id="view_bank_name" v-model="selectedAccount.bank_name" disabled />
                    </div>
                    <div class="space-y-2">
                        <Label for="view_bank_branch">Bank Branch</Label>
                        <Input id="view_bank_branch" v-model="selectedAccount.bank_branch" disabled />
                    </div>
                    <div class="space-y-2">
                        <Label for="view_account_type">Account Type</Label>
                        <Input id="view_account_type" v-model="selectedAccount.account_type" disabled />
                    </div>
                    <div class="space-y-2">
                        <Label for="view_opening_balance">Opening Balance</Label>
                        <div class="text-sm font-medium">
                            {{ formatCurrency(selectedAccount?.opening_balance) }}
                        </div>
                    </div>
                    <div class="space-y-2">
                        <Label for="view_current_balance">Current Balance</Label>
                        <div class="text-lg font-semibold text-green-600">
                            {{ formatCurrency(selectedAccount?.balance) }}
                        </div>
                    </div>
                    <div class="space-y-2">
                        <Label for="view_status">Status</Label>
                        <div class="flex items-center space-x-2">
                            <Badge :variant="selectedAccount?.status === 'active' ? 'default' : 'secondary'">
                                {{ selectedAccount?.status }}
                            </Badge>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <Label for="view_currency">Currency</Label>
                        <Input id="view_currency" v-model="selectedAccount.currency" disabled />
                    </div>
                    <div class="space-y-2">
                        <Label for="view_description">Description</Label>
                        <Input id="view_description" v-model="selectedAccount.description" disabled />
                    </div>
                    <div class="space-y-2">
                        <Label for="view_swift_code">Swift Code</Label>
                        <Input id="view_swift_code" v-model="selectedAccount.swift_code" disabled />
                    </div>
                    <div class="space-y-2">
                        <Label for="view_iban">IBAN</Label>
                        <Input id="view_iban" v-model="selectedAccount.iban" disabled />
                    </div>
                    <div class="space-y-2">
                        <Label for="view_opening_date">Opening Date</Label>
                        <div class="text-sm">
                            {{ selectedAccount?.opening_date ? new
                                Date(selectedAccount.opening_date).toLocaleDateString() : '-' }}
                        </div>
                    </div>
                    <div class="space-y-2">
                        <Label for="view_reconcile_automatically">Auto Reconcile</Label>
                        <div class="flex items-center space-x-2">
                            <Badge :variant="selectedAccount?.reconcile_automatically ? 'default' : 'secondary'">
                                {{ selectedAccount?.reconcile_automatically ? 'Yes' : 'No' }}
                            </Badge>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <Label for="view_allow_overdraft">Allow Overdraft</Label>
                        <div class="flex items-center space-x-2">
                            <Badge :variant="selectedAccount?.allow_overdraft ? 'default' : 'secondary'">
                                {{ selectedAccount?.allow_overdraft ? 'Yes' : 'No' }}
                            </Badge>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <Label for="view_include_in_cash_flow">Include in Cash Flow</Label>
                        <div class="flex items-center space-x-2">
                            <Badge :variant="selectedAccount?.include_in_cash_flow ? 'default' : 'secondary'">
                                {{ selectedAccount?.include_in_cash_flow ? 'Yes' : 'No' }}
                            </Badge>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <Label for="view_notes">Notes</Label>
                        <Input id="view_notes" v-model="selectedAccount.notes" disabled />
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="showViewAccountModal = false">Close</Button>
                    <Button @click="openEditModal" variant="default">Edit</Button>
                    <Button @click="openDeleteModal" variant="destructive">Delete</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Edit Account Modal -->
        <Dialog v-model:open="showEditAccountModal">
            <DialogContent class="!sm:max-w-[425px] max-h-[90vh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle>Edit Bank Account</DialogTitle>
                    <DialogDescription>Edit the details of the selected bank account</DialogDescription>
                </DialogHeader>
                <form @submit.prevent="updateAccount" class="space-y-4">
                    <div class="space-y-2">
                        <Label for="edit_name">Account Name</Label>
                        <Input id="edit_name" v-model="editAccount.name" required />
                    </div>
                    <div class="space-y-2">
                        <Label for="edit_account_number">Account Number</Label>
                        <Input id="edit_account_number" v-model="editAccount.account_number" required />
                    </div>
                    <div class="space-y-2">
                        <Label for="edit_bank_name">Bank Name</Label>
                        <Input id="edit_bank_name" v-model="editAccount.bank_name" required />
                    </div>
                    <div class="space-y-2">
                        <Label for="edit_bank_branch">Bank Branch</Label>
                        <Input id="edit_bank_branch" v-model="editAccount.bank_branch" />
                    </div>
                    <div class="space-y-2">
                        <Label for="edit_account_type">Account Type</Label>
                        <Select v-model="editAccount.account_type">
                            <SelectTrigger>
                                <SelectValue placeholder="Select account type" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="checking">Checking</SelectItem>
                                <SelectItem value="savings">Savings</SelectItem>
                                <SelectItem value="credit">Credit</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="space-y-2">
                        <Label for="edit_opening_balance">Opening Balance</Label>
                        <Input id="edit_opening_balance" type="number" step="0.01" v-model="editAccount.opening_balance"
                            required />
                    </div>
                    <div class="space-y-2">
                        <Label for="edit_currency">Currency</Label>
                        <Input id="edit_currency" v-model="editAccount.currency" required />
                    </div>
                    <div class="space-y-2">
                        <Label for="edit_description">Description</Label>
                        <Input id="edit_description" v-model="editAccount.description" />
                    </div>
                    <div class="space-y-2">
                        <Label for="edit_swift_code">Swift Code</Label>
                        <Input id="edit_swift_code" v-model="editAccount.swift_code" />
                    </div>
                    <div class="space-y-2">
                        <Label for="edit_iban">IBAN</Label>
                        <Input id="edit_iban" v-model="editAccount.iban" />
                    </div>
                    <div class="space-y-2">
                        <Label for="edit_opening_date">Opening Date</Label>
                        <Input id="edit_opening_date" v-model="editAccount.opening_date" />
                    </div>
                    <div class="space-y-2">
                        <Label for="edit_reconcile_automatically">Auto Reconcile</Label>
                        <Select v-model="editAccount.reconcile_automatically">
                            <SelectTrigger>
                                <SelectValue placeholder="Select auto reconcile" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="true">Yes</SelectItem>
                                <SelectItem value="false">No</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="space-y-2">
                        <Label for="edit_allow_overdraft">Allow Overdraft</Label>
                        <Select v-model="editAccount.allow_overdraft">
                            <SelectTrigger>
                                <SelectValue placeholder="Select allow overdraft" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="true">Yes</SelectItem>
                                <SelectItem value="false">No</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="space-y-2">
                        <Label for="edit_include_in_cash_flow">Include in Cash Flow</Label>
                        <Select v-model="editAccount.include_in_cash_flow">
                            <SelectTrigger>
                                <SelectValue placeholder="Select include in cash flow" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="true">Yes</SelectItem>
                                <SelectItem value="false">No</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="space-y-2">
                        <Label for="edit_notes">Notes</Label>
                        <Input id="edit_notes" v-model="editAccount.notes" />
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" @click="showEditAccountModal = false">
                            Cancel
                        </Button>
                        <Button type="submit" :disabled="editing">
                            {{ editing ? 'Saving...' : 'Save Changes' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Delete Account Modal -->
        <Dialog v-model:open="showDeleteAccountModal">
            <DialogContent class="!sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>Delete Bank Account</DialogTitle>
                    <DialogDescription>Are you sure you want to delete this bank account?</DialogDescription>
                </DialogHeader>
                <div class="text-center py-8">
                    <p class="text-lg font-semibold text-red-600">
                        This action cannot be undone.
                    </p>
                    <p class="text-muted-foreground mt-2">
                        Account: {{ selectedAccount.name }}
                    </p>
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="showDeleteAccountModal = false">Cancel</Button>
                    <Button variant="destructive" @click="deleteAccount" :disabled="deleting">
                        {{ deleting ? 'Deleting...' : 'Delete Account' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import { router } from '@inertiajs/vue3'
import { useApi } from '@/composables/useApi'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle, CardFooter } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Plus, Eye, RefreshCw } from 'lucide-vue-next'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'

// Breadcrumbs
const breadcrumbs = [
    { title: 'Finance', href: '/finance' },
    { title: 'Bank Reconciliation', href: '/finance/bank-reconciliation' },
    { title: 'Bank Accounts', href: '/finance/bank-reconciliation/bank-accounts' }
]

// API
const { get, post, put, delete: del } = useApi()

// Reactive data
const loading = ref(false)
const creating = ref(false)
const editing = ref(false)
const deleting = ref(false)
const showNewAccountModal = ref(false)
const showViewAccountModal = ref(false)
const showEditAccountModal = ref(false)
const showDeleteAccountModal = ref(false)

// Data
const bankAccounts = ref<any[]>([])
const selectedAccount = ref<any>(null)

// Form data
const newAccount = ref({
    name: '',
    account_number: '',
    bank_name: '',
    bank_branch: '',
    account_type: 'checking',
    opening_balance: 0,
    currency: 'IDR',
    description: '',
    swift_code: '',
    iban: '',
    opening_date: '',
    reconcile_automatically: 'false',
    allow_overdraft: 'false',
    include_in_cash_flow: 'true',
    notes: ''
})

const editAccount = ref({
    name: '',
    account_number: '',
    bank_name: '',
    bank_branch: '',
    account_type: 'checking',
    opening_balance: 0,
    currency: 'IDR',
    description: '',
    swift_code: '',
    iban: '',
    opening_date: '',
    reconcile_automatically: 'false',
    allow_overdraft: 'false',
    include_in_cash_flow: 'true',
    notes: ''
})

// Methods
const fetchBankAccounts = async () => {
    try {
        loading.value = true
        const response = await get('/api/bank/accounts')
        if (response.data?.data) {
            // Handle pagination response structure
            bankAccounts.value = response.data.data
        }
    } catch (error) {
        // Handle error silently
    } finally {
        loading.value = false
    }
}

const refreshData = () => {
    fetchBankAccounts()
}

const createAccount = async () => {
    try {
        creating.value = true
        const response = await post('/api/bank/accounts', newAccount.value)

        if (response.data) {
            showNewAccountModal.value = false
            await fetchBankAccounts()

            // Reset form
            newAccount.value = {
                name: '',
                account_number: '',
                bank_name: '',
                bank_branch: '',
                account_type: 'checking',
                opening_balance: 0,
                currency: 'IDR',
                description: '',
                swift_code: '',
                iban: '',
                opening_date: '',
                reconcile_automatically: 'false',
                allow_overdraft: 'false',
                include_in_cash_flow: 'true',
                notes: ''
            }
        }
    } catch (error) {
        // Handle error silently
    } finally {
        creating.value = false
    }
}

const viewAccount = (account: any) => {
    selectedAccount.value = account
    showViewAccountModal.value = true
}

const openEditModal = () => {
    showViewAccountModal.value = false
    // Populate edit form
    editAccount.value = {
        name: selectedAccount.value.name,
        account_number: selectedAccount.value.account_number,
        bank_name: selectedAccount.value.bank_name,
        bank_branch: selectedAccount.value.bank_branch || '',
        account_type: selectedAccount.value.account_type,
        opening_balance: parseFloat(selectedAccount.value.opening_balance),
        currency: selectedAccount.value.currency,
        description: selectedAccount.value.description || '',
        swift_code: selectedAccount.value.swift_code || '',
        iban: selectedAccount.value.iban || '',
        opening_date: selectedAccount.value.opening_date || '',
        reconcile_automatically: selectedAccount.value.reconcile_automatically ? 'true' : 'false',
        allow_overdraft: selectedAccount.value.allow_overdraft ? 'true' : 'false',
        include_in_cash_flow: selectedAccount.value.include_in_cash_flow ? 'true' : 'false',
        notes: selectedAccount.value.notes || ''
    }
    showEditAccountModal.value = true
}

const openDeleteModal = () => {
    showViewAccountModal.value = false
    showDeleteAccountModal.value = true
}

const updateAccount = async () => {
    try {
        editing.value = true
        const response = await put(`/api/bank/accounts/${selectedAccount.value.id}`, editAccount.value)

        if (response.data) {
            showEditAccountModal.value = false
            await fetchBankAccounts()
            selectedAccount.value = null
        }
    } catch (error) {
        // Handle error silently
    } finally {
        editing.value = false
    }
}

const deleteAccount = async () => {
    try {
        deleting.value = true
        const response = await del(`/api/bank/accounts/${selectedAccount.value.id}`)

        if (response.data) {
            showDeleteAccountModal.value = false
            await fetchBankAccounts()
            selectedAccount.value = null
        }
    } catch (error) {
        // Handle error silently
    } finally {
        deleting.value = false
    }
}

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(amount)
}

// Lifecycle
onMounted(() => {
    fetchBankAccounts()
})
</script>
