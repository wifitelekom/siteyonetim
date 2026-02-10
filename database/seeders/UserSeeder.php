<?php

namespace Database\Seeders;

use App\Models\Apartment;
use App\Models\Site;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $site = Site::first();

        // Admin kullanıcı
        $admin = User::create([
            'name' => 'Site Yöneticisi',
            'email' => 'admin@siteyonetimi.test',
            'password' => Hash::make('password'),
            'site_id' => $site->id,
            'email_verified_at' => now(),
        ]);
        $admin->assignRole('admin');

        // Daire sahibi (Owner) - Daire 1
        $owner = User::create([
            'name' => 'Ahmet Yılmaz',
            'email' => 'ahmet@test.com',
            'password' => Hash::make('password'),
            'site_id' => $site->id,
            'email_verified_at' => now(),
        ]);
        $owner->assignRole('owner');

        $apt1 = Apartment::where('number', '1')->first();
        if ($apt1) {
            $owner->apartments()->attach($apt1->id, [
                'relation_type' => 'owner',
                'start_date' => now()->subYear(),
            ]);
        }

        // Kiracı (Tenant) - Daire 2
        $tenant = User::create([
            'name' => 'Mehmet Demir',
            'email' => 'mehmet@test.com',
            'password' => Hash::make('password'),
            'site_id' => $site->id,
            'email_verified_at' => now(),
        ]);
        $tenant->assignRole('tenant');

        $apt2 = Apartment::where('number', '2')->first();
        if ($apt2) {
            $tenant->apartments()->attach($apt2->id, [
                'relation_type' => 'tenant',
                'start_date' => now()->subMonths(6),
            ]);
        }

        // Diğer daire sahipleri
        $names = [
            3 => 'Ali Kaya', 4 => 'Fatma Şahin', 5 => 'Hasan Çelik',
            6 => 'Ayşe Öztürk', 7 => 'Mustafa Arslan', 8 => 'Zeynep Aydın',
            9 => 'İbrahim Koç', 10 => 'Elif Yıldırım',
        ];

        foreach ($names as $aptNo => $name) {
            $user = User::create([
                'name' => $name,
                'email' => strtolower(str_replace(' ', '.', $this->removeTurkish($name))) . '@test.com',
                'password' => Hash::make('password'),
                'site_id' => $site->id,
                'email_verified_at' => now(),
            ]);
            $user->assignRole('owner');

            $apt = Apartment::where('number', (string) $aptNo)->first();
            if ($apt) {
                $user->apartments()->attach($apt->id, [
                    'relation_type' => 'owner',
                    'start_date' => now()->subYear(),
                ]);
            }
        }
    }

    private function removeTurkish(string $text): string
    {
        $search = ['ç', 'ğ', 'ı', 'ö', 'ş', 'ü', 'Ç', 'Ğ', 'İ', 'Ö', 'Ş', 'Ü'];
        $replace = ['c', 'g', 'i', 'o', 's', 'u', 'C', 'G', 'I', 'O', 'S', 'U'];
        return str_replace($search, $replace, $text);
    }
}
