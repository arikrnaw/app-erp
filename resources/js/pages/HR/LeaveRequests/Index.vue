<template>
  <AppLayout>
    <div class="container mx-auto p-6">
      <div class="flex justify-between items-center mb-6">
        <div>
          <h1 class="text-3xl font-bold text-white">Leave Requests</h1>
          <p class="text-white mt-2">Manage employee leave requests and approvals</p>
        </div>
        <Button @click="navigateToCreate" class="flex items-center gap-2">
          <Plus class="w-4 h-4" />
          New Leave Request
        </Button>
      </div>

      <!-- Search and Filters -->
      <div class="rounded-lg shadow-sm border p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
          <div>
            <Label for="search">Search</Label>
            <Input id="search" v-model="search" placeholder="Search requests..." @input="debouncedSearch" />
          </div>
          <div>
            <Label for="status">Status</Label>
            <Select v-model="filters.status" @update:model-value="fetchLeaveRequests">
              <option value="">All Status</option>
              <option value="pending">Pending</option>
              <option value="approved">Approved</option>
              <option value="rejected">Rejected</option>
              <option value="cancelled">Cancelled</option>
            </Select>
          </div>
          <div>
            <Label for="leave_type">Leave Type</Label>
            <Select v-model="filters.leave_type_id" @update:model-value="fetchLeaveRequests">
              <option value="">All Types</option>
              <option v-for="type in leaveTypes" :key="type.id" :value="type.id">
                {{ type.name }}
              </option>
            </Select>
          </div>
          <div>
            <Label for="urgency">Urgency</Label>
            <Select v-model="filters.is_urgent" @update:model-value="fetchLeaveRequests">
              <option value="">All</option>
              <option value="true">Urgent</option>
              <option value="false">Normal</option>
            </Select>
          </div>
          <div>
            <Label for="date_range">Date Range</Label>
            <div class="flex gap-2">
              <Input v-model="filters.start_date" type="date" @change="fetchLeaveRequests" />
              <Input v-model="filters.end_date" type="date" @change="fetchLeaveRequests" />
            </div>
          </div>
        </div>
      </div>

      <!-- Leave Requests Table -->
      <div class="rounded-lg shadow-sm border">
        <div class="p-6">
          <div v-if="loading" class="flex justify-center items-center py-8">
            <Loader2 class="w-8 h-8 animate-spin text-blue-600" />
          </div>

          <div v-else-if="leaveRequests.length === 0" class="text-center py-8">
            <Calendar class="w-12 h-12 text-gray-400 mx-auto mb-4" />
            <h3 class="text-lg font-medium text-white mb-2">No leave requests found</h3>
            <p class="text-white mb-4">Get started by creating your first leave request.</p>
            <Button @click="navigateToCreate">Create Leave Request</Button>
          </div>

          <div v-else>
            <div class="overflow-x-auto">
              <table class="w-full">
                <thead>
                  <tr class="border-b">
                    <th class="text-left py-3 px-4 font-medium text-white">Employee</th>
                    <th class="text-left py-3 px-4 font-medium text-white">Leave Type</th>
                    <th class="text-left py-3 px-4 font-medium text-white">Date Range</th>
                    <th class="text-left py-3 px-4 font-medium text-white">Days</th>
                    <th class="text-left py-3 px-4 font-medium text-white">Status</th>
                    <th class="text-left py-3 px-4 font-medium text-white">Urgency</th>
                    <th class="text-left py-3 px-4 font-medium text-white">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="request in leaveRequests" :key="request.id" class="border-b">
                    <td class="py-4 px-4">
                      <div>
                        <div class="font-medium text-white">
                          {{ request.employee?.first_name }} {{ request.employee?.last_name }}
                        </div>
                        <div class="text-sm text-white">{{ request.employee?.employee_number }}</div>
                      </div>
                    </td>
                    <td class="py-4 px-4">
                      <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full"
                          :style="{ backgroundColor: request.leave_type?.color || '#3B82F6' }"></div>
                        <span class="font-medium">{{ request.leave_type?.name }}</span>
                      </div>
                    </td>
                    <td class="py-4 px-4">
                      <div>
                        <div class="font-medium text-white">
                          {{ formatDate(request.start_date) }} - {{ formatDate(request.end_date) }}
                        </div>
                        <div class="text-sm text-white">{{ request.total_days }} days</div>
                      </div>
                    </td>
                    <td class="py-4 px-4">
                      <span class="font-medium">{{ request.total_days }} days</span>
                    </td>
                    <td class="py-4 px-4">
                      <Badge :variant="getStatusVariant(request.status)">
                        {{ request.status }}
                      </Badge>
                    </td>
                    <td class="py-4 px-4">
                      <Badge v-if="request.is_urgent" variant="destructive" class="text-xs">
                        Urgent
                      </Badge>
                      <span v-else class="text-gray-500 text-sm">Normal</span>
                    </td>
                    <td class="py-4 px-4">
                      <div class="flex items-center gap-2">
                        <Button variant="ghost" size="sm" @click="navigateToShow(request.id)">
                          <Eye class="w-4 h-4" />
                        </Button>
                        <Button v-if="request.status === 'pending'" variant="ghost" size="sm"
                          @click="navigateToEdit(request.id)">
                          <Edit class="w-4 h-4" />
                        </Button>
                        <Button v-if="request.status === 'pending'" variant="ghost" size="sm"
                          @click="approveRequest(request)" :disabled="approveLoading === request.id">
                          <Loader2 v-if="approveLoading === request.id" class="w-4 h-4 animate-spin" />
                          <Check v-else class="w-4 h-4" />
                        </Button>
                        <Button v-if="request.status === 'pending'" variant="ghost" size="sm"
                          @click="rejectRequest(request)" :disabled="rejectLoading === request.id">
                          <Loader2 v-if="rejectLoading === request.id" class="w-4 h-4 animate-spin" />
                          <X v-else class="w-4 h-4" />
                        </Button>
                        <Button v-if="request.status === 'pending'" variant="ghost" size="sm"
                          @click="cancelRequest(request)" :disabled="cancelLoading === request.id">
                          <Loader2 v-if="cancelLoading === request.id" class="w-4 h-4 animate-spin" />
                          <Trash2 v-else class="w-4 h-4" />
                        </Button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Pagination -->
            <div v-if="pagination.last_page > 1" class="mt-6">
              <DataPagination :current-page="pagination.current_page" :total-pages="pagination.last_page"
                :total-items="pagination.total" :per-page="pagination.per_page" @page-change="handlePageChange" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Badge } from '@/components/ui/badge'
import { DataPagination } from '@/components/ui/pagination'
import { Plus, Eye, Edit, Trash2, Check, X, Calendar, Loader2 } from 'lucide-vue-next'
import { apiService } from '@/services/api'
import type { LeaveRequest, LeaveType } from '@/types/erp'
import { useDebounce } from '@/composables/useDebounce'

// Reactive data
const leaveRequests = ref<LeaveRequest[]>([])
const leaveTypes = ref<LeaveType[]>([])
const loading = ref(false)
const approveLoading = ref<number | null>(null)
const rejectLoading = ref<number | null>(null)
const cancelLoading = ref<number | null>(null)
const search = ref('')
const filters = ref({
  status: '',
  leave_type_id: '',
  is_urgent: '',
  start_date: '',
  end_date: '',
})
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
})

// Debounced search
const debouncedSearch = useDebounce(() => {
  pagination.value.current_page = 1
  fetchLeaveRequests()
}, 300)

// Fetch leave requests
const fetchLeaveRequests = async () => {
  loading.value = true
  try {
    const params = {
      page: pagination.value.current_page,
      search: search.value,
      ...filters.value,
    }

    const response = await apiService.getLeaveRequests(params)
    leaveRequests.value = response.data
    pagination.value = {
      current_page: response.meta.current_page,
      last_page: response.meta.last_page,
      per_page: response.meta.per_page,
      total: response.meta.total,
    }
  } catch (error) {
    console.error('Error fetching leave requests:', error)
  } finally {
    loading.value = false
  }
}

// Fetch leave types for filter
const fetchLeaveTypes = async () => {
  try {
    const response = await apiService.getActiveLeaveTypes()
    leaveTypes.value = response
  } catch (error) {
    console.error('Error fetching leave types:', error)
  }
}

// Navigation methods
const navigateToCreate = () => {
  router.visit('/hr/leave-requests/create')
}

const navigateToShow = (id: number) => {
  router.visit(`/hr/leave-requests/${id}`)
}

const navigateToEdit = (id: number) => {
  router.visit(`/hr/leave-requests/${id}/edit`)
}

// Action methods
const approveRequest = async (request: LeaveRequest) => {
  approveLoading.value = request.id
  try {
    await apiService.approveLeaveRequest(request.id, {
      approval_notes: 'Approved by manager'
    })
    await fetchLeaveRequests()
  } catch (error) {
    console.error('Error approving leave request:', error)
  } finally {
    approveLoading.value = null
  }
}

const rejectRequest = async (request: LeaveRequest) => {
  const reason = prompt('Please provide rejection reason:')
  if (!reason) return

  rejectLoading.value = request.id
  try {
    await apiService.rejectLeaveRequest(request.id, {
      rejection_reason: reason
    })
    await fetchLeaveRequests()
  } catch (error) {
    console.error('Error rejecting leave request:', error)
  } finally {
    rejectLoading.value = null
  }
}

const cancelRequest = async (request: LeaveRequest) => {
  if (!confirm(`Are you sure you want to cancel this leave request?`)) {
    return
  }

  cancelLoading.value = request.id
  try {
    await apiService.cancelLeaveRequest(request.id)
    await fetchLeaveRequests()
  } catch (error) {
    console.error('Error cancelling leave request:', error)
  } finally {
    cancelLoading.value = null
  }
}

const handlePageChange = (page: number) => {
  pagination.value.current_page = page
  fetchLeaveRequests()
}

// Helper functions
const getStatusVariant = (status: string) => {
  switch (status) {
    case 'approved': return 'default'
    case 'pending': return 'secondary'
    case 'rejected': return 'destructive'
    case 'cancelled': return 'outline'
    default: return 'secondary'
  }
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString()
}

// Initialize
onMounted(() => {
  fetchLeaveRequests()
  fetchLeaveTypes()
})
</script>
