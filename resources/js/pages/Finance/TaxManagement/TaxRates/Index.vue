<template>

    <Head title="Tax Rates" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="font-semibold text-xl leading-tight">
                    Tax Rates
                </h2>
                <Link :href="route('finance.tax-management.tax-rates.create')">
                <Button>
                    <Plus class="w-4 h-4 mr-2" />
                    Add Tax Rate
                </Button>
                </Link>
            </div>

            <div class="overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Search and Filters -->
                    <div class="flex flex-col sm:flex-row gap-4 mb-6">
                        <div class="flex-1">
                            <Input v-model="searchQuery" placeholder="Search tax rates..." class="w-full"
                                @input="debouncedSearch" />
                        </div>
                        <div class="flex gap-2">
                            <Select v-model="statusFilter">
                                <SelectTrigger class="w-[180px]">
                                    <SelectValue placeholder="Status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All Status</SelectItem>
                                    <SelectItem value="true">Active</SelectItem>
                                    <SelectItem value="false">Inactive</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>

                    <!-- Tax Rates Table -->
                    <div class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Name</TableHead>
                                    <TableHead>Rate (%)</TableHead>
                                    <TableHead>Description</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead>Created</TableHead>
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
                                <TableRow v-else-if="taxRates.length === 0">
                                    <TableCell colspan="6" class="text-center py-8 text-gray-500">
                                        No tax rates found
                                    </TableCell>
                                </TableRow>
                                <TableRow v-else v-for="taxRate in taxRates.filter((tr: any) => tr && tr.id)"
                                    :key="taxRate.id">
                                    <TableCell>
                                        <div class="font-medium">{{ taxRate.name }}</div>
                                    </TableCell>
                                    <TableCell>
                                        <div class="text-sm font-mono">{{ taxRate.rate }}%</div>
                                    </TableCell>
                                    <TableCell>
                                        <div class="text-sm text-white">{{ taxRate.description || 'N/A' }}</div>
                                    </TableCell>
                                    <TableCell>
                                        <Badge :variant="taxRate.is_active ? 'default' : 'secondary'">
                                            {{ taxRate.is_active ? 'Active' : 'Inactive' }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        <div class="text-sm">{{ formatDate(taxRate.created_at) }}</div>
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
                                                    <Link
                                                        :href="route('finance.tax-management.tax-rates.show', taxRate.id)">
                                                    <Eye class="w-4 h-4 mr-2" />
                                                    View
                                                    </Link>
                                                </DropdownMenuItem>
                                                <DropdownMenuItem as-child>
                                                    <Link
                                                        :href="route('finance.tax-management.tax-rates.edit', taxRate.id)">
                                                    <Edit class="w-4 h-4 mr-2" />
                                                    Edit
                                                    </Link>
                                                </DropdownMenuItem>
                                                <DropdownMenuSeparator />
                                                <DropdownMenuItem @click="deleteTaxRate(taxRate.id)"
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
                            Showing {{ pagination.meta.from }} to {{ pagination.meta.to }} of {{ pagination.meta.total
                            }}
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
import { apiService } from '@/services/api'
import type { TaxRate, PaginatedData } from '@/types/erp'
import type { BreadcrumbItemType } from '@/types'

interface Props {
    taxRates?: TaxRate[] | any
    pagination?: PaginatedData<TaxRate>
}

const props = withDefaults(defineProps<Props>(), {
    taxRates: () => [],
    pagination: undefined
})

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Finance', href: '/finance' },
    { title: 'Tax Management', href: '/finance/tax-management' },
    { title: 'Tax Rates', href: '/finance/tax-management/tax-rates' }
]

const taxRates = ref<TaxRate[]>([])
const pagination = ref<PaginatedData<TaxRate> | undefined>(props.pagination)
const loading = ref(false)
const searchQuery = ref('')
const statusFilter = ref('all')

let searchTimeout: number | null = null

const debouncedSearch = () => {
    if (searchTimeout) {
        clearTimeout(searchTimeout)
    }
    searchTimeout = setTimeout(() => {
        fetchTaxRates()
    }, 300)
}

const fetchTaxRates = async () => {
    loading.value = true
    try {
        const params: any = {}
        if (searchQuery.value) params.search = searchQuery.value
        if (statusFilter.value && statusFilter.value !== 'all') params.is_active = statusFilter.value

        const response = await apiService.getTaxRates(params)
        console.log('Tax Rates API Response:', response)

        if (response && response.data && Array.isArray(response.data)) {
            taxRates.value = response.data.filter(taxRate => taxRate && typeof taxRate === 'object')
        } else {
            taxRates.value = []
        }

        if (response && response.meta) {
            pagination.value = {
                data: response.data || [],
                links: response.links || [],
                meta: response.meta
            }
        }
    } catch (error) {
        console.error('Error fetching tax rates:', error)
        taxRates.value = []
        pagination.value = undefined
    } finally {
        loading.value = false
    }
}

const changePage = (page: number) => {
    const params = new URLSearchParams()
    if (searchQuery.value) params.append('search', searchQuery.value)
    if (statusFilter.value && statusFilter.value !== 'all') params.append('is_active', statusFilter.value)
    params.append('page', page.toString())

    router.get(`/finance/tax-management/tax-rates?${params.toString()}`)
}

const deleteTaxRate = async (id: number) => {
    if (!id) return

    if (confirm('Are you sure you want to delete this tax rate?')) {
        try {
            await apiService.deleteTaxRate(id)
            await fetchTaxRates()
        } catch (error) {
            console.error('Error deleting tax rate:', error)
        }
    }
}

const formatDate = (dateString: string): string => {
    if (!dateString) return 'N/A'
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    })
}

watch([statusFilter], () => {
    fetchTaxRates()
})

onMounted(() => {
    if (taxRates.value.length === 0) {
        fetchTaxRates()
    }
})
</script>
