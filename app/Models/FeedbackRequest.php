<?php

namespace App\Models;

use App\Enums\CampaignRole;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Demande d'avis du MJ aux joueurs, en fin de séance ou de campagne : une note en étoiles
 * et deux commentaires. Anonyme, le MJ ne voit jamais qui a écrit quoi.
 */
#[Fillable(['anonymous', 'closed_at'])]
class FeedbackRequest extends Model
{
    protected $attributes = ['anonymous' => true];

    protected function casts(): array
    {
        return [
            'anonymous' => 'boolean',
            'closed_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Campaign, $this> */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /** @return BelongsTo<PlaySession, $this> */
    public function playSession(): BelongsTo
    {
        return $this->belongsTo(PlaySession::class);
    }

    /** @return BelongsTo<User, $this> */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** @return HasMany<FeedbackResponse, $this> */
    public function responses(): HasMany
    {
        return $this->hasMany(FeedbackResponse::class);
    }

    public function isOpen(): bool
    {
        return $this->closed_at === null;
    }

    /** « Session 3 » ou « Toute la campagne ». */
    public function subject(): string
    {
        return $this->playSession?->label() ?? __('Toute la campagne');
    }

    /** Un joueur de la campagne peut répondre tant que la demande est ouverte, une seule fois. */
    public function canAnswer(User $user): bool
    {
        return $this->isOpen()
            && $this->campaign->roleOf($user) === CampaignRole::Player
            && ! $this->responses()->where('user_id', $user->id)->exists();
    }

    public function average(): ?float
    {
        $average = $this->responses->avg('rating');

        return $average === null ? null : round((float) $average, 1);
    }

    /** @return array<int, int> nombre de réponses par note, de 5 à 1 */
    public function distribution(): array
    {
        $counts = $this->responses->countBy('rating');

        return collect([5, 4, 3, 2, 1])->mapWithKeys(fn (int $rating) => [$rating => (int) ($counts[$rating] ?? 0)])->all();
    }
}
