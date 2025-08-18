<template>
  <AppLayout title="General Ledger">
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl leading-tight">
          General Ledger
        </h2>
        <div class="flex items-center space-x-2">
          <Button @click="exportLedger" :disabled="loading"
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
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
              <div>
                <Label for="account_filter">Account</Label>
                <Select v-model="filters.account_id" @update:model-value="fetchLedger">
                  <SelectTrigger class="mt-1">
                    <SelectValue placeholder="All Accounts" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="">All Accounts</SelectItem>
                    <SelectItem v-for="account in accounts" :key="account.id" :value="account.id">
                      {{ account.account_code }} - {{ account.name }}
                    </SelectItem>
                  </SelectContent>
                </Select>
              </div>

              <div>
                <Label for="date_from">Date From</Label>
                <Input id="date_from" v-model="filters.date_from" type="date" @change="fetchLedger" class="mt-1" />
              </div>

              <div>
                <Label for="date_to">Date To</Label>
                <Input id="date_to" v-model="filters.date_to" type="date" @change="fetchLedger" class="mt-1" />
              </div>

              <div>
                <Label for="type_filter">Account Type</Label>
                <Select v-model="filters.type" @update:model-value="fetchLedger">
                  <SelectTrigger class="mt-1">
                    <SelectValue placeholder="All Types" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="">All Types</SelectItem>
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
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
          <div class="overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1">
                      </path>
                    </svg>
                  </div>
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-500">Total Assets</p>
                  <p class="text-lg font-semibold">{{ formatCurrency(summary.total_assets) }}</p>
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
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                      </path>
                    </svg>
                  </div>
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-500">Total Liabilities</p>
                  <p class="text-lg font-semibold">{{ formatCurrency(summary.total_liabilities) }}</p>
                </div>
              </div>
            </div>
          </div>

          <div class="overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                  </div>
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-500">Total Equity</p>
                  <p class="text-lg font-semibold">{{ formatCurrency(summary.total_equity) }}</p>
                </div>
              </div>
            </div>
          </div>

          <div class="overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                      </path>
                    </svg>
                  </div>
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-500">Net Income</p>
                  <p class="text-lg font-semibold" :class="summary.net_income >= 0 ? 'text-green-600' : 'text-red-600'">
                    {{ formatCurrency(summary.net_income) }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- General Ledger Table -->
        <div class="overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-medium">Account Balances</h3>
              <div class="text-sm text-gray-500">
                Showing {{ ledger.length }} accounts
              </div>
            </div>

            <div class="overflow-x-auto">
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Account Code</TableHead>
                    <TableHead>Account Name</TableHead>
                    <TableHead>Type</TableHead>
                    <TableHead class="text-right">Opening Balance</TableHead>
                    <TableHead class="text-right">Debit</TableHead>
                    <TableHead class="text-right">Credit</TableHead>
                    <TableHead class="text-right">Closing Balance</TableHead>
                    <TableHead>Actions</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  <TableRow v-for="account in ledger" :key="account.id">
                    <TableCell class="font-medium">{{ account.account_code }}</TableCell>
                    <TableCell>{{ account.name }}</TableCell>
                    <TableCell>
                      <Badge :variant="getTypeVariant(account.type)">
                        {{ getTypeLabel(account.type) }}
                      </Badge>
                    </TableCell>
                    <TableCell class="text-right">
                      {{ formatCurrency(account.opening_balance) }}
                    </TableCell>
                    <TableCell class="text-right">
                      {{ formatCurrency(account.total_debit) }}
                    </TableCell>
                    <TableCell class="text-right">
                      {{ formatCurrency(account.total_credit) }}
                    </TableCell>
                    <TableCell class="text-right font-semibold">
                      {{ formatCurrency(account.closing_balance) }}
                    </TableCell>
                    <TableCell>
                      <DropdownMenu>
                        <DropdownMenuTrigger asChild>
                          <Button variant="ghost" class="h-8 w-8 p-0">
                            <MoreHorizontal class="h-4 w-4" />
                          </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end">
                          <DropdownMenuItem @click="viewTransactions(account.id)">
                            <Eye class="mr-2 h-4 w-4" />
                            View Transactions
                          </DropdownMenuItem>
                        </DropdownMenuContent>
                      </DropdownMenu>
                    </TableCell>
                  </TableRow>
                </TableBody>
              </Table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Badge } from '@/components/ui/badge'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import { Loader2, MoreHorizontal, Eye } from 'lucide-vue-next'
import { apiService } from '@/services/api'
import type { ChartOfAccount } from '@/types/erp'

interface LedgerAccount extends ChartOfAccount {
  opening_balance: number
  total_debit: number
  total_credit: number
  closing_balance: number
}

interface Summary {
  total_assets: number
  total_liabilities: number
  total_equity: number
  net_income: number
}

const accounts = ref<ChartOfAccount[]>([])
const ledger = ref<LedgerAccount[]>([])
const summary = ref<Summary>({
  total_assets: 0,
  total_liabilities: 0,
  total_equity: 0,
  net_income: 0
})
const loading = ref(false)

const filters = ref({
  account_id: '',
  date_from: '',
  date_to: '',
  type: ''
})

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR'
  }).format(amount)
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

const fetchAccounts = async () => {
  try {
    const response = await apiService.getChartOfAccounts({ page: 1, per_page: 1000 })
    accounts.value = response.data
  } catch (error) {
    console.error('Error fetching accounts:', error)
  }
}

const fetchLedger = async () => {
  try {
    loading.value = true
    const params = {
      account_id: filters.value.account_id,
      date_from: filters.value.date_from,
      date_to: filters.value.date_to,
      type: filters.value.type
    }

    // This would be a new API endpoint for general ledger
    const response = await apiService.getGeneralLedger(params)
    ledger.value = response.data.accounts
    summary.value = response.data.summary
  } catch (error) {
    console.error('Error fetching ledger:', error)
  } finally {
    loading.value = false
  }
}

const viewTransactions = (accountId: number) => {
  router.visit(route('finance.general-ledger.transactions', accountId), {
    data: {
      account_id: accountId,
      date_from: filters.value.date_from,
      date_to: filters.value.date_to
    }
  })
}

const exportLedger = async () => {
  try {
    loading.value = true
    const params = {
      account_id: filters.value.account_id,
      date_from: filters.value.date_from,
      date_to: filters.value.date_to,
      type: filters.value.type,
      export: true
    }

    // This would trigger a download
    await apiService.exportGeneralLedger(params)
  } catch (error) {
    console.error('Error exporting ledger:', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchAccounts()
  fetchLedger()
})
</script>
