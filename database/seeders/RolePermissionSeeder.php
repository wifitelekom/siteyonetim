<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Charges
            'charges.view', 'charges.create', 'charges.collect', 'charges.delete',
            // Expenses
            'expenses.view', 'expenses.create', 'expenses.pay', 'expenses.delete',
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
            Permission::create(['name' => $permission]);
        }

        // Admin - tüm yetkiler
        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo(Permission::all());

        // Owner - sınırlı yetkiler
        $owner = Role::create(['name' => 'owner']);
        $owner->givePermissionTo([
            'charges.view',
            'receipts.view', 'receipts.print',
        ]);

        // Tenant - sınırlı yetkiler
        $tenant = Role::create(['name' => 'tenant']);
        $tenant->givePermissionTo([
            'charges.view',
            'receipts.view',
        ]);

        // Vendor - sadece kendi gider/ödemelerini görür
        $vendor = Role::create(['name' => 'vendor']);
        $vendor->givePermissionTo([
            'expenses.view',
            'payments.view',
        ]);
    }
}
