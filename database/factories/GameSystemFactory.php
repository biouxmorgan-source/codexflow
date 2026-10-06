<?php

namespace Database\Factories;

use App\Models\GameSystem;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<GameSystem> */
class GameSystemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->randomElement(['Donjons & Dragons', "L'Appel de Cthulhu", 'Ryuutama', 'Blades in the Dark']),
            'description' => fake()->sentence(),
        ];
    }
}
