<template>
    <Head title="Import & Matching" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 space-y-6">
            <!-- Header Section -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Import & Matching</h1>
                    <p class="text-muted-foreground mt-1">
                        Import bank statements and match transactions
                    </p>
                </div>
                <div class="flex items-center space-x-2">
                    <Button @click="showImportModal = true" variant="default">
                        <Upload class="h-4 w-4 mr-2" />
                        Import Statement
                    </Button>
                    <Button @click="showAutoMatchModal = true" variant="outline">
                        <Bot class="h-4 w-4 mr-2" />
                        Auto Match
                    </Button>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center space-x-4">
                            <div class="p-2 bg-blue-100 dark:bg-blue-900/20 rounded-lg">
                                <FileText class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-muted-foreground">Imported Statements</p>
                                <p class="text-2xl font-bold">{{ statistics.importedStatements }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center space-x-4">
                            <div class="p-2 bg-purple-100 dark:bg-purple-900/20 rounded-lg">
                                <BarChart3 class="h-6 w-6 text-purple-600 dark:text-purple-400" />
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-muted-foreground">Total Transactions</p>
                                <p class="text-2xl font-bold">{{ statistics.totalTransactions }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center space-x-4">
                            <div class="p-2 bg-green-100 dark:bg-green-900/20 rounded-lg">
                                <CheckCircle class="h-6 w-6 text-green-600 dark:text-green-400" />
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-muted-foreground">Matched</p>
                                <p class="text-2xl font-bold text-green-600">{{ statistics.matchedTransactions }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center space-x-4">
                            <div class="p-2 bg-orange-100 dark:bg-orange-900/20 rounded-lg">
                                <AlertTriangle class="h-6 w-6 text-orange-600 dark:text-orange-400" />
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-muted-foreground">Unmatched</p>
                                <p class="text-2xl font-bold text-orange-600">{{ statistics.unmatchedTransactions }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Import Instructions -->
            <Card>
                <CardHeader>
                    <CardTitle>Import Instructions</CardTitle>
                    <CardDescription>Follow these steps to import your bank statements</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <div class="flex items-start space-x-3">
                            <div
                                class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                <span class="text-sm font-medium text-blue-600">1</span>
                            </div>
                            <div>
                                <h4 class="font-medium">Prepare Your File</h4>
                                <p class="text-sm text-muted-foreground">Ensure your bank statement is in CSV, Excel, or PDF
                                    format</p>
                            </div>
                        </div>

                        <div class="flex items-start space-x-3">
                            <div
                                class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                <span class="text-sm font-medium text-blue-600">2</span>
                            </div>
                            <div>
                                <h4 class="font-medium">Upload Statement</h4>
                                <p class="text-sm text-muted-foreground">Click the Import button and select your file</p>
                            </div>
                        </div>

                        <div class="flex items-start space-x-3">
                            <div
                                class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                <span class="text-sm font-medium text-blue-600">3</span>
                            </div>
                            <div>
                                <h4 class="font-medium">Auto Match</h4>
                                <p class="text-sm text-muted-foreground">Use auto matching to automatically match transactions
                                </p>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Recent Imports -->
            <Card>
                <CardHeader>
                    <CardTitle>Recent Imports</CardTitle>
                    <CardDescription>Your recently imported bank statements</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <div v-for="import in recentImports" :key="import.id" class="p-4 border rounded-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="font-medium">{{ import.bank_account_name }}</h4>
                                    <p class="text-sm text-muted-foreground">{{ import.statement_date }} - {{
                                        import.transactions_count }} transactions</p>
                                    <div class="flex items-center space-x-2 mt-1">
                                        <Badge variant="default">{{ import.matched_count }} Matched</Badge>
                                        <Badge variant="secondary">{{ import.unmatched_count }} Unmatched</Badge>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm text-muted-foreground">{{ import.imported_at }}</p>
                                    <Badge :variant="import.status === 'completed' ? 'default' : 'secondary'">
                                        {{ import.status }}
                                    </Badge>
                                </div>
                            </div>
                        </div>

                        <div v-if="recentImports.length === 0" class="text-center py-8 text-muted-foreground">
                            <Upload class="h-12 w-12 mx-auto mb-4 text-gray-400" />
                            <p>No imports yet. Get started by importing your first bank statement.</p>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Import Modal -->
        <Dialog v-model:open="showImportModal">
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>Import Bank Statement</DialogTitle>
                    <DialogDescription>Upload a bank statement file to start reconciliation</DialogDescription>
                </DialogHeader>
                <div class="space-y-4">
                    <div class="space-y-2">
                        <Label for="import_account">Bank Account</Label>
                        <Select v-model="importData.bank_account_id">
                            <SelectTrigger>
                                <SelectValue placeholder="Select account" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="1">Bank BCA</SelectItem>
                                <SelectItem value="2">Bank Mandiri</SelectItem>
                                <SelectItem value="3">Bank BNI</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="space-y-2">
                        <Label for="statement_file">Statement File</Label>
                        <Input id="statement_file" type="file" accept=".csv,.xlsx,.pdf" @change="handleFileUpload" />
                        <p class="text-xs text-muted-foreground">Supported formats: CSV, Excel, PDF</p>
                    </div>

                    <div class="space-y-2">
                        <Label for="statement_date">Statement Date</Label>
                        <Input id="statement_date" type="date" v-model="importData.statement_date" required />
                    </div>

                    <DialogFooter>
                        <Button type="button" variant="outline" @click="showImportModal = false">
                            Cancel
                        </Button>
                        <Button @click="importStatement" :disabled="importing">
                            {{ importing ? 'Importing...' : 'Import Statement' }}
                        </Button>
                    </DialogFooter>
                </div>
            </DialogContent>
        </Dialog>

        <!-- Auto Match Modal -->
        <Dialog v-model:open="showAutoMatchModal">
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>Auto Match Transactions</DialogTitle>
                    <DialogDescription>
                        Automatically match transactions based on amount, date, and description similarity.
                    </DialogDescription>
                </DialogHeader>
                <div class="space-y-4">
                    <div class="space-y-2">
                        <Label for="confidence">Minimum Confidence Score (%)</Label>
                        <Input id="confidence" v-model="autoMatchSettings.confidence" type="number" min="0" max="100" />
                    </div>
                    <div class="space-y-2">
                        <Label for="dateTolerance">Date Tolerance (days)</Label>
                        <Input id="dateTolerance" v-model="autoMatchSettings.dateTolerance" type="number" min="0"
                            max="30" />
                    </div>
                </div>
                <DialogFooter>
                    <Button type="button" variant="outline" @click="showAutoMatchModal = false">
                        Cancel
                    </Button>
                    <Button @click="performAutoMatch" :disabled="autoMatching">
                        <Bot class="h-4 w-4 mr-2" />
                        {{ autoMatching ? 'Processing...' : 'Start Auto Match' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Upload, Bot, FileText, BarChart3, CheckCircle, AlertTriangle } from 'lucide-vue-next'
import { useApi } from '@/composables/useApi'
import AppLayout from '@/layouts/AppLayout.vue'

// Breadcrumbs
const breadcrumbs = [
    { title: 'Finance', href: '/finance' },
    { title: 'Bank Reconciliation', href: '/finance/bank-reconciliation' },
    { title: 'Import & Matching', href: '/finance/bank-reconciliation/import' }
]

const api = useApi()

// Reactive data
const importing = ref(false)
const autoMatching = ref(false)
const showImportModal = ref(false)
const showAutoMatchModal = ref(false)

// Form data
const importData = ref({
    bank_account_id: '',
    statement_date: '',
    file: null as File | null
})

const autoMatchSettings = ref({
    confidence: 80,
    dateTolerance: 3
})

// Data
const statistics = ref({
    importedStatements: 0,
    totalTransactions: 0,
    matchedTransactions: 0,
    unmatchedTransactions: 0,
    matchPercentage: 0
})

const recentImports = ref([])

// Methods
const fetchData = async () => {
    try {
        const [statsResponse, importsResponse] = await Promise.all([
            api.get('/finance/bank-reconciliation/import/statistics'),
            api.get('/finance/bank-reconciliation/import/recent')
        ])

        statistics.value = statsResponse.data
        recentImports.value = importsResponse.data
    } catch (error) {
        console.error('Error fetching data:', error)
        // Set default data for demo
        statistics.value = {
            importedStatements: 3,
            totalTransactions: 45,
            matchedTransactions: 38,
            unmatchedTransactions: 7,
            matchPercentage: 84
        }
        recentImports.value = [
            {
                id: 1,
                bank_account_name: 'Bank BCA',
                statement_date: '2024-01-15',
                transactions_count: 15,
                matched_count: 13,
                unmatched_count: 2,
                imported_at: '2024-01-16 10:30',
                status: 'completed'
            }
        ]
    }
}

const handleFileUpload = (event: Event) => {
    const target = event.target as HTMLInputElement
    if (target.files && target.files[0]) {
        importData.value.file = target.files[0]
    }
}

const importStatement = async () => {
    if (!importData.value.bank_account_id || !importData.value.statement_date || !importData.value.file) {
        return
    }

    try {
        importing.value = true

        // TODO: Implement actual import logic
        console.log('Importing statement:', importData.value)

        // Simulate API call
        await new Promise(resolve => setTimeout(resolve, 1000))

        showImportModal.value = false

        // Reset form
        importData.value = {
            bank_account_id: '',
            statement_date: '',
            file: null
        }

        // Refresh data
        await fetchData()

    } catch (error) {
        console.error('Error importing statement:', error)
    } finally {
        importing.value = false
    }
}

const performAutoMatch = async () => {
    try {
        autoMatching.value = true

        // TODO: Implement actual auto match logic
        console.log('Auto matching with settings:', autoMatchSettings.value)

        // Simulate API call
        await new Promise(resolve => setTimeout(resolve, 2000))

        showAutoMatchModal.value = false

        // Refresh data
        await fetchData()

    } catch (error) {
        console.error('Error performing auto match:', error)
    } finally {
        autoMatching.value = false
    }
}

// Lifecycle
onMounted(() => {
    fetchData()
})
</script>
