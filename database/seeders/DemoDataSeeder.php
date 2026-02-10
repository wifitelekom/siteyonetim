<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Apartment;
use App\Models\CashAccount;
use App\Models\Charge;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\PaymentItem;
use App\Models\Receipt;
use App\Models\ReceiptItem;
use App\Models\Site;
use App\Models\TemplateAidat;
use App\Models\TemplateExpense;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $site = Site::first();
        $admin = User::where('email', 'admin@siteyonetimi.test')->first();
        $aidatAccount = Account::where('code', '600-01')->first();
        $suAccount = Account::where('code', '770-01')->first();
        $elektrikAccount = Account::where('code', '770-02')->first();
        $temizlikAccount = Account::where('code', '770-06')->first();
        $asansorAccount = Account::where('code', '770-07')->first();
        $nakitKasa = CashAccount::where('type', 'cash')->first();
        $bankaHesap = CashAccount::where('type', 'bank')->first();
        $apartments = Apartment::all();
        $vendors = Vendor::all();

        if (
            !$site || !$admin || !$aidatAccount || !$suAccount || !$elektrikAccount
            || !$temizlikAccount || !$asansorAccount || !$nakitKasa || !$bankaHesap
            || $apartments->isEmpty() || $vendors->isEmpty()
        ) {
            $this->command?->warn(
                'DemoDataSeeder icin temel veriler eksik. Once Site/Role/Account/Apartment/User/Vendor seederlarini calistirin.'
            );
            return;
        }

        // --- Aidat Şablonu ---
        $template = TemplateAidat::create([
            'site_id' => $site->id,
            'name' => 'Aylık Aidat',
            'amount' => 1500.00,
            'due_day' => 15,
            'account_id' => $aidatAccount->id,
            'scope' => 'all',
            'is_active' => true,
        ]);

        // --- Gider Şablonu ---
        TemplateExpense::create([
            'site_id' => $site->id,
            'name' => 'Aylık Temizlik',
            'amount' => 8000.00,
            'due_day' => 1,
            'period' => 'monthly',
            'vendor_id' => $vendors->first()?->id,
            'account_id' => $temizlikAccount->id,
            'is_active' => true,
        ]);

        TemplateExpense::create([
            'site_id' => $site->id,
            'name' => '3 Aylık Asansör Bakım',
            'amount' => 4500.00,
            'due_day' => 10,
            'period' => 'quarterly',
            'vendor_id' => $vendors->last()?->id,
            'account_id' => $asansorAccount->id,
            'is_active' => true,
        ]);

        // --- Son 3 ay tahakkuk oluştur ---
        $periods = [
            now()->subMonths(2)->format('Y-m'),
            now()->subMonth()->format('Y-m'),
            now()->format('Y-m'),
        ];

        foreach ($periods as $period) {
            foreach ($apartments as $apartment) {
                $dueDate = Carbon::createFromFormat('Y-m', $period)->day(15);

                Charge::create([
                    'site_id' => $site->id,
                    'apartment_id' => $apartment->id,
                    'account_id' => $aidatAccount->id,
                    'charge_type' => 'aidat',
                    'period' => $period,
                    'due_date' => $dueDate,
                    'amount' => 1500.00,
                    'paid_amount' => 0,
                    'description' => 'Aylık aidat - ' . $period,
                    'created_by' => $admin->id,
                ]);
            }
        }

        // --- Bazı tahsilatlar (2 ay öncesi tam, geçen ay kısmi) ---
        $receiptNo = 1;
        $twoMonthsAgo = $periods[0];

        // 2 ay öncesi - tüm daireler tam ödemiş
        foreach ($apartments as $apartment) {
            $charge = Charge::where('apartment_id', $apartment->id)
                ->where('period', $twoMonthsAgo)
                ->first();

            if (!$charge) continue;

            $receipt = Receipt::create([
                'site_id' => $site->id,
                'receipt_no' => 'MKB-' . now()->format('Y') . '-' . str_pad($receiptNo++, 6, '0', STR_PAD_LEFT),
                'apartment_id' => $apartment->id,
                'cash_account_id' => $receiptNo % 3 === 0 ? $nakitKasa->id : $bankaHesap->id,
                'paid_at' => Carbon::createFromFormat('Y-m', $twoMonthsAgo)->day(rand(10, 20)),
                'method' => $receiptNo % 3 === 0 ? 'cash' : 'bank',
                'total_amount' => 1500.00,
                'description' => null,
                'created_by' => $admin->id,
            ]);

            ReceiptItem::create([
                'receipt_id' => $receipt->id,
                'charge_id' => $charge->id,
                'amount' => 1500.00,
            ]);

            $charge->update(['paid_amount' => 1500.00]);
        }

        // Geçen ay - 6 daire tam, 2 daire kısmi, 2 daire ödenmemiş
        $lastMonth = $periods[1];
        $apartmentList = $apartments->values();

        for ($i = 0; $i < $apartmentList->count(); $i++) {
            $apartment = $apartmentList[$i];
            $charge = Charge::where('apartment_id', $apartment->id)
                ->where('period', $lastMonth)
                ->first();

            if (!$charge) continue;

            if ($i < 6) {
                // Tam ödeme
                $receipt = Receipt::create([
                    'site_id' => $site->id,
                    'receipt_no' => 'MKB-' . now()->format('Y') . '-' . str_pad($receiptNo++, 6, '0', STR_PAD_LEFT),
                    'apartment_id' => $apartment->id,
                    'cash_account_id' => $bankaHesap->id,
                    'paid_at' => Carbon::createFromFormat('Y-m', $lastMonth)->day(rand(12, 25)),
                    'method' => 'bank',
                    'total_amount' => 1500.00,
                    'created_by' => $admin->id,
                ]);

                ReceiptItem::create([
                    'receipt_id' => $receipt->id,
                    'charge_id' => $charge->id,
                    'amount' => 1500.00,
                ]);

                $charge->update(['paid_amount' => 1500.00]);
            } elseif ($i < 8) {
                // Kısmi ödeme (750 TL)
                $receipt = Receipt::create([
                    'site_id' => $site->id,
                    'receipt_no' => 'MKB-' . now()->format('Y') . '-' . str_pad($receiptNo++, 6, '0', STR_PAD_LEFT),
                    'apartment_id' => $apartment->id,
                    'cash_account_id' => $nakitKasa->id,
                    'paid_at' => Carbon::createFromFormat('Y-m', $lastMonth)->day(rand(18, 28)),
                    'method' => 'cash',
                    'total_amount' => 750.00,
                    'created_by' => $admin->id,
                ]);

                ReceiptItem::create([
                    'receipt_id' => $receipt->id,
                    'charge_id' => $charge->id,
                    'amount' => 750.00,
                ]);

                $charge->update(['paid_amount' => 750.00]);
            }
            // Son 2 daire: ödenmemiş kalacak
        }

        // --- Giderler ---
        // 2 ay öncesi temizlik gideri (ödenmiş)
        $expense1 = Expense::create([
            'site_id' => $site->id,
            'vendor_id' => $vendors->first()?->id,
            'account_id' => $temizlikAccount->id,
            'expense_date' => Carbon::createFromFormat('Y-m', $twoMonthsAgo)->day(1),
            'due_date' => Carbon::createFromFormat('Y-m', $twoMonthsAgo)->day(10),
            'amount' => 8000.00,
            'paid_amount' => 8000.00,
            'description' => 'Aylık temizlik hizmeti - ' . $twoMonthsAgo,
            'created_by' => $admin->id,
        ]);

        $payment1 = Payment::create([
            'site_id' => $site->id,
            'vendor_id' => $vendors->first()?->id,
            'cash_account_id' => $bankaHesap->id,
            'paid_at' => Carbon::createFromFormat('Y-m', $twoMonthsAgo)->day(8),
            'method' => 'bank',
            'total_amount' => 8000.00,
            'description' => 'Temizlik ödemesi',
            'created_by' => $admin->id,
        ]);

        PaymentItem::create([
            'payment_id' => $payment1->id,
            'expense_id' => $expense1->id,
            'amount' => 8000.00,
        ]);

        // Geçen ay temizlik gideri (ödenmiş)
        $expense2 = Expense::create([
            'site_id' => $site->id,
            'vendor_id' => $vendors->first()?->id,
            'account_id' => $temizlikAccount->id,
            'expense_date' => Carbon::createFromFormat('Y-m', $lastMonth)->day(1),
            'due_date' => Carbon::createFromFormat('Y-m', $lastMonth)->day(10),
            'amount' => 8000.00,
            'paid_amount' => 8000.00,
            'description' => 'Aylık temizlik hizmeti - ' . $lastMonth,
            'created_by' => $admin->id,
        ]);

        $payment2 = Payment::create([
            'site_id' => $site->id,
            'vendor_id' => $vendors->first()?->id,
            'cash_account_id' => $bankaHesap->id,
            'paid_at' => Carbon::createFromFormat('Y-m', $lastMonth)->day(7),
            'method' => 'bank',
            'total_amount' => 8000.00,
            'description' => 'Temizlik ödemesi',
            'created_by' => $admin->id,
        ]);

        PaymentItem::create([
            'payment_id' => $payment2->id,
            'expense_id' => $expense2->id,
            'amount' => 8000.00,
        ]);

        // Bu ay temizlik gideri (henüz ödenmemiş)
        Expense::create([
            'site_id' => $site->id,
            'vendor_id' => $vendors->first()?->id,
            'account_id' => $temizlikAccount->id,
            'expense_date' => now()->startOfMonth(),
            'due_date' => now()->startOfMonth()->addDays(9),
            'amount' => 8000.00,
            'paid_amount' => 0,
            'description' => 'Aylık temizlik hizmeti - ' . $periods[2],
            'created_by' => $admin->id,
        ]);

        // Asansör bakım gideri (kısmi ödenmiş)
        $expense4 = Expense::create([
            'site_id' => $site->id,
            'vendor_id' => $vendors->last()?->id,
            'account_id' => $asansorAccount->id,
            'expense_date' => Carbon::createFromFormat('Y-m', $lastMonth)->day(10),
            'due_date' => Carbon::createFromFormat('Y-m', $lastMonth)->day(20),
            'amount' => 4500.00,
            'paid_amount' => 2000.00,
            'description' => 'Asansör bakım hizmeti (3 aylık)',
            'created_by' => $admin->id,
        ]);

        $payment3 = Payment::create([
            'site_id' => $site->id,
            'vendor_id' => $vendors->last()?->id,
            'cash_account_id' => $nakitKasa->id,
            'paid_at' => Carbon::createFromFormat('Y-m', $lastMonth)->day(18),
            'method' => 'cash',
            'total_amount' => 2000.00,
            'description' => 'Asansör bakım - kısmi ödeme',
            'created_by' => $admin->id,
        ]);

        PaymentItem::create([
            'payment_id' => $payment3->id,
            'expense_id' => $expense4->id,
            'amount' => 2000.00,
        ]);

        // Su gideri
        Expense::create([
            'site_id' => $site->id,
            'vendor_id' => null,
            'account_id' => $suAccount->id,
            'expense_date' => Carbon::createFromFormat('Y-m', $lastMonth)->day(5),
            'due_date' => Carbon::createFromFormat('Y-m', $lastMonth)->day(25),
            'amount' => 3200.00,
            'paid_amount' => 3200.00,
            'description' => 'Ortak alan su faturası',
            'created_by' => $admin->id,
        ]);

        // Elektrik gideri
        Expense::create([
            'site_id' => $site->id,
            'vendor_id' => null,
            'account_id' => $elektrikAccount->id,
            'expense_date' => now()->subDays(5),
            'due_date' => now()->addDays(10),
            'amount' => 2800.00,
            'paid_amount' => 0,
            'description' => 'Ortak alan elektrik faturası',
            'created_by' => $admin->id,
        ]);
    }
}
