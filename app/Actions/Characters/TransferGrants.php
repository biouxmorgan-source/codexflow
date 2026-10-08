<?php

namespace App\Actions\Characters;

use App\Models\CharacterGrant;
use App\Models\PlayerCharacter;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Nouveau personnage d'un joueur : le MJ choisit ce qui lui passe de l'ancien.
 * Connaissances, informations, documents et règles sont recopiés (l'ancien les garde) ;
 * les objets changent de main. Un objet en attente de validation ou en cours d'échange reste.
 */
class TransferGrants
{
    /** @return Collection<int, CharacterGrant> ce que l'ancien personnage peut transmettre */
    public static function transferable(PlayerCharacter $from): Collection
    {
        return $from->grants()
            ->with(['entity.type', 'document', 'rule', 'exchangeRequest'])
            ->latest('id')
            ->get()
            ->reject(fn (CharacterGrant $grant) => $grant->isPending() || $grant->exchangeRequest !== null)
            ->values();
    }

    /** @param  list<int>  $grantIds */
    public function handle(PlayerCharacter $from, PlayerCharacter $to, array $grantIds): int
    {
        abort_unless($from->campaign_id === $to->campaign_id && $from->id !== $to->id, 422);

        $grants = self::transferable($from)->whereIn('id', array_map('intval', $grantIds));

        return DB::transaction(function () use ($grants, $from, $to) {
            $moved = 0;

            foreach ($grants as $grant) {
                // Une fiche, un document, une règle ou un secret déjà connus ne se dédoublent pas.
                $already = collect(['entity_id', 'document_id', 'rule_id', 'secret_id'])
                    ->contains(fn (string $column) => $grant->{$column} !== null && $to->grants()->where($column, $grant->{$column})->exists());

                if (! $already) {
                    $copy = $to->grants()->create($grant->only([
                        'kind', 'entity_id', 'document_id', 'rule_id', 'secret_id', 'title', 'body', 'quantity', 'added_by_player', 'validated_at',
                    ]) + ['granted_by' => auth()->id()]);
                    GiveToCharacters::record($copy, $to, 'created');
                    $moved++;
                }

                if ($grant->kind === 'possession') {
                    $grant->delete();
                    GiveToCharacters::record($grant, $from, 'deleted');
                }
            }

            return $moved;
        });
    }
}
