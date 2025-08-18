<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Support\Str;

class RbacSeeder extends Seeder
{
    public function run(): void
    {
        $companyId = 1; // Assuming company ID 1 exists

        // Create permissions for different modules
        $permissions = [
            // CRM Module
            ['name' => 'View CRM', 'module' => 'crm', 'action' => 'view', 'description' => 'Can view CRM module'],
            ['name' => 'Manage Prospects', 'module' => 'crm', 'action' => 'prospects', 'description' => 'Can manage prospects'],
            ['name' => 'Manage Follow-ups', 'module' => 'crm', 'action' => 'follow-ups', 'description' => 'Can manage follow-ups'],
            ['name' => 'Manage Support Tickets', 'module' => 'crm', 'action' => 'support-tickets', 'description' => 'Can manage support tickets'],
            ['name' => 'Manage Customer Segments', 'module' => 'crm', 'action' => 'customer-segments', 'description' => 'Can manage customer segments'],
            ['name' => 'Manage Customers', 'module' => 'crm', 'action' => 'customers', 'description' => 'Can manage customers'],

            // Project Management Module
            ['name' => 'View Projects', 'module' => 'projects', 'action' => 'view', 'description' => 'Can view project management module'],
            ['name' => 'Manage Projects', 'module' => 'projects', 'action' => 'manage', 'description' => 'Can manage projects'],
            ['name' => 'Manage Tasks', 'module' => 'projects', 'action' => 'tasks', 'description' => 'Can manage project tasks'],
            ['name' => 'Manage Resources', 'module' => 'projects', 'action' => 'resources', 'description' => 'Can manage project resources'],
            ['name' => 'Manage Costs', 'module' => 'projects', 'action' => 'costs', 'description' => 'Can manage project costs'],
            ['name' => 'Manage Milestones', 'module' => 'projects', 'action' => 'milestones', 'description' => 'Can manage project milestones'],
            ['name' => 'Manage Teams', 'module' => 'projects', 'action' => 'teams', 'description' => 'Can manage project teams'],

            // Reports & Analytics Module
            ['name' => 'View Reports', 'module' => 'reports', 'action' => 'view', 'description' => 'Can view reports and analytics'],
            ['name' => 'Manage Financial Reports', 'module' => 'reports', 'action' => 'financial', 'description' => 'Can manage financial reports'],
            ['name' => 'Manage Business Analytics', 'module' => 'reports', 'action' => 'analytics', 'description' => 'Can manage business analytics'],
            ['name' => 'Manage Report Schedules', 'module' => 'reports', 'action' => 'schedules', 'description' => 'Can manage report schedules'],

            // Customer Service Module
            ['name' => 'View Customer Service', 'module' => 'customer-service', 'action' => 'view', 'description' => 'Can view customer service module'],
            ['name' => 'Manage Complaints', 'module' => 'customer-service', 'action' => 'complaints', 'description' => 'Can manage customer complaints'],
            ['name' => 'Manage After-Sales Services', 'module' => 'customer-service', 'action' => 'after-sales', 'description' => 'Can manage after-sales services'],
            ['name' => 'Manage Service Tickets', 'module' => 'customer-service', 'action' => 'service-tickets', 'description' => 'Can manage service tickets'],
            ['name' => 'Manage Service Categories', 'module' => 'customer-service', 'action' => 'categories', 'description' => 'Can manage service categories'],

            // Settings Module
            ['name' => 'View Settings', 'module' => 'settings', 'action' => 'view', 'description' => 'Can view settings module'],
            ['name' => 'Manage General Settings', 'module' => 'settings', 'action' => 'general', 'description' => 'Can manage general settings'],
            ['name' => 'Manage User Management', 'module' => 'settings', 'action' => 'users', 'description' => 'Can manage users'],
            ['name' => 'Manage System Configuration', 'module' => 'settings', 'action' => 'system', 'description' => 'Can manage system configuration'],
            ['name' => 'Manage Backup & Restore', 'module' => 'settings', 'action' => 'backup', 'description' => 'Can manage backup and restore'],
            ['name' => 'Manage RBAC', 'module' => 'settings', 'action' => 'rbac', 'description' => 'Can manage roles and permissions'],

            // ERP Core Module
            ['name' => 'View ERP', 'module' => 'erp', 'action' => 'view', 'description' => 'Can view ERP core module'],
            ['name' => 'Manage Products', 'module' => 'erp', 'action' => 'products', 'description' => 'Can manage products'],
            ['name' => 'Manage Suppliers', 'module' => 'erp', 'action' => 'suppliers', 'description' => 'Can manage suppliers'],
            ['name' => 'Manage Sales Orders', 'module' => 'erp', 'action' => 'sales-orders', 'description' => 'Can manage sales orders'],
            ['name' => 'Manage Purchase Orders', 'module' => 'erp', 'action' => 'purchase-orders', 'description' => 'Can manage purchase orders'],
            ['name' => 'Manage Invoices', 'module' => 'erp', 'action' => 'invoices', 'description' => 'Can manage invoices'],
            ['name' => 'Manage Bills', 'module' => 'erp', 'action' => 'bills', 'description' => 'Can manage bills'],
            ['name' => 'Manage Payments', 'module' => 'erp', 'action' => 'payments', 'description' => 'Can manage payments'],
            ['name' => 'Manage Inventory', 'module' => 'erp', 'action' => 'inventory', 'description' => 'Can manage inventory'],
            ['name' => 'Manage Accounting', 'module' => 'erp', 'action' => 'accounting', 'description' => 'Can manage accounting'],
        ];

        foreach ($permissions as $permissionData) {
            Permission::create([
                'company_id' => $companyId,
                'name' => $permissionData['name'],
                'slug' => Str::slug("{$permissionData['module']}.{$permissionData['action']}"),
                'module' => $permissionData['module'],
                'action' => $permissionData['action'],
                'description' => $permissionData['description'],
            ]);
        }

        // Create default roles
        $roles = [
            [
                'name' => 'Super Admin',
                'description' => 'Full access to all modules and features',
                'is_system' => true,
                'permissions' => Permission::all()->pluck('id')->toArray(),
            ],
            [
                'name' => 'Admin',
                'description' => 'Administrative access to most modules',
                'is_system' => true,
                'permissions' => Permission::whereNotIn('module', ['settings'])->pluck('id')->toArray(),
            ],
            [
                'name' => 'Manager',
                'description' => 'Manager access to assigned modules',
                'is_system' => true,
                'permissions' => Permission::whereIn('module', ['crm', 'projects', 'reports', 'customer-service'])->pluck('id')->toArray(),
            ],
            [
                'name' => 'User',
                'description' => 'Basic user access',
                'is_system' => true,
                'permissions' => Permission::whereIn('action', ['view'])->pluck('id')->toArray(),
            ],
            [
                'name' => 'CRM Manager',
                'description' => 'CRM module manager',
                'is_system' => false,
                'permissions' => Permission::where('module', 'crm')->pluck('id')->toArray(),
            ],
            [
                'name' => 'Project Manager',
                'description' => 'Project management module manager',
                'is_system' => false,
                'permissions' => Permission::where('module', 'projects')->pluck('id')->toArray(),
            ],
            [
                'name' => 'Customer Service Agent',
                'description' => 'Customer service module access',
                'is_system' => false,
                'permissions' => Permission::where('module', 'customer-service')->pluck('id')->toArray(),
            ],
        ];

        foreach ($roles as $roleData) {
            $role = Role::create([
                'company_id' => $companyId,
                'name' => $roleData['name'],
                'slug' => Str::slug($roleData['name']),
                'description' => $roleData['description'],
                'is_system' => $roleData['is_system'],
            ]);

            $role->syncPermissions($roleData['permissions']);
        }

        // Assign Super Admin role to existing users (if any)
        $superAdminRole = Role::where('slug', 'super-admin')->first();
        if ($superAdminRole) {
            User::where('company_id', $companyId)->each(function ($user) use ($superAdminRole) {
                $user->assignRole($superAdminRole);
            });
        }
    }
}
