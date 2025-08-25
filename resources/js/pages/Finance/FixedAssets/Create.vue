<template>

    <Head title="Add Fixed Asset" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 space-y-6">
            <!-- Header Section -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Add Fixed Asset</h1>
                    <p class="text-muted-foreground mt-1">
                        Register a new fixed asset in your company's asset register
                    </p>
                </div>
                <Link :href="route('finance.fixed-assets.index')">
                <Button variant="outline">
                    <ArrowLeft class="w-4 h-4 mr-2" />
                    Back to Assets
                </Button>
                </Link>
            </div>

            <!-- Asset Form -->
            <Card>
                <CardHeader>
                    <CardTitle>Asset Information</CardTitle>
                    <CardDescription>
                        Fill in the details below to register your new fixed asset
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submitForm" class="space-y-6">
                        <!-- Basic Information -->
                        <div class="grid gap-6 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="name">Asset Name *</Label>
                                <Input id="name" v-model="form.name"
                                    placeholder="e.g., Office Building, Production Machine"
                                    :class="{ 'border-red-500': form.errors.name }" />
                                <p v-if="form.errors.name" class="text-sm text-red-500">{{ form.errors.name }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="tag_number">Asset Tag Number *</Label>
                                <Input id="tag_number" v-model="form.tag_number"
                                    placeholder="e.g., FA-001, MACH-2024-001"
                                    :class="{ 'border-red-500': form.errors.tag_number }" />
                                <p v-if="form.errors.tag_number" class="text-sm text-red-500">{{ form.errors.tag_number
                                }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="category_id">Asset Category *</Label>
                                <Select v-model="form.category_id"
                                    :class="{ 'border-red-500': form.errors.category_id }">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select asset category" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="category in categories" :key="category.id"
                                            :value="category.id">
                                            {{ category.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.category_id" class="text-sm text-red-500">{{
                                    form.errors.category_id }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="location">Asset Location</Label>
                                <Input id="location" v-model="form.location"
                                    placeholder="e.g., Building A, Floor 2, Room 201" />
                                <p v-if="form.errors.location" class="text-sm text-red-500">{{ form.errors.location }}
                                </p>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="space-y-2">
                            <Label for="description">Description</Label>
                            <Textarea id="description" v-model="form.description"
                                placeholder="Provide a detailed description of the asset..." rows="3" />
                            <p v-if="form.errors.description" class="text-sm text-red-500">{{ form.errors.description }}
                            </p>
                        </div>

                        <!-- Financial Information -->
                        <div class="grid gap-6 md:grid-cols-3">
                            <div class="space-y-2">
                                <Label for="purchase_value">Purchase Value *</Label>
                                <div class="relative">
                                    <span
                                        class="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground">Rp</span>
                                    <Input id="purchase_value" v-model="form.purchase_value" type="number" step="0.01"
                                        min="0" placeholder="0.00" class="pl-8"
                                        :class="{ 'border-red-500': form.errors.purchase_value }" />
                                </div>
                                <p v-if="form.errors.purchase_value" class="text-sm text-red-500">{{
                                    form.errors.purchase_value }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="purchase_date">Purchase Date *</Label>
                                <Input id="purchase_date" v-model="form.purchase_date" type="date"
                                    :class="{ 'border-red-500': form.errors.purchase_date }" />
                                <p v-if="form.errors.purchase_date" class="text-sm text-red-500">{{
                                    form.errors.purchase_date }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="manufacturer">Manufacturer</Label>
                                <Input id="manufacturer" v-model="form.manufacturer"
                                    placeholder="e.g., PT Supplier Indonesia" />
                                <p v-if="form.errors.manufacturer" class="text-sm text-red-500">{{
                                    form.errors.manufacturer }}</p>
                            </div>
                        </div>

                        <!-- Depreciation Settings -->
                        <div class="grid gap-6 md:grid-cols-3">
                            <div class="space-y-2">
                                <Label for="depreciation_method">Depreciation Method *</Label>
                                <Select v-model="form.depreciation_method"
                                    :class="{ 'border-red-500': form.errors.depreciation_method }">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select method" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="straight_line">Straight Line</SelectItem>
                                        <SelectItem value="declining_balance">Declining Balance</SelectItem>
                                        <SelectItem value="sum_of_years">Sum of Years</SelectItem>
                                        <SelectItem value="units_of_production">Units of Production</SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.depreciation_method" class="text-sm text-red-500">{{
                                    form.errors.depreciation_method }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="useful_life">Useful Life (Years) *</Label>
                                <Input id="useful_life" v-model="form.useful_life" type="number" min="1" max="100"
                                    placeholder="5" :class="{ 'border-red-500': form.errors.useful_life }" />
                                <p v-if="form.errors.useful_life" class="text-sm text-red-500">{{
                                    form.errors.useful_life }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="salvage_value">Salvage Value</Label>
                                <div class="relative">
                                    <span
                                        class="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground">Rp</span>
                                    <Input id="salvage_value" v-model="form.salvage_value" type="number" step="0.01"
                                        min="0" placeholder="0.00" class="pl-8" />
                                </div>
                                <p v-if="form.errors.salvage_value" class="text-sm text-red-500">{{
                                    form.errors.salvage_value }}
                                </p>
                            </div>
                        </div>

                        <!-- Additional Information -->
                        <div class="grid gap-6 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="warranty_expiry">Warranty Expiry Date</Label>
                                <Input id="warranty_expiry" v-model="form.warranty_expiry" type="date" />
                                <p v-if="form.errors.warranty_expiry" class="text-sm text-red-500">{{
                                    form.errors.warranty_expiry
                                }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="insurance_info">Insurance Information</Label>
                                <Textarea id="insurance_info" v-model="form.insurance_info"
                                    placeholder="Enter insurance policy details, coverage information, etc." rows="2" />
                                <p v-if="form.errors.insurance_info" class="text-sm text-red-500">{{
                                    form.errors.insurance_info }}</p>
                            </div>
                        </div>

                        <!-- Asset Status -->
                        <div class="space-y-2">
                            <Label for="status">Asset Status *</Label>
                            <Select v-model="form.status" :class="{ 'border-red-500': form.errors.status }">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="active">Active</SelectItem>
                                    <SelectItem value="maintenance">Under Maintenance</SelectItem>
                                    <SelectItem value="disposed">Disposed</SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="form.errors.status" class="text-sm text-red-500">{{ form.errors.status }}</p>
                        </div>

                        <!-- Notes -->
                        <div class="space-y-2">
                            <Label for="notes">Additional Notes</Label>
                            <Textarea id="notes" v-model="form.notes"
                                placeholder="Any additional information about the asset..." rows="3" />
                            <p v-if="form.errors.notes" class="text-sm text-red-500">{{ form.errors.notes }}</p>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-end space-x-3 pt-6 border-t">
                            <Link :href="route('finance.fixed-assets.index')">
                            <Button variant="outline" type="button">
                                Cancel
                            </Button>
                            </Link>
                            <Button type="submit" :disabled="loading">
                                <Loader2 v-if="loading" class="w-4 h-4 mr-2 animate-spin" />
                                <Save v-else class="w-4 h-4 mr-2" />
                                {{ loading ? 'Creating Asset...' : 'Create Asset' }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
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
    { title: 'Fixed Assets', href: '/finance/fixed-assets' },
    { title: 'Add Asset', href: '/finance/fixed-assets/create' }
];

const loading = ref(false);
const categories = ref<any[]>([]);

const form = useForm({
    name: '',
    tag_number: '',
    category_id: '', // This will be set after categories are loaded
    location: '',
    description: '',
    purchase_value: '',
    purchase_date: '',
    manufacturer: '',
    depreciation_method: 'straight_line', // Set a default value
    useful_life: '5', // Set a default value
    salvage_value: '',
    warranty_expiry: '',
    insurance_info: '',
    status: 'active',
    notes: ''
});

// Watch for changes in form values for debugging
watch(() => form.category_id, (newValue) => {
    console.log('category_id changed to:', newValue);
});

watch(() => form.data(), (newData) => {
    console.log('Form data changed:', newData);
}, { deep: true });

const fetchCategories = async () => {
    try {
        console.log('Fetching categories...');
        const response = await useApi().get('/api/finance/fixed-assets/categories');
        console.log('Categories response:', response);
        categories.value = response.data;
        console.log('Categories set to:', categories.value);

        // Set a default category_id if none is selected
        if (categories.value.length > 0 && !form.category_id) {
            form.category_id = categories.value[0].id.toString();
            console.log('Set default category_id to:', form.category_id);
        }
    } catch (error) {
        console.error('Error fetching categories:', error);
        // Set a default category if API fails
        categories.value = [
            { id: 1, name: 'Buildings' },
            { id: 2, name: 'Machinery' },
            { id: 3, name: 'Vehicles' },
            { id: 4, name: 'Equipment' },
            { id: 5, name: 'Furniture' },
            { id: 6, name: 'Computers' }
        ];

        // Set default category_id
        if (!form.category_id) {
            form.category_id = '1';
            console.log('Set fallback category_id to:', form.category_id);
        }
    }
};

const submitForm = () => {
    // Add some debugging
    console.log('Form data being submitted:', form.data());
    console.log('Categories available:', categories.value);

    // Validate required fields before submission
    if (!form.name || !form.tag_number || !form.category_id || !form.purchase_value || !form.purchase_date || !form.depreciation_method || !form.useful_life || !form.status) {
        console.error('Required fields missing:', {
            name: !!form.name,
            tag_number: !!form.tag_number,
            category_id: !!form.category_id,
            purchase_value: !!form.purchase_value,
            purchase_date: !!form.purchase_date,
            depreciation_method: !!form.depreciation_method,
            useful_life: !!form.useful_life,
            status: !!form.status
        });
        return;
    }

    form.post(route('finance.fixed-assets.store'), {
        onSuccess: () => {
            // Form will automatically redirect on success
            console.log('Asset created successfully');
        },
        onError: (errors: any) => {
            // Errors will be automatically handled by Inertia
            console.log('Form submission errors:', errors);
        }
    });
};

onMounted(() => {
    fetchCategories();
});
</script>
