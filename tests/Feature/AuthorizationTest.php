<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Apartment;
use App\Models\Expense;
use App\Models\Site;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private Site $site;

    protected function setUp(): void
    {
        parent::setUp();

        $this->site = Site::create(['name' => 'Test Site', 'is_active' => true]);

        $allPermissions = [
            'charges.view', 'charges.create', 'charges.collect', 'charges.delete',
            'expenses.view', 'expenses.create', 'expenses.pay', 'expenses.delete',
            'accounts.view', 'accounts.manage',
            'cash-accounts.view', 'cash-accounts.manage',
            'apartments.view', 'apartments.manage',
            'users.view', 'users.manage',
            'vendors.view', 'vendors.manage',
            'templates.view', 'templates.manage',
            'reports.view',
            'receipts.view', 'receipts.print',
            'payments.view',
        ];

        foreach ($allPermissions as $p) {
            Permission::create(['name' => $p]);
        }

        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo($allPermissions);

        $ownerRole = Role::create(['name' => 'owner']);
        $ownerRole->givePermissionTo(['charges.view', 'receipts.view', 'receipts.print']);

        $tenantRole = Role::create(['name' => 'tenant']);
        $tenantRole->givePermissionTo(['charges.view', 'receipts.view']);

        $vendorRole = Role::create(['name' => 'vendor']);
        $vendorRole->givePermissionTo(['expenses.view', 'payments.view']);
    }

    private function makeUser(string $role): User
    {
        $user = User::create([
            'name' => ucfirst($role), 'email' => $role . '@test.com',
            'password' => bcrypt('password'), 'site_id' => $this->site->id,
        ]);
        $user->assignRole($role);
        return $user;
    }

    public function test_owner_cannot_access_management(): void
    {
        $owner = $this->makeUser('owner');
        $this->actingAs($owner)->get(route('management.apartments.index'))->assertForbidden();
    }

    public function test_tenant_cannot_create_charges(): void
    {
        $tenant = $this->makeUser('tenant');
        $this->actingAs($tenant)->get(route('charges.create'))->assertForbidden();
    }

    public function test_vendor_cannot_access_charges(): void
    {
        $vendor = $this->makeUser('vendor');
        $this->actingAs($vendor)->get(route('charges.index'))->assertForbidden();
    }

    public function test_admin_can_access_reports(): void
    {
        $admin = $this->makeUser('admin');
        $this->actingAs($admin)->get(route('reports.index'))->assertOk();
    }

    public function test_owner_cannot_access_reports(): void
    {
        $owner = $this->makeUser('owner');
        $this->actingAs($owner)->get(route('reports.index'))->assertForbidden();
    }

    public function test_unauthenticated_redirects_to_login(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }
}
