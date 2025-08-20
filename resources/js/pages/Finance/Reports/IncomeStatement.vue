<template>

    <Head title="Income Statement" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="font-semibold text-xl leading-tight">
                    Income Statement
                </h2>
                <div class="flex gap-2">
                    <Input type="date" v-model="startDate" class="w-[180px]" @change="fetchIncomeStatement" />
                    <Input type="date" v-model="endDate" class="w-[180px]" @change="fetchIncomeStatement" />
                    <Button @click="exportReport" variant="outline">
                        <Download class="w-4 h-4 mr-2" />
                        Export
                    </Button>
                </div>
            </div>

            <div v-if="loading" class="flex items-center justify-center py-12">
                <Loader2 class="w-8 h-8 animate-spin mr-2" />
                <span>Loading income statement...</span>
            </div>

            <div v-else-if="incomeStatement" class="space-y-6">
                <!-- Report Header -->
                <div class="text-center">
                    <h3 class="text-lg font-semibold">Income Statement</h3>
                    <p class="text-white">{{ incomeStatement.report_period }}</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Revenue -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-green-700">Revenue</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-3">
                                <div v-for="account in incomeStatement.revenue.accounts" :key="account.account_code"
                                    class="flex justify-between items-center py-2 border-b border-gray-100 last:border-b-0">
                                    <div>
                                        <div class="font-medium text-sm">{{ account.account_name }}</div>
                                        <div class="text-xs text-gray-500">{{ account.account_code }}</div>
                                    </div>
                                    <div class="text-sm font-mono text-green-600">
                                        {{ formatCurrency(account.amount) }}
                                    </div>
                                </div>
                                <div
                                    class="flex justify-between items-center py-3 border-t-2 border-green-200 font-semibold">
                                    <span>Total Revenue</span>
                                    <span class="text-green-700">{{ formatCurrency(incomeStatement.revenue.total)
                                        }}</span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Expenses -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-red-700">Expenses</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-3">
                                <div v-for="account in incomeStatement.expenses.accounts" :key="account.account_code"
                                    class="flex justify-between items-center py-2 border-b border-gray-100 last:border-b-0">
                                    <div>
                                        <div class="font-medium text-sm">{{ account.account_name }}</div>
                                        <div class="text-xs text-gray-500">{{ account.account_code }}</div>
                                    </div>
                                    <div class="text-sm font-mono text-red-600">
                                        {{ formatCurrency(account.amount) }}
                                    </div>
                                </div>
                                <div
                                    class="flex justify-between items-center py-3 border-t-2 border-red-200 font-semibold">
                                    <span>Total Expenses</span>
                                    <span class="text-red-700">{{ formatCurrency(incomeStatement.expenses.total)
                                        }}</span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Summary -->
                <Card>
                    <CardHeader>
                        <CardTitle>Summary</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center py-3 border-b border-gray-200">
                                <span class="text-lg font-medium">Gross Profit</span>
                                <span class="text-lg font-mono text-green-600">{{
                                    formatCurrency(incomeStatement.gross_profit) }}</span>
                            </div>
                            <div class="flex justify-between items-center py-3 border-b border-gray-200">
                                <span class="text-lg font-medium">Total Expenses</span>
                                <span class="text-lg font-mono text-red-600">{{
                                    formatCurrency(incomeStatement.expenses.total) }}</span>
                            </div>
                            <div
                                class="flex justify-between items-center py-4 border-t-2 border-gray-300 font-semibold text-lg">
                                <span>Net Income</span>
                                <span class="font-mono"
                                    :class="incomeStatement.net_income >= 0 ? 'text-green-600' : 'text-red-600'">
                                    {{ formatCurrency(incomeStatement.net_income) }}
                                </span>
                            </div>
                        </div>

                        <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                            <div class="text-center">
                                <div class="text-lg font-semibold"
                                    :class="incomeStatement.net_income >= 0 ? 'text-green-600' : 'text-red-600'">
                                    {{ incomeStatement.net_income >= 0 ? '✓ Profit' : '✗ Loss' }}
                                </div>
                                <div class="text-sm text-white">
                                    Net Income = Revenue - Expenses
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div v-else class="text-center py-12 text-gray-500">
                No income statement data available
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
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Download, Loader2 } from 'lucide-vue-next'
import { apiService } from '@/services/api'
import type { IncomeStatement } from '@/types/erp'
import type { BreadcrumbItemType } from '@/types'

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Finance', href: '/finance' },
    { title: 'Reports', href: '/finance/reports' },
    { title: 'Income Statement', href: '/finance/reports/income-statement' }
]

const incomeStatement = ref<IncomeStatement | null>(null)
const loading = ref(false)
const startDate = ref(new Date().getFullYear() + '-' + String(new Date().getMonth() + 1).padStart(2, '0') + '-01')
const endDate = ref(new Date().toISOString().split('T')[0])

const fetchIncomeStatement = async () => {
    loading.value = true
    try {
        const response = await apiService.getIncomeStatement({
            start_date: startDate.value,
            end_date: endDate.value
        })
        incomeStatement.value = response
    } catch (error) {
        console.error('Error fetching income statement:', error)
        incomeStatement.value = null
    } finally {
        loading.value = false
    }
}

const exportReport = async () => {
    try {
        const response = await apiService.getIncomeStatement({
            start_date: startDate.value,
            end_date: endDate.value,
            export: true
        })

        // Create download link
        const blob = new Blob([response], { type: 'application/pdf' })
        const url = window.URL.createObjectURL(blob)
        const link = document.createElement('a')
        link.href = url
        link.download = `income-statement-${startDate.value}-to-${endDate.value}.pdf`
        link.click()
        window.URL.revokeObjectURL(url)
    } catch (error) {
        console.error('Error exporting report:', error)
    }
}

const formatCurrency = (amount: number): string => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(amount)
}

onMounted(() => {
    fetchIncomeStatement()
})
</script>
