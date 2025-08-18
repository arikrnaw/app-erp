<template>

    <Head title="Create Chart of Account" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="font-semibold text-xl leading-tight">
                    Create Chart of Account
                </h2>
            </div>

            <div class="overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Account Details -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Account Code -->
                            <div>
                                <Label for="account_code">Account Code</Label>
                                <Input id="account_code" v-model="form.account_code" class="mt-1" required />
                                <InputError :message="form.errors.account_code" class="mt-2" />
                            </div>

                            <!-- Account Name -->
                            <div>
                                <Label for="name">Account Name</Label>
                                <Input id="name" v-model="form.name" class="mt-1" required />
                                <InputError :message="form.errors.name" class="mt-2" />
                            </div>
                        </div>

                        <!-- Account Type and Parent -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Account Type -->
                            <div>
                                <Label for="type">Account Type</Label>
                                <select id="type" v-model="form.type"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    required>
                                    <option value="">Select Account Type</option>
                                    <option value="asset">Asset</option>
                                    <option value="liability">Liability</option>
                                    <option value="equity">Equity</option>
                                    <option value="revenue">Revenue</option>
                                    <option value="expense">Expense</option>
                                </select>
                                <InputError :message="form.errors.type" class="mt-2" />
                            </div>

                            <!-- Parent Account -->
                            <div>
                                <Label for="parent_id">Parent Account</Label>
                                <select id="parent_id" v-model="form.parent_id"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="">No Parent (Root Account)</option>
                                    <option v-for="account in parentAccounts" :key="account?.id" :value="account?.id">
                                        {{ account?.account_code }} - {{ account?.name }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.parent_id" class="mt-2" />
                            </div>
                        </div>

                        <!-- Description and Status -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Description -->
                            <div>
                                <Label for="description">Description</Label>
                                <textarea id="description" v-model="form.description" rows="3"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    placeholder="Enter account description..."></textarea>
                                <InputError :message="form.errors.description" class="mt-2" />
                            </div>

                            <!-- Status -->
                            <div>
                                <Label for="status">Status</Label>
                                <select id="status" v-model="form.status"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                                <InputError :message="form.errors.status" class="mt-2" />
                            </div>
                        </div>

                        <!-- Initial Balance -->
                        <div>
                            <Label for="balance">Initial Balance</Label>
                            <Input id="balance" type="number" step="0.01" v-model="form.balance" class="mt-1" />
                            <InputError :message="form.errors.balance" class="mt-2" />
                            <p class="text-sm text-gray-500 mt-1">Leave empty for zero balance</p>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <Link :href="route('finance.chart-of-accounts.index')">
                            <Button variant="outline" type="button" class="mr-2">
                                Cancel
                            </Button>
                            </Link>
                            <Button :class="{ 'opacity-25': form.processing }" :disabled="form.processing"
                                type="submit">
                                Create Account
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import InputError from '@/components/InputError.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
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
        const response = await apiService.getChartOfAccounts({ page: 1 })
        parentAccounts.value = response.data || []
    } catch (error) {
        console.error('Error fetching parent accounts:', error)
        parentAccounts.value = []
    }
}

const submit = (): void => {
    form.post(route('finance.chart-of-accounts.store'))
}

onMounted(() => {
    fetchParentAccounts()
})
</script>
