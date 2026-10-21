<?php

namespace Database\Seeders\Browser;

use App\Actions\Account\DeleteAccount;
use App\Actions\Campaigns\CreateCampaign;
use App\Models\User;
use App\Support\Changelog;
use Illuminate\Database\Seeder;

/**
 * Données du test navigateur « Organiser l'écran » : un MJ, une campagne avec une session ouverte. Rejouable.
 */
class ScreenLayoutSeeder extends Seeder
{
    public const PASSWORD = 'navigateur-e2e-42';

    public function run(CreateCampaign $createCampaign, DeleteAccount $delete): void
    {
        User::where('email', 'e2e-mj-ecrans@sagawyn.test')->get()
            ->each(fn (User $user) => $delete->handle(tap($user->forceFill(['is_admin' => false]))->saveQuietly()));

        $gm = User::factory()->create([
            'name' => 'MJ rangé', 'email' => 'e2e-mj-ecrans@sagawyn.test', 'password' => self::PASSWORD,
            'last_seen_version' => Changelog::version(), 'email_verified_at' => now(),
        ]);
        $campaign = $createCampaign->handle($gm, ['name' => 'Campagne écrans e2e', 'new_game_name' => 'Jeu rangé', 'new_world_name' => 'Monde rangé']);
        $campaign->playSessions()->create(['number' => 1, 'started_at' => now()]);
    }
}
