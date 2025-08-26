<template>
    <div aria-live="assertive"
        class="pointer-events-none fixed inset-0 flex items-end px-4 py-6 sm:items-start sm:p-6 z-50">
        <div class="flex w-full flex-col items-center space-y-4 sm:items-end">
            <TransitionGroup enter-active-class="transform ease-out duration-300 transition"
                enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
                enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
                leave-active-class="transition ease-in duration-100" leave-from-class="opacity-100"
                leave-to-class="opacity-0" move-class="transition-transform duration-300">
                <Toast v-for="toast in toasts" :key="toast.id" :id="toast.id" :type="toast.type" :title="toast.title"
                    :message="toast.message" :duration="toast.duration" :auto-close="toast.autoClose"
                    @close="removeToast" />
            </TransitionGroup>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import Toast from './Toast.vue'
import { useToast } from '@/composables/useToast'

const toastStore = useToast()

const toasts = computed(() => toastStore.toasts)

const removeToast = (id: string) => {
    toastStore.removeToast(id)
}
</script>
