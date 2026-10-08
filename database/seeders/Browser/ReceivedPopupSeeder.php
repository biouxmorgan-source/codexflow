<?php

namespace Database\Seeders\Browser;

use App\Actions\Account\DeleteAccount;
use App\Actions\Campaigns\CreateCampaign;
use App\Actions\Characters\GiveToCharacters;
use App\Enums\CampaignRole;
use App\Models\Entity;
use App\Models\EntityType;
use App\Models\User;
use App\Support\Changelog;
use Illuminate\Database\Seeder;

/**
 * Données du test navigateur de la fenêtre « reçu » (tests/Browser) : un MJ, une joueuse,
 * son personnage et deux informations reçues. Rejouable : les comptes de test sont recréés.
 */
class ReceivedPopupSeeder extends Seeder
{
    public const PASSWORD = 'navigateur-e2e-42';

    public function run(CreateCampaign $createCampaign, GiveToCharacters $give, DeleteAccount $delete): void
    {
        User::whereIn('email', ['e2e-mj@loremundi.test', 'e2e-joueuse@loremundi.test'])->get()->each(fn (User $user) => $delete->handle($user));

        $seen = ['last_seen_version' => Changelog::version(), 'email_verified_at' => now()];
        $gm = User::factory()->create(['name' => 'MJ e2e', 'email' => 'e2e-mj@loremundi.test', 'password' => self::PASSWORD] + $seen);
        $player = User::factory()->create(['name' => 'Joueuse e2e', 'email' => 'e2e-joueuse@loremundi.test', 'password' => self::PASSWORD] + $seen);

        $campaign = $createCampaign->handle($gm, ['name' => 'Campagne e2e', 'new_game_name' => 'Jeu e2e', 'new_world_name' => 'Monde e2e']);
        $campaign->members()->attach($player, ['role' => CampaignRole::Player->value]);

        $sheet = new Entity(['name' => 'Ilse Varn']);
        $sheet->owner()->associate($gm);
        $sheet->campaign()->associate($campaign);
        $sheet->type()->associate(EntityType::standard('character'));
        $sheet->save();
        $character = $campaign->playerCharacters()->create(['entity_id' => $sheet->id, 'user_id' => $player->id]);

        $give->handle($campaign, [$character->id], ['kind' => 'information', 'title' => 'Le phare est éteint', 'body' => 'Personne ne l’a rallumé depuis trois nuits.']);
        $give->handle($campaign, [$character->id], ['kind' => 'possession', 'title' => 'Clé de bronze', 'quantity' => 1]);
    }
}
