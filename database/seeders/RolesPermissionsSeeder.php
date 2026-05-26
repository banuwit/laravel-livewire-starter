<?php

namespace Database\Seeders;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolesPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $crud = ['view', 'create', 'edit', 'delete'];

        $resources = [
            // Core
            'dashboard'              => ['view'],

            // Administration
            'users'                  => [...$crud, 'assign_roles'],
            'roles'                  => $crud,
            'menus'                  => $crud,
            'profiles'               => $crud,
            'parameters'             => $crud,
            'companies'              => $crud,
            'branches'               => $crud,

            // Sales
            'sales_quotations'       => [...$crud, 'approve'],
            'sales_orders'           => [...$crud, 'approve'],
            'sales_invoices'         => [...$crud, 'post'],
            'customers'              => $crud,

            // Transaction
            'transactions'           => [...$crud, 'post', 'void'],
            'payments'               => [...$crud, 'post'],
            'receipts'               => [...$crud, 'post'],

            // Purchasing
            'purchase_requests'      => [...$crud, 'approve'],
            'purchase_orders'        => [...$crud, 'approve'],
            'purchase_invoices'      => [...$crud, 'post'],
            'suppliers'              => $crud,

            // Inventory
            'products'               => $crud,
            'product_categories'     => $crud,
            'warehouses'             => $crud,
            'stock_movements'        => [...$crud, 'approve'],
            'stock_adjustments'      => [...$crud, 'approve'],

            // CRM
            'leads'                  => [...$crud, 'convert'],
            'contacts'               => $crud,
            'opportunities'          => [...$crud, 'convert'],
            'activities'             => $crud,
            'campaigns'              => $crud,

            // Reports (view-only + export)
            'reports_sales'          => ['view', 'export'],
            'reports_purchasing'     => ['view', 'export'],
            'reports_inventory'      => ['view', 'export'],
            'reports_transaction'    => ['view', 'export'],
            'reports_crm'            => ['view', 'export'],
            'reports_financial'      => ['view', 'export'],
        ];

        $allPermissions = [];
        foreach ($resources as $resource => $actions) {
            foreach ($actions as $action) {
                $name = "{$resource}.{$action}";
                Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
                $allPermissions[] = $name;
            }
        }

        // Helper to expand a list of resource keys into all their permission names
        $permsFor = function (array $keys) use ($resources): array {
            $out = [];
            foreach ($keys as $key) {
                foreach ($resources[$key] ?? [] as $action) {
                    $out[] = "{$key}.{$action}";
                }
            }
            return $out;
        };

        Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'web']);

        Role::firstOrCreate(['name' => 'administrator', 'guard_name' => 'web'])
            ->givePermissionTo($permsFor([
                'dashboard',
                'users', 'roles', 'menus', 'profiles', 'parameters', 'companies', 'branches',
                'reports_sales', 'reports_purchasing', 'reports_inventory',
                'reports_transaction', 'reports_crm', 'reports_financial',
            ]));

        Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web'])
            ->givePermissionTo($permsFor([
                'dashboard',
            ]));

        Role::firstOrCreate(['name' => 'sales', 'guard_name' => 'web'])
            ->givePermissionTo($permsFor([
                'dashboard',
                'sales_quotations', 'sales_orders', 'sales_invoices', 'customers',
                'leads', 'contacts', 'opportunities', 'activities',
                'reports_sales', 'reports_crm',
            ]));

        Role::firstOrCreate(['name' => 'purchasing', 'guard_name' => 'web'])
            ->givePermissionTo($permsFor([
                'dashboard',
                'purchase_requests', 'purchase_orders', 'purchase_invoices', 'suppliers',
                'reports_purchasing',
            ]));

        Role::firstOrCreate(['name' => 'inventory', 'guard_name' => 'web'])
            ->givePermissionTo($permsFor([
                'dashboard',
                'products', 'product_categories', 'warehouses',
                'stock_movements', 'stock_adjustments',
                'reports_inventory',
            ]));

        Role::firstOrCreate(['name' => 'accounting', 'guard_name' => 'web'])
            ->givePermissionTo($permsFor([
                'dashboard',
                'transactions', 'payments', 'receipts',
                'sales_invoices', 'purchase_invoices',
                'reports_financial', 'reports_transaction',
            ]));

        Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web'])
            ->givePermissionTo($permsFor(['dashboard']));
    }
}
