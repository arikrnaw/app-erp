<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { ArrowLeft, Edit, Phone, Mail, Building, Calendar } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';

defineProps<{
    id: string;
    prospect?: any;
}>();
</script>

<template>

    <Head title="Prospect Details" />

    <AppLayout>
        <div class="container mx-auto py-6">
            <div class="flex items-center justify-between mb-6">
                <Link :href="route('crm.prospects.index')"
                    class="flex items-center gap-2 text-sm text-muted-foreground hover:text-foreground">
                <ArrowLeft class="h-4 w-4" />
                Back to Prospects
                </Link>
                <Link :href="route('crm.prospects.edit', id)">
                <Button variant="outline" size="sm">
                    <Edit class="h-4 w-4 mr-2" />
                    Edit
                </Button>
                </Link>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Prospect Info -->
                <div class="lg:col-span-2">
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Building class="h-5 w-5" />
                                Prospect Information
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="text-sm font-medium text-muted-foreground">Name</label>
                                    <p class="text-lg font-semibold">{{ prospect?.name || 'N/A' }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-muted-foreground">Company</label>
                                    <p class="text-lg font-semibold">{{ prospect?.company || 'N/A' }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-muted-foreground">Email</label>
                                    <div class="flex items-center gap-2">
                                        <Mail class="h-4 w-4 text-muted-foreground" />
                                        <a :href="`mailto:${prospect?.email}`" class="text-blue-600 hover:underline">
                                            {{ prospect?.email || 'N/A' }}
                                        </a>
                                    </div>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-muted-foreground">Phone</label>
                                    <div class="flex items-center gap-2">
                                        <Phone class="h-4 w-4 text-muted-foreground" />
                                        <a :href="`tel:${prospect?.phone}`" class="text-blue-600 hover:underline">
                                            {{ prospect?.phone || 'N/A' }}
                                        </a>
                                    </div>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-muted-foreground">Source</label>
                                    <Badge variant="secondary">{{ prospect?.source || 'N/A' }}</Badge>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-muted-foreground">Industry</label>
                                    <Badge variant="outline">{{ prospect?.industry || 'N/A' }}</Badge>
                                </div>
                            </div>

                            <div>
                                <label class="text-sm font-medium text-muted-foreground">Notes</label>
                                <p class="mt-1 text-sm text-muted-foreground">{{ prospect?.notes || 'No notes available'
                                    }}</p>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-lg">Status</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <Badge :variant="prospect?.status === 'active' ? 'default' : 'secondary'">
                                {{ prospect?.status || 'Unknown' }}
                            </Badge>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle class="text-lg">Timeline</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="flex items-center gap-3">
                                <Calendar class="h-4 w-4 text-muted-foreground" />
                                <div>
                                    <p class="text-sm font-medium">Created</p>
                                    <p class="text-xs text-muted-foreground">{{ prospect?.created_at || 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <Calendar class="h-4 w-4 text-muted-foreground" />
                                <div>
                                    <p class="text-sm font-medium">Last Updated</p>
                                    <p class="text-xs text-muted-foreground">{{ prospect?.updated_at || 'N/A' }}</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
