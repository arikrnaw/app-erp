<template>
    <Head title="Create Permission" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <h2 class="font-semibold text-xl leading-tight">
                    Create Permission
                </h2>
                <Button variant="outline" @click="navigateBack">
                    <ArrowLeft class="w-4 h-4 mr-2" />
                    Back to Permissions
                </Button>
            </div>

            <!-- Form -->
            <Card>
                <CardContent class="p-6">
                    <form @submit.prevent="submitForm" class="space-y-6">
                        <!-- Permission Name -->
                        <div>
                            <Label for="name">Permission Name</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                placeholder="e.g., View Customers"
                                :class="{ 'border-red-500': errors.name }"
                            />
                            <p v-if="errors.name" class="text-sm text-red-500 mt-1">
                                {{ errors.name }}
                            </p>
                        </div>

                        <!-- Module -->
                        <div>
                            <Label for="module">Module</Label>
                            <Select v-model="form.module" :class="{ 'border-red-500': errors.module }">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select a module" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="module in availableModules" :key="module.value" :value="module.value">
                                        {{ module.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="errors.module" class="text-sm text-red-500 mt-1">
                                {{ errors.module }}
                            </p>
                        </div>

                        <!-- Action -->
                        <div>
                            <Label for="action">Action</Label>
                            <Select v-model="form.action" :class="{ 'border-red-500': errors.action }">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select an action" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="action in availableActions" :key="action.value" :value="action.value">
                                        {{ action.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="errors.action" class="text-sm text-red-500 mt-1">
                                {{ errors.action }}
                            </p>
                        </div>

                        <!-- Description -->
                        <div>
                            <Label for="description">Description</Label>
                            <Textarea
                                id="description"
                                v-model="form.description"
                                placeholder="Describe what this permission allows..."
                                rows="3"
                            />
                            <p v-if="errors.description" class="text-sm text-red-500 mt-1">
                                {{ errors.description }}
                            </p>
                        </div>

                        <!-- Preview -->
                        <div v-if="form.module && form.action" class="p-4 bg-muted rounded-lg">
                            <h4 class="font-medium mb-2">Permission Preview</h4>
                            <div class="space-y-2 text-sm">
                                <div>
                                    <span class="font-medium">Full Permission:</span>
                                    <Badge variant="outline" class="ml-2">
                                        {{ form.module }}.{{ form.action }}
                                    </Badge>
                                </div>
                                <div>
                                    <span class="font-medium">Display Name:</span>
                                    <span class="ml-2">{{ form.name || `${availableModules.find(m => m.value === form.module)?.label} - ${availableActions.find(a => a.value === form.action)?.label}` }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end gap-3">
                            <Button type="button" variant="outline" @click="navigateBack">
                                Cancel
                            </Button>
                            <Button type="submit" :disabled="loading">
                                <Loader2 v-if="loading" class="w-4 h-4 mr-2 animate-spin" />
                                Create Permission
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Badge } from '@/components/ui/badge'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { ArrowLeft, Loader2 } from 'lucide-vue-next'
import type { BreadcrumbItemType } from '@/types'
import { useApi } from '@/composables/useApi'

const api = useApi()

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Settings', href: '/settings' },
    { title: 'Roles & Permissions', href: '/settings/rbac' },
    { title: 'Permissions', href: '/settings/rbac/permissions' },
    { title: 'Create Permission', href: '/settings/rbac/permissions/create' }
]

const form = ref({
    name: '',
    module: '',
    action: '',
    description: ''
})

const errors = ref({})
const loading = ref(false)

const availableModules = [
    { value: 'crm', label: 'Customer Relationship Management' },
    { value: 'projects', label: 'Project Management' },
    { value: 'reports', label: 'Reports & Analytics' },
    { value: 'customer-service', label: 'Customer Service' },
    { value: 'settings', label: 'Settings' },
    { value: 'erp', label: 'ERP Core' }
]

const availableActions = [
    { value: 'view', label: 'View' },
    { value: 'create', label: 'Create' },
    { value: 'edit', label: 'Edit' },
    { value: 'delete', label: 'Delete' },
    { value: 'export', label: 'Export' },
    { value: 'import', label: 'Import' },
    { value: 'approve', label: 'Approve' },
    { value: 'reject', label: 'Reject' },
    { value: 'manage', label: 'Manage' }
]

const submitForm = async () => {
    loading.value = true
    errors.value = {}

    try {
        const response = await api.post('/api/rbac/permissions', form.value)
        window.toast?.success('Permission created successfully')
        navigateBack()
    } catch (error) {
        if (error.response?.data?.errors) {
            errors.value = error.response.data.errors
        } else {
            window.toast?.error('Failed to create permission')
        }
    } finally {
        loading.value = false
    }
}

const navigateBack = () => {
    window.location.href = '/settings/rbac/permissions'
}
</script>
