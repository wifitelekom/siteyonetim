<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\CashAccount;
use App\Models\Expense;
use App\Models\Site;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    private Site $site;
    private User $admin;
    private CashAccount $cashAccount;
    private Expense $expense;

    protected function setUp(): void
    {
        parent::setUp();

        $this->site = Site::create(['name' => 'Test Site', 'is_active' => true]);

        foreach (['expenses.view', 'expenses.create', 'expenses.pay', 'expenses.delete', 'payments.view'] as $p) {
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
            'site_id' => $this->site->id, 'code' => '770-06',
            'name' => 'Temizlik', 'type' => 'expense',
        ]);

        $vendor = Vendor::create(['site_id' => $this->site->id, 'name' => 'Tedarikçi']);

        $this->cashAccount = CashAccount::create([
            'site_id' => $this->site->id, 'name' => 'Banka',
            'type' => 'bank', 'opening_balance' => 50000,
        ]);

        $this->expense = Expense::create([
            'site_id' => $this->site->id, 'vendor_id' => $vendor->id,
            'account_id' => $account->id, 'expense_date' => now(),
            'due_date' => now()->addDays(30), 'amount' => 8000.00,
            'paid_amount' => 0, 'created_by' => $this->admin->id,
        ]);
    }

    public function test_admin_can_make_payment(): void
    {
        $response = $this->actingAs($this->admin)->post(route('expenses.pay', $this->expense), [
            'paid_at' => date('Y-m-d'),
            'method' => 'bank',
            'cash_account_id' => $this->cashAccount->id,
            'amount' => 8000.00,
        ]);

        $response->assertRedirect();
        $this->expense->refresh();
        $this->assertEquals(8000.00, $this->expense->paid_amount);
        $this->assertDatabaseCount('payments', 1);
        $this->assertDatabaseCount('payment_items', 1);
    }

    public function test_partial_payment_updates_expense(): void
    {
        $this->actingAs($this->admin)->post(route('expenses.pay', $this->expense), [
            'paid_at' => date('Y-m-d'),
            'method' => 'bank',
            'cash_account_id' => $this->cashAccount->id,
            'amount' => 3000.00,
        ]);

        $this->expense->refresh();
        $this->assertEquals(3000.00, $this->expense->paid_amount);
        $this->assertEquals('partial', $this->expense->status->value);
    }
}
