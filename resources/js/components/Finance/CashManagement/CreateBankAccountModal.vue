<template>
    <Dialog :open="open" @update:open="$emit('update:open', $event)">
        <DialogContent class="max-w-4xl max-h-[90vh] overflow-y-auto">
            <DialogHeader>
                <DialogTitle>Create Bank Account</DialogTitle>
                <DialogDescription>
                    Add a new bank account to your cash management system
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submitForm" class="space-y-6">
                <!-- Basic Information -->
                <div class="grid gap-6 md:grid-cols-2">
                    <div class="space-y-2">
                        <Label for="name">Account Name *</Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            placeholder="e.g., BCA Main Account"
                            :error="errors.name"
                            required
                        />
                        <p v-if="errors.name" class="text-sm text-destructive">{{ errors.name }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label for="account_number">Account Number *</Label>
                        <Input
                            id="account_number"
                            v-model="form.account_number"
                            placeholder="e.g., 1234567890"
                            :error="errors.account_number"
                            required
                        />
                        <p v-if="errors.account_number" class="text-sm text-destructive">{{ errors.account_number }}</p>
                    </div>
                </div>

                <div class="space-y-2">
                    <Label for="description">Description</Label>
                    <Textarea
                        id="description"
                        v-model="form.description"
                        placeholder="Brief description of this account"
                        rows="3"
                    />
                </div>

                <!-- Bank Information -->
                <div class="grid gap-6 md:grid-cols-2">
                    <div class="space-y-2">
                        <Label for="bank_name">Bank Name *</Label>
                        <Input
                            id="bank_name"
                            v-model="form.bank_name"
                            placeholder="e.g., Bank Central Asia"
                            :error="errors.bank_name"
                            required
                        />
                        <p v-if="errors.bank_name" class="text-sm text-destructive">{{ errors.bank_name }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label for="bank_branch">Bank Branch</Label>
                        <Input
                            id="bank_branch"
                            v-model="form.bank_branch"
                            placeholder="e.g., Jakarta Pusat"
                        />
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
                    </div>
                    <div class="space-y-2">
                        <Label for="iban">IBAN</Label>
                        <Input
                            id="iban"
                            v-model="form.iban"
                            placeholder="e.g., ID12345678901234567890"
                            maxlength="50"
                        />
                    </div>
                </div>

                <!-- Account Details -->
                <div class="grid gap-6 md:grid-cols-3">
                    <div class="space-y-2">
                        <Label for="currency">Currency *</Label>
                        <Select id="currency" v-model="form.currency" required>
                            <option value="">Select Currency</option>
                            <option value="IDR">IDR - Indonesian Rupiah</option>
                            <option value="USD">USD - US Dollar</option>
                            <option value="EUR">EUR - Euro</option>
                            <option value="SGD">SGD - Singapore Dollar</option>
                        </Select>
                        <p v-if="errors.currency" class="text-sm text-destructive">{{ errors.currency }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label for="account_type">Account Type *</Label>
                        <Select id="account_type" v-model="form.account_type" required>
                            <option value="">Select Type</option>
                            <option value="checking">Checking Account</option>
                            <option value="savings">Savings Account</option>
                            <option value="time_deposit">Time Deposit</option>
                            <option value="investment">Investment Account</option>
                        </Select>
                        <p v-if="errors.account_type" class="text-sm text-destructive">{{ errors.account_type }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label for="status">Status</Label>
                        <Select id="status" v-model="form.status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </Select>
                    </div>
                </div>

                <!-- Financial Information -->
                <div class="grid gap-6 md:grid-cols-2">
                    <div class="space-y-2">
                        <Label for="opening_balance">Opening Balance</Label>
                        <Input
                            id="opening_balance"
                            v-model="form.opening_balance"
                            type="number"
                            step="0.01"
                            placeholder="0.00"
                        />
                    </div>
                    <div class="space-y-2">
                        <Label for="opening_date">Opening Date</Label>
                        <Input
                            id="opening_date"
                            v-model="form.opening_date"
                            type="date"
                        />
                    </div>
                </div>

                <!-- Settings -->
                <div class="space-y-4">
                    <Label>Account Settings</Label>
                    <div class="grid gap-4 md:grid-cols-3">
                        <div class="flex items-center space-x-2">
                            <Checkbox
                                id="reconcile_automatically"
                                v-model:checked="form.reconcile_automatically"
                            />
                            <Label for="reconcile_automatically">Auto Reconcile</Label>
                        </div>
                        <div class="flex items-center space-x-2">
                            <Checkbox
                                id="allow_overdraft"
                                v-model:checked="form.allow_overdraft"
                            />
                            <Label for="allow_overdraft">Allow Overdraft</Label>
                        </div>
                        <div class="flex items-center space-x-2">
                            <Checkbox
                                id="include_in_cash_flow"
                                v-model:checked="form.include_in_cash_flow"
                            />
                            <Label for="include_in_cash_flow">Include in Cash Flow</Label>
                        </div>
                    </div>
                </div>

                <div class="space-y-2">
                    <Label for="notes">Notes</Label>
                    <Textarea
                        id="notes"
                        v-model="form.notes"
                        placeholder="Additional notes about this account"
                        rows="3"
                    />
                </div>

                <!-- Error Message -->
                <div v-if="errors.general" class="p-3 bg-destructive/10 border border-destructive/20 rounded-md">
                    <p class="text-sm text-destructive">{{ errors.general }}</p>
                </div>

                <!-- Form Actions -->
                <DialogFooter class="flex items-center justify-end space-x-4 pt-6 border-t">
                    <Button type="button" variant="outline" @click="$emit('update:open', false)">
                        Cancel
                    </Button>
                    <Button type="submit" :disabled="loading">
                        <Loader2 v-if="loading" class="h-4 w-4 mr-2 animate-spin" />
                        Create Bank Account
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import { ref, reactive, watch } from 'vue';
import { useApi } from '@/composables/useApi';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';
import { Loader2 } from 'lucide-vue-next';

interface Props {
    open: boolean;
}

interface Emits {
    (e: 'update:open', value: boolean): void;
    (e: 'created'): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const api = useApi();
const loading = ref(false);

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
    account_type: '',
    status: 'active',
    opening_balance: '',
    opening_date: '',
    reconcile_automatically: false,
    allow_overdraft: false,
    include_in_cash_flow: true,
    notes: ''
});

const errors = ref<Record<string, string>>({});

// Reset form when modal opens
watch(() => props.open, (newOpen) => {
    if (newOpen) {
        resetForm();
    }
});

// Methods
const resetForm = () => {
    Object.assign(form, {
        name: '',
        account_number: '',
        description: '',
        bank_name: '',
        bank_branch: '',
        swift_code: '',
        iban: '',
        currency: '',
        account_type: '',
        status: 'active',
        opening_balance: '',
        opening_date: '',
        reconcile_automatically: false,
        allow_overdraft: false,
        include_in_cash_flow: true,
        notes: ''
    });
    errors.value = {};
};

const submitForm = async () => {
    loading.value = true;
    errors.value = {};

    try {
        const response = await api.post('/api/finance/cash-management/bank-accounts', form);

        if (response.data.success) {
            emit('created');
            emit('update:open', false);
            resetForm();
        } else {
            if (response.data.errors) {
                errors.value = response.data.errors;
            } else {
                errors.value = { general: response.data.message || 'An error occurred' };
            }
        }
    } catch (error: any) {
        console.error('Error creating bank account:', error);
        if (error.response?.data?.errors) {
            errors.value = error.response.data.errors;
        } else {
            errors.value = { general: 'An error occurred while creating the bank account' };
        }
    } finally {
        loading.value = false;
    }
};
</script>
