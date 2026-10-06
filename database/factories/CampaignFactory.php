<?php

namespace Database\Factories;

use App\Enums\CampaignRole;
use App\Enums\CampaignStatus;
use App\Models\Campaign;
use App\Models\GameSystem;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Campaign> */
class CampaignFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'game_system_id' => fn (array $attributes) => GameSystem::factory()->state(['user_id' => $attributes['user_id']]),
            'world_id' => null,
            'name' => 'La '.fake()->word(),
            'description' => fake()->sentence(),
            'status' => CampaignStatus::Active,
        ];
    }

    /**
     * Inscrit le propriétaire comme MJ, comme le fait CreateCampaign.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Campaign $campaign) {
            $campaign->members()->syncWithoutDetaching([
                $campaign->user_id => ['role' => CampaignRole::GameMaster->value],
            ]);
        });
    }
}
