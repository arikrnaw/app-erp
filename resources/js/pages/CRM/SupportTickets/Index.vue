<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold tracking-tight">Support Tickets</h1>
          <p class="text-muted-foreground">
            Manage customer support tickets and inquiries
          </p>
        </div>
        <Button @click="openCreateDialog">
          <Plus class="mr-2 h-4 w-4" />
          Create Ticket
        </Button>
      </div>

      <!-- Statistics Cards -->
      <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Total Tickets</CardTitle>
            <MessageSquare class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ statistics.total_tickets || 0 }}</div>
          </CardContent>
        </Card>
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Open Tickets</CardTitle>
            <AlertCircle class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ statistics.open_tickets || 0 }}</div>
          </CardContent>
        </Card>
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Urgent Tickets</CardTitle>
            <AlertTriangle class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ statistics.urgent_tickets || 0 }}</div>
          </CardContent>
        </Card>
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Avg Satisfaction</CardTitle>
            <Star class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ formatRating(statistics.avg_satisfaction) }}</div>
          </CardContent>
        </Card>
      </div>

      <!-- Quick Actions -->
      <div class="grid gap-4 md:grid-cols-2">
        <Card>
          <CardHeader>
            <CardTitle>Open Tickets</CardTitle>
          </CardHeader>
          <CardContent>
            <div v-if="openTickets.length === 0" class="text-center py-4 text-muted-foreground">
              No open tickets
            </div>
            <div v-else class="space-y-3">
              <div v-for="ticket in openTickets.slice(0, 5)" :key="ticket.id"
                class="flex items-center justify-between p-3 border rounded-lg">
                <div>
                  <div class="font-medium">{{ ticket.subject }}</div>
                  <div class="text-sm text-muted-foreground">
                    {{ ticket.customer?.name }} • {{ formatDate(ticket.created_at) }}
                  </div>
                </div>
                <Badge :variant="getPriorityVariant(ticket.priority)">
                  {{ formatPriority(ticket.priority) }}
                </Badge>
              </div>
            </div>
          </CardContent>
        </Card>
        <Card>
          <CardHeader>
            <CardTitle>Urgent Tickets</CardTitle>
          </CardHeader>
          <CardContent>
            <div v-if="urgentTickets.length === 0" class="text-center py-4 text-muted-foreground">
              No urgent tickets
            </div>
            <div v-else class="space-y-3">
              <div v-for="ticket in urgentTickets.slice(0, 5)" :key="ticket.id"
                class="flex items-center justify-between p-3 border rounded-lg border-red-200">
                <div>
                  <div class="font-medium">{{ ticket.subject }}</div>
                  <div class="text-sm text-muted-foreground">
                    {{ ticket.customer?.name }} • {{ formatDate(ticket.created_at) }}
                  </div>
                </div>
                <Button size="sm" variant="destructive" @click="resolveTicket(ticket)">Resolve</Button>
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
              <Input id="search" v-model="filters.search" placeholder="Search by ticket number, subject, or customer..."
                @input="debouncedSearch" />
            </div>
            <div class="space-y-2">
              <Label for="status">Status</Label>
              <Select v-model="filters.status" @update:model-value="loadTickets">
                <SelectTrigger>
                  <SelectValue placeholder="All statuses" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="">All statuses</SelectItem>
                  <SelectItem value="open">Open</SelectItem>
                  <SelectItem value="in_progress">In Progress</SelectItem>
                  <SelectItem value="waiting_for_customer">Waiting for Customer</SelectItem>
                  <SelectItem value="resolved">Resolved</SelectItem>
                  <SelectItem value="closed">Closed</SelectItem>
                </SelectContent>
              </Select>
            </div>
            <div class="space-y-2">
              <Label for="priority">Priority</Label>
              <Select v-model="filters.priority" @update:model-value="loadTickets">
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
              <Select v-model="filters.assigned_to" @update:model-value="loadTickets">
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

      <!-- Tickets Table -->
      <Card>
        <CardHeader>
          <CardTitle>Support Tickets</CardTitle>
        </CardHeader>
        <CardContent>
          <div class="space-y-4">
            <div class="flex items-center justify-between">
              <div class="text-sm text-muted-foreground">
                Showing {{ pagination.total }} tickets
              </div>
              <div class="flex items-center space-x-2">
                <Label for="per-page">Per page</Label>
                <Select v-model="filters.per_page" @update:model-value="loadTickets">
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
                  <TableHead>Ticket #</TableHead>
                  <TableHead>Subject</TableHead>
                  <TableHead>Customer</TableHead>
                  <TableHead>Status</TableHead>
                  <TableHead>Priority</TableHead>
                  <TableHead>Assigned To</TableHead>
                  <TableHead>Created</TableHead>
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
                <TableRow v-else-if="tickets.length === 0">
                  <TableCell colspan="8" class="text-center py-8">
                    <div class="text-muted-foreground">No tickets found</div>
                  </TableCell>
                </TableRow>
                <TableRow v-else v-for="ticket in tickets" :key="ticket.id">
                  <TableCell>
                    <div class="font-mono text-sm">{{ ticket.ticket_number }}</div>
                  </TableCell>
                  <TableCell>
                    <div>
                      <div class="font-medium">{{ ticket.subject }}</div>
                      <div class="text-sm text-muted-foreground">{{ ticket.category }}</div>
                    </div>
                  </TableCell>
                  <TableCell>{{ ticket.customer?.name }}</TableCell>
                  <TableCell>
                    <Badge :variant="getStatusVariant(ticket.status)">
                      {{ formatStatus(ticket.status) }}
                    </Badge>
                  </TableCell>
                  <TableCell>
                    <Badge :variant="getPriorityVariant(ticket.priority)">
                      {{ formatPriority(ticket.priority) }}
                    </Badge>
                  </TableCell>
                  <TableCell>{{ ticket.assigned_user?.name || '-' }}</TableCell>
                  <TableCell>{{ formatDate(ticket.created_at) }}</TableCell>
                  <TableCell>
                    <DropdownMenu>
                      <DropdownMenuTrigger as-child>
                        <Button variant="ghost" class="h-8 w-8 p-0">
                          <MoreHorizontal class="h-4 w-4" />
                        </Button>
                      </DropdownMenuTrigger>
                      <DropdownMenuContent align="end">
                        <DropdownMenuItem @click="viewTicket(ticket)">
                          <Eye class="mr-2 h-4 w-4" />
                          View
                        </DropdownMenuItem>
                        <DropdownMenuItem @click="editTicket(ticket)">
                          <Edit class="mr-2 h-4 w-4" />
                          Edit
                        </DropdownMenuItem>
                        <DropdownMenuItem v-if="ticket.status !== 'resolved' && ticket.status !== 'closed'"
                          @click="resolveTicket(ticket)">
                          <CheckCircle class="mr-2 h-4 w-4" />
                          Resolve
                        </DropdownMenuItem>
                        <DropdownMenuItem v-if="ticket.status === 'resolved'" @click="closeTicket(ticket)">
                          <X class="mr-2 h-4 w-4" />
                          Close
                        </DropdownMenuItem>
                        <DropdownMenuItem @click="deleteTicket(ticket)" class="text-destructive">
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
          <DialogTitle>{{ editingTicket ? 'Edit Ticket' : 'Create Ticket' }}</DialogTitle>
        </DialogHeader>
        <form @submit.prevent="saveTicket" class="space-y-4">
          <div class="grid gap-4 md:grid-cols-2">
            <div class="space-y-2">
              <Label for="customer_id">Customer *</Label>
              <Select v-model="form.customer_id" required>
                <SelectTrigger>
                  <SelectValue placeholder="Select customer" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem v-for="customer in customers" :key="customer.id" :value="customer.id.toString()">
                    {{ customer.name }}
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>
            <div class="space-y-2">
              <Label for="category">Category</Label>
              <Input id="category" v-model="form.category" placeholder="e.g., Technical, Billing, General" />
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
              <Label for="status">Status *</Label>
              <Select v-model="form.status" required>
                <SelectTrigger>
                  <SelectValue placeholder="Select status" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="open">Open</SelectItem>
                  <SelectItem value="in_progress">In Progress</SelectItem>
                  <SelectItem value="waiting_for_customer">Waiting for Customer</SelectItem>
                  <SelectItem value="resolved">Resolved</SelectItem>
                  <SelectItem value="closed">Closed</SelectItem>
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
              <Label for="due_date">Due Date</Label>
              <Input id="due_date" v-model="form.due_date" type="datetime-local" />
            </div>
          </div>
          <div class="space-y-2">
            <Label for="subject">Subject *</Label>
            <Input id="subject" v-model="form.subject" required />
          </div>
          <div class="space-y-2">
            <Label for="description">Description *</Label>
            <Textarea id="description" v-model="form.description" rows="4" required />
          </div>
          <div class="space-y-2">
            <Label for="internal_notes">Internal Notes</Label>
            <Textarea id="internal_notes" v-model="form.internal_notes" rows="3" />
          </div>
          <DialogFooter>
            <Button type="button" variant="outline" @click="closeDialog">
              Cancel
            </Button>
            <Button type="submit" :disabled="saving">
              <Loader2 v-if="saving" class="mr-2 h-4 w-4 animate-spin" />
              {{ editingTicket ? 'Update' : 'Create' }}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>

    <!-- Resolve Ticket Dialog -->
    <Dialog v-model:open="resolveDialogOpen">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Resolve Ticket</DialogTitle>
        </DialogHeader>
        <form @submit.prevent="saveResolution" class="space-y-4">
          <div class="space-y-2">
            <Label for="customer_satisfaction_rating">Customer Satisfaction Rating</Label>
            <Select v-model="resolveForm.customer_satisfaction_rating">
              <SelectTrigger>
                <SelectValue placeholder="Select rating" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="1">1 - Very Dissatisfied</SelectItem>
                <SelectItem value="2">2 - Dissatisfied</SelectItem>
                <SelectItem value="3">3 - Neutral</SelectItem>
                <SelectItem value="4">4 - Satisfied</SelectItem>
                <SelectItem value="5">5 - Very Satisfied</SelectItem>
              </SelectContent>
            </Select>
          </div>
          <div class="space-y-2">
            <Label for="resolve_notes">Resolution Notes</Label>
            <Textarea id="resolve_notes" v-model="resolveForm.internal_notes" rows="3" />
          </div>
          <DialogFooter>
            <Button type="button" variant="outline" @click="closeResolveDialog">
              Cancel
            </Button>
            <Button type="submit" :disabled="resolving">
              <Loader2 v-if="resolving" class="mr-2 h-4 w-4 animate-spin" />
              Resolve
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
import { Plus, MessageSquare, AlertCircle, AlertTriangle, Star, MoreHorizontal, Eye, Edit, Trash2, Loader2, CheckCircle, X } from 'lucide-vue-next'
import { useApi } from '@/composables/useApi'
import { debounce } from 'lodash'

// Types
interface SupportTicket {
  id: number
  ticket_number: string
  subject: string
  description: string
  priority: string
  status: string
  category: string
  customer_id: number
  assigned_to: number | null
  due_date: string
  resolved_at: string
  customer_satisfaction_rating: number
  internal_notes: string
  customer?: { id: number; name: string; email: string }
  assigned_user?: { id: number; name: string; email: string }
  created_by_user?: { id: number; name: string; email: string }
  resolved_by_user?: { id: number; name: string; email: string }
  created_at: string
  updated_at: string
}

interface User {
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
  total_tickets: number
  open_tickets: number
  in_progress_tickets: number
  waiting_tickets: number
  resolved_tickets: number
  closed_tickets: number
  urgent_tickets: number
  high_priority_tickets: number
  avg_satisfaction: number
  avg_resolution_time: number
}

// Breadcrumbs
const breadcrumbs = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'CRM', href: '/crm/support-tickets' },
  { title: 'Support Tickets', href: '/crm/support-tickets' }
]

// State
const tickets = ref<SupportTicket[]>([])
const openTickets = ref<SupportTicket[]>([])
const urgentTickets = ref<SupportTicket[]>([])
const assignedUsers = ref<User[]>([])
const customers = ref<Customer[]>([])
const statistics = ref<Statistics>({} as Statistics)
const loading = ref(false)
const saving = ref(false)
const resolving = ref(false)
const dialogOpen = ref(false)
const resolveDialogOpen = ref(false)
const editingTicket = ref<SupportTicket | null>(null)
const resolvingTicket = ref<SupportTicket | null>(null)

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
  customer_id: '',
  subject: '',
  description: '',
  priority: 'medium',
  status: 'open',
  category: '',
  assigned_to: '',
  due_date: '',
  internal_notes: ''
})

const resolveForm = ref({
  customer_satisfaction_rating: '',
  internal_notes: ''
})

// API
const api = useApi()

// Methods
const loadTickets = async () => {
  loading.value = true
  try {
    const params = new URLSearchParams({
      page: pagination.value.current_page.toString(),
      ...filters.value
    })

    const response = await api.get(`/api/crm/support-tickets?${params}`)
    tickets.value = response.data.data
    pagination.value = response.data.pagination
  } catch (error) {
    console.error('Error loading tickets:', error)
    window.toast?.error('Failed to load tickets')
  } finally {
    loading.value = false
  }
}

const loadStatistics = async () => {
  try {
    const response = await api.get('/api/crm/support-tickets/statistics')
    statistics.value = response.data.data
  } catch (error) {
    console.error('Error loading statistics:', error)
  }
}

const loadOpenTickets = async () => {
  try {
    const response = await api.get('/api/crm/support-tickets/open')
    openTickets.value = response.data.data
  } catch (error) {
    console.error('Error loading open tickets:', error)
  }
}

const loadUrgentTickets = async () => {
  try {
    const response = await api.get('/api/crm/support-tickets/urgent')
    urgentTickets.value = response.data.data
  } catch (error) {
    console.error('Error loading urgent tickets:', error)
  }
}

const loadAssignedUsers = async () => {
  try {
    const response = await api.get('/api/crm/support-tickets/assigned-users')
    assignedUsers.value = response.data.data
  } catch (error) {
    console.error('Error loading assigned users:', error)
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
  loadTickets()
}, 300)

const changePage = (page: number) => {
  pagination.value.current_page = page
  loadTickets()
}

const openCreateDialog = () => {
  editingTicket.value = null
  resetForm()
  dialogOpen.value = true
}

const editTicket = (ticket: SupportTicket) => {
  editingTicket.value = ticket
  form.value = {
    customer_id: ticket.customer_id.toString(),
    subject: ticket.subject,
    description: ticket.description,
    priority: ticket.priority,
    status: ticket.status,
    category: ticket.category || '',
    assigned_to: ticket.assigned_to?.toString() || '',
    due_date: ticket.due_date ? formatDateForInput(ticket.due_date) : '',
    internal_notes: ticket.internal_notes || ''
  }
  dialogOpen.value = true
}

const saveTicket = async () => {
  saving.value = true
  try {
    const data = {
      ...form.value,
      customer_id: parseInt(form.value.customer_id),
      assigned_to: form.value.assigned_to ? parseInt(form.value.assigned_to) : null
    }

    if (editingTicket.value) {
      await api.put(`/api/crm/support-tickets/${editingTicket.value.id}`, data)
      window.toast?.success('Ticket updated successfully')
    } else {
      await api.post('/api/crm/support-tickets', data)
      window.toast?.success('Ticket created successfully')
    }

    closeDialog()
    loadTickets()
    loadStatistics()
    loadOpenTickets()
    loadUrgentTickets()
  } catch (error: any) {
    console.error('Error saving ticket:', error)
    const message = error.response?.data?.message || 'Failed to save ticket'
    window.toast?.error(message)
  } finally {
    saving.value = false
  }
}

const resolveTicket = (ticket: SupportTicket) => {
  resolvingTicket.value = ticket
  resolveForm.value = {
    customer_satisfaction_rating: '',
    internal_notes: ''
  }
  resolveDialogOpen.value = true
}

const saveResolution = async () => {
  if (!resolvingTicket.value) return

  resolving.value = true
  try {
    const data = {
      ...resolveForm.value,
      customer_satisfaction_rating: resolveForm.value.customer_satisfaction_rating ? parseInt(resolveForm.value.customer_satisfaction_rating) : null
    }

    await api.post(`/api/crm/support-tickets/${resolvingTicket.value.id}/resolve`, data)
    window.toast?.success('Ticket resolved successfully')

    closeResolveDialog()
    loadTickets()
    loadStatistics()
    loadOpenTickets()
    loadUrgentTickets()
  } catch (error: any) {
    console.error('Error resolving ticket:', error)
    const message = error.response?.data?.message || 'Failed to resolve ticket'
    window.toast?.error(message)
  } finally {
    resolving.value = false
  }
}

const closeTicket = async (ticket: SupportTicket) => {
  try {
    await api.post(`/api/crm/support-tickets/${ticket.id}/close`)
    window.toast?.success('Ticket closed successfully')
    loadTickets()
    loadStatistics()
    loadOpenTickets()
    loadUrgentTickets()
  } catch (error: any) {
    console.error('Error closing ticket:', error)
    const message = error.response?.data?.message || 'Failed to close ticket'
    window.toast?.error(message)
  }
}

const deleteTicket = async (ticket: SupportTicket) => {
  if (!confirm('Are you sure you want to delete this ticket?')) return

  try {
    await api.delete(`/api/crm/support-tickets/${ticket.id}`)
    window.toast?.success('Ticket deleted successfully')
    loadTickets()
    loadStatistics()
    loadOpenTickets()
    loadUrgentTickets()
  } catch (error: any) {
    console.error('Error deleting ticket:', error)
    const message = error.response?.data?.message || 'Failed to delete ticket'
    window.toast?.error(message)
  }
}

const viewTicket = (ticket: SupportTicket) => {
  router.visit(`/crm/support-tickets/${ticket.id}`)
}

const closeDialog = () => {
  dialogOpen.value = false
  editingTicket.value = null
  resetForm()
}

const closeResolveDialog = () => {
  resolveDialogOpen.value = false
  resolvingTicket.value = null
  resetResolveForm()
}

const resetForm = () => {
  form.value = {
    customer_id: '',
    subject: '',
    description: '',
    priority: 'medium',
    status: 'open',
    category: '',
    assigned_to: '',
    due_date: '',
    internal_notes: ''
  }
}

const resetResolveForm = () => {
  resolveForm.value = {
    customer_satisfaction_rating: '',
    internal_notes: ''
  }
}

// Utility functions
const formatStatus = (status: string) => {
  return status.charAt(0).toUpperCase() + status.slice(1).replace('_', ' ')
}

const formatPriority = (priority: string) => {
  return priority.charAt(0).toUpperCase() + priority.slice(1)
}

const formatRating = (rating: number) => {
  if (!rating) return 'N/A'
  return `${rating.toFixed(1)}/5`
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString()
}

const formatDateForInput = (date: string) => {
  return new Date(date).toISOString().slice(0, 16)
}

const getStatusVariant = (status: string) => {
  switch (status) {
    case 'open': return 'destructive'
    case 'in_progress': return 'default'
    case 'waiting_for_customer': return 'secondary'
    case 'resolved': return 'default'
    case 'closed': return 'secondary'
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
  loadTickets()
  loadStatistics()
  loadOpenTickets()
  loadUrgentTickets()
  loadAssignedUsers()
  loadCustomers()
})
</script>
