<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';
import { router } from '@inertiajs/vue3';
import { Dialog, DialogContent } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    Search,
    Command,
    Users,
    Package,
    ShoppingCart,
    FileText,
    DollarSign,
    Settings,
    BarChart3,
    Building2,
    Truck,
    Wrench,
    Calendar,
    UserCheck,
    CreditCard,
    Warehouse,
    Factory,
    ClipboardList,
    TrendingUp,
    HelpCircle
} from 'lucide-vue-next';

interface CommandItem {
    id: string;
    title: string;
    description: string;
    icon: any;
    route: string;
    category: string;
    keywords: string[];
}

const props = defineProps<{
    modelValue?: boolean;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: boolean];
}>();

const isOpen = computed({
    get: () => props.modelValue ?? false,
    set: (value) => emit('update:modelValue', value)
});

const searchQuery = ref('');
const selectedIndex = ref(0);

const commands: CommandItem[] = [
    // Dashboard
    {
        id: 'dashboard',
        title: 'Dashboard',
        description: 'View your main dashboard',
        icon: BarChart3,
        route: '/dashboard',
        category: 'General',
        keywords: ['dashboard', 'home', 'main', 'overview']
    },

    // CRM
    {
        id: 'customers',
        title: 'Customers',
        description: 'Manage customer information',
        icon: Users,
        route: '/crm/customers',
        category: 'CRM',
        keywords: ['customers', 'clients', 'crm', 'customer management']
    },
    {
        id: 'prospects',
        title: 'Prospects',
        description: 'Manage potential customers',
        icon: UserCheck,
        route: '/crm/prospects',
        category: 'CRM',
        keywords: ['prospects', 'leads', 'potential customers']
    },
    {
        id: 'customer-segments',
        title: 'Customer Segments',
        description: 'Organize customers into segments',
        icon: Users,
        route: '/crm/customer-segments',
        category: 'CRM',
        keywords: ['segments', 'customer groups', 'targeting']
    },
    {
        id: 'follow-ups',
        title: 'Follow-ups',
        description: 'Manage customer follow-ups',
        icon: Calendar,
        route: '/crm/follow-ups',
        category: 'CRM',
        keywords: ['follow-ups', 'reminders', 'tasks']
    },
    {
        id: 'support-tickets',
        title: 'Support Tickets',
        description: 'Handle customer support requests',
        icon: HelpCircle,
        route: '/crm/support-tickets',
        category: 'CRM',
        keywords: ['support', 'tickets', 'help', 'customer service']
    },

    // Products & Inventory
    {
        id: 'products',
        title: 'Products',
        description: 'Manage your product catalog',
        icon: Package,
        route: '/products',
        category: 'Products',
        keywords: ['products', 'catalog', 'items', 'goods']
    },
    {
        id: 'inventory',
        title: 'Inventory',
        description: 'Track inventory levels',
        icon: Warehouse,
        route: '/inventory',
        category: 'Inventory',
        keywords: ['inventory', 'stock', 'warehouse', 'items']
    },
    {
        id: 'warehouses',
        title: 'Warehouses',
        description: 'Manage warehouse locations',
        icon: Building2,
        route: '/inventory/warehouses',
        category: 'Inventory',
        keywords: ['warehouses', 'locations', 'storage']
    },
    {
        id: 'reorder-alerts',
        title: 'Reorder Alerts',
        description: 'Monitor low stock alerts',
        icon: TrendingUp,
        route: '/inventory/reorder-alerts',
        category: 'Inventory',
        keywords: ['reorder', 'alerts', 'low stock', 'notifications']
    },

    // Sales
    {
        id: 'sales-orders',
        title: 'Sales Orders',
        description: 'Manage sales orders',
        icon: ShoppingCart,
        route: '/sales-orders',
        category: 'Sales',
        keywords: ['sales', 'orders', 'transactions', 'revenue']
    },

    // Purchasing
    {
        id: 'purchase-orders',
        title: 'Purchase Orders',
        description: 'Manage purchase orders',
        icon: ClipboardList,
        route: '/purchase-orders',
        category: 'Purchasing',
        keywords: ['purchase', 'orders', 'buying', 'procurement']
    },
    {
        id: 'suppliers',
        title: 'Suppliers',
        description: 'Manage supplier information',
        icon: Truck,
        route: '/suppliers',
        category: 'Purchasing',
        keywords: ['suppliers', 'vendors', 'partners']
    },

    // Finance
    {
        id: 'chart-of-accounts',
        title: 'Chart of Accounts',
        description: 'Manage financial accounts',
        icon: FileText,
        route: '/finance/chart-of-accounts',
        category: 'Finance',
        keywords: ['accounts', 'chart', 'financial', 'ledger']
    },
    {
        id: 'journal-entries',
        title: 'Journal Entries',
        description: 'Create and manage journal entries',
        icon: CreditCard,
        route: '/finance/journal-entries',
        category: 'Finance',
        keywords: ['journal', 'entries', 'transactions', 'accounting']
    },
    {
        id: 'general-ledger',
        title: 'General Ledger',
        description: 'View general ledger reports',
        icon: DollarSign,
        route: '/finance/general-ledger',
        category: 'Finance',
        keywords: ['ledger', 'general', 'financial reports']
    },
    {
        id: 'trial-balance',
        title: 'Trial Balance',
        description: 'View trial balance report',
        icon: BarChart3,
        route: '/finance/trial-balance',
        category: 'Finance',
        keywords: ['trial balance', 'balance sheet', 'financial']
    },

    // Manufacturing
    {
        id: 'bill-of-materials',
        title: 'Bill of Materials',
        description: 'Manage product components',
        icon: Factory,
        route: '/manufacturing/bill-of-materials',
        category: 'Manufacturing',
        keywords: ['bom', 'materials', 'components', 'manufacturing']
    },
    {
        id: 'work-orders',
        title: 'Work Orders',
        description: 'Manage production work orders',
        icon: Wrench,
        route: '/manufacturing/work-orders',
        category: 'Manufacturing',
        keywords: ['work orders', 'production', 'manufacturing']
    },
    {
        id: 'production-plans',
        title: 'Production Plans',
        description: 'Plan production schedules',
        icon: Calendar,
        route: '/manufacturing/production-plans',
        category: 'Manufacturing',
        keywords: ['production', 'plans', 'schedules', 'manufacturing']
    },

    // Projects
    {
        id: 'projects',
        title: 'Projects',
        description: 'Manage project information',
        icon: FileText,
        route: '/projects',
        category: 'Projects',
        keywords: ['projects', 'project management', 'tasks']
    },

    // HR
    {
        id: 'employees',
        title: 'Employees',
        description: 'Manage employee information',
        icon: Users,
        route: '/hr/employees',
        category: 'HR',
        keywords: ['employees', 'staff', 'personnel', 'hr']
    },
    {
        id: 'leave-requests',
        title: 'Leave Requests',
        description: 'Manage employee leave requests',
        icon: Calendar,
        route: '/hr/leave-requests',
        category: 'HR',
        keywords: ['leave', 'requests', 'vacation', 'time off']
    },
    {
        id: 'leave-types',
        title: 'Leave Types',
        description: 'Configure leave types',
        icon: Settings,
        route: '/hr/leave-types',
        category: 'HR',
        keywords: ['leave types', 'vacation types', 'hr settings']
    },
    {
        id: 'payroll-periods',
        title: 'Payroll Periods',
        description: 'Manage payroll periods',
        icon: DollarSign,
        route: '/hr/payroll-periods',
        category: 'HR',
        keywords: ['payroll', 'periods', 'salary', 'hr']
    },

    // Settings
    {
        id: 'profile',
        title: 'Profile',
        description: 'Manage your profile settings',
        icon: Settings,
        route: '/settings/profile',
        category: 'Settings',
        keywords: ['profile', 'settings', 'account', 'user']
    },
    {
        id: 'appearance',
        title: 'Appearance',
        description: 'Customize app appearance',
        icon: Settings,
        route: '/settings/appearance',
        category: 'Settings',
        keywords: ['appearance', 'theme', 'dark mode', 'light mode']
    }
];

const filteredCommands = computed(() => {
    if (!searchQuery.value) {
        return commands;
    }

    const query = searchQuery.value.toLowerCase();
    return commands.filter(command =>
        command.title.toLowerCase().includes(query) ||
        command.description.toLowerCase().includes(query) ||
        command.keywords.some(keyword => keyword.toLowerCase().includes(query)) ||
        command.category.toLowerCase().includes(query)
    );
});

const groupedCommands = computed(() => {
    const groups: Record<string, CommandItem[]> = {};
    filteredCommands.value.forEach(command => {
        if (!groups[command.category]) {
            groups[command.category] = [];
        }
        groups[command.category].push(command);
    });
    return groups;
});

const handleKeyDown = (event: KeyboardEvent) => {
    if (event.key === 'k' && (event.ctrlKey || event.metaKey)) {
        event.preventDefault();
        openCommandPalette();
    }

    if (!isOpen.value) return;

    switch (event.key) {
        case 'Escape':
            closeCommandPalette();
            break;
        case 'ArrowDown':
            event.preventDefault();
            selectedIndex.value = Math.min(selectedIndex.value + 1, filteredCommands.value.length - 1);
            break;
        case 'ArrowUp':
            event.preventDefault();
            selectedIndex.value = Math.max(selectedIndex.value - 1, 0);
            break;
        case 'Enter':
            event.preventDefault();
            if (filteredCommands.value[selectedIndex.value]) {
                executeCommand(filteredCommands.value[selectedIndex.value]);
            }
            break;
    }
};

const openCommandPalette = () => {
    isOpen.value = true;
    searchQuery.value = '';
    selectedIndex.value = 0;
    nextTick(() => {
        const input = document.querySelector('[data-command-input]') as HTMLInputElement;
        if (input) {
            input.focus();
        }
    });
};

const closeCommandPalette = () => {
    isOpen.value = false;
    searchQuery.value = '';
    selectedIndex.value = 0;
};

const executeCommand = (command: CommandItem) => {
    router.visit(command.route);
    closeCommandPalette();
};

const isSelected = (index: number) => {
    return index === selectedIndex.value;
};

onMounted(() => {
    document.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
    document.removeEventListener('keydown', handleKeyDown);
});
</script>

<template>
    <Dialog :open="isOpen" @update:open="isOpen = $event">
        <DialogContent class="sm:max-w-[600px] p-0">
            <div class="flex items-center border-b px-3">
                <Search class="mr-2 h-4 w-4 shrink-0 opacity-50" />
                <Input v-model="searchQuery" placeholder="Search commands..."
                    class="flex h-11 w-full rounded-md bg-transparent py-3 text-sm outline-none placeholder:text-muted-foreground disabled:cursor-not-allowed disabled:opacity-50 border-0 focus-visible:ring-0"
                    data-command-input />
                <div class="ml-auto flex items-center gap-1">
                    <Badge variant="secondary" class="text-xs">
                        <Command class="mr-1 h-3 w-3" />
                        Ctrl+K
                    </Badge>
                </div>
            </div>

            <div class="max-h-[400px] overflow-y-auto">
                <div v-if="filteredCommands.length === 0" class="p-6 text-center text-sm text-muted-foreground">
                    No commands found.
                </div>

                <div v-else class="p-2">
                    <div v-for="(commands, category) in groupedCommands" :key="category" class="mb-4">
                        <div class="px-2 py-1.5 text-xs font-semibold text-muted-foreground uppercase tracking-wide">
                            {{ category }}
                        </div>
                        <div class="space-y-1">
                            <Button v-for="(command, index) in commands" :key="command.id" variant="ghost"
                                :class="`w-full justify-start h-auto p-3 ${isSelected(index) ? 'bg-accent' : ''}`"
                                @click="executeCommand(command)">
                                <component :is="command.icon" class="mr-3 h-4 w-4 shrink-0" />
                                <div class="flex flex-col items-start">
                                    <div class="text-sm font-medium">{{ command.title }}</div>
                                    <div class="text-xs text-muted-foreground">{{ command.description }}</div>
                                </div>
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>
