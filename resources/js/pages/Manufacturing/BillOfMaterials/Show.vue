<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Bill of Materials</h1>
                <p class="text-muted-foreground">
                    View BOM details and component structure
                </p>
            </div>
            <div class="flex gap-2">
                <Button @click="navigateToIndex" variant="outline">
                    <ArrowLeft class="h-4 w-4 mr-2" />
                    Back to BOMs
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

        <div v-else-if="!bom" class="text-center py-8">
            <Package class="h-12 w-12 mx-auto text-muted-foreground mb-4" />
            <h3 class="text-lg font-semibold mb-2">BOM not found</h3>
            <p class="text-muted-foreground mb-4">
                The bill of materials you're looking for doesn't exist.
            </p>
            <Button @click="navigateToIndex">
                <ArrowLeft class="h-4 w-4 mr-2" />
                Back to BOMs
            </Button>
        </div>

        <div v-else class="space-y-6">
            <!-- Basic Information -->
            <Card>
                <CardHeader>
                    <div class="flex justify-between items-start">
                        <div>
                            <CardTitle>{{ bom.name }}</CardTitle>
                            <p class="text-muted-foreground">{{ bom.bom_number }}</p>
                        </div>
                        <Badge :variant="getStatusVariant(bom.status)">
                            {{ bom.status }}
                        </Badge>
                    </div>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground">Product</Label>
                            <p class="text-sm">{{ bom.product?.name }} ({{ bom.product?.sku }})</p>
                        </div>
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground">Quantity per Unit</Label>
                            <p class="text-sm">{{ bom.quantity_per_unit }} {{ bom.unit }}</p>
                        </div>
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground">Total Cost</Label>
                                                            <p class="text-sm font-semibold">{{ formatCurrency(bom.total_cost) }}</p>
                        </div>
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground">Components</Label>
                            <p class="text-sm">{{ bom.items?.length || 0 }} items</p>
                        </div>
                    </div>

                    <div v-if="bom.description" class="space-y-2">
                        <Label class="text-sm font-medium text-muted-foreground">Description</Label>
                        <p class="text-sm">{{ bom.description }}</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground">Effective Date</Label>
                            <p class="text-sm">{{ bom.effective_date ? formatDate(bom.effective_date) : 'Not set' }}</p>
                        </div>
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground">Expiry Date</Label>
                            <p class="text-sm">{{ bom.expiry_date ? formatDate(bom.expiry_date) : 'Not set' }}</p>
                        </div>
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground">Created</Label>
                            <p class="text-sm">{{ formatDate(bom.created_at) }}</p>
                        </div>
                    </div>

                    <div v-if="bom.approved_at" class="space-y-2">
                        <Label class="text-sm font-medium text-muted-foreground">Approval Information</Label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm">Approved by: {{ bom.approved_by_user?.name }}</p>
                                <p class="text-sm">Approved on: {{ formatDate(bom.approved_at) }}</p>
                            </div>
                            <div v-if="bom.approval_notes">
                                <p class="text-sm">Notes: {{ bom.approval_notes }}</p>
                            </div>
                        </div>
                    </div>

                    <div v-if="bom.notes" class="space-y-2">
                        <Label class="text-sm font-medium text-muted-foreground">Notes</Label>
                        <p class="text-sm">{{ bom.notes }}</p>
                    </div>
                </CardContent>
            </Card>

            <!-- Components -->
            <Card>
                <CardHeader>
                    <CardTitle>Components</CardTitle>
                </CardHeader>
                <CardContent>
                    <div v-if="!bom.items || bom.items.length === 0" class="text-center py-8">
                        <Package class="h-12 w-12 mx-auto text-muted-foreground mb-4" />
                        <h3 class="text-lg font-semibold mb-2">No components</h3>
                        <p class="text-muted-foreground">
                            This BOM doesn't have any components defined.
                        </p>
                    </div>
                    <div v-else class="space-y-4">
                        <div
                            v-for="(item, index) in bom.items"
                            :key="item.id"
                            class="border rounded-lg p-4"
                        >
                            <div class="flex justify-between items-start mb-4">
                                <div class="flex items-center gap-2">
                                    <h4 class="font-semibold">{{ item.item_name }}</h4>
                                    <Badge v-if="item.is_critical" variant="destructive">
                                        Critical
                                    </Badge>
                                </div>
                                <span class="text-sm text-muted-foreground">#{{ item.sequence }}</span>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                <div>
                                    <Label class="text-sm font-medium text-muted-foreground">Product</Label>
                                    <p class="text-sm">{{ item.product?.name }} ({{ item.product?.sku }})</p>
                                </div>
                                <div>
                                    <Label class="text-sm font-medium text-muted-foreground">Quantity Required</Label>
                                    <p class="text-sm">{{ item.quantity_required }} {{ item.unit }}</p>
                                </div>
                                <div>
                                    <Label class="text-sm font-medium text-muted-foreground">Unit Cost</Label>
                                    <p class="text-sm">{{ formatCurrency(item.unit_cost) }}</p>
                                </div>
                                <div>
                                    <Label class="text-sm font-medium text-muted-foreground">Total Cost</Label>
                                    <p class="text-sm font-semibold">{{ formatCurrency(item.total_cost) }}</p>
                                </div>
                            </div>
                            <div v-if="item.description" class="mt-4">
                                <Label class="text-sm font-medium text-muted-foreground">Description</Label>
                                <p class="text-sm">{{ item.description }}</p>
                            </div>
                            <div v-if="item.notes" class="mt-2">
                                <Label class="text-sm font-medium text-muted-foreground">Notes</Label>
                                <p class="text-sm">{{ item.notes }}</p>
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
                            v-if="bom.status === 'draft'"
                            @click="approveBom"
                            :disabled="approving"
                        >
                            <Loader2 v-if="approving" class="h-4 w-4 mr-2 animate-spin" />
                            <Check v-else class="h-4 w-4 mr-2" />
                            Approve BOM
                        </Button>
                        <Button
                            v-if="bom.status === 'active'"
                            @click="deactivateBom"
                            variant="outline"
                            :disabled="updating"
                        >
                            <Loader2 v-if="updating" class="h-4 w-4 mr-2 animate-spin" />
                            <X v-else class="h-4 w-4 mr-2" />
                            Deactivate
                        </Button>
                        <Button
                            v-if="bom.status === 'inactive'"
                            @click="activateBom"
                            variant="outline"
                            :disabled="updating"
                        >
                            <Loader2 v-if="updating" class="h-4 w-4 mr-2 animate-spin" />
                            <Check v-else class="h-4 w-4 mr-2" />
                            Activate
                        </Button>
                        <Button
                            @click="deleteBom"
                            variant="destructive"
                            :disabled="deleting"
                        >
                            <Loader2 v-if="deleting" class="h-4 w-4 mr-2 animate-spin" />
                            <Trash2 v-else class="h-4 w-4 mr-2" />
                            Delete BOM
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
    Package,
    Loader2,
    Check,
    X,
    Trash2,
} from 'lucide-vue-next';
import type { BillOfMaterial } from '@/types/erp';
import apiService from '@/services/api';

// Props
interface Props {
    id: number;
}

const props = defineProps<Props>();

// Data
const bom = ref<BillOfMaterial | null>(null);
const loading = ref(false);
const approving = ref(false);
const updating = ref(false);
const deleting = ref(false);

// Methods
const fetchBom = async () => {
    loading.value = true;
    try {
        const response = await apiService.getBillOfMaterial(props.id);
        bom.value = response;
    } catch (error) {
        console.error('Error fetching BOM:', error);
    } finally {
        loading.value = false;
    }
};

const approveBom = async () => {
    if (!confirm('Are you sure you want to approve this BOM?')) return;
    
    approving.value = true;
    try {
        await apiService.approveBillOfMaterial(props.id, {
            approval_notes: 'Approved via UI'
        });
        await fetchBom();
    } catch (error) {
        console.error('Error approving BOM:', error);
    } finally {
        approving.value = false;
    }
};

const activateBom = async () => {
    updating.value = true;
    try {
        await apiService.updateBillOfMaterial(props.id, {
            ...bom.value,
            status: 'active'
        });
        await fetchBom();
    } catch (error) {
        console.error('Error activating BOM:', error);
    } finally {
        updating.value = false;
    }
};

const deactivateBom = async () => {
    updating.value = true;
    try {
        await apiService.updateBillOfMaterial(props.id, {
            ...bom.value,
            status: 'inactive'
        });
        await fetchBom();
    } catch (error) {
        console.error('Error deactivating BOM:', error);
    } finally {
        updating.value = false;
    }
};

const deleteBom = async () => {
    if (!confirm('Are you sure you want to delete this BOM? This action cannot be undone.')) return;
    
    deleting.value = true;
    try {
        await apiService.deleteBillOfMaterial(props.id);
        router.visit('/manufacturing/bill-of-materials');
    } catch (error) {
        console.error('Error deleting BOM:', error);
        deleting.value = false;
    }
};

const navigateToIndex = () => {
    router.visit('/manufacturing/bill-of-materials');
};

const navigateToEdit = () => {
    router.visit(`/manufacturing/bill-of-materials/${props.id}/edit`);
};

const getStatusVariant = (status: string) => {
    switch (status) {
        case 'active':
            return 'default';
        case 'draft':
            return 'secondary';
        case 'inactive':
            return 'destructive';
        case 'archived':
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
    fetchBom();
});
</script>
