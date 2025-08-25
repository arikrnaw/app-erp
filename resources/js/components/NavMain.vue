<script setup lang="ts">
import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuButton, SidebarMenuItem, SidebarMenuSub, SidebarMenuSubButton, SidebarMenuSubItem } from '@/components/ui/sidebar';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { ChevronDown } from 'lucide-vue-next';
import { computed } from 'vue';

defineProps<{
    items: NavItem[];
}>();

const page = usePage();

// Helper function to check if a menu item or its sub items are active
const isMenuActive = (item: NavItem): boolean => {
    if (item.href && item.href === page.url) {
        return true;
    }

    if (item.items) {
        return item.items.some(subItem => subItem.href === page.url);
    }

    return false;
};

// Helper function to check if a sub menu should be expanded
const shouldExpandSubMenu = (item: NavItem): boolean => {
    if (!item.items) return false;

    return item.items.some(subItem => {
        if (subItem.href === page.url) return true;

        // Check if current URL starts with sub item href (for nested routes)
        if (subItem.href && page.url.startsWith(subItem.href)) return true;

        return false;
    });
};
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel>Platform</SidebarGroupLabel>
        <SidebarMenu>
            <template v-for="item in items" :key="item.title">
                <!-- Regular menu item (no submenu) -->
                <SidebarMenuItem v-if="!item.items || item.items.length === 0">
                    <SidebarMenuButton as-child :is-active="item.href === page.url" :tooltip="item.title">
                        <Link :href="item.href || '#'">
                        <component :is="item.icon" />
                        <span>{{ item.title }}</span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>

                <!-- Menu item with submenu using Collapsible -->
                <Collapsible v-else :default-open="shouldExpandSubMenu(item)" class="group/collapsible">
                    <SidebarMenuItem>
                        <CollapsibleTrigger as-child>
                            <SidebarMenuButton :is-active="isMenuActive(item)" :tooltip="item.title" class="w-full">
                                <component :is="item.icon" />
                                <span>{{ item.title }}</span>
                                <ChevronDown
                                    class="ml-auto transition-transform duration-200 group-data-[state=open]/collapsible:rotate-180" />
                            </SidebarMenuButton>
                        </CollapsibleTrigger>
                        <CollapsibleContent>
                            <SidebarMenuSub>
                                <SidebarMenuSubItem v-for="subItem in item.items" :key="subItem.title">
                                    <SidebarMenuButton as-child
                                        :is-active="subItem.href === page.url || page.url.startsWith(subItem.href || '')"
                                        :tooltip="subItem.title">
                                        <Link :href="subItem.href || '#'">
                                        <component :is="subItem.icon" />
                                        <span>{{ subItem.title }}</span>
                                        </Link>
                                    </SidebarMenuButton>
                                </SidebarMenuSubItem>
                            </SidebarMenuSub>
                        </CollapsibleContent>
                    </SidebarMenuItem>
                </Collapsible>
            </template>
        </SidebarMenu>
    </SidebarGroup>
</template>
