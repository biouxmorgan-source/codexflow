<?php

namespace App\Livewire\Concerns;

use App\Support\ScreenLayout;
use Livewire\Attributes\Computed;

/**
 * « Organiser l'écran » : l'utilisateur déplace, masque et réaffiche les blocs d'un écran, pour lui seul.
 * Le composant indique son écran dans screenLayoutName().
 */
trait ArrangesScreen
{
    /** Les poignées et les boutons de chaque bloc ne s'affichent qu'en mode « Organiser ». */
    public bool $arranging = false;

    abstract protected function screenLayoutName(): string;

    /** @return array{columns: array<string, list<string>>, hidden: list<string>, custom: bool} */
    #[Computed]
    public function screenLayout(): array
    {
        return ScreenLayout::for(auth()->user(), $this->screenLayoutName());
    }

    public function toggleArranging(): void
    {
        $this->arranging = ! $this->arranging;
    }

    /**
     * Nouvel ordre envoyé après un glisser-déposer ou une flèche.
     *
     * @param  array<string, mixed>  $columns
     */
    public function saveLayout(array $columns): void
    {
        ScreenLayout::save(auth()->user(), $this->screenLayoutName(), $columns, $this->screenLayout['hidden']);
        unset($this->screenLayout);
    }

    public function hideBlock(string $block): void
    {
        $layout = $this->screenLayout;
        ScreenLayout::save(auth()->user(), $this->screenLayoutName(), $layout['columns'], [...$layout['hidden'], $block]);
        unset($this->screenLayout);
    }

    public function showBlock(string $block): void
    {
        $layout = $this->screenLayout;
        ScreenLayout::save(auth()->user(), $this->screenLayoutName(), $layout['columns'], array_values(array_diff($layout['hidden'], [$block])));
        unset($this->screenLayout);
    }

    public function resetLayout(): void
    {
        ScreenLayout::reset(auth()->user(), $this->screenLayoutName());
        unset($this->screenLayout);
        // Les blocs changent de colonne, sous-composants compris : un rechargement repart au propre.
        $this->js('window.location.reload()');
    }
}
