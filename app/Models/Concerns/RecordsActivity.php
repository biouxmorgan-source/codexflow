<?php

namespace App\Models\Concerns;

use App\Models\ActivityLog;

/**
 * Inscrit au journal d'audit chaque création, modification et suppression du modèle.
 * Les attributs JSON de valeurs de champs sont comparés clé par clé.
 * Les mises à jour en masse (réordonnancement) ne passent pas par ici : la position n'est pas suivie.
 */
trait RecordsActivity
{
    /** Clé courte du type d'élément (voir ActivityLog::SUBJECTS). */
    abstract public function activityType(): string;

    /** Nom affiché dans le journal, conservé même après suppression. */
    abstract public function activityLabel(): string;

    /** @return array{campaign_id?: ?int, world_id?: ?int, game_system_id?: ?int} */
    abstract public function activityScope(): array;

    public static function bootRecordsActivity(): void
    {
        static::created(function (self $model) {
            // Les valeurs par défaut posées par la base (statut « prévue »…) ne sont pas encore
            // dans le modèle : on les relit pour que le journal et les modifications suivantes les voient.
            $stored = $model->newQueryWithoutScopes()->whereKey($model->getKey())->toBase()->first();

            if ($stored !== null) {
                $model->setRawAttributes(array_merge((array) $stored, $model->getAttributes()));
            }

            $model->logActivity('created', $model->activityDiff([], $model->getAttributes()));
        });

        static::updated(function (self $model) {
            $old = array_intersect_key($model->getRawOriginal(), $model->getChanges());
            $changes = $model->activityDiff($old, $model->getChanges());

            if ($changes !== []) {
                $model->logActivity('updated', $changes);
            }
        });

        static::deleted(fn (self $model) => $model->logActivity('deleted', $model->activityDiff($model->getRawOriginal(), [])));
    }

    private function logActivity(string $event, array $changes): void
    {
        ActivityLog::record($this->activityType(), $this->getKey(), $this->activityLabel(), $event, $changes, $this->activityScope());
    }

    /** @return list<string> */
    protected function activityIgnored(): array
    {
        return ['id', 'user_id', 'created_at', 'updated_at', 'position', 'disk', 'path', 'mime_type', 'size', 'game_system_id', 'campaign_id', 'world_id', 'entity_id'];
    }

    /** @return list<string> */
    protected function activityJsonAttributes(): array
    {
        return [];
    }

    /** Tableau aux clés triées, à tous les niveaux. */
    private static function sorted(mixed $value): mixed
    {
        if (is_array($value)) {
            ksort($value);

            return array_map(self::sorted(...), $value);
        }

        return $value;
    }

    /**
     * Différences entre deux jeux d'attributs bruts (tels qu'en base).
     *
     * @return array<string, array{old: mixed, new: mixed}>
     */
    private function activityDiff(array $old, array $new): array
    {
        $changes = [];

        foreach (array_unique([...array_keys($old), ...array_keys($new)]) as $key) {
            if (in_array($key, $this->activityIgnored(), true)) {
                continue;
            }

            if (in_array($key, $this->activityJsonAttributes(), true)) {
                $before = self::decodeJson($old[$key] ?? null);
                $after = self::decodeJson($new[$key] ?? null);

                foreach (array_unique([...array_keys($before), ...array_keys($after)]) as $field) {
                    // Un compteur relu de la base a ses clés triées ({max, value}) : on compare sans tenir compte de l'ordre.
                    if (self::sorted($before[$field] ?? null) !== self::sorted($after[$field] ?? null) || array_key_exists($field, $before) !== array_key_exists($field, $after)) {
                        $changes[$key.'.'.$field] = ['old' => $before[$field] ?? null, 'new' => $after[$field] ?? null];
                    }
                }

                continue;
            }

            $before = $old[$key] ?? null;
            $after = $new[$key] ?? null;

            if ($before === $after || ((string) $before === (string) $after && ! is_array($before) && ! is_array($after))) {
                continue;
            }

            $changes[$key] = ['old' => $before, 'new' => $after];
        }

        return $changes;
    }

    private static function decodeJson(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        return is_string($value) ? (array) (json_decode($value, true) ?? []) : [];
    }
}
