<template>

    <Head :title="`Edit Asset: ${asset.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 space-y-6">
            <!-- Header Section -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Edit Fixed Asset</h1>
                    <p class="text-muted-foreground mt-1">
                        Update asset information: {{ asset.name }}
                    </p>
                </div>
                <div class="flex items-center space-x-2">
                    <Link :href="route('finance.fixed-assets.show', asset.id)">
                    <Button variant="outline">
                        <ArrowLeft class="w-4 h-4 mr-2" />
                        Back to Asset
                    </Button>
                    </Link>
                    <Link :href="route('finance.fixed-assets.index')">
                    <Button variant="outline">
                        <List class="w-4 h-4 mr-2" />
                        All Assets
                    </Button>
                    </Link>
                </div>
            </div>

            <!-- Asset Form -->
            <Card>
                <CardHeader>
                    <CardTitle>Asset Information</CardTitle>
                    <CardDescription>
                        Update the details below to modify your fixed asset
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
                                    :class="{ 'border-red-500': errors.name }" />
                                <p v-if="errors.name" class="text-sm text-red-500">{{ errors.name }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="tag_number">Asset Tag Number *</Label>
                                <Input id="tag_number" v-model="form.tag_number"
                                    placeholder="e.g., FA-001, MACH-2024-001"
                                    :class="{ 'border-red-500': errors.tag_number }" />
                                <p v-if="errors.tag_number" class="text-sm text-red-500">{{ errors.tag_number }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="category_id">Asset Category *</Label>
                                <Select v-model="form.category_id" :class="{ 'border-red-500': errors.category_id }">
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
                                <p v-if="errors.category_id" class="text-sm text-red-500">{{ errors.category_id }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="location">Asset Location</Label>
                                <Input id="location" v-model="form.location"
                                    placeholder="e.g., Building A, Floor 2, Room 201" />
                                <p v-if="errors.location" class="text-sm text-red-500">{{ errors.location }}</p>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="space-y-2">
                            <Label for="description">Description</Label>
                            <Textarea id="description" v-model="form.description"
                                placeholder="Provide a detailed description of the asset..." rows="3" />
                            <p v-if="errors.description" class="text-sm text-red-500">{{ errors.description }}</p>
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
                                        :class="{ 'border-red-500': errors.purchase_value }" />
                                </div>
                                <p v-if="errors.purchase_value" class="text-sm text-red-500">{{ errors.purchase_value }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="purchase_date">Purchase Date *</Label>
                                <Input id="purchase_date" v-model="form.purchase_date" type="date"
                                    :class="{ 'border-red-500': errors.purchase_date }" />
                                <p v-if="errors.purchase_date" class="text-sm text-red-500">{{ errors.purchase_date }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="manufacturer">Manufacturer</Label>
                                <Input id="manufacturer" v-model="form.manufacturer"
                                    placeholder="e.g., PT Supplier Indonesia" />
                                <p v-if="errors.manufacturer" class="text-sm text-red-500">{{ errors.manufacturer }}</p>
                            </div>
                        </div>

                        <!-- Depreciation Settings -->
                        <div class="grid gap-6 md:grid-cols-3">
                            <div class="space-y-2">
                                <Label for="depreciation_method">Depreciation Method *</Label>
                                <Select v-model="form.depreciation_method"
                                    :class="{ 'border-red-500': errors.depreciation_method }">
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
                                <p v-if="errors.depreciation_method" class="text-sm text-red-500">{{
                                    errors.depreciation_method }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="useful_life">Useful Life (Years) *</Label>
                                <Input id="useful_life" v-model="form.useful_life" type="number" min="1" max="100"
                                    placeholder="5" :class="{ 'border-red-500': errors.useful_life }" />
                                <p v-if="errors.useful_life" class="text-sm text-red-500">{{ errors.useful_life }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="salvage_value">Salvage Value</Label>
                                <div class="relative">
                                    <span
                                        class="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground">Rp</span>
                                    <Input id="salvage_value" v-model="form.salvage_value" type="number" step="0.01"
                                        min="0" placeholder="0.00" class="pl-8" />
                                </div>
                                <p v-if="errors.salvage_value" class="text-sm text-red-500">{{ errors.salvage_value }}
                                </p>
                            </div>
                        </div>

                        <!-- Additional Information -->
                        <div class="grid gap-6 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="warranty_expiry">Warranty Expiry Date</Label>
                                <Input id="warranty_expiry" v-model="form.warranty_expiry" type="date" />
                                <p v-if="errors.warranty_expiry" class="text-sm text-red-500">{{ errors.warranty_expiry
                                }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="insurance_info">Insurance Information</Label>
                                <Textarea id="insurance_info" v-model="form.insurance_info"
                                    placeholder="Enter insurance policy details, coverage information, etc." rows="2" />
                                <p v-if="errors.insurance_info" class="text-sm text-red-500">{{
                                    errors.insurance_info }}</p>
                            </div>
                        </div>

                        <!-- Asset Status -->
                        <div class="space-y-2">
                            <Label for="status">Asset Status *</Label>
                            <Select v-model="form.status" :class="{ 'border-red-500': errors.status }">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="active">Active</SelectItem>
                                    <SelectItem value="maintenance">Under Maintenance</SelectItem>
                                    <SelectItem value="disposed">Disposed</SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="errors.status" class="text-sm text-red-500">{{ errors.status }}</p>
                        </div>

                        <!-- Notes -->
                        <div class="space-y-2">
                            <Label for="notes">Additional Notes</Label>
                            <Textarea id="notes" v-model="form.notes"
                                placeholder="Any additional information about the asset..." rows="3" />
                            <p v-if="errors.notes" class="text-sm text-red-500">{{ errors.notes }}</p>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-end space-x-3 pt-6 border-t">
                            <Link :href="route('finance.fixed-assets.show', asset.id)">
                            <Button variant="outline" type="button">
                                Cancel
                            </Button>
                            </Link>
                            <Button type="submit" :disabled="loading">
                                <Loader2 v-if="loading" class="w-4 h-4 mr-2 animate-spin" />
                                <Save v-else class="w-4 h-4 mr-2" />
                                {{ loading ? 'Updating Asset...' : 'Update Asset' }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
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
    List,
    Save,
    Loader2
} from 'lucide-vue-next';
import { useApi } from '@/composables/useApi';

const props = defineProps<{
    id: string | number;
}>();

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Finance', href: '/finance' },
    { title: 'Fixed Assets', href: '/finance/fixed-assets' },
    { title: 'Edit Asset', href: `/finance/fixed-assets/${props.id}/edit` }
];

const loading = ref(false);
const categories = ref<any[]>([]);
const asset = ref<any>({});

const form = useForm({
    name: '',
    tag_number: '',
    category_id: '',
    location: '',
    description: '',
    purchase_value: '',
    purchase_date: '',
    manufacturer: '',
    depreciation_method: '',
    useful_life: '',
    salvage_value: '',
    warranty_expiry: '',
    insurance_info: '',
    status: 'active',
    notes: ''
});

const errors = ref<Record<string, string>>({});

const fetchData = async () => {
    loading.value = true;
    try {
        const [assetResponse, categoriesResponse] = await Promise.all([
            useApi().get(`/api/finance/fixed-assets/${props.id}`),
            useApi().get('/api/finance/fixed-assets/categories')
        ]);

        asset.value = assetResponse.data;
        categories.value = categoriesResponse.data;

        // Populate form with existing data
        form.name = asset.value.name || '';
        form.tag_number = asset.value.tag_number || '';
        form.category_id = asset.value.category_id || '';
        form.location = asset.value.location || '';
        form.description = asset.value.description || '';
        form.purchase_value = asset.value.purchase_value || '';
        form.purchase_date = asset.value.purchase_date ? asset.value.purchase_date.split('T')[0] : '';
        form.manufacturer = asset.value.manufacturer || '';
        form.depreciation_method = asset.value.depreciation_method || '';
        form.useful_life = asset.value.useful_life_years || '';
        form.salvage_value = asset.value.salvage_value || '';
        form.warranty_expiry = asset.value.warranty_expiry ? asset.value.warranty_expiry.split('T')[0] : '';
        form.insurance_info = asset.value.insurance_info || '';
        form.status = asset.value.status || 'active';
        form.notes = asset.value.notes || '';
    } catch (error) {
        console.error('Error fetching asset data:', error);
    } finally {
        loading.value = false;
    }
};

const submitForm = () => {
    form.put(route('finance.fixed-assets.update', props.id), {
        onSuccess: () => {
            // Form will automatically redirect on success
        },
        onError: (errors: any) => {
            // Errors will be automatically handled by Inertia
        }
    });
};

onMounted(() => {
    fetchData();
});
</script>
