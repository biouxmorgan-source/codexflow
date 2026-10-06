<?php

namespace App\Actions\Campaigns;

use App\Enums\CampaignRole;
use App\Enums\CampaignStatus;
use App\Models\Campaign;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateCampaign
{
    /**
     * Crée une campagne dont l'auteur devient le MJ. Le jeu et le monde peuvent être
     * choisis parmi ceux de l'utilisateur ou créés à la volée.
     *
     * @param  array{name: string, description?: ?string, game_system_id?: ?int, new_game_name?: ?string, world_id?: ?int, new_world_name?: ?string}  $input
     */
    public function handle(User $user, array $input): Campaign
    {
        return DB::transaction(function () use ($user, $input) {
            $gameSystem = filled($input['new_game_name'] ?? null)
                ? $user->gameSystems()->create(['name' => $input['new_game_name']])
                : $user->gameSystems()->findOrFail($input['game_system_id'] ?? null);

            $world = match (true) {
                filled($input['new_world_name'] ?? null) => $user->worlds()->create(['name' => $input['new_world_name']]),
                filled($input['world_id'] ?? null) => $user->worlds()->findOrFail($input['world_id']),
                default => null,
            };

            $campaign = new Campaign([
                'name' => $input['name'],
                'description' => $input['description'] ?? null,
                'status' => CampaignStatus::Active,
            ]);
            $campaign->owner()->associate($user);
            $campaign->gameSystem()->associate($gameSystem);
            $campaign->world()->associate($world);
            $campaign->save();

            $campaign->members()->attach($user, ['role' => CampaignRole::GameMaster->value]);

            return $campaign;
        });
    }
}
