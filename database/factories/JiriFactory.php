<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Jiri>
 */
class JiriFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Jiri ' . $this->faker->word,
            'starting_at' => $this->faker->dateTimeBetween('-1 year', '+1 year'),
        ];
    }
}
