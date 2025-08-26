<template>
  <button type="button" aria-label="Go to next page" :class="cn(
    'inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50',
    'h-9 px-3',
    'bg-background text-foreground hover:bg-accent hover:text-accent-foreground',
    'border border-input',
    className
  )" v-bind="$attrs">
    <span class="hidden sm:inline">Next</span>
    <ChevronRight />
  </button>
</template>

<script setup lang="ts">
import { ChevronRight } from 'lucide-vue-next'
import { cn } from '@/lib/utils'

interface PaginationNextProps {
  className?: string
}

defineProps<PaginationNextProps>()
</script>
