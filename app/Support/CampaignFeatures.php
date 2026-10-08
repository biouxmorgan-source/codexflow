<?php

namespace App\Support;

use App\Models\Campaign;
use App\Support\Plans\Plans;

/**
 * Fonctions que le MJ peut couper dans une campagne, pour alléger l'interface d'une table.
 * Couper une fonction la masque et la refuse côté serveur, pour le MJ comme pour les joueurs ;
 * rien n'est effacé, tout revient quand on la réactive. Par défaut, tout est disponible.
 */
class CampaignFeatures
{
    public const SWITCHABLE = ['table', 'maps', 'exchanges', 'graph', 'timeline', 'ai'];

    /** Message d'une page refusée parce que le MJ a coupé la fonction (traduit par la page d'erreur). */
    public const DISABLED = 'Cette fonction est désactivée dans cette campagne.';

    /** @return array<string, array{label: string, hint: string}> */
    public static function all(): array
    {
        return [
            'table' => ['label' => __('Écran de table et télécommande'), 'hint' => __('Le second écran montré aux joueurs, « Afficher à la table » et la télécommande.')],
            'maps' => ['label' => __('Cartes et jetons'), 'hint' => __('Cartes avec grille, jetons et règle.')],
            'exchanges' => ['label' => __('Échanges entre joueurs'), 'hint' => __('Les joueurs se donnent objets et connaissances.')],
            'graph' => ['label' => __('Graphe des relations'), 'hint' => __('La carte des liens entre les fiches.')],
            'timeline' => ['label' => __('Chronologie'), 'hint' => __('Les événements du monde, prévus et joués.')],
            'ai' => ['label' => __('Assistant IA'), 'hint' => __('Résumés de séance et suggestions de mise à jour des fiches.')],
        ];
    }

    /** Le MJ a-t-il laissé cette fonction active ? (Une fonction hors liste l'est toujours.) */
    public static function enabled(Campaign $campaign, string $feature): bool
    {
        return ! in_array($feature, $campaign->disabled_features ?? [], true);
    }

    /** Active, et comprise dans la formule du propriétaire. */
    public static function usable(Campaign $campaign, string $feature): bool
    {
        return self::enabled($campaign, $feature) && Plans::allows($campaign->owner, $feature);
    }

    /** Refuse une action dont la fonction est coupée ou hors de la formule. */
    public static function ensure(Campaign $campaign, string $feature): void
    {
        abort_unless(self::enabled($campaign, $feature), 403, self::DISABLED);
        Plans::ensure($campaign->owner, $feature);
    }

    public static function toggle(Campaign $campaign, string $feature): void
    {
        abort_unless(in_array($feature, self::SWITCHABLE, true), 422);

        $disabled = collect($campaign->disabled_features ?? []);
        $disabled = $disabled->contains($feature) ? $disabled->reject(fn ($item) => $item === $feature) : $disabled->push($feature);

        $campaign->forceFill(['disabled_features' => $disabled->unique()->values()->all()])->save();
    }

    /**
     * Liste propre (import d'archive, duplication) : seulement des fonctions connues.
     *
     * @return list<string>
     */
    public static function clean(mixed $features): array
    {
        return is_array($features) ? array_values(array_intersect(self::SWITCHABLE, $features)) : [];
    }
}
