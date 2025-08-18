<template>
    <Head title="Edit Permission" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <h2 class="font-semibold text-xl leading-tight">
                    Edit Permission
                </h2>
                <Button variant="outline" @click="navigateBack">
                    <ArrowLeft class="w-4 h-4 mr-2" />
                    Back to Permissions
                </Button>
            </div>

            <div v-if="loading" class="flex items-center justify-center py-8">
                <Loader2 class="w-6 h-6 animate-spin mr-2" />
                Loading permission...
            </div>

            <div v-else-if="permission" class="space-y-6">
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
                                <Button type="submit" :disabled="submitting">
                                    <Loader2 v-if="submitting" class="w-4 h-4 mr-2 animate-spin" />
                                    Update Permission
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>

                <!-- Current Usage -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Shield class="w-5 h-5 text-primary" />
                            Current Usage
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="text-center p-4 border rounded-lg">
                                <div class="text-2xl font-bold text-primary">{{ permission.roles_count || 0 }}</div>
                                <div class="text-sm text-muted-foreground">Assigned Roles</div>
                            </div>
                            <div class="text-center p-4 border rounded-lg">
                                <div class="text-2xl font-bold text-primary">{{ permission.users_count || 0 }}</div>
                                <div class="text-sm text-muted-foreground">Users with Permission</div>
                            </div>
                        </div>
                        <div v-if="permission.roles && permission.roles.length > 0" class="mt-4">
                            <Label class="text-sm font-medium text-muted-foreground">Assigned to Roles:</Label>
                            <div class="flex flex-wrap gap-2 mt-2">
                                <Badge v-for="role in permission.roles" :key="role.id" variant="outline">
                                    {{ role.name }}
                                </Badge>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div v-else class="text-center py-8">
                <Key class="w-12 h-12 mx-auto mb-4 text-muted-foreground/50" />
                <h3 class="text-lg font-medium mb-2">Permission Not Found</h3>
                <p class="text-muted-foreground mb-4">The permission you're looking for doesn't exist or has been deleted.</p>
                <Button @click="navigateBack">
                    Back to Permissions
                </Button>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Badge } from '@/components/ui/badge'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Key, Shield, ArrowLeft, Loader2 } from 'lucide-vue-next'
import type { BreadcrumbItemType } from '@/types'
import { useApi } from '@/composables/useApi'

const api = useApi()

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Settings', href: '/settings' },
    { title: 'Roles & Permissions', href: '/settings/rbac' },
    { title: 'Permissions', href: '/settings/rbac/permissions' },
    { title: 'Edit Permission', href: '/settings/rbac/permissions/edit' }
]

const permission = ref(null)
const loading = ref(false)
const submitting = ref(false)
const errors = ref({})

const form = ref({
    name: '',
    module: '',
    action: '',
    description: ''
})

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

const fetchPermission = async () => {
    loading.value = true
    try {
        const permissionId = window.location.pathname.split('/').pop()
        const response = await api.get(`/api/rbac/permissions/${permissionId}`)
        permission.value = response.data
        
        // Populate form with current values
        form.value = {
            name: permission.value.name,
            module: permission.value.module,
            action: permission.value.action,
            description: permission.value.description || ''
        }
    } catch (error) {
        console.error('Error fetching permission:', error)
        window.toast?.error('Failed to load permission details')
    } finally {
        loading.value = false
    }
}

const submitForm = async () => {
    submitting.value = true
    errors.value = {}

    try {
        const permissionId = window.location.pathname.split('/').pop()
        const response = await api.put(`/api/rbac/permissions/${permissionId}`, form.value)
        window.toast?.success('Permission updated successfully')
        navigateBack()
    } catch (error) {
        if (error.response?.data?.errors) {
            errors.value = error.response.data.errors
        } else {
            window.toast?.error('Failed to update permission')
        }
    } finally {
        submitting.value = false
    }
}

const navigateBack = () => {
    window.location.href = '/settings/rbac/permissions'
}

onMounted(() => {
    fetchPermission()
})
</script>
