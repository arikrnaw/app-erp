<template>

    <Head title="Journal Entries" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 space-y-6">
            <!-- Header Section -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Journal Entries</h1>
                    <p class="text-muted-foreground mt-1">
                        Manage your company's journal entries and accounting transactions
                    </p>
                </div>
                <div class="flex items-center space-x-2">
                    <Button variant="outline" size="sm">
                        <Download class="h-4 w-4 mr-2" />
                        Export
                    </Button>
                    <Link :href="route('finance.journal-entries.create')">
                    <Button>
                        <Plus class="w-4 h-4 mr-2" />
                        Add Journal Entry
                    </Button>
                    </Link>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card class="shadow-sm border-border">
                    <CardContent class="p-6">
                        <div class="flex items-center space-x-4">
                            <div class="p-2 bg-primary/10 rounded-lg">
                                <FileText class="h-6 w-6 text-primary" />
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-muted-foreground">Total Entries</p>
                                <p class="text-2xl font-bold text-card-foreground">{{ journalEntriesData.length }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card class="shadow-sm border-border">
                    <CardContent class="p-6">
                        <div class="flex items-center space-x-4">
                            <div class="p-2 bg-green-100 dark:bg-green-900/20 rounded-lg">
                                <CheckCircle class="h-6 w-6 text-green-600 dark:text-green-400" />
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-muted-foreground">Posted Entries</p>
                                <p class="text-2xl font-bold text-card-foreground">{{ postedEntriesCount }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card class="shadow-sm border-border">
                    <CardContent class="p-6">
                        <div class="flex items-center space-x-4">
                            <div class="p-2 bg-yellow-100 dark:bg-yellow-900/20 rounded-lg">
                                <Clock class="h-6 w-6 text-yellow-600 dark:text-yellow-400" />
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-muted-foreground">Draft Entries</p>
                                <p class="text-2xl font-bold text-card-foreground">{{ draftEntriesCount }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card class="shadow-sm border-border">
                    <CardContent class="p-6">
                        <div class="flex items-center space-x-4">
                            <div class="p-2 bg-purple-100 dark:bg-purple-900/20 rounded-lg">
                                <Calculator class="h-6 w-6 text-purple-600 dark:text-purple-400" />
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-muted-foreground">Total Amount</p>
                                <p class="text-2xl font-bold text-card-foreground">{{ formatCurrency(totalAmount) }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Search and Filters -->
            <Card class="shadow-sm border-border">
                <CardContent class="p-6">
                    <div class="flex flex-col lg:flex-row gap-4">
                        <div class="flex-1">
                            <div class="relative">
                                <Search
                                    class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                                <Input v-model="searchQuery"
                                    placeholder="Search journal entries by number, description, or reference..."
                                    class="pl-10 h-10" @input="debouncedSearch" />
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <Select v-model="statusFilter">
                                <SelectTrigger class="w-[180px] h-10">
                                    <SelectValue placeholder="Status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All Status</SelectItem>
                                    <SelectItem value="draft">Draft</SelectItem>
                                    <SelectItem value="posted">Posted</SelectItem>
                                    <SelectItem value="cancelled">Cancelled</SelectItem>
                                </SelectContent>
                            </Select>
                            <Input type="date" v-model="dateFilter" class="w-[180px] h-10" />
                            <Button variant="outline" @click="clearFilters" class="h-10">
                                <X class="h-4 w-4 mr-2" />
                                Clear
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Journal Entries Table -->
            <Card class="shadow-sm border-border">
                <CardHeader class="border-b border-border bg-muted/30">
                    <CardTitle class="text-xl font-semibold">Journal Entries</CardTitle>
                    <CardDescription class="text-muted-foreground">
                        View and manage your accounting journal entries
                    </CardDescription>
                </CardHeader>
                <CardContent class="p-6">
                    <div class="rounded-md border border-border">
                        <Table>
                            <TableHeader>
                                <TableRow class="border-border">
                                    <TableHead class="text-sm font-medium text-muted-foreground">Entry Number
                                    </TableHead>
                                    <TableHead class="text-sm font-medium text-muted-foreground">Date</TableHead>
                                    <TableHead class="text-sm font-medium text-muted-foreground">Reference</TableHead>
                                    <TableHead class="text-sm font-medium text-muted-foreground">Description</TableHead>
                                    <TableHead class="text-right text-sm font-medium text-muted-foreground">Total Debit
                                    </TableHead>
                                    <TableHead class="text-right text-sm font-medium text-muted-foreground">Total Credit
                                    </TableHead>
                                    <TableHead class="text-sm font-medium text-muted-foreground">Status</TableHead>
                                    <TableHead class="text-right text-sm font-medium text-muted-foreground">Actions
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-if="loading">
                                    <TableCell colspan="8" class="text-center py-12">
                                        <div class="flex items-center justify-center space-x-2">
                                            <Loader2 class="w-6 h-6 animate-spin" />
                                            <span class="text-muted-foreground">Loading journal entries...</span>
                                        </div>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-else-if="journalEntriesData.length === 0">
                                    <TableCell colspan="8" class="text-center py-12">
                                        <div class="flex flex-col items-center space-y-2">
                                            <FileText class="h-12 w-12 text-muted-foreground" />
                                            <div class="text-center">
                                                <h3 class="text-lg font-medium">No journal entries found</h3>
                                                <p class="text-muted-foreground">
                                                    {{ searchQuery || statusFilter !== 'all' || dateFilter
                                                        ? 'Try adjusting your search or filters'
                                                        : 'Get started by creating your first journal entry' }}
                                                </p>
                                            </div>
                                        </div>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-else v-for="entry in journalEntriesData" :key="entry.id">
                                    <TableCell>
                                        <div class="font-mono text-sm font-medium">{{ entry.entry_number }}</div>
                                    </TableCell>
                                    <TableCell>
                                        <div class="text-sm">{{ formatDate(entry.entry_date) }}</div>
                                    </TableCell>
                                    <TableCell>
                                        <div class="text-sm space-y-1">
                                            <div class="font-medium">{{ entry.reference_type || 'N/A' }}</div>
                                            <div class="text-muted-foreground">#{{ entry.reference_id || 'N/A' }}</div>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <div class="text-sm text-muted-foreground max-w-xs truncate">
                                            {{ entry.description || 'No description' }}
                                        </div>
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <div class="font-medium text-red-600 dark:text-red-400">
                                            {{ formatCurrency(entry.total_debit || 0) }}
                                        </div>
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <div class="font-medium text-green-600 dark:text-green-400">
                                            {{ formatCurrency(entry.total_credit || 0) }}
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <Badge :variant="getStatusVariant(entry.status)" class="capitalize">
                                            {{ entry.status }}
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
                                                    View Details
                                                    </Link>
                                                </DropdownMenuItem>
                                                <DropdownMenuItem as-child v-if="entry.status === 'draft'">
                                                    <Link :href="route('finance.journal-entries.edit', entry.id)">
                                                    <Edit class="w-4 h-4 mr-2" />
                                                    Edit Entry
                                                    </Link>
                                                </DropdownMenuItem>
                                                <DropdownMenuSeparator />
                                                <DropdownMenuItem @click="deleteEntry(entry.id)"
                                                    class="text-destructive">
                                                    <Trash2 class="w-4 h-4 mr-2" />
                                                    Delete Entry
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>

                    <!-- Pagination -->
                    <DataPagination v-if="paginationData && paginationData.total > 0"
                        :current-page="paginationData.current_page" :total-pages="paginationData.last_page"
                        :total-items="paginationData.total" :per-page="paginationData.per_page"
                        @page-change="changePage" />
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Badge } from '@/components/ui/badge'
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { DataPagination } from '@/components/ui/pagination'
import {
    Plus,
    MoreHorizontal,
    Eye,
    Edit,
    Trash2,
    Loader2,
    Search,
    X,
    FileText,
    CheckCircle,
    Clock,
    Calculator,
    Download
} from 'lucide-vue-next'
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

const loading = ref(false)
const searchQuery = ref('')
const statusFilter = ref('all')
const dateFilter = ref('')

// Local state for API data
const journalEntriesData = ref<JournalEntry[]>([])
const paginationData = ref<any>(null)

// Computed properties
const postedEntriesCount = computed(() => {
    return journalEntriesData.value.filter((entry: JournalEntry) => entry.status === 'posted').length
})

const draftEntriesCount = computed(() => {
    return journalEntriesData.value.filter((entry: JournalEntry) => entry.status === 'draft').length
})

const totalAmount = computed(() => {
    return journalEntriesData.value.reduce((sum: number, entry: JournalEntry) => {
        return sum + (parseFloat(entry.total_debit?.toString() || '0'))
    }, 0)
})

// Methods
const debouncedSearch = () => {
    // Implement debounced search if needed
    fetchJournalEntries()
}

const clearFilters = () => {
    searchQuery.value = ''
    statusFilter.value = 'all'
    dateFilter.value = ''
    fetchJournalEntries()
}

const getStatusVariant = (status: string): "default" | "destructive" | "outline" | "secondary" | null | undefined => {
    const variants: Record<string, "default" | "destructive" | "outline" | "secondary" | null | undefined> = {
        'draft': 'secondary',
        'posted': 'default',
        'cancelled': 'destructive'
    }
    return variants[status] || 'secondary'
}

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(amount)
}

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    })
}

const deleteEntry = async (id: number) => {
    if (confirm('Are you sure you want to delete this journal entry?')) {
        try {
            await apiService.deleteJournalEntry(id)
            await fetchJournalEntries() // Refresh data after deletion
        } catch (error) {
            console.error('Error deleting journal entry:', error)
        }
    }
}

const changePage = async (page: number) => {
    await fetchJournalEntries(page)
}

const fetchJournalEntries = async (page: number = 1) => {
    try {
        loading.value = true

        const response: any = await apiService.getJournalEntries({
            search: searchQuery.value,
            status: statusFilter.value !== 'all' ? statusFilter.value : undefined,
            date: dateFilter.value || undefined,
            page: page
        })

        // Update local state with API response data
        journalEntriesData.value = response.data || []
        paginationData.value = {
            current_page: response.current_page,
            last_page: response.last_page,
            total: response.total,
            per_page: response.per_page,
            from: response.from,
            to: response.to
        }

    } catch (error) {
        journalEntriesData.value = []
        paginationData.value = null
    } finally {
        loading.value = false
    }
}

onMounted(async () => {
    await fetchJournalEntries()
})
</script>
