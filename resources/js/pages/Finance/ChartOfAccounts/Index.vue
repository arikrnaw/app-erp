<template>

    <Head title="Chart of Accounts" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="font-semibold text-xl leading-tight">
                    Chart of Accounts
                </h2>
                <Link :href="route('finance.chart-of-accounts.create')">
                <Button>
                    <Plus class="w-4 h-4 mr-2" />
                    Add Account
                </Button>
                </Link>
            </div>

            <div class="overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Search and Filters -->
                    <div class="flex flex-col sm:flex-row gap-4 mb-6">
                        <div class="flex-1">
                            <Input v-model="searchQuery" placeholder="Search accounts..." class="w-full"
                                @input="debouncedSearch" />
                        </div>
                        <div class="flex gap-2">
                            <Select v-model="typeFilter">
                                <SelectTrigger class="w-[180px]">
                                    <SelectValue placeholder="Account Type" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All Types</SelectItem>
                                    <SelectItem value="asset">Asset</SelectItem>
                                    <SelectItem value="liability">Liability</SelectItem>
                                    <SelectItem value="equity">Equity</SelectItem>
                                    <SelectItem value="revenue">Revenue</SelectItem>
                                    <SelectItem value="expense">Expense</SelectItem>
                                </SelectContent>
                            </Select>
                            <Select v-model="statusFilter">
                                <SelectTrigger class="w-[180px]">
                                    <SelectValue placeholder="Status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All Status</SelectItem>
                                    <SelectItem value="active">Active</SelectItem>
                                    <SelectItem value="inactive">Inactive</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>

                    <!-- Accounts Table -->
                    <div class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Account Code</TableHead>
                                    <TableHead>Account Name</TableHead>
                                    <TableHead>Type</TableHead>
                                    <TableHead>Parent Account</TableHead>
                                    <TableHead>Balance</TableHead>
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
                                <TableRow v-else-if="accounts.length === 0">
                                    <TableCell colspan="7" class="text-center py-8 text-gray-500">
                                        No accounts found
                                    </TableCell>
                                </TableRow>
                                <TableRow v-else v-for="account in accounts.filter((a: any) => a && a.id)"
                                    :key="account.id">
                                    <TableCell>
                                        <div class="font-mono text-sm">{{ account.account_code }}</div>
                                    </TableCell>
                                    <TableCell>
                                        <div>
                                            <div class="font-medium">{{ account.name }}</div>
                                            <div class="text-sm text-gray-500">{{ account.description || `No
                                                description` }}</div>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <Badge :variant="getTypeVariant(account.type)">
                                            {{ getTypeLabel(account.type) }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        <div class="text-sm text-white">
                                            {{ account.parent?.name || 'Root Account' }}
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <div class="text-sm font-medium" :class="getBalanceColor(account.balance)">
                                            {{ formatCurrency(account.balance || 0) }}
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <Badge :variant="account.status === 'active' ? 'default' : 'secondary'">
                                            {{ account.status }}
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
                                                    <Link :href="route('finance.chart-of-accounts.show', account.id)">
                                                    <Eye class="w-4 h-4 mr-2" />
                                                    View
                                                    </Link>
                                                </DropdownMenuItem>
                                                <DropdownMenuItem as-child>
                                                    <Link :href="route('finance.chart-of-accounts.edit', account.id)">
                                                    <Edit class="w-4 h-4 mr-2" />
                                                    Edit
                                                    </Link>
                                                </DropdownMenuItem>
                                                <DropdownMenuSeparator />
                                                <DropdownMenuItem @click="deleteAccount(account.id)"
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
                            }} results
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
import type { ChartOfAccount, PaginatedData, PaginationLinks, PaginationMeta } from '@/types/erp'
import type { BreadcrumbItemType } from '@/types'

interface Props {
    accounts?: ChartOfAccount[] | any
    pagination?: PaginatedData<ChartOfAccount>
}

const props = withDefaults(defineProps<Props>(), {
    accounts: () => [],
    pagination: undefined
})

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Finance', href: '/finance' },
    { title: 'Chart of Accounts', href: '/finance/chart-of-accounts' }
]

const accounts = ref<ChartOfAccount[]>([])
const pagination = ref<PaginatedData<ChartOfAccount> | undefined>(props.pagination)
const loading = ref(false)
const searchQuery = ref('')
const typeFilter = ref('all')
const statusFilter = ref('all')

let searchTimeout: ReturnType<typeof setTimeout> | null = null

const debouncedSearch = () => {
    if (searchTimeout) {
        clearTimeout(searchTimeout)
    }
    searchTimeout = setTimeout(() => {
        fetchAccounts()
    }, 300)
}

const fetchAccounts = async () => {
    loading.value = true
    try {
        const params: any = {}
        if (searchQuery.value) params.search = searchQuery.value
        if (typeFilter.value && typeFilter.value !== 'all') params.type = typeFilter.value
        if (statusFilter.value && statusFilter.value !== 'all') params.status = statusFilter.value

        const response = await apiService.getChartOfAccounts(params)
        console.log('Chart of Accounts API Response:', response)

        if (response && response.data && Array.isArray(response.data)) {
            accounts.value = response.data.filter(account => account && typeof account === 'object')
        } else {
            accounts.value = []
        }

        if (response && response.meta) {
            pagination.value = {
                data: response.data || [],
                links: response.links as PaginationLinks[],
                meta: response.meta as PaginationMeta
            }
        }
    } catch (error) {
        console.error('Error fetching chart of accounts:', error)
        accounts.value = []
        pagination.value = undefined
    } finally {
        loading.value = false
    }
}

const changePage = (page: number) => {
    const params = new URLSearchParams()
    if (searchQuery.value) params.append('search', searchQuery.value)
    if (typeFilter.value && typeFilter.value !== 'all') params.append('type', typeFilter.value)
    if (statusFilter.value && statusFilter.value !== 'all') params.append('status', statusFilter.value)
    params.append('page', page.toString())

    router.get(`/finance/chart-of-accounts?${params.toString()}`)
}

const deleteAccount = async (id: number) => {
    if (!id) return

    if (confirm('Are you sure you want to delete this account?')) {
        try {
            await apiService.deleteChartOfAccount(id)
            await fetchAccounts()
        } catch (error) {
            console.error('Error deleting account:', error)
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
        'asset': 'Asset',
        'liability': 'Liability',
        'equity': 'Equity',
        'revenue': 'Revenue',
        'expense': 'Expense'
    }
    return labels[type] || type || 'N/A'
}

const getTypeVariant = (type: string): 'default' | 'secondary' | 'destructive' => {
    const variants: Record<string, 'default' | 'secondary' | 'destructive'> = {
        'asset': 'default',
        'liability': 'destructive',
        'equity': 'secondary',
        'revenue': 'default',
        'expense': 'destructive'
    }
    return variants[type] || 'secondary'
}

const getBalanceColor = (balance: number): string => {
    if (balance > 0) return 'text-green-600'
    if (balance < 0) return 'text-red-600'
    return 'text-white'
}

watch([typeFilter, statusFilter], () => {
    fetchAccounts()
})

onMounted(() => {
    if (accounts.value.length === 0) {
        fetchAccounts()
    }
})
</script>
