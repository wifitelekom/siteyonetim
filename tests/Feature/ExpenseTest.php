<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Expense;
use App\Models\Site;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ExpenseTest extends TestCase
{
    use RefreshDatabase;

    private Site $site;
    private User $admin;
    private Account $account;
    private Vendor $vendor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->site = Site::create(['name' => 'Test Site', 'is_active' => true]);

        foreach (['expenses.view', 'expenses.create', 'expenses.pay', 'expenses.delete'] as $p) {
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
            'site_id' => $this->site->id, 'code' => '770-01',
            'name' => 'Su Gideri', 'type' => 'expense',
        ]);

        $this->vendor = Vendor::create([
            'site_id' => $this->site->id, 'name' => 'Test Tedarikçi',
        ]);
    }

    public function test_admin_can_create_expense(): void
    {
        $response = $this->actingAs($this->admin)->post(route('expenses.store'), [
            'vendor_id' => $this->vendor->id,
            'account_id' => $this->account->id,
            'expense_date' => date('Y-m-d'),
            'due_date' => date('Y-m-d', strtotime('+30 days')),
            'amount' => 5000.00,
            'description' => 'Test gider',
        ]);

        $createdExpense = Expense::latest('id')->first();
        $response->assertRedirect(route('expenses.show', $createdExpense));
        $this->assertDatabaseHas('expenses', ['amount' => 5000.00]);
    }

    public function test_expense_without_payment_can_be_deleted(): void
    {
        $expense = Expense::create([
            'site_id' => $this->site->id, 'vendor_id' => $this->vendor->id,
            'account_id' => $this->account->id, 'expense_date' => now(),
            'due_date' => now()->addDays(30), 'amount' => 5000.00,
            'paid_amount' => 0, 'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('expenses.destroy', $expense));
        $response->assertRedirect(route('expenses.index'));
        $this->assertSoftDeleted('expenses', ['id' => $expense->id]);
    }

    public function test_paid_expense_cannot_be_deleted(): void
    {
        $expense = Expense::create([
            'site_id' => $this->site->id, 'vendor_id' => $this->vendor->id,
            'account_id' => $this->account->id, 'expense_date' => now(),
            'due_date' => now()->addDays(30), 'amount' => 5000.00,
            'paid_amount' => 1000.00, 'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('expenses.destroy', $expense));
        $response->assertRedirect();
        $this->assertNotSoftDeleted('expenses', ['id' => $expense->id]);
    }
}
