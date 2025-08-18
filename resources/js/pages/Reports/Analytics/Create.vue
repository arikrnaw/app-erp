<template>

    <Head title="Create Business Analytics" />
    <AppLayout>
        <template #header>
            <AppSidebarHeader :breadcrumbs="breadcrumbs" />
        </template>

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Create Business Analytics</h1>
                    <p class="text-muted-foreground">
                        Create a new business analytics report to gain insights into your business data.
                    </p>
                </div>
                <Link :href="route('reports.analytics.index')">
                <Button variant="outline">
                    <ArrowLeft class="h-4 w-4 mr-2" />
                    Back to Analytics
                </Button>
                </Link>
            </div>

            <!-- Form -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <TrendingUp class="h-5 w-5" />
                        Analytics Information
                    </CardTitle>
                    <CardDescription>
                        Fill in the details below to create your business analytics report.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Basic Information -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <Label for="title">Title *</Label>
                                <Input id="title" v-model="form.title" placeholder="Enter analytics title"
                                    :class="{ 'border-red-500': form.errors.title }" />
                                <p v-if="form.errors.title" class="text-sm text-red-500">
                                    {{ form.errors.title }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="analysis_type">Analysis Type *</Label>
                                <Select v-model="form.analysis_type">
                                    <SelectTrigger :class="{ 'border-red-500': form.errors.analysis_type }">
                                        <SelectValue placeholder="Select analysis type" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="sales_analysis">Sales Analysis</SelectItem>
                                        <SelectItem value="customer_analysis">Customer Analysis</SelectItem>
                                        <SelectItem value="product_analysis">Product Analysis</SelectItem>
                                        <SelectItem value="market_analysis">Market Analysis</SelectItem>
                                        <SelectItem value="performance_analysis">Performance Analysis</SelectItem>
                                        <SelectItem value="trend_analysis">Trend Analysis</SelectItem>
                                        <SelectItem value="forecasting">Forecasting</SelectItem>
                                        <SelectItem value="custom">Custom Analysis</SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.analysis_type" class="text-sm text-red-500">
                                    {{ form.errors.analysis_type }}
                                </p>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="description">Description</Label>
                            <Textarea id="description" v-model="form.description"
                                placeholder="Enter detailed description of the analysis" rows="3" />
                            <p v-if="form.errors.description" class="text-sm text-red-500">
                                {{ form.errors.description }}
                            </p>
                        </div>

                        <!-- Data Configuration -->
                        <Separator />
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="space-y-2">
                                <Label for="data_source">Data Source *</Label>
                                <Select v-model="form.data_source">
                                    <SelectTrigger :class="{ 'border-red-500': form.errors.data_source }">
                                        <SelectValue placeholder="Select data source" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="sales_orders">Sales Orders</SelectItem>
                                        <SelectItem value="customers">Customers</SelectItem>
                                        <SelectItem value="products">Products</SelectItem>
                                        <SelectItem value="inventory">Inventory</SelectItem>
                                        <SelectItem value="financial_reports">Financial Reports</SelectItem>
                                        <SelectItem value="external_api">External API</SelectItem>
                                        <SelectItem value="custom">Custom Data</SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.data_source" class="text-sm text-red-500">
                                    {{ form.errors.data_source }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="analysis_date">Analysis Date *</Label>
                                <Input id="analysis_date" v-model="form.analysis_date" type="date"
                                    :class="{ 'border-red-500': form.errors.analysis_date }" />
                                <p v-if="form.errors.analysis_date" class="text-sm text-red-500">
                                    {{ form.errors.analysis_date }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="priority">Priority *</Label>
                                <Select v-model="form.priority">
                                    <SelectTrigger :class="{ 'border-red-500': form.errors.priority }">
                                        <SelectValue placeholder="Select priority" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="low">Low</SelectItem>
                                        <SelectItem value="medium">Medium</SelectItem>
                                        <SelectItem value="high">High</SelectItem>
                                        <SelectItem value="critical">Critical</SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.priority" class="text-sm text-red-500">
                                    {{ form.errors.priority }}
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <Label for="data_start_date">Data Start Date</Label>
                                <Input id="data_start_date" v-model="form.data_start_date" type="date" />
                                <p v-if="form.errors.data_start_date" class="text-sm text-red-500">
                                    {{ form.errors.data_start_date }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="data_end_date">Data End Date</Label>
                                <Input id="data_end_date" v-model="form.data_end_date" type="date" />
                                <p v-if="form.errors.data_end_date" class="text-sm text-red-500">
                                    {{ form.errors.data_end_date }}
                                </p>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="space-y-2">
                            <Label for="status">Status *</Label>
                            <Select v-model="form.status">
                                <SelectTrigger :class="{ 'border-red-500': form.errors.status }">
                                    <SelectValue placeholder="Select status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="draft">Draft</SelectItem>
                                    <SelectItem value="published">Published</SelectItem>
                                    <SelectItem value="archived">Archived</SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="form.errors.status" class="text-sm text-red-500">
                                {{ form.errors.status }}
                            </p>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center justify-end gap-4 pt-6">
                            <Link :href="route('reports.analytics.index')">
                            <Button type="button" variant="outline">Cancel</Button>
                            </Link>
                            <Button type="submit" :disabled="form.processing">
                                <Save class="h-4 w-4 mr-2" />
                                {{ form.processing ? 'Creating...' : 'Create Analytics' }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import { ArrowLeft, Save, TrendingUp } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';

const form = useForm({
    title: '',
    description: '',
    analysis_type: '',
    data_source: '',
    analysis_date: '',
    data_start_date: '',
    data_end_date: '',
    key_metrics: {},
    insights: {},
    recommendations: {},
    visualization_data: {},
    priority: '',
    status: 'draft',
});

const submit = () => {
    form.post(route('reports.analytics.store'), {
        onSuccess: () => {
            // Handle success
        },
    });
};

const breadcrumbs = [
    { name: 'Dashboard', href: route('dashboard') },
    { name: 'Reporting & Analytics', href: route('reports.index') },
    { name: 'Business Analytics', href: route('reports.analytics.index') },
    { name: 'Create Analytics', href: '#' },
];
</script>
