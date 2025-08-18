<template>
    <Head title="User Management" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <h2 class="font-semibold text-xl leading-tight">
                    User Management
                </h2>
                <Button @click="navigateToCreate">
                    <Plus class="w-4 h-4 mr-2" />
                    Create User
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
                                placeholder="Search users..."
                                @input="debouncedSearch"
                            />
                        </div>
                        <div class="w-full md:w-48">
                            <Label for="status">Status</Label>
                            <Select v-model="filters.is_active">
                                <SelectTrigger>
                                    <SelectValue placeholder="All status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem :value="null">All status</SelectItem>
                                    <SelectItem :value="true">Active</SelectItem>
                                    <SelectItem :value="false">Inactive</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="w-full md:w-48">
                            <Label for="auth_provider">Auth Provider</Label>
                            <Select v-model="filters.auth_provider">
                                <SelectTrigger>
                                    <SelectValue placeholder="All providers" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem :value="null">All providers</SelectItem>
                                    <SelectItem value="email">Email</SelectItem>
                                    <SelectItem value="workos">WorkOS</SelectItem>
                                    <SelectItem value="google">Google</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Users Table -->
            <Card>
                <CardContent class="p-0">
                    <div v-if="loading" class="flex items-center justify-center py-8">
                        <Loader2 class="w-6 h-6 animate-spin mr-2" />
                        Loading users...
                    </div>
                    <div v-else-if="users.length === 0" class="text-center py-8 text-muted-foreground">
                        No users found
                    </div>
                    <div v-else class="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>User</TableHead>
                                    <TableHead>Email</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead>Auth Provider</TableHead>
                                    <TableHead>Roles</TableHead>
                                    <TableHead>Last Login</TableHead>
                                    <TableHead class="text-right">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="user in users" :key="user.id">
                                    <TableCell>
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center">
                                                <span class="text-sm font-medium text-primary">
                                                    {{ user.name.charAt(0).toUpperCase() }}
                                                </span>
                                            </div>
                                            <div>
                                                <div class="font-medium">{{ user.name }}</div>
                                                <div class="text-sm text-muted-foreground">{{ user.company?.name }}</div>
                                            </div>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <span class="text-sm">{{ user.email }}</span>
                                    </TableCell>
                                    <TableCell>
                                        <Badge :variant="user.is_active ? 'default' : 'secondary'">
                                            {{ user.is_active ? 'Active' : 'Inactive' }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        <Badge variant="outline">
                                            {{ user.auth_provider || 'email' }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        <div class="flex flex-wrap gap-1">
                                            <Badge v-for="role in user.roles" :key="role.id" variant="secondary" class="text-xs">
                                                {{ role.name }}
                                            </Badge>
                                            <span v-if="user.roles.length === 0" class="text-sm text-muted-foreground">
                                                No roles
                                            </span>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <span class="text-sm text-muted-foreground">
                                            {{ user.last_login_at ? formatDate(user.last_login_at) : 'Never' }}
                                        </span>
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <DropdownMenu>
                                            <DropdownMenuTrigger as-child>
                                                <Button variant="ghost" size="sm">
                                                    <MoreHorizontal class="w-4 h-4" />
                                                </Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end">
                                                <DropdownMenuItem @click="viewUser(user)">
                                                    <Eye class="w-4 h-4 mr-2" />
                                                    View
                                                </DropdownMenuItem>
                                                <DropdownMenuItem @click="editUser(user)">
                                                    <Edit class="w-4 h-4 mr-2" />
                                                    Edit
                                                </DropdownMenuItem>
                                                <DropdownMenuItem @click="toggleUserStatus(user)">
                                                    <Power class="w-4 h-4 mr-2" />
                                                    {{ user.is_active ? 'Deactivate' : 'Activate' }}
                                                </DropdownMenuItem>
                                                <DropdownMenuItem @click="assignRoles(user)">
                                                    <Shield class="w-4 h-4 mr-2" />
                                                    Assign Roles
                                                </DropdownMenuItem>
                                                <DropdownMenuItem @click="deleteUser(user)" class="text-destructive">
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
import { Plus, MoreHorizontal, Eye, Edit, Trash2, Loader2, Power, Shield } from 'lucide-vue-next'
import type { BreadcrumbItemType } from '@/types'
import { useApi } from '@/composables/useApi'
import { debounce } from 'lodash'

const api = useApi()

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Settings', href: '/settings' },
    { title: 'Roles & Permissions', href: '/settings/rbac' },
    { title: 'Users', href: '/settings/rbac/users' }
]

const users = ref([])
const pagination = ref(null)
const loading = ref(false)
const filters = ref({
    search: '',
    is_active: null,
    auth_provider: null
})

const debouncedSearch = debounce(() => {
    fetchUsers()
}, 300)

const fetchUsers = async () => {
    loading.value = true
    try {
        const params = new URLSearchParams()
        if (filters.value.search) params.append('search', filters.value.search)
        if (filters.value.is_active !== null) params.append('is_active', filters.value.is_active.toString())
        if (filters.value.auth_provider) params.append('auth_provider', filters.value.auth_provider)
        params.append('per_page', '15')

        const response = await api.get(`/api/rbac/users?${params.toString()}`)
        users.value = response.data.data
        pagination.value = {
            data: response.data.data,
            links: response.data.links,
            meta: response.data.meta
        }
    } catch (error) {
        console.error('Error fetching users:', error)
        window.toast?.error('Failed to load users')
    } finally {
        loading.value = false
    }
}

const changePage = (page: number) => {
    const params = new URLSearchParams()
    if (filters.value.search) params.append('search', filters.value.search)
    if (filters.value.is_active !== null) params.append('is_active', filters.value.is_active.toString())
    if (filters.value.auth_provider) params.append('auth_provider', filters.value.auth_provider)
    params.append('page', page.toString())
    params.append('per_page', '15')

    window.location.href = `/settings/rbac/users?${params.toString()}`
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

const navigateToCreate = () => {
    window.location.href = '/settings/rbac/users/create'
}

const viewUser = (user: any) => {
    window.location.href = `/settings/rbac/users/${user.id}`
}

const editUser = (user: any) => {
    window.location.href = `/settings/rbac/users/${user.id}/edit`
}

const toggleUserStatus = async (user: any) => {
    try {
        await api.post(`/api/rbac/users/${user.id}/toggle-status`)
        window.toast?.success(`User ${user.is_active ? 'deactivated' : 'activated'} successfully`)
        fetchUsers()
    } catch (error) {
        console.error('Error toggling user status:', error)
        window.toast?.error('Failed to update user status')
    }
}

const assignRoles = (user: any) => {
    window.location.href = `/settings/rbac/users/${user.id}/edit#roles`
}

const deleteUser = async (user: any) => {
    if (!confirm(`Are you sure you want to delete the user "${user.name}"?`)) {
        return
    }

    try {
        await api.delete(`/api/rbac/users/${user.id}`)
        window.toast?.success('User deleted successfully')
        fetchUsers()
    } catch (error) {
        console.error('Error deleting user:', error)
        window.toast?.error('Failed to delete user')
    }
}

onMounted(() => {
    fetchUsers()
})
</script>
