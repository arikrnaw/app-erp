<template>

    <Head title="Bank Reconciliation" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 space-y-6">
            <!-- Header Section -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Bank Reconciliation</h1>
                    <p class="text-muted-foreground mt-1">
                        Reconcile bank statements with your accounting records
                    </p>
                </div>
                <div class="flex items-center space-x-2">
                    <Button variant="outline" size="sm" @click="exportReconciliationReport">
                        <Download class="h-4 w-4 mr-2" />
                        Export Report
                    </Button>
                    <Button @click="showImportStatementModal = true">
                        <Upload class="h-4 w-4 mr-2" />
                        Import Statement
                    </Button>
                </div>
            </div>

            <!-- Reconciliation Overview Cards -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center space-x-4">
                            <div class="p-2 bg-blue-100 dark:bg-blue-900/20 rounded-lg">
                                <Building2 class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-muted-foreground">Active Accounts</p>
                                <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                    {{ activeAccountsCount }}
                                </p>
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
                                <p class="text-sm font-medium text-muted-foreground">Reconciled</p>
                                <p class="text-2xl font-bold text-green-600 dark:text-green-400">
                                    {{ reconciledCount }}
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center space-x-4">
                            <div class="p-2 bg-orange-100 dark:bg-orange-900/20 rounded-lg">
                                <AlertCircle class="h-6 w-6 text-orange-600 dark:text-orange-400" />
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-muted-foreground">Pending</p>
                                <p class="text-2xl font-bold text-orange-600 dark:text-orange-400">
                                    {{ pendingCount }}
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center space-x-4">
                            <div class="p-2 bg-red-100 dark:bg-red-900/20 rounded-lg">
                                <XCircle class="h-6 w-6 text-red-600 dark:text-red-400" />
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-muted-foreground">Discrepancies</p>
                                <p class="text-2xl font-bold text-red-600 dark:text-red-400">
                                    {{ discrepanciesCount }}
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Quick Actions Grid -->
            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Bank Accounts Management -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center space-x-2">
                            <Building2 class="h-5 w-5" />
                            <span>Bank Accounts</span>
                        </CardTitle>
                        <CardDescription>
                            Manage your bank accounts and setup reconciliation
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
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
                                    <p class="font-semibold">{{ formatCurrency(account.balance || 0) }}</p>
                                    <Badge :variant="getReconciliationStatusVariant(account.reconciliation_status)">
                                        {{ account.reconciliation_status || 'Not Started' }}
                                    </Badge>
                                </div>
                            </div>
                            <Link :href="route('finance.bank-reconciliation.bank-accounts.index')">
                            <Button variant="outline" class="w-full">
                                <Eye class="h-4 w-4 mr-2" />
                                Manage Bank Accounts
                            </Button>
                            </Link>
                        </div>
                    </CardContent>
                </Card>

                <!-- Recent Reconciliations -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center space-x-2">
                            <FileCheck class="h-5 w-5" />
                            <span>Recent Reconciliations</span>
                        </CardTitle>
                        <CardDescription>
                            Latest reconciliation activities
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div v-for="reconciliation in recentReconciliations" :key="reconciliation.id"
                                class="flex items-center space-x-4 p-4 border rounded-lg hover:bg-muted/50">
                                <div class="p-2 rounded-lg" :class="getReconciliationIconClass(reconciliation.status)">
                                    <component :is="getReconciliationIcon(reconciliation.status)" class="h-4 w-4" />
                                </div>
                                <div class="flex-1 space-y-1">
                                    <p class="font-medium">{{ reconciliation.bank_account_name }}</p>
                                    <p class="text-sm text-muted-foreground">
                                        {{ formatDate(reconciliation.reconciliation_date) }} •
                                        {{ reconciliation.transactions_count }} transactions
                                    </p>
                                </div>
                                <Badge :variant="getReconciliationStatusVariant(reconciliation.status)">
                                    {{ reconciliation.status }}
                                </Badge>
                            </div>
                            <div v-if="recentReconciliations.length === 0" class="text-center py-8">
                                <FileCheck class="h-8 w-8 text-muted-foreground mx-auto mb-2" />
                                <p class="text-sm text-muted-foreground">No recent reconciliations</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Quick Actions -->
            <Card>
                <CardHeader>
                    <CardTitle>Quick Actions</CardTitle>
                    <CardDescription>
                        Common bank reconciliation tasks
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-4">
                        <Button variant="outline" class="w-full justify-start" @click="showImportStatementModal = true">
                            <Upload class="h-4 w-4 mr-2" />
                            Import Statement
                        </Button>
                        <Button variant="outline" class="w-full justify-start" @click="showReconciliationModal = true">
                            <FileCheck class="h-4 w-4 mr-2" />
                            Start Reconciliation
                        </Button>
                        <Button variant="outline" class="w-full justify-start" @click="showDiscrepancyModal = true">
                            <AlertTriangle class="h-4 w-4 mr-2" />
                            Review Discrepancies
                        </Button>
                        <Link :href="route('finance.bank-reconciliation.reports.index')">
                        <Button variant="outline" class="w-full justify-start">
                            <BarChart3 class="h-4 w-4 mr-2" />
                            Reconciliation Reports
                        </Button>
                        </Link>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Import Statement Modal -->
        <Dialog :open="showImportStatementModal" @update:open="showImportStatementModal = $event">
            <DialogContent class="sm:max-w-[500px] bg-card border-border">
                <DialogHeader>
                    <DialogTitle>Import Bank Statement</DialogTitle>
                    <DialogDescription>
                        Upload your bank statement file to start reconciliation
                    </DialogDescription>
                </DialogHeader>
                <div class="space-y-4">
                    <div class="border-2 border-dashed border-border rounded-lg p-6 text-center">
                        <Upload class="h-8 w-8 text-muted-foreground mx-auto mb-2" />
                        <p class="text-sm text-muted-foreground">
                            Drag and drop your bank statement file here, or click to browse
                        </p>
                        <p class="text-xs text-muted-foreground mt-1">
                            Supported formats: CSV, Excel, OFX, QIF
                        </p>
                    </div>
                    <div class="space-y-2">
                        <Label for="bank_account">Select Bank Account</Label>
                        <Select v-model="selectedBankAccount">
                            <SelectTrigger>
                                <SelectValue placeholder="Choose bank account" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="account in bankAccounts" :key="account.id" :value="account.id">
                                    {{ account.name }} - {{ account.account_number }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="space-y-2">
                        <Label for="statement_date">Statement Date</Label>
                        <Input type="date" v-model="statementDate" />
                    </div>
                </div>
                <DialogFooter>
                    <Button type="button" variant="outline" @click="showImportStatementModal = false">
                        Cancel
                    </Button>
                    <Button @click="importStatement" :disabled="!selectedBankAccount || !statementDate">
                        <Upload class="h-4 w-4 mr-2" />
                        Import Statement
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Start Reconciliation Modal -->
        <Dialog :open="showReconciliationModal" @update:open="showReconciliationModal = $event">
            <DialogContent class="sm:max-w-[500px] bg-card border-border">
                <DialogHeader>
                    <DialogTitle>Start Bank Reconciliation</DialogTitle>
                    <DialogDescription>
                        Begin reconciliation process for a specific bank account
                    </DialogDescription>
                </DialogHeader>
                <div class="space-y-4">
                    <div class="space-y-2">
                        <Label for="reconciliation_account">Select Bank Account</Label>
                        <Select v-model="reconciliationAccount">
                            <SelectTrigger>
                                <SelectValue placeholder="Choose bank account" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="account in bankAccounts" :key="account.id" :value="account.id">
                                    {{ account.name }} - {{ account.account_number }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="space-y-2">
                        <Label for="reconciliation_period">Reconciliation Period</Label>
                        <Select v-model="reconciliationPeriod">
                            <SelectTrigger>
                                <SelectValue placeholder="Select period" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="current_month">Current Month</SelectItem>
                                <SelectItem value="previous_month">Previous Month</SelectItem>
                                <SelectItem value="custom">Custom Period</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>
                <DialogFooter>
                    <Button type="button" variant="outline" @click="showReconciliationModal = false">
                        Cancel
                    </Button>
                    <Button @click="startReconciliation" :disabled="!reconciliationAccount">
                        <FileCheck class="h-4 w-4 mr-2" />
                        Start Reconciliation
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Review Discrepancies Modal -->
        <Dialog :open="showDiscrepancyModal" @update:open="showDiscrepancyModal = $event">
            <DialogContent class="sm:max-w-[700px] bg-card border-border">
                <DialogHeader>
                    <DialogTitle>Review Discrepancies</DialogTitle>
                    <DialogDescription>
                        Review and resolve reconciliation discrepancies
                    </DialogDescription>
                </DialogHeader>
                <div class="space-y-4">
                    <div v-if="discrepancies.length === 0" class="text-center py-8">
                        <CheckCircle class="h-8 w-8 text-green-600 mx-auto mb-2" />
                        <p class="text-sm text-muted-foreground">No discrepancies found</p>
                    </div>
                    <div v-else v-for="discrepancy in discrepancies" :key="discrepancy.id"
                        class="p-4 border rounded-lg space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-medium">{{ discrepancy.description }}</span>
                            <Badge :variant="getDiscrepancyVariant(discrepancy.type)">
                                {{ discrepancy.type }}
                            </Badge>
                        </div>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-muted-foreground">Bank Amount:</span>
                                <span class="ml-2 font-mono">{{ formatCurrency(discrepancy.bank_amount) }}</span>
                            </div>
                            <div>
                                <span class="text-muted-foreground">Book Amount:</span>
                                <span class="ml-2 font-mono">{{ formatCurrency(discrepancy.book_amount) }}</span>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <Button size="sm" variant="outline" @click="resolveDiscrepancy(discrepancy.id)">
                                <Check class="h-4 w-4 mr-1" />
                                Resolve
                            </Button>
                            <Button size="sm" variant="outline" @click="investigateDiscrepancy(discrepancy.id)">
                                <Search class="h-4 w-4 mr-1" />
                                Investigate
                            </Button>
                        </div>
                    </div>
                </div>
                <DialogFooter>
                    <Button type="button" variant="outline" @click="showDiscrepancyModal = false">
                        Close
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
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
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import {
    Building2,
    CheckCircle,
    AlertCircle,
    XCircle,
    Download,
    Upload,
    FileCheck,
    AlertTriangle,
    BarChart3,
    Check,
    Search
} from 'lucide-vue-next';
import { useApi } from '@/composables/useApi';

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Finance', href: '/finance' },
    { title: 'Bank Reconciliation', href: '/finance/bank-reconciliation' }
];

const api = useApi();
const loading = ref(false);

// Data
const bankAccounts = ref<any[]>([]);
const recentReconciliations = ref<any[]>([]);
const discrepancies = ref<any[]>([]);

// Modal states
const showImportStatementModal = ref(false);
const showReconciliationModal = ref(false);
const showDiscrepancyModal = ref(false);

// Form data
const selectedBankAccount = ref('');
const statementDate = ref('');
const reconciliationAccount = ref('');
const reconciliationPeriod = ref('');

// Computed
const activeAccountsCount = computed(() =>
    bankAccounts.value.filter(acc => acc.status === 'active').length
);

const reconciledCount = computed(() =>
    recentReconciliations.value.filter(rec => rec.status === 'completed').length
);

const pendingCount = computed(() =>
    recentReconciliations.value.filter(rec => rec.status === 'pending').length
);

const discrepanciesCount = computed(() => discrepancies.value.length);

// Methods
const fetchData = async () => {
    loading.value = true;
    try {
        const [accountsResponse, reconciliationsResponse, discrepanciesResponse] = await Promise.all([
            api.get('/api/finance/bank-reconciliation/bank-accounts'),
            api.get('/api/finance/bank-reconciliation/reconciliations/recent'),
            api.get('/api/finance/bank-reconciliation/discrepancies')
        ]);

        if (accountsResponse.data) {
            bankAccounts.value = accountsResponse.data.data || accountsResponse.data || [];
        }
        if (reconciliationsResponse.data) {
            recentReconciliations.value = reconciliationsResponse.data.data || reconciliationsResponse.data || [];
        }
        if (discrepanciesResponse.data) {
            discrepancies.value = discrepanciesResponse.data.data || discrepanciesResponse.data || [];
        }
    } catch (error) {
        console.error('Error fetching bank reconciliation data:', error);
    } finally {
        loading.value = false;
    }
};

const importStatement = async () => {
    // Implementation for importing bank statement
    console.log('Importing statement for account:', selectedBankAccount.value);
    showImportStatementModal.value = false;
};

const startReconciliation = async () => {
    // Implementation for starting reconciliation
    console.log('Starting reconciliation for account:', reconciliationAccount.value);
    showReconciliationModal.value = false;
};

const resolveDiscrepancy = async (id: number) => {
    // Implementation for resolving discrepancy
    console.log('Resolving discrepancy:', id);
};

const investigateDiscrepancy = async (id: number) => {
    // Implementation for investigating discrepancy
    console.log('Investigating discrepancy:', id);
};

const exportReconciliationReport = async () => {
    try {
        const response = await api.get('/api/finance/bank-reconciliation/export/report', {
            responseType: 'blob'
        });
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', 'bank-reconciliation-report.xlsx');
        document.body.appendChild(link);
        link.click();
        link.remove();
    } catch (error) {
        console.error('Error exporting reconciliation report:', error);
    }
};

const formatCurrency = (amount: number): string => {
    if (isNaN(amount) || !isFinite(amount)) return 'Rp 0';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(amount);
};

const formatDate = (date: string): string => {
    return new Date(date).toLocaleDateString('id-ID');
};

const getReconciliationStatusVariant = (status: string): "default" | "secondary" | "destructive" | "outline" => {
    const variants: Record<string, "default" | "secondary" | "destructive" | "outline"> = {
        'completed': 'default',
        'pending': 'secondary',
        'discrepancy': 'destructive',
        'not_started': 'outline'
    };
    return variants[status] || 'secondary';
};

const getReconciliationIcon = (status: string) => {
    const icons: Record<string, any> = {
        'completed': CheckCircle,
        'pending': AlertCircle,
        'discrepancy': XCircle,
        'not_started': FileCheck
    };
    return icons[status] || FileCheck;
};

const getReconciliationIconClass = (status: string): string => {
    const classes: Record<string, string> = {
        'completed': 'bg-green-100 dark:bg-green-900/20',
        'pending': 'bg-orange-100 dark:bg-orange-900/20',
        'discrepancy': 'bg-red-100 dark:bg-red-900/20',
        'not_started': 'bg-gray-100 dark:bg-gray-900/20'
    };
    return classes[status] || 'bg-gray-100 dark:bg-gray-900/20';
};

const getDiscrepancyVariant = (type: string): "default" | "secondary" | "destructive" | "outline" => {
    const variants: Record<string, "default" | "secondary" | "destructive" | "outline"> = {
        'amount_mismatch': 'destructive',
        'missing_transaction': 'secondary',
        'duplicate_transaction': 'outline',
        'timing_difference': 'default'
    };
    return variants[type] || 'secondary';
};

onMounted(() => {
    fetchData();
});
</script>
