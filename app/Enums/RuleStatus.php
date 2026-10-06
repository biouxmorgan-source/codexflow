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

    public function label(): string
    {
        return match ($this) {
            self::Available => 'Disponible',
            self::ToTest => 'À tester',
            self::Testing => 'En test',
            self::Adopted => 'Adoptée',
            self::ToChange => 'À modifier',
            self::Rejected => 'Rejetée',
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
