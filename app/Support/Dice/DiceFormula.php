<?php

namespace App\Support\Dice;

use Illuminate\Validation\ValidationException;

/**
 * Ce qu'on lance et comment on le lit, sans nommer aucun jeu : des dés d'un ou plusieurs types,
 * une lecture (brut, additionner, viser un seuil, compter les réussites, garder les meilleurs, lire les
 * chiffres), un bonus et, au besoin, des dés qui relancent sur leur valeur haute ou sur le 1.
 *
 * Construite depuis un tableau venu du navigateur : chaque valeur est vérifiée et bornée ici.
 */
final class DiceFormula
{
    public const READINGS = ['raw', 'sum', 'above', 'below', 'count', 'keep', 'digits'];

    public const EXPLODES = ['none', 'up', 'down', 'both'];

    public const COMMON_SIDES = [4, 6, 8, 10, 12, 20, 100];

    public const MAX_COUNT = 30;

    public const MAX_SIDES = 1000;

    /** Types de dés différents dans un même lancer (1d20 + 1d6 + 2d4…). */
    public const MAX_KINDS = 6;

    /**
     * @param  list<array{count: int, sides: int}>  $pool  les dés lancés, par type ; count et sides : nombre total et premier type
     */
    public function __construct(
        public readonly int $count,
        public readonly int $sides,
        public readonly string $reading = 'sum',
        /** Seuil à atteindre (above), à ne pas dépasser (below), ou d'un dé réussi (count) ; null : le lanceur le saisit. */
        public readonly ?int $threshold = null,
        /** Compter les réussites : un dé réussit à partir du seuil (true) ou en dessous (false). */
        public readonly bool $countHigh = true,
        /** Garder : combien de dés, et les meilleurs (true) ou les plus faibles (false). */
        public readonly int $keep = 1,
        public readonly bool $keepHigh = true,
        public readonly string $explode = 'none',
        /** Bonus en faveur du lanceur : ajouté au total, ou au seuil quand il faut rester dessous. */
        public readonly int $modifier = 0,
        /** Le seuil reste chez le MJ : les autres voient les dés et le total, pas la réussite. */
        public readonly bool $hideThreshold = false,
        public readonly array $pool = [],
    ) {}

    /** @return list<array{count: int, sides: int}> */
    public function pool(): array
    {
        return $this->pool !== [] ? $this->pool : [['count' => $this->count, 'sides' => $this->sides]];
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $int = fn (string $key, int $min, int $max, int $default) => is_numeric($data[$key] ?? null)
            ? max($min, min($max, (int) $data[$key]))
            : $default;

        $reading = in_array($data['reading'] ?? null, self::READINGS, true) ? $data['reading'] : 'sum';
        // Plusieurs types de dés (pool), ou un seul (count + sides, comme les lancers enregistrés avant).
        $kinds = is_array($data['pool'] ?? null) && $data['pool'] !== [] ? $data['pool'] : [['count' => $data['count'] ?? 1, 'sides' => $data['sides'] ?? 6]];
        $pool = [];
        $left = self::MAX_COUNT;
        foreach ($kinds as $kind) {
            if (! is_array($kind) || $left <= 0) {
                continue;
            }
            $kindSides = is_numeric($kind['sides'] ?? null) ? max(2, min(self::MAX_SIDES, (int) $kind['sides'])) : 6;
            $kindCount = is_numeric($kind['count'] ?? null) ? max(1, min($left, (int) $kind['count'])) : 1;
            $same = array_search($kindSides, array_column($pool, 'sides'), true);
            if ($same !== false) {
                $pool[$same]['count'] += $kindCount;
            } elseif (count($pool) < self::MAX_KINDS) {
                $pool[] = ['count' => $kindCount, 'sides' => $kindSides];
            } else {
                continue;
            }
            $left -= $kindCount;
        }
        $pool = $pool ?: [['count' => 1, 'sides' => 6]];
        $sides = $pool[0]['sides'];
        $count = array_sum(array_column($pool, 'count'));
        $explode = in_array($data['explode'] ?? null, self::EXPLODES, true) ? $data['explode'] : 'none';
        $threshold = is_numeric($data['threshold'] ?? null) ? max(-9999, min(9999, (int) $data['threshold'])) : null;

        // Ce qui n'a pas de sens pour une lecture est remis à zéro : la formule enregistrée reste lisible.
        $explode = match ($reading) {
            'raw', 'digits' => 'none',
            'count' => in_array($explode, ['up', 'both'], true) ? 'up' : 'none',
            default => $explode,
        };

        return new self(
            count: $count,
            sides: $sides,
            reading: $reading,
            threshold: in_array($reading, ['above', 'below', 'count'], true) ? $threshold : null,
            countHigh: $reading === 'count' ? (bool) ($data['count_high'] ?? true) : true,
            keep: $reading === 'keep' ? max(1, min($count, $int('keep', 1, self::MAX_COUNT, 1))) : 1,
            keepHigh: $reading === 'keep' ? (bool) ($data['keep_high'] ?? true) : true,
            explode: $explode,
            modifier: in_array($reading, ['sum', 'above', 'below', 'keep'], true) ? $int('modifier', -999, 999, 0) : 0,
            hideThreshold: in_array($reading, ['above', 'below'], true) && (bool) ($data['hide_threshold'] ?? false),
            pool: $pool,
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'count' => $this->count,
            'sides' => $this->sides,
            'pool' => $this->pool(),
            'reading' => $this->reading,
            'threshold' => $this->threshold,
            'count_high' => $this->countHigh,
            'keep' => $this->keep,
            'keep_high' => $this->keepHigh,
            'explode' => $this->explode,
            'modifier' => $this->modifier,
            'hide_threshold' => $this->hideThreshold,
        ];
    }

    public function needsThreshold(): bool
    {
        return in_array($this->reading, ['above', 'below', 'count'], true);
    }

    /** Le même lancer avec le seuil et le bonus du lanceur (réponse à une demande). */
    public function completedWith(?int $threshold, int $bonus): self
    {
        $data = $this->toArray();
        $data['threshold'] = $this->threshold ?? $threshold;
        $data['modifier'] = $this->modifier + $bonus;

        return self::fromArray($data);
    }

    /** Refuse un lancer qui demande un seuil sans en avoir. */
    public function ensureComplete(string $field = 'threshold'): void
    {
        if ($this->needsThreshold() && $this->threshold === null) {
            throw ValidationException::withMessages([$field => __('Indiquez le seuil.')]);
        }
    }

    /** « 3d6 », « 1d20 + 1d6 » : notation courte, la même dans toutes les langues. */
    public function dice(): string
    {
        return implode(' + ', array_map(fn (array $kind) => $kind['count'].'d'.$kind['sides'], $this->pool()));
    }

    /** Description en mots simples : « 2d6 additionnés, +1 », « 6d6, réussite sur 5 ou plus, le 6 relance ». */
    public function describe(bool $withThreshold = true): string
    {
        $threshold = $withThreshold && $this->threshold !== null ? $this->threshold : '?';
        $parts = [$this->dice()];

        $parts[] = match ($this->reading) {
            'raw' => __('bruts'),
            'sum' => __('additionnés'),
            'above' => __('total à :threshold ou plus', ['threshold' => $threshold]),
            'below' => __('total à :threshold ou moins', ['threshold' => $threshold]),
            'count' => $this->countHigh
                ? __('un dé réussit sur :threshold ou plus', ['threshold' => $threshold])
                : __('un dé réussit sur :threshold ou moins', ['threshold' => $threshold]),
            'keep' => $this->keepHigh
                ? trans_choice('garder le meilleur|garder les :count meilleurs', $this->keep)
                : trans_choice('garder le plus faible|garder les :count plus faibles', $this->keep),
            'digits' => __('lus en chiffres'),
        };

        if ($this->modifier !== 0) {
            $signed = ($this->modifier > 0 ? '+' : '').$this->modifier;
            $parts[] = $this->reading === 'below' ? __('bonus :bonus au seuil', ['bonus' => $signed]) : $signed;
        }

        if (in_array($this->explode, ['up', 'both'], true)) {
            $parts[] = count($this->pool()) > 1 ? __('le max relance et s’ajoute') : __('le :max relance et s’ajoute', ['max' => $this->sides]);
        }
        if (in_array($this->explode, ['down', 'both'], true)) {
            $parts[] = __('le 1 relance et se retranche');
        }

        return implode(', ', $parts);
    }
}
