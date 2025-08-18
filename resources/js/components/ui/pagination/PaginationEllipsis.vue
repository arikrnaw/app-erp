<template>
  <span
    aria-hidden
    data-slot="pagination-ellipsis"
    :class="cn('flex size-9 items-center justify-center', className)"
    v-bind="$attrs"
  >
    <MoreHorizontal class="size-4" />
    <span class="sr-only">More pages</span>
  </span>
</template>

<script setup lang="ts">
import { MoreHorizontal } from 'lucide-vue-next'
import { cn } from '@/lib/utils'

interface PaginationEllipsisProps {
  className?: string
}

defineProps<PaginationEllipsisProps>()
</script>
