<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold tracking-tight">Customer Segments</h1>
          <p class="text-muted-foreground">
            Manage customer segments and categorization
          </p>
        </div>
        <Button @click="openCreateDialog">
          <Plus class="mr-2 h-4 w-4" />
          Create Segment
        </Button>
      </div>

      <!-- Statistics Cards -->
      <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Total Segments</CardTitle>
            <Users class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ segments.length }}</div>
          </CardContent>
        </Card>
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Active Segments</CardTitle>
            <UserCheck class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ activeSegmentsCount }}</div>
          </CardContent>
        </Card>
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Total Customers</CardTitle>
            <UserPlus class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ totalCustomers }}</div>
          </CardContent>
        </Card>
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Avg Customers/Segment</CardTitle>
            <BarChart3 class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ averageCustomersPerSegment }}</div>
          </CardContent>
        </Card>
      </div>

      <!-- Filters -->
      <Card>
        <CardHeader>
          <CardTitle>Filters</CardTitle>
        </CardHeader>
        <CardContent>
          <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            <div class="space-y-2">
              <Label for="search">Search</Label>
              <Input id="search" v-model="filters.search" placeholder="Search by name..." @input="debouncedSearch" />
            </div>
            <div class="space-y-2">
              <Label for="is_active">Status</Label>
              <Select v-model="filters.is_active" @update:model-value="loadSegments">
                <SelectTrigger>
                  <SelectValue placeholder="All statuses" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="">All statuses</SelectItem>
                  <SelectItem value="true">Active</SelectItem>
                  <SelectItem value="false">Inactive</SelectItem>
                </SelectContent>
              </Select>
            </div>
            <div class="space-y-2">
              <Label for="per_page">Per page</Label>
              <Select v-model="filters.per_page" @update:model-value="loadSegments">
                <SelectTrigger>
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="10">10</SelectItem>
                  <SelectItem value="15">15</SelectItem>
                  <SelectItem value="25">25</SelectItem>
                  <SelectItem value="50">50</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Segments Grid -->
      <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        <Card v-for="segment in segments" :key="segment.id" class="relative">
          <CardHeader>
            <div class="flex items-center justify-between">
              <CardTitle class="flex items-center gap-2">
                <div v-if="segment.color" class="w-3 h-3 rounded-full" :style="{ backgroundColor: segment.color }">
                </div>
                {{ segment.name }}
              </CardTitle>
              <DropdownMenu>
                <DropdownMenuTrigger as-child>
                  <Button variant="ghost" class="h-8 w-8 p-0">
                    <MoreHorizontal class="h-4 w-4" />
                  </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end">
                  <DropdownMenuItem @click="viewSegment(segment)">
                    <Eye class="mr-2 h-4 w-4" />
                    View
                  </DropdownMenuItem>
                  <DropdownMenuItem @click="editSegment(segment)">
                    <Edit class="mr-2 h-4 w-4" />
                    Edit
                  </DropdownMenuItem>
                  <DropdownMenuItem @click="toggleSegmentStatus(segment)">
                    <Power class="mr-2 h-4 w-4" />
                    {{ segment.is_active ? 'Deactivate' : 'Activate' }}
                  </DropdownMenuItem>
                  <DropdownMenuItem @click="deleteSegment(segment)" class="text-destructive">
                    <Trash2 class="mr-2 h-4 w-4" />
                    Delete
                  </DropdownMenuItem>
                </DropdownMenuContent>
              </DropdownMenu>
            </div>
          </CardHeader>
          <CardContent>
            <div class="space-y-4">
              <div>
                <p class="text-sm text-muted-foreground">{{ segment.description || 'No description' }}</p>
              </div>
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                  <Badge :variant="segment.is_active ? 'default' : 'secondary'">
                    {{ segment.is_active ? 'Active' : 'Inactive' }}
                  </Badge>
                </div>
                <div class="text-sm text-muted-foreground">
                  {{ segment.customers_count || 0 }} customers
                </div>
              </div>
              <div v-if="segment.criteria" class="text-xs text-muted-foreground">
                <div class="font-medium mb-1">Criteria:</div>
                <pre class="whitespace-pre-wrap">{{ JSON.stringify(segment.criteria, null, 2) }}</pre>
              </div>
              <div class="text-xs text-muted-foreground">
                Created {{ formatDate(segment.created_at) }}
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Empty State -->
      <div v-if="segments.length === 0 && !loading" class="text-center py-12">
        <Users class="mx-auto h-12 w-12 text-muted-foreground" />
        <h3 class="mt-4 text-lg font-semibold">No segments found</h3>
        <p class="mt-2 text-muted-foreground">
          Get started by creating your first customer segment.
        </p>
        <Button @click="openCreateDialog" class="mt-4">
          <Plus class="mr-2 h-4 w-4" />
          Create Segment
        </Button>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="text-center py-12">
        <Loader2 class="mx-auto h-8 w-8 animate-spin text-muted-foreground" />
        <p class="mt-2 text-muted-foreground">Loading segments...</p>
      </div>

      <!-- Pagination -->
      <div v-if="pagination.last_page > 1" class="flex items-center justify-center">
        <div class="flex items-center space-x-2">
          <Button variant="outline" size="sm" :disabled="pagination.current_page === 1"
            @click="changePage(pagination.current_page - 1)">
            Previous
          </Button>
          <span class="text-sm text-muted-foreground">
            Page {{ pagination.current_page }} of {{ pagination.last_page }}
          </span>
          <Button variant="outline" size="sm" :disabled="pagination.current_page === pagination.last_page"
            @click="changePage(pagination.current_page + 1)">
            Next
          </Button>
        </div>
      </div>
    </div>

    <!-- Create/Edit Dialog -->
    <Dialog v-model:open="dialogOpen">
      <DialogContent class="max-w-2xl">
        <DialogHeader>
          <DialogTitle>{{ editingSegment ? 'Edit Segment' : 'Create Segment' }}</DialogTitle>
        </DialogHeader>
        <form @submit.prevent="saveSegment" class="space-y-4">
          <div class="grid gap-4 md:grid-cols-2">
            <div class="space-y-2">
              <Label for="name">Name *</Label>
              <Input id="name" v-model="form.name" required />
            </div>
            <div class="space-y-2">
              <Label for="color">Color</Label>
              <Input id="color" v-model="form.color" type="color" class="h-10" />
            </div>
          </div>
          <div class="space-y-2">
            <Label for="description">Description</Label>
            <Textarea id="description" v-model="form.description" rows="3" />
          </div>
          <div class="space-y-2">
            <Label for="criteria">Criteria (JSON)</Label>
            <Textarea id="criteria" v-model="form.criteria" rows="6"
              placeholder='{"field": "value", "operator": "equals"}' />
            <p class="text-xs text-muted-foreground">
              Define segment criteria in JSON format. Example: {"status": "active", "total_spent": {"operator": "gte",
              "value": 1000}}
            </p>
          </div>
          <div class="flex items-center space-x-2">
            <Checkbox id="is_active" v-model:checked="form.is_active" />
            <Label for="is_active">Active</Label>
          </div>
          <DialogFooter>
            <Button type="button" variant="outline" @click="closeDialog">
              Cancel
            </Button>
            <Button type="submit" :disabled="saving">
              <Loader2 v-if="saving" class="mr-2 h-4 w-4 animate-spin" />
              {{ editingSegment ? 'Update' : 'Create' }}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>

    <!-- View Segment Dialog -->
    <Dialog v-model:open="viewDialogOpen">
      <DialogContent class="max-w-4xl">
        <DialogHeader>
          <DialogTitle>Segment Details</DialogTitle>
        </DialogHeader>
        <div v-if="viewingSegment" class="space-y-6">
          <div class="grid gap-4 md:grid-cols-2">
            <div>
              <Label class="text-sm font-medium">Name</Label>
              <p class="text-sm text-muted-foreground">{{ viewingSegment.name }}</p>
            </div>
            <div>
              <Label class="text-sm font-medium">Status</Label>
              <Badge :variant="viewingSegment.is_active ? 'default' : 'secondary'">
                {{ viewingSegment.is_active ? 'Active' : 'Inactive' }}
              </Badge>
            </div>
            <div>
              <Label class="text-sm font-medium">Color</Label>
              <div class="flex items-center gap-2">
                <div v-if="viewingSegment.color" class="w-4 h-4 rounded-full border"
                  :style="{ backgroundColor: viewingSegment.color }"></div>
                <span class="text-sm text-muted-foreground">{{ viewingSegment.color || 'No color' }}</span>
              </div>
            </div>
            <div>
              <Label class="text-sm font-medium">Created</Label>
              <p class="text-sm text-muted-foreground">{{ formatDate(viewingSegment.created_at) }}</p>
            </div>
          </div>
          <div v-if="viewingSegment.description">
            <Label class="text-sm font-medium">Description</Label>
            <p class="text-sm text-muted-foreground">{{ viewingSegment.description }}</p>
          </div>
          <div v-if="viewingSegment.criteria">
            <Label class="text-sm font-medium">Criteria</Label>
            <pre
              class="text-sm text-muted-foreground bg-muted p-3 rounded-md overflow-auto">{{ JSON.stringify(viewingSegment.criteria, null, 2) }}</pre>
          </div>

          <!-- Customers in Segment -->
          <div>
            <div class="flex items-center justify-between mb-4">
              <Label class="text-sm font-medium">Customers in Segment</Label>
              <Button size="sm" @click="loadSegmentCustomers(viewingSegment)">
                <RefreshCw class="mr-2 h-4 w-4" />
                Refresh
              </Button>
            </div>
            <div v-if="segmentCustomersLoading" class="text-center py-4">
              <Loader2 class="mx-auto h-6 w-6 animate-spin" />
            </div>
            <div v-else-if="segmentCustomers.length === 0" class="text-center py-4 text-muted-foreground">
              No customers in this segment
            </div>
            <div v-else class="space-y-2 max-h-60 overflow-y-auto">
              <div v-for="customer in segmentCustomers" :key="customer.id"
                class="flex items-center justify-between p-2 border rounded">
                <div>
                  <div class="font-medium">{{ customer.name }}</div>
                  <div class="text-sm text-muted-foreground">{{ customer.email }}</div>
                </div>
                <Badge variant="outline">{{ customer.status }}</Badge>
              </div>
            </div>
          </div>
        </div>
        <DialogFooter>
          <Button @click="closeViewDialog">Close</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Checkbox } from '@/components/ui/checkbox'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import { Plus, Users, UserCheck, UserPlus, BarChart3, MoreHorizontal, Eye, Edit, Trash2, Loader2, Power, RefreshCw } from 'lucide-vue-next'
import { useApi } from '@/composables/useApi'
import { debounce } from 'lodash'

// Types
interface CustomerSegment {
  id: number
  name: string
  description: string
  criteria: any
  color: string
  is_active: boolean
  created_by: number
  customers_count?: number
  created_at: string
  updated_at: string
}

interface Customer {
  id: number
  name: string
  email: string
  status: string
}

// Breadcrumbs
const breadcrumbs = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'CRM', href: '/crm/customer-segments' },
  { title: 'Customer Segments', href: '/crm/customer-segments' }
]

// State
const segments = ref<CustomerSegment[]>([])
const segmentCustomers = ref<Customer[]>([])
const loading = ref(false)
const segmentCustomersLoading = ref(false)
const saving = ref(false)
const dialogOpen = ref(false)
const viewDialogOpen = ref(false)
const editingSegment = ref<CustomerSegment | null>(null)
const viewingSegment = ref<CustomerSegment | null>(null)

const filters = ref({
  search: '',
  is_active: '',
  per_page: '15'
})

const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0
})

const form = ref({
  name: '',
  description: '',
  criteria: '',
  color: '#3b82f6',
  is_active: true
})

// API
const api = useApi()

// Computed
const activeSegmentsCount = computed(() => {
  return segments.value.filter(segment => segment.is_active).length
})

const totalCustomers = computed(() => {
  return segments.value.reduce((total, segment) => total + (segment.customers_count || 0), 0)
})

const averageCustomersPerSegment = computed(() => {
  if (segments.value.length === 0) return 0
  return Math.round(totalCustomers.value / segments.value.length)
})

// Methods
const loadSegments = async () => {
  loading.value = true
  try {
    const params = new URLSearchParams({
      page: pagination.value.current_page.toString(),
      ...filters.value
    })

    const response = await api.get(`/api/crm/customer-segments?${params}`)
    segments.value = response.data.data
    pagination.value = response.data.pagination
  } catch (error) {
    console.error('Error loading segments:', error)
    window.toast?.error('Failed to load segments')
  } finally {
    loading.value = false
  }
}

const loadSegmentCustomers = async (segment: CustomerSegment) => {
  segmentCustomersLoading.value = true
  try {
    const response = await api.get(`/api/crm/customer-segments/${segment.id}/customers`)
    segmentCustomers.value = response.data.data
  } catch (error) {
    console.error('Error loading segment customers:', error)
    window.toast?.error('Failed to load segment customers')
  } finally {
    segmentCustomersLoading.value = false
  }
}

const debouncedSearch = debounce(() => {
  pagination.value.current_page = 1
  loadSegments()
}, 300)

const changePage = (page: number) => {
  pagination.value.current_page = page
  loadSegments()
}

const openCreateDialog = () => {
  editingSegment.value = null
  resetForm()
  dialogOpen.value = true
}

const editSegment = (segment: CustomerSegment) => {
  editingSegment.value = segment
  form.value = {
    name: segment.name,
    description: segment.description || '',
    criteria: segment.criteria ? JSON.stringify(segment.criteria, null, 2) : '',
    color: segment.color || '#3b82f6',
    is_active: segment.is_active
  }
  dialogOpen.value = true
}

const saveSegment = async () => {
  saving.value = true
  try {
    let criteria = null
    if (form.value.criteria.trim()) {
      try {
        criteria = JSON.parse(form.value.criteria)
      } catch (error) {
        window.toast?.error('Invalid JSON format for criteria')
        return
      }
    }

    const data = {
      ...form.value,
      criteria
    }

    if (editingSegment.value) {
      await api.put(`/api/crm/customer-segments/${editingSegment.value.id}`, data)
      window.toast?.success('Segment updated successfully')
    } else {
      await api.post('/api/crm/customer-segments', data)
      window.toast?.success('Segment created successfully')
    }

    closeDialog()
    loadSegments()
  } catch (error: any) {
    console.error('Error saving segment:', error)
    const message = error.response?.data?.message || 'Failed to save segment'
    window.toast?.error(message)
  } finally {
    saving.value = false
  }
}

const toggleSegmentStatus = async (segment: CustomerSegment) => {
  try {
    await api.post(`/api/crm/customer-segments/${segment.id}/toggle-status`)
    window.toast?.success('Segment status updated successfully')
    loadSegments()
  } catch (error: any) {
    console.error('Error toggling segment status:', error)
    const message = error.response?.data?.message || 'Failed to update segment status'
    window.toast?.error(message)
  }
}

const deleteSegment = async (segment: CustomerSegment) => {
  if (!confirm('Are you sure you want to delete this segment?')) return

  try {
    await api.delete(`/api/crm/customer-segments/${segment.id}`)
    window.toast?.success('Segment deleted successfully')
    loadSegments()
  } catch (error: any) {
    console.error('Error deleting segment:', error)
    const message = error.response?.data?.message || 'Failed to delete segment'
    window.toast?.error(message)
  }
}

const viewSegment = (segment: CustomerSegment) => {
  viewingSegment.value = segment
  segmentCustomers.value = []
  viewDialogOpen.value = true
  loadSegmentCustomers(segment)
}

const closeDialog = () => {
  dialogOpen.value = false
  editingSegment.value = null
  resetForm()
}

const closeViewDialog = () => {
  viewDialogOpen.value = false
  viewingSegment.value = null
  segmentCustomers.value = []
}

const resetForm = () => {
  form.value = {
    name: '',
    description: '',
    criteria: '',
    color: '#3b82f6',
    is_active: true
  }
}

// Utility functions
const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString()
}

// Lifecycle
onMounted(() => {
  loadSegments()
})
</script>
