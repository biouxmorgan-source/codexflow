<?php

namespace App\Livewire\Dice;

use App\Models\PersonalCharacter;
use App\Support\Dice\DiceFormula;
use App\Support\Dice\DiceRoller;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Dés d'une feuille personnelle (« Mes feuilles ») : hors de toute campagne, le lancer est tiré
 * par le serveur et n'est montré qu'à la personne, sur sa feuille. Rien n'est enregistré.
 */
class Pad extends Component
{
    public const KEPT = 10;

    #[Locked]
    public PersonalCharacter $character;

    public bool $open = false;

    /** @var list<array<string, mixed>> derniers lancers, du plus récent au plus ancien */
    #[Locked]
    public array $rolls = [];

    #[Locked]
    public int $next = 1;

    public function mount(PersonalCharacter $character): void
    {
        $this->authorize('view', $character);
        $this->character = $character;
    }

    public function toggle(): void
    {
        $this->open = ! $this->open;
    }

    /** Ouvert depuis une valeur de la feuille. */
    public function openFor(?int $characterId = null): void
    {
        $this->open = true;
    }

    /** @param array<string, mixed> $formula */
    public function roll(array $formula, string $label = ''): void
    {
        $this->authorize('view', $this->character);
        // Sans campagne, aucune valeur n'est relue : seuls les bonus nommés comptent.
        $formula = DiceFormula::fromArray($formula)->withSheet(fn () => null, [], false);
        $formula->ensureComplete();
        $result = app(DiceRoller::class)->roll($formula);

        array_unshift($this->rolls, [
            'id' => $this->next++,
            'label' => filled($label) ? mb_substr(trim($label), 0, 100) : null,
            'describe' => $formula->describe(),
            'sides' => $formula->sides,
            'reading' => $formula->reading,
            'count_high' => $formula->countHigh,
            'modifier' => $formula->modifier,
            'result' => $result,
            'headline' => DiceRoller::headline($result),
            'at' => now()->format('H:i'),
        ]);
        $this->rolls = array_slice($this->rolls, 0, self::KEPT);
    }

    public function clear(): void
    {
        $this->rolls = [];
    }

    public function render()
    {
        return view('livewire.dice.pad');
    }
}
