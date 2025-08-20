<template>

    <Head title="Create Chart of Account" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 space-y-6">
            <!-- Header Section -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Create Chart of Account</h1>
                    <p class="text-muted-foreground mt-1">
                        Add a new account to your chart of accounts
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
            <Card>
                <CardHeader>
                    <CardTitle>Account Information</CardTitle>
                    <CardDescription>
                        Fill in the details below to create a new chart of account
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Account Details -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Account Code -->
                            <div class="space-y-2">
                                <Label for="account_code">Account Code *</Label>
                                <Input id="account_code" v-model="form.account_code"
                                    placeholder="e.g., 1000, 1100, 2000"
                                    :class="{ 'border-red-500': form.errors.account_code }" required />
                                <p class="text-sm text-muted-foreground">
                                    Unique identifier for the account
                                </p>
                                <p v-if="form.errors.account_code" class="text-sm text-red-600 dark:text-red-400">
                                    {{ form.errors.account_code }}
                                </p>
                            </div>

                            <!-- Account Name -->
                            <div class="space-y-2">
                                <Label for="name">Account Name *</Label>
                                <Input id="name" v-model="form.name"
                                    placeholder="e.g., Cash, Accounts Receivable, Sales Revenue"
                                    :class="{ 'border-red-500': form.errors.name }" required />
                                <p class="text-sm text-muted-foreground">
                                    Descriptive name for the account
                                </p>
                                <p v-if="form.errors.name" class="text-sm text-red-600 dark:text-red-400">
                                    {{ form.errors.name }}
                                </p>
                            </div>
                        </div>

                        <!-- Account Type and Parent -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Account Type -->
                            <div class="space-y-2">
                                <Label for="type">Account Type *</Label>
                                <Select v-model="form.type" :class="{ 'border-red-500': form.errors.type }">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select account type" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="asset">
                                            <div class="flex items-center">
                                                <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
                                                Asset
                                            </div>
                                        </SelectItem>
                                        <SelectItem value="liability">
                                            <div class="flex items-center">
                                                <div class="w-3 h-3 bg-red-500 rounded-full mr-2"></div>
                                                Liability
                                            </div>
                                        </SelectItem>
                                        <SelectItem value="equity">
                                            <div class="flex items-center">
                                                <div class="w-3 h-3 bg-blue-500 rounded-full mr-2"></div>
                                                Equity
                                            </div>
                                        </SelectItem>
                                        <SelectItem value="revenue">
                                            <div class="flex items-center">
                                                <div class="w-3 h-3 bg-purple-500 rounded-full mr-2"></div>
                                                Revenue
                                            </div>
                                        </SelectItem>
                                        <SelectItem value="expense">
                                            <div class="flex items-center">
                                                <div class="w-3 h-3 bg-orange-500 rounded-full mr-2"></div>
                                                Expense
                                            </div>
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <p class="text-sm text-muted-foreground">
                                    The type of account determines how it's classified
                                </p>
                                <p v-if="form.errors.type" class="text-sm text-red-600 dark:text-red-400">
                                    {{ form.errors.type }}
                                </p>
                            </div>

                            <!-- Parent Account -->
                            <div class="space-y-2">
                                <Label for="parent_id">Parent Account</Label>
                                <Select v-model="form.parent_id" :class="{ 'border-red-500': form.errors.parent_id }">
                                    <SelectTrigger>
                                        <SelectValue placeholder="No parent (root account)" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="">No parent (root account)</SelectItem>
                                        <SelectItem v-for="account in parentAccounts" :key="account.id"
                                            :value="account.id">
                                            {{ account.account_code }} - {{ account.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <p class="text-sm text-muted-foreground">
                                    Optional parent account for hierarchical structure
                                </p>
                                <p v-if="form.errors.parent_id" class="text-sm text-red-600 dark:text-red-400">
                                    {{ form.errors.parent_id }}
                                </p>
                            </div>
                        </div>

                        <!-- Description and Status -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Description -->
                            <div class="space-y-2">
                                <Label for="description">Description</Label>
                                <Textarea id="description" v-model="form.description"
                                    placeholder="Enter detailed description of the account..." rows="3" />
                                <p class="text-sm text-muted-foreground">
                                    Optional detailed description
                                </p>
                                <p v-if="form.errors.description" class="text-sm text-red-600 dark:text-red-400">
                                    {{ form.errors.description }}
                                </p>
                            </div>

                            <!-- Status -->
                            <div class="space-y-2">
                                <Label for="status">Status *</Label>
                                <Select v-model="form.status" :class="{ 'border-red-500': form.errors.status }">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="active">
                                            <div class="flex items-center">
                                                <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
                                                Active
                                            </div>
                                        </SelectItem>
                                        <SelectItem value="inactive">
                                            <div class="flex items-center">
                                                <div class="w-3 h-3 bg-gray-500 rounded-full mr-2"></div>
                                                Inactive
                                            </div>
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <p class="text-sm text-muted-foreground">
                                    Account status determines if it can be used in transactions
                                </p>
                                <p v-if="form.errors.status" class="text-sm text-red-600 dark:text-red-400">
                                    {{ form.errors.status }}
                                </p>
                            </div>
                        </div>

                        <!-- Initial Balance -->
                        <div class="space-y-2">
                            <Label for="balance">Initial Balance</Label>
                            <Input id="balance" type="number" step="0.01" v-model="form.balance" placeholder="0.00"
                                :class="{ 'border-red-500': form.errors.balance }" />
                            <p class="text-sm text-muted-foreground">
                                Optional initial balance for the account (leave empty for zero)
                            </p>
                            <p v-if="form.errors.balance" class="text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.balance }}
                            </p>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-end space-x-2 pt-6 border-t">
                            <Link :href="route('finance.chart-of-accounts.index')">
                            <Button variant="outline" type="button">
                                Cancel
                            </Button>
                            </Link>
                            <Button type="submit" :disabled="form.processing"
                                :class="{ 'opacity-50': form.processing }">
                                <Loader2 v-if="form.processing" class="w-4 h-4 mr-2 animate-spin" />
                                <Save v-else class="w-4 h-4 mr-2" />
                                {{ form.processing ? 'Creating...' : 'Create Account' }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>

            <!-- Account Type Information -->
            <Card>
                <CardHeader>
                    <CardTitle>Account Type Guide</CardTitle>
                    <CardDescription>
                        Understanding different account types and their purposes
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-5">
                        <div class="p-4 border rounded-lg">
                            <div class="flex items-center mb-2">
                                <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
                                <h4 class="font-medium">Assets</h4>
                            </div>
                            <p class="text-sm text-muted-foreground">
                                Resources owned by the company (cash, inventory, equipment)
                            </p>
                        </div>
                        <div class="p-4 border rounded-lg">
                            <div class="flex items-center mb-2">
                                <div class="w-3 h-3 bg-red-500 rounded-full mr-2"></div>
                                <h4 class="font-medium">Liabilities</h4>
                            </div>
                            <p class="text-sm text-muted-foreground">
                                Debts and obligations (loans, accounts payable, taxes)
                            </p>
                        </div>
                        <div class="p-4 border rounded-lg">
                            <div class="flex items-center mb-2">
                                <div class="w-3 h-3 bg-blue-500 rounded-full mr-2"></div>
                                <h4 class="font-medium">Equity</h4>
                            </div>
                            <p class="text-sm text-muted-foreground">
                                Owner's investment and retained earnings
                            </p>
                        </div>
                        <div class="p-4 border rounded-lg">
                            <div class="flex items-center mb-2">
                                <div class="w-3 h-3 bg-purple-500 rounded-full mr-2"></div>
                                <h4 class="font-medium">Revenue</h4>
                            </div>
                            <p class="text-sm text-muted-foreground">
                                Income from business activities (sales, services)
                            </p>
                        </div>
                        <div class="p-4 border rounded-lg">
                            <div class="flex items-center mb-2">
                                <div class="w-3 h-3 bg-orange-500 rounded-full mr-2"></div>
                                <h4 class="font-medium">Expenses</h4>
                            </div>
                            <p class="text-sm text-muted-foreground">
                                Costs incurred in business operations
                            </p>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import {
    ArrowLeft,
    Save,
    Loader2
} from 'lucide-vue-next'
import { apiService } from '@/services/api'
import type { ChartOfAccount } from '@/types/erp'
import type { BreadcrumbItemType } from '@/types'

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Finance', href: '/finance' },
    { title: 'Chart of Accounts', href: '/finance/chart-of-accounts' },
    { title: 'Create', href: '/finance/chart-of-accounts/create' }
]

const parentAccounts = ref<ChartOfAccount[]>([])

const form = useForm({
    account_code: '',
    name: '',
    description: '',
    type: '',
    parent_id: '',
    balance: 0,
    status: 'active'
})

const fetchParentAccounts = async () => {
    try {
        const response = await apiService.getChartOfAccounts({
            page: 1,
            per_page: 100,
            status: 'active' // Only show active accounts as parents
        })
        parentAccounts.value = response.data || []
    } catch (error) {
        console.error('Error fetching parent accounts:', error)
        parentAccounts.value = []
    }
}

const submit = async (): Promise<void> => {
    try {
        form.processing = true

        // Prepare data for API
        const accountData = {
            account_code: form.account_code,
            name: form.name,
            description: form.description || null,
            type: form.type,
            parent_id: form.parent_id || null,
            balance: parseFloat(form.balance) || 0,
            status: form.status
        }

        // Call API service
        await apiService.createChartOfAccount(accountData)

        // Redirect to index page on success
        window.location.href = route('finance.chart-of-accounts.index')
    } catch (error: any) {
        console.error('Error creating chart of account:', error)

        // Handle validation errors
        if (error.response?.data?.errors) {
            form.errors = error.response.data.errors
        } else {
            // Handle general error
            alert('Error creating account. Please try again.')
        }
    } finally {
        form.processing = false
    }
}

onMounted(() => {
    fetchParentAccounts()
})
</script>
