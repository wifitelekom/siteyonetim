<?php

namespace Database\Seeders;

use App\Models\Site;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class VendorSeeder extends Seeder
{
    public function run(): void
    {
        $site = Site::first();

        // Tedarikçi 1 - Temizlik firması
        $vendorUser1 = User::create([
            'name' => 'Temiz İş Hizmetleri',
            'email' => 'temizis@test.com',
            'password' => Hash::make('password'),
            'site_id' => $site->id,
            'email_verified_at' => now(),
        ]);
        $vendorUser1->assignRole('vendor');

        $vendor1 = Vendor::create([
            'site_id' => $site->id,
            'name' => 'Temiz İş Hizmetleri Ltd. Şti.',
            'tax_no' => '1111111111',
            'phone' => '0212 444 11 11',
            'email' => 'temizis@test.com',
            'address' => 'Ataşehir, İstanbul',
            'user_id' => $vendorUser1->id,
        ]);

        // Tedarikçi 2 - Asansör bakım firması
        $vendorUser2 = User::create([
            'name' => 'Yüksel Asansör',
            'email' => 'yuksel@test.com',
            'password' => Hash::make('password'),
            'site_id' => $site->id,
            'email_verified_at' => now(),
        ]);
        $vendorUser2->assignRole('vendor');

        $vendor2 = Vendor::create([
            'site_id' => $site->id,
            'name' => 'Yüksel Asansör San. Tic. Ltd. Şti.',
            'tax_no' => '2222222222',
            'phone' => '0216 333 22 22',
            'email' => 'yuksel@test.com',
            'address' => 'Maltepe, İstanbul',
            'user_id' => $vendorUser2->id,
        ]);
    }
}
