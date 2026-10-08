<?php

namespace App\Livewire\Account;

use App\Notifications\EmailChanged;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;

/**
 * « Mon compte » dans Préférences : nom, adresse e-mail et mot de passe.
 * Changer l'adresse ou le mot de passe redemande le mot de passe actuel ; l'ancienne adresse
 * est prévenue, et un nouveau mot de passe déconnecte les autres appareils (AuthenticateSession).
 */
class Profile extends Component
{
    public string $name = '';

    public string $email = '';

    public string $currentPassword = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(): void
    {
        $this->name = auth()->user()->name;
        $this->email = auth()->user()->email;
    }

    public function saveProfile(): void
    {
        $user = auth()->user();
        $this->email = mb_strtolower(trim($this->email));
        $changingEmail = $this->email !== $user->email;

        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'currentPassword' => $changingEmail ? ['required', 'current_password'] : ['nullable'],
        ], attributes: ['name' => __('nom'), 'email' => __('adresse e-mail'), 'currentPassword' => __('mot de passe actuel')]);

        $old = $user->email;
        $user->forceFill(['name' => trim($this->name), 'email' => $this->email])->save();

        if ($changingEmail) {
            Notification::route('mail', $old)->notify(new EmailChanged($this->email));
        }

        $this->reset('currentPassword');
        session()->now('profile-status', $changingEmail ? __('Compte enregistré. Votre ancienne adresse a été prévenue du changement.') : __('Compte enregistré.'));
    }

    public function savePassword(): void
    {
        $this->validate([
            'currentPassword' => ['required', 'current_password'],
            'password' => ['required', 'string', Password::defaults(), 'confirmed', 'different:currentPassword'],
        ], attributes: ['currentPassword' => __('mot de passe actuel'), 'password' => __('nouveau mot de passe')]);

        auth()->user()->forceFill(['password' => $this->password])->save();

        $this->reset('currentPassword', 'password', 'password_confirmation');
        session()->now('profile-status', __('Mot de passe changé. Vos autres appareils sont déconnectés.'));
    }

    public function render()
    {
        return view('livewire.account.profile');
    }
}
