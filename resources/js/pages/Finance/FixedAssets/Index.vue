<template>

    <Head title="Fixed Assets" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 space-y-6">
            <!-- Header Section -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Fixed Assets</h1>
                    <p class="text-muted-foreground mt-1">
                        Manage your company's fixed assets, depreciation, and asset lifecycle
                    </p>
                </div>
                <div class="flex items-center space-x-2">
                    <Button variant="outline" size="sm" @click="exportAssets" :disabled="loading">
                        <Download class="h-4 w-4 mr-2" />
                        Export Assets
                    </Button>
                    <Link :href="route('finance.fixed-assets.create')">
                    <Button>
                        <Plus class="w-4 h-4 mr-2" />
                        Add Asset
                    </Button>
                    </Link>
                </div>
            </div>

            <!-- Asset Overview Cards -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center space-x-4">
                            <div class="p-2 bg-blue-100 dark:bg-blue-900/20 rounded-lg">
                                <Building2 class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-muted-foreground">Total Assets</p>
                                <p class="text-2xl font-bold">{{ assets.length }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center space-x-4">
                            <div class="p-2 bg-green-100 dark:bg-green-900/20 rounded-lg">
                                <Calculator class="h-6 w-6 text-green-600 dark:text-green-400" />
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-muted-foreground">Total Value</p>
                                <p class="text-2xl font-bold text-green-600 dark:text-green-400">
                                    {{ formatCurrency(totalAssetValue) }}
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center space-x-4">
                            <div class="p-2 bg-purple-100 dark:bg-purple-900/20 rounded-lg">
                                <TrendingDown class="h-6 w-6 text-purple-600 dark:text-purple-400" />
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-muted-foreground">Accumulated Depreciation</p>
                                <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">
                                    {{ formatCurrency(totalDepreciation) }}
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center space-x-4">
                            <div class="p-2 bg-orange-100 dark:bg-orange-900/20 rounded-lg">
                                <Wallet class="h-6 w-6 text-orange-600 dark:text-orange-400" />
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-muted-foreground">Net Book Value</p>
                                <p class="text-2xl font-bold text-orange-600 dark:text-orange-400">
                                    {{ formatCurrency(netBookValue) }}
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Asset Categories Overview -->
            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Asset Categories -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center space-x-2">
                            <Layers class="h-5 w-5" />
                            <span>Asset Categories</span>
                        </CardTitle>
                        <CardDescription>
                            Distribution of assets by category
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div v-for="category in assetCategories" :key="category.id"
                                class="flex items-center justify-between p-4 border rounded-lg hover:bg-muted/50">
                                <div class="flex items-center space-x-3">
                                    <div class="p-2 bg-blue-100 dark:bg-blue-900/20 rounded-lg">
                                        <component :is="getCategoryIcon(category.name)"
                                            class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                                    </div>
                                    <div>
                                        <p class="font-medium">{{ category.name }}</p>
                                        <p class="text-sm text-muted-foreground">{{ category.count }} assets</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold">{{ formatCurrency(category.total_value) }}</p>
                                    <p class="text-sm text-muted-foreground">{{ category.percentage }}%</p>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Depreciation Schedule -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center space-x-2">
                            <Calendar class="h-5 w-5" />
                            <span>Monthly Depreciation</span>
                        </CardTitle>
                        <CardDescription>
                            Depreciation expenses for current month
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div v-for="depreciation in monthlyDepreciation" :key="depreciation.id"
                                class="flex items-center justify-between p-4 border rounded-lg hover:bg-muted/50">
                                <div class="flex items-center space-x-3">
                                    <div class="p-2 bg-green-100 dark:bg-green-900/20 rounded-lg">
                                        <Calculator class="h-4 w-4 text-green-600 dark:text-green-400" />
                                    </div>
                                    <div>
                                        <p class="font-medium">{{ depreciation.asset_name }}</p>
                                        <p class="text-sm text-muted-foreground">{{ depreciation.category }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold text-green-600 dark:text-green-400">
                                        {{ formatCurrency(depreciation.amount) }}
                                    </p>
                                    <p class="text-sm text-muted-foreground">{{ depreciation.method }}</p>
                                </div>
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
                                <Search
                                    class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                                <Input v-model="searchQuery" placeholder="Search assets by name, tag, or location..."
                                    class="pl-10" @input="debouncedSearch" />
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <Select v-model="categoryFilter" @update:model-value="filterAssets">
                                <SelectTrigger class="w-[180px]">
                                    <SelectValue placeholder="All Categories" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All Categories</SelectItem>
                                    <SelectItem v-for="category in assetCategories" :key="category.id"
                                        :value="category.id">
                                        {{ category.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <Select v-model="statusFilter" @update:model-value="filterAssets">
                                <SelectTrigger class="w-[180px]">
                                    <SelectValue placeholder="All Status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All Status</SelectItem>
                                    <SelectItem value="active">Active</SelectItem>
                                    <SelectItem value="disposed">Disposed</SelectItem>
                                    <SelectItem value="maintenance">Under Maintenance</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Assets Table -->
            <Card>
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Asset</TableHead>
                                    <TableHead>Tag Number</TableHead>
                                    <TableHead>Category</TableHead>
                                    <TableHead>Location</TableHead>
                                    <TableHead class="text-right">Purchase Value</TableHead>
                                    <TableHead class="text-right">Current Value</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead class="text-right">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="asset in filteredAssets" :key="asset.id" class="hover:bg-muted/50">
                                    <TableCell>
                                        <div class="flex items-center space-x-3">
                                            <div class="p-2 bg-blue-100 dark:bg-blue-900/20 rounded-lg">
                                                <component :is="getCategoryIcon(asset.category)"
                                                    class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                                            </div>
                                            <div>
                                                <p class="font-medium">{{ asset.name }}</p>
                                                <p class="text-sm text-muted-foreground">{{ asset.description }}</p>
                                            </div>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <code class="px-2 py-1 bg-muted rounded text-sm">{{ asset.tag_number }}</code>
                                    </TableCell>
                                    <TableCell>
                                        <Badge variant="outline">{{ asset.category }}</Badge>
                                    </TableCell>
                                    <TableCell>
                                        <div class="flex items-center space-x-2">
                                            <MapPin class="h-4 w-4 text-muted-foreground" />
                                            <span>{{ asset.location }}</span>
                                        </div>
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <p class="font-semibold">{{ formatCurrency(asset.purchase_value) }}</p>
                                        <p class="text-xs text-muted-foreground">{{ formatDate(asset.purchase_date) }}
                                        </p>
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <div class="text-right">
                                            <p class="font-semibold"
                                                :class="asset.current_value >= 0 ? 'text-green-600' : 'text-red-600'">
                                                {{ formatCurrency(asset.current_value) }}
                                            </p>
                                            <p class="text-xs text-muted-foreground">{{ asset.depreciation_method }}</p>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <Badge :variant="getStatusVariant(asset.status)">
                                            {{ asset.status }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <div class="flex items-center justify-end space-x-2">
                                            <Link :href="route('finance.fixed-assets.show', asset.id)">
                                            <Button variant="ghost" size="sm">
                                                <Eye class="h-4 w-4" />
                                            </Button>
                                            </Link>
                                            <Link :href="route('finance.fixed-assets.edit', asset.id)">
                                            <Button variant="ghost" size="sm">
                                                <Edit class="h-4 w-4" />
                                            </Button>
                                            </Link>
                                            <Button variant="ghost" size="sm" @click="showDepreciationModal(asset)">
                                                <Calculator class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-if="filteredAssets.length === 0">
                                    <TableCell colspan="8" class="text-center py-8">
                                        <div class="flex flex-col items-center space-y-2">
                                            <Building2 class="h-8 w-8 text-muted-foreground" />
                                            <p class="text-muted-foreground">No assets found</p>
                                            <Link :href="route('finance.fixed-assets.create')">
                                            <Button variant="outline" size="sm">
                                                <Plus class="h-4 w-4 mr-2" />
                                                Add First Asset
                                            </Button>
                                            </Link>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </CardContent>
            </Card>

            <!-- Pagination -->
            <DataPagination v-if="totalPages > 1" :current-page="currentPage" :total-pages="totalPages"
                :total-items="totalItems" :per-page="20" @page-change="handlePageChange" />
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItemType } from '@/types';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
    CardDescription
} from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow
} from '@/components/ui/table';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue
} from '@/components/ui/select';
import { DataPagination } from '@/components/ui/pagination';
import {
    Building2,
    Calculator,
    TrendingDown,
    Wallet,
    Download,
    Plus,
    Eye,
    Edit,
    Search,
    Layers,
    Calendar,
    MapPin
} from 'lucide-vue-next';
import { useApi } from '@/composables/useApi';
import { useDebounce } from '@/composables/useDebounce';

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Finance', href: '/finance' },
    { title: 'Fixed Assets', href: '/finance/fixed-assets' }
];

const debouncedSearch = useDebounce(() => {
    currentPage.value = 1;
    filterAssets();
}, 300);
const loading = ref(false);

// Data
const assets = ref<any[]>([]);
const assetCategories = ref<any[]>([]);
const monthlyDepreciation = ref<any[]>([]);
const searchQuery = ref('');
const categoryFilter = ref('all');
const statusFilter = ref('all');
const currentPage = ref(1);
const totalPages = ref(1);
const totalItems = ref(0);

// Computed
const totalAssetValue = computed(() =>
    assets.value.reduce((sum, asset) => sum + asset.purchase_value, 0)
);

const totalDepreciation = computed(() =>
    assets.value.reduce((sum, asset) => sum + (asset.purchase_value - asset.current_value), 0)
);

const netBookValue = computed(() => totalAssetValue.value - totalDepreciation.value);

const filteredAssets = computed(() => {
    let filtered = assets.value;

    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(asset =>
            asset.name.toLowerCase().includes(query) ||
            asset.tag_number.toLowerCase().includes(query) ||
            asset.location.toLowerCase().includes(query)
        );
    }

    if (categoryFilter.value && categoryFilter.value !== 'all') {
        filtered = filtered.filter(asset => asset.category_id === categoryFilter.value);
    }

    if (statusFilter.value && statusFilter.value !== 'all') {
        filtered = filtered.filter(asset => asset.status === statusFilter.value);
    }

    return filtered;
});

// Methods
const fetchData = async (page = 1) => {
    loading.value = true;
    try {
        const [assetsResponse, categoriesResponse, depreciationResponse] = await Promise.all([
            useApi().get('/api/finance/fixed-assets', { params: { page, per_page: 20 } }),
            useApi().get('/api/finance/fixed-assets/categories'),
            useApi().get('/api/finance/fixed-assets/monthly-depreciation')
        ]);

        assets.value = assetsResponse.data.data;
        currentPage.value = assetsResponse.data.current_page;
        totalPages.value = assetsResponse.data.last_page;
        totalItems.value = assetsResponse.data.total;

        assetCategories.value = categoriesResponse.data;
        monthlyDepreciation.value = depreciationResponse.data;
    } catch (error) {
        console.error('Error fetching fixed assets data:', error);
    } finally {
        loading.value = false;
    }
};



const filterAssets = () => {
    currentPage.value = 1;
    fetchData();
};

const handlePageChange = (page: number) => {
    currentPage.value = page;
    fetchData(page);
};

const getCategoryIcon = (categoryName: string) => {
    const icons: Record<string, any> = {
        'Buildings': Building2,
        'Machinery': Calculator,
        'Vehicles': Building2,
        'Equipment': Calculator,
        'Furniture': Building2,
        'Computers': Calculator
    };
    return icons[categoryName] || Building2;
};

const getStatusVariant = (status: string): "default" | "secondary" | "destructive" | "outline" => {
    const variants: Record<string, "default" | "secondary" | "destructive" | "outline"> = {
        'active': 'default',
        'disposed': 'destructive',
        'maintenance': 'secondary'
    };
    return variants[status] || 'secondary';
};

const showDepreciationModal = (asset: any) => {
    // Implementation for depreciation modal
    console.log('Show depreciation for asset:', asset);
};

const exportAssets = async () => {
    try {
        const response = await useApi().get('/api/finance/fixed-assets/export', {
            responseType: 'blob'
        });

        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', 'fixed-assets.xlsx');
        document.body.appendChild(link);
        link.click();
        link.remove();
    } catch (error) {
        console.error('Error exporting fixed assets:', error);
    }
};

const formatCurrency = (amount: number): string => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(amount);
};

const formatDate = (date: string): string => {
    return new Date(date).toLocaleDateString('id-ID');
};

onMounted(() => {
    fetchData();
});
</script>
