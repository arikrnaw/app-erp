<template>
  <AppLayout>
    <div class="container mx-auto p-6">
      <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="flex items-center gap-4 mb-6">
          <Button variant="ghost" size="sm" @click="navigateBack">
            <ArrowLeft class="w-4 h-4" />
          </Button>
          <div>
            <h1 class="text-3xl font-bold text-white">New Employee</h1>
            <p class="text-white mt-2">Add a new employee to the system</p>
          </div>
        </div>

        <!-- Form -->
        <div class="rounded-lg shadow-sm border p-6">
          <form @submit.prevent="handleSubmit" class="space-y-8">
            <!-- Basic Information -->
            <div class="space-y-4">
              <h3 class="text-lg font-medium text-white">Basic Information</h3>

              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                  <Label for="employee_number">Employee Number *</Label>
                  <Input id="employee_number" v-model="form.employee_number" placeholder="e.g., EMP-001"
                    :class="{ 'border-red-500': errors.employee_number }" />
                  <p v-if="errors.employee_number" class="text-red-500 text-sm mt-1">{{ errors.employee_number }}</p>
                </div>

                <div>
                  <Label for="first_name">First Name *</Label>
                  <Input id="first_name" v-model="form.first_name" placeholder="John"
                    :class="{ 'border-red-500': errors.first_name }" />
                  <p v-if="errors.first_name" class="text-red-500 text-sm mt-1">{{ errors.first_name }}</p>
                </div>

                <div>
                  <Label for="last_name">Last Name *</Label>
                  <Input id="last_name" v-model="form.last_name" placeholder="Doe"
                    :class="{ 'border-red-500': errors.last_name }" />
                  <p v-if="errors.last_name" class="text-red-500 text-sm mt-1">{{ errors.last_name }}</p>
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <Label for="email">Email *</Label>
                  <Input id="email" v-model="form.email" type="email" placeholder="john.doe@company.com"
                    :class="{ 'border-red-500': errors.email }" />
                  <p v-if="errors.email" class="text-red-500 text-sm mt-1">{{ errors.email }}</p>
                </div>

                <div>
                  <Label for="phone">Phone</Label>
                  <Input id="phone" v-model="form.phone" placeholder="+1234567890"
                    :class="{ 'border-red-500': errors.phone }" />
                  <p v-if="errors.phone" class="text-red-500 text-sm mt-1">{{ errors.phone }}</p>
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                  <Label for="birth_date">Birth Date</Label>
                  <Input id="birth_date" v-model="form.birth_date" type="date"
                    :class="{ 'border-red-500': errors.birth_date }" />
                  <p v-if="errors.birth_date" class="text-red-500 text-sm mt-1">{{ errors.birth_date }}</p>
                </div>

                <div>
                  <Label for="gender">Gender</Label>
                  <Select v-model="form.gender">
                    <option value="">Select Gender</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                    <option value="other">Other</option>
                  </Select>
                </div>

                <div>
                  <Label for="national_id">National ID</Label>
                  <Input id="national_id" v-model="form.national_id" placeholder="123456789" />
                </div>
              </div>

              <div>
                <Label for="address">Address</Label>
                <Textarea id="address" v-model="form.address" placeholder="Enter full address..." rows="3" />
              </div>
            </div>

            <!-- Employment Information -->
            <div class="space-y-4">
              <h3 class="text-lg font-medium text-white">Employment Information</h3>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <Label for="department_id">Department *</Label>
                  <Select v-model="form.department_id" :class="{ 'border-red-500': errors.department_id }">
                    <option value="">Select Department</option>
                    <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                      {{ dept.name }}
                    </option>
                  </Select>
                  <p v-if="errors.department_id" class="text-red-500 text-sm mt-1">{{ errors.department_id }}</p>
                </div>

                <div>
                  <Label for="position_id">Position *</Label>
                  <Select v-model="form.position_id" :class="{ 'border-red-500': errors.position_id }">
                    <option value="">Select Position</option>
                    <option v-for="pos in positions" :key="pos.id" :value="pos.id">
                      {{ pos.title }}
                    </option>
                  </Select>
                  <p v-if="errors.position_id" class="text-red-500 text-sm mt-1">{{ errors.position_id }}</p>
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                  <Label for="hire_date">Hire Date *</Label>
                  <Input id="hire_date" v-model="form.hire_date" type="date"
                    :class="{ 'border-red-500': errors.hire_date }" />
                  <p v-if="errors.hire_date" class="text-red-500 text-sm mt-1">{{ errors.hire_date }}</p>
                </div>

                <div>
                  <Label for="employment_type">Employment Type *</Label>
                  <Select v-model="form.employment_type" :class="{ 'border-red-500': errors.employment_type }">
                    <option value="">Select Type</option>
                    <option value="full_time">Full Time</option>
                    <option value="part_time">Part Time</option>
                    <option value="contract">Contract</option>
                    <option value="internship">Internship</option>
                  </Select>
                  <p v-if="errors.employment_type" class="text-red-500 text-sm mt-1">{{ errors.employment_type }}</p>
                </div>

                <div>
                  <Label for="base_salary">Base Salary</Label>
                  <Input id="base_salary" v-model.number="form.base_salary" type="number" step="0.01"
                    placeholder="5000.00" />
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <Label for="contract_start_date">Contract Start Date</Label>
                  <Input id="contract_start_date" v-model="form.contract_start_date" type="date" />
                </div>

                <div>
                  <Label for="contract_end_date">Contract End Date</Label>
                  <Input id="contract_end_date" v-model="form.contract_end_date" type="date"
                    :min="form.contract_start_date" />
                </div>
              </div>
            </div>

            <!-- Work Schedule -->
            <div class="space-y-4">
              <h3 class="text-lg font-medium text-white">Work Schedule</h3>

              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                  <Label for="work_start_time">Work Start Time</Label>
                  <Input id="work_start_time" v-model="form.work_start_time" type="time" />
                </div>

                <div>
                  <Label for="work_end_time">Work End Time</Label>
                  <Input id="work_end_time" v-model="form.work_end_time" type="time" />
                </div>

                <div>
                  <Label for="working_hours_per_day">Working Hours/Day</Label>
                  <Input id="working_hours_per_day" v-model.number="form.working_hours_per_day" type="number" step="0.5"
                    placeholder="8" />
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <Label for="working_days_per_week">Working Days/Week</Label>
                  <Input id="working_days_per_week" v-model.number="form.working_days_per_week" type="number" min="1"
                    max="7" placeholder="5" />
                </div>

                <div>
                  <Label for="work_location">Work Location</Label>
                  <Input id="work_location" v-model="form.work_location" placeholder="Office, Remote, Hybrid" />
                </div>
              </div>
            </div>

            <!-- Emergency Contact -->
            <div class="space-y-4">
              <h3 class="text-lg font-medium text-white">Emergency Contact</h3>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <Label for="emergency_contact_name">Emergency Contact Name</Label>
                  <Input id="emergency_contact_name" v-model="form.emergency_contact_name"
                    placeholder="Emergency contact full name" />
                </div>

                <div>
                  <Label for="emergency_contact_phone">Emergency Contact Phone</Label>
                  <Input id="emergency_contact_phone" v-model="form.emergency_contact_phone"
                    placeholder="+1234567890" />
                </div>
              </div>

              <div>
                <Label for="emergency_contact_relationship">Relationship</Label>
                <Input id="emergency_contact_relationship" v-model="form.emergency_contact_relationship"
                  placeholder="Spouse, Parent, Sibling, etc." />
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
                {{ submitting ? 'Creating...' : 'Create Employee' }}
              </Button>
            </div>
          </form>
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
import { Textarea } from '@/components/ui/textarea'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { ArrowLeft, Save, Loader2 } from 'lucide-vue-next'
import { apiService } from '@/services/api'
import type { EmployeeForm, Department, Position } from '@/types/erp'

// Form data
const form = ref<EmployeeForm>({
  employee_number: '',
  first_name: '',
  last_name: '',
  email: '',
  phone: '',
  birth_date: '',
  gender: '',
  national_id: '',
  address: '',
  department_id: 0,
  position_id: 0,
  hire_date: '',
  employment_type: 'full_time',
  base_salary: 0,
  contract_start_date: '',
  contract_end_date: '',
  work_start_time: '',
  work_end_time: '',
  working_hours_per_day: 8,
  working_days_per_week: 5,
  work_location: '',
  emergency_contact_name: '',
  emergency_contact_phone: '',
  emergency_contact_relationship: '',
})

// Form state
const submitting = ref(false)
const errors = ref<Record<string, string>>({})

// Data for dropdowns
const departments = ref<Department[]>([])
const positions = ref<Position[]>([])

// Navigation
const navigateBack = () => {
  router.visit('/hr/employees')
}

// Form submission
const handleSubmit = async () => {
  submitting.value = true
  errors.value = {}

  try {
    // Basic validation
    if (!form.value.employee_number.trim()) {
      errors.value.employee_number = 'Employee number is required'
    }
    if (!form.value.first_name.trim()) {
      errors.value.first_name = 'First name is required'
    }
    if (!form.value.last_name.trim()) {
      errors.value.last_name = 'Last name is required'
    }
    if (!form.value.email.trim()) {
      errors.value.email = 'Email is required'
    }
    if (!form.value.department_id) {
      errors.value.department_id = 'Department is required'
    }
    if (!form.value.position_id) {
      errors.value.position_id = 'Position is required'
    }
    if (!form.value.hire_date) {
      errors.value.hire_date = 'Hire date is required'
    }
    if (!form.value.employment_type) {
      errors.value.employment_type = 'Employment type is required'
    }

    if (Object.keys(errors.value).length > 0) {
      submitting.value = false
      return
    }

    // Submit form
    await apiService.createEmployee(form.value)

    // Navigate back to index
    router.visit('/hr/employees', {
      onSuccess: () => {
        // Show success message if needed
      }
    })
  } catch (error: any) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    } else {
      console.error('Error creating employee:', error)
    }
  } finally {
    submitting.value = false
  }
}

// Fetch departments and positions
const fetchDepartments = async () => {
  try {
    const response = await apiService.getDepartments()
    departments.value = response.data
  } catch (error) {
    console.error('Error fetching departments:', error)
  }
}

const fetchPositions = async () => {
  try {
    const response = await apiService.getPositions()
    positions.value = response.data
  } catch (error) {
    console.error('Error fetching positions:', error)
  }
}

// Initialize
onMounted(() => {
  fetchDepartments()
  fetchPositions()
})
</script>
