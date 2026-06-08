<?php

namespace Database\Seeders;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Seeder;

class RolesPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $crud = ['view', 'create', 'edit', 'delete'];

        $resources = [
            // Core
            'dashboard'     => ['view'],

            // Administration
            'users'         => [...$crud, 'assign_roles'],
            'roles'         => $crud,
            'permissions'   => $crud,
            'menus'         => $crud,
            'parameters'    => $crud,
            'organizations' => $crud,
            'branches'      => $crud,

            // Logs & Activity
            'activity_logs' => ['view', 'export'],
        ];

        $allPermissions = [];
        foreach ($resources as $resource => $actions) {
            foreach ($actions as $action) {
                $name = "{$resource}.{$action}";
                Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
                $allPermissions[] = $name;
            }
        }

        // Helper to expand resource keys into permission names
        $permsFor = function (array $keys) use ($resources): array {
            $out = [];
            foreach ($keys as $key) {
                foreach ($resources[$key] ?? [] as $action) {
                    $out[] = "{$key}.{$action}";
                }
            }
            return $out;
        };

        // Superadmin — gets all permissions (via Gate::before)
        Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'web']);

        // Administrator — full access to admin features
        Role::firstOrCreate(['name' => 'administrator', 'guard_name' => 'web'])
            ->givePermissionTo($permsFor([
                'dashboard',
                'users', 'roles', 'permissions', 'menus', 'parameters', 'organizations', 'branches',
                'activity_logs',
            ]));

        // Editor — limited dashboard access
        Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web'])
            ->givePermissionTo($permsFor(['dashboard']));

        // Staff — limited dashboard access
        Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web'])
            ->givePermissionTo($permsFor(['dashboard']));
    }
}
