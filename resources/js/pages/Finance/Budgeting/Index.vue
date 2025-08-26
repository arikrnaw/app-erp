<template>

    <Head title="Budget Management" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 space-y-6">
            <!-- Header Section -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Budget Management</h1>
                    <p class="text-muted-foreground mt-1">
                        Create and monitor budgets across different categories and periods
                    </p>
                </div>
                <div class="flex items-center space-x-2">
                    <Button variant="outline" size="sm" @click="refreshData" :disabled="loading">
                        <RefreshCw class="h-4 w-4 mr-2" />
                        Refresh
                    </Button>
                    <Button @click="showCreateBudget = true">
                        <Plus class="h-4 w-4 mr-2" />
                        Create Budget
                    </Button>
                </div>
            </div>

            <!-- Budget Overview Cards -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center space-x-4">
                            <div class="p-2 bg-blue-100 dark:bg-blue-900/20 rounded-lg">
                                <Calculator class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-muted-foreground">Total Budget</p>
                                <p class="text-2xl font-bold">{{ formatCurrency(totalBudget) }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center space-x-4">
                            <div class="p-2 bg-red-100 dark:bg-red-900/20 rounded-lg">
                                <TrendingDown class="h-6 w-6 text-red-600 dark:text-red-400" />
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-muted-foreground">Total Spent</p>
                                <p class="text-2xl font-bold">{{ formatCurrency(totalSpent) }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center space-x-4">
                            <div class="p-2 bg-green-100 dark:bg-green-900/20 rounded-lg">
                                <TrendingUp class="h-6 w-6 text-green-600 dark:text-green-400" />
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-muted-foreground">Remaining</p>
                                <p class="text-2xl font-bold">{{ formatCurrency(totalBudget - totalSpent) }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center space-x-4">
                            <div class="p-2 bg-purple-100 dark:bg-purple-900/20 rounded-lg">
                                <AlertTriangle class="h-6 w-6 text-purple-600 dark:text-purple-400" />
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-muted-foreground">Over Budget</p>
                                <p class="text-2xl font-bold">{{ overBudgetCount }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Filters -->
            <Card>
                <CardHeader>
                    <CardTitle>Filters</CardTitle>
                    <CardDescription>
                        Filter budgets by period, category, and status
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pb-4">
                        <div class="space-y-2">
                            <Label for="period_filter">Period</Label>
                            <Select v-model="filters.period" @update:model-value="() => fetchBudgets(1)">
                                <SelectTrigger class="w-full">
                                    <SelectValue placeholder="All Periods" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All Periods</SelectItem>
                                    <SelectItem value="current">Current Month</SelectItem>
                                    <SelectItem value="previous">Previous Month</SelectItem>
                                    <SelectItem value="quarter">Current Quarter</SelectItem>
                                    <SelectItem value="year">Current Year</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="space-y-2">
                            <Label for="category_filter">Category</Label>
                            <Select v-model="filters.category_id" @update:model-value="() => fetchBudgets(1)">
                                <SelectTrigger class="w-full">
                                    <SelectValue placeholder="All Categories" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All Categories</SelectItem>
                                    <SelectItem v-for="category in (budgetCategories || [])" :key="category.id"
                                        :value="category.id">
                                        {{ category.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="space-y-2">
                            <Label for="status_filter">Status</Label>
                            <Select v-model="filters.status" @update:model-value="() => fetchBudgets(1)">
                                <SelectTrigger class="w-full">
                                    <SelectValue placeholder="All Status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All Status</SelectItem>
                                    <SelectItem value="active">Active</SelectItem>
                                    <SelectItem value="inactive">Inactive</SelectItem>
                                    <SelectItem value="draft">Draft</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Budgets Table -->
            <Card>
                <CardHeader>
                    <CardTitle>Budgets</CardTitle>
                    <CardDescription>
                        Manage your budgets and monitor spending
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="rounded-md border mb-6 p-2">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Category</TableHead>
                                    <TableHead>Period</TableHead>
                                    <TableHead>Budgeted Amount</TableHead>
                                    <TableHead>Actual Spent</TableHead>
                                    <TableHead>Remaining</TableHead>
                                    <TableHead>Usage %</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead class="text-right">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-if="loading">
                                    <TableCell colspan="8" class="text-center py-12">
                                        <div class="flex items-center justify-center space-x-2">
                                            <Loader2 class="w-6 h-6 animate-spin" />
                                            <span class="text-muted-foreground">Loading budgets...</span>
                                        </div>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-else-if="!budgets || budgets.length === 0">
                                    <TableCell colspan="8" class="text-center py-12">
                                        <div class="flex flex-col items-center space-y-2">
                                            <Calculator class="h-12 w-12 text-muted-foreground" />
                                            <div class="text-center">
                                                <h3 class="text-lg font-medium">No budgets found</h3>
                                                <p class="text-muted-foreground">
                                                    Create your first budget to start monitoring expenses
                                                </p>
                                            </div>
                                        </div>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-else v-for="budget in (budgets || [])"
                                    :key="budget?.id || `budget-${Math.random()}`">
                                    <TableCell>
                                        <div class="space-y-1">
                                            <div class="font-medium">{{ budget?.category?.name || 'N/A' }}</div>
                                            <div class="text-sm text-muted-foreground max-w-xs truncate">
                                                {{ budget?.description || 'No description' }}
                                            </div>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <div class="text-sm">
                                            {{ budget?.period_start ? formatDate(budget.period_start) : 'N/A' }} - {{
                                                budget?.period_end ? formatDate(budget.period_end) : 'N/A' }}
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <div class="font-medium">{{ budget?.amount ? formatCurrency(budget.amount) :
                                            'N/A' }}</div>
                                    </TableCell>
                                    <TableCell>
                                        <div class="font-medium text-red-600">
                                            {{ budget?.actual_spent ? formatCurrency(budget.actual_spent) : 'N/A' }}
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <div class="font-medium text-green-600">
                                            {{ budget?.remaining_amount ? formatCurrency(budget.remaining_amount) :
                                                'N/A' }}
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <div class="flex items-center space-x-2">
                                            <div class="w-16 bg-gray-200 rounded-full h-2">
                                                <div class="bg-blue-600 h-2 rounded-full"
                                                    :style="`width: ${budget?.actual_spent && budget?.amount ? Math.min((budget.actual_spent / parseFloat(budget.amount)) * 100, 100) : 0}%`">
                                                </div>
                                            </div>
                                            <span class="text-sm font-medium">
                                                {{ budget?.actual_spent && budget?.amount ?
                                                    Math.min((budget.actual_spent / parseFloat(budget.amount)) * 100,
                                                        100).toFixed(1) : 0 }}%
                                            </span>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <Badge :variant="getStatusVariant(budget?.status || 'draft')"
                                            class="capitalize">
                                            {{ budget?.status || 'draft' }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <DropdownMenu>
                                            <DropdownMenuTrigger asChild>
                                                <Button variant="ghost" class="h-8 w-8 p-0">
                                                    <MoreHorizontal class="h-4 w-4" />
                                                </Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end">
                                                <DropdownMenuItem @click="editBudget(budget)">
                                                    <Edit class="mr-2 h-4 w-4" />
                                                    Edit Budget
                                                </DropdownMenuItem>
                                                <DropdownMenuItem @click="viewVariance(budget)">
                                                    <BarChart3 class="mr-2 h-4 w-4" />
                                                    View Variance
                                                </DropdownMenuItem>
                                                <DropdownMenuSeparator />
                                                <DropdownMenuItem @click="deleteBudget(budget?.id)"
                                                    class="text-destructive">
                                                    <Trash2 class="mr-2 h-4 w-4" />
                                                    Delete Budget
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>

                        <!-- Pagination -->

                        <DataPagination v-if="pagination && pagination.total > 0"
                            :current-page="pagination.current_page" :total-pages="pagination.last_page"
                            :total-items="pagination.total" :per-page="pagination.per_page"
                            @page-change="(page) => fetchBudgets(page)" />
                    </div>
                </CardContent>
            </Card>

            <!-- Create/Edit Budget Dialog -->
            <Dialog :open="showCreateBudget" @update:open="showCreateBudget = false">
                <DialogContent class="sm:max-w-[500px]">
                    <DialogHeader>
                        <DialogTitle>{{ editingBudget ? 'Edit Budget' : 'Create New Budget' }}</DialogTitle>
                        <DialogDescription>
                            {{ editingBudget ? 'Update budget details' : 'Set up a new budget for monitoring expenses'
                            }}
                        </DialogDescription>
                    </DialogHeader>
                    <form @submit.prevent="saveBudget" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="category_id">Category</Label>
                                <Select v-model="budgetForm.category_id" required>
                                    <SelectTrigger class="w-full">
                                        <SelectValue :placeholder="getCategoryName(budgetForm.category_id)" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="category in (budgetCategories || [])" :key="category.id"
                                            :value="category.id">
                                            {{ category.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div class="space-y-2">
                                <Label for="period_id">Budget Period</Label>
                                <Select v-model="budgetForm.period_id" required>
                                    <SelectTrigger class="w-full">
                                        <SelectValue :placeholder="getPeriodName(budgetForm.period_id)" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="period in (budgetPeriods || [])" :key="period.id"
                                            :value="period.id">
                                            {{ period.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="amount">Amount</Label>
                                <Input id="amount" v-model="budgetForm.amount" type="number" step="0.01" required />
                            </div>
                            <div class="space-y-2">
                                <Label for="status">Status</Label>
                                <Select v-model="budgetForm.status" required>
                                    <SelectTrigger class="w-full">
                                        <SelectValue :placeholder="budgetForm.status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="draft">Draft</SelectItem>
                                        <SelectItem value="active">Active</SelectItem>
                                        <SelectItem value="inactive">Inactive</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="description">Description</Label>
                            <Textarea id="description" v-model="budgetForm.description"
                                placeholder="Budget description..." />
                        </div>
                        <DialogFooter>
                            <Button type="button" variant="outline" @click="showCreateBudget = false">
                                Cancel
                            </Button>
                            <Button type="submit" :disabled="saving">
                                <Loader2 v-if="saving" class="mr-2 h-4 w-4 animate-spin" />
                                {{ editingBudget ? 'Update' : 'Create' }}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Badge } from '@/components/ui/badge'
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import { DataPagination } from '@/components/ui/pagination'


import {
    Calculator,
    TrendingDown,
    TrendingUp,
    AlertTriangle,
    RefreshCw,
    Plus,
    MoreHorizontal,
    Edit,
    BarChart3,
    Trash2,
    Loader2
} from 'lucide-vue-next'
import { apiService } from '@/services/api'
import type { BreadcrumbItemType } from '@/types'

interface BudgetCategory {
    id: number
    name: string
    description: string
    type: string
}

interface Budget {
    id: number
    company_id: number
    category_id: number
    period_id: number
    period_start: string
    period_end: string
    amount: string
    description: string
    status: string
    created_by: number
    updated_by: number
    created_at: string
    updated_at: string
    deleted_at: string | null
    actual_spent?: number
    variance?: number
    variance_percentage?: number
    remaining_amount?: number
    is_over_budget?: boolean
    is_under_budget?: boolean
    category?: BudgetCategory
    period?: {
        id: number
        name: string
        description: string
        start_date: string
        end_date: string
        fiscal_year: string
        status: string
    }
}

interface PaginationData {
    current_page: number
    data: Budget[]
    first_page_url: string
    from: number
    last_page: number
    last_page_url: string
    links: Array<{
        url: string | null
        label: string
        page: number | null
        active: boolean
    }>
    next_page_url: string | null
    path: string
    per_page: number
    prev_page_url: string | null
    to: number
    total: number
}

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Finance', href: '/finance' },
    { title: 'Budget Management', href: '/finance/budgeting' }
]

const loading = ref(false)
const saving = ref(false)
const budgets = ref<Budget[]>([])
const budgetCategories = ref<BudgetCategory[]>([])
const budgetPeriods = ref<any[]>([])
const pagination = ref<PaginationData | null>(null)
const showCreateBudget = ref(false)
const editingBudget = ref<Budget | null>(null)

const filters = ref({
    period: 'all',
    category_id: 'all',
    status: 'all'
})

const perPage = ref(5)

const budgetForm = ref({
    category_id: '',
    period_id: '',
    amount: '',
    description: '',
    status: 'draft'
})

// Computed properties
const totalBudget = computed(() => {
    if (!Array.isArray(budgets.value)) return 0
    return budgets.value.reduce((sum, budget) => sum + parseFloat(budget.amount), 0)
})
const totalSpent = computed(() => {
    if (!Array.isArray(budgets.value)) return 0
    return budgets.value.reduce((sum, budget) => sum + (budget.actual_spent || 0), 0)
})
const overBudgetCount = computed(() => {
    if (!Array.isArray(budgets.value)) return 0
    return budgets.value.filter(budget => budget.is_over_budget).length
})

const fetchBudgets = async (page: number = 1) => {
    loading.value = true
    try {
        const params = {
            period: filters.value.period === 'all' ? '' : filters.value.period,
            category_id: filters.value.category_id === 'all' ? '' : filters.value.category_id,
            status: filters.value.status === 'all' ? '' : filters.value.status,
            page,
            per_page: perPage.value
        }
        const response = await apiService.get('/finance/budgeting/budgets', { params })

        if (response.data.success) {
            budgets.value = response.data.data.data || []
            pagination.value = response.data.data
        }
    } catch (error) {
        console.error('Error fetching budgets:', error)
    } finally {
        loading.value = false
    }
}

const fetchBudgetCategories = async () => {
    try {
        const response = await apiService.get('/finance/budgeting/categories')
        budgetCategories.value = response.data.data || []
    } catch (error) {
        console.error('Error fetching budget categories:', error)
    }
}

const fetchBudgetPeriods = async () => {
    try {
        const response = await apiService.get('/finance/budgeting/periods')
        budgetPeriods.value = response.data.data || []
    } catch (error) {
        console.error('Error fetching budget periods:', error)
    }
}

const refreshData = () => {
    fetchBudgets(1)
    fetchBudgetCategories()
    fetchBudgetPeriods()
}

const editBudget = (budget: Budget) => {
    editingBudget.value = budget
    console.log('Editing budget:', budget) // Debug log
    budgetForm.value = {
        category_id: budget.category_id ? budget.category_id.toString() : '',
        period_id: budget.period_id ? budget.period_id.toString() : '',
        amount: budget.amount ? budget.amount.toString() : '',
        description: budget.description || '',
        status: budget.status || 'draft'
    }
    console.log('Form data:', budgetForm.value) // Debug log
    showCreateBudget.value = true
}

const saveBudget = async () => {
    saving.value = true
    try {
        if (editingBudget.value) {
            await apiService.patch(`/finance/budgeting/budgets/${editingBudget.value.id}`, budgetForm.value)
        } else {
            await apiService.post('/finance/budgeting/budgets', budgetForm.value)
        }
        showCreateBudget.value = false
        editingBudget.value = null
        resetBudgetForm()
        fetchBudgets(1)
    } catch (error) {
        console.error('Error saving budget:', error)
    } finally {
        saving.value = false
    }
}

const deleteBudget = async (id: number) => {
    if (confirm('Are you sure you want to delete this budget?')) {
        try {
            await apiService.delete(`/finance/budgeting/budgets/${id}`)
            fetchBudgets(1)
        } catch (error) {
            console.error('Error deleting budget:', error)
        }
    }
}

const resetBudgetForm = () => {
    budgetForm.value = {
        category_id: '',
        period_id: '',
        amount: '',
        description: '',
        status: 'draft'
    }
}

const viewVariance = (budget: Budget) => {
    // Navigate to variance analysis page
    console.log('View variance for budget:', budget.id)
}

const formatCurrency = (amount: number | string) => {
    const numAmount = typeof amount === 'string' ? parseFloat(amount) : amount
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(numAmount)
}

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    })
}


const getStatusVariant = (status: string) => {
    switch (status) {
        case 'active':
            return 'default'
        case 'inactive':
            return 'secondary'
        case 'draft':
            return 'outline'
        default:
            return 'outline'
    }
}



const getCategoryName = (categoryId: string | number) => {
    if (!categoryId) return 'Select category'
    const category = budgetCategories.value.find(cat => cat.id.toString() === categoryId.toString())
    return category ? category.name : 'Unknown category'
}

const getPeriodName = (periodId: string | number) => {
    if (!periodId) return 'Select period'
    const period = budgetPeriods.value.find(per => per.id.toString() === periodId.toString())
    return period ? period.name : 'Unknown period'
}



onMounted(() => {
    fetchBudgets(1)
    fetchBudgetCategories()
    fetchBudgetPeriods()
})
</script>
