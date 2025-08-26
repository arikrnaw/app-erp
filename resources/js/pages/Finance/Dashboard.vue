<template>

    <Head title="Finance Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 space-y-6">
            <!-- Header Section -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Finance Dashboard</h1>
                    <p class="text-muted-foreground mt-1">
                        Comprehensive overview of your financial performance
                    </p>
                </div>
                <div class="flex items-center space-x-2">
                    <Button variant="outline" size="sm" @click="refreshData" :disabled="loading">
                        <RefreshCw class="h-4 w-4 mr-2" />
                        Refresh
                    </Button>
                    <Button variant="outline" size="sm">
                        <Download class="h-4 w-4 mr-2" />
                        Export
                    </Button>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center space-x-4">
                            <div class="p-2 bg-blue-100 dark:bg-blue-900/20 rounded-lg">
                                <TrendingUp class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-muted-foreground">Total Cash</p>
                                <p class="text-2xl font-bold">{{ formatCurrency(dashboardData.cash_position?.total_cash
                                    || 0) }}</p>
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
                                <p class="text-sm font-medium text-muted-foreground">Monthly Expenses</p>
                                <p class="text-2xl font-bold">{{
                                    formatCurrency(dashboardData.current_month?.expenses?.total || 0) }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center space-x-4">
                            <div class="p-2 bg-green-100 dark:bg-green-900/20 rounded-lg">
                                <Calculator class="h-6 w-6 text-green-600 dark:text-green-400" />
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-muted-foreground">Asset Purchases</p>
                                <p class="text-2xl font-bold">{{
                                    formatCurrency(dashboardData.current_month?.assets?.total || 0) }}</p>
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
                                <p class="text-sm font-medium text-muted-foreground">Pending Approvals</p>
                                <p class="text-2xl font-bold">{{ dashboardData.pending_approvals?.length || 0 }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Cash Flow Chart -->
            <Card>
                <CardHeader>
                    <CardTitle>Cash Flow Overview</CardTitle>
                    <CardDescription>
                        Monthly cash inflows and outflows
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="h-80 flex items-center justify-center bg-muted/20 rounded-lg">
                        <div class="text-center text-muted-foreground">
                            <p class="text-lg font-medium">Chart Component</p>
                            <p class="text-sm">Chart visualization will be implemented here</p>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Budget Variance & Alerts -->
            <div class="grid gap-6 md:grid-cols-2">
                <!-- Budget Variance -->
                <Card>
                    <CardHeader>
                        <CardTitle>Budget Variance</CardTitle>
                        <CardDescription>
                            Current month budget vs actual spending
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div v-if="loading" class="flex items-center justify-center py-8">
                                <Loader2 class="w-6 h-6 animate-spin" />
                            </div>
                            <div v-else-if="dashboardData.current_month?.budget_variance?.length === 0"
                                class="text-center py-8">
                                <p class="text-muted-foreground">No budget data available</p>
                            </div>
                            <div v-else class="space-y-3">
                                <div v-for="variance in dashboardData.current_month?.budget_variance"
                                    :key="variance.category"
                                    class="flex items-center justify-between p-3 rounded-lg border">
                                    <div class="space-y-1">
                                        <p class="font-medium">{{ variance.category }}</p>
                                        <p class="text-sm text-muted-foreground">
                                            Budget: {{ formatCurrency(variance.budgeted) }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-medium">{{ formatCurrency(variance.actual) }}</p>
                                        <p class="text-sm" :class="getVarianceColor(variance.variance_percentage)">
                                            {{ variance.variance_percentage.toFixed(1) }}%
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Budget Alerts -->
                <Card>
                    <CardHeader>
                        <CardTitle>Budget Alerts</CardTitle>
                        <CardDescription>
                            Categories approaching or exceeding budget limits
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div v-if="loading" class="flex items-center justify-center py-8">
                                <Loader2 class="w-6 h-6 animate-spin" />
                            </div>
                            <div v-else-if="dashboardData.budget_alerts?.length === 0" class="text-center py-8">
                                <p class="text-muted-foreground">No budget alerts</p>
                            </div>
                            <div v-else class="space-y-3">
                                <div v-for="alert in dashboardData.budget_alerts" :key="alert.category"
                                    class="flex items-center justify-between p-3 rounded-lg border"
                                    :class="getAlertBorderColor(alert.alert_level)">
                                    <div class="space-y-1">
                                        <p class="font-medium">{{ alert.category }}</p>
                                        <p class="text-sm text-muted-foreground">
                                            {{ alert.percentage_used.toFixed(1) }}% of budget used
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-medium">{{ formatCurrency(alert.actual) }}</p>
                                        <p class="text-sm" :class="getAlertTextColor(alert.alert_level)">
                                            {{ alert.alert_level === 'over_budget' ? 'Over Budget' : 'Near Limit' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Pending Approvals -->
            <Card>
                <CardHeader>
                    <CardTitle>Pending Approvals</CardTitle>
                    <CardDescription>
                        Financial requests awaiting your approval
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <div v-if="loading" class="flex items-center justify-center py-8">
                            <Loader2 class="w-6 h-6 animate-spin" />
                        </div>
                        <div v-else-if="dashboardData.pending_approvals?.length === 0" class="text-center py-8">
                            <p class="text-muted-foreground">No pending approvals</p>
                        </div>
                        <div v-else class="space-y-3">
                            <div v-for="approval in dashboardData.pending_approvals" :key="approval.id"
                                class="flex items-center justify-between p-4 rounded-lg border">
                                <div class="flex items-center space-x-4">
                                    <div class="p-2 rounded-lg" :class="getApprovalTypeColor(approval.type)">
                                        <component :is="getApprovalIcon(approval.type)" class="h-5 w-5" />
                                    </div>
                                    <div class="space-y-1">
                                        <p class="font-medium">{{ approval.description }}</p>
                                        <p class="text-sm text-muted-foreground">
                                            Requested by {{ approval.requestor }} • {{ formatDate(approval.created_at)
                                            }}
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-medium">{{ formatCurrency(approval.amount) }}</p>
                                    <Badge :variant="getPriorityVariant(approval.priority)">
                                        {{ approval.priority }}
                                    </Badge>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Recent Transactions -->
            <Card>
                <CardHeader>
                    <CardTitle>Recent Financial Activities</CardTitle>
                    <CardDescription>
                        Latest expenses, asset purchases, and cash transactions
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <Tabs defaultValue="expenses" class="w-full">
                        <TabsList class="grid w-full grid-cols-3">
                            <TabsTrigger value="expenses">Expenses</TabsTrigger>
                            <TabsTrigger value="assets">Assets</TabsTrigger>
                            <TabsTrigger value="cash">Cash Flow</TabsTrigger>
                        </TabsList>
                        <TabsContent value="expenses" class="space-y-4">
                            <div class="space-y-3">
                                <div v-for="expense in dashboardData.current_month?.expenses?.by_category"
                                    :key="expense.category"
                                    class="flex items-center justify-between p-3 rounded-lg border">
                                    <div>
                                        <p class="font-medium">{{ expense.category }}</p>
                                        <p class="text-sm text-muted-foreground">{{ expense.count }} transactions</p>
                                    </div>
                                    <p class="font-medium text-red-600">{{ formatCurrency(expense.total) }}</p>
                                </div>
                            </div>
                        </TabsContent>
                        <TabsContent value="assets" class="space-y-4">
                            <div class="space-y-3">
                                <div v-for="asset in dashboardData.current_month?.assets?.by_category"
                                    :key="asset.category"
                                    class="flex items-center justify-between p-3 rounded-lg border">
                                    <div>
                                        <p class="font-medium">{{ asset.category }}</p>
                                        <p class="text-sm text-muted-foreground">{{ asset.count }} purchases</p>
                                    </div>
                                    <p class="font-medium text-blue-600">{{ formatCurrency(asset.total) }}</p>
                                </div>
                            </div>
                        </TabsContent>
                        <TabsContent value="cash" class="space-y-4">
                            <div class="space-y-3">
                                <div class="flex items-center justify-between p-3 rounded-lg border">
                                    <div>
                                        <p class="font-medium">Cash Inflows</p>
                                        <p class="text-sm text-muted-foreground">Deposits & transfers</p>
                                    </div>
                                    <p class="font-medium text-green-600">{{
                                        formatCurrency(dashboardData.current_month?.cash_flow?.inflows || 0) }}</p>
                                </div>
                                <div class="flex items-center justify-between p-3 rounded-lg border">
                                    <div>
                                        <p class="font-medium">Cash Outflows</p>
                                        <p class="text-sm text-muted-foreground">Withdrawals & expenses</p>
                                    </div>
                                    <p class="font-medium text-red-600">{{
                                        formatCurrency(dashboardData.current_month?.cash_flow?.outflows || 0) }}</p>
                                </div>
                                <div class="flex items-center justify-between p-3 rounded-lg border bg-muted/50">
                                    <div>
                                        <p class="font-medium">Net Cash Flow</p>
                                        <p class="text-sm text-muted-foreground">Current month</p>
                                    </div>
                                    <p class="font-medium"
                                        :class="getCashFlowColor(dashboardData.current_month?.cash_flow?.net_flow || 0)">
                                        {{ formatCurrency(dashboardData.current_month?.cash_flow?.net_flow || 0) }}
                                    </p>
                                </div>
                            </div>
                        </TabsContent>
                    </Tabs>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'

import {
    TrendingUp,
    TrendingDown,
    Calculator,
    AlertTriangle,
    RefreshCw,
    Download,
    Loader2,
    Receipt,
    Building2,
    CreditCard
} from 'lucide-vue-next'
import { apiService } from '@/services/api'
import type { BreadcrumbItemType } from '@/types'

interface DashboardData {
    current_month: {
        expenses: {
            total: number
            count: number
            by_category: Array<{ category: string; total: number; count: number }>
        }
        assets: {
            total: number
            count: number
            by_category: Array<{ category: string; total: number; count: number }>
        }
        cash_flow: {
            inflows: number
            outflows: number
            net_flow: number
        }
        budget_variance: Array<{
            category: string
            budgeted: number
            actual: number
            variance: number
            variance_percentage: number
            status: string
        }>
    }
    pending_approvals: Array<{
        id: number
        type: string
        amount: number
        description: string
        requestor: string
        priority: string
        due_date: string
        created_at: string
    }>
    budget_alerts: Array<{
        category: string
        budgeted: number
        actual: number
        percentage_used: number
        remaining: number
        alert_level: string
    }>
    cash_position: {
        cash_on_hand: number
        bank_balance: number
        total_cash: number
        last_updated: string
    }
}

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Finance', href: '/finance' },
    { title: 'Finance Dashboard', href: '/finance/dashboard' }
]

const loading = ref(false)
const dashboardData = ref<DashboardData>({} as DashboardData)

const cashFlowData = ref([
    { name: 'Inflows', data: [30, 40, 35, 50, 49, 60] },
    { name: 'Outflows', data: [20, 35, 30, 45, 40, 55] }
])

const fetchDashboardData = async () => {
    loading.value = true
    try {
        const response = await apiService.get('/finance/dashboard')
        dashboardData.value = response.data
    } catch (error) {
        console.error('Error fetching dashboard data:', error)
    } finally {
        loading.value = false
    }
}

const refreshData = () => {
    fetchDashboardData()
}

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(amount)
}

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    })
}

const getVarianceColor = (percentage: number) => {
    if (percentage >= 10) return 'text-red-600 dark:text-red-400'
    if (percentage <= -10) return 'text-green-600 dark:text-green-400'
    return 'text-yellow-600 dark:text-yellow-400'
}

const getAlertBorderColor = (level: string) => {
    if (level === 'over_budget') return 'border-red-200 bg-red-50 dark:border-red-800 dark:bg-red-950/20'
    return 'border-yellow-200 bg-yellow-50 dark:border-yellow-800 dark:bg-yellow-950/20'
}

const getAlertTextColor = (level: string) => {
    if (level === 'over_budget') return 'text-red-600 dark:text-red-400'
    return 'text-yellow-600 dark:text-yellow-400'
}

const getApprovalTypeColor = (type: string) => {
    switch (type) {
        case 'Expense':
            return 'bg-red-100 text-red-600 dark:bg-red-900/20 dark:text-red-400'
        case 'AssetPurchase':
            return 'bg-blue-100 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400'
        default:
            return 'bg-gray-100 text-gray-600 dark:bg-gray-900/20 dark:text-gray-400'
    }
}

const getApprovalIcon = (type: string) => {
    switch (type) {
        case 'Expense':
            return Receipt
        case 'AssetPurchase':
            return Building2
        default:
            return CreditCard
    }
}

const getPriorityVariant = (priority: string) => {
    switch (priority) {
        case 'high':
            return 'destructive'
        case 'medium':
            return 'default'
        case 'low':
            return 'secondary'
        default:
            return 'outline'
    }
}

const getCashFlowColor = (amount: number) => {
    if (amount > 0) return 'text-green-600 dark:text-green-400'
    if (amount < 0) return 'text-red-600 dark:text-red-400'
    return 'text-muted-foreground'
}

onMounted(() => {
    fetchDashboardData()
})
</script>
