<template>
  <AppLayout>

    <Head title="Exchange Rate History" />

    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold tracking-tight">Exchange Rate History</h1>
          <p class="text-muted-foreground">
            Track exchange rate changes over time and analyze trends
          </p>
        </div>
        <div class="flex items-center gap-2">
          <Button @click="exportData" variant="outline">
            <Download class="h-4 w-4 mr-2" />
            Export
          </Button>
          <Button @click="refreshData" variant="outline">
            <RefreshCw class="h-4 w-4 mr-2" />
            Refresh
          </Button>
        </div>
      </div>

      <!-- Filters -->
      <Card>
        <CardHeader>
          <CardTitle>Filters</CardTitle>
        </CardHeader>
        <CardContent>
          <div class="grid gap-4 md:grid-cols-4">
            <div class="space-y-2">
              <Label for="base-currency">Base Currency</Label>
              <Select v-model="filters.baseCurrency" @update:model-value="loadHistory">
                <SelectTrigger>
                  <SelectValue :placeholder="filters.baseCurrency" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem v-for="currency in currencies" :key="currency.code" :value="currency.code">
                    {{ currency.code }} - {{ currency.name }}
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>
            <div class="space-y-2">
              <Label for="target-currency">Target Currency</Label>
              <Select v-model="filters.targetCurrency" @update:model-value="loadHistory">
                <SelectTrigger>
                  <SelectValue :placeholder="filters.targetCurrency" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem v-for="currency in currencies" :key="currency.code" :value="currency.code">
                    {{ currency.code }} - {{ currency.name }}
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>
            <div class="space-y-2">
              <Label for="date-from">From Date</Label>
              <Input id="date-from" v-model="filters.dateFrom" type="date" @change="loadHistory" />
            </div>
            <div class="space-y-2">
              <Label for="date-to">To Date</Label>
              <Input id="date-to" v-model="filters.dateTo" type="date" @change="loadHistory" />
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Exchange Rate Chart -->
      <Card>
        <CardHeader>
          <CardTitle>Exchange Rate Trend</CardTitle>
          <CardDescription>
            {{ filters.baseCurrency }} to {{ filters.targetCurrency }} exchange rate over time
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div class="h-80">
            <AreaChart :data="chartData" :index="'date'" :categories="['rate']" :colors="['#3b82f6']"
              :value-formatter="(value) => value.toFixed(4)" :y-axis-width="80" :show-grid="true" :show-tooltip="true"
              :show-legend="false" />
          </div>
        </CardContent>
      </Card>

      <!-- Statistics Cards -->
      <div class="grid gap-4 md:grid-cols-4">
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Current Rate</CardTitle>
            <TrendingUp class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ statistics.currentRate }}</div>
            <p class="text-xs text-muted-foreground">
              {{ filters.targetCurrency }} per {{ filters.baseCurrency }}
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Highest Rate</CardTitle>
            <ArrowUp class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ statistics.highestRate }}</div>
            <p class="text-xs text-muted-foreground">
              {{ formatDate(statistics.highestDate) }}
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Lowest Rate</CardTitle>
            <ArrowDown class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ statistics.lowestRate }}</div>
            <p class="text-xs text-muted-foreground">
              {{ formatDate(statistics.lowestDate) }}
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Average Rate</CardTitle>
            <BarChart3 class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ statistics.averageRate }}</div>
            <p class="text-xs text-muted-foreground">
              Period average
            </p>
          </CardContent>
        </Card>
      </div>

      <!-- Rate Changes Analysis -->
      <Card>
        <CardHeader>
          <CardTitle>Rate Changes Analysis</CardTitle>
          <CardDescription>
            Detailed breakdown of exchange rate changes
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div class="space-y-4">
            <!-- Period Comparison -->
            <div class="grid gap-4 md:grid-cols-3">
              <div class="space-y-2">
                <Label>This Week</Label>
                <div class="flex items-center gap-2">
                  <Badge :variant="weeklyChange >= 0 ? 'default' : 'destructive'" class="font-mono">
                    {{ weeklyChange >= 0 ? '+' : '' }}{{ weeklyChange.toFixed(4) }}%
                  </Badge>
                  <span class="text-sm text-muted-foreground">
                    {{ weeklyChangeAmount >= 0 ? '+' : '' }}{{ weeklyChangeAmount.toFixed(4) }}
                  </span>
                </div>
              </div>
              <div class="space-y-2">
                <Label>This Month</Label>
                <div class="flex items-center gap-2">
                  <Badge :variant="monthlyChange >= 0 ? 'default' : 'destructive'" class="font-mono">
                    {{ monthlyChange >= 0 ? '+' : '' }}{{ monthlyChange.toFixed(4) }}%
                  </Badge>
                  <span class="text-sm text-muted-foreground">
                    {{ monthlyChangeAmount >= 0 ? '+' : '' }}{{ monthlyChangeAmount.toFixed(4) }}
                  </span>
                </div>
              </div>
              <div class="space-y-2">
                <Label>This Year</Label>
                <div class="flex items-center gap-2">
                  <Badge :variant="yearlyChange >= 0 ? 'default' : 'destructive'" class="font-mono">
                    {{ yearlyChange >= 0 ? '+' : '' }}{{ yearlyChange.toFixed(4) }}%
                  </Badge>
                  <span class="text-sm text-muted-foreground">
                    {{ yearlyChangeAmount >= 0 ? '+' : '' }}{{ yearlyChangeAmount.toFixed(4) }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Volatility Metrics -->
            <div class="grid gap-4 md:grid-cols-2">
              <div class="space-y-2">
                <Label>Volatility (Standard Deviation)</Label>
                <div class="text-lg font-medium">{{ statistics.volatility }}</div>
                <p class="text-sm text-muted-foreground">
                  Higher values indicate more volatile rates
                </p>
              </div>
              <div class="space-y-2">
                <Label>Rate Stability</Label>
                <div class="text-lg font-medium">{{ statistics.stability }}</div>
                <p class="text-sm text-muted-foreground">
                  Percentage of days with rate changes < 1% </p>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Historical Data Table -->
      <Card>
        <CardHeader>
          <CardTitle>Historical Exchange Rates</CardTitle>
          <CardDescription>
            Detailed daily exchange rate data
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div class="space-y-4">
            <!-- Table Controls -->
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <Label for="page-size">Show:</Label>
                <Select v-model="pagination.perPage" @update:model-value="loadHistory">
                  <SelectTrigger class="w-20">
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem :value="10">10</SelectItem>
                    <SelectItem :value="25">25</SelectItem>
                    <SelectItem :value="50">50</SelectItem>
                    <SelectItem :value="100">100</SelectItem>
                  </SelectContent>
                </Select>
                <span class="text-sm text-muted-foreground">entries</span>
              </div>
              <div class="flex items-center gap-2">
                <Input v-model="search" placeholder="Search..." class="w-64" @input="debouncedSearch" />
              </div>
            </div>

            <!-- Data Table -->
            <div class="rounded-md border">
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Date</TableHead>
                    <TableHead>Exchange Rate</TableHead>
                    <TableHead>Change</TableHead>
                    <TableHead>Change %</TableHead>
                    <TableHead>Volume</TableHead>
                    <TableHead>Source</TableHead>
                    <TableHead>Notes</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  <TableRow v-for="rate in historyData" :key="rate.id" class="hover:bg-muted/50">
                    <TableCell class="font-medium">
                      {{ formatDate(rate.date) }}
                    </TableCell>
                    <TableCell class="font-mono">
                      {{ formatExchangeRate(rate.rate) }}
                    </TableCell>
                    <TableCell>
                      <Badge :variant="rate.change >= 0 ? 'default' : 'destructive'" class="font-mono">
                        {{ rate.change >= 0 ? '+' : '' }}{{ formatExchangeRate(rate.change) }}
                      </Badge>
                    </TableCell>
                    <TableCell class="font-mono">
                      {{ rate.change_percentage >= 0 ? '+' : '' }}{{ rate.change_percentage.toFixed(4) }}%
                    </TableCell>
                    <TableCell class="font-mono">
                      {{ formatNumber(rate.volume) }}
                    </TableCell>
                    <TableCell>
                      <Badge variant="outline">{{ rate.source }}</Badge>
                    </TableCell>
                    <TableCell class="max-w-xs truncate">
                      {{ rate.notes || '-' }}
                    </TableCell>
                  </TableRow>
                </TableBody>
              </Table>
            </div>

            <!-- Pagination -->
            <DataPagination :current-page="pagination.currentPage" :total-pages="pagination.totalPages"
              :total-items="pagination.totalItems" :per-page="pagination.perPage" @page-change="onPageChange"
              @per-page-change="onPerPageChange" />
          </div>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Badge } from '@/components/ui/badge'
import { AreaChart } from '@/components/ui/chart-area'
import { DataPagination } from '@/components/ui/pagination'
import {
  RefreshCw,
  Download,
  TrendingUp,
  ArrowUp,
  ArrowDown,
  BarChart3
} from 'lucide-vue-next'
import { useApi } from '@/composables/useApi'
import { debounce } from '@/lib/utils'

const api = useApi()

// State
const currencies = ref([])
const historyData = ref([])
const chartData = ref([])
const statistics = ref({
  currentRate: '0.0000',
  highestRate: '0.0000',
  highestDate: '',
  lowestRate: '0.0000',
  lowestDate: '',
  averageRate: '0.0000',
  volatility: '0.0000',
  stability: '0.00%'
})

const filters = ref({
  baseCurrency: 'USD',
  targetCurrency: 'EUR',
  dateFrom: '',
  dateTo: ''
})

const pagination = ref({
  currentPage: 1,
  totalPages: 1,
  totalItems: 0,
  perPage: 25
})

const search = ref('')
const debouncedSearch = debounce(() => {
  pagination.value.currentPage = 1
  loadHistory()
}, 300)

// Computed
const weeklyChange = computed(() => {
  // Calculate weekly change percentage
  return 0.0
})

const weeklyChangeAmount = computed(() => {
  // Calculate weekly change amount
  return 0.0000
})

const monthlyChange = computed(() => {
  // Calculate monthly change percentage
  return 0.0
})

const monthlyChangeAmount = computed(() => {
  // Calculate monthly change amount
  return 0.0000
})

const yearlyChange = computed(() => {
  // Calculate yearly change percentage
  return 0.0
})

const yearlyChangeAmount = computed(() => {
  // Calculate yearly change amount
  return 0.0000
})

// Methods
const loadCurrencies = async () => {
  try {
    const response = await api.get('/api/finance/multi-currency/currencies')
    currencies.value = response.data.currencies

    if (currencies.value.length > 0) {
      filters.value.baseCurrency = currencies.value.find(c => c.is_base)?.code || currencies.value[0].code
      filters.value.targetCurrency = currencies.value.find(c => !c.is_base)?.code || currencies.value[1]?.code || currencies.value[0].code
    }
  } catch (error) {
    console.error('Error loading currencies:', error)
  }
}

const loadHistory = async () => {
  try {
    const params = {
      base_currency: filters.value.baseCurrency,
      target_currency: filters.value.targetCurrency,
      date_from: filters.value.dateFrom,
      date_to: filters.value.dateTo,
      search: search.value,
      page: pagination.value.currentPage,
      per_page: pagination.value.perPage
    }

    const response = await api.get('/api/finance/multi-currency/exchange-rate-history', { params })

    historyData.value = response.data.history.data
    pagination.value = {
      currentPage: response.data.history.current_page,
      totalPages: response.data.history.last_page,
      totalItems: response.data.history.total,
      perPage: response.data.history.per_page
    }

    // Update chart data
    chartData.value = response.data.chartData.map((item: any) => ({
      date: item.date,
      rate: parseFloat(item.rate)
    }))

    // Update statistics
    statistics.value = response.data.statistics
  } catch (error) {
    console.error('Error loading history:', error)
  }
}

const refreshData = () => {
  loadHistory()
}

const exportData = async () => {
  try {
    const params = {
      base_currency: filters.value.baseCurrency,
      target_currency: filters.value.targetCurrency,
      date_from: filters.value.dateFrom,
      date_to: filters.value.dateTo,
      format: 'excel'
    }

    const response = await api.get('/api/finance/multi-currency/export-history', {
      params,
      responseType: 'blob'
    })

    // Create download link
    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', `exchange-rate-history-${filters.value.baseCurrency}-${filters.value.targetCurrency}.xlsx`)
    document.body.appendChild(link)
    link.click()
    link.remove()
  } catch (error) {
    console.error('Error exporting data:', error)
  }
}

const onPageChange = (page: number) => {
  pagination.value.currentPage = page
  loadHistory()
}

const onPerPageChange = (perPage: number) => {
  pagination.value.perPage = perPage
  pagination.value.currentPage = 1
  loadHistory()
}

const formatExchangeRate = (rate: number) => {
  return rate.toFixed(4)
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString()
}

const formatNumber = (number: number) => {
  return new Intl.NumberFormat().format(number)
}

// Lifecycle
onMounted(() => {
  // Set default dates (last 30 days)
  const today = new Date()
  const thirtyDaysAgo = new Date(today.getTime() - (30 * 24 * 60 * 60 * 1000))

  filters.value.dateFrom = thirtyDaysAgo.toISOString().split('T')[0]
  filters.value.dateTo = today.toISOString().split('T')[0]

  loadCurrencies()
  loadHistory()
})
</script>
