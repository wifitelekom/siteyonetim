<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Apartment;
use App\Models\Charge;
use App\Models\Site;
use App\Models\TemplateAidat;
use App\Models\User;
use App\Services\TemplateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchedulerTest extends TestCase
{
    use RefreshDatabase;

    private Site $site;
    private Account $account;

    protected function setUp(): void
    {
        parent::setUp();

        $this->site = Site::create(['name' => 'Test Site', 'is_active' => true]);

        $this->account = Account::create([
            'site_id' => $this->site->id, 'code' => '600-01',
            'name' => 'Aidat', 'type' => 'income',
        ]);

        for ($i = 1; $i <= 3; $i++) {
            Apartment::create([
                'site_id' => $this->site->id, 'block' => 'A',
                'floor' => $i, 'number' => (string) $i,
                'm2' => 100, 'arsa_payi' => 50, 'is_active' => true,
            ]);
        }

        TemplateAidat::create([
            'site_id' => $this->site->id, 'name' => 'Aidat',
            'amount' => 1500, 'due_day' => 15,
            'account_id' => $this->account->id, 'scope' => 'all', 'is_active' => true,
        ]);
    }

    public function test_monthly_charges_are_generated(): void
    {
        $service = app(TemplateService::class);
        $period = now()->format('Y-m');

        $result = $service->generateMonthlyCharges($period);

        $this->assertEquals(3, $result);
        $this->assertEquals(3, Charge::count());
    }

    public function test_monthly_charges_are_idempotent(): void
    {
        $service = app(TemplateService::class);
        $period = now()->format('Y-m');

        $service->generateMonthlyCharges($period);
        $result = $service->generateMonthlyCharges($period);

        $this->assertEquals(0, $result);
        $this->assertEquals(3, Charge::count()); // Still 3
    }

    public function test_artisan_command_works(): void
    {
        $this->artisan('charges:generate-monthly', ['--period' => now()->format('Y-m')])
            ->assertExitCode(0);

        $this->assertEquals(3, Charge::count());
    }
}
