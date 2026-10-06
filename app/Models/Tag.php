<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['name'])]
class Tag extends Model
{
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
