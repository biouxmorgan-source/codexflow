<?php

namespace App\Livewire\Account;

use App\Support\Appearance;
use Illuminate\Validation\Rule;
use Livewire\Component;

/**
 * Préférences d'affichage du compte : thème, couleur d'accent, taille du texte.
 */
class Preferences extends Component
{
    public string $theme = '';

    public string $accent = '';

    public string $size = '';

    public function mount(): void
    {
        foreach (array_keys(Appearance::DEFAULTS) as $key) {
            $this->{$key} = auth()->user()->preference($key);
        }
    }

    public function save()
    {
        $this->validate(collect(Appearance::CHOICES)->map(fn (array $choices) => ['required', Rule::in(array_keys($choices))])->all());

        $user = auth()->user();
        $user->forceFill(['preferences' => ['theme' => $this->theme, 'accent' => $this->accent, 'size' => $this->size] + ($user->preferences ?? [])])->save();

        session()->flash('status', 'Préférences enregistrées.');

        // Rechargement complet : le thème s'applique à toute la page.
        return redirect()->route('preferences');
    }

    public function render()
    {
        return view('livewire.account.preferences', ['choices' => Appearance::CHOICES])->title('Préférences');
    }
}
