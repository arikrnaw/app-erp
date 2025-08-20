<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" :class="className" v-bind="$attrs">
        <!-- Professional A design with geometric precision -->
        <path fill="currentColor" d="M24 4L38 44H34L32.5 39H15.5L14 44H10L24 4ZM18.5 35H28.5L24 25L18.5 35Z" />

        <!-- Subtle geometric accent -->
        <path fill="currentColor" opacity="0.2" d="M22 40H26" stroke="currentColor" stroke-width="1.5"
            stroke-linecap="round" />

        <!-- Minimalist border circle -->
        <circle cx="24" cy="24" r="22" fill="none" stroke="currentColor" stroke-width="0.8" opacity="0.15" />
    </svg>
</template>
