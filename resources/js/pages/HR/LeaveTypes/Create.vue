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
            <h1 class="text-3xl font-bold text-white">Create Leave Type</h1>
            <p class="text-white mt-2">Add a new leave type for employees</p>
          </div>
        </div>

        <!-- Form -->
        <div class="rounded-lg shadow-sm border p-6">
          <form @submit.prevent="handleSubmit" class="space-y-6">
            <!-- Basic Information -->
            <div class="space-y-4">
              <h3 class="text-lg font-medium text-white">Basic Information</h3>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <Label for="name">Name *</Label>
                  <Input id="name" v-model="form.name" placeholder="e.g., Annual Leave, Sick Leave"
                    :class="{ 'border-red-500': errors.name }" />
                  <p v-if="errors.name" class="text-red-500 text-sm mt-1">{{ errors.name }}</p>
                </div>

                <div>
                  <Label for="color">Color</Label>
                  <div class="flex items-center gap-2">
                    <Input id="color" v-model="form.color" type="color" class="w-16 h-10 p-1" />
                    <Input v-model="form.color" placeholder="#3B82F6" class="flex-1" />
                  </div>
                </div>
              </div>

              <div>
                <Label for="description">Description</Label>
                <Textarea id="description" v-model="form.description" placeholder="Describe this leave type..."
                  rows="3" />
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <Label for="default_days">Default Days per Year *</Label>
                  <Input id="default_days" v-model.number="form.default_days_per_year" type="number" min="0"
                    placeholder="21" :class="{ 'border-red-500': errors.default_days_per_year }" />
                  <p v-if="errors.default_days_per_year" class="text-red-500 text-sm mt-1">
                    {{ errors.default_days_per_year }}
                  </p>
                </div>

                <div>
                  <Label for="sort_order">Sort Order</Label>
                  <Input id="sort_order" v-model.number="form.sort_order" type="number" min="0" placeholder="0" />
                </div>
              </div>
            </div>

            <!-- Configuration -->
            <div class="space-y-4">
              <h3 class="text-lg font-medium text-white">Configuration</h3>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex items-center space-x-2">
                  <Checkbox id="is_paid" v-model:checked="form.is_paid" />
                  <Label for="is_paid">Paid Leave</Label>
                </div>

                <div class="flex items-center space-x-2">
                  <Checkbox id="requires_approval" v-model:checked="form.requires_approval" />
                  <Label for="requires_approval">Requires Approval</Label>
                </div>

                <div class="flex items-center space-x-2">
                  <Checkbox id="requires_document" v-model:checked="form.requires_document" />
                  <Label for="requires_document">Requires Document</Label>
                </div>

                <div class="flex items-center space-x-2">
                  <Checkbox id="can_carry_forward" v-model:checked="form.can_carry_forward" />
                  <Label for="can_carry_forward">Can Carry Forward</Label>
                </div>
              </div>

              <div v-if="form.can_carry_forward" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <Label for="max_carry_forward">Max Carry Forward Days</Label>
                  <Input id="max_carry_forward" v-model.number="form.max_carry_forward_days" type="number" min="0"
                    placeholder="5" />
                </div>
              </div>

              <div class="flex items-center space-x-2">
                <Checkbox id="is_active" v-model:checked="form.is_active" />
                <Label for="is_active">Active</Label>
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
                {{ submitting ? 'Creating...' : 'Create Leave Type' }}
              </Button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Checkbox } from '@/components/ui/checkbox'
import { ArrowLeft, Save, Loader2 } from 'lucide-vue-next'
import { apiService } from '@/services/api'
import type { LeaveTypeForm } from '@/types/erp'

// Form data
const form = ref<LeaveTypeForm>({
  name: '',
  description: '',
  color: '#3B82F6',
  default_days_per_year: 21,
  is_paid: false,
  requires_approval: true,
  requires_document: false,
  can_carry_forward: false,
  max_carry_forward_days: undefined,
  is_active: true,
  sort_order: 0,
})

// Form state
const submitting = ref(false)
const errors = ref<Record<string, string>>({})

// Navigation
const navigateBack = () => {
  router.visit('/hr/leave-types')
}

// Form submission
const handleSubmit = async () => {
  submitting.value = true
  errors.value = {}

  try {
    // Basic validation
    if (!form.value.name.trim()) {
      errors.value.name = 'Name is required'
    }
    if (form.value.default_days_per_year < 0) {
      errors.value.default_days_per_year = 'Days must be 0 or greater'
    }

    if (Object.keys(errors.value).length > 0) {
      submitting.value = false
      return
    }

    // Submit form
    await apiService.createLeaveType(form.value)

    // Navigate back to index
    router.visit('/hr/leave-types', {
      onSuccess: () => {
        // Show success message if needed
      }
    })
  } catch (error: any) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    } else {
      console.error('Error creating leave type:', error)
    }
  } finally {
    submitting.value = false
  }
}
</script>
