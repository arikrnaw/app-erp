<template>
    <Head title="Roles Management" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <h2 class="font-semibold text-xl leading-tight">
                    Roles Management
                </h2>
                <Button @click="navigateToCreate">
                    <Plus class="w-4 h-4 mr-2" />
                    Create Role
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
                                placeholder="Search roles..."
                                @input="debouncedSearch"
                            />
                        </div>
                        <div class="w-full md:w-48">
                            <Label for="type">Type</Label>
                            <Select v-model="filters.is_system">
                                <SelectTrigger>
                                    <SelectValue placeholder="All types" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem :value="null">All types</SelectItem>
                                    <SelectItem :value="true">System roles</SelectItem>
                                    <SelectItem :value="false">Custom roles</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Roles Table -->
            <Card>
                <CardContent class="p-0">
                    <div v-if="loading" class="flex items-center justify-center py-8">
                        <Loader2 class="w-6 h-6 animate-spin mr-2" />
                        Loading roles...
                    </div>
                    <div v-else-if="roles.length === 0" class="text-center py-8 text-muted-foreground">
                        No roles found
                    </div>
                    <div v-else class="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Name</TableHead>
                                    <TableHead>Description</TableHead>
                                    <TableHead>Type</TableHead>
                                    <TableHead>Users</TableHead>
                                    <TableHead>Permissions</TableHead>
                                    <TableHead class="text-right">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="role in roles" :key="role.id">
                                    <TableCell>
                                        <div class="flex items-center gap-2">
                                            <Shield class="w-4 h-4 text-primary" />
                                            <span class="font-medium">{{ role.name }}</span>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <span class="text-sm text-muted-foreground">
                                            {{ role.description || 'No description' }}
                                        </span>
                                    </TableCell>
                                    <TableCell>
                                        <Badge :variant="role.is_system ? 'default' : 'secondary'">
                                            {{ role.is_system ? 'System' : 'Custom' }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        <span class="text-sm">{{ role.users_count || 0 }} users</span>
                                    </TableCell>
                                    <TableCell>
                                        <span class="text-sm">{{ role.permissions_count || 0 }} permissions</span>
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <DropdownMenu>
                                            <DropdownMenuTrigger as-child>
                                                <Button variant="ghost" size="sm">
                                                    <MoreHorizontal class="w-4 h-4" />
                                                </Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end">
                                                <DropdownMenuItem @click="viewRole(role)">
                                                    <Eye class="w-4 h-4 mr-2" />
                                                    View
                                                </DropdownMenuItem>
                                                <DropdownMenuItem @click="editRole(role)" v-if="!role.is_system">
                                                    <Edit class="w-4 h-4 mr-2" />
                                                    Edit
                                                </DropdownMenuItem>
                                                <DropdownMenuItem @click="deleteRole(role)" v-if="!role.is_system" class="text-destructive">
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
import { ref, onMounted, computed } from 'vue'
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
import { Shield, Plus, MoreHorizontal, Eye, Edit, Trash2, Loader2 } from 'lucide-vue-next'
import type { BreadcrumbItemType } from '@/types'
import { useApi } from '@/composables/useApi'
import { debounce } from 'lodash'

const api = useApi()

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Settings', href: '/settings' },
    { title: 'Roles & Permissions', href: '/settings/rbac' },
    { title: 'Roles', href: '/settings/rbac/roles' }
]

const roles = ref([])
const pagination = ref(null)
const loading = ref(false)
const filters = ref({
    search: '',
    is_system: null
})

const debouncedSearch = debounce(() => {
    fetchRoles()
}, 300)

const fetchRoles = async () => {
    loading.value = true
    try {
        const params = new URLSearchParams()
        if (filters.value.search) params.append('search', filters.value.search)
        if (filters.value.is_system !== null) params.append('is_system', filters.value.is_system.toString())
        params.append('per_page', '15')

        const response = await api.get(`/api/rbac/roles?${params.toString()}`)
        roles.value = response.data.data
        pagination.value = {
            data: response.data.data,
            links: response.data.links,
            meta: response.data.meta
        }
    } catch (error) {
        console.error('Error fetching roles:', error)
        window.toast?.error('Failed to load roles')
    } finally {
        loading.value = false
    }
}

const changePage = (page: number) => {
    const params = new URLSearchParams()
    if (filters.value.search) params.append('search', filters.value.search)
    if (filters.value.is_system !== null) params.append('is_system', filters.value.is_system.toString())
    params.append('page', page.toString())
    params.append('per_page', '15')

    window.location.href = `/settings/rbac/roles?${params.toString()}`
}

const navigateToCreate = () => {
    window.location.href = '/settings/rbac/roles/create'
}

const viewRole = (role: any) => {
    window.location.href = `/settings/rbac/roles/${role.id}`
}

const editRole = (role: any) => {
    window.location.href = `/settings/rbac/roles/${role.id}/edit`
}

const deleteRole = async (role: any) => {
    if (!confirm(`Are you sure you want to delete the role "${role.name}"?`)) {
        return
    }

    try {
        await api.delete(`/api/rbac/roles/${role.id}`)
        window.toast?.success('Role deleted successfully')
        fetchRoles()
    } catch (error) {
        console.error('Error deleting role:', error)
        window.toast?.error('Failed to delete role')
    }
}

onMounted(() => {
    fetchRoles()
})
</script>
