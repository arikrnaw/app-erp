<template>

    <Head title="Supplier Details" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl leading-tight">
                    Supplier Details
                </h2>
                <div class="flex gap-2">
                    <Link :href="route('suppliers.edit', supplier.id)">
                    <Button variant="outline">
                        <Edit class="w-4 h-4 mr-2" />
                        Edit
                    </Button>
                    </Link>
                    <Link :href="route('suppliers.index')">
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
                        <!-- Supplier Information -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <Card>
                                <CardHeader>
                                    <CardTitle>Basic Information</CardTitle>
                                </CardHeader>
                                <CardContent class="space-y-4">
                                    <div>
                                        <Label class="text-sm font-medium text-gray-500">Name</Label>
                                        <p class="text-lg font-semibold">{{ supplier.name }}</p>
                                    </div>
                                    <div>
                                        <Label class="text-sm font-medium text-gray-500">Supplier Code</Label>
                                        <p class="text-lg">{{ supplier.code }}</p>
                                    </div>
                                    <div>
                                        <Label class="text-sm font-medium text-gray-500">Email</Label>
                                        <p class="text-lg">{{ supplier.email }}</p>
                                    </div>
                                    <div>
                                        <Label class="text-sm font-medium text-gray-500">Phone</Label>
                                        <p class="text-lg">{{ supplier.phone }}</p>
                                    </div>
                                    <div>
                                        <Label class="text-sm font-medium text-gray-500">Contact Person</Label>
                                        <p class="text-lg">{{ supplier.contact_person }}</p>
                                    </div>
                                    <div>
                                        <Label class="text-sm font-medium text-gray-500">Status</Label>
                                        <span :class="[
                                            'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                                            supplier.status === 'active'
                                                ? 'bg-green-100 text-green-800'
                                                : 'bg-red-100 text-red-800'
                                        ]">
                                            {{ supplier.status }}
                                        </span>
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
                                        <p class="text-lg">{{ supplier.address }}</p>
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
                                        <p class="text-lg">{{ formatDate(supplier.created_at) }}</p>
                                    </div>
                                    <div>
                                        <Label class="text-sm font-medium text-gray-500">Updated At</Label>
                                        <p class="text-lg">{{ formatDate(supplier.updated_at) }}</p>
                                    </div>
                                    <div>
                                        <Label class="text-sm font-medium text-gray-500">Company</Label>
                                        <p class="text-lg">{{ supplier.company?.name || 'N/A' }}</p>
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
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Label } from '@/components/ui/label'
import { Edit, ArrowLeft } from 'lucide-vue-next'
import type { Supplier } from '@/types/erp'

interface Props {
    supplier: Supplier
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
