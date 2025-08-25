<template>
    <Head title="Add Bank Account" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 space-y-6">
            <!-- Header Section -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Add Bank Account</h1>
                    <p class="text-muted-foreground mt-1">
                        Create a new bank account for your company
                    </p>
                </div>
                <Link :href="route('finance.cash-management.bank-accounts.index')">
                    <Button variant="outline">
                        <ArrowLeft class="w-4 h-4 mr-2" />
                        Back to Bank Accounts
                    </Button>
                </Link>
            </div>

            <!-- Form -->
            <Card>
                <CardHeader>
                    <CardTitle>Bank Account Information</CardTitle>
                    <CardDescription>
                        Fill in the details below to create a new bank account
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submitForm" class="space-y-6">
                        <!-- Basic Information -->
                        <div class="grid gap-6 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="name">Account Name *</Label>
                                <Input 
                                    id="name"
                                    v-model="form.name" 
                                    placeholder="e.g., Main Operating Account"
                                    :class="{ 'border-red-500': errors.name }"
                                    required
                                />
                                <p v-if="errors.name" class="text-sm text-red-500">{{ errors.name }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="account_number">Account Number *</Label>
                                <Input 
                                    id="account_number"
                                    v-model="form.account_number" 
                                    placeholder="e.g., 1234567890"
                                    :class="{ 'border-red-500': errors.account_number }"
                                    required
                                />
                                <p v-if="errors.account_number" class="text-sm text-red-500">{{ errors.account_number }}</p>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="description">Description</Label>
                            <Textarea 
                                id="description"
                                v-model="form.description" 
                                placeholder="Brief description of this account's purpose"
                                rows="3"
                            />
                            <p v-if="errors.description" class="text-sm text-red-500">{{ errors.description }}</p>
                        </div>

                        <!-- Bank Information -->
                        <div class="grid gap-6 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="bank_name">Bank Name *</Label>
                                <Input 
                                    id="bank_name"
                                    v-model="form.bank_name" 
                                    placeholder="e.g., Bank Central Asia (BCA)"
                                    :class="{ 'border-red-500': errors.bank_name }"
                                    required
                                />
                                <p v-if="errors.bank_name" class="text-sm text-red-500">{{ errors.bank_name }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="bank_branch">Bank Branch</Label>
                                <Input 
                                    id="bank_branch"
                                    v-model="form.bank_branch" 
                                    placeholder="e.g., Jakarta Pusat"
                                />
                                <p v-if="errors.bank_branch" class="text-sm text-red-500">{{ errors.bank_branch }}</p>
                            </div>
                        </div>

                        <div class="grid gap-6 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="swift_code">SWIFT Code</Label>
                                <Input 
                                    id="swift_code"
                                    v-model="form.swift_code" 
                                    placeholder="e.g., CENAIDJA"
                                    maxlength="11"
                                />
                                <p v-if="errors.swift_code" class="text-sm text-red-500">{{ errors.swift_code }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="iban">IBAN</Label>
                                <Input 
                                    id="iban"
                                    v-model="form.iban" 
                                    placeholder="e.g., ID91 1234 5678 9012 3456 7890"
                                />
                                <p v-if="errors.iban" class="text-sm text-red-500">{{ errors.iban }}</p>
                            </div>
                        </div>

                        <!-- Account Settings -->
                        <div class="grid gap-6 md:grid-cols-3">
                            <div class="space-y-2">
                                <Label for="currency">Currency *</Label>
                                <Select v-model="form.currency" required>
                                    <SelectTrigger :class="{ 'border-red-500': errors.currency }">
                                        <SelectValue placeholder="Select currency" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="IDR">IDR - Indonesian Rupiah</SelectItem>
                                        <SelectItem value="USD">USD - US Dollar</SelectItem>
                                        <SelectItem value="EUR">EUR - Euro</SelectItem>
                                        <SelectItem value="SGD">SGD - Singapore Dollar</SelectItem>
                                        <SelectItem value="JPY">JPY - Japanese Yen</SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="errors.currency" class="text-sm text-red-500">{{ errors.currency }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="opening_balance">Opening Balance</Label>
                                <Input 
                                    id="opening_balance"
                                    v-model="form.opening_balance" 
                                    type="number"
                                    step="0.01"
                                    placeholder="0.00"
                                />
                                <p v-if="errors.opening_balance" class="text-sm text-red-500">{{ errors.opening_balance }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="opening_date">Opening Date</Label>
                                <Input 
                                    id="opening_date"
                                    v-model="form.opening_date" 
                                    type="date"
                                />
                                <p v-if="errors.opening_date" class="text-sm text-red-500">{{ errors.opening_date }}</p>
                            </div>
                        </div>

                        <!-- Account Type and Status -->
                        <div class="grid gap-6 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="account_type">Account Type *</Label>
                                <Select v-model="form.account_type" required>
                                    <SelectTrigger :class="{ 'border-red-500': errors.account_type }">
                                        <SelectValue placeholder="Select account type" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="checking">Checking Account</SelectItem>
                                        <SelectItem value="savings">Savings Account</SelectItem>
                                        <SelectItem value="time_deposit">Time Deposit</SelectItem>
                                        <SelectItem value="investment">Investment Account</SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="errors.account_type" class="text-sm text-red-500">{{ errors.account_type }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="status">Status</Label>
                                <Select v-model="form.status">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="active">Active</SelectItem>
                                        <SelectItem value="inactive">Inactive</SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="errors.status" class="text-sm text-red-500">{{ errors.status }}</p>
                            </div>
                        </div>

                        <!-- Additional Settings -->
                        <div class="space-y-4">
                            <div class="flex items-center space-x-2">
                                <Checkbox 
                                    id="reconcile_automatically" 
                                    v-model:checked="form.reconcile_automatically"
                                />
                                <Label for="reconcile_automatically">Enable automatic reconciliation</Label>
                            </div>

                            <div class="flex items-center space-x-2">
                                <Checkbox 
                                    id="allow_overdraft" 
                                    v-model:checked="form.allow_overdraft"
                                />
                                <Label for="allow_overdraft">Allow overdraft</Label>
                            </div>

                            <div class="flex items-center space-x-2">
                                <Checkbox 
                                    id="include_in_cash_flow" 
                                    v-model:checked="form.include_in_cash_flow"
                                />
                                <Label for="include_in_cash_flow">Include in cash flow reports</Label>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="space-y-2">
                            <Label for="notes">Notes</Label>
                            <Textarea 
                                id="notes"
                                v-model="form.notes" 
                                placeholder="Additional notes or special instructions"
                                rows="3"
                            />
                            <p v-if="errors.notes" class="text-sm text-red-500">{{ errors.notes }}</p>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-end space-x-3 pt-6 border-t">
                            <Link :href="route('finance.cash-management.bank-accounts.index')">
                                <Button variant="outline" type="button">
                                    Cancel
                                </Button>
                            </Link>
                            <Button type="submit" :disabled="loading">
                                <Loader2 v-if="loading" class="w-4 h-4 mr-2 animate-spin" />
                                <Save v-else class="w-4 h-4 mr-2" />
                                {{ loading ? 'Creating...' : 'Create Bank Account' }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
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
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';
import { 
    Select, 
    SelectContent, 
    SelectItem, 
    SelectTrigger, 
    SelectValue 
} from '@/components/ui/select';
import { 
    ArrowLeft, 
    Save, 
    Loader2 
} from 'lucide-vue-next';
import { useApi } from '@/composables/useApi';

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Finance', href: '/finance' },
    { title: 'Cash Management', href: '/finance/cash-management' },
    { title: 'Bank Accounts', href: '/finance/cash-management/bank-accounts' },
    { title: 'Add Bank Account', href: '/finance/cash-management/bank-accounts/create' }
];

const { api } = useApi();
const loading = ref(false);
const errors = reactive({});

// Form data
const form = reactive({
    name: '',
    account_number: '',
    description: '',
    bank_name: '',
    bank_branch: '',
    swift_code: '',
    iban: '',
    currency: '',
    opening_balance: 0,
    opening_date: new Date().toISOString().split('T')[0],
    account_type: '',
    status: 'active',
    reconcile_automatically: false,
    allow_overdraft: false,
    include_in_cash_flow: true,
    notes: ''
});

// Methods
const submitForm = async () => {
    loading.value = true;
    errors.value = {};
    
    try {
        const response = await api.post('/api/finance/cash-management/bank-accounts', form);
        
        // Redirect to bank accounts index on success
        window.location.href = route('finance.cash-management.bank-accounts.index');
    } catch (error: any) {
        if (error.response?.data?.errors) {
            errors.value = error.response.data.errors;
        } else {
            console.error('Error creating bank account:', error);
        }
    } finally {
        loading.value = false;
    }
};
</script>
