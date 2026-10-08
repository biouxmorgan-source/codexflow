<?php

namespace App\Actions\Characters;

use App\Models\ActivityLog;
use App\Models\CharacterGrant;
use App\Models\PlayerCharacter;
use App\Support\Live;
use App\Support\Notify;
use Illuminate\Support\Facades\Gate;

/**
 * Ce que le joueur note lui-même sur sa fiche : une connaissance, ou un objet que le MJ
 * voit aussitôt et valide. Tout passe au journal, le MJ est prévenu.
 */
class PlayerAdditions
{
    public const KINDS = ['information', 'possession'];

    public function add(PlayerCharacter $character, string $kind, string $title, ?string $body = null, ?int $quantity = null): CharacterGrant
    {
        Gate::authorize('play', $character);
        abort_unless(in_array($kind, self::KINDS, true), 422);

        $grant = $character->grants()->create([
            'kind' => $kind,
            'title' => trim($title),
            'body' => trim((string) $body) ?: null,
            'quantity' => $kind === 'possession' && $quantity > 1 ? $quantity : null,
            'granted_by' => auth()->id(),
            'added_by_player' => true,
        ]);

        self::record($grant, $character, 'created');
        Notify::playerAddition($grant, $character);
        Live::character($character->id);

        return $grant;
    }

    public function validate(CharacterGrant $grant): void
    {
        $character = $grant->character()->with('entity')->firstOrFail();
        Gate::authorize('manage', $character);

        if (! $grant->isPending()) {
            return;
        }

        $grant->update(['validated_at' => now()]);
        self::record($grant, $character, 'updated');
        Notify::player($character, 'grant', fn (string $locale) => __('Le MJ a validé « :label ».', ['label' => $grant->label()], $locale), route('characters.show', [$character->campaign_id, $character]).'#section-possession');
        Live::character($character->id);
    }

    /** Le joueur efface ce qu'il avait noté lui-même (jamais ce que le MJ lui a donné). */
    public function remove(CharacterGrant $grant): void
    {
        $character = $grant->character()->with('entity')->firstOrFail();
        Gate::authorize('play', $character);
        // Un objet validé par le MJ fait partie de la fiche : seul le MJ peut le retirer.
        abort_unless($grant->added_by_player && $grant->validated_at === null, 403);

        $grant->delete();
        self::record($grant, $character, 'deleted');
        Live::character($character->id);
    }

    private static function record(CharacterGrant $grant, PlayerCharacter $character, string $event): void
    {
        $values = array_filter([
            'kind' => $grant->kind,
            'character' => $character->id,
            'body' => $grant->body,
            'quantity' => $grant->quantity,
            'added_by_player' => true,
        ], fn ($value) => $value !== null);

        ActivityLog::record(
            'grant',
            $grant->id,
            $grant->label().' à '.$character->entity->name,
            $event,
            match ($event) {
                'deleted' => array_map(fn ($value) => ['old' => $value, 'new' => null], $values),
                'updated' => ['kind' => ['old' => $grant->kind, 'new' => $grant->kind], 'character' => ['old' => null, 'new' => $character->id], 'added_by_player' => ['old' => null, 'new' => true], 'validated' => ['old' => null, 'new' => true]],
                default => array_map(fn ($value) => ['old' => null, 'new' => $value], $values),
            },
            ['campaign_id' => $character->campaign_id],
        );
    }
}
