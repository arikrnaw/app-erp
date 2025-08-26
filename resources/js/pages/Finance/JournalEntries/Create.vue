<template>

  <Head title="Create Journal Entry" />

  <AppLayout>
    <div class="p-6 space-y-6">
      <!-- Header Section -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold tracking-tight">Create Journal Entry</h1>
          <p class="text-muted-foreground mt-1">
            Create a new journal entry with balanced debit and credit lines
          </p>
        </div>
        <Link :href="route('finance.journal-entries.index')">
        <Button variant="outline">
          <ArrowLeft class="w-4 h-4 mr-2" />
          Back to List
        </Button>
        </Link>
      </div>

      <!-- Form Card -->
      <Card class="shadow-sm border-border">
        <CardHeader class="border-b border-border bg-muted/30">
          <CardTitle class="text-xl font-semibold">Journal Entry Information</CardTitle>
        </CardHeader>
        <CardContent class="p-6">
          <form @submit.prevent="submit" class="space-y-6">
            <!-- Header Information -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div class="space-y-3">
                <Label for="entry_date" class="text-sm font-medium">Entry Date *</Label>
                <Input id="entry_date" v-model="form.entry_date" type="date" required class="h-10" />
                <InputError :message="form.errors.entry_date" class="mt-2" />
              </div>

              <div class="space-y-3">
                <Label for="reference_type" class="text-sm font-medium">Reference Type</Label>
                <Select v-model="form.reference_type">
                  <SelectTrigger class="h-10 w-full">
                    <SelectValue placeholder="Select reference type" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="null">No Reference</SelectItem>
                    <SelectItem value="sales_order">Sales Order</SelectItem>
                    <SelectItem value="purchase_order">Purchase Order</SelectItem>
                    <SelectItem value="invoice">Invoice</SelectItem>
                    <SelectItem value="payment">Payment</SelectItem>
                    <SelectItem value="adjustment">Adjustment</SelectItem>
                  </SelectContent>
                </Select>
                <InputError :message="form.errors.reference_type" class="mt-2" />
              </div>

              <div class="space-y-3">
                <Label for="reference_id" class="text-sm font-medium">Reference ID</Label>
                <Input id="reference_id" v-model="form.reference_id" type="number" placeholder="Reference ID"
                  class="h-10" />
                <InputError :message="form.errors.reference_id" class="mt-2" />
              </div>
            </div>

            <div class="space-y-3">
              <Label for="description" class="text-sm font-medium">Description</Label>
              <Textarea id="description" v-model="form.description" rows="3"
                placeholder="Enter journal entry description" class="h-20" />
              <p class="text-sm text-muted-foreground">
                Brief description of the journal entry
              </p>
              <p v-if="form.errors.description" class="text-sm text-destructive">
                {{ form.errors.description }}
              </p>
            </div>

            <!-- Journal Entry Lines -->
            <div class="space-y-4">
              <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-card-foreground">Journal Entry Lines</h3>
                <Button type="button" @click="addLine" variant="outline" size="sm" class="h-9">
                  <Plus class="w-4 h-4 mr-2" />
                  Add Line
                </Button>
              </div>

              <div v-if="form.errors.lines" class="text-red-600 text-sm">
                {{ form.errors.lines }}
              </div>

              <div class="space-y-4">
                <div v-for="(line, index) in form.lines" :key="index"
                  class="grid grid-cols-1 md:grid-cols-4 gap-4 p-4 border border-border rounded-lg bg-card">
                  <div class="space-y-3">
                    <Label :for="`account_${index}`" class="text-sm font-medium">Account</Label>
                    <Select v-model="line.account_id" @update:model-value="updateLine(index, 'account_id', $event)">
                      <SelectTrigger class="h-10 w-full">
                        <SelectValue :placeholder="`Select account for line ${index + 1}`" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem v-for="account in accounts" :key="account.id" :value="account.id">
                          {{ account.account_code }} - {{ account.name }}
                        </SelectItem>
                      </SelectContent>
                    </Select>
                  </div>

                  <div class="space-y-3">
                    <Label :for="`description_${index}`" class="text-sm font-medium">Description</Label>
                    <Input :id="`description_${index}`" v-model="line.description"
                      @input="updateLine(index, 'description', $event.target.value)" placeholder="Line description"
                      class="h-10" />
                  </div>

                  <div class="space-y-3">
                    <Label :for="`debit_${index}`" class="text-sm font-medium">Debit Amount</Label>
                    <Input :id="`debit_${index}`" v-model="line.debit_amount"
                      @input="updateLine(index, 'debit_amount', $event.target.value)" type="number" step="0.01" min="0"
                      placeholder="0.00" class="h-10" />
                  </div>

                  <div class="flex space-x-3">
                    <div class="flex-1 space-y-3">
                      <Label :for="`credit_${index}`" class="text-sm font-medium">Credit Amount</Label>
                      <Input :id="`credit_${index}`" v-model="line.credit_amount"
                        @input="updateLine(index, 'credit_amount', $event.target.value)" type="number" step="0.01"
                        min="0" placeholder="0.00" class="h-10" />
                    </div>
                    <div class="flex items-center mt-6">
                      <Button type="button" @click="removeLine(index)" variant="destructive" size="sm"
                        class="h-10 w-10 p-0">
                        <Trash2 class="w-4 h-4" />
                      </Button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Totals -->
              <div
                class="flex justify-end space-x-4 text-lg font-semibold p-4 bg-muted/30 rounded-lg border border-border">
                <div class="text-card-foreground">Total Debit: {{ formatCurrency(totalDebit) }}</div>
                <div class="text-card-foreground">Total Credit: {{ formatCurrency(totalCredit) }}</div>
                <div :class="isBalanced ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
                  Difference: {{ formatCurrency(difference) }}
                </div>
              </div>
            </div>

            <!-- Submit Button -->
            <div class="flex items-center justify-end space-x-4 pt-6 border-t border-border">
              <Link :href="route('finance.journal-entries.index')">
              <Button variant="outline" type="button" class="h-10 px-4">
                Cancel
              </Button>
              </Link>
              <Button type="submit" :disabled="form.processing || !isBalanced" class="h-10 px-6">
                <Loader2 v-if="form.processing" class="w-4 h-4 mr-2 animate-spin" />
                Create Journal Entry
              </Button>
            </div>
          </form>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Textarea } from '@/components/ui/textarea'
import { ArrowLeft, Plus, Trash2, Loader2 } from 'lucide-vue-next'
import InputError from '@/components/InputError.vue'
import { apiService } from '@/services/api'
import type { ChartOfAccount } from '@/types/erp'

interface JournalEntryLine {
  account_id: string | number
  description: string
  debit_amount: number
  credit_amount: number
}

const accounts = ref<ChartOfAccount[]>([])
const loading = ref(false)

const form = useForm({
  entry_date: new Date().toISOString().split('T')[0],
  reference_type: 'null',
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

const updateLine = (index: number, field: keyof JournalEntryLine, value: unknown) => {
  if (value !== null && value !== undefined) {
    form.lines[index][field] = value as never
  }
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
    const response = await apiService.getChartOfAccounts({ page: 1 })
    accounts.value = response.data
  } catch (error) {
    console.error('Error fetching accounts:', error)
  } finally {
    loading.value = false
  }
}

const submit = async () => {
  if (!isBalanced.value) {
    alert('Total debit must equal total credit')
    return
  }

  if (form.lines.length < 2) {
    alert('At least 2 lines are required')
    return
  }

  try {
    const formData = {
      entry_date: form.entry_date,
      reference_type: form.reference_type === 'null' ? null : form.reference_type,
      reference_id: form.reference_id ? parseInt(form.reference_id) : null,
      description: form.description,
      lines: form.lines.map(line => ({
        ...line,
        debit_amount: parseFloat(line.debit_amount.toString()) || 0,
        credit_amount: parseFloat(line.credit_amount.toString()) || 0
      }))
    }

    await apiService.createJournalEntry(formData)

    // Redirect ke index page setelah berhasil
    window.location.href = route('finance.journal-entries.index')
  } catch (error: any) {
    if (error.response?.data?.message) {
      alert(`Error: ${error.response.data.message}`)
    } else {
      alert('Failed to create journal entry. Please try again.')
    }
  }
}

onMounted(() => {
  fetchAccounts()
  // Add initial lines
  addLine()
  addLine()
})
</script>
