<template>

    <Head title="Bank Reconciliation Reports" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 space-y-6">
            <!-- Header Section -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Bank Reconciliation Reports</h1>
                    <p class="text-muted-foreground mt-1">
                        Generate and view reconciliation reports
                    </p>
                </div>
                <div class="flex items-center space-x-2">
                    <Button variant="outline" size="sm" @click="refreshData" :disabled="loading">
                        <RefreshCw class="h-4 w-4 mr-2" />
                        Refresh
                    </Button>
                    <Button @click="generateReport" :disabled="!canGenerateReport" variant="default">
                        <FileText class="h-4 w-4 mr-2" />
                        Generate Report
                    </Button>
                </div>
            </div>

            <!-- Report Configuration -->
            <Card>
                <CardHeader>
                    <CardTitle>Report Configuration</CardTitle>
                    <CardDescription>Configure report parameters</CardDescription>
                </CardHeader>
                <CardContent class="mb-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="space-y-2">
                            <Label for="bank_account">Bank Account</Label>
                            <Select v-model="reportConfig.bank_account_id">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select account" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="account in bankAccounts" :key="account.id" :value="account.id">
                                        {{ account.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="space-y-2">
                            <Label for="period_start">Start Date</Label>
                            <Input id="period_start" type="date" v-model="reportConfig.period_start" required />
                        </div>

                        <div class="space-y-2">
                            <Label for="period_end">End Date</Label>
                            <Input id="period_end" type="date" v-model="reportConfig.period_end" required />
                        </div>
                    </div>

                    <div class="mt-6 space-y-4">
                        <div>
                            <Label>Report Type</Label>
                            <div class="mt-2 space-y-2">
                                <div class="flex items-center space-x-2">
                                    <Checkbox id="include_transactions"
                                        v-model:checked="reportConfig.include_transactions" />
                                    <Label for="include_transactions">Include transaction details</Label>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <Checkbox id="include_adjustments"
                                        v-model:checked="reportConfig.include_adjustments" />
                                    <Label for="include_adjustments">Include adjustments</Label>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <Checkbox id="include_notes" v-model:checked="reportConfig.include_notes" />
                                    <Label for="include_notes">Include notes</Label>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Generated Reports -->
            <Card>
                <CardHeader>
                    <CardTitle>Generated Reports</CardTitle>
                    <CardDescription>Previously generated reconciliation reports</CardDescription>
                </CardHeader>
                <CardContent class="mb-4">
                    <div v-if="loading" class="flex justify-center py-8">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
                    </div>

                    <div v-else-if="reports.length === 0" class="text-center py-8 text-muted-foreground">
                        No reports generated yet
                    </div>

                    <div v-else class="space-y-4">
                        <div v-for="report in reports" :key="report.id"
                            class="flex items-center justify-between p-4 border rounded-lg hover:bg-muted/50 transition-colors">
                            <div class="flex-1">
                                <div class="flex items-center space-x-3">
                                    <Badge variant="outline">{{ report.type }}</Badge>
                                    <span class="font-medium">{{ report.bank_account_name }}</span>
                                </div>
                                <div class="text-sm text-muted-foreground mt-1">
                                    Period: {{ formatDate(report.period_start) }} - {{ formatDate(report.period_end) }}
                                </div>
                                <div class="text-sm text-muted-foreground">
                                    Generated: {{ formatDate(report.generated_at) }}
                                </div>
                            </div>

                            <div class="flex items-center space-x-2">
                                <Select v-model="report.exportFormat" class="w-32">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Format" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="pdf">PDF</SelectItem>
                                        <SelectItem value="excel">Excel (CSV)</SelectItem>
                                    </SelectContent>
                                </Select>
                                <Button variant="outline" size="sm"
                                    @click="downloadReport(report.id, report.exportFormat)">
                                    <Download class="h-4 w-4 mr-2" />
                                    Download
                                </Button>
                                <Button variant="ghost" size="sm" @click="viewReport(report.id)">
                                    <Eye class="h-4 w-4" />
                                </Button>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>

    <!-- Report Details Modal -->
    <Dialog v-model:open="showReportModal">
        <DialogContent class="!max-w-4xl">
            <DialogHeader>
                <DialogTitle>Report Details</DialogTitle>
                <DialogDescription>
                    Detailed information about the reconciliation report.
                </DialogDescription>
            </DialogHeader>

            <div v-if="loadingReportDetail" class="flex justify-center py-8">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
            </div>

            <div v-else-if="reportDetail" class="space-y-6">
                <!-- Basic Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <Label class="text-sm font-medium text-muted-foreground">Report ID</Label>
                        <p class="text-sm">{{ reportDetail.id }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label class="text-sm font-medium text-muted-foreground">Status</Label>
                        <Badge :variant="reportDetail.status === 'completed' ? 'default' : 'secondary'">
                            {{ reportDetail.status }}
                        </Badge>
                    </div>
                    <div class="space-y-2">
                        <Label class="text-sm font-medium text-muted-foreground">Period Start</Label>
                        <p class="text-sm">{{ formatDate(reportDetail.period_start) }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label class="text-sm font-medium text-muted-foreground">Period End</Label>
                        <p class="text-sm">{{ formatDate(reportDetail.period_end) }}</p>
                    </div>
                </div>

                <!-- Bank Account Info -->
                <div class="space-y-2">
                    <Label class="text-sm font-medium text-muted-foreground">Bank Account</Label>
                    <div v-if="reportDetail.bankAccount" class="p-3 bg-muted rounded-lg">
                        <p class="font-medium">{{ reportDetail.bankAccount.name }}</p>
                        <p class="text-sm text-muted-foreground">{{ reportDetail.bankAccount.account_number }}</p>
                    </div>
                </div>

                <!-- Balances -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="space-y-2">
                        <Label class="text-sm font-medium text-muted-foreground">Bank Statement Balance</Label>
                        <p class="text-lg font-semibold">{{ formatCurrency(reportDetail.bank_statement_balance) }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label class="text-sm font-medium text-muted-foreground">Book Balance</Label>
                        <p class="text-lg font-semibold">{{ formatCurrency(reportDetail.book_balance) }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label class="text-sm font-medium text-muted-foreground">Difference</Label>
                        <p class="text-lg font-semibold" :class="getDifferenceColor(reportDetail.difference)">
                            {{ formatCurrency(reportDetail.difference) }}
                        </p>
                    </div>
                </div>

                <!-- Transaction Matches -->
                <div v-if="reportDetail.transactionMatches && reportDetail.transactionMatches.length > 0"
                    class="space-y-2">
                    <Label class="text-sm font-medium text-muted-foreground">Transaction Matches ({{
                        reportDetail.transactionMatches.length }})</Label>
                    <div class="space-y-2 max-h-40 overflow-y-auto">
                        <div v-for="match in reportDetail.transactionMatches" :key="match.id"
                            class="p-2 bg-muted rounded text-sm">
                            <div class="flex justify-between">
                                <span>{{ match.bankTransaction?.description || 'No Description' }}</span>
                                <Badge :variant="match.match_type === 'exact' ? 'default' : 'secondary'"
                                    class="text-xs">
                                    {{ match.match_type }}
                                </Badge>
                            </div>
                            <div class="text-xs text-muted-foreground">
                                Amount: {{ formatCurrency(match.bankTransaction?.amount || 0) }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Adjustments -->
                <div v-if="reportDetail.adjustments && reportDetail.adjustments.length > 0" class="space-y-2">
                    <Label class="text-sm font-medium text-muted-foreground">Adjustments ({{
                        reportDetail.adjustments.length }})</Label>
                    <div class="space-y-2 max-h-40 overflow-y-auto">
                        <div v-for="adjustment in reportDetail.adjustments" :key="adjustment.id"
                            class="p-2 bg-muted rounded text-sm">
                            <div class="flex justify-between">
                                <span>{{ adjustment.description }}</span>
                                <Badge :variant="adjustment.type === 'bank_charge' ? 'destructive' : 'default'"
                                    class="text-xs">
                                    {{ adjustment.type }}
                                </Badge>
                            </div>
                            <div class="text-xs text-muted-foreground">
                                Amount: {{ formatCurrency(adjustment.amount) }} | Date: {{ formatDate(adjustment.date)
                                }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div v-if="reportDetail.notes" class="space-y-2">
                    <Label class="text-sm font-medium text-muted-foreground">Notes</Label>
                    <p class="text-sm p-3 bg-muted rounded-lg">{{ reportDetail.notes }}</p>
                </div>
            </div>

            <div v-else class="text-center py-8 text-muted-foreground">
                No report details available.
            </div>
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import { router } from '@inertiajs/vue3'
import { useApi } from '@/composables/useApi'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Checkbox } from '@/components/ui/checkbox'
import { FileText, Download, Eye, RefreshCw } from 'lucide-vue-next'
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog'

// Breadcrumbs
const breadcrumbs = [
    { title: 'Finance', href: '/finance' },
    { title: 'Bank Reconciliation', href: '/finance/bank-reconciliation' },
    { title: 'Reports', href: '/finance/bank-reconciliation/reports' }
]

// API
const { get, post } = useApi()

// Reactive data
const loading = ref(false)
const generating = ref(false)

// Data
const reports = ref<any[]>([])
const bankAccounts = ref<any[]>([])

// Modal state
const showReportModal = ref(false)
const selectedReport = ref<any>(null)
const reportDetail = ref<any>(null)
const loadingReportDetail = ref(false)

// Report configuration
const reportConfig = ref({
    bank_account_id: '',
    period_start: '',
    period_end: '',
    include_transactions: true,
    include_adjustments: true,
    include_notes: false
})

// Computed
const canGenerateReport = computed(() => {
    return reportConfig.value.bank_account_id &&
        reportConfig.value.period_start &&
        reportConfig.value.period_end
})

// Methods
const fetchData = async () => {
    try {
        loading.value = true

        // Fetch bank accounts
        const accountsResponse = await get('/api/finance/bank-reconciliation/bank-accounts/dashboard')
        if (accountsResponse.data?.success) {
            bankAccounts.value = accountsResponse.data.data
        }

        // Fetch existing reports
        const reportsResponse = await get('/api/finance/bank-reconciliation-reports')
        if (reportsResponse.data?.success) {
            reports.value = reportsResponse.data.data.map((report: any) => ({
                ...report,
                exportFormat: 'pdf' // Default format
            }))
        }

    } catch (error) {
        console.error('Error fetching data:', error)
    } finally {
        loading.value = false
    }
}

const refreshData = () => {
    fetchData()
}

const generateReport = async () => {
    if (!canGenerateReport.value) return

    try {
        generating.value = true

        const response = await post('/api/finance/bank-reconciliation-reports/generate', reportConfig.value)

        if (response.data?.success) {
            // Refresh reports list
            await fetchData()

            // Reset form
            reportConfig.value = {
                bank_account_id: '',
                period_start: '',
                period_end: '',
                include_transactions: true,
                include_adjustments: true,
                include_notes: false
            }
        }
    } catch (error) {
        console.error('Error generating report:', error)
    } finally {
        generating.value = false
    }
}

const downloadReport = async (reportId: number, format: string) => {
    try {
        // This would typically trigger a file download
        window.open(`/api/finance/bank-reconciliation-reports/${reportId}/download?format=${format}`, '_blank')
    } catch (error) {
        console.error('Error downloading report:', error)
    }
}

const viewReport = async (reportId: number) => {
    try {
        // Find the report from the list
        const report = reports.value.find(r => r.id === reportId)
        if (!report) {
            alert('Report not found')
            return
        }

        selectedReport.value = report
        showReportModal.value = true
        loadingReportDetail.value = true

        // Fetch detailed report data
        const response = await get(`/api/finance/bank-reconciliation-reports/${reportId}`)
        if (response.data?.success) {
            reportDetail.value = response.data.data
        } else {
            alert('Error loading report details')
        }
    } catch (error) {
        console.error('Error viewing report:', error)
        alert('Error viewing report')
    } finally {
        loadingReportDetail.value = false
    }
}

const formatDate = (dateString: string) => {
    if (!dateString) return '-'
    return new Date(dateString).toLocaleDateString()
}

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount)
}

const getDifferenceColor = (difference: number) => {
    if (difference === 0) return 'text-muted-foreground'
    if (difference > 0) return 'text-green-500'
    return 'text-red-500'
}

// Lifecycle
onMounted(() => {
    fetchData()
})
</script>
