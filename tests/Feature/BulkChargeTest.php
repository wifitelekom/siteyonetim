<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Apartment;
use App\Models\Charge;
use App\Models\Site;
use App\Models\User;
use App\Services\ChargeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BulkChargeTest extends TestCase
{
    use RefreshDatabase;

    private Site $site;
    private User $admin;
    private Account $account;

    protected function setUp(): void
    {
        parent::setUp();

        $this->site = Site::create(['name' => 'Test Site', 'is_active' => true]);

        foreach (['charges.view', 'charges.create', 'charges.collect', 'charges.delete'] as $p) {
            Permission::create(['name' => $p]);
        }
        $role = Role::create(['name' => 'admin']);
        $role->givePermissionTo(Permission::all());

        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'admin@test.com',
            'password' => bcrypt('password'), 'site_id' => $this->site->id,
        ]);
        $this->admin->assignRole('admin');

        $this->account = Account::create([
            'site_id' => $this->site->id, 'code' => '600-01',
            'name' => 'Aidat', 'type' => 'income',
        ]);

        // Create 5 apartments
        for ($i = 1; $i <= 5; $i++) {
            Apartment::create([
                'site_id' => $this->site->id, 'block' => 'A',
                'floor' => $i, 'number' => (string) $i,
                'm2' => 100, 'arsa_payi' => 50, 'is_active' => true,
            ]);
        }
    }

    public function test_bulk_charge_creates_for_all_apartments(): void
    {
        $apartments = Apartment::all();

        $response = $this->actingAs($this->admin)->post(route('charges.store-bulk'), [
            'apartment_ids' => $apartments->pluck('id')->toArray(),
            'account_id' => $this->account->id,
            'charge_type' => 'aidat',
            'period' => date('Y-m'),
            'due_date' => date('Y-m-d', strtotime('+15 days')),
            'amount' => 1000.00,
        ]);

        $response->assertRedirect(route('charges.index'));
        $this->assertEquals(5, Charge::count());
    }

    public function test_bulk_charge_is_idempotent(): void
    {
        $service = app(ChargeService::class);
        $apartments = Apartment::all();

        // First run
        $this->actingAs($this->admin);
        $result1 = $service->createBulkCharges([
            'apartment_ids' => $apartments->pluck('id')->toArray(),
            'account_id' => $this->account->id,
            'charge_type' => 'aidat',
            'period' => date('Y-m'),
            'due_date' => date('Y-m-d', strtotime('+15 days')),
            'amount' => 1000.00,
        ]);

        // Second run (should skip existing)
        $result2 = $service->createBulkCharges([
            'apartment_ids' => $apartments->pluck('id')->toArray(),
            'account_id' => $this->account->id,
            'charge_type' => 'aidat',
            'period' => date('Y-m'),
            'due_date' => date('Y-m-d', strtotime('+15 days')),
            'amount' => 1000.00,
        ]);

        $this->assertEquals(5, Charge::count()); // Still 5, not 10
        $this->assertEquals(0, $result2);
    }
}
