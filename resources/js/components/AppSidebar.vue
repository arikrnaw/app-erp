<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import ScrollArea from '@/components/ui/scroll-area.vue';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid, Package, Users, Truck, ShoppingCart, ShoppingBag, DollarSign, BookOpenCheck, Calculator, Receipt, FileText, CreditCard, Warehouse, Package2, AlertTriangle, TrendingUp, BarChart3, FileSpreadsheet, ClipboardList, PackageCheck, RotateCcw, Settings, Calendar, Wrench, UserCheck, Clock, CreditCard as CreditCardIcon, MessageSquare, Target, Users2, DollarSign as DollarSignIcon, Headphones, Shield, Ticket, Tag, Bot, Globe, Building, CheckCircle } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Finance',
        icon: DollarSign,
        items: [
            {
                title: 'Chart of Accounts',
                href: '/finance/chart-of-accounts',
                icon: BookOpenCheck,
            },
            {
                title: 'Journal Entries',
                href: '/finance/journal-entries',
                icon: FileText,
            },
            {
                title: 'General Ledger',
                href: '/finance/general-ledger',
                icon: Calculator,
            },
            {
                title: 'Trial Balance',
                href: '/finance/trial-balance',
                icon: BookOpenCheck,
            },
            {
                title: 'Accounts Receivable',
                href: '/finance/accounts-receivable/invoices',
                icon: Receipt,
            },
            {
                title: 'Accounts Payable',
                href: '/finance/accounts-payable/bills',
                icon: CreditCard,
            },
            {
                title: 'Tax Management',
                href: '/finance/tax-management/tax-rates',
                icon: FileSpreadsheet,
            },
            {
                title: 'Bank Reconciliation',
                href: '/finance/bank-reconciliation/bank-accounts',
                icon: BarChart3,
            },
            {
                title: 'Cash Management',
                href: '/finance/cash-management',
                icon: CreditCard,
            },
            {
                title: 'Fixed Assets',
                href: '/finance/fixed-assets',
                icon: Building,
            },
            {
                title: 'Budgeting',
                href: '/finance/budgeting',
                icon: TrendingUp,
            },
            {
                title: 'Multi-Currency',
                href: '/finance/multi-currency',
                icon: Globe,
            },
            {
                title: 'Approval Workflow',
                href: '/finance/approval-workflow',
                icon: CheckCircle,
            },
            {
                title: 'Financial Reports',
                href: '/finance/reports/balance-sheet',
                icon: TrendingUp,
            },
        ],
    },
    {
        title: 'Products',
        href: '/products',
        icon: Package,
    },
    {
        title: 'Purchasing',
        icon: ShoppingBag,
        items: [
            {
                title: 'Purchase Requests',
                href: '/purchasing/purchase-requests',
                icon: ClipboardList,
            },
            {
                title: 'Purchase Orders',
                href: '/purchase-orders',
                icon: ShoppingBag,
            },
            {
                title: 'Goods Receipts',
                href: '/purchasing/goods-receipts',
                icon: PackageCheck,
            },
            {
                title: 'Purchase Returns',
                href: '/purchasing/purchase-returns',
                icon: RotateCcw,
            },
        ],
    },
    {
        title: 'Inventory',
        icon: Warehouse,
        items: [
            {
                title: 'Transactions',
                href: '/inventory',
                icon: Package2,
            },
            {
                title: 'Warehouses',
                href: '/inventory/warehouses',
                icon: Warehouse,
            },
            {
                title: 'Warehouse Locations',
                href: '/inventory/warehouse-locations',
                icon: Package2,
            },
            {
                title: 'Product Lots',
                href: '/inventory/product-lots',
                icon: Package2,
            },
            {
                title: 'Reorder Alerts',
                href: '/inventory/reorder-alerts',
                icon: AlertTriangle,
            },
        ],
    },
    {
        title: 'Manufacturing',
        icon: Settings,
        items: [
            {
                title: 'Bill of Materials',
                href: '/manufacturing/bill-of-materials',
                icon: BookOpen,
            },
            {
                title: 'Production Plans',
                href: '/manufacturing/production-plans',
                icon: Calendar,
            },
            {
                title: 'Work Orders',
                href: '/manufacturing/work-orders',
                icon: Wrench,
            },
        ],
    },
    {
        title: 'HR Management',
        icon: UserCheck,
        items: [
            {
                title: 'Employees',
                href: '/hr/employees',
                icon: Users,
            },
            {
                title: 'Leave Types',
                href: '/hr/leave-types',
                icon: Calendar,
            },
            {
                title: 'Leave Requests',
                href: '/hr/leave-requests',
                icon: Clock,
            },
            {
                title: 'Payroll Periods',
                href: '/hr/payroll-periods',
                icon: CreditCardIcon,
            },
        ],
    },
    {
        title: 'CRM',
        icon: Users,
        items: [
            {
                title: 'Customers',
                href: '/crm/customers',
                icon: Users,
            },
            {
                title: 'Prospects',
                href: '/crm/prospects',
                icon: UserCheck,
            },
            {
                title: 'Follow-ups',
                href: '/crm/follow-ups',
                icon: Clock,
            },
            {
                title: 'Support Tickets',
                href: '/crm/support-tickets',
                icon: MessageSquare,
            },
            {
                title: 'Customer Segments',
                href: '/crm/customer-segments',
                icon: Users,
            },
        ],
    },
    {
        title: 'Project Management',
        icon: Target,
        items: [
            {
                title: 'Projects',
                href: '/projects',
                icon: Target,
            },
            {
                title: 'Tasks',
                href: '/projects/tasks',
                icon: ClipboardList,
            },
            {
                title: 'Resources',
                href: '/projects/resources',
                icon: Users2,
            },
            {
                title: 'Costs',
                href: '/projects/costs',
                icon: DollarSignIcon,
            },
            {
                title: 'Milestones',
                href: '/projects/milestones',
                icon: Calendar,
            },
            {
                title: 'Teams',
                href: '/projects/teams',
                icon: Users,
            },
        ],
    },
    {
        title: 'Suppliers',
        href: '/suppliers',
        icon: Truck,
    },
    {
        title: 'Sales Orders',
        href: '/sales-orders',
        icon: ShoppingCart,
    },
    {
        title: 'Reporting & Analytics',
        icon: BarChart3,
        items: [
            {
                title: 'Dashboard',
                href: '/reports',
                icon: BarChart3,
            },
            {
                title: 'Financial Reports',
                href: '/reports/financial',
                icon: FileText,
            },
            {
                title: 'Business Analytics',
                href: '/reports/analytics',
                icon: TrendingUp,
            },
            {
                title: 'Report Schedules',
                href: '/reports/schedules',
                icon: Clock,
            },
        ],
    },
    {
        title: 'Customer Service',
        icon: Headphones,
        items: [
            {
                title: 'Dashboard',
                href: '/customer-service',
                icon: BarChart3,
            },
            {
                title: 'Customer Complaints',
                href: '/customer-service/complaints',
                icon: AlertTriangle,
            },
            {
                title: 'After-Sales Services',
                href: '/customer-service/after-sales-services',
                icon: Shield,
            },
            {
                title: 'Service Tickets',
                href: '/customer-service/service-tickets',
                icon: Ticket,
            },
            {
                title: 'Service Categories',
                href: '/customer-service/service-categories',
                icon: Tag,
            },
        ],
    },
    {
        title: 'AI Assistant',
        href: '/ai-chat',
        icon: Bot,
    },
    {
        title: 'Settings',
        icon: Settings,
        items: [
            {
                title: 'General Settings',
                href: '/settings/rbac/general',
                icon: Settings,
            },
            {
                title: 'User Management',
                href: '/settings/rbac/users-management',
                icon: Users,
            },
            {
                title: 'Roles & Permissions',
                href: '/settings/rbac',
                icon: Shield,
            },
            {
                title: 'System Configuration',
                href: '/settings/rbac/system',
                icon: Settings,
            },
            {
                title: 'Backup & Restore',
                href: '/settings/rbac/backup',
                icon: Folder,
            },
        ],
    },
];

const footerNavItems: NavItem[] = [];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                        <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent class="flex-1 overflow-hidden">
            <ScrollArea class="h-full" style="max-height: calc(100vh - 200px);">
                <NavMain :items="mainNavItems" />
            </ScrollArea>
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
