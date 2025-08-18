<template>
    <nav role="navigation" aria-label="pagination" data-slot="pagination"
        :class="cn('mx-auto flex w-full justify-center', className)" v-bind="$attrs">
        <slot />
    </nav>
</template>

<script setup lang="ts">
import { cn } from '@/lib/utils'

interface PaginationProps {
    className?: string
}

defineProps<PaginationProps>()
</script>
