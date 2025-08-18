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
            <h1 class="text-3xl font-bold text-white">{{ employee?.first_name }} {{ employee?.last_name }}</h1>
            <p class="text-white mt-2">Employee details and information</p>
          </div>
          <div class="flex items-center gap-2">
            <Button variant="outline" @click="navigateToEdit">
              <Edit class="w-4 h-4 mr-2" />
              Edit
            </Button>
          </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="flex justify-center items-center py-12">
          <Loader2 class="w-8 h-8 animate-spin text-blue-600" />
        </div>

        <!-- Not Found State -->
        <div v-else-if="!employee" class="text-center py-12">
          <Users class="w-12 h-12 text-gray-400 mx-auto mb-4" />
          <h3 class="text-lg font-medium text-white mb-2">Employee not found</h3>
          <p class="text-white mb-4">The employee you're looking for doesn't exist.</p>
          <Button @click="navigateBack">Back to Employees</Button>
        </div>

        <!-- Content -->
        <div v-else class="space-y-6">
          <!-- Status Banner -->
          <div class="rounded-lg shadow-sm border p-4">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <Badge :variant="getStatusVariant(employee.status)" class="text-sm">
                  {{ employee.status.toUpperCase() }}
                </Badge>
                <span class="text-sm text-white">{{ employee.employee_number }}</span>
              </div>
              <div class="text-sm text-white">
                Created {{ formatDate(employee.created_at) }}
              </div>
            </div>
          </div>

          <!-- Basic Information -->
          <div class="rounded-lg shadow-sm border">
            <div class="p-6">
              <h3 class="text-lg font-medium text-white mb-4">Basic Information</h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <Label class="text-sm font-medium text-gray-500">Full Name</Label>
                  <p class="text-white mt-1">{{ employee.first_name }} {{ employee.last_name }}</p>
                </div>
                <div>
                  <Label class="text-sm font-medium text-gray-500">Employee Number</Label>
                  <p class="text-white mt-1">{{ employee.employee_number }}</p>
                </div>
                <div>
                  <Label class="text-sm font-medium text-gray-500">Email</Label>
                  <p class="text-white mt-1">{{ employee.email }}</p>
                </div>
                <div>
                  <Label class="text-sm font-medium text-gray-500">Phone</Label>
                  <p class="text-white mt-1">{{ employee.phone || 'N/A' }}</p>
                </div>
                <div>
                  <Label class="text-sm font-medium text-gray-500">Birth Date</Label>
                  <p class="text-white mt-1">{{ employee.birth_date ? formatDate(employee.birth_date) : 'N/A' }}</p>
                </div>
                <div>
                  <Label class="text-sm font-medium text-gray-500">Gender</Label>
                  <p class="text-white mt-1">{{ employee.gender || 'N/A' }}</p>
                </div>
                <div>
                  <Label class="text-sm font-medium text-gray-500">National ID</Label>
                  <p class="text-white mt-1">{{ employee.national_id || 'N/A' }}</p>
                </div>
                <div>
                  <Label class="text-sm font-medium text-gray-500">Address</Label>
                  <p class="text-white mt-1">{{ employee.address || 'N/A' }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Employment Information -->
          <div class="rounded-lg shadow-sm border">
            <div class="p-6">
              <h3 class="text-lg font-medium text-white mb-4">Employment Information</h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <Label class="text-sm font-medium text-gray-500">Department</Label>
                  <p class="text-white mt-1">{{ employee.department?.name || 'N/A' }}</p>
                </div>
                <div>
                  <Label class="text-sm font-medium text-gray-500">Position</Label>
                  <p class="text-white mt-1">{{ employee.position?.title || 'N/A' }}</p>
                </div>
                <div>
                  <Label class="text-sm font-medium text-gray-500">Hire Date</Label>
                  <p class="text-white mt-1">{{ formatDate(employee.hire_date) }}</p>
                </div>
                <div>
                  <Label class="text-sm font-medium text-gray-500">Employment Type</Label>
                  <Badge variant="outline" class="mt-1">
                    {{ employee.employment_type?.replace('_', ' ') || 'N/A' }}
                  </Badge>
                </div>
                <div>
                  <Label class="text-sm font-medium text-gray-500">Base Salary</Label>
                  <p class="text-white mt-1">{{ employee.base_salary ? formatCurrency(employee.base_salary) : 'N/A'
                  }}</p>
                </div>
                <div>
                  <Label class="text-sm font-medium text-gray-500">Status</Label>
                  <Badge :variant="getStatusVariant(employee.status)" class="mt-1">
                    {{ employee.status }}
                  </Badge>
                </div>
                <div v-if="employee.contract_start_date">
                  <Label class="text-sm font-medium text-gray-500">Contract Start Date</Label>
                  <p class="text-white mt-1">{{ formatDate(employee.contract_start_date) }}</p>
                </div>
                <div v-if="employee.contract_end_date">
                  <Label class="text-sm font-medium text-gray-500">Contract End Date</Label>
                  <p class="text-white mt-1">{{ formatDate(employee.contract_end_date) }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Work Schedule -->
          <div class="rounded-lg shadow-sm border">
            <div class="p-6">
              <h3 class="text-lg font-medium text-white mb-4">Work Schedule</h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <Label class="text-sm font-medium text-gray-500">Work Start Time</Label>
                  <p class="text-white mt-1">{{ employee.work_start_time || 'N/A' }}</p>
                </div>
                <div>
                  <Label class="text-sm font-medium text-gray-500">Work End Time</Label>
                  <p class="text-white mt-1">{{ employee.work_end_time || 'N/A' }}</p>
                </div>
                <div>
                  <Label class="text-sm font-medium text-gray-500">Working Hours/Day</Label>
                  <p class="text-white mt-1">{{ employee.working_hours_per_day || 'N/A' }} hours</p>
                </div>
                <div>
                  <Label class="text-sm font-medium text-gray-500">Working Days/Week</Label>
                  <p class="text-white mt-1">{{ employee.working_days_per_week || 'N/A' }} days</p>
                </div>
                <div>
                  <Label class="text-sm font-medium text-gray-500">Work Location</Label>
                  <p class="text-white mt-1">{{ employee.work_location || 'N/A' }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Emergency Contact -->
          <div v-if="employee.emergency_contact_name" class="rounded-lg shadow-sm border">
            <div class="p-6">
              <h3 class="text-lg font-medium text-white mb-4">Emergency Contact</h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <Label class="text-sm font-medium text-gray-500">Contact Name</Label>
                  <p class="text-white mt-1">{{ employee.emergency_contact_name }}</p>
                </div>
                <div>
                  <Label class="text-sm font-medium text-gray-500">Contact Phone</Label>
                  <p class="text-white mt-1">{{ employee.emergency_contact_phone || 'N/A' }}</p>
                </div>
                <div>
                  <Label class="text-sm font-medium text-gray-500">Relationship</Label>
                  <p class="text-white mt-1">{{ employee.emergency_contact_relationship || 'N/A' }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Statistics -->
          <div class="rounded-lg shadow-sm border">
            <div class="p-6">
              <h3 class="text-lg font-medium text-white mb-4">Employment Statistics</h3>
              <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="text-center">
                  <div class="text-2xl font-bold text-blue-600">{{ calculateYearsOfService }} years</div>
                  <div class="text-sm text-white">Years of Service</div>
                </div>
                <div class="text-center">
                  <div class="text-2xl font-bold text-green-600">{{ employee.working_hours_per_day || 0 }}h</div>
                  <div class="text-sm text-white">Daily Hours</div>
                </div>
                <div class="text-center">
                  <div class="text-2xl font-bold text-purple-600">{{ employee.working_days_per_week || 0 }}d</div>
                  <div class="text-sm text-white">Weekly Days</div>
                </div>
                <div class="text-center">
                  <div class="text-2xl font-bold text-orange-600">{{ formatCurrency(employee.base_salary || 0) }}</div>
                  <div class="text-sm text-white">Base Salary</div>
                </div>
              </div>
            </div>
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
import { Badge } from '@/components/ui/badge'
import { Label } from '@/components/ui/label'
import { ArrowLeft, Edit, Loader2, Users } from 'lucide-vue-next'
import { apiService } from '@/services/api'
import type { Employee } from '@/types/erp'

// Props
interface Props {
  id: number
}

const props = defineProps<Props>()

// Reactive data
const employee = ref<Employee | null>(null)
const loading = ref(true)

// Computed properties
const calculateYearsOfService = computed(() => {
  if (!employee.value?.hire_date) return 0

  const hireDate = new Date(employee.value.hire_date)
  const today = new Date()
  const diffTime = Math.abs(today.getTime() - hireDate.getTime())
  const diffYears = Math.ceil(diffTime / (1000 * 60 * 60 * 24 * 365))

  return diffYears
})

// Navigation
const navigateBack = () => {
  router.visit('/hr/employees')
}

const navigateToEdit = () => {
  router.visit(`/hr/employees/${props.id}/edit`)
}

// Helper functions
const getStatusVariant = (status: string) => {
  switch (status) {
    case 'active': return 'default'
    case 'inactive': return 'secondary'
    case 'terminated': return 'destructive'
    default: return 'secondary'
  }
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString()
}

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
  }).format(amount)
}

// Fetch data
const fetchEmployee = async () => {
  loading.value = true
  try {
    const data = await apiService.getEmployee(props.id)
    employee.value = data
  } catch (error) {
    console.error('Error fetching employee:', error)
  } finally {
    loading.value = false
  }
}

// Initialize
onMounted(() => {
  fetchEmployee()
})
</script>
