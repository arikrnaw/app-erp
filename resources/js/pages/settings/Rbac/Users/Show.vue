<template>
    <Head title="User Details" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <h2 class="font-semibold text-xl leading-tight">
                    User Details
                </h2>
                <div class="flex gap-2">
                    <Button variant="outline" @click="editUser">
                        <Edit class="w-4 h-4 mr-2" />
                        Edit
                    </Button>
                    <Button variant="outline" @click="navigateBack">
                        <ArrowLeft class="w-4 h-4 mr-2" />
                        Back to Users
                    </Button>
                </div>
            </div>

            <div v-if="loading" class="flex items-center justify-center py-8">
                <Loader2 class="w-6 h-6 animate-spin mr-2" />
                Loading user details...
            </div>

            <div v-else-if="user" class="space-y-6">
                <!-- User Info -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <User class="w-5 h-5 text-primary" />
                            User Information
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <Label class="text-sm font-medium text-muted-foreground">Full Name</Label>
                                <p class="text-lg font-medium">{{ user.name }}</p>
                            </div>
                            <div>
                                <Label class="text-sm font-medium text-muted-foreground">Email Address</Label>
                                <p class="text-lg">{{ user.email }}</p>
                            </div>
                            <div>
                                <Label class="text-sm font-medium text-muted-foreground">Status</Label>
                                <Badge :variant="user.is_active ? 'default' : 'secondary'">
                                    {{ user.is_active ? 'Active' : 'Inactive' }}
                                </Badge>
                            </div>
                            <div>
                                <Label class="text-sm font-medium text-muted-foreground">Authentication</Label>
                                <Badge variant="outline">
                                    {{ authProviderLabels[user.auth_provider] || user.auth_provider }}
                                </Badge>
                            </div>
                        </div>
                        <div v-if="user.workos_id">
                            <Label class="text-sm font-medium text-muted-foreground">WorkOS ID</Label>
                            <p class="text-sm text-muted-foreground">{{ user.workos_id }}</p>
                        </div>
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
                    </CardContent>
                </Card>

                <!-- Assigned Roles -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Shield class="w-5 h-5 text-primary" />
                            Assigned Roles
                            <Badge variant="secondary">{{ user.roles?.length || 0 }} roles</Badge>
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div v-if="!user.roles || user.roles.length === 0" class="text-center py-8 text-muted-foreground">
                            <Shield class="w-12 h-12 mx-auto mb-4 text-muted-foreground/50" />
                            <p>No roles assigned to this user</p>
                        </div>
                        <div v-else class="space-y-3">
                            <div v-for="role in user.roles" :key="role.id" class="flex items-center justify-between p-3 border rounded-lg">
                                <div class="flex items-center gap-3">
                                    <Shield class="w-4 h-4 text-primary" />
                                    <div>
                                        <p class="font-medium">{{ role.name }}</p>
                                        <p class="text-sm text-muted-foreground">{{ role.description }}</p>
                                    </div>
                                </div>
                                <Badge v-if="role.is_system" variant="outline">System Role</Badge>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Permissions -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Key class="w-5 h-5 text-primary" />
                            Effective Permissions
                            <Badge variant="secondary">{{ userPermissions.length }} permissions</Badge>
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div v-if="userPermissions.length === 0" class="text-center py-8 text-muted-foreground">
                            <Key class="w-12 h-12 mx-auto mb-4 text-muted-foreground/50" />
                            <p>No permissions assigned to this user</p>
                        </div>
                        <div v-else class="space-y-2">
                            <div v-for="permission in userPermissions" :key="permission.id" class="flex items-center justify-between p-2 border rounded">
                                <div class="flex items-center gap-2">
                                    <Key class="w-3 h-3 text-primary" />
                                    <span class="text-sm font-medium">{{ permission.name }}</span>
                                </div>
                                <Badge variant="outline" class="text-xs">
                                    {{ permission.module }}.{{ permission.action }}
                                </Badge>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Activity Statistics -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <BarChart3 class="w-5 h-5 text-primary" />
                            Activity Statistics
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="text-center p-4 border rounded-lg">
                                <div class="text-2xl font-bold text-primary">{{ user.roles?.length || 0 }}</div>
                                <div class="text-sm text-muted-foreground">Assigned Roles</div>
                            </div>
                            <div class="text-center p-4 border rounded-lg">
                                <div class="text-2xl font-bold text-primary">{{ userPermissions.length }}</div>
                                <div class="text-sm text-muted-foreground">Total Permissions</div>
                            </div>
                            <div class="text-center p-4 border rounded-lg">
                                <div class="text-2xl font-bold text-primary">{{ user.is_active ? 'Active' : 'Inactive' }}</div>
                                <div class="text-sm text-muted-foreground">Account Status</div>
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
import { ref, computed, onMounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Label } from '@/components/ui/label'
import { Badge } from '@/components/ui/badge'
import { User, Shield, Key, BarChart3, Edit, ArrowLeft, Loader2 } from 'lucide-vue-next'
import type { BreadcrumbItemType } from '@/types'
import { useApi } from '@/composables/useApi'

const api = useApi()

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Settings', href: '/settings' },
    { title: 'Roles & Permissions', href: '/settings/rbac' },
    { title: 'Users', href: '/settings/rbac/users' },
    { title: 'User Details', href: '/settings/rbac/users/show' }
]

const user = ref(null)
const loading = ref(false)

const authProviderLabels = {
    'email': 'Email & Password',
    'workos': 'WorkOS SSO',
    'google': 'Google OAuth'
}

const userPermissions = computed(() => {
    if (!user.value?.roles) return []
    
    const permissions = new Map()
    user.value.roles.forEach(role => {
        if (role.permissions) {
            role.permissions.forEach(permission => {
                permissions.set(permission.id, permission)
            })
        }
    })
    
    return Array.from(permissions.values())
})

const fetchUser = async () => {
    loading.value = true
    try {
        const userId = window.location.pathname.split('/').pop()
        const response = await api.get(`/api/rbac/users/${userId}`)
        user.value = response.data
    } catch (error) {
        console.error('Error fetching user:', error)
        window.toast?.error('Failed to load user details')
    } finally {
        loading.value = false
    }
}

const editUser = () => {
    if (user.value) {
        window.location.href = `/settings/rbac/users/${user.value.id}/edit`
    }
}

const navigateBack = () => {
    window.location.href = '/settings/rbac/users'
}

onMounted(() => {
    fetchUser()
})
</script>
