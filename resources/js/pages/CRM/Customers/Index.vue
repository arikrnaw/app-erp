<template>

    <Head title="Customers" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="font-semibold text-xl leading-tight">
                    Customers
                </h2>
                <Link :href="route('customers.create')">
                <Button>
                    <Plus class="w-4 h-4 mr-2" />
                    Add Customer
                </Button>
                </Link>
            </div>

            <div class="overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Search and Filters -->
                    <div class="flex flex-col sm:flex-row gap-4 mb-6">
                        <div class="flex-1">
                            <Input v-model="searchQuery" placeholder="Search customers..." class="w-full"
                                @input="debouncedSearch" />
                        </div>
                        <div class="flex gap-2">
                            <Select v-model="statusFilter">
                                <SelectTrigger class="w-[180px]">
                                    <SelectValue placeholder="Status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All Status</SelectItem>
                                    <SelectItem value="active">Active</SelectItem>
                                    <SelectItem value="inactive">Inactive</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>

                    <!-- Customers Table -->
                    <div class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Name</TableHead>
                                    <TableHead>Email</TableHead>
                                    <TableHead>Phone</TableHead>
                                    <TableHead>Company</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead class="text-right">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-if="loading">
                                    <TableCell colspan="6" class="text-center py-8">
                                        <div class="flex items-center justify-center">
                                            <Loader2 class="w-6 h-6 animate-spin mr-2" />
                                            Loading...
                                        </div>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-else-if="customers.length === 0">
                                    <TableCell colspan="6" class="text-center py-8 text-gray-500">
                                        No customers found
                                    </TableCell>
                                </TableRow>
                                <TableRow v-else v-for="customer in customers.filter((c: any) => c && c.id)"
                                    :key="customer.id">
                                    <TableCell>
                                        <div>
                                            <div class="font-medium">{{ customer.name || 'N/A' }}</div>
                                            <div class="text-sm text-gray-500">{{ customer.customer_code || 'N/A' }}
                                            </div>
                                        </div>
                                    </TableCell>
                                    <TableCell>{{ customer.email || 'N/A' }}</TableCell>
                                    <TableCell>{{ customer.phone || 'N/A' }}</TableCell>
                                    <TableCell>{{ customer.company_name || 'N/A' }}</TableCell>
                                    <TableCell>
                                        <Badge :variant="customer.status === 'active' ? 'default' : 'secondary'">
                                            {{ customer.status || 'N/A' }}
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
                                                    <Link :href="route('customers.show', customer.id)">
                                                    <Eye class="w-4 h-4 mr-2" />
                                                    View
                                                    </Link>
                                                </DropdownMenuItem>
                                                <DropdownMenuItem as-child>
                                                    <Link :href="route('customers.edit', customer.id)">
                                                    <Edit class="w-4 h-4 mr-2" />
                                                    Edit
                                                    </Link>
                                                </DropdownMenuItem>
                                                <DropdownMenuSeparator />
                                                <DropdownMenuItem @click="deleteCustomer(customer.id)"
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
                    <div v-if="pagination && pagination.meta?.last_page > 1" class="mt-6">
                        <DataPagination :current-page="pagination.meta?.current_page || 1"
                            :total-pages="pagination.meta?.last_page || 1" :total-items="pagination.meta?.total || 0"
                            :per-page="pagination.meta?.per_page || 15" @page-change="changePage" />
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
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Plus, MoreHorizontal, Eye, Edit, Trash2, Loader2 } from 'lucide-vue-next'
import { DataPagination } from '@/components/ui/pagination'
import { apiService } from '@/services/api'
import type { Customer, PaginatedData } from '@/types/erp'
import type { BreadcrumbItemType } from '@/types'

interface Props {
    customers?: Customer[] | any
    pagination?: PaginatedData<Customer>
}

const props = withDefaults(defineProps<Props>(), {
    customers: () => [],
    pagination: undefined
})

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Customers', href: '/customers' }
]

const customers = ref<Customer[]>([])
const pagination = ref<PaginatedData<Customer> | undefined>(props.pagination)
const loading = ref(false)
const searchQuery = ref('')
const statusFilter = ref('all')

let searchTimeout: number | null = null

const debouncedSearch = () => {
    if (searchTimeout) {
        clearTimeout(searchTimeout)
    }
    searchTimeout = setTimeout(() => {
        fetchCustomers()
    }, 300)
}

const fetchCustomers = async () => {
    loading.value = true
    try {
        const params: any = {}
        if (searchQuery.value) params.search = searchQuery.value
        if (statusFilter.value && statusFilter.value !== 'all') params.status = statusFilter.value

        const response = await apiService.getCustomers(params)
        console.log('API Response:', response) // Debug log

        // Ensure we have valid data
        if (response && response.data && Array.isArray(response.data)) {
            customers.value = response.data.filter(customer => customer && typeof customer === 'object')
        } else {
            customers.value = []
        }

        if (response && response.meta) {
            pagination.value = {
                data: response.data || [],
                links: response.links || [],
                meta: response.meta
            }
        }
    } catch (error) {
        console.error('Error fetching customers:', error)
        customers.value = []
        pagination.value = undefined
    } finally {
        loading.value = false
    }
}

const changePage = (page: number) => {
    const params = new URLSearchParams()
    if (searchQuery.value) params.append('search', searchQuery.value)
    if (statusFilter.value && statusFilter.value !== 'all') params.append('status', statusFilter.value)
    params.append('page', page.toString())

    router.get(`/customers?${params.toString()}`)
}

const deleteCustomer = async (id: number) => {
    if (!id) return

    if (confirm('Are you sure you want to delete this customer?')) {
        try {
            await apiService.deleteCustomer(id)
            await fetchCustomers()
        } catch (error) {
            console.error('Error deleting customer:', error)
        }
    }
}

watch(statusFilter, () => {
    fetchCustomers()
})

onMounted(() => {
    if (customers.value.length === 0) {
        fetchCustomers()
    }
})
</script>
