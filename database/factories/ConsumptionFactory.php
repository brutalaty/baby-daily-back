<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\=Consumption>
 */
class ConsumptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'activity_id' => \App\Models\Activity::factory()->create()->id,
            'volume' => fake()->numberBetween(0, 100),
            'name' => fake()->randomElement(['Spaghetti', 'paracetamol', 'Soup', 'Toast', 'Ibuprofen', 'Garlic Bread', 'Muffin'])
        ];
    }
}
