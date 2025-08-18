<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Production Plan</h1>
                <p class="text-muted-foreground">
                    View production plan details and work orders
                </p>
            </div>
            <div class="flex gap-2">
                <Button @click="navigateToIndex" variant="outline">
                    <ArrowLeft class="h-4 w-4 mr-2" />
                    Back to Plans
                </Button>
                <Button @click="navigateToEdit" variant="outline">
                    <Edit class="h-4 w-4 mr-2" />
                    Edit
                </Button>
            </div>
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

        <div v-else class="space-y-6">
            <!-- Basic Information -->
            <Card>
                <CardHeader>
                    <div class="flex justify-between items-start">
                        <div>
                            <CardTitle>{{ plan.name }}</CardTitle>
                            <p class="text-muted-foreground">{{ plan.plan_number }}</p>
                        </div>
                        <div class="flex gap-2">
                            <Badge :variant="getStatusVariant(plan.status)">
                                {{ plan.status }}
                            </Badge>
                            <Badge :variant="getPriorityVariant(plan.priority)">
                                {{ plan.priority }}
                            </Badge>
                        </div>
                    </div>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground">Product</Label>
                            <p class="text-sm">{{ plan.product?.name }} ({{ plan.product?.sku }})</p>
                        </div>
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground">Bill of Material</Label>
                            <p class="text-sm">{{ plan.bill_of_material?.name || 'Not specified' }}</p>
                        </div>
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground">Planned Quantity</Label>
                            <p class="text-sm">{{ plan.planned_quantity }} {{ plan.unit }}</p>
                        </div>
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground">Warehouse</Label>
                            <p class="text-sm">{{ plan.warehouse?.name || 'Not specified' }}</p>
                        </div>
                    </div>

                    <div v-if="plan.description" class="space-y-2">
                        <Label class="text-sm font-medium text-muted-foreground">Description</Label>
                        <p class="text-sm">{{ plan.description }}</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground">Start Date</Label>
                            <p class="text-sm">{{ formatDate(plan.start_date) }}</p>
                        </div>
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground">End Date</Label>
                            <p class="text-sm">{{ formatDate(plan.end_date) }}</p>
                        </div>
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground">Due Date</Label>
                            <p class="text-sm">{{ formatDate(plan.due_date) }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground">Estimated Cost</Label>
                            <p class="text-sm font-semibold">${{ formatCurrency(plan.estimated_cost) }}</p>
                        </div>
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground">Actual Cost</Label>
                            <p class="text-sm font-semibold">${{ formatCurrency(plan.actual_cost) }}</p>
                        </div>
                    </div>

                    <div v-if="plan.approved_at" class="space-y-2">
                        <Label class="text-sm font-medium text-muted-foreground">Approval Information</Label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm">Approved by: {{ plan.approved_by_user?.name }}</p>
                                <p class="text-sm">Approved on: {{ formatDate(plan.approved_at) }}</p>
                            </div>
                            <div v-if="plan.approval_notes">
                                <p class="text-sm">Notes: {{ plan.approval_notes }}</p>
                            </div>
                        </div>
                    </div>

                    <div v-if="plan.notes" class="space-y-2">
                        <Label class="text-sm font-medium text-muted-foreground">Notes</Label>
                        <p class="text-sm">{{ plan.notes }}</p>
                    </div>
                </CardContent>
            </Card>

            <!-- Work Orders -->
            <Card>
                <CardHeader>
                    <div class="flex justify-between items-center">
                        <CardTitle>Work Orders</CardTitle>
                        <Button @click="navigateToCreateWorkOrder" variant="outline">
                            <Plus class="h-4 w-4 mr-2" />
                            Create Work Order
                        </Button>
                    </div>
                </CardHeader>
                <CardContent>
                    <div v-if="!plan.work_orders || plan.work_orders.length === 0" class="text-center py-8">
                        <Wrench class="h-12 w-12 mx-auto text-muted-foreground mb-4" />
                        <h3 class="text-lg font-semibold mb-2">No work orders</h3>
                        <p class="text-muted-foreground mb-4">
                            This production plan doesn't have any work orders yet.
                        </p>
                        <Button @click="navigateToCreateWorkOrder">
                            <Plus class="h-4 w-4 mr-2" />
                            Create First Work Order
                        </Button>
                    </div>
                    <div v-else class="space-y-4">
                        <div
                            v-for="workOrder in plan.work_orders"
                            :key="workOrder.id"
                            class="border rounded-lg p-4"
                        >
                            <div class="flex justify-between items-start mb-4">
                                <div class="flex items-center gap-2">
                                    <h4 class="font-semibold">{{ workOrder.name }}</h4>
                                    <Badge :variant="getWorkOrderStatusVariant(workOrder.status)">
                                        {{ workOrder.status }}
                                    </Badge>
                                    <Badge :variant="getPriorityVariant(workOrder.priority)">
                                        {{ workOrder.priority }}
                                    </Badge>
                                </div>
                                <span class="text-sm text-muted-foreground">{{ workOrder.work_order_number }}</span>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                <div>
                                    <Label class="text-sm font-medium text-muted-foreground">Product</Label>
                                    <p class="text-sm">{{ workOrder.product?.name }}</p>
                                </div>
                                <div>
                                    <Label class="text-sm font-medium text-muted-foreground">Quantity</Label>
                                    <p class="text-sm">{{ workOrder.planned_quantity }} {{ workOrder.unit }}</p>
                                </div>
                                <div>
                                    <Label class="text-sm font-medium text-muted-foreground">Progress</Label>
                                    <p class="text-sm">{{ workOrder.completed_quantity || 0 }} / {{ workOrder.planned_quantity }}</p>
                                </div>
                                <div>
                                    <Label class="text-sm font-medium text-muted-foreground">Due Date</Label>
                                    <p class="text-sm">{{ formatDate(workOrder.due_date) }}</p>
                                </div>
                            </div>
                            <div class="mt-4 flex gap-2">
                                <Button
                                    @click="navigateToWorkOrder(workOrder.id)"
                                    variant="outline"
                                    size="sm"
                                >
                                    <Eye class="h-4 w-4 mr-2" />
                                    View
                                </Button>
                                <Button
                                    @click="navigateToEditWorkOrder(workOrder.id)"
                                    variant="outline"
                                    size="sm"
                                >
                                    <Edit class="h-4 w-4 mr-2" />
                                    Edit
                                </Button>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Actions -->
            <Card>
                <CardHeader>
                    <CardTitle>Actions</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="flex gap-4">
                        <Button
                            v-if="plan.status === 'draft'"
                            @click="approvePlan"
                            :disabled="approving"
                        >
                            <Loader2 v-if="approving" class="h-4 w-4 mr-2 animate-spin" />
                            <Check v-else class="h-4 w-4 mr-2" />
                            Approve Plan
                        </Button>
                        <Button
                            v-if="plan.status === 'approved'"
                            @click="startPlan"
                            variant="outline"
                            :disabled="updating"
                        >
                            <Loader2 v-if="updating" class="h-4 w-4 mr-2 animate-spin" />
                            <Play v-else class="h-4 w-4 mr-2" />
                            Start Production
                        </Button>
                        <Button
                            v-if="plan.status === 'in_progress'"
                            @click="completePlan"
                            variant="outline"
                            :disabled="updating"
                        >
                            <Loader2 v-if="updating" class="h-4 w-4 mr-2 animate-spin" />
                            <CheckCircle v-else class="h-4 w-4 mr-2" />
                            Complete Plan
                        </Button>
                        <Button
                            v-if="plan.status === 'draft'"
                            @click="cancelPlan"
                            variant="outline"
                            :disabled="updating"
                        >
                            <Loader2 v-if="updating" class="h-4 w-4 mr-2 animate-spin" />
                            <X v-else class="h-4 w-4 mr-2" />
                            Cancel Plan
                        </Button>
                        <Button
                            @click="deletePlan"
                            variant="destructive"
                            :disabled="deleting"
                        >
                            <Loader2 v-if="deleting" class="h-4 w-4 mr-2 animate-spin" />
                            <Trash2 v-else class="h-4 w-4 mr-2" />
                            Delete Plan
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import {
    ArrowLeft,
    Edit,
    Calendar,
    Loader2,
    Check,
    X,
    Trash2,
    Plus,
    Wrench,
    Play,
    CheckCircle,
} from 'lucide-vue-next';
import type { ProductionPlan } from '@/types/erp';
import apiService from '@/services/api';

// Props
interface Props {
    id: number;
}

const props = defineProps<Props>();

// Data
const plan = ref<ProductionPlan | null>(null);
const loading = ref(false);
const approving = ref(false);
const updating = ref(false);
const deleting = ref(false);

// Methods
const fetchPlan = async () => {
    loading.value = true;
    try {
        const response = await apiService.getProductionPlan(props.id);
        plan.value = response;
    } catch (error) {
        console.error('Error fetching production plan:', error);
    } finally {
        loading.value = false;
    }
};

const approvePlan = async () => {
    if (!confirm('Are you sure you want to approve this production plan?')) return;
    
    approving.value = true;
    try {
        await apiService.approveProductionPlan(props.id, {
            approval_notes: 'Approved via UI'
        });
        await fetchPlan();
    } catch (error) {
        console.error('Error approving production plan:', error);
    } finally {
        approving.value = false;
    }
};

const startPlan = async () => {
    updating.value = true;
    try {
        await apiService.updateProductionPlan(props.id, {
            ...plan.value,
            status: 'in_progress'
        });
        await fetchPlan();
    } catch (error) {
        console.error('Error starting production plan:', error);
    } finally {
        updating.value = false;
    }
};

const completePlan = async () => {
    updating.value = true;
    try {
        await apiService.updateProductionPlan(props.id, {
            ...plan.value,
            status: 'completed'
        });
        await fetchPlan();
    } catch (error) {
        console.error('Error completing production plan:', error);
    } finally {
        updating.value = false;
    }
};

const cancelPlan = async () => {
    if (!confirm('Are you sure you want to cancel this production plan?')) return;
    
    updating.value = true;
    try {
        await apiService.updateProductionPlan(props.id, {
            ...plan.value,
            status: 'cancelled'
        });
        await fetchPlan();
    } catch (error) {
        console.error('Error cancelling production plan:', error);
    } finally {
        updating.value = false;
    }
};

const deletePlan = async () => {
    if (!confirm('Are you sure you want to delete this production plan? This action cannot be undone.')) return;
    
    deleting.value = true;
    try {
        await apiService.deleteProductionPlan(props.id);
        router.visit('/manufacturing/production-plans');
    } catch (error) {
        console.error('Error deleting production plan:', error);
        deleting.value = false;
    }
};

const navigateToIndex = () => {
    router.visit('/manufacturing/production-plans');
};

const navigateToEdit = () => {
    router.visit(`/manufacturing/production-plans/${props.id}/edit`);
};

const navigateToCreateWorkOrder = () => {
    router.visit(`/manufacturing/work-orders/create?production_plan_id=${props.id}`);
};

const navigateToWorkOrder = (id: number) => {
    router.visit(`/manufacturing/work-orders/${id}`);
};

const navigateToEditWorkOrder = (id: number) => {
    router.visit(`/manufacturing/work-orders/${id}/edit`);
};

const getStatusVariant = (status: string) => {
    switch (status) {
        case 'approved':
            return 'default';
        case 'draft':
            return 'secondary';
        case 'in_progress':
            return 'default';
        case 'completed':
            return 'default';
        case 'cancelled':
            return 'destructive';
        default:
            return 'secondary';
    }
};

const getPriorityVariant = (priority: string) => {
    switch (priority) {
        case 'urgent':
            return 'destructive';
        case 'high':
            return 'default';
        case 'medium':
            return 'secondary';
        case 'low':
            return 'outline';
        default:
            return 'secondary';
    }
};

const getWorkOrderStatusVariant = (status: string) => {
    switch (status) {
        case 'approved':
            return 'default';
        case 'draft':
            return 'secondary';
        case 'in_progress':
            return 'default';
        case 'completed':
            return 'default';
        case 'paused':
            return 'outline';
        case 'cancelled':
            return 'destructive';
        default:
            return 'secondary';
    }
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(amount);
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString();
};

// Lifecycle
onMounted(() => {
    fetchPlan();
});
</script>
