<template>

    <Head title="Chart of Account Details" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="font-semibold text-xl leading-tight">
                    Chart of Account Details
                </h2>
                <div class="flex gap-2">
                    <Link :href="route('finance.chart-of-accounts.edit', account.id)">
                    <Button variant="outline">
                        <Edit class="w-4 h-4 mr-2" />
                        Edit
                    </Button>
                    </Link>
                    <Link :href="route('finance.chart-of-accounts.index')">
                    <Button variant="outline">
                        <ArrowLeft class="w-4 h-4 mr-2" />
                        Back
                    </Button>
                    </Link>
                </div>
            </div>

            <div class="overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Account Information -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <Card>
                            <CardHeader>
                                <CardTitle>Account Information</CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div>
                                    <Label class="text-sm font-medium text-gray-500">Account Code</Label>
                                    <p class="text-lg font-mono font-semibold">{{ account.account_code }}</p>
                                </div>
                                <div>
                                    <Label class="text-sm font-medium text-gray-500">Account Name</Label>
                                    <p class="text-lg font-semibold">{{ account.name }}</p>
                                </div>
                                <div>
                                    <Label class="text-sm font-medium text-gray-500">Account Type</Label>
                                    <Badge :variant="getTypeVariant(account.type)">
                                        {{ getTypeLabel(account.type) }}
                                    </Badge>
                                </div>
                                <div>
                                    <Label class="text-sm font-medium text-gray-500">Status</Label>
                                    <Badge :variant="account.status === 'active' ? 'default' : 'secondary'">
                                        {{ account.status }}
                                    </Badge>
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>Financial Information</CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div>
                                    <Label class="text-sm font-medium text-gray-500">Current Balance</Label>
                                    <p class="text-lg font-bold" :class="getBalanceColor(account.balance || 0)">
                                        {{ formatCurrency(account.balance || 0) }}
                                    </p>
                                </div>
                                <div>
                                    <Label class="text-sm font-medium text-gray-500">Parent Account</Label>
                                    <p class="text-lg">{{ account.parent?.name || 'Root Account' }}</p>
                                </div>
                                <div>
                                    <Label class="text-sm font-medium text-gray-500">Child Accounts</Label>
                                    <p class="text-lg">{{ account.children?.length || 0 }} sub-accounts</p>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <!-- Description -->
                    <Card v-if="account.description" class="mb-8">
                        <CardHeader>
                            <CardTitle>Description</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p class="text-lg">{{ account.description }}</p>
                        </CardContent>
                    </Card>

                    <!-- Child Accounts -->
                    <Card v-if="account.children && account.children.length > 0">
                        <CardHeader>
                            <CardTitle>Child Accounts</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="rounded-md border">
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>Code</TableHead>
                                            <TableHead>Name</TableHead>
                                            <TableHead>Type</TableHead>
                                            <TableHead>Balance</TableHead>
                                            <TableHead>Status</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        <TableRow v-for="child in account.children" :key="child.id">
                                            <TableCell>
                                                <div class="font-mono text-sm">{{ child.account_code }}</div>
                                            </TableCell>
                                            <TableCell>
                                                <div>
                                                    <div class="font-medium">{{ child.name }}</div>
                                                    <div class="text-sm text-gray-500">{{ child.description || `No
                                                        description` }}</div>
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <Badge :variant="getTypeVariant(child.type)">
                                                    {{ getTypeLabel(child.type) }}
                                                </Badge>
                                            </TableCell>
                                            <TableCell>
                                                <div class="text-sm font-medium"
                                                    :class="getBalanceColor(child.balance || 0)">
                                                    {{ formatCurrency(child.balance || 0) }}
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <Badge :variant="child.status === 'active' ? 'default' : 'secondary'">
                                                    {{ child.status }}
                                                </Badge>
                                            </TableCell>
                                        </TableRow>
                                    </TableBody>
                                </Table>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Additional Information -->
                    <Card class="mt-8">
                        <CardHeader>
                            <CardTitle>Additional Information</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <Label class="text-sm font-medium text-gray-500">Created By</Label>
                                    <p class="text-lg">{{ (account as any).created_by_user?.name || 'N/A' }}</p>
                                </div>
                                <div>
                                    <Label class="text-sm font-medium text-gray-500">Created At</Label>
                                    <p class="text-lg">{{ formatDate(account.created_at) }}</p>
                                </div>
                                <div>
                                    <Label class="text-sm font-medium text-gray-500">Updated At</Label>
                                    <p class="text-lg">{{ formatDate(account.updated_at) }}</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Label } from '@/components/ui/label'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Edit, ArrowLeft } from 'lucide-vue-next'
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
    { title: props.account.name, href: `/finance/chart-of-accounts/${props.account.id}` }
])

const formatDate = (dateString: string): string => {
    if (!dateString) return 'N/A'
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const formatCurrency = (amount: number): string => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(amount)
}

const getTypeLabel = (type: string): string => {
    const labels: Record<string, string> = {
        'asset': 'Asset',
        'liability': 'Liability',
        'equity': 'Equity',
        'revenue': 'Revenue',
        'expense': 'Expense'
    }
    return labels[type] || type || 'N/A'
}

const getTypeVariant = (type: string): 'default' | 'secondary' | 'destructive' => {
    const variants: Record<string, 'default' | 'secondary' | 'destructive'> = {
        'asset': 'default',
        'liability': 'destructive',
        'equity': 'secondary',
        'revenue': 'default',
        'expense': 'destructive'
    }
    return variants[type] || 'secondary'
}

const getBalanceColor = (balance: number): string => {
    if (balance > 0) return 'text-green-600'
    if (balance < 0) return 'text-red-600'
    return 'text-white'
}
</script>
