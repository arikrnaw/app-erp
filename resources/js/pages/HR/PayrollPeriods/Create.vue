<template>
    <div class="container mx-auto p-6">
        <div class="max-w-2xl mx-auto">
            <!-- Header -->
            <div class="flex items-center gap-4 mb-6">
                <Button variant="ghost" size="sm" @click="navigateBack">
                    <ArrowLeft class="w-4 h-4" />
                </Button>
                <div>
                    <h1 class="text-3xl font-bold text-white">New Payroll Period</h1>
                    <p class="text-white mt-2">Create a new payroll period</p>
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
                                <Label for="period_code">Period Code *</Label>
                                <Input id="period_code" v-model="form.period_code" placeholder="e.g., PP-2024-01"
                                    :class="{ 'border-red-500': errors.period_code }" />
                                <p v-if="errors.period_code" class="text-red-500 text-sm mt-1">{{ errors.period_code }}
                                </p>
                            </div>

                            <div>
                                <Label for="name">Period Name *</Label>
                                <Input id="name" v-model="form.name" placeholder="e.g., January 2024 Payroll"
                                    :class="{ 'border-red-500': errors.name }" />
                                <p v-if="errors.name" class="text-red-500 text-sm mt-1">{{ errors.name }}</p>
                            </div>
                        </div>

                        <div>
                            <Label for="notes">Notes</Label>
                            <Textarea id="notes" v-model="form.notes"
                                placeholder="Additional notes about this payroll period..." rows="3" />
                        </div>
                    </div>

                    <!-- Period Details -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium text-white">Period Details</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <Label for="start_date">Start Date *</Label>
                                <Input id="start_date" v-model="form.start_date" type="date"
                                    :class="{ 'border-red-500': errors.start_date }" />
                                <p v-if="errors.start_date" class="text-red-500 text-sm mt-1">{{ errors.start_date }}
                                </p>
                            </div>

                            <div>
                                <Label for="end_date">End Date *</Label>
                                <Input id="end_date" v-model="form.end_date" type="date" :min="form.start_date"
                                    :class="{ 'border-red-500': errors.end_date }" />
                                <p v-if="errors.end_date" class="text-red-500 text-sm mt-1">{{ errors.end_date }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <Label for="pay_date">Pay Date *</Label>
                                <Input id="pay_date" v-model="form.pay_date" type="date" :min="form.end_date"
                                    :class="{ 'border-red-500': errors.pay_date }" />
                                <p v-if="errors.pay_date" class="text-red-500 text-sm mt-1">{{ errors.pay_date }}</p>
                            </div>

                            <div>
                                <Label for="frequency">Frequency *</Label>
                                <Select v-model="form.frequency" :class="{ 'border-red-500': errors.frequency }">
                                    <option value="">Select Frequency</option>
                                    <option value="weekly">Weekly</option>
                                    <option value="bi_weekly">Bi-Weekly</option>
                                    <option value="monthly">Monthly</option>
                                    <option value="quarterly">Quarterly</option>
                                    <option value="yearly">Yearly</option>
                                </Select>
                                <p v-if="errors.frequency" class="text-red-500 text-sm mt-1">{{ errors.frequency }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Summary -->
                    <div v-if="form.start_date && form.end_date" class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-medium text-white mb-2">Period Summary</h4>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="font-medium text-gray-700">Duration:</span>
                                <span class="ml-2 text-white">{{ calculateDays }} days</span>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">Frequency:</span>
                                <span class="ml-2 text-white">{{ form.frequency ? form.frequency.replace('_', ' ') :
                                    'Not set' }}</span>
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
                            {{ submitting ? 'Creating...' : 'Create Payroll Period' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { ArrowLeft, Save, Loader2 } from 'lucide-vue-next'
import { apiService } from '@/services/api'
import type { PayrollPeriodForm } from '@/types/erp'

// Form data
const form = ref<PayrollPeriodForm>({
    period_code: '',
    name: '',
    start_date: '',
    end_date: '',
    pay_date: '',
    frequency: 'monthly',
    notes: '',
})

// Form state
const submitting = ref(false)
const errors = ref<Record<string, string>>({})

// Computed properties
const calculateDays = computed(() => {
    if (!form.value.start_date || !form.value.end_date) return 0

    const start = new Date(form.value.start_date)
    const end = new Date(form.value.end_date)
    const diffTime = Math.abs(end.getTime() - start.getTime())
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))

    return diffDays + 1 // Include both start and end dates
})

// Navigation
const navigateBack = () => {
    router.visit('/hr/payroll-periods')
}

// Form submission
const handleSubmit = async () => {
    submitting.value = true
    errors.value = {}

    try {
        // Basic validation
        if (!form.value.period_code.trim()) {
            errors.value.period_code = 'Period code is required'
        }
        if (!form.value.name.trim()) {
            errors.value.name = 'Period name is required'
        }
        if (!form.value.start_date) {
            errors.value.start_date = 'Start date is required'
        }
        if (!form.value.end_date) {
            errors.value.end_date = 'End date is required'
        }
        if (!form.value.pay_date) {
            errors.value.pay_date = 'Pay date is required'
        }
        if (!form.value.frequency) {
            errors.value.frequency = 'Frequency is required'
        }

        if (Object.keys(errors.value).length > 0) {
            submitting.value = false
            return
        }

        // Submit form
        await apiService.createPayrollPeriod(form.value)

        // Navigate back to index
        router.visit('/hr/payroll-periods', {
            onSuccess: () => {
                // Show success message if needed
            }
        })
    } catch (error: any) {
        if (error.response?.data?.errors) {
            errors.value = error.response.data.errors
        } else {
            console.error('Error creating payroll period:', error)
        }
    } finally {
        submitting.value = false
    }
}
</script>
