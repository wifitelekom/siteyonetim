<?php

namespace Tests\Unit;

use App\Models\Account;
use App\Models\Apartment;
use App\Models\Charge;
use App\Models\Site;
use App\Models\User;
use App\Services\SitePurger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitePurgerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_purges_target_site_records_even_when_authenticated_user_belongs_to_another_site(): void
    {
        $siteA = Site::create(['name' => 'Site A', 'is_active' => true]);
        $siteB = Site::create(['name' => 'Site B', 'is_active' => true]);

        $userFromSiteA = User::create([
            'name' => 'User A',
            'email' => 'user-a@example.com',
            'password' => bcrypt('password'),
            'site_id' => $siteA->id,
        ]);
        $this->actingAs($userFromSiteA);

        $accountA = Account::create([
            'site_id' => $siteA->id,
            'code' => '600-01-A',
            'name' => 'Aidat Geliri A',
            'type' => 'income',
        ]);

        $accountB = Account::create([
            'site_id' => $siteB->id,
            'code' => '600-01-B',
            'name' => 'Aidat Geliri B',
            'type' => 'income',
        ]);

        $apartmentB = Apartment::create([
            'site_id' => $siteB->id,
            'block' => 'A',
            'floor' => 1,
            'number' => '1',
            'm2' => 95,
            'arsa_payi' => 40,
            'is_active' => true,
        ]);

        $chargeB = Charge::create([
            'site_id' => $siteB->id,
            'apartment_id' => $apartmentB->id,
            'account_id' => $accountB->id,
            'charge_type' => 'aidat',
            'period' => now()->format('Y-m'),
            'due_date' => now()->addDays(10)->toDateString(),
            'amount' => 1000,
            'paid_amount' => 0,
        ]);

        $siteBUser = User::create([
            'name' => 'User B',
            'email' => 'user-b@example.com',
            'password' => bcrypt('password'),
            'site_id' => $siteB->id,
        ]);

        app(SitePurger::class)->purge($siteB);

        $this->assertDatabaseMissing('sites', ['id' => $siteB->id]);
        $this->assertDatabaseMissing('accounts', ['id' => $accountB->id]);
        $this->assertDatabaseMissing('apartments', ['id' => $apartmentB->id]);
        $this->assertDatabaseMissing('charges', ['id' => $chargeB->id]);
        $this->assertDatabaseHas('users', ['id' => $siteBUser->id, 'site_id' => null]);

        $this->assertDatabaseHas('sites', ['id' => $siteA->id]);
        $this->assertDatabaseHas('accounts', ['id' => $accountA->id, 'site_id' => $siteA->id]);
    }
}

