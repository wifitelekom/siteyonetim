<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Apartment;
use App\Models\CashAccount;
use App\Models\Charge;
use App\Models\Receipt;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReceiptTest extends TestCase
{
    use RefreshDatabase;

    private Site $site;
    private User $admin;
    private CashAccount $cashAccount;
    private Charge $charge;
    private Apartment $apartment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->site = Site::create(['name' => 'Test Site', 'is_active' => true]);

        foreach (['charges.view', 'charges.create', 'charges.collect', 'charges.delete', 'receipts.view', 'receipts.print'] as $p) {
            Permission::create(['name' => $p]);
        }
        $role = Role::create(['name' => 'admin']);
        $role->givePermissionTo(Permission::all());

        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'admin@test.com',
            'password' => bcrypt('password'), 'site_id' => $this->site->id,
        ]);
        $this->admin->assignRole('admin');

        $account = Account::create([
            'site_id' => $this->site->id, 'code' => '600-01',
            'name' => 'Aidat', 'type' => 'income',
        ]);

        $this->apartment = Apartment::create([
            'site_id' => $this->site->id, 'block' => 'A',
            'floor' => 1, 'number' => '1', 'm2' => 100, 'arsa_payi' => 50, 'is_active' => true,
        ]);

        $this->cashAccount = CashAccount::create([
            'site_id' => $this->site->id, 'name' => 'Kasa',
            'type' => 'cash', 'opening_balance' => 0,
        ]);

        $this->charge = Charge::create([
            'site_id' => $this->site->id, 'apartment_id' => $this->apartment->id,
            'account_id' => $account->id, 'charge_type' => 'aidat',
            'period' => date('Y-m'), 'due_date' => now()->addDays(15),
            'amount' => 1500.00, 'paid_amount' => 0, 'created_by' => $this->admin->id,
        ]);
    }

    public function test_admin_can_collect_full_payment(): void
    {
        $response = $this->actingAs($this->admin)->post(route('charges.collect', $this->charge), [
            'paid_at' => date('Y-m-d'),
            'method' => 'cash',
            'cash_account_id' => $this->cashAccount->id,
            'amount' => 1500.00,
            'description' => 'Test tahsilat',
        ]);

        $response->assertRedirect();
        $this->charge->refresh();
        $this->assertEquals(1500.00, $this->charge->paid_amount);
        $this->assertEquals('paid', $this->charge->status->value);
    }

    public function test_partial_payment_updates_paid_amount(): void
    {
        $this->actingAs($this->admin)->post(route('charges.collect', $this->charge), [
            'paid_at' => date('Y-m-d'),
            'method' => 'cash',
            'cash_account_id' => $this->cashAccount->id,
            'amount' => 500.00,
        ]);

        $this->charge->refresh();
        $this->assertEquals(500.00, $this->charge->paid_amount);
        $this->assertEquals('open', $this->charge->status->value);
        $this->assertEquals(1000.00, $this->charge->remaining);
    }

    public function test_receipt_is_created_on_collection(): void
    {
        $this->actingAs($this->admin)->post(route('charges.collect', $this->charge), [
            'paid_at' => date('Y-m-d'),
            'method' => 'cash',
            'cash_account_id' => $this->cashAccount->id,
            'amount' => 1500.00,
        ]);

        $this->assertDatabaseCount('receipts', 1);
        $this->assertDatabaseCount('receipt_items', 1);
    }

    public function test_tenant_without_print_permission_cannot_download_receipt_pdf(): void
    {
        $tenantRole = Role::create(['name' => 'tenant']);
        $tenantRole->givePermissionTo(['receipts.view']);

        $tenant = User::create([
            'name' => 'Tenant', 'email' => 'tenant@test.com',
            'password' => bcrypt('password'), 'site_id' => $this->site->id,
        ]);
        $tenant->assignRole('tenant');
        $tenant->apartments()->attach($this->apartment->id, [
            'relation_type' => 'tenant', 'start_date' => now(),
        ]);

        $this->actingAs($this->admin)->post(route('charges.collect', $this->charge), [
            'paid_at' => date('Y-m-d'),
            'method' => 'cash',
            'cash_account_id' => $this->cashAccount->id,
            'amount' => 100,
        ]);

        $receipt = Receipt::firstOrFail();

        $this->actingAs($tenant)
            ->get(route('receipts.pdf', $receipt))
            ->assertForbidden();
    }
}
