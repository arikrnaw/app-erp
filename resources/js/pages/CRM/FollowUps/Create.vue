<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { ArrowLeft } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';

defineProps<{
    followUp?: any;
    prospects?: any[];
    assignedUsers?: any[];
}>();
</script>

<template>

    <Head title="Create Follow-up" />

    <AppLayout>
        <div class="container mx-auto py-6">
            <div class="flex items-center gap-4 mb-6">
                <Link :href="route('crm.follow-ups.index')"
                    class="flex items-center gap-2 text-sm text-muted-foreground hover:text-foreground">
                <ArrowLeft class="h-4 w-4" />
                Back to Follow-ups
                </Link>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Create New Follow-up</CardTitle>
                    <CardDescription>
                        Schedule a new follow-up task
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <Label for="title">Title</Label>
                                <Input id="title" placeholder="Enter follow-up title" />
                            </div>

                            <div class="space-y-2">
                                <Label for="prospect">Prospect</Label>
                                <Select>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select prospect" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="1">John Doe - ABC Company</SelectItem>
                                        <SelectItem value="2">Jane Smith - XYZ Corp</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div class="space-y-2">
                                <Label for="due_date">Due Date</Label>
                                <Input id="due_date" type="datetime-local" />
                            </div>

                            <div class="space-y-2">
                                <Label for="priority">Priority</Label>
                                <Select>
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
                            </div>

                            <div class="space-y-2">
                                <Label for="type">Type</Label>
                                <Select>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select type" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="call">Phone Call</SelectItem>
                                        <SelectItem value="email">Email</SelectItem>
                                        <SelectItem value="meeting">Meeting</SelectItem>
                                        <SelectItem value="demo">Demo</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div class="space-y-2">
                                <Label for="assigned_to">Assigned To</Label>
                                <Select>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select assignee" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="1">John Sales</SelectItem>
                                        <SelectItem value="2">Sarah Manager</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="description">Description</Label>
                            <Textarea id="description" placeholder="Enter follow-up description" rows="4" />
                        </div>

                        <div class="flex justify-end gap-4">
                            <Link :href="route('crm.follow-ups.index')">
                            <Button variant="outline">Cancel</Button>
                            </Link>
                            <Button type="submit">Create Follow-up</Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
