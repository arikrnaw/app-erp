<template>
    <Head title="Edit User" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <h2 class="font-semibold text-xl leading-tight">
                    Edit User
                </h2>
                <Button variant="outline" @click="navigateBack">
                    <ArrowLeft class="w-4 h-4 mr-2" />
                    Back to Users
                </Button>
            </div>

            <div v-if="loading" class="flex items-center justify-center py-8">
                <Loader2 class="w-6 h-6 animate-spin mr-2" />
                Loading user...
            </div>

            <div v-else-if="user" class="space-y-6">
                <!-- Form -->
                <Card>
                    <CardContent class="p-6">
                        <form @submit.prevent="submitForm" class="space-y-6">
                            <!-- Basic Information -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <Label for="name">Full Name</Label>
                                    <Input
                                        id="name"
                                        v-model="form.name"
                                        placeholder="Enter full name"
                                        :class="{ 'border-red-500': errors.name }"
                                    />
                                    <p v-if="errors.name" class="text-sm text-red-500 mt-1">
                                        {{ errors.name }}
                                    </p>
                                </div>
                                <div>
                                    <Label for="email">Email Address</Label>
                                    <Input
                                        id="email"
                                        v-model="form.email"
                                        type="email"
                                        placeholder="Enter email address"
                                        :class="{ 'border-red-500': errors.email }"
                                    />
                                    <p v-if="errors.email" class="text-sm text-red-500 mt-1">
                                        {{ errors.email }}
                                    </p>
                                </div>
                            </div>

                            <!-- Password Change (Optional) -->
                            <div class="border-t pt-6">
                                <h3 class="text-lg font-medium mb-4">Change Password (Optional)</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <Label for="password">New Password</Label>
                                        <Input
                                            id="password"
                                            v-model="form.password"
                                            type="password"
                                            placeholder="Leave blank to keep current password"
                                        />
                                        <p v-if="errors.password" class="text-sm text-red-500 mt-1">
                                            {{ errors.password }}
                                        </p>
                                    </div>
                                    <div>
                                        <Label for="password_confirmation">Confirm New Password</Label>
                                        <Input
                                            id="password_confirmation"
                                            v-model="form.password_confirmation"
                                            type="password"
                                            placeholder="Confirm new password"
                                        />
                                        <p v-if="errors.password_confirmation" class="text-sm text-red-500 mt-1">
                                            {{ errors.password_confirmation }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Authentication Provider -->
                            <div>
                                <Label for="auth_provider">Authentication Provider</Label>
                                <Select v-model="form.auth_provider">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select authentication method" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="email">Email & Password</SelectItem>
                                        <SelectItem value="workos">WorkOS SSO</SelectItem>
                                        <SelectItem value="google">Google OAuth</SelectItem>
                                    </SelectContent>
                                </Select>
                                <p class="text-sm text-muted-foreground mt-1">
                                    Choose how this user will authenticate to the system
                                </p>
                            </div>

                            <!-- Status -->
                            <div>
                                <div class="flex items-center space-x-2">
                                    <Checkbox id="is_active" v-model:checked="form.is_active" />
                                    <Label for="is_active">Active User</Label>
                                </div>
                                <p class="text-sm text-muted-foreground mt-1">
                                    Inactive users cannot log in to the system
                                </p>
                            </div>

                            <!-- Roles Assignment -->
                            <div>
                                <Label>Assign Roles</Label>
                                <div class="mt-2 space-y-2">
                                    <div v-for="role in availableRoles" :key="role.id" class="flex items-center space-x-2">
                                        <Checkbox 
                                            :id="`role_${role.id}`" 
                                            :checked="selectedRoles.includes(role.id)"
                                            @update:checked="toggleRole(role.id)"
                                        />
                                        <Label :for="`role_${role.id}`" class="flex items-center gap-2">
                                            {{ role.name }}
                                            <Badge v-if="role.is_system" variant="outline" class="text-xs">System</Badge>
                                        </Label>
                                    </div>
                                </div>
                                <p v-if="errors.roles" class="text-sm text-red-500 mt-1">
                                    {{ errors.roles }}
                                </p>
                            </div>

                            <!-- Submit Button -->
                            <div class="flex justify-end gap-3">
                                <Button type="button" variant="outline" @click="navigateBack">
                                    Cancel
                                </Button>
                                <Button type="submit" :disabled="submitting">
                                    <Loader2 v-if="submitting" class="w-4 h-4 mr-2 animate-spin" />
                                    Update User
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>

                <!-- Current User Info -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <User class="w-5 h-5 text-primary" />
                            Current User Information
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <Label class="text-sm font-medium text-muted-foreground">Last Login</Label>
                                <p class="text-sm text-muted-foreground">
                                    {{ user.last_login_at ? new Date(user.last_login_at).toLocaleString() : 'Never' }}
                                </p>
                            </div>
                            <div>
                                <Label class="text-sm font-medium text-muted-foreground">Member Since</Label>
                                <p class="text-sm text-muted-foreground">
                                    {{ new Date(user.created_at).toLocaleDateString() }}
                                </p>
                            </div>
                            <div v-if="user.workos_id">
                                <Label class="text-sm font-medium text-muted-foreground">WorkOS ID</Label>
                                <p class="text-sm text-muted-foreground">{{ user.workos_id }}</p>
                            </div>
                            <div>
                                <Label class="text-sm font-medium text-muted-foreground">Current Roles</Label>
                                <div class="flex flex-wrap gap-1 mt-1">
                                    <Badge v-for="role in user.roles" :key="role.id" variant="outline" class="text-xs">
                                        {{ role.name }}
                                    </Badge>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div v-else class="text-center py-8">
                <User class="w-12 h-12 mx-auto mb-4 text-muted-foreground/50" />
                <h3 class="text-lg font-medium mb-2">User Not Found</h3>
                <p class="text-muted-foreground mb-4">The user you're looking for doesn't exist or has been deleted.</p>
                <Button @click="navigateBack">
                    Back to Users
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
import { Badge } from '@/components/ui/badge'
import { Checkbox } from '@/components/ui/checkbox'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { User, ArrowLeft, Loader2 } from 'lucide-vue-next'
import type { BreadcrumbItemType } from '@/types'
import { useApi } from '@/composables/useApi'

const api = useApi()

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Settings', href: '/settings' },
    { title: 'Roles & Permissions', href: '/settings/rbac' },
    { title: 'Users', href: '/settings/rbac/users' },
    { title: 'Edit User', href: '/settings/rbac/users/edit' }
]

const user = ref(null)
const loading = ref(false)
const submitting = ref(false)
const errors = ref({})
const availableRoles = ref([])
const selectedRoles = ref([])

const form = ref({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    auth_provider: 'email',
    is_active: true
})

const fetchUser = async () => {
    loading.value = true
    try {
        const userId = window.location.pathname.split('/').pop()
        const response = await api.get(`/api/rbac/users/${userId}`)
        user.value = response.data
        
        // Populate form with current values
        form.value = {
            name: user.value.name,
            email: user.value.email,
            password: '',
            password_confirmation: '',
            auth_provider: user.value.auth_provider || 'email',
            is_active: user.value.is_active
        }
        
        // Set selected roles
        selectedRoles.value = user.value.roles?.map(role => role.id) || []
    } catch (error) {
        console.error('Error fetching user:', error)
        window.toast?.error('Failed to load user details')
    } finally {
        loading.value = false
    }
}

const fetchAvailableRoles = async () => {
    try {
        const response = await api.get('/api/rbac/users/available-roles')
        availableRoles.value = response.data
    } catch (error) {
        console.error('Error fetching roles:', error)
        window.toast?.error('Failed to load available roles')
    }
}

const toggleRole = (roleId: number) => {
    const index = selectedRoles.value.indexOf(roleId)
    if (index > -1) {
        selectedRoles.value.splice(index, 1)
    } else {
        selectedRoles.value.push(roleId)
    }
}

const submitForm = async () => {
    submitting.value = true
    errors.value = {}

    try {
        const userId = window.location.pathname.split('/').pop()
        const formData = {
            ...form.value,
            roles: selectedRoles.value
        }
        
        // Remove empty password fields if not changing password
        if (!formData.password) {
            delete formData.password
            delete formData.password_confirmation
        }
        
        const response = await api.put(`/api/rbac/users/${userId}`, formData)
        window.toast?.success('User updated successfully')
        navigateBack()
    } catch (error) {
        if (error.response?.data?.errors) {
            errors.value = error.response.data.errors
        } else {
            window.toast?.error('Failed to update user')
        }
    } finally {
        submitting.value = false
    }
}

const navigateBack = () => {
    window.location.href = '/settings/rbac/users'
}

onMounted(() => {
    fetchUser()
    fetchAvailableRoles()
})
</script>
