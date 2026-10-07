<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Support\Appearance;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use NotificationChannels\WebPush\HasPushSubscriptions;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasPushSubscriptions, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'preferences' => 'array',
            'is_admin' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        // Un nouveau compte n'a pas besoin de « Quoi de neuf » : il découvre tout.
        static::creating(function (User $user) {
            $user->last_seen_version ??= config('codexflow.version');

            // Le premier compte d'une installation l'administre (problèmes signalés).
            if (! static::query()->exists()) {
                $user->is_admin = true;
            }
        });
    }

    /** Préférence d'affichage, ou sa valeur par défaut. */
    public function preference(string $key): string
    {
        $value = $this->preferences[$key] ?? null;

        return isset(Appearance::CHOICES[$key][$value]) ? $value : Appearance::DEFAULTS[$key];
    }

    /** @return BelongsToMany<Campaign, $this> */
    public function campaigns(): BelongsToMany
    {
        return $this->belongsToMany(Campaign::class, 'campaign_memberships')
            ->using(CampaignMembership::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    /** @return HasMany<Tag, $this> */
    public function tags(): HasMany
    {
        return $this->hasMany(Tag::class);
    }

    /** @return HasMany<GameSystem, $this> */
    public function gameSystems(): HasMany
    {
        return $this->hasMany(GameSystem::class);
    }

    /** @return HasMany<World, $this> */
    public function worlds(): HasMany
    {
        return $this->hasMany(World::class);
    }
}
