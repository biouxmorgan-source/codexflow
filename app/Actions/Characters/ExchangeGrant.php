<?php

namespace App\Actions\Characters;

use App\Models\ActivityLog;
use App\Models\CharacterGrant;
use App\Models\ExchangeRequest;
use App\Models\PlayerCharacter;
use App\Support\Live;
use App\Support\Notify;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

/**
 * Un personnage donne un objet à un autre, ou lui transmet ce qu'il sait (information,
 * fiche, document, règle). L'objet change de main ; une connaissance est partagée.
 * Le destinataire est prévenu, le MJ aussi, et l'échange va au journal. Le MJ peut exiger
 * de valider chaque échange (réglage de la campagne, actif par défaut).
 */
class ExchangeGrant
{
    /**
     * Le joueur donne ou transmet. Si le MJ valide les échanges de la campagne, c'est une demande
     * qui attend sa réponse ; sinon l'échange a lieu tout de suite.
     *
     * @return CharacterGrant|ExchangeRequest l'élément reçu, ou la demande envoyée au MJ
     */
    public function handle(CharacterGrant $grant, PlayerCharacter $to, ?int $quantity = null): CharacterGrant|ExchangeRequest
    {
        $from = $grant->character()->with(['entity', 'campaign'])->firstOrFail();
        Gate::authorize('play', $from);

        abort_unless($to->campaign_id === $from->campaign_id && $to->isNot($from) && $to->is_active, 404);

        if ($grant->isPending()) {
            throw ValidationException::withMessages(['exchange' => __('Le MJ doit d\'abord valider cet objet.')]);
        }

        if ($grant->exchangeRequest()->exists()) {
            throw ValidationException::withMessages(['exchange' => __('Un échange de cet élément attend déjà le MJ.')]);
        }

        $to->loadMissing('entity');
        $quantity = $this->check($grant, $to, $quantity);

        if ($from->campaign->exchanges_need_approval && Gate::denies('manage', $from)) {
            return $this->request($grant, $from, $to, $quantity);
        }

        return $this->perform($grant, $from, $to, $quantity);
    }

    /** Le MJ accepte : l'échange a lieu, le joueur qui l'a proposé est prévenu. */
    public function approve(ExchangeRequest $request): CharacterGrant
    {
        $request->load(['grant', 'from.entity', 'to.entity']);
        Gate::authorize('manage', $request->from);

        $grant = $request->grant;
        $label = $request->label();
        abort_unless($grant->player_character_id === $request->from_character_id && $request->to->is_active, 404);
        $quantity = $this->check($grant, $request->to, $request->quantity);

        $request->delete();
        $received = $this->perform($grant, $request->from, $request->to, $quantity);
        Notify::player($request->from, 'grant', fn (string $locale) => __('Le MJ a accepté l’échange : :label à :receiver.', Notify::quoted(['label' => $label, 'receiver' => $request->to->entity->name], $locale), $locale), route('characters.show', [$request->campaign_id, $request->from_character_id]).'#section-'.$this->section($grant));

        return $received;
    }

    /** Le MJ refuse : rien ne bouge, le joueur est prévenu. */
    public function reject(ExchangeRequest $request): void
    {
        $request->load(['grant', 'from.entity', 'to.entity']);
        Gate::authorize('manage', $request->from);

        $label = $request->label();
        $request->delete();
        Notify::player($request->from, 'grant', fn (string $locale) => __('Le MJ a refusé l’échange : :label à :receiver.', Notify::quoted(['label' => $label, 'receiver' => $request->to->entity->name], $locale), $locale), route('characters.show', [$request->campaign_id, $request->from_character_id]).'#section-'.$this->section($request->grant));
        Live::character($request->from_character_id);
    }

    /** Le joueur retire sa proposition tant que le MJ n'a pas répondu. */
    public function cancel(ExchangeRequest $request): void
    {
        Gate::authorize('play', $request->from);

        $request->delete();
        Live::character($request->from_character_id);
    }

    /**
     * Ce qui empêcherait l'échange : quantité impossible, connaissance déjà partagée.
     *
     * @return int|null la quantité d'objets donnée (null pour une connaissance)
     */
    private function check(CharacterGrant $grant, PlayerCharacter $to, ?int $quantity): ?int
    {
        if ($grant->kind !== 'possession') {
            $known = match ($grant->kind) {
                'entity', 'document', 'rule' => $to->grants()->where($grant->kind.'_id', $grant->{$grant->kind.'_id'})->exists(),
                default => ($grant->secret_id !== null && $to->grants()->where('secret_id', $grant->secret_id)->exists())
                    || $to->grants()->where('kind', $grant->kind)->where('title', $grant->title)
                        ->where(fn ($q) => $grant->body === null ? $q->whereNull('body') : $q->where('body', $grant->body))->exists(),
            };

            if ($known) {
                throw ValidationException::withMessages(['exchange' => __(':name le sait déjà.', ['name' => $to->entity->name])]);
            }

            return null;
        }

        $owned = max(1, (int) $grant->quantity);
        $quantity ??= $owned;

        if ($quantity < 1 || $quantity > $owned) {
            throw ValidationException::withMessages(['exchangeQuantity' => __('Quantité entre 1 et :max.', ['max' => $owned])]);
        }

        return $quantity;
    }

    private function request(CharacterGrant $grant, PlayerCharacter $from, PlayerCharacter $to, ?int $quantity): ExchangeRequest
    {
        $request = $from->campaign->exchangeRequests()->create([
            'character_grant_id' => $grant->id,
            'from_character_id' => $from->id,
            'to_character_id' => $to->id,
            'quantity' => $quantity,
            'user_id' => auth()->id(),
        ]);
        $request->setRelation('grant', $grant);

        $replace = ['giver' => $from->entity->name, 'receiver' => $to->entity->name, 'label' => $request->label()];
        Notify::gameMasters($from->campaign, 'grant', fn (string $locale) => __(':giver propose de transmettre :label à :receiver : à valider.', Notify::quoted($replace, $locale), $locale), route('characters.show', [$from->campaign_id, $from]).'#section-'.$this->section($grant), $from);
        Live::character($from->id);

        return $request;
    }

    private function perform(CharacterGrant $grant, PlayerCharacter $from, PlayerCharacter $to, ?int $quantity): CharacterGrant
    {
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

    private function section(CharacterGrant $grant): string
    {
        return match ($grant->kind) {
            'possession' => 'possession',
            'rule' => 'rule',
            'document' => 'document',
            default => 'knowledge',
        };
    }

    /**
     * L'objet change de main, en entier ou en partie.
     *
     * @return array{0: CharacterGrant, 1: int} l'objet chez le destinataire, la quantité donnée
     */
    private function move(CharacterGrant $grant, PlayerCharacter $to, int $quantity): array
    {
        $owned = max(1, (int) $grant->quantity);

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

    /** Une connaissance se partage : celui qui la transmet la garde. Un secret partagé reste un secret connu. */
    private function share(CharacterGrant $grant, PlayerCharacter $to): CharacterGrant
    {
        return $to->grants()->create($grant->only(['kind', 'entity_id', 'document_id', 'rule_id', 'secret_id', 'title', 'body']) + ['granted_by' => auth()->id()]);
    }

    /** Une ligne de journal « de X à Y », visible dans le journal des deux personnages. */
    public static function record(CharacterGrant $grant, string $label, ?int $given, PlayerCharacter $from, PlayerCharacter $to): void
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
