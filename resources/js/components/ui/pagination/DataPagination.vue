<template>
  <div v-if="showPagination" class="flex items-center justify-between px-2">
    <div class="flex-1 text-sm text-muted-foreground">
      Showing {{ startItem }} to {{ endItem }} of {{ totalItems }} results
    </div>
    
    <Pagination>
      <PaginationContent>
        <PaginationItem>
          <PaginationPrevious 
            :href="previousPageUrl"
            :class="{ 'pointer-events-none opacity-50': currentPage <= 1 }"
            @click.prevent="goToPage(currentPage - 1)"
          />
        </PaginationItem>
        
        <template v-for="page in visiblePages" :key="page">
          <PaginationItem v-if="page !== '...'">
            <PaginationLink 
              :href="`?page=${page}`"
              :isActive="page === currentPage"
              @click.prevent="goToPage(page)"
            >
              {{ page }}
            </PaginationLink>
          </PaginationItem>
          <PaginationItem v-else>
            <PaginationEllipsis />
          </PaginationItem>
        </template>
        
        <PaginationItem>
          <PaginationNext 
            :href="nextPageUrl"
            :class="{ 'pointer-events-none opacity-50': currentPage >= totalPages }"
            @click.prevent="goToPage(currentPage + 1)"
          />
        </PaginationItem>
      </PaginationContent>
    </Pagination>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import {
  Pagination,
  PaginationContent,
  PaginationItem,
  PaginationLink,
  PaginationPrevious,
  PaginationNext,
  PaginationEllipsis
} from './index'

interface Props {
  currentPage: number
  totalPages: number
  totalItems: number
  perPage: number
  showPagination?: boolean
}

interface Emits {
  (e: 'page-change', page: number): void
}

const props = withDefaults(defineProps<Props>(), {
  showPagination: true
})

const emit = defineEmits<Emits>()

// Computed properties
const startItem = computed(() => {
  return props.totalItems > 0 ? (props.currentPage - 1) * props.perPage + 1 : 0
})

const endItem = computed(() => {
  return Math.min(props.currentPage * props.perPage, props.totalItems)
})

const previousPageUrl = computed(() => {
  return props.currentPage > 1 ? `?page=${props.currentPage - 1}` : '#'
})

const nextPageUrl = computed(() => {
  return props.currentPage < props.totalPages ? `?page=${props.currentPage + 1}` : '#'
})

// Compute visible pages for pagination
const visiblePages = computed(() => {
  const pages: (number | string)[] = []
  const maxVisible = 5
  
  if (props.totalPages <= maxVisible) {
    // Show all pages if total is small
    for (let i = 1; i <= props.totalPages; i++) {
      pages.push(i)
    }
  } else {
    // Show first page
    pages.push(1)
    
    if (props.currentPage > 3) {
      pages.push('...')
    }
    
    // Show pages around current page
    const start = Math.max(2, props.currentPage - 1)
    const end = Math.min(props.totalPages - 1, props.currentPage + 1)
    
    for (let i = start; i <= end; i++) {
      pages.push(i)
    }
    
    if (props.currentPage < props.totalPages - 2) {
      pages.push('...')
    }
    
    // Show last page
    if (props.totalPages > 1) {
      pages.push(props.totalPages)
    }
  }
  
  return pages
})

// Methods
const goToPage = (page: number) => {
  if (page >= 1 && page <= props.totalPages && page !== props.currentPage) {
    emit('page-change', page)
  }
}
</script>
