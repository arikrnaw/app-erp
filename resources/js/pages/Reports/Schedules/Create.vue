<template>

    <Head title="Create Report Schedule" />
    <AppLayout>
        <template #header>
            <AppSidebarHeader :breadcrumbs="breadcrumbs" />
        </template>

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Create Report Schedule</h1>
                    <p class="text-muted-foreground">
                        Set up automated report generation and delivery schedules.
                    </p>
                </div>
                <Link :href="route('reports.schedules.index')">
                <Button variant="outline">
                    <ArrowLeft class="h-4 w-4 mr-2" />
                    Back to Schedules
                </Button>
                </Link>
            </div>

            <!-- Form -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Clock class="h-5 w-5" />
                        Schedule Information
                    </CardTitle>
                    <CardDescription>
                        Configure the report schedule details and delivery settings.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Basic Information -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <Label for="name">Schedule Name *</Label>
                                <Input id="name" v-model="form.name" placeholder="Enter schedule name"
                                    :class="{ 'border-red-500': form.errors.name }" />
                                <p v-if="form.errors.name" class="text-sm text-red-500">
                                    {{ form.errors.name }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="report_type">Report Type *</Label>
                                <Select v-model="form.report_type">
                                    <SelectTrigger :class="{ 'border-red-500': form.errors.report_type }">
                                        <SelectValue placeholder="Select report type" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="financial_report">Financial Report</SelectItem>
                                        <SelectItem value="business_analytics">Business Analytics</SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.report_type" class="text-sm text-red-500">
                                    {{ form.errors.report_type }}
                                </p>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="description">Description</Label>
                            <Textarea id="description" v-model="form.description"
                                placeholder="Enter schedule description" rows="3" />
                            <p v-if="form.errors.description" class="text-sm text-red-500">
                                {{ form.errors.description }}
                            </p>
                        </div>

                        <!-- Schedule Configuration -->
                        <Separator />
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="space-y-2">
                                <Label for="frequency">Frequency *</Label>
                                <Select v-model="form.frequency">
                                    <SelectTrigger :class="{ 'border-red-500': form.errors.frequency }">
                                        <SelectValue placeholder="Select frequency" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="daily">Daily</SelectItem>
                                        <SelectItem value="weekly">Weekly</SelectItem>
                                        <SelectItem value="monthly">Monthly</SelectItem>
                                        <SelectItem value="quarterly">Quarterly</SelectItem>
                                        <SelectItem value="yearly">Yearly</SelectItem>
                                        <SelectItem value="custom">Custom</SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.frequency" class="text-sm text-red-500">
                                    {{ form.errors.frequency }}
                                </p>
                            </div>

                            <div v-if="form.frequency === 'weekly'" class="space-y-2">
                                <Label for="day_of_week">Day of Week</Label>
                                <Select v-model="form.day_of_week">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select day" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="monday">Monday</SelectItem>
                                        <SelectItem value="tuesday">Tuesday</SelectItem>
                                        <SelectItem value="wednesday">Wednesday</SelectItem>
                                        <SelectItem value="thursday">Thursday</SelectItem>
                                        <SelectItem value="friday">Friday</SelectItem>
                                        <SelectItem value="saturday">Saturday</SelectItem>
                                        <SelectItem value="sunday">Sunday</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div v-if="form.frequency === 'monthly'" class="space-y-2">
                                <Label for="day_of_month">Day of Month</Label>
                                <Input id="day_of_month" v-model="form.day_of_month" type="number" min="1" max="31"
                                    placeholder="1-31" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <Label for="delivery_time">Delivery Time *</Label>
                                <Input id="delivery_time" v-model="form.delivery_time" type="time"
                                    :class="{ 'border-red-500': form.errors.delivery_time }" />
                                <p v-if="form.errors.delivery_time" class="text-sm text-red-500">
                                    {{ form.errors.delivery_time }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="delivery_method">Delivery Method *</Label>
                                <Select v-model="form.delivery_method">
                                    <SelectTrigger :class="{ 'border-red-500': form.errors.delivery_method }">
                                        <SelectValue placeholder="Select delivery method" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="email">Email</SelectItem>
                                        <SelectItem value="dashboard">Dashboard</SelectItem>
                                        <SelectItem value="pdf">PDF Download</SelectItem>
                                        <SelectItem value="excel">Excel Download</SelectItem>
                                        <SelectItem value="api">API</SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.delivery_method" class="text-sm text-red-500">
                                    {{ form.errors.delivery_method }}
                                </p>
                            </div>
                        </div>

                        <!-- Report Template -->
                        <div class="space-y-2">
                            <Label for="report_template">Report Template</Label>
                            <Input id="report_template" v-model="form.report_template"
                                placeholder="Enter report template name or ID" />
                            <p v-if="form.errors.report_template" class="text-sm text-red-500">
                                {{ form.errors.report_template }}
                            </p>
                        </div>

                        <!-- Active Status -->
                        <div class="flex items-center space-x-2">
                            <input id="is_active" v-model="form.is_active" type="checkbox"
                                class="rounded border-gray-300" />
                            <Label for="is_active">Active Schedule</Label>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center justify-end gap-4 pt-6">
                            <Link :href="route('reports.schedules.index')">
                            <Button type="button" variant="outline">Cancel</Button>
                            </Link>
                            <Button type="submit" :disabled="form.processing">
                                <Save class="h-4 w-4 mr-2" />
                                {{ form.processing ? 'Creating...' : 'Create Schedule' }}
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
import { ArrowLeft, Save, Clock } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    description: '',
    report_type: '',
    report_template: '',
    frequency: '',
    day_of_week: '',
    day_of_month: '',
    delivery_time: '',
    delivery_method: '',
    recipients: [],
    parameters: {},
    is_active: true,
});

const submit = () => {
    form.post(route('reports.schedules.store'), {
        onSuccess: () => {
            // Handle success
        },
    });
};

const breadcrumbs = [
    { name: 'Dashboard', href: route('dashboard') },
    { name: 'Reporting & Analytics', href: route('reports.index') },
    { name: 'Report Schedules', href: route('reports.schedules.index') },
    { name: 'Create Schedule', href: '#' },
];
</script>
