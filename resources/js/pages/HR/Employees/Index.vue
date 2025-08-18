<template>
  <AppLayout>
    <div class="container mx-auto p-6">
      <div class="flex justify-between items-center mb-6">
        <div>
          <h1 class="text-3xl font-bold text-white">Employees</h1>
          <p class="text-white mt-2">Manage employee information and records</p>
        </div>
        <Button @click="navigateToCreate" class="flex items-center gap-2">
          <Plus class="w-4 h-4" />
          New Employee
        </Button>
      </div>

      <!-- Search and Filters -->
      <div class="rounded-lg shadow-sm border p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
          <div>
            <Label for="search">Search</Label>
            <Input id="search" v-model="search" placeholder="Search employees..." @input="debouncedSearch" />
          </div>
          <div>
            <Label for="status">Status</Label>
            <Select v-model="filters.status" @update:model-value="fetchEmployees">
              <option value="">All Status</option>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
              <option value="terminated">Terminated</option>
            </Select>
          </div>
          <div>
            <Label for="department">Department</Label>
            <Select v-model="filters.department_id" @update:model-value="fetchEmployees">
              <option value="">All Departments</option>
              <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                {{ dept.name }}
              </option>
            </Select>
          </div>
          <div>
            <Label for="position">Position</Label>
            <Select v-model="filters.position_id" @update:model-value="fetchEmployees">
              <option value="">All Positions</option>
              <option v-for="pos in positions" :key="pos.id" :value="pos.id">
                {{ pos.title }}
              </option>
            </Select>
          </div>
          <div>
            <Label for="employment_type">Employment Type</Label>
            <Select v-model="filters.employment_type" @update:model-value="fetchEmployees">
              <option value="">All Types</option>
              <option value="full_time">Full Time</option>
              <option value="part_time">Part Time</option>
              <option value="contract">Contract</option>
              <option value="internship">Internship</option>
            </Select>
          </div>
        </div>
      </div>

      <!-- Employees Table -->
      <div class="rounded-lg shadow-sm border">
        <div class="p-6">
          <div v-if="loading" class="flex justify-center items-center py-8">
            <Loader2 class="w-8 h-8 animate-spin text-blue-600" />
          </div>

          <div v-else-if="employees.length === 0" class="text-center py-8">
            <Users class="w-12 h-12 text-gray-400 mx-auto mb-4" />
            <h3 class="text-lg font-medium text-white mb-2">No employees found</h3>
            <p class="text-white mb-4">Get started by adding your first employee.</p>
            <Button @click="navigateToCreate">Add Employee</Button>
          </div>

          <div v-else>
            <div class="overflow-x-auto">
              <table class="w-full">
                <thead>
                  <tr class="border-b">
                    <th class="text-left py-3 px-4 font-medium text-white">Employee</th>
                    <th class="text-left py-3 px-4 font-medium text-white">Department</th>
                    <th class="text-left py-3 px-4 font-medium text-white">Position</th>
                    <th class="text-left py-3 px-4 font-medium text-white">Employment Type</th>
                    <th class="text-left py-3 px-4 font-medium text-white">Hire Date</th>
                    <th class="text-left py-3 px-4 font-medium text-white">Status</th>
                    <th class="text-left py-3 px-4 font-medium text-white">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="employee in employees" :key="employee.id" class="border-b">
                    <td class="py-4 px-4">
                      <div>
                        <div class="font-medium text-white">
                          {{ employee.first_name }} {{ employee.last_name }}
                        </div>
                        <div class="text-sm text-white">{{ employee.employee_number }}</div>
                        <div class="text-sm text-gray-500">{{ employee.email }}</div>
                      </div>
                    </td>
                    <td class="py-4 px-4">
                      <span class="font-medium">{{ employee.department?.name || 'N/A' }}</span>
                    </td>
                    <td class="py-4 px-4">
                      <span class="font-medium">{{ employee.position?.title || 'N/A' }}</span>
                    </td>
                    <td class="py-4 px-4">
                      <Badge variant="outline">
                        {{ employee.employment_type?.replace('_', ' ') || 'N/A' }}
                      </Badge>
                    </td>
                    <td class="py-4 px-4">
                      <span class="font-medium">{{ formatDate(employee.hire_date) }}</span>
                    </td>
                    <td class="py-4 px-4">
                      <Badge :variant="getStatusVariant(employee.status)">
                        {{ employee.status }}
                      </Badge>
                    </td>
                    <td class="py-4 px-4">
                      <div class="flex items-center gap-2">
                        <Button variant="ghost" size="sm" @click="navigateToShow(employee.id)">
                          <Eye class="w-4 h-4" />
                        </Button>
                        <Button variant="ghost" size="sm" @click="navigateToEdit(employee.id)">
                          <Edit class="w-4 h-4" />
                        </Button>
                        <Button variant="ghost" size="sm" @click="toggleEmployeeStatus(employee)"
                          :disabled="toggleLoading === employee.id">
                          <Loader2 v-if="toggleLoading === employee.id" class="w-4 h-4 animate-spin" />
                          <Power v-else class="w-4 h-4" />
                        </Button>
                        <Button variant="ghost" size="sm" @click="deleteEmployee(employee)"
                          :disabled="deleteLoading === employee.id">
                          <Loader2 v-if="deleteLoading === employee.id" class="w-4 h-4 animate-spin" />
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
import { Plus, Eye, Edit, Trash2, Power, Loader2, Users } from 'lucide-vue-next'
import { apiService } from '@/services/api'
import type { Employee, Department, Position } from '@/types/erp'
import { useDebounce } from '@/composables/useDebounce'

// Reactive data
const employees = ref<Employee[]>([])
const departments = ref<Department[]>([])
const positions = ref<Position[]>([])
const loading = ref(false)
const toggleLoading = ref<number | null>(null)
const deleteLoading = ref<number | null>(null)
const search = ref('')
const filters = ref({
  status: '',
  department_id: '',
  position_id: '',
  employment_type: '',
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
  fetchEmployees()
}, 300)

// Methods
const fetchEmployees = async () => {
  try {
    loading.value = true
    const params = {
      page: pagination.value.current_page,
      search: search.value,
      ...filters.value
    }

    const response = await apiService.getEmployees(params)
    employees.value = response.data
    pagination.value = response.pagination
  } catch (error) {
    console.error('Error fetching employees:', error)
  } finally {
    loading.value = false
  }
}

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

const handlePageChange = (page: number) => {
  pagination.value.current_page = page
  fetchEmployees()
}

const navigateToCreate = () => {
  router.visit('/hr/employees/create')
}

const navigateToShow = (id: number) => {
  router.visit(`/hr/employees/${id}`)
}

const navigateToEdit = (id: number) => {
  router.visit(`/hr/employees/${id}/edit`)
}

const toggleEmployeeStatus = async (employee: Employee) => {
  try {
    toggleLoading.value = employee.id
    await apiService.toggleEmployeeStatus(employee.id)
    await fetchEmployees()
  } catch (error) {
    console.error('Error toggling employee status:', error)
  } finally {
    toggleLoading.value = null
  }
}

const deleteEmployee = async (employee: Employee) => {
  if (!confirm(`Are you sure you want to delete ${employee.first_name} ${employee.last_name}?`)) {
    return
  }

  try {
    deleteLoading.value = employee.id
    await apiService.deleteEmployee(employee.id)
    await fetchEmployees()
  } catch (error) {
    console.error('Error deleting employee:', error)
  } finally {
    deleteLoading.value = null
  }
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString()
}

const getStatusVariant = (status: string) => {
  switch (status) {
    case 'active':
      return 'default'
    case 'inactive':
      return 'secondary'
    case 'terminated':
      return 'destructive'
    default:
      return 'outline'
  }
}

// Lifecycle
onMounted(() => {
  fetchEmployees()
  fetchDepartments()
  fetchPositions()
})
</script>
