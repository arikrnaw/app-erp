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
                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center space-x-4">
                            <div class="p-2 bg-blue-100 dark:bg-blue-900/20 rounded-lg">
                                <FileText class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-muted-foreground">Total Entries</p>
                                <p class="text-2xl font-bold">{{ journalEntries.length }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center space-x-4">
                            <div class="p-2 bg-green-100 dark:bg-green-900/20 rounded-lg">
                                <CheckCircle class="h-6 w-6 text-green-600 dark:text-green-400" />
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-muted-foreground">Posted Entries</p>
                                <p class="text-2xl font-bold">{{ postedEntriesCount }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center space-x-4">
                            <div class="p-2 bg-yellow-100 dark:bg-yellow-900/20 rounded-lg">
                                <Clock class="h-6 w-6 text-yellow-600 dark:text-yellow-400" />
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-muted-foreground">Draft Entries</p>
                                <p class="text-2xl font-bold">{{ draftEntriesCount }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center space-x-4">
                            <div class="p-2 bg-purple-100 dark:bg-purple-900/20 rounded-lg">
                                <Calculator class="h-6 w-6 text-purple-600 dark:text-purple-400" />
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-muted-foreground">Total Amount</p>
                                <p class="text-2xl font-bold">{{ formatCurrency(totalAmount) }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Search and Filters -->
            <Card>
                <CardContent class="p-6">
                    <div class="flex flex-col lg:flex-row gap-4">
                        <div class="flex-1">
                            <div class="relative">
                                <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                                <Input 
                                    v-model="searchQuery" 
                                    placeholder="Search journal entries by number, description, or reference..." 
                                    class="pl-10"
                                    @input="debouncedSearch" 
                                />
                            </div>
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
                            <Button variant="outline" @click="clearFilters">
                                <X class="h-4 w-4 mr-2" />
                                Clear
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Journal Entries Table -->
            <Card>
                <CardHeader>
                    <CardTitle>Journal Entries</CardTitle>
                    <CardDescription>
                        View and manage your accounting journal entries
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Entry Number</TableHead>
                                    <TableHead>Date</TableHead>
                                    <TableHead>Reference</TableHead>
                                    <TableHead>Description</TableHead>
                                    <TableHead class="text-right">Total Debit</TableHead>
                                    <TableHead class="text-right">Total Credit</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead class="text-right">Actions</TableHead>
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
                                <TableRow v-else-if="filteredEntries.length === 0">
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
                                <TableRow v-else v-for="entry in filteredEntries" :key="entry.id">
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
                                                <DropdownMenuItem @click="deleteEntry(entry.id)" class="text-destructive">
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
                    <div v-if="pagination && pagination.meta && pagination.meta.last_page > 1" 
                         class="flex items-center justify-between mt-6">
                        <div class="text-sm text-muted-foreground">
                            Showing {{ pagination.meta.from }} to {{ pagination.meta.to }} of {{ pagination.meta.total }} results
                        </div>
                        <div class="flex gap-2">
                            <Button 
                                variant="outline" 
                                size="sm" 
                                :disabled="pagination.meta.current_page === 1"
                                @click="changePage(pagination.meta.current_page - 1)"
                            >
                                <ChevronLeft class="h-4 w-4 mr-1" />
                                Previous
                            </Button>
                            <Button 
                                variant="outline" 
                                size="sm"
                                :disabled="pagination.meta.current_page === pagination.meta.last_page"
                                @click="changePage(pagination.meta.current_page + 1)"
                            >
                                Next
                                <ChevronRight class="h-4 w-4 ml-1" />
                            </Button>
                        </div>
                    </div>
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
    Download,
    ChevronLeft,
    ChevronRight
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

// Computed properties
const postedEntriesCount = computed(() => {
    return props.journalEntries.filter((entry: JournalEntry) => entry.status === 'posted').length
})

const draftEntriesCount = computed(() => {
    return props.journalEntries.filter((entry: JournalEntry) => entry.status === 'draft').length
})

const totalAmount = computed(() => {
    return props.journalEntries.reduce((sum: number, entry: JournalEntry) => {
        return sum + (entry.total_debit || 0)
    }, 0)
})

const filteredEntries = computed(() => {
    let filtered = props.journalEntries

    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase()
        filtered = filtered.filter((entry: JournalEntry) =>
            entry.entry_number?.toLowerCase().includes(query) ||
            entry.description?.toLowerCase().includes(query) ||
            entry.reference_type?.toLowerCase().includes(query)
        )
    }

    if (statusFilter.value !== 'all') {
        filtered = filtered.filter((entry: JournalEntry) => entry.status === statusFilter.value)
    }

    if (dateFilter.value) {
        filtered = filtered.filter((entry: JournalEntry) => entry.entry_date === dateFilter.value)
    }

    return filtered
})

// Methods
const debouncedSearch = () => {
    // Implement debounced search if needed
}

const clearFilters = () => {
    searchQuery.value = ''
    statusFilter.value = 'all'
    dateFilter.value = ''
}

const getStatusVariant = (status: string) => {
    const variants: Record<string, string> = {
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
            router.reload()
        } catch (error) {
            console.error('Error deleting journal entry:', error)
        }
    }
}

const changePage = (page: number) => {
    router.get(route('finance.journal-entries.index'), { page }, { preserveState: true })
}

onMounted(() => {
    // Initialize any necessary data
})
</script>
