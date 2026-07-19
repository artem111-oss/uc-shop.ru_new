<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true),
            'price' => 100,
            'type_id' => 1,
            'category_id' => 1,
            'status' => 1,
            'delivery_mode' => 'auto',
            'product_kind' => 'uc',
        ];
    }
}