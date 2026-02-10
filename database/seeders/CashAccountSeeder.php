<?php

namespace Database\Seeders;

use App\Models\CashAccount;
use App\Models\Site;
use Illuminate\Database\Seeder;

class CashAccountSeeder extends Seeder
{
    public function run(): void
    {
        $site = Site::first();

        CashAccount::create([
            'site_id' => $site->id,
            'name' => 'Nakit Kasası',
            'type' => 'cash',
            'opening_balance' => 5000.00,
        ]);

        CashAccount::create([
            'site_id' => $site->id,
            'name' => 'Ziraat Bankası',
            'type' => 'bank',
            'opening_balance' => 25000.00,
        ]);
    }
}
