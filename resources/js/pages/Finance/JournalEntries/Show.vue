<template>

  <Head title="Journal Entry Details" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6">
      <!-- Header Section -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold tracking-tight">Journal Entry Details</h1>
          <p class="text-muted-foreground mt-1">
            View detailed information about this journal entry
          </p>
        </div>
        <div class="flex gap-3">
          <Link :href="route('finance.journal-entries.edit', entry.id)" v-if="entry.status === 'draft'">
          <Button variant="outline" class="h-10 px-4">
            <Edit class="w-4 h-4 mr-2" />
            Edit
          </Button>
          </Link>
          <Button v-if="entry.status === 'draft'" @click="postEntry" :disabled="posting" class="h-10 px-4">
            <Loader2 v-if="posting" class="w-4 h-4 mr-2 animate-spin" />
            Post Entry
          </Button>
          <Link :href="route('finance.journal-entries.index')">
          <Button variant="outline" class="h-10 px-4">
            <ArrowLeft class="w-4 h-4 mr-2" />
            Back
          </Button>
          </Link>
        </div>
      </div>

      <!-- Main Information Card -->
      <Card class="mt-8 shadow-sm border-border">
        <CardHeader class="border-b border-border bg-muted/30">
          <CardTitle class="text-xl font-semibold">Journal Entry Information</CardTitle>
        </CardHeader>
        <CardContent class="p-6">
          <!-- Entry Information Grid -->
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div>
              <Label class="text-sm font-medium text-muted-foreground">Entry Number</Label>
              <p class="text-xl font-mono font-semibold text-card-foreground mt-1">{{ entry.entry_number }}</p>
            </div>
            <div>
              <Label class="text-sm font-medium text-muted-foreground">Entry Date</Label>
              <p class="text-xl text-card-foreground mt-1">{{ formatDate(entry.entry_date) }}</p>
            </div>
            <div>
              <Label class="text-sm font-medium text-muted-foreground">Status</Label>
              <div class="mt-1">
                <Badge :variant="getStatusVariant(entry.status)" class="text-sm">
                  {{ getStatusLabel(entry.status) }}
                </Badge>
              </div>
            </div>
            <div>
              <Label class="text-sm font-medium text-muted-foreground">Created By</Label>
              <p class="text-xl text-card-foreground mt-1">{{ entry.created_by_user?.name || 'N/A' }}</p>
            </div>
          </div>

          <!-- Reference Information -->
          <div v-if="entry.reference_type || entry.reference_id" class="mb-8">
            <h3 class="text-lg font-medium text-card-foreground mb-4">Reference Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div v-if="entry.reference_type">
                <Label class="text-sm font-medium text-muted-foreground">Reference Type</Label>
                <p class="text-lg text-card-foreground mt-1">{{ getReferenceTypeLabel(entry.reference_type) }}</p>
              </div>
              <div v-if="entry.reference_id">
                <Label class="text-sm font-medium text-muted-foreground">Reference ID</Label>
                <p class="text-lg text-card-foreground mt-1">{{ entry.reference_id }}</p>
              </div>
            </div>
          </div>

          <!-- Description -->
          <div v-if="entry.description" class="mb-8">
            <h3 class="text-lg font-medium text-card-foreground mb-4">Description</h3>
            <p class="text-lg text-card-foreground">{{ entry.description }}</p>
          </div>

          <!-- Journal Entry Lines -->
          <div class="mb-8">
            <h3 class="text-lg font-medium text-card-foreground mb-4">Journal Entry Lines</h3>
            <div class="rounded-md border border-border">
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
                  <TableRow v-for="line in entry.lines" :key="line.id">
                    <TableCell>
                      <span class="font-mono text-sm text-card-foreground">{{ line.id }}</span>
                    </TableCell>
                    <TableCell>
                      <div>
                        <div class="font-medium text-card-foreground">{{ line.account?.account_code }}</div>
                        <div class="text-sm text-muted-foreground">{{ line.account?.name }}</div>
                      </div>
                    </TableCell>
                    <TableCell>
                      <span class="text-card-foreground">{{ line.description || '-' }}</span>
                    </TableCell>
                    <TableCell class="text-right">
                      <span v-if="line.debit_amount > 0" class="font-medium text-green-600 dark:text-green-400">
                        {{ formatCurrency(line.debit_amount) }}
                      </span>
                      <span v-else class="text-muted-foreground">-</span>
                    </TableCell>
                    <TableCell class="text-right">
                      <span v-if="line.credit_amount > 0" class="font-medium text-red-600 dark:text-red-400">
                        {{ formatCurrency(line.credit_amount) }}
                      </span>
                      <span v-else class="text-muted-foreground">-</span>
                    </TableCell>
                  </TableRow>
                </TableBody>
              </Table>
            </div>
          </div>

          <!-- Totals -->
          <div class="mb-8">
            <h3 class="text-lg font-medium text-card-foreground mb-4">Entry Totals</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div class="text-center">
                <Label class="text-sm font-medium text-muted-foreground">Total Debit</Label>
                <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">
                  {{ formatCurrency(entry.total_debit) }}
                </p>
              </div>
              <div class="text-center">
                <Label class="text-sm font-medium text-muted-foreground">Total Credit</Label>
                <p class="text-2xl font-bold text-red-600 dark:text-red-400 mt-1">
                  {{ formatCurrency(entry.total_credit) }}
                </p>
              </div>
              <div class="text-center">
                <Label class="text-sm font-medium text-muted-foreground">Difference</Label>
                <p class="text-2xl font-bold mt-1"
                  :class="isBalanced ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
                  {{ formatCurrency(difference) }}
                </p>
                <Badge v-if="isBalanced" variant="default" class="mt-2">Balanced</Badge>
                <Badge v-else variant="destructive" class="mt-2">Unbalanced</Badge>
              </div>
            </div>
          </div>

          <!-- Posted Information -->
          <div v-if="entry.posted_at"
            class="mb-8 p-4 bg-green-50 dark:bg-green-950/20 border border-green-200 rounded-lg">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                    clip-rule="evenodd" />
                </svg>
              </div>
              <div class="ml-3">
                <h3 class="text-lg font-medium text-green-800 dark:text-green-200">Posted Successfully</h3>
                <p class="text-green-700 dark:text-green-300 mt-1">
                  This journal entry was posted on {{ formatDateTime(entry.posted_at) }}
                </p>
              </div>
            </div>
          </div>

          <!-- Additional Information -->
          <div>
            <h3 class="text-lg font-medium text-card-foreground mb-4">Additional Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div>
                <Label class="text-sm font-medium text-muted-foreground">Created At</Label>
                <p class="text-lg text-card-foreground mt-1">{{ formatDateTime(entry.created_at) }}</p>
              </div>
              <div>
                <Label class="text-sm font-medium text-muted-foreground">Updated At</Label>
                <p class="text-lg text-card-foreground mt-1">{{ formatDateTime(entry.updated_at) }}</p>
              </div>
              <div v-if="entry.posted_at">
                <Label class="text-sm font-medium text-muted-foreground">Posted At</Label>
                <p class="text-lg text-card-foreground mt-1">{{ formatDateTime(entry.posted_at) }}</p>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Label } from '@/components/ui/label'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { ArrowLeft, Edit, Loader2 } from 'lucide-vue-next'
import type { JournalEntry } from '@/types/erp'
import type { BreadcrumbItemType } from '@/types'

interface Props {
  entry: JournalEntry
}

const props = defineProps<Props>()
const posting = ref(false)

const breadcrumbs = computed<BreadcrumbItemType[]>(() => [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Finance', href: '/finance' },
  { title: 'Journal Entries', href: '/finance/journal-entries' },
  { title: props.entry.entry_number, href: `/finance/journal-entries/${props.entry.id}` }
])

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
