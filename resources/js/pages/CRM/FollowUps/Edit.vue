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
    id: string;
    followUp?: any;
    prospects?: any[];
    assignedUsers?: any[];
}>();
</script>

<template>

    <Head title="Edit Follow-up" />

    <AppLayout>
        <div class="container mx-auto py-6">
            <div class="flex items-center gap-4 mb-6">
                <Link :href="route('crm.follow-ups.show', id)"
                    class="flex items-center gap-2 text-sm text-muted-foreground hover:text-foreground">
                <ArrowLeft class="h-4 w-4" />
                Back to Follow-up Details
                </Link>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Edit Follow-up</CardTitle>
                    <CardDescription>
                        Update follow-up information
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <Label for="title">Title</Label>
                                <Input id="title" :value="followUp?.title" placeholder="Enter follow-up title" />
                            </div>

                            <div class="space-y-2">
                                <Label for="due_date">Due Date</Label>
                                <Input id="due_date" type="datetime-local" :value="followUp?.due_date" />
                            </div>

                            <div class="space-y-2">
                                <Label for="priority">Priority</Label>
                                <Select :value="followUp?.priority">
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
                                <Select :value="followUp?.type">
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
                        </div>

                        <div class="space-y-2">
                            <Label for="description">Description</Label>
                            <Textarea id="description" :value="followUp?.description"
                                placeholder="Enter follow-up description" rows="4" />
                        </div>

                        <div class="flex justify-end gap-4">
                            <Link :href="route('crm.follow-ups.show', id)">
                            <Button variant="outline">Cancel</Button>
                            </Link>
                            <Button type="submit">Update Follow-up</Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
