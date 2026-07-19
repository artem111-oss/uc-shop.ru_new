<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PubgAccount>
 */
class PubgAccountFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'pubg_id' => (string) $this->faker->numberBetween(510000000, 519999999),
            'nickname' => $this->faker->userName(),
            'is_primary' => false,
        ];
    }
}