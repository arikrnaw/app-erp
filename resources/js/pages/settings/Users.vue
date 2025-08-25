<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-medium">User Management</h3>
                    <p class="text-sm text-muted-foreground">
                        Manage system users, roles, and permissions.
                    </p>
                </div>
                <Button @click="showCreateDialog = true">
                    <Users class="h-4 w-4 mr-2" />
                    Add User
                </Button>
            </div>

            <!-- Search and Filters -->
            <div class="flex gap-4">
                <div class="flex-1">
                    <Input v-model="searchQuery" placeholder="Search users..." class="max-w-sm" />
                </div>
                <Select v-model="roleFilter">
                    <SelectTrigger class="w-48">
                        <SelectValue placeholder="Filter by role" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="">All Roles</SelectItem>
                        <SelectItem value="admin">Administrator</SelectItem>
                        <SelectItem value="manager">Manager</SelectItem>
                        <SelectItem value="user">User</SelectItem>
                    </SelectContent>
                </Select>
                <Select v-model="statusFilter">
                    <SelectTrigger class="w-48">
                        <SelectValue placeholder="Filter by status" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="">All Status</SelectItem>
                        <SelectItem value="active">Active</SelectItem>
                        <SelectItem value="inactive">Inactive</SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <!-- Users Table -->
            <div class="border rounded-lg">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>User</TableHead>
                            <TableHead>Email</TableHead>
                            <TableHead>Role</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead>Last Login</TableHead>
                            <TableHead>Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="user in filteredUsers" :key="user.id">
                            <TableCell>
                                <div class="flex items-center space-x-3">
                                    <Avatar>
                                        <AvatarImage :src="user.avatar || ''" />
                                        <AvatarFallback>{{ getUserInitials(user.name) }}</AvatarFallback>
                                    </Avatar>
                                    <div>
                                        <div class="font-medium">{{ user.name }}</div>
                                        <div class="text-sm text-muted-foreground">{{ user.username }}</div>
                                    </div>
                                </div>
                            </TableCell>
                            <TableCell>{{ user.email }}</TableCell>
                            <TableCell>
                                <Badge :variant="getRoleVariant(user.role)">
                                    {{ user.role }}
                                </Badge>
                            </TableCell>
                            <TableCell>
                                <Badge :variant="user.is_active ? 'default' : 'secondary'">
                                    {{ user.is_active ? 'Active' : 'Inactive' }}
                                </Badge>
                            </TableCell>
                            <TableCell>{{ formatDate(user.last_login) }}</TableCell>
                            <TableCell>
                                <div class="flex items-center space-x-2">
                                    <Button variant="ghost" size="sm" @click="editUser(user)">
                                        <Edit class="h-4 w-4" />
                                    </Button>
                                    <Button variant="ghost" size="sm" @click="toggleUserStatus(user)">
                                        <Power class="h-4 w-4" />
                                    </Button>
                                    <Button variant="ghost" size="sm" @click="deleteUser(user)">
                                        <Trash2 class="h-4 w-4" />
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <!-- Pagination -->
            <DataPagination :total-items="filteredUsers.length" :total-pages="Math.ceil(filteredUsers.length / perPage)"
                :per-page="perPage" :current-page="currentPage" @page-change="currentPage = $event" />

            <!-- Create User Dialog -->
            <Dialog v-model:open="showCreateDialog">
                <DialogContent class="sm:max-w-[500px]">
                    <DialogHeader>
                        <DialogTitle>Create New User</DialogTitle>
                        <DialogDescription>
                            Add a new user to the system with appropriate roles and permissions.
                        </DialogDescription>
                    </DialogHeader>
                    <form @submit.prevent="createUser" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="name">Full Name</Label>
                                <Input id="name" v-model="newUser.name" required />
                            </div>
                            <div class="space-y-2">
                                <Label for="username">Username</Label>
                                <Input id="username" v-model="newUser.username" required />
                            </div>
                        </div>
                        <div class="space-y-2">
                            <Label for="email">Email</Label>
                            <Input id="email" v-model="newUser.email" type="email" required />
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="password">Password</Label>
                                <Input id="password" v-model="newUser.password" type="password" required />
                            </div>
                            <div class="space-y-2">
                                <Label for="role">Role</Label>
                                <Select v-model="newUser.role" required>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select role" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="user">User</SelectItem>
                                        <SelectItem value="manager">Manager</SelectItem>
                                        <SelectItem value="admin">Administrator</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                        <div class="flex justify-end space-x-2">
                            <Button type="button" variant="outline" @click="showCreateDialog = false">
                                Cancel
                            </Button>
                            <Button type="submit" :disabled="creating">
                                <span v-if="creating">Creating...</span>
                                <span v-else>Create User</span>
                            </Button>
                        </div>
                    </form>
                </DialogContent>
            </Dialog>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, reactive } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { DataPagination } from '@/components/ui/pagination';
import { Users, Edit, Power, Trash2 } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';

const breadcrumbs = [
    { title: 'Settings', href: '/settings/rbac' },
    { title: 'User Management', href: '/settings/rbac/users-management' }
];

// Mock data - replace with actual API calls
const users = ref([
    {
        id: 1,
        name: 'John Doe',
        username: 'johndoe',
        email: 'john@example.com',
        role: 'admin',
        is_active: true,
        avatar: null,
        last_login: '2024-01-15T10:30:00Z'
    },
    {
        id: 2,
        name: 'Jane Smith',
        username: 'janesmith',
        email: 'jane@example.com',
        role: 'manager',
        is_active: true,
        avatar: null,
        last_login: '2024-01-14T15:45:00Z'
    },
    {
        id: 3,
        name: 'Bob Johnson',
        username: 'bobjohnson',
        email: 'bob@example.com',
        role: 'user',
        is_active: false,
        avatar: null,
        last_login: '2024-01-10T09:15:00Z'
    }
]);

const searchQuery = ref('');
const roleFilter = ref('');
const statusFilter = ref('');
const showCreateDialog = ref(false);
const creating = ref(false);
const currentPage = ref(1);
const perPage = 10;

const newUser = reactive({
    name: '',
    username: '',
    email: '',
    password: '',
    role: 'user'
});

const filteredUsers = computed(() => {
    let filtered = users.value;

    if (searchQuery.value) {
        filtered = filtered.filter(user =>
            user.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            user.email.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            user.username.toLowerCase().includes(searchQuery.value.toLowerCase())
        );
    }

    if (roleFilter.value) {
        filtered = filtered.filter(user => user.role === roleFilter.value);
    }

    if (statusFilter.value) {
        const isActive = statusFilter.value === 'active';
        filtered = filtered.filter(user => user.is_active === isActive);
    }

    return filtered;
});

const getUserInitials = (name: string) => {
    return name.split(' ').map(n => n[0]).join('').toUpperCase();
};

const getRoleVariant = (role: string) => {
    switch (role) {
        case 'admin': return 'destructive';
        case 'manager': return 'default';
        default: return 'secondary';
    }
};

const formatDate = (dateString: string) => {
    if (!dateString) return 'Never';
    return new Date(dateString).toLocaleDateString();
};

const createUser = async () => {
    creating.value = true;
    try {
        // TODO: Implement API call to create user
        await new Promise(resolve => setTimeout(resolve, 1000)); // Simulate API call

        const user = {
            id: users.value.length + 1,
            name: newUser.name,
            username: newUser.username,
            email: newUser.email,
            role: newUser.role,
            is_active: true,
            avatar: null,
            last_login: ''
        };

        users.value.push(user);

        // Reset form
        Object.assign(newUser, {
            name: '',
            username: '',
            email: '',
            password: '',
            role: 'user'
        });

        showCreateDialog.value = false;
        window.toast?.success('User created successfully');
    } catch (error) {
        window.toast?.error('Failed to create user');
    } finally {
        creating.value = false;
    }
};

const editUser = (user: any) => {
    // TODO: Implement edit user functionality
    console.log('Edit user:', user);
};

const toggleUserStatus = async (user: any) => {
    try {
        // TODO: Implement API call to toggle user status
        await new Promise(resolve => setTimeout(resolve, 500)); // Simulate API call
        user.is_active = !user.is_active;
        window.toast?.success(`User ${user.is_active ? 'activated' : 'deactivated'} successfully`);
    } catch (error) {
        window.toast?.error('Failed to update user status');
    }
};

const deleteUser = async (user: any) => {
    if (!confirm(`Are you sure you want to delete ${user.name}?`)) return;

    try {
        // TODO: Implement API call to delete user
        await new Promise(resolve => setTimeout(resolve, 500)); // Simulate API call
        const index = users.value.findIndex(u => u.id === user.id);
        if (index > -1) {
            users.value.splice(index, 1);
        }
        window.toast?.success('User deleted successfully');
    } catch (error) {
        window.toast?.error('Failed to delete user');
    }
};
</script>
