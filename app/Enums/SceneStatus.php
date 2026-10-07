<?php

namespace App\Enums;

enum SceneStatus: string
{
    case Planned = 'planned';
    case Available = 'available';
    case InProgress = 'in_progress';
    case Played = 'played';
    case Skipped = 'skipped';

    /** Libellé dans la langue courante, ou dans $locale (« fr » pour l'export et l'import). */
    public function label(?string $locale = null): string
    {
        return match ($this) {
            self::Planned => __('Prévue', [], $locale),
            self::Available => __('Disponible', [], $locale),
            self::InProgress => __('En cours', [], $locale),
            self::Played => __('Jouée', [], $locale),
            self::Skipped => __('Ignorée', [], $locale),
        };
    }

    /** Classes Tailwind de la pastille de statut. */
    public function badge(): string
    {
        return match ($this) {
            self::Planned => 'bg-stone-100 text-stone-600',
            self::Available => 'bg-codex/10 text-codex',
            self::InProgress => 'bg-flow/15 text-flow',
            self::Played => 'bg-emerald-50 text-emerald-700',
            self::Skipped => 'bg-stone-100 text-stone-400 line-through',
        };
    }
}
