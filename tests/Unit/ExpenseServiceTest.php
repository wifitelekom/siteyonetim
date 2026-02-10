<?php

namespace Tests\Unit;

use App\Models\Account;
use App\Models\CashAccount;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\PaymentItem;
use App\Models\Site;
use App\Models\User;
use App\Models\Vendor;
use App\Services\ExpenseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_recalculate_paid_amount(): void
    {
        $site = Site::create(['name' => 'Test', 'is_active' => true]);
        $user = User::create([
            'name' => 'Admin', 'email' => 'admin@test.com',
            'password' => bcrypt('pw'), 'site_id' => $site->id,
        ]);

        $account = Account::create([
            'site_id' => $site->id, 'code' => '770-01',
            'name' => 'Su', 'type' => 'expense',
        ]);

        $vendor = Vendor::create(['site_id' => $site->id, 'name' => 'Tedarikçi']);

        $cashAccount = CashAccount::create([
            'site_id' => $site->id, 'name' => 'Banka',
            'type' => 'bank', 'opening_balance' => 50000,
        ]);

        $expense = Expense::create([
            'site_id' => $site->id, 'vendor_id' => $vendor->id,
            'account_id' => $account->id, 'expense_date' => '2025-01-01',
            'due_date' => '2025-01-31', 'amount' => 5000.00,
            'paid_amount' => 0, 'created_by' => $user->id,
        ]);

        $payment1 = Payment::create([
            'site_id' => $site->id, 'vendor_id' => $vendor->id,
            'cash_account_id' => $cashAccount->id, 'paid_at' => '2025-01-10',
            'method' => 'bank', 'total_amount' => 2000, 'created_by' => $user->id,
        ]);
        PaymentItem::create(['payment_id' => $payment1->id, 'expense_id' => $expense->id, 'amount' => 2000]);

        $payment2 = Payment::create([
            'site_id' => $site->id, 'vendor_id' => $vendor->id,
            'cash_account_id' => $cashAccount->id, 'paid_at' => '2025-01-15',
            'method' => 'bank', 'total_amount' => 1500, 'created_by' => $user->id,
        ]);
        PaymentItem::create(['payment_id' => $payment2->id, 'expense_id' => $expense->id, 'amount' => 1500]);

        $service = app(ExpenseService::class);
        $service->recalculatePaidAmount($expense);

        $expense->refresh();
        $this->assertEquals(3500.00, $expense->paid_amount);
        $this->assertEquals('partial', $expense->status->value);
    }
}
