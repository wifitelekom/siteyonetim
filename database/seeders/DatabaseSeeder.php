<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $seeders = [
            SiteSeeder::class,
            RolePermissionSeeder::class,
            SuperAdminSeeder::class,
            AccountSeeder::class,
            CashAccountSeeder::class,
            ApartmentSeeder::class,
            UserSeeder::class,
            VendorSeeder::class,
        ];

        if ($this->shouldSeedDemoData()) {
            $seeders[] = DemoDataSeeder::class;
        }

        $this->call($seeders);
    }

    private function shouldSeedDemoData(): bool
    {
        if (app()->environment(['local', 'testing'])) {
            return true;
        }

        return (bool) env('SEED_DEMO_DATA', false);
    }
}
