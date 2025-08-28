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

        // Check sub-submenu items
        if (subItem.items) {
            return subItem.items.some(subSubItem => {
                if (subSubItem.href === page.url) return true;
                if (subSubItem.href && page.url.startsWith(subSubItem.href)) return true;
                return false;
            });
        }

        return false;
    });
};

// Helper function to check if a sub-submenu should be expanded
const shouldExpandSubSubMenu = (subItem: NavItem): boolean => {
    if (!subItem.items) return false;

    return subItem.items.some(subSubItem => {
        if (subSubItem.href === page.url) return true;
        if (subSubItem.href && page.url.startsWith(subSubItem.href)) return true;
        return false;
    });
};

// Helper function to check if a submenu is active
const isSubMenuActive = (subItem: NavItem): boolean => {
    if (subItem.href && (subItem.href === page.url || page.url.startsWith(subItem.href))) {
        return true;
    }

    if (subItem.items) {
        return subItem.items.some(subSubItem => {
            if (subSubItem.href === page.url) return true;
            if (subSubItem.href && page.url.startsWith(subSubItem.href)) return true;
            return false;
        });
    }

    return false;
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
                                    <!-- Sub-submenu item -->
                                    <Collapsible v-if="subItem.items && subItem.items.length > 0"
                                        :default-open="shouldExpandSubSubMenu(subItem)" class="group/subsub">
                                        <CollapsibleTrigger as-child>
                                            <SidebarMenuButton :is-active="isSubMenuActive(subItem)"
                                                :tooltip="subItem.title" class="w-full">
                                                <component :is="subItem.icon" />
                                                <span>{{ subItem.title }}</span>
                                                <ChevronDown
                                                    class="ml-auto transition-transform duration-200 group-data-[state=open]/subsub:rotate-180" />
                                            </SidebarMenuButton>
                                        </CollapsibleTrigger>
                                        <CollapsibleContent>
                                            <SidebarMenuSub>
                                                <SidebarMenuSubItem v-for="subSubItem in subItem.items"
                                                    :key="subSubItem.title">
                                                    <SidebarMenuButton as-child
                                                        :is-active="subSubItem.href === page.url || page.url.startsWith(subSubItem.href || '')"
                                                        :tooltip="subSubItem.title">
                                                        <Link :href="subSubItem.href || '#'">
                                                        <span>{{ subSubItem.title }}</span>
                                                        </Link>
                                                    </SidebarMenuButton>
                                                </SidebarMenuSubItem>
                                            </SidebarMenuSub>
                                        </CollapsibleContent>
                                    </Collapsible>

                                    <!-- Direct submenu item -->
                                    <SidebarMenuButton v-else as-child
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
