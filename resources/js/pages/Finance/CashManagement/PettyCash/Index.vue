<template>

    <Head title="Petty Cash Funds" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 space-y-6">
            <!-- Header Section -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Petty Cash Funds</h1>
                    <p class="text-muted-foreground mt-1">
                        Manage your company's petty cash funds and expenses
                    </p>
                </div>
                <div class="flex items-center space-x-2">
                    <Button variant="outline" size="sm" @click="exportFunds" :disabled="loading">
                        <Download class="h-4 w-4 mr-2" />
                        Export
                    </Button>
                    <Button @click="showCreateModal = true">
                        <Plus class="w-4 h-4 mr-2" />
                        New Petty Cash Fund
                    </Button>
                </div>
            </div>

            <!-- Filters -->
            <Card>
                <CardContent class="p-4">
                    <div class="flex flex-wrap gap-4 items-center">
                        <div class="flex-1 min-w-[200px]">
                            <Input v-model="filters.search" placeholder="Search funds..." @input="debouncedSearch" />
                        </div>
                        <Select v-model="filters.status">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </Select>
                        <Select v-model="filters.currency">
                            <option value="">All Currencies</option>
                            <option value="IDR">IDR</option>
                            <option value="USD">USD</option>
                            <option value="EUR">EUR</option>
                        </Select>
                        <Button variant="outline" @click="resetFilters">
                            <RotateCcw class="h-4 w-4 mr-2" />
                            Reset
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <!-- Petty Cash Funds Table -->
            <Card>
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="border-b bg-muted/50">
                                <tr>
                                    <th class="text-left p-4 font-medium">Fund Name</th>
                                    <th class="text-left p-4 font-medium">Custodian</th>
                                    <th class="text-left p-4 font-medium">Location</th>
                                    <th class="text-left p-4 font-medium">Initial Amount</th>
                                    <th class="text-left p-4 font-medium">Current Balance</th>
                                    <th class="text-left p-4 font-medium">Currency</th>
                                    <th class="text-left p-4 font-medium">Status</th>
                                    <th class="text-left p-4 font-medium">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="fund in pettyCashFunds" :key="fund.id" class="border-b hover:bg-muted/50">
                                    <td class="p-4">
                                        <div>
                                            <p class="font-medium">{{ fund.name }}</p>
                                            <p class="text-sm text-muted-foreground">{{ fund.description }}</p>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <p class="font-medium">{{ fund.custodian }}</p>
                                    </td>
                                    <td class="p-4">
                                        <p class="text-sm">{{ fund.location || 'N/A' }}</p>
                                    </td>
                                    <td class="p-4">
                                        <p class="font-semibold text-green-600">
                                            {{ formatCurrency(fund.initial_amount) }}
                                        </p>
                                    </td>
                                    <td class="p-4">
                                        <p class="font-semibold"
                                            :class="fund.current_balance >= 0 ? 'text-green-600' : 'text-red-600'">
                                            {{ formatCurrency(fund.current_balance) }}
                                        </p>
                                        <p class="text-xs text-muted-foreground">
                                            {{ fund.needsReplenishment ? 'Needs Replenishment' : 'Sufficient' }}
                                        </p>
                                    </td>
                                    <td class="p-4">
                                        <Badge variant="secondary">{{ fund.currency }}</Badge>
                                    </td>
                                    <td class="p-4">
                                        <Badge :variant="fund.status === 'active' ? 'default' : 'secondary'">
                                            {{ fund.status }}
                                        </Badge>
                                    </td>
                                    <td class="p-4">
                                        <div class="flex items-center space-x-2">
                                            <Link :href="route('finance.cash-management.petty-cash.show', fund.id)">
                                            <Button variant="ghost" size="sm">
                                                <Eye class="h-4 w-4" />
                                            </Button>
                                            </Link>
                                            <Link :href="route('finance.cash-management.petty-cash.edit', fund.id)">
                                            <Button variant="ghost" size="sm">
                                                <Edit class="h-4 w-4" />
                                            </Button>
                                            </Link>
                                            <Button variant="ghost" size="sm" @click="toggleStatus(fund)"
                                                :disabled="loading">
                                                <Power class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="pettyCashFunds.length === 0">
                                    <td colspan="8" class="text-center py-8 text-muted-foreground">
                                        No petty cash funds found
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>

            <!-- Pagination -->
            <div v-if="pagination && pagination.last_page > 1" class="flex items-center justify-between">
                <p class="text-sm text-muted-foreground">
                    Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} results
                </p>
                <div class="flex items-center space-x-2">
                    <Button variant="outline" size="sm" :disabled="pagination.current_page === 1"
                        @click="changePage(pagination.current_page - 1)">
                        Previous
                    </Button>
                    <span class="text-sm">
                        Page {{ pagination.current_page }} of {{ pagination.last_page }}
                    </span>
                    <Button variant="outline" size="sm" :disabled="pagination.current_page === pagination.last_page"
                        @click="changePage(pagination.current_page + 1)">
                        Next
                    </Button>
                </div>
            </div>
        </div>

        <!-- Create Modal -->
        <CreatePettyCashModal v-model:open="showCreateModal" @created="fetchData" />
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItemType } from '@/types';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Select } from '@/components/ui/select';
import {
    Download,
    Plus,
    Eye,
    Edit,
    Power,
    RotateCcw
} from 'lucide-vue-next';
import { useApi } from '@/composables/useApi';
import CreatePettyCashModal from '@/components/Finance/CashManagement/CreatePettyCashModal.vue';

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Finance', href: '/finance' },
    { title: 'Cash Management', href: '/finance/cash-management' },
    { title: 'Petty Cash', href: '/finance/cash-management/petty-cash' }
];

const api = useApi();
const loading = ref(false);

// Data
const pettyCashFunds = ref<any[]>([]);
const pagination = ref<any>(null);

// Modal state
const showCreateModal = ref(false);

// Filters
const filters = ref({
    search: '',
    status: '',
    currency: '',
    page: 1
});

// Methods
const fetchData = async () => {
    loading.value = true;
    try {
        const params = new URLSearchParams();
        if (filters.value.search) params.append('search', filters.value.search);
        if (filters.value.status) params.append('status', filters.value.status);
        if (filters.value.currency) params.append('currency', filters.value.currency);
        if (filters.value.page > 1) params.append('page', filters.value.page.toString());

        const response = await api.get(`/api/finance/cash-management/petty-cash?${params.toString()}`);

        if (response.data && response.data.data) {
            pettyCashFunds.value = response.data.data;
            pagination.value = {
                current_page: response.data.current_page,
                last_page: response.data.last_page,
                from: response.data.from,
                to: response.data.to,
                total: response.data.total
            };
        }
    } catch (error) {
        console.error('Error fetching petty cash funds:', error);
    } finally {
        loading.value = false;
    }
};

const debouncedSearch = () => {
    filters.value.page = 1;
    fetchData();
};

const resetFilters = () => {
    filters.value = {
        search: '',
        status: '',
        currency: '',
        page: 1
    };
    fetchData();
};

const changePage = (page: number) => {
    filters.value.page = page;
    fetchData();
};

const toggleStatus = async (fund: any) => {
    try {
        const newStatus = fund.status === 'active' ? 'inactive' : 'active';
        await api.patch(`/api/finance/cash-management/petty-cash/${fund.id}/status`, {
            status: newStatus
        });

        // Update local state
        fund.status = newStatus;
    } catch (error) {
        console.error('Error toggling status:', error);
    }
};

const exportFunds = async () => {
    try {
        const response = await api.get('/api/finance/cash-management/petty-cash/export', {
            responseType: 'blob'
        });

        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', 'petty-cash-funds.xlsx');
        document.body.appendChild(link);
        link.click();
        link.remove();
    } catch (error) {
        console.error('Error exporting petty cash funds:', error);
    }
};

const formatCurrency = (amount: number): string => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(amount);
};

// Watch filters
watch(filters, () => {
    if (filters.value.page === 1) {
        fetchData();
    }
}, { deep: true });

onMounted(() => {
    fetchData();
});
</script>
