<?php

namespace Database\Seeders\Browser;

use App\Actions\Account\DeleteAccount;
use App\Actions\Campaigns\CreateCampaign;
use App\Enums\CampaignRole;
use App\Models\Entity;
use App\Models\EntityType;
use App\Models\User;
use App\Support\Changelog;
use Illuminate\Database\Seeder;

/**
 * Données des tests navigateur transverses (tests/Browser) : un MJ, un joueur en texte
 * « très grande », une campagne avec son personnage et quelques fiches. Rejouable.
 */
class TransversalSeeder extends Seeder
{
    public const PASSWORD = 'navigateur-e2e-42';

    public const CAMPAIGN = 'Campagne transverse e2e';

    public function run(CreateCampaign $createCampaign, DeleteAccount $delete): void
    {
        User::whereIn('email', ['e2e-mj-t@loremundi.test', 'e2e-joueur-t@loremundi.test'])->get()->each(fn (User $user) => $delete->handle($user));

        $seen = ['last_seen_version' => Changelog::version(), 'email_verified_at' => now()];
        $gm = User::factory()->create(['name' => 'MJ transverse', 'email' => 'e2e-mj-t@loremundi.test', 'password' => self::PASSWORD] + $seen);
        $player = User::factory()->create([
            'name' => 'Joueur transverse', 'email' => 'e2e-joueur-t@loremundi.test', 'password' => self::PASSWORD,
            'preferences' => ['size' => 'xlarge'],
        ] + $seen);

        $campaign = $createCampaign->handle($gm, ['name' => self::CAMPAIGN, 'new_game_name' => 'Jeu transverse', 'new_world_name' => 'Monde transverse']);
        $campaign->members()->attach($player, ['role' => CampaignRole::Player->value]);

        $entity = fn (string $name, string $type, string $description = '') => tap(new Entity(['name' => $name, 'summary' => $description]), function (Entity $entity) use ($gm, $campaign, $type) {
            $entity->owner()->associate($gm);
            $entity->campaign()->associate($campaign);
            $entity->type()->associate(EntityType::standard($type));
            $entity->save();
        });

        $sheet = $entity('Aldebrand Fortecolline-de-la-Haute-Lande', 'character', 'Archiviste itinérant aux poches pleines de cartes annotées.');
        $campaign->playerCharacters()->create(['entity_id' => $sheet->id, 'user_id' => $player->id]);
        $entity('Bibliothèque engloutie de Vehrmund', 'place', 'Une salle de lecture sous trois brasses d’eau froide.');
        $entity('Confrérie des Lanternes', 'organization', 'Ils rallument les phares que d’autres éteignent.');
    }
}
