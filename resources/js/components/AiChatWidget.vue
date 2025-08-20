<template>
    <div class="ai-chat-widget">
        <!-- Chat Toggle Button -->
        <Button v-if="!isOpen" @click="toggleChat"
            class="fixed bottom-6 right-6 z-50 rounded-full w-14 h-14 shadow-lg bg-primary hover:bg-primary/90"
            :disabled="loading">
            <MessageCircle v-if="!loading" class="w-6 h-6 text-primary-foreground" />
            <Loader2 v-else class="w-6 h-6 text-primary-foreground animate-spin" />
        </Button>

        <!-- Chat Window -->
        <div v-if="isOpen"
            class="fixed bottom-6 right-6 z-50 w-96 h-[500px] bg-background border border-border rounded-lg shadow-xl flex flex-col">
            <!-- Chat Header -->
            <div class="flex items-center justify-between p-4 border-b bg-primary text-primary-foreground rounded-t-lg">
                <div class="flex items-center gap-2">
                    <Bot class="w-5 h-5" />
                    <h3 class="font-semibold">AI Assistant</h3>
                </div>
                <div class="flex items-center gap-2">
                    <Button @click="clearHistory" variant="ghost" size="sm"
                        class="text-primary-foreground hover:bg-primary-foreground/20" :disabled="loading">
                        <Trash2 class="w-4 h-4" />
                    </Button>
                    <Button @click="toggleChat" variant="ghost" size="sm"
                        class="text-primary-foreground hover:bg-primary-foreground/20" :disabled="loading">
                        <X class="w-4 h-4" />
                    </Button>
                </div>
            </div>

            <!-- Chat Messages -->
            <div class="flex-1 overflow-y-auto p-4 space-y-4 bg-background" ref="messagesContainer">
                <!-- Welcome Message -->
                <div v-if="messages.length === 0" class="text-center py-8">
                    <Bot class="w-12 h-12 mx-auto mb-4 text-muted-foreground/50" />
                    <h4 class="font-medium mb-2 text-foreground">Selamat datang di AI Assistant!</h4>
                    <p class="text-sm text-muted-foreground mb-4">
                        Saya siap membantu Anda menggunakan aplikasi ini. Silakan ajukan pertanyaan.
                    </p>
                    <div class="space-y-2">
                        <Button v-for="suggestion in quickSuggestions" :key="suggestion"
                            @click="sendMessage(suggestion)" variant="outline" size="sm" class="w-full justify-start"
                            :disabled="loading">
                            {{ suggestion }}
                        </Button>
                    </div>
                </div>

                <!-- Messages -->
                <div v-for="(message, index) in messages" :key="index" class="flex gap-3"
                    :class="message.type === 'user' ? 'justify-end' : 'justify-start'">
                    <div class="max-w-[80%] rounded-lg px-3 py-2" :class="message.type === 'user'
                        ? 'bg-primary text-primary-foreground'
                        : 'bg-muted text-muted-foreground'
                        ">
                        <p class="text-sm whitespace-pre-wrap">{{ message.content }}</p>
                        <p class="text-xs opacity-70 mt-1">
                            {{ formatTime(message.timestamp) }}
                        </p>
                    </div>
                </div>

                <!-- Loading Indicator -->
                <div v-if="loading" class="flex gap-3 justify-start">
                    <div class="max-w-[80%] rounded-lg px-3 py-2 bg-muted text-muted-foreground">
                        <div class="flex items-center gap-2">
                            <Loader2 class="w-4 h-4 animate-spin" />
                            <span class="text-sm">AI sedang mengetik...</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chat Input -->
            <div class="p-4 border-t border-border bg-background">
                <form @submit.prevent="handleSubmit" class="flex gap-2">
                    <Input v-model="newMessage" placeholder="Ketik pesan Anda..." class="flex-1" :disabled="loading"
                        @keydown.enter="handleSubmit" />
                    <Button type="submit" size="sm" :disabled="!newMessage.trim() || loading">
                        <Send class="w-4 h-4" />
                    </Button>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, nextTick, onMounted, watch } from 'vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { MessageCircle, Bot, X, Send, Loader2, Trash2 } from 'lucide-vue-next'
import { useApi } from '@/composables/useApi'

const api = useApi()

interface ChatMessage {
    type: 'user' | 'ai'
    content: string
    timestamp: Date
}

const isOpen = ref(false)
const loading = ref(false)
const messages = ref<ChatMessage[]>([])
const newMessage = ref('')
const messagesContainer = ref<HTMLElement>()

const quickSuggestions = [
    'Bagaimana cara menggunakan Dashboard?',
    'Cara menambah produk baru',
    'Cara membuat order penjualan',
    'Cara mengelola pelanggan',
    'Cara melihat laporan keuangan',
    'Cara mengelola inventori',
    'Cara mengatur pengguna dan hak akses'
]

const toggleChat = () => {
    isOpen.value = !isOpen.value
    if (isOpen.value) {
        nextTick(() => {
            scrollToBottom()
        })
    }
}

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
        // Get current page context
        const context = getCurrentPageContext()

        const response = await api.post('/api/ai-chat/chat', {
            message: content,
            context
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

const getCurrentPageContext = () => {
    const path = window.location.pathname
    const pathSegments = path.split('/').filter(Boolean)

    if (pathSegments.length === 0) return 'Dashboard'

    const pageMap: Record<string, string> = {
        'dashboard': 'Dashboard - Ringkasan data bisnis',
        'crm': 'CRM - Manajemen pelanggan dan prospek',
        'products': 'Produk - Manajemen produk dan kategori',
        'sales-orders': 'Order Penjualan - Membuat dan mengelola pesanan',
        'purchase-orders': 'Order Pembelian - Membuat dan mengelola pembelian',
        'inventory': 'Inventori - Manajemen stok dan gudang',
        'finance': 'Keuangan - Jurnal, buku besar, dan laporan',
        'manufacturing': 'Manufaktur - BOM, produksi, dan work order',
        'hr': 'HR - Manajemen karyawan dan penggajian',
        'projects': 'Proyek - Manajemen proyek dan tugas',
        'reports': 'Laporan - Analitik dan laporan bisnis',
        'settings': 'Pengaturan - Profil dan konfigurasi'
    }

    return pageMap[pathSegments[0]] || `Halaman: ${pathSegments[0]}`
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
.ai-chat-widget {
    font-family: inherit;
}

/* Custom scrollbar for messages */
.messages-container::-webkit-scrollbar {
    width: 4px;
}

.messages-container::-webkit-scrollbar-track {
    background: transparent;
}

.messages-container::-webkit-scrollbar-thumb {
    background: #d1d5db;
    border-radius: 2px;
}

.messages-container::-webkit-scrollbar-thumb:hover {
    background: #9ca3af;
}
</style>
