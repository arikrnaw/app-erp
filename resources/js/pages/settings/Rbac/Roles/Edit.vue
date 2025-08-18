<template>
    <Head title="Edit Role" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <h2 class="font-semibold text-xl leading-tight">
                    Edit Role
                </h2>
                <Button variant="outline" @click="navigateBack">
                    <ArrowLeft class="w-4 h-4 mr-2" />
                    Back to Roles
                </Button>
            </div>

            <div v-if="loading" class="flex items-center justify-center py-8">
                <Loader2 class="w-6 h-6 animate-spin mr-2" />
                Loading role...
            </div>

            <div v-else-if="role" class="space-y-6">
                <!-- Form -->
                <Card>
                    <CardContent class="p-6">
                        <form @submit.prevent="submitForm" class="space-y-6">
                            <!-- Role Name -->
                            <div>
                                <Label for="name">Role Name</Label>
                                <Input
                                    id="name"
                                    v-model="form.name"
                                    placeholder="e.g., CRM Manager"
                                    :class="{ 'border-red-500': errors.name }"
                                    :disabled="role.is_system"
                                />
                                <p v-if="errors.name" class="text-sm text-red-500 mt-1">
                                    {{ errors.name }}
                                </p>
                                <p v-if="role.is_system" class="text-sm text-muted-foreground mt-1">
                                    System roles cannot be renamed
                                </p>
                            </div>

                            <!-- Description -->
                            <div>
                                <Label for="description">Description</Label>
                                <Textarea
                                    id="description"
                                    v-model="form.description"
                                    placeholder="Describe what this role is for..."
                                    rows="3"
                                    :disabled="role.is_system"
                                />
                                <p v-if="errors.description" class="text-sm text-red-500 mt-1">
                                    {{ errors.description }}
                                </p>
                            </div>

                            <!-- Permissions Assignment -->
                            <div>
                                <Label>Assign Permissions</Label>
                                <div class="mt-4 space-y-4">
                                    <div v-for="module in groupedPermissions" :key="module.name" class="border rounded-lg p-4">
                                        <div class="flex items-center justify-between mb-3">
                                            <h4 class="font-medium">{{ module.label }}</h4>
                                            <div class="flex items-center gap-2">
                                                <Button 
                                                    type="button" 
                                                    variant="outline" 
                                                    size="sm"
                                                    @click="selectAllModule(module.name)"
                                                >
                                                    Select All
                                                </Button>
                                                <Button 
                                                    type="button" 
                                                    variant="outline" 
                                                    size="sm"
                                                    @click="deselectAllModule(module.name)"
                                                >
                                                    Deselect All
                                                </Button>
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                                            <div v-for="permission in module.permissions" :key="permission.id" class="flex items-center space-x-2">
                                                <Checkbox 
                                                    :id="`permission_${permission.id}`" 
                                                    :checked="selectedPermissions.includes(permission.id)"
                                                    @update:checked="togglePermission(permission.id)"
                                                    :disabled="role.is_system"
                                                />
                                                <Label :for="`permission_${permission.id}`" class="text-sm">
                                                    {{ permission.name }}
                                                </Label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <p v-if="errors.permissions" class="text-sm text-red-500 mt-1">
                                    {{ errors.permissions }}
                                </p>
                            </div>

                            <!-- Submit Button -->
                            <div class="flex justify-end gap-3">
                                <Button type="button" variant="outline" @click="navigateBack">
                                    Cancel
                                </Button>
                                <Button type="submit" :disabled="submitting || role.is_system">
                                    <Loader2 v-if="submitting" class="w-4 h-4 mr-2 animate-spin" />
                                    Update Role
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>

                <!-- Current Role Info -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Shield class="w-5 h-5 text-primary" />
                            Current Role Information
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <Label class="text-sm font-medium text-muted-foreground">Role Type</Label>
                                <Badge :variant="role.is_system ? 'default' : 'secondary'">
                                    {{ role.is_system ? 'System Role' : 'Custom Role' }}
                                </Badge>
                            </div>
                            <div>
                                <Label class="text-sm font-medium text-muted-foreground">Created</Label>
                                <p class="text-sm text-muted-foreground">
                                    {{ new Date(role.created_at).toLocaleDateString() }}
                                </p>
                            </div>
                            <div>
                                <Label class="text-sm font-medium text-muted-foreground">Current Permissions</Label>
                                <p class="text-sm text-muted-foreground">
                                    {{ role.permissions?.length || 0 }} permissions assigned
                                </p>
                            </div>
                            <div>
                                <Label class="text-sm font-medium text-muted-foreground">Assigned Users</Label>
                                <p class="text-sm text-muted-foreground">
                                    {{ role.users?.length || 0 }} users have this role
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div v-else class="text-center py-8">
                <Shield class="w-12 h-12 mx-auto mb-4 text-muted-foreground/50" />
                <h3 class="text-lg font-medium mb-2">Role Not Found</h3>
                <p class="text-muted-foreground mb-4">The role you're looking for doesn't exist or has been deleted.</p>
                <Button @click="navigateBack">
                    Back to Roles
                </Button>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Badge } from '@/components/ui/badge'
import { Checkbox } from '@/components/ui/checkbox'
import { Shield, ArrowLeft, Loader2 } from 'lucide-vue-next'
import type { BreadcrumbItemType } from '@/types'
import { useApi } from '@/composables/useApi'

const api = useApi()

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Settings', href: '/settings' },
    { title: 'Roles & Permissions', href: '/settings/rbac' },
    { title: 'Roles', href: '/settings/rbac/roles' },
    { title: 'Edit Role', href: '/settings/rbac/roles/edit' }
]

const role = ref(null)
const loading = ref(false)
const submitting = ref(false)
const errors = ref({})
const availablePermissions = ref([])
const selectedPermissions = ref([])

const form = ref({
    name: '',
    description: ''
})

const moduleLabels = {
    'crm': 'Customer Relationship Management',
    'projects': 'Project Management',
    'reports': 'Reports & Analytics',
    'customer-service': 'Customer Service',
    'settings': 'Settings',
    'erp': 'ERP Core'
}

const groupedPermissions = computed(() => {
    const groups = {}
    
    availablePermissions.value.forEach(permission => {
        if (!groups[permission.module]) {
            groups[permission.module] = {
                name: permission.module,
                label: moduleLabels[permission.module] || permission.module,
                permissions: []
            }
        }
        groups[permission.module].permissions.push(permission)
    })
    
    return Object.values(groups)
})

const fetchRole = async () => {
    loading.value = true
    try {
        const roleId = window.location.pathname.split('/').pop()
        const response = await api.get(`/api/rbac/roles/${roleId}`)
        role.value = response.data
        
        // Populate form with current values
        form.value = {
            name: role.value.name,
            description: role.value.description || ''
        }
        
        // Set selected permissions
        selectedPermissions.value = role.value.permissions?.map(permission => permission.id) || []
    } catch (error) {
        console.error('Error fetching role:', error)
        window.toast?.error('Failed to load role details')
    } finally {
        loading.value = false
    }
}

const fetchAvailablePermissions = async () => {
    try {
        const response = await api.get('/api/rbac/permissions')
        availablePermissions.value = response.data.data || []
    } catch (error) {
        console.error('Error fetching permissions:', error)
        window.toast?.error('Failed to load available permissions')
    }
}

const togglePermission = (permissionId: number) => {
    const index = selectedPermissions.value.indexOf(permissionId)
    if (index > -1) {
        selectedPermissions.value.splice(index, 1)
    } else {
        selectedPermissions.value.push(permissionId)
    }
}

const selectAllModule = (moduleName: string) => {
    const modulePermissions = availablePermissions.value.filter(p => p.module === moduleName)
    modulePermissions.forEach(permission => {
        if (!selectedPermissions.value.includes(permission.id)) {
            selectedPermissions.value.push(permission.id)
        }
    })
}

const deselectAllModule = (moduleName: string) => {
    const modulePermissions = availablePermissions.value.filter(p => p.module === moduleName)
    modulePermissions.forEach(permission => {
        const index = selectedPermissions.value.indexOf(permission.id)
        if (index > -1) {
            selectedPermissions.value.splice(index, 1)
        }
    })
}

const submitForm = async () => {
    submitting.value = true
    errors.value = {}

    try {
        const roleId = window.location.pathname.split('/').pop()
        const formData = {
            ...form.value,
            permissions: selectedPermissions.value
        }
        
        const response = await api.put(`/api/rbac/roles/${roleId}`, formData)
        window.toast?.success('Role updated successfully')
        navigateBack()
    } catch (error) {
        if (error.response?.data?.errors) {
            errors.value = error.response.data.errors
        } else {
            window.toast?.error('Failed to update role')
        }
    } finally {
        submitting.value = false
    }
}

const navigateBack = () => {
    window.location.href = '/settings/rbac/roles'
}

onMounted(() => {
    fetchRole()
    fetchAvailablePermissions()
})
</script>
