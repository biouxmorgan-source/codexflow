<?php

namespace Database\Seeders\Browser;

use App\Actions\Account\DeleteAccount;
use App\Actions\Campaigns\CreateCampaign;
use App\Models\Document;
use App\Models\TableMap;
use App\Models\User;
use App\Support\Changelog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Données du test navigateur « Organiser l'écran » : un MJ, une campagne avec une session ouverte et une carte. Rejouable.
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

        $image = imagecreatetruecolor(800, 450);
        imagefill($image, 0, 0, imagecolorallocate($image, 60, 90, 110));
        ob_start();
        imagepng($image);
        $path = 'documents/'.Str::random(40).'.png';
        Storage::disk('local')->put($path, (string) ob_get_clean());

        $document = new Document(['title' => 'Crypte', 'disk' => 'local', 'path' => $path, 'original_name' => 'crypte.png', 'mime_type' => 'image/png', 'size' => 1000]);
        $document->owner()->associate($gm);
        $document->campaign()->associate($campaign);
        $document->save();

        $map = new TableMap(['name' => 'Crypte', 'grid_size' => TableMap::defaultGridSize(800)]);
        $map->forceFill(['width' => 800, 'height' => 450]);
        $map->campaign()->associate($campaign);
        $map->document()->associate($document);
        $map->save();
    }
}
