<template>

    <Head title="Edit Chart of Account" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto py-6 space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="space-y-1">
                    <h1 class="text-3xl font-bold tracking-tight">Edit Chart of Account</h1>
                    <p class="text-muted-foreground">
                        Update the account details and configuration
                    </p>
                </div>
                <div class="flex items-center space-x-2">
                    <Link :href="route('finance.chart-of-accounts.index')">
                    <Button variant="outline" size="sm">
                        <ArrowLeft class="h-4 w-4 mr-2" />
                        Back to Accounts
                    </Button>
                    </Link>
                </div>
            </div>

            <!-- Main Form Card -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center space-x-2">
                        <BookOpen class="h-5 w-5 text-primary" />
                        <span>Account Information</span>
                    </CardTitle>
                    <CardDescription>
                        Modify the account details, type, and hierarchy settings
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Account Details Section -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold text-foreground">Basic Details</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <Label for="account_code">Account Code</Label>
                                    <Input id="account_code" v-model="form.account_code" placeholder="e.g., 1000, 2000"
                                        required />
                                    <InputError :message="form.errors.account_code" class="mt-1" />
                                </div>

                                <div class="space-y-2">
                                    <Label for="name">Account Name</Label>
                                    <Input id="name" v-model="form.name" placeholder="e.g., Cash, Accounts Payable"
                                        required />
                                    <InputError :message="form.errors.name" class="mt-1" />
                                </div>
                            </div>
                        </div>

                        <!-- Account Configuration Section -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold text-foreground">Configuration</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <Label for="type">Account Type</Label>
                                    <Select v-model="form.type" required>
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select account type" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="asset">Asset</SelectItem>
                                            <SelectItem value="liability">Liability</SelectItem>
                                            <SelectItem value="equity">Equity</SelectItem>
                                            <SelectItem value="revenue">Revenue</SelectItem>
                                            <SelectItem value="expense">Expense</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <InputError :message="form.errors.type" class="mt-1" />
                                </div>

                                <div class="space-y-2">
                                    <Label for="parent_id">Parent Account</Label>
                                    <Select v-model="form.parent_id">
                                        <SelectTrigger>
                                            <SelectValue placeholder="No parent (root account)" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="null">No Parent (Root Account)</SelectItem>
                                            <SelectItem v-for="account in parentAccounts" :key="account?.id"
                                                :value="account?.id" :disabled="account?.id === props.account.id">
                                                {{ account?.account_code }} - {{ account?.name }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <InputError :message="form.errors.parent_id" class="mt-1" />
                                </div>
                            </div>
                        </div>

                        <!-- Additional Details Section -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold text-foreground">Additional Details</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <Label for="description">Description</Label>
                                    <Textarea id="description" v-model="form.description" rows="3"
                                        placeholder="Enter a detailed description of this account..."
                                        class="resize-none" />
                                    <InputError :message="form.errors.description" class="mt-1" />
                                </div>

                                <div class="space-y-2">
                                    <Label for="status">Status</Label>
                                    <Select v-model="form.status" required>
                                        <SelectTrigger>
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="active">
                                                <div class="flex items-center space-x-2">
                                                    <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                                    <span>Active</span>
                                                </div>
                                            </SelectItem>
                                            <SelectItem value="inactive">
                                                <div class="flex items-center space-x-2">
                                                    <div class="w-2 h-2 bg-gray-500 rounded-full"></div>
                                                    <span>Inactive</span>
                                                </div>
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <InputError :message="form.errors.status" class="mt-1" />
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-end space-x-3 pt-6 border-t">
                            <Link :href="route('finance.chart-of-accounts.index')">
                            <Button variant="outline" type="button">
                                Cancel
                            </Button>
                            </Link>
                            <Button type="submit" :disabled="isSubmitting" class="min-w-[120px]">
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
import InputError from '@/components/InputError.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { ArrowLeft, BookOpen, Save, Loader2 } from 'lucide-vue-next'
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

        // Show success message and redirect
        alert('Account updated successfully!')
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
            alert('Failed to update account. Please try again.')
        }
    } finally {
        isSubmitting.value = false
    }
}

onMounted(() => {
    fetchParentAccounts()
})
</script>
