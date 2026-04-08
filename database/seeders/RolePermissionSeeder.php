<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use App\Models\Branch;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Menu
            'menu.view',
            'menu.create',
            'menu.edit',
            'menu.delete',
            
            // Customers
            'customers.view',
            'customers.create',
            'customers.edit',
            'customers.delete',
            
            // Suppliers
            'suppliers.view',
            'suppliers.create',
            'suppliers.edit',
            'suppliers.delete',
            
            // Quotations
            'quotations.view',
            'quotations.create',
            'quotations.edit',
            'quotations.delete',
            'quotations.approve',
            'quotations.send',
            
            // Orders
            'orders.view',
            'orders.create',
            'orders.edit',
            'orders.delete',
            'orders.confirm',
            'orders.complete',
            'orders.cancel',
            
            // Invoices
            'invoices.view',
            'invoices.create',
            'invoices.edit',
            'invoices.delete',
            'invoices.restore',
            
            // Payments
            'payments.view',
            'payments.create',
            'payments.edit',
            'payments.delete',
            
            // Discounts
            'discounts.apply',
            'discounts.approve',
            'discounts.override',
            
            // Price Override
            'prices.override',
            
            // Reports
            'reports.view',
            'reports.view_all_branches',
            
            // Complaints
            'complaints.view',
            'complaints.update',
            'complaints.resolve',
            
            // Branches (Admin only)
            'branches.view',
            'branches.create',
            'branches.edit',
            'branches.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles
        $admin = Role::create(['name' => 'Admin']);
        $branchManager = Role::create(['name' => 'Branch Manager']);
        $staff = Role::create(['name' => 'Staff']);

        // Assign all permissions to Admin
        $admin->givePermissionTo(Permission::all());

        // Branch Manager permissions
        $branchManager->givePermissionTo([
            'menu.view', 'menu.create', 'menu.edit',
            'customers.view', 'customers.create', 'customers.edit',
            'suppliers.view', 'suppliers.create', 'suppliers.edit',
            'quotations.view', 'quotations.create', 'quotations.edit', 'quotations.approve', 'quotations.send',
            'orders.view', 'orders.create', 'orders.edit', 'orders.confirm', 'orders.complete',
            'invoices.view', 'invoices.create', 'invoices.edit',
            'payments.view', 'payments.create', 'payments.edit',
            'discounts.apply', 'discounts.approve',
            'reports.view',
            'complaints.view', 'complaints.update', 'complaints.resolve',
        ]);

        // Staff permissions
        $staff->givePermissionTo([
            'menu.view',
            'customers.view', 'customers.create', 'customers.edit',
            'quotations.view', 'quotations.create', 'quotations.edit', 'quotations.send',
            'orders.view', 'orders.create', 'orders.edit',
            'invoices.view', 'invoices.create',
            'payments.view', 'payments.create',
            'discounts.apply',
        ]);

        // Create default branch if none exists
        $branch = Branch::firstOrCreate(
            ['name' => 'Main Branch'],
            [
                'phone' => '1234567890',
                'address' => 'Main Address',
                'invoice_prefix' => 'INV',
                'status' => true,
            ]
        );

        // Create default admin user
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@kitchy.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
                'branch_id' => $branch->id,
            ]
        );
        $adminUser->assignRole('Admin');
    }
}
