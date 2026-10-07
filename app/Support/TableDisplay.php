<?php

namespace App\Support;

use App\Enums\CampaignRole;
use App\Enums\Zone;
use App\Models\Attachment;
use App\Models\Campaign;
use App\Models\Document;
use App\Models\Entity;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Écran de table : ce que le MJ montre aux joueurs sur un second écran (carte, image,
 * portrait, zone publique d'une fiche, règle, annonce). Jamais la zone MJ.
 */
class TableDisplay
{
    /** Ce qu'on peut afficher depuis une page de contenu, avec « Afficher à la table ». */
    public const KINDS = ['document', 'entity', 'portrait', 'attachment', 'rule'];

    /**
     * Affiche un élément de la campagne. Faux si l'élément n'y est pas utilisable ou ne s'affiche pas.
     */
    public static function show(Campaign $campaign, string $kind, int $id): bool
    {
        $displayable = match ($kind) {
            'document' => $campaign->availableDocuments()->whereKey($id)->exists(),
            'entity' => $campaign->availableEntities()->whereKey($id)->exists(),
            'portrait' => $campaign->availableEntities()->whereKey($id)->whereNotNull('image_path')->exists(),
            'attachment' => self::attachment($campaign, $id) !== null,
            'rule' => $campaign->availableRules()->whereKey($id)->exists(),
            default => false,
        };

        if ($displayable) {
            self::save($campaign, ['kind' => $kind, 'id' => $id]);
        }

        return $displayable;
    }

    /** L'affichage en cours est-il cet élément ? */
    public static function isShowing(Campaign $campaign, string $kind, int $id): bool
    {
        $state = $campaign->table_display;

        return ($state['kind'] ?? null) === $kind && (int) ($state['id'] ?? 0) === $id;
    }

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

    /** Le MJ et les spectateurs voient toujours l'écran ; un joueur seulement quand le MJ le partage. */
    public static function canWatch(User $user, Campaign $campaign): bool
    {
        return match ($campaign->roleOf($user)) {
            CampaignRole::GameMaster, CampaignRole::Spectator => true,
            CampaignRole::Player => (bool) $campaign->table_shared,
            null => false,
        };
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
            'portrait' => ($entity = $campaign->availableEntities()->whereNotNull('image_path')->find($state['id'] ?? 0))
                ? ['kind' => 'portrait', 'entity' => $entity, 'key' => $key]
                : null,
            'attachment' => ($attachment = self::attachment($campaign, (int) ($state['id'] ?? 0)))
                ? ['kind' => 'attachment', 'attachment' => $attachment, 'key' => $key]
                : null,
            'rule' => ($rule = $campaign->availableRules()->find($state['id'] ?? 0))
                ? ['kind' => 'rule', 'rule' => $rule, 'key' => $key]
                : null,
            'text' => filled($state['text'] ?? null) ? ['kind' => 'text', 'text' => $state['text'], 'key' => $key] : null,
            default => null,
        };
    }

    /** Une illustration jointe à une fiche de la campagne (les images seulement). */
    private static function attachment(Campaign $campaign, int $id): ?Attachment
    {
        return Attachment::query()
            ->whereKey($id)
            ->where('mime_type', 'like', 'image/%')
            ->whereIn('entity_id', $campaign->availableEntities()->select('entities.id'))
            ->first();
    }

    /** Libellé court pour le mode Session. */
    public static function label(Campaign $campaign): string
    {
        $current = self::current($campaign);

        return match ($current['kind'] ?? null) {
            'document' => $current['document']->title,
            'entity' => $current['entity']->name,
            'portrait' => __('Portrait de :name', ['name' => $current['entity']->name]),
            'attachment' => $current['attachment']->original_name,
            'rule' => $current['rule']->title,
            'text' => __('« :text »', ['text' => mb_strimwidth($current['text'], 0, 60, '…')]),
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
