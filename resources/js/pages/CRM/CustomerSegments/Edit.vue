<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { ArrowLeft } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';

defineProps<{
    id: string;
    segment?: any;
}>();
</script>

<template>

    <Head title="Edit Customer Segment" />

    <AppLayout>
        <div class="container mx-auto py-6">
            <div class="flex items-center gap-4 mb-6">
                <Link :href="route('crm.customer-segments.show', id)"
                    class="flex items-center gap-2 text-sm text-muted-foreground hover:text-foreground">
                <ArrowLeft class="h-4 w-4" />
                Back to Segment Details
                </Link>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Edit Customer Segment</CardTitle>
                    <CardDescription>
                        Update segment information
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <Label for="name">Name</Label>
                                <Input id="name" :value="segment?.name" placeholder="Enter segment name" />
                            </div>

                            <div class="space-y-2">
                                <Label for="code">Code</Label>
                                <Input id="code" :value="segment?.code" placeholder="Enter segment code" />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="description">Description</Label>
                            <Textarea id="description" :value="segment?.description"
                                placeholder="Enter segment description" rows="4" />
                        </div>

                        <div class="flex justify-end gap-4">
                            <Link :href="route('crm.customer-segments.show', id)">
                            <Button variant="outline">Cancel</Button>
                            </Link>
                            <Button type="submit">Update Segment</Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
