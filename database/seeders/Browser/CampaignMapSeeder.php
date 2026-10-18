<?php

namespace Database\Seeders\Browser;

use App\Actions\Account\DeleteAccount;
use App\Actions\Campaigns\CreateCampaign;
use App\Models\Campaign;
use App\Models\Document;
use App\Models\TableMap;
use App\Models\User;
use App\Support\Changelog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Données du test navigateur de la carte de campagne : un MJ, une campagne, le continent
 * (carte de campagne) avec un point qui ouvre la sous-carte de Port-Brume. Rejouable.
 */
class CampaignMapSeeder extends Seeder
{
    public const PASSWORD = 'navigateur-e2e-42';

    public const CAMPAIGN = 'Campagne carte e2e';

    public function run(CreateCampaign $createCampaign, DeleteAccount $delete): void
    {
        User::where('email', 'e2e-mj-carte@sagawyn.test')->get()
            ->each(fn (User $user) => $delete->handle(tap($user->forceFill(['is_admin' => false]))->saveQuietly()));

        $gm = User::factory()->create([
            'name' => 'MJ cartographe', 'email' => 'e2e-mj-carte@sagawyn.test', 'password' => self::PASSWORD,
            'last_seen_version' => Changelog::version(), 'email_verified_at' => now(),
        ]);
        $campaign = $createCampaign->handle($gm, ['name' => self::CAMPAIGN, 'new_game_name' => 'Jeu cartographe', 'new_world_name' => 'Monde cartographe']);

        $world = $this->map($campaign, $gm, 'Le continent', 1200, 600);
        $world->forceFill(['is_campaign_map' => true])->save();
        $city = $this->map($campaign, $gm, 'Port-Brume', 600, 600);

        $world->points()->make(['label' => 'Port-Brume', 'x' => 300, 'y' => 300, 'shown_at_table' => true])
            ->forceFill(['target_map_id' => $city->id])->save();
    }

    private function map(Campaign $campaign, User $gm, string $name, int $width, int $height): TableMap
    {
        $image = imagecreatetruecolor($width, $height);
        imagefill($image, 0, 0, imagecolorallocate($image, 60, 90, 110));
        ob_start();
        imagepng($image);
        $path = 'documents/'.Str::random(40).'.png';
        Storage::disk('local')->put($path, (string) ob_get_clean());

        $document = new Document(['title' => $name, 'disk' => 'local', 'path' => $path, 'original_name' => 'carte.png', 'mime_type' => 'image/png', 'size' => 1000]);
        $document->owner()->associate($gm);
        $document->campaign()->associate($campaign);
        $document->save();

        $map = new TableMap(['name' => $name, 'grid_size' => TableMap::defaultGridSize($width)]);
        $map->forceFill(['width' => $width, 'height' => $height]);
        $map->campaign()->associate($campaign);
        $map->document()->associate($document);
        $map->save();

        return $map;
    }
}
