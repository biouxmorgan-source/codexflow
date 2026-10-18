<?php

namespace Database\Seeders\Browser;

use App\Actions\Account\DeleteAccount;
use App\Actions\Campaigns\CreateCampaign;
use App\Enums\CampaignRole;
use App\Enums\FieldType;
use App\Enums\Zone;
use App\Models\Entity;
use App\Models\EntityType;
use App\Models\User;
use App\Support\Changelog;
use Illuminate\Database\Seeder;

/**
 * Données du test navigateur du mode hors ligne (tests/Browser) : une joueuse, son personnage,
 * un compteur et deux champs qu'elle peut modifier. Rejouable : les comptes de test sont recréés.
 */
class OfflineSheetSeeder extends Seeder
{
    public const PASSWORD = 'navigateur-e2e-42';

    public function run(CreateCampaign $createCampaign, DeleteAccount $delete): void
    {
        User::whereIn('email', ['e2e-mj-hl@sagawyn.test', 'e2e-joueuse-hl@sagawyn.test'])->get()
            ->each(fn (User $user) => $delete->handle(tap($user->forceFill(['is_admin' => false]))->saveQuietly()));

        $seen = ['last_seen_version' => Changelog::version(), 'email_verified_at' => now()];
        $gm = User::factory()->create(['name' => 'MJ hors ligne', 'email' => 'e2e-mj-hl@sagawyn.test', 'password' => self::PASSWORD] + $seen);
        $player = User::factory()->create(['name' => 'Joueuse hors ligne', 'email' => 'e2e-joueuse-hl@sagawyn.test', 'password' => self::PASSWORD] + $seen);

        $campaign = $createCampaign->handle($gm, ['name' => 'Campagne hors ligne e2e', 'new_game_name' => 'Jeu hors ligne e2e', 'new_world_name' => 'Monde hors ligne e2e']);
        $campaign->members()->attach($player, ['role' => CampaignRole::Player->value]);

        $field = fn (string $name, FieldType $type) => $campaign->gameSystem->fieldDefinitions()->create([
            'name' => $name, 'type' => $type, 'zone' => Zone::Public, 'player_editable' => true,
            'entity_type_id' => EntityType::standard('character')->id,
        ]);
        $health = $field('PV', FieldType::Counter);
        $job = $field('Métier', FieldType::Text);
        $mood = $field('Humeur', FieldType::Text);

        $sheet = new Entity(['name' => 'Ilse Varn']);
        $sheet->owner()->associate($gm);
        $sheet->campaign()->associate($campaign);
        $sheet->type()->associate(EntityType::standard('character'));
        $sheet->setFieldValues([$health->id => ['value' => 12, 'max' => 12], $job->id => 'Archéologue', $mood->id => 'Calme']);
        $sheet->save();
        $campaign->playerCharacters()->create(['entity_id' => $sheet->id, 'user_id' => $player->id]);
    }
}
