<template>
  <AppLayout>
    <div class="container mx-auto p-6">
      <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="flex items-center gap-4 mb-6">
          <Button variant="ghost" size="sm" @click="navigateBack">
            <ArrowLeft class="w-4 h-4" />
          </Button>
          <div class="flex-1">
            <div class="flex items-center gap-3">
              <div class="w-6 h-6 rounded-full" :style="{ backgroundColor: leaveType?.color || '#3B82F6' }"></div>
              <h1 class="text-3xl font-bold text-white">{{ leaveType?.name }}</h1>
            </div>
            <p class="text-white mt-2">Leave type details and configuration</p>
          </div>
          <div class="flex items-center gap-2">
            <Button variant="outline" @click="navigateToEdit">
              <Edit class="w-4 h-4 mr-2" />
              Edit
            </Button>
            <Button variant="outline" @click="toggleStatus" :disabled="toggleLoading">
              <Loader2 v-if="toggleLoading" class="w-4 h-4 mr-2 animate-spin" />
              <Power v-else class="w-4 h-4 mr-2" />
              {{ leaveType?.is_active ? 'Deactivate' : 'Activate' }}
            </Button>
            <Button variant="destructive" @click="deleteLeaveType" :disabled="deleteLoading">
              <Loader2 v-if="deleteLoading" class="w-4 h-4 mr-2 animate-spin" />
              <Trash2 v-else class="w-4 h-4 mr-2" />
              Delete
            </Button>
          </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="flex justify-center items-center py-12">
          <Loader2 class="w-8 h-8 animate-spin text-blue-600" />
        </div>

        <!-- Not Found State -->
        <div v-else-if="!leaveType" class="text-center py-12">
          <Calendar class="w-12 h-12 text-gray-400 mx-auto mb-4" />
          <h3 class="text-lg font-medium text-white mb-2">Leave type not found</h3>
          <p class="text-white mb-4">The leave type you're looking for doesn't exist.</p>
          <Button @click="navigateBack">Back to Leave Types</Button>
        </div>

        <!-- Content -->
        <div v-else class="space-y-6">
          <!-- Basic Information -->
          <div class="rounded-lg shadow-sm border">
            <div class="p-6">
              <h3 class="text-lg font-medium text-white mb-4">Basic Information</h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <Label class="text-sm font-medium text-gray-500">Name</Label>
                  <p class="text-white mt-1">{{ leaveType.name }}</p>
                </div>
                <div>
                  <Label class="text-sm font-medium text-gray-500">Color</Label>
                  <div class="flex items-center gap-2 mt-1">
                    <div class="w-4 h-4 rounded-full" :style="{ backgroundColor: leaveType.color || '#3B82F6' }"></div>
                    <span class="text-white">{{ leaveType.color || '#3B82F6' }}</span>
                  </div>
                </div>
                <div>
                  <Label class="text-sm font-medium text-gray-500">Default Days per Year</Label>
                  <p class="text-white mt-1">{{ leaveType.default_days_per_year }} days</p>
                </div>
                <div>
                  <Label class="text-sm font-medium text-gray-500">Sort Order</Label>
                  <p class="text-white mt-1">{{ leaveType.sort_order }}</p>
                </div>
                <div v-if="leaveType.description" class="md:col-span-2">
                  <Label class="text-sm font-medium text-gray-500">Description</Label>
                  <p class="text-white mt-1">{{ leaveType.description }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Configuration -->
          <div class="rounded-lg shadow-sm border">
            <div class="p-6">
              <h3 class="text-lg font-medium text-white mb-4">Configuration</h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <Label class="text-sm font-medium text-gray-500">Leave Type</Label>
                  <Badge :variant="leaveType.is_paid ? 'default' : 'secondary'" class="mt-1">
                    {{ leaveType.is_paid ? 'Paid Leave' : 'Unpaid Leave' }}
                  </Badge>
                </div>
                <div>
                  <Label class="text-sm font-medium text-gray-500">Approval Required</Label>
                  <Badge :variant="leaveType.requires_approval ? 'default' : 'secondary'" class="mt-1">
                    {{ leaveType.requires_approval ? 'Required' : 'Not Required' }}
                  </Badge>
                </div>
                <div>
                  <Label class="text-sm font-medium text-gray-500">Document Required</Label>
                  <Badge :variant="leaveType.requires_document ? 'default' : 'secondary'" class="mt-1">
                    {{ leaveType.requires_document ? 'Required' : 'Not Required' }}
                  </Badge>
                </div>
                <div>
                  <Label class="text-sm font-medium text-gray-500">Can Carry Forward</Label>
                  <Badge :variant="leaveType.can_carry_forward ? 'default' : 'secondary'" class="mt-1">
                    {{ leaveType.can_carry_forward ? 'Yes' : 'No' }}
                  </Badge>
                </div>
                <div v-if="leaveType.can_carry_forward && leaveType.max_carry_forward_days">
                  <Label class="text-sm font-medium text-gray-500">Max Carry Forward Days</Label>
                  <p class="text-white mt-1">{{ leaveType.max_carry_forward_days }} days</p>
                </div>
                <div>
                  <Label class="text-sm font-medium text-gray-500">Status</Label>
                  <Badge :variant="leaveType.is_active ? 'default' : 'secondary'" class="mt-1">
                    {{ leaveType.is_active ? 'Active' : 'Inactive' }}
                  </Badge>
                </div>
              </div>
            </div>
          </div>

          <!-- Statistics -->
          <div class="rounded-lg shadow-sm border">
            <div class="p-6">
              <h3 class="text-lg font-medium text-white mb-4">Usage Statistics</h3>
              <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="text-center">
                  <div class="text-2xl font-bold text-blue-600">{{ statistics.total_requests || 0 }}</div>
                  <div class="text-sm text-white">Total Requests</div>
                </div>
                <div class="text-center">
                  <div class="text-2xl font-bold text-green-600">{{ statistics.approved_requests || 0 }}</div>
                  <div class="text-sm text-white">Approved</div>
                </div>
                <div class="text-center">
                  <div class="text-2xl font-bold text-yellow-600">{{ statistics.pending_requests || 0 }}</div>
                  <div class="text-sm text-white">Pending</div>
                </div>
                <div class="text-center">
                  <div class="text-2xl font-bold text-red-600">{{ statistics.rejected_requests || 0 }}</div>
                  <div class="text-sm text-white">Rejected</div>
                </div>
              </div>
              <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center">
                  <div class="text-lg font-semibold text-white">{{ statistics.total_days_used || 0 }}</div>
                  <div class="text-sm text-white">Total Days Used</div>
                </div>
                <div class="text-center">
                  <div class="text-lg font-semibold text-white">{{ statistics.average_days_per_request || 0 }}</div>
                  <div class="text-sm text-white">Avg Days per Request</div>
                </div>
                <div class="text-center">
                  <div class="text-lg font-semibold text-white">{{ statistics.usage_percentage || 0 }}%</div>
                  <div class="text-sm text-white">Usage Rate</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Recent Leave Requests -->
          <div class="rounded-lg shadow-sm border">
            <div class="p-6">
              <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-white">Recent Leave Requests</h3>
                <Button variant="outline" size="sm" @click="navigateToLeaveRequests">
                  View All
                </Button>
              </div>
              <div v-if="recentRequests.length === 0" class="text-center py-8">
                <Calendar class="w-8 h-8 text-gray-400 mx-auto mb-2" />
                <p class="text-white">No leave requests found for this type</p>
              </div>
              <div v-else class="space-y-3">
                <div v-for="request in recentRequests" :key="request.id"
                  class="flex items-center justify-between p-3 border rounded-lg hover:bg-gray-50">
                  <div class="flex items-center gap-3">
                    <div class="w-2 h-2 rounded-full" :class="getStatusColor(request.status)"></div>
                    <div>
                      <div class="font-medium text-white">
                        {{ request.employee?.first_name }} {{ request.employee?.last_name }}
                      </div>
                      <div class="text-sm text-white">
                        {{ formatDate(request.start_date) }} - {{ formatDate(request.end_date) }}
                      </div>
                    </div>
                  </div>
                  <Badge :variant="getStatusVariant(request.status)">
                    {{ request.status }}
                  </Badge>
                </div>
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
import { Badge } from '@/components/ui/badge'
import { Label } from '@/components/ui/label'
import { ArrowLeft, Edit, Power, Trash2, Calendar, Loader2 } from 'lucide-vue-next'
import { apiService } from '@/services/api'
import type { LeaveType, LeaveRequest } from '@/types/erp'

// Props
interface Props {
  id: number
}

const props = defineProps<Props>()

// Reactive data
const leaveType = ref<LeaveType | null>(null)
const recentRequests = ref<LeaveRequest[]>([])
const statistics = ref<any>({})
const loading = ref(true)
const toggleLoading = ref(false)
const deleteLoading = ref(false)

// Navigation
const navigateBack = () => {
  router.visit('/hr/leave-types')
}

const navigateToEdit = () => {
  router.visit(`/hr/leave-types/${props.id}/edit`)
}

const navigateToLeaveRequests = () => {
  router.visit('/hr/leave-requests', {
    data: { leave_type_id: props.id }
  })
}

// Actions
const toggleStatus = async () => {
  if (!leaveType.value) return

  toggleLoading.value = true
  try {
    await apiService.toggleLeaveTypeStatus(leaveType.value.id)
    await fetchLeaveType()
  } catch (error) {
    console.error('Error toggling leave type status:', error)
  } finally {
    toggleLoading.value = false
  }
}

const deleteLeaveType = async () => {
  if (!leaveType.value) return

  if (!confirm(`Are you sure you want to delete "${leaveType.value.name}"?`)) {
    return
  }

  deleteLoading.value = true
  try {
    await apiService.deleteLeaveType(leaveType.value.id)
    navigateBack()
  } catch (error) {
    console.error('Error deleting leave type:', error)
  } finally {
    deleteLoading.value = false
  }
}

// Helper functions
const getStatusColor = (status: string) => {
  switch (status) {
    case 'approved': return 'bg-green-500'
    case 'pending': return 'bg-yellow-500'
    case 'rejected': return 'bg-red-500'
    case 'cancelled': return 'bg-gray-500'
    default: return 'bg-gray-500'
  }
}

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

// Fetch data
const fetchLeaveType = async () => {
  loading.value = true
  try {
    const [leaveTypeData, statisticsData] = await Promise.all([
      apiService.getLeaveType(props.id),
      apiService.getLeaveTypeStatistics(props.id)
    ])

    leaveType.value = leaveTypeData
    statistics.value = statisticsData
  } catch (error) {
    console.error('Error fetching leave type:', error)
  } finally {
    loading.value = false
  }
}

// Initialize
onMounted(() => {
  fetchLeaveType()
})
</script>
