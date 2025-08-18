<template>
  <AppLayout title="Create Journal Entry">
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl leading-tight">
          Create Journal Entry
        </h2>
        <Link :href="route('finance.journal-entries.index')"
          class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
        <ArrowLeft class="w-4 h-4 mr-2" />
        Back to List
        </Link>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <form @submit.prevent="submit" class="space-y-6">
              <!-- Header Information -->
              <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                  <Label for="entry_date">Entry Date</Label>
                  <Input id="entry_date" v-model="form.entry_date" type="date" required class="mt-1" />
                  <InputError :message="form.errors.entry_date" class="mt-2" />
                </div>

                <div>
                  <Label for="reference_type">Reference Type</Label>
                  <Select v-model="form.reference_type">
                    <SelectTrigger class="mt-1">
                      <SelectValue placeholder="Select reference type" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="">No Reference</SelectItem>
                      <SelectItem value="sales_order">Sales Order</SelectItem>
                      <SelectItem value="purchase_order">Purchase Order</SelectItem>
                      <SelectItem value="invoice">Invoice</SelectItem>
                      <SelectItem value="payment">Payment</SelectItem>
                      <SelectItem value="adjustment">Adjustment</SelectItem>
                    </SelectContent>
                  </Select>
                  <InputError :message="form.errors.reference_type" class="mt-2" />
                </div>

                <div>
                  <Label for="reference_id">Reference ID</Label>
                  <Input id="reference_id" v-model="form.reference_id" type="number" placeholder="Reference ID"
                    class="mt-1" />
                  <InputError :message="form.errors.reference_id" class="mt-2" />
                </div>
              </div>

              <div>
                <Label for="description">Description</Label>
                <textarea id="description" v-model="form.description" rows="3"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                  placeholder="Enter journal entry description"></textarea>
                <InputError :message="form.errors.description" class="mt-2" />
              </div>

              <!-- Journal Entry Lines -->
              <div class="space-y-4">
                <div class="flex items-center justify-between">
                  <h3 class="text-lg font-medium">Journal Entry Lines</h3>
                  <Button type="button" @click="addLine"
                    class="inline-flex items-center px-3 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                    <Plus class="w-4 h-4 mr-2" />
                    Add Line
                  </Button>
                </div>

                <div v-if="form.errors.lines" class="text-red-600 text-sm">
                  {{ form.errors.lines }}
                </div>

                <div class="space-y-4">
                  <div v-for="(line, index) in form.lines" :key="index"
                    class="grid grid-cols-1 md:grid-cols-4 gap-4 p-4 border rounded-lg">
                    <div>
                      <Label :for="`account_${index}`">Account</Label>
                      <Select v-model="line.account_id" @update:model-value="updateLine(index, 'account_id', $event)">
                        <SelectTrigger class="mt-1">
                          <SelectValue :placeholder="`Select account for line ${index + 1}`" />
                        </SelectTrigger>
                        <SelectContent>
                          <SelectItem v-for="account in accounts" :key="account.id" :value="account.id">
                            {{ account.account_code }} - {{ account.name }}
                          </SelectItem>
                        </SelectContent>
                      </Select>
                    </div>

                    <div>
                      <Label :for="`description_${index}`">Description</Label>
                      <Input :id="`description_${index}`" v-model="line.description"
                        @input="updateLine(index, 'description', $event.target.value)" placeholder="Line description"
                        class="mt-1" />
                    </div>

                    <div>
                      <Label :for="`debit_${index}`">Debit Amount</Label>
                      <Input :id="`debit_${index}`" v-model="line.debit_amount"
                        @input="updateLine(index, 'debit_amount', $event.target.value)" type="number" step="0.01"
                        min="0" placeholder="0.00" class="mt-1" />
                    </div>

                    <div class="flex items-end space-x-2">
                      <div class="flex-1">
                        <Label :for="`credit_${index}`">Credit Amount</Label>
                        <Input :id="`credit_${index}`" v-model="line.credit_amount"
                          @input="updateLine(index, 'credit_amount', $event.target.value)" type="number" step="0.01"
                          min="0" placeholder="0.00" class="mt-1" />
                      </div>
                      <Button type="button" @click="removeLine(index)" variant="destructive" size="sm" class="mb-1">
                        <Trash2 class="w-4 h-4" />
                      </Button>
                    </div>
                  </div>
                </div>

                <!-- Totals -->
                <div class="flex justify-end space-x-4 text-lg font-semibold">
                  <div>Total Debit: {{ formatCurrency(totalDebit) }}</div>
                  <div>Total Credit: {{ formatCurrency(totalCredit) }}</div>
                  <div :class="isBalanced ? 'text-green-600' : 'text-red-600'">
                    Difference: {{ formatCurrency(difference) }}
                  </div>
                </div>
              </div>

              <!-- Submit Button -->
              <div class="flex items-center justify-end space-x-4">
                <Link :href="route('finance.journal-entries.index')"
                  class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400">
                Cancel
                </Link>
                <Button type="submit" :disabled="form.processing || !isBalanced"
                  class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 disabled:opacity-50">
                  <Loader2 v-if="form.processing" class="w-4 h-4 mr-2 animate-spin" />
                  Create Journal Entry
                </Button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { ArrowLeft, Plus, Trash2, Loader2 } from 'lucide-vue-next'
import InputError from '@/Components/InputError.vue'
import { apiService } from '@/services/api'
import type { ChartOfAccount } from '@/types/erp'

interface JournalEntryLine {
  account_id: string
  description: string
  debit_amount: number
  credit_amount: number
}

const accounts = ref<ChartOfAccount[]>([])
const loading = ref(false)

const form = useForm({
  entry_date: new Date().toISOString().split('T')[0],
  reference_type: '',
  reference_id: '',
  description: '',
  lines: [] as JournalEntryLine[]
})

// Add initial lines
const addLine = () => {
  form.lines.push({
    account_id: '',
    description: '',
    debit_amount: 0,
    credit_amount: 0
  })
}

const removeLine = (index: number) => {
  form.lines.splice(index, 1)
}

const updateLine = (index: number, field: keyof JournalEntryLine, value: any) => {
  form.lines[index][field] = value
}

const totalDebit = computed(() => {
  return form.lines.reduce((sum, line) => sum + (parseFloat(line.debit_amount.toString()) || 0), 0)
})

const totalCredit = computed(() => {
  return form.lines.reduce((sum, line) => sum + (parseFloat(line.credit_amount.toString()) || 0), 0)
})

const difference = computed(() => {
  return Math.abs(totalDebit.value - totalCredit.value)
})

const isBalanced = computed(() => {
  return Math.abs(totalDebit.value - totalCredit.value) < 0.01
})

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR'
  }).format(amount)
}

const fetchAccounts = async () => {
  try {
    loading.value = true
    const response = await apiService.getChartOfAccounts({ page: 1, per_page: 1000 })
    accounts.value = response.data
  } catch (error) {
    console.error('Error fetching accounts:', error)
  } finally {
    loading.value = false
  }
}

const submit = () => {
  if (!isBalanced.value) {
    alert('Total debit must equal total credit')
    return
  }

  if (form.lines.length < 2) {
    alert('At least 2 lines are required')
    return
  }

  form.post(route('finance.journal-entries.store'))
}

onMounted(() => {
  fetchAccounts()
  // Add initial lines
  addLine()
  addLine()
})
</script>
