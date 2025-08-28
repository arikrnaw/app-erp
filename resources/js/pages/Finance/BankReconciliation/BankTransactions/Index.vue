<template>

    <Head title="Bank Transactions Management" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl leading-tight">
                    Bank Transactions Management
                </h2>
                <Link :href="route('finance.bank-reconciliation.bank-transactions.create')">
                <Button>
                    <Plus class="w-4 h-4 mr-2" />
                    Add Transaction
                </Button>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <!-- Search and Filters -->
                        <div class="flex flex-col sm:flex-row gap-4 mb-6">
                            <div class="flex-1">
                                <Input v-model="searchQuery" placeholder="Search transactions..." class="w-full"
                                    @input="debouncedSearch" />
                            </div>
                            <div class="flex gap-2">
                                <select v-model="bankAccountFilter"
                                    class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="">All Bank Accounts</option>
                                    <option v-for="account in bankAccounts" :key="account?.id" :value="account?.id">
                                        {{ account?.name || 'N/A' }}
                                    </option>
                                </select>
                                <select v-model="reconciledFilter"
                                    class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="">All Status</option>
                                    <option value="1">Reconciled</option>
                                    <option value="0">Not Reconciled</option>
                                </select>
                            </div>
                        </div>

                        <!-- Bank Transactions Table -->
                        <div class="rounded-md border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Date</TableHead>
                                        <TableHead>Description</TableHead>
                                        <TableHead>Bank Account</TableHead>
                                        <TableHead>Reference</TableHead>
                                        <TableHead>Debit</TableHead>
                                        <TableHead>Credit</TableHead>
                                        <TableHead>Balance</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead class="text-right">Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-if="loading">
                                        <TableCell colspan="9" class="text-center py-8">
                                            <div class="flex items-center justify-center">
                                                <Loader2 class="w-6 h-6 animate-spin mr-2" />
                                                Loading...
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                    <TableRow v-else-if="transactions.length === 0">
                                        <TableCell colspan="9" class="text-center py-8 text-gray-500">
                                            No bank transactions found
                                        </TableCell>
                                    </TableRow>
                                    <TableRow v-else v-for="transaction in transactions" :key="transaction?.id">
                                        <TableCell>
                                            <div class="text-sm">{{ formatDate(transaction?.transaction_date) }}</div>
                                        </TableCell>
                                        <TableCell>
                                            <div>
                                                <div class="font-medium">{{ transaction?.description || 'N/A' }}</div>
                                                <div class="text-sm text-gray-500">{{ transaction?.notes || 'No notes'
                                                    }}</div>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div class="text-sm">
                                                {{ transaction?.bank_account?.name || 'N/A' }}
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div class="text-sm">
                                                {{ transaction?.reference_number || 'N/A' }}
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div class="text-sm text-red-600">
                                                {{ transaction?.debit_amount > 0 ?
                                                    formatCurrency(transaction?.debit_amount) :
                                                    '-' }}
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div class="text-sm text-green-600">
                                                {{ transaction?.credit_amount > 0 ?
                                                    formatCurrency(transaction?.credit_amount) :
                                                    '-' }}
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div class="text-sm font-medium">
                                                {{ formatCurrency(transaction?.balance) }}
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <Badge :variant="getReconciledVariant(transaction?.is_reconciled)">
                                                {{ getReconciledLabel(transaction?.is_reconciled) }}
                                            </Badge>
                                        </TableCell>
                                        <TableCell class="text-right">
                                            <DropdownMenu>
                                                <DropdownMenuTrigger as-child>
                                                    <Button variant="ghost" class="h-8 w-8 p-0">
                                                        <MoreHorizontal class="h-4 w-4" />
                                                    </Button>
                                                </DropdownMenuTrigger>
                                                <DropdownMenuContent align="end">
                                                    <DropdownMenuItem as-child>
                                                        <Link
                                                            :href="route('finance.bank-reconciliation.bank-transactions.show', transaction?.id)">
                                                        <Eye class="w-4 h-4 mr-2" />
                                                        View
                                                        </Link>
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem as-child>
                                                        <Link
                                                            :href="route('finance.bank-reconciliation.bank-transactions.edit', transaction?.id)">
                                                        <Edit class="w-4 h-4 mr-2" />
                                                        Edit
                                                        </Link>
                                                    </DropdownMenuItem>
                                                    <DropdownMenuSeparator />
                                                    <DropdownMenuItem @click="deleteTransaction(transaction?.id)"
                                                        class="text-red-600">
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
                            <div class="text-sm text-gray-700">
                                Showing {{ pagination.meta.from }} to {{ pagination.meta.to }} of {{
                                    pagination.meta.total }}
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
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Badge } from '@/components/ui/badge'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import { Plus, MoreHorizontal, Eye, Edit, Trash2, Loader2 } from 'lucide-vue-next'
import { apiService } from '@/services/api'
import type { BankTransaction, BankAccount, PaginatedData } from '@/types/erp'

interface Props {
    transactions?: BankTransaction[] | any
    pagination?: PaginatedData<BankTransaction>
}

const props = withDefaults(defineProps<Props>(), {
    transactions: () => [],
    pagination: undefined
})

const transactions = ref<BankTransaction[]>([])
const pagination = ref<PaginatedData<BankTransaction> | undefined>(props.pagination)
const loading = ref(false)
const searchQuery = ref('')
const bankAccountFilter = ref('')
const reconciledFilter = ref('')
const bankAccounts = ref<BankAccount[]>([])

let searchTimeout: number | null = null

const debouncedSearch = () => {
    if (searchTimeout) {
        clearTimeout(searchTimeout)
    }
    searchTimeout = setTimeout(() => {
        fetchTransactions()
    }, 300)
}

const fetchTransactions = async () => {
    loading.value = true
    try {
        const params: any = {}
        if (searchQuery.value) params.search = searchQuery.value
        if (bankAccountFilter.value) params.bank_account_id = bankAccountFilter.value
        if (reconciledFilter.value !== '') params.is_reconciled = reconciledFilter.value

        const response = await apiService.getBankTransactions(params)
        console.log('Bank Transactions API Response:', response)

        if (response && response.data && Array.isArray(response.data)) {
            transactions.value = response.data.filter((transaction: BankTransaction) => transaction && typeof transaction === 'object')
        } else {
            transactions.value = []
        }

        if (response && response.meta) {
            pagination.value = {
                data: response.data || [],
                links: response.links || [],
                meta: response.meta
            }
        }
    } catch (error) {
        console.error('Error fetching bank transactions:', error)
        transactions.value = []
        pagination.value = undefined
    } finally {
        loading.value = false
    }
}

const fetchBankAccounts = async () => {
    try {
        const response = await apiService.getBankAccounts({ page: 1 })
        bankAccounts.value = response.data || []
    } catch (error) {
        console.error('Error fetching bank accounts:', error)
        bankAccounts.value = []
    }
}

const changePage = (page: number) => {
    const params = new URLSearchParams()
    if (searchQuery.value) params.append('search', searchQuery.value)
    if (bankAccountFilter.value) params.append('bank_account_id', bankAccountFilter.value)
    if (reconciledFilter.value !== '') params.append('is_reconciled', reconciledFilter.value)
    params.append('page', page.toString())

    router.get(`/finance/bank-reconciliation/bank-transactions?${params.toString()}`)
}

const deleteTransaction = async (id: number) => {
    if (!id) return

    if (confirm('Are you sure you want to delete this bank transaction?')) {
        try {
            await apiService.deleteBankTransaction(id)
            await fetchTransactions()
        } catch (error) {
            console.error('Error deleting bank transaction:', error)
        }
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

const getReconciledLabel = (isReconciled: boolean): string => {
    return isReconciled ? 'Reconciled' : 'Not Reconciled'
}

const getReconciledVariant = (isReconciled: boolean): 'default' | 'secondary' | 'destructive' => {
    return isReconciled ? 'default' : 'secondary'
}

watch([bankAccountFilter, reconciledFilter], () => {
    fetchTransactions()
})

onMounted(() => {
    if (transactions.value.length === 0) {
        fetchTransactions()
    }
    fetchBankAccounts()
})
</script>
