<template>

    <Head title="Bank Reconciliation - Process" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 space-y-6">
            <!-- Header Section -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Bank Reconciliation</h1>
                    <p class="text-muted-foreground mt-1">
                        {{ bankAccount?.name }} - {{ bankAccount?.account_number }}
                    </p>
                </div>
                <div class="flex items-center space-x-2">
                    <Button variant="outline" size="sm" @click="exportReconciliation">
                        <Download class="h-4 w-4 mr-2" />
                        Export
                    </Button>
                    <Button @click="completeReconciliation" :disabled="!canComplete">
                        <CheckCircle class="h-4 w-4 mr-2" />
                        Complete Reconciliation
                    </Button>
                </div>
            </div>

            <!-- Reconciliation Summary -->
            <div class="grid gap-4 md:grid-cols-4">
                <Card>
                    <CardContent class="p-4">
                        <div class="text-center">
                            <p class="text-sm text-muted-foreground">Bank Statement Balance</p>
                            <p class="text-2xl font-bold text-blue-600">{{ formatCurrency(bankStatementBalance) }}</p>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="p-4">
                        <div class="text-center">
                            <p class="text-sm text-muted-foreground">Book Balance</p>
                            <p class="text-2xl font-bold text-green-600">{{ formatCurrency(bookBalance) }}</p>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="p-4">
                        <div class="text-center">
                            <p class="text-sm text-muted-foreground">Difference</p>
                            <p class="text-2xl font-bold" :class="differenceColor">{{ formatCurrency(difference) }}</p>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="p-4">
                        <div class="text-center">
                            <p class="text-sm text-muted-foreground">Status</p>
                            <Badge :variant="reconciliationStatusVariant">{{ reconciliationStatus }}</Badge>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Reconciliation Process -->
            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Bank Statement Transactions -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center justify-between">
                            <span>Bank Statement Transactions</span>
                            <Badge variant="outline">{{ bankTransactions.length }} items</Badge>
                        </CardTitle>
                        <CardDescription>
                            Transactions from your bank statement
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-3">
                            <div v-for="transaction in bankTransactions" :key="transaction.id"
                                class="p-3 border rounded-lg hover:bg-muted/50"
                                :class="{ 'bg-green-50 border-green-200': transaction.is_matched }">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <p class="font-medium">{{ transaction.description }}</p>
                                        <p class="text-sm text-muted-foreground">
                                            {{ formatDate(transaction.transaction_date) }} •
                                            {{ transaction.reference_number }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-semibold"
                                            :class="transaction.amount > 0 ? 'text-green-600' : 'text-red-600'">
                                            {{ formatCurrency(Math.abs(transaction.amount)) }}
                                        </p>
                                        <Badge v-if="transaction.is_matched" variant="default" class="text-xs">
                                            Matched
                                        </Badge>
                                    </div>
                                </div>
                                <div v-if="transaction.is_matched" class="mt-2 p-2 bg-green-100 rounded text-sm">
                                    <span class="text-green-700">✓ Matched with: {{
                                        transaction.matched_transaction?.description }}</span>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Book Transactions -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center justify-between">
                            <span>Book Transactions</span>
                            <Badge variant="outline">{{ bookTransactions.length }} items</Badge>
                        </CardTitle>
                        <CardDescription>
                            Transactions from your accounting records
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-3">
                            <div v-for="transaction in bookTransactions" :key="transaction.id"
                                class="p-3 border rounded-lg hover:bg-muted/50"
                                :class="{ 'bg-green-50 border-green-200': transaction.is_matched }">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <p class="font-medium">{{ transaction.description }}</p>
                                        <p class="text-sm text-muted-foreground">
                                            {{ formatDate(transaction.transaction_date) }} •
                                            {{ transaction.reference_number }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-semibold"
                                            :class="transaction.amount > 0 ? 'text-green-600' : 'text-red-600'">
                                            {{ formatCurrency(Math.abs(transaction.amount)) }}
                                        </p>
                                        <Badge v-if="transaction.is_matched" variant="default" class="text-xs">
                                            Matched
                                        </Badge>
                                    </div>
                                </div>
                                <div v-if="transaction.is_matched" class="mt-2 p-2 bg-green-100 rounded text-sm">
                                    <span class="text-green-700">✓ Matched with: {{
                                        transaction.matched_transaction?.description }}</span>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Unreconciled Items -->
            <Card>
                <CardHeader>
                    <CardTitle>Unreconciled Items</CardTitle>
                    <CardDescription>
                        Items that need attention or manual matching
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <div v-if="unreconciledItems.length === 0" class="text-center py-8">
                            <CheckCircle class="h-8 w-8 text-green-600 mx-auto mb-2" />
                            <p class="text-sm text-muted-foreground">All items are reconciled!</p>
                        </div>
                        <div v-else v-for="item in unreconciledItems" :key="item.id"
                            class="p-4 border rounded-lg space-y-3">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-medium">{{ item.description }}</p>
                                    <p class="text-sm text-muted-foreground">
                                        {{ item.type }} • {{ formatDate(item.transaction_date) }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold"
                                        :class="item.amount > 0 ? 'text-green-600' : 'text-red-600'">
                                        {{ formatCurrency(Math.abs(item.amount)) }}
                                    </p>
                                    <Badge :variant="getUnreconciledTypeVariant(item.type)">
                                        {{ item.type }}
                                    </Badge>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <Button size="sm" variant="outline" @click="suggestMatches(item)">
                                    <Search class="h-4 w-4 mr-1" />
                                    Suggest Matches
                                </Button>
                                <Button size="sm" variant="outline" @click="markAsCleared(item)">
                                    <Check class="h-4 w-4 mr-1" />
                                    Mark as Cleared
                                </Button>
                                <Button size="sm" variant="outline" @click="addAdjustment(item)">
                                    <Plus class="h-4 w-4 mr-1" />
                                    Add Adjustment
                                </Button>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Reconciliation Notes -->
            <Card>
                <CardHeader>
                    <CardTitle>Reconciliation Notes</CardTitle>
                    <CardDescription>
                        Add notes about this reconciliation process
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <div class="space-y-2">
                            <Label for="notes">Notes</Label>
                            <Textarea id="notes" v-model="reconciliationNotes"
                                placeholder="Add any notes about this reconciliation..." rows="4" />
                        </div>
                        <div class="flex justify-end">
                            <Button @click="saveNotes">
                                <Save class="h-4 w-4 mr-2" />
                                Save Notes
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Suggest Matches Modal -->
        <Dialog :open="showSuggestionsModal" @update:open="showSuggestionsModal = $event">
            <DialogContent class="sm:max-w-[600px] bg-card border-border">
                <DialogHeader>
                    <DialogTitle>Suggested Matches</DialogTitle>
                    <DialogDescription>
                        Select the best match for this transaction
                    </DialogDescription>
                </DialogHeader>
                <div class="space-y-4">
                    <div v-if="suggestedMatches.length === 0" class="text-center py-4">
                        <p class="text-muted-foreground">No suggestions found</p>
                    </div>
                    <div v-else v-for="match in suggestedMatches" :key="match.id"
                        class="p-3 border rounded-lg hover:bg-muted/50 cursor-pointer" @click="selectMatch(match)">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium">{{ match.description }}</p>
                                <p class="text-sm text-muted-foreground">
                                    {{ formatDate(match.transaction_date) }} • {{ match.reference_number }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold">{{ formatCurrency(Math.abs(match.amount)) }}</p>
                                <p class="text-xs text-muted-foreground">Match Score: {{ match.match_score }}%</p>
                            </div>
                        </div>
                    </div>
                </div>
                <DialogFooter>
                    <Button type="button" variant="outline" @click="showSuggestionsModal = false">
                        Cancel
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Add Adjustment Modal -->
        <Dialog :open="showAdjustmentModal" @update:open="showAdjustmentModal = $event">
            <DialogContent class="sm:max-w-[500px] bg-card border-border">
                <DialogHeader>
                    <DialogTitle>Add Adjustment</DialogTitle>
                    <DialogDescription>
                        Add an adjustment entry for this reconciliation
                    </DialogDescription>
                </DialogHeader>
                <form @submit.prevent="submitAdjustment" class="space-y-4">
                    <div class="space-y-2">
                        <Label for="adjustment_type">Adjustment Type</Label>
                        <Select v-model="adjustmentForm.type">
                            <SelectTrigger>
                                <SelectValue placeholder="Select adjustment type" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="bank_charge">Bank Charge</SelectItem>
                                <SelectItem value="interest_earned">Interest Earned</SelectItem>
                                <SelectItem value="service_fee">Service Fee</SelectItem>
                                <SelectItem value="other">Other</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="space-y-2">
                        <Label for="adjustment_amount">Amount</Label>
                        <Input id="adjustment_amount" v-model="adjustmentForm.amount" type="number" step="0.01"
                            placeholder="0.00" required />
                    </div>
                    <div class="space-y-2">
                        <Label for="adjustment_description">Description</Label>
                        <Input id="adjustment_description" v-model="adjustmentForm.description"
                            placeholder="Adjustment description" required />
                    </div>
                </form>
                <DialogFooter>
                    <Button type="button" variant="outline" @click="showAdjustmentModal = false">
                        Cancel
                    </Button>
                    <Button @click="submitAdjustment" :disabled="!adjustmentForm.type || !adjustmentForm.amount">
                        <Plus class="h-4 w-4 mr-2" />
                        Add Adjustment
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
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
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import {
    Download,
    CheckCircle,
    Search,
    Check,
    Plus,
    Save
} from 'lucide-vue-next';
import { useApi } from '@/composables/useApi';

const page = usePage();
const bankAccountId = page.props.bankAccountId || 1;

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Finance', href: '/finance' },
    { title: 'Bank Reconciliation', href: '/finance/bank-reconciliation' },
    { title: 'Reconciliation Process', href: `/finance/bank-reconciliation/reconciliation/${bankAccountId}` }
];

const api = useApi();
const loading = ref(false);

// Data
const bankAccount = ref<any>(null);
const bankTransactions = ref<any[]>([]);
const bookTransactions = ref<any[]>([]);
const reconciliationNotes = ref('');

// Modal states
const showSuggestionsModal = ref(false);
const showAdjustmentModal = ref(false);
const suggestedMatches = ref<any[]>([]);
const selectedItem = ref<any>(null);

// Adjustment form
const adjustmentForm = ref({
    type: '',
    amount: '',
    description: ''
});

// Computed
const bankStatementBalance = computed(() =>
    bankTransactions.value.reduce((sum, t) => sum + (t.amount || 0), 0)
);

const bookBalance = computed(() =>
    bookTransactions.value.reduce((sum, t) => sum + (t.amount || 0), 0)
);

const difference = computed(() =>
    bankStatementBalance.value - bookBalance.value
);

const differenceColor = computed(() =>
    Math.abs(difference.value) < 0.01 ? 'text-green-600' : 'text-red-600'
);

const reconciliationStatus = computed(() => {
    if (Math.abs(difference.value) < 0.01) return 'Reconciled';
    if (unreconciledItems.value.length === 0) return 'Ready to Complete';
    return 'In Progress';
});

const reconciliationStatusVariant = computed(() => {
    if (reconciliationStatus.value === 'Reconciled') return 'default';
    if (reconciliationStatus.value === 'Ready to Complete') return 'outline';
    return 'secondary';
});

const unreconciledItems = computed(() => {
    const bankUnreconciled = bankTransactions.value.filter(t => !t.is_matched);
    const bookUnreconciled = bookTransactions.value.filter(t => !t.is_matched);
    return [...bankUnreconciled, ...bookUnreconciled];
});

const canComplete = computed(() =>
    Math.abs(difference.value) < 0.01 && unreconciledItems.value.length === 0
);

// Methods
const fetchData = async () => {
    loading.value = true;
    try {
        const [accountResponse, transactionsResponse] = await Promise.all([
            api.get(`/api/finance/bank-reconciliation/bank-accounts/${bankAccountId}`),
            api.get(`/api/finance/bank-reconciliation/reconciliation/${bankAccountId}`)
        ]);

        if (accountResponse.data) {
            bankAccount.value = accountResponse.data;
        }
        if (transactionsResponse.data) {
            bankTransactions.value = transactionsResponse.data.bank_transactions || [];
            bookTransactions.value = transactionsResponse.data.book_transactions || [];
        }
    } catch (error) {
        console.error('Error fetching reconciliation data:', error);
    } finally {
        loading.value = false;
    }
};

const suggestMatches = async (item: any) => {
    selectedItem.value = item;
    try {
        const response = await api.get(`/api/finance/bank-reconciliation/suggest-matches`, {
            params: { transaction_id: item.id, type: item.type }
        });
        suggestedMatches.value = response.data || [];
        showSuggestionsModal.value = true;
    } catch (error) {
        console.error('Error fetching suggestions:', error);
    }
};

const selectMatch = async (match: any) => {
    try {
        await api.post(`/api/finance/bank-reconciliation/match-transactions`, {
            bank_transaction_id: selectedItem.value.type === 'bank' ? selectedItem.value.id : match.id,
            book_transaction_id: selectedItem.value.type === 'book' ? selectedItem.value.id : match.id
        });
        await fetchData();
        showSuggestionsModal.value = false;
    } catch (error) {
        console.error('Error matching transactions:', error);
    }
};

const markAsCleared = async (item: any) => {
    try {
        await api.post(`/api/finance/bank-reconciliation/mark-cleared`, {
            transaction_id: item.id,
            type: item.type
        });
        await fetchData();
    } catch (error) {
        console.error('Error marking as cleared:', error);
    }
};

const addAdjustment = (item: any) => {
    selectedItem.value = item;
    showAdjustmentModal.value = true;
};

const submitAdjustment = async () => {
    try {
        await api.post(`/api/finance/bank-reconciliation/adjustments`, {
            ...adjustmentForm.value,
            bank_account_id: bankAccountId,
            related_transaction_id: selectedItem.value?.id
        });
        await fetchData();
        showAdjustmentModal.value = false;
        adjustmentForm.value = { type: '', amount: '', description: '' };
    } catch (error) {
        console.error('Error adding adjustment:', error);
    }
};

const saveNotes = async () => {
    try {
        await api.post(`/api/finance/bank-reconciliation/notes`, {
            bank_account_id: bankAccountId,
            notes: reconciliationNotes.value
        });
    } catch (error) {
        console.error('Error saving notes:', error);
    }
};

const completeReconciliation = async () => {
    try {
        await api.post(`/api/finance/bank-reconciliation/complete`, {
            bank_account_id: bankAccountId,
            reconciliation_date: new Date().toISOString(),
            notes: reconciliationNotes.value
        });
        // Redirect to reconciliation summary
        window.location.href = `/finance/bank-reconciliation/summary/${bankAccountId}`;
    } catch (error) {
        console.error('Error completing reconciliation:', error);
    }
};

const exportReconciliation = async () => {
    try {
        const response = await api.get(`/api/finance/bank-reconciliation/export/${bankAccountId}`, {
            responseType: 'blob'
        });
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `reconciliation-${bankAccountId}.xlsx`);
        document.body.appendChild(link);
        link.click();
        link.remove();
    } catch (error) {
        console.error('Error exporting reconciliation:', error);
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

const getUnreconciledTypeVariant = (type: string): "default" | "secondary" | "destructive" | "outline" => {
    const variants: Record<string, "default" | "secondary" | "destructive" | "outline"> = {
        'bank': 'outline',
        'book': 'secondary',
        'adjustment': 'destructive'
    };
    return variants[type] || 'secondary';
};

onMounted(() => {
    fetchData();
});
</script>
