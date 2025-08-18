<template>
  <AppLayout title="Journal Entry Details">
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl leading-tight">
          Journal Entry Details
        </h2>
        <div class="flex items-center space-x-2">
          <Link :href="route('finance.journal-entries.edit', entry.id)" v-if="entry.status === 'draft'"
            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
          <Edit class="w-4 h-4 mr-2" />
          Edit
          </Link>
          <Button v-if="entry.status === 'draft'" @click="postEntry" :disabled="posting"
            class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
            <Loader2 v-if="posting" class="w-4 h-4 mr-2 animate-spin" />
            Post Entry
          </Button>
          <Link :href="route('finance.journal-entries.index')"
            class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
          <ArrowLeft class="w-4 h-4 mr-2" />
          Back to List
          </Link>
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <!-- Header Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
              <div>
                <Label class="text-sm font-medium text-gray-500">Entry Number</Label>
                <p class="text-lg font-semibold">{{ entry.entry_number }}</p>
              </div>
              <div>
                <Label class="text-sm font-medium text-gray-500">Entry Date</Label>
                <p class="text-lg">{{ formatDate(entry.entry_date) }}</p>
              </div>
              <div>
                <Label class="text-sm font-medium text-gray-500">Status</Label>
                <Badge :variant="getStatusVariant(entry.status)">
                  {{ getStatusLabel(entry.status) }}
                </Badge>
              </div>
              <div>
                <Label class="text-sm font-medium text-gray-500">Created By</Label>
                <p class="text-lg">{{ entry.created_by_user?.name || 'N/A' }}</p>
              </div>
            </div>

            <!-- Reference Information -->
            <div v-if="entry.reference_type || entry.reference_id" class="mb-8">
              <h3 class="text-lg font-medium mb-4">Reference Information</h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div v-if="entry.reference_type">
                  <Label class="text-sm font-medium text-gray-500">Reference Type</Label>
                  <p class="text-lg">{{ getReferenceTypeLabel(entry.reference_type) }}</p>
                </div>
                <div v-if="entry.reference_id">
                  <Label class="text-sm font-medium text-gray-500">Reference ID</Label>
                  <p class="text-lg">{{ entry.reference_id }}</p>
                </div>
              </div>
            </div>

            <!-- Description -->
            <div v-if="entry.description" class="mb-8">
              <Label class="text-sm font-medium text-gray-500">Description</Label>
              <p class="text-lg mt-1">{{ entry.description }}</p>
            </div>

            <!-- Journal Entry Lines -->
            <div class="mb-8">
              <h3 class="text-lg font-medium mb-4">Journal Entry Lines</h3>
              <div class="overflow-x-auto">
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead>Line</TableHead>
                      <TableHead>Account</TableHead>
                      <TableHead>Description</TableHead>
                      <TableHead class="text-right">Debit</TableHead>
                      <TableHead class="text-right">Credit</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    <TableRow v-for="line in entry.items" :key="line.id">
                      <TableCell>{{ line.line_number }}</TableCell>
                      <TableCell>
                        <div>
                          <div class="font-medium">{{ line.account?.account_code }}</div>
                          <div class="text-sm text-gray-500">{{ line.account?.name }}</div>
                        </div>
                      </TableCell>
                      <TableCell>{{ line.description || '-' }}</TableCell>
                      <TableCell class="text-right">
                        <span v-if="line.debit_amount > 0" class="font-medium">
                          {{ formatCurrency(line.debit_amount) }}
                        </span>
                        <span v-else>-</span>
                      </TableCell>
                      <TableCell class="text-right">
                        <span v-if="line.credit_amount > 0" class="font-medium">
                          {{ formatCurrency(line.credit_amount) }}
                        </span>
                        <span v-else>-</span>
                      </TableCell>
                    </TableRow>
                  </TableBody>
                </Table>
              </div>
            </div>

            <!-- Totals -->
            <div class="flex justify-end space-x-8 text-lg font-semibold border-t pt-4">
              <div>Total Debit: {{ formatCurrency(entry.total_debit) }}</div>
              <div>Total Credit: {{ formatCurrency(entry.total_credit) }}</div>
              <div :class="isBalanced ? 'text-green-600' : 'text-red-600'">
                Difference: {{ formatCurrency(difference) }}
              </div>
            </div>

            <!-- Posted Information -->
            <div v-if="entry.posted_at" class="mt-8 p-4 bg-green-50 border border-green-200 rounded-lg">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                      d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                      clip-rule="evenodd" />
                  </svg>
                </div>
                <div class="ml-3">
                  <h3 class="text-sm font-medium text-green-800">Posted Successfully</h3>
                  <div class="mt-1 text-sm text-green-700">
                    <p>This journal entry was posted on {{ formatDateTime(entry.posted_at) }}</p>
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
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Label } from '@/components/ui/label'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { ArrowLeft, Edit, Loader2 } from 'lucide-vue-next'
import type { JournalEntry } from '@/types/erp'

interface Props {
  entry: JournalEntry
}

const props = defineProps<Props>()
const posting = ref(false)

const difference = computed(() => {
  return Math.abs(props.entry.total_debit - props.entry.total_credit)
})

const isBalanced = computed(() => {
  return Math.abs(props.entry.total_debit - props.entry.total_credit) < 0.01
})

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const formatDateTime = (date: string) => {
  return new Date(date).toLocaleString('id-ID', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR'
  }).format(amount)
}

const getStatusLabel = (status: string) => {
  const labels: Record<string, string> = {
    draft: 'Draft',
    posted: 'Posted',
    cancelled: 'Cancelled'
  }
  return labels[status] || status
}

const getStatusVariant = (status: string): 'default' | 'secondary' | 'destructive' => {
  const variants: Record<string, 'default' | 'secondary' | 'destructive'> = {
    draft: 'secondary',
    posted: 'default',
    cancelled: 'destructive'
  }
  return variants[status] || 'default'
}

const getReferenceTypeLabel = (type: string) => {
  const labels: Record<string, string> = {
    sales_order: 'Sales Order',
    purchase_order: 'Purchase Order',
    invoice: 'Invoice',
    payment: 'Payment',
    adjustment: 'Adjustment'
  }
  return labels[type] || type
}

const postEntry = async () => {
  if (!confirm('Are you sure you want to post this journal entry? This action cannot be undone.')) {
    return
  }

  try {
    posting.value = true
    await router.post(route('finance.journal-entries.post', props.entry.id))
  } catch (error) {
    console.error('Error posting journal entry:', error)
  } finally {
    posting.value = false
  }
}
</script>
