<?php

namespace Tests\Unit;

use App\Models\Apartment;
use App\Models\CashAccount;
use App\Models\Expense;
use App\Models\Account;
use App\Models\Payment;
use App\Models\PaymentItem;
use App\Models\Receipt;
use App\Models\ReceiptItem;
use App\Models\Charge;
use App\Models\Site;
use App\Models\User;
use App\Models\Vendor;
use App\Services\CashAccountService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashAccountServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_balance_calculation(): void
    {
        $site = Site::create(['name' => 'Test', 'is_active' => true]);
        $user = User::create([
            'name' => 'Admin', 'email' => 'admin@test.com',
            'password' => bcrypt('pw'), 'site_id' => $site->id,
        ]);

        $incomeAccount = Account::create([
            'site_id' => $site->id, 'code' => '600-01', 'name' => 'Aidat', 'type' => 'income',
        ]);
        $expenseAccount = Account::create([
            'site_id' => $site->id, 'code' => '770-01', 'name' => 'Su', 'type' => 'expense',
        ]);

        $apartment = Apartment::create([
            'site_id' => $site->id, 'block' => 'A',
            'floor' => 1, 'number' => '1', 'm2' => 100, 'arsa_payi' => 50, 'is_active' => true,
        ]);

        $vendor = Vendor::create(['site_id' => $site->id, 'name' => 'Tedarikçi']);

        $cashAccount = CashAccount::create([
            'site_id' => $site->id, 'name' => 'Kasa',
            'type' => 'cash', 'opening_balance' => 10000.00,
        ]);

        // Receipt: +5000
        $charge = Charge::create([
            'site_id' => $site->id, 'apartment_id' => $apartment->id,
            'account_id' => $incomeAccount->id, 'charge_type' => 'aidat',
            'period' => '2025-01', 'due_date' => '2025-01-15',
            'amount' => 5000, 'paid_amount' => 5000, 'created_by' => $user->id,
        ]);

        $receipt = Receipt::create([
            'site_id' => $site->id, 'receipt_no' => 'MKB-2025-000001',
            'apartment_id' => $apartment->id, 'cash_account_id' => $cashAccount->id,
            'paid_at' => '2025-01-10', 'method' => 'cash',
            'total_amount' => 5000, 'created_by' => $user->id,
        ]);
        ReceiptItem::create(['receipt_id' => $receipt->id, 'charge_id' => $charge->id, 'amount' => 5000]);

        // Payment: -3000
        $expense = Expense::create([
            'site_id' => $site->id, 'vendor_id' => $vendor->id,
            'account_id' => $expenseAccount->id, 'expense_date' => '2025-01-01',
            'due_date' => '2025-01-31', 'amount' => 3000,
            'paid_amount' => 3000, 'created_by' => $user->id,
        ]);

        $payment = Payment::create([
            'site_id' => $site->id, 'vendor_id' => $vendor->id,
            'cash_account_id' => $cashAccount->id, 'paid_at' => '2025-01-12',
            'method' => 'cash', 'total_amount' => 3000, 'created_by' => $user->id,
        ]);
        PaymentItem::create(['payment_id' => $payment->id, 'expense_id' => $expense->id, 'amount' => 3000]);

        // Balance = 10000 (opening) + 5000 (receipt) - 3000 (payment) = 12000
        $service = app(CashAccountService::class);
        $balance = $service->getBalance($cashAccount);

        $this->assertEquals(12000.00, $balance);
    }
}
