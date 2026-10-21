<?php

namespace App\Actions\Characters;

use App\Enums\FieldType;
use App\Enums\Zone;
use App\Models\CharacterNote;
use App\Models\Entity;
use App\Models\FieldDefinition;
use App\Models\PlayerCharacter;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Ce que le joueur a changé sur sa fiche sans réseau, envoyé au retour de la connexion.
 * Fusion champ par champ, jamais la fiche entière : ce que le MJ a ajouté entre-temps
 * (secret, connaissance, nouveau champ) n'est pas touché.
 *
 * - Compteurs : le joueur envoie ce qu'il a retiré ou ajouté (−3, +1), appliqué à la valeur
 *   actuelle ; les changements du MJ entre-temps sont donc conservés.
 * - Autres champs : appliqués seulement si le MJ n'a pas changé ce champ entre-temps ;
 *   sinon la valeur du MJ est gardée et le champ est signalé au joueur.
 * - Notes : ajoutées, à la date où le joueur les a écrites.
 *
 * Les droits sont vérifiés à nouveau ici : ce que le joueur ne peut plus modifier est écarté.
 */
class OfflineSync
{
    /** Une synchronisation déjà reçue (réseau coupé avant la réponse) n'est pas appliquée deux fois. */
    private const REPLAY_DAYS = 30;

    /** Types de champs modifiables hors ligne : les autres demandent la liste des fiches ou documents connus. */
    public const FIELD_TYPES = [FieldType::Text, FieldType::LongText, FieldType::Number, FieldType::Boolean, FieldType::Date, FieldType::Select, FieldType::Link];

    /**
     * @param  array{sync_id: string, counters?: array<int|string, int>, fields?: array<int|string, array{base: mixed, value: mixed}>, notes?: list<array{body: string, visibility: string, shares?: list<int>, written_at?: string}>}  $changes
     * @return array{counters: list<string>, fields: list<string>, conflicts: list<string>, rejected: list<string>, notes: int}|null null : la même synchronisation est en cours
     */
    public function handle(PlayerCharacter $character, User $user, array $changes): ?array
    {
        $key = 'offline-sync:'.$user->id.':'.$character->id.':'.$changes['sync_id'];

        if (! Cache::add($key, 'pending', now()->addDays(self::REPLAY_DAYS))) {
            $done = Cache::get($key);

            return is_array($done) ? $done : null;
        }

        try {
            $result = DB::transaction(fn () => $this->apply($character->fresh(), $user, $changes));
        } catch (\Throwable $error) {
            Cache::forget($key);

            throw $error;
        }

        Cache::put($key, $result, now()->addDays(self::REPLAY_DAYS));

        return $result;
    }

    private function apply(PlayerCharacter $character, User $user, array $changes): array
    {
        $result = ['counters' => [], 'fields' => [], 'conflicts' => [], 'rejected' => [], 'notes' => 0];
        $counters = $changes['counters'] ?? [];
        $fields = $changes['fields'] ?? [];

        if ($counters !== [] || $fields !== []) {
            $this->applyFields($character, $user, $counters, $fields, $result);
        }

        $notes = $changes['notes'] ?? [];

        if ($notes !== []) {
            if ($character->isPlayedBy($user) && $character->is_active) {
                $result['notes'] = $this->addNotes($character, $user, $notes);
            } else {
                $result['rejected'][] = trans_choice(':count note|:count notes', count($notes));
            }
        }

        return $result;
    }

    private function applyFields(PlayerCharacter $character, User $user, array $counters, array $fields, array &$result): void
    {
        // Verrou : le MJ peut modifier la fiche au même moment.
        $entity = Entity::query()->lockForUpdate()->findOrFail($character->entity_id);
        $campaign = $character->campaign;
        $definitions = $campaign->gameSystem->fieldDefinitions()
            ->forType($entity->entity_type_id)
            ->where('zone', Zone::Public)
            ->where('player_editable', true)
            ->get()
            ->keyBy('id');
        // Personnage verrouillé ou mis au repos entre-temps : plus rien ne bouge.
        $allowed = $user->can('play', $character);
        $values = [];

        foreach ($counters as $id => $delta) {
            $definition = $allowed ? $definitions->get((int) $id) : null;

            if ($definition?->type !== FieldType::Counter) {
                $result['rejected'][] = $this->name((int) $id, $definition);

                continue;
            }

            $values[$definition->id] = FieldType::adjustCounter($entity->fieldValue($definition), (int) $delta);
            $result['counters'][] = $definition->name;
        }

        foreach ($fields as $id => $change) {
            $definition = $allowed ? $definitions->get((int) $id) : null;

            if ($definition === null || ! in_array($definition->type, self::FIELD_TYPES, true)) {
                $result['rejected'][] = $this->name((int) $id, $definition);

                continue;
            }

            $current = $definition->type->input($entity->fieldValue($definition));
            $base = $this->normalize($definition, $change['base'] ?? null);
            $wanted = $this->normalize($definition, $change['value'] ?? null);

            if ($wanted === $current) {
                continue;
            }

            // Le MJ a changé ce champ pendant que le joueur était hors ligne : sa valeur reste.
            if ($base !== $current) {
                $result['conflicts'][] = $definition->name;

                continue;
            }

            [$value, $error] = $definition->parse($change['value'] ?? null, $campaign);

            if ($error !== null) {
                $result['rejected'][] = __(':field : :error.', ['field' => $definition->name, 'error' => $error]);

                continue;
            }

            $values[$definition->id] = $value;
            $result['fields'][] = $definition->name;
        }

        if ($values !== []) {
            $entity->setFieldValues($values);
            $entity->save();
        }
    }

    /** Saisie ramenée à la forme de FieldType::input, pour comparer avec la valeur actuelle. */
    private function normalize(FieldDefinition $definition, mixed $raw): string|bool
    {
        if ($definition->type === FieldType::Boolean) {
            return filter_var($raw, FILTER_VALIDATE_BOOLEAN);
        }

        return trim(is_scalar($raw) ? (string) $raw : '');
    }

    private function name(int $id, ?FieldDefinition $definition): string
    {
        return $definition?->name ?? FieldDefinition::query()->whereKey($id)->value('name') ?? __('un champ supprimé');
    }

    private function addNotes(PlayerCharacter $character, User $user, array $notes): int
    {
        $companions = $character->campaign->playerCharacters()->active()
            ->whereKeyNot($character->id)->whereNotNull('user_id')->pluck('id')->all();
        $session = $character->campaign->openSession();
        $added = 0;

        foreach ($notes as $data) {
            $visibility = array_key_exists($data['visibility'] ?? '', CharacterNote::VISIBILITIES) ? $data['visibility'] : 'gm';
            $shares = array_values(array_intersect(array_map('intval', $data['shares'] ?? []), $companions));

            if ($visibility === 'players' && $shares === []) {
                $visibility = 'gm';
            }

            $note = new CharacterNote(['body' => trim($data['body']), 'visibility' => $visibility]);
            $note->character()->associate($character);
            $note->author()->associate($user);
            $note->playSession()->associate($session);
            $writtenAt = $this->writtenAt($data['written_at'] ?? null);

            if ($writtenAt !== null) {
                $note->created_at = $note->updated_at = $writtenAt;
            }

            $note->save();
            $note->sharedWith()->sync($visibility === 'players' ? $shares : []);
            $added++;
        }

        return $added;
    }

    /** Date d'écriture donnée par l'appareil : gardée si elle est plausible (passée, de moins d'un mois). */
    private function writtenAt(?string $value): ?Carbon
    {
        try {
            $date = $value ? Carbon::parse($value) : null;
        } catch (\Throwable) {
            return null;
        }

        return $date !== null && $date->isPast() && $date->greaterThan(now()->subDays(self::REPLAY_DAYS)) ? $date : null;
    }
}
