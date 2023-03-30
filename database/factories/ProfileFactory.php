<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Profile>
 */
class ProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'first_name' => fake()->name(),
            'last_name' => fake()->name(),
            'date_of_birth' => fake()->date(),
            'gender' => fake()->boolean(),
            'phone' => fake()->numerify('###########'),
        ];
    }
}
