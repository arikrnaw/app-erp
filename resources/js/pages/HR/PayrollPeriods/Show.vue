<template>
    <div class="container mx-auto p-6">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="flex items-center gap-4 mb-6">
                <Button variant="ghost" size="sm" @click="navigateBack">
                    <ArrowLeft class="w-4 h-4" />
                </Button>
                <div class="flex-1">
                    <h1 class="text-3xl font-bold text-white">{{ payrollPeriod?.name }}</h1>
                    <p class="text-white mt-2">Payroll period details and processing status</p>
                </div>
                <div class="flex items-center gap-2">
                    <Button v-if="payrollPeriod?.status === 'draft'" variant="outline" @click="navigateToEdit">
                        <Edit class="w-4 h-4 mr-2" />
                        Edit
                    </Button>
                    <Button v-if="payrollPeriod?.status === 'draft'" variant="outline" @click="processPayroll"
                        :disabled="processLoading">
                        <Loader2 v-if="processLoading" class="w-4 h-4 mr-2 animate-spin" />
                        <Calculator v-else class="w-4 h-4 mr-2" />
                        Process Payroll
                    </Button>
                    <Button v-if="payrollPeriod?.status === 'processing'" variant="outline" @click="approvePayroll"
                        :disabled="approveLoading">
                        <Loader2 v-if="approveLoading" class="w-4 h-4 mr-2 animate-spin" />
                        <Check v-else class="w-4 h-4 mr-2" />
                        Approve
                    </Button>
                    <Button v-if="payrollPeriod?.status === 'approved'" variant="outline" @click="markAsPaid"
                        :disabled="paidLoading">
                        <Loader2 v-if="paidLoading" class="w-4 h-4 mr-2 animate-spin" />
                        <CreditCard v-else class="w-4 h-4 mr-2" />
                        Mark as Paid
                    </Button>
                </div>
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="flex justify-center items-center py-12">
                <Loader2 class="w-8 h-8 animate-spin text-blue-600" />
            </div>

            <!-- Not Found State -->
            <div v-else-if="!payrollPeriod" class="text-center py-12">
                <CreditCard class="w-12 h-12 text-gray-400 mx-auto mb-4" />
                <h3 class="text-lg font-medium text-white mb-2">Payroll period not found</h3>
                <p class="text-white mb-4">The payroll period you're looking for doesn't exist.</p>
                <Button @click="navigateBack">Back to Payroll Periods</Button>
            </div>

            <!-- Content -->
            <div v-else class="space-y-6">
                <!-- Status Banner -->
                <div class="rounded-lg shadow-sm border p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <Badge :variant="getStatusVariant(payrollPeriod.status)" class="text-sm">
                                {{ payrollPeriod.status.toUpperCase() }}
                            </Badge>
                        </div>
                        <div class="text-sm text-white">
                            Created {{ formatDate(payrollPeriod.created_at) }}
                        </div>
                    </div>
                </div>

                <!-- Basic Information -->
                <div class="rounded-lg shadow-sm border">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-white mb-4">Basic Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <Label class="text-sm font-medium text-gray-500">Period Code</Label>
                                <p class="text-white mt-1">{{ payrollPeriod.period_code }}</p>
                            </div>
                            <div>
                                <Label class="text-sm font-medium text-gray-500">Period Name</Label>
                                <p class="text-white mt-1">{{ payrollPeriod.name }}</p>
                            </div>
                            <div>
                                <Label class="text-sm font-medium text-gray-500">Frequency</Label>
                                <Badge variant="outline" class="mt-1">
                                    {{ payrollPeriod.frequency.replace('_', ' ') }}
                                </Badge>
                            </div>
                            <div>
                                <Label class="text-sm font-medium text-gray-500">Status</Label>
                                <Badge :variant="getStatusVariant(payrollPeriod.status)" class="mt-1">
                                    {{ payrollPeriod.status }}
                                </Badge>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Period Details -->
                <div class="rounded-lg shadow-sm border">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-white mb-4">Period Details</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <Label class="text-sm font-medium text-gray-500">Start Date</Label>
                                <p class="text-white mt-1">{{ formatDate(payrollPeriod.start_date) }}</p>
                            </div>
                            <div>
                                <Label class="text-sm font-medium text-gray-500">End Date</Label>
                                <p class="text-white mt-1">{{ formatDate(payrollPeriod.end_date) }}</p>
                            </div>
                            <div>
                                <Label class="text-sm font-medium text-gray-500">Pay Date</Label>
                                <p class="text-white mt-1">{{ formatDate(payrollPeriod.pay_date) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payroll Summary -->
                <div class="rounded-lg shadow-sm border">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-white mb-4">Payroll Summary</h3>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-blue-600">{{ payrollPeriod.total_employees || 0 }}
                                </div>
                                <div class="text-sm text-white">Total Employees</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-green-600">${{
                                    formatCurrency(payrollPeriod.total_gross_pay || 0) }}</div>
                                <div class="text-sm text-white">Gross Pay</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-red-600">${{
                                    formatCurrency(payrollPeriod.total_deductions || 0) }}</div>
                                <div class="text-sm text-white">Total Deductions</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-purple-600">${{
                                    formatCurrency(payrollPeriod.total_net_pay || 0) }}</div>
                                <div class="text-sm text-white">Net Pay</div>
                            </div>
                        </div>
                        <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="text-center">
                                <div class="text-lg font-semibold text-white">${{
                                    formatCurrency(payrollPeriod.total_allowances || 0) }}</div>
                                <div class="text-sm text-white">Total Allowances</div>
                            </div>
                            <div class="text-center">
                                <div class="text-lg font-semibold text-white">${{
                                    formatCurrency(payrollPeriod.total_tax || 0) }}</div>
                                <div class="text-sm text-white">Total Tax</div>
                            </div>
                            <div class="text-center">
                                <div class="text-lg font-semibold text-white">{{ calculateDays }} days</div>
                                <div class="text-sm text-white">Period Duration</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Approval Information -->
                <div v-if="payrollPeriod.status !== 'draft'" class="rounded-lg shadow-sm border">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-white mb-4">Processing Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <Label class="text-sm font-medium text-gray-500">Created By</Label>
                                <p class="text-white mt-1">{{ payrollPeriod.created_by_user?.name || 'N/A' }}</p>
                            </div>
                            <div>
                                <Label class="text-sm font-medium text-gray-500">Approved By</Label>
                                <p class="text-white mt-1">{{ payrollPeriod.approved_by_user?.name || 'N/A' }}</p>
                            </div>
                            <div>
                                <Label class="text-sm font-medium text-gray-500">Approved At</Label>
                                <p class="text-white mt-1">{{ payrollPeriod.approved_at ?
                                    formatDate(payrollPeriod.approved_at) : 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div v-if="payrollPeriod.notes" class="rounded-lg shadow-sm border">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-white mb-4">Notes</h3>
                        <p class="text-white">{{ payrollPeriod.notes }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Label } from '@/components/ui/label'
import { ArrowLeft, Edit, Check, CreditCard, Calculator, Loader2 } from 'lucide-vue-next'
import { apiService } from '@/services/api'
import type { PayrollPeriod } from '@/types/erp'

// Props
interface Props {
    id: number
}

const props = defineProps<Props>()

// Reactive data
const payrollPeriod = ref<PayrollPeriod | null>(null)
const loading = ref(true)
const processLoading = ref(false)
const approveLoading = ref(false)
const paidLoading = ref(false)

// Computed properties
const calculateDays = computed(() => {
    if (!payrollPeriod.value?.start_date || !payrollPeriod.value?.end_date) return 0

    const start = new Date(payrollPeriod.value.start_date)
    const end = new Date(payrollPeriod.value.end_date)
    const diffTime = Math.abs(end.getTime() - start.getTime())
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))

    return diffDays + 1 // Include both start and end dates
})

// Navigation
const navigateBack = () => {
    router.visit('/hr/payroll-periods')
}

const navigateToEdit = () => {
    router.visit(`/hr/payroll-periods/${props.id}/edit`)
}

// Actions
const processPayroll = async () => {
    if (!payrollPeriod.value) return

    processLoading.value = true
    try {
        await apiService.processPayrollPeriod(payrollPeriod.value.id)
        await fetchPayrollPeriod()
    } catch (error) {
        console.error('Error processing payroll:', error)
    } finally {
        processLoading.value = false
    }
}

const approvePayroll = async () => {
    if (!payrollPeriod.value) return

    approveLoading.value = true
    try {
        await apiService.approvePayrollPeriod(payrollPeriod.value.id)
        await fetchPayrollPeriod()
    } catch (error) {
        console.error('Error approving payroll:', error)
    } finally {
        approveLoading.value = false
    }
}

const markAsPaid = async () => {
    if (!payrollPeriod.value) return

    paidLoading.value = true
    try {
        await apiService.markPayrollAsPaid(payrollPeriod.value.id)
        await fetchPayrollPeriod()
    } catch (error) {
        console.error('Error marking payroll as paid:', error)
    } finally {
        paidLoading.value = false
    }
}

// Helper functions
const getStatusVariant = (status: string) => {
    switch (status) {
        case 'paid': return 'default'
        case 'approved': return 'default'
        case 'processing': return 'secondary'
        case 'draft': return 'outline'
        case 'cancelled': return 'destructive'
        default: return 'secondary'
    }
}

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString()
}

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(amount)
}

// Fetch data
const fetchPayrollPeriod = async () => {
    loading.value = true
    try {
        const data = await apiService.getPayrollPeriod(props.id)
        payrollPeriod.value = data
    } catch (error) {
        console.error('Error fetching payroll period:', error)
    } finally {
        loading.value = false
    }
}

// Initialize
onMounted(() => {
    fetchPayrollPeriod()
})
</script>
