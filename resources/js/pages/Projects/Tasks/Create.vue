<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { ArrowLeft, Save, ClipboardList } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';

interface Project {
    id: number;
    name: string;
    code: string;
}

interface User {
    id: number;
    name: string;
    email: string;
}

const props = defineProps<{
    projects?: Project[];
    users?: User[];
}>();

const form = useForm({
    name: '',
    description: '',
    status: 'todo',
    priority: 'medium',
    type: 'feature',
    project_id: '',
    assigned_to: '',
    start_date: '',
    due_date: '',
    estimated_hours: 0,
});

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Project Management', href: '/projects' },
    { title: 'Tasks', href: '/projects/tasks' },
    { title: 'Create Task', href: '/projects/tasks/create' },
];

const submit = () => {
    form.post(route('projects.tasks.store'), {
        onSuccess: () => {
            // Redirect to tasks index
        },
    });
};
</script>

<template>

    <Head title="Create Task" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Create New Task</h1>
                    <p class="text-muted-foreground">
                        Add a new task to your project
                    </p>
                </div>
                <Link :href="route('projects.tasks.index')">
                <Button variant="outline">
                    <ArrowLeft class="mr-2 h-4 w-4" />
                    Back to Tasks
                </Button>
                </Link>
            </div>

            <!-- Task Form -->
            <form @submit.prevent="submit" class="space-y-6">
                <div class="grid gap-6 md:grid-cols-2">
                    <!-- Basic Information -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <ClipboardList class="h-5 w-5" />
                                Basic Information
                            </CardTitle>
                            <CardDescription>
                                Enter the basic details of your task
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="space-y-2">
                                <Label for="name">Task Name *</Label>
                                <Input id="name" v-model="form.name" placeholder="Enter task name"
                                    :class="{ 'border-red-500': form.errors.name }" />
                                <p v-if="form.errors.name" class="text-sm text-red-500">
                                    {{ form.errors.name }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="description">Description</Label>
                                <Textarea id="description" v-model="form.description"
                                    placeholder="Enter task description" rows="4"
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
                                            <SelectItem value="todo">To Do</SelectItem>
                                            <SelectItem value="in_progress">In Progress</SelectItem>
                                            <SelectItem value="review">Review</SelectItem>
                                            <SelectItem value="testing">Testing</SelectItem>
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

                            <div class="space-y-2">
                                <Label for="type">Task Type *</Label>
                                <Select v-model="form.type">
                                    <SelectTrigger :class="{ 'border-red-500': form.errors.type }">
                                        <SelectValue placeholder="Select type" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="feature">Feature</SelectItem>
                                        <SelectItem value="bug">Bug</SelectItem>
                                        <SelectItem value="improvement">Improvement</SelectItem>
                                        <SelectItem value="documentation">Documentation</SelectItem>
                                        <SelectItem value="testing">Testing</SelectItem>
                                        <SelectItem value="other">Other</SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.type" class="text-sm text-red-500">
                                    {{ form.errors.type }}
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Project & Assignment -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Project & Assignment</CardTitle>
                            <CardDescription>
                                Assign task to project and team member
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="space-y-2">
                                <Label for="project_id">Project *</Label>
                                <Select v-model="form.project_id">
                                    <SelectTrigger :class="{ 'border-red-500': form.errors.project_id }">
                                        <SelectValue placeholder="Select project" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="project in projects" :key="project.id"
                                            :value="project.id.toString()">
                                            {{ project.name }} ({{ project.code }})
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.project_id" class="text-sm text-red-500">
                                    {{ form.errors.project_id }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="assigned_to">Assigned To</Label>
                                <Select v-model="form.assigned_to">
                                    <SelectTrigger :class="{ 'border-red-500': form.errors.assigned_to }">
                                        <SelectValue placeholder="Select assignee" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="">Unassigned</SelectItem>
                                        <SelectItem v-for="user in users" :key="user.id" :value="user.id.toString()">
                                            {{ user.name }} ({{ user.email }})
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.assigned_to" class="text-sm text-red-500">
                                    {{ form.errors.assigned_to }}
                                </p>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <Label for="start_date">Start Date</Label>
                                    <Input id="start_date" v-model="form.start_date" type="date"
                                        :class="{ 'border-red-500': form.errors.start_date }" />
                                    <p v-if="form.errors.start_date" class="text-sm text-red-500">
                                        {{ form.errors.start_date }}
                                    </p>
                                </div>

                                <div class="space-y-2">
                                    <Label for="due_date">Due Date</Label>
                                    <Input id="due_date" v-model="form.due_date" type="date"
                                        :class="{ 'border-red-500': form.errors.due_date }" />
                                    <p v-if="form.errors.due_date" class="text-sm text-red-500">
                                        {{ form.errors.due_date }}
                                    </p>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <Label for="estimated_hours">Estimated Hours</Label>
                                <Input id="estimated_hours" v-model="form.estimated_hours" type="number" placeholder="0"
                                    :class="{ 'border-red-500': form.errors.estimated_hours }" />
                                <p v-if="form.errors.estimated_hours" class="text-sm text-red-500">
                                    {{ form.errors.estimated_hours }}
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Form Actions -->
                <div class="flex justify-end gap-4">
                    <Link :href="route('projects.tasks.index')">
                    <Button type="button" variant="outline">Cancel</Button>
                    </Link>
                    <Button type="submit" :disabled="form.processing">
                        <Save class="mr-2 h-4 w-4" />
                        {{ form.processing ? 'Creating...' : 'Create Task' }}
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
