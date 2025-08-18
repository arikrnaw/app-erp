<template>

    <Head title="Create Customer Complaint" />
    <AppLayout>
        <template #header>
            <AppSidebarHeader :breadcrumbs="breadcrumbs" />
        </template>

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Create Customer Complaint</h1>
                    <p class="text-muted-foreground">
                        Record a new customer complaint and assign it to the appropriate team member.
                    </p>
                </div>
                <Link :href="route('customer-service.complaints.index')">
                <Button variant="outline">
                    <ArrowLeft class="h-4 w-4 mr-2" />
                    Back to Complaints
                </Button>
                </Link>
            </div>

            <!-- Form -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <AlertTriangle class="h-5 w-5" />
                        Complaint Information
                    </CardTitle>
                    <CardDescription>
                        Fill in the details below to create a new customer complaint record.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Basic Information -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <Label for="title">Title *</Label>
                                <Input id="title" v-model="form.title" placeholder="Enter complaint title"
                                    :class="{ 'border-red-500': form.errors.title }" />
                                <p v-if="form.errors.title" class="text-sm text-red-500">
                                    {{ form.errors.title }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="complaint_type">Complaint Type *</Label>
                                <Select v-model="form.complaint_type">
                                    <SelectTrigger :class="{ 'border-red-500': form.errors.complaint_type }">
                                        <SelectValue placeholder="Select complaint type" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="product_issue">Product Issue</SelectItem>
                                        <SelectItem value="service_issue">Service Issue</SelectItem>
                                        <SelectItem value="billing_issue">Billing Issue</SelectItem>
                                        <SelectItem value="delivery_issue">Delivery Issue</SelectItem>
                                        <SelectItem value="technical_issue">Technical Issue</SelectItem>
                                        <SelectItem value="quality_issue">Quality Issue</SelectItem>
                                        <SelectItem value="communication_issue">Communication Issue</SelectItem>
                                        <SelectItem value="other">Other</SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.complaint_type" class="text-sm text-red-500">
                                    {{ form.errors.complaint_type }}
                                </p>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="description">Description *</Label>
                            <Textarea id="description" v-model="form.description"
                                placeholder="Provide detailed description of the complaint" rows="4"
                                :class="{ 'border-red-500': form.errors.description }" />
                            <p v-if="form.errors.description" class="text-sm text-red-500">
                                {{ form.errors.description }}
                            </p>
                        </div>

                        <!-- Priority and Status -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
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

                            <div class="space-y-2">
                                <Label for="status">Status *</Label>
                                <Select v-model="form.status">
                                    <SelectTrigger :class="{ 'border-red-500': form.errors.status }">
                                        <SelectValue placeholder="Select status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="open">Open</SelectItem>
                                        <SelectItem value="in_progress">In Progress</SelectItem>
                                        <SelectItem value="resolved">Resolved</SelectItem>
                                        <SelectItem value="closed">Closed</SelectItem>
                                        <SelectItem value="escalated">Escalated</SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.status" class="text-sm text-red-500">
                                    {{ form.errors.status }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="source">Source *</Label>
                                <Select v-model="form.source">
                                    <SelectTrigger :class="{ 'border-red-500': form.errors.source }">
                                        <SelectValue placeholder="Select source" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="phone">Phone</SelectItem>
                                        <SelectItem value="email">Email</SelectItem>
                                        <SelectItem value="chat">Chat</SelectItem>
                                        <SelectItem value="social_media">Social Media</SelectItem>
                                        <SelectItem value="in_person">In Person</SelectItem>
                                        <SelectItem value="website">Website</SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.source" class="text-sm text-red-500">
                                    {{ form.errors.source }}
                                </p>
                            </div>
                        </div>

                        <!-- Customer Information -->
                        <Separator />
                        <div>
                            <h3 class="text-lg font-medium mb-4">Customer Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <Label for="customer_name">Customer Name *</Label>
                                    <Input id="customer_name" v-model="form.customer_name"
                                        placeholder="Enter customer name"
                                        :class="{ 'border-red-500': form.errors.customer_name }" />
                                    <p v-if="form.errors.customer_name" class="text-sm text-red-500">
                                        {{ form.errors.customer_name }}
                                    </p>
                                </div>

                                <div class="space-y-2">
                                    <Label for="customer_email">Customer Email *</Label>
                                    <Input id="customer_email" v-model="form.customer_email" type="email"
                                        placeholder="Enter customer email"
                                        :class="{ 'border-red-500': form.errors.customer_email }" />
                                    <p v-if="form.errors.customer_email" class="text-sm text-red-500">
                                        {{ form.errors.customer_email }}
                                    </p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                                <div class="space-y-2">
                                    <Label for="customer_phone">Customer Phone</Label>
                                    <Input id="customer_phone" v-model="form.customer_phone"
                                        placeholder="Enter customer phone"
                                        :class="{ 'border-red-500': form.errors.customer_phone }" />
                                    <p v-if="form.errors.customer_phone" class="text-sm text-red-500">
                                        {{ form.errors.customer_phone }}
                                    </p>
                                </div>

                                <div class="space-y-2">
                                    <Label for="incident_date">Incident Date *</Label>
                                    <Input id="incident_date" v-model="form.incident_date" type="date"
                                        :class="{ 'border-red-500': form.errors.incident_date }" />
                                    <p v-if="form.errors.incident_date" class="text-sm text-red-500">
                                        {{ form.errors.incident_date }}
                                    </p>
                                </div>
                            </div>

                            <div class="space-y-2 mt-6">
                                <Label for="customer_address">Customer Address</Label>
                                <Textarea id="customer_address" v-model="form.customer_address"
                                    placeholder="Enter customer address" rows="2"
                                    :class="{ 'border-red-500': form.errors.customer_address }" />
                                <p v-if="form.errors.customer_address" class="text-sm text-red-500">
                                    {{ form.errors.customer_address }}
                                </p>
                            </div>
                        </div>

                        <!-- Issue Details -->
                        <Separator />
                        <div>
                            <h3 class="text-lg font-medium mb-4">Issue Details</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <Label for="product_name">Product Name</Label>
                                    <Input id="product_name" v-model="form.product_name"
                                        placeholder="Enter product name"
                                        :class="{ 'border-red-500': form.errors.product_name }" />
                                    <p v-if="form.errors.product_name" class="text-sm text-red-500">
                                        {{ form.errors.product_name }}
                                    </p>
                                </div>

                                <div class="space-y-2">
                                    <Label for="order_number">Order Number</Label>
                                    <Input id="order_number" v-model="form.order_number"
                                        placeholder="Enter order number"
                                        :class="{ 'border-red-500': form.errors.order_number }" />
                                    <p v-if="form.errors.order_number" class="text-sm text-red-500">
                                        {{ form.errors.order_number }}
                                    </p>
                                </div>
                            </div>

                            <div class="space-y-2 mt-6">
                                <Label for="expected_resolution">Expected Resolution</Label>
                                <Textarea id="expected_resolution" v-model="form.expected_resolution"
                                    placeholder="Describe the expected resolution" rows="3"
                                    :class="{ 'border-red-500': form.errors.expected_resolution }" />
                                <p v-if="form.errors.expected_resolution" class="text-sm text-red-500">
                                    {{ form.errors.expected_resolution }}
                                </p>
                            </div>
                        </div>

                        <!-- Assignment -->
                        <Separator />
                        <div>
                            <h3 class="text-lg font-medium mb-4">Assignment</h3>
                            <div class="space-y-2">
                                <Label for="assigned_to">Assign To</Label>
                                <Select v-model="form.assigned_to">
                                    <SelectTrigger :class="{ 'border-red-500': form.errors.assigned_to }">
                                        <SelectValue placeholder="Select team member" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="">Unassigned</SelectItem>
                                        <SelectItem v-for="user in users" :key="user.id" :value="user.id">
                                            {{ user.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.assigned_to" class="text-sm text-red-500">
                                    {{ form.errors.assigned_to }}
                                </p>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center justify-end gap-4 pt-6">
                            <Link :href="route('customer-service.complaints.index')">
                            <Button type="button" variant="outline">Cancel</Button>
                            </Link>
                            <Button type="submit" :disabled="form.processing">
                                <Save class="h-4 w-4 mr-2" />
                                {{ form.processing ? 'Creating...' : 'Create Complaint' }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import {
    AlertTriangle,
    ArrowLeft,
    Save
} from 'lucide-vue-next';

// Props
const props = defineProps<{
    users?: any[];
}>();

// Form
const form = useForm({
    title: '',
    description: '',
    complaint_type: '',
    priority: '',
    status: 'open',
    source: '',
    customer_name: '',
    customer_email: '',
    customer_phone: '',
    customer_address: '',
    product_name: '',
    order_number: '',
    incident_date: new Date().toISOString().split('T')[0],
    expected_resolution: '',
    assigned_to: '',
});

const submit = () => {
    form.post(route('customer-service.complaints.store'), {
        onSuccess: () => {
            // Handle success
        },
    });
};

const users = ref(props.users || []);

const breadcrumbs = [
    { name: 'Dashboard', href: route('dashboard') },
    { name: 'Customer Service', href: route('customer-service.index') },
    { name: 'Complaints', href: route('customer-service.complaints.index') },
    { name: 'Create Complaint', href: '#' },
];
</script>
