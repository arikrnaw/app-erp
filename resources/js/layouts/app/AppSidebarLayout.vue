<script setup lang="ts">
import { ref } from 'vue';
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import CommandPalette from '@/components/CommandPalette.vue';
import Toaster from '@/components/ui/sonner.vue';
import AiChatWidget from '@/components/AiChatWidget.vue';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const showCommandPalette = ref(false);

const openSearch = () => {
    showCommandPalette.value = true;
};
</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebar />
        <AppContent variant="sidebar" class="overflow-x-hidden">
            <AppSidebarHeader :breadcrumbs="breadcrumbs" @open-search="openSearch" />
            <slot />
        </AppContent>
    </AppShell>
    <CommandPalette v-model="showCommandPalette" />
    <Toaster />
    <AiChatWidget />
</template>
