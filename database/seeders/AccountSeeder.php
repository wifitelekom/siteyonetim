<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Site;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    public function run(): void
    {
        $site = Site::first();

        $accounts = [
            // Gelir hesapları
            ['code' => '600-01', 'name' => 'Aidat Geliri', 'type' => 'income'],
            ['code' => '600-02', 'name' => 'Otopark Geliri', 'type' => 'income'],
            ['code' => '600-03', 'name' => 'Diğer Gelirler', 'type' => 'income'],

            // Gider hesapları
            ['code' => '770-01', 'name' => 'Su Gideri', 'type' => 'expense'],
            ['code' => '770-02', 'name' => 'Elektrik Gideri', 'type' => 'expense'],
            ['code' => '770-03', 'name' => 'Doğalgaz Gideri', 'type' => 'expense'],
            ['code' => '770-04', 'name' => 'Bakım/Onarım Gideri', 'type' => 'expense'],
            ['code' => '770-05', 'name' => 'Personel Gideri', 'type' => 'expense'],
            ['code' => '770-06', 'name' => 'Temizlik Gideri', 'type' => 'expense'],
            ['code' => '770-07', 'name' => 'Asansör Bakım Gideri', 'type' => 'expense'],
            ['code' => '770-08', 'name' => 'Sigorta Gideri', 'type' => 'expense'],
            ['code' => '770-09', 'name' => 'Diğer Giderler', 'type' => 'expense'],

            // Varlık hesapları
            ['code' => '100-01', 'name' => 'Kasa', 'type' => 'asset'],
            ['code' => '102-01', 'name' => 'Banka', 'type' => 'asset'],

            // Borç hesapları
            ['code' => '320-01', 'name' => 'Tedarikçi Borçları', 'type' => 'liability'],
        ];

        foreach ($accounts as $account) {
            Account::create(array_merge($account, ['site_id' => $site->id]));
        }
    }
}
