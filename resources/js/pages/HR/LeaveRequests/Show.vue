<template>
  <div class="container mx-auto p-6">
    <div class="max-w-4xl mx-auto">
      <!-- Header -->
      <div class="flex items-center gap-4 mb-6">
        <Button variant="ghost" size="sm" @click="navigateBack">
          <ArrowLeft class="w-4 h-4" />
        </Button>
        <div class="flex-1">
          <h1 class="text-3xl font-bold text-white">Leave Request #{{ leaveRequest?.request_number }}</h1>
          <p class="text-white mt-2">Leave request details and approval status</p>
        </div>
        <div class="flex items-center gap-2">
          <Button v-if="leaveRequest?.status === 'pending'" variant="outline" @click="navigateToEdit">
            <Edit class="w-4 h-4 mr-2" />
            Edit
          </Button>
          <Button v-if="leaveRequest?.status === 'pending'" variant="outline" @click="approveRequest"
            :disabled="approveLoading">
            <Check v-if="!approveLoading" class="w-4 h-4 mr-2" />
            <Loader2 v-else class="w-4 h-4 mr-2 animate-spin" />
            Approve
          </Button>
          <Button v-if="leaveRequest?.status === 'pending'" variant="outline" @click="rejectRequest"
            :disabled="rejectLoading">
            <X v-if="!rejectLoading" class="w-4 h-4 mr-2" />
            <Loader2 v-else class="w-4 h-4 mr-2 animate-spin" />
            Reject
          </Button>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center py-12">
        <Loader2 class="w-8 h-8 animate-spin text-blue-600" />
      </div>

      <!-- Not Found State -->
      <div v-else-if="!leaveRequest" class="text-center py-12">
        <Calendar class="w-12 h-12 text-gray-400 mx-auto mb-4" />
        <h3 class="text-lg font-medium text-white mb-2">Leave request not found</h3>
        <p class="text-white mb-4">The leave request you're looking for doesn't exist.</p>
        <Button @click="navigateBack">Back to Leave Requests</Button>
      </div>

      <!-- Content -->
      <div v-else class="space-y-6">
        <!-- Status Banner -->
        <div class="rounded-lg shadow-sm border p-4">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <Badge :variant="getStatusVariant(leaveRequest.status)" class="text-sm">
                {{ leaveRequest.status.toUpperCase() }}
              </Badge>
              <Badge v-if="leaveRequest.is_urgent" variant="destructive" class="text-xs">
                URGENT
              </Badge>
            </div>
            <div class="text-sm text-white">
              Created {{ formatDate(leaveRequest.created_at) }}
            </div>
          </div>
        </div>

        <!-- Employee Information -->
        <div class="rounded-lg shadow-sm border">
          <div class="p-6">
            <h3 class="text-lg font-medium text-white mb-4">Employee Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <Label class="text-sm font-medium text-gray-500">Employee Name</Label>
                <p class="text-white mt-1">
                  {{ leaveRequest.employee?.first_name }} {{ leaveRequest.employee?.last_name }}
                </p>
              </div>
              <div>
                <Label class="text-sm font-medium text-gray-500">Employee Number</Label>
                <p class="text-white mt-1">{{ leaveRequest.employee?.employee_number }}</p>
              </div>
              <div>
                <Label class="text-sm font-medium text-gray-500">Department</Label>
                <p class="text-white mt-1">{{ leaveRequest.employee?.department?.name || 'N/A' }}</p>
              </div>
              <div>
                <Label class="text-sm font-medium text-gray-500">Position</Label>
                <p class="text-white mt-1">{{ leaveRequest.employee?.position?.title || 'N/A' }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Leave Details -->
        <div class="rounded-lg shadow-sm border">
          <div class="p-6">
            <h3 class="text-lg font-medium text-white mb-4">Leave Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <Label class="text-sm font-medium text-gray-500">Leave Type</Label>
                <div class="flex items-center gap-2 mt-1">
                  <div class="w-4 h-4 rounded-full"
                    :style="{ backgroundColor: leaveRequest.leave_type?.color || '#3B82F6' }"></div>
                  <span class="text-white">{{ leaveRequest.leave_type?.name }}</span>
                </div>
              </div>
              <div>
                <Label class="text-sm font-medium text-gray-500">Leave Duration</Label>
                <p class="text-white mt-1">{{ leaveRequest.leave_duration.replace('_', ' ') }}</p>
              </div>
              <div>
                <Label class="text-sm font-medium text-gray-500">Start Date</Label>
                <p class="text-white mt-1">{{ formatDate(leaveRequest.start_date) }}</p>
              </div>
              <div>
                <Label class="text-sm font-medium text-gray-500">End Date</Label>
                <p class="text-white mt-1">{{ formatDate(leaveRequest.end_date) }}</p>
              </div>
              <div>
                <Label class="text-sm font-medium text-gray-500">Total Days</Label>
                <p class="text-white mt-1">{{ leaveRequest.total_days }} days</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Request Information -->
        <div class="rounded-lg shadow-sm border">
          <div class="p-6">
            <h3 class="text-lg font-medium text-white mb-4">Request Information</h3>
            <div class="space-y-4">
              <div>
                <Label class="text-sm font-medium text-gray-500">Reason</Label>
                <p class="text-white mt-1">{{ leaveRequest.reason }}</p>
              </div>
              <div v-if="leaveRequest.additional_notes">
                <Label class="text-sm font-medium text-gray-500">Additional Notes</Label>
                <p class="text-white mt-1">{{ leaveRequest.additional_notes }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Approval Information -->
        <div v-if="leaveRequest.status !== 'pending'" class="rounded-lg shadow-sm border">
          <div class="p-6">
            <h3 class="text-lg font-medium text-white mb-4">Approval Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <Label class="text-sm font-medium text-gray-500">Approved By</Label>
                <p class="text-white mt-1">{{ leaveRequest.approved_by_user?.name || 'N/A' }}</p>
              </div>
              <div>
                <Label class="text-sm font-medium text-gray-500">Approved At</Label>
                <p class="text-white mt-1">{{ leaveRequest.approved_at ? formatDate(leaveRequest.approved_at) : 'N/A'
                }}</p>
              </div>
              <div v-if="leaveRequest.approval_notes">
                <Label class="text-sm font-medium text-gray-500">Approval Notes</Label>
                <p class="text-white mt-1">{{ leaveRequest.approval_notes }}</p>
              </div>
              <div v-if="leaveRequest.rejection_reason">
                <Label class="text-sm font-medium text-gray-500">Rejection Reason</Label>
                <p class="text-white mt-1">{{ leaveRequest.rejection_reason }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Label } from '@/components/ui/label'
import { ArrowLeft, Edit, Check, X, Calendar, Loader2 } from 'lucide-vue-next'
import { apiService } from '@/services/api'
import type { LeaveRequest } from '@/types/erp'

// Props
interface Props {
  id: number
}

const props = defineProps<Props>()

// Reactive data
const leaveRequest = ref<LeaveRequest | null>(null)
const loading = ref(true)
const approveLoading = ref(false)
const rejectLoading = ref(false)

// Navigation
const navigateBack = () => {
  router.visit('/hr/leave-requests')
}

const navigateToEdit = () => {
  router.visit(`/hr/leave-requests/${props.id}/edit`)
}

// Actions
const approveRequest = async () => {
  if (!leaveRequest.value) return

  const notes = prompt('Please provide approval notes (optional):')

  approveLoading.value = true
  try {
    await apiService.approveLeaveRequest(leaveRequest.value.id, {
      approval_notes: notes || ''
    })
    await fetchLeaveRequest()
  } catch (error) {
    console.error('Error approving leave request:', error)
  } finally {
    approveLoading.value = false
  }
}

const rejectRequest = async () => {
  if (!leaveRequest.value) return

  const reason = prompt('Please provide rejection reason:')
  if (!reason) return

  rejectLoading.value = true
  try {
    await apiService.rejectLeaveRequest(leaveRequest.value.id, {
      rejection_reason: reason
    })
    await fetchLeaveRequest()
  } catch (error) {
    console.error('Error rejecting leave request:', error)
  } finally {
    rejectLoading.value = false
  }
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

// Fetch data
const fetchLeaveRequest = async () => {
  loading.value = true
  try {
    const data = await apiService.getLeaveRequest(props.id)
    leaveRequest.value = data
  } catch (error) {
    console.error('Error fetching leave request:', error)
  } finally {
    loading.value = false
  }
}

// Initialize
onMounted(() => {
  fetchLeaveRequest()
})
</script>
