<?php

namespace Database\Factories;

use App\Models\Entity;
use App\Models\EntityType;
use App\Models\User;
use App\Models\World;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Entity> */
class EntityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'world_id' => fn (array $attributes) => ($attributes['campaign_id'] ?? null)
                ? null
                : World::factory()->state(['user_id' => $attributes['user_id']]),
            'campaign_id' => null,
            'entity_type_id' => fn () => EntityType::standard('character')->getKey(),
            'name' => fake()->firstName(),
            'summary' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'gm_notes' => fake()->sentence(),
        ];
    }
}
