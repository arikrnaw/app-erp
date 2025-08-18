<template>
    <Head title="Permissions Management" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <h2 class="font-semibold text-xl leading-tight">
                    Permissions Management
                </h2>
                <Button @click="navigateToCreate">
                    <Plus class="w-4 h-4 mr-2" />
                    Create Permission
                </Button>
            </div>

            <!-- Filters -->
            <Card class="mb-6">
                <CardContent class="p-4">
                    <div class="flex flex-col md:flex-row gap-4">
                        <div class="flex-1">
                            <Label for="search">Search</Label>
                            <Input
                                id="search"
                                v-model="filters.search"
                                placeholder="Search permissions..."
                                @input="debouncedSearch"
                            />
                        </div>
                        <div class="w-full md:w-48">
                            <Label for="module">Module</Label>
                            <Select v-model="filters.module">
                                <SelectTrigger>
                                    <SelectValue placeholder="All modules" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem :value="null">All modules</SelectItem>
                                    <SelectItem v-for="module in modules" :key="module" :value="module">
                                        {{ moduleLabels[module] || module }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="w-full md:w-48">
                            <Label for="action">Action</Label>
                            <Select v-model="filters.action">
                                <SelectTrigger>
                                    <SelectValue placeholder="All actions" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem :value="null">All actions</SelectItem>
                                    <SelectItem v-for="action in actions" :key="action" :value="action">
                                        {{ action }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Permissions Table -->
            <Card>
                <CardContent class="p-0">
                    <div v-if="loading" class="flex items-center justify-center py-8">
                        <Loader2 class="w-6 h-6 animate-spin mr-2" />
                        Loading permissions...
                    </div>
                    <div v-else-if="permissions.length === 0" class="text-center py-8 text-muted-foreground">
                        No permissions found
                    </div>
                    <div v-else class="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Permission</TableHead>
                                    <TableHead>Module</TableHead>
                                    <TableHead>Action</TableHead>
                                    <TableHead>Description</TableHead>
                                    <TableHead>Roles</TableHead>
                                    <TableHead class="text-right">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="permission in permissions" :key="permission.id">
                                    <TableCell>
                                        <div class="flex items-center gap-2">
                                            <Key class="w-4 h-4 text-primary" />
                                            <span class="font-medium">{{ permission.name }}</span>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <Badge variant="outline">
                                            {{ moduleLabels[permission.module] || permission.module }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        <Badge variant="secondary">
                                            {{ permission.action }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        <span class="text-sm text-muted-foreground">
                                            {{ permission.description || 'No description' }}
                                        </span>
                                    </TableCell>
                                    <TableCell>
                                        <span class="text-sm">{{ permission.roles_count || 0 }} roles</span>
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <DropdownMenu>
                                            <DropdownMenuTrigger as-child>
                                                <Button variant="ghost" size="sm">
                                                    <MoreHorizontal class="w-4 h-4" />
                                                </Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end">
                                                <DropdownMenuItem @click="viewPermission(permission)">
                                                    <Eye class="w-4 h-4 mr-2" />
                                                    View
                                                </DropdownMenuItem>
                                                <DropdownMenuItem @click="editPermission(permission)">
                                                    <Edit class="w-4 h-4 mr-2" />
                                                    Edit
                                                </DropdownMenuItem>
                                                <DropdownMenuItem @click="deletePermission(permission)" class="text-destructive">
                                                    <Trash2 class="w-4 h-4 mr-2" />
                                                    Delete
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="pagination && pagination.meta.last_page > 1" class="p-4 border-t">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-muted-foreground">
                                Showing {{ pagination.meta.from }} to {{ pagination.meta.to }} of {{ pagination.meta.total }} results
                            </div>
                            <div class="flex items-center gap-2">
                                <Button
                                    variant="outline"
                                    size="sm"
                                    :disabled="pagination.meta.current_page === 1"
                                    @click="changePage(pagination.meta.current_page - 1)"
                                >
                                    Previous
                                </Button>
                                <span class="text-sm">
                                    Page {{ pagination.meta.current_page }} of {{ pagination.meta.last_page }}
                                </span>
                                <Button
                                    variant="outline"
                                    size="sm"
                                    :disabled="pagination.meta.current_page === pagination.meta.last_page"
                                    @click="changePage(pagination.meta.current_page + 1)"
                                >
                                    Next
                                </Button>
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
import { Card, CardContent } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Badge } from '@/components/ui/badge'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import { Key, Plus, MoreHorizontal, Eye, Edit, Trash2, Loader2 } from 'lucide-vue-next'
import type { BreadcrumbItemType } from '@/types'
import { useApi } from '@/composables/useApi'
import { debounce } from 'lodash'

const api = useApi()

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Settings', href: '/settings' },
    { title: 'Roles & Permissions', href: '/settings/rbac' },
    { title: 'Permissions', href: '/settings/rbac/permissions' }
]

const permissions = ref([])
const pagination = ref(null)
const loading = ref(false)
const modules = ref([])
const actions = ref([])
const filters = ref({
    search: '',
    module: null,
    action: null
})

const moduleLabels = {
    'crm': 'Customer Relationship Management',
    'projects': 'Project Management',
    'reports': 'Reports & Analytics',
    'customer-service': 'Customer Service',
    'settings': 'Settings',
    'erp': 'ERP Core'
}

const debouncedSearch = debounce(() => {
    fetchPermissions()
}, 300)

const fetchPermissions = async () => {
    loading.value = true
    try {
        const params = new URLSearchParams()
        if (filters.value.search) params.append('search', filters.value.search)
        if (filters.value.module) params.append('module', filters.value.module)
        if (filters.value.action) params.append('action', filters.value.action)
        params.append('per_page', '15')

        const response = await api.get(`/api/rbac/permissions?${params.toString()}`)
        permissions.value = response.data.data
        pagination.value = {
            data: response.data.data,
            links: response.data.links,
            meta: response.data.meta
        }
    } catch (error) {
        console.error('Error fetching permissions:', error)
        window.toast?.error('Failed to load permissions')
    } finally {
        loading.value = false
    }
}

const fetchModules = async () => {
    try {
        const response = await api.get('/api/rbac/permissions/modules')
        modules.value = response.data
    } catch (error) {
        console.error('Error fetching modules:', error)
    }
}

const fetchActions = async () => {
    try {
        const response = await api.get('/api/rbac/permissions/actions')
        actions.value = response.data
    } catch (error) {
        console.error('Error fetching actions:', error)
    }
}

const changePage = (page: number) => {
    const params = new URLSearchParams()
    if (filters.value.search) params.append('search', filters.value.search)
    if (filters.value.module) params.append('module', filters.value.module)
    if (filters.value.action) params.append('action', filters.value.action)
    params.append('page', page.toString())
    params.append('per_page', '15')

    window.location.href = `/settings/rbac/permissions?${params.toString()}`
}

const navigateToCreate = () => {
    window.location.href = '/settings/rbac/permissions/create'
}

const viewPermission = (permission: any) => {
    window.location.href = `/settings/rbac/permissions/${permission.id}`
}

const editPermission = (permission: any) => {
    window.location.href = `/settings/rbac/permissions/${permission.id}/edit`
}

const deletePermission = async (permission: any) => {
    if (!confirm(`Are you sure you want to delete the permission "${permission.name}"?`)) {
        return
    }

    try {
        await api.delete(`/api/rbac/permissions/${permission.id}`)
        window.toast?.success('Permission deleted successfully')
        fetchPermissions()
    } catch (error) {
        console.error('Error deleting permission:', error)
        window.toast?.error('Failed to delete permission')
    }
}

onMounted(() => {
    fetchPermissions()
    fetchModules()
    fetchActions()
})
</script>
