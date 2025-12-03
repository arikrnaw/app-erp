<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { ArrowLeft, Edit } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';

defineProps<{
    id: string;
    ticket?: any;
}>();
</script>

<template>

    <Head title="Support Ticket Details" />

    <AppLayout>
        <div class="container mx-auto py-6">
            <div class="flex items-center justify-between mb-6">
                <Link :href="route('crm.support-tickets.index')"
                    class="flex items-center gap-2 text-sm text-muted-foreground hover:text-foreground">
                <ArrowLeft class="h-4 w-4" />
                Back to Support Tickets
                </Link>
                <Link :href="route('crm.support-tickets.edit', id)">
                <Button variant="outline" size="sm">
                    <Edit class="h-4 w-4 mr-2" />
                    Edit
                </Button>
                </Link>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>{{ ticket?.title || 'Support Ticket Details' }}</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Status</label>
                            <Badge :variant="ticket?.status === 'open' ? 'default' : 'secondary'">
                                {{ ticket?.status || 'Open' }}
                            </Badge>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Priority</label>
                            <Badge :variant="ticket?.priority === 'high' ? 'destructive' : 'secondary'">
                                {{ ticket?.priority || 'Medium' }}
                            </Badge>
                        </div>
                    </div>
                    <div class="mt-4">
                        <label class="text-sm font-medium text-muted-foreground">Description</label>
                        <p class="mt-1 text-sm text-muted-foreground">{{ ticket?.description || `No description
                            available` }}</p>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
