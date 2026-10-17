<?php

namespace Tests\Feature\Dice;

use App\Support\Dice\DiceFormula;
use App\Support\Dice\DiceRoller;
use Random\Engine\Mt19937;
use Random\Randomizer;
use Tests\TestCase;

/**
 * Lecture des dés : additionner, viser un seuil (au-dessus ou en dessous), compter les réussites,
 * garder les meilleurs, lire en chiffres, et les dés qui relancent sur le max ou sur le 1.
 */
class DiceRollerTest extends TestCase
{
    /** @param list<int|list<int>> $faces un dé, ou un dé et ses relances ; une relance négative se retranche */
    private function faces(array $faces): array
    {
        return array_map(function ($face) {
            $rolls = array_map(fn (int $v) => ['v' => abs($v), 'sub' => $v < 0], (array) $face);
            $value = array_sum(array_map(fn ($roll) => $roll['sub'] ? -$roll['v'] : $roll['v'], $rolls));

            return ['rolls' => $rolls, 'value' => $value, 'kept' => true, 'hits' => null];
        }, $faces);
    }

    public function test_sum_adds_the_dice_and_the_bonus(): void
    {
        $result = DiceRoller::read(DiceFormula::fromArray(['count' => 2, 'sides' => 6, 'modifier' => 3]), $this->faces([4, 5]));

        $this->assertSame(12, $result['total']);
        $this->assertSame('12', DiceRoller::headline($result));
    }

    public function test_above_a_threshold(): void
    {
        $formula = DiceFormula::fromArray(['count' => 1, 'sides' => 20, 'reading' => 'above', 'threshold' => 15, 'modifier' => 2]);

        $hit = DiceRoller::read($formula, $this->faces([13]));
        $this->assertTrue($hit['success']);
        $this->assertSame(0, $hit['margin']);

        $miss = DiceRoller::read($formula, $this->faces([4]));
        $this->assertFalse($miss['success']);
        $this->assertSame('Échec de 9', DiceRoller::headline($miss));
    }

    public function test_below_a_threshold_the_bonus_widens_the_threshold(): void
    {
        $formula = DiceFormula::fromArray(['count' => 1, 'sides' => 100, 'reading' => 'below', 'threshold' => 45, 'modifier' => 20]);
        $result = DiceRoller::read($formula, $this->faces([60]));

        $this->assertSame(60, $result['total']);
        $this->assertSame(65, $result['threshold']);
        $this->assertTrue($result['success']);
        $this->assertSame('Réussite de 5', DiceRoller::headline($result));
        $this->assertSame('60', DiceRoller::headline($result, showThreshold: false));
    }

    public function test_count_successes_each_reroll_counts(): void
    {
        $formula = DiceFormula::fromArray(['count' => 4, 'sides' => 6, 'reading' => 'count', 'threshold' => 5, 'explode' => 'both']);
        $this->assertSame('up', $formula->explode, 'Le 1 qui retranche n’a pas de sens en comptant les réussites.');

        $result = DiceRoller::read($formula, $this->faces([[6, 5], 2, 5, 1]));
        $this->assertSame(3, $result['successes']);
        $this->assertSame(2, $result['dice'][0]['hits']);

        $low = DiceRoller::read(DiceFormula::fromArray(['count' => 3, 'sides' => 10, 'reading' => 'count', 'threshold' => 3, 'count_high' => false]), $this->faces([1, 3, 9]));
        $this->assertSame(2, $low['successes']);
    }

    public function test_keep_the_best_or_the_worst(): void
    {
        $best = DiceRoller::read(DiceFormula::fromArray(['count' => 2, 'sides' => 20, 'reading' => 'keep', 'keep' => 1]), $this->faces([7, 18]));
        $this->assertSame(18, $best['total']);
        $this->assertFalse($best['dice'][0]['kept']);
        $this->assertTrue($best['dice'][1]['kept']);

        $worst = DiceRoller::read(DiceFormula::fromArray(['count' => 4, 'sides' => 6, 'reading' => 'keep', 'keep' => 2, 'keep_high' => false]), $this->faces([3, 1, 6, 3]));
        $this->assertSame(4, $worst['total']);
    }

    public function test_digits_and_triples(): void
    {
        $result = DiceRoller::read(DiceFormula::fromArray(['count' => 3, 'sides' => 6, 'reading' => 'digits']), $this->faces([3, 5, 1]));
        $this->assertSame('351', $result['digits']);
        $this->assertNull($result['special']);

        $triple = DiceRoller::read(DiceFormula::fromArray(['count' => 3, 'sides' => 6, 'reading' => 'digits']), $this->faces([6, 6, 6]));
        $this->assertSame('666 · Triple', DiceRoller::headline($triple));

        $tens = DiceRoller::read(DiceFormula::fromArray(['count' => 2, 'sides' => 10, 'reading' => 'digits']), $this->faces([10, 4]));
        $this->assertSame('04', $tens['digits']);
    }

    public function test_exploding_dice_add_on_the_max_and_subtract_on_a_one(): void
    {
        $result = DiceRoller::read(DiceFormula::fromArray(['count' => 2, 'sides' => 100, 'explode' => 'both']), $this->faces([[100, 37], [1, -42]]));

        $this->assertSame(137 - 41, $result['total']);
    }

    public function test_real_rolls_stay_within_the_faces_and_chains_are_bounded(): void
    {
        $roller = new DiceRoller(new Randomizer(new Mt19937(42)));

        for ($i = 0; $i < 200; $i++) {
            $result = $roller->roll(DiceFormula::fromArray(['count' => 5, 'sides' => 2, 'explode' => 'both']));

            foreach ($result['dice'] as $group) {
                $this->assertLessThanOrEqual(DiceRoller::MAX_CHAIN + 1, count($group['rolls']));
                foreach ($group['rolls'] as $roll) {
                    $this->assertContains($roll['v'], [1, 2]);
                }
            }
        }
    }

    public function test_formula_from_the_browser_is_bounded(): void
    {
        $formula = DiceFormula::fromArray(['count' => 500, 'sides' => 1, 'reading' => 'nope', 'modifier' => 'x', 'explode' => 'boom']);

        $this->assertSame(DiceFormula::MAX_COUNT, $formula->count);
        $this->assertSame(2, $formula->sides);
        $this->assertSame('sum', $formula->reading);
        $this->assertSame(0, $formula->modifier);
        $this->assertSame('none', $formula->explode);
        $this->assertSame('30d2, additionnés', $formula->describe());
    }
}
