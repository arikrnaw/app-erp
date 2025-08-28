<template>

    <Head title="Transaction Matching" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 space-y-6">
            <!-- Header Section -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Transaction Matching</h1>
                    <p class="text-muted-foreground mt-1">
                        Match bank transactions with book transactions
                    </p>
                </div>
                <div class="flex items-center space-x-2">
                    <Button @click="showAutoMatchModal = true">
                        <Bot class="h-4 w-4 mr-2" />
                        Auto Match
                    </Button>
                    <Button @click="showManualMatchModal = true">
                        <Search class="h-4 w-4 mr-2" />
                        Manual Match
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

                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center space-x-4">
                            <div class="p-2 bg-purple-100 dark:bg-purple-900/20 rounded-lg">
                                <TrendingUp class="h-6 w-6 text-purple-600 dark:text-purple-400" />
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-muted-foreground">Confidence Score</p>
                                <p class="text-2xl font-bold text-purple-600">{{ statistics.averageConfidence }}%</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Matching Interface -->
            <Card>
                <CardHeader>
                    <CardTitle>Transaction Matching</CardTitle>
                    <CardDescription>Match bank transactions with your book transactions</CardDescription>
                </CardHeader>
                <CardContent class="mb-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Bank Transactions -->
                        <div>
                            <h3 class="text-lg font-medium mb-4">Bank Transactions</h3>
                            <ScrollArea class="h-[calc(100vh-400px)]">
                                <div class="space-y-2 pr-4">
                                    <div v-for="transaction in bankTransactions" :key="transaction.id"
                                        class="p-3 border rounded-lg cursor-pointer hover:bg-muted/50 transition-colors"
                                        :class="{ 'border-blue-500 bg-blue-50': selectedBankTransaction?.id === transaction.id }"
                                        @click="selectBankTransaction(transaction)">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <p class="font-medium">{{ transaction.description }}</p>
                                                <p class="text-sm text-muted-foreground">{{ transaction.date }}</p>
                                            </div>
                                            <div class="text-right">
                                                <p class="font-medium"
                                                    :class="(transaction.amount || 0) > 0 ? 'text-green-600' : 'text-red-600'">
                                                    {{ formatCurrency(transaction.amount) }}
                                                </p>
                                                <Badge v-if="transaction.matched" variant="secondary" class="text-xs">
                                                    Matched
                                                </Badge>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </ScrollArea>
                        </div>

                        <!-- Book Transactions -->
                        <div>
                            <h3 class="text-lg font-medium mb-4">Book Transactions</h3>
                            <ScrollArea class="h-[calc(100vh-400px)]">
                                <div class="space-y-2 pr-4">
                                    <div v-for="transaction in bookTransactions" :key="transaction.id"
                                        class="p-3 border rounded-lg cursor-pointer hover:bg-muted/50 transition-colors"
                                        :class="{ 'border-green-500 bg-green-50': selectedBookTransaction?.id === transaction.id }"
                                        @click="selectBookTransaction(transaction)">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <p class="font-medium">{{ transaction.description }}</p>
                                                <p class="text-sm text-muted-foreground">{{ transaction.date }}</p>
                                            </div>
                                            <div class="text-right">
                                                <p class="font-medium"
                                                    :class="(transaction.amount || 0) > 0 ? 'text-green-600' : 'text-red-600'">
                                                    {{ formatCurrency(transaction.amount) }}
                                                </p>
                                                <Badge v-if="transaction.matched" variant="secondary" class="text-xs">
                                                    Matched
                                                </Badge>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </ScrollArea>
                        </div>
                    </div>

                    <!-- Match Button -->
                    <div class="mt-6 text-center">
                        <Button @click="matchTransactions" :disabled="!canMatch" size="lg">
                            <CheckCircle class="h-4 w-4 mr-2" />
                            Match Transactions
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Auto Match Modal -->
        <Dialog v-model:open="showAutoMatchModal">
            <DialogContent class="max-w-md max-h-[90vh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle>Auto Match Transactions</DialogTitle>
                    <DialogDescription>
                        Automatically match transactions based on amount, date, and description similarity.
                    </DialogDescription>
                </DialogHeader>
                <div class="space-y-4">
                    <div>
                        <Label for="confidence">Minimum Confidence Score (%)</Label>
                        <Select v-model="autoMatchSettings.confidence">
                            <SelectTrigger class="w-full">
                                <SelectValue placeholder="Select confidence level" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="90">90% - High Accuracy</SelectItem>
                                <SelectItem value="80">80% - Good Accuracy</SelectItem>
                                <SelectItem value="70">70% - Moderate Accuracy</SelectItem>
                                <SelectItem value="60">60% - Low Accuracy</SelectItem>
                                <SelectItem value="50">50% - Very Low Accuracy</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div>
                        <Label for="dateTolerance">Date Tolerance (days)</Label>
                        <Select v-model="autoMatchSettings.dateTolerance">
                            <SelectTrigger class="w-full">
                                <SelectValue placeholder="Select date tolerance" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="0">0 days - Exact date match</SelectItem>
                                <SelectItem value="1">1 day - Very close</SelectItem>
                                <SelectItem value="3">3 days - Close</SelectItem>
                                <SelectItem value="7">7 days - Within week</SelectItem>
                                <SelectItem value="15">15 days - Within fortnight</SelectItem>
                                <SelectItem value="30">30 days - Within month</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div>
                        <Label>Matching Criteria</Label>
                        <div class="space-y-2">
                            <label class="flex items-center space-x-2">
                                <input type="checkbox" v-model="autoMatchSettings.matchByAmount" class="rounded">
                                <span class="text-sm">Match by amount</span>
                            </label>
                            <label class="flex items-center space-x-2">
                                <input type="checkbox" v-model="autoMatchSettings.matchByDate" class="rounded">
                                <span class="text-sm">Match by date</span>
                            </label>
                            <label class="flex items-center space-x-2">
                                <input type="checkbox" v-model="autoMatchSettings.matchByDescription" class="rounded">
                                <span class="text-sm">Match by description similarity</span>
                            </label>
                        </div>
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="outline"
                        @click="() => { showAutoMatchModal = false; resetAutoMatchForm(); }">Cancel</Button>
                    <Button @click="performAutoMatch" :loading="autoMatching">
                        <Bot class="h-4 w-4 mr-2" />
                        Start Auto Match
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Manual Match Modal -->
        <Dialog v-model:open="showManualMatchModal">
            <DialogContent class="max-w-2xl max-h-[90vh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle>Manual Transaction Match</DialogTitle>
                    <DialogDescription>
                        Manually match a bank transaction with a book transaction.
                    </DialogDescription>
                </DialogHeader>
                <div class="space-y-4">
                    <div>
                        <Label>Bank Transaction</Label>
                        <Select v-model="manualMatch.bankTransactionId">
                            <SelectTrigger class="w-full">
                                <SelectValue placeholder="Select bank transaction" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="none">Select bank transaction</SelectItem>
                                <SelectItem v-for="transaction in unmatchedBankTransactions" :key="transaction.id"
                                    :value="transaction.id">
                                    {{ transaction.description }} - {{ formatCurrency(transaction.amount) }} ({{
                                        transaction.date }})
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <div v-if="manualMatch.bankTransactionId" class="mt-2 p-3 bg-blue-50 rounded-lg">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="font-medium text-blue-900">{{ getSelectedBankTransaction?.description }}
                                    </p>
                                    <p class="text-sm text-blue-700">{{ getSelectedBankTransaction?.date }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-medium text-blue-900">{{
                                        formatCurrency(getSelectedBankTransaction?.amount) }}</p>
                                    <p class="text-sm text-blue-700">{{ getSelectedBankTransaction?.bank_account }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <Label>Book Transaction</Label>
                        <Select v-model="manualMatch.bookTransactionId">
                            <SelectTrigger class="w-full">
                                <SelectValue placeholder="Select book transaction" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="none">Select book transaction</SelectItem>
                                <SelectItem v-for="transaction in unmatchedBookTransactions" :key="transaction.id"
                                    :value="transaction.id">
                                    {{ transaction.description }} - {{ formatCurrency(transaction.amount) }} ({{
                                        transaction.date }})
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <div v-if="manualMatch.bookTransactionId" class="mt-2 p-3 bg-green-50 rounded-lg">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="font-medium text-green-900">{{ getSelectedBookTransaction?.description }}
                                    </p>
                                    <p class="text-sm text-green-700">{{ getSelectedBookTransaction?.date }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-medium text-green-900">{{
                                        formatCurrency(getSelectedBookTransaction?.amount) }}</p>
                                    <p class="text-sm text-green-700">{{ getSelectedBookTransaction?.account }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <Label>Match Details</Label>
                        <div class="space-y-2">
                            <label class="flex items-center space-x-2">
                                <input type="checkbox" v-model="manualMatch.confirmMatch" class="rounded">
                                <span class="text-sm">I confirm this is a correct match</span>
                            </label>
                            <label class="flex items-center space-x-2">
                                <input type="checkbox" v-model="manualMatch.autoReconcile" class="rounded">
                                <span class="text-sm">Automatically reconcile after matching</span>
                            </label>
                        </div>
                    </div>
                    <div v-if="manualMatch.bankTransactionId && manualMatch.bookTransactionId">
                        <Label>Match Summary</Label>
                        <div class="p-3 bg-gray-50 rounded-lg">
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <p class="font-medium text-gray-700">Amount Match:</p>
                                    <p class="text-gray-600">
                                        <span v-if="isAmountMatch" class="text-green-600">✓ Exact match</span>
                                        <span v-else class="text-orange-600">⚠ Different amounts</span>
                                    </p>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-700">Date Match:</p>
                                    <p class="text-gray-600">
                                        <span v-if="isDateMatch" class="text-green-600">✓ Same date</span>
                                        <span v-else class="text-orange-600">⚠ Different dates</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="outline"
                        @click="() => { showManualMatchModal = false; resetManualMatchForm(); }">Cancel</Button>
                    <Button @click="performManualMatch" :disabled="!canManualMatch">
                        <CheckCircle class="h-4 w-4 mr-2" />
                        Match
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import ScrollArea from '@/components/ui/scroll-area.vue'
import { FileText, CheckCircle, AlertTriangle, TrendingUp, Bot, Search } from 'lucide-vue-next'
import { useApi } from '@/composables/useApi'
import AppLayout from '@/layouts/AppLayout.vue'

// Breadcrumbs
const breadcrumbs = [
    { title: 'Finance', href: '/finance' },
    { title: 'Bank Reconciliation', href: '/finance/bank-reconciliation' },
    { title: 'Transaction Matching', href: '/finance/bank-reconciliation/matching' }
]

const api = useApi()

// State
const showAutoMatchModal = ref(false)
const showManualMatchModal = ref(false)
const autoMatching = ref(false)
const selectedBankTransaction = ref<any | null>(null)
const selectedBookTransaction = ref<any | null>(null)

// Data
const statistics = ref({
    totalTransactions: 0,
    matchedTransactions: 0,
    unmatchedTransactions: 0,
    matchPercentage: 0,
    averageConfidence: 0
})

const bankTransactions = ref<any[]>([])
const bookTransactions = ref<any[]>([])

const autoMatchSettings = ref({
    confidence: '80',
    dateTolerance: '3',
    matchByAmount: true,
    matchByDate: true,
    matchByDescription: true
})

const manualMatch = ref({
    bankTransactionId: 'none',
    bookTransactionId: 'none',
    confirmMatch: false,
    autoReconcile: false
})

// Computed
const unmatchedBankTransactions = computed(() =>
    bankTransactions.value.filter(t => !t.matched)
)

const unmatchedBookTransactions = computed(() =>
    bookTransactions.value.filter(t => !t.matched)
)

const canMatch = computed(() =>
    selectedBankTransaction.value && selectedBookTransaction.value
)

const canManualMatch = computed(() =>
    manualMatch.value.bankTransactionId &&
    manualMatch.value.bankTransactionId !== 'none' &&
    manualMatch.value.bookTransactionId &&
    manualMatch.value.bookTransactionId !== 'none' &&
    manualMatch.value.confirmMatch
)

const getSelectedBankTransaction = computed(() => {
    if (!manualMatch.value.bankTransactionId || manualMatch.value.bankTransactionId === 'none') return null
    return bankTransactions.value.find(t => t.id == manualMatch.value.bankTransactionId)
})

const getSelectedBookTransaction = computed(() => {
    if (!manualMatch.value.bookTransactionId || manualMatch.value.bookTransactionId === 'none') return null
    return bookTransactions.value.find(t => t.id == manualMatch.value.bookTransactionId)
})

const isAmountMatch = computed(() => {
    if (!getSelectedBankTransaction.value || !getSelectedBookTransaction.value) return false
    const bankAmount = getSelectedBankTransaction.value.amount || 0
    const bookAmount = getSelectedBookTransaction.value.amount || 0
    return Math.abs(bankAmount - bookAmount) < 0.01
})

const isDateMatch = computed(() => {
    if (!getSelectedBankTransaction.value || !getSelectedBookTransaction.value) return false
    return getSelectedBankTransaction.value.date === getSelectedBookTransaction.value.date
})

// Methods
const resetAutoMatchForm = () => {
    autoMatchSettings.value = {
        confidence: '80',
        dateTolerance: '3',
        matchByAmount: true,
        matchByDate: true,
        matchByDescription: true
    }
}

const resetManualMatchForm = () => {
    manualMatch.value = {
        bankTransactionId: 'none',
        bookTransactionId: 'none',
        confirmMatch: false,
        autoReconcile: false
    }
}

const fetchData = async () => {
    try {
        const [statsResponse, bankResponse, bookResponse] = await Promise.all([
            api.get('/api/finance/bank-reconciliation/matching/statistics'),
            api.get('/api/finance/bank-reconciliation/matching/bank-transactions'),
            api.get('/api/finance/bank-reconciliation/matching/book-transactions')
        ])

        if (statsResponse.data?.success) {
            statistics.value = statsResponse.data.data
        }
        if (bankResponse.data?.success) {
            bankTransactions.value = bankResponse.data.data
        }
        if (bookResponse.data?.success) {
            bookTransactions.value = bookResponse.data.data
        }
    } catch (error) {
        console.error('Error fetching matching data:', error)
    }
}

const selectBankTransaction = (transaction: any) => {
    selectedBankTransaction.value = transaction
}

const selectBookTransaction = (transaction: any) => {
    selectedBookTransaction.value = transaction
}

const matchTransactions = async () => {
    if (!canMatch.value) return

    try {
        const response = await api.post('/api/finance/bank-reconciliation/matching/match', {
            bank_transaction_id: selectedBankTransaction.value.id,
            book_transaction_id: selectedBookTransaction.value.id
        })

        if (response.data?.success) {
            // Refresh data
            await fetchData()

            // Clear selection
            selectedBankTransaction.value = null
            selectedBookTransaction.value = null

            // Show success message
            // You can add a toast notification here
        }
    } catch (error) {
        console.error('Error matching transactions:', error)
    }
}

const performAutoMatch = async () => {
    autoMatching.value = true

    try {
        const payload = {
            confidence: autoMatchSettings.value.confidence.toString(),
            dateTolerance: autoMatchSettings.value.dateTolerance.toString(),
            matchByAmount: autoMatchSettings.value.matchByAmount,
            matchByDate: autoMatchSettings.value.matchByDate,
            matchByDescription: autoMatchSettings.value.matchByDescription
        }

        const response = await api.post('/api/finance/bank-reconciliation/matching/auto', payload)

        if (response.data?.success) {
            await fetchData()
            showAutoMatchModal.value = false
        }
    } catch (error) {
        console.error('Error performing auto match:', error)
    } finally {
        autoMatching.value = false
    }
}

const performManualMatch = async () => {
    if (!canManualMatch.value) return

    try {
        const payload = {
            bank_transaction_id: manualMatch.value.bankTransactionId,
            book_transaction_id: manualMatch.value.bookTransactionId
        }

        const response = await api.post('/api/finance/bank-reconciliation/matching/manual', payload)

        if (response.data?.success) {
            await fetchData()
            showManualMatchModal.value = false

            // Clear form
            resetManualMatchForm()
        }
    } catch (error) {
        console.error('Error performing manual match:', error)
    }
}

const formatCurrency = (amount: number | null | undefined) => {
    // Handle null, undefined, or NaN values
    if (amount === null || amount === undefined || isNaN(amount)) {
        return 'Rp 0'
    }

    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(amount)
}

// Lifecycle
onMounted(() => {
    fetchData()
})
</script>
