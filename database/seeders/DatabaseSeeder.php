<?php

namespace Database\Seeders;

use App\Actions\Campaigns\CreateCampaign;
use App\Enums\FieldType;
use App\Enums\RuleOrigin;
use App\Enums\RuleStatus;
use App\Enums\SceneStatus;
use App\Enums\Zone;
use App\Models\Document;
use App\Models\Entity;
use App\Models\EntityRelation;
use App\Models\EntityType;
use App\Models\Rule;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

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

        // Un scénario de démonstration avec ses scènes.
        $scenario = $first->scenarios()->create([
            'name' => 'Une nuit au Poney fringant',
            'summary' => 'Les voyageurs font halte à l\'auberge ; la Guilde y règle ses comptes.',
        ]);

        $arrival = $scenario->scenes()->create([
            'chapter' => 'Acte I',
            'name' => 'Arrivée à l\'auberge',
            'status' => SceneStatus::Available,
            'position' => 1,
            'description' => "La salle est bondée. [[Aldric le tavernier|{$aldric->id}]] propose les dernières chambres à prix d'or.\n[[Mira la colporteuse|{$mira->id}]] cherche discrètement quelqu'un pour porter une lettre.",
        ]);
        $arrival->entities()->attach([
            $inn->id => ['note' => null, 'position' => 0],
            $aldric->id => ['note' => 'derrière le comptoir', 'position' => 1],
            $mira->id => ['note' => 'près de la cheminée', 'position' => 2],
        ]);

        $night = $scenario->scenes()->create([
            'chapter' => 'Acte I',
            'name' => 'Visite nocturne',
            'position' => 2,
            'description' => "Deux hommes de [[La Guilde des ombres|{$guild->id}]] descendent à la cave.",
        ]);
        $night->entities()->attach([$guild->id => ['note' => 'deux hommes encapuchonnés', 'position' => 0]]);

        // Règles : une du jeu (partagée par ses campagnes), une propre à la campagne.
        $travel = new Rule([
            'title' => 'Jet de condition du matin',
            'category' => 'Voyage',
            'summary' => 'Chaque matin, Force + Esprit : le résultat fixe la condition du jour.',
            'procedure' => "1. Chaque voyageur lance Force + Esprit.\n2. Ajoutez +1 s'il a bien dormi à l'auberge.\n3. Le résultat devient sa condition pour la journée.",
            'source' => 'Livre de base',
            'origin' => RuleOrigin::Reference,
        ]);
        $travel->owner()->associate($gm);
        $travel->gameSystem()->associate($first->gameSystem);
        $travel->save();
        $travel->tags()->sync(Tag::idsFromInput($gm, 'voyage'));

        $brawl = new Rule([
            'title' => 'Bagarre de taverne',
            'category' => 'Combat',
            'summary' => 'Pas de blessure grave : on perd des points de condition, pas des PV.',
            'procedure' => 'Chaque coup réussi retire 1 point de condition. À 0, le personnage est sonné jusqu\'à la fin de la scène.',
            'gm_notes' => 'À tester ce soir : si c\'est trop long, passer à un seul jet en opposition.',
            'origin' => RuleOrigin::House,
            'status' => RuleStatus::ToTest,
        ]);
        $brawl->owner()->associate($gm);
        $brawl->campaign()->associate($first);
        $brawl->save();
        $brawl->tags()->sync(Tag::idsFromInput($gm, 'combat, taverne'));

        // Un document PDF rangé dans la campagne et lié aux deux scènes.
        $path = 'documents/lettre-de-mira.pdf';
        Storage::disk(Document::DISK)->put($path, self::samplePdf('Lettre de Mira : rendez-vous a minuit, cave du Poney.'));
        $letter = new Document([
            'title' => 'Lettre de Mira',
            'description' => 'La lettre que Mira cherche à faire porter.',
            'zone' => Zone::GameMaster,
            'disk' => Document::DISK,
            'path' => $path,
            'original_name' => 'lettre-de-mira.pdf',
            'mime_type' => 'application/pdf',
            'size' => Storage::disk(Document::DISK)->size($path),
        ]);
        $letter->owner()->associate($gm);
        $letter->campaign()->associate($first);
        $letter->save();

        $arrival->rules()->attach([$travel->id => ['position' => 0], $brawl->id => ['position' => 1]]);
        $arrival->documents()->attach($letter->id, ['position' => 0]);
        $mira->documents()->attach($letter->id);
    }

    /** Un PDF d'une page, juste assez pour la démonstration. */
    private static function samplePdf(string $text): string
    {
        $stream = 'BT /F1 18 Tf 72 720 Td ('.addcslashes($text, '()\\').') Tj ET';
        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>',
            '<< /Length '.strlen($stream)." >>\nstream\n{$stream}\nendstream",
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [];

        foreach ($objects as $index => $object) {
            $offsets[] = strlen($pdf);
            $pdf .= ($index + 1)." 0 obj\n{$object}\nendobj\n";
        }

        $xref = strlen($pdf);
        $pdf .= 'xref'."\n0 ".(count($objects) + 1)."\n0000000000 65535 f \n";

        foreach ($offsets as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }

        return $pdf.'trailer << /Size '.(count($objects) + 1)." /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF\n";
    }
}
