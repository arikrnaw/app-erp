<template>
    <div ref="rootRef" :class="cn('relative overflow-hidden', $attrs.class)" v-bind="$attrs">
        <div ref="viewportRef" class="h-full w-full overflow-auto custom-scrollbar" @scroll="handleScroll"
            style="max-height: inherit;">
            <div class="scroll-content">
                <slot />
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, nextTick } from 'vue'
import { cn } from '@/lib/utils'

interface Props {
    orientation?: 'vertical' | 'horizontal' | 'both'
    className?: string
}

const props = withDefaults(defineProps<Props>(), {
    orientation: 'both'
})

const rootRef = ref<HTMLElement>()
const viewportRef = ref<HTMLElement>()

const handleScroll = () => {
    // Handle scroll events if needed
}

onMounted(() => {
    nextTick(() => {
        // Initialize if needed
    })
})

onUnmounted(() => {
    // Cleanup if needed
})
</script>

<style scoped>
/* Webkit Scrollbar - Modern Design */
.custom-scrollbar::-webkit-scrollbar {
    width: 5px !important;
    height: 5px !important;
    scrollbar-width: thin;
    scrollbar-color: #383838 #ffffff00;
    scroll-behavior: smooth;
    /* Force scrollbar to show */
    overflow: auto !important;
}

/* Hide scrollbar buttons (arrows) */
.custom-scrollbar::-webkit-scrollbar-button {
    display: none !important;
    width: 0 !important;
    height: 0 !important;
}

.custom-scrollbar::-webkit-scrollbar-track {
    border-radius: 10px !important;
    background-color: #f5f5f500 !important;
    margin: 2px !important;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    border-radius: 10px !important;
    background-color: #383838 !important;
    transition: background-color 0.3s ease !important;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background-color: #a09f9f !important;
}

.custom-scrollbar::-webkit-scrollbar-thumb:active {
    background-color: #a09f9f !important;
}

.custom-scrollbar::-webkit-scrollbar-corner {
    background-color: #F5F5F5 !important;
}
</style>
