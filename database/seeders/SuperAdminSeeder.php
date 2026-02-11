<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'superadmin@siteyonetimi.test'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'site_id' => null,
                'email_verified_at' => now(),
            ]
        );

        if (!$user->hasRole('super-admin')) {
            $user->assignRole('super-admin');
        }
    }
}
