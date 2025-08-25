<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div>
                <h3 class="text-lg font-medium">General Settings</h3>
                <p class="text-sm text-muted-foreground">
                    Configure basic system settings and company information.
                </p>
            </div>

            <div class="space-y-6">
                <!-- Company Information -->
                <div class="space-y-4">
                    <h4 class="text-md font-medium">Company Information</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label for="company-name">Company Name</Label>
                            <Input id="company-name" v-model="form.company_name" placeholder="Enter company name" />
                        </div>
                        <div class="space-y-2">
                            <Label for="company-email">Company Email</Label>
                            <Input id="company-email" v-model="form.company_email" type="email"
                                placeholder="Enter company email" />
                        </div>
                        <div class="space-y-2">
                            <Label for="company-phone">Company Phone</Label>
                            <Input id="company-phone" v-model="form.company_phone" placeholder="Enter company phone" />
                        </div>
                        <div class="space-y-2">
                            <Label for="company-website">Company Website</Label>
                            <Input id="company-website" v-model="form.company_website"
                                placeholder="Enter company website" />
                        </div>
                    </div>
                    <div class="space-y-2">
                        <Label for="company-address">Company Address</Label>
                        <Textarea id="company-address" v-model="form.company_address"
                            placeholder="Enter company address" rows="3" />
                    </div>
                </div>

                <!-- System Settings -->
                <div class="space-y-4">
                    <h4 class="text-md font-medium">System Settings</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label for="timezone">Timezone</Label>
                            <Select v-model="form.timezone">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select timezone" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="UTC">UTC</SelectItem>
                                    <SelectItem value="America/New_York">Eastern Time</SelectItem>
                                    <SelectItem value="America/Chicago">Central Time</SelectItem>
                                    <SelectItem value="America/Denver">Mountain Time</SelectItem>
                                    <SelectItem value="America/Los_Angeles">Pacific Time</SelectItem>
                                    <SelectItem value="Europe/London">London</SelectItem>
                                    <SelectItem value="Europe/Paris">Paris</SelectItem>
                                    <SelectItem value="Asia/Tokyo">Tokyo</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-2">
                            <Label for="date-format">Date Format</Label>
                            <Select v-model="form.date_format">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select date format" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="Y-m-d">YYYY-MM-DD</SelectItem>
                                    <SelectItem value="m/d/Y">MM/DD/YYYY</SelectItem>
                                    <SelectItem value="d/m/Y">DD/MM/YYYY</SelectItem>
                                    <SelectItem value="M d, Y">Jan 01, 2024</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-2">
                            <Label for="time-format">Time Format</Label>
                            <Select v-model="form.time_format">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select time format" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="H:i">24-hour</SelectItem>
                                    <SelectItem value="h:i A">12-hour</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-2">
                            <Label for="currency">Default Currency</Label>
                            <Select v-model="form.currency">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select currency" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="USD">USD ($)</SelectItem>
                                    <SelectItem value="EUR">EUR (€)</SelectItem>
                                    <SelectItem value="GBP">GBP (£)</SelectItem>
                                    <SelectItem value="JPY">JPY (¥)</SelectItem>
                                    <SelectItem value="CAD">CAD (C$)</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                </div>

                <!-- Notification Settings -->
                <div class="space-y-4">
                    <h4 class="text-md font-medium">Notification Settings</h4>
                    <div class="space-y-3">
                        <div class="flex items-center space-x-2">
                            <Checkbox id="email-notifications" v-model:checked="form.email_notifications" />
                            <Label for="email-notifications">Enable email notifications</Label>
                        </div>
                        <div class="flex items-center space-x-2">
                            <Checkbox id="sms-notifications" v-model:checked="form.sms_notifications" />
                            <Label for="sms-notifications">Enable SMS notifications</Label>
                        </div>
                        <div class="flex items-center space-x-2">
                            <Checkbox id="browser-notifications" v-model:checked="form.browser_notifications" />
                            <Label for="browser-notifications">Enable browser notifications</Label>
                        </div>
                    </div>
                </div>

                <!-- Save Button -->
                <div class="flex justify-end">
                    <Button @click="saveSettings" :disabled="loading">
                        <span v-if="loading">Saving...</span>
                        <span v-else>Save Settings</span>
                    </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Checkbox } from '@/components/ui/checkbox';
import AppLayout from '@/layouts/AppLayout.vue';
// Toast notifications are available globally via window.toast

const breadcrumbs = [
    { title: 'Settings', href: '/settings/rbac' },
    { title: 'General Settings', href: '/settings/rbac/general' }
];

const loading = ref(false);

const form = reactive({
    company_name: '',
    company_email: '',
    company_phone: '',
    company_website: '',
    company_address: '',
    timezone: 'UTC',
    date_format: 'Y-m-d',
    time_format: 'H:i',
    currency: 'USD',
    email_notifications: true,
    sms_notifications: false,
    browser_notifications: true,
});

const saveSettings = async () => {
    loading.value = true;
    try {
        // TODO: Implement API call to save settings
        await new Promise(resolve => setTimeout(resolve, 1000)); // Simulate API call
        window.toast?.success('Settings saved successfully');
    } catch (error) {
        window.toast?.error('Failed to save settings. Please try again.');
    } finally {
        loading.value = false;
    }
};
</script>
