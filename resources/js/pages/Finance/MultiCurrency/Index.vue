<template>
  <AppLayout>

    <Head title="Multi-Currency Management" />

    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold tracking-tight">Multi-Currency Management</h1>
          <p class="text-muted-foreground">
            Manage currencies, exchange rates, and foreign currency transactions
          </p>
        </div>
        <div class="flex items-center gap-2">
          <Button @click="refreshData" variant="outline">
            <RefreshCw class="h-4 w-4 mr-2" />
            Refresh
          </Button>
          <Button @click="showAddCurrency = true">
            <Plus class="h-4 w-4 mr-2" />
            Add Currency
          </Button>
        </div>
      </div>

      <!-- Overview Cards -->
      <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Active Currencies</CardTitle>
            <DollarSign class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ overview.activeCurrencies }}</div>
            <p class="text-xs text-muted-foreground">
              {{ overview.totalCurrencies }} total currencies
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Base Currency</CardTitle>
            <Globe class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ overview.baseCurrency }}</div>
            <p class="text-xs text-muted-foreground">
              {{ overview.baseCurrencyName }}
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Exchange Rate Updates</CardTitle>
            <TrendingUp class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ overview.lastUpdate }}</div>
            <p class="text-xs text-muted-foreground">
              {{ overview.nextUpdate }}
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Foreign Transactions</CardTitle>
            <Activity class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ overview.foreignTransactions }}</div>
            <p class="text-xs text-muted-foreground">
              This month
            </p>
          </CardContent>
        </Card>
      </div>

      <!-- Exchange Rates Section -->
      <Card>
        <CardHeader>
          <CardTitle>Exchange Rates</CardTitle>
          <CardDescription>
            Current exchange rates for all supported currencies
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div class="space-y-4">
            <!-- Base Currency Selection -->
            <div class="flex items-center gap-4">
              <Label for="base-currency">Base Currency:</Label>
              <Select v-model="selectedBaseCurrency" @update:model-value="updateExchangeRates">
                <SelectTrigger class="w-48">
                  <SelectValue :placeholder="selectedBaseCurrency" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem v-for="currency in currencies" :key="currency.code" :value="currency.code">
                    {{ currency.code }} - {{ currency.name }}
                  </SelectItem>
                </SelectContent>
              </Select>
              <Button @click="refreshExchangeRates" variant="outline" size="sm">
                <RefreshCw class="h-4 w-4 mr-2" />
                Update Rates
              </Button>
            </div>

            <!-- Exchange Rates Table -->
            <div class="rounded-md border">
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Currency</TableHead>
                    <TableHead>Code</TableHead>
                    <TableHead>Rate</TableHead>
                    <TableHead>Last Updated</TableHead>
                    <TableHead>Change</TableHead>
                    <TableHead>Actions</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  <TableRow v-for="rate in exchangeRates" :key="rate.currency_code" class="hover:bg-muted/50">
                    <TableCell class="font-medium">
                      <div class="flex items-center gap-2">
                        <span class="text-lg">{{ rate.symbol }}</span>
                        {{ rate.currency_name }}
                      </div>
                    </TableCell>
                    <TableCell>{{ rate.currency_code }}</TableCell>
                    <TableCell class="font-mono">
                      {{ formatExchangeRate(rate.rate) }}
                    </TableCell>
                    <TableCell>{{ formatDate(rate.updated_at) }}</TableCell>
                    <TableCell>
                      <Badge :variant="rate.change >= 0 ? 'default' : 'destructive'" class="font-mono">
                        {{ rate.change >= 0 ? '+' : '' }}{{ rate.change.toFixed(4) }}%
                      </Badge>
                    </TableCell>
                    <TableCell>
                      <Button @click="editExchangeRate(rate)" variant="ghost" size="sm">
                        <Edit class="h-4 w-4" />
                      </Button>
                    </TableCell>
                  </TableRow>
                </TableBody>
              </Table>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Currency Management Section -->
      <div class="grid gap-6 md:grid-cols-2">
        <!-- Active Currencies -->
        <Card>
          <CardHeader>
            <CardTitle>Active Currencies</CardTitle>
            <CardDescription>
              Manage supported currencies and their settings
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div class="space-y-3">
              <div v-for="currency in activeCurrencies" :key="currency.id"
                class="flex items-center justify-between p-3 rounded-lg border">
                <div class="flex items-center gap-3">
                  <span class="text-lg">{{ currency.symbol }}</span>
                  <div>
                    <p class="font-medium">{{ currency.name }}</p>
                    <p class="text-sm text-muted-foreground">
                      {{ currency.code }} - {{ currency.decimal_places }} decimals
                    </p>
                  </div>
                </div>
                <div class="flex items-center gap-2">
                  <Badge :variant="currency.is_base ? 'default' : 'secondary'">
                    {{ currency.is_base ? 'Base' : 'Active' }}
                  </Badge>
                  <Button @click="editCurrency(currency)" variant="ghost" size="sm">
                    <Edit class="h-4 w-4" />
                  </Button>
                  <Button v-if="!currency.is_base" @click="toggleCurrencyStatus(currency)" variant="ghost" size="sm">
                    <X class="h-4 w-4" />
                  </Button>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Recent Foreign Transactions -->
        <Card>
          <CardHeader>
            <CardTitle>Recent Foreign Transactions</CardTitle>
            <CardDescription>
              Latest transactions in foreign currencies
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div class="space-y-3">
              <div v-for="transaction in recentTransactions" :key="transaction.id"
                class="flex items-center justify-between p-3 rounded-lg border">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center">
                    <span class="text-xs font-medium">{{ transaction.currency_code }}</span>
                  </div>
                  <div>
                    <p class="font-medium">{{ transaction.description }}</p>
                    <p class="text-sm text-muted-foreground">
                      {{ formatDate(transaction.transaction_date) }}
                    </p>
                  </div>
                </div>
                <div class="text-right">
                  <p class="font-medium">
                    {{ transaction.currency_symbol }}{{ formatAmount(transaction.amount) }}
                  </p>
                  <p class="text-sm text-muted-foreground">
                    {{ formatAmount(transaction.base_amount) }} {{ overview.baseCurrency }}
                  </p>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Quick Actions -->
      <Card>
        <CardHeader>
          <CardTitle>Quick Actions</CardTitle>
        </CardHeader>
        <CardContent>
          <div class="grid gap-4 md:grid-cols-3">
            <Button @click="showAddCurrency = true" variant="outline" class="h-20 flex-col gap-2">
              <Plus class="h-6 w-6" />
              <span>Add New Currency</span>
            </Button>
            <Button @click="showExchangeRateHistory = true" variant="outline" class="h-20 flex-col gap-2">
              <History class="h-6 w-6" />
              <span>Exchange Rate History</span>
            </Button>
            <Button @click="showCurrencyReport = true" variant="outline" class="h-20 flex-col gap-2">
              <FileText class="h-6 w-6" />
              <span>Currency Report</span>
            </Button>
          </div>
        </CardContent>
      </Card>

      <!-- Add Currency Dialog -->
      <Dialog v-model:open="showAddCurrency">
        <DialogContent class="sm:max-w-[500px]">
          <DialogHeader>
            <DialogTitle>Add New Currency</DialogTitle>
            <DialogDescription>
              Add a new currency to the system with its exchange rate
            </DialogDescription>
          </DialogHeader>
          <form @submit.prevent="addCurrency" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
              <div class="space-y-2">
                <Label for="currency-code">Currency Code</Label>
                <Input id="currency-code" v-model="newCurrency.code" placeholder="USD" maxlength="3" required />
              </div>
              <div class="space-y-2">
                <Label for="currency-symbol">Symbol</Label>
                <Input id="currency-symbol" v-model="newCurrency.symbol" placeholder="$" maxlength="5" required />
              </div>
            </div>
            <div class="space-y-2">
              <Label for="currency-name">Currency Name</Label>
              <Input id="currency-name" v-model="newCurrency.name" placeholder="US Dollar" required />
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div class="space-y-2">
                <Label for="exchange-rate">Exchange Rate</Label>
                <Input id="exchange-rate" v-model="newCurrency.exchange_rate" type="number" step="0.0001"
                  placeholder="1.0000" required />
              </div>
              <div class="space-y-2">
                <Label for="decimal-places">Decimal Places</Label>
                <Select v-model="newCurrency.decimal_places">
                  <SelectTrigger>
                    <SelectValue :placeholder="newCurrency.decimal_places" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem :value="0">0</SelectItem>
                    <SelectItem :value="2">2</SelectItem>
                    <SelectItem :value="3">3</SelectItem>
                    <SelectItem :value="4">4</SelectItem>
                  </SelectContent>
                </Select>
              </div>
            </div>
            <div class="space-y-2">
              <Label for="currency-description">Description</Label>
              <Textarea id="currency-description" v-model="newCurrency.description" placeholder="Optional description"
                rows="3" />
            </div>
            <div class="flex items-center space-x-2">
              <Checkbox id="auto-update" v-model:checked="newCurrency.auto_update" />
              <Label for="auto-update">Auto-update exchange rates</Label>
            </div>
            <DialogFooter>
              <Button type="button" variant="outline" @click="showAddCurrency = false">
                Cancel
              </Button>
              <Button type="submit" :disabled="isSubmitting">
                {{ isSubmitting ? 'Adding...' : 'Add Currency' }}
              </Button>
            </DialogFooter>
          </form>
        </DialogContent>
      </Dialog>

      <!-- Edit Exchange Rate Dialog -->
      <Dialog v-model:open="showEditRate">
        <DialogContent class="sm:max-w-[400px]">
          <DialogHeader>
            <DialogTitle>Edit Exchange Rate</DialogTitle>
            <DialogDescription>
              Update the exchange rate for {{ editingRate?.currency_name }}
            </DialogDescription>
          </DialogHeader>
          <form @submit.prevent="updateExchangeRate" class="space-y-4">
            <div class="space-y-2">
              <Label for="edit-rate">Exchange Rate</Label>
              <Input id="edit-rate" v-model="editingRate.rate" type="number" step="0.0001" required />
            </div>
            <div class="space-y-2">
              <Label for="rate-date">Effective Date</Label>
              <Input id="rate-date" v-model="editingRate.effective_date" type="date" required />
            </div>
            <div class="space-y-2">
              <Label for="rate-notes">Notes</Label>
              <Textarea id="rate-notes" v-model="editingRate.notes" placeholder="Optional notes about this rate change"
                rows="3" />
            </div>
            <DialogFooter>
              <Button type="button" variant="outline" @click="showEditRate = false">
                Cancel
              </Button>
              <Button type="submit" :disabled="isSubmitting">
                {{ isSubmitting ? 'Updating...' : 'Update Rate' }}
              </Button>
            </DialogFooter>
          </form>
        </DialogContent>
      </Dialog>
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
import { Checkbox } from '@/components/ui/checkbox'
import { Textarea } from '@/components/ui/textarea'
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import {
  Plus,
  Edit,
  X,
  RefreshCw,
  DollarSign,
  Globe,
  TrendingUp,
  Activity,
  History,
  FileText
} from 'lucide-vue-next'
import { useApi } from '@/composables/useApi'
import { debounce } from '@/lib/utils'

const api = useApi()

// State
const overview = ref({
  activeCurrencies: 0,
  totalCurrencies: 0,
  baseCurrency: '',
  baseCurrencyName: '',
  lastUpdate: '',
  nextUpdate: '',
  foreignTransactions: 0
})

const currencies = ref([])
const activeCurrencies = ref([])
const exchangeRates = ref([])
const recentTransactions = ref([])
const selectedBaseCurrency = ref('')
const showAddCurrency = ref(false)
const showEditRate = ref(false)
const showExchangeRateHistory = ref(false)
const showCurrencyReport = ref(false)
const isSubmitting = ref(false)

const newCurrency = ref({
  code: '',
  symbol: '',
  name: '',
  exchange_rate: '',
  decimal_places: 2,
  description: '',
  auto_update: false
})

const editingRate = ref({
  id: null,
  currency_code: '',
  currency_name: '',
  rate: '',
  effective_date: '',
  notes: ''
})

// Computed
const formatExchangeRate = (rate: number) => {
  return rate.toFixed(4)
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString()
}

const formatAmount = (amount: number) => {
  return new Intl.NumberFormat().format(amount)
}

// Methods
const loadDashboard = async () => {
  try {
    const response = await api.get('/api/finance/multi-currency/dashboard')
    overview.value = response.data.overview
    currencies.value = response.data.currencies
    activeCurrencies.value = response.data.activeCurrencies
    exchangeRates.value = response.data.exchangeRates
    recentTransactions.value = response.data.recentTransactions

    if (!selectedBaseCurrency.value && currencies.value.length > 0) {
      selectedBaseCurrency.value = currencies.value.find(c => c.is_base)?.code || currencies.value[0].code
    }
  } catch (error) {
    console.error('Error loading dashboard:', error)
  }
}

const refreshData = () => {
  loadDashboard()
}

const refreshExchangeRates = async () => {
  try {
    await api.post('/api/finance/multi-currency/refresh-rates')
    await loadDashboard()
  } catch (error) {
    console.error('Error refreshing rates:', error)
  }
}

const updateExchangeRates = () => {
  loadDashboard()
}

const addCurrency = async () => {
  try {
    isSubmitting.value = true
    await api.post('/api/finance/multi-currency/currencies', newCurrency.value)

    // Reset form
    newCurrency.value = {
      code: '',
      symbol: '',
      name: '',
      exchange_rate: '',
      decimal_places: 2,
      description: '',
      auto_update: false
    }

    showAddCurrency.value = false
    await loadDashboard()
  } catch (error) {
    console.error('Error adding currency:', error)
  } finally {
    isSubmitting.value = false
  }
}

const editCurrency = (currency: any) => {
  // Implementation for editing currency
  console.log('Edit currency:', currency)
}

const toggleCurrencyStatus = async (currency: any) => {
  try {
    await api.patch(`/api/finance/multi-currency/currencies/${currency.id}/toggle-status`)
    await loadDashboard()
  } catch (error) {
    console.error('Error toggling currency status:', error)
  }
}

const editExchangeRate = (rate: any) => {
  editingRate.value = {
    id: rate.id,
    currency_code: rate.currency_code,
    currency_name: rate.currency_name,
    rate: rate.rate.toString(),
    effective_date: new Date().toISOString().split('T')[0],
    notes: ''
  }
  showEditRate.value = true
}

const updateExchangeRate = async () => {
  try {
    isSubmitting.value = true
    await api.patch(`/api/finance/multi-currency/exchange-rates/${editingRate.value.id}`, {
      rate: editingRate.value.rate,
      effective_date: editingRate.value.effective_date,
      notes: editingRate.value.notes
    })

    showEditRate.value = false
    await loadDashboard()
  } catch (error) {
    console.error('Error updating exchange rate:', error)
  } finally {
    isSubmitting.value = false
  }
}

// Lifecycle
onMounted(() => {
  loadDashboard()
})
</script>
