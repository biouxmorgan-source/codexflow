<?php

namespace App\Support;

use App\Enums\CampaignRole;
use App\Enums\Zone;
use App\Models\Attachment;
use App\Models\Campaign;
use App\Models\Document;
use App\Models\Entity;
use App\Models\TableMap;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Écran de table : ce que le MJ montre aux joueurs sur un second écran (carte, image,
 * portrait, zone publique d'une fiche, règle, carte avec ses jetons visibles, annonce).
 * Jamais la zone MJ, jamais un jeton masqué.
 */
class TableDisplay
{
    /** Ce qu'on peut afficher depuis une page de contenu, avec « Afficher à la table ». */
    public const KINDS = ['document', 'entity', 'portrait', 'attachment', 'rule', 'map'];

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
            'map' => CampaignFeatures::enabled($campaign, 'maps') && $campaign->maps()->whereKey($id)->exists(),
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

    /**
     * Une carte affichée a changé (vue, grille, jetons, règle) : l'écran se redessine,
     * sans le fondu d'un nouvel affichage.
     */
    public static function mapChanged(TableMap $map): void
    {
        $campaign = $map->campaign;

        if (self::isShowing($campaign, 'map', $map->id)) {
            self::broadcast($campaign);
        }
    }

    /** Le document affiché est-il un PDF, qu'on feuillette page à page ? */
    public static function showsPdf(Campaign $campaign): bool
    {
        $current = self::current($campaign);

        return ($current['kind'] ?? null) === 'document' && $current['document']->isPdf();
    }

    /**
     * Tourne les pages du PDF affiché (télécommande, page du document, flèches de l'écran du MJ) :
     * les joueurs qui suivent l'écran passent à la même page. Le nombre de pages, connu une fois
     * le PDF ouvert sur l'écran du MJ, borne la navigation.
     */
    public static function turnTo(Campaign $campaign, int $page): void
    {
        abort_unless(self::showsPdf($campaign), 404);

        $state = $campaign->table_display;
        $last = (int) ($state['pages'] ?? 0);
        $page = max(1, $last > 0 ? min($last, $page) : $page);

        if ($page !== self::page($campaign)) {
            // Même affichage (même clé), autre page : pas de fondu, l'écran tourne la page.
            $campaign->forceFill(['table_display' => ['page' => $page] + $state])->save();
            self::broadcast($campaign);
        }
    }

    public static function turn(Campaign $campaign, int $delta): void
    {
        self::turnTo($campaign, self::page($campaign) + max(-1, min(1, $delta)));
    }

    /** Page affichée du PDF, 1 par défaut. */
    public static function page(Campaign $campaign): int
    {
        return max(1, (int) ($campaign->table_display['page'] ?? 1));
    }

    /** Nombre de pages du PDF affiché, 0 tant que l'écran ne l'a pas ouvert. */
    public static function pages(Campaign $campaign): int
    {
        return (int) ($campaign->table_display['pages'] ?? 0);
    }

    /** L'écran du MJ a ouvert le PDF : il en donne le nombre de pages. */
    public static function knowPages(Campaign $campaign, int $pages): void
    {
        if (self::showsPdf($campaign) && $pages > 0 && $pages !== self::pages($campaign)) {
            $campaign->forceFill(['table_display' => ['pages' => min($pages, 10000)] + $campaign->table_display])->save();
            self::broadcast($campaign);
        }
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
        CampaignFeatures::ensure($campaign, 'table');
        $campaign->forceFill(['table_shared' => $shared])->save();
        self::broadcast($campaign);
    }

    /** Habillage de l'écran : le changement est envoyé tout de suite aux écrans ouverts. */
    public static function theme(Campaign $campaign, string $theme): void
    {
        abort_unless(array_key_exists($theme, TableTheme::THEMES), 422);
        CampaignFeatures::ensure($campaign, 'table');

        $campaign->forceFill(['table_theme' => $theme])->save();
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
                ? ['kind' => 'document', 'document' => $document, 'key' => $key, 'page' => max(1, (int) ($state['page'] ?? 1))]
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
            'map' => CampaignFeatures::enabled($campaign, 'maps') && ($map = $campaign->maps()->with(['document', 'tokens' => fn ($q) => $q->where('hidden', false)->with('entity')])->find($state['id'] ?? 0))
                ? ['kind' => 'map', 'map' => $map, 'key' => $key]
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
            'map' => $current['map']->name,
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
        // Vider l'écran reste possible après la fin d'une formule ou une fonction coupée ; afficher, non.
        if ($state !== null) {
            CampaignFeatures::ensure($campaign, 'table');
        }

        $campaign->forceFill(['table_display' => $state === null ? null : $state + ['at' => now()->getTimestampMs()]])->save();

        self::broadcast($campaign);
    }
}
