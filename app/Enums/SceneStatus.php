<?php

namespace App\Enums;

enum SceneStatus: string
{
    case Planned = 'planned';
    case Available = 'available';
    case InProgress = 'in_progress';
    case Played = 'played';
    case Skipped = 'skipped';

    public function label(): string
    {
        return match ($this) {
            self::Planned => __('Prévue'),
            self::Available => __('Disponible'),
            self::InProgress => __('En cours'),
            self::Played => __('Jouée'),
            self::Skipped => __('Ignorée'),
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
