<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { ArrowLeft, Edit, Calendar, User, Phone, Mail } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';

defineProps<{
    id: string;
    followUp?: any;
}>();
</script>

<template>

    <Head title="Follow-up Details" />

    <AppLayout>
        <div class="container mx-auto py-6">
            <div class="flex items-center justify-between mb-6">
                <Link :href="route('crm.follow-ups.index')"
                    class="flex items-center gap-2 text-sm text-muted-foreground hover:text-foreground">
                <ArrowLeft class="h-4 w-4" />
                Back to Follow-ups
                </Link>
                <Link :href="route('crm.follow-ups.edit', id)">
                <Button variant="outline" size="sm">
                    <Edit class="h-4 w-4 mr-2" />
                    Edit
                </Button>
                </Link>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2">
                    <Card>
                        <CardHeader>
                            <CardTitle>{{ followUp?.title || 'Follow-up Details' }}</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="text-sm font-medium text-muted-foreground">Due Date</label>
                                    <div class="flex items-center gap-2">
                                        <Calendar class="h-4 w-4 text-muted-foreground" />
                                        <p class="text-lg font-semibold">{{ followUp?.due_date || 'N/A' }}</p>
                                    </div>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-muted-foreground">Priority</label>
                                    <Badge :variant="followUp?.priority === 'high' ? 'destructive' : 'secondary'">
                                        {{ followUp?.priority || 'N/A' }}
                                    </Badge>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-muted-foreground">Type</label>
                                    <Badge variant="outline">{{ followUp?.type || 'N/A' }}</Badge>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-muted-foreground">Status</label>
                                    <Badge :variant="followUp?.status === 'completed' ? 'default' : 'secondary'">
                                        {{ followUp?.status || 'Pending' }}
                                    </Badge>
                                </div>
                            </div>

                            <div>
                                <label class="text-sm font-medium text-muted-foreground">Description</label>
                                <p class="mt-1 text-sm text-muted-foreground">{{ followUp?.description || `No
                                    description available` }}</p>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <div class="space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-lg">Assigned To</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="flex items-center gap-2">
                                <User class="h-4 w-4 text-muted-foreground" />
                                <span>{{ followUp?.assigned_to || 'Unassigned' }}</span>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle class="text-lg">Prospect</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-2">
                                <p class="font-medium">{{ followUp?.prospect?.name || 'N/A' }}</p>
                                <p class="text-sm text-muted-foreground">{{ followUp?.prospect?.company || 'N/A' }}</p>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
