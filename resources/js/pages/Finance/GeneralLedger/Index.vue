<template>

  <Head title="General Ledger" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 space-y-6">
      <!-- Header Section -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold tracking-tight">General Ledger</h1>
          <p class="text-muted-foreground mt-1">
            View detailed account transactions and balances
          </p>
        </div>
        <div class="flex items-center space-x-2">
          <Button variant="outline" size="sm" @click="exportLedger" :disabled="loading">
            <Download class="h-4 w-4 mr-2" />
            Export
          </Button>
          <Button variant="outline" size="sm">
            <Printer class="h-4 w-4 mr-2" />
            Print
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
                <p class="text-sm font-medium text-muted-foreground">Total Assets</p>
                <p class="text-2xl font-bold">{{ formatCurrency(summary.total_assets) }}</p>
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
                <p class="text-2xl font-bold">{{ formatCurrency(summary.total_liabilities) }}</p>
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
                <p class="text-sm font-medium text-muted-foreground">Total Equity</p>
                <p class="text-2xl font-bold">{{ formatCurrency(summary.total_equity) }}</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-6">
            <div class="flex items-center space-x-4">
              <div class="p-2 bg-purple-100 dark:bg-purple-900/20 rounded-lg">
                <FileText class="h-6 w-6 text-purple-600 dark:text-purple-400" />
              </div>
              <div class="space-y-1">
                <p class="text-sm font-medium text-muted-foreground">Total Transactions</p>
                <p class="text-2xl font-bold">{{ ledgerData.length }}</p>
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
            Filter the general ledger by account, date range, and type
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="space-y-2">
              <Label for="account_filter">Account</Label>
              <Select v-model="filters.account_id" @update:model-value="fetchLedger">
                <SelectTrigger>
                  <SelectValue placeholder="All Accounts" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">All Accounts</SelectItem>
                  <SelectItem v-for="account in accounts" :key="account.id" :value="account.id">
                    {{ account.account_code }} - {{ account.account_name }}
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>

            <div class="space-y-2">
              <Label for="date_from">Date From</Label>
              <Input id="date_from" v-model="filters.date_from" type="date" @change="fetchLedger" />
            </div>

            <div class="space-y-2">
              <Label for="date_to">Date To</Label>
              <Input id="date_to" v-model="filters.date_to" type="date" @change="fetchLedger" />
            </div>

            <div class="space-y-2">
              <Label for="type_filter">Account Type</Label>
              <Select v-model="filters.type" @update:model-value="fetchLedger">
                <SelectTrigger>
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
        </CardContent>
      </Card>

      <!-- General Ledger Table -->
      <Card>
        <CardHeader>
          <CardTitle>General Ledger</CardTitle>
          <CardDescription>
            Detailed view of all account transactions
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div class="rounded-md border">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Date</TableHead>
                  <TableHead>Account</TableHead>
                  <TableHead>Description</TableHead>
                  <TableHead>Reference</TableHead>
                  <TableHead class="text-right">Debit</TableHead>
                  <TableHead class="text-right">Credit</TableHead>
                  <TableHead class="text-right">Balance</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-if="loading">
                  <TableCell colspan="7" class="text-center py-12">
                    <div class="flex items-center justify-center space-x-2">
                      <Loader2 class="w-6 h-6 animate-spin" />
                      <span class="text-muted-foreground">Loading general ledger...</span>
                    </div>
                  </TableCell>
                </TableRow>
                <TableRow v-else-if="ledgerData.length === 0">
                  <TableCell colspan="7" class="text-center py-12">
                    <div class="flex flex-col items-center space-y-2">
                      <FileText class="h-12 w-12 text-muted-foreground" />
                      <div class="text-center">
                        <h3 class="text-lg font-medium">No transactions found</h3>
                        <p class="text-muted-foreground">
                          Try adjusting your filters or create some journal entries
                        </p>
                      </div>
                    </div>
                  </TableCell>
                </TableRow>
                <TableRow v-else v-for="entry in ledgerData" :key="entry.id">
                  <TableCell>
                    <div class="text-sm">{{ formatDate(entry.date) }}</div>
                  </TableCell>
                  <TableCell>
                    <div class="space-y-1">
                      <div class="font-medium">{{ entry.account_code }}</div>
                      <div class="text-sm text-muted-foreground">{{ entry.account_name }}</div>
                    </div>
                  </TableCell>
                  <TableCell>
                    <div class="text-sm text-muted-foreground max-w-xs truncate">
                      {{ entry.description || 'No description' }}
                    </div>
                  </TableCell>
                  <TableCell>
                    <div class="text-sm">
                      <div class="font-medium">{{ entry.reference_type || 'N/A' }}</div>
                      <div class="text-muted-foreground">#{{ entry.reference_id || 'N/A' }}</div>
                    </div>
                  </TableCell>
                  <TableCell class="text-right">
                    <div class="font-medium text-red-600 dark:text-red-400">
                      {{ entry.debit > 0 ? formatCurrency(entry.debit) : '-' }}
                    </div>
                  </TableCell>
                  <TableCell class="text-right">
                    <div class="font-medium text-green-600 dark:text-green-400">
                      {{ entry.credit > 0 ? formatCurrency(entry.credit) : '-' }}
                    </div>
                  </TableCell>
                  <TableCell class="text-right">
                    <div class="font-medium" :class="getBalanceColor(entry.balance)">
                      {{ formatCurrency(entry.balance) }}
                    </div>
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>

          <!-- Pagination -->
          <DataPagination v-if="pagination && pagination.meta && pagination.meta.last_page > 1"
            :current-page="pagination.meta.current_page" :total-pages="pagination.meta.last_page"
            :total-items="pagination.meta.total" :per-page="pagination.meta.per_page || 15" class="mt-6"
            @page-change="changePage" />
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
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { DataPagination } from '@/components/ui/pagination'
import {
  Download,
  Printer,
  Loader2,
  TrendingUp,
  TrendingDown,
  Calculator,
  FileText
} from 'lucide-vue-next'
import { apiService } from '@/services/api'
import type { BreadcrumbItemType } from '@/types'

interface LedgerEntry {
  id: number
  date: string
  account_code: string
  account_name: string
  description?: string
  reference_type?: string
  reference_id?: number
  debit: number
  credit: number
  balance: number
}

interface Account {
  id: number
  account_code: string
  account_name: string
}

const breadcrumbs: BreadcrumbItemType[] = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Finance', href: '/finance' },
  { title: 'General Ledger', href: '/finance/general-ledger' }
]

const loading = ref(false)
const ledgerData = ref<LedgerEntry[]>([])
const accounts = ref<Account[]>([])
const pagination = ref<{
  meta: {
    current_page: number
    last_page: number
    total: number
    per_page: number
    from: number
    to: number
  }
} | null>(null)

const summary = ref({
  total_assets: 0,
  total_liabilities: 0,
  total_equity: 0
})

const filters = ref({
  account_id: 'all',
  date_from: '',
  date_to: '',
  type: 'all'
})

const fetchLedger = async () => {
  loading.value = true
  try {
    // Prepare filters for API call, converting 'all' to empty strings
    const apiFilters = {
      account_id: filters.value.account_id === 'all' ? '' : filters.value.account_id,
      date_from: filters.value.date_from,
      date_to: filters.value.date_to,
      type: filters.value.type === 'all' ? '' : filters.value.type,
      page: pagination.value?.meta?.current_page || 1
    }

    const response = await apiService.getGeneralLedger(apiFilters)
    ledgerData.value = response.data || []
    pagination.value = response.pagination || null
    summary.value = response.summary || {
      total_assets: 0,
      total_liabilities: 0,
      total_equity: 0
    }
  } catch (error) {
    console.error('Error fetching general ledger:', error)
    ledgerData.value = []
  } finally {
    loading.value = false
  }
}

const exportLedger = async () => {
  try {
    // Prepare filters for API call, converting 'all' to empty strings
    const apiFilters = {
      account_id: filters.value.account_id === 'all' ? '' : filters.value.account_id,
      date_from: filters.value.date_from,
      date_to: filters.value.date_to,
      type: filters.value.type === 'all' ? '' : filters.value.type
    }

    const response = await apiService.exportGeneralLedger(apiFilters)
    // Handle file download
    const blob = new Blob([response], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' })
    const url = window.URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = 'general-ledger.xlsx'
    a.click()
    window.URL.revokeObjectURL(url)
  } catch (error) {
    console.error('Error exporting general ledger:', error)
  }
}

const changePage = async (page: number) => {
  if (pagination.value && page >= 1 && page <= pagination.value.meta.last_page) {
    // Update current page in pagination
    if (pagination.value.meta) {
      pagination.value.meta.current_page = page
    }
    // Fetch data for the new page
    await fetchLedger()
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
    month: 'short',
    day: 'numeric'
  })
}

const getBalanceColor = (balance: number) => {
  if (balance > 0) return 'text-green-600 dark:text-green-400'
  if (balance < 0) return 'text-red-600 dark:text-red-400'
  return 'text-muted-foreground'
}

onMounted(() => {
  fetchLedger()
})
</script>
