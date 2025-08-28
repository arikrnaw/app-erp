<template>
    <Dialog :open="open" @update:open="$emit('update:open', $event)">
        <DialogContent class="max-w-3xl max-h-[90vh] overflow-y-auto">
            <DialogHeader>
                <DialogTitle>Create Petty Cash Fund</DialogTitle>
                <DialogDescription>
                    Add a new petty cash fund to your cash management system
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submitForm" class="space-y-6">
                <!-- Basic Information -->
                <div class="grid gap-6 md:grid-cols-2">
                    <div class="space-y-2">
                        <Label for="name">Fund Name *</Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            placeholder="e.g., Office Petty Cash"
                            :error="errors.name"
                            required
                        />
                        <p v-if="errors.name" class="text-sm text-destructive">{{ errors.name }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label for="custodian">Custodian *</Label>
                        <Input
                            id="custodian"
                            v-model="form.custodian"
                            placeholder="e.g., John Doe"
                            :error="errors.custodian"
                            required
                        />
                        <p v-if="errors.custodian" class="text-sm text-destructive">{{ errors.custodian }}</p>
                    </div>
                </div>

                <div class="space-y-2">
                    <Label for="description">Description</Label>
                    <Textarea
                        id="description"
                        v-model="form.description"
                        placeholder="Brief description of this petty cash fund"
                        rows="3"
                    />
                </div>

                <!-- Location and Contact -->
                <div class="grid gap-6 md:grid-cols-2">
                    <div class="space-y-2">
                        <Label for="location">Location</Label>
                        <Input
                            id="location"
                            v-model="form.location"
                            placeholder="e.g., Main Office, Floor 2"
                        />
                    </div>
                    <div class="space-y-2">
                        <Label for="contact_number">Contact Number</Label>
                        <Input
                            id="contact_number"
                            v-model="form.contact_number"
                            placeholder="e.g., +62 812-3456-7890"
                        />
                    </div>
                </div>

                <!-- Financial Information -->
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
                        <Label for="initial_amount">Initial Amount *</Label>
                        <Input
                            id="initial_amount"
                            v-model="form.initial_amount"
                            type="number"
                            step="0.01"
                            placeholder="0.00"
                            :error="errors.initial_amount"
                            required
                        />
                        <p v-if="errors.initial_amount" class="text-sm text-destructive">{{ errors.initial_amount }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label for="status">Status</Label>
                        <Select id="status" v-model="form.status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </Select>
                    </div>
                </div>

                <!-- Fund Settings -->
                <div class="space-y-4">
                    <Label>Fund Settings</Label>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="flex items-center space-x-2">
                            <Checkbox
                                id="requires_approval"
                                v-model:checked="form.requires_approval"
                            />
                            <Label for="requires_approval">Requires Approval for Expenses</Label>
                        </div>
                        <div class="flex items-center space-x-2">
                            <Checkbox
                                id="auto_replenish"
                                v-model:checked="form.auto_replenish"
                            />
                            <Label for="auto_replenish">Auto Replenish When Low</Label>
                        </div>
                    </div>
                </div>

                <!-- Replenishment Settings -->
                <div class="grid gap-6 md:grid-cols-2">
                    <div class="space-y-2">
                        <Label for="replenishment_threshold">Replenishment Threshold</Label>
                        <Input
                            id="replenishment_threshold"
                            v-model="form.replenishment_threshold"
                            type="number"
                            step="0.01"
                            placeholder="0.00"
                        />
                        <p class="text-xs text-muted-foreground">
                            Amount at which fund needs replenishment
                        </p>
                    </div>
                    <div class="space-y-2">
                        <Label for="max_expense_amount">Max Expense Amount</Label>
                        <Input
                            id="max_expense_amount"
                            v-model="form.max_expense_amount"
                            type="number"
                            step="0.01"
                            placeholder="0.00"
                        />
                        <p class="text-xs text-muted-foreground">
                            Maximum amount for single expense
                        </p>
                    </div>
                </div>

                <div class="space-y-2">
                    <Label for="notes">Notes</Label>
                    <Textarea
                        id="notes"
                        v-model="form.notes"
                        placeholder="Additional notes about this petty cash fund"
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
                        Create Petty Cash Fund
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
    custodian: '',
    description: '',
    location: '',
    contact_number: '',
    currency: '',
    initial_amount: '',
    status: 'active',
    requires_approval: false,
    auto_replenish: false,
    replenishment_threshold: '',
    max_expense_amount: '',
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
        custodian: '',
        description: '',
        location: '',
        contact_number: '',
        currency: '',
        initial_amount: '',
        status: 'active',
        requires_approval: false,
        auto_replenish: false,
        replenishment_threshold: '',
        max_expense_amount: '',
        notes: ''
    });
    errors.value = {};
};

const submitForm = async () => {
    loading.value = true;
    errors.value = {};

    try {
        const response = await api.post('/api/finance/cash-management/petty-cash', form);

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
        console.error('Error creating petty cash fund:', error);
        if (error.response?.data?.errors) {
            errors.value = error.response.data.errors;
        } else {
            errors.value = { general: 'An error occurred while creating the petty cash fund' };
        }
    } finally {
        loading.value = false;
    }
};
</script>
