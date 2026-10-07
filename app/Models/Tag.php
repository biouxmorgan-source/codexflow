<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

#[Fillable(['name', 'color'])]
class Tag extends Model
{
    /** Couleurs proposées, purement visuelles : clé => teinte de la pastille. */
    public const COLORS = [
        'red' => '#c2410c',
        'amber' => '#b7791f',
        'green' => '#3d7a4a',
        'teal' => '#2e7d7a',
        'blue' => '#2f5d9c',
        'violet' => '#6b4fa0',
        'pink' => '#b04a7a',
        'stone' => '#78716c',
    ];

    /** @return array<string, string> libellés des couleurs, dans la langue de l'interface */
    public static function colorLabels(): array
    {
        return [
            'red' => __('Rouge'),
            'amber' => __('Ambre'),
            'green' => __('Vert'),
            'teal' => __('Turquoise'),
            'blue' => __('Bleu'),
            'violet' => __('Violet'),
            'pink' => __('Rose'),
            'stone' => __('Gris'),
        ];
    }

    public function hex(): ?string
    {
        return self::COLORS[$this->color] ?? null;
    }

    /** @return BelongsTo<User, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** @return BelongsToMany<Entity, $this> */
    public function entities(): BelongsToMany
    {
        return $this->belongsToMany(Entity::class);
    }

    /** @return BelongsToMany<Rule, $this> */
    public function rules(): BelongsToMany
    {
        return $this->belongsToMany(Rule::class);
    }

    /** @return BelongsToMany<Document, $this> */
    public function documents(): BelongsToMany
    {
        return $this->belongsToMany(Document::class);
    }

    /** @return BelongsToMany<Scene, $this> */
    public function scenes(): BelongsToMany
    {
        return $this->belongsToMany(Scene::class);
    }

    /** Éléments rangés sous ce tag, toutes catégories confondues. */
    public function usageCount(): int
    {
        return (int) ($this->entities_count + $this->rules_count + $this->documents_count + $this->scenes_count);
    }

    /**
     * Fusionne ce tag dans un autre du même compte : ses éléments passent sous l'autre, puis il disparaît.
     */
    public function mergeInto(self $target): void
    {
        abort_unless($target->user_id === $this->user_id && ! $target->is($this), 422);

        DB::transaction(function () use ($target) {
            foreach (['entities', 'rules', 'documents', 'scenes'] as $relation) {
                $target->{$relation}()->syncWithoutDetaching($this->{$relation}()->pluck($this->{$relation}()->getRelated()->getTable().'.id'));
            }

            $this->delete();
        });
    }

    /**
     * Retrouve ou crée les étiquettes d'un compte à partir d'une saisie « a, b, c » (sans tenir compte de la casse).
     *
     * @return list<int>
     */
    public static function idsFromInput(User $user, string $input): array
    {
        $names = collect(explode(',', $input))
            ->map(fn (string $name) => mb_substr(trim(preg_replace('/\s+/', ' ', $name)), 0, 60))
            ->filter()
            ->unique(fn (string $name) => mb_strtolower($name));

        return $names->map(function (string $name) use ($user) {
            $tag = static::query()->where('user_id', $user->getKey())->whereRaw('lower(name) = ?', [mb_strtolower($name)])->first();

            if ($tag === null) {
                $tag = new static(['name' => $name]);
                $tag->owner()->associate($user);
                $tag->save();
            }

            return $tag->getKey();
        })->values()->all();
    }
}
