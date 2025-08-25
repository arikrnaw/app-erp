<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div>
                <h3 class="text-lg font-medium">System Configuration</h3>
                <p class="text-sm text-muted-foreground">
                    Configure advanced system settings, security, and maintenance options.
                </p>
            </div>

            <div class="space-y-6">
                <!-- Database Configuration -->
                <div class="space-y-4">
                    <h4 class="text-md font-medium">Database Configuration</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label for="db-host">Database Host</Label>
                            <Input id="db-host" v-model="form.db_host" placeholder="localhost" />
                        </div>
                        <div class="space-y-2">
                            <Label for="db-port">Database Port</Label>
                            <Input id="db-port" v-model="form.db_port" placeholder="3306" />
                        </div>
                        <div class="space-y-2">
                            <Label for="db-name">Database Name</Label>
                            <Input id="db-name" v-model="form.db_name" placeholder="erp_system" />
                        </div>
                        <div class="space-y-2">
                            <Label for="db-connection">Connection Status</Label>
                            <div class="flex items-center space-x-2">
                                <div class="w-2 h-2 rounded-full"
                                    :class="dbStatus === 'connected' ? 'bg-green-500' : 'bg-red-500'"></div>
                                <span class="text-sm">{{ dbStatus === 'connected' ? 'Connected' : 'Disconnected'
                                }}</span>
                            </div>
                        </div>
                    </div>
                    <Button variant="outline" @click="testDatabaseConnection">
                        Test Connection
                    </Button>
                </div>

                <!-- Email Configuration -->
                <div class="space-y-4">
                    <h4 class="text-md font-medium">Email Configuration</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label for="smtp-host">SMTP Host</Label>
                            <Input id="smtp-host" v-model="form.smtp_host" placeholder="smtp.gmail.com" />
                        </div>
                        <div class="space-y-2">
                            <Label for="smtp-port">SMTP Port</Label>
                            <Input id="smtp-port" v-model="form.smtp_port" placeholder="587" />
                        </div>
                        <div class="space-y-2">
                            <Label for="smtp-username">SMTP Username</Label>
                            <Input id="smtp-username" v-model="form.smtp_username" placeholder="your-email@gmail.com" />
                        </div>
                        <div class="space-y-2">
                            <Label for="smtp-password">SMTP Password</Label>
                            <Input id="smtp-password" v-model="form.smtp_password" type="password"
                                placeholder="••••••••" />
                        </div>
                    </div>
                    <div class="space-y-2">
                        <Label for="from-email">From Email Address</Label>
                        <Input id="from-email" v-model="form.from_email" type="email"
                            placeholder="noreply@company.com" />
                    </div>
                    <Button variant="outline" @click="testEmailConfiguration">
                        Test Email Configuration
                    </Button>
                </div>

                <!-- Security Settings -->
                <div class="space-y-4">
                    <h4 class="text-md font-medium">Security Settings</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label for="session-timeout">Session Timeout (minutes)</Label>
                            <Input id="session-timeout" v-model="form.session_timeout" type="number" min="15"
                                max="1440" />
                        </div>
                        <div class="space-y-2">
                            <Label for="max-login-attempts">Max Login Attempts</Label>
                            <Input id="max-login-attempts" v-model="form.max_login_attempts" type="number" min="3"
                                max="10" />
                        </div>
                        <div class="space-y-2">
                            <Label for="password-min-length">Minimum Password Length</Label>
                            <Input id="password-min-length" v-model="form.password_min_length" type="number" min="8"
                                max="20" />
                        </div>
                        <div class="space-y-2">
                            <Label for="lockout-duration">Lockout Duration (minutes)</Label>
                            <Input id="lockout-duration" v-model="form.lockout_duration" type="number" min="5"
                                max="60" />
                        </div>
                    </div>
                    <div class="space-y-3">
                        <div class="flex items-center space-x-2">
                            <Checkbox id="require-2fa" v-model:checked="form.require_2fa" />
                            <Label for="require-2fa">Require Two-Factor Authentication</Label>
                        </div>
                        <div class="flex items-center space-x-2">
                            <Checkbox id="force-ssl" v-model:checked="form.force_ssl" />
                            <Label for="force-ssl">Force SSL/TLS Connections</Label>
                        </div>
                        <div class="flex items-center space-x-2">
                            <Checkbox id="audit-logging" v-model:checked="form.audit_logging" />
                            <Label for="audit-logging">Enable Audit Logging</Label>
                        </div>
                    </div>
                </div>

                <!-- System Maintenance -->
                <div class="space-y-4">
                    <h4 class="text-md font-medium">System Maintenance</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label for="backup-frequency">Backup Frequency</Label>
                            <Select v-model="form.backup_frequency">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select frequency" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="daily">Daily</SelectItem>
                                    <SelectItem value="weekly">Weekly</SelectItem>
                                    <SelectItem value="monthly">Monthly</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-2">
                            <Label for="backup-retention">Backup Retention (days)</Label>
                            <Input id="backup-retention" v-model="form.backup_retention" type="number" min="7"
                                max="365" />
                        </div>
                        <div class="space-y-2">
                            <Label for="log-retention">Log Retention (days)</Label>
                            <Input id="log-retention" v-model="form.log_retention" type="number" min="30" max="1095" />
                        </div>
                        <div class="space-y-2">
                            <Label for="maintenance-window">Maintenance Window</Label>
                            <Select v-model="form.maintenance_window">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select time" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="00:00-02:00">12:00 AM - 2:00 AM</SelectItem>
                                    <SelectItem value="02:00-04:00">2:00 AM - 4:00 AM</SelectItem>
                                    <SelectItem value="04:00-06:00">4:00 AM - 6:00 AM</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                    <div class="flex space-x-2">
                        <Button variant="outline" @click="clearLogs">
                            Clear Logs
                        </Button>
                        <Button variant="outline" @click="optimizeDatabase">
                            Optimize Database
                        </Button>
                        <Button variant="outline" @click="runMaintenance">
                            Run Maintenance
                        </Button>
                    </div>
                </div>

                <!-- System Information -->
                <div class="space-y-4">
                    <h4 class="text-md font-medium">System Information</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label>PHP Version</Label>
                            <div class="text-sm text-muted-foreground">{{ systemInfo.php_version }}</div>
                        </div>
                        <div class="space-y-2">
                            <Label>Laravel Version</Label>
                            <div class="text-sm text-muted-foreground">{{ systemInfo.laravel_version }}</div>
                        </div>
                        <div class="space-y-2">
                            <Label>Database Version</Label>
                            <div class="text-sm text-muted-foreground">{{ systemInfo.db_version }}</div>
                        </div>
                        <div class="space-y-2">
                            <Label>Server Uptime</Label>
                            <div class="text-sm text-muted-foreground">{{ systemInfo.uptime }}</div>
                        </div>
                    </div>
                </div>

                <!-- Save Button -->
                <div class="flex justify-end">
                    <Button @click="saveConfiguration" :disabled="loading">
                        <span v-if="loading">Saving...</span>
                        <span v-else>Save Configuration</span>
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
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Checkbox } from '@/components/ui/checkbox';
import AppLayout from '@/layouts/AppLayout.vue';

const breadcrumbs = [
    { title: 'Settings', href: '/settings/rbac' },
    { title: 'System Configuration', href: '/settings/rbac/system' }
];

const loading = ref(false);
const dbStatus = ref('connected');

const form = reactive({
    // Database
    db_host: 'localhost',
    db_port: '3306',
    db_name: 'erp_system',

    // Email
    smtp_host: 'smtp.gmail.com',
    smtp_port: '587',
    smtp_username: '',
    smtp_password: '',
    from_email: 'noreply@company.com',

    // Security
    session_timeout: 120,
    max_login_attempts: 5,
    password_min_length: 8,
    lockout_duration: 15,
    require_2fa: false,
    force_ssl: true,
    audit_logging: true,

    // Maintenance
    backup_frequency: 'daily',
    backup_retention: 30,
    log_retention: 90,
    maintenance_window: '02:00-04:00'
});

const systemInfo = reactive({
    php_version: '8.2.0',
    laravel_version: '10.0.0',
    db_version: 'MySQL 8.0.0',
    uptime: '15 days, 8 hours'
});

const testDatabaseConnection = async () => {
    try {
        // TODO: Implement actual database connection test
        await new Promise(resolve => setTimeout(resolve, 1000));
        dbStatus.value = 'connected';
        window.toast?.success('Database connection successful');
    } catch (error) {
        dbStatus.value = 'disconnected';
        window.toast?.error('Database connection failed');
    }
};

const testEmailConfiguration = async () => {
    try {
        // TODO: Implement actual email configuration test
        await new Promise(resolve => setTimeout(resolve, 1000));
        window.toast?.success('Email configuration test successful');
    } catch (error) {
        window.toast?.error('Email configuration test failed');
    }
};

const clearLogs = async () => {
    if (!confirm('Are you sure you want to clear all system logs? This action cannot be undone.')) return;

    try {
        // TODO: Implement actual log clearing
        await new Promise(resolve => setTimeout(resolve, 1000));
        window.toast?.success('System logs cleared successfully');
    } catch (error) {
        window.toast?.error('Failed to clear system logs');
    }
};

const optimizeDatabase = async () => {
    try {
        // TODO: Implement actual database optimization
        await new Promise(resolve => setTimeout(resolve, 2000));
        window.toast?.success('Database optimized successfully');
    } catch (error) {
        window.toast?.error('Failed to optimize database');
    }
};

const runMaintenance = async () => {
    try {
        // TODO: Implement actual maintenance tasks
        await new Promise(resolve => setTimeout(resolve, 3000));
        window.toast?.success('System maintenance completed successfully');
    } catch (error) {
        window.toast?.error('System maintenance failed');
    }
};

const saveConfiguration = async () => {
    loading.value = true;
    try {
        // TODO: Implement API call to save configuration
        await new Promise(resolve => setTimeout(resolve, 1000));
        window.toast?.success('Configuration saved successfully');
    } catch (error) {
        window.toast?.error('Failed to save configuration');
    } finally {
        loading.value = false;
    }
};
</script>
