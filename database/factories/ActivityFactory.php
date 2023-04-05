<?php

namespace Database\Factories;

use App\Models\Child;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Activity>
 */
class ActivityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'child_id' => Child::factory()->create()->id,
            'time' => now(),
            'type' => config('enums.activities.poop')
        ];
    }
}
