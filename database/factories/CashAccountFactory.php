<?php

namespace Database\Factories;

use App\Models\CashAccount;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

class CashAccountFactory extends Factory
{
    protected $model = CashAccount::class;

    public function definition(): array
    {
        return [
            'site_id' => Site::factory(),
            'name' => $this->faker->randomElement(['Nakit Kasası', 'Banka Hesabı', 'Yedek Kasa']),
            'type' => $this->faker->randomElement(['cash', 'bank']),
            'opening_balance' => $this->faker->randomFloat(2, 0, 50000),
        ];
    }
}
