<template>
    <Teleport to="body">
        <div v-if="toasts.length > 0"
            class="fixed top-0 z-[100] flex max-h-screen w-full flex-col-reverse p-4 sm:bottom-0 sm:right-0 sm:top-auto sm:flex-col md:max-w-[420px]">
            <TransitionGroup name="toast" tag="div" class="flex flex-col gap-2">
                <div v-for="toast in toasts" :key="toast.id"
                    class="group pointer-events-auto relative flex w-full items-center justify-between space-x-4 overflow-hidden rounded-md border p-6 pr-8 shadow-lg transition-all data-[swipe=cancel]:translate-x-0 data-[swipe=end]:translate-x-[var(--radix-toast-swipe-end-x)] data-[swipe=move]:translate-x-[var(--radix-toast-swipe-move-x)] data-[swipe=move]:transition-none data-[state=open]:animate-in data-[state=closed]:animate-out data-[swipe=end]:animate-out data-[state=closed]:fade-out-80 data-[state=closed]:slide-out-to-right-full data-[state=open]:slide-in-from-top-full data-[state=open]:sm:slide-in-from-bottom-full"
                    :class="getToastClasses(toast.type)">
                    <div class="grid gap-1">
                        <div v-if="toast.title" class="text-sm font-semibold">
                            {{ toast.title }}
                        </div>
                        <div v-if="toast.description" class="text-sm opacity-90">
                            {{ toast.description }}
                        </div>
                    </div>
                    <button @click="dismiss(toast.id)"
                        class="absolute right-2 top-2 rounded-md p-1 text-foreground/50 opacity-0 transition-opacity hover:text-foreground focus:opacity-100 focus:outline-none focus:ring-2 group-hover:opacity-100 group-[.destructive]:text-red-300 group-[.destructive]:hover:text-red-50 group-[.destructive]:focus:ring-red-400 group-[.destructive]:focus:ring-offset-red-600">
                        <X class="h-4 w-4" />
                    </button>
                    <div v-if="toast.action" class="flex flex-col gap-1">
                        <button @click="toast.action.onClick"
                            class="inline-flex h-8 shrink-0 items-center justify-center rounded-md border bg-transparent px-3 text-sm font-medium ring-offset-background transition-colors hover:bg-secondary focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 group-[.destructive]:border-muted/40 group-[.destructive]:hover:border-destructive/30 group-[.destructive]:hover:bg-destructive group-[.destructive]:hover:text-destructive-foreground group-[.destructive]:focus:ring-destructive">
                            {{ toast.action.label }}
                        </button>
                    </div>
                </div>
            </TransitionGroup>
        </div>
    </Teleport>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { X } from 'lucide-vue-next'

interface ToastAction {
    label: string
    onClick: () => void
}

interface Toast {
    id: string
    title?: string
    description?: string
    type?: 'default' | 'destructive' | 'success' | 'warning'
    action?: ToastAction
    duration?: number
}

const toasts = ref<Toast[]>([])
let toastIdCounter = 0

const getToastClasses = (type: string = 'default') => {
    const baseClasses = 'bg-background border'

    switch (type) {
        case 'destructive':
            return `${baseClasses} destructive group border-destructive/50 text-destructive dark:border-destructive [&>svg]:text-destructive`
        case 'success':
            return `${baseClasses} border-green-500/50 text-green-600 dark:text-green-400 [&>svg]:text-green-600 dark:[&>svg]:text-green-400`
        case 'warning':
            return `${baseClasses} border-yellow-500/50 text-yellow-600 dark:text-yellow-400 [&>svg]:text-yellow-600 dark:[&>svg]:text-yellow-400`
        default:
            return baseClasses
    }
}

const addToast = (toast: Omit<Toast, 'id'>) => {
    const id = `toast-${++toastIdCounter}`
    const newToast: Toast = {
        id,
        duration: 5000,
        ...toast
    }

    toasts.value.push(newToast)

    if (newToast.duration && newToast.duration > 0) {
        setTimeout(() => {
            dismiss(id)
        }, newToast.duration)
    }

    return id
}

const dismiss = (id: string) => {
    const index = toasts.value.findIndex(toast => toast.id === id)
    if (index > -1) {
        toasts.value.splice(index, 1)
    }
}

// Expose methods globally
const toast = {
    default: (message: string, options?: Partial<Toast>) => {
        return addToast({ title: message, ...options })
    },
    success: (message: string, options?: Partial<Toast>) => {
        return addToast({ title: message, type: 'success', ...options })
    },
    error: (message: string, options?: Partial<Toast>) => {
        return addToast({ title: message, type: 'destructive', ...options })
    },
    warning: (message: string, options?: Partial<Toast>) => {
        return addToast({ title: message, type: 'warning', ...options })
    }
}

// Make toast available globally
onMounted(() => {
    window.toast = toast
})

onUnmounted(() => {
    delete window.toast
})

// Expose for use in components
defineExpose({
    toast,
    dismiss
})
</script>

<style scoped>
.toast-enter-active,
.toast-leave-active {
    transition: all 0.3s ease;
}

.toast-enter-from {
    opacity: 0;
    transform: translateX(100%);
}

.toast-leave-to {
    opacity: 0;
    transform: translateX(100%);
}

.toast-move {
    transition: transform 0.3s ease;
}
</style>
