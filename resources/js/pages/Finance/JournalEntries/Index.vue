<template>

    <Head title="Journal Entries" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="font-semibold text-xl leading-tight">
                    Journal Entries
                </h2>
                <Link :href="route('finance.journal-entries.create')">
                <Button>
                    <Plus class="w-4 h-4 mr-2" />
                    Add Journal Entry
                </Button>
                </Link>
            </div>

            <div class="overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Search and Filters -->
                    <div class="flex flex-col sm:flex-row gap-4 mb-6">
                        <div class="flex-1">
                            <Input v-model="searchQuery" placeholder="Search journal entries..." class="w-full"
                                @input="debouncedSearch" />
                        </div>
                        <div class="flex gap-2">
                            <Select v-model="statusFilter">
                                <SelectTrigger class="w-[180px]">
                                    <SelectValue placeholder="Status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All Status</SelectItem>
                                    <SelectItem value="draft">Draft</SelectItem>
                                    <SelectItem value="posted">Posted</SelectItem>
                                    <SelectItem value="cancelled">Cancelled</SelectItem>
                                </SelectContent>
                            </Select>
                            <Input type="date" v-model="dateFilter" class="w-[180px]" />
                        </div>
                    </div>

                    <!-- Journal Entries Table -->
                    <div class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Entry Number</TableHead>
                                    <TableHead>Date</TableHead>
                                    <TableHead>Reference</TableHead>
                                    <TableHead>Description</TableHead>
                                    <TableHead>Total Debit</TableHead>
                                    <TableHead>Total Credit</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead class="text-right">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-if="loading">
                                    <TableCell colspan="8" class="text-center py-8">
                                        <div class="flex items-center justify-center">
                                            <Loader2 class="w-6 h-6 animate-spin mr-2" />
                                            Loading...
                                        </div>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-else-if="journalEntries.length === 0">
                                    <TableCell colspan="8" class="text-center py-8 text-gray-500">
                                        No journal entries found
                                    </TableCell>
                                </TableRow>
                                <TableRow v-else v-for="entry in journalEntries.filter((e: any) => e && e.id)"
                                    :key="entry.id">
                                    <TableCell>
                                        <div class="font-mono text-sm">{{ entry.entry_number }}</div>
                                    </TableCell>
                                    <TableCell>
                                        <div class="text-sm">{{ formatDate(entry.entry_date) }}</div>
                                    </TableCell>
                                    <TableCell>
                                        <div class="text-sm">
                                            <div>{{ entry.reference_type || 'N/A' }}</div>
                                            <div class="text-gray-500">#{{ entry.reference_id || 'N/A' }}</div>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <div class="text-sm text-white max-w-xs truncate">
                                            {{ entry.description || 'No description' }}
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <div class="text-sm font-medium text-red-600">
                                            {{ formatCurrency(entry.total_debit || 0) }}
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <div class="text-sm font-medium text-green-600">
                                            {{ formatCurrency(entry.total_credit || 0) }}
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <Badge :variant="getStatusVariant(entry.status)">
                                            {{ getStatusLabel(entry.status) }}
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
                                                    <Link :href="route('finance.journal-entries.show', entry.id)">
                                                    <Eye class="w-4 h-4 mr-2" />
                                                    View
                                                    </Link>
                                                </DropdownMenuItem>
                                                <DropdownMenuItem as-child v-if="entry.status === 'draft'">
                                                    <Link :href="route('finance.journal-entries.edit', entry.id)">
                                                    <Edit class="w-4 h-4 mr-2" />
                                                    Edit
                                                    </Link>
                                                </DropdownMenuItem>
                                                <DropdownMenuSeparator />
                                                <DropdownMenuItem @click="deleteEntry(entry.id)"
                                                    v-if="entry.status === 'draft'" class="text-red-600">
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
                            }} results
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
import type { JournalEntry, PaginatedData } from '@/types/erp'
import type { BreadcrumbItemType } from '@/types'

interface Props {
    journalEntries?: JournalEntry[] | any
    pagination?: PaginatedData<JournalEntry>
}

const props = withDefaults(defineProps<Props>(), {
    journalEntries: () => [],
    pagination: undefined
})

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Finance', href: '/finance' },
    { title: 'Journal Entries', href: '/finance/journal-entries' }
]

const journalEntries = ref<JournalEntry[]>([])
const pagination = ref<PaginatedData<JournalEntry> | undefined>(props.pagination)
const loading = ref(false)
const searchQuery = ref('')
const statusFilter = ref('all')
const dateFilter = ref('')

let searchTimeout: number | null = null

const debouncedSearch = () => {
    if (searchTimeout) {
        clearTimeout(searchTimeout)
    }
    searchTimeout = setTimeout(() => {
        fetchJournalEntries()
    }, 300)
}

const fetchJournalEntries = async () => {
    loading.value = true
    try {
        const params: any = {}
        if (searchQuery.value) params.search = searchQuery.value
        if (statusFilter.value && statusFilter.value !== 'all') params.status = statusFilter.value
        if (dateFilter.value) params.date = dateFilter.value

        const response = await apiService.getJournalEntries(params)
        console.log('Journal Entries API Response:', response)

        if (response && response.data && Array.isArray(response.data)) {
            journalEntries.value = response.data.filter(entry => entry && typeof entry === 'object')
        } else {
            journalEntries.value = []
        }

        if (response && response.meta) {
            pagination.value = {
                data: response.data || [],
                links: response.links || [],
                meta: response.meta
            }
        }
    } catch (error) {
        console.error('Error fetching journal entries:', error)
        journalEntries.value = []
        pagination.value = undefined
    } finally {
        loading.value = false
    }
}

const changePage = (page: number) => {
    const params = new URLSearchParams()
    if (searchQuery.value) params.append('search', searchQuery.value)
    if (statusFilter.value && statusFilter.value !== 'all') params.append('status', statusFilter.value)
    if (dateFilter.value) params.append('date', dateFilter.value)
    params.append('page', page.toString())

    router.get(`/finance/journal-entries?${params.toString()}`)
}

const deleteEntry = async (id: number) => {
    if (!id) return

    if (confirm('Are you sure you want to delete this journal entry?')) {
        try {
            await apiService.deleteJournalEntry(id)
            await fetchJournalEntries()
        } catch (error) {
            console.error('Error deleting journal entry:', error)
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

const formatCurrency = (amount: number): string => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount)
}

const getStatusLabel = (status: string): string => {
    const labels: Record<string, string> = {
        'draft': 'Draft',
        'posted': 'Posted',
        'cancelled': 'Cancelled'
    }
    return labels[status] || status || 'N/A'
}

const getStatusVariant = (status: string): 'default' | 'secondary' | 'destructive' => {
    const variants: Record<string, 'default' | 'secondary' | 'destructive'> = {
        'draft': 'secondary',
        'posted': 'default',
        'cancelled': 'destructive'
    }
    return variants[status] || 'secondary'
}

watch([statusFilter, dateFilter], () => {
    fetchJournalEntries()
})

onMounted(() => {
    if (journalEntries.value.length === 0) {
        fetchJournalEntries()
    }
})
</script>
