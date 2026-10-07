<?php

namespace App\Livewire\Account;

use App\Support\Appearance;
use App\Support\Locale;
use Illuminate\Validation\Rule;
use Livewire\Component;

/**
 * Préférences du compte : langue, thème, couleur d'accent, taille du texte.
 */
class Preferences extends Component
{
    public string $theme = '';

    public string $accent = '';

    public string $size = '';

    /** Langue choisie, ou « » pour suivre le navigateur. */
    public string $locale = '';

    public function mount(): void
    {
        $this->locale = (string) (auth()->user()->preferences['locale'] ?? '');

        foreach (array_keys(Appearance::DEFAULTS) as $key) {
            $this->{$key} = auth()->user()->preference($key);
        }
    }

    public function save()
    {
        $this->validate(collect(Appearance::CHOICES)->map(fn (array $choices) => ['required', Rule::in(array_keys($choices))])->all()
            + ['locale' => ['nullable', Rule::in(array_keys(Locale::available()))]]);

        $user = auth()->user();
        $preferences = ['theme' => $this->theme, 'accent' => $this->accent, 'size' => $this->size, 'locale' => $this->locale ?: null] + ($user->preferences ?? []);
        $user->forceFill(['preferences' => array_filter($preferences, fn ($value) => $value !== null)])->save();
        // Le message de confirmation s'affiche déjà dans la nouvelle langue.
        app()->setLocale($this->locale ?: Locale::fromBrowser(request()));

        session()->flash('status', __('Préférences enregistrées.'));

        // Rechargement complet : le thème s'applique à toute la page.
        return redirect()->route('preferences');
    }

    public function render()
    {
        return view('livewire.account.preferences', ['choices' => Appearance::labels(), 'locales' => Locale::available()])->title(__('Préférences'));
    }
}
