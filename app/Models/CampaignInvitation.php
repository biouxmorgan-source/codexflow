<?php

namespace App\Models;

use App\Enums\CampaignRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Invitation à rejoindre une campagne : un lien secret, valable une fois et pendant un temps limité.
 * Le libellé et l'adresse e-mail sont de simples repères pour le MJ.
 */
class CampaignInvitation extends Model
{
    public const VALID_DAYS = 14;

    protected $fillable = ['label', 'email', 'role'];

    protected function casts(): array
    {
        return [
            'role' => CampaignRole::class,
            'accepted_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (CampaignInvitation $invitation) {
            $invitation->token ??= Str::random(48);
            $invitation->expires_at ??= now()->addDays(self::VALID_DAYS);
        });
    }

    /** @return BelongsTo<Campaign, $this> */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /** @return BelongsTo<User, $this> */
    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    /** @param Builder<CampaignInvitation> $query */
    public function scopePending(Builder $query): void
    {
        $query->whereNull('accepted_at')->where('expires_at', '>', now());
    }

    public function isUsable(): bool
    {
        return $this->accepted_at === null && $this->expires_at->isFuture();
    }

    public function url(): string
    {
        return route('invitations.show', $this->token);
    }
}
