<template>
  <ul
    data-slot="pagination-content"
    :class="cn('flex flex-row items-center gap-1', className)"
    v-bind="$attrs"
  >
    <slot />
  </ul>
</template>

<script setup lang="ts">
import { cn } from '@/lib/utils'

interface PaginationContentProps {
  className?: string
}

defineProps<PaginationContentProps>()
</script>
