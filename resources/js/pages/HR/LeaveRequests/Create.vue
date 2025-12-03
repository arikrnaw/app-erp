<template>
  <AppLayout>
    <div class="container mx-auto p-6">
      <div class="max-w-2xl mx-auto">
        <!-- Header -->
        <div class="flex items-center gap-4 mb-6">
          <Button variant="ghost" size="sm" @click="navigateBack">
            <ArrowLeft class="w-4 h-4" />
          </Button>
          <div>
            <h1 class="text-3xl font-bold text-white">New Leave Request</h1>
            <p class="text-white mt-2">Submit a new leave request for an employee</p>
          </div>
        </div>

        <!-- Form -->
        <div class="rounded-lg shadow-sm border p-6">
          <form @submit.prevent="handleSubmit" class="space-y-6">
            <!-- Employee Selection -->
            <div class="space-y-4">
              <h3 class="text-lg font-medium text-white">Employee Information</h3>

              <div>
                <Label for="employee">Employee *</Label>
                <Select v-model="form.employee_id" @update:model-value="onEmployeeChange">
                  <option value="">Select Employee</option>
                  <option v-for="employee in employees" :key="employee.id" :value="employee.id">
                    {{ employee.first_name }} {{ employee.last_name }} ({{ employee.employee_number }})
                  </option>
                </Select>
                <p v-if="errors.employee_id" class="text-red-500 text-sm mt-1">{{ errors.employee_id }}</p>
              </div>

              <div v-if="selectedEmployee" class="bg-gray-50 p-4 rounded-lg">
                <div class="grid grid-cols-2 gap-4 text-sm">
                  <div>
                    <span class="font-medium text-gray-700">Department:</span>
                    <span class="ml-2 text-white">{{ selectedEmployee.department?.name || 'N/A' }}</span>
                  </div>
                  <div>
                    <span class="font-medium text-gray-700">Position:</span>
                    <span class="ml-2 text-white">{{ selectedEmployee.position?.title || 'N/A' }}</span>
                  </div>
                  <div>
                    <span class="font-medium text-gray-700">Hire Date:</span>
                    <span class="ml-2 text-white">{{ formatDate(selectedEmployee.hire_date) }}</span>
                  </div>
                  <div>
                    <span class="font-medium text-gray-700">Employment Type:</span>
                    <span class="ml-2 text-white">{{ selectedEmployee.employment_type }}</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Leave Details -->
            <div class="space-y-4">
              <h3 class="text-lg font-medium text-white">Leave Details</h3>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <Label for="leave_type">Leave Type *</Label>
                  <Select v-model="form.leave_type_id" @update:model-value="onLeaveTypeChange">
                    <option value="">Select Leave Type</option>
                    <option v-for="type in leaveTypes" :key="type.id" :value="type.id">
                      {{ type.name }} ({{ type.default_days_per_year }} days/year)
                    </option>
                  </Select>
                  <p v-if="errors.leave_type_id" class="text-red-500 text-sm mt-1">{{ errors.leave_type_id }}</p>
                </div>

                <div>
                  <Label for="leave_duration">Leave Duration *</Label>
                  <Select v-model="form.leave_duration">
                    <option value="full_day">Full Day</option>
                    <option value="half_day">Half Day</option>
                    <option value="hours">Hours</option>
                  </Select>
                  <p v-if="errors.leave_duration" class="text-red-500 text-sm mt-1">{{ errors.leave_duration }}</p>
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <Label for="start_date">Start Date *</Label>
                  <Input id="start_date" v-model="form.start_date" type="date" :min="today" @change="calculateDays"
                    :class="{ 'border-red-500': errors.start_date }" />
                  <p v-if="errors.start_date" class="text-red-500 text-sm mt-1">{{ errors.start_date }}</p>
                </div>

                <div>
                  <Label for="end_date">End Date *</Label>
                  <Input id="end_date" v-model="form.end_date" type="date" :min="form.start_date"
                    @change="calculateDays" :class="{ 'border-red-500': errors.end_date }" />
                  <p v-if="errors.end_date" class="text-red-500 text-sm mt-1">{{ errors.end_date }}</p>
                </div>
              </div>

              <div v-if="form.leave_duration === 'hours'" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <Label for="start_time">Start Time</Label>
                  <Input id="start_time" v-model="form.start_time" type="time" />
                </div>

                <div>
                  <Label for="end_time">End Time</Label>
                  <Input id="end_time" v-model="form.end_time" type="time" />
                </div>
              </div>

              <div v-if="selectedLeaveType" class="bg-blue-50 p-4 rounded-lg">
                <div class="grid grid-cols-2 gap-4 text-sm">
                  <div>
                    <span class="font-medium text-gray-700">Available Days:</span>
                    <span class="ml-2 text-white">{{ selectedLeaveType.default_days_per_year }} days/year</span>
                  </div>
                  <div>
                    <span class="font-medium text-gray-700">Type:</span>
                    <span class="ml-2 text-white">{{ selectedLeaveType.is_paid ? 'Paid' : 'Unpaid' }}</span>
                  </div>
                  <div>
                    <span class="font-medium text-gray-700">Approval Required:</span>
                    <span class="ml-2 text-white">{{ selectedLeaveType.requires_approval ? 'Yes' : 'No' }}</span>
                  </div>
                  <div>
                    <span class="font-medium text-gray-700">Document Required:</span>
                    <span class="ml-2 text-white">{{ selectedLeaveType.requires_document ? 'Yes' : 'No' }}</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Request Details -->
            <div class="space-y-4">
              <h3 class="text-lg font-medium text-white">Request Details</h3>

              <div>
                <Label for="reason">Reason *</Label>
                <Textarea id="reason" v-model="form.reason" placeholder="Please provide the reason for leave..."
                  rows="3" :class="{ 'border-red-500': errors.reason }" />
                <p v-if="errors.reason" class="text-red-500 text-sm mt-1">{{ errors.reason }}</p>
              </div>

              <div>
                <Label for="additional_notes">Additional Notes</Label>
                <Textarea id="additional_notes" v-model="form.additional_notes"
                  placeholder="Any additional information..." rows="2" />
              </div>

              <div class="flex items-center space-x-2">
                <Checkbox id="is_urgent" v-model:checked="form.is_urgent" />
                <Label for="is_urgent">Mark as Urgent</Label>
              </div>
            </div>

            <!-- Summary -->
            <div v-if="form.start_date && form.end_date" class="bg-gray-50 p-4 rounded-lg">
              <h4 class="font-medium text-white mb-2">Request Summary</h4>
              <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                  <span class="font-medium text-gray-700">Total Days:</span>
                  <span class="ml-2 text-white">{{ calculatedDays }} days</span>
                </div>
                <div>
                  <span class="font-medium text-gray-700">Duration:</span>
                  <span class="ml-2 text-white">{{ form.leave_duration.replace('_', ' ') }}</span>
                </div>
              </div>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end gap-4 pt-6 border-t">
              <Button type="button" variant="outline" @click="navigateBack" :disabled="submitting">
                Cancel
              </Button>
              <Button type="submit" :disabled="submitting" class="flex items-center gap-2">
                <Loader2 v-if="submitting" class="w-4 h-4 animate-spin" />
                <Save v-else class="w-4 h-4" />
                {{ submitting ? 'Submitting...' : 'Submit Request' }}
              </Button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Checkbox } from '@/components/ui/checkbox'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { ArrowLeft, Save, Loader2 } from 'lucide-vue-next'
import { apiService } from '@/services/api'
import type { LeaveRequestForm, Employee, LeaveType } from '@/types/erp'

// Reactive data
const employees = ref<Employee[]>([])
const leaveTypes = ref<LeaveType[]>([])
const selectedEmployee = ref<Employee | null>(null)
const selectedLeaveType = ref<LeaveType | null>(null)
const submitting = ref(false)
const errors = ref<Record<string, string>>({})

// Form data
const form = ref<LeaveRequestForm>({
  employee_id: 0,
  leave_type_id: 0,
  start_date: '',
  end_date: '',
  start_time: '',
  end_time: '',
  leave_duration: 'full_day',
  reason: '',
  additional_notes: '',
  attachment_file: '',
  is_urgent: false,
})

// Computed properties
const today = computed(() => {
  return new Date().toISOString().split('T')[0]
})

const calculatedDays = computed(() => {
  if (!form.value.start_date || !form.value.end_date) return 0

  const start = new Date(form.value.start_date)
  const end = new Date(form.value.end_date)
  const diffTime = Math.abs(end.getTime() - start.getTime())
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))

  return diffDays + 1 // Include both start and end dates
})

// Navigation
const navigateBack = () => {
  router.visit('/hr/leave-requests')
}

// Event handlers
const onEmployeeChange = () => {
  selectedEmployee.value = employees.value.find(emp => emp.id === form.value.employee_id) || null
}

const onLeaveTypeChange = () => {
  selectedLeaveType.value = leaveTypes.value.find(type => type.id === form.value.leave_type_id) || null
}

const calculateDays = () => {
  // This will trigger the computed property
}

// Form submission
const handleSubmit = async () => {
  submitting.value = true
  errors.value = {}

  try {
    // Basic validation
    if (!form.value.employee_id) {
      errors.value.employee_id = 'Employee is required'
    }
    if (!form.value.leave_type_id) {
      errors.value.leave_type_id = 'Leave type is required'
    }
    if (!form.value.start_date) {
      errors.value.start_date = 'Start date is required'
    }
    if (!form.value.end_date) {
      errors.value.end_date = 'End date is required'
    }
    if (!form.value.reason.trim()) {
      errors.value.reason = 'Reason is required'
    }

    if (Object.keys(errors.value).length > 0) {
      submitting.value = false
      return
    }

    // Submit form
    await apiService.createLeaveRequest(form.value)

    // Navigate back to index
    router.visit('/hr/leave-requests', {
      onSuccess: () => {
        // Show success message if needed
      }
    })
  } catch (error: any) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    } else {
      console.error('Error creating leave request:', error)
    }
  } finally {
    submitting.value = false
  }
}

// Fetch data
const fetchEmployees = async () => {
  try {
    const response = await apiService.getActiveEmployees()
    employees.value = response
  } catch (error) {
    console.error('Error fetching employees:', error)
  }
}

const fetchLeaveTypes = async () => {
  try {
    const response = await apiService.getActiveLeaveTypes()
    leaveTypes.value = response
  } catch (error) {
    console.error('Error fetching leave types:', error)
  }
}

// Helper functions
const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString()
}

// Initialize
onMounted(() => {
  fetchEmployees()
  fetchLeaveTypes()
})
</script>
