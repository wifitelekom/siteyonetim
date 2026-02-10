<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ValidationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $site = Site::create(['name' => 'Test Site', 'is_active' => true]);

        foreach (['charges.view', 'charges.create', 'charges.collect', 'charges.delete', 'expenses.view', 'expenses.create', 'expenses.pay', 'expenses.delete', 'accounts.view', 'accounts.manage'] as $p) {
            Permission::create(['name' => $p]);
        }
        $role = Role::create(['name' => 'admin']);
        $role->givePermissionTo(Permission::all());

        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'admin@test.com',
            'password' => bcrypt('password'), 'site_id' => $site->id,
        ]);
        $this->admin->assignRole('admin');

        Account::create([
            'site_id' => $site->id, 'code' => '600-01',
            'name' => 'Aidat', 'type' => 'income',
        ]);
    }

    public function test_charge_requires_amount(): void
    {
        $response = $this->actingAs($this->admin)->post(route('charges.store'), [
            'apartment_id' => 1,
            'account_id' => 1,
            'charge_type' => 'aidat',
            'period' => date('Y-m'),
            'due_date' => date('Y-m-d'),
            // amount missing
        ]);

        $response->assertSessionHasErrors('amount');
    }

    public function test_expense_requires_valid_date(): void
    {
        $response = $this->actingAs($this->admin)->post(route('expenses.store'), [
            'account_id' => 1,
            'expense_date' => 'invalid-date',
            'due_date' => date('Y-m-d'),
            'amount' => 1000,
        ]);

        $response->assertSessionHasErrors('expense_date');
    }

    public function test_account_code_must_be_unique_per_site(): void
    {
        $this->actingAs($this->admin)->post(route('accounts.store'), [
            'code' => '600-01', // Already exists
            'name' => 'Duplicate',
            'type' => 'income',
        ]);

        $this->assertEquals(1, Account::where('code', '600-01')->count());
    }

    public function test_charge_amount_must_be_positive(): void
    {
        $response = $this->actingAs($this->admin)->post(route('charges.store'), [
            'apartment_id' => 1,
            'account_id' => 1,
            'charge_type' => 'aidat',
            'period' => date('Y-m'),
            'due_date' => date('Y-m-d'),
            'amount' => -100,
        ]);

        $response->assertSessionHasErrors('amount');
    }
}
