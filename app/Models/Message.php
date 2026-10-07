<?php

namespace App\Models;

use App\Enums\CampaignRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Message entre le MJ et les joueurs. Il appartient à la conversation d'un personnage
 * (MJ ↔ joueur) ou s'adresse à tout le groupe (player_character_id nul).
 * Sa visibilité est vérifiée côté serveur (scopeVisibleTo).
 */
class Message extends Model
{
    protected $fillable = ['body'];

    /** @return BelongsTo<Campaign, $this> */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /** @return BelongsTo<PlayerCharacter, $this> conversation du personnage, null pour le groupe */
    public function character(): BelongsTo
    {
        return $this->belongsTo(PlayerCharacter::class, 'player_character_id');
    }

    /** @return BelongsTo<User, $this> */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /** @return BelongsTo<PlayerCharacter, $this> personnage au nom duquel un joueur a écrit */
    public function senderCharacter(): BelongsTo
    {
        return $this->belongsTo(PlayerCharacter::class, 'sender_character_id');
    }

    /** Nom affiché de l'auteur : son personnage pour un joueur, « MJ » sinon. */
    public function senderLabel(): string
    {
        return $this->senderCharacter?->entity?->name ?? __('MJ');
    }

    /** @return BelongsTo<Entity, $this> */
    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
    }

    /** @return BelongsTo<Document, $this> */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /** @return BelongsTo<Rule, $this> */
    public function rule(): BelongsTo
    {
        return $this->belongsTo(Rule::class);
    }

    /** @return BelongsToMany<User, $this> */
    public function readers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'message_reads')->withPivot('read_at');
    }

    public function isForGroup(): bool
    {
        return $this->player_character_id === null;
    }

    public function hasReference(): bool
    {
        return $this->entity_id !== null || $this->document_id !== null || $this->rule_id !== null;
    }

    /**
     * Messages d'une campagne qu'un utilisateur a le droit de lire : tous pour le MJ ;
     * pour un joueur, ceux du groupe et ceux de ses personnages.
     *
     * @param  Builder<Message>  $query
     */
    public function scopeVisibleTo(Builder $query, User $user, Campaign $campaign): void
    {
        $query->where('messages.campaign_id', $campaign->id);

        $role = $campaign->roleOf($user);

        if ($role === null || $role === CampaignRole::Spectator) {
            $query->whereRaw('false');

            return;
        }

        if ($role === CampaignRole::GameMaster) {
            return;
        }

        $query->where(fn (Builder $q) => $q
            ->whereNull('player_character_id')
            ->orWhereIn('player_character_id', PlayerCharacter::query()
                ->where('campaign_id', $campaign->id)
                ->where('user_id', $user->id)
                ->select('id')));
    }

    /**
     * Messages reçus et pas encore lus par l'utilisateur.
     *
     * @param  Builder<Message>  $query
     */
    public function scopeUnreadBy(Builder $query, User $user): void
    {
        $query->where('sender_id', '!=', $user->id)
            ->whereDoesntHave('readers', fn (Builder $q) => $q->whereKey($user->id));
    }

    public static function unreadCount(User $user, Campaign $campaign): int
    {
        return self::query()->visibleTo($user, $campaign)->unreadBy($user)->count();
    }

    /**
     * Marque lus les messages affichés et les notifications qui les annoncent.
     *
     * @param  Collection<int, Message>  $messages
     * @param  int|'group'|null  $scope  conversation affichée au MJ (personnage ou groupe) ; null = toutes
     * @return int nombre d'éléments passés en lu
     */
    public static function markRead(User $user, Campaign $campaign, Collection $messages, int|string|null $scope = null): int
    {
        $changed = $user->unreadNotifications()
            ->whereRaw("data->>'kind' = 'message'")
            ->whereRaw("(data->>'campaign_id')::bigint = ?", [$campaign->id])
            ->when($scope === 'group', fn ($q) => $q->whereRaw("data->>'character_id' is null"))
            ->when(is_int($scope), fn ($q) => $q->whereRaw("(data->>'character_id')::bigint = ?", [$scope]))
            ->update(['read_at' => now()]);

        $unread = $messages->filter(fn (Message $message) => $message->sender_id !== $user->id)->modelKeys();

        if ($unread !== []) {
            $now = now();
            $changed += DB::table('message_reads')->insertOrIgnore(array_map(fn (int $id) => [
                'message_id' => $id,
                'user_id' => $user->id,
                'read_at' => $now,
            ], $unread));
        }

        return $changed;
    }
}
