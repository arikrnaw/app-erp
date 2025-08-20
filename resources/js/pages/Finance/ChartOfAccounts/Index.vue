<template>

    <Head title="Chart of Accounts" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 space-y-6">
            <!-- Header Section -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Chart of Accounts</h1>
                    <p class="text-muted-foreground mt-1">
                        Manage your company's chart of accounts and financial structure
                    </p>
                </div>
                <div class="flex items-center space-x-2">
                    <Button variant="outline" size="sm" @click="exportAccounts" :disabled="loading">
                        <Download class="h-4 w-4 mr-2" />
                        Export
                    </Button>
                    <Link :href="route('finance.chart-of-accounts.create')">
                    <Button>
                        <Plus class="w-4 h-4 mr-2" />
                        Add Account
                    </Button>
                    </Link>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center space-x-4">
                            <div class="p-2 bg-blue-100 dark:bg-blue-900/20 rounded-lg">
                                <BookOpen class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-muted-foreground">Total Accounts</p>
                                <p class="text-2xl font-bold">{{ accounts.length }}</p>
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
                                <p class="text-sm font-medium text-muted-foreground">Active Accounts</p>
                                <p class="text-2xl font-bold">{{ activeAccountsCount }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center space-x-4">
                            <div class="p-2 bg-purple-100 dark:bg-purple-900/20 rounded-lg">
                                <Layers class="h-6 w-6 text-purple-600 dark:text-purple-400" />
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-muted-foreground">Account Types</p>
                                <p class="text-2xl font-bold">{{ accountTypesCount }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center space-x-4">
                            <div class="p-2 bg-orange-100 dark:bg-orange-900/20 rounded-lg">
                                <Calculator class="h-6 w-6 text-orange-600 dark:text-orange-400" />
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-muted-foreground">Total Balance</p>
                                <p class="text-2xl font-bold">{{ formatCurrency(totalBalance) }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Search and Filters -->
            <Card>
                <CardContent class="p-6">
                    <div class="flex flex-col lg:flex-row gap-4">
                        <div class="flex-1">
                            <div class="relative">
                                <Search
                                    class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                                <Input v-model="searchQuery"
                                    placeholder="Search accounts by code, name, or description..." class="pl-10"
                                    @input="debouncedSearch" />
                            </div>
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
                            <Button variant="outline" @click="clearFilters">
                                <X class="h-4 w-4 mr-2" />
                                Clear
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Accounts Table -->
            <Card>
                <CardHeader>
                    <CardTitle>Accounts</CardTitle>
                    <CardDescription>
                        Manage your chart of accounts and financial structure
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Account Code</TableHead>
                                    <TableHead>Account Name</TableHead>
                                    <TableHead>Type</TableHead>
                                    <TableHead>Parent Account</TableHead>
                                    <TableHead class="text-right">Balance</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead class="text-right">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-if="loading">
                                    <TableCell colspan="7" class="text-center py-12">
                                        <div class="flex items-center justify-center space-x-2">
                                            <Loader2 class="w-6 h-6 animate-spin" />
                                            <span class="text-muted-foreground">Loading accounts...</span>
                                        </div>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-else-if="filteredAccounts.length === 0">
                                    <TableCell colspan="7" class="text-center py-12">
                                        <div class="flex flex-col items-center space-y-2">
                                            <BookOpen class="h-12 w-12 text-muted-foreground" />
                                            <div class="text-center">
                                                <h3 class="text-lg font-medium">No accounts found</h3>
                                                <p class="text-muted-foreground">
                                                    {{ searchQuery || typeFilter !== 'all' || statusFilter !== 'all'
                                                        ? 'Try adjusting your search or filters'
                                                        : 'Get started by creating your first account' }}
                                                </p>
                                            </div>
                                        </div>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-else v-for="account in filteredAccounts" :key="account.id">
                                    <TableCell>
                                        <div class="font-mono text-sm font-medium">{{ account.account_code }}</div>
                                    </TableCell>
                                    <TableCell>
                                        <div class="space-y-1">
                                            <div class="font-medium">{{ account.name }}</div>
                                            <div class="text-sm text-muted-foreground max-w-xs truncate">
                                                {{ account.description || 'No description' }}
                                            </div>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <Badge :variant="getTypeVariant(account.type)" class="capitalize">
                                            {{ getTypeLabel(account.type) }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        <div class="text-sm text-muted-foreground">
                                            {{ account.parent?.name || 'Root Account' }}
                                        </div>
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <div class="font-medium" :class="getBalanceColor(account.balance || 0)">
                                            {{ formatCurrency(account.balance || 0) }}
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <Badge :variant="account.status === 'active' ? 'default' : 'secondary'">
                                            {{ account.status === 'active' ? 'Active' : 'Inactive' }}
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
                                                    View Details
                                                    </Link>
                                                </DropdownMenuItem>
                                                <DropdownMenuItem as-child>
                                                    <Link :href="route('finance.chart-of-accounts.edit', account.id)">
                                                    <Edit class="w-4 h-4 mr-2" />
                                                    Edit Account
                                                    </Link>
                                                </DropdownMenuItem>
                                                <DropdownMenuSeparator />
                                                <DropdownMenuItem @click="deleteAccount(account.id)"
                                                    class="text-destructive">
                                                    <Trash2 class="w-4 h-4 mr-2" />
                                                    Delete Account
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
                            }} results
                        </div>
                        <div class="flex gap-2">
                            <Button variant="outline" size="sm" :disabled="pagination.meta.current_page === 1"
                                @click="changePage(pagination.meta.current_page - 1)">
                                <ChevronLeft class="h-4 w-4 mr-1" />
                                Previous
                            </Button>
                            <Button variant="outline" size="sm"
                                :disabled="pagination.meta.current_page === pagination.meta.last_page"
                                @click="changePage(pagination.meta.current_page + 1)">
                                Next
                                <ChevronRight class="h-4 w-4 ml-1" />
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, watch, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Badge } from '@/components/ui/badge'
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import {
    Plus,
    MoreHorizontal,
    Eye,
    Edit,
    Trash2,
    Loader2,
    Search,
    X,
    BookOpen,
    CheckCircle,
    Layers,
    Calculator,
    Download,
    ChevronLeft,
    ChevronRight
} from 'lucide-vue-next'
import { apiService } from '@/services/api'
import type { ChartOfAccount, PaginatedData } from '@/types/erp'
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

const loading = ref(false)
const searchQuery = ref('')
const typeFilter = ref('all')
const statusFilter = ref('all')

// Reactive data for accounts from API
const accounts = ref<ChartOfAccount[]>([])
const pagination = ref<any>(null)

// Computed properties
const activeAccountsCount = computed(() => {
    return accounts.value.filter((account: ChartOfAccount) => account.status === 'active').length
})

const accountTypesCount = computed(() => {
    const types = new Set(accounts.value.map((account: ChartOfAccount) => account.type))
    return types.size
})

const totalBalance = computed(() => {
    return accounts.value.reduce((sum: number, account: ChartOfAccount) => {
        return sum + (account.balance || 0)
    }, 0)
})

const filteredAccounts = computed(() => {
    let filtered = accounts.value

    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase()
        filtered = filtered.filter((account: ChartOfAccount) =>
            account.account_code?.toLowerCase().includes(query) ||
            account.name?.toLowerCase().includes(query) ||
            account.description?.toLowerCase().includes(query)
        )
    }

    if (typeFilter.value !== 'all') {
        filtered = filtered.filter((account: ChartOfAccount) => account.type === typeFilter.value)
    }

    if (statusFilter.value !== 'all') {
        const isActive = statusFilter.value === 'active'
        filtered = filtered.filter((account: ChartOfAccount) => account.status === statusFilter.value)
    }

    return filtered
})

// Methods
const debouncedSearch = async () => {
    loading.value = true
    try {
        const params: any = {}
        if (searchQuery.value) params.search = searchQuery.value
        if (typeFilter.value !== 'all') params.type = typeFilter.value
        if (statusFilter.value !== 'all') params.status = statusFilter.value

        const response = await apiService.getChartOfAccounts(params)
        accounts.value = response.data || []
        pagination.value = response
    } catch (error) {
        console.error('Error searching accounts:', error)
    } finally {
        loading.value = false
    }
}

const clearFilters = () => {
    searchQuery.value = ''
    typeFilter.value = 'all'
    statusFilter.value = 'all'
}

const getTypeVariant = (type: string): 'default' | 'secondary' | 'outline' | 'destructive' => {
    const variants: Record<string, 'default' | 'secondary' | 'outline' | 'destructive'> = {
        'asset': 'default',
        'liability': 'secondary',
        'equity': 'outline',
        'revenue': 'default',
        'expense': 'destructive'
    }
    return variants[type] || 'secondary'
}

const getTypeLabel = (type: string) => {
    return type.charAt(0).toUpperCase() + type.slice(1)
}

const getBalanceColor = (balance: number) => {
    if (balance > 0) return 'text-green-600 dark:text-green-400'
    if (balance < 0) return 'text-red-600 dark:text-red-400'
    return 'text-muted-foreground'
}

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(amount)
}

const deleteAccount = async (id: number) => {
    if (confirm('Are you sure you want to delete this account?')) {
        try {
            await apiService.deleteChartOfAccount(id)
            // Reload data after successful deletion
            const response = await apiService.getChartOfAccounts()
            accounts.value = response.data || []
            pagination.value = response
        } catch (error) {
            console.error('Error deleting account:', error)
        }
    }
}

const changePage = async (page: number) => {
    loading.value = true
    try {
        const response = await apiService.getChartOfAccounts({ page })
        accounts.value = response.data || []
        pagination.value = response
    } catch (error) {
        console.error('Error loading chart of accounts:', error)
    } finally {
        loading.value = false
    }
}

const exportAccounts = async () => {
    try {
        const params: any = {}
        if (searchQuery.value) params.search = searchQuery.value
        if (typeFilter.value !== 'all') params.type = typeFilter.value
        if (statusFilter.value !== 'all') params.status = statusFilter.value

        const blob = await apiService.exportChartOfAccounts(params)

        // Create download link
        const url = window.URL.createObjectURL(blob)
        const link = document.createElement('a')
        link.href = url
        link.download = `chart_of_accounts_${new Date().toISOString().slice(0, 10)}.csv`
        document.body.appendChild(link)
        link.click()
        document.body.removeChild(link)
        window.URL.revokeObjectURL(url)
    } catch (error) {
        console.error('Error exporting accounts:', error)
        alert('Failed to export accounts. Please try again.')
    }
}

onMounted(async () => {
    // Always load data from API
    loading.value = true
    try {
        const response = await apiService.getChartOfAccounts()
        // Update the component data with the API response
        accounts.value = response.data || []
        pagination.value = response
        console.log('Chart of accounts loaded:', response)
    } catch (error) {
        console.error('Error loading chart of accounts:', error)
    } finally {
        loading.value = false
    }
})

// Watchers for search and filters
watch([searchQuery, typeFilter, statusFilter], () => {
    debouncedSearch()
}, { deep: true })
</script>
