<?php

namespace App\Support;

use App\Enums\Zone;
use App\Models\Campaign;
use App\Models\Document;
use App\Models\Entity;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Écran de table : ce que le MJ montre aux joueurs sur un second écran (carte, image,
 * zone publique d'une fiche, annonce). Jamais la zone MJ.
 */
class TableDisplay
{
    public static function showDocument(Campaign $campaign, Document $document): void
    {
        self::save($campaign, ['kind' => 'document', 'id' => $document->id]);
    }

    public static function showEntity(Campaign $campaign, Entity $entity): void
    {
        self::save($campaign, ['kind' => 'entity', 'id' => $entity->id]);
    }

    public static function showText(Campaign $campaign, string $text): void
    {
        self::save($campaign, ['kind' => 'text', 'text' => $text]);
    }

    public static function clear(Campaign $campaign): void
    {
        self::save($campaign, null);
    }

    /** Le MJ ouvre ou ferme l'écran de table aux joueurs. */
    public static function share(Campaign $campaign, bool $shared): void
    {
        $campaign->forceFill(['table_shared' => $shared])->save();
        self::broadcast($campaign);
    }

    /** Le MJ voit toujours l'écran ; un joueur seulement quand le MJ le partage. */
    public static function canWatch(User $user, Campaign $campaign): bool
    {
        return $campaign->isGameMaster($user) || ($campaign->table_shared && $user->can('view', $campaign));
    }

    /**
     * Ce qui est affiché, relu à chaque fois : un élément supprimé ou retiré de la campagne
     * entre-temps laisse l'écran vide.
     *
     * @return array{kind: string, document?: Document, entity?: Entity, fields?: Collection<int, array{label: string, value: string}>, text?: string, key: string}|null
     */
    public static function current(Campaign $campaign): ?array
    {
        $state = $campaign->table_display;
        $key = ($state['kind'] ?? '').'-'.($state['id'] ?? '').'-'.($state['at'] ?? '');

        return match ($state['kind'] ?? null) {
            'document' => ($document = $campaign->availableDocuments()->find($state['id'] ?? 0))
                ? ['kind' => 'document', 'document' => $document, 'key' => $key]
                : null,
            'entity' => ($entity = $campaign->availableEntities()->find($state['id'] ?? 0))
                ? ['kind' => 'entity', 'entity' => $entity, 'fields' => self::publicFields($campaign, $entity), 'key' => $key]
                : null,
            'text' => filled($state['text'] ?? null) ? ['kind' => 'text', 'text' => $state['text'], 'key' => $key] : null,
            default => null,
        };
    }

    /** Libellé court pour le mode Session. */
    public static function label(Campaign $campaign): string
    {
        $current = self::current($campaign);

        return match ($current['kind'] ?? null) {
            'document' => $current['document']->title,
            'entity' => $current['entity']->name,
            'text' => '« '.mb_strimwidth($current['text'], 0, 60, '…').' »',
            default => __('Écran vide'),
        };
    }

    /** @return Collection<int, array{label: string, value: string}> */
    private static function publicFields(Campaign $campaign, Entity $entity)
    {
        return $campaign->gameSystem->fieldDefinitions()
            ->forType($entity->entity_type_id)
            ->where('zone', Zone::Public)
            ->ordered()
            ->get()
            ->filter(fn ($definition) => $entity->fieldValueIn($definition, $campaign) !== null)
            ->map(fn ($definition) => ['label' => $definition->name, 'value' => (string) $definition->type->format($entity->fieldValueIn($definition, $campaign))])
            ->values();
    }

    private static function broadcast(Campaign $campaign): void
    {
        // Tous les membres : le MJ sur la télé, les joueurs qui suivent sur leur appareil.
        $campaign->members()->pluck('users.id')
            ->push($campaign->user_id)
            ->unique()
            ->each(fn (int $id) => Live::user($id, 'table', $campaign->id));
    }

    /** @param array<string, mixed>|null $state */
    private static function save(Campaign $campaign, ?array $state): void
    {
        $campaign->forceFill(['table_display' => $state === null ? null : $state + ['at' => now()->getTimestampMs()]])->save();

        self::broadcast($campaign);
    }
}
