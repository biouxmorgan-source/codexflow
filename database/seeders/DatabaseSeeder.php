<?php

namespace Database\Seeders;

use App\Actions\Campaigns\CreateCampaign;
use App\Models\Entity;
use App\Models\EntityType;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Compte de démonstration : mj@codexflow.test / password.
     */
    public function run(CreateCampaign $createCampaign): void
    {
        $gm = User::factory()->create([
            'name' => 'Maître de jeu',
            'email' => 'mj@codexflow.test',
        ]);

        $first = $createCampaign->handle($gm, [
            'name' => 'La Couronne de cendres',
            'description' => 'Une campagne de démonstration.',
            'new_game_name' => 'Ryuutama',
            'new_world_name' => 'Valdaria',
        ]);

        $second = $createCampaign->handle($gm, [
            'name' => 'Les Routes du sel',
            'game_system_id' => $first->game_system_id,
            'world_id' => $first->world_id,
        ]);

        $place = EntityType::standard('place');
        $character = EntityType::standard('character');

        $inn = Entity::factory()->for($gm, 'owner')->for($first->world)->create([
            'entity_type_id' => $place->id,
            'name' => 'Le Poney fringant',
            'summary' => 'Auberge animée au carrefour des routes marchandes.',
            'gm_notes' => 'La cave abrite un passage vers les égouts.',
        ]);

        foreach (['Aldric le tavernier', 'Mira la colporteuse', 'Frère Anselme'] as $name) {
            Entity::factory()->for($gm, 'owner')->for($first->world)->create([
                'entity_type_id' => $character->id,
                'name' => $name,
            ]);
        }

        $inn->stateIn($second)->fill(['status' => 'incendiée'])->save();
    }
}
