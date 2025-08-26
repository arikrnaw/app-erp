<template>
    <Transition enter-active-class="transform ease-out duration-300 transition"
        enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
        enter-to-class="translate-y-0 opacity-100 sm:translate-x-0" leave-active-class="transition ease-in duration-100"
        leave-from-class="opacity-100" leave-to-class="opacity-0">
        <div v-if="isVisible"
            class="pointer-events-auto w-full max-w-sm overflow-hidden rounded-lg bg-card shadow-lg ring-1 ring-border"
            :class="[
                type === 'success' && 'ring-green-500/20',
                type === 'error' && 'ring-destructive/20',
                type === 'warning' && 'ring-yellow-500/20',
                type === 'info' && 'ring-blue-500/20'
            ]">
            <div class="p-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <component :is="iconComponent" class="h-5 w-5" :class="iconClasses" />
                    </div>
                    <div class="ml-3 w-0 flex-1">
                        <p class="text-sm font-medium text-card-foreground">
                            {{ title }}
                        </p>
                        <p v-if="message" class="mt-1 text-sm text-muted-foreground">
                            {{ message }}
                        </p>
                    </div>
                    <div class="ml-4 flex flex-shrink-0">
                        <button @click="close"
                            class="inline-flex rounded-md bg-transparent text-muted-foreground hover:text-card-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2">
                            <span class="sr-only">Close</span>
                            <X class="h-4 w-4" />
                        </button>
                    </div>
                </div>
            </div>

            <!-- Progress Bar -->
            <div v-if="autoClose && duration > 0" class="h-1 bg-muted">
                <div class="h-full transition-all duration-100 ease-linear" :class="[
                    type === 'success' && 'bg-green-500',
                    type === 'error' && 'bg-destructive',
                    type === 'warning' && 'bg-yellow-500',
                    type === 'info' && 'bg-blue-500'
                ]" :style="{ width: progressWidth + '%' }" />
            </div>
        </div>
    </Transition>
</template>

<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from 'vue'
import { X, CheckCircle, AlertCircle, AlertTriangle, Info } from 'lucide-vue-next'

interface Props {
    id: string
    type?: 'success' | 'error' | 'warning' | 'info'
    title: string
    message?: string
    duration?: number
    autoClose?: boolean
    onClose?: (id: string) => void
}

const props = withDefaults(defineProps<Props>(), {
    type: 'info',
    duration: 5000,
    autoClose: true
})

const emit = defineEmits<{
    close: [id: string]
}>()

const isVisible = ref(true)
const progressWidth = ref(100)
let progressInterval: NodeJS.Timeout | null = null
let closeTimeout: NodeJS.Timeout | null = null

const iconComponent = computed(() => {
    switch (props.type) {
        case 'success':
            return CheckCircle
        case 'error':
            return AlertCircle
        case 'warning':
            return AlertTriangle
        case 'info':
        default:
            return Info
    }
})

const iconClasses = computed(() => {
    switch (props.type) {
        case 'success':
            return 'text-green-500'
        case 'error':
            return 'text-destructive'
        case 'warning':
            return 'text-yellow-500'
        case 'info':
        default:
            return 'text-blue-500'
    }
})

const close = () => {
    isVisible.value = false
    emit('close', props.id)
    if (props.onClose) {
        props.onClose(props.id)
    }
}

const startProgress = () => {
    if (!props.autoClose || props.duration <= 0) return

    const startTime = Date.now()
    const endTime = startTime + props.duration

    progressInterval = setInterval(() => {
        const now = Date.now()
        const elapsed = now - startTime
        const remaining = endTime - now

        if (remaining <= 0) {
            progressWidth.value = 0
            close()
            return
        }

        progressWidth.value = (remaining / props.duration) * 100
    }, 100)
}

const startAutoClose = () => {
    if (!props.autoClose || props.duration <= 0) return

    closeTimeout = setTimeout(() => {
        close()
    }, props.duration)
}

onMounted(() => {
    if (props.autoClose && props.duration > 0) {
        startProgress()
        startAutoClose()
    }
})

onUnmounted(() => {
    if (progressInterval) {
        clearInterval(progressInterval)
    }
    if (closeTimeout) {
        clearTimeout(closeTimeout)
    }
})
</script>
