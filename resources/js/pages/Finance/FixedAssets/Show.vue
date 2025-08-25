<template>

    <Head :title="`Asset: ${asset.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 space-y-6">
            <!-- Header Section -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">{{ asset.name }}</h1>
                    <p class="text-muted-foreground mt-1">
                        Asset Tag: {{ asset.tag_number }} | Category: {{ asset.category }}
                    </p>
                </div>
                <div class="flex items-center space-x-2">
                    <Link :href="route('finance.fixed-assets.index')">
                    <Button variant="outline">
                        <ArrowLeft class="w-4 h-4 mr-2" />
                        Back to Assets
                    </Button>
                    </Link>
                    <Link :href="route('finance.fixed-assets.edit', asset.id)">
                    <Button>
                        <Edit class="w-4 h-4 mr-2" />
                        Edit Asset
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
                                <p class="text-sm font-medium text-muted-foreground">Purchase Value</p>
                                <p class="text-2xl font-bold">{{ formatCurrency(asset.purchase_value) }}</p>
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
                                <p class="text-sm font-medium text-muted-foreground">Current Value</p>
                                <p class="text-2xl font-bold text-green-600 dark:text-green-400">
                                    {{ formatCurrency(asset.current_value) }}
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
                                    {{ formatCurrency(asset.accumulated_depreciation || 0) }}
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
                                    {{ formatCurrency(asset.net_book_value || 0) }}
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Asset Details -->
            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Basic Information -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center space-x-2">
                            <Info class="h-5 w-5" />
                            <span>Basic Information</span>
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid gap-4">
                            <div class="flex justify-between">
                                <span class="text-sm font-medium text-muted-foreground">Asset Name:</span>
                                <span class="text-sm">{{ asset.name }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm font-medium text-muted-foreground">Tag Number:</span>
                                <code class="px-2 py-1 bg-muted rounded text-sm">{{ asset.tag_number }}</code>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm font-medium text-muted-foreground">Category:</span>
                                <Badge variant="outline">{{ asset.category }}</Badge>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm font-medium text-muted-foreground">Location:</span>
                                <span class="text-sm">{{ asset.location || 'Not specified' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm font-medium text-muted-foreground">Status:</span>
                                <Badge :variant="getStatusVariant(asset.status)">
                                    {{ asset.status }}
                                </Badge>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm font-medium text-muted-foreground">Description:</span>
                                <span class="text-sm text-right max-w-xs">{{ asset.description || 'No description'
                                    }}</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Financial Information -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center space-x-2">
                            <Calculator class="h-5 w-5" />
                            <span>Financial Details</span>
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid gap-4">
                            <div class="flex justify-between">
                                <span class="text-sm font-medium text-muted-foreground">Purchase Value:</span>
                                <span class="text-sm font-semibold">{{ formatCurrency(asset.purchase_value) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm font-medium text-muted-foreground">Purchase Date:</span>
                                <span class="text-sm">{{ formatDate(asset.purchase_date) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm font-medium text-muted-foreground">Manufacturer:</span>
                                <span class="text-sm">{{ asset.manufacturer || 'Not specified' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm font-medium text-muted-foreground">Depreciation Method:</span>
                                <span class="text-sm">{{ formatDepreciationMethod(asset.depreciation_method) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm font-medium text-muted-foreground">Useful Life:</span>
                                <span class="text-sm">{{ asset.useful_life }} years</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm font-medium text-muted-foreground">Salvage Value:</span>
                                <span class="text-sm">{{ formatCurrency(asset.salvage_value || 0) }}</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Additional Information -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center space-x-2">
                        <FileText class="h-5 w-5" />
                        <span>Additional Information</span>
                    </CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-muted-foreground">Warranty Expiry:</span>
                            <span class="text-sm">{{ asset.warranty_expiry ? formatDate(asset.warranty_expiry) : 'Not
                                specified' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-muted-foreground">Insurance Policy:</span>
                            <span class="text-sm">{{ asset.insurance_policy || 'Not specified' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-muted-foreground">Created Date:</span>
                            <span class="text-sm">{{ formatDate(asset.created_at) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-muted-foreground">Last Updated:</span>
                            <span class="text-sm">{{ formatDate(asset.updated_at) }}</span>
                        </div>
                    </div>

                    <div v-if="asset.notes" class="pt-4 border-t">
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-muted-foreground">Notes:</span>
                        </div>
                        <p class="text-sm mt-2 text-muted-foreground">{{ asset.notes }}</p>
                    </div>
                </CardContent>
            </Card>

            <!-- Depreciation History -->
            <Card v-if="depreciationHistory.length > 0">
                <CardHeader>
                    <CardTitle class="flex items-center space-x-2">
                        <TrendingDown class="h-5 w-5" />
                        <span>Depreciation History</span>
                    </CardTitle>
                    <CardDescription>
                        Monthly depreciation calculations for this asset
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Period</TableHead>
                                    <TableHead>Depreciation Amount</TableHead>
                                    <TableHead>Accumulated Depreciation</TableHead>
                                    <TableHead class="text-right">Net Book Value</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="entry in depreciationHistory" :key="entry.id">
                                    <TableCell>{{ formatDate(entry.period) }}</TableCell>
                                    <TableCell>{{ formatCurrency(entry.depreciation_amount) }}</TableCell>
                                    <TableCell>{{ formatCurrency(entry.accumulated_depreciation) }}</TableCell>
                                    <TableCell class="text-right">{{ formatCurrency(entry.net_book_value) }}</TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
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
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow
} from '@/components/ui/table';
import {
    ArrowLeft,
    Edit,
    Building2,
    Calculator,
    TrendingDown,
    Wallet,
    Info,
    FileText
} from 'lucide-vue-next';
import { useApi } from '@/composables/useApi';

const props = defineProps<{
    id: string | number;
}>();

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Finance', href: '/finance' },
    { title: 'Fixed Assets', href: '/finance/fixed-assets' },
    { title: 'Asset Details', href: `/finance/fixed-assets/${props.id}` }
];

const asset = ref<any>({});
const depreciationHistory = ref<any[]>([]);
const loading = ref(false);

const fetchAsset = async () => {
    loading.value = true;
    try {
        const [assetResponse, depreciationResponse] = await Promise.all([
            useApi().get(`/api/finance/fixed-assets/${props.id}`),
            useApi().get(`/api/finance/fixed-assets/${props.id}/depreciation-history`)
        ]);

        asset.value = assetResponse.data;
        depreciationHistory.value = depreciationResponse.data || [];
    } catch (error) {
        console.error('Error fetching asset data:', error);
    } finally {
        loading.value = false;
    }
};

const getStatusVariant = (status: string): "default" | "secondary" | "destructive" | "outline" => {
    const variants: Record<string, "default" | "secondary" | "destructive" | "outline"> = {
        'active': 'default',
        'disposed': 'destructive',
        'maintenance': 'secondary'
    };
    return variants[status] || 'secondary';
};

const formatCurrency = (amount: number): string => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(amount || 0);
};

const formatDate = (date: string): string => {
    if (!date) return 'Not specified';
    return new Date(date).toLocaleDateString('id-ID');
};

const formatDepreciationMethod = (method: string): string => {
    const methods: Record<string, string> = {
        'straight_line': 'Straight Line',
        'declining_balance': 'Declining Balance',
        'sum_of_years': 'Sum of Years',
        'units_of_production': 'Units of Production'
    };
    return methods[method] || method;
};

onMounted(() => {
    fetchAsset();
});
</script>
