<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold tracking-tight">Follow-ups</h1>
          <p class="text-muted-foreground">
            Manage customer and prospect follow-ups
          </p>
        </div>
        <Button @click="openCreateDialog">
          <Plus class="mr-2 h-4 w-4" />
          Add Follow-up
        </Button>
      </div>

      <!-- Statistics Cards -->
      <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Total Follow-ups</CardTitle>
            <Clock class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ statistics.total_follow_ups || 0 }}</div>
          </CardContent>
        </Card>
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Scheduled</CardTitle>
            <Calendar class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ statistics.scheduled_follow_ups || 0 }}</div>
          </CardContent>
        </Card>
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Completed</CardTitle>
            <CheckCircle class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ statistics.completed_follow_ups || 0 }}</div>
          </CardContent>
        </Card>
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Positive Outcomes</CardTitle>
            <TrendingUp class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ statistics.positive_outcomes || 0 }}</div>
          </CardContent>
        </Card>
      </div>

      <!-- Quick Actions -->
      <div class="grid gap-4 md:grid-cols-2">
        <Card>
          <CardHeader>
            <CardTitle>Upcoming Follow-ups</CardTitle>
          </CardHeader>
          <CardContent>
            <div v-if="upcomingFollowUps.length === 0" class="text-center py-4 text-muted-foreground">
              No upcoming follow-ups
            </div>
            <div v-else class="space-y-3">
              <div v-for="followUp in upcomingFollowUps.slice(0, 5)" :key="followUp.id"
                class="flex items-center justify-between p-3 border rounded-lg">
                <div>
                  <div class="font-medium">{{ followUp.subject }}</div>
                  <div class="text-sm text-muted-foreground">
                    {{ followUp.prospect?.name || followUp.customer?.name }} • {{ formatDate(followUp.scheduled_date) }}
                  </div>
                </div>
                <Button size="sm" @click="completeFollowUp(followUp)">Complete</Button>
              </div>
            </div>
          </CardContent>
        </Card>
        <Card>
          <CardHeader>
            <CardTitle>Overdue Follow-ups</CardTitle>
          </CardHeader>
          <CardContent>
            <div v-if="overdueFollowUps.length === 0" class="text-center py-4 text-muted-foreground">
              No overdue follow-ups
            </div>
            <div v-else class="space-y-3">
              <div v-for="followUp in overdueFollowUps.slice(0, 5)" :key="followUp.id"
                class="flex items-center justify-between p-3 border rounded-lg border-red-200">
                <div>
                  <div class="font-medium">{{ followUp.subject }}</div>
                  <div class="text-sm text-muted-foreground">
                    {{ followUp.prospect?.name || followUp.customer?.name }} • {{ formatDate(followUp.scheduled_date) }}
                  </div>
                </div>
                <Button size="sm" variant="destructive" @click="completeFollowUp(followUp)">Complete</Button>
              </div>
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
              <Input id="search" v-model="filters.search" placeholder="Search by subject..." @input="debouncedSearch" />
            </div>
            <div class="space-y-2">
              <Label for="status">Status</Label>
              <Select v-model="filters.status" @update:model-value="loadFollowUps">
                <SelectTrigger>
                  <SelectValue placeholder="All statuses" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="">All statuses</SelectItem>
                  <SelectItem value="scheduled">Scheduled</SelectItem>
                  <SelectItem value="in_progress">In Progress</SelectItem>
                  <SelectItem value="completed">Completed</SelectItem>
                  <SelectItem value="cancelled">Cancelled</SelectItem>
                  <SelectItem value="no_show">No Show</SelectItem>
                </SelectContent>
              </Select>
            </div>
            <div class="space-y-2">
              <Label for="type">Type</Label>
              <Select v-model="filters.type" @update:model-value="loadFollowUps">
                <SelectTrigger>
                  <SelectValue placeholder="All types" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="">All types</SelectItem>
                  <SelectItem value="call">Call</SelectItem>
                  <SelectItem value="email">Email</SelectItem>
                  <SelectItem value="meeting">Meeting</SelectItem>
                  <SelectItem value="presentation">Presentation</SelectItem>
                  <SelectItem value="demo">Demo</SelectItem>
                  <SelectItem value="proposal">Proposal</SelectItem>
                  <SelectItem value="other">Other</SelectItem>
                </SelectContent>
              </Select>
            </div>
            <div class="space-y-2">
              <Label for="assigned_to">Assigned To</Label>
              <Select v-model="filters.assigned_to" @update:model-value="loadFollowUps">
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

      <!-- Follow-ups Table -->
      <Card>
        <CardHeader>
          <CardTitle>Follow-ups</CardTitle>
        </CardHeader>
        <CardContent>
          <div class="space-y-4">
            <div class="flex items-center justify-between">
              <div class="text-sm text-muted-foreground">
                Showing {{ pagination.total }} follow-ups
              </div>
              <div class="flex items-center space-x-2">
                <Label for="per-page">Per page</Label>
                <Select v-model="filters.per_page" @update:model-value="loadFollowUps">
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
                  <TableHead>Subject</TableHead>
                  <TableHead>Type</TableHead>
                  <TableHead>Status</TableHead>
                  <TableHead>Contact</TableHead>
                  <TableHead>Assigned To</TableHead>
                  <TableHead>Scheduled Date</TableHead>
                  <TableHead>Outcome</TableHead>
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
                <TableRow v-else-if="followUps.length === 0">
                  <TableCell colspan="8" class="text-center py-8">
                    <div class="text-muted-foreground">No follow-ups found</div>
                  </TableCell>
                </TableRow>
                <TableRow v-else v-for="followUp in followUps" :key="followUp.id">
                  <TableCell>
                    <div>
                      <div class="font-medium">{{ followUp.subject }}</div>
                      <div class="text-sm text-muted-foreground">{{ followUp.description }}</div>
                    </div>
                  </TableCell>
                  <TableCell>
                    <Badge :variant="getTypeVariant(followUp.type)">
                      {{ formatType(followUp.type) }}
                    </Badge>
                  </TableCell>
                  <TableCell>
                    <Badge :variant="getStatusVariant(followUp.status)">
                      {{ formatStatus(followUp.status) }}
                    </Badge>
                  </TableCell>
                  <TableCell>
                    {{ followUp.prospect?.name || followUp.customer?.name }}
                  </TableCell>
                  <TableCell>{{ followUp.assigned_user?.name || '-' }}</TableCell>
                  <TableCell>
                    {{ followUp.scheduled_date ? formatDate(followUp.scheduled_date) : '-' }}
                  </TableCell>
                  <TableCell>
                    <Badge v-if="followUp.outcome" :variant="getOutcomeVariant(followUp.outcome)">
                      {{ formatOutcome(followUp.outcome) }}
                    </Badge>
                    <span v-else>-</span>
                  </TableCell>
                  <TableCell>
                    <DropdownMenu>
                      <DropdownMenuTrigger as-child>
                        <Button variant="ghost" class="h-8 w-8 p-0">
                          <MoreHorizontal class="h-4 w-4" />
                        </Button>
                      </DropdownMenuTrigger>
                      <DropdownMenuContent align="end">
                        <DropdownMenuItem @click="viewFollowUp(followUp)">
                          <Eye class="mr-2 h-4 w-4" />
                          View
                        </DropdownMenuItem>
                        <DropdownMenuItem @click="editFollowUp(followUp)">
                          <Edit class="mr-2 h-4 w-4" />
                          Edit
                        </DropdownMenuItem>
                        <DropdownMenuItem v-if="followUp.status !== 'completed'" @click="completeFollowUp(followUp)">
                          <CheckCircle class="mr-2 h-4 w-4" />
                          Complete
                        </DropdownMenuItem>
                        <DropdownMenuItem @click="deleteFollowUp(followUp)" class="text-destructive">
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
          <DialogTitle>{{ editingFollowUp ? 'Edit Follow-up' : 'Add Follow-up' }}</DialogTitle>
        </DialogHeader>
        <form @submit.prevent="saveFollowUp" class="space-y-4">
          <div class="grid gap-4 md:grid-cols-2">
            <div class="space-y-2">
              <Label for="subject">Subject *</Label>
              <Input id="subject" v-model="form.subject" required />
            </div>
            <div class="space-y-2">
              <Label for="type">Type *</Label>
              <Select v-model="form.type" required>
                <SelectTrigger>
                  <SelectValue placeholder="Select type" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="call">Call</SelectItem>
                  <SelectItem value="email">Email</SelectItem>
                  <SelectItem value="meeting">Meeting</SelectItem>
                  <SelectItem value="presentation">Presentation</SelectItem>
                  <SelectItem value="demo">Demo</SelectItem>
                  <SelectItem value="proposal">Proposal</SelectItem>
                  <SelectItem value="other">Other</SelectItem>
                </SelectContent>
              </Select>
            </div>
            <div class="space-y-2">
              <Label for="method">Method *</Label>
              <Select v-model="form.method" required>
                <SelectTrigger>
                  <SelectValue placeholder="Select method" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="phone">Phone</SelectItem>
                  <SelectItem value="email">Email</SelectItem>
                  <SelectItem value="in_person">In Person</SelectItem>
                  <SelectItem value="video_call">Video Call</SelectItem>
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
                  <SelectItem value="scheduled">Scheduled</SelectItem>
                  <SelectItem value="in_progress">In Progress</SelectItem>
                  <SelectItem value="completed">Completed</SelectItem>
                  <SelectItem value="cancelled">Cancelled</SelectItem>
                  <SelectItem value="no_show">No Show</SelectItem>
                </SelectContent>
              </Select>
            </div>
            <div class="space-y-2">
              <Label for="prospect_id">Prospect</Label>
              <Select v-model="form.prospect_id">
                <SelectTrigger>
                  <SelectValue placeholder="Select prospect" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="">No prospect</SelectItem>
                  <SelectItem v-for="prospect in prospects" :key="prospect.id" :value="prospect.id.toString()">
                    {{ prospect.name }}
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>
            <div class="space-y-2">
              <Label for="customer_id">Customer</Label>
              <Select v-model="form.customer_id">
                <SelectTrigger>
                  <SelectValue placeholder="Select customer" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="">No customer</SelectItem>
                  <SelectItem v-for="customer in customers" :key="customer.id" :value="customer.id.toString()">
                    {{ customer.name }}
                  </SelectItem>
                </SelectContent>
              </Select>
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
              <Label for="scheduled_date">Scheduled Date</Label>
              <Input id="scheduled_date" v-model="form.scheduled_date" type="datetime-local" />
            </div>
          </div>
          <div class="space-y-2">
            <Label for="description">Description</Label>
            <Textarea id="description" v-model="form.description" rows="3" />
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
              {{ editingFollowUp ? 'Update' : 'Create' }}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>

    <!-- Complete Follow-up Dialog -->
    <Dialog v-model:open="completeDialogOpen">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Complete Follow-up</DialogTitle>
        </DialogHeader>
        <form @submit.prevent="saveCompletion" class="space-y-4">
          <div class="space-y-2">
            <Label for="outcome">Outcome *</Label>
            <Select v-model="completionForm.outcome" required>
              <SelectTrigger>
                <SelectValue placeholder="Select outcome" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="positive">Positive</SelectItem>
                <SelectItem value="neutral">Neutral</SelectItem>
                <SelectItem value="negative">Negative</SelectItem>
                <SelectItem value="no_response">No Response</SelectItem>
              </SelectContent>
            </Select>
          </div>
          <div class="space-y-2">
            <Label for="next_action">Next Action</Label>
            <Input id="next_action" v-model="completionForm.next_action" />
          </div>
          <div class="space-y-2">
            <Label for="next_follow_up_date">Next Follow-up Date</Label>
            <Input id="next_follow_up_date" v-model="completionForm.next_follow_up_date" type="datetime-local" />
          </div>
          <div class="space-y-2">
            <Label for="completion_notes">Notes</Label>
            <Textarea id="completion_notes" v-model="completionForm.notes" rows="3" />
          </div>
          <DialogFooter>
            <Button type="button" variant="outline" @click="closeCompleteDialog">
              Cancel
            </Button>
            <Button type="submit" :disabled="completing">
              <Loader2 v-if="completing" class="mr-2 h-4 w-4 animate-spin" />
              Complete
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
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
import { Plus, Clock, Calendar, CheckCircle, TrendingUp, MoreHorizontal, Eye, Edit, Trash2, Loader2 } from 'lucide-vue-next'
import { useApi } from '@/composables/useApi'
import { debounce } from 'lodash'

// Types
interface FollowUp {
  id: number
  subject: string
  description: string
  type: string
  method: string
  status: string
  outcome: string
  scheduled_date: string
  completed_date: string
  next_follow_up_date: string
  notes: string
  next_action: string
  prospect_id: number | null
  customer_id: number | null
  assigned_to: number | null
  prospect?: { id: number; name: string }
  customer?: { id: number; name: string }
  assigned_user?: { id: number; name: string; email: string }
  created_at: string
  updated_at: string
}

interface User {
  id: number
  name: string
  email: string
}

interface Prospect {
  id: number
  name: string
  email: string
}

interface Customer {
  id: number
  name: string
  email: string
}

interface Statistics {
  total_follow_ups: number
  scheduled_follow_ups: number
  in_progress_follow_ups: number
  completed_follow_ups: number
  cancelled_follow_ups: number
  positive_outcomes: number
  neutral_outcomes: number
  negative_outcomes: number
}

// Breadcrumbs
const breadcrumbs = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'CRM', href: '/crm/follow-ups' },
  { title: 'Follow-ups', href: '/crm/follow-ups' }
]

// State
const followUps = ref<FollowUp[]>([])
const upcomingFollowUps = ref<FollowUp[]>([])
const overdueFollowUps = ref<FollowUp[]>([])
const assignedUsers = ref<User[]>([])
const prospects = ref<Prospect[]>([])
const customers = ref<Customer[]>([])
const statistics = ref<Statistics>({} as Statistics)
const loading = ref(false)
const saving = ref(false)
const completing = ref(false)
const dialogOpen = ref(false)
const completeDialogOpen = ref(false)
const editingFollowUp = ref<FollowUp | null>(null)
const completingFollowUp = ref<FollowUp | null>(null)

const filters = ref({
  search: '',
  status: '',
  type: '',
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
  subject: '',
  description: '',
  type: 'call',
  method: 'phone',
  status: 'scheduled',
  prospect_id: '',
  customer_id: '',
  assigned_to: '',
  scheduled_date: '',
  notes: ''
})

const completionForm = ref({
  outcome: '',
  next_action: '',
  next_follow_up_date: '',
  notes: ''
})

// API
const api = useApi()

// Methods
const loadFollowUps = async () => {
  loading.value = true
  try {
    const params = new URLSearchParams({
      page: pagination.value.current_page.toString(),
      ...filters.value
    })

    const response = await api.get(`/api/crm/follow-ups?${params}`)
    followUps.value = response.data.data
    pagination.value = response.data.pagination
  } catch (error) {
    console.error('Error loading follow-ups:', error)
    window.toast?.error('Failed to load follow-ups')
  } finally {
    loading.value = false
  }
}

const loadStatistics = async () => {
  try {
    const response = await api.get('/api/crm/follow-ups/statistics')
    statistics.value = response.data.data
  } catch (error) {
    console.error('Error loading statistics:', error)
  }
}

const loadUpcoming = async () => {
  try {
    const response = await api.get('/api/crm/follow-ups/upcoming')
    upcomingFollowUps.value = response.data.data
  } catch (error) {
    console.error('Error loading upcoming follow-ups:', error)
  }
}

const loadOverdue = async () => {
  try {
    const response = await api.get('/api/crm/follow-ups/overdue')
    overdueFollowUps.value = response.data.data
  } catch (error) {
    console.error('Error loading overdue follow-ups:', error)
  }
}

const loadAssignedUsers = async () => {
  try {
    const response = await api.get('/api/crm/follow-ups/assigned-users')
    assignedUsers.value = response.data.data
  } catch (error) {
    console.error('Error loading assigned users:', error)
  }
}

const loadProspects = async () => {
  try {
    const response = await api.get('/api/crm/prospects')
    prospects.value = response.data.data
  } catch (error) {
    console.error('Error loading prospects:', error)
  }
}

const loadCustomers = async () => {
  try {
    const response = await api.get('/api/customers')
    customers.value = response.data.data
  } catch (error) {
    console.error('Error loading customers:', error)
  }
}

const debouncedSearch = debounce(() => {
  pagination.value.current_page = 1
  loadFollowUps()
}, 300)

const changePage = (page: number) => {
  pagination.value.current_page = page
  loadFollowUps()
}

const openCreateDialog = () => {
  editingFollowUp.value = null
  resetForm()
  dialogOpen.value = true
}

const editFollowUp = (followUp: FollowUp) => {
  editingFollowUp.value = followUp
  form.value = {
    subject: followUp.subject,
    description: followUp.description || '',
    type: followUp.type,
    method: followUp.method,
    status: followUp.status,
    prospect_id: followUp.prospect_id?.toString() || '',
    customer_id: followUp.customer_id?.toString() || '',
    assigned_to: followUp.assigned_to?.toString() || '',
    scheduled_date: followUp.scheduled_date ? formatDateForInput(followUp.scheduled_date) : '',
    notes: followUp.notes || ''
  }
  dialogOpen.value = true
}

const saveFollowUp = async () => {
  saving.value = true
  try {
    const data = {
      ...form.value,
      prospect_id: form.value.prospect_id ? parseInt(form.value.prospect_id) : null,
      customer_id: form.value.customer_id ? parseInt(form.value.customer_id) : null,
      assigned_to: form.value.assigned_to ? parseInt(form.value.assigned_to) : null
    }

    if (editingFollowUp.value) {
      await api.put(`/api/crm/follow-ups/${editingFollowUp.value.id}`, data)
      window.toast?.success('Follow-up updated successfully')
    } else {
      await api.post('/api/crm/follow-ups', data)
      window.toast?.success('Follow-up created successfully')
    }

    closeDialog()
    loadFollowUps()
    loadStatistics()
    loadUpcoming()
    loadOverdue()
  } catch (error: any) {
    console.error('Error saving follow-up:', error)
    const message = error.response?.data?.message || 'Failed to save follow-up'
    window.toast?.error(message)
  } finally {
    saving.value = false
  }
}

const completeFollowUp = (followUp: FollowUp) => {
  completingFollowUp.value = followUp
  completionForm.value = {
    outcome: '',
    next_action: '',
    next_follow_up_date: '',
    notes: ''
  }
  completeDialogOpen.value = true
}

const saveCompletion = async () => {
  if (!completingFollowUp.value) return

  completing.value = true
  try {
    const data = {
      ...completionForm.value,
      next_follow_up_date: completionForm.value.next_follow_up_date || null
    }

    await api.post(`/api/crm/follow-ups/${completingFollowUp.value.id}/complete`, data)
    window.toast?.success('Follow-up completed successfully')

    closeCompleteDialog()
    loadFollowUps()
    loadStatistics()
    loadUpcoming()
    loadOverdue()
  } catch (error: any) {
    console.error('Error completing follow-up:', error)
    const message = error.response?.data?.message || 'Failed to complete follow-up'
    window.toast?.error(message)
  } finally {
    completing.value = false
  }
}

const deleteFollowUp = async (followUp: FollowUp) => {
  if (!confirm('Are you sure you want to delete this follow-up?')) return

  try {
    await api.delete(`/api/crm/follow-ups/${followUp.id}`)
    window.toast?.success('Follow-up deleted successfully')
    loadFollowUps()
    loadStatistics()
    loadUpcoming()
    loadOverdue()
  } catch (error: any) {
    console.error('Error deleting follow-up:', error)
    const message = error.response?.data?.message || 'Failed to delete follow-up'
    window.toast?.error(message)
  }
}

const viewFollowUp = (followUp: FollowUp) => {
  router.visit(`/crm/follow-ups/${followUp.id}`)
}

const closeDialog = () => {
  dialogOpen.value = false
  editingFollowUp.value = null
  resetForm()
}

const closeCompleteDialog = () => {
  completeDialogOpen.value = false
  completingFollowUp.value = null
  resetCompletionForm()
}

const resetForm = () => {
  form.value = {
    subject: '',
    description: '',
    type: 'call',
    method: 'phone',
    status: 'scheduled',
    prospect_id: '',
    customer_id: '',
    assigned_to: '',
    scheduled_date: '',
    notes: ''
  }
}

const resetCompletionForm = () => {
  completionForm.value = {
    outcome: '',
    next_action: '',
    next_follow_up_date: '',
    notes: ''
  }
}

// Utility functions
const formatStatus = (status: string) => {
  return status.charAt(0).toUpperCase() + status.slice(1).replace('_', ' ')
}

const formatType = (type: string) => {
  return type.charAt(0).toUpperCase() + type.slice(1)
}

const formatOutcome = (outcome: string) => {
  return outcome.charAt(0).toUpperCase() + outcome.slice(1).replace('_', ' ')
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString()
}

const formatDateForInput = (date: string) => {
  return new Date(date).toISOString().slice(0, 16)
}

const getStatusVariant = (status: string) => {
  switch (status) {
    case 'scheduled': return 'secondary'
    case 'in_progress': return 'default'
    case 'completed': return 'default'
    case 'cancelled': return 'destructive'
    case 'no_show': return 'destructive'
    default: return 'secondary'
  }
}

const getTypeVariant = (type: string) => {
  switch (type) {
    case 'call': return 'secondary'
    case 'email': return 'default'
    case 'meeting': return 'default'
    case 'presentation': return 'default'
    case 'demo': return 'default'
    case 'proposal': return 'default'
    case 'other': return 'secondary'
    default: return 'secondary'
  }
}

const getOutcomeVariant = (outcome: string) => {
  switch (outcome) {
    case 'positive': return 'default'
    case 'neutral': return 'secondary'
    case 'negative': return 'destructive'
    case 'no_response': return 'secondary'
    default: return 'secondary'
  }
}

// Lifecycle
onMounted(() => {
  loadFollowUps()
  loadStatistics()
  loadUpcoming()
  loadOverdue()
  loadAssignedUsers()
  loadProspects()
  loadCustomers()
})
</script>
