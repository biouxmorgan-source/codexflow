<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\World;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<World> */
class WorldFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->randomElement(['Les Royaumes Brisés', 'Arkham', 'Valdaria', 'Les Terres du Milieu-Sud']),
            'description' => fake()->sentence(),
        ];
    }
}
