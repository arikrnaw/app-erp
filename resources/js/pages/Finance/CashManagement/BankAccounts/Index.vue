<template>
    <Head title="Bank Accounts" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 space-y-6">
            <!-- Header Section -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Bank Accounts</h1>
                    <p class="text-muted-foreground mt-1">
                        Manage your company's bank accounts and balances
                    </p>
                </div>
                <div class="flex items-center space-x-2">
                    <Button variant="outline" size="sm" @click="exportAccounts" :disabled="loading">
                        <Download class="h-4 w-4 mr-2" />
                        Export
                    </Button>
                    <Link :href="route('finance.cash-management.bank-accounts.create')">
                        <Button>
                            <Plus class="w-4 h-4 mr-2" />
                            Add Bank Account
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
                                <Building2 class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-muted-foreground">Total Accounts</p>
                                <p class="text-2xl font-bold">{{ bankAccounts.length }}</p>
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
                                <p class="text-sm font-medium text-muted-foreground">Active Accounts</p>
                                <p class="text-2xl font-bold">{{ activeAccountsCount }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center space-x-4">
                            <div class="p-2 bg-purple-100 dark:bg-purple-900/20 rounded-lg">
                                <Wallet class="h-6 w-6 text-purple-600 dark:text-purple-400" />
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-muted-foreground">Total Balance</p>
                                <p class="text-2xl font-bold">{{ formatCurrency(totalBalance) }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center space-x-4">
                            <div class="p-2 bg-orange-100 dark:bg-orange-900/20 rounded-lg">
                                <Globe class="h-6 w-6 text-orange-600 dark:text-orange-400" />
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-muted-foreground">Currencies</p>
                                <p class="text-2xl font-bold">{{ currenciesCount }}</p>
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
                                    placeholder="Search bank accounts by name, number, or bank..." 
                                    class="pl-10"
                                    @input="debouncedSearch" 
                                />
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <Select v-model="statusFilter" @update:model-value="filterAccounts">
                                <SelectTrigger class="w-[180px]">
                                    <SelectValue placeholder="All Status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">All Status</SelectItem>
                                    <SelectItem value="active">Active</SelectItem>
                                    <SelectItem value="inactive">Inactive</SelectItem>
                                </SelectContent>
                            </Select>
                            <Select v-model="currencyFilter" @update:model-value="filterAccounts">
                                <SelectTrigger class="w-[180px]">
                                    <SelectValue placeholder="All Currencies" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">All Currencies</SelectItem>
                                    <SelectItem value="IDR">IDR</SelectItem>
                                    <SelectItem value="USD">USD</SelectItem>
                                    <SelectItem value="EUR">EUR</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Bank Accounts Table -->
            <Card>
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Bank Account</TableHead>
                                    <TableHead>Account Number</TableHead>
                                    <TableHead>Bank</TableHead>
                                    <TableHead>Currency</TableHead>
                                    <TableHead class="text-right">Balance</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead class="text-right">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="account in filteredAccounts" :key="account.id" class="hover:bg-muted/50">
                                    <TableCell>
                                        <div class="flex items-center space-x-3">
                                            <div class="p-2 bg-blue-100 dark:bg-blue-900/20 rounded-lg">
                                                <Building2 class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                                            </div>
                                            <div>
                                                <p class="font-medium">{{ account.name }}</p>
                                                <p class="text-sm text-muted-foreground">{{ account.description }}</p>
                                            </div>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <code class="px-2 py-1 bg-muted rounded text-sm">{{ account.account_number }}</code>
                                    </TableCell>
                                    <TableCell>
                                        <div class="flex items-center space-x-2">
                                            <img v-if="account.bank_logo" :src="account.bank_logo" :alt="account.bank_name" class="h-6 w-6 rounded" />
                                            <span>{{ account.bank_name }}</span>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <Badge variant="outline">{{ account.currency }}</Badge>
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <div class="text-right">
                                            <p class="font-semibold" :class="account.balance >= 0 ? 'text-green-600' : 'text-red-600'">
                                                {{ formatCurrency(account.balance) }}
                                            </p>
                                            <p class="text-xs text-muted-foreground">{{ account.last_reconciled_date }}</p>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <Badge :variant="account.status === 'active' ? 'default' : 'secondary'">
                                            {{ account.status }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <div class="flex items-center justify-end space-x-2">
                                            <Link :href="route('finance.cash-management.bank-accounts.show', account.id)">
                                                <Button variant="ghost" size="sm">
                                                    <Eye class="h-4 w-4" />
                                                </Button>
                                            </Link>
                                            <Link :href="route('finance.cash-management.bank-accounts.edit', account.id)">
                                                <Button variant="ghost" size="sm">
                                                    <Edit class="h-4 w-4" />
                                                </Button>
                                            </Link>
                                            <Button 
                                                variant="ghost" 
                                                size="sm" 
                                                @click="toggleAccountStatus(account)"
                                                :disabled="loading"
                                            >
                                                <component :is="account.status === 'active' ? Lock : Unlock" class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-if="filteredAccounts.length === 0">
                                    <TableCell colspan="7" class="text-center py-8">
                                        <div class="flex flex-col items-center space-y-2">
                                            <Building2 class="h-8 w-8 text-muted-foreground" />
                                            <p class="text-muted-foreground">No bank accounts found</p>
                                            <Link :href="route('finance.cash-management.bank-accounts.create')">
                                                <Button variant="outline" size="sm">
                                                    <Plus class="h-4 w-4 mr-2" />
                                                    Add First Account
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
            <DataPagination 
                v-if="totalPages > 1"
                :current-page="currentPage"
                :total-pages="totalPages"
                :total-items="totalItems"
                @page-change="handlePageChange"
            />
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
    CheckCircle, 
    Wallet, 
    Globe, 
    Download, 
    Plus,
    Eye,
    Edit,
    Lock,
    Unlock,
    Search
} from 'lucide-vue-next';
import { useApi } from '@/composables/useApi';
import { useDebounce } from '@/composables/useDebounce';

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Finance', href: '/finance' },
    { title: 'Cash Management', href: '/finance/cash-management' },
    { title: 'Bank Accounts', href: '/finance/cash-management/bank-accounts' }
];

const { api } = useApi();
const { debounce } = useDebounce();
const loading = ref(false);

// Data
const bankAccounts = ref([]);
const searchQuery = ref('');
const statusFilter = ref('');
const currencyFilter = ref('');
const currentPage = ref(1);
const totalPages = ref(1);
const totalItems = ref(0);

// Computed
const activeAccountsCount = computed(() => 
    bankAccounts.value.filter(acc => acc.status === 'active').length
);

const totalBalance = computed(() => 
    bankAccounts.value.reduce((sum, acc) => sum + acc.balance, 0)
);

const currenciesCount = computed(() => 
    new Set(bankAccounts.value.map(acc => acc.currency)).size
);

const filteredAccounts = computed(() => {
    let filtered = bankAccounts.value;
    
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(acc => 
            acc.name.toLowerCase().includes(query) ||
            acc.account_number.toLowerCase().includes(query) ||
            acc.bank_name.toLowerCase().includes(query)
        );
    }
    
    if (statusFilter.value) {
        filtered = filtered.filter(acc => acc.status === statusFilter.value);
    }
    
    if (currencyFilter.value) {
        filtered = filtered.filter(acc => acc.currency === currencyFilter.value);
    }
    
    return filtered;
});

// Methods
const fetchBankAccounts = async (page = 1) => {
    loading.value = true;
    try {
        const response = await api.get('/api/finance/cash-management/bank-accounts', {
            params: { page, per_page: 20 }
        });
        
        bankAccounts.value = response.data.data;
        currentPage.value = response.data.current_page;
        totalPages.value = response.data.last_page;
        totalItems.value = response.data.total;
    } catch (error) {
        console.error('Error fetching bank accounts:', error);
    } finally {
        loading.value = false;
    }
};

const debouncedSearch = debounce(() => {
    currentPage.value = 1;
    filterAccounts();
}, 300);

const filterAccounts = () => {
    currentPage.value = 1;
    fetchBankAccounts();
};

const handlePageChange = (page: number) => {
    currentPage.value = page;
    fetchBankAccounts(page);
};

const toggleAccountStatus = async (account: any) => {
    try {
        const newStatus = account.status === 'active' ? 'inactive' : 'active';
        await api.patch(`/api/finance/cash-management/bank-accounts/${account.id}/status`, {
            status: newStatus
        });
        
        account.status = newStatus;
    } catch (error) {
        console.error('Error toggling account status:', error);
    }
};

const exportAccounts = async () => {
    try {
        const response = await api.get('/api/finance/cash-management/bank-accounts/export', {
            responseType: 'blob'
        });
        
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', 'bank-accounts.xlsx');
        document.body.appendChild(link);
        link.click();
        link.remove();
    } catch (error) {
        console.error('Error exporting bank accounts:', error);
    }
};

const formatCurrency = (amount: number): string => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(amount);
};

onMounted(() => {
    fetchBankAccounts();
});
</script>
