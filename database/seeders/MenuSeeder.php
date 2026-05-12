<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        // ---------- 1. Dashboard ----------
        $dashboard = $this->menu('dashboard', [
            'name' => 'Dashboard',
            'icon' => 'home',
            'route_name' => 'dashboard',
            'route_pattern' => 'dashboard',
            'sort_order' => 1,
        ]);
        $this->attachPerms($dashboard, ['dashboard.view']);

        // ---------- 2. Sales ----------
        $sales = $this->group('sales', 'Sales', 'shopping-bag', 2);

        $salesQuot = $this->child('sales-quotations', 'Quotations', 'document-text', $sales, 1);
        $this->attachPerms($salesQuot, [
            'sales_quotations.view', 'sales_quotations.create',
            'sales_quotations.edit', 'sales_quotations.delete', 'sales_quotations.approve',
        ]);

        $salesOrd = $this->child('sales-orders', 'Sales Orders', 'clipboard-document-list', $sales, 2);
        $this->attachPerms($salesOrd, [
            'sales_orders.view', 'sales_orders.create',
            'sales_orders.edit', 'sales_orders.delete', 'sales_orders.approve',
        ]);

        $salesInv = $this->child('sales-invoices', 'Sales Invoices', 'document-currency-dollar', $sales, 3);
        $this->attachPerms($salesInv, [
            'sales_invoices.view', 'sales_invoices.create',
            'sales_invoices.edit', 'sales_invoices.delete', 'sales_invoices.post',
        ]);

        $customers = $this->child('customers', 'Customers', 'user-group', $sales, 4);
        $this->attachPerms($customers, [
            'customers.view', 'customers.create', 'customers.edit', 'customers.delete',
        ]);

        // ---------- 3. Purchasing ----------
        $purchasing = $this->group('purchasing', 'Purchasing', 'shopping-cart', 3);

        $pr = $this->child('purchase-requests', 'Purchase Requests', 'document-plus', $purchasing, 1);
        $this->attachPerms($pr, [
            'purchase_requests.view', 'purchase_requests.create',
            'purchase_requests.edit', 'purchase_requests.delete', 'purchase_requests.approve',
        ]);

        $po = $this->child('purchase-orders', 'Purchase Orders', 'clipboard-document-check', $purchasing, 2);
        $this->attachPerms($po, [
            'purchase_orders.view', 'purchase_orders.create',
            'purchase_orders.edit', 'purchase_orders.delete', 'purchase_orders.approve',
        ]);

        $pi = $this->child('purchase-invoices', 'Purchase Invoices', 'banknotes', $purchasing, 3);
        $this->attachPerms($pi, [
            'purchase_invoices.view', 'purchase_invoices.create',
            'purchase_invoices.edit', 'purchase_invoices.delete', 'purchase_invoices.post',
        ]);

        $suppliers = $this->child('suppliers', 'Suppliers', 'truck', $purchasing, 4);
        $this->attachPerms($suppliers, [
            'suppliers.view', 'suppliers.create', 'suppliers.edit', 'suppliers.delete',
        ]);

        // ---------- 4. Inventory ----------
        $inventory = $this->group('inventory', 'Inventory', 'cube', 4);

        $products = $this->child('products', 'Products', 'tag', $inventory, 1);
        $this->attachPerms($products, [
            'products.view', 'products.create', 'products.edit', 'products.delete',
        ]);

        $cats = $this->child('product-categories', 'Categories', 'rectangle-stack', $inventory, 2);
        $this->attachPerms($cats, [
            'product_categories.view', 'product_categories.create',
            'product_categories.edit', 'product_categories.delete',
        ]);

        $warehouses = $this->child('warehouses', 'Warehouses', 'building-storefront', $inventory, 3);
        $this->attachPerms($warehouses, [
            'warehouses.view', 'warehouses.create', 'warehouses.edit', 'warehouses.delete',
        ]);

        $stockMov = $this->child('stock-movements', 'Stock Movements', 'arrows-right-left', $inventory, 4);
        $this->attachPerms($stockMov, [
            'stock_movements.view', 'stock_movements.create',
            'stock_movements.edit', 'stock_movements.delete', 'stock_movements.approve',
        ]);

        $stockAdj = $this->child('stock-adjustments', 'Stock Adjustments', 'adjustments-horizontal', $inventory, 5);
        $this->attachPerms($stockAdj, [
            'stock_adjustments.view', 'stock_adjustments.create',
            'stock_adjustments.edit', 'stock_adjustments.delete', 'stock_adjustments.approve',
        ]);

        // ---------- 5. Transaction ----------
        $transaction = $this->group('transaction', 'Transaction', 'arrow-path', 5);

        $trx = $this->child('transactions', 'Journal Entries', 'book-open', $transaction, 1);
        $this->attachPerms($trx, [
            'transactions.view', 'transactions.create', 'transactions.edit',
            'transactions.delete', 'transactions.post', 'transactions.void',
        ]);

        $payments = $this->child('payments', 'Payments', 'credit-card', $transaction, 2);
        $this->attachPerms($payments, [
            'payments.view', 'payments.create', 'payments.edit',
            'payments.delete', 'payments.post',
        ]);

        $receipts = $this->child('receipts', 'Receipts', 'receipt-percent', $transaction, 3);
        $this->attachPerms($receipts, [
            'receipts.view', 'receipts.create', 'receipts.edit',
            'receipts.delete', 'receipts.post',
        ]);

        // ---------- 6. CRM ----------
        $crm = $this->group('crm', 'CRM', 'sparkles', 6);

        $leads = $this->child('leads', 'Leads', 'bolt', $crm, 1);
        $this->attachPerms($leads, [
            'leads.view', 'leads.create', 'leads.edit', 'leads.delete', 'leads.convert',
        ]);

        $contacts = $this->child('contacts', 'Contacts', 'identification', $crm, 2);
        $this->attachPerms($contacts, [
            'contacts.view', 'contacts.create', 'contacts.edit', 'contacts.delete',
        ]);

        $opps = $this->child('opportunities', 'Opportunities', 'presentation-chart-line', $crm, 3);
        $this->attachPerms($opps, [
            'opportunities.view', 'opportunities.create', 'opportunities.edit',
            'opportunities.delete', 'opportunities.convert',
        ]);

        $acts = $this->child('activities', 'Activities', 'calendar-days', $crm, 4);
        $this->attachPerms($acts, [
            'activities.view', 'activities.create', 'activities.edit', 'activities.delete',
        ]);

        $campaigns = $this->child('campaigns', 'Campaigns', 'megaphone', $crm, 5);
        $this->attachPerms($campaigns, [
            'campaigns.view', 'campaigns.create', 'campaigns.edit', 'campaigns.delete',
        ]);

        // ---------- 7. Reports ----------
        $reports = $this->group('reports', 'Reports', 'chart-bar', 7);

        $rs = $this->child('reports-sales', 'Sales Report', 'chart-bar-square', $reports, 1);
        $this->attachPerms($rs, ['reports_sales.view', 'reports_sales.export']);

        $rp = $this->child('reports-purchasing', 'Purchasing Report', 'chart-pie', $reports, 2);
        $this->attachPerms($rp, ['reports_purchasing.view', 'reports_purchasing.export']);

        $ri = $this->child('reports-inventory', 'Inventory Report', 'archive-box', $reports, 3);
        $this->attachPerms($ri, ['reports_inventory.view', 'reports_inventory.export']);

        $rt = $this->child('reports-transaction', 'Transaction Report', 'document-chart-bar', $reports, 4);
        $this->attachPerms($rt, ['reports_transaction.view', 'reports_transaction.export']);

        $rc = $this->child('reports-crm', 'CRM Report', 'chart-bar', $reports, 5);
        $this->attachPerms($rc, ['reports_crm.view', 'reports_crm.export']);

        $rf = $this->child('reports-financial', 'Financial Report', 'currency-dollar', $reports, 6);
        $this->attachPerms($rf, ['reports_financial.view', 'reports_financial.export']);

        // ---------- 8. Administration ----------
        $admin = $this->group('administration', 'Administration', 'cog-6-tooth', 90);

        $users = $this->child('users', 'Users', 'user', $admin, 1, 'users.index', 'users.*');
        $this->attachPerms($users, [
            'users.view', 'users.create', 'users.edit', 'users.delete', 'users.assign_roles',
        ]);


        $companies = $this->child('companies', 'Companies', 'building-office', $admin, 3, 'companies.index', 'companies.*');
        $this->attachPerms($companies, [
            'companies.view', 'companies.create', 'companies.edit', 'companies.delete',
        ]);

        $branches = $this->child('branches', 'Branches', 'building-office-2', $admin, 4, 'branches.index', 'branches.*');
        $this->attachPerms($branches, [
            'branches.view', 'branches.create', 'branches.edit', 'branches.delete',
        ]);

        $roles = $this->child('roles', 'Roles', 'lock-closed', $admin, 5, 'roles.index', 'roles.*');
        $this->attachPerms($roles, [
            'roles.view', 'roles.create', 'roles.edit', 'roles.delete',
        ]);

        $menus = $this->child('menus', 'Menus', 'bars-3', $admin, 6, 'menus.index', 'menus.*');
        $this->attachPerms($menus, [
            'menus.view', 'menus.create', 'menus.edit', 'menus.delete',
        ]);

        // ---------- 9. Nav user ----------
        Menu::firstOrCreate(['slug' => 'my-profile'], [
            'name' => 'My Profile',
            'icon' => 'identification',
            'route_name' => 'profile',
            'route_pattern' => 'profile',
            'parent_id' => null,
            'level' => 0,
            'sort_order' => 1,
            'layout' => 'nav_user',
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
