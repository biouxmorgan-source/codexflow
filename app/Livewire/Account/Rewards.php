<?php

namespace App\Livewire\Account;

use App\Support\GiftCodes;
use App\Support\Plans\Plans;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

/**
 * Mes récompenses : ce qu'un contributeur du Kickstarter a reçu, ses codes cadeaux à
 * partager (et qui les a utilisés), et la case pour utiliser un code reçu.
 */
class Rewards extends Component
{
    public string $code = '';

    public function redeem(): void
    {
        $this->validate(['code' => ['required', 'string', 'max:30']], attributes: ['code' => __('code')]);

        // On ne devine pas les codes en essayant à la chaîne.
        $key = 'gift-code:'.auth()->id();
        if (RateLimiter::tooManyAttempts($key, 10)) {
            $this->addError('code', __('Trop d’essais : réessayez dans :seconds secondes.', ['seconds' => RateLimiter::availableIn($key)]));

            return;
        }
        RateLimiter::hit($key, 60);

        if ($problem = GiftCodes::redeem(auth()->user(), $this->code)) {
            $this->addError('code', $problem);

            return;
        }

        $this->code = '';
        session()->now('status', __('Code utilisé : votre Premium court jusqu’au :date.', ['date' => auth()->user()->fresh()->plan_ends_at->isoFormat('LL')]));
    }

    public function render()
    {
        $user = auth()->user()->fresh();

        return view('livewire.account.rewards', [
            'me' => $user,
            'plan' => Plans::effective($user),
            'bonusMb' => (int) Plans::settings()['founder_bonus_mb'],
            'gifts' => $user->giftCodes()->with('redeemer')->orderBy('id')->get(),
        ])->title(__('Mes récompenses'));
    }
}
