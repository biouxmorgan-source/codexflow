<?php

namespace App\Livewire\Account;

use App\Actions\Account\DeleteAccount;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;
use Livewire\Component;

/**
 * Sécurité du compte dans Préférences : double authentification (application TOTP et codes
 * de secours) et « Mes données » (téléchargement, suppression du compte).
 * Chaque changement redemande le mot de passe actuel.
 */
class Security extends Component
{
    public string $password = '';

    public string $code = '';

    /** Codes de secours montrés juste après leur création, une seule fois. */
    public bool $showRecoveryCodes = false;

    public string $deletePassword = '';

    public bool $deleteConfirmed = false;

    public function enable(EnableTwoFactorAuthentication $enable): void
    {
        $this->checkPassword();
        $enable(auth()->user());
        $this->reset('password', 'code');
    }

    public function confirm(ConfirmTwoFactorAuthentication $confirm): void
    {
        $this->validate(['code' => ['required', 'string', 'max:10']], attributes: ['code' => __('code')]);

        try {
            $confirm(auth()->user(), $this->code);
        } catch (ValidationException) {
            throw ValidationException::withMessages(['code' => __('Ce code ne correspond pas. Vérifiez l’heure de votre téléphone et réessayez.')]);
        }

        $this->reset('code');
        $this->showRecoveryCodes = true;
    }

    public function regenerate(GenerateNewRecoveryCodes $generate): void
    {
        $this->checkPassword();
        $generate(auth()->user());
        $this->reset('password');
        $this->showRecoveryCodes = true;
    }

    public function disable(DisableTwoFactorAuthentication $disable): void
    {
        $this->checkPassword();
        $disable(auth()->user());
        $this->reset('password', 'code', 'showRecoveryCodes');
    }

    public function deleteAccount(DeleteAccount $delete)
    {
        $this->validate([
            'deletePassword' => ['required', 'current_password'],
            'deleteConfirmed' => ['accepted'],
        ], attributes: ['deletePassword' => __('mot de passe'), 'deleteConfirmed' => __('confirmation')]);

        $delete->handle(auth()->user());

        // Sans renouveler le jeton « se souvenir de moi » : cela réenregistrerait le compte supprimé.
        auth()->guard('web')->logoutCurrentDevice();
        session()->invalidate();
        session()->regenerateToken();
        session()->flash('status', __('Votre compte et vos données ont été supprimés.'));

        return $this->redirectRoute('login');
    }

    private function checkPassword(): void
    {
        $this->validate(['password' => ['required', 'current_password']], attributes: ['password' => __('mot de passe')]);
    }

    public function render()
    {
        $user = auth()->user();

        return view('livewire.account.security', [
            'pending' => $user->two_factor_secret !== null && $user->two_factor_confirmed_at === null,
            'enabled' => $user->two_factor_confirmed_at !== null,
            'owned' => $user->ownedCampaigns()->count(),
        ]);
    }
}
