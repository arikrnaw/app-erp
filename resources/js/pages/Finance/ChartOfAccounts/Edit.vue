<template>

    <Head title="Edit Chart of Account" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 space-y-6">
            <!-- Header Section -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Edit Chart of Account</h1>
                    <p class="text-muted-foreground mt-1">
                        Update the account details and configuration
                    </p>
                </div>
                <Link :href="route('finance.chart-of-accounts.index')">
                <Button variant="outline">
                    <ArrowLeft class="h-4 w-4 mr-2" />
                    Back to Accounts
                </Button>
                </Link>
            </div>

            <!-- Form Card -->
            <Card class="shadow-sm border-border">
                <CardHeader class="border-b border-border bg-muted/30">
                    <CardTitle class="text-xl font-semibold">Account Information</CardTitle>
                    <CardDescription class="text-muted-foreground">
                        Modify the account details, type, and hierarchy settings
                    </CardDescription>
                </CardHeader>
                <CardContent class="p-6">
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Account Details -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Account Code -->
                            <div class="space-y-3">
                                <Label for="account_code" class="text-sm font-medium">Account Code *</Label>
                                <Input id="account_code" v-model="form.account_code" placeholder="e.g., 1000, 2000"
                                    class="h-10"
                                    :class="{ 'border-destructive focus:ring-destructive': form.errors.account_code }"
                                    required />
                                <p class="text-sm text-muted-foreground">
                                    Unique identifier for the account
                                </p>
                                <p v-if="form.errors.account_code" class="text-sm text-destructive">
                                    {{ form.errors.account_code }}
                                </p>
                            </div>

                            <!-- Account Name -->
                            <div class="space-y-3">
                                <Label for="name" class="text-sm font-medium">Account Name *</Label>
                                <Input id="name" v-model="form.name" placeholder="e.g., Cash, Accounts Payable"
                                    class="h-10"
                                    :class="{ 'border-destructive focus:ring-destructive': form.errors.name }"
                                    required />
                                <p class="text-sm text-muted-foreground">
                                    Descriptive name for the account
                                </p>
                                <p v-if="form.errors.name" class="text-sm text-destructive">
                                    {{ form.errors.name }}
                                </p>
                            </div>
                        </div>

                        <!-- Account Type and Parent -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Account Type -->
                            <div class="space-y-3 w-full">
                                <Label for="type" class="text-sm font-medium">Account Type *</Label>
                                <Select v-model="form.type" class="w-full"
                                    :class="{ 'border-destructive focus:ring-destructive': form.errors.type }" required>
                                    <SelectTrigger class="h-10 w-full">
                                        <SelectValue placeholder="Select account type" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="asset">
                                            <div class="flex items-center">
                                                <div class="w-3 h-3 bg-primary rounded-full mr-2"></div>
                                                Asset
                                            </div>
                                        </SelectItem>
                                        <SelectItem value="liability">
                                            <div class="flex items-center">
                                                <div class="w-3 h-3 bg-secondary rounded-full mr-2"></div>
                                                Liability
                                            </div>
                                        </SelectItem>
                                        <SelectItem value="equity">
                                            <div class="flex items-center">
                                                <div class="w-3 h-3 bg-accent rounded-full mr-2"></div>
                                                Equity
                                            </div>
                                        </SelectItem>
                                        <SelectItem value="revenue">
                                            <div class="flex items-center">
                                                <div class="w-3 h-3 bg-primary rounded-full mr-2"></div>
                                                Revenue
                                            </div>
                                        </SelectItem>
                                        <SelectItem value="expense">
                                            <div class="flex items-center">
                                                <div class="w-3 h-3 bg-destructive rounded-full mr-2"></div>
                                                Expense
                                            </div>
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <p class="text-sm text-muted-foreground">
                                    The type of account determines how it's classified
                                </p>
                                <p v-if="form.errors.type" class="text-sm text-destructive">
                                    {{ form.errors.type }}
                                </p>
                            </div>

                            <!-- Parent Account -->
                            <div class="space-y-3">
                                <Label for="parent_id" class="text-sm font-medium">Parent Account</Label>
                                <Select v-model="form.parent_id" class="w-full"
                                    :class="{ 'border-destructive focus:ring-destructive': form.errors.parent_id }">
                                    <SelectTrigger class="h-10 w-full">
                                        <SelectValue placeholder="No parent (root account)" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="null">No parent (root account)</SelectItem>
                                        <SelectItem v-for="account in parentAccounts" :key="account?.id"
                                            :value="account?.id" :disabled="account?.id === props.account.id">
                                            {{ account?.account_code }} - {{ account?.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <p class="text-sm text-muted-foreground">
                                    Optional parent account for hierarchical structure
                                </p>
                                <p v-if="form.errors.parent_id" class="text-sm text-destructive">
                                    {{ form.errors.parent_id }}
                                </p>
                            </div>
                        </div>

                        <!-- Description and Status -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Description -->
                            <div class="space-y-3">
                                <Label for="description" class="text-sm font-medium">Description</Label>
                                <Textarea id="description" v-model="form.description" rows="3"
                                    placeholder="Enter a detailed description of this account..." />
                                <p class="text-sm text-muted-foreground">
                                    Optional detailed description
                                </p>
                                <p v-if="form.errors.description" class="text-sm text-destructive">
                                    {{ form.errors.description }}
                                </p>
                            </div>

                            <!-- Status -->
                            <div class="space-y-3">
                                <Label for="status" class="text-sm font-medium">Status *</Label>
                                <Select v-model="form.status" class="w-full"
                                    :class="{ 'border-destructive focus:ring-destructive': form.errors.status }"
                                    required>
                                    <SelectTrigger class="h-10 w-full">
                                        <SelectValue placeholder="Select status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="active">
                                            <div class="flex items-center">
                                                <div class="w-3 h-3 bg-primary rounded-full mr-2"></div>
                                                Active
                                            </div>
                                        </SelectItem>
                                        <SelectItem value="inactive">
                                            <div class="flex items-center">
                                                <div class="w-3 h-3 bg-muted-foreground rounded-full mr-2"></div>
                                                Inactive
                                            </div>
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <p class="text-sm text-muted-foreground">
                                    Account status determines if it can be used in transactions
                                </p>
                                <p v-if="form.errors.status" class="text-sm text-destructive">
                                    {{ form.errors.status }}
                                </p>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-end space-x-3 pt-6 border-t border-border">
                            <Link :href="route('finance.chart-of-accounts.index')">
                            <Button variant="outline" type="button" class="h-10 px-4">
                                Cancel
                            </Button>
                            </Link>
                            <Button type="submit" :disabled="isSubmitting" class="h-10 px-6">
                                <Loader2 v-if="isSubmitting" class="h-4 w-4 mr-2 animate-spin" />
                                <Save v-else class="h-4 w-4 mr-2" />
                                {{ isSubmitting ? 'Updating...' : 'Update Account' }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { ArrowLeft, Save, Loader2 } from 'lucide-vue-next'
import { apiService } from '@/services/api'
import type { ChartOfAccount } from '@/types/erp'
import type { BreadcrumbItemType } from '@/types'

interface Props {
    account: ChartOfAccount
}

const props = defineProps<Props>()

const breadcrumbs = computed<BreadcrumbItemType[]>(() => [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Finance', href: '/finance' },
    { title: 'Chart of Accounts', href: '/finance/chart-of-accounts' },
    { title: 'Edit', href: `/finance/chart-of-accounts/${props.account.id}/edit` }
])

const parentAccounts = ref<ChartOfAccount[]>([])
const isSubmitting = ref(false)

const form = useForm({
    account_code: props.account.account_code,
    name: props.account.name,
    description: props.account.description || '',
    type: props.account.type,
    parent_id: props.account.parent_id || 'null',
    status: props.account.status
})

const fetchParentAccounts = async () => {
    try {
        const response = await apiService.getChartOfAccounts({ page: 1 })
        parentAccounts.value = response.data || []
    } catch (error) {
        console.error('Error fetching parent accounts:', error)
        parentAccounts.value = []
    }
}

const submit = async (): Promise<void> => {
    if (isSubmitting.value) return

    try {
        isSubmitting.value = true

        // Prepare data for API
        const updateData = {
            account_code: form.account_code,
            name: form.name,
            description: form.description,
            type: form.type,
            parent_id: form.parent_id === 'null' ? null : form.parent_id,
            status: form.status
        }

        // Call API service
        await apiService.updateChartOfAccount(props.account.id, updateData)

        // Show success toast
        if (typeof window !== 'undefined' && window.toast) {
            window.toast.success('Account Updated!', 'Chart of account has been updated successfully')
        }

        // Redirect to index page on success
        window.location.href = route('finance.chart-of-accounts.index')
    } catch (error: any) {
        console.error('Error updating account:', error)

        // Handle validation errors
        if (error.response?.data?.errors) {
            // Convert API errors to form errors
            Object.keys(error.response.data.errors).forEach(key => {
                if (error.response.data.errors[key] && error.response.data.errors[key][0]) {
                    (form as any).setError(key, error.response.data.errors[key][0])
                }
            })
        } else {
            // Show general error
            if (typeof window !== 'undefined' && window.toast) {
                window.toast.error('Error!', 'Failed to update account. Please try again.')
            } else {
                alert('Failed to update account. Please try again.')
            }
        }
    } finally {
        isSubmitting.value = false
    }
}

onMounted(() => {
    fetchParentAccounts()
})
</script>
