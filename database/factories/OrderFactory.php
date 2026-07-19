<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'client_id' => 1,
            'status_id' => 1,
            'price' => 100,
            'product_id' => Product::factory(),
            'type_id' => 1,
            'user_id' => null,
            'uid' => (string) $this->faker->numberBetween(510000000, 519999999),
            'game_id' => (string) $this->faker->numberBetween(510000000, 519999999),
            'email' => $this->faker->safeEmail(),
            'uc_amount' => '60 UC',
            'payment_status' => 'pending',
        ];
    }
}