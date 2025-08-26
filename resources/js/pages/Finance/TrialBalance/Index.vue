<template>

  <Head title="Trial Balance" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 space-y-6">
      <!-- Header Section -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold tracking-tight">Trial Balance</h1>
          <p class="text-muted-foreground mt-1">
            Verify that your debits equal credits and ensure accounting accuracy
          </p>
        </div>
        <div class="flex items-center space-x-2">
          <Button variant="outline" size="sm" @click="exportTrialBalance" :disabled="loading">
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
      <div class="grid gap-4 md:grid-cols-3">
        <Card>
          <CardContent class="p-6">
            <div class="flex items-center space-x-4">
              <div class="p-2 bg-blue-100 dark:bg-blue-900/20 rounded-lg">
                <TrendingUp class="h-6 w-6 text-blue-600 dark:text-blue-400" />
              </div>
              <div class="space-y-1">
                <p class="text-sm font-medium text-muted-foreground">Total Debit</p>
                <p class="text-2xl font-bold">{{ formatCurrency(summary.total_debit) }}</p>
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
                <p class="text-sm font-medium text-muted-foreground">Total Credit</p>
                <p class="text-2xl font-bold">{{ formatCurrency(summary.total_credit) }}</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-6">
            <div class="flex items-center space-x-4">
              <div class="p-2 rounded-lg"
                :class="isBalanced ? 'bg-green-100 dark:bg-green-900/20' : 'bg-red-100 dark:bg-red-900/20'">
                <component :is="isBalanced ? CheckCircle : XCircle" class="h-6 w-6"
                  :class="isBalanced ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'" />
              </div>
              <div class="space-y-1">
                <p class="text-sm font-medium text-muted-foreground">Balance Status</p>
                <p class="text-2xl font-bold"
                  :class="isBalanced ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
                  {{ isBalanced ? 'Balanced' : 'Unbalanced' }}
                </p>
                <p class="text-xs"
                  :class="isBalanced ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
                  {{ formatCurrency(Math.abs(summary.total_debit - summary.total_credit)) }} difference
                </p>
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
            Filter the trial balance by date range and account type
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div class="space-y-2">
              <Label for="date_from">Date From</Label>
              <Input id="date_from" v-model="filters.date_from" type="date" @change="fetchTrialBalance" />
            </div>

            <div class="space-y-2">
              <Label for="date_to">Date To</Label>
              <Input id="date_to" v-model="filters.date_to" type="date" @change="fetchTrialBalance" />
            </div>

            <div class="space-y-2">
              <Label for="type_filter">Account Type</Label>
              <Select v-model="filters.type" @update:model-value="fetchTrialBalance">
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

      <!-- Trial Balance Table -->
      <Card>
        <CardHeader>
          <CardTitle>Trial Balance</CardTitle>
          <CardDescription>
            Account balances as of {{ filters.date_to || 'current date' }}
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div class="rounded-md border">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Account Code</TableHead>
                  <TableHead>Account Name</TableHead>
                  <TableHead>Type</TableHead>
                  <TableHead class="text-right">Debit Balance</TableHead>
                  <TableHead class="text-right">Credit Balance</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-if="loading">
                  <TableCell colspan="5" class="text-center py-12">
                    <div class="flex items-center justify-center space-x-2">
                      <Loader2 class="w-6 h-6 animate-spin" />
                      <span class="text-muted-foreground">Loading trial balance...</span>
                    </div>
                  </TableCell>
                </TableRow>
                <TableRow v-else-if="trialBalanceData.length === 0">
                  <TableCell colspan="5" class="text-center py-12">
                    <div class="flex flex-col items-center space-y-2">
                      <Calculator class="h-12 w-12 text-muted-foreground" />
                      <div class="text-center">
                        <h3 class="text-lg font-medium">No trial balance data found</h3>
                        <p class="text-muted-foreground">
                          Try adjusting your filters or ensure you have journal entries
                        </p>
                      </div>
                    </div>
                  </TableCell>
                </TableRow>
                <TableRow v-else v-for="account in trialBalanceData" :key="account.id">
                  <TableCell>
                    <div class="font-mono text-sm font-medium">{{ account.account_code }}</div>
                  </TableCell>
                  <TableCell>
                    <div class="space-y-1">
                      <div class="font-medium">{{ account.account_name }}</div>
                      <div class="text-sm text-muted-foreground">{{ account.description || 'No description' }}</div>
                    </div>
                  </TableCell>
                  <TableCell>
                    <Badge :variant="getTypeVariant(account.account_type)" class="capitalize">
                      {{ account.account_type }}
                    </Badge>
                  </TableCell>
                  <TableCell class="text-right">
                    <div class="font-medium text-red-600 dark:text-red-400">
                      {{ account.debit_balance > 0 ? formatCurrency(account.debit_balance) : '-' }}
                    </div>
                  </TableCell>
                  <TableCell class="text-right">
                    <div class="font-medium text-green-600 dark:text-green-400">
                      {{ account.credit_balance > 0 ? formatCurrency(account.credit_balance) : '-' }}
                    </div>
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>

          <!-- Totals Row -->
          <div v-if="trialBalanceData.length > 0" class="mt-6 p-4 bg-muted/50 mb-4 rounded-lg">
            <div class="grid grid-cols-3 gap-4 text-center">
              <div>
                <p class="text-sm font-medium text-muted-foreground">Total Debit</p>
                <p class="text-lg font-bold text-red-600 dark:text-red-400">
                  {{ formatCurrency(summary.total_debit) }}
                </p>
              </div>
              <div>
                <p class="text-sm font-medium text-muted-foreground">Total Credit</p>
                <p class="text-lg font-bold text-green-600 dark:text-green-400">
                  {{ formatCurrency(summary.total_credit) }}
                </p>
              </div>
              <div>
                <p class="text-sm font-medium text-muted-foreground">Difference</p>
                <p class="text-lg font-bold"
                  :class="isBalanced ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
                  {{ formatCurrency(Math.abs(summary.total_debit - summary.total_credit)) }}
                </p>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Badge } from '@/components/ui/badge'
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import {
  Download,
  Printer,
  Loader2,
  TrendingUp,
  TrendingDown,
  Calculator,
  CheckCircle,
  XCircle
} from 'lucide-vue-next'
import { apiService } from '@/services/api'
import type { BreadcrumbItemType } from '@/types'

interface TrialBalanceAccount {
  id: number
  account_code: string
  account_name: string
  description?: string
  account_type: string
  debit_balance: number
  credit_balance: number
}

interface TrialBalanceSummary {
  total_debit: number
  total_credit: number
}

const breadcrumbs: BreadcrumbItemType[] = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Finance', href: '/finance' },
  { title: 'Trial Balance', href: '/finance/trial-balance' }
]

const loading = ref(false)
const trialBalanceData = ref<TrialBalanceAccount[]>([])

const summary = ref<TrialBalanceSummary>({
  total_debit: 0,
  total_credit: 0
})

const filters = ref({
  date_from: '',
  date_to: '',
  type: 'all'
})

const isBalanced = computed(() => {
  return Math.abs(summary.value.total_debit - summary.value.total_credit) < 0.01
})

const fetchTrialBalance = async () => {
  loading.value = true
  try {
    const response = await apiService.getTrialBalance(filters.value)
    trialBalanceData.value = response.data || []
    summary.value = response.summary || {
      total_debit: 0,
      total_credit: 0
    }
  } catch (error) {
    console.error('Error fetching trial balance:', error)
    trialBalanceData.value = []
  } finally {
    loading.value = false
  }
}

const exportTrialBalance = async () => {
  try {
    const response = await apiService.exportTrialBalance(filters.value)
    // Handle file download
    const blob = new Blob([response], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' })
    const url = window.URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = 'trial-balance.xlsx'
    a.click()
    window.URL.revokeObjectURL(url)
  } catch (error) {
    console.error('Error exporting trial balance:', error)
  }
}

const getTypeVariant = (type: string): "default" | "secondary" | "outline" | "destructive" => {
  const variants: Record<string, "default" | "secondary" | "outline" | "destructive"> = {
    'asset': 'default',
    'liability': 'secondary',
    'equity': 'outline',
    'revenue': 'default',
    'expense': 'destructive'
  }
  return variants[type] || 'secondary'
}

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR'
  }).format(amount)
}

onMounted(() => {
  // Set default date range to current month
  const now = new Date()
  const firstDay = new Date(now.getFullYear(), now.getMonth(), 1)
  const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0)

  filters.value.date_from = firstDay.toISOString().split('T')[0]
  filters.value.date_to = lastDay.toISOString().split('T')[0]

  fetchTrialBalance()
})
</script>
