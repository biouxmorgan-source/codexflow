<?php

namespace App\Livewire\Account;

use App\Notifications\ConfirmNewEmail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;

/**
 * « Mon compte » dans Préférences : nom, adresse e-mail et mot de passe.
 * Changer l'adresse ou le mot de passe redemande le mot de passe actuel. Une nouvelle adresse
 * n'est adoptée qu'après le lien de confirmation qu'elle reçoit (ConfirmEmailChangeController),
 * et un nouveau mot de passe déconnecte les autres appareils (AuthenticateSession).
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

        $user->forceFill(['name' => trim($this->name)])->save();

        if ($changingEmail) {
            $url = URL::temporarySignedRoute('account.email.confirm', now()->addMinutes(60), ['user' => $user, 'email' => $this->email]);
            Notification::route('mail', $this->email)->notify(new ConfirmNewEmail($url));
            session()->now('profile-status', __('Un lien de confirmation a été envoyé à :email. Votre adresse changera quand vous l’ouvrirez.', ['email' => $this->email]));
            $this->email = $user->email;
        } else {
            session()->now('profile-status', __('Compte enregistré.'));
        }

        $this->reset('currentPassword');
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
