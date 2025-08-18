<template>

    <Head title="Bank Accounts" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="font-semibold text-xl leading-tight">
                    Bank Accounts
                </h2>
                <Link :href="route('finance.bank-reconciliation.bank-accounts.create')">
                <Button>
                    <Plus class="w-4 h-4 mr-2" />
                    Add Bank Account
                </Button>
                </Link>
            </div>

            <div class="overflow-hidden shadow-sm sm:rounded-lg">
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
                    <div class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Account Number</TableHead>
                                    <TableHead>Account Name</TableHead>
                                    <TableHead>Bank Name</TableHead>
                                    <TableHead>Type</TableHead>
                                    <TableHead>Current Balance</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead class="text-right">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-if="loading">
                                    <TableCell colspan="7" class="text-center py-8">
                                        <div class="flex items-center justify-center">
                                            <Loader2 class="w-6 h-6 animate-spin mr-2" />
                                            Loading...
                                        </div>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-else-if="bankAccounts.length === 0">
                                    <TableCell colspan="7" class="text-center py-8 text-gray-500">
                                        No bank accounts found
                                    </TableCell>
                                </TableRow>
                                <TableRow v-else v-for="account in bankAccounts.filter((ba: any) => ba && ba.id)"
                                    :key="account.id">
                                    <TableCell>
                                        <div class="font-mono text-sm">{{ account.account_number }}</div>
                                    </TableCell>
                                    <TableCell>
                                        <div class="font-medium">{{ account.account_name }}</div>
                                    </TableCell>
                                    <TableCell>
                                        <div class="text-sm">{{ account.bank_name }}</div>
                                    </TableCell>
                                    <TableCell>
                                        <Badge :variant="getTypeVariant(account.account_type)">
                                            {{ getTypeLabel(account.account_type) }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        <div class="text-sm font-mono font-medium"
                                            :class="account.current_balance >= 0 ? 'text-green-600' : 'text-red-600'">
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
                                                <Button variant="ghost" class="h-8 w-8 p-0">
                                                    <MoreHorizontal class="h-4 w-4" />
                                                </Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end">
                                                <DropdownMenuItem as-child>
                                                    <Link
                                                        :href="route('finance.bank-reconciliation.bank-accounts.show', account.id)">
                                                    <Eye class="w-4 h-4 mr-2" />
                                                    View
                                                    </Link>
                                                </DropdownMenuItem>
                                                <DropdownMenuItem as-child>
                                                    <Link
                                                        :href="route('finance.bank-reconciliation.bank-accounts.edit', account.id)">
                                                    <Edit class="w-4 h-4 mr-2" />
                                                    Edit
                                                    </Link>
                                                </DropdownMenuItem>
                                                <DropdownMenuSeparator />
                                                <DropdownMenuItem @click="deleteBankAccount(account.id)"
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
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Plus, MoreHorizontal, Eye, Edit, Trash2, Loader2 } from 'lucide-vue-next'
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

let searchTimeout: number | null = null

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
            bankAccounts.value = response.data.filter(account => account && typeof account === 'object')
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
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount)
}

const getTypeLabel = (type: string): string => {
    const labels: Record<string, string> = {
        'checking': 'Checking',
        'savings': 'Savings',
        'credit': 'Credit'
    }
    return labels[type] || type || 'N/A'
}

const getTypeVariant = (type: string): 'default' | 'secondary' | 'destructive' => {
    const variants: Record<string, 'default' | 'secondary' | 'destructive'> = {
        'checking': 'default',
        'savings': 'secondary',
        'credit': 'destructive'
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
