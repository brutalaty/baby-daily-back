<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\Family;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invitation>
 */
class InvitationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'family_id' => Family::factory(),
            'relation' => 'Mother',
            'expiration' => now()
                ->addMonths(config('invitations.expiration.months'))
                ->addDays(config('invitations.expiration.days'))
        ];
    }
}
