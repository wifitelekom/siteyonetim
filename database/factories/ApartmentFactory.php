<?php

namespace Database\Factories;

use App\Models\Apartment;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

class ApartmentFactory extends Factory
{
    protected $model = Apartment::class;

    public function definition(): array
    {
        return [
            'site_id' => Site::factory(),
            'block' => $this->faker->randomElement(['A', 'B', 'C']),
            'floor' => $this->faker->numberBetween(1, 10),
            'number' => (string) $this->faker->numberBetween(1, 50),
            'm2' => $this->faker->numberBetween(60, 200),
            'arsa_payi' => $this->faker->numberBetween(30, 150),
            'is_active' => true,
        ];
    }
}
