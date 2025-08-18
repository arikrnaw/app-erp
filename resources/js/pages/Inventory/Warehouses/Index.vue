<template>

    <Head title="Warehouse Management" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl leading-tight">
                    Warehouse Management
                </h2>
                <Link :href="route('inventory.warehouses.create')">
                <Button>
                    <Plus class="w-4 h-4 mr-2" />
                    Add Warehouse
                </Button>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <!-- Search and Filters -->
                        <div class="flex flex-col sm:flex-row gap-4 mb-6">
                            <div class="flex-1">
                                <Input v-model="searchQuery" placeholder="Search warehouses..." class="w-full"
                                    @input="debouncedSearch" />
                            </div>
                            <div class="flex gap-2">
                                <select v-model="statusFilter"
                                    class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="">All Status</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>

                        <!-- Warehouses Table -->
                        <div class="rounded-md border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Code</TableHead>
                                        <TableHead>Name</TableHead>
                                        <TableHead>Manager</TableHead>
                                        <TableHead>Contact</TableHead>
                                        <TableHead>Locations</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead class="text-right">Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-if="loading">
                                        <TableCell colspan="7" class="text-center py-8">
                                            <div class="flex items-center justify-center">
                                                <Loader2 class="w-6 h-6 animate-spin mr-2" />
                                                Loading...
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                    <TableRow v-else-if="warehouses.length === 0">
                                        <TableCell colspan="7" class="text-center py-8 text-gray-500">
                                            No warehouses found
                                        </TableCell>
                                    </TableRow>
                                    <TableRow v-else v-for="warehouse in warehouses" :key="warehouse?.id">
                                        <TableCell>
                                            <div class="font-medium">{{ warehouse?.code || 'N/A' }}</div>
                                        </TableCell>
                                        <TableCell>
                                            <div>
                                                <div class="font-medium">{{ warehouse?.name || 'N/A' }}</div>
                                                <div class="text-sm text-gray-500">{{ warehouse?.address || 'No address'
                                                    }}
                                                </div>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div class="text-sm">
                                                {{ warehouse?.manager_name || 'No manager' }}
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div class="text-sm">
                                                <div>{{ warehouse?.phone || 'No phone' }}</div>
                                                <div class="text-gray-500">{{ warehouse?.email || 'No email' }}</div>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <Badge variant="secondary">
                                                {{ warehouse?.locations?.length || 0 }} locations
                                            </Badge>
                                        </TableCell>
                                        <TableCell>
                                            <Badge :variant="getStatusVariant(warehouse?.status)">
                                                {{ getStatusLabel(warehouse?.status) }}
                                            </Badge>
                                        </TableCell>
                                        <TableCell class="text-right">
                                            <DropdownMenu>
                                                <DropdownMenuTrigger as-child>
                                                    <Button variant="ghost" class="h-8 w-8 p-0">
                                                        <MoreHorizontal class="h-4 w-4" />
                                                    </Button>
                                                </DropdownMenuTrigger>
                                                <DropdownMenuContent align="end">
                                                    <DropdownMenuItem as-child>
                                                        <Link :href="route('inventory.warehouses.show', warehouse?.id)">
                                                        <Eye class="w-4 h-4 mr-2" />
                                                        View
                                                        </Link>
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem as-child>
                                                        <Link :href="route('inventory.warehouses.edit', warehouse?.id)">
                                                        <Edit class="w-4 h-4 mr-2" />
                                                        Edit
                                                        </Link>
                                                    </DropdownMenuItem>
                                                    <DropdownMenuSeparator />
                                                    <DropdownMenuItem @click="deleteWarehouse(warehouse?.id)"
                                                        class="text-red-600">
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
                        <div v-if="pagination && pagination.meta && pagination.meta.last_page > 1"
                            class="flex items-center justify-between mt-6">
                            <div class="text-sm text-gray-700">
                                Showing {{ pagination.meta.from }} to {{ pagination.meta.to }} of {{
                                    pagination.meta.total }}
                                results
                            </div>
                            <div class="flex gap-2">
                                <Button variant="outline" size="sm" :disabled="pagination.meta.current_page === 1"
                                    @click="changePage(pagination.meta.current_page - 1)">
                                    Previous
                                </Button>
                                <Button variant="outline" size="sm"
                                    :disabled="pagination.meta.current_page === pagination.meta.last_page"
                                    @click="changePage(pagination.meta.current_page + 1)">
                                    Next
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Badge } from '@/components/ui/badge'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import { Plus, MoreHorizontal, Eye, Edit, Trash2, Loader2 } from 'lucide-vue-next'
import { apiService } from '@/services/api'
import type { Warehouse, PaginatedData } from '@/types/erp'

interface Props {
    warehouses?: Warehouse[] | any
    pagination?: PaginatedData<Warehouse>
}

const props = withDefaults(defineProps<Props>(), {
    warehouses: () => [],
    pagination: undefined
})

const warehouses = ref<Warehouse[]>([])
const pagination = ref<PaginatedData<Warehouse> | undefined>(props.pagination)
const loading = ref(false)
const searchQuery = ref('')
const statusFilter = ref('')

let searchTimeout: number | null = null

const debouncedSearch = () => {
    if (searchTimeout) {
        clearTimeout(searchTimeout)
    }
    searchTimeout = setTimeout(() => {
        fetchWarehouses()
    }, 300)
}

const fetchWarehouses = async () => {
    loading.value = true
    try {
        const params: any = {}
        if (searchQuery.value) params.search = searchQuery.value
        if (statusFilter.value) params.status = statusFilter.value

        const response = await apiService.getWarehouses(params)
        console.log('Warehouse API Response:', response)

        if (response && response.data && Array.isArray(response.data)) {
            warehouses.value = response.data.filter(warehouse => warehouse && typeof warehouse === 'object')
        } else {
            warehouses.value = []
        }

        if (response && response.meta) {
            pagination.value = {
                data: response.data || [],
                links: response.links || [],
                meta: response.meta
            }
        }
    } catch (error) {
        console.error('Error fetching warehouses:', error)
        warehouses.value = []
        pagination.value = undefined
    } finally {
        loading.value = false
    }
}

const changePage = (page: number) => {
    const params = new URLSearchParams()
    if (searchQuery.value) params.append('search', searchQuery.value)
    if (statusFilter.value) params.append('status', statusFilter.value)
    params.append('page', page.toString())

    router.get(`/inventory/warehouses?${params.toString()}`)
}

const deleteWarehouse = async (id: number) => {
    if (!id) return

    if (confirm('Are you sure you want to delete this warehouse?')) {
        try {
            await apiService.deleteWarehouse(id)
            await fetchWarehouses()
        } catch (error) {
            console.error('Error deleting warehouse:', error)
        }
    }
}

const getStatusLabel = (status: string): string => {
    const labels = {
        'active': 'Active',
        'inactive': 'Inactive'
    }
    return labels[status as keyof typeof labels] || status || 'N/A'
}

const getStatusVariant = (status: string): 'default' | 'secondary' | 'destructive' => {
    const variants: Record<string, 'default' | 'secondary' | 'destructive'> = {
        'active': 'default',
        'inactive': 'secondary'
    }
    return variants[status] || 'secondary'
}

watch([statusFilter], () => {
    fetchWarehouses()
})

onMounted(() => {
    if (warehouses.value.length === 0) {
        fetchWarehouses()
    }
})
</script>
