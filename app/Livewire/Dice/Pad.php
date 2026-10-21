<?php

namespace App\Livewire\Dice;

use App\Models\PersonalCharacter;
use App\Support\Dice\DiceFormula;
use App\Support\Dice\DiceRoller;
use App\Support\Dice\TableDice;
use Illuminate\Support\Str;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Dés d'une feuille personnelle (« Mes feuilles ») : hors de toute campagne, le lancer est tiré
 * par le serveur et n'est montré qu'à la personne, sur sa feuille. Seuls les lancers qu'elle
 * enregistre sont gardés, avec la feuille.
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

    /** @return list<array{id: string, label: string, formula: array<string, mixed>}> */
    public function presets(): array
    {
        return TableDice::cleanPresets($this->character->dice_presets);
    }

    /**
     * Enregistre le lancer composé avec la feuille ; avec $id, met à jour ce lancer.
     *
     * @param  array<string, mixed>  $formula
     */
    public function savePreset(array $formula, string $label, ?string $id = null): ?string
    {
        $this->authorize('update', $this->character);

        if (trim($label) === '') {
            $this->addError('presetLabel', __('Donnez un nom au lancer.'));

            return null;
        }
        $this->resetErrorBag('presetLabel');

        $presets = $this->presets();
        $entry = ['label' => mb_substr(trim($label), 0, 60), 'formula' => DiceFormula::fromArray($formula)->toArray()];
        $index = $id === null ? false : array_search($id, array_column($presets, 'id'), true);

        if ($index !== false) {
            $presets[$index] = ['id' => $id] + $entry;
        } elseif (count($presets) >= TableDice::MAX_PRESETS) {
            $this->addError('presetLabel', __('Trente lancers enregistrés au plus.'));

            return null;
        } else {
            $presets[] = ['id' => $id = Str::random(8)] + $entry;
        }

        $this->character->forceFill(['dice_presets' => $presets])->save();

        return $id;
    }

    public function removePreset(string $id): void
    {
        $this->authorize('update', $this->character);
        $presets = array_values(array_filter($this->presets(), fn (array $preset) => $preset['id'] !== $id));
        $this->character->forceFill(['dice_presets' => $presets === [] ? null : $presets])->save();
    }

    public function clear(): void
    {
        $this->rolls = [];
    }

    public function render()
    {
        return view('livewire.dice.pad', ['presets' => $this->open ? $this->presets() : []]);
    }
}
