<?php

namespace App\Support\Plans;

use App\Models\Attachment;
use App\Models\AudioTrack;
use App\Models\Document;
use App\Models\Entity;
use App\Models\GameSystem;
use App\Models\PlayerCharacter;
use App\Models\User;
use App\Models\World;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Number;

/**
 * Espace occupé par les fichiers d'un compte : documents, pièces jointes, images de fiches
 * et feuilles PDF des personnages de ses campagnes.
 */
class StorageUsage
{
    public static function bytes(User $user, bool $fresh = false): int
    {
        $key = 'storage-usage:'.$user->id;

        if ($fresh) {
            Cache::forget($key);
        }

        return Cache::remember($key, now()->addMinutes(10), fn () => self::measure($user));
    }

    public static function format(int $bytes): string
    {
        $units = [__('o'), __('Ko'), __('Mo'), __('Go'), __('To')];
        $power = $bytes > 0 ? min((int) floor(log($bytes, 1024)), count($units) - 1) : 0;
        $value = $bytes / 1024 ** $power;

        return Number::format($value, $power > 0 && $value < 10 ? 1 : 0, locale: app()->getLocale()).' '.$units[$power];
    }

    private static function measure(User $user): int
    {
        $bytes = (int) Document::where('user_id', $user->id)->sum('size')
            + (int) Attachment::where('user_id', $user->id)->sum('size')
            + (int) AudioTrack::where('user_id', $user->id)->sum('size');

        $disk = Storage::disk(Entity::FILES_DISK);
        Entity::where('user_id', $user->id)->whereNotNull('image_path')->pluck('image_path')
            ->each(function (string $path) use ($disk, &$bytes) {
                $bytes += $disk->exists($path) ? $disk->size($path) : 0;
            });

        // Images des pages jeu et monde.
        GameSystem::where('user_id', $user->id)->whereNotNull('image_path')->pluck('image_path')
            ->merge(World::where('user_id', $user->id)->whereNotNull('image_path')->pluck('image_path'))
            ->each(function (string $path) use ($disk, &$bytes) {
                $bytes += $disk->exists($path) ? $disk->size($path) : 0;
            });

        $sheets = Storage::disk(PlayerCharacter::DISK);
        PlayerCharacter::whereNotNull('sheet_path')->whereHas('campaign', fn ($q) => $q->where('user_id', $user->id))->pluck('sheet_path')
            ->each(function (string $path) use ($sheets, &$bytes) {
                $bytes += $sheets->exists($path) ? $sheets->size($path) : 0;
            });

        return $bytes;
    }
}
