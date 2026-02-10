<?php

namespace Database\Factories;

use App\Models\Site;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

class VendorFactory extends Factory
{
    protected $model = Vendor::class;

    public function definition(): array
    {
        return [
            'site_id' => Site::factory(),
            'name' => $this->faker->company(),
            'tax_no' => $this->faker->numerify('##########'),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->companyEmail(),
            'address' => $this->faker->address(),
        ];
    }
}
