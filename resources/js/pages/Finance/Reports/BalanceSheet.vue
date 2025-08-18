<template>

    <Head title="Balance Sheet" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="font-semibold text-xl leading-tight">
                    Balance Sheet
                </h2>
                <div class="flex gap-2">
                    <Input type="date" v-model="reportDate" class="w-[180px]" @change="fetchBalanceSheet" />
                    <Button @click="exportReport" variant="outline">
                        <Download class="w-4 h-4 mr-2" />
                        Export
                    </Button>
                </div>
            </div>

            <div v-if="loading" class="flex items-center justify-center py-12">
                <Loader2 class="w-8 h-8 animate-spin mr-2" />
                <span>Loading balance sheet...</span>
            </div>

            <div v-else-if="balanceSheet" class="space-y-6">
                <!-- Report Header -->
                <div class="text-center">
                    <h3 class="text-lg font-semibold">Balance Sheet</h3>
                    <p class="text-white">As of {{ formatDate(balanceSheet.report_date) }}</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Assets -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-green-700">Assets</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-3">
                                <div v-for="account in balanceSheet.assets.accounts" :key="account.account_code"
                                    class="flex justify-between items-center py-2 border-b border-gray-100 last:border-b-0">
                                    <div>
                                        <div class="font-medium text-sm">{{ account.account_name }}</div>
                                        <div class="text-xs text-gray-500">{{ account.account_code }}</div>
                                    </div>
                                    <div class="text-sm font-mono"
                                        :class="account.balance >= 0 ? 'text-green-600' : 'text-red-600'">
                                        {{ formatCurrency(account.balance) }}
                                    </div>
                                </div>
                                <div
                                    class="flex justify-between items-center py-3 border-t-2 border-green-200 font-semibold">
                                    <span>Total Assets</span>
                                    <span class="text-green-700">{{ formatCurrency(balanceSheet.total_assets) }}</span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Liabilities -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-red-700">Liabilities</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-3">
                                <div v-for="account in balanceSheet.liabilities.accounts" :key="account.account_code"
                                    class="flex justify-between items-center py-2 border-b border-gray-100 last:border-b-0">
                                    <div>
                                        <div class="font-medium text-sm">{{ account.account_name }}</div>
                                        <div class="text-xs text-gray-500">{{ account.account_code }}</div>
                                    </div>
                                    <div class="text-sm font-mono"
                                        :class="account.balance >= 0 ? 'text-red-600' : 'text-green-600'">
                                        {{ formatCurrency(account.balance) }}
                                    </div>
                                </div>
                                <div
                                    class="flex justify-between items-center py-3 border-t-2 border-red-200 font-semibold">
                                    <span>Total Liabilities</span>
                                    <span class="text-red-700">{{ formatCurrency(balanceSheet.total_liabilities)
                                        }}</span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Equity -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-blue-700">Equity</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-3">
                                <div v-for="account in balanceSheet.equity.accounts" :key="account.account_code"
                                    class="flex justify-between items-center py-2 border-b border-gray-100 last:border-b-0">
                                    <div>
                                        <div class="font-medium text-sm">{{ account.account_name }}</div>
                                        <div class="text-xs text-gray-500">{{ account.account_code }}</div>
                                    </div>
                                    <div class="text-sm font-mono"
                                        :class="account.balance >= 0 ? 'text-blue-600' : 'text-red-600'">
                                        {{ formatCurrency(account.balance) }}
                                    </div>
                                </div>
                                <div
                                    class="flex justify-between items-center py-3 border-t-2 border-blue-200 font-semibold">
                                    <span>Total Equity</span>
                                    <span class="text-blue-700">{{ formatCurrency(balanceSheet.total_equity) }}</span>
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
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="text-center p-4 bg-green-50 rounded-lg">
                                <div class="text-2xl font-bold text-green-700">{{
                                    formatCurrency(balanceSheet.total_assets) }}</div>
                                <div class="text-sm text-green-600">Total Assets</div>
                            </div>
                            <div class="text-center p-4 bg-red-50 rounded-lg">
                                <div class="text-2xl font-bold text-red-700">{{
                                    formatCurrency(balanceSheet.total_liabilities) }}</div>
                                <div class="text-sm text-red-600">Total Liabilities</div>
                            </div>
                            <div class="text-center p-4 bg-blue-50 rounded-lg">
                                <div class="text-2xl font-bold text-blue-700">{{
                                    formatCurrency(balanceSheet.total_equity) }}</div>
                                <div class="text-sm text-blue-600">Total Equity</div>
                            </div>
                        </div>

                        <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                            <div class="text-center">
                                <div class="text-lg font-semibold"
                                    :class="balanceSheet.total_assets === (balanceSheet.total_liabilities + balanceSheet.total_equity) ? 'text-green-600' : 'text-red-600'">
                                    {{ balanceSheet.total_assets === (balanceSheet.total_liabilities +
                                        balanceSheet.total_equity) ? '✓ Balanced' : '✗ Not Balanced' }}
                                </div>
                                <div class="text-sm text-white">
                                    Assets = Liabilities + Equity
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div v-else class="text-center py-12 text-gray-500">
                No balance sheet data available
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
import type { BalanceSheet } from '@/types/erp'
import type { BreadcrumbItemType } from '@/types'

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Finance', href: '/finance' },
    { title: 'Reports', href: '/finance/reports' },
    { title: 'Balance Sheet', href: '/finance/reports/balance-sheet' }
]

const balanceSheet = ref<BalanceSheet | null>(null)
const loading = ref(false)
const reportDate = ref(new Date().toISOString().split('T')[0])

const fetchBalanceSheet = async () => {
    loading.value = true
    try {
        const response = await apiService.getBalanceSheet({ date: reportDate.value })
        balanceSheet.value = response
    } catch (error) {
        console.error('Error fetching balance sheet:', error)
        balanceSheet.value = null
    } finally {
        loading.value = false
    }
}

const exportReport = async () => {
    try {
        const response = await apiService.getBalanceSheet({
            date: reportDate.value,
            export: true
        })

        // Create download link
        const blob = new Blob([response], { type: 'application/pdf' })
        const url = window.URL.createObjectURL(blob)
        const link = document.createElement('a')
        link.href = url
        link.download = `balance-sheet-${reportDate.value}.pdf`
        link.click()
        window.URL.revokeObjectURL(url)
    } catch (error) {
        console.error('Error exporting report:', error)
    }
}

const formatDate = (dateString: string): string => {
    if (!dateString) return 'N/A'
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    })
}

const formatCurrency = (amount: number): string => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount)
}

onMounted(() => {
    fetchBalanceSheet()
})
</script>
