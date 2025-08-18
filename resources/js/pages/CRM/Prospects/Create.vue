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
    prospect?: any;
    sources?: any[];
    industries?: any[];
    assignedUsers?: any[];
}>();
</script>

<template>

    <Head title="Create Prospect" />

    <AppLayout>
        <div class="container mx-auto py-6">
            <div class="flex items-center gap-4 mb-6">
                <Link :href="route('crm.prospects.index')"
                    class="flex items-center gap-2 text-sm text-muted-foreground hover:text-foreground">
                <ArrowLeft class="h-4 w-4" />
                Back to Prospects
                </Link>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Create New Prospect</CardTitle>
                    <CardDescription>
                        Add a new prospect to your CRM system
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <Label for="name">Name</Label>
                                <Input id="name" placeholder="Enter prospect name" />
                            </div>

                            <div class="space-y-2">
                                <Label for="email">Email</Label>
                                <Input id="email" type="email" placeholder="Enter email address" />
                            </div>

                            <div class="space-y-2">
                                <Label for="phone">Phone</Label>
                                <Input id="phone" placeholder="Enter phone number" />
                            </div>

                            <div class="space-y-2">
                                <Label for="company">Company</Label>
                                <Input id="company" placeholder="Enter company name" />
                            </div>

                            <div class="space-y-2">
                                <Label for="source">Source</Label>
                                <Select>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select source" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="website">Website</SelectItem>
                                        <SelectItem value="referral">Referral</SelectItem>
                                        <SelectItem value="social-media">Social Media</SelectItem>
                                        <SelectItem value="cold-call">Cold Call</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div class="space-y-2">
                                <Label for="industry">Industry</Label>
                                <Select>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select industry" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="technology">Technology</SelectItem>
                                        <SelectItem value="healthcare">Healthcare</SelectItem>
                                        <SelectItem value="finance">Finance</SelectItem>
                                        <SelectItem value="retail">Retail</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="notes">Notes</Label>
                            <Textarea id="notes" placeholder="Enter additional notes" rows="4" />
                        </div>

                        <div class="flex justify-end gap-4">
                            <Link :href="route('crm.prospects.index')">
                            <Button variant="outline">Cancel</Button>
                            </Link>
                            <Button type="submit">Create Prospect</Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
