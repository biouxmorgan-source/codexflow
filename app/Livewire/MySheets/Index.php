<?php

namespace App\Livewire\MySheets;

use App\Models\PersonalCharacter;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Mes feuilles de personnage : les personnages que je joue hors de SagaWyn (chez un ami, en
 * club, en ligne ailleurs), rangés dans mon compte et visibles de moi seul.
 */
class Index extends Component
{
    #[Url]
    public string $game = '';

    public string $name = '';

    public string $newGame = '';

    /** @return Collection<int, PersonalCharacter> */
    #[Computed]
    public function characters(): Collection
    {
        return auth()->user()->personalCharacters()
            ->when($this->game !== '', fn ($query) => $query->whereRaw('lower(game) = ?', [mb_strtolower($this->game)]))
            ->orderByRaw('lower(name)')
            ->get();
    }

    /** @return list<string> jeux déjà nommés, pour filtrer et proposer */
    #[Computed]
    public function games(): array
    {
        return auth()->user()->personalCharacters()
            ->whereNotNull('game')
            ->orderByRaw('lower(game)')
            ->pluck('game')
            ->unique(fn (string $game) => mb_strtolower($game))
            ->values()
            ->all();
    }

    public function create(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'newGame' => ['nullable', 'string', 'max:255'],
        ], attributes: ['name' => __('nom'), 'newGame' => __('jeu')]);

        abort_if(auth()->user()->personalCharacters()->count() >= 500, 422);

        $character = auth()->user()->personalCharacters()->create([
            'name' => trim($this->name),
            'game' => trim($this->newGame) ?: null,
        ]);

        $this->redirectRoute('my-sheets.show', $character, navigate: true);
    }

    public function render()
    {
        return view('livewire.my-sheets.index')->title(__('Mes feuilles de personnage'));
    }
}
