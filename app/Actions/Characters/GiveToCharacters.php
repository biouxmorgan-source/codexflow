<?php

namespace App\Actions\Characters;

use App\Enums\Zone;
use App\Models\ActivityLog;
use App\Models\Campaign;
use App\Models\CharacterGrant;
use App\Models\PlayerCharacter;
use Illuminate\Support\Facades\DB;

/**
 * Révèle ou donne un élément à un ou plusieurs personnages de la campagne, et l'inscrit au journal.
 * Une fiche, un document ou une règle déjà révélés à un personnage ne sont pas dupliqués.
 */
class GiveToCharacters
{
    /**
     * @param  list<int>  $characterIds
     * @param  array{kind: string, entity_id?: ?int, document_id?: ?int, rule_id?: ?int, title?: ?string, body?: ?string, quantity?: ?int}  $data
     * @return int nombre de personnages qui ont reçu l'élément
     */
    public function handle(Campaign $campaign, array $characterIds, array $data): int
    {
        $kind = $data['kind'];
        abort_unless(isset(CharacterGrant::KINDS[$kind]), 422);

        // Seuls une fiche ou un document accessibles dans la campagne peuvent être révélés.
        if ($kind === 'entity') {
            abort_unless($campaign->availableEntities()->whereKey($data['entity_id'] ?? 0)->exists(), 404);
        }

        if ($kind === 'document') {
            abort_unless($campaign->availableDocuments()->whereKey($data['document_id'] ?? 0)->exists(), 404);
        }

        // Une règle de la zone MJ ne s'ouvre jamais aux joueurs.
        if ($kind === 'rule') {
            abort_unless($campaign->availableRules()->where('zone', Zone::Public)->whereKey($data['rule_id'] ?? 0)->exists(), 404);
        }

        $characters = $campaign->playerCharacters()->with('entity')->whereKey($characterIds)->get();

        return DB::transaction(function () use ($characters, $data, $kind) {
            $given = 0;

            foreach ($characters as $character) {
                /** @var PlayerCharacter $character */
                $attributes = [
                    'kind' => $kind,
                    'entity_id' => $kind === 'entity' ? $data['entity_id'] : null,
                    'document_id' => $kind === 'document' ? $data['document_id'] : null,
                    'rule_id' => $kind === 'rule' ? $data['rule_id'] : null,
                    'title' => in_array($kind, ['information', 'possession'], true) ? trim((string) $data['title']) : null,
                    'body' => in_array($kind, ['information', 'possession'], true) ? (trim((string) ($data['body'] ?? '')) ?: null) : null,
                    'quantity' => $kind === 'possession' ? ($data['quantity'] ?? null) : null,
                ];

                if (in_array($kind, ['entity', 'document', 'rule'], true)) {
                    $column = $kind.'_id';

                    if ($character->grants()->where($column, $attributes[$column])->exists()) {
                        continue;
                    }
                }

                $grant = $character->grants()->create($attributes + ['granted_by' => auth()->id()]);
                self::record($grant, $character, 'created');
                $given++;
            }

            return $given;
        });
    }

    public static function revoke(CharacterGrant $grant): void
    {
        DB::transaction(function () use ($grant) {
            $grant->delete();
            self::record($grant, $grant->character, 'deleted');
        });
    }

    private static function record(CharacterGrant $grant, PlayerCharacter $character, string $event): void
    {
        $values = array_filter([
            'kind' => $grant->kind,
            // Sert au journal du joueur : il n'y voit que ce qui concerne son personnage.
            'character' => $character->id,
            'body' => $grant->body,
            'quantity' => $grant->quantity,
        ], fn ($value) => $value !== null);

        ActivityLog::record(
            'grant',
            $grant->id,
            $grant->label().' à '.$character->entity->name,
            $event,
            array_map(fn ($value) => $event === 'deleted' ? ['old' => $value, 'new' => null] : ['old' => null, 'new' => $value], $values),
            ['campaign_id' => $character->campaign_id],
        );
    }
}
