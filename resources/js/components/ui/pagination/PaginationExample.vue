<template>
  <div class="space-y-4">
    <h3 class="text-lg font-semibold">Pagination Example</h3>
    
    <!-- Basic Pagination -->
    <Pagination>
      <PaginationContent>
        <PaginationItem>
          <PaginationPrevious href="#" />
        </PaginationItem>
        <PaginationItem>
          <PaginationLink href="#" isActive>1</PaginationLink>
        </PaginationItem>
        <PaginationItem>
          <PaginationLink href="#">2</PaginationLink>
        </PaginationItem>
        <PaginationItem>
          <PaginationLink href="#">3</PaginationLink>
        </PaginationItem>
        <PaginationItem>
          <PaginationEllipsis />
        </PaginationItem>
        <PaginationItem>
          <PaginationNext href="#" />
        </PaginationItem>
      </PaginationContent>
    </Pagination>

    <!-- Pagination with Dynamic Data -->
    <div class="space-y-2">
      <h4 class="text-md font-medium">Dynamic Pagination</h4>
      <Pagination>
        <PaginationContent>
          <PaginationItem>
            <PaginationPrevious 
              :href="currentPage > 1 ? `?page=${currentPage - 1}` : '#'"
              :class="{ 'pointer-events-none opacity-50': currentPage <= 1 }"
            />
          </PaginationItem>
          
          <template v-for="page in visiblePages" :key="page">
            <PaginationItem v-if="page !== '...'">
              <PaginationLink 
                :href="`?page=${page}`"
                :isActive="page === currentPage"
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
              :href="currentPage < totalPages ? `?page=${currentPage + 1}` : '#'"
              :class="{ 'pointer-events-none opacity-50': currentPage >= totalPages }"
            />
          </PaginationItem>
        </PaginationContent>
      </Pagination>
      
      <div class="text-sm text-gray-500">
        Page {{ currentPage }} of {{ totalPages }} ({{ totalItems }} items)
      </div>
    </div>
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

// Example props for dynamic pagination
interface Props {
  currentPage?: number
  totalPages?: number
  totalItems?: number
}

const props = withDefaults(defineProps<Props>(), {
  currentPage: 1,
  totalPages: 10,
  totalItems: 100
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
</script>
