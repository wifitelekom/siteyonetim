<?php

namespace Tests\Unit;

use App\Models\Account;
use App\Models\Apartment;
use App\Models\CashAccount;
use App\Models\Charge;
use App\Models\Receipt;
use App\Models\ReceiptItem;
use App\Models\Site;
use App\Models\User;
use App\Services\ChargeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChargeServiceTest extends TestCase
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
            'site_id' => $site->id, 'code' => '600-01',
            'name' => 'Aidat', 'type' => 'income',
        ]);

        $apartment = Apartment::create([
            'site_id' => $site->id, 'block' => 'A',
            'floor' => 1, 'number' => '1', 'm2' => 100, 'arsa_payi' => 50, 'is_active' => true,
        ]);

        $cashAccount = CashAccount::create([
            'site_id' => $site->id, 'name' => 'Kasa',
            'type' => 'cash', 'opening_balance' => 0,
        ]);

        $charge = Charge::create([
            'site_id' => $site->id, 'apartment_id' => $apartment->id,
            'account_id' => $account->id, 'charge_type' => 'aidat',
            'period' => '2025-01', 'due_date' => '2025-01-15',
            'amount' => 1500.00, 'paid_amount' => 0, 'created_by' => $user->id,
        ]);

        // Create two receipts with items
        $receipt1 = Receipt::create([
            'site_id' => $site->id, 'receipt_no' => 'MKB-2025-000001',
            'apartment_id' => $apartment->id, 'cash_account_id' => $cashAccount->id,
            'paid_at' => '2025-01-10', 'method' => 'cash',
            'total_amount' => 500, 'created_by' => $user->id,
        ]);
        ReceiptItem::create(['receipt_id' => $receipt1->id, 'charge_id' => $charge->id, 'amount' => 500]);

        $receipt2 = Receipt::create([
            'site_id' => $site->id, 'receipt_no' => 'MKB-2025-000002',
            'apartment_id' => $apartment->id, 'cash_account_id' => $cashAccount->id,
            'paid_at' => '2025-01-12', 'method' => 'cash',
            'total_amount' => 700, 'created_by' => $user->id,
        ]);
        ReceiptItem::create(['receipt_id' => $receipt2->id, 'charge_id' => $charge->id, 'amount' => 700]);

        $service = app(ChargeService::class);
        $service->recalculatePaidAmount($charge);

        $charge->refresh();
        $this->assertEquals(1200.00, $charge->paid_amount);
        $this->assertEquals(300.00, $charge->remaining);
    }
}
