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
import { ref } from 'vue';

interface ProjectTask {
    id: number;
    name: string;
    description: string;
    status: string;
    priority: string;
    type: string;
    project_id: number;
    parent_task_id?: number;
    assigned_to?: number;
    start_date: string;
    due_date: string;
    estimated_hours: number;
    actual_hours: number;
    progress_percentage: number;
}

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
    task: ProjectTask;
    projects: Project[];
    users: User[];
    parentTasks?: ProjectTask[];
}>();

const form = useForm({
    name: props.task?.name || '',
    description: props.task?.description || '',
    status: props.task?.status || 'todo',
    priority: props.task?.priority || 'medium',
    type: props.task?.type || 'feature',
    project_id: props.task?.project_id || '',
    parent_task_id: props.task?.parent_task_id || '',
    assigned_to: props.task?.assigned_to || '',
    start_date: props.task?.start_date || '',
    due_date: props.task?.due_date || '',
    estimated_hours: props.task?.estimated_hours || 0,
    actual_hours: props.task?.actual_hours || 0,
    progress_percentage: props.task?.progress_percentage || 0,
});

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Project Management', href: '/projects' },
    { title: 'Tasks', href: '/projects/tasks' },
    { title: props.task?.name || 'Edit Task', href: '#' },
];

const submit = () => {
    form.put(route('projects.tasks.update', props.task?.id));
};
</script>

<template>

    <Head title="Edit Task" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link :href="route('projects.tasks.show', task?.id)">
                    <Button variant="outline" size="sm">
                        <ArrowLeft class="mr-2 h-4 w-4" />
                        Back to Task
                    </Button>
                    </Link>
                    <div>
                        <h1 class="text-3xl font-bold tracking-tight">Edit Task</h1>
                        <p class="text-muted-foreground">
                            Update task information and details
                        </p>
                    </div>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Basic Information -->
                <Card>
                    <CardHeader>
                        <CardTitle>Basic Information</CardTitle>
                        <CardDescription>
                            Update the basic task information
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="name">Task Name *</Label>
                                <Input id="name" v-model="form.name" placeholder="Enter task name"
                                    :class="{ 'border-red-500': form.errors.name }" />
                                <p v-if="form.errors.name" class="text-sm text-red-500">{{ form.errors.name }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="type">Task Type</Label>
                                <Select v-model="form.type">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select task type" />
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
                                <p v-if="form.errors.type" class="text-sm text-red-500">{{ form.errors.type }}</p>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="description">Description</Label>
                            <Textarea id="description" v-model="form.description" placeholder="Enter task description"
                                rows="4" :class="{ 'border-red-500': form.errors.description }" />
                            <p v-if="form.errors.description" class="text-sm text-red-500">{{ form.errors.description }}
                            </p>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="status">Status</Label>
                                <Select v-model="form.status">
                                    <SelectTrigger>
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
                                <p v-if="form.errors.status" class="text-sm text-red-500">{{ form.errors.status }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="priority">Priority</Label>
                                <Select v-model="form.priority">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select priority" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="low">Low</SelectItem>
                                        <SelectItem value="medium">Medium</SelectItem>
                                        <SelectItem value="high">High</SelectItem>
                                        <SelectItem value="urgent">Urgent</SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.priority" class="text-sm text-red-500">{{ form.errors.priority }}
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Project Assignment -->
                <Card>
                    <CardHeader>
                        <CardTitle>Project Assignment</CardTitle>
                        <CardDescription>
                            Assign this task to a project and team member
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="project">Project *</Label>
                                <Select v-model="form.project_id">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select project" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="project in projects" :key="project.id" :value="project.id">
                                            {{ project.name }} ({{ project.code }})
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.project_id" class="text-sm text-red-500">{{ form.errors.project_id
                                }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="assigned_to">Assigned To</Label>
                                <Select v-model="form.assigned_to">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select team member" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="">Unassigned</SelectItem>
                                        <SelectItem v-for="user in users" :key="user.id" :value="user.id">
                                            {{ user.name }} ({{ user.email }})
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.assigned_to" class="text-sm text-red-500">{{
                                    form.errors.assigned_to }}</p>
                            </div>
                        </div>

                        <div class="space-y-2" v-if="parentTasks && parentTasks.length > 0">
                            <Label for="parent_task">Parent Task</Label>
                            <Select v-model="form.parent_task_id">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select parent task (optional)" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">No parent task</SelectItem>
                                    <SelectItem v-for="parentTask in parentTasks" :key="parentTask.id"
                                        :value="parentTask.id">
                                        {{ parentTask.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="form.errors.parent_task_id" class="text-sm text-red-500">{{
                                form.errors.parent_task_id }}</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Timeline -->
                <Card>
                    <CardHeader>
                        <CardTitle>Timeline</CardTitle>
                        <CardDescription>
                            Set task timeline and deadlines
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="start_date">Start Date</Label>
                                <Input id="start_date" v-model="form.start_date" type="date"
                                    :class="{ 'border-red-500': form.errors.start_date }" />
                                <p v-if="form.errors.start_date" class="text-sm text-red-500">{{ form.errors.start_date
                                }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="due_date">Due Date</Label>
                                <Input id="due_date" v-model="form.due_date" type="date"
                                    :class="{ 'border-red-500': form.errors.due_date }" />
                                <p v-if="form.errors.due_date" class="text-sm text-red-500">{{ form.errors.due_date }}
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Hours & Progress -->
                <Card>
                    <CardHeader>
                        <CardTitle>Hours & Progress</CardTitle>
                        <CardDescription>
                            Track estimated and actual hours, and progress
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid gap-4 md:grid-cols-3">
                            <div class="space-y-2">
                                <Label for="estimated_hours">Estimated Hours</Label>
                                <Input id="estimated_hours" v-model="form.estimated_hours" type="number" min="0"
                                    step="0.5" placeholder="0"
                                    :class="{ 'border-red-500': form.errors.estimated_hours }" />
                                <p v-if="form.errors.estimated_hours" class="text-sm text-red-500">{{
                                    form.errors.estimated_hours }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="actual_hours">Actual Hours</Label>
                                <Input id="actual_hours" v-model="form.actual_hours" type="number" min="0" step="0.5"
                                    placeholder="0" :class="{ 'border-red-500': form.errors.actual_hours }" />
                                <p v-if="form.errors.actual_hours" class="text-sm text-red-500">{{
                                    form.errors.actual_hours }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="progress_percentage">Progress (%)</Label>
                                <Input id="progress_percentage" v-model="form.progress_percentage" type="number" min="0"
                                    max="100" step="1" placeholder="0"
                                    :class="{ 'border-red-500': form.errors.progress_percentage }" />
                                <p v-if="form.errors.progress_percentage" class="text-sm text-red-500">{{
                                    form.errors.progress_percentage }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Actions -->
                <div class="flex justify-end gap-4">
                    <Link :href="route('projects.tasks.show', task?.id)">
                    <Button type="button" variant="outline">
                        Cancel
                    </Button>
                    </Link>
                    <Button type="submit" :disabled="form.processing">
                        <Save v-if="!form.processing" class="mr-2 h-4 w-4" />
                        {{ form.processing ? 'Saving...' : 'Save Changes' }}
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
