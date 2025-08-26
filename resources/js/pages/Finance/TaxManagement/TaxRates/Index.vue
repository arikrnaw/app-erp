<template>

    <Head title="Tax Rates" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="font-semibold text-xl leading-tight text-foreground">
                    Tax Rates
                </h2>
                <Button @click="openCreateDialog">
                    <Plus class="w-4 h-4 mr-2" />
                    Add Tax Rate
                </Button>
            </div>

            <div class="overflow-hidden shadow-sm sm:rounded-lg border border-border bg-card">
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
                    <div class="rounded-md border border-border">
                        <Table>
                            <TableHeader>
                                <TableRow class="bg-muted/50">
                                    <TableHead class="text-muted-foreground font-medium">Name</TableHead>
                                    <TableHead class="text-muted-foreground font-medium">Rate (%)</TableHead>
                                    <TableHead class="text-muted-foreground font-medium">Description</TableHead>
                                    <TableHead class="text-muted-foreground font-medium">Status</TableHead>
                                    <TableHead class="text-muted-foreground font-medium">Created</TableHead>
                                    <TableHead class="text-right text-muted-foreground font-medium">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-if="loading">
                                    <TableCell colspan="6" class="text-center py-8">
                                        <div class="flex items-center justify-center text-muted-foreground">
                                            <Loader2 class="w-6 h-6 animate-spin mr-2" />
                                            Loading...
                                        </div>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-else-if="taxRates.length === 0">
                                    <TableCell colspan="6" class="text-center py-8 text-muted-foreground">
                                        No tax rates found
                                    </TableCell>
                                </TableRow>
                                <TableRow v-else v-for="taxRate in taxRates.filter((tr: TaxRate) => tr && tr.id)"
                                    :key="taxRate.id" class="hover:bg-muted/50">
                                    <TableCell>
                                        <div class="font-medium text-foreground">{{ taxRate.name }}</div>
                                    </TableCell>
                                    <TableCell>
                                        <div class="text-sm font-mono text-foreground">{{ taxRate.rate }}%</div>
                                    </TableCell>
                                    <TableCell>
                                        <div class="text-sm text-muted-foreground">{{ taxRate.description || 'N/A' }}
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <Badge :variant="taxRate.is_active ? 'default' : 'secondary'">
                                            {{ taxRate.is_active ? 'Active' : 'Inactive' }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        <div class="text-sm text-muted-foreground">{{ formatDate(taxRate.created_at) }}
                                        </div>
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <DropdownMenu>
                                            <DropdownMenuTrigger as-child>
                                                <Button variant="ghost" class="h-8 w-8 p-0 hover:bg-muted">
                                                    <MoreHorizontal class="h-4 h-4" />
                                                </Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end" class="bg-popover border-border">
                                                <DropdownMenuItem @click="openShowDialog(taxRate)"
                                                    class="hover:bg-accent">
                                                    <Eye class="w-4 h-4 mr-2" />
                                                    View
                                                </DropdownMenuItem>
                                                <DropdownMenuItem @click="openEditDialog(taxRate)"
                                                    class="hover:bg-accent">
                                                    <Edit class="w-4 h-4 mr-2" />
                                                    Edit
                                                </DropdownMenuItem>
                                                <DropdownMenuSeparator class="bg-border" />
                                                <DropdownMenuItem @click="deleteTaxRate(taxRate.id)"
                                                    class="text-destructive hover:bg-destructive/10">
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
                        <div class="text-sm text-muted-foreground">
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

        <!-- Create Tax Rate Dialog -->
        <Dialog :open="showCreateDialog" @update:open="showCreateDialog = $event">
            <DialogContent class="sm:max-w-[600px] bg-card border-border">
                <DialogHeader>
                    <DialogTitle class="text-foreground">Create New Tax Rate</DialogTitle>
                    <DialogDescription class="text-muted-foreground">
                        Create a new tax rate for your business operations
                    </DialogDescription>
                </DialogHeader>

                <form @submit.prevent="submitForm" class="space-y-4">
                    <!-- Tax Rate Name -->
                    <div class="space-y-2">
                        <Label for="name" class="text-foreground">Tax Rate Name *</Label>
                        <Input id="name" v-model="form.name" placeholder="e.g., GST, VAT, Sales Tax"
                            :class="{ 'border-destructive ring-destructive': errors.name }" required />
                        <p v-if="errors.name" class="text-sm text-destructive">{{ errors.name }}</p>
                    </div>

                    <!-- Tax Rate Percentage -->
                    <div class="space-y-2">
                        <Label for="rate" class="text-foreground">Tax Rate (%) *</Label>
                        <Input id="rate" v-model="form.rate" type="number" step="0.01" min="0" max="100"
                            placeholder="10.00" :class="{ 'border-destructive ring-destructive': errors.rate }"
                            required />
                        <p v-if="errors.rate" class="text-sm text-destructive">{{ errors.rate }}</p>
                    </div>

                    <!-- Description -->
                    <div class="space-y-2">
                        <Label for="description" class="text-foreground">Description</Label>
                        <Textarea id="description" v-model="form.description"
                            placeholder="Optional description for this tax rate" rows="3" />
                        <p v-if="errors.description" class="text-sm text-destructive">{{ errors.description }}</p>
                    </div>

                    <!-- Is Active -->
                    <div class="flex items-center space-x-2">
                        <Checkbox id="is_active" :checked="form.is_active"
                            @update:checked="(checked: boolean) => form.is_active = checked" />
                        <Label for="is_active" class="text-foreground">Active</Label>
                    </div>

                    <!-- Error Message -->
                    <div v-if="errors.general"
                        class="text-sm text-destructive bg-destructive/10 p-3 rounded-md border border-destructive/20">
                        {{ errors.general }}
                    </div>
                </form>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="closeCreateDialog">
                        Cancel
                    </Button>
                    <Button type="submit" @click="submitForm" :disabled="loading">
                        <Loader2 v-if="loading" class="w-4 h-4 mr-2 animate-spin" />
                        <Save v-else class="w-4 h-4 mr-2" />
                        Create Tax Rate
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Edit Tax Rate Dialog -->
        <Dialog :open="showEditDialog" @update:open="showEditDialog = $event">
            <DialogContent class="sm:max-w-[600px] bg-card border-border">
                <DialogHeader>
                    <DialogTitle class="text-foreground">Edit Tax Rate: {{ editForm.name }}</DialogTitle>
                    <DialogDescription class="text-muted-foreground">
                        Update the tax rate information
                    </DialogDescription>
                </DialogHeader>

                <form @submit.prevent="submitEditForm" class="space-y-4">
                    <!-- Tax Rate Name -->
                    <div class="space-y-2">
                        <Label for="edit_name" class="text-foreground">Tax Rate Name *</Label>
                        <Input id="edit_name" v-model="editForm.name" placeholder="e.g., GST, VAT, Sales Tax"
                            :class="{ 'border-destructive ring-destructive': editErrors.name }" required />
                        <p v-if="editErrors.name" class="text-sm text-destructive">{{ editErrors.name }}</p>
                    </div>

                    <!-- Tax Rate Percentage -->
                    <div class="space-y-2">
                        <Label for="edit_rate" class="text-foreground">Tax Rate (%) *</Label>
                        <Input id="edit_rate" v-model="editForm.rate" type="number" step="0.01" min="0" max="100"
                            placeholder="10.00" :class="{ 'border-destructive ring-destructive': editErrors.rate }"
                            required />
                        <p v-if="editErrors.rate" class="text-sm text-destructive">{{ editErrors.rate }}</p>
                    </div>

                    <!-- Description -->
                    <div class="space-y-2">
                        <Label for="edit_description" class="text-foreground">Description</Label>
                        <Textarea id="edit_description" v-model="editForm.description"
                            placeholder="Optional description for this tax rate" rows="3" />
                        <p v-if="editErrors.description" class="text-sm text-destructive">{{ editErrors.description }}
                        </p>
                    </div>

                    <!-- Is Active -->
                    <div class="flex items-center space-x-2">
                        <Checkbox id="edit_is_active" v-model="editIsActive" />
                        <Label for="edit_is_active" class="text-foreground">Active</Label>
                    </div>

                    <!-- Error Message -->
                    <div v-if="editErrors.general"
                        class="text-sm text-destructive bg-destructive/10 p-3 rounded-md border border-destructive/20">
                        {{ editErrors.general }}
                    </div>
                </form>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="closeEditDialog">
                        Cancel
                    </Button>
                    <Button type="submit" @click="submitEditForm" :disabled="editLoading">
                        <Loader2 v-if="editLoading" class="w-4 h-4 mr-2 animate-spin" />
                        <Save v-else class="w-4 h-4 mr-2" />
                        Update Tax Rate
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Show Tax Rate Dialog -->
        <Dialog :open="showViewDialog" @update:open="showViewDialog = $event">
            <DialogContent class="sm:max-w-[700px] bg-card border-border">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2 text-foreground">
                        <FileSpreadsheet class="w-5 h-5" />
                        {{ viewTaxRate?.name }}
                    </DialogTitle>
                    <DialogDescription class="text-muted-foreground">
                        Tax rate details and information
                    </DialogDescription>
                </DialogHeader>

                <div v-if="viewTaxRate" class="space-y-6">
                    <!-- Basic Information -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground">Tax Rate</Label>
                            <p class="text-2xl font-bold text-primary">{{ viewTaxRate.rate }}%</p>
                        </div>
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground">Status</Label>
                            <Badge :variant="viewTaxRate.is_active ? 'default' : 'secondary'">
                                {{ viewTaxRate.is_active ? 'Active' : 'Inactive' }}
                            </Badge>
                        </div>
                    </div>

                    <div v-if="viewTaxRate.description">
                        <Label class="text-sm font-medium text-muted-foreground">Description</Label>
                        <p class="text-foreground">{{ viewTaxRate.description }}</p>
                    </div>

                    <!-- System Information -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground">Created</Label>
                            <p class="text-foreground">{{ formatDate(viewTaxRate.created_at) }}</p>
                        </div>
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground">Last Updated</Label>
                            <p class="text-foreground">{{ formatDate(viewTaxRate.updated_at) }}</p>
                        </div>
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground">ID</Label>
                            <p class="font-mono text-foreground">{{ viewTaxRate.id }}</p>
                        </div>
                    </div>
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="closeViewDialog">
                        Close
                    </Button>
                    <Button @click="openEditDialog(viewTaxRate)" v-if="viewTaxRate">
                        <Edit class="w-4 h-4 mr-2" />
                        Edit
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, watch, computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Badge } from '@/components/ui/badge'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Checkbox } from '@/components/ui/checkbox'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { Plus, MoreHorizontal, Eye, Edit, Trash2, Loader2, Save, FileSpreadsheet } from 'lucide-vue-next'
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

// Dialog states
const showCreateDialog = ref(false)
const showEditDialog = ref(false)
const showViewDialog = ref(false)
const errors = ref<Record<string, string>>({})
const editErrors = ref<Record<string, string>>({})
const editLoading = ref(false)

// Forms
const form = ref({
    name: '',
    rate: '',
    description: '',
    is_active: true
})

const editForm = ref({
    id: 0,
    name: '',
    rate: '',
    description: '',
    is_active: true
})

const viewTaxRate = ref<TaxRate | null>(null)

// Computed properties for checkbox binding
const editIsActive = computed({
    get: () => editForm.value.is_active,
    set: (value: boolean) => {
        editForm.value.is_active = value
    }
})

let searchTimeout: number | null = null

const openCreateDialog = () => {
    showCreateDialog.value = true
    resetForm()
}

const closeCreateDialog = () => {
    showCreateDialog.value = false
    resetForm()
}

const openEditDialog = (taxRate: TaxRate) => {

    viewTaxRate.value = taxRate

    const isActiveValue = Boolean(taxRate.is_active)

    editForm.value = {
        id: taxRate.id,
        name: taxRate.name,
        rate: taxRate.rate.toString(),
        description: taxRate.description || '',
        is_active: isActiveValue
    }

    showEditDialog.value = true
    showViewDialog.value = false
    editErrors.value = {}
}

const closeEditDialog = () => {
    showEditDialog.value = false
    resetEditForm()
}

const openShowDialog = (taxRate: TaxRate) => {
    viewTaxRate.value = taxRate
    showViewDialog.value = true
}

const closeViewDialog = () => {
    showViewDialog.value = false
    viewTaxRate.value = null
}

const resetForm = () => {
    form.value = {
        name: '',
        rate: '',
        description: '',
        is_active: true
    }
    errors.value = {}
}

const resetEditForm = () => {
    editForm.value = {
        id: 0,
        name: '',
        rate: '',
        description: '',
        is_active: true
    }
    editErrors.value = {}
}

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
            taxRates.value = response.data.filter((taxRate: any) => taxRate && typeof taxRate === 'object')
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

const submitForm = async () => {
    loading.value = true
    errors.value = {}

    try {
        await apiService.createTaxRate(form.value)

        // Close dialog and refresh data
        closeCreateDialog()
        await fetchTaxRates()

        // Show success message (you can implement a toast notification here)
        console.log('Tax rate created successfully')
    } catch (error: any) {
        if (error.response?.data?.errors) {
            errors.value = error.response.data.errors
        } else {
            errors.value = { general: 'An error occurred while creating the tax rate' }
        }
    } finally {
        loading.value = false
    }
}

const submitEditForm = async () => {
    editLoading.value = true
    editErrors.value = {}

    try {
        await apiService.updateTaxRate(editForm.value.id, editForm.value)

        // Close dialog and refresh data
        closeEditDialog()
        await fetchTaxRates()

        // Show success message
        console.log('Tax rate updated successfully')
    } catch (error: any) {
        if (error.response?.data?.errors) {
            editErrors.value = error.response.data.errors
        } else {
            editErrors.value = { general: 'An error occurred while updating the tax rate' }
        }
    } finally {
        editLoading.value = false
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
