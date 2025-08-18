<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Edit Production Plan</h1>
                <p class="text-muted-foreground">
                    Update production plan details and settings
                </p>
            </div>
            <Button @click="navigateToShow" variant="outline">
                <ArrowLeft class="h-4 w-4 mr-2" />
                Back to Plan
            </Button>
        </div>

        <div v-if="loading" class="flex justify-center py-8">
            <Loader2 class="h-8 w-8 animate-spin" />
        </div>

        <div v-else-if="!plan" class="text-center py-8">
            <Calendar class="h-12 w-12 mx-auto text-muted-foreground mb-4" />
            <h3 class="text-lg font-semibold mb-2">Production plan not found</h3>
            <p class="text-muted-foreground mb-4">
                The production plan you're looking for doesn't exist.
            </p>
            <Button @click="navigateToIndex">
                <ArrowLeft class="h-4 w-4 mr-2" />
                Back to Plans
            </Button>
        </div>

        <form v-else @submit.prevent="handleSubmit" class="space-y-6">
            <!-- Basic Information -->
            <Card>
                <CardHeader>
                    <CardTitle>Basic Information</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label for="plan_number">Plan Number</Label>
                            <Input id="plan_number" v-model="form.plan_number" disabled />
                        </div>
                        <div class="space-y-2">
                            <Label for="name">Name *</Label>
                            <Input id="name" v-model="form.name" placeholder="Enter plan name"
                                :class="{ 'border-red-500': errors.name }" />
                            <p v-if="errors.name" class="text-sm text-red-500">{{ errors.name }}</p>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label for="description">Description</Label>
                        <Textarea id="description" v-model="form.description" placeholder="Enter description"
                            rows="3" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="space-y-2">
                            <Label for="product_id">Product *</Label>
                            <Select v-model="form.product_id" :class="{ 'border-red-500': errors.product_id }">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select product" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="product in products" :key="product.id"
                                        :value="product.id.toString()">
                                        {{ product.name }} ({{ product.sku }})
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="errors.product_id" class="text-sm text-red-500">{{ errors.product_id }}</p>
                        </div>
                        <div class="space-y-2">
                            <Label for="bill_of_material_id">Bill of Material</Label>
                            <Select v-model="form.bill_of_material_id">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select BOM (optional)" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">No BOM</SelectItem>
                                    <SelectItem v-for="bom in billOfMaterials" :key="bom.id" :value="bom.id.toString()">
                                        {{ bom.name }} ({{ bom.bom_number }})
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-2">
                            <Label for="warehouse_id">Warehouse</Label>
                            <Select v-model="form.warehouse_id">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select warehouse" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="warehouse in warehouses" :key="warehouse.id"
                                        :value="warehouse.id.toString()">
                                        {{ warehouse.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="space-y-2">
                            <Label for="planned_quantity">Planned Quantity *</Label>
                            <Input id="planned_quantity" v-model="form.planned_quantity" type="number" step="0.0001"
                                min="0.0001" placeholder="1.0000"
                                :class="{ 'border-red-500': errors.planned_quantity }" />
                            <p v-if="errors.planned_quantity" class="text-sm text-red-500">{{ errors.planned_quantity }}
                            </p>
                        </div>
                        <div class="space-y-2">
                            <Label for="unit">Unit *</Label>
                            <Input id="unit" v-model="form.unit" placeholder="pcs, kg, m, etc."
                                :class="{ 'border-red-500': errors.unit }" />
                            <p v-if="errors.unit" class="text-sm text-red-500">{{ errors.unit }}</p>
                        </div>
                        <div class="space-y-2">
                            <Label for="priority">Priority *</Label>
                            <Select v-model="form.priority">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select priority" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="low">Low</SelectItem>
                                    <SelectItem value="medium">Medium</SelectItem>
                                    <SelectItem value="high">High</SelectItem>
                                    <SelectItem value="urgent">Urgent</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="space-y-2">
                            <Label for="start_date">Start Date *</Label>
                            <Input id="start_date" v-model="form.start_date" type="date"
                                :class="{ 'border-red-500': errors.start_date }" />
                            <p v-if="errors.start_date" class="text-sm text-red-500">{{ errors.start_date }}</p>
                        </div>
                        <div class="space-y-2">
                            <Label for="end_date">End Date *</Label>
                            <Input id="end_date" v-model="form.end_date" type="date"
                                :class="{ 'border-red-500': errors.end_date }" />
                            <p v-if="errors.end_date" class="text-sm text-red-500">{{ errors.end_date }}</p>
                        </div>
                        <div class="space-y-2">
                            <Label for="due_date">Due Date *</Label>
                            <Input id="due_date" v-model="form.due_date" type="date"
                                :class="{ 'border-red-500': errors.due_date }" />
                            <p v-if="errors.due_date" class="text-sm text-red-500">{{ errors.due_date }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label for="estimated_cost">Estimated Cost</Label>
                            <Input id="estimated_cost" v-model="form.estimated_cost" type="number" step="0.01" min="0"
                                placeholder="0.00" />
                        </div>
                        <div class="space-y-2">
                            <Label for="status">Status *</Label>
                            <Select v-model="form.status">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="draft">Draft</SelectItem>
                                    <SelectItem value="approved">Approved</SelectItem>
                                    <SelectItem value="in_progress">In Progress</SelectItem>
                                    <SelectItem value="completed">Completed</SelectItem>
                                    <SelectItem value="cancelled">Cancelled</SelectItem>
                                </SelectContent>
                            </Select>
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
                        <Textarea id="notes" v-model="form.notes" placeholder="Enter any additional notes..."
                            rows="3" />
                    </div>
                </CardContent>
            </Card>

            <!-- Actions -->
            <div class="flex justify-end gap-4">
                <Button @click="navigateToShow" type="button" variant="outline">
                    Cancel
                </Button>
                <Button type="submit" :disabled="submitting">
                    <Loader2 v-if="submitting" class="h-4 w-4 mr-2 animate-spin" />
                    <Save v-else class="h-4 w-4 mr-2" />
                    Update Plan
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
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    ArrowLeft,
    Save,
    Loader2,
    Calendar,
} from 'lucide-vue-next';
import type { Product, BillOfMaterial, Warehouse, ProductionPlan } from '@/types/erp';
import apiService from '@/services/api';

// Props
interface Props {
    id: number;
}

const props = defineProps<Props>();

// Data
const plan = ref<ProductionPlan | null>(null);
const products = ref<Product[]>([]);
const billOfMaterials = ref<BillOfMaterial[]>([]);
const warehouses = ref<Warehouse[]>([]);
const loading = ref(false);
const submitting = ref(false);
const errors = ref<Record<string, string>>({});

// Form
const form = ref({
    plan_number: '',
    name: '',
    description: '',
    product_id: '',
    bill_of_material_id: '',
    planned_quantity: 1,
    unit: 'pcs',
    start_date: '',
    end_date: '',
    due_date: '',
    priority: 'medium',
    status: 'draft',
    estimated_cost: 0,
    warehouse_id: '',
    notes: '',
});

// Methods
const fetchPlan = async () => {
    loading.value = true;
    try {
        const response = await apiService.getProductionPlan(props.id);
        plan.value = response;

        // Populate form with existing data
        form.value = {
            plan_number: response.plan_number,
            name: response.name,
            description: response.description || '',
            product_id: response.product_id.toString(),
            bill_of_material_id: response.bill_of_material_id?.toString() || '',
            planned_quantity: response.planned_quantity,
            unit: response.unit,
            start_date: response.start_date,
            end_date: response.end_date,
            due_date: response.due_date,
            priority: response.priority,
            status: response.status,
            estimated_cost: response.estimated_cost || 0,
            warehouse_id: response.warehouse_id?.toString() || '',
            notes: response.notes || '',
        };
    } catch (error) {
        console.error('Error fetching production plan:', error);
    } finally {
        loading.value = false;
    }
};

const fetchProducts = async () => {
    try {
        const response = await apiService.getProducts();
        products.value = response.data;
    } catch (error) {
        console.error('Error fetching products:', error);
    }
};

const fetchBillOfMaterials = async () => {
    try {
        const response = await apiService.getBillOfMaterials();
        billOfMaterials.value = response.data;
    } catch (error) {
        console.error('Error fetching BOMs:', error);
    }
};

const fetchWarehouses = async () => {
    try {
        const response = await apiService.getWarehouses();
        warehouses.value = response.data;
    } catch (error) {
        console.error('Error fetching warehouses:', error);
    }
};

const handleSubmit = async () => {
    submitting.value = true;
    errors.value = {};

    try {
        // Validate required fields
        if (!form.value.name) errors.value.name = 'Name is required';
        if (!form.value.product_id) errors.value.product_id = 'Product is required';
        if (!form.value.planned_quantity || form.value.planned_quantity <= 0) {
            errors.value.planned_quantity = 'Quantity must be greater than 0';
        }
        if (!form.value.unit) errors.value.unit = 'Unit is required';
        if (!form.value.start_date) errors.value.start_date = 'Start date is required';
        if (!form.value.end_date) errors.value.end_date = 'End date is required';
        if (!form.value.due_date) errors.value.due_date = 'Due date is required';

        // Validate dates
        if (form.value.start_date && form.value.end_date && form.value.start_date > form.value.end_date) {
            errors.value.end_date = 'End date must be after start date';
        }
        if (form.value.due_date && form.value.end_date && form.value.due_date < form.value.end_date) {
            errors.value.due_date = 'Due date must be after or equal to end date';
        }

        if (Object.keys(errors.value).length > 0) {
            submitting.value = false;
            return;
        }

        // Prepare data for submission
        const submitData = {
            ...form.value,
            product_id: parseInt(form.value.product_id),
            bill_of_material_id: form.value.bill_of_material_id ? parseInt(form.value.bill_of_material_id) : null,
            warehouse_id: form.value.warehouse_id ? parseInt(form.value.warehouse_id) : null,
            planned_quantity: parseFloat(form.value.planned_quantity.toString()),
            estimated_cost: parseFloat(form.value.estimated_cost.toString()),
        };

        await apiService.updateProductionPlan(props.id, submitData);
        router.visit(`/manufacturing/production-plans/${props.id}`);
    } catch (error: any) {
        if (error.response?.data?.errors) {
            errors.value = error.response.data.errors;
        } else {
            console.error('Error updating production plan:', error);
        }
    } finally {
        submitting.value = false;
    }
};

const navigateToIndex = () => {
    router.visit('/manufacturing/production-plans');
};

const navigateToShow = () => {
    router.visit(`/manufacturing/production-plans/${props.id}`);
};

// Lifecycle
onMounted(() => {
    fetchPlan();
    fetchProducts();
    fetchBillOfMaterials();
    fetchWarehouses();
});
</script>
