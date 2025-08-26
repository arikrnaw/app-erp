<template>
  <PaginationLink aria-label="Go to previous page" size="default" :class="cn('gap-1 px-2.5 sm:pl-2.5', className)"
    v-bind="$attrs">
    <ChevronLeft />
    <span class="hidden sm:block">Previous</span>
  </PaginationLink>
</template>

<script setup lang="ts">
import { ChevronLeft } from 'lucide-vue-next'
import { cn } from '@/lib/utils'
import PaginationLink from './PaginationLink.vue'

interface PaginationPreviousProps {
  className?: string
}

defineProps<PaginationPreviousProps>()
</script>
