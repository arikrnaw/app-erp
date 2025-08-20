<template>
  <AppLayout>
    <div class="container mx-auto p-6">
      <div class="flex justify-between items-center mb-6">
        <div>
          <h1 class="text-3xl font-bold text-white">Payroll Periods</h1>
          <p class="text-white mt-2">Manage payroll periods and processing</p>
        </div>
        <Button @click="navigateToCreate" class="flex items-center gap-2">
          <Plus class="w-4 h-4" />
          New Payroll Period
        </Button>
      </div>

      <!-- Search and Filters -->
      <div class="rounded-lg shadow-sm border p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <Label for="search">Search</Label>
            <Input id="search" v-model="search" placeholder="Search periods..." @input="debouncedSearch" />
          </div>
          <div>
            <Label for="status">Status</Label>
            <Select v-model="filters.status" @update:model-value="fetchPayrollPeriods">
              <option value="">All Status</option>
              <option value="draft">Draft</option>
              <option value="processing">Processing</option>
              <option value="approved">Approved</option>
              <option value="paid">Paid</option>
              <option value="cancelled">Cancelled</option>
            </Select>
          </div>
          <div>
            <Label for="frequency">Frequency</Label>
            <Select v-model="filters.frequency" @update:model-value="fetchPayrollPeriods">
              <option value="">All Frequencies</option>
              <option value="weekly">Weekly</option>
              <option value="bi_weekly">Bi-Weekly</option>
              <option value="monthly">Monthly</option>
              <option value="quarterly">Quarterly</option>
              <option value="yearly">Yearly</option>
            </Select>
          </div>
          <div>
            <Label for="date_range">Date Range</Label>
            <div class="flex gap-2">
              <Input v-model="filters.start_date" type="date" @change="fetchPayrollPeriods" />
              <Input v-model="filters.end_date" type="date" @change="fetchPayrollPeriods" />
            </div>
          </div>
        </div>
      </div>

      <!-- Payroll Periods Table -->
      <div class="rounded-lg shadow-sm border">
        <div class="p-6">
          <div v-if="loading" class="flex justify-center items-center py-8">
            <Loader2 class="w-8 h-8 animate-spin text-blue-600" />
          </div>

          <div v-else-if="payrollPeriods.length === 0" class="text-center py-8">
            <CreditCard class="w-12 h-12 text-gray-400 mx-auto mb-4" />
            <h3 class="text-lg font-medium text-white mb-2">No payroll periods found</h3>
            <p class="text-white mb-4">Get started by creating your first payroll period.</p>
            <Button @click="navigateToCreate">Create Payroll Period</Button>
          </div>

          <div v-else>
            <div class="overflow-x-auto">
              <table class="w-full">
                <thead>
                  <tr class="border-b">
                    <th class="text-left py-3 px-4 font-medium text-white">Period</th>
                    <th class="text-left py-3 px-4 font-medium text-white">Date Range</th>
                    <th class="text-left py-3 px-4 font-medium text-white">Pay Date</th>
                    <th class="text-left py-3 px-4 font-medium text-white">Frequency</th>
                    <th class="text-left py-3 px-4 font-medium text-white">Employees</th>
                    <th class="text-left py-3 px-4 font-medium text-white">Total Pay</th>
                    <th class="text-left py-3 px-4 font-medium text-white">Status</th>
                    <th class="text-left py-3 px-4 font-medium text-white">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="period in payrollPeriods" :key="period.id" class="border-b">
                    <td class="py-4 px-4">
                      <div>
                        <div class="font-medium text-white">{{ period.name }}</div>
                        <div class="text-sm text-white">{{ period.period_code }}</div>
                      </div>
                    </td>
                    <td class="py-4 px-4">
                      <div>
                        <div class="font-medium text-white">
                          {{ formatDate(period.start_date) }} - {{ formatDate(period.end_date) }}
                        </div>
                      </div>
                    </td>
                    <td class="py-4 px-4">
                      <span class="font-medium">{{ formatDate(period.pay_date) }}</span>
                    </td>
                    <td class="py-4 px-4">
                      <Badge variant="outline">
                        {{ period.frequency.replace('_', ' ') }}
                      </Badge>
                    </td>
                    <td class="py-4 px-4">
                      <span class="font-medium">{{ period.total_employees || 0 }}</span>
                    </td>
                    <td class="py-4 px-4">
                      <div>
                        <div class="font-medium text-white">
                                                          {{ formatCurrency(period.total_net_pay || 0) }}
                        </div>
                        <div class="text-sm text-white">
                                                      Gross: {{ formatCurrency(period.total_gross_pay || 0) }}
                        </div>
                      </div>
                    </td>
                    <td class="py-4 px-4">
                      <Badge :variant="getStatusVariant(period.status)">
                        {{ period.status }}
                      </Badge>
                    </td>
                    <td class="py-4 px-4">
                      <div class="flex items-center gap-2">
                        <Button variant="ghost" size="sm" @click="navigateToShow(period.id)">
                          <Eye class="w-4 h-4" />
                        </Button>
                        <Button v-if="period.status === 'draft'" variant="ghost" size="sm"
                          @click="navigateToEdit(period.id)">
                          <Edit class="w-4 h-4" />
                        </Button>
                        <Button v-if="period.status === 'draft'" variant="ghost" size="sm"
                          @click="processPayroll(period)" :disabled="processLoading === period.id">
                          <Loader2 v-if="processLoading === period.id" class="w-4 h-4 animate-spin" />
                          <Calculator v-else class="w-4 h-4" />
                        </Button>
                        <Button v-if="period.status === 'processing'" variant="ghost" size="sm"
                          @click="approvePayroll(period)" :disabled="approveLoading === period.id">
                          <Loader2 v-if="approveLoading === period.id" class="w-4 h-4 animate-spin" />
                          <Check v-else class="w-4 h-4" />
                        </Button>
                        <Button v-if="period.status === 'approved'" variant="ghost" size="sm"
                          @click="markAsPaid(period)" :disabled="paidLoading === period.id">
                          <Loader2 v-if="paidLoading === period.id" class="w-4 h-4 animate-spin" />
                          <CreditCard v-else class="w-4 h-4" />
                        </Button>
                        <Button v-if="period.status === 'draft'" variant="ghost" size="sm"
                          @click="deletePayrollPeriod(period)" :disabled="deleteLoading === period.id">
                          <Loader2 v-if="deleteLoading === period.id" class="w-4 h-4 animate-spin" />
                          <Trash2 v-else class="w-4 h-4" />
                        </Button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Pagination -->
            <div v-if="pagination.last_page > 1" class="mt-6">
              <DataPagination :current-page="pagination.current_page" :total-pages="pagination.last_page"
                :total-items="pagination.total" :per-page="pagination.per_page" @page-change="handlePageChange" />
            </div>
          </div>
        </div>
      </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Badge } from '@/components/ui/badge'
import { DataPagination } from '@/components/ui/pagination'
import { Plus, Eye, Edit, Trash2, Check, CreditCard, Calculator, Loader2 } from 'lucide-vue-next'
import { apiService } from '@/services/api'
import type { PayrollPeriod } from '@/types/erp'
import { useDebounce } from '@/composables/useDebounce'

// Reactive data
const payrollPeriods = ref<PayrollPeriod[]>([])
const loading = ref(false)
const processLoading = ref<number | null>(null)
const approveLoading = ref<number | null>(null)
const paidLoading = ref<number | null>(null)
const deleteLoading = ref<number | null>(null)
const search = ref('')
const filters = ref({
  status: '',
  frequency: '',
  start_date: '',
  end_date: '',
})
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
})

// Debounced search
const debouncedSearch = useDebounce(() => {
  pagination.value.current_page = 1
  fetchPayrollPeriods()
}, 300)

// Fetch payroll periods
const fetchPayrollPeriods = async () => {
  loading.value = true
  try {
    const params = {
      page: pagination.value.current_page,
      search: search.value,
      ...filters.value,
    }

    const response = await apiService.getPayrollPeriods(params)
    payrollPeriods.value = response.data
    pagination.value = {
      current_page: response.meta.current_page,
      last_page: response.meta.last_page,
      per_page: response.meta.per_page,
      total: response.meta.total,
    }
  } catch (error) {
    console.error('Error fetching payroll periods:', error)
  } finally {
    loading.value = false
  }
}

// Navigation methods
const navigateToCreate = () => {
  router.visit('/hr/payroll-periods/create')
}

const navigateToShow = (id: number) => {
  router.visit(`/hr/payroll-periods/${id}`)
}

const navigateToEdit = (id: number) => {
  router.visit(`/hr/payroll-periods/${id}/edit`)
}

// Action methods
const processPayroll = async (period: PayrollPeriod) => {
  processLoading.value = period.id
  try {
    await apiService.processPayrollPeriod(period.id)
    await fetchPayrollPeriods()
  } catch (error) {
    console.error('Error processing payroll:', error)
  } finally {
    processLoading.value = null
  }
}

const approvePayroll = async (period: PayrollPeriod) => {
  approveLoading.value = period.id
  try {
    await apiService.approvePayrollPeriod(period.id)
    await fetchPayrollPeriods()
  } catch (error) {
    console.error('Error approving payroll:', error)
  } finally {
    approveLoading.value = null
  }
}

const markAsPaid = async (period: PayrollPeriod) => {
  paidLoading.value = period.id
  try {
    await apiService.markPayrollAsPaid(period.id)
    await fetchPayrollPeriods()
  } catch (error) {
    console.error('Error marking payroll as paid:', error)
  } finally {
    paidLoading.value = null
  }
}

const deletePayrollPeriod = async (period: PayrollPeriod) => {
  if (!confirm(`Are you sure you want to delete "${period.name}"?`)) {
    return
  }

  deleteLoading.value = period.id
  try {
    await apiService.deletePayrollPeriod(period.id)
    await fetchPayrollPeriods()
  } catch (error) {
    console.error('Error deleting payroll period:', error)
  } finally {
    deleteLoading.value = null
  }
}

const handlePageChange = (page: number) => {
  pagination.value.current_page = page
  fetchPayrollPeriods()
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

// Initialize
onMounted(() => {
  fetchPayrollPeriods()
})
</script>
