<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div>
                <h3 class="text-lg font-medium">Backup & Restore</h3>
                <p class="text-sm text-muted-foreground">
                    Manage system backups, restore data, and configure backup schedules.
                </p>
            </div>

            <div class="space-y-6">
                <!-- Manual Backup -->
                <div class="space-y-4">
                    <h4 class="text-md font-medium">Manual Backup</h4>
                    <div class="p-4 border rounded-lg bg-muted/50">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-muted-foreground mb-2">
                                    Create a manual backup of your system data including database and files.
                                </p>
                                <div class="flex items-center space-x-4 text-sm">
                                    <span>Last backup: {{ lastBackupDate || 'Never' }}</span>
                                    <span>Backup size: {{ lastBackupSize || 'N/A' }}</span>
                                </div>
                            </div>
                            <Button @click="createBackup" :disabled="creatingBackup">
                                <span v-if="creatingBackup">Creating...</span>
                                <span v-else>Create Backup</span>
                            </Button>
                        </div>
                    </div>
                </div>

                <!-- Backup Configuration -->
                <div class="space-y-4">
                    <h4 class="text-md font-medium">Backup Configuration</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label for="backup-type">Backup Type</Label>
                            <Select v-model="config.backup_type">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select backup type" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="full">Full Backup (Database + Files)</SelectItem>
                                    <SelectItem value="database">Database Only</SelectItem>
                                    <SelectItem value="files">Files Only</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-2">
                            <Label for="compression">Compression</Label>
                            <Select v-model="config.compression">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select compression" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="none">No Compression</SelectItem>
                                    <SelectItem value="gzip">Gzip</SelectItem>
                                    <SelectItem value="zip">ZIP</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-2">
                            <Label for="storage-location">Storage Location</Label>
                            <Select v-model="config.storage_location">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select storage location" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="local">Local Storage</SelectItem>
                                    <SelectItem value="s3">Amazon S3</SelectItem>
                                    <SelectItem value="gcs">Google Cloud Storage</SelectItem>
                                    <SelectItem value="azure">Azure Blob Storage</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-2">
                            <Label for="encryption">Encryption</Label>
                            <Select v-model="config.encryption">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select encryption" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="none">No Encryption</SelectItem>
                                    <SelectItem value="aes256">AES-256</SelectItem>
                                    <SelectItem value="aes128">AES-128</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                </div>

                <!-- Scheduled Backups -->
                <div class="space-y-4">
                    <h4 class="text-md font-medium">Scheduled Backups</h4>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 border rounded-lg">
                            <div class="flex items-center space-x-3">
                                <Checkbox id="enable-scheduled" v-model:checked="config.enable_scheduled" />
                                <div>
                                    <Label for="enable-scheduled" class="font-medium">Enable Scheduled Backups</Label>
                                    <p class="text-sm text-muted-foreground">
                                        Automatically create backups based on schedule
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div v-if="config.enable_scheduled" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="space-y-2">
                                <Label for="schedule-frequency">Frequency</Label>
                                <Select v-model="config.schedule_frequency">
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
                                <Label for="schedule-time">Time</Label>
                                <Input id="schedule-time" v-model="config.schedule_time" type="time" />
                            </div>
                            <div class="space-y-2">
                                <Label for="schedule-day">Day (Weekly/Monthly)</Label>
                                <Select v-model="config.schedule_day">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select day" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="monday">Monday</SelectItem>
                                        <SelectItem value="tuesday">Tuesday</SelectItem>
                                        <SelectItem value="wednesday">Wednesday</SelectItem>
                                        <SelectItem value="thursday">Thursday</SelectItem>
                                        <SelectItem value="friday">Friday</SelectItem>
                                        <SelectItem value="saturday">Saturday</SelectItem>
                                        <SelectItem value="sunday">Sunday</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Backup History -->
                <div class="space-y-4">
                    <h4 class="text-md font-medium">Backup History</h4>
                    <div class="border rounded-lg">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Date</TableHead>
                                    <TableHead>Type</TableHead>
                                    <TableHead>Size</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead>Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="backup in backups" :key="backup.id">
                                    <TableCell>{{ formatDate(backup.created_at) }}</TableCell>
                                    <TableCell>
                                        <Badge :variant="getBackupTypeVariant(backup.type)">
                                            {{ backup.type }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>{{ formatFileSize(backup.size) }}</TableCell>
                                    <TableCell>
                                        <Badge :variant="backup.status === 'completed' ? 'default' : 'secondary'">
                                            {{ backup.status }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        <div class="flex items-center space-x-2">
                                            <Button variant="ghost" size="sm" @click="downloadBackup(backup)">
                                                <Download class="h-4 w-4" />
                                            </Button>
                                            <Button variant="ghost" size="sm" @click="restoreBackup(backup)">
                                                <RotateCcw class="h-4 w-4" />
                                            </Button>
                                            <Button variant="ghost" size="sm" @click="deleteBackup(backup)">
                                                <Trash2 class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </div>

                <!-- Restore Options -->
                <div class="space-y-4">
                    <h4 class="text-md font-medium">Restore Options</h4>
                    <div class="p-4 border rounded-lg bg-muted/50">
                        <div class="space-y-3">
                            <div class="flex items-center space-x-2">
                                <Checkbox id="restore-database" v-model:checked="restoreOptions.database" />
                                <Label for="restore-database">Restore Database</Label>
                            </div>
                            <div class="flex items-center space-x-2">
                                <Checkbox id="restore-files" v-model:checked="restoreOptions.files" />
                                <Label for="restore-files">Restore Files</Label>
                            </div>
                            <div class="flex items-center space-x-2">
                                <Checkbox id="restore-settings" v-model:checked="restoreOptions.settings" />
                                <Label for="restore-settings">Restore Settings</Label>
                            </div>
                            <div class="flex items-center space-x-2">
                                <Checkbox id="overwrite-existing" v-model:checked="restoreOptions.overwrite" />
                                <Label for="overwrite-existing">Overwrite Existing Data</Label>
                            </div>
                        </div>
                        <div class="mt-4">
                            <Label for="backup-file">Upload Backup File</Label>
                            <Input id="backup-file" type="file" accept=".zip,.sql,.tar.gz" class="mt-2" />
                        </div>
                    </div>
                </div>

                <!-- Save Configuration -->
                <div class="flex justify-end">
                    <Button @click="saveConfiguration" :disabled="saving">
                        <span v-if="saving">Saving...</span>
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
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Download, RotateCcw, Trash2 } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';

const breadcrumbs = [
    { title: 'Settings', href: '/settings/rbac' },
    { title: 'Backup & Restore', href: '/settings/rbac/backup' }
];

const creatingBackup = ref(false);
const saving = ref(false);
const lastBackupDate = ref('2024-01-15 02:00:00');
const lastBackupSize = ref('2.5 GB');

const config = reactive({
    backup_type: 'full',
    compression: 'gzip',
    storage_location: 'local',
    encryption: 'aes256',
    enable_scheduled: true,
    schedule_frequency: 'daily',
    schedule_time: '02:00',
    schedule_day: 'monday'
});

const restoreOptions = reactive({
    database: true,
    files: false,
    settings: false,
    overwrite: false
});

const backups = ref([
    {
        id: 1,
        created_at: '2024-01-15T02:00:00Z',
        type: 'full',
        size: 2684354560, // 2.5 GB
        status: 'completed'
    },
    {
        id: 2,
        created_at: '2024-01-14T02:00:00Z',
        type: 'database',
        size: 536870912, // 512 MB
        status: 'completed'
    },
    {
        id: 3,
        created_at: '2024-01-13T02:00:00Z',
        type: 'full',
        size: 2684354560, // 2.5 GB
        status: 'completed'
    }
]);

const createBackup = async () => {
    creatingBackup.value = true;
    try {
        // TODO: Implement actual backup creation
        await new Promise(resolve => setTimeout(resolve, 3000));

        const newBackup = {
            id: backups.value.length + 1,
            created_at: new Date().toISOString(),
            type: config.backup_type,
            size: Math.random() * 3000000000 + 500000000, // Random size between 500MB and 3.5GB
            status: 'completed'
        };

        backups.value.unshift(newBackup);
        lastBackupDate.value = new Date().toLocaleString();
        lastBackupSize.value = formatFileSize(newBackup.size);

        window.toast?.success('Backup created successfully');
    } catch (error) {
        window.toast?.error('Failed to create backup');
    } finally {
        creatingBackup.value = false;
    }
};

const downloadBackup = (backup: any) => {
    // TODO: Implement actual backup download
    window.toast?.success(`Downloading ${backup.type} backup...`);
};

const restoreBackup = async (backup: any) => {
    if (!confirm(`Are you sure you want to restore from this backup? This action cannot be undone.`)) return;

    try {
        // TODO: Implement actual backup restoration
        await new Promise(resolve => setTimeout(resolve, 2000));
        window.toast?.success('Backup restored successfully');
    } catch (error) {
        window.toast?.error('Failed to restore backup');
    }
};

const deleteBackup = async (backup: any) => {
    if (!confirm(`Are you sure you want to delete this backup?`)) return;

    try {
        // TODO: Implement actual backup deletion
        await new Promise(resolve => setTimeout(resolve, 500));
        const index = backups.value.findIndex(b => b.id === backup.id);
        if (index > -1) {
            backups.value.splice(index, 1);
        }
        window.toast?.success('Backup deleted successfully');
    } catch (error) {
        window.toast?.error('Failed to delete backup');
    }
};

const saveConfiguration = async () => {
    saving.value = true;
    try {
        // TODO: Implement API call to save configuration
        await new Promise(resolve => setTimeout(resolve, 1000));
        window.toast?.success('Configuration saved successfully');
    } catch (error) {
        window.toast?.error('Failed to save configuration');
    } finally {
        saving.value = false;
    }
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleString();
};

const formatFileSize = (bytes: number) => {
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    if (bytes === 0) return '0 Bytes';
    const i = Math.floor(Math.log(bytes) / Math.log(1024));
    return Math.round(bytes / Math.pow(1024, i) * 100) / 100 + ' ' + sizes[i];
};

const getBackupTypeVariant = (type: string) => {
    switch (type) {
        case 'full': return 'default';
        case 'database': return 'secondary';
        case 'files': return 'outline';
        default: return 'secondary';
    }
};
</script>
