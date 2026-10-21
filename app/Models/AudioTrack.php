<?php

namespace App\Models;

use App\Enums\CampaignRole;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Morceau de la bibliothèque sonore : musique ou ambiance, lancée en session sur l'appareil du MJ
 * ou sur l'écran de table. Réservée au MJ et à ses co-MJ.
 *
 * Il appartient soit à une campagne, soit (campaign_id vide) à la bibliothèque personnelle de son
 * MJ : le même fichier sert alors dans chaque campagne où il l'ajoute (audio_track_campaign).
 */
#[Fillable(['title', 'loop', 'disk', 'path', 'original_name', 'mime_type', 'size'])]
class AudioTrack extends Model
{
    public const DISK = 'local';

    /** Extensions acceptées au téléversement. */
    public const EXTENSIONS = ['mp3', 'ogg', 'oga', 'opus', 'm4a', 'aac', 'wav', 'flac', 'webm'];

    /** 50 Mo par morceau, en Ko (règle « max »). */
    public const MAX_KB = 51200;

    protected function casts(): array
    {
        return [
            'loop' => 'boolean',
            'size' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::deleted(fn (AudioTrack $track) => Storage::disk($track->disk)->delete($track->path));
    }

    /** @return BelongsTo<User, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** @return BelongsTo<Campaign, $this> */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /** @return BelongsToMany<Campaign, $this> campagnes où ce son de bibliothèque est ajouté */
    public function linkedCampaigns(): BelongsToMany
    {
        return $this->belongsToMany(Campaign::class, 'audio_track_campaign');
    }

    public function inLibrary(): bool
    {
        return $this->campaign_id === null;
    }

    /**
     * Sons utilisables dans une campagne : les siens, et ceux qu'un de ses MJ y a ajoutés depuis sa
     * bibliothèque. Un co-MJ qui quitte la campagne reprend les siens.
     *
     * @param  Builder<AudioTrack>  $query
     */
    public function scopeAvailableIn(Builder $query, Campaign $campaign): void
    {
        $query->where(function (Builder $q) use ($campaign) {
            $q->where('audio_tracks.campaign_id', $campaign->getKey())
                ->orWhere(fn (Builder $library) => $library->whereNull('audio_tracks.campaign_id')
                    ->whereIn('audio_tracks.id', fn ($links) => $links->select('audio_track_id')->from('audio_track_campaign')->where('campaign_id', $campaign->getKey()))
                    ->whereIn('audio_tracks.user_id', fn ($gms) => $gms->select('user_id')->from('campaign_memberships')->where('campaign_id', $campaign->getKey())->where('role', CampaignRole::GameMaster->value)));
        });
    }

    /** @return BelongsToMany<Tag, $this> */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->orderByRaw('lower(tags.name)');
    }

    /** @return BelongsToMany<Scene, $this> */
    public function scenes(): BelongsToMany
    {
        return $this->belongsToMany(Scene::class);
    }

    /**
     * Un fichier son : extension connue et contenu audio. Les .m4a et .webm sont parfois
     * reconnus comme vidéo : leur conteneur est le même.
     */
    public static function accepts(UploadedFile $file): bool
    {
        $mime = self::sniff($file);

        return in_array(strtolower($file->getClientOriginalExtension()), self::EXTENSIONS, true)
            && (str_starts_with($mime, 'audio/') || in_array($mime, ['application/ogg', 'video/mp4', 'video/webm', 'video/ogg'], true));
    }

    /** Type envoyé au navigateur : un .m4a reconnu comme vidéo reste un son. */
    public static function audioMime(UploadedFile $file): string
    {
        $mime = self::sniff($file);

        return str_starts_with($mime, 'audio/') ? $mime : match ($mime) {
            'video/mp4' => 'audio/mp4',
            'video/webm' => 'audio/webm',
            default => 'audio/ogg',
        };
    }

    /** Type lu dans le contenu du fichier, jamais dans son nom. */
    private static function sniff(UploadedFile $file): string
    {
        $path = $file->getRealPath();

        return $path ? (string) (new \finfo(FILEINFO_MIME_TYPE))->file($path) : '';
    }

    public function humanSize(): string
    {
        return match (true) {
            $this->size >= 1_048_576 => __(':size Mo', ['size' => number_format($this->size / 1_048_576, 1, ',', ' ')]),
            $this->size >= 1024 => __(':size Ko', ['size' => number_format($this->size / 1024, 0, ',', ' ')]),
            default => __(':size o', ['size' => $this->size]),
        };
    }
}
