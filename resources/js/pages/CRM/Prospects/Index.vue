<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold tracking-tight">Prospects</h1>
          <p class="text-muted-foreground">
            Manage your sales prospects and leads
          </p>
        </div>
        <Button @click="openCreateDialog">
          <Plus class="mr-2 h-4 w-4" />
          Add Prospect
        </Button>
      </div>

      <!-- Statistics Cards -->
      <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Total Prospects</CardTitle>
            <Users class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ statistics.total_prospects || 0 }}</div>
          </CardContent>
        </Card>
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">New Prospects</CardTitle>
            <UserPlus class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ statistics.new_prospects || 0 }}</div>
          </CardContent>
        </Card>
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Qualified</CardTitle>
            <UserCheck class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ statistics.qualified_prospects || 0 }}</div>
          </CardContent>
        </Card>
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Total Value</CardTitle>
            <DollarSign class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">
              {{ formatCurrency(statistics.total_estimated_value || 0) }}
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Filters -->
      <Card>
        <CardHeader>
          <CardTitle>Filters</CardTitle>
        </CardHeader>
        <CardContent>
          <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            <div class="space-y-2">
              <Label for="search">Search</Label>
              <Input id="search" v-model="filters.search" placeholder="Search by name, email, or company..."
                @input="debouncedSearch" />
            </div>
            <div class="space-y-2">
              <Label for="status">Status</Label>
              <Select v-model="filters.status" @update:model-value="loadProspects">
                <SelectTrigger>
                  <SelectValue placeholder="All statuses" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="">All statuses</SelectItem>
                  <SelectItem value="new">New</SelectItem>
                  <SelectItem value="contacted">Contacted</SelectItem>
                  <SelectItem value="qualified">Qualified</SelectItem>
                  <SelectItem value="proposal">Proposal</SelectItem>
                  <SelectItem value="negotiation">Negotiation</SelectItem>
                  <SelectItem value="won">Won</SelectItem>
                  <SelectItem value="lost">Lost</SelectItem>
                </SelectContent>
              </Select>
            </div>
            <div class="space-y-2">
              <Label for="priority">Priority</Label>
              <Select v-model="filters.priority" @update:model-value="loadProspects">
                <SelectTrigger>
                  <SelectValue placeholder="All priorities" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="">All priorities</SelectItem>
                  <SelectItem value="low">Low</SelectItem>
                  <SelectItem value="medium">Medium</SelectItem>
                  <SelectItem value="high">High</SelectItem>
                  <SelectItem value="urgent">Urgent</SelectItem>
                </SelectContent>
              </Select>
            </div>
            <div class="space-y-2">
              <Label for="assigned_to">Assigned To</Label>
              <Select v-model="filters.assigned_to" @update:model-value="loadProspects">
                <SelectTrigger>
                  <SelectValue placeholder="All users" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="">All users</SelectItem>
                  <SelectItem v-for="user in assignedUsers" :key="user.id" :value="user.id.toString()">
                    {{ user.name }}
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Prospects Table -->
      <Card>
        <CardHeader>
          <CardTitle>Prospects</CardTitle>
        </CardHeader>
        <CardContent>
          <div class="space-y-4">
            <div class="flex items-center justify-between">
              <div class="text-sm text-muted-foreground">
                Showing {{ pagination.total }} prospects
              </div>
              <div class="flex items-center space-x-2">
                <Label for="per-page">Per page</Label>
                <Select v-model="filters.per_page" @update:model-value="loadProspects">
                  <SelectTrigger class="w-20">
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

            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Name</TableHead>
                  <TableHead>Company</TableHead>
                  <TableHead>Status</TableHead>
                  <TableHead>Priority</TableHead>
                  <TableHead>Assigned To</TableHead>
                  <TableHead>Estimated Value</TableHead>
                  <TableHead>Next Follow-up</TableHead>
                  <TableHead class="w-[100px]">Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-if="loading">
                  <TableCell colspan="8" class="text-center">
                    <div class="flex items-center justify-center py-4">
                      <Loader2 class="h-6 w-6 animate-spin" />
                    </div>
                  </TableCell>
                </TableRow>
                <TableRow v-else-if="prospects.length === 0">
                  <TableCell colspan="8" class="text-center py-8">
                    <div class="text-muted-foreground">No prospects found</div>
                  </TableCell>
                </TableRow>
                <TableRow v-else v-for="prospect in prospects" :key="prospect.id">
                  <TableCell>
                    <div>
                      <div class="font-medium">{{ prospect.name }}</div>
                      <div class="text-sm text-muted-foreground">{{ prospect.email }}</div>
                    </div>
                  </TableCell>
                  <TableCell>{{ prospect.company_name }}</TableCell>
                  <TableCell>
                    <Badge :variant="getStatusVariant(prospect.status)">
                      {{ formatStatus(prospect.status) }}
                    </Badge>
                  </TableCell>
                  <TableCell>
                    <Badge :variant="getPriorityVariant(prospect.priority)">
                      {{ formatPriority(prospect.priority) }}
                    </Badge>
                  </TableCell>
                  <TableCell>{{ prospect.assigned_user?.name || '-' }}</TableCell>
                  <TableCell>{{ formatCurrency(prospect.estimated_value) }}</TableCell>
                  <TableCell>
                    {{ prospect.next_follow_up_date ? formatDate(prospect.next_follow_up_date) : '-' }}
                  </TableCell>
                  <TableCell>
                    <DropdownMenu>
                      <DropdownMenuTrigger as-child>
                        <Button variant="ghost" class="h-8 w-8 p-0">
                          <MoreHorizontal class="h-4 w-4" />
                        </Button>
                      </DropdownMenuTrigger>
                      <DropdownMenuContent align="end">
                        <DropdownMenuItem @click="viewProspect(prospect)">
                          <Eye class="mr-2 h-4 w-4" />
                          View
                        </DropdownMenuItem>
                        <DropdownMenuItem @click="editProspect(prospect)">
                          <Edit class="mr-2 h-4 w-4" />
                          Edit
                        </DropdownMenuItem>
                        <DropdownMenuItem @click="deleteProspect(prospect)" class="text-destructive">
                          <Trash2 class="mr-2 h-4 w-4" />
                          Delete
                        </DropdownMenuItem>
                      </DropdownMenuContent>
                    </DropdownMenu>
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>

            <!-- Pagination -->
            <div class="flex items-center justify-between">
              <div class="text-sm text-muted-foreground">
                Page {{ pagination.current_page }} of {{ pagination.last_page }}
              </div>
              <div class="flex items-center space-x-2">
                <Button variant="outline" size="sm" :disabled="pagination.current_page === 1"
                  @click="changePage(pagination.current_page - 1)">
                  Previous
                </Button>
                <Button variant="outline" size="sm" :disabled="pagination.current_page === pagination.last_page"
                  @click="changePage(pagination.current_page + 1)">
                  Next
                </Button>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Create/Edit Dialog -->
    <Dialog v-model:open="dialogOpen">
      <DialogContent class="max-w-2xl">
        <DialogHeader>
          <DialogTitle>{{ editingProspect ? 'Edit Prospect' : 'Add Prospect' }}</DialogTitle>
        </DialogHeader>
        <form @submit.prevent="saveProspect" class="space-y-4">
          <div class="grid gap-4 md:grid-cols-2">
            <div class="space-y-2">
              <Label for="name">Name *</Label>
              <Input id="name" v-model="form.name" required />
            </div>
            <div class="space-y-2">
              <Label for="email">Email</Label>
              <Input id="email" v-model="form.email" type="email" />
            </div>
            <div class="space-y-2">
              <Label for="phone">Phone</Label>
              <Input id="phone" v-model="form.phone" />
            </div>
            <div class="space-y-2">
              <Label for="company_name">Company Name</Label>
              <Input id="company_name" v-model="form.company_name" />
            </div>
            <div class="space-y-2">
              <Label for="position">Position</Label>
              <Input id="position" v-model="form.position" />
            </div>
            <div class="space-y-2">
              <Label for="industry">Industry</Label>
              <Input id="industry" v-model="form.industry" />
            </div>
            <div class="space-y-2">
              <Label for="source">Source</Label>
              <Select v-model="form.source">
                <SelectTrigger>
                  <SelectValue placeholder="Select source" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="website">Website</SelectItem>
                  <SelectItem value="referral">Referral</SelectItem>
                  <SelectItem value="cold_call">Cold Call</SelectItem>
                  <SelectItem value="social_media">Social Media</SelectItem>
                  <SelectItem value="email_campaign">Email Campaign</SelectItem>
                  <SelectItem value="other">Other</SelectItem>
                </SelectContent>
              </Select>
            </div>
            <div class="space-y-2">
              <Label for="status">Status *</Label>
              <Select v-model="form.status" required>
                <SelectTrigger>
                  <SelectValue placeholder="Select status" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="new">New</SelectItem>
                  <SelectItem value="contacted">Contacted</SelectItem>
                  <SelectItem value="qualified">Qualified</SelectItem>
                  <SelectItem value="proposal">Proposal</SelectItem>
                  <SelectItem value="negotiation">Negotiation</SelectItem>
                  <SelectItem value="won">Won</SelectItem>
                  <SelectItem value="lost">Lost</SelectItem>
                </SelectContent>
              </Select>
            </div>
            <div class="space-y-2">
              <Label for="priority">Priority *</Label>
              <Select v-model="form.priority" required>
                <SelectTrigger>
                  <SelectValue placeholder="Select priority" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="low">Low</SelectItem>
                  <SelectItem value="medium">Medium</SelectItem>
                  <SelectItem value="high">High</SelectItem>
                  <SelectItem value="urgent">Urgent</SelectItem>
                </SelectContent>
              </Select>
            </div>
            <div class="space-y-2">
              <Label for="estimated_value">Estimated Value</Label>
              <Input id="estimated_value" v-model="form.estimated_value" type="number" step="0.01" />
            </div>
            <div class="space-y-2">
              <Label for="assigned_to">Assigned To</Label>
              <Select v-model="form.assigned_to">
                <SelectTrigger>
                  <SelectValue placeholder="Select user" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem v-for="user in assignedUsers" :key="user.id" :value="user.id.toString()">
                    {{ user.name }}
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>
            <div class="space-y-2">
              <Label for="next_follow_up_date">Next Follow-up Date</Label>
              <Input id="next_follow_up_date" v-model="form.next_follow_up_date" type="datetime-local" />
            </div>
          </div>
          <div class="space-y-2">
            <Label for="notes">Notes</Label>
            <Textarea id="notes" v-model="form.notes" rows="3" />
          </div>
          <DialogFooter>
            <Button type="button" variant="outline" @click="closeDialog">
              Cancel
            </Button>
            <Button type="submit" :disabled="saving">
              <Loader2 v-if="saving" class="mr-2 h-4 w-4 animate-spin" />
              {{ editingProspect ? 'Update' : 'Create' }}
            </Button>
          </DialogFooter>
        </form>
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
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Badge } from '@/components/ui/badge'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import { Plus, Users, UserPlus, UserCheck, DollarSign, MoreHorizontal, Eye, Edit, Trash2, Loader2 } from 'lucide-vue-next'
import { useApi } from '@/composables/useApi'
import { debounce } from 'lodash'

// Types
interface Prospect {
  id: number
  name: string
  email: string
  phone: string
  company_name: string
  position: string
  industry: string
  source: string
  status: string
  priority: string
  estimated_value: number
  notes: string
  assigned_to: number | null
  assigned_user?: { id: number; name: string; email: string }
  next_follow_up_date: string
  last_contact_date: string
  created_at: string
  updated_at: string
}

interface User {
  id: number
  name: string
  email: string
}

interface Statistics {
  total_prospects: number
  new_prospects: number
  contacted_prospects: number
  qualified_prospects: number
  proposal_prospects: number
  negotiation_prospects: number
  won_prospects: number
  lost_prospects: number
  total_estimated_value: number
}

// Breadcrumbs
const breadcrumbs = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'CRM', href: '/crm/prospects' },
  { title: 'Prospects', href: '/crm/prospects' }
]

// State
const prospects = ref<Prospect[]>([])
const assignedUsers = ref<User[]>([])
const statistics = ref<Statistics>({} as Statistics)
const loading = ref(false)
const saving = ref(false)
const dialogOpen = ref(false)
const editingProspect = ref<Prospect | null>(null)

const filters = ref({
  search: '',
  status: '',
  priority: '',
  assigned_to: '',
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
  email: '',
  phone: '',
  company_name: '',
  position: '',
  industry: '',
  source: '',
  status: 'new',
  priority: 'medium',
  estimated_value: '',
  notes: '',
  assigned_to: '',
  next_follow_up_date: ''
})

// API
const api = useApi()

// Methods
const loadProspects = async () => {
  loading.value = true
  try {
    const params = new URLSearchParams({
      page: pagination.value.current_page.toString(),
      ...filters.value
    })

    const response = await api.get(`/api/crm/prospects?${params}`)
    prospects.value = response.data.data
    pagination.value = response.data.pagination
  } catch (error) {
    console.error('Error loading prospects:', error)
    window.toast?.error('Failed to load prospects')
  } finally {
    loading.value = false
  }
}

const loadStatistics = async () => {
  try {
    const response = await api.get('/api/crm/prospects/statistics')
    statistics.value = response.data.data
  } catch (error) {
    console.error('Error loading statistics:', error)
  }
}

const loadAssignedUsers = async () => {
  try {
    const response = await api.get('/api/crm/prospects/assigned-users')
    assignedUsers.value = response.data.data
  } catch (error) {
    console.error('Error loading assigned users:', error)
  }
}

const debouncedSearch = debounce(() => {
  pagination.value.current_page = 1
  loadProspects()
}, 300)

const changePage = (page: number) => {
  pagination.value.current_page = page
  loadProspects()
}

const openCreateDialog = () => {
  editingProspect.value = null
  resetForm()
  dialogOpen.value = true
}

const editProspect = (prospect: Prospect) => {
  editingProspect.value = prospect
  form.value = {
    name: prospect.name,
    email: prospect.email || '',
    phone: prospect.phone || '',
    company_name: prospect.company_name || '',
    position: prospect.position || '',
    industry: prospect.industry || '',
    source: prospect.source || '',
    status: prospect.status,
    priority: prospect.priority,
    estimated_value: prospect.estimated_value?.toString() || '',
    notes: prospect.notes || '',
    assigned_to: prospect.assigned_to?.toString() || '',
    next_follow_up_date: prospect.next_follow_up_date ? formatDateForInput(prospect.next_follow_up_date) : ''
  }
  dialogOpen.value = true
}

const saveProspect = async () => {
  saving.value = true
  try {
    const data = {
      ...form.value,
      estimated_value: form.value.estimated_value ? parseFloat(form.value.estimated_value) : null,
      assigned_to: form.value.assigned_to ? parseInt(form.value.assigned_to) : null
    }

    if (editingProspect.value) {
      await api.put(`/api/crm/prospects/${editingProspect.value.id}`, data)
      window.toast?.success('Prospect updated successfully')
    } else {
      await api.post('/api/crm/prospects', data)
      window.toast?.success('Prospect created successfully')
    }

    closeDialog()
    loadProspects()
    loadStatistics()
  } catch (error: any) {
    console.error('Error saving prospect:', error)
    const message = error.response?.data?.message || 'Failed to save prospect'
    window.toast?.error(message)
  } finally {
    saving.value = false
  }
}

const deleteProspect = async (prospect: Prospect) => {
  if (!confirm('Are you sure you want to delete this prospect?')) return

  try {
    await api.delete(`/api/crm/prospects/${prospect.id}`)
    window.toast?.success('Prospect deleted successfully')
    loadProspects()
    loadStatistics()
  } catch (error: any) {
    console.error('Error deleting prospect:', error)
    const message = error.response?.data?.message || 'Failed to delete prospect'
    window.toast?.error(message)
  }
}

const viewProspect = (prospect: Prospect) => {
  router.visit(`/crm/prospects/${prospect.id}`)
}

const closeDialog = () => {
  dialogOpen.value = false
  editingProspect.value = null
  resetForm()
}

const resetForm = () => {
  form.value = {
    name: '',
    email: '',
    phone: '',
    company_name: '',
    position: '',
    industry: '',
    source: '',
    status: 'new',
    priority: 'medium',
    estimated_value: '',
    notes: '',
    assigned_to: '',
    next_follow_up_date: ''
  }
}

// Utility functions
const formatStatus = (status: string) => {
  return status.charAt(0).toUpperCase() + status.slice(1)
}

const formatPriority = (priority: string) => {
  return priority.charAt(0).toUpperCase() + priority.slice(1)
}

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR'
  }).format(amount || 0)
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString()
}

const formatDateForInput = (date: string) => {
  return new Date(date).toISOString().slice(0, 16)
}

const getStatusVariant = (status: string) => {
  switch (status) {
    case 'new': return 'secondary'
    case 'contacted': return 'default'
    case 'qualified': return 'default'
    case 'proposal': return 'default'
    case 'negotiation': return 'default'
    case 'won': return 'default'
    case 'lost': return 'destructive'
    default: return 'secondary'
  }
}

const getPriorityVariant = (priority: string) => {
  switch (priority) {
    case 'low': return 'secondary'
    case 'medium': return 'default'
    case 'high': return 'default'
    case 'urgent': return 'destructive'
    default: return 'secondary'
  }
}

// Lifecycle
onMounted(() => {
  loadProspects()
  loadStatistics()
  loadAssignedUsers()
})
</script>
