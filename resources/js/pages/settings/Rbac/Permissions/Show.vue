<template>
    <Head title="Permission Details" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <h2 class="font-semibold text-xl leading-tight">
                    Permission Details
                </h2>
                <div class="flex gap-2">
                    <Button variant="outline" @click="editPermission">
                        <Edit class="w-4 h-4 mr-2" />
                        Edit
                    </Button>
                    <Button variant="outline" @click="navigateBack">
                        <ArrowLeft class="w-4 h-4 mr-2" />
                        Back to Permissions
                    </Button>
                </div>
            </div>

            <div v-if="loading" class="flex items-center justify-center py-8">
                <Loader2 class="w-6 h-6 animate-spin mr-2" />
                Loading permission details...
            </div>

            <div v-else-if="permission" class="space-y-6">
                <!-- Permission Info -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Key class="w-5 h-5 text-primary" />
                            Permission Information
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <Label class="text-sm font-medium text-muted-foreground">Name</Label>
                                <p class="text-lg font-medium">{{ permission.name }}</p>
                            </div>
                            <div>
                                <Label class="text-sm font-medium text-muted-foreground">Slug</Label>
                                <Badge variant="outline" class="text-sm">
                                    {{ permission.slug }}
                                </Badge>
                            </div>
                            <div>
                                <Label class="text-sm font-medium text-muted-foreground">Module</Label>
                                <Badge variant="secondary">
                                    {{ moduleLabels[permission.module] || permission.module }}
                                </Badge>
                            </div>
                            <div>
                                <Label class="text-sm font-medium text-muted-foreground">Action</Label>
                                <Badge variant="outline">
                                    {{ permission.action }}
                                </Badge>
                            </div>
                        </div>
                        <div v-if="permission.description">
                            <Label class="text-sm font-medium text-muted-foreground">Description</Label>
                            <p class="text-sm text-muted-foreground">{{ permission.description }}</p>
                        </div>
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground">Created</Label>
                            <p class="text-sm text-muted-foreground">
                                {{ new Date(permission.created_at).toLocaleDateString() }}
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
                            <Badge variant="secondary">{{ permission.roles?.length || 0 }} roles</Badge>
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div v-if="!permission.roles || permission.roles.length === 0" class="text-center py-8 text-muted-foreground">
                            <Shield class="w-12 h-12 mx-auto mb-4 text-muted-foreground/50" />
                            <p>No roles assigned to this permission</p>
                        </div>
                        <div v-else class="space-y-3">
                            <div v-for="role in permission.roles" :key="role.id" class="flex items-center justify-between p-3 border rounded-lg">
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
                                <div class="text-2xl font-bold text-primary">{{ permission.roles_count || 0 }}</div>
                                <div class="text-sm text-muted-foreground">Assigned Roles</div>
                            </div>
                            <div class="text-center p-4 border rounded-lg">
                                <div class="text-2xl font-bold text-primary">{{ permission.users_count || 0 }}</div>
                                <div class="text-sm text-muted-foreground">Users with Permission</div>
                            </div>
                            <div class="text-center p-4 border rounded-lg">
                                <div class="text-2xl font-bold text-primary">{{ permission.created_at ? 'Active' : 'Inactive' }}</div>
                                <div class="text-sm text-muted-foreground">Status</div>
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
import { Label } from '@/components/ui/label'
import { Badge } from '@/components/ui/badge'
import { Key, Shield, BarChart3, Edit, ArrowLeft, Loader2 } from 'lucide-vue-next'
import type { BreadcrumbItemType } from '@/types'
import { useApi } from '@/composables/useApi'

const api = useApi()

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Settings', href: '/settings' },
    { title: 'Roles & Permissions', href: '/settings/rbac' },
    { title: 'Permissions', href: '/settings/rbac/permissions' },
    { title: 'Permission Details', href: '/settings/rbac/permissions/show' }
]

const permission = ref(null)
const loading = ref(false)

const moduleLabels = {
    'crm': 'Customer Relationship Management',
    'projects': 'Project Management',
    'reports': 'Reports & Analytics',
    'customer-service': 'Customer Service',
    'settings': 'Settings',
    'erp': 'ERP Core'
}

const fetchPermission = async () => {
    loading.value = true
    try {
        const permissionId = window.location.pathname.split('/').pop()
        const response = await api.get(`/api/rbac/permissions/${permissionId}`)
        permission.value = response.data
    } catch (error) {
        console.error('Error fetching permission:', error)
        window.toast?.error('Failed to load permission details')
    } finally {
        loading.value = false
    }
}

const editPermission = () => {
    if (permission.value) {
        window.location.href = `/settings/rbac/permissions/${permission.value.id}/edit`
    }
}

const navigateBack = () => {
    window.location.href = '/settings/rbac/permissions'
}

onMounted(() => {
    fetchPermission()
})
</script>
