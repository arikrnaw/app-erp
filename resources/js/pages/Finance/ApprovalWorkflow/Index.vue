<template>
  <AppLayout>
    <Head title="Approval Workflow Management" />
    
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Approval Workflow Management</h1>
          <p class="text-gray-600">Centralized approval management for all financial transactions</p>
        </div>
        <Button @click="showCreateWorkflow = true">
          <Plus class="w-4 h-4 mr-2" />
          Create Workflow
        </Button>
      </div>

      <!-- Overview Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <Card>
          <CardContent class="p-6">
            <div class="flex items-center">
              <div class="p-2 bg-blue-100 rounded-lg">
                <Clock class="w-6 h-6 text-blue-600" />
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Pending Approvals</p>
                <p class="text-2xl font-bold text-gray-900">{{ overview.pending_count }}</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-6">
            <div class="flex items-center">
              <div class="p-2 bg-green-100 rounded-lg">
                <CheckCircle class="w-6 h-6 text-green-600" />
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Approved Today</p>
                <p class="text-2xl font-bold text-gray-900">{{ overview.approved_today }}</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-6">
            <div class="flex items-center">
              <div class="p-2 bg-yellow-100 rounded-lg">
                <AlertTriangle class="w-6 h-6 text-yellow-600" />
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Overdue</p>
                <p class="text-2xl font-bold text-gray-900">{{ overview.overdue_count }}</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-6">
            <div class="flex items-center">
              <div class="p-2 bg-purple-100 rounded-lg">
                <BarChart3 class="w-6 h-6 text-purple-600" />
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Total Workflows</p>
                <p class="text-2xl font-bold text-gray-900">{{ overview.total_workflows }}</p>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Tabs -->
      <div class="border-b border-gray-200">
        <nav class="-mb-px flex space-x-8">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            @click="activeTab = tab.id"
            :class="[
              activeTab === tab.id
                ? 'border-blue-500 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
              'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm'
            ]"
          >
            {{ tab.name }}
          </button>
        </nav>
      </div>

      <!-- Tab Content -->
      <div class="space-y-6">
        <!-- Pending Approvals Tab -->
        <div v-if="activeTab === 'pending'" class="space-y-4">
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-medium text-gray-900">Pending Approvals</h3>
            <div class="flex space-x-2">
              <Select v-model="pendingFilters.type" class="w-40">
                <option value="">All Types</option>
                <option value="purchase_order">Purchase Orders</option>
                <option value="expense">Expenses</option>
                <option value="asset_purchase">Asset Purchases</option>
                <option value="budget">Budget</option>
              </Select>
              <Select v-model="pendingFilters.priority" class="w-40">
                <option value="">All Priorities</option>
                <option value="low">Low</option>
                <option value="medium">Medium</option>
                <option value="high">High</option>
                <option value="urgent">Urgent</option>
              </Select>
            </div>
          </div>

          <div class="bg-white shadow rounded-lg">
            <div class="overflow-x-auto">
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Type</TableHead>
                    <TableHead>Description</TableHead>
                    <TableHead>Amount</TableHead>
                    <TableHead>Requestor</TableHead>
                    <TableHead>Due Date</TableHead>
                    <TableHead>Priority</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead>Actions</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  <TableRow v-for="approval in pendingApprovals" :key="approval.id">
                    <TableCell>
                      <Badge :variant="getTypeBadgeVariant(approval.approvable_type)">
                        {{ getTypeLabel(approval.approvable_type) }}
                      </Badge>
                    </TableCell>
                    <TableCell class="max-w-xs truncate">
                      {{ approval.description }}
                    </TableCell>
                    <TableCell class="font-medium">
                      {{ formatCurrency(approval.amount) }}
                    </TableCell>
                    <TableCell>{{ approval.requestor?.name }}</TableCell>
                    <TableCell>
                      <span :class="isOverdue(approval.due_date) ? 'text-red-600 font-medium' : ''">
                        {{ formatDate(approval.due_date) }}
                      </span>
                    </TableCell>
                    <TableCell>
                      <Badge :variant="getPriorityBadgeVariant(approval.priority)">
                        {{ approval.priority }}
                      </Badge>
                    </TableCell>
                    <TableCell>
                      <Badge :variant="getStatusBadgeVariant(approval.status)">
                        {{ approval.status }}
                      </Badge>
                    </TableCell>
                    <TableCell>
                      <div class="flex space-x-2">
                        <Button size="sm" @click="viewApproval(approval)">
                          <Eye class="w-4 h-4" />
                        </Button>
                        <Button size="sm" variant="outline" @click="approveRequest(approval.id)">
                          <Check class="w-4 h-4" />
                        </Button>
                        <Button size="sm" variant="outline" @click="rejectRequest(approval.id)">
                          <X class="w-4 h-4" />
                        </Button>
                      </div>
                    </TableCell>
                  </TableRow>
                </TableBody>
              </Table>
            </div>
          </div>
        </div>

        <!-- Workflows Tab -->
        <div v-if="activeTab === 'workflows'" class="space-y-4">
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-medium text-gray-900">Approval Workflows</h3>
            <Button @click="showCreateWorkflow = true">
              <Plus class="w-4 h-4 mr-2" />
              New Workflow
            </Button>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <Card v-for="workflow in workflows" :key="workflow.id" class="hover:shadow-lg transition-shadow">
              <CardHeader>
                <div class="flex items-center justify-between">
                  <h4 class="font-semibold">{{ workflow.name }}</h4>
                  <Badge :variant="workflow.is_active ? 'default' : 'secondary'">
                    {{ workflow.is_active ? 'Active' : 'Inactive' }}
                  </Badge>
                </div>
                <p class="text-sm text-gray-600">{{ workflow.description }}</p>
              </CardHeader>
              <CardContent>
                <div class="space-y-3">
                  <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Type:</span>
                    <span class="font-medium">{{ getTypeLabel(workflow.type) }}</span>
                  </div>
                  <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Threshold:</span>
                    <span class="font-medium">{{ formatCurrency(workflow.threshold_amount) }}</span>
                  </div>
                  <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Levels:</span>
                    <span class="font-medium">{{ workflow.levels?.length || 0 }}</span>
                  </div>
                </div>
              </CardContent>
              <CardFooter>
                <div class="flex space-x-2 w-full">
                  <Button size="sm" variant="outline" class="flex-1" @click="editWorkflow(workflow)">
                    <Edit class="w-4 h-4 mr-1" />
                    Edit
                  </Button>
                  <Button size="sm" variant="outline" class="flex-1" @click="toggleWorkflowStatus(workflow.id)">
                    {{ workflow.is_active ? 'Deactivate' : 'Activate' }}
                  </Button>
                </div>
              </CardFooter>
            </Card>
          </div>
        </div>

        <!-- Analytics Tab -->
        <div v-if="activeTab === 'analytics'" class="space-y-6">
          <h3 class="text-lg font-medium text-gray-900">Approval Analytics</h3>
          
          <!-- Charts Row -->
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <Card>
              <CardHeader>
                <CardTitle>Approval Volume by Type</CardTitle>
              </CardHeader>
              <CardContent>
                <AreaChart
                  :data="analytics.byType"
                  :categories="['Type']"
                  :index="'type'"
                  :value="'count'"
                  class="h-64"
                />
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle>Approval Time by Priority</CardTitle>
              </CardHeader>
              <CardContent>
                <AreaChart
                  :data="analytics.byPriority"
                  :categories="['Priority']"
                  :index="'priority'"
                  :value="'avg_time'"
                  class="h-64"
                />
              </CardContent>
            </Card>
          </div>

          <!-- Statistics Grid -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <Card>
              <CardContent class="p-6">
                <div class="text-center">
                  <p class="text-sm font-medium text-gray-600">Average Approval Time</p>
                  <p class="text-2xl font-bold text-gray-900">{{ analytics.avgApprovalTime }} hours</p>
                </div>
              </CardContent>
            </Card>

            <Card>
              <CardContent class="p-6">
                <div class="text-center">
                  <p class="text-sm font-medium text-gray-600">Approval Rate</p>
                  <p class="text-2xl font-bold text-gray-900">{{ analytics.approvalRate }}%</p>
                </div>
              </CardContent>
            </Card>

            <Card>
              <CardContent class="p-6">
                <div class="text-center">
                  <p class="text-sm font-medium text-gray-600">Total Amount Approved</p>
                  <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(analytics.totalApproved) }}</p>
                </div>
              </CardContent>
            </Card>
          </div>
        </div>
      </div>
    </div>

    <!-- Create/Edit Workflow Modal -->
    <Dialog :open="showCreateWorkflow" @close="showCreateWorkflow = false">
      <DialogContent class="max-w-2xl">
        <DialogHeader>
          <DialogTitle>{{ editingWorkflow ? 'Edit Workflow' : 'Create New Workflow' }}</DialogTitle>
        </DialogHeader>
        
        <form @submit.prevent="saveWorkflow" class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <Label for="name">Workflow Name</Label>
              <Input id="name" v-model="workflowForm.name" required />
            </div>
            <div>
              <Label for="type">Type</Label>
              <Select id="type" v-model="workflowForm.type" required>
                <option value="purchase_order">Purchase Order</option>
                <option value="expense">Expense</option>
                <option value="asset_purchase">Asset Purchase</option>
                <option value="budget">Budget</option>
                <option value="general">General</option>
              </Select>
            </div>
          </div>

          <div>
            <Label for="description">Description</Label>
            <Textarea id="description" v-model="workflowForm.description" rows="3" />
          </div>

          <div>
            <Label for="threshold">Threshold Amount</Label>
            <Input id="threshold" v-model="workflowForm.threshold_amount" type="number" step="0.01" required />
          </div>

          <div class="space-y-4">
            <Label>Approval Levels</Label>
            <div v-for="(level, index) in workflowForm.approval_levels" :key="index" class="border p-4 rounded-lg">
              <div class="grid grid-cols-3 gap-4">
                <div>
                  <Label>Level {{ level.level }}</Label>
                  <Input v-model="level.level" type="number" min="1" required />
                </div>
                <div>
                  <Label>Approver Role</Label>
                  <Input v-model="level.approver_role" required />
                </div>
                <div>
                  <Label>Escalation (hours)</Label>
                  <Input v-model="level.escalation_hours" type="number" min="1" />
                </div>
              </div>
              <Button 
                v-if="workflowForm.approval_levels.length > 1" 
                type="button" 
                variant="outline" 
                size="sm" 
                @click="removeLevel(index)"
                class="mt-2"
              >
                <X class="w-4 h-4 mr-1" />
                Remove Level
              </Button>
            </div>
            <Button type="button" variant="outline" @click="addLevel">
              <Plus class="w-4 h-4 mr-2" />
              Add Level
            </Button>
          </div>

          <div class="flex space-x-2">
            <Button type="submit" :disabled="saving">
              {{ saving ? 'Saving...' : (editingWorkflow ? 'Update' : 'Create') }}
            </Button>
            <Button type="button" variant="outline" @click="showCreateWorkflow = false">
              Cancel
            </Button>
          </div>
        </form>
      </DialogContent>
    </Dialog>

    <!-- View Approval Modal -->
    <Dialog :open="showViewApproval" @close="showViewApproval = false">
      <DialogContent class="max-w-4xl">
        <DialogHeader>
          <DialogTitle>Approval Details</DialogTitle>
        </DialogHeader>
        
        <div v-if="selectedApproval" class="space-y-6">
          <!-- Approval Info -->
          <div class="grid grid-cols-2 gap-4">
            <div>
              <Label class="text-sm font-medium text-gray-600">Type</Label>
              <p class="text-sm">{{ getTypeLabel(selectedApproval.approvable_type) }}</p>
            </div>
            <div>
              <Label class="text-sm font-medium text-gray-600">Amount</Label>
              <p class="text-sm font-medium">{{ formatCurrency(selectedApproval.amount) }}</p>
            </div>
            <div>
              <Label class="text-sm font-medium text-gray-600">Requestor</Label>
              <p class="text-sm">{{ selectedApproval.requestor?.name }}</p>
            </div>
            <div>
              <Label class="text-sm font-medium text-gray-600">Due Date</Label>
              <p class="text-sm">{{ formatDate(selectedApproval.due_date) }}</p>
            </div>
          </div>

          <div>
            <Label class="text-sm font-medium text-gray-600">Description</Label>
            <p class="text-sm">{{ selectedApproval.description }}</p>
          </div>

          <!-- Approval Actions -->
          <div class="border-t pt-4">
            <Label for="comments">Comments</Label>
            <Textarea id="comments" v-model="approvalComments" rows="3" placeholder="Add your approval comments..." />
            
            <div class="flex space-x-2 mt-4">
              <Button @click="approveRequest(selectedApproval.id)" :disabled="processing">
                <Check class="w-4 h-4 mr-2" />
                Approve
              </Button>
              <Button variant="outline" @click="rejectRequest(selectedApproval.id)" :disabled="processing">
                <X class="w-4 h-4 mr-2" />
                Reject
              </Button>
            </div>
          </div>
        </div>
      </DialogContent>
    </Dialog>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle, CardFooter } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Select } from '@/components/ui/select'
import { Textarea } from '@/components/ui/textarea'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { AreaChart } from '@/components/ui/chart-area'
import { 
  Plus, 
  Edit, 
  Eye, 
  Check, 
  X, 
  Clock, 
  CheckCircle, 
  AlertTriangle, 
  BarChart3 
} from 'lucide-vue-next'
import { useApi } from '@/composables/useApi'

// Composables
const api = useApi()

// State
const activeTab = ref('pending')
const showCreateWorkflow = ref(false)
const showViewApproval = ref(false)
const editingWorkflow = ref(null)
const selectedApproval = ref(null)
const approvalComments = ref('')
const saving = ref(false)
const processing = ref(false)

// Data
const overview = ref({
  pending_count: 0,
  approved_today: 0,
  overdue_count: 0,
  total_workflows: 0
})

const pendingApprovals = ref([])
const workflows = ref([])
const analytics = ref({
  byType: [],
  byPriority: [],
  avgApprovalTime: 0,
  approvalRate: 0,
  totalApproved: 0
})

// Filters
const pendingFilters = ref({
  type: '',
  priority: ''
})

// Form
const workflowForm = ref({
  name: '',
  type: 'purchase_order',
  description: '',
  threshold_amount: 0,
  approval_levels: [
    { level: 1, approver_role: '', escalation_hours: 24 }
  ],
  is_active: true,
  auto_escalate: false,
  require_all_levels: false
})

// Tabs
const tabs = [
  { id: 'pending', name: 'Pending Approvals' },
  { id: 'workflows', name: 'Workflows' },
  { id: 'analytics', name: 'Analytics' }
]

// Computed
const filteredPendingApprovals = computed(() => {
  let filtered = pendingApprovals.value

  if (pendingFilters.value.type) {
    filtered = filtered.filter(a => a.approvable_type === pendingFilters.value.type)
  }

  if (pendingFilters.value.priority) {
    filtered = filtered.filter(a => a.priority === pendingFilters.value.priority)
  }

  return filtered
})

// Methods
const loadDashboard = async () => {
  try {
    const response = await api.get('/finance/approval-workflow/dashboard')
    if (response.success) {
      overview.value = response.data.overview
      pendingApprovals.value = response.data.pending_approvals
      workflows.value = response.data.workflows
      analytics.value = response.data.analytics
    }
  } catch (error) {
    console.error('Error loading dashboard:', error)
  }
}

const addLevel = () => {
  const nextLevel = workflowForm.value.approval_levels.length + 1
  workflowForm.value.approval_levels.push({
    level: nextLevel,
    approver_role: '',
    escalation_hours: 24
  })
}

const removeLevel = (index: number) => {
  workflowForm.value.approval_levels.splice(index, 1)
  // Reorder levels
  workflowForm.value.approval_levels.forEach((level, idx) => {
    level.level = idx + 1
  })
}

const saveWorkflow = async () => {
  saving.value = true
  try {
    if (editingWorkflow.value) {
      await api.patch(`/finance/approval-workflow/workflows/${editingWorkflow.value.id}`, workflowForm.value)
    } else {
      await api.post('/finance/approval-workflow/workflows', workflowForm.value)
    }
    
    showCreateWorkflow.value = false
    editingWorkflow.value = null
    resetWorkflowForm()
    await loadDashboard()
  } catch (error) {
    console.error('Error saving workflow:', error)
  } finally {
    saving.value = false
  }
}

const editWorkflow = (workflow: any) => {
  editingWorkflow.value = workflow
  workflowForm.value = { ...workflow }
  showCreateWorkflow.value = true
}

const resetWorkflowForm = () => {
  workflowForm.value = {
    name: '',
    type: 'purchase_order',
    description: '',
    threshold_amount: 0,
    approval_levels: [
      { level: 1, approver_role: '', escalation_hours: 24 }
    ],
    is_active: true,
    auto_escalate: false,
    require_all_levels: false
  }
}

const toggleWorkflowStatus = async (workflowId: number) => {
  try {
    await api.patch(`/finance/approval-workflow/workflows/${workflowId}`, {
      is_active: !workflows.value.find(w => w.id === workflowId)?.is_active
    })
    await loadDashboard()
  } catch (error) {
    console.error('Error toggling workflow status:', error)
  }
}

const viewApproval = (approval: any) => {
  selectedApproval.value = approval
  showViewApproval.value = true
}

const approveRequest = async (approvalId: number) => {
  processing.value = true
  try {
    await api.patch(`/finance/approval-workflow/approval-requests/${approvalId}/process`, {
      action: 'approve',
      comments: approvalComments.value
    })
    
    showViewApproval.value = false
    selectedApproval.value = null
    approvalComments.value = ''
    await loadDashboard()
  } catch (error) {
    console.error('Error approving request:', error)
  } finally {
    processing.value = false
  }
}

const rejectRequest = async (approvalId: number) => {
  processing.value = true
  try {
    await api.patch(`/finance/approval-workflow/approval-requests/${approvalId}/process`, {
      action: 'reject',
      comments: approvalComments.value
    })
    
    showViewApproval.value = false
    selectedApproval.value = null
    approvalComments.value = ''
    await loadDashboard()
  } catch (error) {
    console.error('Error rejecting request:', error)
  } finally {
    processing.value = false
  }
}

// Utility methods
const getTypeLabel = (type: string) => {
  const labels = {
    purchase_order: 'Purchase Order',
    expense: 'Expense',
    asset_purchase: 'Asset Purchase',
    budget: 'Budget',
    general: 'General'
  }
  return labels[type] || type
}

const getTypeBadgeVariant = (type: string) => {
  const variants = {
    purchase_order: 'default',
    expense: 'secondary',
    asset_purchase: 'outline',
    budget: 'destructive',
    general: 'secondary'
  }
  return variants[type] || 'secondary'
}

const getPriorityBadgeVariant = (priority: string) => {
  const variants = {
    low: 'secondary',
    medium: 'default',
    high: 'destructive',
    urgent: 'destructive'
  }
  return variants[priority] || 'secondary'
}

const getStatusBadgeVariant = (status: string) => {
  const variants = {
    pending: 'warning',
    approved: 'default',
    rejected: 'destructive',
    cancelled: 'secondary'
  }
  return variants[status] || 'secondary'
}

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(amount)
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString()
}

const isOverdue = (dueDate: string) => {
  return new Date(dueDate) < new Date()
}

// Lifecycle
onMounted(() => {
  loadDashboard()
})
</script>
