<?php

namespace App\Enums;

/**
 * Cycle expérimental d'une règle.
 */
enum RuleStatus: string
{
    case Available = 'available';
    case ToTest = 'to_test';
    case Testing = 'testing';
    case Adopted = 'adopted';
    case ToChange = 'to_change';
    case Rejected = 'rejected';

    /** Libellé dans la langue courante, ou dans $locale (« fr » pour l'export et l'import). */
    public function label(?string $locale = null): string
    {
        return match ($this) {
            self::Available => __('Disponible', [], $locale),
            self::ToTest => __('À tester', [], $locale),
            self::Testing => __('En test', [], $locale),
            self::Adopted => __('Adoptée', [], $locale),
            self::ToChange => __('À modifier', [], $locale),
            self::Rejected => __('Rejetée', [], $locale),
        };
    }

    /** Classes Tailwind de la pastille de statut. */
    public function badge(): string
    {
        return match ($this) {
            self::Available => 'bg-stone-100 text-stone-600',
            self::ToTest => 'bg-amber-50 text-amber-700',
            self::Testing => 'bg-flow/15 text-flow',
            self::Adopted => 'bg-emerald-50 text-emerald-700',
            self::ToChange => 'bg-codex/10 text-codex',
            self::Rejected => 'bg-stone-100 text-stone-400 line-through',
        };
    }
}
