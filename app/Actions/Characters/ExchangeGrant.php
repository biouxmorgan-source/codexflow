<?php

namespace App\Actions\Characters;

use App\Models\ActivityLog;
use App\Models\CharacterGrant;
use App\Models\PlayerCharacter;
use App\Support\Live;
use App\Support\Notify;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

/**
 * Un personnage donne un objet à un autre, ou lui transmet ce qu'il sait (information,
 * fiche, document, règle). L'objet change de main ; une connaissance est partagée.
 * Le destinataire est prévenu, le MJ aussi, et l'échange va au journal.
 */
class ExchangeGrant
{
    /** @return CharacterGrant l'élément tel que le destinataire l'a reçu */
    public function handle(CharacterGrant $grant, PlayerCharacter $to, ?int $quantity = null): CharacterGrant
    {
        $from = $grant->character()->with('entity')->firstOrFail();
        Gate::authorize('play', $from);

        abort_unless($to->campaign_id === $from->campaign_id && $to->isNot($from) && $to->is_active, 404);
        $to->loadMissing('entity');

        [$received, $given] = DB::transaction(fn () => $grant->kind === 'possession'
            ? $this->move($grant, $to, $quantity)
            : [$this->share($grant, $to), null]);

        $label = $grant->kind === 'possession' && $given > 1 ? $grant->title.' ×'.$given : $received->label();
        $this->record($received, $label, $given, $from, $to);
        Notify::exchange($received, $label, $from, $to);
        Live::character($from->id);
        Live::character($to->id);

        return $received;
    }

    /**
     * L'objet change de main, en entier ou en partie.
     *
     * @return array{0: CharacterGrant, 1: int} l'objet chez le destinataire, la quantité donnée
     */
    private function move(CharacterGrant $grant, PlayerCharacter $to, ?int $quantity): array
    {
        $owned = max(1, (int) $grant->quantity);
        $quantity ??= $owned;

        if ($quantity < 1 || $quantity > $owned) {
            throw ValidationException::withMessages(['exchangeQuantity' => 'Quantité entre 1 et '.$owned.'.']);
        }

        // Même objet déjà chez le destinataire : on additionne.
        $same = $to->grants()->where('kind', 'possession')
            ->whereRaw('lower(title) = ?', [mb_strtolower((string) $grant->title)])
            ->where(fn ($q) => $grant->body === null ? $q->whereNull('body') : $q->where('body', $grant->body))
            ->first();

        if ($same === null && $quantity === $owned) {
            $grant->update(['player_character_id' => $to->id]);

            return [$grant, $quantity];
        }

        if ($same) {
            $same->update(['quantity' => max(1, (int) $same->quantity) + $quantity]);
        }

        $received = $same ?? $to->grants()->create([
            'kind' => 'possession',
            'title' => $grant->title,
            'body' => $grant->body,
            'quantity' => $quantity > 1 ? $quantity : null,
            'granted_by' => auth()->id(),
        ]);

        $quantity === $owned ? $grant->delete() : $grant->update(['quantity' => $owned - $quantity]);

        return [$received, $quantity];
    }

    /** Une connaissance se partage : le giver la garde. */
    private function share(CharacterGrant $grant, PlayerCharacter $to): CharacterGrant
    {
        $known = match ($grant->kind) {
            'entity', 'document', 'rule' => $to->grants()->where($grant->kind.'_id', $grant->{$grant->kind.'_id'})->exists(),
            default => $to->grants()->where('kind', $grant->kind)->where('title', $grant->title)
                ->where(fn ($q) => $grant->body === null ? $q->whereNull('body') : $q->where('body', $grant->body))->exists(),
        };

        if ($known) {
            throw ValidationException::withMessages(['exchange' => $to->entity->name.' le sait déjà.']);
        }

        return $to->grants()->create($grant->only(['kind', 'entity_id', 'document_id', 'rule_id', 'title', 'body']) + ['granted_by' => auth()->id()]);
    }

    private function record(CharacterGrant $grant, string $label, ?int $given, PlayerCharacter $from, PlayerCharacter $to): void
    {
        $values = array_filter([
            'kind' => ['old' => null, 'new' => $grant->kind],
            // Les deux personnages voient l'échange dans leur journal.
            'character' => ['old' => $from->id, 'new' => $to->id],
            'body' => $grant->body === null ? null : ['old' => null, 'new' => $grant->body],
            'quantity' => $given > 1 ? ['old' => null, 'new' => $given] : null,
        ]);

        ActivityLog::record(
            'grant',
            $grant->id,
            $label.' de '.$from->entity->name.' à '.$to->entity->name,
            'created',
            $values,
            ['campaign_id' => $from->campaign_id],
        );
    }
}
