<template>
  <AppLayout title="Trial Balance">
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl leading-tight">
          Trial Balance
        </h2>
        <div class="flex items-center space-x-2">
          <Button @click="exportTrialBalance" :disabled="loading"
            class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
            <Loader2 v-if="loading" class="w-4 h-4 mr-2 animate-spin" />
            Export
          </Button>
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Filters -->
        <div class="overflow-hidden shadow-sm sm:rounded-lg mb-6">
          <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div>
                <Label for="date_from">Date From</Label>
                <Input id="date_from" v-model="filters.date_from" type="date" @change="fetchTrialBalance"
                  class="mt-1" />
              </div>

              <div>
                <Label for="date_to">Date To</Label>
                <Input id="date_to" v-model="filters.date_to" type="date" @change="fetchTrialBalance" class="mt-1" />
              </div>

              <div>
                <Label for="type_filter">Account Type</Label>
                <Select v-model="filters.type" @update:model-value="fetchTrialBalance">
                  <SelectTrigger class="mt-1">
                    <SelectValue placeholder="All Types" />
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
              </div>
            </div>
          </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
          <div class="overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                  </div>
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-500">Total Debit</p>
                  <p class="text-lg font-semibold">{{ formatCurrency(summary.total_debit) }}</p>
                </div>
              </div>
            </div>
          </div>

          <div class="overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="w-8 h-8 bg-red-500 rounded-md flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                      </path>
                    </svg>
                  </div>
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-500">Total Credit</p>
                  <p class="text-lg font-semibold">{{ formatCurrency(summary.total_credit) }}</p>
                </div>
              </div>
            </div>
          </div>

          <div class="overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="w-8 h-8 rounded-md flex items-center justify-center"
                    :class="isBalanced ? 'bg-green-500' : 'bg-red-500'">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                  </div>
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-500">Difference</p>
                  <p class="text-lg font-semibold" :class="isBalanced ? 'text-green-600' : 'text-red-600'">
                    {{ formatCurrency(difference) }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Trial Balance Table -->
        <div class="overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-medium">Trial Balance</h3>
              <div class="text-sm text-gray-500">
                Period: {{ formatDate(filters.date_from) }} - {{ formatDate(filters.date_to) }}
              </div>
            </div>

            <div class="overflow-x-auto">
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Account Code</TableHead>
                    <TableHead>Account Name</TableHead>
                    <TableHead>Type</TableHead>
                    <TableHead class="text-right">Debit</TableHead>
                    <TableHead class="text-right">Credit</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  <TableRow v-for="account in trialBalance" :key="account.id">
                    <TableCell class="font-medium">{{ account.account_code }}</TableCell>
                    <TableCell>{{ account.name }}</TableCell>
                    <TableCell>
                      <Badge :variant="getTypeVariant(account.type)">
                        {{ getTypeLabel(account.type) }}
                      </Badge>
                    </TableCell>
                    <TableCell class="text-right">
                      <span v-if="account.debit_balance > 0" class="font-medium">
                        {{ formatCurrency(account.debit_balance) }}
                      </span>
                      <span v-else>-</span>
                    </TableCell>
                    <TableCell class="text-right">
                      <span v-if="account.credit_balance > 0" class="font-medium">
                        {{ formatCurrency(account.credit_balance) }}
                      </span>
                      <span v-else>-</span>
                    </TableCell>
                  </TableRow>
                </TableBody>
                <TableFooter>
                  <TableRow>
                    <TableCell colspan="3" class="text-right font-semibold">Total</TableCell>
                    <TableCell class="text-right font-semibold">{{ formatCurrency(summary.total_debit) }}</TableCell>
                    <TableCell class="text-right font-semibold">{{ formatCurrency(summary.total_credit) }}</TableCell>
                  </TableRow>
                </TableFooter>
              </Table>
            </div>

            <!-- Balance Status -->
            <div class="mt-6 p-4 rounded-lg"
              :class="isBalanced ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <svg v-if="isBalanced" class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                      d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                      clip-rule="evenodd" />
                  </svg>
                  <svg v-else class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                      d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                      clip-rule="evenodd" />
                  </svg>
                </div>
                <div class="ml-3">
                  <h3 class="text-sm font-medium" :class="isBalanced ? 'text-green-800' : 'text-red-800'">
                    {{ isBalanced ? 'Trial Balance is Balanced' : 'Trial Balance is Not Balanced' }}
                  </h3>
                  <div class="mt-1 text-sm" :class="isBalanced ? 'text-green-700' : 'text-red-700'">
                    <p v-if="isBalanced">
                      Total debit equals total credit. The trial balance is balanced.
                    </p>
                    <p v-else>
                      Total debit and total credit are not equal. There may be errors in the accounting records.
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Badge } from '@/components/ui/badge'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow, TableFooter } from '@/components/ui/table'
import { Loader2 } from 'lucide-vue-next'
import { apiService } from '@/services/api'
import type { ChartOfAccount } from '@/types/erp'

interface TrialBalanceAccount extends ChartOfAccount {
  debit_balance: number
  credit_balance: number
}

interface Summary {
  total_debit: number
  total_credit: number
}

const trialBalance = ref<TrialBalanceAccount[]>([])
const summary = ref<Summary>({
  total_debit: 0,
  total_credit: 0
})
const loading = ref(false)

const filters = ref({
  date_from: new Date().toISOString().split('T')[0],
  date_to: new Date().toISOString().split('T')[0],
  type: 'all'
})

const difference = computed(() => {
  return Math.abs(summary.value.total_debit - summary.value.total_credit)
})

const isBalanced = computed(() => {
  return Math.abs(summary.value.total_debit - summary.value.total_credit) < 0.01
})

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR'
  }).format(amount)
}

const formatDate = (date: string) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const getTypeLabel = (type: string) => {
  const labels: Record<string, string> = {
    asset: 'Asset',
    liability: 'Liability',
    equity: 'Equity',
    revenue: 'Revenue',
    expense: 'Expense'
  }
  return labels[type] || type
}

const getTypeVariant = (type: string): 'default' | 'secondary' | 'destructive' => {
  const variants: Record<string, 'default' | 'secondary' | 'destructive'> = {
    asset: 'default',
    liability: 'destructive',
    equity: 'secondary',
    revenue: 'default',
    expense: 'destructive'
  }
  return variants[type] || 'default'
}

const fetchTrialBalance = async () => {
  try {
    loading.value = true
    const params = {
      date_from: filters.value.date_from,
      date_to: filters.value.date_to,
      type: filters.value.type === 'all' ? '' : filters.value.type
    }

    // This would be a new API endpoint for trial balance
    const response = await apiService.getTrialBalance(params)
    trialBalance.value = response.accounts
    summary.value = response.summary
  } catch (error) {
    console.error('Error fetching trial balance:', error)
  } finally {
    loading.value = false
  }
}

const exportTrialBalance = async () => {
  try {
    loading.value = true
    const params = {
      date_from: filters.value.date_from,
      date_to: filters.value.date_to,
      type: filters.value.type === 'all' ? '' : filters.value.type,
      export: true
    }

    // This would trigger a download
    await apiService.exportTrialBalance(params)
  } catch (error) {
    console.error('Error exporting trial balance:', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchTrialBalance()
})
</script>
