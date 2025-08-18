<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { ArrowLeft, Save, Target } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';

interface User {
    id: number;
    name: string;
    email: string;
}

interface Customer {
    id: number;
    name: string;
    email: string;
}

const props = defineProps<{
    users?: User[];
    customers?: Customer[];
}>();

const form = useForm({
    name: '',
    code: '',
    description: '',
    status: 'planning',
    priority: 'medium',
    start_date: '',
    end_date: '',
    budget: 0,
    project_manager_id: '',
    client_id: '',
    location: '',
    contact_person: '',
    contact_email: '',
    contact_phone: '',
});

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Project Management', href: '/projects' },
    { title: 'Projects', href: '/projects' },
    { title: 'Create Project', href: '/projects/create' },
];

const generateCode = async () => {
    try {
        const response = await fetch('/api/projects/generate-code', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
        });
        const data = await response.json();
        if (data.success) {
            form.code = data.data.code;
        }
    } catch (error) {
        console.error('Error generating code:', error);
    }
};

const submit = () => {
    form.post(route('projects.store'), {
        onSuccess: () => {
            // Redirect to projects index
        },
    });
};
</script>

<template>

    <Head title="Create Project" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Create New Project</h1>
                    <p class="text-muted-foreground">
                        Add a new project to your organization
                    </p>
                </div>
                <Link :href="route('projects.index')">
                <Button variant="outline">
                    <ArrowLeft class="mr-2 h-4 w-4" />
                    Back to Projects
                </Button>
                </Link>
            </div>

            <!-- Project Form -->
            <form @submit.prevent="submit" class="space-y-6">
                <div class="grid gap-6 md:grid-cols-2">
                    <!-- Basic Information -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Target class="h-5 w-5" />
                                Basic Information
                            </CardTitle>
                            <CardDescription>
                                Enter the basic details of your project
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="space-y-2">
                                <Label for="name">Project Name *</Label>
                                <Input id="name" v-model="form.name" placeholder="Enter project name"
                                    :class="{ 'border-red-500': form.errors.name }" />
                                <p v-if="form.errors.name" class="text-sm text-red-500">
                                    {{ form.errors.name }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="code">Project Code *</Label>
                                <div class="flex gap-2">
                                    <Input id="code" v-model="form.code" placeholder="Enter project code"
                                        :class="{ 'border-red-500': form.errors.code }" />
                                    <Button type="button" variant="outline" @click="generateCode">
                                        Generate
                                    </Button>
                                </div>
                                <p v-if="form.errors.code" class="text-sm text-red-500">
                                    {{ form.errors.code }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="description">Description</Label>
                                <Textarea id="description" v-model="form.description"
                                    placeholder="Enter project description" rows="4"
                                    :class="{ 'border-red-500': form.errors.description }" />
                                <p v-if="form.errors.description" class="text-sm text-red-500">
                                    {{ form.errors.description }}
                                </p>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <Label for="status">Status *</Label>
                                    <Select v-model="form.status">
                                        <SelectTrigger :class="{ 'border-red-500': form.errors.status }">
                                            <SelectValue placeholder="Select status" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="planning">Planning</SelectItem>
                                            <SelectItem value="active">Active</SelectItem>
                                            <SelectItem value="on_hold">On Hold</SelectItem>
                                            <SelectItem value="completed">Completed</SelectItem>
                                            <SelectItem value="cancelled">Cancelled</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <p v-if="form.errors.status" class="text-sm text-red-500">
                                        {{ form.errors.status }}
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
                                            <SelectItem value="urgent">Urgent</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <p v-if="form.errors.priority" class="text-sm text-red-500">
                                        {{ form.errors.priority }}
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Timeline & Budget -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Timeline & Budget</CardTitle>
                            <CardDescription>
                                Set project timeline and budget information
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <Label for="start_date">Start Date *</Label>
                                    <Input id="start_date" v-model="form.start_date" type="date"
                                        :class="{ 'border-red-500': form.errors.start_date }" />
                                    <p v-if="form.errors.start_date" class="text-sm text-red-500">
                                        {{ form.errors.start_date }}
                                    </p>
                                </div>

                                <div class="space-y-2">
                                    <Label for="end_date">End Date *</Label>
                                    <Input id="end_date" v-model="form.end_date" type="date"
                                        :class="{ 'border-red-500': form.errors.end_date }" />
                                    <p v-if="form.errors.end_date" class="text-sm text-red-500">
                                        {{ form.errors.end_date }}
                                    </p>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <Label for="budget">Budget (IDR)</Label>
                                <Input id="budget" v-model="form.budget" type="number" placeholder="0"
                                    :class="{ 'border-red-500': form.errors.budget }" />
                                <p v-if="form.errors.budget" class="text-sm text-red-500">
                                    {{ form.errors.budget }}
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Team & Contact -->
                <Card>
                    <CardHeader>
                        <CardTitle>Team & Contact Information</CardTitle>
                        <CardDescription>
                            Assign project manager and client information
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="project_manager_id">Project Manager *</Label>
                                <Select v-model="form.project_manager_id">
                                    <SelectTrigger :class="{ 'border-red-500': form.errors.project_manager_id }">
                                        <SelectValue placeholder="Select project manager" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="user in users" :key="user.id" :value="user.id.toString()">
                                            {{ user.name }} ({{ user.email }})
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.project_manager_id" class="text-sm text-red-500">
                                    {{ form.errors.project_manager_id }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="client_id">Client</Label>
                                <Select v-model="form.client_id">
                                    <SelectTrigger :class="{ 'border-red-500': form.errors.client_id }">
                                        <SelectValue placeholder="Select client" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="">No client</SelectItem>
                                        <SelectItem v-for="customer in customers" :key="customer.id"
                                            :value="customer.id.toString()">
                                            {{ customer.name }} ({{ customer.email }})
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.client_id" class="text-sm text-red-500">
                                    {{ form.errors.client_id }}
                                </p>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="location">Location</Label>
                            <Input id="location" v-model="form.location" placeholder="Enter project location"
                                :class="{ 'border-red-500': form.errors.location }" />
                            <p v-if="form.errors.location" class="text-sm text-red-500">
                                {{ form.errors.location }}
                            </p>
                        </div>

                        <div class="grid gap-4 md:grid-cols-3">
                            <div class="space-y-2">
                                <Label for="contact_person">Contact Person</Label>
                                <Input id="contact_person" v-model="form.contact_person"
                                    placeholder="Enter contact person name"
                                    :class="{ 'border-red-500': form.errors.contact_person }" />
                                <p v-if="form.errors.contact_person" class="text-sm text-red-500">
                                    {{ form.errors.contact_person }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="contact_email">Contact Email</Label>
                                <Input id="contact_email" v-model="form.contact_email" type="email"
                                    placeholder="Enter contact email"
                                    :class="{ 'border-red-500': form.errors.contact_email }" />
                                <p v-if="form.errors.contact_email" class="text-sm text-red-500">
                                    {{ form.errors.contact_email }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="contact_phone">Contact Phone</Label>
                                <Input id="contact_phone" v-model="form.contact_phone" placeholder="Enter contact phone"
                                    :class="{ 'border-red-500': form.errors.contact_phone }" />
                                <p v-if="form.errors.contact_phone" class="text-sm text-red-500">
                                    {{ form.errors.contact_phone }}
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Form Actions -->
                <div class="flex justify-end gap-4">
                    <Link :href="route('projects.index')">
                    <Button type="button" variant="outline">Cancel</Button>
                    </Link>
                    <Button type="submit" :disabled="form.processing">
                        <Save class="mr-2 h-4 w-4" />
                        {{ form.processing ? 'Creating...' : 'Create Project' }}
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
