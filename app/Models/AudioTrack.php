<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Morceau de la bibliothèque sonore d'une campagne : musique ou ambiance, lancée en session
 * sur l'appareil du MJ ou sur l'écran de table. Réservée au MJ et à ses co-MJ.
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
