<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Work Order</h1>
                <p class="text-muted-foreground">
                    View work order details and production tracking
                </p>
            </div>
            <div class="flex gap-2">
                <Button @click="navigateToIndex" variant="outline">
                    <ArrowLeft class="h-4 w-4 mr-2" />
                    Back to Work Orders
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

        <div v-else-if="!workOrder" class="text-center py-8">
            <Wrench class="h-12 w-12 mx-auto text-muted-foreground mb-4" />
            <h3 class="text-lg font-semibold mb-2">Work order not found</h3>
            <p class="text-muted-foreground mb-4">
                The work order you're looking for doesn't exist.
            </p>
            <Button @click="navigateToIndex">
                <ArrowLeft class="h-4 w-4 mr-2" />
                Back to Work Orders
            </Button>
        </div>

        <div v-else class="space-y-6">
            <!-- Basic Information -->
            <Card>
                <CardHeader>
                    <div class="flex justify-between items-start">
                        <div>
                            <CardTitle>{{ workOrder.name }}</CardTitle>
                            <p class="text-muted-foreground">{{ workOrder.work_order_number }}</p>
                        </div>
                        <div class="flex gap-2">
                            <Badge :variant="getStatusVariant(workOrder.status)">
                                {{ workOrder.status }}
                            </Badge>
                            <Badge :variant="getPriorityVariant(workOrder.priority)">
                                {{ workOrder.priority }}
                            </Badge>
                        </div>
                    </div>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground">Product</Label>
                            <p class="text-sm">{{ workOrder.product?.name }} ({{ workOrder.product?.sku }})</p>
                        </div>
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground">Production Plan</Label>
                            <p class="text-sm">{{ workOrder.production_plan?.name || 'Not specified' }}</p>
                        </div>
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground">Bill of Material</Label>
                            <p class="text-sm">{{ workOrder.bill_of_material?.name || 'Not specified' }}</p>
                        </div>
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground">Work Center</Label>
                            <p class="text-sm">{{ workOrder.work_center?.name || 'Not assigned' }}</p>
                        </div>
                    </div>

                    <div v-if="workOrder.description" class="space-y-2">
                        <Label class="text-sm font-medium text-muted-foreground">Description</Label>
                        <p class="text-sm">{{ workOrder.description }}</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground">Planned Quantity</Label>
                            <p class="text-sm">{{ workOrder.planned_quantity }} {{ workOrder.unit }}</p>
                        </div>
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground">Completed Quantity</Label>
                            <p class="text-sm">{{ workOrder.completed_quantity || 0 }} {{ workOrder.unit }}</p>
                        </div>
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground">Progress</Label>
                            <p class="text-sm">{{ getProgressPercentage() }}%</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground">Start Date</Label>
                            <p class="text-sm">{{ formatDate(workOrder.start_date) }}</p>
                        </div>
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground">Due Date</Label>
                            <p class="text-sm">{{ formatDate(workOrder.due_date) }}</p>
                        </div>
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground">Assigned To</Label>
                            <p class="text-sm">{{ workOrder.assigned_to_user?.name || 'Not assigned' }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground">Estimated Hours</Label>
                            <p class="text-sm">{{ workOrder.estimated_hours || 0 }}h</p>
                        </div>
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground">Actual Hours</Label>
                            <p class="text-sm">{{ workOrder.actual_hours || 0 }}h</p>
                        </div>
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground">Estimated Cost</Label>
                            <p class="text-sm font-semibold">${{ formatCurrency(workOrder.estimated_cost) }}</p>
                        </div>
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground">Actual Cost</Label>
                            <p class="text-sm font-semibold">${{ formatCurrency(workOrder.actual_cost) }}</p>
                        </div>
                    </div>

                    <div v-if="workOrder.approved_at" class="space-y-2">
                        <Label class="text-sm font-medium text-muted-foreground">Approval Information</Label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm">Approved by: {{ workOrder.approved_by_user?.name }}</p>
                                <p class="text-sm">Approved on: {{ formatDate(workOrder.approved_at) }}</p>
                            </div>
                            <div v-if="workOrder.approval_notes">
                                <p class="text-sm">Notes: {{ workOrder.approval_notes }}</p>
                            </div>
                        </div>
                    </div>

                    <div v-if="workOrder.notes" class="space-y-2">
                        <Label class="text-sm font-medium text-muted-foreground">Notes</Label>
                        <p class="text-sm">{{ workOrder.notes }}</p>
                    </div>
                </CardContent>
            </Card>

            <!-- Production Tracking -->
            <Card>
                <CardHeader>
                    <div class="flex justify-between items-center">
                        <CardTitle>Production Tracking</CardTitle>
                        <Button @click="navigateToCreateTracking" variant="outline">
                            <Plus class="h-4 w-4 mr-2" />
                            Add Tracking
                        </Button>
                    </div>
                </CardHeader>
                <CardContent>
                    <div v-if="!workOrder.production_tracking || workOrder.production_tracking.length === 0" class="text-center py-8">
                        <Activity class="h-12 w-12 mx-auto text-muted-foreground mb-4" />
                        <h3 class="text-lg font-semibold mb-2">No production tracking</h3>
                        <p class="text-muted-foreground mb-4">
                            This work order doesn't have any production tracking records yet.
                        </p>
                        <Button @click="navigateToCreateTracking">
                            <Plus class="h-4 w-4 mr-2" />
                            Add First Tracking
                        </Button>
                    </div>
                    <div v-else class="space-y-4">
                        <div
                            v-for="tracking in workOrder.production_tracking"
                            :key="tracking.id"
                            class="border rounded-lg p-4"
                        >
                            <div class="flex justify-between items-start mb-4">
                                <div class="flex items-center gap-2">
                                    <h4 class="font-semibold">{{ tracking.operation_type }}</h4>
                                    <Badge :variant="getTrackingStatusVariant(tracking.status)">
                                        {{ tracking.status }}
                                    </Badge>
                                </div>
                                <span class="text-sm text-muted-foreground">{{ formatDate(tracking.start_time) }}</span>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                <div>
                                    <Label class="text-sm font-medium text-muted-foreground">Work Center</Label>
                                    <p class="text-sm">{{ tracking.work_center?.name }}</p>
                                </div>
                                <div>
                                    <Label class="text-sm font-medium text-muted-foreground">Operator</Label>
                                    <p class="text-sm">{{ tracking.operator?.name }}</p>
                                </div>
                                <div>
                                    <Label class="text-sm font-medium text-muted-foreground">Quantity Produced</Label>
                                    <p class="text-sm">{{ tracking.quantity_produced || 0 }}</p>
                                </div>
                                <div>
                                    <Label class="text-sm font-medium text-muted-foreground">Duration</Label>
                                    <p class="text-sm">{{ tracking.duration_minutes || 0 }} minutes</p>
                                </div>
                            </div>
                            <div v-if="tracking.quantity_rejected && tracking.quantity_rejected > 0" class="mt-4 p-3 bg-red-50 border border-red-200 rounded">
                                <div class="flex items-center gap-2">
                                    <AlertTriangle class="h-4 w-4 text-red-500" />
                                    <span class="text-sm font-medium text-red-700">Rejected: {{ tracking.quantity_rejected }}</span>
                                </div>
                                <p v-if="tracking.rejection_reason" class="text-sm text-red-600 mt-1">
                                    Reason: {{ tracking.rejection_reason }}
                                </p>
                            </div>
                            <div v-if="tracking.notes" class="mt-4">
                                <Label class="text-sm font-medium text-muted-foreground">Notes</Label>
                                <p class="text-sm">{{ tracking.notes }}</p>
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
                            v-if="workOrder.status === 'draft'"
                            @click="approveWorkOrder"
                            :disabled="approving"
                        >
                            <Loader2 v-if="approving" class="h-4 w-4 mr-2 animate-spin" />
                            <Check v-else class="h-4 w-4 mr-2" />
                            Approve Work Order
                        </Button>
                        <Button
                            v-if="workOrder.status === 'approved'"
                            @click="startWorkOrder"
                            variant="outline"
                            :disabled="updating"
                        >
                            <Loader2 v-if="updating" class="h-4 w-4 mr-2 animate-spin" />
                            <Play v-else class="h-4 w-4 mr-2" />
                            Start Production
                        </Button>
                        <Button
                            v-if="workOrder.status === 'in_progress'"
                            @click="pauseWorkOrder"
                            variant="outline"
                            :disabled="updating"
                        >
                            <Loader2 v-if="updating" class="h-4 w-4 mr-2 animate-spin" />
                            <Pause v-else class="h-4 w-4 mr-2" />
                            Pause Production
                        </Button>
                        <Button
                            v-if="workOrder.status === 'paused'"
                            @click="resumeWorkOrder"
                            variant="outline"
                            :disabled="updating"
                        >
                            <Loader2 v-if="updating" class="h-4 w-4 mr-2 animate-spin" />
                            <Play v-else class="h-4 w-4 mr-2" />
                            Resume Production
                        </Button>
                        <Button
                            v-if="workOrder.status === 'in_progress' || workOrder.status === 'paused'"
                            @click="completeWorkOrder"
                            variant="outline"
                            :disabled="updating"
                        >
                            <Loader2 v-if="updating" class="h-4 w-4 mr-2 animate-spin" />
                            <CheckCircle v-else class="h-4 w-4 mr-2" />
                            Complete Work Order
                        </Button>
                        <Button
                            v-if="workOrder.status === 'draft'"
                            @click="cancelWorkOrder"
                            variant="outline"
                            :disabled="updating"
                        >
                            <Loader2 v-if="updating" class="h-4 w-4 mr-2 animate-spin" />
                            <X v-else class="h-4 w-4 mr-2" />
                            Cancel Work Order
                        </Button>
                        <Button
                            @click="deleteWorkOrder"
                            variant="destructive"
                            :disabled="deleting"
                        >
                            <Loader2 v-if="deleting" class="h-4 w-4 mr-2 animate-spin" />
                            <Trash2 v-else class="h-4 w-4 mr-2" />
                            Delete Work Order
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
    Wrench,
    Loader2,
    Check,
    X,
    Trash2,
    Plus,
    Activity,
    Play,
    Pause,
    CheckCircle,
    AlertTriangle,
} from 'lucide-vue-next';
import type { WorkOrder } from '@/types/erp';
import apiService from '@/services/api';

// Props
interface Props {
    id: number;
}

const props = defineProps<Props>();

// Data
const workOrder = ref<WorkOrder | null>(null);
const loading = ref(false);
const approving = ref(false);
const updating = ref(false);
const deleting = ref(false);

// Methods
const fetchWorkOrder = async () => {
    loading.value = true;
    try {
        const response = await apiService.getWorkOrder(props.id);
        workOrder.value = response;
    } catch (error) {
        console.error('Error fetching work order:', error);
    } finally {
        loading.value = false;
    }
};

const approveWorkOrder = async () => {
    if (!confirm('Are you sure you want to approve this work order?')) return;
    
    approving.value = true;
    try {
        await apiService.approveWorkOrder(props.id, {
            approval_notes: 'Approved via UI'
        });
        await fetchWorkOrder();
    } catch (error) {
        console.error('Error approving work order:', error);
    } finally {
        approving.value = false;
    }
};

const startWorkOrder = async () => {
    updating.value = true;
    try {
        await apiService.startWorkOrder(props.id);
        await fetchWorkOrder();
    } catch (error) {
        console.error('Error starting work order:', error);
    } finally {
        updating.value = false;
    }
};

const pauseWorkOrder = async () => {
    updating.value = true;
    try {
        await apiService.updateWorkOrder(props.id, {
            ...workOrder.value,
            status: 'paused'
        });
        await fetchWorkOrder();
    } catch (error) {
        console.error('Error pausing work order:', error);
    } finally {
        updating.value = false;
    }
};

const resumeWorkOrder = async () => {
    updating.value = true;
    try {
        await apiService.updateWorkOrder(props.id, {
            ...workOrder.value,
            status: 'in_progress'
        });
        await fetchWorkOrder();
    } catch (error) {
        console.error('Error resuming work order:', error);
    } finally {
        updating.value = false;
    }
};

const completeWorkOrder = async () => {
    updating.value = true;
    try {
        await apiService.completeWorkOrder(props.id);
        await fetchWorkOrder();
    } catch (error) {
        console.error('Error completing work order:', error);
    } finally {
        updating.value = false;
    }
};

const cancelWorkOrder = async () => {
    if (!confirm('Are you sure you want to cancel this work order?')) return;
    
    updating.value = true;
    try {
        await apiService.updateWorkOrder(props.id, {
            ...workOrder.value,
            status: 'cancelled'
        });
        await fetchWorkOrder();
    } catch (error) {
        console.error('Error cancelling work order:', error);
    } finally {
        updating.value = false;
    }
};

const deleteWorkOrder = async () => {
    if (!confirm('Are you sure you want to delete this work order? This action cannot be undone.')) return;
    
    deleting.value = true;
    try {
        await apiService.deleteWorkOrder(props.id);
        router.visit('/manufacturing/work-orders');
    } catch (error) {
        console.error('Error deleting work order:', error);
        deleting.value = false;
    }
};

const navigateToIndex = () => {
    router.visit('/manufacturing/work-orders');
};

const navigateToEdit = () => {
    router.visit(`/manufacturing/work-orders/${props.id}/edit`);
};

const navigateToCreateTracking = () => {
    router.visit(`/manufacturing/production-tracking/create?work_order_id=${props.id}`);
};

const getProgressPercentage = () => {
    if (!workOrder.value || workOrder.value.planned_quantity <= 0) return 0;
    const completed = workOrder.value.completed_quantity || 0;
    return Math.round((completed / workOrder.value.planned_quantity) * 100);
};

const getStatusVariant = (status: string) => {
    switch (status) {
        case 'approved':
            return 'default';
        case 'draft':
            return 'secondary';
        case 'in_progress':
            return 'default';
        case 'paused':
            return 'outline';
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

const getTrackingStatusVariant = (status: string) => {
    switch (status) {
        case 'in_progress':
            return 'default';
        case 'completed':
            return 'default';
        case 'paused':
            return 'outline';
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
    fetchWorkOrder();
});
</script>
