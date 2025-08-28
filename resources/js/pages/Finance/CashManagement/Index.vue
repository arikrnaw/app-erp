<template>

    <Head title="Cash Management" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 space-y-6">
            <!-- Header Section -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Cash Management</h1>
                    <p class="text-muted-foreground mt-1">
                        Manage your company's cash flow, bank accounts, and petty cash
                    </p>
                </div>
                <div class="flex items-center space-x-2">
                    <Button variant="outline" size="sm" @click="exportCashFlow" :disabled="loading">
                        <Download class="h-4 w-4 mr-2" />
                        Export Cash Flow
                    </Button>
                    <Button @click="showTransactionModal = true">
                        <Plus class="w-4 h-4 mr-2" />
                        New Transaction
                    </Button>
                </div>
            </div>

            <!-- Cash Overview Cards -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center space-x-4">
                            <div class="p-2 bg-green-100 dark:bg-green-900/20 rounded-lg">
                                <Wallet class="h-6 w-6 text-green-600 dark:text-green-400" />
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-muted-foreground">Total Cash Balance</p>
                                <p class="text-2xl font-bold text-green-600 dark:text-green-400">
                                    {{ formatCurrency(totalCashBalance) }}
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center space-x-4">
                            <div class="p-2 bg-blue-100 dark:bg-blue-900/20 rounded-lg">
                                <TrendingUp class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-muted-foreground">Cash Inflow</p>
                                <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                    {{ formatCurrency(totalCashInflow) }}
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center space-x-4">
                            <div class="p-2 bg-red-100 dark:bg-red-900/20 rounded-lg">
                                <TrendingDown class="h-6 w-6 text-red-600 dark:text-red-400" />
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-muted-foreground">Cash Outflow</p>
                                <p class="text-2xl font-bold text-red-600 dark:text-red-400">
                                    {{ formatCurrency(totalCashOutflow) }}
                                </p>
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
                                <p class="text-sm font-medium text-muted-foreground">Net Cash Flow</p>
                                <p class="text-2xl font-bold" :class="netCashFlowColor">
                                    {{ formatCurrency(netCashFlow) }}
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Cash Accounts Grid -->
            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Bank Accounts -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center space-x-2">
                            <Building2 class="h-5 w-5" />
                            <span>Bank Accounts</span>
                        </CardTitle>
                        <CardDescription>
                            Manage your bank accounts and balances
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <!-- Debug info -->
                            <div v-if="bankAccounts.length === 0"
                                class="text-center py-4 text-sm text-muted-foreground">
                                No bank accounts found. Debug: {{ typeof bankAccounts }} - {{
                                    Array.isArray(bankAccounts) ? 'Array' : 'Not Array' }}
                            </div>
                            <div v-for="account in bankAccounts" :key="account.id"
                                class="flex items-center justify-between p-4 border rounded-lg hover:bg-muted/50">
                                <div class="flex items-center space-x-3">
                                    <div class="p-2 bg-blue-100 dark:bg-blue-900/20 rounded-lg">
                                        <Building2 class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                                    </div>
                                    <div>
                                        <p class="font-medium">{{ account.name }}</p>
                                        <p class="text-sm text-muted-foreground">{{ account.account_number }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold">{{ formatCurrency(parseFloat(account.balance) || 0) }}</p>
                                    <p class="text-sm text-muted-foreground">{{ account.currency }}</p>
                                </div>
                            </div>
                            <Link :href="route('finance.bank-reconciliation.bank-accounts.index')">
                            <Button variant="outline" class="w-full">
                                <Eye class="h-4 w-4 mr-2" />
                                View All Bank Accounts
                            </Button>
                            </Link>
                        </div>
                    </CardContent>
                </Card>

                <!-- Petty Cash -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center space-x-2">
                            <Coins class="h-5 w-5" />
                            <span>Petty Cash</span>
                        </CardTitle>
                        <CardDescription>
                            Manage petty cash funds and expenses
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <!-- Debug info -->
                            <div v-if="pettyCashFunds.length === 0"
                                class="text-center py-4 text-sm text-muted-foreground">
                                No petty cash funds found. Debug: {{ typeof pettyCashFunds }} - {{
                                    Array.isArray(pettyCashFunds) ? 'Array' : 'Not Array' }}
                            </div>
                            <div v-for="fund in pettyCashFunds" :key="fund.id"
                                class="flex items-center justify-between p-4 border rounded-lg hover:bg-muted/50">
                                <div class="flex items-center space-x-3">
                                    <div class="p-2 bg-green-100 dark:bg-green-900/20 rounded-lg">
                                        <Coins class="h-4 w-4 text-green-600 dark:text-green-400" />
                                    </div>
                                    <div>
                                        <p class="font-medium">{{ fund.name }}</p>
                                        <p class="text-sm text-muted-foreground">{{ fund.custodian }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold">{{ formatCurrency(parseFloat(fund.balance) || 0) }}</p>
                                    <p class="text-sm text-muted-foreground">Available</p>
                                </div>
                            </div>
                            <Link :href="route('finance.cash-management.petty-cash.index')">
                            <Button variant="outline" class="w-full">
                                <Eye class="h-4 w-4 mr-2" />
                                View All Petty Cash
                            </Button>
                            </Link>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Recent Transactions -->
            <Card>
                <CardHeader>
                    <CardTitle>Recent Cash Transactions</CardTitle>
                    <CardDescription>
                        Latest cash movements and bank transactions
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <div v-for="transaction in recentTransactions" :key="transaction.id"
                            class="flex items-center space-x-4 p-4 border rounded-lg hover:bg-muted/50">
                            <div class="p-2 rounded-lg" :class="getTransactionIconClass(transaction.type)">
                                <component :is="getTransactionIcon(transaction.type)" class="h-4 w-4" />
                            </div>
                            <div class="flex-1 space-y-1">
                                <p class="font-medium">{{ transaction.description }}</p>
                                <p class="text-sm text-muted-foreground">
                                    {{ transaction.bank_account?.name || 'Unknown Account' }} • {{
                                        formatDate(transaction.transaction_date) }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold"
                                    :class="parseFloat(transaction.amount) > 0 ? 'text-green-600' : 'text-red-600'">
                                    {{ parseFloat(transaction.amount) > 0 ? '+' : '' }}{{
                                        formatCurrency(parseFloat(transaction.amount) || 0) }}
                                </p>
                                <Badge :variant="getStatusVariant(transaction.status)" class="text-xs">
                                    {{ transaction.status }}
                                </Badge>
                            </div>
                        </div>
                        <div v-if="recentTransactions.length === 0" class="text-center py-8">
                            <FileText class="h-8 w-8 text-muted-foreground mx-auto mb-2" />
                            <p class="text-sm text-muted-foreground">No recent transactions</p>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Quick Actions -->
            <Card>
                <CardHeader>
                    <CardTitle>Quick Actions</CardTitle>
                    <CardDescription>
                        Common cash management tasks
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-4">
                        <Button variant="outline" class="w-full justify-start" @click="showTransactionModal = true">
                            <Plus class="h-4 w-4 mr-2" />
                            New Transaction
                        </Button>
                        <Button variant="outline" class="w-full justify-start" @click="showBankAccountModal = true">
                            <Building2 class="h-4 w-4 mr-2" />
                            Add Bank Account
                        </Button>
                        <Button variant="outline" class="w-full justify-start" @click="showPettyCashModal = true">
                            <Coins class="h-4 w-4 mr-2" />
                            Create Petty Cash Fund
                        </Button>
                        <Link :href="route('finance.cash-management.reports.cash-flow')">
                        <Button variant="outline" class="w-full justify-start">
                            <BarChart3 class="h-4 w-4 mr-2" />
                            Cash Flow Report
                        </Button>
                        </Link>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Modals -->
        <CreateBankAccountModal v-model:open="showBankAccountModal" @created="fetchData" />

        <CreatePettyCashModal v-model:open="showPettyCashModal" @created="fetchData" />
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
    Wallet,
    TrendingUp,
    TrendingDown,
    Calculator,
    Download,
    Plus,
    Building2,
    Coins,
    Eye,
    FileText,
    BarChart3
} from 'lucide-vue-next';
import { useApi } from '@/composables/useApi';
import CreateBankAccountModal from '@/components/Finance/CashManagement/CreateBankAccountModal.vue';
import CreatePettyCashModal from '@/components/Finance/CashManagement/CreatePettyCashModal.vue';

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Finance', href: '/finance' },
    { title: 'Cash Management', href: '/finance/cash-management' }
];

const api = useApi();
const loading = ref(false);

// Data
const bankAccounts = ref<any[]>([]);
const pettyCashFunds = ref<any[]>([]);
const recentTransactions = ref<any[]>([]);

// Modal states
const showBankAccountModal = ref(false);
const showPettyCashModal = ref(false);
const showTransactionModal = ref(false);

// Computed
const totalCashBalance = computed(() => {
    if (!Array.isArray(bankAccounts.value) || !Array.isArray(pettyCashFunds.value)) {
        return 0;
    }
    const bankTotal = bankAccounts.value.reduce((sum, acc) => {
        const balance = parseFloat(acc.balance) || 0;
        return sum + balance;
    }, 0);
    const pettyTotal = pettyCashFunds.value.reduce((sum, fund) => {
        const balance = parseFloat(fund.balance) || 0;
        return sum + balance;
    }, 0);
    return bankTotal + pettyTotal;
});

const totalCashInflow = computed(() => {
    if (!Array.isArray(recentTransactions.value)) {
        return 0;
    }
    return recentTransactions.value
        .filter(t => parseFloat(t.amount) > 0)
        .reduce((sum, t) => {
            const amount = parseFloat(t.amount) || 0;
            return sum + amount;
        }, 0);
});

const totalCashOutflow = computed(() => {
    if (!Array.isArray(recentTransactions.value)) {
        return 0;
    }
    return recentTransactions.value
        .filter(t => parseFloat(t.amount) < 0)
        .reduce((sum, t) => {
            const amount = parseFloat(t.amount) || 0;
            return sum + Math.abs(amount);
        }, 0);
});

const netCashFlow = computed(() => {
    const inflow = totalCashInflow.value || 0;
    const outflow = totalCashOutflow.value || 0;
    return inflow - outflow;
});

const netCashFlowColor = computed(() => {
    const flow = netCashFlow.value;
    if (isNaN(flow) || !isFinite(flow)) return 'text-gray-600 dark:text-gray-400';
    return flow >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400';
});

// Methods
const fetchData = async () => {
    loading.value = true;
    try {
        const [bankResponse, pettyResponse, transactionsResponse] = await Promise.all([
            api.get('/api/finance/cash-management/bank-accounts/dashboard'),
            api.get('/api/finance/cash-management/petty-cash/dashboard'),
            api.get('/api/finance/cash-management/transactions/recent')
        ]);

        // Ensure we're getting arrays and handle potential errors
        if (bankResponse.data && Array.isArray(bankResponse.data)) {
            bankAccounts.value = bankResponse.data.map((acc: any) => ({
                ...acc,
                balance: acc.balance !== null && acc.balance !== undefined ? acc.balance : 0
            }));
        } else if (bankResponse.data && bankResponse.data.data && Array.isArray(bankResponse.data.data)) {
            bankAccounts.value = bankResponse.data.data.map((acc: any) => ({
                ...acc,
                balance: acc.balance !== null && acc.balance !== undefined ? acc.balance : 0
            }));
        } else {
            console.warn('Bank accounts data is not an array:', bankResponse.data);
            bankAccounts.value = [];
        }

        if (pettyResponse.data && Array.isArray(pettyResponse.data)) {
            pettyCashFunds.value = pettyResponse.data.map((fund: any) => ({
                ...fund,
                balance: fund.current_balance !== null && fund.current_balance !== undefined ? fund.current_balance : 0
            }));
        } else if (pettyResponse.data && pettyResponse.data.data && Array.isArray(pettyResponse.data.data)) {
            pettyCashFunds.value = pettyResponse.data.data.map((fund: any) => ({
                ...fund,
                balance: fund.current_balance !== null && fund.current_balance !== undefined ? fund.current_balance : 0
            }));
        } else {
            console.warn('Petty cash data is not an array:', pettyResponse.data);
            pettyCashFunds.value = [];
        }

        if (transactionsResponse.data && Array.isArray(transactionsResponse.data)) {
            recentTransactions.value = transactionsResponse.data.map((transaction: any) => ({
                ...transaction,
                amount: transaction.amount !== null && transaction.amount !== undefined ? transaction.amount : 0
            }));
        } else if (transactionsResponse.data && transactionsResponse.data.data && Array.isArray(transactionsResponse.data.data)) {
            recentTransactions.value = transactionsResponse.data.data.map((transaction: any) => ({
                ...transaction,
                amount: transaction.amount !== null && transaction.amount !== undefined ? transaction.amount : 0
            }));
        } else {
            console.warn('Transactions data is not an array:', transactionsResponse.data);
            recentTransactions.value = [];
        }

        console.log('Fetched data:', {
            bankAccounts: bankAccounts.value,
            pettyCashFunds: pettyCashFunds.value,
            recentTransactions: recentTransactions.value
        });

        // Debug balance values
        console.log('Balance debugging:', {
            bankBalances: bankAccounts.value.map(acc => ({
                id: acc.id,
                balance: acc.balance,
                type: typeof acc.balance,
                allFields: Object.keys(acc)
            })),
            pettyBalances: pettyCashFunds.value.map(fund => ({
                id: fund.id,
                balance: fund.balance,
                type: typeof fund.balance,
                allFields: Object.keys(fund)
            })),
            transactionAmounts: recentTransactions.value.map(t => ({
                id: t.id,
                amount: t.amount,
                type: typeof t.amount,
                allFields: Object.keys(t)
            }))
        });
    } catch (error) {
        console.error('Error fetching cash management data:', error);
        // Set empty arrays on error to prevent crashes
        bankAccounts.value = [];
        pettyCashFunds.value = [];
        recentTransactions.value = [];
    } finally {
        loading.value = false;
    }
};

const exportCashFlow = async () => {
    try {
        const response = await api.get('/api/finance/cash-management/export/cash-flow', {
            responseType: 'blob'
        });

        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', 'cash-flow-report.xlsx');
        document.body.appendChild(link);
        link.click();
        link.remove();
    } catch (error) {
        console.error('Error exporting cash flow:', error);
    }
};

const getTransactionIcon = (type: string) => {
    const icons: Record<string, any> = {
        'deposit': TrendingUp,
        'withdrawal': TrendingDown,
        'transfer': Building2,
        'expense': TrendingDown
    };
    return icons[type] || FileText;
};

const getTransactionIconClass = (type: string): string => {
    const classes: Record<string, string> = {
        'deposit': 'bg-green-100 dark:bg-green-900/20',
        'withdrawal': 'bg-red-100 dark:bg-red-900/20',
        'transfer': 'bg-blue-100 dark:bg-blue-900/20',
        'expense': 'bg-orange-100 dark:bg-orange-900/20'
    };
    return classes[type] || 'bg-gray-100 dark:bg-gray-900/20';
};

const getStatusVariant = (status: string): "default" | "secondary" | "destructive" | "outline" => {
    const variants: Record<string, "default" | "secondary" | "destructive" | "outline"> = {
        'completed': 'default',
        'pending': 'secondary',
        'cancelled': 'destructive'
    };
    return variants[status] || 'secondary';
};

const formatCurrency = (amount: number): string => {
    // Handle NaN and invalid numbers
    if (isNaN(amount) || !isFinite(amount)) {
        return 'Rp 0';
    }
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
