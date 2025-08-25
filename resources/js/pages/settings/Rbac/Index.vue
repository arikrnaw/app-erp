<template>

    <Head title="Roles & Permissions" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <h2 class="font-semibold text-xl leading-tight">
                    Roles & Permissions Management
                </h2>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Roles</CardTitle>
                        <Shield class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.total_roles || 0 }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ stats.system_roles || 0 }} system roles
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Permissions</CardTitle>
                        <Key class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.total_permissions || 0 }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ stats.modules_count || 0 }} modules
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Active Users</CardTitle>
                        <Users class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.active_users || 0 }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ stats.users_with_roles || 0 }} with roles assigned
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Custom Roles</CardTitle>
                        <UserPlus class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.custom_roles || 0 }}</div>
                        <p class="text-xs text-muted-foreground">
                            User-defined roles
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Shield class="h-5 w-5" />
                            Manage Roles
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="text-sm text-muted-foreground mb-4">
                            Create, edit, and manage user roles with specific permissions.
                        </p>
                        <div class="flex gap-2">
                            <Button @click="navigateToRoles" class="flex-1">
                                <Shield class="w-4 h-4 mr-2" />
                                View Roles
                            </Button>
                            <Button @click="navigateToCreateRole" variant="outline">
                                <Plus class="w-4 h-4" />
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Key class="h-5 w-5" />
                            Manage Permissions
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="text-sm text-muted-foreground mb-4">
                            Define and manage system permissions for different modules.
                        </p>
                        <div class="flex gap-2">
                            <Button @click="navigateToPermissions" class="flex-1">
                                <Key class="w-4 h-4 mr-2" />
                                View Permissions
                            </Button>
                            <Button @click="navigateToCreatePermission" variant="outline">
                                <Plus class="w-4 h-4" />
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Users class="h-5 w-5" />
                            User Management
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="text-sm text-muted-foreground mb-4">
                            Manage users and assign roles to control access.
                        </p>
                        <div class="flex gap-2">
                            <Button @click="navigateToUsers" class="flex-1">
                                <Users class="w-4 h-4 mr-2" />
                                View Users
                            </Button>
                            <Button @click="navigateToCreateUser" variant="outline">
                                <Plus class="w-4 h-4" />
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Recent Activity -->
            <Card>
                <CardHeader>
                    <CardTitle>Recent Activity</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <div v-if="loading" class="flex items-center justify-center py-8">
                            <Loader2 class="w-6 h-6 animate-spin mr-2" />
                            Loading activity...
                        </div>
                        <div v-else-if="recentActivity.length === 0" class="text-center py-8 text-muted-foreground">
                            No recent activity
                        </div>
                        <div v-else class="space-y-3">
                            <div v-for="activity in recentActivity" :key="activity.id"
                                class="flex items-center gap-3 p-3 rounded-lg border">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center">
                                        <Shield v-if="activity.type === 'role'" class="w-4 h-4 text-primary" />
                                        <Key v-else-if="activity.type === 'permission'" class="w-4 h-4 text-primary" />
                                        <Users v-else class="w-4 h-4 text-primary" />
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium">{{ activity.description }}</p>
                                    <p class="text-xs text-muted-foreground">{{ formatDate(activity.created_at) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Shield, Key, Users, UserPlus, Plus, Loader2 } from 'lucide-vue-next'
import type { BreadcrumbItemType } from '@/types'
import { useApi } from '@/composables/useApi'

const api = useApi()

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Settings', href: '/settings' },
    { title: 'Roles & Permissions', href: '/settings/rbac' }
]

const stats = ref({
    total_roles: 0,
    system_roles: 0,
    custom_roles: 0,
    total_permissions: 0,
    modules_count: 0,
    active_users: 0,
    users_with_roles: 0
})

interface ActivityItem {
    id: number
    type: 'role' | 'permission' | 'user'
    description: string
    created_at: string
}

const recentActivity = ref<ActivityItem[]>([])

const loading = ref(false)

const fetchStats = async () => {
    try {
        const [rolesStats, permissionsStats, usersStats] = await Promise.all([
            api.get('/api/rbac/roles/statistics'),
            api.get('/api/rbac/permissions/statistics'),
            api.get('/api/rbac/users/statistics')
        ])

        stats.value = {
            ...rolesStats.data,
            ...permissionsStats.data,
            ...usersStats.data
        }
    } catch (error) {
        console.error('Error fetching stats:', error)
    }
}

const fetchRecentActivity = async () => {
    loading.value = true
    try {
        // This would be implemented when you add activity logging
        // const response = await api.get('/api/rbac/activity')
        // recentActivity.value = response.data
        recentActivity.value = []
    } catch (error) {
        console.error('Error fetching activity:', error)
    } finally {
        loading.value = false
    }
}

const formatDate = (dateString: string): string => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const navigateToRoles = () => {
    window.location.href = '/settings/rbac/roles'
}

const navigateToCreateRole = () => {
    window.location.href = '/settings/rbac/roles/create'
}

const navigateToPermissions = () => {
    window.location.href = '/settings/rbac/permissions'
}

const navigateToCreatePermission = () => {
    window.location.href = '/settings/rbac/permissions/create'
}

const navigateToUsers = () => {
    window.location.href = '/settings/rbac/users'
}

const navigateToCreateUser = () => {
    window.location.href = '/settings/rbac/users/create'
}

onMounted(() => {
    fetchStats()
    fetchRecentActivity()
})
</script>
