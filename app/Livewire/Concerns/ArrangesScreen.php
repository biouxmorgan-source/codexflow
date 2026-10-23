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

    /** @return array{blocks: list<array{id: string, column: int, span: int}>, hidden: list<string>, custom: bool} */
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
     * Nouvelle disposition envoyée après un glisser-déposer, une flèche ou un changement de largeur :
     * les blocs dans l'ordre, chacun avec sa colonne de départ et sa largeur.
     *
     * @param  array<int, mixed>  $blocks
     */
    public function saveLayout(array $blocks): void
    {
        ScreenLayout::save(auth()->user(), $this->screenLayoutName(), $blocks, $this->screenLayout['hidden']);
        unset($this->screenLayout);
    }

    public function hideBlock(string $block): void
    {
        $layout = $this->screenLayout;
        ScreenLayout::save(auth()->user(), $this->screenLayoutName(), $layout['blocks'], [...$layout['hidden'], $block]);
        unset($this->screenLayout);
    }

    public function showBlock(string $block): void
    {
        $layout = $this->screenLayout;
        ScreenLayout::save(auth()->user(), $this->screenLayoutName(), $layout['blocks'], array_values(array_diff($layout['hidden'], [$block])));
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
