<?php

namespace Database\Seeders;

use App\Models\Site;
use Illuminate\Database\Seeder;

class SiteSeeder extends Seeder
{
    public function run(): void
    {
        Site::create([
            'name' => 'Güneştepe Sitesi',
            'address' => 'Güneştepe Mah. Yıldız Sok. No:1, Kadıköy/İstanbul',
            'tax_no' => '1234567890',
            'phone' => '0216 555 00 00',
            'is_active' => true,
        ]);
    }
}
