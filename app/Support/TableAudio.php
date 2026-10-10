<?php

namespace App\Support;

use App\Enums\CampaignRole;
use App\Models\AudioTrack;
use App\Models\Campaign;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * Musique de l'écran de table : le MJ lance un morceau de la bibliothèque sonore depuis le
 * mode Session, l'écran de table (branché sur les enceintes) le joue. Elle se joue à côté de
 * ce qui est affiché, sans le remplacer. Les joueurs qui suivent l'écran sur leur appareil
 * ne l'entendent pas : autour de la table, chaque téléphone jouerait en décalé.
 */
final class TableAudio
{
    public const DEFAULT_VOLUME = 80;

    /** Qui entend la musique de l'écran : le MJ, ses co-MJ et le compte spectateur de la table. */
    public static function canHear(User $user, Campaign $campaign): bool
    {
        return in_array($campaign->roleOf($user), [CampaignRole::GameMaster, CampaignRole::Spectator], true);
    }

    public static function play(Campaign $campaign, AudioTrack $track): void
    {
        abort_unless($track->campaign_id === $campaign->id, 404);

        self::save($campaign, [
            'track' => $track->id,
            'loop' => $track->loop,
            'volume' => (int) ($campaign->table_audio['volume'] ?? self::DEFAULT_VOLUME),
            'playing' => true,
            'key' => Str::random(8),
        ]);
    }

    public static function pause(Campaign $campaign, bool $paused = true): void
    {
        if ($campaign->table_audio !== null) {
            self::save($campaign, ['playing' => ! $paused] + $campaign->table_audio);
        }
    }

    public static function loop(Campaign $campaign, bool $loop): void
    {
        if ($campaign->table_audio !== null) {
            self::save($campaign, ['loop' => $loop] + $campaign->table_audio);
        }
    }

    public static function volume(Campaign $campaign, int $volume): void
    {
        if ($campaign->table_audio !== null) {
            self::save($campaign, ['volume' => max(0, min(100, $volume))] + $campaign->table_audio);
        }
    }

    public static function stop(Campaign $campaign): void
    {
        $campaign->forceFill(['table_audio' => null])->save();
        TableDisplay::broadcast($campaign);
    }

    /**
     * Ce que joue l'écran, relu à chaque fois : un morceau supprimé entre-temps arrête la musique.
     *
     * @return array{track: AudioTrack, loop: bool, volume: int, playing: bool, key: string}|null
     */
    public static function current(Campaign $campaign): ?array
    {
        $state = $campaign->table_audio;
        $track = isset($state['track']) ? $campaign->audioTracks()->find($state['track']) : null;

        if ($track === null || ! CampaignFeatures::usable($campaign, 'table')) {
            return null;
        }

        return [
            'track' => $track,
            'loop' => (bool) ($state['loop'] ?? true),
            'volume' => (int) ($state['volume'] ?? self::DEFAULT_VOLUME),
            'playing' => (bool) ($state['playing'] ?? true),
            'key' => (string) ($state['key'] ?? ''),
        ];
    }

    /** @param array<string, mixed> $state */
    private static function save(Campaign $campaign, array $state): void
    {
        CampaignFeatures::ensure($campaign, 'table');

        $campaign->forceFill(['table_audio' => $state])->save();
        TableDisplay::broadcast($campaign);
    }
}
