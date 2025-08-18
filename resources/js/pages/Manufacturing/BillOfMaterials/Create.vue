<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Create Bill of Materials</h1>
                <p class="text-muted-foreground">
                    Define the component structure for your product
                </p>
            </div>
            <Button @click="navigateToIndex" variant="outline">
                <ArrowLeft class="h-4 w-4 mr-2" />
                Back to BOMs
            </Button>
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-6">
            <!-- Basic Information -->
            <Card>
                <CardHeader>
                    <CardTitle>Basic Information</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label for="bom_number">BOM Number</Label>
                            <div class="flex gap-2">
                                <Input
                                    id="bom_number"
                                    v-model="form.bom_number"
                                    placeholder="Auto-generated"
                                    disabled
                                />
                                <Button
                                    @click="generateNumber"
                                    type="button"
                                    variant="outline"
                                    :disabled="generatingNumber"
                                >
                                    <RefreshCw v-if="generatingNumber" class="h-4 w-4 animate-spin" />
                                    <RefreshCw v-else class="h-4 w-4" />
                                </Button>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <Label for="name">Name *</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                placeholder="Enter BOM name"
                                :class="{ 'border-red-500': errors.name }"
                            />
                            <p v-if="errors.name" class="text-sm text-red-500">{{ errors.name }}</p>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label for="description">Description</Label>
                        <Textarea
                            id="description"
                            v-model="form.description"
                            placeholder="Enter description"
                            rows="3"
                        />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="space-y-2">
                            <Label for="product_id">Product *</Label>
                            <Select v-model="form.product_id" :class="{ 'border-red-500': errors.product_id }">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select product" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="product in products"
                                        :key="product.id"
                                        :value="product.id.toString()"
                                    >
                                        {{ product.name }} ({{ product.sku }})
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="errors.product_id" class="text-sm text-red-500">{{ errors.product_id }}</p>
                        </div>
                        <div class="space-y-2">
                            <Label for="quantity_per_unit">Quantity per Unit *</Label>
                            <Input
                                id="quantity_per_unit"
                                v-model="form.quantity_per_unit"
                                type="number"
                                step="0.0001"
                                min="0.0001"
                                placeholder="1.0000"
                                :class="{ 'border-red-500': errors.quantity_per_unit }"
                            />
                            <p v-if="errors.quantity_per_unit" class="text-sm text-red-500">{{ errors.quantity_per_unit }}</p>
                        </div>
                        <div class="space-y-2">
                            <Label for="unit">Unit *</Label>
                            <Input
                                id="unit"
                                v-model="form.unit"
                                placeholder="pcs, kg, m, etc."
                                :class="{ 'border-red-500': errors.unit }"
                            />
                            <p v-if="errors.unit" class="text-sm text-red-500">{{ errors.unit }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="space-y-2">
                            <Label for="status">Status *</Label>
                            <Select v-model="form.status">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="draft">Draft</SelectItem>
                                    <SelectItem value="active">Active</SelectItem>
                                    <SelectItem value="inactive">Inactive</SelectItem>
                                    <SelectItem value="archived">Archived</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-2">
                            <Label for="effective_date">Effective Date</Label>
                            <Input
                                id="effective_date"
                                v-model="form.effective_date"
                                type="date"
                            />
                        </div>
                        <div class="space-y-2">
                            <Label for="expiry_date">Expiry Date</Label>
                            <Input
                                id="expiry_date"
                                v-model="form.expiry_date"
                                type="date"
                            />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Components -->
            <Card>
                <CardHeader>
                    <div class="flex justify-between items-center">
                        <CardTitle>Components</CardTitle>
                        <Button @click="addComponent" type="button" variant="outline">
                            <Plus class="h-4 w-4 mr-2" />
                            Add Component
                        </Button>
                    </div>
                </CardHeader>
                <CardContent>
                    <div v-if="form.items.length === 0" class="text-center py-8">
                        <Package class="h-12 w-12 mx-auto text-muted-foreground mb-4" />
                        <h3 class="text-lg font-semibold mb-2">No components added</h3>
                        <p class="text-muted-foreground mb-4">
                            Add components to define the material structure.
                        </p>
                        <Button @click="addComponent" type="button">
                            <Plus class="h-4 w-4 mr-2" />
                            Add First Component
                        </Button>
                    </div>
                    <div v-else class="space-y-4">
                        <div
                            v-for="(item, index) in form.items"
                            :key="index"
                            class="border rounded-lg p-4"
                        >
                            <div class="flex justify-between items-start mb-4">
                                <h4 class="font-semibold">Component {{ index + 1 }}</h4>
                                <Button
                                    @click="removeComponent(index)"
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    class="text-destructive hover:text-destructive"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </Button>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                <div class="space-y-2">
                                    <Label>Product</Label>
                                    <Select v-model="item.product_id">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select product" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem
                                                v-for="product in products"
                                                :key="product.id"
                                                :value="product.id.toString()"
                                            >
                                                {{ product.name }} ({{ product.sku }})
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div class="space-y-2">
                                    <Label>Item Name</Label>
                                    <Input
                                        v-model="item.item_name"
                                        placeholder="Component name"
                                    />
                                </div>
                                <div class="space-y-2">
                                    <Label>Quantity Required</Label>
                                    <Input
                                        v-model="item.quantity_required"
                                        type="number"
                                        step="0.0001"
                                        min="0.0001"
                                        placeholder="1.0000"
                                    />
                                </div>
                                <div class="space-y-2">
                                    <Label>Unit</Label>
                                    <Input
                                        v-model="item.unit"
                                        placeholder="pcs, kg, m, etc."
                                    />
                                </div>
                                <div class="space-y-2">
                                    <Label>Unit Cost</Label>
                                    <Input
                                        v-model="item.unit_cost"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        placeholder="0.00"
                                    />
                                </div>
                                <div class="space-y-2">
                                    <Label>Sequence</Label>
                                    <Input
                                        v-model="item.sequence"
                                        type="number"
                                        min="0"
                                        placeholder="0"
                                    />
                                </div>
                                <div class="space-y-2">
                                    <Label>Critical Component</Label>
                                    <div class="flex items-center space-x-2">
                                        <Checkbox
                                            v-model="item.is_critical"
                                            id="critical"
                                        />
                                        <Label for="critical">Mark as critical</Label>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <Label>Total Cost</Label>
                                    <Input
                                        :value="calculateItemTotal(item)"
                                        disabled
                                        class="font-mono"
                                    />
                                </div>
                            </div>
                            <div class="mt-4">
                                <Label>Description</Label>
                                <Textarea
                                    v-model="item.description"
                                    placeholder="Component description"
                                    rows="2"
                                />
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Notes -->
            <Card>
                <CardHeader>
                    <CardTitle>Notes</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="space-y-2">
                        <Label for="notes">Additional Notes</Label>
                        <Textarea
                            id="notes"
                            v-model="form.notes"
                            placeholder="Enter any additional notes..."
                            rows="3"
                        />
                    </div>
                </CardContent>
            </Card>

            <!-- Actions -->
            <div class="flex justify-end gap-4">
                <Button @click="navigateToIndex" type="button" variant="outline">
                    Cancel
                </Button>
                <Button type="submit" :disabled="submitting">
                    <Loader2 v-if="submitting" class="h-4 w-4 mr-2 animate-spin" />
                    <Save v-else class="h-4 w-4 mr-2" />
                    Create BOM
                </Button>
            </div>
        </form>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    ArrowLeft,
    Plus,
    Trash2,
    Package,
    RefreshCw,
    Save,
    Loader2,
} from 'lucide-vue-next';
import type { Product, BomItem } from '@/types/erp';
import apiService from '@/services/api';

// Data
const products = ref<Product[]>([]);
const submitting = ref(false);
const generatingNumber = ref(false);
const errors = ref<Record<string, string>>({});

// Form
const form = ref({
    bom_number: '',
    name: '',
    description: '',
    product_id: '',
    quantity_per_unit: 1,
    unit: 'pcs',
    status: 'draft',
    effective_date: '',
    expiry_date: '',
    notes: '',
    items: [] as BomItem[],
});

// Methods
const fetchProducts = async () => {
    try {
        const response = await apiService.getProducts();
        products.value = response.data;
    } catch (error) {
        console.error('Error fetching products:', error);
    }
};

const generateNumber = async () => {
    generatingNumber.value = true;
    try {
        const response = await apiService.generateBillOfMaterialNumber();
        form.value.bom_number = response.bom_number;
    } catch (error) {
        console.error('Error generating number:', error);
    } finally {
        generatingNumber.value = false;
    }
};

const addComponent = () => {
    form.value.items.push({
        product_id: '',
        item_name: '',
        description: '',
        quantity_required: 1,
        unit: 'pcs',
        unit_cost: 0,
        sequence: form.value.items.length,
        is_critical: false,
        notes: '',
    });
};

const removeComponent = (index: number) => {
    form.value.items.splice(index, 1);
    // Reorder sequences
    form.value.items.forEach((item, idx) => {
        item.sequence = idx;
    });
};

const calculateItemTotal = (item: BomItem) => {
    const quantity = parseFloat(item.quantity_required?.toString() || '0');
    const cost = parseFloat(item.unit_cost?.toString() || '0');
    return (quantity * cost).toFixed(2);
};

const handleSubmit = async () => {
    submitting.value = true;
    errors.value = {};

    try {
        // Validate required fields
        if (!form.value.name) errors.value.name = 'Name is required';
        if (!form.value.product_id) errors.value.product_id = 'Product is required';
        if (!form.value.quantity_per_unit || form.value.quantity_per_unit <= 0) {
            errors.value.quantity_per_unit = 'Quantity must be greater than 0';
        }
        if (!form.value.unit) errors.value.unit = 'Unit is required';
        if (form.value.items.length === 0) {
            errors.value.items = 'At least one component is required';
        }

        // Validate items
        form.value.items.forEach((item, index) => {
            if (!item.product_id) {
                errors.value[`items.${index}.product_id`] = 'Product is required';
            }
            if (!item.item_name) {
                errors.value[`items.${index}.item_name`] = 'Item name is required';
            }
            if (!item.quantity_required || item.quantity_required <= 0) {
                errors.value[`items.${index}.quantity_required`] = 'Quantity must be greater than 0';
            }
            if (!item.unit) {
                errors.value[`items.${index}.unit`] = 'Unit is required';
            }
        });

        if (Object.keys(errors.value).length > 0) {
            submitting.value = false;
            return;
        }

        // Prepare data for submission
        const submitData = {
            ...form.value,
            product_id: parseInt(form.value.product_id),
            items: form.value.items.map(item => ({
                ...item,
                product_id: parseInt(item.product_id),
                quantity_required: parseFloat(item.quantity_required.toString()),
                unit_cost: parseFloat(item.unit_cost.toString()),
                sequence: parseInt(item.sequence.toString()),
            })),
        };

        await apiService.createBillOfMaterial(submitData);
        router.visit('/manufacturing/bill-of-materials');
    } catch (error: any) {
        if (error.response?.data?.errors) {
            errors.value = error.response.data.errors;
        } else {
            console.error('Error creating BOM:', error);
        }
    } finally {
        submitting.value = false;
    }
};

const navigateToIndex = () => {
    router.visit('/manufacturing/bill-of-materials');
};

// Lifecycle
onMounted(() => {
    fetchProducts();
    generateNumber();
});
</script>
