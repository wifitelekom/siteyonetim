<?php

namespace Database\Seeders;

use App\Models\Apartment;
use App\Models\Site;
use Illuminate\Database\Seeder;

class ApartmentSeeder extends Seeder
{
    public function run(): void
    {
        $site = Site::first();

        // A Blok - 10 daire (5 kat, her katta 2 daire)
        for ($floor = 1; $floor <= 5; $floor++) {
            for ($no = 1; $no <= 2; $no++) {
                $doorNo = (($floor - 1) * 2) + $no;
                Apartment::create([
                    'site_id' => $site->id,
                    'block' => 'A',
                    'floor' => $floor,
                    'number' => (string) $doorNo,
                    'm2' => rand(80, 150),
                    'arsa_payi' => rand(50, 120),
                    'is_active' => true,
                ]);
            }
        }
    }
}
