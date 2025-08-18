# Vue Pagination Components

Komponen pagination Vue yang dikonversi dari React pagination.tsx dengan styling Shadcn UI.

## Komponen yang Tersedia

### 1. Pagination (Container)
Komponen utama yang membungkus seluruh pagination.

```vue
<template>
  <Pagination>
    <PaginationContent>
      <!-- Pagination items -->
    </PaginationContent>
  </Pagination>
</template>
```

### 2. PaginationContent
Container untuk item-item pagination.

```vue
<template>
  <PaginationContent>
    <PaginationItem>
      <!-- Pagination links -->
    </PaginationItem>
  </PaginationContent>
</template>
```

### 3. PaginationItem
Item individual dalam pagination.

```vue
<template>
  <PaginationItem>
    <PaginationLink href="#" isActive>1</PaginationLink>
  </PaginationItem>
</template>
```

### 4. PaginationLink
Link untuk halaman individual.

```vue
<template>
  <PaginationLink 
    href="#" 
    :isActive="currentPage === 1"
    size="default"
  >
    1
  </PaginationLink>
</template>
```

**Props:**
- `isActive?: boolean` - Menandakan halaman aktif
- `size?: 'default' | 'sm' | 'lg' | 'icon'` - Ukuran link
- `className?: string` - Class tambahan

### 5. PaginationPrevious
Link untuk halaman sebelumnya.

```vue
<template>
  <PaginationPrevious href="#" />
</template>
```

### 6. PaginationNext
Link untuk halaman berikutnya.

```vue
<template>
  <PaginationNext href="#" />
</template>
```

### 7. PaginationEllipsis
Titik-titik untuk menandakan halaman yang tidak ditampilkan.

```vue
<template>
  <PaginationEllipsis />
</template>
```

## Komponen Praktis: DataPagination

Komponen `DataPagination` adalah wrapper yang memudahkan penggunaan pagination dengan data dari API.

### Penggunaan Dasar

```vue
<template>
  <DataPagination
    :current-page="pagination.current_page"
    :total-pages="pagination.last_page"
    :total-items="pagination.total"
    :per-page="pagination.per_page"
    @page-change="handlePageChange"
  />
</template>

<script setup lang="ts">
import { DataPagination } from '@/components/ui/pagination'

const pagination = ref({
  current_page: 1,
  last_page: 10,
  per_page: 15,
  total: 150,
})

const handlePageChange = (page: number) => {
  pagination.value.current_page = page
  fetchData()
}
</script>
```

### Props DataPagination

- `currentPage: number` - Halaman saat ini
- `totalPages: number` - Total halaman
- `totalItems: number` - Total item
- `perPage: number` - Item per halaman
- `showPagination?: boolean` - Tampilkan pagination (default: true)

### Events DataPagination

- `@page-change` - Dipanggil ketika halaman berubah

## Contoh Penggunaan Lengkap

```vue
<template>
  <div class="space-y-4">
    <!-- Data Table -->
    <div class="bg-white rounded-lg shadow-sm border">
      <table class="w-full">
        <thead>
          <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in data" :key="item.id">
            <td>{{ item.name }}</td>
            <td>{{ item.email }}</td>
            <td>
              <Button size="sm">Edit</Button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <DataPagination
      :current-page="pagination.current_page"
      :total-pages="pagination.last_page"
      :total-items="pagination.total"
      :per-page="pagination.per_page"
      @page-change="handlePageChange"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { DataPagination } from '@/components/ui/pagination'
import { apiService } from '@/services/api'

const data = ref([])
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
})

const fetchData = async () => {
  try {
    const response = await apiService.getData({
      page: pagination.value.current_page,
    })
    data.value = response.data
    pagination.value = {
      current_page: response.meta.current_page,
      last_page: response.meta.last_page,
      per_page: response.meta.per_page,
      total: response.meta.total,
    }
  } catch (error) {
    console.error('Error fetching data:', error)
  }
}

const handlePageChange = (page: number) => {
  pagination.value.current_page = page
  fetchData()
}

onMounted(() => {
  fetchData()
})
</script>
```

## Import

```typescript
// Import semua komponen
import {
  Pagination,
  PaginationContent,
  PaginationItem,
  PaginationLink,
  PaginationPrevious,
  PaginationNext,
  PaginationEllipsis,
  DataPagination
} from '@/components/ui/pagination'

// Atau import individual
import { DataPagination } from '@/components/ui/pagination'
```

## Fitur

- ✅ Responsive design
- ✅ Accessibility (ARIA labels)
- ✅ TypeScript support
- ✅ Shadcn UI styling
- ✅ Event handling
- ✅ Dynamic page calculation
- ✅ Ellipsis for large page counts
- ✅ Previous/Next navigation
- ✅ Active page highlighting

## Styling

Komponen menggunakan Tailwind CSS dan Shadcn UI classes. Anda dapat menyesuaikan styling dengan menambahkan class tambahan melalui prop `className`.
