<?php

namespace Database\Seeders;

use App\Actions\Campaigns\CreateCampaign;
use App\Enums\FieldType;
use App\Enums\Zone;
use App\Models\Entity;
use App\Models\EntityRelation;
use App\Models\EntityType;
use App\Models\Tag;
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
            'description' => 'Deux étages de bois sombre, une cheminée immense et des chambres à louer à la semaine.',
            'gm_notes' => 'La cave abrite un passage vers les égouts.',
        ]);

        $npcs = [
            'Aldric le tavernier' => ['Tient le Poney fringant depuis vingt ans.', 'Renseigne la guilde des voleurs contre quelques pièces.'],
            'Mira la colporteuse' => ['Marchande ambulante, toujours de passage.', 'Transporte en secret des lettres pour la résistance.'],
            'Frère Anselme' => ['Moine érudit du monastère voisin.', 'Cherche le grimoire perdu de son ordre.'],
        ];

        // Champs libres du jeu de démonstration, nommés comme le MJ le ferait.
        $game = $first->gameSystem;
        $dice = ['d4', 'd6', 'd8', 'd10', 'd12'];
        $fields = collect([
            ['Force', 'Caractéristiques', FieldType::Select, $dice],
            ['Dextérité', 'Caractéristiques', FieldType::Select, $dice],
            ['Intelligence', 'Caractéristiques', FieldType::Select, $dice],
            ['Esprit', 'Caractéristiques', FieldType::Select, $dice],
            ['Niveau', 'Caractéristiques', FieldType::Number, null],
            ['Métier', 'Profil', FieldType::Text, null],
        ])->map(fn (array $field, int $position) => $game->fieldDefinitions()->create([
            'name' => $field[0],
            'group' => $field[1],
            'type' => $field[2],
            'options' => $field[3],
            'zone' => Zone::Public,
            'entity_type_id' => $character->id,
            'position' => $position,
        ]))->keyBy('name');
        $fields['Ambition secrète'] = $game->fieldDefinitions()->create([
            'name' => 'Ambition secrète', 'type' => FieldType::Text, 'zone' => Zone::GameMaster,
            'entity_type_id' => $character->id, 'position' => $fields->count(),
        ]);

        foreach ($npcs as $name => [$summary, $secret]) {
            $npc = Entity::factory()->for($gm, 'owner')->for($first->world)->create([
                'entity_type_id' => $character->id,
                'name' => $name,
                'summary' => $summary,
                'description' => null,
                'gm_notes' => $secret,
            ]);

            if ($name === 'Aldric le tavernier') {
                $npc->setFieldValues([
                    $fields['Force']->id => 'd8',
                    $fields['Dextérité']->id => 'd4',
                    $fields['Intelligence']->id => 'd6',
                    $fields['Esprit']->id => 'd6',
                    $fields['Niveau']->id => 3,
                    $fields['Métier']->id => 'Aubergiste',
                    $fields['Ambition secrète']->id => 'Racheter la guilde',
                ]);
                $npc->save();
            }
        }

        $inn->stateIn($second)->fill(['status' => 'incendiée'])->save();

        // Un type personnalisé, des tags et des relations pour montrer le rangement.
        $faction = new EntityType(['name' => 'Faction']);
        $faction->user_id = $gm->id;
        $faction->save();

        $guild = Entity::factory()->for($gm, 'owner')->for($first->world)->create([
            'entity_type_id' => $faction->id,
            'name' => 'La Guilde des ombres',
            'summary' => 'Réseau de voleurs et d\'informateurs.',
            'description' => null,
            'gm_notes' => 'Dirigée en secret par le bourgmestre.',
        ]);

        $aldric = Entity::where('name', 'Aldric le tavernier')->sole();
        $mira = Entity::where('name', 'Mira la colporteuse')->sole();

        $inn->tags()->sync(Tag::idsFromInput($gm, 'taverne, acte 1'));
        $aldric->tags()->sync(Tag::idsFromInput($gm, 'taverne, intrigue'));
        $guild->tags()->sync(Tag::idsFromInput($gm, 'intrigue'));

        foreach ([
            [$aldric, $inn, 'tient', 'tenue par', Zone::Public],
            [$mira, $inn, 'loge à', null, Zone::Public],
            [$aldric, $guild, 'renseigne', 'paie', Zone::GameMaster],
        ] as [$from, $to, $label, $reverse, $zone]) {
            $relation = new EntityRelation(['label' => $label, 'reverse_label' => $reverse, 'zone' => $zone]);
            $relation->owner()->associate($gm);
            $relation->from()->associate($from);
            $relation->to()->associate($to);
            $relation->save();
        }
    }
}
