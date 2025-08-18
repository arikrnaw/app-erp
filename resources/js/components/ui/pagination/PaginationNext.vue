<template>
  <PaginationLink
    aria-label="Go to next page"
    size="default"
    :class="cn('gap-1 px-2.5 sm:pr-2.5', className)"
    v-bind="$attrs"
  >
    <span class="hidden sm:block">Next</span>
    <ChevronRight />
  </PaginationLink>
</template>

<script setup lang="ts">
import { ChevronRight } from 'lucide-vue-next'
import { cn } from '@/lib/utils'
import PaginationLink from './PaginationLink.vue'

interface PaginationNextProps {
  className?: string
}

defineProps<PaginationNextProps>()
</script>
