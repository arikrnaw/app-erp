<template>
    <Dialog :open="open" @update:open="$emit('close')">
        <DialogContent class="!max-w-4xl max-h-[90vh] overflow-y-auto">
            <DialogHeader>
                <DialogTitle>Edit Bill</DialogTitle>
                <DialogDescription>
                    Edit supplier bill details and items
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="handleSubmit" class="space-y-6">
                <!-- Bill Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <Label for="supplier_id">Supplier</Label>
                        <Select v-model="form.supplier_id" required>
                            <SelectTrigger>
                                <SelectValue placeholder="Select supplier" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">
                                    {{ supplier.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="errors.supplier_id" class="text-sm text-destructive">{{ errors.supplier_id }}</p>
                    </div>

                    <div class="space-y-2">
                        <Label for="bill_number">Bill Number</Label>
                        <Input v-model="form.bill_number" disabled />
                    </div>

                    <div class="space-y-2">
                        <Label for="bill_date">Bill Date</Label>
                        <Input v-model="form.bill_date" type="date" required />
                        <p v-if="errors.bill_date" class="text-sm text-destructive">{{ errors.bill_date }}</p>
                    </div>

                    <div class="space-y-2">
                        <Label for="due_date">Due Date</Label>
                        <Input v-model="form.due_date" type="date" required />
                        <p v-if="errors.due_date" class="text-sm text-destructive">{{ errors.due_date }}</p>
                    </div>
                </div>

                <div class="space-y-2">
                    <Label for="notes">Notes</Label>
                    <Textarea v-model="form.notes" placeholder="Enter bill notes..." />
                    <p v-if="errors.notes" class="text-sm text-destructive">{{ errors.notes }}</p>
                </div>

                <!-- Bill Items -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <Label class="text-base font-semibold">Bill Items</Label>
                        <Button type="button" variant="outline" size="sm" @click="addItem">
                            <Plus class="w-4 h-4 mr-2" />
                            Add Item
                        </Button>
                    </div>

                    <div class="space-y-3">
                        <div v-for="(item, index) in form.lines" :key="index" class="p-4 border rounded-lg bg-muted/30">
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                                <div class="md:col-span-4">
                                    <Label :for="`description-${index}`" class="text-sm font-medium">Description</Label>
                                    <Input :id="`description-${index}`" v-model="item.description"
                                        placeholder="Item description" required class="mt-1" />
                                </div>
                                <div class="md:col-span-2">
                                    <Label :for="`quantity-${index}`" class="text-sm font-medium">Quantity</Label>
                                    <Input :id="`quantity-${index}`" v-model.number="item.quantity" type="number"
                                        min="0.01" step="0.01" required class="mt-1" />
                                </div>
                                <div class="md:col-span-2">
                                    <Label :for="`unit_price-${index}`" class="text-sm font-medium">Unit Price</Label>
                                    <Input :id="`unit_price-${index}`" v-model.number="item.unit_price" type="number"
                                        min="0.01" step="0.01" required class="mt-1" />
                                </div>
                                <div class="md:col-span-2">
                                    <Label :for="`tax_percentage-${index}`" class="text-sm font-medium">Tax Rate
                                        (%)</Label>
                                    <Input :id="`tax_percentage-${index}`" v-model.number="item.tax_percentage"
                                        type="number" min="0" max="100" step="0.01" class="mt-1" />
                                </div>
                                <div class="md:col-span-2 flex items-end space-x-2">
                                    <div class="flex-1">
                                        <Label class="text-sm font-medium">Total</Label>
                                        <div
                                            class="mt-1 p-2 bg-background border rounded text-sm font-medium text-center">
                                            {{ formatCurrency(calculateItemTotal(item)) }}
                                        </div>
                                    </div>
                                    <Button type="button" variant="ghost" size="sm" @click="removeItem(index)"
                                        :disabled="form.lines.length === 1" class="mb-0.5">
                                        <Trash2 class="w-4 h-4" />
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <p v-if="errors.lines" class="text-sm text-destructive">{{ errors.lines }}</p>
                </div>

                <!-- Totals Summary -->
                <div class="border-t pt-4">
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-sm text-muted-foreground">Subtotal:</span>
                            <span class="font-medium">{{ formatCurrency(calculateSubtotal) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-muted-foreground">Tax Amount:</span>
                            <span class="font-medium">{{ formatCurrency(calculateTaxAmount) }}</span>
                        </div>
                        <div class="flex justify-between border-t pt-2">
                            <span class="text-base font-semibold">Total Amount:</span>
                            <span class="text-lg font-bold">{{ formatCurrency(calculateTotalAmount) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <DialogFooter>
                    <Button type="button" variant="outline" @click="$emit('close')">
                        Cancel
                    </Button>
                    <Button type="submit" :disabled="loading">
                        <Loader2 v-if="loading" class="w-4 h-4 mr-2 animate-spin" />
                        {{ loading ? 'Updating...' : 'Update Bill' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Plus, Trash2, Loader2 } from 'lucide-vue-next'
import { apiService } from '@/services/api'
import type { Bill, Supplier } from '@/types/erp'

interface BillItem {
    description: string
    quantity: number
    unit_price: number
    tax_percentage: number
}

interface BillForm {
    supplier_id: number | null
    bill_number: string
    bill_date: string
    due_date: string
    notes: string
    lines: BillItem[]
}

interface Props {
    open: boolean
    bill: Bill | null
    suppliers: Supplier[]
}

interface Emits {
    (e: 'close'): void
    (e: 'updated', bill: any): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const loading = ref(false)
const errors = ref<Record<string, string>>({})

const form = ref<BillForm>({
    supplier_id: null,
    bill_number: '',
    bill_date: '',
    due_date: '',
    notes: '',
    lines: []
})

// Computed properties for calculations
const calculateItemTotal = (item: BillItem) => {
    const lineTotal = item.quantity * item.unit_price
    const taxAmount = lineTotal * (item.tax_percentage / 100)
    return lineTotal + taxAmount
}

const calculateSubtotal = computed(() => {
    return form.value.lines.reduce((sum, item) => {
        return sum + (item.quantity * item.unit_price)
    }, 0)
})

const calculateTaxAmount = computed(() => {
    return form.value.lines.reduce((sum, item) => {
        const lineTotal = item.quantity * item.unit_price
        return sum + (lineTotal * (item.tax_percentage / 100))
    }, 0)
})

const calculateTotalAmount = computed(() => {
    return calculateSubtotal.value + calculateTaxAmount.value
})

// Methods
const addItem = () => {
    form.value.lines.push({
        description: '',
        quantity: 1,
        unit_price: 0,
        tax_percentage: 0
    })
}

const removeItem = (index: number) => {
    if (form.value.lines.length > 1) {
        form.value.lines.splice(index, 1)
    }
}

const handleSubmit = async () => {
    if (!props.bill) return

    loading.value = true
    errors.value = {}

    try {
        const response = await apiService.updateBill(props.bill.id, {
            supplier_id: form.value.supplier_id,
            bill_date: form.value.bill_date,
            due_date: form.value.due_date,
            notes: form.value.notes,
            items: form.value.lines
        })

        if (response.success) {
            emit('updated', response.data)
            emit('close')
        } else {
            if (response.errors) {
                errors.value = response.errors
            }
        }
    } catch (error: any) {
        console.error('Error updating bill:', error)
        if (error.response?.data?.errors) {
            errors.value = error.response.data.errors
        } else {
            errors.value = { general: 'An error occurred while updating the bill' }
        }
    } finally {
        loading.value = false
    }
}

const loadBillData = () => {
    if (props.bill) {
        form.value = {
            supplier_id: props.bill.supplier_id,
            bill_number: props.bill.bill_number,
            bill_date: props.bill.bill_date.split('T')[0],
            due_date: props.bill.due_date.split('T')[0],
            notes: props.bill.notes || '',
            lines: props.bill.items?.map(item => ({
                description: item.description || '',
                quantity: item.quantity,
                unit_price: item.unit_price,
                tax_percentage: item.tax_percentage || 0
            })) || []
        }
    }
}

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(amount)
}

// Watch for form changes to validate due date
watch(() => form.value.bill_date, (newDate) => {
    if (newDate && form.value.due_date && new Date(newDate) >= new Date(form.value.due_date)) {
        form.value.due_date = new Date(new Date(newDate).getTime() + 24 * 60 * 60 * 1000).toISOString().split('T')[0]
    }
})

// Watch for bill changes to load data
watch(() => props.bill, () => {
    if (props.bill) {
        loadBillData()
    }
}, { immediate: true })

onMounted(() => {
    if (props.bill) {
        loadBillData()
    }
})
</script>
