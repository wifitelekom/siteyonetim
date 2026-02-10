<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Apartment;
use App\Models\Charge;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ChargeTest extends TestCase
{
    use RefreshDatabase;

    private Site $site;
    private User $admin;
    private User $owner;
    private Account $account;
    private Apartment $apartment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->site = Site::create(['name' => 'Test Site', 'is_active' => true]);

        // Create permissions and roles
        $permissions = ['charges.view', 'charges.create', 'charges.collect', 'charges.delete'];
        foreach ($permissions as $p) {
            Permission::create(['name' => $p]);
        }

        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo($permissions);

        $ownerRole = Role::create(['name' => 'owner']);
        $ownerRole->givePermissionTo(['charges.view']);

        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'admin@test.com',
            'password' => bcrypt('password'), 'site_id' => $this->site->id,
        ]);
        $this->admin->assignRole('admin');

        $this->owner = User::create([
            'name' => 'Owner', 'email' => 'owner@test.com',
            'password' => bcrypt('password'), 'site_id' => $this->site->id,
        ]);
        $this->owner->assignRole('owner');

        $this->account = Account::create([
            'site_id' => $this->site->id, 'code' => '600-01',
            'name' => 'Aidat Geliri', 'type' => 'income',
        ]);

        $this->apartment = Apartment::create([
            'site_id' => $this->site->id, 'block' => 'A',
            'floor' => 1, 'number' => '1', 'm2' => 100, 'arsa_payi' => 50, 'is_active' => true,
        ]);

        $this->owner->apartments()->attach($this->apartment->id, [
            'relation_type' => 'owner', 'start_date' => now(),
        ]);
    }

    public function test_admin_can_create_charge(): void
    {
        $response = $this->actingAs($this->admin)->post(route('charges.store'), [
            'apartment_id' => $this->apartment->id,
            'account_id' => $this->account->id,
            'charge_type' => 'aidat',
            'period' => date('Y-m'),
            'due_date' => date('Y-m-d', strtotime('+15 days')),
            'amount' => 1500.00,
            'description' => 'Test aidat',
        ]);

        $createdCharge = Charge::latest('id')->first();
        $response->assertRedirect(route('charges.show', $createdCharge));
        $this->assertDatabaseHas('charges', [
            'apartment_id' => $this->apartment->id,
            'amount' => 1500.00,
        ]);
    }

    public function test_owner_cannot_create_charge(): void
    {
        $response = $this->actingAs($this->owner)->post(route('charges.store'), [
            'apartment_id' => $this->apartment->id,
            'account_id' => $this->account->id,
            'charge_type' => 'aidat',
            'period' => date('Y-m'),
            'due_date' => date('Y-m-d', strtotime('+15 days')),
            'amount' => 1500.00,
        ]);

        $response->assertForbidden();
    }

    public function test_owner_sees_only_own_charges(): void
    {
        $otherApartment = Apartment::create([
            'site_id' => $this->site->id, 'block' => 'A',
            'floor' => 2, 'number' => '2', 'm2' => 100, 'arsa_payi' => 50, 'is_active' => true,
        ]);

        // Charge for owner's apartment
        Charge::create([
            'site_id' => $this->site->id, 'apartment_id' => $this->apartment->id,
            'account_id' => $this->account->id, 'charge_type' => 'aidat',
            'period' => date('Y-m'), 'due_date' => now()->addDays(15),
            'amount' => 1500, 'paid_amount' => 0, 'created_by' => $this->admin->id,
        ]);

        // Charge for other apartment
        Charge::create([
            'site_id' => $this->site->id, 'apartment_id' => $otherApartment->id,
            'account_id' => $this->account->id, 'charge_type' => 'aidat',
            'period' => date('Y-m'), 'due_date' => now()->addDays(15),
            'amount' => 1500, 'paid_amount' => 0, 'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->owner)->get(route('charges.index'));
        $response->assertOk();
        // Owner should only see 1 charge (their own apartment)
        $response->assertViewHas('charges', function ($charges) {
            return $charges->count() === 1;
        });
    }
}
