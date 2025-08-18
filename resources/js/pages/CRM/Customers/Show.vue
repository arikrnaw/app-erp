<template>

    <Head title="Customer Details" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl leading-tight">
                    Customer Details
                </h2>
                <div class="flex gap-2">
                    <Link :href="route('crm.customers.edit', customer.id)">
                    <Button variant="outline">
                        <Edit class="w-4 h-4 mr-2" />
                        Edit
                    </Button>
                    </Link>
                    <Link :href="route('crm.customers.index')">
                    <Button variant="outline">
                        <ArrowLeft class="w-4 h-4 mr-2" />
                        Back
                    </Button>
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <!-- Customer Information -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <Card>
                                <CardHeader>
                                    <CardTitle>Basic Information</CardTitle>
                                </CardHeader>
                                <CardContent class="space-y-4">
                                    <div>
                                        <Label class="text-sm font-medium text-gray-500">Name</Label>
                                        <p class="text-lg font-semibold">{{ customer.name }}</p>
                                    </div>
                                    <div>
                                        <Label class="text-sm font-medium text-gray-500">Customer Code</Label>
                                        <p class="text-lg">{{ customer.customer_code }}</p>
                                    </div>
                                    <div>
                                        <Label class="text-sm font-medium text-gray-500">Email</Label>
                                        <p class="text-lg">{{ customer.email }}</p>
                                    </div>
                                    <div>
                                        <Label class="text-sm font-medium text-gray-500">Phone</Label>
                                        <p class="text-lg">{{ customer.phone }}</p>
                                    </div>
                                    <div>
                                        <Label class="text-sm font-medium text-gray-500">Company Name</Label>
                                        <p class="text-lg">{{ customer.company_name || 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <Label class="text-sm font-medium text-gray-500">Customer Type</Label>
                                        <Badge
                                            :variant="customer.customer_type === 'individual' ? 'default' : 'secondary'">
                                            {{ customer.customer_type }}
                                        </Badge>
                                    </div>
                                    <div>
                                        <Label class="text-sm font-medium text-gray-500">Status</Label>
                                        <Badge :variant="customer.status === 'active' ? 'default' : 'secondary'">
                                            {{ customer.status }}
                                        </Badge>
                                    </div>
                                </CardContent>
                            </Card>

                            <Card>
                                <CardHeader>
                                    <CardTitle>Address Information</CardTitle>
                                </CardHeader>
                                <CardContent class="space-y-4">
                                    <div>
                                        <Label class="text-sm font-medium text-gray-500">Address</Label>
                                        <p class="text-lg">{{ customer.address || 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <Label class="text-sm font-medium text-gray-500">City</Label>
                                        <p class="text-lg">{{ customer.city || 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <Label class="text-sm font-medium text-gray-500">State/Province</Label>
                                        <p class="text-lg">{{ customer.state || 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <Label class="text-sm font-medium text-gray-500">Postal Code</Label>
                                        <p class="text-lg">{{ customer.postal_code || 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <Label class="text-sm font-medium text-gray-500">Country</Label>
                                        <p class="text-lg">{{ customer.country || 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <Label class="text-sm font-medium text-gray-500">Tax ID</Label>
                                        <p class="text-lg">{{ customer.tax_id || 'N/A' }}</p>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>

                        <!-- Additional Information -->
                        <Card class="mt-6">
                            <CardHeader>
                                <CardTitle>Additional Information</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <Label class="text-sm font-medium text-gray-500">Created At</Label>
                                        <p class="text-lg">{{ formatDate(customer.created_at) }}</p>
                                    </div>
                                    <div>
                                        <Label class="text-sm font-medium text-gray-500">Updated At</Label>
                                        <p class="text-lg">{{ formatDate(customer.updated_at) }}</p>
                                    </div>
                                    <div>
                                        <Label class="text-sm font-medium text-gray-500">Company</Label>
                                        <p class="text-lg">{{ customer.company?.name || 'N/A' }}</p>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Label } from '@/components/ui/label'
import { Edit, ArrowLeft } from 'lucide-vue-next'
import type { Customer } from '@/types/erp'

interface Props {
    customer: Customer
}

const props = defineProps<Props>()

const formatDate = (dateString: string): string => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}
</script>
