<template>
    <Head title="Role Details" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <h2 class="font-semibold text-xl leading-tight">
                    Role Details
                </h2>
                <div class="flex gap-2">
                    <Button variant="outline" @click="editRole">
                        <Edit class="w-4 h-4 mr-2" />
                        Edit
                    </Button>
                    <Button variant="outline" @click="navigateBack">
                        <ArrowLeft class="w-4 h-4 mr-2" />
                        Back to Roles
                    </Button>
                </div>
            </div>

            <div v-if="loading" class="flex items-center justify-center py-8">
                <Loader2 class="w-6 h-6 animate-spin mr-2" />
                Loading role details...
            </div>

            <div v-else-if="role" class="space-y-6">
                <!-- Role Info -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Shield class="w-5 h-5 text-primary" />
                            Role Information
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <Label class="text-sm font-medium text-muted-foreground">Name</Label>
                                <p class="text-lg font-medium">{{ role.name }}</p>
                            </div>
                            <div>
                                <Label class="text-sm font-medium text-muted-foreground">Slug</Label>
                                <Badge variant="outline" class="text-sm">
                                    {{ role.slug }}
                                </Badge>
                            </div>
                            <div>
                                <Label class="text-sm font-medium text-muted-foreground">Type</Label>
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
                        </div>
                        <div v-if="role.description">
                            <Label class="text-sm font-medium text-muted-foreground">Description</Label>
                            <p class="text-sm text-muted-foreground">{{ role.description }}</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Assigned Permissions -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Key class="w-5 h-5 text-primary" />
                            Assigned Permissions
                            <Badge variant="secondary">{{ role.permissions?.length || 0 }} permissions</Badge>
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div v-if="!role.permissions || role.permissions.length === 0" class="text-center py-8 text-muted-foreground">
                            <Key class="w-12 h-12 mx-auto mb-4 text-muted-foreground/50" />
                            <p>No permissions assigned to this role</p>
                        </div>
                        <div v-else class="space-y-3">
                            <div v-for="permission in role.permissions" :key="permission.id" class="flex items-center justify-between p-3 border rounded-lg">
                                <div class="flex items-center gap-3">
                                    <Key class="w-4 h-4 text-primary" />
                                    <div>
                                        <p class="font-medium">{{ permission.name }}</p>
                                        <p class="text-sm text-muted-foreground">{{ permission.description }}</p>
                                    </div>
                                </div>
                                <Badge variant="outline" class="text-xs">
                                    {{ permission.module }}.{{ permission.action }}
                                </Badge>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Assigned Users -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Users class="w-5 h-5 text-primary" />
                            Assigned Users
                            <Badge variant="secondary">{{ role.users?.length || 0 }} users</Badge>
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div v-if="!role.users || role.users.length === 0" class="text-center py-8 text-muted-foreground">
                            <Users class="w-12 h-12 mx-auto mb-4 text-muted-foreground/50" />
                            <p>No users assigned to this role</p>
                        </div>
                        <div v-else class="space-y-3">
                            <div v-for="user in role.users" :key="user.id" class="flex items-center justify-between p-3 border rounded-lg">
                                <div class="flex items-center gap-3">
                                    <User class="w-4 h-4 text-primary" />
                                    <div>
                                        <p class="font-medium">{{ user.name }}</p>
                                        <p class="text-sm text-muted-foreground">{{ user.email }}</p>
                                    </div>
                                </div>
                                <Badge :variant="user.is_active ? 'default' : 'secondary'">
                                    {{ user.is_active ? 'Active' : 'Inactive' }}
                                </Badge>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Usage Statistics -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <BarChart3 class="w-5 h-5 text-primary" />
                            Usage Statistics
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="text-center p-4 border rounded-lg">
                                <div class="text-2xl font-bold text-primary">{{ role.permissions?.length || 0 }}</div>
                                <div class="text-sm text-muted-foreground">Assigned Permissions</div>
                            </div>
                            <div class="text-center p-4 border rounded-lg">
                                <div class="text-2xl font-bold text-primary">{{ role.users?.length || 0 }}</div>
                                <div class="text-sm text-muted-foreground">Assigned Users</div>
                            </div>
                            <div class="text-center p-4 border rounded-lg">
                                <div class="text-2xl font-bold text-primary">{{ role.is_system ? 'System' : 'Custom' }}</div>
                                <div class="text-sm text-muted-foreground">Role Type</div>
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
import { ref, onMounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Label } from '@/components/ui/label'
import { Badge } from '@/components/ui/badge'
import { Shield, Key, Users, User, BarChart3, Edit, ArrowLeft, Loader2 } from 'lucide-vue-next'
import type { BreadcrumbItemType } from '@/types'
import { useApi } from '@/composables/useApi'

const api = useApi()

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Settings', href: '/settings' },
    { title: 'Roles & Permissions', href: '/settings/rbac' },
    { title: 'Roles', href: '/settings/rbac/roles' },
    { title: 'Role Details', href: '/settings/rbac/roles/show' }
]

const role = ref(null)
const loading = ref(false)

const fetchRole = async () => {
    loading.value = true
    try {
        const roleId = window.location.pathname.split('/').pop()
        const response = await api.get(`/api/rbac/roles/${roleId}`)
        role.value = response.data
    } catch (error) {
        console.error('Error fetching role:', error)
        window.toast?.error('Failed to load role details')
    } finally {
        loading.value = false
    }
}

const editRole = () => {
    if (role.value) {
        window.location.href = `/settings/rbac/roles/${role.value.id}/edit`
    }
}

const navigateBack = () => {
    window.location.href = '/settings/rbac/roles'
}

onMounted(() => {
    fetchRole()
})
</script>
