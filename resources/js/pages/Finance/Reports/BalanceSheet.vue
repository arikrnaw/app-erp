<template>

    <Head title="Balance Sheet" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 space-y-6">
            <!-- Header Section -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Balance Sheet</h1>
                    <p class="text-muted-foreground mt-1">
                        Financial position as of {{ formatDate(reportDate) }}
                    </p>
                </div>
                <div class="flex items-center space-x-2">
                    <Input type="date" v-model="reportDate" class="w-[180px]" @change="fetchBalanceSheet" />
                    <Button @click="exportReport" variant="outline" :disabled="loading">
                        <Download class="w-4 h-4 mr-2" />
                        Export PDF
                    </Button>
                    <Button @click="exportExcel" variant="outline" :disabled="loading">
                        <Download class="w-4 h-4 mr-2" />
                        Export Excel
                    </Button>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid gap-4 md:grid-cols-3">
                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center space-x-4">
                            <div class="p-2 bg-green-100 dark:bg-green-900/20 rounded-lg">
                                <TrendingUp class="h-6 w-6 text-green-600 dark:text-green-400" />
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-muted-foreground">Total Assets</p>
                                <p class="text-2xl font-bold text-green-600 dark:text-green-400">
                                    {{ formatCurrency(balanceSheet?.total_assets || 0) }}
                                </p>
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
                                <p class="text-sm font-medium text-muted-foreground">Total Liabilities</p>
                                <p class="text-2xl font-bold text-red-600 dark:text-red-400">
                                    {{ formatCurrency(balanceSheet?.total_liabilities || 0) }}
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center space-x-4">
                            <div class="p-2 bg-blue-100 dark:bg-blue-900/20 rounded-lg">
                                <Calculator class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-muted-foreground">Total Equity</p>
                                <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                    {{ formatCurrency(balanceSheet?.total_equity || 0) }}
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="flex items-center justify-center py-12">
                <div class="flex items-center space-x-2">
                    <Loader2 class="w-8 h-8 animate-spin" />
                    <span class="text-muted-foreground">Loading balance sheet...</span>
                </div>
            </div>

            <!-- Balance Sheet Content -->
            <div v-else-if="balanceSheet" class="space-y-6">
                <!-- Report Header -->
                <Card>
                    <CardContent class="p-6 text-center">
                        <h3 class="text-2xl font-bold">Balance Sheet</h3>
                        <p class="text-muted-foreground mt-1">As of {{ formatDate(balanceSheet.report_date) }}</p>
                    </CardContent>
                </Card>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Assets -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-green-700 dark:text-green-400 flex items-center">
                                <TrendingUp class="w-5 h-5 mr-2" />
                                Assets
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-3">
                                <div v-for="account in balanceSheet.assets.accounts" :key="account.account_code"
                                    class="flex justify-between items-center py-2 border-b border-border last:border-b-0">
                                    <div>
                                        <div class="font-medium text-sm">{{ account.account_name }}</div>
                                        <div class="text-xs text-muted-foreground">{{ account.account_code }}</div>
                                    </div>
                                    <div class="text-sm font-mono"
                                        :class="account.balance >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
                                        {{ formatCurrency(account.balance) }}
                                    </div>
                                </div>
                                <div
                                    class="flex justify-between items-center py-3 border-t-2 border-green-200 dark:border-green-800 font-semibold">
                                    <span>Total Assets</span>
                                    <span class="text-green-700 dark:text-green-400">{{
                                        formatCurrency(balanceSheet.total_assets) }}</span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Liabilities -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-red-700 dark:text-red-400 flex items-center">
                                <TrendingDown class="w-5 h-5 mr-2" />
                                Liabilities
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-3">
                                <div v-for="account in balanceSheet.liabilities.accounts" :key="account.account_code"
                                    class="flex justify-between items-center py-2 border-b border-border last:border-b-0">
                                    <div>
                                        <div class="font-medium text-sm">{{ account.account_name }}</div>
                                        <div class="text-xs text-muted-foreground">{{ account.account_code }}</div>
                                    </div>
                                    <div class="text-sm font-mono"
                                        :class="account.balance >= 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400'">
                                        {{ formatCurrency(account.balance) }}
                                    </div>
                                </div>
                                <div
                                    class="flex justify-between items-center py-3 border-t-2 border-red-200 dark:border-red-800 font-semibold">
                                    <span>Total Liabilities</span>
                                    <span class="text-red-700 dark:text-red-400">{{
                                        formatCurrency(balanceSheet.total_liabilities) }}</span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Equity -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-blue-700 dark:text-blue-400 flex items-center">
                                <Calculator class="w-5 h-5 mr-2" />
                                Equity
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-3">
                                <div v-for="account in balanceSheet.equity.accounts" :key="account.account_code"
                                    class="flex justify-between items-center py-2 border-b border-border last:border-b-0">
                                    <div>
                                        <div class="font-medium text-sm">{{ account.account_name }}</div>
                                        <div class="text-xs text-muted-foreground">{{ account.account_code }}</div>
                                    </div>
                                    <div class="text-sm font-mono"
                                        :class="account.balance >= 0 ? 'text-blue-600 dark:text-blue-400' : 'text-red-600 dark:text-red-400'">
                                        {{ formatCurrency(account.balance) }}
                                    </div>
                                </div>
                                <div
                                    class="flex justify-between items-center py-3 border-t-2 border-blue-200 dark:border-blue-800 font-semibold">
                                    <span>Total Equity</span>
                                    <span class="text-blue-700 dark:text-blue-400">{{
                                        formatCurrency(balanceSheet.total_equity) }}</span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Financial Ratios -->
                <Card>
                    <CardHeader>
                        <CardTitle>Financial Ratios</CardTitle>
                        <CardDescription>
                            Key financial ratios based on the balance sheet
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="grid gap-4 md:grid-cols-3">
                            <div class="p-4 bg-muted/50 rounded-lg">
                                <div class="text-sm font-medium text-muted-foreground">Debt-to-Equity Ratio</div>
                                <div class="text-2xl font-bold">{{ calculateDebtToEquityRatio() }}</div>
                                <div class="text-xs text-muted-foreground mt-1">
                                    {{ getDebtToEquityInterpretation() }}
                                </div>
                            </div>
                            <div class="p-4 bg-muted/50 rounded-lg">
                                <div class="text-sm font-medium text-muted-foreground">Current Ratio</div>
                                <div class="text-2xl font-bold">{{ calculateCurrentRatio() }}</div>
                                <div class="text-xs text-muted-foreground mt-1">
                                    {{ getCurrentRatioInterpretation() }}
                                </div>
                            </div>
                            <div class="p-4 bg-muted/50 rounded-lg">
                                <div class="text-sm font-medium text-muted-foreground">Working Capital</div>
                                <div class="text-2xl font-bold">{{ formatCurrency(calculateWorkingCapital()) }}</div>
                                <div class="text-xs text-muted-foreground mt-1">
                                    {{ getWorkingCapitalInterpretation() }}
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Empty State -->
            <div v-else class="flex items-center justify-center py-12">
                <div class="text-center">
                    <FileText class="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                    <h3 class="text-lg font-medium mb-2">No balance sheet data available</h3>
                    <p class="text-muted-foreground">Try selecting a different date or ensure you have chart of accounts
                        set up.</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card'
import {
    Download,
    Loader2,
    TrendingUp,
    TrendingDown,
    Calculator,
    FileText
} from 'lucide-vue-next'
import { apiService } from '@/services/api'
import type { BreadcrumbItemType } from '@/types'

interface BalanceSheetAccount {
    account_code: string
    account_name: string
    balance: number
}

interface BalanceSheetSection {
    accounts: BalanceSheetAccount[]
    total: number
}

interface BalanceSheet {
    report_date: string
    assets: BalanceSheetSection
    liabilities: BalanceSheetSection
    equity: BalanceSheetSection
    total_assets: number
    total_liabilities: number
    total_equity: number
}

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Finance', href: '/finance' },
    { title: 'Reports', href: '/finance/reports' },
    { title: 'Balance Sheet', href: '/finance/reports/balance-sheet' }
]

const loading = ref(false)
const balanceSheet = ref<BalanceSheet | null>(null)
const reportDate = ref(new Date().toISOString().split('T')[0])

const fetchBalanceSheet = async () => {
    loading.value = true
    try {
        const response = await apiService.getBalanceSheet({ date: reportDate.value })
        balanceSheet.value = response.data
    } catch (error) {
        console.error('Error fetching balance sheet:', error)
        balanceSheet.value = null
    } finally {
        loading.value = false
    }
}

const exportReport = async () => {
    try {
        const response = await apiService.exportBalanceSheet({ date: reportDate.value, format: 'pdf' })
        // Handle PDF download
        const blob = new Blob([response], { type: 'application/pdf' })
        const url = window.URL.createObjectURL(blob)
        const a = document.createElement('a')
        a.href = url
        a.download = `balance-sheet-${reportDate.value}.pdf`
        a.click()
        window.URL.revokeObjectURL(url)
    } catch (error) {
        console.error('Error exporting balance sheet:', error)
    }
}

const exportExcel = async () => {
    try {
        const response = await apiService.exportBalanceSheet({ date: reportDate.value, format: 'excel' })
        // Handle Excel download
        const blob = new Blob([response], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' })
        const url = window.URL.createObjectURL(blob)
        const a = document.createElement('a')
        a.href = url
        a.download = `balance-sheet-${reportDate.value}.xlsx`
        a.click()
        window.URL.revokeObjectURL(url)
    } catch (error) {
        console.error('Error exporting balance sheet:', error)
    }
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
        month: 'long',
        day: 'numeric'
    })
}

// Financial Ratios Calculations
const calculateDebtToEquityRatio = () => {
    if (!balanceSheet.value || balanceSheet.value.total_equity === 0) return 'N/A'
    const ratio = balanceSheet.value.total_liabilities / balanceSheet.value.total_equity
    return ratio.toFixed(2)
}

const getDebtToEquityInterpretation = () => {
    const ratio = parseFloat(calculateDebtToEquityRatio())
    if (isNaN(ratio)) return 'No data available'
    if (ratio < 0.5) return 'Low debt, conservative'
    if (ratio < 1) return 'Moderate debt level'
    return 'High debt level'
}

const calculateCurrentRatio = () => {
    if (!balanceSheet.value) return 'N/A'
    // This would need current assets and current liabilities from the balance sheet
    // For now, returning a placeholder
    return '1.25'
}

const getCurrentRatioInterpretation = () => {
    const ratio = parseFloat(calculateCurrentRatio())
    if (isNaN(ratio)) return 'No data available'
    if (ratio > 2) return 'Very liquid'
    if (ratio > 1) return 'Good liquidity'
    return 'Low liquidity'
}

const calculateWorkingCapital = () => {
    if (!balanceSheet.value) return 0
    // This would need current assets and current liabilities
    // For now, returning a placeholder
    return 500000
}

const getWorkingCapitalInterpretation = () => {
    const workingCapital = calculateWorkingCapital()
    if (workingCapital > 0) return 'Positive working capital'
    return 'Negative working capital'
}

onMounted(() => {
    fetchBalanceSheet()
})
</script>
