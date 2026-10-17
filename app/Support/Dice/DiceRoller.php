<?php

namespace App\Support\Dice;

use Random\Randomizer;

/**
 * Lance les dés côté serveur (personne ne choisit son résultat) et les lit selon la formule.
 *
 * Résultat enregistré tel quel avec le lancer :
 * - dice : un groupe par dé lancé, avec ses relances ({rolls: [{v, sub}], value, kept, hits}) ;
 * - total, successes, success, margin, digits, special selon la lecture.
 */
final class DiceRoller
{
    /** Relances au plus par dé, pour qu'un dé à deux faces ne relance pas sans fin. */
    public const MAX_CHAIN = 10;

    public function __construct(private ?Randomizer $randomizer = null)
    {
        $this->randomizer ??= new Randomizer;
    }

    /** @return array<string, mixed> */
    public function roll(DiceFormula $formula): array
    {
        $groups = [];
        foreach ($formula->pool() as $kind) {
            for ($i = 0; $i < $kind['count']; $i++) {
                $groups[] = $this->rollDie($formula, $kind['sides']);
            }
        }

        return self::read($formula, $groups);
    }

    /** @return array{sides: int, rolls: list<array{v: int, sub: bool}>, value: int, kept: bool, hits: int|null} */
    private function rollDie(DiceFormula $formula, int $sides): array
    {
        $first = $this->face($sides);
        $rolls = [['v' => $first, 'sub' => false]];

        $up = in_array($formula->explode, ['up', 'both'], true);
        $down = in_array($formula->explode, ['down', 'both'], true);

        if ($up && $first === $sides) {
            // La face haute relance et s'ajoute, tant qu'elle retombe sur la face haute.
            do {
                $next = $this->face($sides);
                $rolls[] = ['v' => $next, 'sub' => false];
            } while ($next === $sides && count($rolls) <= self::MAX_CHAIN);
        } elseif ($down && $first === 1) {
            // Le 1 relance et se retranche ; une face haute à la relance retranche encore.
            do {
                $next = $this->face($sides);
                $rolls[] = ['v' => $next, 'sub' => true];
            } while ($next === $sides && count($rolls) <= self::MAX_CHAIN);
        }

        $value = array_sum(array_map(fn (array $roll) => $roll['sub'] ? -$roll['v'] : $roll['v'], $rolls));

        return ['sides' => $sides, 'rolls' => $rolls, 'value' => $value, 'kept' => true, 'hits' => null];
    }

    private function face(int $sides): int
    {
        return $this->randomizer->getInt(1, $sides);
    }

    /**
     * Lecture des dés lancés (séparée du hasard, pour les tests et pour relire un lancer).
     *
     * @param  list<array{rolls: list<array{v: int, sub: bool}>, value: int, kept: bool, hits: int|null}>  $groups
     * @return array<string, mixed>
     */
    public static function read(DiceFormula $formula, array $groups): array
    {
        $result = [
            'dice' => $groups,
            'total' => null,
            'successes' => null,
            'success' => null,
            'margin' => null,
            'threshold' => null,
            'digits' => null,
            'special' => null,
        ];

        switch ($formula->reading) {
            case 'keep':
                $order = array_keys($groups);
                $direction = $formula->keepHigh ? -1 : 1;
                usort($order, fn (int $a, int $b) => ($direction * ($groups[$a]['value'] <=> $groups[$b]['value'])) ?: $a <=> $b);
                $kept = array_slice($order, 0, $formula->keep);
                foreach ($groups as $index => $group) {
                    $groups[$index]['kept'] = in_array($index, $kept, true);
                }
                $result['dice'] = $groups;
                $result['total'] = array_sum(array_map(fn (int $index) => $groups[$index]['value'], $kept)) + $formula->modifier;
                break;

            case 'count':
                $successes = 0;
                foreach ($groups as $index => $group) {
                    // Chaque relance compte comme un dé de plus.
                    $hits = count(array_filter($group['rolls'], fn (array $roll) => $formula->countHigh
                        ? $roll['v'] >= $formula->threshold
                        : $roll['v'] <= $formula->threshold));
                    $groups[$index]['hits'] = $hits;
                    $successes += $hits;
                }
                $result['dice'] = $groups;
                $result['successes'] = $successes;
                $result['threshold'] = $formula->threshold;
                break;

            case 'digits':
                $faces = array_map(fn (array $group) => $group['rolls'][0]['v'], $groups);
                // Un dé à 10 faces donne 0 à 9.
                $result['digits'] = implode('', array_map(fn (array $group) => ($group['sides'] ?? $formula->sides) === 10 ? $group['rolls'][0]['v'] % 10 : $group['rolls'][0]['v'], $groups));
                if (count($faces) >= 2 && count(array_unique($faces)) === 1) {
                    $result['special'] = count($faces) === 2 ? 'double' : 'triple';
                }
                break;

            default:
                $sum = array_sum(array_column($groups, 'value'));

                if ($formula->reading === 'below') {
                    // Rester sous le seuil : le bonus élargit le seuil, il ne touche pas les dés.
                    $threshold = $formula->threshold + $formula->modifier;
                    $result['total'] = $sum;
                    $result['threshold'] = $threshold;
                    $result['success'] = $sum <= $threshold;
                    $result['margin'] = $threshold - $sum;
                } else {
                    $result['total'] = $sum + $formula->modifier;

                    if ($formula->reading === 'above') {
                        $result['threshold'] = $formula->threshold;
                        $result['success'] = $result['total'] >= $formula->threshold;
                        $result['margin'] = $result['total'] - $formula->threshold;
                    }
                }
        }

        return $result;
    }

    /**
     * Ce que montre l'écran ou l'historique, en un mot ou un nombre : « 17 », « 3 réussites »,
     * « Réussite de 12 », « 351 · Triple ». Sans le seuil caché pour qui ne doit pas le voir.
     */
    public static function headline(array $result, bool $showThreshold = true): string
    {
        if ($result['digits'] !== null) {
            return $result['digits'].match ($result['special']) {
                'double' => ' · '.__('Double'),
                'triple' => ' · '.__('Triple'),
                default => '',
            };
        }

        if ($result['successes'] !== null) {
            return trans_choice(':count réussite|:count réussites', $result['successes']);
        }

        if ($result['success'] !== null && $showThreshold) {
            return $result['success']
                ? __('Réussite de :margin', ['margin' => abs($result['margin'])])
                : __('Échec de :margin', ['margin' => abs($result['margin'])]);
        }

        return (string) $result['total'];
    }
}
