<?php

namespace App\Support;

use App\Models\AudioTrack;
use App\Models\Campaign;
use App\Models\User;
use App\Support\Demo\DemoLock;
use App\Support\Plans\Plans;
use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * Sons d'une campagne et de la bibliothèque personnelle du MJ : un son de bibliothèque est un seul
 * fichier, compté une fois dans l'espace de son MJ, ajouté aux campagnes qu'il mène sans copie.
 */
final class SoundLibrary
{
    /** Règles du téléversement, communes à la page Sons d'une campagne et à la bibliothèque. */
    public static function rules(): array
    {
        return [
            'uploads' => ['required', 'array', 'max:20'],
            'uploads.*' => ['file', 'max:'.AudioTrack::MAX_KB, function (string $attribute, mixed $file, Closure $fail) {
                if (! $file instanceof TemporaryUploadedFile || ! AudioTrack::accepts($file)) {
                    $fail(__('Le fichier doit être un son : mp3, ogg, m4a, wav, flac ou webm.'));
                }
            }],
            'tags' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Range les fichiers dans une campagne, ou dans la bibliothèque de $owner quand $campaign est vide.
     *
     * @param  array<int, TemporaryUploadedFile>  $files
     * @param  list<int>  $tagIds
     * @return list<int> les sons créés
     */
    public static function store(array $files, User $owner, ?Campaign $campaign, bool $loop, array $tagIds): array
    {
        $ids = [];

        Plans::ensureRoom($owner, array_sum(array_map(fn ($file) => (int) $file->getSize(), $files)), 'uploads');

        foreach ($files as $file) {
            $name = mb_substr($file->getClientOriginalName(), 0, 255);
            $track = new AudioTrack([
                'title' => mb_substr(pathinfo($name, PATHINFO_FILENAME) ?: $name, 0, 255),
                'loop' => $loop,
                'disk' => AudioTrack::DISK,
                'path' => $file->storeAs('audio', Str::random(40).'.'.strtolower($file->getClientOriginalExtension()), AudioTrack::DISK),
                'original_name' => $name,
                'mime_type' => AudioTrack::audioMime($file),
                'size' => $file->getSize(),
            ]);
            $track->owner()->associate($owner);
            $track->campaign()->associate($campaign);
            $track->save();
            $track->tags()->sync($tagIds);
            $ids[] = $track->id;
        }

        return $ids;
    }

    /**
     * Passe un son de la campagne dans la bibliothèque de son MJ, en le gardant dans la campagne.
     * Si la bibliothèque a déjà le même fichier (une copie téléversée dans une autre campagne),
     * la copie est remplacée par l'original partout puis supprimée : l'espace est libéré.
     */
    public static function moveToLibrary(AudioTrack $track): AudioTrack
    {
        $campaign = $track->campaign;
        abort_if($campaign === null, 404);

        $same = self::sameFileInLibrary($track);

        if ($same === null) {
            $track->forceFill(['campaign_id' => null])->save();
            $track->linkedCampaigns()->syncWithoutDetaching([$campaign->id]);

            return $track;
        }

        DB::transaction(function () use ($track, $same, $campaign) {
            $same->linkedCampaigns()->syncWithoutDetaching([$campaign->id]);
            $same->tags()->syncWithoutDetaching($track->tags()->pluck('tags.id'));

            foreach (DB::table('audio_track_scene')->where('audio_track_id', $track->id)->get() as $link) {
                DB::table('audio_track_scene')->insertOrIgnore(['scene_id' => $link->scene_id, 'audio_track_id' => $same->id, 'position' => $link->position]);
            }

            if (($campaign->table_audio['track'] ?? null) === $track->id) {
                $campaign->forceFill(['table_audio' => ['track' => $same->id] + $campaign->table_audio])->save();
            }

            $track->delete();
        });

        return $same;
    }

    /** Ajoute un son de bibliothèque à une campagne : le fichier n'est pas copié. */
    public static function link(AudioTrack $track, Campaign $campaign): void
    {
        abort_unless($track->inLibrary(), 404);
        DemoLock::guardCampaign($campaign);

        $track->linkedCampaigns()->syncWithoutDetaching([$campaign->id]);
    }

    /** Retire un son de bibliothèque d'une campagne (et de ses scènes) sans toucher au fichier. */
    public static function detach(AudioTrack $track, Campaign $campaign): void
    {
        DemoLock::guardCampaign($campaign);

        if (($campaign->table_audio['track'] ?? null) === $track->id) {
            TableAudio::stop($campaign);
        }

        DB::table('audio_track_scene')
            ->where('audio_track_id', $track->id)
            ->whereIn('scene_id', $campaign->scenes()->select('scenes.id'))
            ->delete();
        $track->linkedCampaigns()->detach($campaign->id);
    }

    /** Supprime le son et son fichier ; l'écran de table qui le jouait s'arrête. */
    public static function delete(AudioTrack $track): void
    {
        $campaigns = $track->inLibrary() ? $track->linkedCampaigns()->get() : collect([$track->campaign])->filter();

        foreach ($campaigns as $campaign) {
            if (($campaign->table_audio['track'] ?? null) === $track->id) {
                TableAudio::stop($campaign);
            }
        }

        $track->delete();
    }

    private static function sameFileInLibrary(AudioTrack $track): ?AudioTrack
    {
        $disk = Storage::disk($track->disk);

        if (! $disk->exists($track->path)) {
            return null;
        }

        $hash = hash_file('sha256', $disk->path($track->path));

        return AudioTrack::query()
            ->whereNull('campaign_id')
            ->where('user_id', $track->user_id)
            ->where('size', $track->size)
            ->get()
            ->first(fn (AudioTrack $other) => Storage::disk($other->disk)->exists($other->path)
                && hash_file('sha256', Storage::disk($other->disk)->path($other->path)) === $hash);
    }
}
