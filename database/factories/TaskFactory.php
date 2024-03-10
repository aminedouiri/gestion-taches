<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'titre' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'statut' => $this->faker->randomElement(['en attente', 'terminée']),
            'date d\'échéance' => $this->faker->dateTimeBetween('now', '+1 month')->format('Y-m-d'),
            'user_id' => function () {
                return User::factory()->create()->id;
            },
        ];
    }
}
