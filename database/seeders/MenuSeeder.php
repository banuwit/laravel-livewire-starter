<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        // ---------- 1. General ----------
        $dashboard = $this->menu('dashboard', [
            'name' => 'Dashboard',
            'icon' => 'home',
            'route_name' => 'dashboard',
            'route_pattern' => 'dashboard',
            'sort_order' => 1,
        ]);
        $this->attachPerms($dashboard, ['dashboard.view']);

        $users = $this->menu('users', [
            'name' => 'Users',
            'icon' => 'user',
            'route_name' => 'users.index',
            'route_pattern' => 'users.*',
            'sort_order' => 2,
        ]);
        $this->attachPerms($users, [
            'users.view', 'users.create', 'users.edit', 'users.delete', 'users.assign_roles',
        ]);

        // ---------- 2. Master Data ----------
        $masterData = $this->group('master-data', 'Master Data', 'cog-6-tooth', 4);

        $organizations = $this->child('organizations', 'Organizations', 'building-office', $masterData, 1, 'organizations.index', 'organizations.*');
        $this->attachPerms($organizations, [
            'organizations.view', 'organizations.create', 'organizations.edit', 'organizations.delete',
        ]);

        $branches = $this->child('branches', 'Branches', 'building-office-2', $masterData, 2, 'branches.index', 'branches.*');
        $this->attachPerms($branches, [
            'branches.view', 'branches.create', 'branches.edit', 'branches.delete',
        ]);

        // ---------- 3. System Configuration ----------
        $configuration = $this->group('configuration', 'Configuration', 'cog-6-tooth', 5);

        $parameters = $this->child('parameters', 'Parameters', 'adjustments-horizontal', $configuration, 1, 'parameters.index', 'parameters.*');
        $this->attachPerms($parameters, [
            'parameters.view', 'parameters.create', 'parameters.edit', 'parameters.delete',
        ]);

        $roles = $this->child('roles', 'Roles', 'lock-closed', $configuration, 2, 'roles.index', 'roles.*');
        $this->attachPerms($roles, [
            'roles.view', 'roles.create', 'roles.edit', 'roles.delete',
        ]);

        $permissions = $this->child('permissions', 'Permissions', 'shield-check', $configuration, 3, 'permissions.index', 'permissions.*');
        $this->attachPerms($permissions, [
            'permissions.view', 'permissions.create', 'permissions.edit', 'permissions.delete',
        ]);

        $menus = $this->child('menus', 'Menus', 'bars-3', $configuration, 4, 'menus.index', 'menus.*');
        $this->attachPerms($menus, [
            'menus.view', 'menus.create', 'menus.edit', 'menus.delete',
        ]);

        $actLogs = $this->child('activity-logs', 'Activity Logs', 'clock', $configuration, 5, 'activity-logs.index', 'activity-logs.*');
        $this->attachPerms($actLogs, [
            'activity_logs.view', 'activity_logs.export',
        ]);

        // ---------- 4. User Menu ----------
        Menu::firstOrCreate(['slug' => 'my-profile'], [
            'name' => 'My Profile',
            'icon' => 'identification',
            'route_name' => 'profile.edit',
            'route_pattern' => 'settings/profile',
            'parent_id' => null,
            'level' => 0,
            'layout' => 'nav_user',
            'sort_order' => 1,
        ]);
    }

    private function menu(string $slug, array $attrs): Menu
    {
        return Menu::firstOrCreate(['slug' => $slug], array_merge([
            'parent_id' => null,
            'level' => 0,
            'layout' => 'sidebar',
            'route_name' => null,
            'route_pattern' => null,
        ], $attrs));
    }

    private function group(string $slug, string $name, string $icon, int $sort): Menu
    {
        return $this->menu($slug, [
            'name' => $name,
            'icon' => $icon,
            'sort_order' => $sort,
        ]);
    }

    private function child(
        string $slug,
        string $name,
        string $icon,
        Menu $parent,
        int $sort,
        ?string $routeName = null,
        ?string $routePattern = null,
    ): Menu {
        return Menu::firstOrCreate(['slug' => $slug], [
            'name' => $name,
            'icon' => $icon,
            'route_name' => $routeName,
            'route_pattern' => $routePattern,
            'parent_id' => $parent->id,
            'level' => 1,
            'sort_order' => $sort,
            'layout' => 'sidebar',
        ]);
    }

    private function attachPerms(Menu $menu, array $names): void
    {
        foreach ($names as $i => $name) {
            Permission::where('name', $name)->update([
                'menu_id' => $menu->id,
                'sort_order' => $i + 1,
            ]);
        }
    }
}
