<template>

    <Head title="Suppliers" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl leading-tight">
                    Suppliers
                </h2>
                <Link :href="route('suppliers.create')">
                <Button>
                    <Plus class="w-4 h-4 mr-2" />
                    Add Supplier
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
                                <Input v-model="searchQuery" placeholder="Search suppliers..." class="w-full"
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

                        <!-- Suppliers Table -->
                        <div class="rounded-md border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Name</TableHead>
                                        <TableHead>Code</TableHead>
                                        <TableHead>Email</TableHead>
                                        <TableHead>Phone</TableHead>
                                        <TableHead>Contact Person</TableHead>
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
                                    <TableRow v-else-if="suppliers.length === 0">
                                        <TableCell colspan="7" class="text-center py-8 text-gray-500">
                                            No suppliers found
                                        </TableCell>
                                    </TableRow>
                                    <TableRow v-else v-for="supplier in suppliers" :key="supplier?.id || Math.random()">
                                        <TableCell>
                                            <div>
                                                <div class="font-medium">{{ supplier?.name || 'N/A' }}</div>
                                            </div>
                                        </TableCell>
                                        <TableCell>{{ supplier?.code || 'N/A' }}</TableCell>
                                        <TableCell>{{ supplier?.email || 'N/A' }}</TableCell>
                                        <TableCell>{{ supplier?.phone || 'N/A' }}</TableCell>
                                        <TableCell>{{ supplier?.contact_person || 'N/A' }}</TableCell>
                                        <TableCell>
                                            <span :class="[
                                                'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                                                supplier?.status === 'active'
                                                    ? 'bg-green-100 text-green-800'
                                                    : 'bg-red-100 text-red-800'
                                            ]">
                                                {{ supplier?.status || 'N/A' }}
                                            </span>
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
                                                        <Link :href="route('suppliers.show', supplier?.id)">
                                                        <Eye class="w-4 h-4 mr-2" />
                                                        View
                                                        </Link>
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem as-child>
                                                        <Link :href="route('suppliers.edit', supplier?.id)">
                                                        <Edit class="w-4 h-4 mr-2" />
                                                        Edit
                                                        </Link>
                                                    </DropdownMenuItem>
                                                    <DropdownMenuSeparator />
                                                    <DropdownMenuItem @click="deleteSupplier(supplier?.id)"
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
                        <div v-if="pagination && pagination.meta && pagination.meta.last_page > 1" class="mt-6">
                            <DataPagination :current-page="pagination.meta.current_page"
                                :total-pages="pagination.meta.last_page" :total-items="pagination.meta.total"
                                :per-page="pagination.meta.per_page" @page-change="changePage" />
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
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import { Plus, MoreHorizontal, Eye, Edit, Trash2, Loader2 } from 'lucide-vue-next'
import { DataPagination } from '@/components/ui/pagination'
import { apiService } from '@/services/api'
import type { Supplier, PaginatedData } from '@/types/erp'

interface Props {
    suppliers?: Supplier[] | any
    pagination?: PaginatedData<Supplier>
}

const props = withDefaults(defineProps<Props>(), {
    suppliers: () => [],
    pagination: undefined
})

const suppliers = ref<Supplier[]>([])
const pagination = ref<PaginatedData<Supplier> | undefined>(props.pagination)
const loading = ref(false)
const searchQuery = ref('')
const statusFilter = ref('')

let searchTimeout: number | null = null

const debouncedSearch = () => {
    if (searchTimeout) {
        clearTimeout(searchTimeout)
    }
    searchTimeout = setTimeout(() => {
        fetchSuppliers()
    }, 300)
}

const fetchSuppliers = async () => {
    loading.value = true
    try {
        const params: any = {}
        if (searchQuery.value) params.search = searchQuery.value
        if (statusFilter.value) params.status = statusFilter.value

        const response = await apiService.getSuppliers(params)
        console.log('Suppliers API Response:', response) // Debug log

        // Ensure we have valid data
        if (response && response.data && Array.isArray(response.data)) {
            suppliers.value = response.data.filter(supplier => supplier && typeof supplier === 'object')
        } else {
            suppliers.value = []
        }

        if (response && response.meta) {
            pagination.value = {
                data: response.data || [],
                links: response.links || [],
                meta: response.meta
            }
        }
    } catch (error) {
        console.error('Error fetching suppliers:', error)
        suppliers.value = []
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

    router.get(`/suppliers?${params.toString()}`)
}

const deleteSupplier = async (id: number) => {
    if (!id) return

    if (confirm('Are you sure you want to delete this supplier?')) {
        try {
            await apiService.deleteSupplier(id)
            await fetchSuppliers()
        } catch (error) {
            console.error('Error deleting supplier:', error)
        }
    }
}

watch(statusFilter, () => {
    fetchSuppliers()
})

onMounted(() => {
    if (suppliers.value.length === 0) {
        fetchSuppliers()
    }
})
</script>
