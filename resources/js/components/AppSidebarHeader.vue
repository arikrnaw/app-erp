<script setup lang="ts">
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { Input } from '@/components/ui/input';
import { Search } from 'lucide-vue-next';
import type { BreadcrumbItemType } from '@/types';

const emit = defineEmits<{
    openSearch: [];
}>();

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItemType[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);

const openSearch = () => {
    emit('openSearch');
};
</script>

<template>
    <header
        class="flex h-16 shrink-0 items-center gap-2 border-b border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4">
        <div class="flex items-center gap-2">
            <SidebarTrigger class="-ml-1" />
            <template v-if="breadcrumbs && breadcrumbs.length > 0">
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </template>
        </div>

        <div class="ml-auto flex items-center gap-2">
            <div class="relative w-48 md:w-64">
                <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                <Input placeholder="Search anything..."
                    class="pl-9 pr-4 h-9 text-sm cursor-pointer hover:bg-accent/50 transition-colors" readonly
                    @click="openSearch" @keydown.ctrl.k.prevent="openSearch" @keydown.meta.k.prevent="openSearch" />
                <div class="absolute right-3 top-1/2 -translate-y-1/2 hidden md:block">
                    <kbd
                        class="pointer-events-none inline-flex h-5 select-none items-center gap-1 rounded border bg-muted px-1.5 pt-0.5 font-mono text-[10px] font-medium text-muted-foreground opacity-60">
                        <span class="text-xs">Ctrl + K</span>
                    </kbd>
                </div>
            </div>
        </div>
    </header>
</template>
