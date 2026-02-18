<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Sites (super admin)
            'sites.manage',
            // Charges
            'charges.view', 'charges.create', 'charges.collect', 'charges.delete',
            // Expenses
            'expenses.view', 'expenses.create', 'expenses.update', 'expenses.pay', 'expenses.delete',
            // Accounts
            'accounts.view', 'accounts.manage',
            // Cash Accounts
            'cash-accounts.view', 'cash-accounts.manage',
            // Apartments
            'apartments.view', 'apartments.manage',
            // Users
            'users.view', 'users.manage',
            // Vendors
            'vendors.view', 'vendors.manage',
            // Templates
            'templates.view', 'templates.manage',
            // Reports
            'reports.view',
            // Receipts
            'receipts.view', 'receipts.print',
            // Payments
            'payments.view',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $superAdmin = Role::firstOrCreate(['name' => 'super-admin']);
        $superAdmin->givePermissionTo(Permission::all());

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->givePermissionTo(Permission::all()->where('name', '!=', 'sites.manage'));

        $owner = Role::firstOrCreate(['name' => 'owner']);
        $owner->givePermissionTo([
            'charges.view',
            'receipts.view', 'receipts.print',
        ]);

        $tenant = Role::firstOrCreate(['name' => 'tenant']);
        $tenant->givePermissionTo([
            'charges.view',
            'receipts.view',
        ]);

        $vendor = Role::firstOrCreate(['name' => 'vendor']);
        $vendor->givePermissionTo([
            'expenses.view',
            'payments.view',
        ]);
    }
}
