<template>
    <Head title="Create Role" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <h2 class="font-semibold text-xl leading-tight">
                    Create New Role
                </h2>
            </div>

            <div class="max-w-4xl">
                <Card>
                    <CardContent class="p-6">
                        <form @submit.prevent="submitForm" class="space-y-6">
                            <!-- Basic Information -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-medium">Basic Information</h3>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <Label for="name">Role Name *</Label>
                                        <Input
                                            id="name"
                                            v-model="form.name"
                                            placeholder="Enter role name"
                                            :class="{ 'border-red-500': errors.name }"
                                        />
                                        <p v-if="errors.name" class="text-sm text-red-500 mt-1">{{ errors.name }}</p>
                                    </div>

                                    <div>
                                        <Label for="description">Description</Label>
                                        <Input
                                            id="description"
                                            v-model="form.description"
                                            placeholder="Enter role description"
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- Permissions -->
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg font-medium">Permissions</h3>
                                    <div class="flex gap-2">
                                        <Button type="button" variant="outline" size="sm" @click="selectAll">
                                            Select All
                                        </Button>
                                        <Button type="button" variant="outline" size="sm" @click="deselectAll">
                                            Deselect All
                                        </Button>
                                    </div>
                                </div>

                                <div v-if="loadingPermissions" class="flex items-center justify-center py-8">
                                    <Loader2 class="w-6 h-6 animate-spin mr-2" />
                                    Loading permissions...
                                </div>
                                <div v-else class="space-y-4">
                                    <div v-for="module in groupedPermissions" :key="module.name" class="border rounded-lg p-4">
                                        <div class="flex items-center gap-2 mb-3">
                                            <Checkbox
                                                :id="`module-${module.name}`"
                                                :checked="isModuleSelected(module.name)"
                                                @update:checked="toggleModule(module.name)"
                                            />
                                            <Label :for="`module-${module.name}`" class="font-medium">
                                                {{ module.label }}
                                            </Label>
                                        </div>
                                        
                                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 ml-6">
                                            <div v-for="permission in module.permissions" :key="permission.id" class="flex items-center gap-2">
                                                <Checkbox
                                                    :id="`permission-${permission.id}`"
                                                    :checked="selectedPermissions.includes(permission.id)"
                                                    @update:checked="togglePermission(permission.id)"
                                                />
                                                <Label :for="`permission-${permission.id}`" class="text-sm">
                                                    {{ permission.name }}
                                                </Label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="flex items-center justify-end gap-4 pt-6 border-t">
                                <Button type="button" variant="outline" @click="cancel">
                                    Cancel
                                </Button>
                                <Button type="submit" :disabled="submitting">
                                    <Loader2 v-if="submitting" class="w-4 h-4 mr-2 animate-spin" />
                                    Create Role
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Checkbox } from '@/components/ui/checkbox'
import { Loader2 } from 'lucide-vue-next'
import type { BreadcrumbItemType } from '@/types'
import { useApi } from '@/composables/useApi'

const api = useApi()

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Settings', href: '/settings' },
    { title: 'Roles & Permissions', href: '/settings/rbac' },
    { title: 'Roles', href: '/settings/rbac/roles' },
    { title: 'Create Role', href: '/settings/rbac/roles/create' }
]

const form = ref({
    name: '',
    description: ''
})

const permissions = ref([])
const selectedPermissions = ref([])
const loadingPermissions = ref(false)
const submitting = ref(false)
const errors = ref({})

const moduleLabels = {
    'crm': 'Customer Relationship Management',
    'projects': 'Project Management',
    'reports': 'Reports & Analytics',
    'customer-service': 'Customer Service',
    'settings': 'Settings',
    'erp': 'ERP Core'
}

const groupedPermissions = computed(() => {
    const grouped = {}
    
    permissions.value.forEach(permission => {
        if (!grouped[permission.module]) {
            grouped[permission.module] = {
                name: permission.module,
                label: moduleLabels[permission.module] || permission.module,
                permissions: []
            }
        }
        grouped[permission.module].permissions.push(permission)
    })
    
    return Object.values(grouped)
})

const fetchPermissions = async () => {
    loadingPermissions.value = true
    try {
        const response = await api.get('/api/rbac/permissions')
        permissions.value = response.data.data
    } catch (error) {
        console.error('Error fetching permissions:', error)
        window.toast?.error('Failed to load permissions')
    } finally {
        loadingPermissions.value = false
    }
}

const selectAll = () => {
    selectedPermissions.value = permissions.value.map(p => p.id)
}

const deselectAll = () => {
    selectedPermissions.value = []
}

const isModuleSelected = (moduleName: string) => {
    const modulePermissions = permissions.value.filter(p => p.module === moduleName)
    return modulePermissions.every(p => selectedPermissions.value.includes(p.id))
}

const toggleModule = (moduleName: string) => {
    const modulePermissions = permissions.value.filter(p => p.module === moduleName)
    const allSelected = modulePermissions.every(p => selectedPermissions.value.includes(p.id))
    
    if (allSelected) {
        // Deselect all permissions in this module
        selectedPermissions.value = selectedPermissions.value.filter(id => 
            !modulePermissions.some(p => p.id === id)
        )
    } else {
        // Select all permissions in this module
        modulePermissions.forEach(p => {
            if (!selectedPermissions.value.includes(p.id)) {
                selectedPermissions.value.push(p.id)
            }
        })
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

const submitForm = async () => {
    submitting.value = true
    errors.value = {}
    
    try {
        const response = await api.post('/api/rbac/roles', {
            name: form.value.name,
            description: form.value.description,
            permissions: selectedPermissions.value
        })
        
        window.toast?.success('Role created successfully')
        window.location.href = '/settings/rbac/roles'
    } catch (error) {
        console.error('Error creating role:', error)
        if (error.response?.data?.errors) {
            errors.value = error.response.data.errors
        } else {
            window.toast?.error('Failed to create role')
        }
    } finally {
        submitting.value = false
    }
}

const cancel = () => {
    window.location.href = '/settings/rbac/roles'
}

onMounted(() => {
    fetchPermissions()
})
</script>
