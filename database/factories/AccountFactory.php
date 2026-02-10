<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccountFactory extends Factory
{
    protected $model = Account::class;

    public function definition(): array
    {
        return [
            'site_id' => Site::factory(),
            'code' => $this->faker->unique()->numerify('###-##'),
            'name' => $this->faker->word() . ' Hesabı',
            'type' => $this->faker->randomElement(['income', 'expense', 'asset', 'liability']),
        ];
    }
}
