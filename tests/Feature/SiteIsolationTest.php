<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Apartment;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SiteIsolationTest extends TestCase
{
    use RefreshDatabase;

    private Site $siteA;
    private Site $siteB;
    private User $adminA;

    protected function setUp(): void
    {
        parent::setUp();

        $this->siteA = Site::create(['name' => 'Site A', 'is_active' => true]);
        $this->siteB = Site::create(['name' => 'Site B', 'is_active' => true]);

        foreach (['users.view', 'users.manage', 'charges.view', 'charges.create'] as $permission) {
            Permission::create(['name' => $permission]);
        }

        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        $this->adminA = User::create([
            'name' => 'Admin A',
            'email' => 'admin-a@test.com',
            'password' => bcrypt('password'),
            'site_id' => $this->siteA->id,
        ]);
        $this->adminA->assignRole('admin');
    }

    public function test_admin_cannot_access_other_site_user_via_route_binding(): void
    {
        $foreignUser = User::create([
            'name' => 'Foreign User',
            'email' => 'foreign@test.com',
            'password' => bcrypt('password'),
            'site_id' => $this->siteB->id,
        ]);

        $response = $this->actingAs($this->adminA)
            ->get(route('management.users.edit', $foreignUser));

        $this->assertContains($response->getStatusCode(), [403, 404]);
    }

    public function test_charge_creation_rejects_foreign_site_ids(): void
    {
        $foreignApartment = Apartment::create([
            'site_id' => $this->siteB->id,
            'block' => 'B',
            'floor' => 1,
            'number' => '1',
            'm2' => 90,
            'arsa_payi' => 40,
            'is_active' => true,
        ]);

        $foreignAccount = Account::create([
            'site_id' => $this->siteB->id,
            'code' => '600-99',
            'name' => 'Foreign Income',
            'type' => 'income',
        ]);

        $response = $this->actingAs($this->adminA)->post(route('charges.store'), [
            'apartment_id' => $foreignApartment->id,
            'account_id' => $foreignAccount->id,
            'charge_type' => 'aidat',
            'period' => now()->format('Y-m'),
            'due_date' => now()->addDays(10)->toDateString(),
            'amount' => 1000,
        ]);

        $response->assertSessionHasErrors(['apartment_id', 'account_id']);
    }
}
