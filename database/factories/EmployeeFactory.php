<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Employee>
 */
class EmployeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'user_id' => User::factory(['type' => 'employee']),
            'salary' => fake()->numberBetween(700,5000),
            'hired_at' => fake()->date(),
            'job_title' => fake()->jobTitle(),
        ];
    }
}
