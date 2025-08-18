<template>
  <AppLayout>
    <div class="container mx-auto p-6">
      <div class="flex justify-between items-center mb-6">
        <div>
          <h1 class="text-3xl font-bold text-white">Leave Types</h1>
          <p class="text-white mt-2">Manage employee leave types and configurations</p>
        </div>
        <Button @click="navigateToCreate" class="flex items-center gap-2">
          <Plus class="w-4 h-4" />
          Add Leave Type
        </Button>
      </div>

      <!-- Search and Filters -->
      <div class="rounded-lg shadow-sm border p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <Label for="search">Search</Label>
            <Input id="search" v-model="search" placeholder="Search leave types..." @input="debouncedSearch" />
          </div>
          <div>
            <Label for="status">Status</Label>
            <Select v-model="filters.is_active" @update:model-value="fetchLeaveTypes">
              <option value="">All Status</option>
              <option value="true">Active</option>
              <option value="false">Inactive</option>
            </Select>
          </div>
          <div>
            <Label for="paid">Paid Status</Label>
            <Select v-model="filters.is_paid" @update:model-value="fetchLeaveTypes">
              <option value="">All Types</option>
              <option value="true">Paid Leave</option>
              <option value="false">Unpaid Leave</option>
            </Select>
          </div>
          <div>
            <Label for="approval">Approval Required</Label>
            <Select v-model="filters.requires_approval" @update:model-value="fetchLeaveTypes">
              <option value="">All</option>
              <option value="true">Requires Approval</option>
              <option value="false">No Approval</option>
            </Select>
          </div>
        </div>
      </div>

      <!-- Leave Types Table -->
      <div class="rounded-lg shadow-sm border">
        <div class="p-6">
          <div v-if="loading" class="flex justify-center items-center py-8">
            <Loader2 class="w-8 h-8 animate-spin text-blue-600" />
          </div>

          <div v-else-if="leaveTypes.length === 0" class="text-center py-8">
            <Calendar class="w-12 h-12 text-gray-400 mx-auto mb-4" />
            <h3 class="text-lg font-medium text-white mb-2">No leave types found</h3>
            <p class="text-white mb-4">Get started by creating your first leave type.</p>
            <Button @click="navigateToCreate">Create Leave Type</Button>
          </div>

          <div v-else>
            <div class="overflow-x-auto">
              <table class="w-full">
                <thead>
                  <tr class="border-b">
                    <th class="text-left py-3 px-4 font-medium text-white">Name</th>
                    <th class="text-left py-3 px-4 font-medium text-white">Days/Year</th>
                    <th class="text-left py-3 px-4 font-medium text-white">Type</th>
                    <th class="text-left py-3 px-4 font-medium text-white">Approval</th>
                    <th class="text-left py-3 px-4 font-medium text-white">Status</th>
                    <th class="text-left py-3 px-4 font-medium text-white">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="leaveType in leaveTypes" :key="leaveType.id" class="border-b">
                    <td class="py-4 px-4">
                      <div class="flex items-center gap-3">
                        <div class="w-4 h-4 rounded-full" :style="{ backgroundColor: leaveType.color || '#3B82F6' }">
                        </div>
                        <div>
                          <div class="font-medium text-white">{{ leaveType.name }}</div>
                          <div v-if="leaveType.description" class="text-sm text-white">
                            {{ leaveType.description }}
                          </div>
                        </div>
                      </div>
                    </td>
                    <td class="py-4 px-4">
                      <span class="font-medium">{{ leaveType.default_days_per_year }} days</span>
                    </td>
                    <td class="py-4 px-4">
                      <Badge :variant="leaveType.is_paid ? 'default' : 'secondary'">
                        {{ leaveType.is_paid ? 'Paid' : 'Unpaid' }}
                      </Badge>
                    </td>
                    <td class="py-4 px-4">
                      <Badge :variant="leaveType.requires_approval ? 'default' : 'secondary'">
                        {{ leaveType.requires_approval ? 'Required' : 'Not Required' }}
                      </Badge>
                    </td>
                    <td class="py-4 px-4">
                      <Badge :variant="leaveType.is_active ? 'default' : 'secondary'">
                        {{ leaveType.is_active ? 'Active' : 'Inactive' }}
                      </Badge>
                    </td>
                    <td class="py-4 px-4">
                      <div class="flex items-center gap-2">
                        <Button variant="ghost" size="sm" @click="navigateToShow(leaveType.id)">
                          <Eye class="w-4 h-4" />
                        </Button>
                        <Button variant="ghost" size="sm" @click="navigateToEdit(leaveType.id)">
                          <Edit class="w-4 h-4" />
                        </Button>
                        <Button variant="ghost" size="sm" @click="toggleStatus(leaveType)"
                          :disabled="toggleLoading === leaveType.id">
                          <Loader2 v-if="toggleLoading === leaveType.id" class="w-4 h-4 animate-spin" />
                          <Power v-else class="w-4 h-4" />
                        </Button>
                        <Button variant="ghost" size="sm" @click="deleteLeaveType(leaveType)"
                          :disabled="deleteLoading === leaveType.id">
                          <Loader2 v-if="deleteLoading === leaveType.id" class="w-4 h-4 animate-spin" />
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
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Badge } from '@/components/ui/badge'
import { DataPagination } from '@/components/ui/pagination'
import { Plus, Eye, Edit, Trash2, Power, Calendar, Loader2 } from 'lucide-vue-next'
import { apiService } from '@/services/api'
import type { LeaveType } from '@/types/erp'
import { useDebounce } from '@/composables/useDebounce'

// Reactive data
const leaveTypes = ref<LeaveType[]>([])
const loading = ref(false)
const toggleLoading = ref<number | null>(null)
const deleteLoading = ref<number | null>(null)
const search = ref('')
const filters = ref({
  is_active: '',
  is_paid: '',
  requires_approval: '',
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
  fetchLeaveTypes()
}, 300)

// Fetch leave types
const fetchLeaveTypes = async () => {
  loading.value = true
  try {
    const params = {
      page: pagination.value.current_page,
      search: search.value,
      ...filters.value,
    }

    const response = await apiService.getLeaveTypes(params)
    leaveTypes.value = response.data
    pagination.value = {
      current_page: response.meta.current_page,
      last_page: response.meta.last_page,
      per_page: response.meta.per_page,
      total: response.meta.total,
    }
  } catch (error) {
    console.error('Error fetching leave types:', error)
  } finally {
    loading.value = false
  }
}

// Navigation methods
const navigateToCreate = () => {
  router.visit('/hr/leave-types/create')
}

const navigateToShow = (id: number) => {
  router.visit(`/hr/leave-types/${id}`)
}

const navigateToEdit = (id: number) => {
  router.visit(`/hr/leave-types/${id}/edit`)
}

// Action methods
const toggleStatus = async (leaveType: LeaveType) => {
  toggleLoading.value = leaveType.id
  try {
    await apiService.toggleLeaveTypeStatus(leaveType.id)
    await fetchLeaveTypes()
  } catch (error) {
    console.error('Error toggling leave type status:', error)
  } finally {
    toggleLoading.value = null
  }
}

const deleteLeaveType = async (leaveType: LeaveType) => {
  if (!confirm(`Are you sure you want to delete "${leaveType.name}"?`)) {
    return
  }

  deleteLoading.value = leaveType.id
  try {
    await apiService.deleteLeaveType(leaveType.id)
    await fetchLeaveTypes()
  } catch (error) {
    console.error('Error deleting leave type:', error)
  } finally {
    deleteLoading.value = null
  }
}

const handlePageChange = (page: number) => {
  pagination.value.current_page = page
  fetchLeaveTypes()
}

// Initialize
onMounted(() => {
  fetchLeaveTypes()
})
</script>
