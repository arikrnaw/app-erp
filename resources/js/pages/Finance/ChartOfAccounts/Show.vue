<template>

    <Head title="Chart of Account Details" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <!-- Header Section -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Chart of Account Details</h1>
                    <p class="text-muted-foreground mt-1">
                        View detailed information about this account
                    </p>
                </div>
                <div class="flex gap-3">
                    <Link :href="route('finance.chart-of-accounts.edit', account.id)">
                    <Button variant="outline" class="h-10 px-4">
                        <Edit class="w-4 h-4 mr-2" />
                        Edit
                    </Button>
                    </Link>
                    <Link :href="route('finance.chart-of-accounts.index')">
                    <Button variant="outline" class="h-10 px-4">
                        <ArrowLeft class="w-4 h-4 mr-2" />
                        Back
                    </Button>
                    </Link>
                </div>
            </div>

            <!-- Account Information Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <Card class="shadow-sm border-border">
                    <CardHeader class="border-b border-border bg-muted/30">
                        <CardTitle class="text-xl font-semibold">Account Information</CardTitle>
                    </CardHeader>
                    <CardContent class="p-6 space-y-4">
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground">Account Code</Label>
                            <p class="text-lg font-mono font-semibold text-card-foreground">{{ account.account_code }}
                            </p>
                        </div>
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground">Account Name</Label>
                            <p class="text-lg font-semibold text-card-foreground">{{ account.name }}</p>
                        </div>
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground">Account Type</Label>
                            <Badge :variant="getTypeVariant(account.type)">
                                {{ getTypeLabel(account.type) }}
                            </Badge>
                        </div>
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground">Status</Label>
                            <Badge :variant="account.status === 'active' ? 'default' : 'secondary'">
                                {{ account.status }}
                            </Badge>
                        </div>
                    </CardContent>
                </Card>

                <Card class="shadow-sm border-border">
                    <CardHeader class="border-b border-border bg-muted/30">
                        <CardTitle class="text-xl font-semibold">Financial Information</CardTitle>
                    </CardHeader>
                    <CardContent class="p-6 space-y-4">
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground">Current Balance</Label>
                            <p class="text-lg font-bold" :class="getBalanceColor(account.balance || 0)">
                                {{ formatCurrency(account.balance || 0) }}
                            </p>
                        </div>
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground">Parent Account</Label>
                            <p class="text-lg text-card-foreground">{{ account.parent?.name || 'Root Account' }}</p>
                        </div>
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground">Child Accounts</Label>
                            <p class="text-lg text-card-foreground">{{ account.children?.length || 0 }} sub-accounts</p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Description -->
            <Card v-if="account.description" class="mb-8 shadow-sm border-border">
                <CardHeader class="border-b border-border bg-muted/30">
                    <CardTitle class="text-xl font-semibold">Description</CardTitle>
                </CardHeader>
                <CardContent class="p-6">
                    <p class="text-lg text-card-foreground">{{ account.description }}</p>
                </CardContent>
            </Card>

            <!-- Child Accounts -->
            <Card v-if="account.children && account.children.length > 0" class="shadow-sm border-border">
                <CardHeader class="border-b border-border bg-muted/30">
                    <CardTitle class="text-xl font-semibold">Child Accounts</CardTitle>
                </CardHeader>
                <CardContent class="p-6">
                    <div class="rounded-md border border-border">
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
                                        <div class="font-mono text-sm text-card-foreground">{{ child.account_code }}
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <div>
                                            <div class="font-medium text-card-foreground">{{ child.name }}</div>
                                            <div class="text-sm text-muted-foreground">{{ child.description || `No
                                                description` }}</div>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <Badge :variant="getTypeVariant(child.type)">
                                            {{ getTypeLabel(child.type) }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        <div class="text-sm font-medium" :class="getBalanceColor(child.balance || 0)">
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
            <Card class="mt-8 shadow-sm border-border">
                <CardHeader class="border-b border-border bg-muted/30">
                    <CardTitle class="text-xl font-semibold">Additional Information</CardTitle>
                </CardHeader>
                <CardContent class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground">Created By</Label>
                            <p class="text-lg text-card-foreground">{{ (account as any).created_by_user?.name || 'N/A'
                            }}</p>
                        </div>
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground">Created At</Label>
                            <p class="text-lg text-card-foreground">{{ formatDate(account.created_at) }}</p>
                        </div>
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground">Updated At</Label>
                            <p class="text-lg text-card-foreground">{{ formatDate(account.updated_at) }}</p>
                        </div>
                    </div>
                </CardContent>
            </Card>
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
    if (balance > 0) return 'text-green-600 dark:text-green-400'
    if (balance < 0) return 'text-red-600 dark:text-red-400'
    return 'text-muted-foreground'
}
</script>
