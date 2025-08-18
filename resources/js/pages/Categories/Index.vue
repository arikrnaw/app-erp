<template>

    <Head title="Categories" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl leading-tight">
                    Categories
                </h2>
                <Link :href="route('categories.create')">
                <Button>
                    <Plus class="w-4 h-4 mr-2" />
                    Create Category
                </Button>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <!-- Search -->
                        <div class="flex flex-col sm:flex-row gap-4 mb-6">
                            <div class="flex-1">
                                <Input v-model="searchQuery" placeholder="Search categories..." class="w-full"
                                    @input="debouncedSearch" />
                            </div>
                        </div>

                        <!-- Categories Table -->
                        <div class="rounded-md border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Name</TableHead>
                                        <TableHead>Parent Category</TableHead>
                                        <TableHead>Products Count</TableHead>
                                        <TableHead>Created At</TableHead>
                                        <TableHead class="text-right">Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-if="loading">
                                        <TableCell colspan="5" class="text-center py-8">
                                            <div class="flex items-center justify-center">
                                                <Loader2 class="w-6 h-6 animate-spin mr-2" />
                                                Loading...
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                    <TableRow v-else-if="categories.length === 0">
                                        <TableCell colspan="5" class="text-center py-8 text-gray-500">
                                            No categories found
                                        </TableCell>
                                    </TableRow>
                                    <TableRow v-else v-for="category in categories" :key="category.id">
                                        <TableCell>
                                            <div>
                                                <div class="font-medium">{{ category.name }}</div>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <span v-if="category.parent" class="text-sm text-white">
                                                {{ category.parent.name }}
                                            </span>
                                            <span v-else class="text-sm text-gray-400">-</span>
                                        </TableCell>
                                        <TableCell>
                                            <span class="text-sm">{{ category.products_count || 0 }}</span>
                                        </TableCell>
                                        <TableCell>{{ formatDate(category.created_at) }}</TableCell>
                                        <TableCell class="text-right">
                                            <DropdownMenu>
                                                <DropdownMenuTrigger as-child>
                                                    <Button variant="ghost" class="h-8 w-8 p-0">
                                                        <MoreHorizontal class="h-4 w-4" />
                                                    </Button>
                                                </DropdownMenuTrigger>
                                                <DropdownMenuContent align="end">
                                                    <DropdownMenuItem as-child>
                                                        <Link :href="route('categories.show', category.id)">
                                                        <Eye class="w-4 h-4 mr-2" />
                                                        View
                                                        </Link>
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem as-child>
                                                        <Link :href="route('categories.edit', category.id)">
                                                        <Edit class="w-4 h-4 mr-2" />
                                                        Edit
                                                        </Link>
                                                    </DropdownMenuItem>
                                                    <DropdownMenuSeparator />
                                                    <DropdownMenuItem @click="deleteCategory(category.id)"
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
                        <div v-if="pagination && pagination.meta.last_page > 1" class="mt-6">
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
import type { Category, PaginatedData } from '@/types/erp'

interface Props {
    categories?: Category[]
    pagination?: PaginatedData<Category>
}

const props = withDefaults(defineProps<Props>(), {
    categories: () => [],
    pagination: undefined
})

const categories = ref<Category[]>(props.categories || [])
const pagination = ref<PaginatedData<Category> | undefined>(props.pagination)
const loading = ref(false)
const searchQuery = ref('')

let searchTimeout: ReturnType<typeof setTimeout> | null = null

const debouncedSearch = () => {
    if (searchTimeout) {
        clearTimeout(searchTimeout)
    }
    searchTimeout = setTimeout(() => {
        fetchCategories()
    }, 300)
}

const fetchCategories = async () => {
    loading.value = true
    try {
        const response = await apiService.getCategories()
        categories.value = response
    } catch (error) {
        console.error('Error fetching categories:', error)
    } finally {
        loading.value = false
    }
}

const changePage = (page: number) => {
    const params = new URLSearchParams()
    if (searchQuery.value) params.append('search', searchQuery.value)
    params.append('page', page.toString())

    router.get(`/categories?${params.toString()}`)
}

const deleteCategory = async (id: number) => {
    if (confirm('Are you sure you want to delete this category?')) {
        try {
            await apiService.deleteCategory(id)
            await fetchCategories()
        } catch (error) {
            console.error('Error deleting category:', error)
        }
    }
}

const formatDate = (dateString: string): string => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    })
}

onMounted(() => {
    if (categories.value.length === 0) {
        fetchCategories()
    }
})
</script>
