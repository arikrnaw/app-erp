<template>

    <Head title="AI Assistant" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="h-[calc(100vh-120px)] flex flex-col">
            <!-- Header -->
            <div class="flex items-center justify-between p-6 border-b border-border bg-background">
                <div class="flex items-center gap-3">
                    <Bot class="w-8 h-8 text-primary" />
                    <div>
                        <h1 class="text-2xl font-bold text-foreground">AI Assistant</h1>
                        <p class="text-muted-foreground">Dapatkan bantuan untuk menggunakan aplikasi ini</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Button @click="clearHistory" variant="outline" :disabled="loading">
                        <Trash2 class="w-4 h-4 mr-2" />
                        Bersihkan Riwayat
                    </Button>
                    <Button @click="exportChat" variant="outline" :disabled="messages.length === 0">
                        <Download class="w-4 h-4 mr-2" />
                        Export Chat
                    </Button>
                </div>
            </div>

            <!-- Chat Area -->
            <div class="flex-1 flex">
                <!-- Main Chat -->
                <div class="flex-1 flex flex-col">
                    <!-- Messages -->
                    <div class="flex-1 overflow-y-auto p-6 space-y-4" ref="messagesContainer">
                        <!-- Welcome Message -->
                        <div v-if="messages.length === 0" class="text-center py-12">
                            <Bot class="w-16 h-16 mx-auto mb-6 text-muted-foreground/50" />
                            <h2 class="text-2xl font-bold mb-4">Selamat datang di AI Assistant!</h2>
                            <p class="text-muted-foreground mb-8 max-w-md mx-auto">
                                Saya adalah asisten AI yang siap membantu Anda menggunakan aplikasi ERP/CRM ini.
                                Silakan ajukan pertanyaan tentang fitur-fitur yang ingin Anda ketahui.
                            </p>

                            <!-- Quick Suggestions -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-2xl mx-auto">
                                <Card v-for="suggestion in quickSuggestions" :key="suggestion.title"
                                    class="cursor-pointer hover:shadow-md transition-shadow"
                                    @click="sendMessage(suggestion.question)">
                                    <CardContent class="p-4">
                                        <div class="flex items-start gap-3">
                                            <div
                                                class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0">
                                                <component :is="suggestion.icon" class="w-4 h-4 text-primary" />
                                            </div>
                                            <div>
                                                <h3 class="font-medium mb-1">{{ suggestion.title }}</h3>
                                                <p class="text-sm text-muted-foreground">{{ suggestion.description }}
                                                </p>
                                            </div>
                                        </div>
                                    </CardContent>
                                </Card>
                            </div>
                        </div>

                        <!-- Messages -->
                        <div v-for="(message, index) in messages" :key="index" class="flex gap-4"
                            :class="message.type === 'user' ? 'justify-end' : 'justify-start'">
                            <div class="max-w-2xl rounded-lg px-4 py-3" :class="message.type === 'user'
                                ? 'bg-primary text-primary-foreground'
                                : 'bg-muted text-muted-foreground'
                                ">
                                <p class="whitespace-pre-wrap">{{ message.content }}</p>
                                <p class="text-xs opacity-70 mt-2">
                                    {{ formatTime(message.timestamp) }}
                                </p>
                            </div>
                        </div>

                        <!-- Loading Indicator -->
                        <div v-if="loading" class="flex gap-4 justify-start">
                            <div class="max-w-2xl rounded-lg px-4 py-3 bg-muted text-muted-foreground">
                                <div class="flex items-center gap-3">
                                    <Loader2 class="w-5 h-5 animate-spin" />
                                    <span>AI sedang mengetik...</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Input Area -->
                    <div class="p-6 border-t border-border bg-background">
                        <form @submit.prevent="handleSubmit" class="flex gap-4">
                            <Input v-model="newMessage"
                                placeholder="Ketik pesan Anda atau ajukan pertanyaan tentang aplikasi..." class="flex-1"
                                :disabled="loading" @keydown.enter="handleSubmit" />
                            <Button type="submit" :disabled="!newMessage.trim() || loading">
                                <Send class="w-4 h-4 mr-2" />
                                Kirim
                            </Button>
                        </form>
                    </div>
                </div>

                <!-- Sidebar with Quick Actions -->
                <div class="w-80 border-l border-border bg-muted/30 p-6">
                    <h3 class="font-semibold mb-4 text-foreground">Quick Actions</h3>

                    <!-- Common Questions -->
                    <div class="space-y-3">
                        <h4 class="text-sm font-medium text-muted-foreground">Pertanyaan Umum</h4>
                        <div class="space-y-2">
                            <Button v-for="question in commonQuestions" :key="question" @click="sendMessage(question)"
                                variant="ghost" size="sm" class="w-full justify-start text-left h-auto p-3"
                                :disabled="loading">
                                <span class="text-sm">{{ question }}</span>
                            </Button>
                        </div>
                    </div>

                    <Separator class="my-6" />

                    <!-- Application Modules -->
                    <div class="space-y-3">
                        <h4 class="text-sm font-medium text-muted-foreground">Modul Aplikasi</h4>
                        <div class="space-y-2">
                            <Button v-for="module in applicationModules" :key="module.name"
                                @click="sendMessage(`Bagaimana cara menggunakan ${module.name}?`)" variant="ghost"
                                size="sm" class="w-full justify-start text-left h-auto p-3" :disabled="loading">
                                <component :is="module.icon" class="w-4 h-4 mr-2" />
                                <span class="text-sm">{{ module.name }}</span>
                            </Button>
                        </div>
                    </div>

                    <Separator class="my-6" />

                    <!-- Tips -->
                    <div class="space-y-3">
                        <h4 class="text-sm font-medium text-muted-foreground">Tips</h4>
                        <div class="text-sm text-muted-foreground space-y-2">
                            <p>• Ajukan pertanyaan spesifik untuk jawaban yang lebih akurat</p>
                            <p>• Gunakan bahasa Indonesia untuk hasil terbaik</p>
                            <p>• AI akan membantu berdasarkan halaman yang sedang Anda buka</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, nextTick, onMounted, watch } from 'vue'
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Card, CardContent } from '@/components/ui/card'
import { Separator } from '@/components/ui/separator'
import {
    Bot,
    Send,
    Loader2,
    Trash2,
    Download,
    BarChart3,
    Users,
    Package,
    ShoppingCart,
    Building2,
    FileText,
    Settings,
    HelpCircle
} from 'lucide-vue-next'
import type { BreadcrumbItemType } from '@/types'
import { useApi } from '@/composables/useApi'

const api = useApi()

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'AI Assistant', href: '/ai-chat' }
]

interface ChatMessage {
    type: 'user' | 'ai'
    content: string
    timestamp: Date
}

const loading = ref(false)
const messages = ref<ChatMessage[]>([])
const newMessage = ref('')
const messagesContainer = ref<HTMLElement>()

const quickSuggestions = [
    {
        title: 'Dashboard',
        description: 'Pelajari cara menggunakan dashboard dan melihat ringkasan bisnis',
        question: 'Bagaimana cara menggunakan Dashboard?',
        icon: BarChart3
    },
    {
        title: 'Manajemen Produk',
        description: 'Cara menambah, mengedit, dan mengelola produk',
        question: 'Cara menambah produk baru',
        icon: Package
    },
    {
        title: 'Order Penjualan',
        description: 'Cara membuat dan mengelola order penjualan',
        question: 'Cara membuat order penjualan',
        icon: ShoppingCart
    },
    {
        title: 'Manajemen Pelanggan',
        description: 'Cara mengelola data pelanggan dan prospek',
        question: 'Cara mengelola pelanggan',
        icon: Users
    }
]

const commonQuestions = [
    'Bagaimana cara menggunakan Dashboard?',
    'Cara menambah produk baru',
    'Cara membuat order penjualan',
    'Cara mengelola pelanggan',
    'Cara melihat laporan keuangan',
    'Cara mengelola inventori',
    'Cara mengatur pengguna dan hak akses',
    'Cara membuat laporan'
]

const applicationModules = [
    { name: 'Dashboard', icon: BarChart3 },
    { name: 'CRM', icon: Users },
    { name: 'Produk', icon: Package },
    { name: 'Penjualan', icon: ShoppingCart },
    { name: 'Pembelian', icon: Building2 },
    { name: 'Inventori', icon: Package },
    { name: 'Keuangan', icon: FileText },
    { name: 'Manufaktur', icon: Building2 },
    { name: 'HR', icon: Users },
    { name: 'Proyek', icon: BarChart3 },
    { name: 'Laporan', icon: FileText },
    { name: 'Pengaturan', icon: Settings }
]

const sendMessage = async (message?: string) => {
    const content = message || newMessage.value.trim()
    if (!content || loading.value) return

    // Add user message
    messages.value.push({
        type: 'user',
        content,
        timestamp: new Date()
    })

    newMessage.value = ''
    loading.value = true

    try {
        const response = await api.post('/api/ai-chat/chat', {
            message: content,
            context: 'Halaman AI Assistant - Chat penuh'
        })

        if (response.data.success) {
            messages.value.push({
                type: 'ai',
                content: response.data.message,
                timestamp: new Date()
            })
        } else {
            throw new Error(response.data.message || 'Terjadi kesalahan')
        }
    } catch (error) {
        console.error('Chat error:', error)
        messages.value.push({
            type: 'ai',
            content: 'Maaf, terjadi kesalahan dalam memproses pesan Anda. Silakan coba lagi.',
            timestamp: new Date()
        })
    } finally {
        loading.value = false
        nextTick(() => {
            scrollToBottom()
        })
    }
}

const handleSubmit = (event: Event) => {
    event.preventDefault()
    sendMessage()
}

const clearHistory = async () => {
    try {
        await api.delete('/api/ai-chat/history')
        messages.value = []
    } catch (error) {
        console.error('Error clearing history:', error)
    }
}

const exportChat = () => {
    const chatContent = messages.value.map(msg => {
        const time = formatTime(msg.timestamp)
        const sender = msg.type === 'user' ? 'Anda' : 'AI Assistant'
        return `[${time}] ${sender}: ${msg.content}`
    }).join('\n\n')

    const blob = new Blob([chatContent], { type: 'text/plain' })
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `ai-chat-${new Date().toISOString().split('T')[0]}.txt`
    document.body.appendChild(a)
    a.click()
    document.body.removeChild(a)
    URL.revokeObjectURL(url)
}

const scrollToBottom = () => {
    if (messagesContainer.value) {
        messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
    }
}

const formatTime = (date: Date) => {
    return date.toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit'
    })
}

// Watch for new messages and scroll to bottom
watch(messages, () => {
    nextTick(() => {
        scrollToBottom()
    })
}, { deep: true })

onMounted(() => {
    // Load chat history if needed
    // For now, we'll start with empty history
})
</script>

<style scoped>
/* Custom scrollbar for messages */
.messages-container::-webkit-scrollbar {
    width: 6px;
}

.messages-container::-webkit-scrollbar-track {
    background: transparent;
}

.messages-container::-webkit-scrollbar-thumb {
    background: #d1d5db;
    border-radius: 3px;
}

.messages-container::-webkit-scrollbar-thumb:hover {
    background: #9ca3af;
}
</style>
